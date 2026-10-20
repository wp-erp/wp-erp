"""WordPress-specific helpers: readme headers, version bumping, changelog,
upstream versions and deprecation lists. All deterministic, no LLM."""
from __future__ import annotations

import datetime as dt
import fnmatch
import json
import re
import subprocess
import urllib.parse
import urllib.request
from dataclasses import dataclass
from pathlib import Path

from .config import Config

UA = {"User-Agent": "wp-release-agent"}


def http_get(url: str, timeout: int = 30) -> str:
    req = urllib.request.Request(url, headers=UA)
    with urllib.request.urlopen(req, timeout=timeout) as r:
        return r.read().decode("utf-8", "replace")


# -- readme.txt -------------------------------------------------------------------
def readme_headers(cfg: Config) -> dict[str, str]:
    text = cfg.path(cfg.plugin["readme"]).read_text(encoding="utf-8")
    head = text.split("== Description ==")[0]
    out = {}
    for line in head.splitlines():
        m = re.match(r"^([A-Za-z][A-Za-z ]+):\s*(.+)$", line.strip())
        if m:
            out.setdefault(m.group(1).strip(), m.group(2).strip())
    return out


def set_readme_header(cfg: Config, key: str, value: str) -> bool:
    p = cfg.path(cfg.plugin["readme"])
    text = p.read_text(encoding="utf-8")
    new, n = re.subn(rf"^({re.escape(key)}:\s*)(.+)$", rf"\g<1>{value}", text, count=1, flags=re.M)
    if n and new != text:
        p.write_text(new, encoding="utf-8")
        return True
    return False


# -- versions ---------------------------------------------------------------------
@dataclass
class VersionHit:
    file: str
    pattern: str
    value: str | None


def read_versions(cfg: Config) -> list[VersionHit]:
    hits = []
    for loc in cfg.plugin["version_locations"]:
        text = cfg.path(loc["file"]).read_text(encoding="utf-8")
        m = re.search(loc["pattern"], text, flags=re.M)
        hits.append(VersionHit(loc["file"], loc["pattern"], m.group(1) if m else None))
    return hits


def bump_versions(cfg: Config, new_version: str) -> list[str]:
    changed = []
    for loc in cfg.plugin["version_locations"]:
        p = cfg.path(loc["file"])
        text = p.read_text(encoding="utf-8")
        m = re.search(loc["pattern"], text, flags=re.M)
        if not m:
            raise RuntimeError(f"version pattern not found in {loc['file']}: {loc['pattern']}")
        new_text = text[: m.start(1)] + new_version + text[m.end(1):]
        if new_text != text:
            p.write_text(new_text, encoding="utf-8")
            changed.append(loc["file"])
    return sorted(set(changed))


def parse_ver(v: str) -> tuple:
    parts = re.findall(r"\d+", v)
    return tuple(int(x) for x in parts[:3]) + (0,) * (3 - len(parts[:3]))


def next_version(current: str, bump: str) -> str:
    major, minor, patch = parse_ver(current)
    if bump == "major":
        return f"{major + 1}.0.0"
    if bump == "minor":
        return f"{major}.{minor + 1}.0"
    return f"{major}.{minor}.{patch + 1}"


def wp_branch(v: str) -> str:
    """'7.1.3' -> '7.1' (what 'Tested up to' should carry)."""
    major, minor, _ = parse_ver(v)
    return f"{major}.{minor}"


# -- changelog --------------------------------------------------------------------
def render_changelog(cfg: Config, version: str, entries: list[dict], date: dt.date | None = None) -> str:
    ch = cfg.changelog
    date = date or dt.date.today()
    fmt = ch.get("date_format", "%b %-d, %Y")
    try:
        date_s = date.strftime(fmt)
    except ValueError:  # %-d is not portable
        date_s = date.strftime(fmt.replace("%-d", "%d")).replace(" 0", " ")
    lines = [ch["header"].format(version=version, date=date_s)]
    order = {c: i for i, c in enumerate(ch["categories"])}
    for e in sorted(entries, key=lambda e: order.get(e["type"], 99)):
        text = e["text"].strip().rstrip(".") + "."
        if ch.get("link_prs") and e.get("pr") and cfg.github_repo:
            text += f" [#{e['pr']}](https://github.com/{cfg.github_repo}/pull/{e['pr']})"
        lines.append(f"* [{e['type']}] {text}")
    return "\n".join(lines) + "\n"


