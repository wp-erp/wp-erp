---
name: erp-free-release
description: Release a new version of WP ERP (free, wp.org slug `erp`) to wp.org via GitHub Actions (`.github/workflows/deploy-erp.yml`). Tag push triggers the deploy workflow — no Appsero. Trigger when user says "release wperp", "release erp free", "ship erp X.Y.Z", "publish wp-erp", "/erp-free-release".
---

# WP ERP (Free) Release Skill

Workflow-driven release for the free **WP ERP** plugin (wp.org slug `erp`). Pushing tag `vX.Y.Z` triggers `.github/workflows/deploy-erp.yml`, which builds with pnpm + composer and deploys to wp.org SVN via `10up/action-wordpress-plugin-deploy`.

**No Appsero. No git-flow. No release branches. Just bump + commit + tag + push.**

## TL;DR

```bash
# 1. Bump versions, 2. commit, 3. tag vX.Y.Z, 4. push branch + tag
# → tag push triggers deploy-erp.yml → builds zip → deploys to wp.org SVN (slug `erp`)
```

## When to Use

User wants to ship WP ERP (free). Match phrases:
- `release wperp 1.17.5`
- `ship erp free next version`
- `publish wp-erp X.Y.Z`
- `/erp-free-release`

**Do NOT use for:** ERP Pro (`erp-pro`), AppBridge, code review, or hotfixes that don't bump version.

## Repo facts (cached)

- Repo: `wp-erp/wp-erp`
- Default branch: `develop`
- **wp.org slug: `erp`** (NOT the repo name `wp-erp`!)
- Wp.org URL: https://wordpress.org/plugins/erp/
- SVN URL: https://plugins.svn.wordpress.org/erp/
- Tag format: `vX.Y.Z`
- Deploy workflow: `.github/workflows/deploy-erp.yml` (triggers on **any tag push**, `tags: ['*']`)
- Asset/readme update workflow: `.github/workflows/asset.yml` (triggers on `master` push, slug `erp`)
- Build: pnpm 9 + Node 22 + PHP 7.4 + Composer
- Main plugin file: `wp-erp.php`
- Version locations (ALL must match the tag):
  - `wp-erp.php` — `* Version: X.Y.Z` header (~line 8)
  - `wp-erp.php` — `define( 'WPERP_VERSION', 'X.Y.Z' );` (~line 64)
  - `readme.txt` — `Stable tag: X.Y.Z`
  - `package.json` — `version` field
- Build excludes: `.distignore` (excludes `/.git`, `/.github`, `/.claude`, `/.wordpress-org`, `/node_modules`, src dirs, dev config)
- wp.org assets (banners/icons/screenshots): `.wordpress-org/` — pushed via `ASSETS_DIR: .wordpress-org` in deploy step

⚠️ **Known stale state**: `wp-erp.php` `Version:` header may lag behind `WPERP_VERSION` / `readme.txt` / `package.json`. Always sync all four before tagging.

## Version-bump checklist

Bump to `X.Y.Z` in all four spots, then verify they agree:

```bash
cd <wp-erp clone>
# edit:
#   wp-erp.php   : * Version: X.Y.Z          (header)
#   wp-erp.php   : define( 'WPERP_VERSION', 'X.Y.Z' );
#   readme.txt   : Stable tag: X.Y.Z   (and  Tested up to: <WP ver>)
#   package.json : "version": "X.Y.Z"

# verify all four match:
grep -E "Version:|WPERP_VERSION" wp-erp.php
grep -E "Stable tag|Tested up to" readme.txt
grep '"version"' package.json
```

## Changelog format (matters!)

WP ERP changelog lives **only in `readme.txt`** (no separate `changelog.txt`). Insert the new block right after the `== Changelog ==` line (~line 329). Format is the arrow header + bracketed-tag style:

```
= vX.Y.Z → Mon D, YYYY
--------------------------
* [New] Description of new feature.
* [Improved] Description of an improvement.
* [Fixed] Description of a bug fix.
```

NOT the PM bold-prefix style (`**New:**`) — that's a different plugin.

### Drafting the changelog (agent flow)

