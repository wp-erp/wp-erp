# Contributing to the WP ERP Playwright Suite

Thanks for helping improve the WP ERP test suite! This guide covers the
conventions for adding and running tests under `tests/pw`.

## Prerequisites

- **Node.js ≥ 18** and **npm ≥ 9**
- **Docker** running (for the default `@wordpress/env` provider)
- For `@pro` work: the `erp-pro` plugin checked out as a sibling of `wp-erp`, plus
  `cp .wp-env.override.json.example .wp-env.override.json` to mount it (see the README)

## Local setup

```bash
cd wp-content/plugins/wp-erp/tests/pw
cp .env.example .env          # never put real secrets in .env.example
npm install
npm run install:chromium
npm run start:env             # boots wp-env (Docker)
npm test                      # lite run
```

See the [README](./README.md) for the full setup and the lite/pro details.

## Before you open a PR

Run all three gates — they must be clean:

```bash
npm run type:check            # tsc --noEmit  → 0 errors
npm run lint                  # eslint .      → 0 errors
npm run format                # prettier --check
```

`npm run lint:fix` and `npm run format:fix` will auto-correct most issues.

## Adding a test

- **Place it** under `tests/e2e/<module>/` (UI) or `tests/api/<module>/` (REST),
  where `<module>` ∈ `hrm | crm | accounting | core`.
- **Tag every test** with one tier, one module, and one role tag:
  - tier — `@lite` (runs always), `@liteOnly` (lite-only), `@pro` (pro runs only)
  - module — `@hrm` / `@crm` / `@accounting` / `@core`
  - role — `@admin` / `@manager` / `@employee`
  - optional — `@serial` (opt-in; excluded from default parallel runs)
- **Use the page-object pattern** for UI: selectors + flows live in
  `tests/e2e/<module>/<feature>Page.ts`; specs stay thin and pick a role via
  `test.use({ storageState: data.auth.<role>File })`.
- **REST**: use `ApiUtils.fromStorageState(...)` and `endPoints` / `restUrl`.
- **DB assertions**: use `dbUtils` (`@utils/dbUtils`). Note `mysql2` returns
  `DATETIME` columns as JS `Date` objects — don't string-match them.
- **Keep data unique** per run (timestamp suffix) and clean up in `afterAll`.
- **Files that mutate shared singletons/tables** must add
  `test.describe.configure({ mode: 'serial' })` (the REST config is `fullyParallel`).
- **Prefer resilient assertions** — branch on status; never assert an exact `500`
  unless documenting a known bug.

## Safety

- **Never commit `.env`** or any real credentials / license key.
- **Never run the suite against a production site** — it creates and mutates data.

By contributing you agree your work is licensed under **GPL-2.0-or-later**.
