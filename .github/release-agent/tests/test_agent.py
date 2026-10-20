"""Unit tests. Run from .github/release-agent:  python -m pytest -q tests"""
from __future__ import annotations

import json
import shutil
import subprocess
import textwrap
from pathlib import Path
from types import SimpleNamespace

import pytest

from release_agent import scanners, wp
from release_agent.config import load_config
from release_agent.jobs import compat, review
from release_agent.llm import run_agent
from release_agent.repo_tools import RepoTools

AGENT_DIR = Path(__file__).resolve().parent.parent


# -- fixtures ---------------------------------------------------------------------
def git(root, *args):
    subprocess.run(["git", "-C", str(root), *args], check=True, capture_output=True,
                   env={"GIT_AUTHOR_NAME": "t", "GIT_AUTHOR_EMAIL": "t@t", "GIT_COMMITTER_NAME": "t",
                        "GIT_COMMITTER_EMAIL": "t@t", "PATH": "/usr/bin:/bin:/usr/local/bin"})


@pytest.fixture
def plugin(tmp_path, monkeypatch):
    """A minimal plugin repo laid out like WP ERP."""
    monkeypatch.delenv("ANTHROPIC_API_KEY", raising=False)
    root = tmp_path / "plugin"
    root.mkdir()
    (root / "wp-erp.php").write_text(textwrap.dedent("""\
        <?php
        /**
         * Plugin Name: ERP
         * Version: 1.2.3
         */
        define( 'WPERP_VERSION', '1.2.3' );
        function erp_old_helper( $id ) { return $id; }
        do_action( 'erp_loaded' );
        $x = get_page_by_title( 'Home' );
        class Foo { public function get_page_by_title() {} }
        """))
    (root / "readme.txt").write_text(textwrap.dedent("""\
        === ERP ===
        Requires at least: 5.6
        Tested up to: 7.0
        Requires PHP: 7.4
        Stable tag: 1.2.3

        == Description ==
        Hello.

        == Changelog ==
        = v1.2.3 → Sep 22, 2026
        --------------------------
        * [Fixed] Something old.
        """))
    (root / "package.json").write_text('{\n  "name": "x",\n  "version": "1.2.3"\n}\n')
    (root / ".distignore").write_text("/tests\n/.github\n")
    git(root, "init", "-q", "-b", "develop")
    git(root, "add", ".")
    git(root, "commit", "-q", "-m", "init")
    git(root, "tag", "v1.2.3")
    cfg = load_config(root, AGENT_DIR / "release-agent.toml", dry_run=True)
    cfg.out_dir = tmp_path / "out"
    return cfg


SAMPLE_DIFF = textwrap.dedent("""\
    diff --git a/includes/Ajax.php b/includes/Ajax.php
    index 111..222 100644
    --- a/includes/Ajax.php
    +++ b/includes/Ajax.php
    @@ -10,4 +10,6 @@ class Ajax {
         public function init() {
    -        add_action( 'wp_ajax_erp_old', [ $this, 'old' ] );
    +        add_action( 'wp_ajax_erp_new', [ $this, 'handle' ] );
    +        $id = $_POST['id'];
    +        $wpdb->get_results( "SELECT * FROM t WHERE id = $id" );
         }
     }
    diff --git a/wp-erp.php b/wp-erp.php
    index 333..444 100644
    --- a/wp-erp.php
    +++ b/wp-erp.php
    @@ -6,3 +6,2 @@
     define( 'WPERP_VERSION', '1.2.3' );
    -function erp_removed_fn( $id ) { return $id; }
    -do_action( 'erp_gone_hook' );
    """)


# -- diff + scanners ------------------------------------------------------------------
def test_parse_diff_line_numbers():
    files = scanners.parse_diff(SAMPLE_DIFF)
    ajax = files[0]
    assert ajax.path == "includes/Ajax.php"
    assert ajax.added == {
        11: "        add_action( 'wp_ajax_erp_new', [ $this, 'handle' ] );",
        12: "        $id = $_POST['id'];",
        13: '        $wpdb->get_results( "SELECT * FROM t WHERE id = $id" );',
    }
    assert len(ajax.removed) == 1


def test_security_leads():
    rules = {l.rule for l in scanners.security_leads(scanners.parse_diff(SAMPLE_DIFF))}
    assert {"ajax-handler", "raw-input", "sql-interpolation"} <= rules


def test_removed_api_only_when_gone(plugin):
    removed = scanners.removed_api(scanners.parse_diff(SAMPLE_DIFF), plugin.repo_root)
    names = {r["name"] for r in removed}
    assert names == {"erp_removed_fn", "erp_gone_hook"}  # neither exists in the fixture repo


def test_numbered_diff_respects_budget():
    files = scanners.parse_diff(SAMPLE_DIFF)
    text, omitted = scanners.render_numbered_diff(files, max_chars=400)
    assert "includes/Ajax.php" in text and omitted == ["wp-erp.php"]


# -- versions / changelog ------------------------------------------------------------
def test_versions_bump_and_changelog(plugin):
    assert {h.value for h in wp.read_versions(plugin)} == {"1.2.3"}
    assert wp.next_version("1.2.3", "minor") == "1.3.0"
    changed = wp.bump_versions(plugin, "1.2.4")
    assert changed == ["package.json", "readme.txt", "wp-erp.php"]
    assert {h.value for h in wp.read_versions(plugin)} == {"1.2.4"}
    plugin.github_repo = "wp-erp/wp-erp"
    import datetime as dt
    block = wp.render_changelog(plugin, "1.2.4", [
        {"type": "Fixed", "text": "Leave approval failed", "pr": 9},
        {"type": "New", "text": "Bulk approve leave requests."},
    ], dt.date(2026, 10, 5))
    assert block.splitlines()[0] == "= v1.2.4 → Oct 5, 2026"
    assert block.splitlines()[2] == "* [New] Bulk approve leave requests."
    assert "[#9](https://github.com/wp-erp/wp-erp/pull/9)" in block
    wp.insert_changelog(plugin, block)
    text = plugin.path("readme.txt").read_text()
    assert text.index("v1.2.4") < text.index("v1.2.3 →")


