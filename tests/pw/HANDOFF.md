# WP ERP Playwright suite — HANDOFF

**Last updated:** 2026-08-19
**Branch:** `test/pw-suite-rebuild` (repo: `wp-content/plugins/wp-erp`)
**Suite root:** `wp-content/plugins/wp-erp/tests/pw`

Written so this work can be resumed in a fresh context with nothing carried over in conversation.
Read this, then `test-cases/COVERAGE.md` (the honest ledger), then `harness/report/INDEX.md`.

---

## Current state — verified, not remembered

Last full run: **107 passed, 6 skipped, 0 failed**. `npx tsc --noEmit` clean, `npx eslint .` clean.

| Area | Tests | Green | Skipped |
|---|---|---|---|
| core — license, modules, tools, company | 29 | 29 | 3 |
| hrm-people — employees, departments, designations | 26 | 26 | — |
| hrm-leave — holidays | 9 | 9 | — |
| hrm-leave — policies | 10 | 10 | — |
| hrm-leave — entitlements | 7 | 7 | — |
| hrm-leave — requests | 9 | 6 | 3 |

The 6 skips are all deliberate and recorded in `COVERAGE.md`: 3 licence cases that need the site to be
UNlicensed (the activation form is not rendered while licensed), and 3 leave-request cases blocked on
an unexplained disabled-submit (see "Open question" below).

## How to run

```bash
cd wp-content/plugins/wp-erp/tests/pw
npx wp-env start                 # docker: 8888 dev / 8889 mysql, 8890/8891 tests
npm run setup                    # site_setup -> auth_setup -> env_setup (licence, WooCommerce, seed)
npx playwright test --project=e2e_tests                      # everything
NO_SETUP=true npx playwright test --project=e2e_tests <file> # one spec, skip the setup chain
```

`NO_SETUP=true` is the fast loop, but auth `storageState` goes stale — a stale state silently
redirects to `wp-login.php?reauth=1`, which makes an authorization test look like an empty list
rather than a refusal. When authz tests behave oddly, re-run `--project=auth_setup` first.

## Environment facts

- wp-erp **1.17.8** + erp-pro **1.7.0**, WP **7.0.4**, PHP **8.2**, WooCommerce **11.0.1**
- Licence: activated for real on `localhost:8888` (`site_count: 1`, `activations_left: 0`, 100 seats,
  tier `scale`, 23 extensions). Key lives in the gitignored `.env`. **One seat total** — a local run
  and a CI run must not overlap; CI needs its own key.
- WooCommerce Site Visibility is **Live** (`woocommerce_coming_soon = 'no'`), asserted in setup.
- Seeded demo company: **Northwind Analytics Ltd.** — 24 employees, 8 departments, 20 designations,
  6 leave policies, 6 holidays, 8 CRM contacts, 6 customers, 4 vendors, 8 storefront products.
- The suite cleans up after itself (`utils/cleanup.ts`, called from each HRM spec's `afterAll`).
  Suite-created people are matched by the `@example.test` e-mail domain; seeded demo staff are all
  `@northwind-analytics.test` and are never touched.

## Structure

- `pages/basePage.ts` — shared: admin navigation, ERP modal, dialog capture, `isAccessDenied()`,
  `rendersInjectedScript()`, `fillDate()`.
- `pages/core/*.ts`, `pages/hrm/*.ts` — one page object per spec.
- `tests/e2e/core/*.spec.ts`, `tests/e2e/hrm/*.spec.ts` — **tests only**. No raw locators, no
  `browser.newContext`, no `getByRole`, no `textContent` in a spec. Verify with:
  `grep -nE "page\.locator|browser\.newContext|getByRole|textContent" tests/e2e/**/*.spec.ts`
- `utils/roles.ts` — `withRole(browser, 'employee', fn)` for authorization cases.
- `harness/` — the captured site inventory; `harness/report/INDEX.md` is the readable index.
- `bin/harness-report.mjs`, `bin/test-cases.mjs`, `bin/flows.mjs` — regenerate the harness report and
  the 1,669 tiered test cases.

## Product traps already paid for — do not rediscover these

1. **Sub-section slugs are singular**: `sub-section=department`, not `departments`. Tools' audit tab
   is `log`, not `audit-log`. An unknown slug renders an EMPTY screen rather than 404ing, so a wrong
   slug looks like a broken feature.
2. **The modal shell is a decoy**: `#erp-modal .erp-modal` computes `display:none` while the form is
   interactive, and the shell ships an empty `.activate > button.button-primary` that never becomes
   visible. The real form is `.erp-modal-form`; the real submit is the *labelled* visible
   `button.button-primary`. Handled in `BasePage`.
3. **List tables** put a `<th class="check-column">` first, so the name is the first `<td>`. Each
   screen's "Add New" has its own id (`#erp-new-dept`, `#erp-new-designation`, `#erp-employee-new`,
   `#erp-hr-new-holiday`, `#erp-leave-policy-new`). A text match on "Add New" grabs the hidden nav's
   "Add New Training".
