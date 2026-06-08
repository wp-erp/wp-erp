# WP ERP Playwright Suite — Setup Reference

Single source of truth for how the WP ERP (free **wp-erp** + **erp-pro**) Playwright
suite is provisioned and run. Mirrors the Dokan suite's architecture. Keep it
accurate — every change here should reflect a real change in the suite.

---

## 1. Where everything lives

```
wp-content/plugins/wp-erp/tests/pw/
├── .env                        # local credentials (gitignored)
├── .env.example                # canonical variable list
├── .wp-env.json                # default wp-env config — LITE (wp-erp only)
├── .wp-env.override.json        # local override — adds erp-pro (LITE + PRO)
├── .wp-env.ci.json             # CI override — adds erp-pro (LITE + PRO)
├── .wp-env.override.json.example
├── api.config.ts               # Playwright config for REST tests
├── playwright.config.ts        # Playwright config for E2E tests (project chain)
├── global-setup.ts / global-teardown.ts
├── package.json                # all npm scripts (see §5)
├── setup.md                    # THIS FILE
├── bin/                        # syncDbPort.js, createAdmin.js
├── mu-plugins/erp-qa-force-pro.php   # test-only Pro license/module enabler (§6)
├── tests/
│   ├── e2e/_localSite.setup.ts # wp-env provisioning (LITE)
│   ├── e2e/_site.setup.ts      # site readiness + SEPARATE @pro activation (§6)
│   ├── e2e/_auth.setup.ts      # role logins → storageState + nonces
│   ├── e2e/_env.setup.ts       # per-module fixtures
│   ├── e2e/<module>/*.spec.ts  # E2E specs
│   └── api/                     # REST specs
└── utils/                      # helpers, dbUtils, testData, reporters
```

Sibling plugin clone (required for `@pro`):

```
wp-content/plugins/
├── wp-erp/      # this repo (free)
└── erp-pro/     # required for @pro
```

---

## 2. Lite vs Pro is selected at the env-config layer

wp-env auto-activates every plugin listed in the effective config's `plugins` array.

| File | When used | Mounts | Result |
|------|-----------|--------|--------|
| `.wp-env.json` | always (base) | `wp-erp` + `mu-plugins` + `wp-data` | **Lite** site |
| `.wp-env.override.json` | local dev (auto-merged by wp-env) | adds `erp-pro` | **Lite + Pro** |
| `.wp-env.ci.json` | CI (copied to `.wp-env.override.json`) | adds `erp-pro` | **Lite + Pro** |

`erp-pro` is **never** in `.wp-env.json` — Pro is opt-in via the override/ci file,
exactly like Dokan keeps `dokan-pro` out of its base config.

> **`reset:env` behaviour.** `wp-env destroy && wp-env start` recreates WordPress and
> auto-activates `wp-erp` (always) and `erp-pro` (when an override is present). It does
> **NOT** enable Pro modules, set the license, create users, or seed fixtures, and it
> **wipes the DB** — so you must re-seed (`npm run docker:setup`, or `npm test`).

---

## 3. The setup chain (Playwright project dependencies)

`playwright.config.ts` wires setup as a dependency graph, so `npm test` runs the whole
chain automatically. `NO_SETUP=true` drops the dependencies (run against a seeded site).

```
local_site_setup → site_setup → auth_setup → e2e_setup → e2e_tests
   (_localSite)     (_site)      (_auth)      (_env)      (*.spec.ts)
```

| Project | Spec | Responsibility |
|---------|------|----------------|
| `local_site_setup` | `_localSite.setup.ts` | wp-env only: activate wp-erp, permalinks, timezone (LITE) |
| `site_setup` | `_site.setup.ts` | activate CRM/Accounting; **separate, explicit `@pro` Pro activation** (§6) |
| `auth_setup` | `_auth.setup.ts` | log in each role → storageState files + REST nonces |
| `e2e_setup` | `_env.setup.ts` | per-module fixtures |
| `e2e_tests` | `*.spec.ts` | the actual tests (sharded in CI) |

`npm run setup` / `npm run docker:setup` = `playwright test --project=e2e_setup`, which
runs the four setup projects (via dependencies) and **stops before the tests** — the
seed-once step.

---

## 4. Pro setup is separate and trackable (§6 detail) — quick view

In a Pro run the report shows these discrete `@pro` nodes under `site_setup`:

```
✓ activate erp-pro plugin
✓ set erp pro license
✓ activate all erp pro modules
✓ verify pro install completed
```

---

## 5. npm scripts (run from `tests/pw/`)

