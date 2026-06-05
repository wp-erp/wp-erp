# WP ERP — Playwright Test Suite (`tests/pw`)

End-to-end (UI), REST, and database tests for the **WP ERP** plugin family — free
`wp-erp` plus the commercial `erp-pro` — built in the mature **Dokan `tests/pw`
style**: feature-isolated page objects, a linear project-chain setup that seeds
fixtures via REST/DB and stashes IDs back into `.env`, and tag-driven lite/pro
selection. It exercises three layers in concert — Playwright UI flows, WordPress
REST endpoints, and direct MySQL assertions — across the **HRM**, **CRM**,
**Accounting**, and **Core** modules.

<p>
  <img alt="Playwright" src="https://img.shields.io/badge/tested%20with-Playwright-2EAD33?logo=playwright&logoColor=white">
  <img alt="TypeScript" src="https://img.shields.io/badge/TypeScript-5.x-3178C6?logo=typescript&logoColor=white">
  <img alt="Node" src="https://img.shields.io/badge/Node-%3E%3D18-339933?logo=node.js&logoColor=white">
  <img alt="License" src="https://img.shields.io/badge/license-GPL--2.0--or--later-blue">
</p>

## Features

- **Dual-suite architecture** — UI E2E (`playwright.config.ts`, `tests/e2e`) and
  REST (`api.config.ts`, `tests/api`) share one setup chain and one `utils/` layer.
- **Tag-driven lite/pro selection** — `@lite` / `@liteOnly` / `@pro` tiers filtered
  by the `ERP_PRO` env var via `grep`/`grepInvert`, Dokan-style — no separate config.
- **Module + role tag taxonomy** — `@hrm` / `@crm` / `@accounting` / `@core` and
  `@admin` / `@manager` / `@employee` for targeted runs.
- **Linear project-chain seeding** — `local_site_setup → site_setup → auth_setup →
  e2e_setup` seeds fixtures (REST for HRM/Accounting, DB for CRM) and writes IDs
  back into `.env`.
- **Dual environment provider** — zero-config `@wordpress/env` (Docker) site
  (`WP_ENV=true`), or any existing WordPress / Valet install (`WP_ENV=false`).
- **Three assertion layers** — Playwright UI, REST (cookie + `X-WP-Nonce` via
  `ApiUtils`), and direct MySQL checks (`dbUtils` / `mysql2`).
- **Role-based auth fixtures** — per-role `storageState` (admin, HR/CRM/Acct
  managers, employee) generated once and reused.
- **Faker + Zod data layer** and custom reporters (summary JSON + spec-duration).

## Requirements

- **Node.js ≥ 18** and **npm ≥ 9**
- **Docker** running — required by `@wordpress/env` for the default `WP_ENV=true`
  mode (not needed for `WP_ENV=false` external-site mode)
- For **`@pro`** runs: the **`erp-pro`** plugin checked out as a sibling of `wp-erp`
  (mounted via `.wp-env.override.json` — see below) plus a valid license key

## Quick start (wp-env / Docker)

```bash
cd wp-content/plugins/wp-erp/tests/pw

cp .env.example .env          # defaults target wp-env
npm install
npm run install:chromium      # downloads the Chromium browser binary
npm run start:env             # boots wp-env (Docker) + auto-syncs DB_PORT into .env
npm run create:admin          # idempotent — ensures the admin user exists

npm test                      # LITE run — full e2e chain (setup + tests)
npm run test:api              # LITE run — REST suite
```

> The base `.wp-env.json` is **free-only** (mounts only `wp-erp`), so the quick start
> works out of the box with just the free plugin. Pro is opt-in via an override (below).

### Pro (`@pro`) runs

Pro testing mounts the commercial `erp-pro` plugin via a git-ignored override, keeping
the base config free-only.

```bash
# 1) check out erp-pro as a sibling of wp-erp (it resolves to ../../../erp-pro)
# 2) enable the pro mount:
cp .wp-env.override.json.example .wp-env.override.json
npm run start:env             # restart so wp-env picks up the override (+ re-syncs DB_PORT)
# 3) in .env set:
#      ERP_PRO=true
#      LICENSE_KEY=<your real key>           # .env only — never .env.example
#      ERP_PRO_EMAIL=<license email>
ERP_PRO=true npm test         # runs @lite + @pro
ERP_PRO=true npm run test:api
```

