"""Deterministic checks that run before the model sees a PR.

Their output is given to Claude as *leads*: cheap regex hits that it then
confirms or dismisses by reading the surrounding code. This keeps the review
grounded and catches things a model might skim past in a big diff.
"""
from __future__ import annotations

import fnmatch
import json
import re
import shutil
import subprocess
from dataclasses import dataclass, field, asdict
from pathlib import Path


# -- unified diff parsing -----------------------------------------------------------
@dataclass
class FileDiff:
    path: str
    old_path: str | None = None
    status: str = "modified"
    added: dict[int, str] = field(default_factory=dict)     # new line no -> text
    removed: list[str] = field(default_factory=list)
    hunks: list[str] = field(default_factory=list)

    @property
    def commentable_lines(self) -> set[int]:
        return set(self.added)


_HUNK = re.compile(r"^@@ -(\d+)(?:,\d+)? \+(\d+)(?:,(\d+))? @@")


def parse_diff(diff: str) -> list[FileDiff]:
    files: list[FileDiff] = []
    cur: FileDiff | None = None
    new_ln = 0
    hunk_buf: list[str] = []
    for line in diff.splitlines():
        if line.startswith("diff --git "):
            if cur and hunk_buf:
                cur.hunks.append("\n".join(hunk_buf))
            hunk_buf = []
            m = re.match(r"diff --git a/(.+?) b/(.+)$", line)
            cur = FileDiff(path=m.group(2) if m else line, old_path=m.group(1) if m else None)
            files.append(cur)
            continue
        if cur is None:
            continue
        if line.startswith("new file mode"):
            cur.status = "added"
        elif line.startswith("deleted file mode"):
            cur.status = "removed"
        elif line.startswith("rename to "):
            cur.status = "renamed"
        elif line.startswith(("+++ ", "--- ", "index ", "similarity ", "rename from ", "old mode", "new mode", "Binary files")):
            continue
        elif (m := _HUNK.match(line)):
            if hunk_buf:
                cur.hunks.append("\n".join(hunk_buf))
            hunk_buf = [line]
            new_ln = int(m.group(2))
        elif line.startswith("+"):
            cur.added[new_ln] = line[1:]
            hunk_buf.append(f"{new_ln:>5} +{line[1:]}")
            new_ln += 1
        elif line.startswith("-"):
            cur.removed.append(line[1:])
            hunk_buf.append(f"      -{line[1:]}")
        elif line.startswith("\\"):
            continue
        else:
            hunk_buf.append(f"{new_ln:>5}  {line[1:] if line.startswith(' ') else line}")
            new_ln += 1
    if cur and hunk_buf:
        cur.hunks.append("\n".join(hunk_buf))
    return files


def filter_paths(files: list[FileDiff], skip: list[str]) -> list[FileDiff]:
    return [f for f in files if not any(fnmatch.fnmatch(f.path, p) for p in skip)]


def render_numbered_diff(files: list[FileDiff], max_chars: int) -> tuple[str, list[str]]:
    """Diff with new-file line numbers so the model can cite exact lines.
    Returns (text, list of files that were left out for size)."""
    parts, omitted, size = [], [], 0
    for f in files:
        block = f"### {f.path} ({f.status})\n" + "\n".join(f.hunks)
        if size + len(block) > max_chars:
            omitted.append(f.path)
            continue
        parts.append(block)
        size += len(block)
    return "\n\n".join(parts), omitted


# -- security leads -------------------------------------------------------------------
@dataclass
class Lead:
    path: str
    line: int
    rule: str
    detail: str


SECURITY_RULES: list[tuple[str, re.Pattern, str]] = [
    ("raw-input", re.compile(r"\$_(GET|POST|REQUEST|COOKIE|SERVER)\s*\["),
     "Superglobal read; check wp_unslash + sanitize_* and nonce/capability before use"),
    ("sql-interpolation", re.compile(r"\$wpdb->(query|get_results|get_row|get_var|get_col)\s*\(\s*(\"[^\"]*\$|'[^']*'\s*\.|\$sql|\$query)"),
     "Query built from a string/variable; confirm it goes through $wpdb->prepare()"),
    ("sql-orderby", re.compile(r"ORDER\s+BY\s+[\"'.\s]*\$|\{\$(order|orderby|sort)", re.I),
     "Dynamic ORDER BY; must be whitelisted (prepare() can't protect identifiers)"),
    ("unserialize", re.compile(r"\b(maybe_)?unserialize\s*\("),
     "Unserialize; if input can be user-influenced this is PHP object injection"),
    ("echo-unescaped", re.compile(r"\becho\s+\$[A-Za-z_]|\becho\s+[^;]*\$_(GET|POST|REQUEST)|<\?=\s*\$"),
     "Echoing a variable; confirm it is escaped (esc_html/esc_attr/esc_url/wp_kses)"),
    ("ajax-handler", re.compile(r"add_action\(\s*['\"]wp_ajax_(nopriv_)?([A-Za-z0-9_\-]+)"),
     "New AJAX handler; it must verify a nonce and current_user_can() (and object ownership)"),
    ("rest-route", re.compile(r"register_rest_route\s*\("),
     "New REST route; check permission_callback is present and not __return_true for private data"),
    ("permissive-permission", re.compile(r"permission_callback['\"]?\s*=>\s*['\"]__return_true"),
     "permission_callback is __return_true"),
    ("file-include", re.compile(r"\b(include|require)(_once)?\s*\(?\s*\$"),
     "Include of a variable path; confirm it can't be user-controlled"),
    ("exec", re.compile(r"\b(eval|assert|shell_exec|exec|system|passthru|proc_open|popen)\s*\("),
     "Code/command execution primitive"),
    ("redirect", re.compile(r"\bwp_redirect\s*\(\s*\$"),
     "Redirect to a variable; prefer wp_safe_redirect"),
    ("upload", re.compile(r"move_uploaded_file|\$_FILES\s*\["),
     "File upload handling; check type validation (wp_check_filetype_and_ext) and capability"),
]


