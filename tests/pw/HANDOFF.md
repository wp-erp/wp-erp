# WP ERP Playwright suite — HANDOFF

**Last updated:** 2026-08-19
**Branch:** `test/pw-suite-rebuild` (repo: `wp-content/plugins/wp-erp`)
**Suite root:** `wp-content/plugins/wp-erp/tests/pw`

Written so this work can be resumed in a fresh context with nothing carried over in conversation.
Read this, then `test-cases/COVERAGE.md` (the honest ledger), then `harness/report/INDEX.md`.

---

## Current state — verified, not remembered

Last full run: **225 passed, 3 skipped, 0 failed** in `e2e_tests`, twice consecutively (the setup
projects add ~18 more — `crmAgent2` joined the auth chain).
`npx tsc --noEmit` clean, `npx eslint .` clean.

| Area | Tests | Green | Skipped |
|---|---|---|---|
| core — license, modules, tools, company | 29 | 29 | 3 |
| hrm-people — employees, departments, designations | 26 | 26 | — |
| hrm-leave — holidays | 9 | 9 | — |
| hrm-leave — policies | 10 | 10 | — |
| hrm-leave — entitlements | 7 | 7 | — |
| hrm-leave — requests | 9 | 9 | — |
| hrm-leave — calendar | 6 | 6 | — |
| hrm-payroll — screens, pay calendar, authz | 13 | 13 | — |
| hrm-attendance — screens, shifts, authz | 10 | 10 | — |
| hrm-assets — screens, categories, assets, authz | 8 | 8 | — |
| hrm-recruitment — screens, stages, opening wizard, authz | 17 | 17 | — |
| hrm-documents — screen, upload, authz, security | 4 | 4 | — |
| hrm-training — list, CPT create, authz | 4 | 4 | — |
| hrm-reports — nine reports, seed reconciliation | 17 | 17 | — |
| crm-contacts — list, create, REST guard, authz | 5 | 5 | — |
| crm-companies — list, create, validation, authz | 5 | 5 | — |
| crm-activities — feed, note, empty state, authz | 4 | 4 | — |
| crm-tasks — list, create guard, authz | 3 | 3 | — |
| crm-deals — board, dashboard, activities, create, stages, authz | 20 | 20 | — |
| crm-deals — single deal: notes, competitors, stage move, won/reopen/trash, authz | 10 | 10 | — |
| crm-deals — agent-vs-agent access control (6 positive controls + 2 guards) | 8 | 8 | — |
| harness — date helpers (no browser; guards the timezone bug in trap 41) | 4 | 4 | — |
| hrm — admin router (status probes, ERP-148 guard) | 4 | 4 | — |
| accounting — 29 screens, chart classes, reports, authz | 33 | 33 | — |
| accounting — invoice create, line maths, double entry, customer ledger, validation | 8 | 8 | — |
| accounting — invoice settlement: full, partial, receipt application, validation | 6 | 6 | — |
| accounting — bills: create, double entry, pay in full, unfunded refusal, validation | 7 | 7 | — |
| accounting — reports: trial balance, income statement, balance sheet, cross-report agreement | 8 | 8 | — |
| pro — integrations: settings list, 8 configure screens, CRM subset | 12 | 12 | 8 gated |

The 3 skips are deliberate and recorded in `COVERAGE.md`: licence cases that need the site to be
UNlicensed (the activation form is not rendered while licensed).

## How to run

```bash
cd wp-content/plugins/wp-erp/tests/pw
npx wp-env start                 # docker: 8888 dev / 8889 mysql, 8890/8891 tests
npm run setup                    # site_setup -> auth_setup -> env_setup (licence, WooCommerce, seed)
npx playwright test --project=e2e_tests                              # the main suite
npx playwright test --project=accounting_money --workers=1 --no-deps # the money specs, SERIAL
NO_SETUP=true npx playwright test --project=e2e_tests <file>        # one spec, skip the setup chain
```

**Two e2e projects, and both must be named to run the whole suite.** `accounting_money` holds
`transactions|payments|bills|reports`; `e2e_tests` `testIgnore`s them.

⚠️ **`--workers=1` on the command line is REQUIRED, and is not expressible in the config.** `workers`
is a top-level Playwright option only — it is **not** per-project. `fullyParallel: false` serialises
tests *within* a file, not files against each other. A `workers: 1` inside the project block was
silently ignored for several runs while I read the resulting failures as product faults and then as
scoping faults; the diagnosis only landed when the same four Cash-dependent tests kept failing after
every other explanation had been fixed. **Run the money project with the flag, or its files race.**

