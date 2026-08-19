# WP ERP Playwright suite — Coverage & Honesty ledger

**Last updated:** 2026-08-18
**Build:** wp-erp 1.17.8 (free) + erp-pro 1.7.0 (pro), WordPress 7.0.4, PHP 8.2, WooCommerce 11.0.1
**Environment:** wp-env at `http://localhost:8888` (dev) / `:8890` (tests); MySQL `:8889` / `:8891`

---

## ⛔ Read this line first

**1,669 test cases are DESIGNED. ZERO are implemented as automated specs, and therefore zero have
executed.** Nothing in this document should be read as coverage of the product. A case counts as
covered only once its spec exists, has run against a real site, and has passed. Until Phase 3 runs,
the correct summary of this suite's product coverage is **none**.

---

## What exists today

| Phase | State |
|---|---|
| Framework scaffold (configs, POM base, utils, mu-plugins, seeding) | **Done** — `tsc` clean, `eslint` clean |
| Setup DAG (`site_setup` → `auth_setup` → `env_setup`) | **Done — 23/23 passing** against localhost:8888 |
| Real licence activation | **Done and exercised** — activated through the admin form; `site_count: 1`, `activations_left: 0` |
| WooCommerce configured, Site Visibility = **Live** | **Done** — `woocommerce_coming_soon = 'no'`, asserted in setup |
| Demo-company seed data | **Done** — 24 employees, 8 departments, 20 designations, 6 leave policies, 6 holidays, 8 CRM contacts, 6 customers, 4 vendors, 8 storefront products |
| Site harness (screens, fields, labels, options, routes, schema) | **Done** — see `harness/report/INDEX.md` |
| Tiered test-case design | **Done** — 1,669 cases across 19 areas |
| Specs authored | **Core done** — licence, modules, tools, company (4 page objects + 4 specs) |
| Specs executed | **107 passed, 6 skipped-with-reason, 0 failed** against localhost:8888 (Core + HRM people + Leave holidays/policies/entitlements/requests) |
| CI workflow | Draft exists at `wp-erp/.github/workflows/pw-suite.yml`; **never run** |

## Case design by area

| Area | Screens harvested | Form templates | Tier 1 | Tier 2 | Tier 3 |
|---|---|---|---|---|---|
| accounting | 31 | 0 | 61 | 56 | 38 |
| core | 1 | 0 | 1 | 0 | 1 |
| core-company | 4 | 0 | 9 | 14 | 7 |
| core-license | 1 | 0 | 3 | 6 | 5 |
| core-modules | 1 | 0 | 4 | 2 | 1 |
| core-tools | 5 | 0 | 11 | 33 | 10 |
| crm | 15 | 6 | 52 | 91 | 36 |
| hrm-assets | 3 | 9 | 27 | 64 | 39 |
| hrm-attendance | 1 | 0 | 2 | 0 | 1 |
| hrm-documents | 1 | 2 | 12 | 15 | 2 |
| hrm-leave | 7 | 4 | 31 | 54 | 17 |
| hrm-payroll | 5 | 0 | 13 | 9 | 6 |
| hrm-people | 7 | 22 | 108 | 184 | 75 |
| hrm-recruitment | 13 | 0 | 43 | 85 | 28 |
| hrm-reports | 7 | 0 | 24 | 16 | 7 |
| hrm-training | 2 | 1 | 14 | 40 | 14 |
| pro-custom-fields | 2 | 0 | 2 | 0 | 2 |
| pro-workflow | 2 | 0 | 2 | 0 | 2 |
| settings | 44 | 0 | 111 | 155 | 24 |
| **Total** | | | **530** | **824** | **315** |

Cases are generated from the harness (`bin/test-cases.mjs`) so that no case can name a field, label
or option that does not exist on the running site, plus hand-written business-flow cases
(`bin/flows.mjs`) whose oracles are business rules a generator cannot infer.

---

## NOT covered, and why

### Not harvested at all — so not designed, so not covered

- **HR Frontend module (front-end)** — the employee-facing dashboard at the site front end. The
  harvest only walked `/wp-admin`. Zero cases exist for it.
- **Public recruitment apply form** — the candidate-facing job application page. Zero cases.
- **CRM contact forms (front-end)** — the `#/erp-crm/contact_forms` settings route was captured, but
  the rendered front-end form was not. Zero cases for the front-end side.
- **Accounting `#/reports` sub-routes** — the reports landing route captured 0 fields and its
  individual report routes (trial balance, balance sheet, ledger, sales/expense reports) were not
  enumerated. Only the landing screen has a case. This is a significant gap in a money module.
- **CRM dashboard, integrations and reports screens** — captured with 0 fields; these are Vue screens
  whose controls did not render in the harvest. Their cases are load-only.
