"""Compatibility testing: build the PHP x WordPress matrix, then analyze the
results of every cell (errors, REST sweep, admin pages, PHPCompatibility)."""
from __future__ import annotations

import json
import re
from collections import defaultdict
from pathlib import Path

from ..config import Config
from ..github import GitHub, set_output, step_summary
from ..llm import run_agent
from ..repo_tools import RepoTools
from ..wp import parse_ver, readme_headers, wp_versions

MARKER = "<!-- wp-release-agent:compat -->"


# -- matrix -----------------------------------------------------------------------
def matrix(cfg: Config) -> dict:
    php = list(cfg.compat.get("php", ["7.4", "8.3"]))
    headers = readme_headers(cfg)
    min_php = headers.get("Requires PHP")
    if min_php:
        php = [p for p in php if parse_ver(p) >= parse_ver(min_php)] or php
    versions = wp_versions()
    include = []
    for key in cfg.compat.get("wordpress", ["latest"]):
        if key == "min":
            v = headers.get("Requires at least")
            if not v:
                continue
            # Old WordPress can't run new PHP: pair the minimum WP with the lowest PHP only.
            include.append({"wp": v, "php": php[0], "label": f"min WP {v}"})
            continue
        if key == "beta":
            v = versions.get("beta")
            if not v:
                continue
        elif key == "latest":
            v = versions["latest"]
        elif key == "nightly":
            v = "nightly"
        else:
            v = key
        include += [{"wp": v, "php": p, "label": f"{key} WP {v}"} for p in php]
    out = {"include": include}
    set_output("matrix", json.dumps(out))
    print(json.dumps(out, indent=1))
    return out


# -- analysis ---------------------------------------------------------------------
def _norm(msg: str) -> str:
    """Group the same error raised with different values."""
    msg = re.sub(r"'[^']{0,80}'", "'…'", msg)
    msg = re.sub(r"\b\d+\b", "N", msg)
    return msg[:300]


def load_results(results_dir: Path) -> list[dict]:
    cells = []
    for p in sorted(results_dir.rglob("compat-result-*.json")):
        try:
            cells.append(json.loads(p.read_text()))
        except json.JSONDecodeError:
            print(f"skipping unreadable {p}")
    return cells


def aggregate(cfg: Config, cells: list[dict]) -> dict:
    fail_on = set(cfg.compat.get("fail_on", ["fatal", "error", "warning"]))
    clusters: dict[tuple, dict] = {}
    cell_status = {}
    for c in cells:
        bad_steps = [s for s in c.get("steps", []) if s["status"] != "ok"]
        blocking = []
        for e in c.get("errors", []):
            key = (e["origin"], e["type"], _norm(e["message"]), e.get("file"), e.get("line"))
            cl = clusters.setdefault(key, {
                "origin": e["origin"], "type": e["type"], "message": e["message"],
                "file": e.get("file"), "line": e.get("line"), "cells": [], "contexts": set(),
            })
            if c["cell"] not in cl["cells"]:
                cl["cells"].append(c["cell"])
            cl["contexts"].add(e.get("context", "")[:120])
            if e["origin"] == "plugin" and e["type"] in fail_on:
                blocking.append(e)
        rest_failed = c.get("rest", {}).get("failed", {})
        cell_status[c["cell"]] = {
            "php": c["php"], "wp": c["wp"],
            "ok": not bad_steps and not blocking and not rest_failed,
            "failed_steps": bad_steps, "blocking_errors": len(blocking),
            "rest_total": c.get("rest", {}).get("total", 0), "rest_failed": rest_failed,
            "deprecations": sum(1 for e in c.get("errors", []) if e["origin"] == "plugin" and e["type"] == "deprecated"),
        }
    order = {"fatal": 0, "error": 1, "warning": 2, "doing_it_wrong": 3, "notice": 4, "deprecated": 5}
    cl_list = sorted(clusters.values(), key=lambda x: (x["origin"] != "plugin", order.get(x["type"], 9), -len(x["cells"])))
    for cl in cl_list:
        cl["contexts"] = sorted(cl["contexts"])[:5]
    return {"cells": cell_status, "clusters": cl_list}


