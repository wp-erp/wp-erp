# Skip justifications

Every `test.skip` in this suite, audited 2026-08-06 against `develop` @ `786189f8d`.

A skip is a test that did **not** run. It proves nothing, and the coverage report
(`utils/generateCoverageReport.js`) counts it as **uncovered** for exactly that reason.
This file exists so that each one is a decision on record rather than a habit.

**Measured on the audit date**

| Where                                                                                                                      | Skips |
| -------------------------------------------------------------------------------------------------------------------------- | ----- |
| `test.skip` call sites in the spec tree                                                                                    | 149   |
| Actually skipped in a full local **pro** run (388 tests)                                                                   | 4     |
| Actually skipped in CI run [31075731631](https://github.com/wp-erp/wp-erp/actions/runs/31075731631) (934 tests, e2e + api) | 18    |

Call sites vastly outnumber real skips because most are _guards_ that only fire when a
prerequisite is genuinely missing.

---

## Class A — chained-fixture guards (~130 sites)

`test.skip(!dealId, 'needs the deal created earlier')` and its relatives:
`!empUserId`, `!jobId`, `!folder.dirId`, `!lifecycleApplicantId`, `!customerId ||
!invoiceVoucherNo`, `!ids.CONTACT_ID`, …

**Why they exist.** Lifecycle specs are ordered: step 2 cannot assert on a deal that
step 1 never created. Without the guard, a seed failure produces a cascade of
identical downstream failures that bury the one real error.

**Verdict: keep — with a caveat that is not hypothetical.** A guard turns "the seed
broke" into a quiet skip. In the pro run above only 4 tests skipped, so the seeds are
working today; if that number climbs, the cause is a broken seed, not a flaky test.

**Rule:** never add a guard to make a red test green. A guard is only correct when the
prerequisite is legitimately optional (a pro module the environment lacks); if the
prerequisite is supposed to exist, let it fail.

## Class B — pro module not active in this environment (~6 sites)

`helpers.proModuleActive('<id>')`, driven by `ERP_PRO_ACTIVE_MODULES` which
`_site.setup.ts` publishes from the live site.

**Why.** `activate_modules()` cannot activate `woocommerce` or `awesome_support`
without their host plugin, so 21 of 23 pro modules come up on a clean site. Specs for
the other 2 skip rather than fail.

**Verdict: keep.** The alternative is a permanently red suite for a missing third-party
plugin. Note the ceiling honestly: **WooCommerce and Awesome Support integrations are
not covered by any automated run.**

## Class C — needs external third-party credentials (3 sites, unconditional)

| Test                                                 | File                                                      | Why                                                        |
| ---------------------------------------------------- | --------------------------------------------------------- | ---------------------------------------------------------- |
| `Mailchimp: valid api_key persists (success branch)` | `tests/e2e/integrations/integrations.connect.spec.ts:368` | `is_connected()` calls the live Mailchimp API              |
| `HubSpot: valid api_key persists (success branch)`   | `tests/e2e/integrations/integrations.connect.spec.ts:402` | same, live HubSpot API                                     |
| `Salesforce: complete external OAuth connect`        | `tests/e2e/integrations/integrations.connect.spec.ts:425` | OAuth redirect to `login.salesforce.com` / `api.wperp.com` |

**Verdict: keep, and they are the honest kind of skip** — the _rejection_ branch of each
integration (invalid key rejected, option not written) IS asserted right above the skip;
only the success branch needs a real account. **Gap stated: no automated run proves a
valid third-party key connects.** Closing it needs sandbox credentials in CI secrets.

## Class D — environment-dependent oracle (~10 sites)

`test.skip(true, 'DB unavailable for the … oracle')`, `'no inventory rows to reconcile
…'`, `'announcement create unavailable in this environment'`.

**Why.** These sit inside an `if` that already checked the precondition, so the literal
`true` is a branch, not an unconditional skip. The REST assertion has already run; only
the deeper DB cross-check is dropped.

**Verdict: keep, weakest class.** Each one silently downgrades a two-layer assertion
(REST + DB) to one layer. They do not fire in CI today. If one starts firing regularly,
fix the environment rather than accept the thinner oracle.

---

## Adding a skip

1. Say **why** in the skip message — the message is the audit trail.
2. Add the entry here, in the right class.
3. If it is a product defect, do not skip: file the bug and mark the test
   `test.fail()` with the issue link, so it still runs and turns red the day it starts
   passing.
