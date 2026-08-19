# Tier 3 — core-company

Negative cases — required-field enforcement, malformed input, injection, and per-role authorization.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**7 cases** (7 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### CORE_COMPANY-T3-001 — `address[country]` ("Country") is enforced as required

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier3 @core-company @validation
- **Steps:**
  1. Open `admin.php?page=erp-company&action=edit`.
  2. Fill every field EXCEPT `address[country]` ("Country").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### CORE_COMPANY-T3-002 — `admin.php?page=erp-company&action=edit` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier3 @core-company @security
- **Steps:**
  1. Open `admin.php?page=erp-company&action=edit`.
  2. Set each of 9 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### CORE_COMPANY-T3-003 — `website` ("Website") rejects a malformed value

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier3 @core-company @validation
- **Steps:**
  1. Open `admin.php?page=erp-company&action=edit`.
  2. Set `website` ("Website") to a value of the wrong shape (e.g. `not-an-url`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### CORE_COMPANY-T3-004 — `admin.php?page=erp-company` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-company`
- **Tags:** @tier3 @core-company @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-company` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### CORE_COMPANY-T3-005 — `admin.php?page=erp-company&action=edit` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier3 @core-company @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-company&action=edit` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### CORE_COMPANY-T3-006 — `admin.php?page=erp-company` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-company`
- **Tags:** @tier3 @core-company @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-company` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### CORE_COMPANY-T3-007 — `http://localhost:8888/wp-admin/admin.php?page=erp-company` is closed to roles that do not own it

- **Surface:** `http://localhost:8888/wp-admin/admin.php?page=erp-company`
- **Tags:** @tier3 @core-company @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `http://localhost:8888/wp-admin/admin.php?page=erp-company` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.