def insert_changelog(cfg: Config, block: str) -> None:
    p = cfg.path(cfg.changelog["file"])
    text = p.read_text(encoding="utf-8")
    anchor = cfg.changelog["anchor"]
    if anchor not in text:
        raise RuntimeError(f"changelog anchor {anchor!r} not found in {p.name}")
    text = text.replace(anchor, f"{anchor}\n{block}", 1)
    p.write_text(text, encoding="utf-8")


# -- git --------------------------------------------------------------------------
def git(cfg: Config, *args: str, check: bool = True) -> str:
    res = subprocess.run(["git", "-C", str(cfg.repo_root), *args], capture_output=True, text=True)
    if check and res.returncode != 0:
        raise RuntimeError(f"git {' '.join(args)} failed: {res.stderr.strip()}")
    return res.stdout.strip()


def last_release_tag(cfg: Config) -> str | None:
    tags = git(cfg, "tag", "--list", check=False).splitlines()
    rx = re.compile("^" + re.escape(cfg.plugin["tag_format"]).replace(r"\{version\}", r"(\d+\.\d+\.\d+)") + "$")
    rel = [(parse_ver(m.group(1)), t) for t in tags if (m := rx.match(t))]
    return max(rel)[1] if rel else None


# -- dist package check -----------------------------------------------------------
def distignore_patterns(cfg: Config) -> list[str]:
    p = cfg.path(".distignore")
    if not p.exists():
        return []
    return [l.strip() for l in p.read_text().splitlines() if l.strip() and not l.startswith("#")]


def shipped_files(cfg: Config) -> list[str]:
    """Tracked files that would end up in the wp.org zip after .distignore."""
    pats = distignore_patterns(cfg)
    files = git(cfg, "ls-files").splitlines()

    def ignored(f: str) -> bool:
        for pat in pats:
            if pat.startswith("/"):
                base = pat.lstrip("/").rstrip("/")
                if f == base or f.startswith(base + "/") or fnmatch.fnmatch(f, base):
                    return True
            elif fnmatch.fnmatch(Path(f).name, pat) or fnmatch.fnmatch(f, pat):
                return True
        return False

    return [f for f in files if not ignored(f)]


SUSPICIOUS = [
    (r"(^|/)\.env($|\.)", "environment file"),
    (r"\.sql$", "database dump"),
    (r"(^|/)(tests?|e2e|spec)/", "test files"),
    (r"(^|/)\.claude/", "AI assistant config"),
    (r"(^|/)node_modules/", "node_modules"),
    (r"\.(zip|tar|gz|log)$", "archive or log"),
    (r"(^|/)(phpunit|phpcs|codeception|playwright)\.", "dev tool config"),
    (r"(^|/)\.github/", "CI config"),
]


def suspicious_shipped(cfg: Config) -> list[tuple[str, str]]:
    out = []
    for f in shipped_files(cfg):
        for rx, why in SUSPICIOUS:
            if re.search(rx, f):
                out.append((f, why))
                break
    return out


# -- upstream versions ------------------------------------------------------------
def wp_versions() -> dict[str, str | None]:
    data = json.loads(http_get("https://api.wordpress.org/core/version-check/1.7/"))
    latest = data["offers"][0]["version"]
    beta = None
    try:
        b = json.loads(http_get("https://api.wordpress.org/core/version-check/1.7/?channel=beta"))
        for o in b.get("offers", []):
            v = o.get("version", "")
            if re.search(r"(beta|rc)", v, re.I) or parse_ver(v) > parse_ver(latest):
                beta = v
                break
    except Exception:
        pass
    return {"latest": latest, "beta": beta}


def plugin_info(slug: str) -> dict:
    q = urllib.parse.urlencode({
        "action": "plugin_information", "request[slug]": slug,
        "request[fields][sections]": 0, "request[fields][versions]": 0,
    })
    return json.loads(http_get(f"https://api.wordpress.org/plugins/info/1.2/?{q}"))


