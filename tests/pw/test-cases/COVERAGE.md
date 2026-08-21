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
| Specs executed | **87 passed, 3 skipped-with-reason, 0 failed** in `e2e_tests` against localhost:8888 (Core + HRM people + Leave holidays/policies/entitlements/requests). The 3 skips are the licence cases that need an UNlicensed site. |
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
| hrm-leave (requests) | 9 | 9 | 9 | 0 | 0 |
| hrm-leave (calendar) | 6 | 6 | 6 | 0 | 2 (D5, D6 — both `test.fail()`) |
| hrm-payroll | 13 | 13 | 13 | 0 | 1 re-verified (ERP-004, 2 `test.fail()` guards) |
| hrm-attendance | 10 | 10 | 10 | 0 | 0 |
| hrm-assets | 8 | 8 | 8 | 0 | 0 |
| hrm-recruitment | 17 | 17 | 17 | 0 | 0 |
| hrm-documents | 4 | 4 | 4 | 0 | 1 (D8 — `test.fail()`) |
| hrm-training | 4 | 4 | 4 | 0 | 1 gap (`test.fail()`, not filed) |
| hrm-reports | 17 | 17 | 17 | 0 | 0 |
| crm-contacts | 5 | 5 | 5 | 0 | 1 re-verified (ERP-135, `test.fail()`) |
| crm-companies | 5 | 5 | 5 | 0 | 0 |
| crm-activities | 4 | 4 | 4 | 0 | 0 |
| crm-tasks | 3 | 3 | 3 | 0 | 1 (D9 — `test.fail()`) |

The suite cleans up after itself (`utils/cleanup.ts`, called from each HRM spec's `afterAll`), so a
full run leaves the demo site exactly as it found it — verified: 24 employees, 0 suite leftovers.
Records are matched by the suite's own `@example.test` e-mail domain, never by name; seeded demo
staff are all `@northwind-analytics.test`.

### CRM — first slice: Contacts (5 cases, all green)

CRM is a family, so it is being taken in slices; this is Contacts, the core. Covered: the list renders
its captured columns, holds **exactly** the eight seeded contacts, a contact can be created and is
stored as a person of type `contact`, and the screen is closed to the employee role.

**The table that matters:** contacts, customers, vendors AND employees all live in one table,
`erp_peoples`, distinguished only by a row in `erp_people_type_relations`. Every CRM count therefore
depends on that table being clean — which is how the pollution below came to light.

**ERP-135 / erp-pro#950 re-verified — still open.** `GET erp/v1/crm/contacts` answers with a SINGLE
raw contact object rather than a collection: a stray `wp_send_json` inside the loop
(`ContactsController.php:378`) ends the request on the first row, so the client gets one contact,
unwrapped, with no total and no pagination. Confirmed live this run (200, `isArray: false`, keys
`id,user_id,first_name,…`). Carried as a `test.fail()` guard; not re-filed.

### ERP-137 / erp-pro#952 — the visibility guard was FLAKY and has been replaced

The full run turned this known-defect guard red: `test.fail()` on "the Pro extensions block never
becomes visible" reported **passed**, i.e. the defect did not reproduce. Measured rather than guessed:
running that spec alone the block is hidden **5/5**, but in a full four-worker run it is **visible**.

That is the race #952 itself describes, seen from the other side — under parallel load the page is
slower, `window.load` lands AFTER jQuery's ready callback, the handler binds in time and the reveal
works. **This is new information for the issue** and worth adding to it: the defect's appearance
depends on page-load speed, so a fast/cached site shows the bug and a loaded one hides it.

For the suite it meant a filed defect was generating intermittent red. Visibility cannot honestly be
asserted either way, so the deterministic half is asserted instead: every licensed Pro extension is
**rendered** in the markup, whatever the race does about showing it. That still catches a licensing or
rendering regression, which is what the screen is for, and `revealProExtensions()` remains for the
tests that need to interact with the list.

### Suite pollution found and fixed: 58 orphaned `erp_peoples` rows

Writing the first CRM assertion surfaced something the HRM passes had been doing for weeks:
`cleanupEmployees()` deleted the `erp_hr_employees` row, the user and its meta — but **not** the
shared `erp_peoples` row or its type relation. An ERP person is two rows, and only one was being
removed. **58 orphans had accumulated**, every one of them a `pwerp`/`@example.test` record from the
suite's own employee tests.

That mattered more than tidiness: `erp_peoples` is the CRM table, so the suite had been quietly
inflating the CRM data set on every run. The very first CRM REST probe returned one of my own orphans
(`Qa Xss / pwerp_xss…@example.test`) as if it were company data. Any "the list holds exactly the
seeded contacts" assertion would have been impossible to write honestly against that base.

Fixed by adding `cleanupPeople()` and calling it from `cleanupEmployees()`; the 58 existing orphans
were purged. Verified afterwards that the seeded demo data survived intact — 8 contacts, 6 customers,
4 vendors, 25 HR employees — because the delete matches only the suite's own markers
(`@example.test`, `pwerp` names), never `@northwind-analytics.test`.

### CRM — Companies (5 cases, all green)

Same `erp_peoples` table again, distinguished by the `company` people type; the form differs from a
contact's only in having one name field (`contact[main][company]`) instead of first/last. Covered: the
list's captured columns, create stored as a person of type **company** with the name in the `company`
column, the genuinely-empty starting state, the required-field refusal, and the screen closed to the
employee role.

**A second cleanup collision, same root as the leave one:** the full run turned the CONTACTS create
test red, because the companies spec's `cleanupCrmContacts()` deleted every suite-created person —
including the contacts spec's in-flight row — while the two files ran on different workers. Cleanup of
`erp_peoples` is now **scoped by people type**: contacts clean `contact`, companies clean `company`,
employees clean `employee`. Unscoped cleanup is reserved for `cleanupAll()`. That is the second time a
shared table has produced cross-file red; the rule is now in HANDOFF.

**A seed-data trap worth knowing:** `seedData.crmCompanies` does NOT create CRM companies. It is
consumed by `seedAccountingPeople()` to create accounting **customers** — so the six "companies"
(Beacon Retail, Harbourline, …) exist as type `customer`, and the CRM `company` type has **zero** rows
on a freshly seeded site. The empty-state test states that explicitly rather than leaving the next
reader to rediscover it. The name is misleading; renaming it is a seed change, not a test change, so
it is recorded here rather than done silently mid-pass.

**A refusal I described wrongly at first:** the "company with no life stage or owner" case initially
asserted that the PRODUCT refuses it. It does not — the refusal is the **browser's**: both selects
carry `required`, so constraint validation blocks the submit and no request is ever sent. Verified: no
AJAX fires, three fields report invalid, and the select's own message reads "Please select an item in
the list." The test now asserts that mechanism, because "the server rejected it" would have been an
untrue description of a passing test.

### CRM — Activities (4 cases, all green)

The activity feed on a contact's detail page: a composer with five tabs (New Note, Email, Log
Activity, Schedule, Tasks) over a timeline filtered by All Activities / Email / Task / Schedule /
Note. Activities are rows in `erp_crm_customer_activities`, typed by the tab that wrote them.

Covered: the feed renders its composer tabs and filters, a note is logged against the contact and
appears in the timeline (DB oracle on type `new_note` and `user_id`), the empty feed states itself,
and the feed is closed to the employee role.

**Two markup traps, both cheap to lose an hour to:**

1. The note body is a **Trix editor** — a contenteditable custom element, not a textarea and not an
   iframe. `fill()` cannot drive it; the text is typed with real key events after focusing.
2. The save control is an **`<input type="submit" value="Save Note">`**, not a `<button>`. Playwright's
   `:has-text()` matches text content and never an input's value, so `button:has-text("Save Note")`
   silently matched nothing. My first dump missed this because it enumerated hidden elements too and
   reported the control as present.

**A bug in the suite's OWN cleanup, found by a residue check:** the type-scoped `cleanupPeople()` I
added for the companies collision deleted the type RELATION first and then scoped the people delete
*by that relation* — which no longer existed. The person row survived every time, with no type at all.
Six orphans had built up within a single pass. Fixed by resolving the target ids before deleting
anything, plus a sweep for rows the broken ordering already orphaned. `erp_peoples` is back to its
seeded baseline of 19 after a full run.

**Not covered in activities, and why:** the Email tab (it sends mail — out of scope for a functional
pass and worth its own decision), and Schedule/Task creation, which belong with the CRM tasks and
schedules pass.

### D9 / ERP-143 → erp-pro#958 — CRM Tasks and Schedules cannot be created at all (filed 2026-08-19)

The Tasks pass found a functional defect: on a contact's activity feed, **"Create Task" and "Create
Schedule" are rendered `disabled` and never enable**, whatever is filled in. New Note on the same feed
works, so the feed itself is sound.

Root cause read from source and confirmed live: the buttons bind to `:disabled="!isValid"`; `isValid`
for these composers needs only `feedData.message`; and that is populated solely by a `trix-change`
listener attached once in `activate()` to `jQuery(this.$el).find('trix-editor').get(0)`
(`crm-app.js:194`). Instrumentation shows the listener is not on the editor being typed into: typing
fires **16 `trix-change` events**, exactly **one** `trix-editor` exists in the document, and the model
still never updates.

Ruled out before claiming it: that the buttons were simply waiting on more required input. With due
date `2026-09-30`, time `10:00am`, an assignee selected and a message typed, the button is still
disabled. Also ruled out that the tab switch was the trigger — loading the page directly at `#tasks`
behaves identically. **5/5**, filed as ERP-143 (awaiting go-ahead), carried as a `test.fail()`.

**A detour I got wrong twice, recorded so nobody repeats it:** I tried to confirm the REST route as a
comparison and sent `type: 'tasks'` (the DB value — the API expects `task`,
`ActivitiesController.php:34`), then `user_id`/`message` when the response suggests `contact_id`. The
second attempt wrote a row with NULL message and NULL user_id, which may simply be my wrong field
names. **No REST defect is claimed.** If someone later sends a correct payload and the row still comes
back empty, that would be an ERP-136-shaped bug worth its own report.

### CRM — Deals (20 cases, all green — 3 of them known-defect guards)

An erp-pro module (`modules/crm/deals`) with four screens behind one page slug, switched by
`sub-section`: `dashboard` (analytics), `all-deals` (the pipeline board), `activities` (the cross-deal
activity list) and `settings` (rendered inside ERP Settings, not here). It registers **no REST routes
at all** — everything is one of 41 `wp_ajax_erp_deals_*` actions — so `DealsPage.callAjax()` exists to
reach a handler's own permission check rather than the menu's.

**Verified before writing a line of it, per the ERP-141 lesson:** all thirteen `wp_erp_crm_deals*`
tables exist on this install. Seeded content is one pipeline, five stages and six activity types;
`erp_crm_deals` itself and every other child table start empty, so this pass creates its own data.

Covered: the board renders every seeded stage and paints them in the order the database holds; the
dashboard renders its three statistic boxes and its funnel; the activities screen renders its seven
columns and its empty state; a deal is created through the modal and stored against its contact with
the right value, stage and owner; stage history is written; the status and owner filters render with
their four and one options; the pipeline switcher stays hidden while one pipeline exists; ERP Settings
and the board agree on the stage list; a deal with no counterparty is refused; the server refuses an
empty title and a non-existent stage through the AJAX endpoint; the board is closed to an employee and
pipeline administration is closed to a CRM agent; and a script payload in a deal title never becomes
executable markup.

