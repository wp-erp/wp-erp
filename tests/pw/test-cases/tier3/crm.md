# Tier 3 — crm

Negative cases — required-field enforcement, malformed input, injection, and per-role authorization.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**36 cases** (36 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### CRM-T3-001 — `contact[main][first_name]` ("First Name *") is enforced as required

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier3 @crm @validation
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Fill every field EXCEPT `contact[main][first_name]` ("First Name *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### CRM-T3-002 — `contact[main][company]` ("Company Name *") is enforced as required

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier3 @crm @validation
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Fill every field EXCEPT `contact[main][company]` ("Company Name *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### CRM-T3-003 — `contact[main][email]` ("Email *") is enforced as required

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier3 @crm @validation
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Fill every field EXCEPT `contact[main][email]` ("Email *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### CRM-T3-004 — `contact[meta][life_stage]` ("Life Stage *") is enforced as required

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier3 @crm @validation
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Fill every field EXCEPT `contact[meta][life_stage]` ("Life Stage *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### CRM-T3-005 — `contact[meta][contact_owner]` ("Contact Owner *") is enforced as required

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier3 @crm @validation
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Fill every field EXCEPT `contact[meta][contact_owner]` ("Contact Owner *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### CRM-T3-006 — modal form `tmpl-erp-crm-new-contact` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier3 @crm @security
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Set each of 19 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### CRM-T3-007 — `contact[main][email]` ("Email *") rejects a malformed value

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier3 @crm @validation
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Set `contact[main][email]` ("Email *") to a value of the wrong shape (e.g. `not-an-email`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### CRM-T3-008 — `contact[meta][contact_age]` ("Age (years)") rejects a malformed value

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier3 @crm @validation
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Set `contact[meta][contact_age]` ("Age (years)") to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### CRM-T3-009 — `csv_file` ("CSV File *") is enforced as required

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier3 @crm @validation
- **Steps:**
  1. Open modal form `tmpl-erp-crm-import-customer`.
  2. Fill every field EXCEPT `csv_file` ("CSV File *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### CRM-T3-010 — `customeresc_attr_email` ("Enter your email") is enforced as required

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier3 @crm @validation
- **Steps:**
  1. Open modal form `tmpl-erp-make-wp-user`.
  2. Fill every field EXCEPT `customeresc_attr_email` ("Enter your email").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### CRM-T3-011 — modal form `tmpl-erp-make-wp-user` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier3 @crm @security
- **Steps:**
  1. Open modal form `tmpl-erp-make-wp-user`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### CRM-T3-012 — `customeresc_attr_email` ("Enter your email") rejects a malformed value

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier3 @crm @validation
- **Steps:**
  1. Open modal form `tmpl-erp-make-wp-user`.
  2. Set `customeresc_attr_email` ("Enter your email") to a value of the wrong shape (e.g. `not-an-email`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### CRM-T3-013 — `schedule_title` is enforced as required

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier3 @crm @validation
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Fill every field EXCEPT `schedule_title`.
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### CRM-T3-014 — `user_id` is enforced as required

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier3 @crm @validation
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Fill every field EXCEPT `user_id`.
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### CRM-T3-015 — `start_time` ("Start") is enforced as required

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier3 @crm @validation
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Fill every field EXCEPT `start_time` ("Start").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### CRM-T3-016 — `end_date` ("End") is enforced as required

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier3 @crm @validation
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Fill every field EXCEPT `end_date` ("End").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### CRM-T3-017 — `end_time` ("End") is enforced as required

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier3 @crm @validation
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Fill every field EXCEPT `end_time` ("End").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### CRM-T3-018 — `schedule_type` ("Schedule Type") is enforced as required

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier3 @crm @validation
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Fill every field EXCEPT `schedule_type` ("Schedule Type").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### CRM-T3-019 — `user_id` is enforced as required

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier3 @crm @validation
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Fill every field EXCEPT `user_id`.
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### CRM-T3-020 — `log_type` is enforced as required

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier3 @crm @validation
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Fill every field EXCEPT `log_type`.
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### CRM-T3-021 — `log_time` is enforced as required

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier3 @crm @validation
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Fill every field EXCEPT `log_time`.
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### CRM-T3-022 — modal form `tmpl-erp-crm-customer-schedules` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier3 @crm @security
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Set each of 9 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### CRM-T3-023 — `admin.php?page=erp-crm&section=task` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier3 @crm @security
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=task`.
  2. Set each of 2 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### CRM-T3-024 — `admin.php?page=erp-crm&section=deals` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-crm&section=deals`
- **Tags:** @tier3 @crm @security
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=deals`.
  2. Set each of 14 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### CRM-T3-025 — `admin.php?page=erp-crm&section=dashboard` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-crm&section=dashboard`
- **Tags:** @tier3 @crm @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-crm&section=dashboard` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### CRM-T3-026 — `admin.php?page=erp-crm&section=contact` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier3 @crm @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-crm&section=contact` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### CRM-T3-027 — `admin.php?page=erp-crm&section=task` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier3 @crm @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-crm&section=task` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### CRM-T3-028 — `admin.php?page=erp-crm&section=deals` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-crm&section=deals`
- **Tags:** @tier3 @crm @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-crm&section=deals` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### CRM-T3-029 — `admin.php?page=erp-crm&section=integration` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-crm&section=integration`
- **Tags:** @tier3 @crm @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-crm&section=integration` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### CRM-T3-030 — `admin.php?page=erp-crm&section=reports` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-crm&section=reports`
- **Tags:** @tier3 @crm @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-crm&section=reports` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### CRM-T3-031 — `admin.php?page=erp-crm&section=contact&sub-section=companies` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-crm&section=contact&sub-section=companies`
- **Tags:** @tier3 @crm @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-crm&section=contact&sub-section=companies` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### CRM-T3-032 — `admin.php?page=erp-crm&section=contact&sub-section=contact_group` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-crm&section=contact&sub-section=contact_group`
- **Tags:** @tier3 @crm @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-crm&section=contact&sub-section=contact_group` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### CRM-T3-033 — `admin.php?page=erp-crm&section=contact&sub-section=activity` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-crm&section=contact&sub-section=activity`
- **Tags:** @tier3 @crm @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-crm&section=contact&sub-section=activity` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### CRM-T3-034 — `admin.php?page=erp-crm&section=reports&sub-section=growth` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-crm&section=reports&sub-section=growth`
- **Tags:** @tier3 @crm @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-crm&section=reports&sub-section=growth` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### CRM-T3-035 — `admin.php?page=erp-crm&section=dashboard` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-crm&section=dashboard`
- **Tags:** @tier3 @crm @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-crm&section=dashboard` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### CRM-T3-036 — `admin.php?page=erp-crm&section=deals` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-crm&section=deals`
- **Tags:** @tier3 @crm @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-crm&section=deals` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.