It also runs **after `e2e_tests`** (`dependencies: ['e2e_tests']`). Two separate reasons, both
measured:

1. Those files post to one shared **Cash ledger** and one voucher sequence — global state no
   customer or vendor scoping can isolate. `reports.spec.ts` goes further: it aggregates EVERY
   transaction on the site, so its cleanup is deliberately **unscoped**, which is safe only here. Run beside each other, one file's funding broke another's
   "the account is empty" precondition and one file's cleanup emptied the account another was about
   to pay from.
2. Single-worker alone was **not enough**: Playwright runs projects concurrently, so one accounting
   worker still competed with four e2e workers for one Docker site. Measured: **21/21 green running
   the project alone, and 3–4 failures every time it ran alongside the rest.** The dependency is what
   makes it deterministic. It costs wall-clock — the right trade for a ledger oracle.

`NO_SETUP=true` is the fast loop, but auth `storageState` goes stale — a stale state silently
redirects to `wp-login.php?reauth=1`, which makes an authorization test look like an empty list
rather than a refusal. When authz tests behave oddly, re-run `--project=auth_setup` first.

## Integration credentials — how the gated specs turn on

`tests/e2e/pro/integrations.spec.ts` covers everything reachable WITHOUT a third-party account
(12 green), and carries one **gated** connect case per integration that skips until its variables
exist. Add them to `.env` and the case runs — no spec edit needed.

| Integration | Variables |
|---|---|
| Dropbox | `DROPBOX_ACCESS_TOKEN` |
| Zendesk | `ZENDESK_SUBDOMAIN`, `ZENDESK_EMAIL`, `ZENDESK_API_TOKEN` |
| Push Notification | `ONESIGNAL_APP_ID`, `ONESIGNAL_API_KEY` |
| Help Scout | `HELPSCOUT_APP_ID`, `HELPSCOUT_APP_SECRET` |
| HubSpot | `HUBSPOT_ACCESS_TOKEN` |
| Mailchimp | `MAILCHIMP_API_KEY` |
| Salesforce | `SALESFORCE_CLIENT_ID`, `SALESFORCE_CLIENT_SECRET` |
| SMS | `SMS_GATEWAY_KEY` |

**The variable names are ours, not the product's** — they are what the spec reads, and can be renamed
freely as long as the spec's `integrations` table is updated with them.

⚠️ **A gated case FAILS LOUDLY if its credentials are present but the connect flow has not been
captured yet.** That is deliberate: field names, the connect control and the success signal differ per
integration and must be read off a live sandbox, not guessed. When credentials arrive, run the file
and finish each case against the real screen.

## Environment variables the suite needs

`.env` is gitignored (correctly — it holds the licence key). **`.env.example` is ALSO gitignored**, by
the plugin repo's own root `.gitignore:36`, so there is no tracked template to copy. Until that
changes, this list is the template:

`BASE_URL`, `ADMIN`, `ADMIN_PASSWORD`, `USER_PASSWORD`, `HR_MANAGER`, `CRM_MANAGER`, `CRM_AGENT`,
**`CRM_AGENT_2`**, `ACCOUNT_MANAGER`, `RECRUITER`, `EMPLOYEE`, `DB_*`, `ERP_LICENSE_EMAIL`,
`ERP_LICENSE_KEY`.

`CRM_AGENT_2` was added 2026-08-20 for the agent-vs-agent access-control pass — one agent cannot
answer whether another agent's data is reachable. `_auth.setup.ts` is data-driven off
`utils/authStates.ts`, so adding the actor there is all that is needed; the value on this machine is
`erp_crm_agent2`.

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
11. **A leave request is THREE rows**: the header (`wp_erp_hr_leave_requests`), one
    `wp_erp_hr_leave_request_details` row per day, and a `wp_erp_hr_leave_approval_status` row. The
    overlap guard reads the DETAILS table, so deleting only the header leaves rows that refuse every
    later request over those dates with "Existing Leave Record found within selected range!" —
    delivered as an AJAX error, which the JS turns into a permanently disabled `#submit`.
12. **The requests list defaults to Pending** (`status=2`). An approved or rejected request is not
    missing, it moved — assert on `goto('all')`.
13. **Reject requires a reason** (`#erp-hr-leave-reject-reason`), approve does not. Submitting it
    empty leaves the modal open and the request Pending, with no notice and no dialog.
14. **Payroll is erp-pro, has NO REST API, and its forms carry no id or name.** 67
    `wp_ajax_erp_payroll_*` actions and Vue `v-model` fields — `PayrollPage` anchors on label text.
    Three rules that shape any payroll test: only ONE pay calendar may exist per TYPE; the employee
    picker only offers staff whose own pay type matches the calendar (all seeded staff are monthly,
    so the form cannot build a weekly/biweekly one); and creating a calendar needs at least one
    employee plus a sweetalert confirm, with NO request sent and no message if either is missing.