def load_phpcompat(path: Path | None) -> list[dict]:
    if not path or not path.exists():
        return []
    try:
        data = json.loads(path.read_text())
    except json.JSONDecodeError:
        return []
    out = []
    for f, d in (data.get("files") or {}).items():
        for m in d.get("messages", []):
            out.append({"file": f, "line": m.get("line"), "type": m.get("type"), "message": m.get("message"), "source": m.get("source")})
    return out


SUBMIT = {
    "name": "submit_compat_report",
    "description": "Submit the compatibility diagnosis.",
    "input_schema": {
        "type": "object",
        "properties": {
            "summary": {"type": "string", "description": "2-3 sentences for the release manager: safe to claim compatibility or not."},
            "verdict": {"type": "string", "enum": ["compatible", "compatible-with-warnings", "incompatible"]},
            "problems": {
                "type": "array",
                "items": {
                    "type": "object",
                    "properties": {
                        "title": {"type": "string"},
                        "severity": {"type": "string", "enum": ["blocker", "major", "minor"]},
                        "affects": {"type": "string", "description": "Which PHP/WP versions, e.g. 'PHP 8.4 only'"},
                        "locations": {"type": "array", "items": {"type": "string"}, "description": "file:line"},
                        "root_cause": {"type": "string"},
                        "fix": {"type": "string", "description": "Concrete fix; include a short code snippet when useful."},
                    },
                    "required": ["title", "severity", "affects", "locations", "root_cause", "fix"],
                },
            },
            "ignored": {"type": "string", "description": "What you dismissed as noise (core/vendor/other plugins) and why."},
        },
        "required": ["summary", "verdict", "problems"],
    },
}


def _system(cfg: Config) -> str:
    return f"""You are the QA engineer responsible for {cfg.plugin['name']}'s compatibility claims (Requires PHP, Requires at least, Tested up to).
You get results from a PHP x WordPress test matrix: errors captured while activating the plugin, loading every one of its admin pages, dispatching every GET REST route as an administrator, and deactivating it, plus a static PHPCompatibility scan.

Your job:
- Group symptoms into root causes. One bug often shows up as dozens of errors across cells.
- Read the code at each location (read_file) before diagnosing. Explain why it fails on that PHP/WP version specifically (e.g. PHP 8.1 null-to-non-nullable deprecation, removed WP function, dynamic property in 8.2).
- Give a concrete fix that still works on the minimum supported PHP ({readme_headers(cfg).get('Requires PHP', 'unknown')}).
- Errors in WordPress core, other plugins, or bundled vendor libraries are usually noise; mention them under `ignored` unless the plugin causes them (e.g. passes bad arguments to a core function).
- A REST route returning 4xx with missing parameters is expected; 500s and fatals are not.
- Severity: blocker = fatal or data loss on a supported version; major = warnings/broken screen; minor = deprecations that will become errors later.
Call submit_compat_report when done."""