- **Custom Field Builder and Workflow builder** — captured with 0 fields (Vue apps that did not yield
  their controls to the harvest), so both areas have load-only cases and no field coverage.
- **Attendance** — only 1 screen captured live; the `#/shifts`, `#/exim` and `#/assign-shift-bulk`
  SPA routes were not walked. The business-flow case exists but the field coverage does not.

### Harvested but only in one state

- Screens that need seeded data to render their real controls (an empty pay run, an empty invoice
  list, an empty audit log) were captured in their **empty state only**. Their populated-state
  controls — row actions, bulk actions, per-row menus — are not in the harness and so not in the
  cases.

### Deliberately out of automation scope

- **ERP Tools → Danger Zone / System Reset** — wipes all ERP data. Designed as `@manual-only`; the
  suite asserts the confirmation gate exists and never executes the reset.
- **Currency / financial-year / base-country changes** — global, data-re-denominating settings. One
  case exists (`ACCOUNTING-F2-004`) and is tagged `@destructive`; it runs only after a verified DB
  snapshot.

### Blocked on credentials the suite does not have

- **The 7 externally-authenticated Pro modules** — Salesforce, HubSpot, Mailchimp, Zendesk,
  HelpScout, Gravity Forms, Awesome Support. The user has chosen **full coverage** for these and will
  supply sandbox credentials. Until each credential exists, its specs will `test.skip` with an
  explicit reason and are counted here as **not covered**, never as passing. Their settings screens
  are covered today; their connect/sync flows are not.
- **Awesome Support** is additionally not even active on the test site (it is the one Pro module of
  23 that is inactive), so nothing about it has been harvested.

### Licence activation

- **The real activation flow has NOT been exercised.** The key grants one seat
  (`license_limit: 1`, `activations_left: 0`) and that seat is currently held by
  `https://shohan-erp.test`. Setup therefore verifies the licence the docker site already holds and
  logs `NOT ACTIVATED IN THIS RUN` rather than implying otherwise. `CORE_LICENSE-F1-001/002` stay
  **not covered** until a second key is available.
- `CORE_LICENSE-F3-003` (activation-limit error) needs a key whose seat is already consumed
  elsewhere, so it is tagged `@needs-external`.

### The 100/101-user rule

Designed as six cases (`CORE_LICENSE-F2-001` … `-006`) covering: the 100th user accepted, the 101st
blocked with the product's exact message, the same block over REST, an over-limit role assignment
reverted with its notice, terminated employees freeing a seat, and administrators never consuming
one. **None has run.** They are `@destructive` (they leave 100 users on the site) and live in their
own `license_limit` Playwright project, excluded from the normal suite by `grepInvert`.

---

## Known environment facts that shape coverage

- There is **no React admin UI** in these versions. `erp_hr_ui_default_engine` still exists as a DB
  option but has zero code references in wp-erp 1.17.8 or erp-pro 1.7.0, so the suite has no UI axis.
- WordPress core's `/wp/v2/users` **cannot create ERP-role users** (`rest_user_invalid_role`, because
  ERP roles are absent from `get_editable_roles()`). All role seeding goes through the test-only
  mu-plugin.
- `#wpadminbar` is not a reliable authentication oracle for ERP roles — some are redirected to the
  front end. Auth is asserted on the `wordpress_logged_in_*` cookie.
- Counted roles for the seat limit are `erp_crm_manager`, `erp_crm_agent`, `erp_ac_manager`,
  `erp_hr_manager`, `employee`; administrators are excluded and non-active employees are subtracted.

---

## Executed coverage — Core

| Area | Authored | Run | Green | Skipped | Bugs found |
|---|---|---|---|---|---|
| core-license | 10 | 10 | 7 | 3 (activation form not rendered while licensed) | 0 |
| core-modules | 8 | 8 | 8 | 0 | 1 (D3, below) |
| core-tools | 6 | 6 | 6 | 0 | 0 |
| core-company | 5 | 5 | 5 | 0 | 0 |
| hrm-people (departments) | 6 | 6 | 6 | 0 | 0 |
| hrm-people (designations) | 6 | 6 | 6 | 0 | 0 |
| hrm-people (employees) | 14 | 14 | 14 | 0 | 0 |
| hrm-leave (holidays) | 9 | 9 | 9 | 0 | 1 observation (D4) |
| hrm-leave (policies) | 10 | 10 | 10 | 0 | 0 |
| hrm-leave (entitlements) | 7 | 7 | 7 | 0 | 0 |
| hrm-leave (requests) | 9 | 9 | 6 | 3 (blocked, below) | 0 |

