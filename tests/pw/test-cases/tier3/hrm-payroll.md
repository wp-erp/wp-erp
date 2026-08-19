# Tier 3 — hrm-payroll

Negative cases — required-field enforcement, malformed input, injection, and per-role authorization.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**6 cases** (6 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### HRM_PAYROLL-T3-001 — `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`
- **Tags:** @tier3 @hrm-payroll @security
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_PAYROLL-T3-002 — `admin.php?page=erp-hr&section=payroll&sub-section=dashboard` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=dashboard`
- **Tags:** @tier3 @hrm-payroll @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=payroll&sub-section=dashboard` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_PAYROLL-T3-003 — `admin.php?page=erp-hr&section=payroll&sub-section=calendar` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=calendar`
- **Tags:** @tier3 @hrm-payroll @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=payroll&sub-section=calendar` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_PAYROLL-T3-004 — `admin.php?page=erp-hr&section=payroll&sub-section=payrun` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=payrun`
- **Tags:** @tier3 @hrm-payroll @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=payroll&sub-section=payrun` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_PAYROLL-T3-005 — `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`
- **Tags:** @tier3 @hrm-payroll @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_PAYROLL-T3-006 — `admin.php?page=erp-hr&section=payroll&sub-section=reports` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=reports`
- **Tags:** @tier3 @hrm-payroll @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=payroll&sub-section=reports` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.