def analyze(cfg: Config, results_dir: str, phpcompat: str | None = None, pr_number: int | None = None) -> int:
    cells = load_results(Path(results_dir))
    if not cells:
        print("No compat results found.")
        return 1
    agg = aggregate(cfg, cells)
    pc = load_phpcompat(Path(phpcompat) if phpcompat else None)
    plugin_clusters = [c for c in agg["clusters"] if c["origin"] == "plugin"]

    if cfg.no_llm or (not plugin_clusters and not pc and all(c["ok"] for c in agg["cells"].values())):
        verdict = "compatible" if all(c["ok"] for c in agg["cells"].values()) else "incompatible"
        result = {"summary": "No plugin errors found across the matrix." if verdict == "compatible"
                  else "Failures found (automated summary, no AI diagnosis).",
                  "verdict": verdict, "problems": []}
    else:
        payload = {
            "cells": agg["cells"],
            "plugin_error_clusters": plugin_clusters[:60],
            "other_error_clusters_sample": [c for c in agg["clusters"] if c["origin"] != "plugin"][:15],
            "phpcompat_static_scan": pc[:80],
        }
        result = run_agent(cfg, task_name="compat", system=_system(cfg),
                           user="```json\n" + json.dumps(payload, indent=1, default=str)[:150000] + "\n```",
                           submit_tool=SUBMIT, tools=RepoTools(cfg.repo_root))

    md = render(cfg, result, agg, pc)
    cfg.write_output("compat-report.md", md)
    cfg.write_output("compat-report.json", json.dumps({"result": result, "aggregate": agg}, indent=1, default=str))
    step_summary(md)

    gh = GitHub(cfg.github_repo, dry_run=cfg.dry_run, log_dir=str(cfg.out_dir))
    if pr_number:
        gh.upsert_pr_comment(pr_number, MARKER, md)
    elif result["verdict"] != "compatible":
        wp_latest = max((c["wp"] for c in agg["cells"].values()), key=parse_ver)
        gh.upsert_issue(cfg.upstream.get("compat_issue_label", "compat-report"),
                        f"Compatibility report: {result['verdict']} (WP {wp_latest})", md)
    return 1 if result["verdict"] == "incompatible" else 0


def render(cfg: Config, result: dict, agg: dict, pc: list) -> str:
    icon = {"compatible": "✅", "compatible-with-warnings": "⚠️", "incompatible": "❌"}[result["verdict"]]
    out = [f"## {icon} Compatibility: {result['verdict']}", "", result["summary"], ""]
    out += ["| Cell | WordPress | PHP | Result | REST 5xx/fatal | Plugin deprecations |", "|---|---|---|---|---|---|"]
    for name, c in sorted(agg["cells"].items()):
        res = "✅ pass" if c["ok"] else f"❌ {c['blocking_errors']} errors" + (f", {len(c['failed_steps'])} failed steps" if c["failed_steps"] else "")
        out.append(f"| `{name}` | {c['wp']} | {c['php']} | {res} | {len(c['rest_failed'])}/{c['rest_total']} | {c['deprecations']} |")
    out.append("")
    if result.get("problems"):
        out.append("### Problems")
        for p in result["problems"]:
            sev = {"blocker": "🔴", "major": "🟠", "minor": "🟡"}[p["severity"]]
            out += [f"#### {sev} {p['title']}", f"**Affects:** {p['affects']} · **Where:** " + ", ".join(f"`{l}`" for l in p["locations"][:6]),
                    "", f"**Cause:** {p['root_cause']}", "", f"**Fix:** {p['fix']}", ""]
    else:
        clusters = [c for c in agg["clusters"] if c["origin"] == "plugin"]
        if clusters:
            out.append("### Plugin errors")
            out += [f"- **{c['type']}** `{c['file']}:{c['line']}` {c['message'][:200]} ({', '.join(c['cells'])})" for c in clusters[:30]]
            out.append("")
    rest = {}
    for n, c in agg["cells"].items():
        for route, v in c["rest_failed"].items():
            rest.setdefault((route, str(v.get("status")), str(v.get("code", ""))[:160]), []).append(n)
    if rest:
        out.append("<details><summary>REST routes returning 5xx or crashing</summary>\n")
        out += [f"- `GET {r}` → {st}: {code} ({', '.join(cells)})" for (r, st, code), cells in sorted(rest.items())]
        out.append("\n</details>\n")
    failed = {n: c for n, c in agg["cells"].items() if c["failed_steps"]}
    if failed:
        out.append("<details><summary>Failed steps</summary>\n")
        for n, c in failed.items():
            out += [f"- `{n}` {s['step']}: {s['detail'][:300]}" for s in c["failed_steps"]]
        out.append("\n</details>\n")
    if pc:
        out.append(f"<details><summary>PHPCompatibility static scan: {len(pc)} findings</summary>\n")
        out += [f"- `{f['file']}:{f['line']}` {f['message']}" for f in pc[:40]]
        out.append("\n</details>\n")
    if result.get("ignored"):
        out += [f"<sub>Ignored: {result['ignored']}</sub>", ""]
    return "\n".join(out)
