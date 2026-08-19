# Tier 3 — hrm-assets

Negative cases — required-field enforcement, malformed input, injection, and per-role authorization.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**39 cases** (39 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### HRM_ASSETS-T3-001 — `category_id` ("Category *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-add-asset`.
  2. Fill every field EXCEPT `category_id` ("Category *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-002 — `item_group` ("Item Group *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-add-asset`.
  2. Fill every field EXCEPT `item_group` ("Item Group *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-003 — `item` ("Item Name *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-add-asset`.
  2. Fill every field EXCEPT `item` ("Item Name *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-004 — `given_date` ("Given Date *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-add-asset`.
  2. Fill every field EXCEPT `given_date` ("Given Date *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-005 — modal form `tmpl-erp-hr-emp-add-asset` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @security
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-add-asset`.
  2. Set each of 2 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_ASSETS-T3-006 — `category_id` ("Category *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-request-asset`.
  2. Fill every field EXCEPT `category_id` ("Category *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-007 — `item_group` ("Item Name *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-request-asset`.
  2. Fill every field EXCEPT `item_group` ("Item Name *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-008 — modal form `tmpl-erp-hr-emp-request-asset` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @security
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-request-asset`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_ASSETS-T3-009 — `category_id` ("Category *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-allotment-new`.
  2. Fill every field EXCEPT `category_id` ("Category *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-010 — `item_group` ("Item Name *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-allotment-new`.
  2. Fill every field EXCEPT `item_group` ("Item Name *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-011 — `item` ("Item *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-allotment-new`.
  2. Fill every field EXCEPT `item` ("Item *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-012 — `allotted_to` ("Allot To *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-allotment-new`.
  2. Fill every field EXCEPT `allotted_to` ("Allot To *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-013 — `given_date` ("Given Date *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-allotment-new`.
  2. Fill every field EXCEPT `given_date` ("Given Date *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-014 — `return_date` ("Return Date *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-allotment-new`.
  2. Fill every field EXCEPT `return_date` ("Return Date *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-015 — modal form `tmpl-erp-allotment-new` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @security
- **Steps:**
  1. Open modal form `tmpl-erp-allotment-new`.
  2. Set each of 2 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_ASSETS-T3-016 — `return_date` ("Return Date *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-asset-return`.
  2. Fill every field EXCEPT `return_date` ("Return Date *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-017 — modal form `tmpl-erp-asset-return` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @security
- **Steps:**
  1. Open modal form `tmpl-erp-asset-return`.
  2. Set each of 2 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_ASSETS-T3-018 — `category_id` ("Category *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-asset-request-reply`.
  2. Fill every field EXCEPT `category_id` ("Category *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-019 — `item_group` ("Item Name *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-asset-request-reply`.
  2. Fill every field EXCEPT `item_group` ("Item Name *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-020 — `item` ("Item *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-asset-request-reply`.
  2. Fill every field EXCEPT `item` ("Item *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-021 — `given_date` ("Given Date *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-asset-request-reply`.
  2. Fill every field EXCEPT `given_date` ("Given Date *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-022 — `return_date` ("Return Date *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-asset-request-reply`.
  2. Fill every field EXCEPT `return_date` ("Return Date *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-023 — modal form `tmpl-erp-asset-request-reply` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @security
- **Steps:**
  1. Open modal form `tmpl-erp-asset-request-reply`.
  2. Set each of 3 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_ASSETS-T3-024 — modal form `tmpl-erp-asset-request-reject` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier3 @hrm-assets @security
- **Steps:**
  1. Open modal form `tmpl-erp-asset-request-reject`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_ASSETS-T3-025 — `category_id` ("Category *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-asset-new`.
  2. Fill every field EXCEPT `category_id` ("Category *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-026 — `item_group` ("Item Name *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-asset-new`.
  2. Fill every field EXCEPT `item_group` ("Item Name *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-027 — `asset_type` ("Asset Type *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-asset-new`.
  2. Fill every field EXCEPT `asset_type` ("Asset Type *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-028 — `items[1][item_code]` ("Item Code *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-asset-new`.
  2. Fill every field EXCEPT `items[1][item_code]` ("Item Code *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-029 — modal form `tmpl-erp-asset-new` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier3 @hrm-assets @security
- **Steps:**
  1. Open modal form `tmpl-erp-asset-new`.
  2. Set each of 8 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_ASSETS-T3-030 — `items[1][price]` ("Price") rejects a malformed value

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-asset-new`.
  2. Set `items[1][price]` ("Price") to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### HRM_ASSETS-T3-031 — `category_id` ("Category *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-asset-edit`.
  2. Fill every field EXCEPT `category_id` ("Category *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-032 — `item_group` ("Item Group *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-asset-edit`.
  2. Fill every field EXCEPT `item_group` ("Item Group *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-033 — `items[{{i}}][item_code]` ("Item Code *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-erp-asset-edit`.
  2. Fill every field EXCEPT `items[{{i}}][item_code]` ("Item Code *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-034 — modal form `tmpl-erp-asset-edit` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier3 @hrm-assets @security
- **Steps:**
  1. Open modal form `tmpl-erp-asset-edit`.
  2. Set each of 9 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_ASSETS-T3-035 — `cat_name` ("Category Name *") is enforced as required

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier3 @hrm-assets @validation
- **Steps:**
  1. Open modal form `tmpl-asset-category-new`.
  2. Fill every field EXCEPT `cat_name` ("Category Name *").
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### HRM_ASSETS-T3-036 — modal form `tmpl-asset-category-new` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier3 @hrm-assets @security
- **Steps:**
  1. Open modal form `tmpl-asset-category-new`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### HRM_ASSETS-T3-037 — `admin.php?page=erp-hr&section=asset&sub-section=asset` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier3 @hrm-assets @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=asset&sub-section=asset` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_ASSETS-T3-038 — `admin.php?page=erp-hr&section=asset&sub-section=asset-allottment` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset-allottment`
- **Tags:** @tier3 @hrm-assets @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=asset&sub-section=asset-allottment` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### HRM_ASSETS-T3-039 — `admin.php?page=erp-hr&section=asset&sub-section=asset-request` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset-request`
- **Tags:** @tier3 @hrm-assets @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-hr&section=asset&sub-section=asset-request` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.
