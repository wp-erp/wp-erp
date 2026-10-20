# Release & QA agent

An AI agent for WP ERP's release pipeline. It runs in GitHub Actions and does four jobs:

| Job | Trigger | What it produces |
|---|---|---|
| **PR review** | every non-draft PR | One review with inline comments: security, authorization, SQL, escaping, breaking hooks, PHP 8 issues, a QA checklist and a suggested changelog line |
| **Compatibility test** | weekly, release PRs, or the `run-compat` label | Installs WordPress (min / latest / beta) on PHP 7.4–8.4, activates ERP, loads every ERP admin page, calls every `erp/v1` GET route as admin, deactivates, and records every PHP error. Claude groups errors into root causes and proposes fixes. |
| **Release prep** | manual (Actions → AI release prep) | A `release/vX.Y.Z` PR with the changelog drafted from merged PRs, all four version strings bumped, and pre-flight checks. **It never tags.** |
| **Upstream watch** | weekly | One issue, updated in place: new WordPress/WooCommerce versions, "Tested up to" lag, deprecated core/WC functions ERP still calls, and dev notes that touch ERP code |

## How it works

Each job follows the same pattern:

1. **Deterministic checks first.** Diff parsing, regex security leads, phpcs, removed hooks and functions, version strings, deprecation lists, and the compat matrix all run without AI.
2. **Claude investigates.** The model gets those results as unverified leads plus three read-only tools: `read_file`, `search_code` and `list_dir`. It reads the code, confirms or dismisses each lead, and finishes by calling a `submit_*` tool. The result is always structured JSON.
3. **Results are posted** as a PR review, PR comment, issue or release PR, and saved to `agent-output/` as an artifact along with the full transcript and token usage.

Without `ANTHROPIC_API_KEY`, every job still runs in deterministic-only mode, so you can try it before adding a key.

```
release_agent/
  llm.py          agent loop (Claude + tools + forced submit on the last round)
  repo_tools.py   read-only, path-confined code tools
  scanners.py     diff parser, security leads, removed-API detection, phpcs
  wp.py           readme/version/changelog, wp.org + WooCommerce APIs, deprecations
  github.py       REST client; dry-run logs writes instead of sending
  jobs/           review.py  compat.py  release.py  upstream.py
compat/
  run-cell.sh         one PHP x WP cell (MySQL in CI, SQLite locally)
  compat-capture.php  mu-plugin that records every notice/warning/deprecation/fatal
  smoke-rest.php      REST sweep that survives fatals (resumes after the crashing route)
  collect.py          folds a cell's output into compat-result-*.json
release-agent.toml    all plugin-specific settings
```

## Setup

1. **Add the secret** `ANTHROPIC_API_KEY` under repo Settings → Secrets and variables → Actions.
2. **Create labels** `upstream-watch`, `compat-report` and `run-compat` (Issues → Labels).
3. *(Optional)* **Add `RELEASE_AGENT_TOKEN`**, a fine-grained PAT or GitHub App token with contents and pull-requests write access. A PR opened with the default `GITHUB_TOKEN` doesn't trigger other workflows, so without this token CI won't run on the release PR automatically.
4. **Run each workflow once** from the Actions tab with "Run workflow" to check the output.

The model is set in `release-agent.toml` (`[llm] model`). It defaults to `claude-sonnet-5-5`.

## Releasing with it

1. Run **AI compatibility test** and make sure it's green on the latest WordPress.
2. Run **AI release prep**. Leave the version empty to let the agent choose patch or minor from the changes, and tick *update_tested_up_to* if step 1 passed.
3. Review the release PR. Edit the changelog wording in the PR if needed, work through the QA checklist, then merge.
4. Tag as before: `git tag -a vX.Y.Z -m "release version X.Y.Z" && git push origin vX.Y.Z`. This runs the existing `deploy-erp.yml`.

## Running locally

```bash
cd .github/release-agent
pip install -r requirements.txt
export ANTHROPIC_API_KEY=...            # optional; omit for deterministic-only mode

# review your branch against develop (prints to agent-output/review.md, posts nothing)
python -m release_agent review --repo-root ../.. --base origin/develop --dry-run

# one compat cell without MySQL (needs php, wp-cli, composer install already done)
WP_VERSION=7.1.3 PLUGIN_DIR=$PWD/../.. PLUGIN_SLUG=erp OUT_DIR=/tmp/compat DB_ENGINE=sqlite bash compat/run-cell.sh
python -m release_agent compat-analyze --repo-root ../.. --results /tmp/compat --dry-run

python -m release_agent upstream --repo-root ../.. --dry-run
python -m release_agent release-prep --repo-root /path/to/a/throwaway/clone --dry-run   # edits files!

python -m pytest -q tests
```

SQLite mode is for quick local checks only. SQL dialect differences cause some false errors (for example "no such column" in leave entitlements), so trust the MySQL cells in CI.

## Safety

- **Reviews never block merges.** `event_on_high = "COMMENT"`. Change it to `REQUEST_CHANGES` once the team trusts the findings.
- **The reviewer can't be changed by the PR it reviews.** It runs from the base branch's copy of this folder. Fork PRs are skipped because they get no secrets.
- **Tools are read-only and confined to the repo.** All prompts tell the model that PR text, code and upstream content are data, not instructions.
- **No releases from AI.** Release prep stops at a PR, and tagging and deploying stay human steps. The PR opens as a draft if a blocking check fails.
- **Costs are bounded.** There's a hard cap on tool rounds (`max_tool_rounds`), on diff size (`max_diff_chars`), and on inline comments. Token usage is printed in every run and saved in the transcript.

## Using it for another plugin

Copy `.github/release-agent/` and the four `ai-*.yml` workflows, then edit `release-agent.toml`: slug, main file, `version_locations`, changelog header format, REST namespace and admin page prefix. Also change `PLUGIN_SLUG`, `REST_NS` and `PAGE_PREFIX` in `ai-compat.yml`. For Dokan, set `watch_woocommerce = true` and add the WooCommerce plugin to the compat cell.