def test_last_tag_and_shipped_files(plugin):
    assert wp.last_release_tag(plugin) == "v1.2.3"
    (plugin.repo_root / ".env").write_text("DB_PASSWORD=x")
    git(plugin.repo_root, "add", ".env")
    assert (".env", "environment file") in wp.suspicious_shipped(plugin)


def test_deprecation_parsing_and_usage(plugin):
    src = textwrap.dedent("""\
        function get_page_by_title( $page_title, $output = OBJECT ) {
        \t_deprecated_function( __FUNCTION__, '6.2.0', 'WP_Query' );
        \treturn null;
        }
        function fine_function() {
        \treturn 1;
        }
        """)
    dep = wp.parse_deprecated_functions(src)
    assert dep == {"get_page_by_title": {"since": "6.2.0", "replacement": "WP_Query"}}
    hits = wp.find_usages(plugin, list(dep))
    assert hits == {"get_page_by_title": ["wp-erp.php:9"]}  # the method definition on line 10 is ignored


# -- agent loop -------------------------------------------------------------------------
class FakeClient:
    """Scripted stand-in for anthropic.Anthropic: first explores, then submits."""

    def __init__(self, script):
        self.script = list(script)
        self.calls = []
        self.messages = self

    def create(self, **kw):
        import copy
        self.calls.append(copy.deepcopy(kw))
        blocks = self.script.pop(0)
        return SimpleNamespace(content=blocks, usage=SimpleNamespace(input_tokens=10, output_tokens=5, cache_read_input_tokens=0))


def tool_use(id_, name, inp):
    return {"type": "tool_use", "id": id_, "name": name, "input": inp}


def test_agent_loop_uses_tools_then_submits(plugin):
    client = FakeClient([
        [tool_use("t1", "read_file", {"path": "wp-erp.php", "start_line": 1, "end_line": 3})],
        [tool_use("t2", "submit_review", {"summary": "ok", "risk": "low", "findings": [], "breaking_changes": [], "qa_steps": []})],
    ])
    out = run_agent(plugin, task_name="t", system="s", user="u", submit_tool=review.SUBMIT_REVIEW,
                    tools=RepoTools(plugin.repo_root), client=client)
    assert out["risk"] == "low"
    # the tool result for read_file was fed back to the model
    second_msgs = client.calls[1]["messages"]
    assert second_msgs[-1]["content"][0]["type"] == "tool_result"
    assert "Plugin Name: ERP" in second_msgs[-1]["content"][0]["content"]
    assert (plugin.out_dir / "t-transcript.json").exists()


def test_repo_tools_refuse_escape(plugin):
    assert "outside repository" in RepoTools(plugin.repo_root).dispatch("read_file", {"path": "../../etc/passwd"})


# -- review rendering ---------------------------------------------------------------
def test_review_render_places_only_valid_lines(plugin):
    files = scanners.parse_diff(SAMPLE_DIFF)
    result = {"summary": "s", "risk": "high", "breaking_changes": ["hook erp_gone_hook removed"], "qa_steps": ["Log in as CRM agent"],
              "findings": [
                  {"path": "includes/Ajax.php", "line": 13, "severity": "high", "category": "sql", "title": "SQLi", "body": "b"},
                  {"path": "includes/Ajax.php", "line": 99, "severity": "low", "category": "bug", "title": "Off-diff", "body": "b"},
              ]}
    body, inline = review._render(plugin, result, files, [], [])
    assert [c["line"] for c in inline] == [13]
    assert "Off-diff" in body and "erp_gone_hook" in body and "- [ ] Log in as CRM agent" in body


# -- compat aggregation -------------------------------------------------------------------
def test_compat_aggregate(plugin, tmp_path):
    rdir = tmp_path / "results"
    rdir.mkdir()
    for php in ("8.1", "8.4"):
        (rdir / f"compat-result-php{php}-wp7.1.json").write_text(json.dumps({
            "cell": f"php{php}-wp7.1", "php": php, "wp": "7.1.3",
            "steps": [{"step": "activate", "status": "ok", "detail": ""}],
            "errors": [
                {"type": "deprecated", "message": "explode(): Passing null to parameter #2", "file": "includes/functions-people.php", "line": 453, "origin": "plugin", "context": "rest GET /erp/v1/crm/contacts"},
                {"type": "warning", "message": "Undefined array key 'x'", "file": "wp-includes/x.php", "line": 1, "origin": "core", "context": "cli"},
            ] + ([{"type": "warning", "message": "Undefined variable $a", "file": "modules/hrm/x.php", "line": 5, "origin": "plugin", "context": "http /wp-admin/admin.php?page=erp-hr"}] if php == "8.4" else []),
            "rest": {"total": 200, "failed": {}, "crashed": []},
        }))
    agg = compat.aggregate(plugin, compat.load_results(rdir))
    assert agg["cells"]["php8.1-wp7.1"]["ok"] is True          # deprecations and core noise don't fail
    assert agg["cells"]["php8.4-wp7.1"]["ok"] is False         # plugin warning does
    dep = next(c for c in agg["clusters"] if c["type"] == "deprecated")
    assert dep["cells"] == ["php8.1-wp7.1", "php8.4-wp7.1"]    # same error grouped across cells
    rc = compat.analyze(plugin, str(rdir))
    assert rc == 1 and "incompatible" in (plugin.out_dir / "compat-report.md").read_text()