15. **Attendance shifts are keyed by NAME *and* by TIME RANGE** — two shifts may not share a
    start/end pair even under different names, so each test owns its own range and the spec cleans
    in `beforeAll` as well as `afterAll`. Also: navigating to a hash the SPA is already on is a
    no-op, so `openShiftForm()` forces a document load before clicking "Add New Shift".
16. **Assets sub-section slugs are irregular and one is MISSPELLED**: `asset`,
    `asset-allottment` (two t's), `asset-request`. One asset is TWO rows — a group (`parent = 0`)
    plus one child per item code — and its modal shows BOTH "Save Asset" and "Save Category"
    buttons at once, so `BasePage.submitModal()` always presses Save Asset. Asset refusals arrive
    by **sweetalert**, not a notice: the handler answers HTTP 200 with a bare `die()` string.
17. **Recruitment job openings are a WordPress CPT** (`erp_hr_recruitment`), so the oracle is
    `wp_posts`. The opening wizard's "Next" is enabled by a jQuery **keyup** handler on the title —
    `fill()` leaves it disabled forever, use `pressSequentially()`. And the Job Opening list INNER
    JOINs postmeta on `_expire_date`, written only by a LATER step, so an opening abandoned at step
    one is published yet invisible. **The four AI screens are backed by a Gemini API key: LOAD them,
    never trigger a generation** — the test mu-plugin blocks only wordpress.org, so the call would
    really go out.
18. **`test.fail()` reports a PASS on ANY failure** — including a broken precondition. A
    known-defect case must assert its own setup first, and needs a plain sibling test as the canary.
    Also: WordPress **deduplicates** uploaded filenames (`name-1.txt`), so a fixed fixture name is
    only assertable on the first run — upload a unique name from memory instead.
19. **Training is a WordPress CPT** (`erp_hr_training`) in the CLASSIC editor, no tables of its own.
    Its subject field's **id is `traning-subject` (misspelled)** while the name is `training_subject`
    — anchor on NAME. Opening `post-new.php` writes an `auto-draft` row every time, so cleanup must
    sweep those too or they pile up run after run.
20. **The site has 25 employees, not 24** — 24 seeded demo staff plus the suite's own `erp_employee`
    actor from `_auth.setup.ts`. The headcount report omits the actor (no department/hire date) while
    the leaves report includes it. Never assert a flat total; assert the seeded names are present.
21. **`erp_peoples` cleanup must be SCOPED BY PEOPLE TYPE.** Contacts, companies, customers, vendors
    and employees share that one table, and four workers run at once — an unscoped delete from one
    spec removed another spec's in-flight row and turned an unrelated file red. `cleanupCrmContacts()`
    cleans `contact`, `cleanupCrmCompanies()` cleans `company`, `cleanupEmployees()` cleans
    `employee`. Unscoped is for `cleanupAll()` only. **Second time a shared table has caused this** —
    the first was leave cleanup.
22. **ANY spec cleaning a SHARED table must scope to its own marker.** Third occurrence: leave
    requests, then `erp_peoples`, now `erp_crm_customer_activities` (the tasks spec wiped the
    activities spec's in-flight note, failing ~1 run in 3). Scope by marker or by type; unscoped
    cleanup belongs only in `cleanupAll()`.
23. **The CRM note editor is TRIX** (a contenteditable custom element, not a textarea/iframe) — type
    into it, `fill()` does nothing. Its save control is an **`<input type=submit value="Save Note">`**,
    so `:has-text()` never matches it; select on the value. Beware dumps that enumerate hidden
    elements — they report such controls as present when they are not usable.
24. **`seedData.crmCompanies` does NOT create CRM companies** — it feeds `seedAccountingPeople()` to
    create accounting CUSTOMERS. A freshly seeded site has ZERO people of type `company`.
25. **An ERP person is TWO rows** — the module row (`erp_hr_employees`) AND a shared `erp_peoples`
    row with its type relation. `erp_peoples` is the same table CRM contacts, customers and vendors
    live in, so deleting only the module row inflates the CRM data set every run (58 orphans had
    accumulated). `cleanupEmployees()` now calls `cleanupPeople()`.
26. **erp-pro#952's visibility is a RACE, so it cannot be asserted either way** — hidden 5/5 when its
    spec runs alone, VISIBLE under a four-worker run (slower load ⇒ `window.load` lands after ready).
    The suite asserts the deterministic half instead: every licensed Pro extension is RENDERED.
27. **Two spec files must not share a seeded employee.** Four workers run in parallel, so files
    overlap in time. Ownership: entitlements 3-4, requests 4-12, calendar 15-17 + 20-21, payroll
    none (it uses departments).
28. **Leave cleanup must be SCOPED to the employees a spec touched.** Four workers run in parallel,
    so an unscoped marker-wide delete in one file's `afterAll` removes rows another file is
    mid-approve on — and the product answers that with an uncaught fatal, not an error (ERP-140).
    `cleanupLeaveRequests(names)` / `cleanupEntitlements(names)` take the scope; pass it.
29. **Modules screen**: the Pro block is hidden by a `$(window).on('load')` handler bound too late
    (defect ERP-137). `ModulesPage.revealProExtensions()` works around it for other tests.
30. **Deals ships its OWN modal and its OWN success channel.** `.erp-deal-modal` is unrelated to the
    shared `#erp-modal` shell, so no `BasePage` modal helper applies — `DealsPage.dealModal` is named
    apart from `BasePage.modal` on purpose. Both success and refusal arrive by **sweetalert**, never a
    notice, and THREE sweetalert containers sit in the DOM at once (new deal / schedule activity /
    mark as lost), so every locator on `.sweet-alert` or `.erp-deal-modal-content` needs `.first()`.
31. **Deals registers no REST routes at all** — 41 `wp_ajax_erp_deals_*` actions and nothing else.
    `DealsPage.callAjax()` posts with the page's own nonce, which is the only way to test a handler's
    permission check rather than the menu's. **Twelve handlers carry no capability check at all**
    (`save_deal`, `delete_deal`, the note/attachment/agent/participant/competitor/email ones); what
    protects them is that the nonce is localized only on `erp_crm_add_contact`-gated screens. Assert
    that — denied AND handed no nonce — not the absence of a check.
32. **Deals fields have no id and no name** (payroll again), the pipeline-stage bullets carry their
    title in `tooltip-title` with no text content, the contact picker needs **3+ characters** plus a
    real AJAX round-trip (`pressSequentially()`, not `fill()`), and the dashboard funnel's own column
    header reuses `.stage-name` so its first cell is the literal word "Stage".
33. **Two deals reports look like one funnel and are not.** "Number of Qualified Leads" is
    `deals_by_pipeline_stages()` — deals in each stage NOW, and it is correct. "Deal Progress" is
    `deals_progress_by_stages()` — deals that have REACHED each stage, read from
    `erp_crm_deals_stage_history`, and it is the one ERP-144 corrupts. Screenshotting the wrong one
    nearly produced a filed claim the data did not support.
34. **There is no untitled deal.** Picking a counterparty auto-fills the title as `<contact> deal` —
    and overwrites one the user already typed (ERP-145). Select the contact BEFORE filling the title,
    or the suite's own marker is destroyed and `cleanupDeals()` cannot find the row.
35. **The single-deal page's ids are Vue `_uid` counters** (`activity-form-22`, `erp-deal-note-24`,
    `competitor-form-16`) — stable across reloads when measured, but a render-order artifact rather
    than a contract. Anchor on classes. And its **stage bar's `tooltip-title` is a composed history
    string** ("Lead In 0 days ($500.00)"), NOT the stage name it is in the Add New Deal modal — so
    stages are addressed there by index.
36. **A dump taken with `innerText` lies about CSS-transformed labels.** The deal timeline tabs render
    as `ALL / ACTIVITIES / NOTES …` but the DOM text is `All / Activities / Notes` — `text-transform`
    is applied to the render, and `textContent` (what every Playwright text API reads) sees the
    original. Capture label constants with `textContent`, never `innerText`.
37. **`erp_crm_deals_notes` stores the body in `note`, not `content`** — and deals notes are **Trix**,
    the third Trix editor in this product after the CRM contact feed. Type into it; `fill()` does
    nothing.
38. **`cleanupDeals()` defaults to the `pwerp` marker, which matches EVERY suite deal.** Three deal
    specs run in parallel, so each passes its own: `pwerp_deal` (deals.spec), `pwerp_sd`
    (singleDeal.spec) and `pwerp_vis` (dealVisibility.spec). Fourth occurrence of this class — caught
    by reading this time, not by red.
39. **Deals' permission model lives in an Eloquent SCOPE, not in the handlers.**
    `Deal::scopeReadable()` (`Models/Deal.php:35`) restricts a non-admin/non-manager to deals they own
    or are a listed agent on, and everything routed through `get_deal()` inherits it. Reading the AJAX
    layer alone shows no capability checks and suggests four holes; three of those four are enforced
    one layer down. **Assert the behaviour before believing the absence of a check.** The one genuine
    hole is `save_deal` (ERP-146).
40. **Several deals handlers read `$_GET`, not `$_POST`** — `get_single_deal_data`,
    `get_deals_by_pipeline`, `get_overview_data`, `search_people`. POSTing to them sends no arguments
    and the handler answers its "invalid" branch, **which is indistinguishable from a permission
    refusal**. Use `DealsPage.callAjaxGet()` for those, or manufacture a false access-denied finding.
41. **`toDate()` must never use `toISOString()`.** Every date the suite builds comes from LOCAL
    components (`setDate()`, `getDay()`), and `toISOString()` converts to UTC before slicing — so on
    UTC+6 any date generated between 00:00 and 06:00 local came out a day early, turning
    `upcomingMonday()` into a Sunday and two leave specs red with "expected 3, received 2" working
    days. **The product was right; the harness was wrong, and only at night.** Fixed at the root in
    `helpers.toDate()`; guarded by `tests/e2e/core/dateHelpers.spec.ts`. Note CI runners are UTC, so
    CI would have stayed green while a local evening run went red.
42. **Accounting is a HASH-ROUTER SPA** — `admin.php?page=erp-accounting#/users/customers`, not
    `section=`. A hash-only change does NOT reload, so `AccountingPage.gotoRoute()` sets
    `location.hash` and dispatches `hashchange`. Take the route list from the app's own nav anchors;
    `router/index.js` nests 124 path fragments and hand-assembling them is guesswork.
43. **Accounting's `Save` is `class="btn-fake"` and a HIDDEN `Save as Draft` shares that class** — a
    loose `button:has-text("Save")` resolves to the hidden one and times out. Match visible buttons on
    an exact `/^Save$/`.
44. **An invoice's double entry SPANS TWO TABLES** — the receivable debit in
    `erp_acct_invoice_account_details`, the income credit in `erp_acct_ledger_details`. Summing either
    alone looks unbalanced and reads like a defect. And the customer ledger row goes to
    `erp_acct_people_trn_**details**`, NOT `erp_acct_people_trn` — both tables exist and only the
    `_details` one is written on create.
45. **mysql2 hydrates DATE columns into JS `Date` objects.** `String(row.trn_date)` is
    `"Thu Aug 20 2026 00:00:00 GMT+0600 (…)"` and never equals an ISO string — use `helpers.dbDate()`.
46. **`hasNoPhpFatal()` cannot see a fatal inside an AJAX/REST response** — it only reads rendered
    body text. That blind spot hid ERP-147 through 29 occurrences of green payroll runs. Screen-level
    smoke cases should call `BasePage.watchServerErrors()` and assert `serverErrorList()` is empty;
    that one assertion found ERP-147, ERP-148 and ERP-150.
47. **When a form silently does nothing, find its validator before blaming the submit.** The invoice
    form sent no request and showed no error under my selectors; reading `validateForm()` in
    `InvoiceCreate.vue` is what turned "the save button is broken" into ERP-149. The error panel
    renders above the fold with no `.error` class.
48. **Accounting cleanup must be per TEST, not per file, and must sweep ORPHANS.** The payment screen
    lists every outstanding invoice for a customer and pre-fills each with its full balance, so a
    balance left by an earlier case is silently settled by the next — three cases read as ledger
    defects until each started from a customer owing nothing. And `cleanupInvoices()`/`cleanupPayments()`
    derive ids FROM the parent tables, so a parent removed without its children leaves
    `people_trn_details` rows they can never find again: six orphans made a fresh 1,800 invoice read
    as **10,800** outstanding. `cleanupLedgerOrphans()` exists for that.
49. **EVERY accounting child table keys on `voucher_no`, never on the parent's primary key** —
    `invoice_details.trn_no`, `invoice_account_details.invoice_no`/`trn_no`, `ledger_details.trn_no`,
    `people_trn_details.voucher_no`, `invoice_receipts_details.invoice_no`. Measured: invoice `id`
    102 → `voucher_no` 134, children all 134. Voucher numbers come from a sequence shared across
    every transaction type, so id and voucher_no are **equal on a young site and drift apart later** —
    every `id`-keyed query works at first and silently stops matching. It caused three separate
    failures here: a query that found no line items, a cleanup that left orphans, and an orphan sweep
    that deleted LIVE rows.
50. **Accounting specs scope by CUSTOMER.** Accounting rows carry no title to mark, so
    `transactions.spec.ts` owns Verdant Foods and `payments.spec.ts` owns Harbourline Logistics; every
    query and cleanup is scoped by `customer_id`. Sixth occurrence of the cross-file cleanup
    collision — same principle as marker scoping, different key.
51. **The due-invoice list is a race.** Choosing a customer on the payment screen fires
    `GET /invoices/due/{id}` and the rows paint only when it returns; a fixed sleep saw zero rows and
    the payment then covered nothing. Wait for the rows.
52. **A test that has cried wolf is not thereby always wrong.** Three cases in `payments.spec.ts`
    failed on my own state leakage; the fourth failure looked identical and was ERP-151, a Critical
    money bug. Fix the isolation, re-run, and if it still fails, READ THE SOURCE.
53. **A bill's grand total recomputes on KEYUP only** (`BillCreate.vue:73`
    `@keyup="updateFinalAmount"`). `fill()` updates the line and leaves the total at 0, so the form
    refuses with "Total amount can't be zero" beside a line that visibly shows the amount. Type it.
    Same family as the CRM search box and the recruitment wizard.
