---
description: Cut a release of WP ERP to wordpress.org
---

# Release WP ERP

Goal: bump version, build, tag, and let the asset workflow ship to wp.org SVN trunk.

> **Releases are user-triggered.** Confirm intent before performing any of these steps. Do NOT push to `master` or tag without explicit approval.

## Pre-flight (always)

1. `git checkout develop && git pull`
2. `composer install && composer dump-autoload -o`
3. `npm install && npm run build`
4. `composer phpcs` — must pass
5. `npm run lint` — must pass
6. Scan changelog candidates: `git log --oneline $(git describe --tags --abbrev=0)..HEAD`

## Version bump

Three places must match:

| File | Where |
|---|---|
| [wp-erp.php](../../wp-erp.php) | `Version:` header (line ~8) AND `define( 'WPERP_VERSION', '…' )` (line ~63) |
| [package.json](../../package.json) | `"version": "…"` |
| [readme.txt](../../readme.txt) | `Stable tag:` AND the `== Changelog ==` section |

## i18n

```bash
npm run makepot
```
Commit the updated `.pot` if it changed.

## Tag + push

```bash
git checkout master
git merge --no-ff develop
git tag -a v<X.Y.Z> -m "Release v<X.Y.Z>"
git push origin master --tags
```

Pushing to `master` triggers [.github/workflows/asset.yml](../../.github/workflows/asset.yml), which runs `10up/action-wordpress-plugin-asset-update` to push `.wordpress-org/` assets and `readme.txt` updates to the wp.org SVN trunk (`SLUG: erp`, `IGNORE_OTHER_FILES: true` — code itself is shipped separately).

## Post-release

- Open the [wp.org plugin page](https://wordpress.org/plugins/erp/) — verify version, screenshots, changelog
- Merge `master` back into `develop` if any hot-fixes happened during release
- `git checkout develop`

## Rollback

If something breaks in production:

1. Identify last good tag: `git tag --sort=-v:refname | head`
2. SVN trunk on wp.org has its own history — coordinate with the release manager; do NOT force-push tags.
3. Cut a patch release (`X.Y.Z+1`) with the revert rather than rewriting history.