**The authorization case is worth reading before writing another one for this module.** Twelve of the
41 handlers — `save_deal`, `delete_deal`, `save_deal_note`, `delete_note`, `add_deal_attachment`,
`remove_deal_attachment`, `update_deal_people`, `add_agents`, `remove_agents`, `send_email`,
`save_competitor`, `delete_competitor` — carry **no capability check whatsoever**, only
`verify_nonce('erp-deals')`. That is not automatically a hole: the nonce is localized only on the
module's own screens, which are gated at `erp_crm_add_contact`, so a role that cannot load the board
never obtains one. The employee case asserts exactly that — denied AND handed no nonce — rather than
asserting the absence of a check. What is NOT covered is whether a CRM **agent**, who legitimately
holds the nonce, can act on another agent's deals; that is an ownership question, needs a second
agent and deals owned by each, and belongs with the CRM agent/manager visibility pass.

**Two product defects found, both reproduced, neither posted yet:**

- **ERP-144 (Major)** — the seeded pipeline is out of order and every deal records reaching
  "Proposal Made". Written up below. Posted as [erp-pro#959](https://github.com/wp-erp/erp-pro/issues/959).
- **ERP-145 (Minor)** — the Add New Deal modal overwrites a title the user already typed. Written up
  below. Posted as [erp-pro#960](https://github.com/wp-erp/erp-pro/issues/960).

**Product traps paid for in this pass:**

1. **The module ships its own modal.** `.erp-deal-modal` has nothing to do with the shared `#erp-modal`
   shell `BasePage` handles, so none of the inherited modal helpers apply. `DealsPage.dealModal` is
   deliberately named differently from `BasePage.modal` rather than overriding it.
2. **Success and refusal both arrive by sweetalert**, never as a notice — and three sweetalert
   containers are in the DOM at once (new deal, schedule activity, mark as lost), so any locator on
   `.sweet-alert` or `.erp-deal-modal-content` must be `.first()`.
3. **No ids, no names on any field** — same as payroll. Everything is anchored on the sibling
   `<label>`, and the pipeline-stage bullets carry their title in a `tooltip-title` attribute with no
   text content at all.
4. **The contact picker needs 3+ characters and a real AJAX round-trip.** `pressSequentially()`, not
   `fill()`, and the option list has to be awaited.
5. **The dashboard funnel's own column header reuses `.stage-name`**, so the first cell of
   `funnelStageOrder()` is the literal word "Stage". Dropped in the page object.
6. **Two different reports look like the same funnel and are not.** "Number of Qualified Leads" is
   `deals_by_pipeline_stages()` — deals in each stage *now*, and it is correct. "Deal Progress" is
   `deals_progress_by_stages()` — deals that have *reached* each stage, read from
   `erp_crm_deals_stage_history`, and it is the one ERP-144 corrupts. I screenshotted the wrong one
   first and would have filed a claim the data did not support; the screenshot was discarded.
7. **`save_deal` needs `owner_id` when the caller is a manager.** `Deal_Ajax::save_deal` defaults the
   owner to the current user only for non-managers, so an admin calling the endpoint without it gets
   the generic `"Could not save the deal. Please try again."` from the failed insert rather than a
   field error. Not filed — the UI always sends it, and the message is only reachable by hand-built
   requests.

**A test I planned and then deleted, because the behaviour does not exist:** "a deal with no title is
refused". There is no such thing as an untitled deal from this modal — picking a counterparty fills
the title. Asserting the refusal would have asserted a rule the product does not have. It was replaced
by the auto-fill case plus a server-side empty-title case through the AJAX endpoint, where the rule
genuinely lives.

**Not covered in deals, and why:**

- **The single-deal page** (`action=view-deal`) — notes, participants, agents, competitors,
  attachments, the email composer, the changelog and the activity modal. It is the largest surface in
  the module and deserves its own pass; four of the prior Deals bugs (ERP-043/044/045/066) live there.
- **Drag-and-drop between stages.** The board uses `jquery-ui-sortable`; a stage move is the event that
  writes the `out` side of stage history, and it is the natural place to re-check ERP-043's
  one-open-row invariant. Not attempted this pass.
- **Won / Lost / Reopen and lost reasons.** `erp_crm_deals_lost_reasons` is seeded empty, so the lost
  path needs a reason created through settings first.
- **Pipeline and stage administration beyond the authorization guard** — creating a second pipeline,
  reordering stages, deleting a stage with deals in it (ERP-045's territory). A second pipeline would
  also expose the ERP-043 cross-pipeline leak; deliberately left to that pass so the two are not
  confused.
- **The `activities` screen beyond rendering.** Creating a deal activity needs a deal and the activity
  modal, which belongs with the single-deal pass.
- **Deal ownership between agents** — see the authorization note above.

### CRM — Single deal (10 cases, all green — 2 of them known-defect guards)

`sub-section=all-deals&action=view-deal&id=N`: the largest surface in Deals, and where four of the
five earlier Deals bugs live. An editable header (title, value, owner, pipeline, expected close
date), a clickable stage bar, Won / Lost / Trash controls, five sidebar boxes, a four-tab composer
(Add activity, Take notes, Send Mail, Upload Files), competitors, and a six-tab timeline.

Covered: the page renders its seven boxes, four composer tabs and six timeline tabs with the deal's
own title and formatted value; a note is written through the Trix composer and lands in
`erp_crm_deals_notes` and in the Notes timeline; a competitor is added through its modal and lands in
`erp_crm_deals_competitors`; clicking the stage bar moves the deal and the stored stage matches the
clicked position; Won stamps `won_at` and swaps the buttons to Reopen, and Reopen clears it; Trash is
a SOFT delete (`deleted_at` stamped, row kept) and the deal leaves the board; the page is closed to an
employee; and a script payload in a note never becomes executable markup.

**Two pre-existing defects re-verified on 1.7.0 — both still Open, and both were NEVER POSTED to
GitHub when filed on 2026-07-21 against 1.6.0:**

- **ERP-043** — a deal's stage history should hold exactly ONE open (`out IS NULL`) row, naming the
  stage the deal is in. Measured on a deal at `Demo Scheduled`: **4 open rows**, naming Proposal Made,
  Lead In, Contact Made and Demo Scheduled. `save_deal()` still rebuilds history from an unscoped
  `PipelineStageModel::get()` (`Deals.php:611`). Carried as a `test.fail()` guard. **Distinct from
  ERP-144**: this is the COUNT of open rows, ERP-144 is WHICH stages they name — one survives a fix to
  the other.
- **ERP-044** — `erp_deals_delete_competitor` as a CRM agent returns **HTTP 500, 3/3**, body
  `<p>There has been a critical error on this website.</p>`. Unchanged in 1.7.0:
  `delete_competitor()` passes `CompetitorModel::where(...)->get()` — a Collection — to
  `is_user_can_delete_competitor()`, which reads `$competitor->created_by`. Admins and managers
  short-circuit one line above and never reach it. **One detail the original report does not have:**
  it fatals on a competitor id that does not exist either, because `empty($competitor)` is false for
  an empty Collection — so the intended `"Invalid competitor"` refusal is dead code by the same
  mechanism. Carried as a `test.fail()` guard asserting only that the module ANSWERS rather than
  fatals; a refusal on ownership grounds would be a legitimate answer and would still pass.

**Traps paid for here:**

1. **The page's ids are Vue `_uid` counters** — `activity-form-22`, `erp-deal-note-24`,
   `competitor-form-16`. They were stable across reloads when measured, but they are a render-order
   artifact, not a contract. Everything anchors on classes instead.
2. **The stage bar's `tooltip-title` is NOT the stage name on this page.** It is a composed history
   string ("Lead In 0 days ($500.00)"), unlike the Add New Deal modal where it is the bare title.
   Stages are addressed by index here.
3. **A dump taken with `innerText` lies about text-transformed labels.** The timeline tabs render as
   `ALL / ACTIVITIES / NOTES …` and I wrote the constant that way; the DOM text is `All / Activities /
   Notes`, uppercased by CSS. `textContent` — and therefore every Playwright text API — sees the real
   casing. The assertion caught it.
4. **`erp_crm_deals_notes` stores the body in `note`, not `content`.** Assumed, and the query failed
   loudly, which is the right way for that to go wrong.
5. **Notes are Trix a third time** (CRM contact feed, deals note editor). Type, never `fill()`.

**A cross-file cleanup collision caught BEFORE it caused red — the fourth of this class.**
`deals.spec.ts` was calling `cleanupDeals()` with the default `pwerp` marker, which matches every
suite-created deal including `singleDeal.spec.ts`'s. Four workers run in parallel, so the two files
overlap in time and the deals spec's `afterAll` would have wiped rows the single-deal spec was
mid-assertion on. Both files now carry their own marker — `pwerp_deal` and `pwerp_sd`. Same lesson as
traps 21, 22 and 28: **any spec cleaning a shared table scopes to its own marker; the unscoped default
belongs to `cleanupAll()` only.** Worth noting it was found by reading, not by a failure.

**Not covered on the single-deal page, and why:**

- **The Send Mail composer** — it sends real mail. Same decision as the CRM contact feed's Email tab.
- **Upload Files / attachments**, which is ERP-066's territory (an attachment row written for a
  non-existent media id). Needs a media fixture and its own pass.
- **The Add activity composer and Open Activities** — activity creation, completion and the activity
  modal. Adjacent to the `activities` sub-section, better done together.
- **Lost**, and the lost-reason modal — `erp_crm_deals_lost_reasons` is seeded empty, so the path
  needs a reason created through ERP Settings first. Won and Reopen ARE covered.
- **Restore and permanent Delete** from the trashed state, and the Trashed deals board filter.
- **Participants and Agents** beyond rendering their empty boxes — both need a second CRM user and
  belong with the agent/manager visibility pass.
- **The editable header popovers** (title, value, owner, pipeline switch, expected close date).
- **ERP-045** (cross-pipeline stage transfer on delete) — needs a second pipeline, deliberately left
  to the pipeline-administration pass so it is not confused with ERP-043 or ERP-144.

### Accounting — first pass (41 cases, all green — 3 known-defect guards)

Accounting had **zero specs** before tonight despite tier-1 cases being authored and 161 REST routes
harvested. This pass covers every screen plus the invoice money path.

**It is a hash-router Vue SPA**, unlike every other ERP module — screens are
`admin.php?page=erp-accounting#/users/customers`, not `section=`/`sub-section=`. Two consequences:
a hash-only change does NOT reload, so `AccountingPage.gotoRoute()` sets `location.hash` and fires
`hashchange` by hand; and the route list was taken from **the app's own navigation anchors** rather
than reconstructed from `router/index.js`, whose 124 nested path declarations I started to
hand-assemble and abandoned as guesswork.

**Screens (33 cases):** all 29 routes render their captured heading with real content and **no 5xx**,
the chart of accounts groups all five account classes, the reports screen offers its five reports, and
Accounting is closed to both an employee and an HR manager — the second being a real
separation-of-duties check rather than a repeat, since HR and Accounting are different capability
domains.

**The money path (8 cases):** an invoice is created from the UI and stored against its customer with
the chosen dates; the line records qty x unit price and agrees with the invoice total; the posting is
a **balanced double entry**; and the customer's transaction ledger gains the matching row. Plus two
validation refusals (no customer, no line item).

**Three defects found and filed:**

- **ERP-149 / erp-pro#966 (Major)** — typed dates are ignored on every Accounting form. Written up
  below; it shaped the whole page object.
- **ERP-150 / erp-pro#967 (Major, P1)** — invoice create answers HTTP 500 and sends no invoice email.
  Written up below.
- **ERP-147 / erp-pro#964** and **ERP-148 / erp-pro#965** came out of the same 5xx sweep — see the
  HRM sections.

**Three assertions I got wrong, all caught before they became bug reports.** Recorded because each
one would have been a false accusation against the product:

1. **"The invoice posts an unbalanced entry."** It does not — the double entry **spans two tables**:
   the receivable debit lands in `erp_acct_invoice_account_details` and the income credit in
   `erp_acct_ledger_details`. Summing either alone looks unbalanced. The oracle now sums both.
2. **"The customer ledger is never written."** Wrong table: the row goes to
   `erp_acct_people_trn_**details**` (`transactions.php:1735`), not `erp_acct_people_trn`. Both tables
   exist, which is what made the mistake easy.
3. **"The save button does nothing."** That was ERP-149 — but I only got there by reading
   `validateForm()`; the error panel renders above the fold and carries no `.error` class, so my first
   check saw nothing at all. **When a form silently does nothing, find its validator before blaming
   the submit.**

**Harness traps paid for here:**

- **The `Save` button is `class="btn-fake"`, and there is a hidden `Save as Draft` with the same
  class.** A loose `button:has-text("Save")` resolves to the hidden one and times out.
  `AccountingPage.save()` matches visible buttons on an exact `/^Save$/`.
- **mysql2 hydrates DATE columns into JS `Date` objects**, so `String(row.trn_date)` is
  `"Thu Aug 20 2026 00:00:00 GMT+0600 (…)"` and never equals an ISO string. `helpers.dbDate()` exists
  for this and routes through `toDate()` so it inherits the local-components rule.
- The transaction forms are label-anchored with no ids and no names except `qty` — payroll and the
  deals modal again.

**Not covered in Accounting, and why:**

- **Payments, bills, purchases, expenses, checks, journals, transfers and estimates** — the create
  forms all render and are covered by the screens pass, but only the INVOICE flow has an end-to-end
  money oracle. Each of the others needs its own posting rules understood before an oracle can be
  written, and inventing one would be worse than leaving the gap stated.
- **The invoice → payment settlement** (tier-1 case ACCOUNTING-F1-001) — the next thing I would write.
  Receive Payment needs an existing unpaid invoice and its own ledger oracle.
- **Opening balance and the trial balance** — 118 fields on one screen, and the report it feeds.
- **Tax rates, agencies, categories and tax payments** beyond rendering.
- **Reports' actual numbers.** They render; nothing asserts the figures yet, which is where the real
  accounting risk lives.
- **Multi-line invoices, discounts and tax on a line** — single-line only so far.

### ERP-149 → erp-pro#966 — typed dates are ignored on every Accounting form (filed 2026-08-20)

`components/base/Datepicker.vue:86` emits to the parent v-model **only when the field is emptied**:

```js
onChangeDate() {
    if (this.selectedDate.length === 0) { … this.$emit('input', this.selectedDate); }
}
```

So typing a date updates what the user sees and nothing else. Saving reports *"Transaction Date is
required. Due Date is required."* with both boxes visibly showing dates — **0 POSTs sent, 3/3**. Only
a calendar day click (`pickerSelect()`) emits a usable value. **34 usages across 21 screens**:
invoice, bill, expense, purchase, check, journal, pay-bill, pay-purchase, receive-payment, transfer,
tax payment, the transaction filters and every dated report.

`AccountingPage.pickDate()` drives the calendar so the suite can create transactions despite this;
`typeDate()` exists only to reproduce it in the guard. **The workaround is in the harness, the defect
is on the ledger.**

### ERP-150 → erp-pro#967 — invoice create returns 500 and sends no invoice email (filed 2026-08-20)

`erp-pdf-invoice 1.2.1` calls `get_magic_quotes_runtime()` — **removed in PHP 8.0** — at
`class-tfpdf.php:1265`, reached from accounting's `erp_acct_new_transaction_sales` hook via
`erp_acct_send_email_on_transaction()` → `erp_acct_generate_pdf()`.

The invoice commits BEFORE the hook, so it is created correctly and appears in Sales Transactions:
the screen looks like a success. What is lost is the response and the mail — `POST
erp/v1/accounting/v1/invoices` answers **500, 3/3**, and after four invoices the site's email log
holds **zero** invoice emails, only WordPress's own "Your Site is Experiencing a Technical Issue"
notice. An API consumer is told a successful create failed.

**Stated, not claimed:** whether bill/purchase/estimate/payment emails break identically was not
tested — the code path is shared and it is likely, but likely is not measured.

### Accounting — invoice settlement (6 cases, all green — 2 known-defect guards)

The money path end to end: raise an invoice, receive a payment, and check the customer's ledger.
`ACCOUNTING-F1-001` and `F1-002` from the tier-1 plan.

**The oracle is the customer's own ledger**, `erp_acct_people_trn_details`: an invoice writes a DEBIT,
a payment writes a CREDIT, and the two net to zero when settled. That sum is what "the customer owes
nothing" actually means in this schema, and it is far harder to fake than a status label — which is
exactly what ERP-151 turned out to exploit.

Covered: paying in full clears the balance; the receipt is applied to the right invoice; a partial
payment leaves exactly the remainder with no rounding drift; and a payment missing its method or
deposit account is refused with the product's own message.

**Payments need FOUR fields, not the two the screen emphasises.** `validateForm()` in
`RecPaymentCreate.vue` also requires **Payment Method** and **Deposit to**, and omitting either makes
the form refuse silently — the same above-the-fold error panel as the invoice form.

### ERP-151 → erp-pro#968 — a multi-invoice payment credits only the last line (filed 2026-08-20)

**Critical, and the most serious thing found in Accounting.**
`erp_acct_insert_payment_data()` resets `$total = 0` INSIDE its line-item loop
(`rec-payments.php:170`), so after the loop `$payment_data['amount']` holds only the LAST line's
total — and `:196` credits that to the customer ledger.

Pay two invoices of 1,800 and 3,600 in full: the receipt records **5,400**, both invoices show
**Paid**, and the customer is credited **3,600**, still owing **1,800** forever. The customer screen
contradicts itself — summary `Outstanding $0.00`, ledger total `Debit 5,400 / Credit 3,600 /
Balance 1800 Dr`. Single-invoice payments are unaffected, which is why it hides.

**How it surfaced is the part worth keeping.** A tier-2 case failed with `expected 3600, received
5400`, and my first assumption was that my own test had leaked state — because **three earlier cases
in that same file genuinely had.** I fixed the isolation, saw it again, and only then read
`rec-payments.php`. The intermediate observation that made it unmistakable: zeroing the second line
produced a ledger credit of **0.00** against a receipt of 1,800 — the last line's value, whatever it
happens to be.

Being wrong three times in a row about that file is precisely why the fourth failure got read
properly instead of being explained away. **A test that has cried wolf is not thereby always wrong.**

**A sixth cross-file cleanup collision, and the fix that finally generalises.** `transactions.spec.ts`
and `payments.spec.ts` both cleaned ALL invoices and run in parallel, so each wiped the other's
in-flight rows. Accounting rows carry no title to mark, so the scope is the **customer**: each file
now owns one seeded customer — Verdant Foods and Harbourline Logistics — and every query and cleanup
in it is scoped by `customer_id`. Same principle as the marker scoping in CRM and HRM, different key.

**Three harness faults fixed along the way, all the same family:**

1. **Per-file cleanup was not enough — it had to be per TEST.** The payment screen lists EVERY
   outstanding invoice for the customer and pre-fills each with its full balance, so a balance left by
   an earlier case is silently settled by the next one. Three cases read as ledger defects until each
   started from a customer who owes nothing.
2. **Orphaned ledger rows — and the ROOT CAUSE was mine.** Six orphans made a fresh 1,800 invoice
   read as **10,800** outstanding. I first patched it with a `cleanupLedgerOrphans()` sweep, which
   was treating the symptom: `cleanupInvoices()` was deleting children by the invoice's PRIMARY KEY
   when **every child table keys on `voucher_no`**. The two are equal on a young site and drift apart
   later — invoice id 102 carries voucher 134 — so the child deletes silently stopped matching and
   left the rows behind. Fixed at the source; the sweep stays as a backstop.
   Then the sweep itself had the same bug in mirror image: it compared against invoice `id`, so it
   considered every LEGITIMATE ledger row an orphan and deleted it. Running in parallel it emptied
   the other accounting spec's customer ledger mid-test, which looked exactly like the product
   failing to post. **The same wrong assumption produced both a false pass and a false failure.**
3. **A fixed sleep raced the due-invoice list.** Choosing a customer fires `GET /invoices/due/{id}`
   and the rows paint only when it returns; a 2.5s wait sometimes saw zero rows, and the payment then
   covered nothing. `receivePayment()` now waits for the rows.

**The schema fact that cost the most time, stated once and plainly:**

**Every accounting child table keys on `voucher_no`, never on the parent's primary key** —
`invoice_details.trn_no`, `invoice_account_details.invoice_no`/`trn_no`, `ledger_details.trn_no`,
`people_trn_details.voucher_no`, and `invoice_receipts_details.invoice_no` all hold the voucher.
Measured directly: invoice `id` 102 → `voucher_no` 134, and all five children carry 134.

Voucher numbers come from a sequence shared across every transaction type, so `id` and `voucher_no`
are **equal on a young site and drift apart as other vouchers are issued**. That is the worst kind of
trap: every query keyed on `id` works at first and silently stops matching later. It cost three
separate failures here — a query that found no line items, a cleanup that left orphans, and an orphan
sweep that deleted live rows.

**Not covered in settlement, and why:** over-payment (ERP-046's territory — pay more than the
balance), payment against a partially-paid invoice, payment reversal/refund, and bill payments, which
are the vendor-side mirror and need `pay-bills.php`'s own posting rules read first.

### Accounting — bills and bill payments (7 cases, all green — 1 known-defect guard)

The vendor side, and deliberately NOT a parameterised copy of the customer side. Four real
differences shape it:

1. **Line items are LEDGER ACCOUNTS, not products.** A bill charges an expense account directly.
2. **The signs invert.** A bill CREDITS the vendor (we owe them) and a payment DEBITS them — the
   mirror of an invoice debiting a customer.
3. **The line amount is `input[name="amount"]` and its grand total recomputes on `keyup` only**
   (`BillCreate.vue:73`). `fill()` updates the line and leaves `finalTotalAmount` at 0, so the form
   refuses with "Total amount can't be zero" beside a line visibly showing the amount. Typed, not
   filled — the same jQuery-keyup family as the CRM search box and the recruitment wizard.
4. **Paying a bill needs FUNDS.** The product refuses with *"Not enough balance in selected account"*
   when the paying account is empty, and this site seeds no opening balances.

That last point is the interesting one. Rather than fake a balance, the happy-path case earns it: it
invoices a funding customer, collects the payment into Cash, and only then pays the bill — a genuine
money-in-then-money-out cycle. Found while building the file, and asserted on its own as a tier-2
case (bill raised, cash empty, payment refused, vendor still owed).

Covered: a bill is created and credited to the vendor with the dates chosen; the posting is a
balanced double entry (expense debit in `ledger_details`, payable credit in `bill_account_details`);
paying in full clears the vendor balance; payment from an unfunded account is refused; a bill with no
vendor is refused by the product; and a bill with no amount is refused **by the browser**.

**That last distinction is deliberate.** Picking an account sets `:required` on the line's amount, so
constraint validation blocks the submit and no request is sent — the product's own "Total amount
can't be zero" rule never runs. Claiming the product refused it would overstate what was tested. Same
shape as the CRM companies life-stage case.

**ERP-150 now covers the whole transaction family.** The original report left bills, purchases,
estimates and payments as "likely but unverified". Measured since: `POST` to **invoices, payments,
bills and pay-bills** all answer **500** from the same `get_magic_quotes_runtime()` call, while every
record commits correctly. Both confirmations were added as comments to erp-pro#967 rather than
silently folded into the body. Estimates and purchases remain untested and are still described that
way.

**Checked so nobody repeats it:** ERP-151's `$total = 0`-inside-the-loop pattern is **not** in
`pay-bills.php` or `pay-purchases.php` — both take the amount straight from the request. The only
other occurrence is in `erp_acct_update_payment()`, the payment EDIT path, which is untested and is
where a sibling would be.

### Accounting — reports (8 cases, all green)

Every other accounting spec asserts what ONE transaction wrote. These assert what the books SAY,
which is what an accountant relies on and the last place an error can hide: a transaction can post
correctly and still be reported wrongly.

**The strongest assertions here need no arithmetic from me, because the product states them itself:**

| Invariant | Where it comes from |
|---|---|
| debits equal credits | the trial balance's own Total row |
| `Assets = Liability + Equity` | the line the balance sheet prints at its foot |
| profit equals income minus expense | the income statement's own three figures |
| the two reports agree on profit | the balance sheet's equity vs the income statement |

That last one is the most valuable case in the file. Two reports are computed independently from the
same ledger; if either aggregation is wrong they diverge, and **neither report alone would reveal it**
— which is exactly the class of error that survives to a year-end.

Seeded from figures the suite controls: one invoice (1,800 revenue) and one bill (400 expense). The
trial balance then reads `Accounts Receivable 1,800 Dr · Sales Revenue 1,800 Cr · Accounts Payable
400 Cr · Advertising 400 Dr · Total 2,200 / 2,200`, the income statement `Income 1,800 · Expense 400
· Profit 1,400`, and the balance sheet `Assets 1,800 = Liability 400 + Equity 1,400`.

**The empty-ledger case is a precondition, not padding.** If the reports showed stale figures on an
empty ledger, a seeded number matching later would prove nothing.

**This file owns the WHOLE ledger for its duration**, which is why its cleanup is UNSCOPED — the one
place in this suite where that is correct rather than a bug. Reports aggregate every transaction on
the site, so they cannot be scoped to a customer or vendor the way the other accounting specs are. It
is safe only because `accounting_money` is single-worker and runs after `e2e_tests`; the unscoped
clean would be a defect in any other file, and the comment in the spec says so.

**One assumption of mine the product corrected.** I asserted that an expense with no revenue still
prints a "Profit" line. It does not — it switches the line to **Loss** and carries it into equity as a
DEBIT, so `Liability Cr 400 + Equity Dr 400` nets to the Assets figure of 0 and the equation still
holds. Entirely correct behaviour; the assumption was mine. The case now asserts the loss and the
equation through it, which is a better test than the one I set out to write.

**Two harness faults the reports pass exposed, both in `AccountingPage` and both affecting every
accounting spec:**

1. **`gotoRoute()` was a no-op when re-entering the route the SPA was already on.** The hash does not
   change, so Vue keeps the same component instance and the screen keeps whatever the previous test
   left in it — a half-filled form whose account lists are never refetched. It surfaced as
   `receivePayment()` returning false on the LAST test of a file and reading as a broken picker. Now
   forces a real reload in that case.
2. **`settle()` waited on a spinner and a fixed pause, which a full reload outruns.** With the reload
   above in place, form interactions started against a half-rendered screen, the save failed
   validation silently, and the assertion read as "the record was never created" — twice, in two
   different files, for two different records. It now waits for a heading with actual text, i.e. for
   the Vue route to have rendered.

The second was caused by the first: fixing the staleness made pages slower to be ready and exposed a
wait that had always been too weak. Worth stating plainly — **a fix that surfaces a second fault is
not a regression, but it does mean the first run after it cannot be trusted as a verdict on either.**

**Not covered in reports, and why:**

- **Date-range filtering.** Every report query is `trn_date BETWEEN`, and all seeded transactions are
  dated today, so the default range is never exercised at its edges. A transaction dated outside the
  range appearing or vanishing is untested — and given ERP-149 (typed dates ignored), setting a
  custom range through the UI needs the calendar helper on the report filters first.
- **Sales Tax reports** — four of them (`agency`, `category`, `customer`, `transaction` based), all
  needing tax rates configured, which nothing in the suite creates yet.
- **The Ledger Report's figures.** It renders and is covered by the no-5xx case, but asserting its
  numbers needs an account selected in its filter; only the empty state is exercised today.
- **Opening balances** feeding the trial balance — 118 fields on one screen, still untouched.
- **Multi-currency**, which the plugin flags as a `@todo` in its own source.

### The accounting money specs need their own project — measured, not assumed

`transactions`, `payments` and `bills` post to one shared **Cash ledger** and one voucher sequence.
That is global state no customer or vendor scoping can isolate, and it took two fixes to settle:

1. **Own project, single worker.** Run beside each other, one file's funding broke another's "the
   account is empty" precondition and one file's cleanup emptied the account another was about to pay
   from. `accounting_money` runs them serially; `e2e_tests` `testIgnore`s them.
2. **`dependencies: ['e2e_tests']` — and this was the part I got wrong first.** Single-worker alone
   did NOT fix it, because Playwright runs projects **concurrently**: one accounting worker still
   competed with four e2e workers for one Docker site. Measured plainly — **21/21 green running the
   project alone, 3–4 failures every time it ran alongside the rest.** Ordering it after `e2e_tests`
   is what made the ledger assertions deterministic.

**A false fix I shipped and had to undo in between:** moving bill PAYMENT into `payments.spec.ts` was
correct (it spends the same Cash), but I gave it `bills.spec.ts`'s vendor — so both files then cleaned
the same bills and destroyed each other's rows. One collision traded for another. **One party per
file, always.**

**The wider lesson, and the next harness task.** These were not the only load-sensitive failures: the
CRM deals board also went red at ~300 tests, asserting an empty stage list because the SPA had not
painted. The suite's fixed `waitForTimeout()` calls held at 200 tests and are marginal at 300. The
deals board and the accounting pickers now wait on their own content; **the rest of the fixed sleeps
should be converted the same way before the suite grows again.** Recorded here rather than left as
folklore.

### Pro — integrations (12 cases green, 8 gated skips)

The seven externally-authenticated integrations plus Dropbox, written in two halves on the lead's
instruction: cover what needs no third-party account now, and write the credential-dependent half so
it runs the day sandbox credentials arrive.

**Green today** — everything reachable without an account: ERP Settings → Integration lists all eight
bundled integrations, each has exactly one Configure control (counted, so a missing row cannot pass by
a label appearing elsewhere on the page), clicking Configure opens that integration's own panel, each
of the eight configuration screens loads without a fatal or a 5xx, and CRM → Integrations renders its
own subset.

**Gated** — one connect case per integration, skipped with a reason naming the exact variables that
are missing (`Salesforce sandbox credentials not configured — set SALESFORCE_CLIENT_ID,
SALESFORCE_CLIENT_SECRET in .env`). They appear in every report as skips rather than being absent,
because a silent gap is indistinguishable from coverage.

**What the gated cases deliberately do NOT do:** script a speculative connect flow. Field names, the
connect control and the success signal differ per integration and must be captured against a live
sandbox. A guessed selector would fail for the wrong reason on the first real run and cost more than
it saved. Each gated case therefore fails loudly with instructions if credentials ARE present and the
flow has not yet been captured — so the day the variables land, the report says exactly what to finish
rather than going quietly green.

**One assertion corrected here too:** the first version checked that opening a panel "reveals its
credential fields", and it failed against a perfectly healthy screen — on a DISABLED integration the
only visible controls are the enable toggles, and the token field appears once it is switched on.
Enabling an integration just to satisfy the assertion would have been changing product state to fit
the test. It now asserts the panel's own heading instead.

### A harness bug that only fires at night — `toDate()` and the timezone (found 2026-08-20)

Two leave specs went red on a full run with `"Mon–Wed counts as three working days — expected 3,
received 2"`. Nothing in leave had changed, and the same specs had been green twice that evening.

**It was ours, and it was the clock.** `toDate()` formatted with `date.toISOString().slice(0, 10)`,
but every date the suite builds is assembled from LOCAL components (`setDate()`, `getDay()`).
`toISOString()` converts to UTC first, so on a timezone ahead of UTC any date produced between
midnight and the offset comes out as the **previous day**. This machine is UTC+6 and the run started
at 00:04 local, so `upcomingMonday()` returned a **Sunday**:

```
local now       : Thu Aug 20 2026 00:04:25
toISOString     : 2026-08-19T18:04:25.051Z
local Monday    : Mon Sep 07 2026   (getDay 1)
after toISO cut : 2026-09-06        (day-of-week 0 — Sunday)
```

Mon–Wed silently became Sun–Tue, which really is two working days. **The product was right and the
harness was wrong**, and the failure message pointed straight at working-day counting — the exact
shape of a product defect. Had it been believed, it would have produced a bug report about leave
maths.

Fixed at the root: `toDate()` now formats from local components, which repairs `dateOffset()`,
`upcomingMonday()` and every caller at once rather than patching one helper.

**Guard added:** `tests/e2e/core/dateHelpers.spec.ts` — four cases, no browser and no site, asserting
that `upcomingMonday()` lands on a Monday for every offset the suite actually uses, that
Monday+1/+2/+4 are Tue/Wed/Fri, that `toDate()` returns the local calendar day, and that a locally
built date survives the round trip with its weekday intact.

Three things worth carrying forward:

1. **A time-of-day-dependent bug is the worst kind to triage** — it passes all day and fails at night,
   so it gets written off as flake. The COVERAGE entry from the earlier CRM pass, "one intermittent I
   could not reproduce", is very likely this same bug seen once and dismissed. It is now reproducible
   on demand: run between 00:00 and 06:00 local, or set `TZ` ahead of UTC.
2. **`toISOString()` is not a date formatter.** It is a UTC instant serialiser. Any code mixing it
   with `setDate()`/`getDay()` has this bug latent in it.
3. **It would have hit CI regardless of local time.** GitHub Actions runners are UTC, so `toDate()`
   there is accidentally correct — meaning CI would have been GREEN while a Bangladesh-hours local run
   was red, which is the most confusing possible split.

### CRM — Deal access control between agents (8 cases, all green — 2 known-defect guards)

The security pass, and the one that needed a **second CRM agent**: one agent can only answer "can I
see my own data". `authStates.ts` now carries `crmAgent2`, and `_auth.setup.ts` — which is
data-driven off that map — seeds and authenticates it with no change of its own.

**Deals HAS a working ownership model, and that is the finding's foundation.**
`Deal::scopeReadable()` (`Models/Deal.php:35`) restricts anyone who is neither administrator nor CRM
manager to deals they own or are a listed agent on. Every path that loads a deal through `get_deal()`
inherits it. Six positive controls confirm it holds, and they are what make the one failure
meaningful — this is a single missing check, not "deals have no permissions":

| Path | Agent → another agent's deal | Result |
|---|---|---|
| `erp_deals_get_single_deal_data` | read | refused — `"Deal does not exist"` |
| `erp_deals_save_deal_note` | note | refused — `"Invalid deal id"`, nothing written |
| `erp_deals_delete_deal` | trash | refused — `"Invalid deal"`, `deleted_at` still NULL |
| board (`get_deals_by_pipeline`) | list | the deal is absent |
| `erp_deals_get_single_deal_data` as CRM **manager** | read | allowed — the exemption works |
| `erp_deals_save_deal` | **write** | **allowed — ERP-146** |

**ERP-146 / erp-pro#963 (Major, P1)** — `Deals::save_deal()` (`Deals.php:535`) is the one path that
never routes through `readable()`, and the AJAX handler has no capability check either. Worse than an
unauthorised edit: `Deal_Ajax.php:260` reassigns `owner_id` to the caller for any non-manager, so the
write **transfers the deal**. Measured 3/3 — `owner_id` 11 → 115 while `created_by` stayed 11, a trash
refused as `"Invalid deal"` seconds earlier then succeeded, and the original agent's board went
**empty**. Both halves are carried as `test.fail()` guards: the takeover itself, and the escalation
(refused-before / allowed-after on the same endpoint, same caller, same deal).

**How wrong my code-reading was, recorded because it is the lesson.** From reading the AJAX layer
alone I predicted FOUR holes — read, write, delete and board scope — because none of those handlers
carries a capability check. Three of the four were wrong: the model lives one layer down, in an
Eloquent scope on the model, not in the handlers. The first run reported "Expected to fail, but
passed" three times, which is `test.fail()` doing exactly its job. **A permission model can live
below the layer you are reading; assert the behaviour before believing the absence of a check.**

The first probe also nearly produced a wrong conclusion in the other direction: a run that did
`save_deal` *before* `delete_deal` showed the delete succeeding, which looks like "delete is
unguarded". It is not — the earlier write had already made the caller the owner. Ordering the probe
so the delete is attempted BOTH before and after the write is what separated the two.

**A second trap worth keeping:** several deals handlers read `$_GET`, not `$_POST` —
`get_single_deal_data`, `get_deals_by_pipeline`, `get_overview_data`, `search_people`. POSTing to them
sends no arguments at all and the handler answers its "invalid" branch, **which reads exactly like a
permission refusal**. `DealsPage.callAjaxGet()` exists for those; using the wrong one would have
manufactured a false "access denied" and a bug report to match.

**Not covered here, and why:**

- **CRM contacts, companies and activities** between agents — the same question on the free side, a
  different ownership column (`contact_owner`), and a bigger surface. Deals only, this pass.
- **The deal `agents` list** — `scopeReadable()` also grants access to a listed agent, which is the
  intended sharing mechanism. Adding an agent and confirming the grant works belongs with the
  participants/agents pass.
- **Whether the takeover is reachable through the UI** rather than through the endpoint. It is filed
  on the endpoint, which is what the module exposes; the single-deal page will not offer an agent a
  deal they cannot read, so the practical path is a crafted request.

### ERP-144 → erp-pro#959 — the default pipeline is seeded out of order, and it is not cosmetic (filed 2026-08-19)

`table-data.php:26` seeds `Proposal Made` with `order = 0` while Lead In..Negotiations Started get
1,2,3,4. Every reader of that column is correct; the data is wrong. So the board, ERP Settings, the
Add New Deal modal and the Deal Progress report all open the funnel at the fourth stage, and
`new-deal-modal/index.js:298` makes `Proposal Made` the default stage for every new deal.

The part that turns it from cosmetic into wrong data: `Deals::save_deal()` (`Deals.php:611-625`)
rebuilds stage history by walking stages in `order` sequence and writing an `in` row for each until it
reaches the deal's own stage. A deal created at `Lead In` therefore records having reached
`Proposal Made` first. `Statistics::deals_progress_by_stages()` (`Statistics.php:193/197`) counts that
table, and reported — with two deals, one at Lead In and one at Contact Made — `Proposal Made` 2 deals
/ $3,000.00 while `Demo Scheduled`, the stage immediately before it, sat at 0. A funnel that cannot
occur.

**Not a duplicate of ERP-043**, and the register row says so: ERP-043 is the unscoped
`PipelineStageModel::get()` leaking stages from *other* pipelines. This reproduces on a
single-pipeline site and would survive a fix to ERP-043 untouched. Duplicate search was run over
REGISTER.md and every bug file before minting.

**4/4** deal creations (two through the board, two through `erp_deals_save_deal`) each wrote the
phantom row, on a deterministic DB and report oracle, plus 2/2 suite runs of the guard. Carried as two
`test.fail()` guards — one on the painted order, one on the history — each with a precondition
assertion, because `test.fail()` reports a PASS on any failure including a broken setup. The canary
beside them ("a new deal opens at the first stage of the pipeline") asserts what the product *does*,
so a broken modal cannot make the guards pass for free.

### ERP-145 → erp-pro#960 — the new-deal modal overwrites a title the user already typed (filed 2026-08-19)

Type a deal title, then pick the contact, and the title is replaced with `<contact> deal`. No notice,
no undo. `new-deal-modal/index.js:324-331` is a watcher on the selected contact that assigns the title
unconditionally; `:333-343` does the same for the company. A `if (!this.deal.title)` guard would keep
the convenience and stop the loss.

Only bites users who fill the form out of order, since Contact is the first field — hence Minor. **3/3
in the browser plus 2/2 suite runs, which is below the 5–6× gate a browser finding normally needs.
Stated, not waived:** it was filed anyway because the cause is an unconditional assignment read in
source with no timing or state dependency for a race to hide in. Carried as a `test.fail()` guard.

### A third cleanup collision — and one intermittent I could not reproduce

`cleanupCrmActivities()` deleted every `%pwerp%` activity, so the tasks spec wiped the activities
spec's in-flight note across workers; the note test failed about one run in three. Cleanup is now
scoped by marker (`pwerp note` vs `pwerp task`). **That is the third time a shared table has produced
cross-file red** — after leave requests and `erp_peoples` — so the rule is now general in HANDOFF: any
spec cleaning a shared table scopes to its own marker.

Honesty note: one run AFTER that fix still showed the same test failing, and it was also markedly
slower (41.6s vs ~20s), which points at machine contention rather than the collision. I could not
reproduce it in five subsequent CRM runs or two full-suite runs. It is recorded as an unresolved
intermittent rather than declared fixed.

**Not covered yet in CRM, and why:** contact groups contact groups and subscribers, activities and
schedules, tasks, deals and the deal pipeline, CRM reports, and the CRM agent/manager visibility rules
— the last being a security question (can an agent see another agent's contacts?) that deserves its
own focused pass rather than a line in a functional one.

**HRM is now authored end to end** — People, Leave (holidays, policies, entitlements, requests,
calendar), Payroll, Attendance, Assets, Recruitment, Documents, Training and Reports all have specs
that have actually run. What remains inside those areas is listed per-section above as "not covered,
and why".

### HR Reports — 17 cases, all green

Nine read-only reports (age profile, gender profile, headcount, years of service, salary history,
leaves, assets, attendance by date, attendance by employee). All nine load without a PHP fatal, three
render their captured columns, and — the point of this pass — **their figures are reconciled against
the seeded company rather than against the screen describing itself**:

- headcount lists all **24** seeded employees and exactly those;
- salary history reports each spot-checked employee at their seeded pay rate and `monthly` pay type;
- the leaves report carries a column for every one of the six seeded leave policies;
- the age profile breaks down by all eight seeded departments.

**A discrepancy I chased before asserting anything:** `erp_hr_employees` holds **25** active rows while
the seed defines 24 and headcount shows 24. The 25th is the suite's OWN `erp_employee` actor, created
by `_auth.setup.ts` for authorization tests; it has no department, designation or hire date and the
headcount report legitimately omits it. Asserting a flat "24" would have been right by accident and
brittle forever, so the test asserts every seeded name is present AND that the report holds exactly
the seeded staff — which stays true whatever actors the suite adds. The leaves report meanwhile counts
**25 items**, because it does include the actor; that is a difference between two reports' inclusion
rules, not an error, and it is why no cross-report total is asserted.

**Not covered, and why:** the attendance reports render but hold no data — attendance logging is not
covered yet (see the attendance section), so there is nothing to reconcile. Their CSV export and the
date-range filters are likewise unexercised. The Flot charts are not asserted at all: they are canvas
drawings with no accessible text, and asserting their existence would prove nothing about the numbers
in them.

### Training — 4 cases, all green

Not an ERP screen at all: trainings are a WordPress custom post type (`erp_hr_training`) edited in the
CLASSIC editor and listed by `edit.php`, with the module's fields in an "HR Training Options"
metabox. The module ships **no tables** — assignments live in meta (`erp_employee_training` on the
user, `erp_training_completed_employee` / `..._incompleted_employee` on the post).

Covered and green: the list renders its captured columns; a training can be created and its subject
round-trips into postmeta and back out into the list column; the list is closed to the employee role;
and the headcount endpoint case below.

**A product typo that matters for selectors:** the subject field's id is **`traning-subject`**
(misspelled) while its posted name is `training_subject`. The page object anchors on the NAME. This is
the third typo of its kind in erp-pro after `asset-allottment` and the view file `assign-new-traing.php`
— worth expecting rather than being surprised by.

**A gap carried but deliberately NOT filed:** `erp_training_employee_count` (`Ajax.php:28`) is the one
training AJAX action with **neither a capability check nor a nonce**; the other four check
`erp_list_employee`. Verified live: as a plain employee it answers `{"success":true,"data":{"count":0}}`.
It is carried as a `test.fail()` so it flips the moment a check is added — or the moment the endpoint
starts returning more than a number. Not filed because the response is a bare **count**: no names, no
e-mails, no PII, so the disclosure is a departmental headcount. Worth noting alongside it that **none**
of the five actions verifies a nonce, so the mutating ones (assign, delete) are CSRF-able against a
user who does hold the capability — that is the more interesting half, and it is recorded here rather
than filed because I did not build the cross-site proof this pass.

**Suite pollution I caused and fixed:** opening `post-new.php` makes WordPress write an `auto-draft`
row EVERY time, and my first cleanup matched only `pwerp%` titles — so each run left a stray "Auto
Draft" behind (5 had accumulated before I checked). `cleanupTrainings()` now sweeps auto-drafts of
this post type too, and a full run leaves zero rows.

**Not covered, and why:** assigning a training to an employee and the completion flow. Both live on
the EMPLOYEE profile's Training tab rather than this screen, and they need an employee actor plus the
assign modal — that belongs with the employee-profile pass, not here.

### Documents — 4 cases green, and one security finding

Files are ordinary WordPress media attachments; the folder tree lives in
`erp_employee_dir_file_relationship` and sharing in `erp_dir_file_share`. Both tables exist.

Covered and green: the screen renders its controls (Upload, Create Folder, Move to, Delete, Share
with) and its three sources (Owned by me, My Dropbox, Shared with me); a file can be uploaded and is
recorded against the tree with a real attachment behind it; and the screen is closed to the employee
role (HR manager is allowed — checked both, rather than assuming).

### D8 / ERP-142 → erp-pro#957 — HR documents are served from public, unauthenticated URLs (filed 2026-08-19)

The module stores documents as plain media and hands out `wp_get_attachment_url()` with no protection
of its own. `curl` with no cookies returns **HTTP 200 and the full contents**, 3/3, and the same from
a fresh browser context with an empty cookie jar. Yet the module ships an ownership and sharing model
(`erp_dir_file_share`, "Shared with me", a "Share with" action) — which turns out to govern only
whether a file is LISTED, not whether it can be read.

**Stated fairly:** directory listing is **403**, so files cannot be browsed; an attacker needs the
URL. But filenames survive `sanitize_file_name()` verbatim under a predictable `uploads/YYYY/MM/`
path, and URLs leak through history, referrers, backups and forwarded links. This is WordPress's
default media handling — the argument is not that WordPress is wrong, but that a module shipping a
sharing model should not rely on it for HR records. Filed as **High**, not Critical, for that reason.

**Not a duplicate, and why it took a careful look:** ERP-021 (Open) reports a share-route IDOR whose
oracle mentions the attachment URL, and it would be easy to fold this in. But that defect needs a
logged-in employee abusing `/documents/share`; this one needs **no account at all** and would survive
a complete fix of that route. Worth flagging the chain: ERP-021 hands an employee someone else's
document URL, ERP-142 makes that URL work for anyone, forever.

**A trap in my own test I had to correct:** `test.fail()` reports a PASS on any failure, so the
security case would have looked satisfied if the UPLOAD had broken instead. It now asserts its own
precondition, the `@crud` case is the canary, and I verified out-of-band that the failing assertion is
the anonymous fetch (200 + confidential text), not the upload. Also: WordPress **deduplicates**
repeated filenames (`name-1.txt`, `name-2.txt`), so a fixed fixture name is only assertable on the
first run — each run now uploads a unique name from memory.

**Not covered, and why:** folder create/move/delete, the share flow itself (ERP-021 territory — it
needs two employee actors and is a security pass rather than a functional one), and the **Dropbox**
integration, which is an external service and is left alone entirely.

### Recruitment — 17 cases, all green

The largest HRM module: 12 screens, 10 tables (all present, checked first), job openings stored as a
WordPress custom post type (`erp_hr_recruitment`) rather than an ERP table.

Covered and green: all eight screens load without a PHP fatal (job openings, add opening, candidates,
add candidate, stages, calendar, reports, AI settings); the job-opening, candidate and stage lists
render their captured columns; the four default hiring stages are present; step one of the opening
wizard publishes the post and advances; the wizard cannot be advanced without a title; the AI settings
screen renders its key field; and the module is closed to the employee role.

**Two things I got wrong and corrected — both worth recording:**

1. **I invented the default stage names.** I wrote `Unscreened / In Process / Archived / Other`, taken
   from the REPORTS screen's candidate-distribution columns. The actual stages are
   **Screening / Phone Interview / Face to Face Interview / Make an Offer**. The test failed and the
   names came from the screen. This is precisely the failure the anti-invention rail exists to catch,
   and it caught it — but only because the assertion was real.
2. **"Created → listed" was the wrong assertion.** Step one publishes the post, yet the list INNER
   JOINs postmeta on `_expire_date` (`functions-recruitment.php:604`), which a LATER wizard step
   writes. The test now asserts what is actually true and documents the consequence.

**Observed, NOT filed — the orphaned opening:** an opening abandoned at step one is a **published
post that never appears in the Job Opening list**, and so cannot be seen, edited or deleted from that
screen. It is carried as a passing Tier-2 case that states the behaviour. Not filed because it needs a
product decision (should step one publish at all, or save a draft?) rather than a defect report, and I
have no evidence of user harm beyond the invisibility itself.

**The AI screens are LOADED, never exercised.** Four screens are backed by a **Gemini API key**
(`erp_rec_gemini_api_key`). The suite asserts the settings screen renders and stops there. No
generation, CV analysis or job-writer action is triggered anywhere: those are outbound calls to a paid
third-party service, and the test mu-plugin blocks **only wordpress.org** — such a call would really
leave the machine. Tagged `@needs-external`. Exercising them needs a sandbox key and an explicit
decision from the user.

**Not covered, and why:** the rest of the opening wizard (hiring workflow, job information,
questionnaire), candidates and their stage movement/rating, the todo calendar, and the reports
export/e-mail paths. The wizard is several more steps and candidates depend on a completed opening —
that is its own pass.

### Assets — 8 cases, all green

Three server-rendered lists sharing the ERP modal shell. All four tables exist (checked first).

Covered and green: the assets, allotments and requests screens each render their captured columns; a
category can be created; an asset is stored correctly and listed; the form offers the categories that
exist; an asset with no category is refused with the product's own message; and the screen is closed
to the employee role.

**Product facts learned:**

1. **Sub-section slugs are irregular and one is misspelled** — `asset`, `asset-allottment` (two t's),
   `asset-request`. A wrong slug silently renders the Assets list, so a typo looks like three screens
   with identical columns. That is exactly what I saw before checking the nav.
2. **One asset is TWO rows**: a group row (`parent = 0`) plus one child per item code, the child
   carrying `status = 'stock'`. The list's "Available/Total" counts the children, so a one-item asset
   reads 1/1.
3. **The modal shows BOTH submit buttons at once** — "Save Asset" and "Save Category" — so
   `BasePage.submitModal()` (which takes the first primary button) always pressed Save Asset. The page
   object names them.
4. **A refusal is delivered by sweetalert**, not a notice and not a native dialog: `asset_insert`
   answers HTTP 200 with a bare `die()` string which the JS renders through swal. Reading the page
   body finds nothing and makes a refusal that DID warn the user look silent — I hit exactly that and
   fixed the reader rather than the assertion.

**Observed, NOT filed — a dangling AJAX callback that is not reachable:**
`wp_ajax_erp-hr-emp-delete-asset` is registered to `[$this, 'emp_asset_remove']`, and that method
**does not exist** in `AjaxHandler` (which has no parent class). Calling the action returns HTTP 500,
verified as admin. The employee asset tab does carry `data-action="erp-hr-emp-delete-asset"` on its
delete control (`asset-employee-tab.php:114`), which looks damning — but the handler bound to that
control (`assets.js:1558`) sends a hardcoded `erp-assets-request-delete`, which works, and never reads
`data-action`. So the dangling callback is **dead code, not a user-facing fault**. Recorded rather than
filed: filing it as "delete is broken" would have been wrong, and it took reading the JS binding to
know that.

Also worth noting for whoever tests this next: the module has **28 AJAX actions and ZERO
`current_user_can`** — the payroll pattern. It is less exposed than payroll only because 24 of the 28
verify a form-specific nonce an employee cannot obtain. The four that verify nothing are
`erp-hr-emp-delete-asset` (dead), `erp-assets-emp-request-return`, `erp-assets-emp-reject-return-request`
and `erp_asset_edit_category_reload`. **The two live return-request actions were NOT probed for
privilege escalation this pass** — that needs an allotment to exist first, and it is the obvious next
thing to check here.

**Not covered, and why:** allotting an asset to an employee, the return flow, asset requests and their
approve/reject, and the dismiss/single-item paths. All of them need an allotment chain built first;
that is its own pass, and it is where the unguarded return actions should be probed.

### Attendance — 10 cases, all green

The best-built module met so far. It is a hash-routed SPA (`#/`, `#/shifts`, `#/assign-shift-bulk`,
`#/exim`) with a REST API at `erp/v1/hrm/attendance/*` that guards **every** route with a capability
check — 29 `current_user_can` calls, against payroll's one. All five of its tables exist (checked
first, after ERP-141 taught that this cannot be assumed).

Covered and green: the attendance, shifts and bulk-assign screens each render their captured columns;
the bulk-assign screen offers the seeded employees; a shift can be created with a DB oracle on
`start_time`/`end_time`/`duration`; an overnight shift counts its own hours, not a negative span; a
duplicate shift is refused; a 24-hour shift is refused; the screen is closed to the employee role; and
the REST shift route refuses an employee over Basic-Auth — the case that PROVES the capability check
rather than assuming it.

**Product rules learned:**

1. **A shift is a duplicate on NAME or on TIME RANGE** (`erp_atts_is_duplicate_shift`) — two shifts
   may not share a start/end pair even under different names. Each test therefore owns a distinct
   time range, and the file cleans shifts in `beforeAll` as well as `afterAll`: a leftover range makes
   an unrelated later create look like a duplicate.
2. **A shift must be under 24 hours.** Equal start and end pushes the end forward a day, which trips
   the `invalid-shift-range` guard.
3. **Navigating to the hash you are already on is a no-op** — the SPA does not remount, so a second
   "Add New Shift" click finds nothing. `openShiftForm()` forces a document load.

**Observed, not filed:** validation failures come back as **HTTP 500** with a `WP_Error` body
(`duplicate-shift`, `invalid-shift-range`) where a 4xx would be correct — a `WP_Error` with no status
defaults to 500. It is an API-correctness wart, not a functional fault: the message is accurate, the
UI shows it, and nothing is written. Recorded rather than filed.

**Not covered, and why:** check-in/check-out logging (`erp_attendance_log`), shift generation
("Generate" per shift), the import/export and grace-time settings on `#/exim`, and the attendance
report. Those need generated shift days and log rows to assert anything honest about presence/late
arithmetic, which is its own pass. Payroll has a
first pass (below); its pay-RUN arithmetic is not covered yet.

### Payroll — first pass (13 cases green), and ERP-004 re-verified as still open

Payroll is erp-pro, has **no REST API**, and is driven by **67 `wp_ajax_erp_payroll_*` actions**
(`payroll/includes/AjaxHandler.php`). The forms are Vue with **no id or name on any field**, so
`PayrollPage` anchors every control on its visible label.

Covered and green: all five screens load without a PHP fatal (dashboard, calendar, payrun, bulk pay
item edit, reports); the pay-run and bulk-edit lists render their captured columns; a pay calendar
can be created through the form and the row is verified in the DB; the type list offers exactly
Hourly/Weekly/Biweekly/Monthly (`daily` and `contract` are explicitly removed); a second calendar of
the same type is refused; and the Pay Run screen is closed to the employee role.

**ERP-004 (filed 2026-07-02 against erp-pro 1.6.0, Critical, still Open) re-verified on 1.7.0 —
still unfixed.** `AjaxHandler.php` contains exactly ONE `current_user_can` call across its 67
actions. Confirmed live this run, as a plain `employee`:

- `erp_payroll_get_employee_list` → `{"success":true,...}` with every employee's name, e-mail and
  `pay_rate`. The whole salary roster, to anyone logged in.
- `erp_payroll_create_pay_calendar` → `"Pay calendar created successfully"`, and the row was really
  written to `wp_erp_hr_payroll_pay_calendar` (I deleted it again).

Carried as two `test.fail()` guards, NOT re-filed — ERP-004 already covers both halves. They will
start failing the moment it is fixed.

**Product rules learned, both of which shape the tests:**

1. **One calendar per TYPE.** `create_pay_calendar` counts rows of that type and refuses a second.
   This bit the authz guard: it first used `monthly`, which an earlier test had already created, so
   the employee's write was refused by the DUPLICATE check and the guard read as "fixed". It now uses
   `hourly` and explicitly asserts the refusal is *not* the duplicate message — a pass for the wrong
   reason is the exact failure mode a security guard must not have.
2. **The employee picker only offers staff whose own pay type matches the calendar.** Every seeded
   employee is monthly, so the form cannot build a weekly or biweekly calendar at all. The
   duplicate-type case therefore drives the endpoint, and says so in the test.

**A collision the parallel run caught, worth recording:** the calendar spec and the entitlements spec
were both using employee index 3, and the suite runs four workers — so two files assigned entitlements
to the same person at the same time and both went red, while each passed alone. Test-data ownership is
now disjoint per spec file: entitlements 3-4, requests 4-12, calendar 15-17 + 20-21. Two spec files
must not share a seeded employee.

### The pay RUN is BLOCKED on this environment — ERP-141 → erp-pro#956 (Critical), filed 2026-08-19

No pay-run tests were written, deliberately. `wp_erp_hr_payroll_payrun` **does not exist** on this
install: the installer's `CREATE TABLE` declares an unquoted `to_date`, which is a RESERVED word in
MariaDB 12.3, so the statement is a syntax error — and `dbDelta()` reports `"Created table …"` anyway
while `Installer.php:205` ignores the return. Seven of the module's eight tables exist; this one does
not. MySQL does not reserve `TO_DATE`, so the defect is MariaDB-only, which is why earlier payroll
bugs (ERP-018, ERP-032) could describe pay runs that worked.

The consequence is financial and verified: `start_payrun` can never record a run, so every
`payrun_detail` row carries `payrun_id = 0`, and the employee-list query sums by `empid` + `payrun_id`
— which makes each pay run include every previous one. Observed 2/2 for an employee on 5,200:
**5,200 → 5,200 → 10,400 → 20,800**, persisted and shown as Net Pay.

**Writing pay-run assertions here would be writing tests against a broken schema** — they would encode
the compounding as expected, or fail for a reason unrelated to what they claim to check. The cases stay
unwritten and are recorded here as blocked. Once ERP-141 is fixed (or the suite runs on MySQL), the
oracle is ready: Net Pay = Pay Basic + Payment − Deduction − Tax, cross-checked against the ledger rows
`approve_payment()` writes to `wp_erp_acct_ledger_details` (cash 7, wages_salaries 42,
payroll_tax_expense 43 on this install).

**A wrong hypothesis I discarded, recorded so nobody re-runs it:** I first blamed the malformed
`updated_at timestamp on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` in the same
CREATE. A raw query with that clause alone succeeds on MariaDB 12.3 — it is not the cause. Only the
column-by-column probe pinned `to_date`.

**Also observed, NOT filed:** `approve_payment()` computes the amounts it posts to the accounting
ledger entirely from `$_POST['employeedata']`, never recomputing them from the stored pay-run detail,
and with no capability check (nonce only). That is a money-integrity concern, but ERP-141 blocks the
approve path here, so it is unproven end-to-end and is written down rather than claimed.

**Not covered yet, and why:** the pay RUN itself — starting a run, variable input, approving, and the
payslip. That is the arithmetic (gross − deductions = net) and the money path, and it deserves its own
pass rather than being tacked on here. Also uncovered: pay items and categories, payroll settings, the
reports screen beyond loading, and `erp_payroll_get_employee_list`'s missing-`empids` warning noted
while reading `create_pay_calendar` (`$_POST['empids']` is read without `isset`).

### D5 / D6 — Leave Calendar department filter (ERP-138, ERP-139), found 2026-08-19

Two defects in one code path, both carried as `test.fail()` in `leaveCalendar.spec.ts` — they pass
while the defect stands and fail the moment it is fixed. Both filed 2026-08-19 with screenshots:
**ERP-138 → erp-pro#953**, **ERP-139 → erp-pro#954**.

- **D5 / ERP-138 — the filter widens instead of narrowing.** Filtering the calendar by a department
  with no approved leave shows EVERY approved leave in the company. `views/leave/calendar.php:3`
  normalises the "- Select Designation -" sentinel `-1` with `absint()`, and `absint('-1')` is **1** —
  so a department-only filter becomes department + designation 1. When that pair matches nobody,
  `erp_hr_get_leave_requests()` adds no WHERE clause at all (`if ( $users->count() )`) and returns
  everything. Proven at the data layer: `department_id=65, designation_id=0` → 0 rows (correct),
  `designation_id=1` → 2 rows, which is the unfiltered total.
- **D6 / ERP-139 — the id list collapses to one employee.**
  `$wpdb->prepare(" AND request.user_id in (%s)", implode(', ', $user_ids))` quotes the whole list, so
  MySQL casts `IN ('21, 22, 23')` to 21. Confirmed in MySQL directly: `22 in ('21, 22, 23')` = 0.
  Observed drawing 0 of 2 approved Engineering leaves.

D5 **masks** D6 through the filter form, so the D6 test reaches the department-only branch through
the URL the view already supports. Fixing D5 alone exposes D6 to every ordinary filter use.

What is green on the calendar: it mounts with its navigation and view controls, the filter offers
every department, an approved leave is drawn and a pending one is not (the whole `status => 1` rule),
and the screen is closed to the employee role.

### D7 — approving a vanished leave request fatals (ERP-140), found 2026-08-19

Surfaced by a race that was **mine**: four workers, and one spec file's `afterAll` cleanup deleted a
request another file was mid-approve on. The race is fixed — leave cleanup is now scoped to the
employees each spec actually touched (`utils/cleanup.ts`, `userIdsFor()`), and the orphan sweep only
runs on an unscoped call.

The fatal it exposed is the product's and needs no harness at all:
`erp_hr_leave_request_update_status()` calls `$request->toArray()` at `functions-leave.php:1674`, one
line BEFORE `if ( empty( $request ) )`, so the `no-request-found` WP_Error is unreachable and a
missing id always throws `Call to a member function toArray() on null`. Proven 3/3 with an id
guaranteed not to exist. Every caller inherits it, including the REST approve/reject endpoints.

**Not carried as a `test.fail()` case, deliberately:** asserting it means deleting a request and then
approving it, which leaves the entitlement ledger half-written for whatever runs next. The
deterministic probe proves it without that cost. This is a coverage gap that is *recorded*, not one
that is hidden. Filed 2026-08-19 with a screenshot: **ERP-140 → erp-pro#955**.

**Not covered, and why:** the year boundary. The view only ever loads the CURRENT calendar year, but
the seeded financial year is 2026 only, so a 2027 request cannot be entitled and the boundary cannot
be exercised without a second financial year. Left uncovered deliberately rather than asserted from
code reading.

### Leave requests — the 3 blocked cases are RESOLVED and green (2026-08-19)

Raising, approving and rejecting a request all run green now, twice consecutively. The block was
**two harness defects of my own**, not a product rule, and the earlier write-up here reached the
wrong conclusion for an instructive reason — recorded rather than quietly deleted.

**1. The suite's own cleanup left orphan rows that the product then honoured.**
A leave request is THREE rows: the header in `wp_erp_hr_leave_requests`, one
`wp_erp_hr_leave_request_details` row per leave day, and a `wp_erp_hr_leave_approval_status` row.
`cleanupLeaveRequests()` deleted only the header. The product's overlap guard
(`erp_hrm_is_leave_recored_exist_between_date`, `functions-leave.php:93`) queries the **details**
table, so every orphan silently refused any later request over the same dates with
*"Existing Leave Record found within selected range!"* — an AJAX error, which `leave.js:728` turns
into a disabled `#submit`.

Why it looked like a per-date product rule: the dates that failed were exactly the dates a previous
failed run had already written details rows for. And the earlier check that "ruled out" overlaps
looked at `wp_erp_hr_leave_requests`, which was genuinely empty — the header had been deleted. The
oracle was reading the wrong table, which is the same class of mistake as the retracted D4 below.
Fixed in `utils/cleanup.ts`: children are deleted before the header, plus an orphan sweep that heals
state an older run left behind.

**2. The list defaults to Pending, so an approved request is not "missing" — it moved.**
`LeaveRequestsListTable.php:369` defaults `status` to `2` (Pending). Once acted on, a request leaves
the default view entirely. `LeaveRequestsPage.goto()` now takes a status view and the approve/reject
cases assert on `all`.

**3. Reject requires a reason; approve does not.**
`tmpl-erp-hr-leave-reject-js-tmp` has one required field, `#erp-hr-leave-reject-reason` ("Reason *").
Submitting it empty renders the error inside `#leave-reject-form-error`, leaves the modal open and
the request Pending — no notice, no dialog, nothing that reads as a failure from outside. The page
object now fills it.

The evidence for all three is the product's own AJAX response, captured live:
`{"success":false,"data":"Existing Leave Record found within selected range!"}`. **Nothing here was
filed as a product defect, because none of it is one.**

What is now proven and green: a request can be raised against an entitlement, approving it consumes
exactly the days requested (balance oracle before/after), rejecting it spends nothing, the list
renders its full column set once a request exists, the form only offers policies the employee is
entitled to, submit stays disabled until the form is complete, a span across the weekend counts only
its working days (Fri→Mon = 2), an employee with no entitlement is offered no policy, and the screen
is closed to the employee role. People (employees, departments,
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

### Accounting — the payment edit path (4 cases: 1 green canary, 3 known-defect guards)

Raising a payment is only half of the cycle. An amount keyed wrong has to be correctable, and until
this pass nothing in the suite had ever tried to correct one. It splits cleanly into two independent
failures, and **fixing either leaves the other standing**:

- **ERP-153 — the screen.** `#/payments/{id}/edit` reuses `RecPaymentCreate.vue`, which asks for
  `GET /invoices/{id}` using the **payment's** voucher number. Invoices and payments draw from one
  voucher sequence, so no invoice can ever carry a payment's number — the lookup fails on every
  payment, on every site. The component shows `Invoice does not exists!` and returns early, which
  also skips the `pay_methods` assignment on the line above it, so even the method list is empty.
  Past that it could not populate anyway: `setDataForEdit()` is called at `:281` and is never defined
  in the file, though every sibling create/edit screen defines its own. And it has no update path at
  all — `editMode` is set and never read, and the only write is `HTTP.post('/payments')`. **6/6.**
- **ERP-152 — the route underneath.** `PUT /accounting/v1/payments/{id}` answers `200` and updates
  the receipt header, but `erp_acct_update_payment()` passes its arguments to
  `erp_acct_update_payment_line_items()` in the wrong order, and fills the invoice number from
  `$item['invoice_id']` — a key the payload does not carry. Both `$wpdb->update()` calls inside then
  match no rows, and the ledger write is an INSERT, so the edit **appends** a second cash row with
  `trn_no = 0` instead of correcting the first. **3/3.**

**The oracle is the Trial Balance, not the database.** Editing an 1,800.00 payment down to 900.00
leaves the product printing `Total $2,700.00 / $1,800.00` on its own report — a trial balance that
does not balance is a statement the product makes about itself, and it needs no arithmetic from me.
Transactions → Sales says the same thing twice over on one screen: `$1,800.00 Received` in the
summary above a payment row reading `$900.00`.

**Why the canary exists.** `test.fail()` reports a PASS on ANY failure, so a guard that breaks for
the wrong reason is indistinguishable from one that proves its defect. "The payment edit screen
opens" runs the same setup and asserts only that the route rendered; it stayed green 6/6 while the
guard beside it failed 6/6, which is what makes the guard's failure attributable.

**A claim I had to withdraw before filing.** The first draft of ERP-153 said saving from the blank
edit screen creates a duplicate payment. The code supports it — the component's only write is a
`POST` — but when I actually re-entered the fields and saved, nothing was created and nothing
changed. The report now says what was observed. The code-read inference was plausible and wrong, and
it would have been indistinguishable from a measurement in the filed text.

**Not covered here, and why:**

- **The bill and purchase edit paths, and draft conversion.** The expense edit path is covered in
  its own section below. ⚠️ **A correction to what this section said when first written:** it cited
  `erp_acct_update_expense()` as `expenses.php:520-537`. Those lines are
  `erp_acct_convert_draft_to_expense()`; the update function is `:361-439`. The draft-conversion path
  does call the ledger inserts without clearing the old rows first and is **still unmeasured**, as
  are the bill-payment and purchase-payment edits. The mis-citation is left visible rather than
  quietly corrected, because it is exactly the kind of specific an Engineer would trust without
  re-checking.
- **`erp_acct_update_data_into_people_trn_details()`** (`transactions.php:1764`) is a single
  `$wpdb->delete()` with **no re-insert**, and invoices, bills and purchases all call it on edit.
  **Read, not measured**; not filed, and it may not be reported as a defect until it has been.
- **Editing a payment upward, and editing one that covers several invoices.** Only the downward
  single-invoice case is asserted. ERP-151 showed multi-invoice payments have their own arithmetic
  fault on the create side, so the edit side of that is likely worse, not better — untested.

### Accounting — expenses and the expense edit path (7 cases: 3 green, 4 known-defect guards)

Expenses had no money oracle at all before this pass — the screens were covered, the double entry
was not. An expense DEBITS its expense account and CREDITS the account it is paid from, and that pair
is what the trial balance and the income statement are built from, so it is the oracle here.

The edit path is why the file exists, and it fails in two unrelated ways:

- **ERP-155 — the screen drops one field.** `ExpensesController::prepare_item_for_response()`
  publishes the transaction date as `date`; `ExpenseCreate.vue:322` reads `trn_date`. Everything else
  loads, so the screen looks correct until **Update** is refused with `Transaction Date is required.`
  — about a field the user never touched. **5/5.**
- **ERP-154 — the edit never reaches the books.** `erp_acct_update_expense()` (`expenses.php:361`)
  updates the header, rewrites the line items, and references `erp_acct_ledger_details` nowhere. It
  also re-inserts the lines **without `trn_no`** while the create path sets it, so after one edit the
  expense has no line items on any screen and the orphaned row is unreachable forever. **5/5.**

**The trial balance still balances, and that is the point.** Unlike ERP-152, where a duplicate ledger
row pushed the debit and credit columns apart, this edit writes nothing at all — so the report is
internally consistent and quietly wrong. `Utilities Dr $600.00` sits beside an Expenses list showing
`$250.00` with no warning anywhere. A test that only asserted "debits equal credits" would pass on
this. The assertion that catches it is the one that compares the ledger against the figure the screen
claims.

**Cash is asserted as a DELTA, never as an absolute.** Every accounting spec deposits into and spends
out of the same Cash ledger, so only the change across one operation is this file's to claim. The
absolute figure would be a hidden dependency on which files ran first.

**Two harness faults this pass exposed, both affecting other specs:**

1. **`pickLineAccount()` counted `.multiselect` from the top of the page** (`index + 1`). That held
   only on the bill form, which has exactly one picker above its lines; the expense form has three,
   so the same index silently selected the funding account instead of the line's expense account. Now
   scoped to `table tbody tr .multiselect`. `bills.spec.ts` re-run green after the change.
2. **`setLineAmount()` had no way to REPLACE an existing value**, which every edit screen needs. The
   first attempt used `Control+a`, which moves the caret on macOS rather than selecting — so 600
   edited to 250 submitted as **250600** and failed a balance check for entirely fictional reasons. It
   now takes `{ replace: true }` and clears with `fill('')`.

**Not covered here, and why:**

- **Editing an expense upward, changing its account, or adding and removing lines.** Only a downward
  amount change on a single line is asserted. Multi-line expenses are where the missing `trn_no` will
  do the most damage, and they are untested.
- **Check-type expenses** (`voucher_type = check`), which take a different branch in both the create
  and update functions.

### Accounting — the bill-payment edit path (4 known-defect guards)

The money-out mirror of the payment edit section, and the worst of the three edit paths measured. It
is also the only one with **no screen behind it**: the SPA router gives `/pay-bills` only `new` and
`:id` children (`router/index.js:592-611`), and a bill-payment row in the transaction list offers
only **Void**. `PUT /accounting/v1/pay-bills/{id}` is reachable by an API consumer and nobody else.

All four guards are **ERP-156** — one function, four ways of being wrong:

1. **The payment never updates.** `erp_acct_update_pay_bill()` writes `bill_no` and `type` into
   `erp_acct_pay_bill`, which has neither column. MySQL rejects the statement, `$wpdb->update()`
   returns `false` **without throwing**, and the surrounding `try` block commits everything after it.
   `debug.log` carries `Unknown column 'bill_no' in 'SET'` on every edit.
2. **The money comes back.** The controller sums `$item['total']`; the product's own pay-bill form
   sends `amount` and never `total`. `array_sum` over an undefined key gives 0, so the cash credit is
   written as `0.00` and the entire payment returns to Cash while the payment record still stands.
3. **A multi-bill payment collapses onto one bill.** Both `$wpdb->update()` calls in the line loop
   use a WHERE keyed on the payment, never the line, so every pass rewrites every row.
4. **The vendor ledger is never written, and the sides are reversed.** Create debits
   `bill_account_details`, update credits it, and `erp_acct_get_bill_due()` is `SUM(debit - credit)` —
   so an edited payment ADDS to the bill it was meant to settle.

**The oracle is the Trial Balance and the Expenses list, not the database.** Editing a 750.00 payment
to 250.00 leaves the product printing `Total $2,550.00 / $2,800.00`, Cash back up by the **full**
750.00, the payment still listed at `$750.00 · Paid`, and the $750.00 bill showing `$1,000.00` due —
more than it was ever raised for. Every one of those is a statement the product makes about itself.

**Severity was argued both ways and written down.** No administrator can reach this today, which is a
real argument for downgrading it. It is filed **Critical** anyway: the endpoint is published, the
corruption is silent (`200 OK`), the cash it invents is unattributable afterwards, and nothing in the
product would surface the damage. Downgrading would be a bet that no integration calls a documented
route.

**One thing here works, and the report says so.** `erp_acct_update_pay_bill_data_into_ledger()`
(`pay-bills.php:445`) is a correct keyed UPDATE. A review that reads "the edit path is broken" and
rewrites the lot would throw away the one piece that is right.

**Not filed separately, and why:** that no edit UI exists for bill payments at all, when invoices and
expenses both have one. A user can Void and re-enter, so it is a gap rather than a defect — but it is
recorded here so the absence is deliberate rather than overlooked, and it is what makes ERP-156
invisible in normal use.

**Not covered here, and why:**

- **The pay-purchase edit path** (`pay-purchases.php`) — same shape, read but **not measured**.
- **Editing a bill payment upward**, and editing one paid from a bank rather than cash (`trn_by = 2`,
  which takes the transaction-charge branch).

### Pro — Custom Field Builder (11 cases, all green, no defects found)

The module that adds fields to the employee, contact, company, customer and vendor forms. Its whole
point is that a field defined in one place appears in another, so the oracle is never the builder
screen — it is the **form the field was added to**. A test that only checked the builder listed what
it was told would pass on a module that writes nothing.

Covered: all five people-type tabs; a field's storage against its own type and no other; the meta key
derived from the label; a dropdown's options round-tripping; persistence across a reload; deletion
removing it from both the builder and the employee form; the page being closed to an employee; the
save endpoint refusing a request with a bad nonce; and a script payload in a field label never
executing.

**Two harness traps, and the first nearly became a false bug report.**

1. **`deleteModel()` opens a native `confirm()`, and Playwright dismisses dialogs by default.** The
   click landed, no error was raised, and nothing was removed — indistinguishable from a dead button.
   I had it half-written up as "the trash button does nothing" before reading the source and finding
   the `confirm()`. What saved it was checking the cause before filing; what would have caught it
   sooner is that a dead button usually leaves *some* trace, and this left none.
2. The trash button lives in a holder the stylesheet keeps at `display: none` until the row is
   **hovered**, so it cannot be clicked without hovering first.

Also worth knowing: deleting a field **persists on its own** — `deleteModel()` calls `sendToServer()`
immediately — while adding one does not, and needs **Save Changes**. The delete case deliberately
does not call `save()`, so it would fail if that ever changed.

**Read and deliberately NOT filed:** `erp_field_builder_handler()` (`Module.php:376`) verifies a
nonce but performs **no capability check**, and builds its option name from unsanitised
`$_REQUEST['people']`. That is a hardening gap rather than a reachable defect: the
`erp-form-builder` nonce is printed only by `enqueue_scripts`, hooked on
`admin_print_styles-{$page_hook}` for a page registered with `manage_options`, so a lower role has no
way to obtain a valid nonce for their own user, and the handler is `wp_ajax_` only (no `nopriv`). The
tier-3 case asserts the refusal path holds. If the nonce ever gets printed on a wider screen this
becomes exploitable, which is why it is written down rather than dropped.

**Not covered, and why:** the Contact, Company, Customer and Vendor forms are asserted only through
the stored definition, not by opening each of those four forms — the Employee form is the one driven
end to end. Field *types* beyond Text and Dropdown (Radio, Checkbox, Date, Url, Email, Password,
number) are offered by the builder and untested on the rendering side. Minor and unfiled: the type
list prints `number` in lower case among nine Title-Case entries.

---

## What "done" will mean

A module is reported as covered only when: its page objects exist, its specs exist, the specs have
**actually run** against a booted site, and they pass. Progress will be reported per module as
`authored / run / green / bugs found`. A spec that cannot pass because of a product defect is filed
as a bug (reproduced twice, screenshot attached) — never deleted, never `skip`ped to make the run
green.