54. **Paying a bill needs FUNDS, and this site seeds none.** The product refuses with "Not enough
    balance in selected account" when the paying account is empty. The bills spec earns the cash —
    invoice a funding customer, collect into Cash, then pay — which makes the happy path a real
    money-in-then-out cycle instead of a mocked balance.
55. **A bill line with an account but no amount is refused by the BROWSER, not the product.** Picking
    an account sets `:required` on the amount, so no request is sent and the Vue error panel never
    renders. Assert the browser's refusal, not the product's.
56. **`AccountingPage.gotoRoute()` must force a RELOAD when re-entering the route it is already on.**
    A hash that does not change fires no navigation, Vue reuses the component instance, and the screen
    keeps the previous test's half-filled form with stale account lists. It looked like a broken
    picker on the last test of a file.
57. **`settle()` waits for a heading with TEXT, not for a spinner and a pause.** A full reload outruns
    a fixed delay, so form interactions began on a half-rendered screen and the save failed validation
    silently — the assertion then read as "the record was never created". Two files, two records, one
    cause.
58. **Ordering matters when probing access control.** A probe that ran `save_deal` before
    `delete_deal` made the delete look unguarded; it is guarded, but the earlier write had already
    made the caller the owner. Attempt the guarded action BOTH before and after the suspected
    escalation, or the chain reads as two independent holes.

