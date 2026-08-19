/**
 * Hand-written business-flow cases — the ones a generator cannot infer, because
 * their oracle is a business rule rather than a form field.
 *
 * Every route, message and rule cited here was read from the running site or
 * from the plugin source (file:line given where it matters). Nothing invented.
 */

const c = (id, title, o) => ({ id, title, ...o });

export const FLOWS = {
    'core-license': {
        t1: [
            c('CORE_LICENSE-F1-001', 'Activating the licence through the admin form enables Pro', {
                surface: 'admin.php?page=erp-license',
                tags: ['@tier1', '@core-license', '@pro', '@flow'],
                pre: 'The licence seat is free (no other site or CI run holds it) and `ERP_LICENSE_KEY`/`ERP_LICENSE_EMAIL` are set.',
                steps: [
                    'Open `admin.php?page=erp-license`.',
                    'Fill `#email` and `#license_key`, choose `scale_yearly` in `#subscription-type`.',
                    'Submit `#submit` ("Save & Activate").',
                ],
                expected: 'The notice reads exactly "License activated successfully." and the screen now offers `button[name="deactivate_license"]`.',
                oracle: '`erp_pro_license_status` stores `license: "valid"`, `users: 100`, `tier: "scale"` (read via `erp-pw/v1/license`).',
            }),
            c('CORE_LICENSE-F1-002', 'Deactivating the licence releases the seat', {
                surface: 'admin.php?page=erp-license',
                tags: ['@tier1', '@core-license', '@pro', '@flow'],
                pre: 'The site holds an active licence.',
                steps: ['Open `admin.php?page=erp-license`.', 'Click "Deactivate License".'],
                expected: 'The notice reads exactly "License deactivated successfully." and the activation form returns.',
                oracle: '`erp_pro_license` and `erp_pro_license_status` are both deleted (see `erp-pro/includes/Admin/Update.php:1382-1400`).',
            }),
        ],
        t2: [
            c('CORE_LICENSE-F2-001', 'The 100th ERP user is accepted', {
                surface: 'admin.php?page=erp-hr&section=people&sub-section=employee',
                tags: ['@tier2', '@core-license', '@pro', '@license-limit', '@serial', '@destructive'],
                pre: 'Licence reports `users: 100`. The site has been seeded to exactly 99 counted users — non-admin holders of `erp_crm_manager`, `erp_crm_agent`, `erp_ac_manager`, `erp_hr_manager` or `employee` (the counted set from `erp-pro/includes/Admin/Update.php:493`). Administrators do not count; non-active employees are subtracted (`:526`).',
                steps: [
                    'Confirm the product\'s own count reads 99 via `erp-pw/v1/user-count` (`count_users`).',
                    'Create one more employee through the Add New Employee modal.',
                ],
                expected: 'The 100th employee is created normally, with no limit warning.',
                oracle: '`count_users` becomes 100 and the employee row exists — the guard at `Update.php:554` allows the create while `count_users() < get_licensed_user()`.',
            }),
            c('CORE_LICENSE-F2-002', 'The 101st ERP user is blocked with the product\'s own message', {
                surface: 'admin.php?page=erp-hr&section=people&sub-section=employee',
                tags: ['@tier2', '@core-license', '@pro', '@license-limit', '@serial', '@destructive'],
                pre: 'Immediately follows CORE_LICENSE-F2-001, with `count_users` at exactly 100.',
                steps: ['Attempt to create one more employee through the Add New Employee modal.'],
                expected: 'The create is refused and the message reads exactly: "Current WP ERP PRO user limit has been exceeded. Please upgrade the number of users in order to add new Employee."',
                oracle: 'UI message (`Update.php:571`) + `count_users` still 100 + no new `wp_erp_hr_employees` row.',
            }),
            c('CORE_LICENSE-F2-003', 'The 101st user is blocked over REST too, not only in the UI', {
                surface: 'POST erp/v1/hrm/employees',
                tags: ['@tier2', '@core-license', '@pro', '@license-limit', '@serial'],
                pre: '`count_users` is at 100.',
                steps: ['POST a valid new employee payload to `erp/v1/hrm/employees`.'],
                expected: 'The request fails with the `user-limit-exceeded` error code, not a 200.',
                oracle: 'REST error code — the guard is registered on `pre_erp_hr_employee_args` (`Update.php:165`) precisely so REST and WP-CLI are covered, not only `is_admin()` requests.',
            }),
            c('CORE_LICENSE-F2-004', 'An over-limit role assignment is reverted and explained', {
                surface: '/wp-admin/user-edit.php',
                tags: ['@tier2', '@core-license', '@pro', '@license-limit', '@serial'],
                pre: '`count_users` is at 100. A WordPress user exists holding no counted ERP role.',
                steps: ['Assign that user a counted role (e.g. `employee`).', 'Reload and inspect the user\'s roles.', 'Read the admin notice.'],
                expected: 'The role assignment is undone — the user is back on their previous role — and a notice explains the limit, quoting Purchased Users and Current Site Users.',
                oracle: '`wp_capabilities` usermeta after the change + the `erp_pro_user_limit_role_notice_<uid>` transient (`Update.php:592-690`), readable via `erp-pw/v1/notices`.',
            }),
            c('CORE_LICENSE-F2-005', 'Terminated employees free a seat', {
                surface: 'admin.php?page=erp-hr&section=people&sub-section=employee',
                tags: ['@tier2', '@core-license', '@pro', '@license-limit', '@serial'],
                pre: '`count_users` is at 100 and creates are blocked.',
                steps: ['Terminate one existing employee.', 'Re-read `erp-pw/v1/user-count`.', 'Attempt an employee create again.'],
                expected: 'The count drops to 99 and the create is allowed again.',
                oracle: '`count_users` before/after — `Update.php:526` subtracts employees whose status is not `active`. Note the count is cached for an hour under `erp-pro-get-employees-count`, so the cache must be flushed for the change to be observable.',
            }),
            c('CORE_LICENSE-F2-006', 'Administrators never consume a seat', {
                surface: '/wp-admin/user-new.php',
                tags: ['@tier2', '@core-license', '@pro', '@license-limit', '@serial'],
                pre: '`count_users` is at 100.',
                steps: ['Create a new user with the `administrator` role.', 'Re-read `erp-pw/v1/user-count`.'],
                expected: 'The count is unchanged at 100 and the administrator is created without a warning.',
                oracle: '`count_users` before/after — administrators are excluded by `role__not_in` in `count_users()` (`Update.php:526-539`).',
            }),
        ],
        t3: [
            c('CORE_LICENSE-F3-001', 'Submitting the licence form with no e-mail is rejected', {
                surface: 'admin.php?page=erp-license',
                tags: ['@tier3', '@core-license', '@pro', '@validation'],
                steps: ['Open the licence screen with no licence active.', 'Clear `#email`, fill `#license_key`, submit.'],
                expected: 'The error "Empty email address" renders and no activation request is sent.',
                oracle: 'UI error text (`Update.php:1351`) + `erp_pro_license_status` unchanged.',
            }),
            c('CORE_LICENSE-F3-002', 'Submitting the licence form with no key is rejected', {
                surface: 'admin.php?page=erp-license',
                tags: ['@tier3', '@core-license', '@pro', '@validation'],
                steps: ['Clear `#license_key`, fill `#email`, submit.'],
                expected: 'The error "Empty license key" renders and no activation request is sent.',
                oracle: 'UI error text (`Update.php:1355`).',
            }),
            c('CORE_LICENSE-F3-003', 'A key whose seats are exhausted reports the activation limit', {
                surface: 'admin.php?page=erp-license',
                tags: ['@tier3', '@core-license', '@pro', '@needs-external'],
                pre: 'A key whose single seat is already held by another site URL.',
                steps: ['Activate that key on this site.'],
                expected: 'The error reads "Your license key has reached its activation limit." and the site stays unlicensed.',
                oracle: 'UI error text — the `no_activations_left` branch at `Update.php:1309`.',
            }),
            c('CORE_LICENSE-F3-004', 'A garbage licence key is reported, not silently accepted', {
                surface: 'admin.php?page=erp-license',
                tags: ['@tier3', '@core-license', '@pro'],
                steps: ['Enter a well-formed but non-existent 32-character key and submit.'],
                expected: 'An error from the licence server renders (e.g. "Invalid license. License doesn\'t exist.") and Pro stays disabled.',
                oracle: 'UI error text + `erp_pro_license_status` is not `valid`.',
            }),
        ],
    },

    'hrm-people': {
        t1: [
            c('HRM_PEOPLE-F1-001', 'Employee lifecycle: hire, edit, terminate, restore', {
                surface: 'admin.php?page=erp-hr&section=people&sub-section=employee',
                tags: ['@tier1', '@hrm-people', '@flow'],
                pre: 'At least one department and one designation exist.',
                steps: [
                    'Create an employee via the "Add New Employee" modal (`tmpl-erp-new-employee`) with all 8 required fields.',
                    'Open the new employee\'s profile and confirm the personal and job-info tabs echo what was saved.',
                    'Change Job Title on the Job Info tab and save.',
                    'Terminate the employee via `tmpl-erp-employment-terminate`.',
                    'Confirm the employee moves out of the active list, then restore them.',
                ],
                expected: 'Each step succeeds; the profile reflects each change; a terminated employee leaves the active list and returns on restore.',
                oracle: '`wp_erp_hr_employees.status` transitions, plus a row in `wp_erp_hr_employee_history` for the job-title change.',
            }),
            c('HRM_PEOPLE-F1-002', 'A new employee gets a WordPress user in the `employee` role', {
                surface: 'admin.php?page=erp-hr&section=people&sub-section=employee',
                tags: ['@tier1', '@hrm-people', '@flow'],
                steps: ['Create an employee with a previously unused e-mail address.'],
                expected: 'A WordPress user is created for that address and holds the `employee` role.',
                oracle: '`wp_users` row + `wp_capabilities` usermeta contains `employee`.',
            }),
            c('HRM_PEOPLE-F1-003', 'Department and designation CRUD, including reassignment', {
                surface: 'admin.php?page=erp-hr&section=people&sub-section=departments',
                tags: ['@tier1', '@hrm-people', '@flow'],
                steps: [
                    'Create a department via `tmpl-erp-new-dept` and a designation via `tmpl-erp-new-desig`.',
                    'Assign an employee to both.',
                    'Rename each and confirm the employee list reflects the new names.',
                    'Delete the department and observe what happens to the assigned employee.',
                ],
                expected: 'Create/rename succeed. Deleting a department in use either blocks with a message or reassigns the employee — whichever it does, it must not leave the employee pointing at a missing department.',
                oracle: '`wp_erp_hr_depts` / `wp_erp_hr_designations` rows + the employee\'s `department`/`designation` columns.',
            }),
        ],
        t2: [
            c('HRM_PEOPLE-F2-001', 'Duplicate e-mail on a second employee is handled', {
                surface: 'admin.php?page=erp-hr&section=people&sub-section=employee',
                tags: ['@tier2', '@hrm-people', '@edge'],
                steps: ['Create an employee with e-mail X.', 'Create a second employee with the same e-mail X.'],
                expected: 'The second create is rejected with a message about the existing user, or reuses the existing WordPress user deliberately — not a 500 and not a duplicate WP user.',
                oracle: 'UI message + `wp_users` count for that address.',
            }),
            c('HRM_PEOPLE-F2-002', 'Employee list filters combine correctly', {
                surface: 'admin.php?page=erp-hr&section=people&sub-section=employee',
                tags: ['@tier2', '@hrm-people', '@edge'],
                steps: ['Filter by `filter_designation`, then add `filter_department`, then add `filter_employment_type`.', 'Apply "Reset".'],
                expected: 'Filters intersect (AND), the row count only narrows, and Reset restores the unfiltered list.',
                oracle: 'Row count per filter combination vs the equivalent DB query.',
            }),
            c('HRM_PEOPLE-F2-003', 'CSV import and export round-trip', {
                surface: 'admin.php?page=erp-hr&section=people&sub-section=employee',
                tags: ['@tier2', '@hrm-people', '@edge'],
                steps: ['Export employees via `tmpl-erp-employee-export-csv`.', 'Import that same file via `tmpl-erp-employee-import-csv`.'],
                expected: 'The import reports how many rows it processed and creates no duplicates for employees that already exist.',
                oracle: 'Employee row count before vs after + the import result message.',
            }),
        ],
    },

    'hrm-leave': {
        t1: [
            c('HRM_LEAVE-F1-001', 'Policy → entitlement → request → approval consumes balance', {
                surface: 'admin.php?page=erp-hr&section=leave',
                tags: ['@tier1', '@hrm-leave', '@flow'],
                pre: 'A financial year exists (Settings → HR → Financial year) and an employee is active.',
                steps: [
                    'Create a leave policy with a known day count.',
                    'Assign an entitlement from that policy to the employee.',
                    'Raise a leave request for N days against it.',
                    'Approve the request.',
                ],
                expected: 'The employee\'s available balance drops by exactly N days and the request shows as approved.',
                oracle: '`wp_erp_hr_leave_requests` status + the entitlement balance in `wp_erp_hr_leave_entitlements`.',
            }),
            c('HRM_LEAVE-F1-002', 'Rejecting a request leaves the balance untouched', {
                surface: 'admin.php?page=erp-hr&section=leave&sub-section=leave-requests',
                tags: ['@tier1', '@hrm-leave', '@flow'],
                steps: ['Raise a request, note the balance, then reject it via `tmpl-erp-hr-leave-reject-js-tmp`.'],
                expected: 'The request reads rejected and the balance is identical to before the request.',
                oracle: 'Balance before/after + request status.',
            }),
            c('HRM_LEAVE-F1-003', 'A holiday is excluded from the leave-day count', {
                surface: 'admin.php?page=erp-hr&section=leave&sub-section=holidays',
                tags: ['@tier1', '@hrm-leave', '@flow'],
                steps: ['Create a holiday on a date inside a working week (`tmpl-erp-hr-holiday-js-tmp`).', 'Raise a leave request spanning that date.'],
                expected: 'The counted leave days exclude the holiday.',
                oracle: 'The day count the request stores vs the calendar span.',
            }),
        ],
        t2: [
            c('HRM_LEAVE-F2-001', 'A request exceeding the entitlement is refused', {
                surface: 'admin.php?page=erp-hr&section=leave',
                tags: ['@tier2', '@hrm-leave', '@edge'],
                steps: ['With a balance of N days, request N+1 days.'],
                expected: 'The request is refused with a balance message; no partial request is stored.',
                oracle: 'UI message + request row count unchanged.',
            }),
            c('HRM_LEAVE-F2-002', 'Overlapping leave requests are detected', {
                surface: 'admin.php?page=erp-hr&section=leave',
                tags: ['@tier2', '@hrm-leave', '@edge'],
                steps: ['Approve a request for 10–12 March.', 'Raise a second request for 11–13 March for the same employee.'],
                expected: 'The overlap is reported rather than silently double-booking the days.',
                oracle: 'UI message + the two requests\' date ranges.',
            }),
            c('HRM_LEAVE-F2-003', 'A request whose end date precedes its start date is refused', {
                surface: 'admin.php?page=erp-hr&section=leave',
                tags: ['@tier2', '@hrm-leave', '@edge'],
                steps: ['Set the start date after the end date and submit.'],
                expected: 'A date-order validation message; no request is created.',
                oracle: 'UI message + row count.',
            }),
        ],
    },

    'hrm-payroll': {
        t1: [
            c('HRM_PAYROLL-F1-001', 'Pay calendar → pay run → payslip totals reconcile', {
                surface: 'admin.php?page=erp-hr&section=payroll&sub-section=calendar',
                tags: ['@tier1', '@hrm-payroll', '@pro', '@flow'],
                pre: 'At least one employee has a pay rate and pay type set.',
                steps: [
                    'Create a pay calendar and assign the employee to it.',
                    'Configure at least one earning and one deduction pay item.',
                    'Generate a pay run for the current period.',
                    'Open the payslip.',
                ],
                expected: 'Net pay equals gross earnings minus total deductions, for every employee in the run.',
                oracle: 'Arithmetic across `wp_erp_hr_payroll_payrun_detail` vs the payslip as rendered.',
            }),
        ],
        t2: [
            c('HRM_PAYROLL-F2-001', 'A pay run over a period with no employees is handled', {
                surface: 'admin.php?page=erp-hr&section=payroll&sub-section=payrun',
                tags: ['@tier2', '@hrm-payroll', '@pro', '@edge'],
                steps: ['Generate a pay run for a calendar with no assigned employees.'],
                expected: 'An empty pay run or a clear message — never a fatal and never a run with phantom rows.',
                oracle: 'Pay-run row count + rendered body.',
            }),
        ],
    },

    'hrm-attendance': {
        t1: [
            c('HRM_ATTENDANCE-F1-001', 'Shift assignment then attendance entry appears in the report', {
                surface: 'admin.php?page=erp-hr&section=attendance',
                tags: ['@tier1', '@hrm-attendance', '@pro', '@flow'],
                steps: ['Create a shift.', 'Assign an employee to it (single, then via Assign Bulk Shift).', 'Record an attendance entry for that employee.', 'Open the date-based attendance report.'],
                expected: 'The entry appears against the right employee and date, and the report totals match the entries recorded.',
                oracle: 'Report figures vs the attendance rows.',
            }),
        ],
    },

    'hrm-assets': {
        t1: [
            c('HRM_ASSETS-F1-001', 'Asset → allotment → return updates availability', {
                surface: 'admin.php?page=erp-hr&section=asset&sub-section=asset',
                tags: ['@tier1', '@hrm-assets', '@pro', '@flow'],
                steps: ['Create an asset category, then an asset with a known total quantity (`tmpl-erp-asset-new`).', 'Allot one unit to an employee (`tmpl-erp-allotment-new`).', 'Confirm Available/Total drops by one.', 'Return the unit (`tmpl-erp-asset-return`).'],
                expected: 'Available count decrements on allotment and increments back on return; it never exceeds the total or goes negative.',
                oracle: 'The Available/Total column vs `wp_erp_hr_assets` / `wp_erp_hr_assets_history`.',
            }),
            c('HRM_ASSETS-F1-002', 'An asset request can be approved and rejected', {
                surface: 'admin.php?page=erp-hr&section=asset&sub-section=asset-request',
                tags: ['@tier1', '@hrm-assets', '@pro', '@flow'],
                steps: ['Raise a request as an employee (`tmpl-erp-hr-emp-request-asset`).', 'Reply/approve it (`tmpl-erp-asset-request-reply`).', 'Raise a second request and reject it (`tmpl-erp-asset-request-reject`).'],
                expected: 'An approved request creates an allotment; a rejected one does not and records the rejection reason.',
                oracle: '`wp_erp_hr_assets_request` status + allotment rows.',
            }),
        ],
        t2: [
            c('HRM_ASSETS-F2-001', 'Allotting more units than exist is refused', {
                surface: 'admin.php?page=erp-hr&section=asset&sub-section=asset-allottment',
                tags: ['@tier2', '@hrm-assets', '@pro', '@edge'],
                steps: ['With an asset of total N, allot N+1 units.'],
                expected: 'Refused with a stock message; availability never goes negative.',
                oracle: 'UI message + the Available/Total figure.',
            }),
        ],
    },

    'hrm-recruitment': {
        t1: [
            c('HRM_RECRUITMENT-F1-001', 'Job opening → candidate → stage → shortlist', {
                surface: 'admin.php?page=erp-hr&section=recruitment&sub-section=add-opening',
                tags: ['@tier1', '@hrm-recruitment', '@pro', '@flow'],
                steps: ['Publish a job opening.', 'Add a candidate against it.', 'Move the candidate through the configured stages.', 'Shortlist the candidate.'],
                expected: 'The candidate appears under the opening, the stage change is recorded, and the shortlist state persists across a reload.',
                oracle: 'REST `erp/v1/hrm/recruitment/candidates/{id}/stage` and `/shortlist` + the list rendering.',
            }),
        ],
    },

    crm: {
        t1: [
            c('CRM-F1-001', 'Contact lifecycle with life stage and owner', {
                surface: 'admin.php?page=erp-crm&section=contact',
                tags: ['@tier1', '@crm', '@flow'],
                steps: ['Create a contact via `tmpl-erp-crm-new-contact` with all 26 fields.', 'Confirm it appears in the list with the captured columns (Contact name, Email Address, Phone, Life stage, Owner, Created At).', 'Change its life stage and owner.', 'Delete it and confirm it leaves the list.'],
                expected: 'Each change is reflected in the list row and the contact detail view.',
                oracle: 'REST `erp/v1/crm/contacts/{id}` + `wp_erp_peoples`.',
            }),
            c('CRM-F1-002', 'Contact group subscribe and unsubscribe', {
                surface: 'admin.php?page=erp-crm&section=contact',
                tags: ['@tier1', '@crm', '@flow'],
                steps: ['Create a contact group.', 'Subscribe a contact to it.', 'Unsubscribe the contact.'],
                expected: 'Group membership count changes by exactly one in each direction.',
                oracle: 'REST `erp/v1/crm/contacts/groups/{id}/subscribes`.',
            }),
            c('CRM-F1-003', 'A deal moves through its pipeline stages and can be won or lost', {
                surface: 'admin.php?page=erp-crm&section=deals',
                tags: ['@tier1', '@crm', '@pro', '@flow'],
                steps: ['Create a deal on the default pipeline.', 'Move it through each stage.', 'Mark it Won.', 'Create a second deal and mark it Lost, choosing a lost reason.'],
                expected: 'Stage history records each move; won/lost states render in their own filters; a lost deal stores its reason.',
                oracle: '`wp_erp_crm_deals_stage_history` + `wp_erp_crm_deals_lost_reasons`.',
            }),
        ],
        t2: [
            c('CRM-F2-001', 'Contact import creates no duplicates on a second run', {
                surface: 'admin.php?page=erp-crm&section=contact',
                tags: ['@tier2', '@crm', '@edge'],
                steps: ['Import contacts from a CSV (`tmpl-erp-crm-import-customer`).', 'Import the identical file again.'],
                expected: 'The second import creates no duplicate contacts and says how many rows it skipped.',
                oracle: 'Contact count after each import.',
            }),
            c('CRM-F2-002', 'Converting a contact to a WordPress user', {
                surface: 'admin.php?page=erp-crm&section=contact',
                tags: ['@tier2', '@crm', '@edge'],
                steps: ['Use `tmpl-erp-make-wp-user` on a contact whose e-mail has no WP user.', 'Repeat on a contact whose e-mail already has one.'],
                expected: 'The first creates a user in the chosen role; the second reports the existing user instead of creating a duplicate.',
                oracle: '`wp_users` rows for both addresses.',
            }),
        ],
    },

    accounting: {
        t1: [
            c('ACCOUNTING-F1-001', 'Invoice → payment settles the customer balance', {
                surface: 'admin.php?page=erp-accounting#/invoices/new',
                tags: ['@tier1', '@accounting', '@flow'],
                pre: 'A customer and at least one product exist; the chart of accounts is initialised.',
                steps: ['Create an invoice with two line items and save it.', 'Receive a payment for the full invoice total.', 'Open the customer.'],
                expected: 'The invoice shows as paid and the customer\'s outstanding balance returns to zero.',
                oracle: 'Invoice status + customer balance + the ledger entries the payment created.',
            }),
            c('ACCOUNTING-F1-002', 'A partial payment leaves the correct remainder', {
                surface: 'admin.php?page=erp-accounting#/payments/new',
                tags: ['@tier1', '@accounting', '@flow'],
                steps: ['Create an invoice for a known total T.', 'Receive a payment of T/2.'],
                expected: 'The invoice shows partially paid with exactly T/2 outstanding — no rounding drift.',
                oracle: 'Invoice due amount vs T/2 to the currency\'s decimal places.',
            }),
            c('ACCOUNTING-F1-003', 'Bill → pay bill settles the vendor balance', {
                surface: 'admin.php?page=erp-accounting#/bills/new',
                tags: ['@tier1', '@accounting', '@flow'],
                steps: ['Create a bill against a vendor.', 'Pay it in full via Pay Bill.'],
                expected: 'The bill shows paid and the vendor balance returns to zero.',
                oracle: 'Bill status + vendor balance + ledger entries.',
            }),
            c('ACCOUNTING-F1-004', 'A manual journal must balance before it posts', {
                surface: 'admin.php?page=erp-accounting#/transactions/journals/new',
                tags: ['@tier1', '@accounting', '@flow'],
                steps: ['Create a journal with equal debit and credit totals and save.'],
                expected: 'The journal posts and appears in the journals list.',
                oracle: 'Journal row + the resulting ledger entries summing to zero.',
            }),
            c('ACCOUNTING-F1-005', 'Opening balance seeds the trial balance', {
                surface: 'admin.php?page=erp-accounting#/opening-balance',
                tags: ['@tier1', '@accounting', '@flow'],
                steps: ['Enter opening balances across the chart of accounts (118 fields captured on this screen) so debits equal credits.', 'Save.', 'Open Reports → Trial Balance.'],
                expected: 'The trial balance reflects the entered opening figures and its debit and credit totals are equal.',
                oracle: 'Trial-balance totals vs the entered opening balances.',
            }),
        ],
        t2: [
            c('ACCOUNTING-F2-001', 'An unbalanced journal is refused', {
                surface: 'admin.php?page=erp-accounting#/transactions/journals/new',
                tags: ['@tier2', '@accounting', '@edge'],
                steps: ['Create a journal whose debits and credits differ by 0.01 and save.'],
                expected: 'The save is refused with a balance message; no journal or ledger entry is written.',
                oracle: 'UI message + journal row count unchanged.',
            }),
            c('ACCOUNTING-F2-002', 'A payment larger than the invoice total is handled', {
                surface: 'admin.php?page=erp-accounting#/payments/new',
                tags: ['@tier2', '@accounting', '@edge'],
                steps: ['Receive a payment greater than the invoice due amount.'],
                expected: 'It is either refused or recorded as an explicit overpayment/credit — never a negative due amount shown as if it were normal.',
                oracle: 'Invoice due amount + customer balance sign.',
            }),
            c('ACCOUNTING-F2-003', 'Tax rate applies to invoice line items correctly', {
                surface: 'admin.php?page=erp-accounting#/settings/taxes/tax-rates',
                tags: ['@tier2', '@accounting', '@edge'],
                steps: ['Create a tax rate of a known percentage.', 'Create an invoice applying it to a line of known value.'],
                expected: 'Line tax equals value × rate, and the invoice total equals subtotal + tax, to the currency\'s decimal places.',
                oracle: 'Arithmetic on the rendered invoice vs the stored line rows.',
            }),
            c('ACCOUNTING-F2-004', 'Changing the currency does not re-denominate existing records', {
                surface: 'admin.php?page=erp-settings#/erp-ac/currency_option',
                tags: ['@tier2', '@accounting', '@edge', '@destructive'],
                pre: 'A DB snapshot has been taken — this changes global state.',
                steps: ['Note an existing invoice total.', 'Change the accounting currency.', 'Re-open that invoice.'],
                expected: 'The stored amount is unchanged; only the displayed symbol changes. No silent conversion.',
                oracle: 'Stored amount before/after + rendered symbol.',
            }),
        ],
    },

    settings: {
        t1: [
            c('SETTINGS-F1-001', 'Every settings section persists across a reload', {
                surface: 'admin.php?page=erp-settings',
                tags: ['@tier1', '@settings', '@flow'],
                steps: ['For each of the 44 captured settings routes, change one field and click "Save Changes".', 'Reload the route.'],
                expected: 'Each changed value is still present after the reload and after a full page refresh.',
                oracle: 'The corresponding `erp_settings_*` option value.',
            }),
        ],
        t3: [
            c('SETTINGS-F3-001', 'Settings save rejects a tampered section slug', {
                surface: 'admin.php?page=erp-settings',
                tags: ['@tier3', '@settings', '@security'],
                steps: ['Post a settings save with a `module`/section value that does not exist.'],
                expected: 'A 4xx with a clear error — never a PHP fatal and never a write to an unexpected option.',
                oracle: 'Response status + `wp_options` diff.',
            }),
        ],
    },

    'core-tools': {
        t2: [
            c('CORE_TOOLS-F2-001', 'The audit log records an ERP change', {
                surface: 'admin.php?page=erp-tools&tab=audit-log',
                tags: ['@tier2', '@core-tools', '@flow'],
                steps: ['Note the audit-log row count.', 'Make one auditable change (edit an employee).', 'Reload the audit log.'],
                expected: 'Exactly one new entry naming the change, the actor and the time.',
                oracle: 'Audit-log row count and content.',
            }),
        ],
        t3: [
            c('CORE_TOOLS-F3-001', 'Danger Zone / System Reset is gated', {
                surface: 'admin.php?page=erp-tools&tab=danger-zone',
                tags: ['@tier3', '@core-tools', '@destructive', '@manual-only'],
                pre: 'NOT automated. This wipes all ERP data; it runs only by hand, only with the user present, and only after a verified DB snapshot.',
                steps: ['Open the Danger Zone tab.', 'Confirm the destructive action requires an explicit confirmation step before it will run.'],
                expected: 'The reset cannot be triggered in one click; it demands confirmation.',
                oracle: 'UI — the confirmation gate exists. The reset itself is NOT executed by the suite.',
            }),
        ],
    },

    'core-modules': {
        t1: [
            c('CORE_MODULES-F1-001', 'A Pro module can be deactivated and reactivated', {
                surface: 'admin.php?page=erp-extensions',
                tags: ['@tier1', '@core-modules', '@pro', '@flow'],
                steps: ['Deactivate a Pro module (e.g. `attendance`) from the Modules screen.', 'Confirm its admin screens disappear from the ERP navigation.', 'Reactivate it.', 'Confirm the screens return and its data is intact.'],
                expected: 'Navigation follows module state in both directions and no data is lost on the round trip.',
                oracle: 'REST `erp_pro/v1/admin/modules` active flags + the rendered ERP nav + the module\'s row counts before/after.',
            }),
        ],
    },
};