When `ERP_PRO=true`, the `@pro` site-setup step turns on the full pro surface: it
sets the `erp_qa_force_pro` option, and the test-only must-use plugin
`mu-plugins/erp-qa-force-pro.php` then forces a valid license and runs erp-pro's
real `activate_modules()` so every pro module installs its tables and capabilities.
With `ERP_PRO` unset, the flag is cleared and the same site behaves as lite. The QA
test site is intentionally oversized (far more users than any license cap), which is
why the force-pro mu-plugin is needed; it is **test-only** and never ships with the
plugin.

### Alternative: an existing site (Valet, no Docker)

```bash
cp .env.example .env
# uncomment & set the override block in .env: WP_ENV=false, BASE_URL, SERVER_URL,
# WP_ROOT, ADMIN/ADMIN_PASSWORD, and DB_* for your local database. A local `wp`
# (wp-cli) binary must be on PATH.
npm install && npm run install:chromium
npm test
```

## Running tests

| Command | What it does |
|---|---|
| `npm test` | Full **e2e** suite (setup chain → `tests/e2e`) |
| `npm run test:api` | **REST** suite (`api.config.ts` → `tests/api`) |
| `npm run test:e2e` | `e2e_tests` only with `NO_SETUP=true` — re-run against an already-seeded site |
| `npm run test:headed` | Run with a visible browser |
| `npm run test:ui` | Playwright interactive UI mode |
| `npm run test:debug` | Playwright Inspector / step debugger |
| `npm run test:report` | Open the last HTML report |
| `ERP_PRO=true npm test` | Include `@pro` specs (see above) |
| `npm test -- --grep @hrm` | Filter to a module (`@hrm`/`@crm`/`@accounting`/`@core`) |
| `npm test -- --grep @manager` | Filter to a role (`@admin`/`@manager`/`@employee`) |
| `npm test -- tests/e2e/hrm/hrm.spec.ts` | Run a single spec file |
| `SLOWMO=300 HEADLESS=false npm test` | Watch a flow (slow-mo, non-headless) |
| `npm run stop:env` / `npm run reset:env` | Stop / destroy + recreate the wp-env site |

> wp-env auto-assigns the MySQL host port on each (re)create. `start:env` and
> `reset:env` automatically run `npm run db:port`, which writes the live port into
> `.env` — so `DB_PORT` is always correct without manual syncing.

## Tags & test selection

Every test carries three tags — one **tier**, one **module**, one **role**:

- **tier** — `@lite` (runs always), `@liteOnly` (lite-only), `@pro` (pro runs only)
- **module** — `@hrm` / `@crm` / `@accounting` / `@core`
- **role** — `@admin` / `@manager` / `@employee`
- **opt-in** — `@serial` (excluded from the default parallel runs)

The config's `grep`/`grepInvert` plus `ERP_PRO` decide which tier runs: a lite run
drops `@pro`; a pro run keeps `@lite` + `@pro`. Combine with `--grep` to slice by
module or role.

## The setup chain

`local_site_setup → site_setup → auth_setup → e2e_setup → e2e_tests` (the REST suite
uses a trimmed `site_setup → auth_setup → api_tests`).

- **local_site_setup** — wp-env only: activate plugins, pretty permalinks, timezone.
- **site_setup** — site-readiness + module activation; the `@pro` step force-activates pro.
- **auth_setup** — admin login → `storageState` + capture `X-WP-Nonce`; create the
  role users; log each role in once → `playwright/.auth/<role>StorageState.json`, and
  capture each manager's own REST nonce.
- **e2e_setup** — per-module `seed()` creates fixtures and writes IDs
  (`EMPLOYEE_ID`, `CUSTOMER_ID`, …) back into `.env`.

`NO_SETUP=true` skips the chain so you can re-run specs against an already-seeded site.

## Project structure

