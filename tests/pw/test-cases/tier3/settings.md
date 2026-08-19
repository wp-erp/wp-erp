# Tier 3 — settings

Negative cases — required-field enforcement, malformed input, injection, and per-role authorization.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**24 cases** (23 derived from the harness, 1 hand-written business flows).

## Business flows

#### SETTINGS-F3-001 — Settings save rejects a tampered section slug

- **Surface:** `admin.php?page=erp-settings`
- **Tags:** @tier3 @settings @security
- **Steps:**
  1. Post a settings save with a `module`/section value that does not exist.
- **Expected:** A 4xx with a clear error — never a PHP fatal and never a write to an unexpected option.
- **Oracle:** Response status + `wp_options` diff.

## Screen & field coverage

#### SETTINGS-T3-001 — `admin.php?page=erp-settings#/general` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-settings#/general`
- **Tags:** @tier3 @settings @security
- **Steps:**
  1. Open `admin.php?page=erp-settings#/general`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### SETTINGS-T3-002 — `admin.php?page=erp-settings#/erp-email` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-settings#/erp-email`
- **Tags:** @tier3 @settings @security
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### SETTINGS-T3-003 — `admin.php?page=erp-settings#/erp-hr/financial` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-settings#/erp-hr/financial`
- **Tags:** @tier3 @settings @security
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/financial`.
  2. Set each of 2 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### SETTINGS-T3-004 — `admin.php?page=erp-settings#/erp-hr/attendance` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-settings#/erp-hr/attendance`
- **Tags:** @tier3 @settings @security
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/attendance`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### SETTINGS-T3-005 — `admin.php?page=erp-settings#/erp-crm/subscription` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-settings#/erp-crm/subscription`
- **Tags:** @tier3 @settings @security
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/subscription`.
  2. Set each of 4 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### SETTINGS-T3-006 — `admin.php?page=erp-settings#/erp-ac/opening_balance` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-settings#/erp-ac/opening_balance`
- **Tags:** @tier3 @settings @security
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/opening_balance`.
  2. Set each of 2 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### SETTINGS-T3-007 — `admin.php?page=erp-settings#/erp-email/general` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-settings#/erp-email/general`
- **Tags:** @tier3 @settings @security
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/general`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### SETTINGS-T3-008 — `admin.php?page=erp-settings#/erp-email/email_connect` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-settings#/erp-email/email_connect`
- **Tags:** @tier3 @settings @security
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/email_connect`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### SETTINGS-T3-009 — `erp_wpmail_test_email` ("Test Mail") rejects a malformed value

- **Surface:** `admin.php?page=erp-settings#/erp-email/email_connect`
- **Tags:** @tier3 @settings @validation
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/email_connect`.
  2. Set `erp_wpmail_test_email` ("Test Mail") to a value of the wrong shape (e.g. `not-an-email`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### SETTINGS-T3-010 — `admin.php?page=erp-settings#/erp-email/templates` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-settings#/erp-email/templates`
- **Tags:** @tier3 @settings @security
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/templates`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### SETTINGS-T3-011 — `href` rejects a malformed value

- **Surface:** `admin.php?page=erp-settings#/erp-email/templates`
- **Tags:** @tier3 @settings @validation
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/templates`.
  2. Set `href` to a value of the wrong shape (e.g. `not-an-url`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### SETTINGS-T3-012 — `admin.php?page=erp-settings#/general` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-settings#/general`
- **Tags:** @tier3 @settings @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-settings#/general` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### SETTINGS-T3-013 — `admin.php?page=erp-settings#/erp-hr` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-settings#/erp-hr`
- **Tags:** @tier3 @settings @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-settings#/erp-hr` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### SETTINGS-T3-014 — `admin.php?page=erp-settings#/erp-crm` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-settings#/erp-crm`
- **Tags:** @tier3 @settings @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-settings#/erp-crm` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### SETTINGS-T3-015 — `admin.php?page=erp-settings#/erp-woocommerce` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce`
- **Tags:** @tier3 @settings @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-settings#/erp-woocommerce` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### SETTINGS-T3-016 — `admin.php?page=erp-settings#/erp-ac` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-settings#/erp-ac`
- **Tags:** @tier3 @settings @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-settings#/erp-ac` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### SETTINGS-T3-017 — `admin.php?page=erp-settings#/erp-email` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-settings#/erp-email`
- **Tags:** @tier3 @settings @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-settings#/erp-email` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### SETTINGS-T3-018 — `admin.php?page=erp-settings#/erp-integration` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-settings#/erp-integration`
- **Tags:** @tier3 @settings @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-settings#/erp-integration` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### SETTINGS-T3-019 — `admin.php?page=erp-settings#/erp-hr/workdays` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-settings#/erp-hr/workdays`
- **Tags:** @tier3 @settings @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-settings#/erp-hr/workdays` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### SETTINGS-T3-020 — `admin.php?page=erp-settings#/erp-hr/leave` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-settings#/erp-hr/leave`
- **Tags:** @tier3 @settings @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-settings#/erp-hr/leave` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### SETTINGS-T3-021 — `admin.php?page=erp-settings#/erp-hr/financial` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-settings#/erp-hr/financial`
- **Tags:** @tier3 @settings @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-settings#/erp-hr/financial` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### SETTINGS-T3-022 — `admin.php?page=erp-settings#/erp-hr/remote_work` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-settings#/erp-hr/remote_work`
- **Tags:** @tier3 @settings @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-settings#/erp-hr/remote_work` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### SETTINGS-T3-023 — `admin.php?page=erp-settings#/erp-hr/miscellaneous` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-settings#/erp-hr/miscellaneous`
- **Tags:** @tier3 @settings @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-settings#/erp-hr/miscellaneous` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.