| Script | What it does |
|--------|--------------|
| `npm run start:env` | Boots wp-env; `poststart:env` syncs `DB_PORT` from Docker |
| `npm run stop:env` | Stops containers (keeps DB) |
| `npm run restart:env` | `stop:env` + `start:env` |
| `npm run reset:env` | Destroys + recreates the stack (**DB lost — re-seed required**) |
| `npm run db:port` | Re-sync `DB_PORT` from the running dev MySQL container |
| `npm run create:admin` | Ensures the `.env` admin user exists |
| `npm run setup` | Run the setup chain only (seed-once) — `--project=e2e_setup` |
| `npm run docker:setup` | Alias of `setup` |
| `npm run docker:full` | `start:env` + `create:admin` + `setup` (use on first boot) |
| `npm test` | Full Playwright run (setup chain + e2e) |
| `npm run test:e2e` | E2E only, `NO_SETUP=true` (seeded site) |
| `npm run test:api` | REST tests via `api.config.ts` |
| `npm run check:plugins` / `check:users` / `check:modules` | wp-cli introspection |
| `npm run test:headed` / `test:ui` / `test:debug` / `test:report` | Run modes / report |
| `npm run lint` / `lint:fix` / `format` / `format:fix` / `type:check` | Quality |

---

## 6. Lite/Pro toggle + the force-pro mu-plugin

`ERP_PRO` (env) drives the toggle:

- **Pro run** (`ERP_PRO=true`): `@pro` setup steps run; `_site.setup.ts` activates
  `erp-pro`, writes `erp_pro_license`, flips `erp_qa_force_pro=1`, and verifies the
  modules. Pro `@pro` specs run.
- **Lite run** (`ERP_PRO` unset): `@pro` steps are grep-excluded and the lite toggle
  clears `erp_qa_force_pro`, so the same site behaves as Lite.

**Why a mu-plugin (the one divergence from Dokan).** erp-pro's license is
**user-cap-gated** and periodically re-checked; this QA site seeds ~240 users (over the
cap), so a plainly-written license is rejected and Pro modules would never load.
`mu-plugins/erp-qa-force-pro.php` forges a valid, all-extensions, high-cap license at the
`option_erp_pro_license_status` **read** layer (so nothing — cron / re-check / restart —
can wipe it mid-run) and runs erp-pro's real `activate_modules()` once on `erp_loaded`.
Dokan needs no such shim because its license has no user-cap gate. The setup steps stay
thin (flip scalar options); all install logic lives in PHP, avoiding fragile multi-layer
wp-cli eval through wp-env.

**Module coverage + the skip pattern.** `activate_modules()` skips any HRM/CRM/Accounting
pro module whose **free parent** module is inactive, so the mu-plugin activates the CRM +
Accounting cores **first**; with that, **21 of 23** pro modules activate on a clean site.
The remaining 2 — `woocommerce` and `awesome_support` — need their external host plugin
(WooCommerce, Awesome Support) and stay inactive unless you add those plugins.
`_site.setup.ts` publishes the active set to **`ERP_PRO_ACTIVE_MODULES`** (in `.env`), and
`@pro` specs call **`helpers.proModuleActive('<id>')`** to `test.skip` (not fail) when
their module is inactive — the "needs external X" pattern (fail-open: if the var is unset
the test still runs).

---

## 7. Tag system (drives Lite/Pro filtering)

`playwright.config.ts`:
- `grep: [/@lite/, /@liteOnly/, /@pro/]`
- `grepInvert: ERP_PRO ? [/@liteOnly/, /@serial/] : [/@pro/, /@serial/]`

| Tag | Meaning |
|-----|---------|
| `@lite` | Runs in Lite **and** Lite+Pro environments |
| `@liteOnly` | Runs ONLY when Pro is absent |
| `@pro` | Requires erp-pro |
| `@hrm` / `@crm` / `@accounting` | Module |
| `@admin` / `@manager` / `@employee` | Role |

Every spec carries one Lite/Pro gate + one module tag + one role tag.

---

## 8. Run modes

| Mode | Command |
|------|---------|
| **First boot (Pro)** | `npm run docker:full` then `npm run test:e2e` |
| **Re-seed after reset:env** | `npm run docker:setup` |
| **Iterate on a seeded site** | `NO_SETUP=true npx playwright test --project=e2e_tests <path>` |
| **Lite only** | `ERP_PRO=false npm test` |
| **API** | `npm run test:api` |

---

## 9. CI (GitHub Actions — `.github/workflows/playwright.yml`)

Same two-phase shape as Dokan:

1. **build wp-erp** and **build erp-pro** jobs → upload tarball artifacts.
2. **e2e** job (matrix shards): download artifacts → `npm ci` → prepare `.env` +
   choose Lite/Pro by copying `.wp-env.ci.json` → `.wp-env.override.json` (sets
   `ERP_PRO=true`) → `npm run start:env` → **`npm run docker:setup`** (explicit, logged
   seed incl. the `@pro` steps) → **`npm run test:e2e -- --shard=i/N`** (NO_SETUP).
3. **api** job → `npm run test:api`.

Each shard is its own runner with its own wp-env, so each shard seeds then runs its
slice — Pro provisioning is a distinct, trackable step in every shard's log.

---

## 10. Hard preconditions

1. Docker Desktop running (`docker info` succeeds).
2. `.env` present (copy from `.env.example`).
3. Pro modes: `LICENSE_KEY` set **and** `erp-pro` cloned as a sibling under
   `wp-content/plugins/` **and** an override file present (`.wp-env.override.json`).
4. After `reset:env` (fresh DB): run `npm run docker:setup` before any test.