```
tests/pw/
├── playwright.config.ts        # e2e suite — project chain + tag grep
├── api.config.ts               # REST suite (testDir tests/api)
├── global-setup.ts             # truncate wp-data/debug.log before a run
├── global-teardown.ts          # write playwright/systemInfo.json
├── .wp-env.json                # wp-env (Docker) provider — FREE-ONLY base (wp-erp + mu-plugins)
├── .wp-env.override.json.example  # copy to .wp-env.override.json to add erp-pro (pro runs)
├── .env.example                # copy to .env (wp-env defaults + existing-site block)
├── mu-plugins/
│   └── erp-qa-force-pro.php     # TEST-ONLY: force-activate pro for @pro runs
├── bin/createAdmin.js          # `npm run create:admin`
├── utils/                      # test.ts, helpers.ts, apiUtils.ts, apiEndPoints.ts,
│                               # dbUtils.ts, dbData.ts, testData.ts, payloads.ts,
│                               # schemas.ts, interfaces.ts, pwMatchers.ts, reporters
└── tests/
    ├── e2e/                    # _localSite/_site/_auth/_env setup + <module>/<feature>{Page,spec}.ts
    └── api/                    # <module>/<feature>.api.spec.ts
```

## Configuration (`.env`)

Copy `.env.example` → `.env` and adjust. Key groups:

- **Admin & role users** — `ADMIN`/`ADMIN_PASSWORD`, the per-role usernames, `USER_PASSWORD`.
- **Lite/pro** — `ERP_PRO`, `LICENSE_KEY`, `ERP_PRO_EMAIL` (real values in `.env` only).
- **URLs** — `BASE_URL`, `SERVER_URL`.
- **Database** — `DB_HOST_NAME`, `DB_USER_NAME`, `DB_USER_PASSWORD`, `DATABASE`,
  `DB_PORT` (auto-synced by `npm run db:port`), `DB_PREFIX`.
- **Provider** — `WP_ENV` (`true` = wp-env, `false` = existing site), `WP_ROOT`.
- **Auto-seeded IDs** — `EMPLOYEE_ID`, `CUSTOMER_ID`, … (leave blank; the setup chain fills them).

## Test layers

- **UI (`tests/e2e`)** — thin specs drive feature-isolated page objects. Several
  pro/accounting/CRM screens are Vue SPAs, so their UI specs are smoke-level (mount
  + no fatal + key controls); behavioral depth lives in the REST and DB specs.
- **REST (`tests/api`)** — `ApiUtils.fromStorageState(...)` with cookie + `X-WP-Nonce`;
  full CRUD, edge, negative, and access-control coverage.
- **DB (`utils/dbUtils.ts`)** — `mysql2` assertions for table-backed features.
  Note: `mysql2` returns `DATETIME` columns as JS `Date` objects — don't string-match them.

## Quality gates

```bash
npm run type:check     # tsc --noEmit
npm run lint           # eslint .          (npm run lint:fix to autofix)
npm run format         # prettier --check  (npm run format:fix to write)
```

## Reports & artifacts

- `playwright-report/` — HTML report (`npm run test:report`)
- `summary-report/results.json` — machine-readable summary
- `test-results/` — traces, screenshots, video on failure
- `wp-data/debug.log` — PHP debug log (truncated per run; grep it for `PHP Fatal`)

All of the above are git-ignored.

## Troubleshooting

- **`wp-env` won't boot / port in use** — make sure Docker is running and the web
  ports `9999` / `9989` are free; `npm run reset:env` recreates the site.
- **DB connection errors** — run `npm run db:port` to re-sync `DB_PORT` to the live
  wp-env MySQL port (it changes whenever containers are recreated).
- **`@pro` specs fail / pro features missing** — ensure `../../../erp-pro` exists, you
  copied `.wp-env.override.json.example` → `.wp-env.override.json` and restarted
  (`npm run start:env`), and `ERP_PRO=true` + a valid `LICENSE_KEY`/`ERP_PRO_EMAIL` are
  set in `.env`.
- **Stale seeded IDs / nonce or auth expiry** — delete the seeded IDs in `.env` and the
  `playwright/.auth/` state and re-run the full chain (`npm test`, not `test:e2e`).

## CI notes

The configs auto-tune for CI (`CI=true`): `retries=2`, `workers=1`, the `blob`
reporter, and `forbidOnly`. This suite is not yet wired into the plugin's GitHub
Actions (assets/deploy/phpcs only) — add a workflow that runs
`npm ci && npm run start:env && npm test` to gate PRs.

## Safety & license

- **`.env` is git-ignored** — never commit real credentials or a real `LICENSE_KEY`.
- **Never run the suite against a production site** — it creates and mutates data.
- Licensed **GPL-2.0-or-later** (see [`LICENSE`](./LICENSE)), matching WP ERP.
  Contributions are welcome — see [`CONTRIBUTING.md`](./CONTRIBUTING.md).