def security_leads(files: list[FileDiff]) -> list[Lead]:
    leads = []
    for f in files:
        if not f.path.endswith(".php"):
            continue
        for ln, text in f.added.items():
            if text.lstrip().startswith(("//", "*", "#")):
                continue
            for rule, rx, detail in SECURITY_RULES:
                if rx.search(text):
                    leads.append(Lead(f.path, ln, rule, detail))
    return leads


# -- breaking-change detection --------------------------------------------------------
_HOOK_RX = re.compile(r"\b(do_action|apply_filters|do_action_ref_array|apply_filters_ref_array)\s*\(\s*['\"]([A-Za-z0-9_\-./]+)['\"]")
_FUNC_RX = re.compile(r"^\s*function\s+&?\s*([A-Za-z_][A-Za-z0-9_]*)\s*\(")
_METHOD_RX = re.compile(r"^\s*(?:final\s+|abstract\s+|static\s+)*public\s+(?:static\s+)?function\s+&?\s*([A-Za-z_][A-Za-z0-9_]*)\s*\(")


def removed_api(files: list[FileDiff], repo_root: Path) -> list[dict]:
    """Hooks fired / global functions / public methods that a PR removes and that
    no longer exist anywhere in the repo. These break erp-pro and add-ons."""
    out = []
    for f in files:
        if not (f.path.endswith(".php") or (f.old_path or "").endswith(".php")):
            continue
        added_text = "\n".join(f.added.values())
        for line in f.removed:
            for kind, rx, group in (("hook", _HOOK_RX, 2), ("function", _FUNC_RX, 1), ("public method", _METHOD_RX, 1)):
                m = rx.search(line)
                if not m:
                    continue
                name = m.group(group)
                if name in added_text or name.startswith("__"):
                    continue  # moved within the same file / magic method
                if _still_exists(repo_root, kind, name):
                    continue
                out.append({"kind": kind, "name": name, "file": f.old_path or f.path})
    # dedupe
    seen, uniq = set(), []
    for item in out:
        key = (item["kind"], item["name"])
        if key not in seen:
            seen.add(key)
            uniq.append(item)
    return uniq


def _still_exists(root: Path, kind: str, name: str) -> bool:
    if kind == "hook":
        rx = r"(do_action|apply_filters)(_ref_array)?\s*\(\s*['\"]" + re.escape(name) + r"['\"]"
    else:
        rx = r"function\s+&?\s*" + re.escape(name) + r"\s*\("
    res = subprocess.run(["git", "-C", str(root), "grep", "-q", "-E", rx, "--", "*.php", ":!vendor"],
                         capture_output=True)
    return res.returncode == 0


# -- phpcs ----------------------------------------------------------------------------
def phpcs_command(repo_root: Path) -> list[str] | None:
    """phpcs binary plus installed_paths for every standard found in vendor/.
    (The composer installer plugin doesn't always register them, e.g. with
    old dealerdirect versions, which makes the ruleset fail to load.)"""
    phpcs = repo_root / "vendor/bin/phpcs"
    if not phpcs.exists():
        found = shutil.which("phpcs")
        if not found:
            return None
        phpcs = Path(found)
    cmd = [str(phpcs)]
    std_dirs = []
    for pattern in ("vendor/wp-coding-standards/*", "vendor/phpcompatibility/*", "vendor/phpcsstandards/*"):
        std_dirs += sorted(str(p) for p in repo_root.glob(pattern) if p.is_dir())
    if std_dirs:
        cmd += ["--runtime-set", "installed_paths", ",".join(std_dirs)]
    return cmd


class PhpcsError(RuntimeError):
    pass


def run_phpcs(repo_root: Path, files: list[FileDiff], max_findings: int = 80) -> list[dict] | None:
    """Run the repo's own phpcs ruleset on changed PHP files, keep findings on
    added lines only. Returns None if phpcs isn't installed or can't run."""
    cmd = phpcs_command(repo_root)
    if not cmd:
        return None
    targets = [f for f in files if f.path.endswith(".php") and f.status != "removed" and (repo_root / f.path).exists()]
    if not targets:
        return []
    res = subprocess.run(
        [*cmd, "--report=json", "-q", "--no-colors", *[f.path for f in targets]],
        cwd=repo_root, capture_output=True, text=True, timeout=600,
    )
    try:
        report = json.loads(res.stdout or "{}")
    except json.JSONDecodeError:
        print(f"phpcs could not run: {(res.stdout or res.stderr)[:300]}")
        return None
    by_path = {f.path: f for f in targets}
    out = []
    for abs_path, data in (report.get("files") or {}).items():
        rel = str(Path(abs_path).resolve().relative_to(repo_root.resolve())) if Path(abs_path).is_absolute() else abs_path
        fd = by_path.get(rel)
        if not fd:
            continue
        for msg in data.get("messages", []):
            if msg.get("line") in fd.added:
                out.append({"path": rel, "line": msg["line"], "severity": msg.get("type"),
                            "source": msg.get("source"), "message": msg.get("message")})
    # Security sniffs first; they matter most
    out.sort(key=lambda m: (0 if "Security" in (m["source"] or "") else 1, m["severity"] != "ERROR"))
    return out[:max_findings]


def leads_as_dicts(leads: list[Lead]) -> list[dict]:
    return [asdict(l) for l in leads]