1. Find last tag: `git ls-remote --tags origin | awk -F/ '{print $NF}' | grep -E '^v[0-9]' | sort -V | tail -1`
2. Inspect commits since: `git log v<last>..HEAD --no-merges --pretty='format:%h %s'`
3. Review merged PRs: `gh pr list --repo wp-erp/wp-erp --state merged --base develop --limit 30 --json number,title,labels`
4. Categorize into `[New]` / `[Improved]` / `[Fixed]`. Skip dev-only commits (chore/lint/refactor with no user-visible impact).
5. Rewrite user-centric (what the user sees, not the implementation).

## Release steps (manual — canonical until a script exists)

```bash
# 1. Fresh clone
git clone --branch develop git@github.com:wp-erp/wp-erp.git
cd wp-erp

# 2. Bump versions (4 spots — see checklist) + insert readme.txt changelog block

# 3. Commit + tag + push
git add wp-erp.php readme.txt package.json
git commit -m "chore: bump version to X.Y.Z"
git tag -a vX.Y.Z -m "release version X.Y.Z"
git push origin develop
git push origin vX.Y.Z          # ← tag push fires deploy-erp.yml

# 4. Watch workflow
gh run watch --repo wp-erp/wp-erp
```

## What deploy-erp.yml does

Triggers on **any tag push** (`tags: ['*']`):

1. Checkout tagged commit
2. `pnpm install --frozen-lockfile` + `pnpm run build`
3. `pnpm run makepot` (POT generation via `wp-vue-i18n`)
4. `composer install --no-dev --optimize-autoloader`
5. Build zip via `10up/action-wordpress-plugin-build-zip` (uses `.distignore`), `SLUG: erp`
6. Deploy via `10up/action-wordpress-plugin-deploy`, `SLUG: erp`, `ASSETS_DIR: .wordpress-org`, with SVN secrets

**Required secrets on repo:** `SVN_USERNAME`, `SVN_PASSWORD` (wp.org creds). Missing = workflow fails at deploy step (safe for fork tests).

To dry-run on a fork: set `dry-run: true` on the deploy step, or push the tag to a fork with no SVN secrets.

## Setup (one-time)

```bash
which git gh node pnpm composer
gh auth status                  # must be logged in
gh api repos/wp-erp/wp-erp/collaborators/$(gh api user --jq .login)/permission --jq .role_name
# need >= write to push branch + tag. If develop protected: get bypass from tareq1988 / nizamuddinbabu.
```

## ⚠️ Don't

- DO NOT release more than once per 24h (wp.org indexer rate-limits — bundle changes into one release).
- DO NOT push the tag with `Stable tag` ≠ tag version — wp.org rejects the deploy.
- DO NOT forget to bump ALL FOUR version spots (header drifts most often).
- DO NOT delete published tags on `wp-erp/wp-erp` (wp.org's permanent reference).
- DO NOT force-push tags.
- DO NOT ship `.claude/`, `node_modules/`, or `src/` in the zip — `.distignore` handles it; verify if you add new dev dirs.

## Troubleshooting

### "Stable tag mismatch" / "Tag already exists"
`readme.txt` `Stable tag:` ≠ pushed tag. Check: `git show vX.Y.Z:readme.txt | grep 'Stable tag'`

### Deploy step fails on auth
`SVN_USERNAME` / `SVN_PASSWORD` missing:
```bash
gh secret set SVN_USERNAME --repo wp-erp/wp-erp
gh secret set SVN_PASSWORD --repo wp-erp/wp-erp
```

### wp.org stuck on old version after success
```bash
curl -s "https://plugins.svn.wordpress.org/erp/trunk/readme.txt" | grep 'Stable tag'
curl -s "https://api.wordpress.org/plugins/info/1.0/erp.json" \
  | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('version'),d.get('last_updated'))"
```
SVN correct but API stale: wait 15–60 min. > 2h: email `plugins@wordpress.org`.

### Zip leaks dev artifacts
Add the path to `.distignore`, re-tag (or push to fork to test).

### Tag push rejected (protected branch / GH006)
Branch protection on `develop`. Get write/bypass from `tareq1988` or `nizamuddinbabu`.

## Verified releases via this flow

| Version | Date | Status |
|---------|------|--------|
| _none yet_ | — | — |
