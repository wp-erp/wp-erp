"""Fold one matrix cell's raw output into compat-result-<cell>.json.

usage: collect.py WORK_DIR PHP WP_REQUESTED WP_ACTUAL PLUGIN_SLUG
"""
import json
import sys
from pathlib import Path

work, php, wp_req, wp_real, slug = Path(sys.argv[1]), *sys.argv[2:6]
prefix = f"wp-content/plugins/{slug}/"


def origin(path: str) -> str:
    if prefix in path:
        rel = path.split(prefix, 1)[1]
        return "plugin-vendor" if rel.startswith(("vendor/", "includes/Lib/")) else "plugin"
    if "/plugins/" in path:
        return "other-plugin"
    if path.startswith(("wp-includes", "wp-admin")) or "/wp-includes/" in path or "/wp-admin/" in path:
        return "core"
    return "unknown"


errors = []
log = work / "errors.jsonl"
if log.exists():
    for line in log.read_text().splitlines():
        try:
            e = json.loads(line)
        except json.JSONDecodeError:
            continue
        e["origin"] = origin(e.get("file", ""))
        if e["origin"].startswith("plugin"):
            e["file"] = e["file"].split(prefix, 1)[1]
        errors.append(e)

steps = []
if (work / "steps.jsonl").exists():
    steps = [json.loads(l) for l in (work / "steps.jsonl").read_text().splitlines() if l.strip()]

rest = {}
if (work / "rest.json").exists():
    rest = json.loads((work / "rest.json").read_text())
import re
rest_fail = {}
for r, v in (rest.get("routes") or {}).items():
    if v.get("status") in ("fatal", "exception") or (isinstance(v.get("status"), int) and v["status"] >= 500):
        code = re.sub(r"<[^>]+>", " ", str(v.get("code", "")))
        rest_fail[r] = {"status": v.get("status"), "code": " ".join(code.split())[:400]}

print(json.dumps({
    "cell": f"php{php}-wp{wp_req}",
    "php": php, "wp_requested": wp_req, "wp": wp_real,
    "steps": steps,
    "errors": errors,
    "rest": {"total": rest.get("total", 0), "failed": rest_fail, "crashed": rest.get("crashed", [])},
}, indent=1))
