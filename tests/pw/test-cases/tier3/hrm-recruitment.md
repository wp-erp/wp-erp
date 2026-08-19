# Tier 3 — hrm-recruitment

Negative cases — required-field enforcement, malformed input, injection, and per-role authorization.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**28 cases** (28 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### HRM_RECRUITMENT-T3-001 — `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`
- **Tags:** @tier3 @hrm-recruitment @security
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`.
  2. Set each of 5 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_RECRUITMENT-T3-002 — `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`
- **Tags:** @tier3 @hrm-recruitment @security
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_RECRUITMENT-T3-003 — `job_title` ("Job Title *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Tags:** @tier3 @hrm-recruitment @validation
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`.
  2. Fill every field EXCEPT `job_title` ("Job Title *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_RECRUITMENT-T3-004 — `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Tags:** @tier3 @hrm-recruitment @security
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`.
  2. Set each of 10 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_RECRUITMENT-T3-005 — `post-new.php?post_type=erp_hr_questionnaire` neutralises script and HTML in its text fields

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier3 @hrm-recruitment @security
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. Set each of 7 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_RECRUITMENT-T3-006 — `weight_skills_match` ("Skills") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier3 @hrm-recruitment @validation
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Fill every field EXCEPT `weight_skills_match` ("Skills").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_RECRUITMENT-T3-007 — `weight_experience_match` ("Experience") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier3 @hrm-recruitment @validation
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Fill every field EXCEPT `weight_experience_match` ("Experience").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_RECRUITMENT-T3-008 — `weight_education_fit` ("Education") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier3 @hrm-recruitment @validation
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Fill every field EXCEPT `weight_education_fit` ("Education").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_RECRUITMENT-T3-009 — `weight_certification` ("Certifications") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier3 @hrm-recruitment @validation
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Fill every field EXCEPT `weight_certification` ("Certifications").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_RECRUITMENT-T3-010 — `weight_soft_skills` ("Soft Skills") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier3 @hrm-recruitment @validation
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Fill every field EXCEPT `weight_soft_skills` ("Soft Skills").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_RECRUITMENT-T3-011 — `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier3 @hrm-recruitment @security
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Set each of 9 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_RECRUITMENT-T3-012 — `weight_skills_match` ("Skills") rejects a malformed value

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier3 @hrm-recruitment @validation
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Set `weight_skills_match` ("Skills") to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### HRM_RECRUITMENT-T3-013 — `weight_experience_match` ("Experience") rejects a malformed value

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier3 @hrm-recruitment @validation
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Set `weight_experience_match` ("Experience") to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### HRM_RECRUITMENT-T3-014 — `weight_education_fit` ("Education") rejects a malformed value

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier3 @hrm-recruitment @validation
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Set `weight_education_fit` ("Education") to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### HRM_RECRUITMENT-T3-015 — `weight_certification` ("Certifications") rejects a malformed value

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier3 @hrm-recruitment @validation
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Set `weight_certification` ("Certifications") to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### HRM_RECRUITMENT-T3-016 — `weight_soft_skills` ("Soft Skills") rejects a malformed value

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier3 @hrm-recruitment @validation
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Set `weight_soft_skills` ("Soft Skills") to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### HRM_RECRUITMENT-T3-017 — `admin.php?page=erp-hr&section=recruitment&sub-section=job-opening` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=job-opening`
- **Tags:** @tier3 @hrm-recruitment @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=recruitment&sub-section=job-opening` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_RECRUITMENT-T3-018 — `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`
- **Tags:** @tier3 @hrm-recruitment @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_RECRUITMENT-T3-019 — `admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list`
- **Tags:** @tier3 @hrm-recruitment @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_RECRUITMENT-T3-020 — `admin.php?page=erp-hr&section=recruitment&sub-section=add_candidate` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=add_candidate`
- **Tags:** @tier3 @hrm-recruitment @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=recruitment&sub-section=add_candidate` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_RECRUITMENT-T3-021 — `admin.php?page=erp-hr&section=recruitment&sub-section=stages` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=stages`
- **Tags:** @tier3 @hrm-recruitment @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=recruitment&sub-section=stages` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_RECRUITMENT-T3-022 — `admin.php?page=erp-hr&section=recruitment&sub-section=reports` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=reports`
- **Tags:** @tier3 @hrm-recruitment @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=recruitment&sub-section=reports` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_RECRUITMENT-T3-023 — `edit.php?post_type=erp_hr_questionnaire` is closed to roles that do not own it

- **Surface:** `edit.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier3 @hrm-recruitment @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `edit.php?post_type=erp_hr_questionnaire` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_RECRUITMENT-T3-024 — `admin.php?page=erp-hr&section=recruitment&sub-section=todo-calendar` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=todo-calendar`
- **Tags:** @tier3 @hrm-recruitment @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=recruitment&sub-section=todo-calendar` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_RECRUITMENT-T3-025 — `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`
- **Tags:** @tier3 @hrm-recruitment @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_RECRUITMENT-T3-026 — `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Tags:** @tier3 @hrm-recruitment @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_RECRUITMENT-T3-027 — `post-new.php?post_type=erp_hr_questionnaire` is closed to roles that do not own it

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier3 @hrm-recruitment @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `post-new.php?post_type=erp_hr_questionnaire` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_RECRUITMENT-T3-028 — `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier3 @hrm-recruitment @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.
