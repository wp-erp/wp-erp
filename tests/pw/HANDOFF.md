# WP ERP Playwright suite — HANDOFF

**Last updated:** 2026-08-19
**Branch:** `test/pw-suite-rebuild` (repo: `wp-content/plugins/wp-erp`)
**Suite root:** `wp-content/plugins/wp-erp/tests/pw`

Written so this work can be resumed in a fresh context with nothing carried over in conversation.
Read this, then `test-cases/COVERAGE.md` (the honest ledger), then `harness/report/INDEX.md`.

---

## Current state — verified, not remembered

Last full run: **203 passed, 3 skipped, 0 failed** in `e2e_tests`, twice consecutively (the setup
projects add ~17 more).
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

The 3 skips are deliberate and recorded in `COVERAGE.md`: licence cases that need the site to be
UNlicensed (the activation form is not rendered while licensed).

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

Bug files: `~/.claude/skills/wp-erp-qa/bugs/2026-08-18/` and `2026-08-19/`. Register: `bugs/REGISTER.md`, **next ID `ERP-146`**. Filing needs the user's explicit go-ahead per issue; identity gate is `gh auth status`
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

## RESUME HERE — the next pass is the single-deal page

CRM Deals is authored end to end **for its list-level screens** (board, dashboard, activities,
settings agreement, authorization). The biggest surface in the module is still untouched, and
everything below was gathered while writing this pass so it does not start cold:

- **Screen:** `admin.php?page=erp-crm&section=deals&sub-section=all-deals&action=view-deal&id=<N>`.
  It loads TinyMCE plus the email templates and shortcodes, on top of everything the board loads.
- **What lives there:** notes, participants, agents, competitors, attachments, the email composer,
  the changelog and the activity modal. **Four of the five prior Deals bugs are on this page** —
  ERP-043 (stage history), ERP-044 (`delete_competitor` fatals for a CRM agent), ERP-045
  (cross-pipeline stage transfer), ERP-066 (attachment row for a non-existent media id). Re-verify
  each against 1.7.0 before writing a new case near it.
- **A deal must be created first** — nothing is seeded. `DealsPage.createDeal()` does it through the
  modal; `DealsPage.callAjax('erp_deals_save_deal', …)` does it faster, but a manager MUST pass
  `owner_id` or the insert fails with the generic "Could not save the deal. Please try again."
- **Drag-and-drop between stages is the other gap** — the board uses `jquery-ui-sortable`, and a stage
  move is what writes the `out` side of stage history. That is where ERP-043's one-open-row invariant
  should be re-checked, and it needs a SECOND pipeline to expose the cross-pipeline half.
- **Won / Lost / Reopen** needs a lost reason created through ERP Settings first —
  `erp_crm_deals_lost_reasons` is seeded empty.
- **Cleanup:** `cleanupDeals(marker)` removes the deal plus all eight child tables by deal id. It
  matches on the TITLE, so remember trap 34 — pick the contact before typing the title.

## Next steps, in order

1. **HRM is authored end to end.** Remaining gaps inside it are listed per-section in
   `COVERAGE.md` ("not covered, and why") — chiefly the payroll pay RUN (blocked by erp-pro#956),
   attendance check-in/out logging, the asset allotment/return chain, the recruitment wizard beyond
   step one, and document folder/share operations. The asset allotment/return chain is also where
   the two unguarded asset return actions should be probed for privilege escalation (see COVERAGE).
2. Payroll pay RUN — **blocked by ERP-141 / erp-pro#956** on this MariaDB environment; the schema is
   broken, so no pay-run assertions were written. Revisit once it is fixed, or run that pass against
   MySQL. Pay items/categories and payroll settings are still open regardless.
3. **CRM in progress** — Contacts, Companies, Activities, Tasks and Deals (list-level) done.
   Remaining: the single-deal page and stage drag-and-drop (see RESUME HERE), contact
   groups/subscribers, schedules, CRM reports, and the agent/manager visibility rules — a security
   pass of its own, and the one that answers whether a CRM agent holding the deals nonce can act on
   another agent's deals (trap 31). Then Accounting, then the remaining Pro modules.
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