# -- deprecations -----------------------------------------------------------------
WP_DEPRECATED_FILES = [
    "wp-includes/deprecated.php",
    "wp-includes/pluggable-deprecated.php",
    "wp-includes/ms-deprecated.php",
    "wp-admin/includes/deprecated.php",
]
WC_DEPRECATED_FILES = [
    "plugins/woocommerce/includes/wc-deprecated-functions.php",
    "plugins/woocommerce/includes/compat/class-wc-deprecated-action-hooks.php",
    "plugins/woocommerce/includes/compat/class-wc-deprecated-filter-hooks.php",
]

_FN_RX = re.compile(r"^function\s+&?\s*([A-Za-z_][A-Za-z0-9_]*)\s*\((.*?)^\}", re.M | re.S)
_DEP_RX = re.compile(r"_deprecated_function\(\s*__FUNCTION__\s*,\s*'([^']+)'(?:\s*,\s*'([^']*)')?")


def parse_deprecated_functions(php_src: str) -> dict[str, dict]:
    """{func_name: {"since": "x.y", "replacement": "..."}} from a core deprecated.php."""
    out = {}
    for m in _FN_RX.finditer(php_src):
        name, body = m.group(1), m.group(2)
        d = _DEP_RX.search(body)
        if d:
            out[name] = {"since": d.group(1), "replacement": d.group(2) or ""}
    return out


def parse_deprecated_wc_hooks(php_src: str) -> dict[str, dict]:
    """WooCommerce keeps maps like 'new_hook' => 'old_hook' plus a version map."""
    out = {}
    for m in re.finditer(r"'([a-z0-9_]+)'\s*=>\s*'([a-z0-9_]+)'", php_src):
        new, old = m.group(1), m.group(2)
        if re.match(r"^\d", old):
            continue
        out[old] = {"since": "", "replacement": new}
    for m in re.finditer(r"'([a-z0-9_]+)'\s*=>\s*'(\d+\.\d+(?:\.\d+)?)'", php_src):
        if m.group(1) in out:
            out[m.group(1)]["since"] = m.group(2)
    return out


def fetch_wp_deprecations(version: str) -> dict[str, dict]:
    out = {}
    for f in WP_DEPRECATED_FILES:
        try:
            src = http_get(f"https://raw.githubusercontent.com/WordPress/WordPress/{version}/{f}")
        except Exception:
            continue
        out.update(parse_deprecated_functions(src))
    return out


def fetch_wc_deprecations(ref: str = "trunk") -> tuple[dict, dict]:
    funcs, hooks = {}, {}
    for f in WC_DEPRECATED_FILES:
        try:
            src = http_get(f"https://raw.githubusercontent.com/woocommerce/woocommerce/{ref}/{f}")
        except Exception:
            continue
        if "hooks" in f:
            hooks.update(parse_deprecated_wc_hooks(src))
        else:
            funcs.update(parse_deprecated_functions(src))
    return funcs, hooks


def find_usages(cfg: Config, names: list[str], kind: str = "function") -> dict[str, list[str]]:
    """Locate calls to the given functions (or hooks) in the plugin's own PHP code."""
    if not names:
        return {}
    hits: dict[str, list[str]] = {}
    chunk = 200
    for i in range(0, len(names), chunk):
        part = names[i:i + chunk]
        if kind == "function":
            rx = r"(^|[^A-Za-z0-9_>:$\\])(" + "|".join(map(re.escape, part)) + r")\s*\("
        else:
            rx = r"(add_action|add_filter|do_action|apply_filters)\s*\(\s*['\"](" + "|".join(map(re.escape, part)) + r")['\"]"
        res = subprocess.run(
            ["git", "-C", str(cfg.repo_root), "grep", "-n", "-I", "-E", rx, "--", "*.php",
             ":!vendor", ":!includes/Lib", ":!node_modules", ":!tests"],
            capture_output=True, text=True,
        )
        for line in res.stdout.splitlines():
            code = line.split(":", 2)[-1]
            m = re.search(rx, code)
            if not m:
                continue
            if kind == "function":
                if re.match(r"^\s*(\*|//|#)", code):
                    continue  # comment
                if re.search(r"function\s+&?\s*$", code[: m.start(2)]):
                    continue  # a definition (incl. methods with the same name), not a call
            hits.setdefault(m.group(2), []).append(":".join(line.split(":", 2)[:2]))
    return hits