## Bugs filed (all on `wp-erp/erp-pro`, sub-issues of #844, screenshots embedded)

| ID | Issue | Severity | Summary |
|---|---|---|---|
| ERP-135 | [#950](https://github.com/wp-erp/erp-pro/issues/950) | Critical | CRM Contacts REST returns one raw row and dies — stray `wp_send_json` at `ContactsController.php:378` |
| ERP-136 | [#951](https://github.com/wp-erp/erp-pro/issues/951) | Major | `POST erp/v1/hrm/leaves/policies` answers 201 but writes nothing (`leave_id` never mapped) |
| ERP-137 | [#952](https://github.com/wp-erp/erp-pro/issues/952) | Major | Modules screen can show zero Pro extensions |
| ERP-138 | [#953](https://github.com/wp-erp/erp-pro/issues/953) | Major | Leave Calendar department filter **widens** instead of narrowing — `absint('-1')` is 1, and an empty match set adds no WHERE at all |
| ERP-139 | [#954](https://github.com/wp-erp/erp-pro/issues/954) | Major | `prepare(" … in (%s)", implode(…))` collapses the id list — only a department's first employee is matched |
| ERP-140 | [#955](https://github.com/wp-erp/erp-pro/issues/955) | Major | Approving a vanished leave request fatals — `toArray()` runs before the `empty()` guard |
| ERP-141 | [#956](https://github.com/wp-erp/erp-pro/issues/956) | **Critical** | Payroll `payrun` table never created on MariaDB (`to_date` is reserved, `dbDelta` reports success) — pay runs unrecorded and basic pay compounds every run |
| ERP-142 | [#957](https://github.com/wp-erp/erp-pro/issues/957) | High | HR documents served from public, unauthenticated URLs — the sharing model governs listing only, not the file |
| ERP-143 | [#958](https://github.com/wp-erp/erp-pro/issues/958) | Major | CRM Tasks/Schedules cannot be created — the composer's submit never enables (`trix-change` bound to the wrong editor) |
| ERP-144 | [#959](https://github.com/wp-erp/erp-pro/issues/959) | Major | Default deal pipeline seeded out of order (`Proposal Made` gets `order = 0`) — the board, settings, modal and Deal Progress all open at stage four, and every deal records having REACHED Proposal Made, so the funnel reports 2 deals there with 0 at the stage before it |
| ERP-145 | [#960](https://github.com/wp-erp/erp-pro/issues/960) | Minor | Add New Deal overwrites a title the user already typed — the contact watcher assigns unconditionally |
| ERP-147 | [#964](https://github.com/wp-erp/erp-pro/issues/964) | Major | Payroll Overview chart 500s where PHP lacks the optional `calendar` extension — `cal_days_in_month()` unguarded at `AjaxHandler.php:138`; the history panel stays blank |
| ERP-148 | [#965](https://github.com/wp-erp/erp-pro/issues/965) | Minor | HR router fatals on redirect-only submenus (`'callback' => ''`) — `?section=payroll|attendance&sub-section=settings` is 500; the CRM router handles the same shape |
| ERP-149 | [#966](https://github.com/wp-erp/erp-pro/issues/966) | Major | **Typed dates are ignored on every Accounting form** — `Datepicker.vue:86` emits only when the field is emptied; save says the date is required while showing it. 34 usages, 21 screens |
| ERP-151 | [#968](https://github.com/wp-erp/erp-pro/issues/968) | **CRITICAL / P1** | **A payment covering several invoices credits only the LAST line to the customer ledger** — `$total = 0` inside the line loop (`rec-payments.php:170`). Pay 1,800 + 3,600 in full: receipt 5,400, both invoices Paid, customer credited **3,600**, still owing 1,800 forever |
| ERP-150 | [#967](https://github.com/wp-erp/erp-pro/issues/967) | **Major / P1** | Invoice create answers 500 and sends **no invoice email** — `erp-pdf-invoice` calls `get_magic_quotes_runtime()`, removed in PHP 8. The invoice IS created, so the screen looks fine |
| ERP-146 | [#963](https://github.com/wp-erp/erp-pro/issues/963) | **Major / P1** | A CRM agent can take over another agent's deal — `save_deal` is the one path that skips `Deal::scopeReadable()`, and it reassigns `owner_id` to the caller, so the write transfers the deal and unlocks the trash/read/note paths that DO check |

**Filed 2026-07-21 against 1.6.0, re-verified on 1.7.0, and POSTED 2026-08-19** (the two that were re-tested):

| ID | Severity | Still reproduces? | Summary |
|---|---|---|---|
| ERP-043 → [#961](https://github.com/wp-erp/erp-pro/issues/961) | Major | **Yes** — 4 open rows on a deal at Demo Scheduled, expected 1 | Stage history keeps an open (`out IS NULL`) row for every stage at or below the deal's, not just the current one — `Deals.php:611` loads stages unscoped |
| ERP-044 → [#962](https://github.com/wp-erp/erp-pro/issues/962) | Major | **Yes** — HTTP 500, 3/3 | `erp_deals_delete_competitor` fatals for any CRM agent: a Collection is passed where a model is expected. Also fatals for a non-existent competitor id, because `empty()` is false for an empty Collection — so the "Invalid competitor" guard is dead code |
| ERP-045 | Major | **Not re-tested** — needs a second pipeline; still unposted | Stage/pipeline delete transfer accepts a `transfer_to_stage_id` from a different pipeline |
| ERP-066 | Minor | **Not re-tested** — needs a media fixture; still unposted | `add_attachment()` persists a row for a non-existent WP media id |

Bug files: `~/.claude/skills/wp-erp-qa/bugs/2026-08-18/` and `2026-08-19/`. Register: `bugs/REGISTER.md`, **next ID `ERP-152`**. Filing needs the user's explicit go-ahead per issue; identity gate is `gh auth status`
= `shohan0120`. Screenshots are attached by loading the PNG onto the macOS clipboard
(`osascript … as «class PNGf»`) and sending a real Cmd+V into the GitHub comment box — `gh` cannot
upload images, and the React editor exposes no file input. Two things make this reliable, both
learned the hard way:
- Take the click coordinate from an actual **screenshot**, not from `getBoundingClientRect()` — the
  page settles after `scrollIntoView()` and the computed point is stale by ~45px, so the click lands
  above the box and the paste goes nowhere.
- Confirm `document.activeElement` is the textarea, and reload the clipboard, IMMEDIATELY before the
  paste. Then verify the asset URL appears in the textarea, and after embedding verify the image
  RENDERS on the live page (`naturalWidth > 200`), not merely that `gh issue edit` exited 0.

## Resolved (2026-08-19) — the disabled-submit was OURS, not the product's

The old "some week ranges disable `#submit`" question is closed. It was two harness defects; nothing
was filed, because nothing was a product defect. Full write-up in `COVERAGE.md`. Short version:

1. `cleanupLeaveRequests()` deleted the request HEADER only, leaving `_leave_request_details` rows.
   The product's overlap guard reads the details table, so those orphans refused every later request
   over the same dates — and the earlier "no overlapping row" check looked at
   `wp_erp_hr_leave_requests`, which was empty precisely because the header had been deleted. The
   date pattern was just wherever a previous failed run had left rows.
2. Approve/reject then failed for two more reasons: the list defaults to Pending so an acted-on
   request leaves the view, and the reject modal has a required reason field.

The lesson is the same one D4 taught: **when an oracle disagrees with the UI, check the oracle is
reading the table the product reads.**

## RESUME HERE — the payment EDIT path (`erp_acct_update_payment`)

Accounting has **62 cases**: all 29 screens, invoice → payment, bill → pay bill, and the reports.
The next pass is the payment EDIT path, and the reading below was already done — start from it rather
than re-deriving.

**Why it is the top item:** it is completely untested, and it carries the same shape as ERP-151.

**What was read in `modules/accounting/includes/functions/rec-payments.php:297` (`erp_acct_update_payment`):**

1. **The same `$total = 0` INSIDE the line-item `foreach`** (`:330`), so after the loop
   `$payment_data['amount']` holds only the LAST line's total — identical to the ERP-151 shape at
   `:171`. Here it is passed to `erp_acct_update_payment_line_items()` per line, so the impact needs
   measuring rather than assuming; it is NOT obviously the same bug.
2. **The receipt header is updated BEFORE the loop**, using the amount from
   `erp_acct_get_formatted_payment_data()` — so the header total looks correct even if the lines are
   not. Check header vs lines vs ledger separately.
3. ⚠️ **The update path never writes `erp_acct_people_trn_details` at all** — `grep -c people_trn`
   over the whole function returns **0**. The customer ledger is what ERP-151 proved the balance is
   computed from, so an edited payment may leave the customer's balance showing the OLD amount.
   **This is the highest-value hypothesis to test first:** create a payment for X, edit it to Y,
   then check `SUM(debit) - SUM(credit)` for that customer.
4. ⚠️ **`erp_acct_update_data_into_people_trn_details()` (`transactions.php:1764`) only DELETES.** Its
   whole body is one `$wpdb->delete()` — it never re-inserts. Any caller relying on it to "update"
   the ledger silently removes the row instead. Find its callers before writing the case.

**How to reach the edit UI:** the router has `/payments` but no obvious edit route in the path dump —
open a receipt from **Transactions → Sales** (the `Receive` rows) and look for its edit control, or
drive `erp_acct_update_payment` through the REST route. Capture the real route before writing the
spec.

**Where the spec goes:** `tests/e2e/accounting/payments.spec.ts` (it owns Harbourline Logistics and
Meridian Office Supplies), and therefore the `accounting_money` project — **which must be run with
`--workers=1 --no-deps`**, see "How to run". A new file would need its own party per the one-party-per-file rule.

## Next steps, in order

1. **HRM is authored end to end.** Remaining gaps inside it are listed per-section in
   `COVERAGE.md` ("not covered, and why") — chiefly the payroll pay RUN (blocked by erp-pro#956),
   attendance check-in/out logging, the asset allotment/return chain, the recruitment wizard beyond
   step one, and document folder/share operations. The asset allotment/return chain is also where
   the two unguarded asset return actions should be probed for privilege escalation (see COVERAGE).
2. Payroll pay RUN — **blocked by ERP-141 / erp-pro#956** on this MariaDB environment; the schema is
   broken, so no pay-run assertions were written. Revisit once it is fixed, or run that pass against
   MySQL. Pay items/categories and payroll settings are still open regardless.
3. **CRM** — Contacts, Companies, Activities, Tasks and Deals (board, single-deal AND agent access
   control) done. Remaining: contact groups/subscribers, CRM reports, schedules (expect a `test.fail()`
   guard — ERP-143/#958), and the same agent-vs-agent question on the FREE side, which uses
   `contact_owner` rather than the deals model.
4. **Accounting — 62 cases**: all 29 screens, both money cycles end to end, and the reports asserted
   against controlled figures. What is left is in RESUME HERE above; the payment EDIT path is the top
   item and the likeliest place for a sibling to ERP-151.
5. **Integrations — half done.** Everything reachable without a third-party account is green (12
   cases); the connect flows are written and gated on credentials (8 skips). See the credential table
   above.
6. **Untouched entirely:** the Pro non-HRM modules — inventory, payment gateway, WooCommerce.
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
