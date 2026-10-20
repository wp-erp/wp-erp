"""Tiny GitHub REST client (stdlib only) plus Actions helpers.

In dry-run mode every write is logged to agent-output/github-writes.jsonl
instead of being sent, so the whole flow can be exercised safely.
"""
from __future__ import annotations

import json
import os
import urllib.error
import urllib.parse
import urllib.request
from typing import Any, Iterator

API = os.environ.get("GITHUB_API_URL", "https://api.github.com")


class GitHubError(RuntimeError):
    pass


class GitHub:
    def __init__(self, repo: str | None, token: str | None = None, dry_run: bool = False, log_dir=None):
        self.repo = repo
        self.token = token or os.environ.get("GITHUB_TOKEN") or os.environ.get("GH_TOKEN")
        self.dry_run = dry_run or not self.token
        self.log_dir = log_dir

    # -- low level ----------------------------------------------------------
    def _request(self, method: str, path: str, body: Any = None, accept: str = "application/vnd.github+json") -> Any:
        url = path if path.startswith("http") else f"{API}{path}"
        data = json.dumps(body).encode() if body is not None else None
        req = urllib.request.Request(url, data=data, method=method)
        req.add_header("Accept", accept)
        req.add_header("X-GitHub-Api-Version", "2022-11-28")
        req.add_header("User-Agent", "wp-release-agent")
        if self.token:
            req.add_header("Authorization", f"Bearer {self.token}")
        if data is not None:
            req.add_header("Content-Type", "application/json")
        try:
            with urllib.request.urlopen(req, timeout=60) as resp:
                raw = resp.read()
                if "json" not in accept:
                    return raw.decode("utf-8", "replace")
                return json.loads(raw) if raw else None
        except urllib.error.HTTPError as e:
            detail = e.read().decode("utf-8", "replace")[:500]
            raise GitHubError(f"{method} {path} -> {e.code}: {detail}") from e

    def get(self, path: str, **kw) -> Any:
        return self._request("GET", path, **kw)

    def write(self, method: str, path: str, body: Any) -> Any:
        if self.dry_run:
            self._log_write(method, path, body)
            return {"dry_run": True, "html_url": f"(dry-run) {method} {path}", "number": 0}
        return self._request(method, path, body)

    def _log_write(self, method: str, path: str, body: Any) -> None:
        if not self.log_dir:
            return
        os.makedirs(self.log_dir, exist_ok=True)
        with open(os.path.join(self.log_dir, "github-writes.jsonl"), "a", encoding="utf-8") as fh:
            fh.write(json.dumps({"method": method, "path": path, "body": body}) + "\n")

    def paginate(self, path: str, limit: int = 500) -> Iterator[Any]:
        sep = "&" if "?" in path else "?"
        page, seen = 1, 0
        while seen < limit:
            items = self.get(f"{path}{sep}per_page=100&page={page}")
            if not items:
                return
            for it in items:
                yield it
                seen += 1
                if seen >= limit:
                    return
            if len(items) < 100:
                return
            page += 1

    # -- pull requests --------------------------------------------------------
    def pr(self, number: int) -> dict:
        return self.get(f"/repos/{self.repo}/pulls/{number}")

    def pr_files(self, number: int) -> list[dict]:
        return list(self.paginate(f"/repos/{self.repo}/pulls/{number}/files", limit=3000))

    def pr_diff(self, number: int) -> str:
        return self.get(f"/repos/{self.repo}/pulls/{number}", accept="application/vnd.github.diff")

    def create_review(self, number: int, commit_id: str, body: str, event: str, comments: list[dict]) -> Any:
        return self.write("POST", f"/repos/{self.repo}/pulls/{number}/reviews", {
            "commit_id": commit_id, "body": body, "event": event, "comments": comments,
        })

    def upsert_pr_comment(self, number: int, marker: str, body: str) -> Any:
        """Edit our previous comment (found by a hidden marker) instead of piling up new ones."""
        body = f"{marker}\n{body}"
        if not self.dry_run:
            for c in self.paginate(f"/repos/{self.repo}/issues/{number}/comments"):
                if marker in (c.get("body") or ""):
                    return self.write("PATCH", f"/repos/{self.repo}/issues/comments/{c['id']}", {"body": body})
        return self.write("POST", f"/repos/{self.repo}/issues/{number}/comments", {"body": body})

    def merged_prs_since(self, base: str, since_iso: str, limit: int = 200) -> list[dict]:
        out = []
        for p in self.paginate(f"/repos/{self.repo}/pulls?state=closed&base={base}&sort=updated&direction=desc", limit=limit):
            if p.get("merged_at") and p["merged_at"] >= since_iso:
                out.append(p)
        return sorted(out, key=lambda p: p["merged_at"])

    def create_pr(self, head: str, base: str, title: str, body: str, draft: bool = False) -> Any:
        return self.write("POST", f"/repos/{self.repo}/pulls", {
            "head": head, "base": base, "title": title, "body": body, "draft": draft,
        })

    # -- issues ---------------------------------------------------------------
    def upsert_issue(self, label: str, title: str, body: str, key: str | None = None) -> Any:
        """One open issue per label (+ optional key in the title). Updates it if present."""
        if not self.dry_run:
            q = urllib.parse.quote(label)
            for it in self.paginate(f"/repos/{self.repo}/issues?state=open&labels={q}", limit=100):
                if "pull_request" in it:
                    continue
                if key is None or key in it["title"]:
                    return self.write("PATCH", f"/repos/{self.repo}/issues/{it['number']}", {"title": title, "body": body})
        return self.write("POST", f"/repos/{self.repo}/issues", {"title": title, "body": body, "labels": [label]})


# -- GitHub Actions helpers ---------------------------------------------------
def step_summary(markdown: str) -> None:
    path = os.environ.get("GITHUB_STEP_SUMMARY")
    if path:
        with open(path, "a", encoding="utf-8") as fh:
            fh.write(markdown + "\n")


def set_output(name: str, value: str) -> None:
    path = os.environ.get("GITHUB_OUTPUT")
    if path:
        with open(path, "a", encoding="utf-8") as fh:
            if "\n" in value:
                fh.write(f"{name}<<__EOF__\n{value}\n__EOF__\n")
            else:
                fh.write(f"{name}={value}\n")


def annotate(level: str, message: str, file: str | None = None, line: int | None = None) -> None:
    """Emit an Actions annotation (::warning file=..,line=..::msg)."""
    if not os.environ.get("GITHUB_ACTIONS"):
        return
    loc = ""
    if file:
        loc = f" file={file}" + (f",line={line}" if line else "")
    print(f"::{level}{loc}::{message.replace(chr(10), '%0A')}")
