# WP ERP — Claude Code Guide

Open-source ERP plugin for WordPress (HRM + CRM + double-entry Accounting) by weDevs. Exposes operations as MCP abilities on WP 6.9+.

- Repo type: WordPress plugin (single repo, Composer + npm hybrid)
- Plugin slug: `erp`, text domain: `erp`
- Version: see `WPERP_VERSION` in [wp-erp.php](wp-erp.php) and `package.json`

## Tech stack

| Layer | Tool / Version |
|---|---|
| PHP runtime | 7.4+ enforced (`$min_php` in [wp-erp.php:91](wp-erp.php#L91)), composer platform pinned to 7.2, phpcs `testVersion=5.6-` |
| WordPress | 4.4+; Abilities API features require 6.9+ |
| Autoload | Composer PSR-4 (`WeDevs\ERP\*` → `includes/`, modules under `WeDevs\ERP\{HRM,CRM,Accounting}\*`) |
| ORM | `tareq1988/wp-eloquent` (Eloquent on `$wpdb`) |
| Frontend | Vue **2.6** + Vuex + Vue Router (legacy), Webpack 3, Babel, Less/Sass |
| Node | v20 (see `.nvmrc`) |
| Tests | Codeception (acceptance/functional/unit suites) — see [tests/README.md](tests/README.md) |
| Lint (PHP) | WordPress Coding Standards via [phpcs.xml](phpcs.xml) |
| Lint (JS) | ESLint standard + vue/essential via [eslintrc.js](eslintrc.js) |
| Format (PHP) | `tareq1988/wp-php-cs-fixer` via [.php_cs](.php_cs) |
| CI | GitHub Actions — phpcs on PRs ([.github/workflows/phpcs.yml](.github/workflows/phpcs.yml)) |

## Critical commands

Install + build:
```bash
composer install && composer dump-autoload -o
npm install
npm run build              # production
npm run dev                # webpack watch
```

Lint / format:
```bash
composer phpcs             # WPCS scan → phpcs-report.txt
composer phpcbf            # auto-fix sniff errors
composer phpcsf            # php-cs-fixer
npm run lint               # eslint .js,.vue
npm run lint-fix
```

Tests (Codeception; requires `.env` configured per `.env.example` + Selenium for acceptance):
```bash
vendor/bin/codecept run acceptance
vendor/bin/codecept run acceptance Accounting/SomeCest.php
vendor/bin/codecept run acceptance Accounting/SomeCest.php:specificTest
```

i18n:
```bash
npm run makepot
```

WP-CLI (when plugin active inside a WP install):
```bash
wp erp module activate hrm
wp erp module activate crm,accounting
```

## Project layout

```
wp-erp.php                    Plugin bootstrap, defines WPERP_* constants
includes/                     Core: Framework, Admin, API, Lib, Settings, Updates, cli, email
  API/                        REST controllers extending REST_Controller
  Lib/                        Vendored libs (bgprocess, google, parsecsv, Emogrifier)
  cli/commands.php            WP-CLI commands
modules/
  hrm/                        HR module — namespace WeDevs\ERP\HRM\*
    includes/functions-abilities.php   MCP abilities (WP 6.9+)
    includes/Models/                   Eloquent models
    views/                             Server-rendered PHP views
  crm/                        CRM module — namespace WeDevs\ERP\CRM\*
  accounting/                 Double-entry accounting — namespace WeDevs\ERP\Accounting\*
assets/                       Built frontend (do not edit by hand)
build/                        Webpack output (gitignored)
i18n/languages/               .pot / .mo (gitignored except .pot)
tests/                        Codeception suites: acceptance, functional, unit (dir empty)
docs/mcp-abilities.md         Authoritative MCP ability reference
.wordpress-org/               wp.org SVN assets (banner, icon, screenshots)
```

Top-level files of note: [readme.txt](readme.txt) (wp.org), [Gruntfile.js](Gruntfile.js) (legacy build helpers), [webpack.config.js](webpack.config.js), [phpcs.xml](phpcs.xml).

## Conventions

- **Always** start PHP files with `if ( ! defined( 'ABSPATH' ) ) { exit; }`.
- **Namespaces** PSR-4 under `WeDevs\ERP\*`. New module code goes under the matching module namespace.
- **Functions** snake_case, prefixed by domain: `erp_`, `erp_hr_`, `erp_crm_`, `erp_ac_`. No new top-level globals.
- **Text domain** is `erp`. Every translatable string must pass `'erp'` as the second arg (`__()`, `_e()`, `esc_html__()`, etc.). phpcs enforces this.
- **Indentation** 4 spaces (PHP/JS/Vue), 2 spaces for JSON/YAML (`.editorconfig`).
- **Caching** writes go through `erp_cache_set_last_changed( 'hrm', 'employee' )` so reads invalidate via `erp_cache_get_last_changed`. See pattern in [modules/hrm/includes/functions-employee.php](modules/hrm/includes/functions-employee.php).
- **DB tables** prefixed `{$wpdb->prefix}erp_*`. Wrap raw table names with `esc_sql()` (see recent commit `699663a6`).
- **Dates** use `wp_date()` not `date()` (see commit `a2e24c73`).
- **Capabilities** are the auth boundary. Every REST handler and ability uses `current_user_can( 'erp_...' )`. Reuse existing caps before inventing new ones; the canonical list is in [docs/mcp-abilities.md](docs/mcp-abilities.md).
- **Eloquent vs raw `$wpdb`**: prefer the existing pattern in the surrounding file. Models live in `modules/*/includes/Models/`.
- **REST controllers** extend `WeDevs\ERP\API\REST_Controller`; namespace `erp/v1` registered in [includes/API/ApiRegistrar.php](includes/API/ApiRegistrar.php).
- **MCP abilities** wrap all `wp_register_ability*` calls in `function_exists()` guards so WP < 6.9 stays safe.
- **Coding-standard typos**: certain WP ERP capability names contain intentional typos (`erp_crate_announcement`, `erp_crm_manage_activites`). Do NOT "fix" them — see commit `a2e24c73`. Match the existing string.

## Do not touch

- `vendor/`, `node_modules/`, `build/`, `assets/` (built output)
- `composer.lock`, `package-lock.json` — only update by running the install commands
- `includes/Lib/google/apiclient-services/` — vendored path repo (composer linked)
- `i18n/languages/*.mo` — generated
- Files in `.wordpress-org/` — only release manager updates these
- `wp-erp.php` version constant and `package.json` version — only bump on release
- Intentional capability-name typos noted above

## How to run a single test

```bash
# whole suite
vendor/bin/codecept run acceptance

# a single Cest file
vendor/bin/codecept run acceptance HR/EmployeeCreateCest.php

# a single test method
vendor/bin/codecept run acceptance HR/EmployeeCreateCest.php:createValidEmployee
```

Suites: `acceptance`, `functional`, `unit` (`unit.suite.yml` exists; the `tests/unit/` dir is empty — add Cest files there). Requires `.env` per `.env.example` and a running WP test site; acceptance needs Selenium + chromedriver.

## Detailed rules

See `.claude/rules/` for per-topic depth:

- [code-style.md](.claude/rules/code-style.md) — naming, formatting, file scaffolding
- [wordpress-security.md](.claude/rules/wordpress-security.md) — nonces, caps, escaping, sanitization
- [database.md](.claude/rules/database.md) — `$wpdb`, Eloquent, caching, table naming
- [rest-api.md](.claude/rules/rest-api.md) — controller pattern, permission callbacks, schemas
- [mcp-abilities.md](.claude/rules/mcp-abilities.md) — registering / extending abilities for MCP
- [testing.md](.claude/rules/testing.md) — Codeception layout, writing a Cest
