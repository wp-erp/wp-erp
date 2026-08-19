# Tier 2 — core-license

Edge cases — boundaries, optional-field omission, empty states, unusual but legal input.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**6 cases** (0 derived from the harness, 6 hand-written business flows).

## Business flows

#### CORE_LICENSE-F2-001 — The 100th ERP user is accepted

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @core-license @pro @license-limit @serial @destructive
- **Preconditions:** Licence reports `users: 100`. The site has been seeded to exactly 99 counted users — non-admin holders of `erp_crm_manager`, `erp_crm_agent`, `erp_ac_manager`, `erp_hr_manager` or `employee` (the counted set from `erp-pro/includes/Admin/Update.php:493`). Administrators do not count; non-active employees are subtracted (`:526`).
- **Steps:**
  1. Confirm the product's own count reads 99 via `erp-pw/v1/user-count` (`count_users`).
  2. Create one more employee through the Add New Employee modal.
- **Expected:** The 100th employee is created normally, with no limit warning.
- **Oracle:** `count_users` becomes 100 and the employee row exists — the guard at `Update.php:554` allows the create while `count_users() < get_licensed_user()`.

#### CORE_LICENSE-F2-002 — The 101st ERP user is blocked with the product's own message

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @core-license @pro @license-limit @serial @destructive
- **Preconditions:** Immediately follows CORE_LICENSE-F2-001, with `count_users` at exactly 100.
- **Steps:**
  1. Attempt to create one more employee through the Add New Employee modal.
- **Expected:** The create is refused and the message reads exactly: "Current WP ERP PRO user limit has been exceeded. Please upgrade the number of users in order to add new Employee."
- **Oracle:** UI message (`Update.php:571`) + `count_users` still 100 + no new `wp_erp_hr_employees` row.

#### CORE_LICENSE-F2-003 — The 101st user is blocked over REST too, not only in the UI

- **Surface:** `POST erp/v1/hrm/employees`
- **Tags:** @tier2 @core-license @pro @license-limit @serial
- **Preconditions:** `count_users` is at 100.
- **Steps:**
  1. POST a valid new employee payload to `erp/v1/hrm/employees`.
- **Expected:** The request fails with the `user-limit-exceeded` error code, not a 200.
- **Oracle:** REST error code — the guard is registered on `pre_erp_hr_employee_args` (`Update.php:165`) precisely so REST and WP-CLI are covered, not only `is_admin()` requests.

#### CORE_LICENSE-F2-004 — An over-limit role assignment is reverted and explained

- **Surface:** `/wp-admin/user-edit.php`
- **Tags:** @tier2 @core-license @pro @license-limit @serial
- **Preconditions:** `count_users` is at 100. A WordPress user exists holding no counted ERP role.
- **Steps:**
  1. Assign that user a counted role (e.g. `employee`).
  2. Reload and inspect the user's roles.
  3. Read the admin notice.
- **Expected:** The role assignment is undone — the user is back on their previous role — and a notice explains the limit, quoting Purchased Users and Current Site Users.
- **Oracle:** `wp_capabilities` usermeta after the change + the `erp_pro_user_limit_role_notice_<uid>` transient (`Update.php:592-690`), readable via `erp-pw/v1/notices`.

#### CORE_LICENSE-F2-005 — Terminated employees free a seat

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @core-license @pro @license-limit @serial
- **Preconditions:** `count_users` is at 100 and creates are blocked.
- **Steps:**
  1. Terminate one existing employee.
  2. Re-read `erp-pw/v1/user-count`.
  3. Attempt an employee create again.
- **Expected:** The count drops to 99 and the create is allowed again.
- **Oracle:** `count_users` before/after — `Update.php:526` subtracts employees whose status is not `active`. Note the count is cached for an hour under `erp-pro-get-employees-count`, so the cache must be flushed for the change to be observable.

#### CORE_LICENSE-F2-006 — Administrators never consume a seat

- **Surface:** `/wp-admin/user-new.php`
- **Tags:** @tier2 @core-license @pro @license-limit @serial
- **Preconditions:** `count_users` is at 100.
- **Steps:**
  1. Create a new user with the `administrator` role.
  2. Re-read `erp-pw/v1/user-count`.
- **Expected:** The count is unchanged at 100 and the administrator is created without a warning.
- **Oracle:** `count_users` before/after — administrators are excluded by `role__not_in` in `count_users()` (`Update.php:526-539`).
