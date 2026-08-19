# Tier 3 — core-tools

Negative cases — required-field enforcement, malformed input, injection, and per-role authorization.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**10 cases** (9 derived from the harness, 1 hand-written business flows).

## Business flows

#### CORE_TOOLS-F3-001 — Danger Zone / System Reset is gated

- **Surface:** `admin.php?page=erp-tools&tab=danger-zone`
- **Tags:** @tier3 @core-tools @destructive @manual-only
- **Preconditions:** NOT automated. This wipes all ERP data; it runs only by hand, only with the user present, and only after a verified DB snapshot.
- **Steps:**
  1. Open the Danger Zone tab.
  2. Confirm the destructive action requires an explicit confirmation step before it will run.
- **Expected:** The reset cannot be triggered in one click; it demands confirmation.
- **Oracle:** UI — the confirmation gate exists. The reset itself is NOT executed by the suite.

## Screen & field coverage

#### CORE_TOOLS-T3-001 — `admin.php?page=erp-tools&tab=misc` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-tools&tab=misc`
- **Tags:** @tier3 @core-tools @security
- **Steps:**
  1. Open `admin.php?page=erp-tools&tab=misc`.
  2. Set each of 3 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### CORE_TOOLS-T3-002 — `to` ("To *") rejects a malformed value

- **Surface:** `admin.php?page=erp-tools&tab=misc`
- **Tags:** @tier3 @core-tools @validation
- **Steps:**
  1. Open `admin.php?page=erp-tools&tab=misc`.
  2. Set `to` ("To *") to a value of the wrong shape (e.g. `not-an-email`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### CORE_TOOLS-T3-003 — `erp_reset_confirmation` is enforced as required

- **Surface:** `admin.php?page=erp-tools&tab=danger-zone`
- **Tags:** @tier3 @core-tools @validation
- **Steps:**
  1. Open `admin.php?page=erp-tools&tab=danger-zone`.
  2. Fill every field EXCEPT `erp_reset_confirmation`.
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### CORE_TOOLS-T3-004 — `admin.php?page=erp-tools&tab=danger-zone` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-tools&tab=danger-zone`
- **Tags:** @tier3 @core-tools @security
- **Steps:**
  1. Open `admin.php?page=erp-tools&tab=danger-zone`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### CORE_TOOLS-T3-005 — `admin.php?page=erp-tools` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier3 @core-tools @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-tools` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### CORE_TOOLS-T3-006 — `admin.php?page=erp-tools&tab=misc` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-tools&tab=misc`
- **Tags:** @tier3 @core-tools @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-tools&tab=misc` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### CORE_TOOLS-T3-007 — `admin.php?page=erp-tools&tab=status` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-tools&tab=status`
- **Tags:** @tier3 @core-tools @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-tools&tab=status` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### CORE_TOOLS-T3-008 — `admin.php?page=erp-tools&tab=audit-log` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-tools&tab=audit-log`
- **Tags:** @tier3 @core-tools @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-tools&tab=audit-log` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### CORE_TOOLS-T3-009 — `admin.php?page=erp-tools&tab=danger-zone` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-tools&tab=danger-zone`
- **Tags:** @tier3 @core-tools @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-tools&tab=danger-zone` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.