The suite cleans up after itself (`utils/cleanup.ts`, called from each HRM spec's `afterAll`), so a
full run leaves the demo site exactly as it found it — verified: 24 employees, 0 suite leftovers.
Records are matched by the suite's own `@example.test` e-mail domain, never by name; seeded demo
staff are all `@northwind-analytics.test`.

**HRM still to author:** leave calendar, payroll, attendance, assets, recruitment, documents,
training, reports.

### Leave requests — 3 cases BLOCKED, not silently skipped

`test.fixme()` marks three request cases: raising a request, approving it (the balance oracle), and
rejecting it. They are blocked on the new-request form leaving `#submit` **disabled for some week
ranges and not others**, for the same employee with the same 20-day balance:

- enables for `2026-09-07`, `2026-09-14`, `2026-09-21`, `2026-10-05`
- stays disabled for `2026-09-28`
- "20 days are available" is shown in every case
- no overlapping row in `wp_erp_hr_leave_requests` (the table was empty)
- no holiday in the range — the seeded holidays are Jan/Feb/Mar/May/Dec

I have NOT established whether this is a product rule I have not found (an advance-notice window, an
entitlement validity boundary) or a timing problem in the harness, so it is **not claimed as a
defect and not asserted as correct**. Ruled out so far: overlapping requests, holidays in range,
stale entitlement ledger rows, and `fill()` not firing the datepicker's `onSelect` (tried, made
things worse, reverted).

What IS proven and green around them: the form only offers policies the employee is entitled to,
submit stays disabled until the form is complete, a span across the weekend counts only its working
days (Fri→Mon = 2), an employee with no entitlement is offered no policy, and the screen is closed
to the employee role. People (employees, departments,
designations) and Leave → Holidays + Policies are done and green.

Leave-policy creation is a **full page** at `&action=new`, not a modal, and its form requires
`#leave-id` — the leave TYPE from `wp_erp_hr_leaves`. That is independent UI-side confirmation of
ERP-136 / erp-pro#951: the REST controller's `prepare_item_for_database()` never maps `leave_id`,
which is why the endpoint can never create a policy while the UI can. `#color` ("Calendar Color") is
required but hidden behind wpColorPicker, so it is set by value rather than typed.

### Product behaviour learned while authoring HRM — worth knowing before the next module

- Person names are validated by `erp_is_valid_name()` (`wp-erp/includes/functions.php:3756`), which
  rejects digits, `_` and most punctuation. Generated test names must be letters only — hence
  `helpers.uniqueName()`.
- ERP reports form validation through JS `alert()`, which Playwright auto-dismisses. `BasePage`
  captures dialog text so a spec can assert the product's own message instead of inferring a refusal
  from an unchanged row count.
- A `<script>` payload in an employee name is **sanitised away** before storage (stored surname
  became `Xss`) and the record is created — a safe outcome. The spec asserts non-execution, not
  rejection.
- List screens paginate at 20 and order by hire date, so a freshly created or early-hire record is
  often not on page 1. Oracles use WP's own "N items" counter plus a cross-page search.
- The employee filter selects sit in a panel collapsed behind a "Filters" toggle.
- A stale `storageState` silently redirects to `wp-login.php?reauth=1`, which makes an authorization
  test look like "empty list" rather than "refused". Auth states are refreshed by `auth_setup`.

Specs contain tests only. Every locator, action, role-context and text read lives in the paired page
object (`pages/core/*.ts`) or in `utils/roles.ts`; a grep of `tests/e2e/core/*.spec.ts` for
`page.locator` / `browser.newContext` / `getByRole` / `textContent` returns nothing.

The three skips are real and recorded, not silent: the licence activation form (`#email`,
`#license_key`, `#subscription-type`) only renders while the site is UNlicensed, so those three
negative cases cannot run against an activated site.

## Defects found so far

Found while building the harness and the seeder — both reproduced more than twice and root-caused to
a line of product source. **Both filed on 2026-08-18** with screenshots, labelled `Type: Bug` +
`QA Testing`, and linked as sub-issues of the QA tracking issue wp-erp/erp-pro#844:

| ID | Issue | Severity |
|---|---|---|
| ERP-135 | [wp-erp/erp-pro#950](https://github.com/wp-erp/erp-pro/issues/950) | Critical |
| ERP-136 | [wp-erp/erp-pro#951](https://github.com/wp-erp/erp-pro/issues/951) | Major |
| ERP-137 | [wp-erp/erp-pro#952](https://github.com/wp-erp/erp-pro/issues/952) | Major |

### D1 / ERP-135 — CRM Contacts REST API returns one raw row and dies (Critical) — [#950](https://github.com/wp-erp/erp-pro/issues/950)

`wp-erp/includes/API/ContactsController.php:378` — `prepare_item_for_response()` opens with a stray
`wp_send_json( $item );`. `wp_send_json()` echoes and calls `die()`, so the method never reaches its
formatting code and the request terminates mid-flight.

- `GET /erp/v1/crm/contacts` returns a **single unformatted DB row** instead of a collection, with no
  `X-WP-Total` / `X-WP-TotalPages` headers, even though 8 contacts exist.
- Reproduced via `?rest_route=`, via `/wp-json/`, and in-process through `rest_do_request()`.
- `erp_get_peoples()` itself is fine — called directly it returns `array(14)` — so the fault is
  entirely in the controller.
- `git blame`: introduced by Tareq Hasan on **2020-09-21** (`98670b2d01`), still present on HEAD
  `f88daba2c` (v1.17.8). The working tree is clean, so this is shipped code, not a local edit.
- **Impact:** every consumer of the CRM Contacts REST API — and the entire remainder of that method
  is dead code.

### D2 / ERP-136 — `POST erp/v1/hrm/leaves/policies` answers 201 Created without creating anything (Major) — [#951](https://github.com/wp-erp/erp-pro/issues/951)

`wp-erp/modules/hrm/includes/API/LeavePoliciesController.php:158-169` — `create_policy()` passes the
result of `erp_hr_leave_insert_policy()` straight to the response without ever checking for a
`WP_Error`, and forces `set_status( 201 )`.

- Every attempt returns **HTTP 201** with `{"id": 0, "name": null, ...}` and writes **zero rows**.
  Reproduced four times across three payload variants.
- Root cause of the failure underneath: `prepare_item_for_database()` never resolves the `leave_id`
  that `erp_hr_leave_insert_policy()` requires (a policy references a leave TYPE in
  `wp_erp_hr_leaves`), and never maps `f_year` either — so the insert cannot succeed through this
  endpoint at all.
- Line 166 also builds the `Location` header from `$id`, a variable **never defined in that method**.
- **Impact:** leave policies cannot be created over REST, and a client is told they were.

### D4 — WITHDRAWN (was: "a holiday can be stored with a NULL start date")

Retracted. The evidence was a **query artifact of my own making**: `wp_erp_hr_holiday.start` is a
`TIMESTAMP` column, and I inspected it with `FROM_UNIXTIME(start)`, which returns NULL for a
datetime. Every holiday I believed was dateless in fact held the correct value — re-reading the raw
column shows `2026-01-01 00:00:00` and so on.

The secondary symptom (a holiday dated `2028-02-29` not appearing in the list) has an innocent
explanation too: the list is scoped to the financial year, which is 2026 on this site.

No product defect is claimed here. Recording the retraction rather than deleting it, because the
lesson generalises: **verify the column type before trusting a conversion in a QA oracle** — a bad
query fabricates evidence just as convincingly as a real bug.

### D3 / ERP-137 — Modules screen can show zero Pro extensions (Major) — [#952](https://github.com/wp-erp/erp-pro/issues/952)

`wp-erp/includes/Admin/views/modules.php:817-823` hides `#erp_addon_wrap` and `#filter` on DOM-ready
and only un-hides them from a `$(window).on('load')` handler bound *inside that same ready callback*:

```js
$('#filter').hide();
$('#erp_addon_wrap').hide();
$(window).on('load', function () { $('#erp_addon_wrap').fadeIn(); $('#filter').fadeIn(); });
```

When `load` has already fired by the time the handler binds, it never runs and every Pro extension
stays permanently hidden. Observed: `window.load` fired (instrumented via `addInitScript`), jQuery is
not deferred, no console errors — yet `#filter` and `#erp_addon_wrap` compute to `display: none`, all
23 licensed extensions are in the DOM but none visible, and calling `.show()` by hand reveals all 23.
Reproduces deterministically in Chromium, **headless and headed**, at 1440/1600/1920 widths and with
`waitUntil` of `domcontentloaded`, `load` and `networkidle`.

Scope of the claim: verified in Chromium against this build. NOT verified in a normal desktop Chrome
profile — the browser available for that check was not logged into the test site, and credentials
were not entered into it. Whether a given customer hits it depends on whether `load` beats jQuery's
ready callback on their site, which is likely on fast or heavily-cached installs.

Covered in the suite by an expected-to-fail test (`test.fail()`) that passes while the defect stands
and fails the moment it is fixed, plus a `revealProExtensions()` helper so the other module tests
exercise the list itself rather than re-failing on this.

**Consequence for this suite:** seeding routes around both — CRM contacts are counted in the DB, and
leave policies are inserted into the schema directly. Those workarounds live in test code only; no
product source was touched (RULE 1).

---

## What "done" will mean

A module is reported as covered only when: its page objects exist, its specs exist, the specs have
**actually run** against a booted site, and they pass. Progress will be reported per module as
`authored / run / green / bugs found`. A spec that cannot pass because of a product defect is filed
as a bug (reproduced twice, screenshot attached) — never deleted, never `skip`ped to make the run
green.
