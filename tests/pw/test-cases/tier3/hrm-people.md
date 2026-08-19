# Tier 3 — hrm-people

Negative cases — required-field enforcement, malformed input, injection, and per-role authorization.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**75 cases** (75 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### HRM_PEOPLE-T3-001 — `location_name` ("Location Name *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-address`.
  2. Fill every field EXCEPT `location_name` ("Location Name *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-002 — `address_1` ("Address Line 1 *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-address`.
  2. Fill every field EXCEPT `address_1` ("Address Line 1 *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-003 — modal form `tmpl-erp-address` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @security
- **Steps:**
  1. Open modal form `tmpl-erp-address`.
  2. Set each of 5 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_PEOPLE-T3-004 — `personal[first_name]` ("First Name *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Fill every field EXCEPT `personal[first_name]` ("First Name *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-005 — `personal[last_name]` ("Last Name *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Fill every field EXCEPT `personal[last_name]` ("Last Name *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-006 — `user_email` ("Email *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Fill every field EXCEPT `user_email` ("Email *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-007 — `work[type]` ("Employee Type *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Fill every field EXCEPT `work[type]` ("Employee Type *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-008 — `work[status]` ("Employee Status *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Fill every field EXCEPT `work[status]` ("Employee Status *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-009 — `work[hiring_date]` ("Date of Hire *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Fill every field EXCEPT `work[hiring_date]` ("Date of Hire *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-010 — `work[department]` ("Department *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Fill every field EXCEPT `work[department]` ("Department *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-011 — `work[designation]` ("Job Title *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Fill every field EXCEPT `work[designation]` ("Job Title *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-012 — modal form `tmpl-erp-new-employee` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @security
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Set each of 24 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_PEOPLE-T3-013 — `user_email` ("Email *") rejects a malformed value

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Set `user_email` ("Email *") to a value of the wrong shape (e.g. `not-an-email`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### HRM_PEOPLE-T3-014 — `personal[other_email]` ("Other Email") rejects a malformed value

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Set `personal[other_email]` ("Other Email") to a value of the wrong shape (e.g. `not-an-email`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### HRM_PEOPLE-T3-015 — `personal[user_url]` ("Website") rejects a malformed value

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Set `personal[user_url]` ("Website") to a value of the wrong shape (e.g. `not-an-url`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### HRM_PEOPLE-T3-016 — `date` ("Date *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-type`.
  2. Fill every field EXCEPT `date` ("Date *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-017 — modal form `tmpl-erp-employment-type` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @security
- **Steps:**
  1. Open modal form `tmpl-erp-employment-type`.
  2. Set each of 2 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_PEOPLE-T3-018 — `date` ("Date *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-status`.
  2. Fill every field EXCEPT `date` ("Date *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-019 — modal form `tmpl-erp-employment-status` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @security
- **Steps:**
  1. Open modal form `tmpl-erp-employment-status`.
  2. Set each of 2 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_PEOPLE-T3-020 — `date` ("Date *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation`.
  2. Fill every field EXCEPT `date` ("Date *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-021 — `pay_rate` ("Pay Rate *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation`.
  2. Fill every field EXCEPT `pay_rate` ("Pay Rate *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-022 — `pay_type` ("Pay Type *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation`.
  2. Fill every field EXCEPT `pay_type` ("Pay Type *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-023 — modal form `tmpl-erp-employment-compensation` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @security
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation`.
  2. Set each of 3 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_PEOPLE-T3-024 — `date` ("Date *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-jobinfo`.
  2. Fill every field EXCEPT `date` ("Date *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-025 — modal form `tmpl-erp-employment-jobinfo` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @security
- **Steps:**
  1. Open modal form `tmpl-erp-employment-jobinfo`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_PEOPLE-T3-026 — `company_name` ("Previous Company *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-work-experience`.
  2. Fill every field EXCEPT `company_name` ("Previous Company *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-027 — `job_title` ("Job Title *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-work-experience`.
  2. Fill every field EXCEPT `job_title` ("Job Title *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-028 — `from` ("From *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-work-experience`.
  2. Fill every field EXCEPT `from` ("From *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-029 — `to` ("To *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-work-experience`.
  2. Fill every field EXCEPT `to` ("To *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-030 — modal form `tmpl-erp-employment-work-experience` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @security
- **Steps:**
  1. Open modal form `tmpl-erp-employment-work-experience`.
  2. Set each of 5 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_PEOPLE-T3-031 — `school` ("School Name *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. Fill every field EXCEPT `school` ("School Name *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-032 — `degree` ("Degree *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. Fill every field EXCEPT `degree` ("Degree *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-033 — `field` ("Field of Study *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. Fill every field EXCEPT `field` ("Field of Study *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-034 — `finished` ("Year of Completion *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. Fill every field EXCEPT `finished` ("Year of Completion *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-035 — `result_type` ("Result type *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. Fill every field EXCEPT `result_type` ("Result type *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-036 — `gpa` ("Result (Grade) *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. Fill every field EXCEPT `gpa` ("Result (Grade) *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-037 — `scale` ("Scale (Out of) *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. Fill every field EXCEPT `scale` ("Scale (Out of) *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-038 — modal form `tmpl-erp-employment-education` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @security
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. Set each of 7 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_PEOPLE-T3-039 — `finished` ("Year of Completion *") rejects a malformed value

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. Set `finished` ("Year of Completion *") to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### HRM_PEOPLE-T3-040 — `scale` ("Scale (Out of) *") rejects a malformed value

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. Set `scale` ("Scale (Out of) *") to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### HRM_PEOPLE-T3-041 — `performance_date` ("Review Date *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-reviews`.
  2. Fill every field EXCEPT `performance_date` ("Review Date *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-042 — modal form `tmpl-erp-employment-performance-reviews` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @security
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-reviews`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_PEOPLE-T3-043 — `performance_date` ("Reference Date *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-comments`.
  2. Fill every field EXCEPT `performance_date` ("Reference Date *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-044 — modal form `tmpl-erp-employment-performance-comments` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @security
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-comments`.
  2. Set each of 2 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_PEOPLE-T3-045 — `performance_date` ("Set Date *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-goals`.
  2. Fill every field EXCEPT `performance_date` ("Set Date *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-046 — `completion_date` ("Completion Date *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-goals`.
  2. Fill every field EXCEPT `completion_date` ("Completion Date *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-047 — modal form `tmpl-erp-employment-performance-goals` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @security
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-goals`.
  2. Set each of 5 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_PEOPLE-T3-048 — `name` ("Name *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-dependent`.
  2. Fill every field EXCEPT `name` ("Name *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-049 — `relation` ("Relationship *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-dependent`.
  2. Fill every field EXCEPT `relation` ("Relationship *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-050 — modal form `tmpl-erp-employment-dependent` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @security
- **Steps:**
  1. Open modal form `tmpl-erp-employment-dependent`.
  2. Set each of 3 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_PEOPLE-T3-051 — `title` ("Department Title *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-new-dept`.
  2. Fill every field EXCEPT `title` ("Department Title *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-052 — modal form `tmpl-erp-new-dept` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @security
- **Steps:**
  1. Open modal form `tmpl-erp-new-dept`.
  2. Set each of 2 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_PEOPLE-T3-053 — `title` ("Designation Title *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-new-desig`.
  2. Fill every field EXCEPT `title` ("Designation Title *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-054 — modal form `tmpl-erp-new-desig` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @security
- **Steps:**
  1. Open modal form `tmpl-erp-new-desig`.
  2. Set each of 2 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_PEOPLE-T3-055 — `terminate_date` ("Termination Date *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-terminate`.
  2. Fill every field EXCEPT `terminate_date` ("Termination Date *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-056 — `termination_type` ("Termination Type *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-terminate`.
  2. Fill every field EXCEPT `termination_type` ("Termination Type *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-057 — `termination_reason` ("Termination Reason *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-terminate`.
  2. Fill every field EXCEPT `termination_reason` ("Termination Reason *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-058 — `eligible_for_rehire` ("Eligible for Rehire *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-terminate`.
  2. Fill every field EXCEPT `eligible_for_rehire` ("Eligible for Rehire *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-059 — modal form `tmpl-erp-employment-terminate` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @security
- **Steps:**
  1. Open modal form `tmpl-erp-employment-terminate`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_PEOPLE-T3-060 — `csv_file` ("CSV File *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employee-import-csv`.
  2. Fill every field EXCEPT `csv_file` ("CSV File *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-061 — `date` ("Date *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation-history`.
  2. Fill every field EXCEPT `date` ("Date *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-062 — `type` ("Pay Rate *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation-history`.
  2. Fill every field EXCEPT `type` ("Pay Rate *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-063 — `category` ("Pay Type *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation-history`.
  2. Fill every field EXCEPT `category` ("Pay Type *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-064 — modal form `tmpl-erp-employment-compensation-history` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @security
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation-history`.
  2. Set each of 3 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_PEOPLE-T3-065 — `date` ("Date *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-job-info-history`.
  2. Fill every field EXCEPT `date` ("Date *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-066 — modal form `tmpl-erp-employment-job-info-history` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @security
- **Steps:**
  1. Open modal form `tmpl-erp-employment-job-info-history`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_PEOPLE-T3-067 — `date` ("Date *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @validation
- **Steps:**
  1. Open modal form `tmpl-erp-employment-type-history`.
  2. Fill every field EXCEPT `date` ("Date *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_PEOPLE-T3-068 — modal form `tmpl-erp-employment-type-history` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @security
- **Steps:**
  1. Open modal form `tmpl-erp-employment-type-history`.
  2. Set each of 2 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_PEOPLE-T3-069 — `admin.php?page=erp-hr&section=dashboard` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=dashboard`
- **Tags:** @tier3 @hrm-people @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=dashboard` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_PEOPLE-T3-070 — `admin.php?page=erp-hr&section=people` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=people`
- **Tags:** @tier3 @hrm-people @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=people` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_PEOPLE-T3-071 — `admin.php?page=erp-hr&section=people&sub-section=employee` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-people @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=people&sub-section=employee` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_PEOPLE-T3-072 — `admin.php?page=erp-hr&section=people&sub-section=departments` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=departments`
- **Tags:** @tier3 @hrm-people @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=people&sub-section=departments` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_PEOPLE-T3-073 — `admin.php?page=erp-hr&section=people&sub-section=designation` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier3 @hrm-people @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=people&sub-section=designation` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_PEOPLE-T3-074 — `admin.php?page=erp-hr&section=people&sub-section=location` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=location`
- **Tags:** @tier3 @hrm-people @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=people&sub-section=location` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_PEOPLE-T3-075 — `admin.php?page=erp-hr&section=people&sub-section=employee&action=view` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee&action=view`
- **Tags:** @tier3 @hrm-people @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=people&sub-section=employee&action=view` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.
