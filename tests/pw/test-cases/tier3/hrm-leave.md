# Tier 3 — hrm-leave

Negative cases — required-field enforcement, malformed input, injection, and per-role authorization.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**17 cases** (17 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### HRM_LEAVE-T3-001 — modal form `tmpl-erp-hr-leave-approve-js-tmp` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier3 @hrm-leave @security
- **Steps:**
  1. Open modal form `tmpl-erp-hr-leave-approve-js-tmp`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_LEAVE-T3-002 — `reason` ("Reason *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier3 @hrm-leave @validation
- **Steps:**
  1. Open modal form `tmpl-erp-hr-leave-reject-js-tmp`.
  2. Fill every field EXCEPT `reason` ("Reason *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_LEAVE-T3-003 — modal form `tmpl-erp-hr-leave-reject-js-tmp` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier3 @hrm-leave @security
- **Steps:**
  1. Open modal form `tmpl-erp-hr-leave-reject-js-tmp`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_LEAVE-T3-004 — `title` ("Holiday Name *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`
- **Tags:** @tier3 @hrm-leave @validation
- **Steps:**
  1. Open modal form `tmpl-erp-hr-holiday-js-tmp`.
  2. Fill every field EXCEPT `title` ("Holiday Name *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_LEAVE-T3-005 — `start_date` ("Start Date *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`
- **Tags:** @tier3 @hrm-leave @validation
- **Steps:**
  1. Open modal form `tmpl-erp-hr-holiday-js-tmp`.
  2. Fill every field EXCEPT `start_date` ("Start Date *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_LEAVE-T3-006 — modal form `tmpl-erp-hr-holiday-js-tmp` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`
- **Tags:** @tier3 @hrm-leave @security
- **Steps:**
  1. Open modal form `tmpl-erp-hr-holiday-js-tmp`.
  2. Set each of 4 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_LEAVE-T3-007 — `admin.php?page=erp-hr&section=leave` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=leave`
- **Tags:** @tier3 @hrm-leave @security
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_LEAVE-T3-008 — `admin.php?page=erp-hr&section=leave&sub-section=leave-requests` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier3 @hrm-leave @security
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_LEAVE-T3-009 — `admin.php?page=erp-hr&section=leave&sub-section=holidays` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`
- **Tags:** @tier3 @hrm-leave @security
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=holidays`.
  2. Set each of 2 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_LEAVE-T3-010 — `admin.php?page=erp-hr&section=leave&sub-section=leave-requests` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier3 @hrm-leave @security
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_LEAVE-T3-011 — `admin.php?page=erp-hr&section=leave` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=leave`
- **Tags:** @tier3 @hrm-leave @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=leave` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_LEAVE-T3-012 — `admin.php?page=erp-hr&section=leave&sub-section=leave-requests` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier3 @hrm-leave @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_LEAVE-T3-013 — `admin.php?page=erp-hr&section=leave&sub-section=leave-entitlements` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-entitlements`
- **Tags:** @tier3 @hrm-leave @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=leave&sub-section=leave-entitlements` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_LEAVE-T3-014 — `admin.php?page=erp-hr&section=leave&sub-section=holidays` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`
- **Tags:** @tier3 @hrm-leave @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=leave&sub-section=holidays` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_LEAVE-T3-015 — `admin.php?page=erp-hr&section=leave&sub-section=policies` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=policies`
- **Tags:** @tier3 @hrm-leave @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=leave&sub-section=policies` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_LEAVE-T3-016 — `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar`
- **Tags:** @tier3 @hrm-leave @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_LEAVE-T3-017 — `admin.php?page=erp-hr&section=leave&sub-section=leave-requests` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier3 @hrm-leave @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.
