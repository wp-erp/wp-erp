# Tier 3 — hrm-training

Negative cases — required-field enforcement, malformed input, injection, and per-role authorization.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**14 cases** (14 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### HRM_TRAINING-T3-001 — `training-id` ("Training *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-training @validation
- **Steps:**
  1. Open modal form `tmpl-employee-assign-new-training`.
  2. Fill every field EXCEPT `training-id` ("Training *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_TRAINING-T3-002 — `training-completed-date` ("Completed Date *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-training @validation
- **Steps:**
  1. Open modal form `tmpl-employee-assign-new-training`.
  2. Fill every field EXCEPT `training-completed-date` ("Completed Date *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_TRAINING-T3-003 — `training-trainer` ("Trainer's name *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-training @validation
- **Steps:**
  1. Open modal form `tmpl-employee-assign-new-training`.
  2. Fill every field EXCEPT `training-trainer` ("Trainer's name *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_TRAINING-T3-004 — `training-rate` ("Rating *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-training @validation
- **Steps:**
  1. Open modal form `tmpl-employee-assign-new-training`.
  2. Fill every field EXCEPT `training-rate` ("Rating *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_TRAINING-T3-005 — modal form `tmpl-employee-assign-new-training` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-training @security
- **Steps:**
  1. Open modal form `tmpl-employee-assign-new-training`.
  2. Set each of 7 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_TRAINING-T3-006 — `training-rate` ("Rating *") rejects a malformed value

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-training @validation
- **Steps:**
  1. Open modal form `tmpl-employee-assign-new-training`.
  2. Set `training-rate` ("Rating *") to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### HRM_TRAINING-T3-007 — `mm` ("Month 01-Jan 02-Feb 03-Mar 04-Apr 05-May 06-Jun 07-Jul 08-Aug 09-Sep 10-Oct 11-Nov 12-Dec") is enforced as required

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier3 @hrm-training @validation
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Fill every field EXCEPT `mm` ("Month 01-Jan 02-Feb 03-Mar 04-Apr 05-May 06-Jun 07-Jul 08-Aug 09-Sep 10-Oct 11-Nov 12-Dec").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_TRAINING-T3-008 — `jj` ("Day") is enforced as required

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier3 @hrm-training @validation
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Fill every field EXCEPT `jj` ("Day").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_TRAINING-T3-009 — `aa` ("Year") is enforced as required

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier3 @hrm-training @validation
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Fill every field EXCEPT `aa` ("Year").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_TRAINING-T3-010 — `hh` ("Hour") is enforced as required

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier3 @hrm-training @validation
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Fill every field EXCEPT `hh` ("Hour").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_TRAINING-T3-011 — `mn` ("Minute") is enforced as required

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier3 @hrm-training @validation
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Fill every field EXCEPT `mn` ("Minute").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_TRAINING-T3-012 — `post-new.php?post_type=erp_hr_training` neutralises script and HTML in its text fields

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier3 @hrm-training @security
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Set each of 10 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_TRAINING-T3-013 — `edit.php?post_type=erp_hr_training` is closed to roles that do not own it

- **Surface:** `edit.php?post_type=erp_hr_training`
- **Tags:** @tier3 @hrm-training @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `edit.php?post_type=erp_hr_training` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_TRAINING-T3-014 — `post-new.php?post_type=erp_hr_training` is closed to roles that do not own it

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier3 @hrm-training @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `post-new.php?post_type=erp_hr_training` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.