4. **Lists paginate at 20** and hide rows with CSS rather than removing them. Count with `:visible`,
   and use WP's own "N items" counter plus a cross-page search as the oracle.
5. **Search is a jQuery `keyup` handler** — `fill()` does not trigger it, `pressSequentially()` does.
6. **Person names reject digits/`_`** (`erp_is_valid_name()`, `wp-erp/includes/functions.php:3756`).
   Use `helpers.uniqueName()` (letters only), not `uniqueId()`.
7. **Validation arrives as JS `alert()`**, which Playwright auto-dismisses. `BasePage.captureDialogs()`
   records the text so a spec can assert the product's own wording.
8. **Leave counts WORKING days**, not calendar days — Thu–Sat is 2. Anchor ranges with
   `helpers.upcomingMonday()`.
9. **`wp_erp_hr_leave_entitlements` is a LEDGER**: granting writes `day_in`, approving writes a
   SECOND row with `day_out` and `description = 'Approved'`. "Available" is the difference.
10. **`wp_erp_hr_holiday.start` is a TIMESTAMP**, not a unix int. `FROM_UNIXTIME(start)` returns NULL
    and fabricates a convincing phantom bug — I filed nothing but wasted real time on it. Check the
    column type before trusting a conversion in an oracle.
11. **Modules screen**: the Pro block is hidden by a `$(window).on('load')` handler bound too late
    (defect ERP-137). `ModulesPage.revealProExtensions()` works around it for other tests.

## Bugs filed (all on `wp-erp/erp-pro`, sub-issues of #844, screenshots embedded)

| ID | Issue | Severity | Summary |
|---|---|---|---|
| ERP-135 | [#950](https://github.com/wp-erp/erp-pro/issues/950) | Critical | CRM Contacts REST returns one raw row and dies — stray `wp_send_json` at `ContactsController.php:378` |
| ERP-136 | [#951](https://github.com/wp-erp/erp-pro/issues/951) | Major | `POST erp/v1/hrm/leaves/policies` answers 201 but writes nothing (`leave_id` never mapped) |
| ERP-137 | [#952](https://github.com/wp-erp/erp-pro/issues/952) | Major | Modules screen can show zero Pro extensions |

Bug files: `~/.claude/skills/wp-erp-qa/bugs/2026-08-18/`. Register: `bugs/REGISTER.md`, **next ID
`ERP-138`**. Filing needs the user's explicit go-ahead per issue; identity gate is `gh auth status`
= `shohan0120`. Screenshots are attached by loading the PNG onto the macOS clipboard
(`osascript … as «class PNGf»`) and sending a real Cmd+V into the GitHub comment box — `gh` cannot
upload images, and the React editor exposes no file input. Reload the clipboard IMMEDIATELY before
the paste; it gets clobbered easily.

## Open question — NOT a claimed defect

The new-request form leaves `#submit` disabled for some week ranges and not others, same employee,
same 20-day balance: enables for `2026-09-07`/`09-14`/`09-21`/`10-05`, stays disabled for
`2026-09-28`, with "20 days are available" shown in every case, no overlapping row in
`wp_erp_hr_leave_requests`, and no holiday in range. Ruled out: overlaps, holidays, stale ledger
rows, and a datepicker `onSelect` theory (tried, made it worse, reverted). Three request cases carry
`test.fixme()` with this reason. Resolve it by finding the product rule (advance-notice window?
entitlement validity boundary?) before either asserting it or filing it.

## Next steps, in order

1. Resolve the disabled-submit question above and un-fixme the 3 leave-request cases.
2. HRM remaining: leave calendar, payroll, attendance, assets, recruitment, documents, training,
   reports.
3. Then CRM, then Accounting, then the Pro modules.
4. The 100/101-seat licence test is designed (6 cases in `test-cases/`) but **not implemented** — it
   belongs in the separate `license_limit` project so it never runs inside the normal suite.
5. CI: `.github/workflows/pw-suite.yml` exists but has **never been run**. Needs `ERP_PRO_TOKEN`,
   `ERP_LICENSE_EMAIL`, `ERP_LICENSE_KEY` (the second key) as repo secrets.

## Standing rules for this work

- QA reports bugs; QA never fixes product source. Test code is ours.
- No commit, push, PR, or GitHub write without the user's explicit permission for that action.
- Specs contain tests only — everything else lives in the paired page object or `utils/`.
- No fake green: a test that cannot pass is `fixme`'d with a written reason and recorded in
  `COVERAGE.md`, never deleted or quietly skipped.
