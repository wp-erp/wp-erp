# Tier 2 — hrm-reports

Edge cases — boundaries, optional-field omission, empty states, unusual but legal input.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**16 cases** (16 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### HRM_REPORTS-T2-001 — `year` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=headcount`
- **Tags:** @tier2 @hrm-reports @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=headcount`.
  2. Save with `year` set to the first real option.
  3. Save with `year` set to the last option.
  4. Save with `year` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_REPORTS-T2-002 — `department` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=headcount`
- **Tags:** @tier2 @hrm-reports @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=headcount`.
  2. Save with `department` set to the first real option.
  3. Save with `department` set to the last option.
  4. Save with `department` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_REPORTS-T2-003 — `admin.php?page=erp-hr&section=report&sub-section=report&type=headcount` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=headcount`
- **Tags:** @tier2 @hrm-reports @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=headcount`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_REPORTS-T2-004 — `filter_year` ("Filter by Designation") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`
- **Tags:** @tier2 @hrm-reports @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`.
  2. Save with `filter_year` ("Filter by Designation") set to the first real option.
  3. Save with `filter_year` ("Filter by Designation") set to the last option.
  4. Save with `filter_year` ("Filter by Designation") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_REPORTS-T2-005 — `filter_designation` ("Filter by Designation") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`
- **Tags:** @tier2 @hrm-reports @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`.
  2. Save with `filter_designation` ("Filter by Designation") set to the first real option.
  3. Save with `filter_designation` ("Filter by Designation") set to the last option.
  4. Save with `filter_designation` ("Filter by Designation") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_REPORTS-T2-006 — `filter_department` ("Filter by Designation") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`
- **Tags:** @tier2 @hrm-reports @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`.
  2. Save with `filter_department` ("Filter by Designation") set to the first real option.
  3. Save with `filter_department` ("Filter by Designation") set to the last option.
  4. Save with `filter_department` ("Filter by Designation") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_REPORTS-T2-007 — `filter_employment_type` ("Filter by Designation") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`
- **Tags:** @tier2 @hrm-reports @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`.
  2. Save with `filter_employment_type` ("Filter by Designation") set to the first real option.
  3. Save with `filter_employment_type` ("Filter by Designation") set to the last option.
  4. Save with `filter_employment_type` ("Filter by Designation") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_REPORTS-T2-008 — `filter_leave_report` ("Filter by Designation") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`
- **Tags:** @tier2 @hrm-reports @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`.
  2. Save with `filter_leave_report` ("Filter by Designation") set to a single character.
  3. Save with `filter_leave_report` ("Filter by Designation") set to a 255-character value.
  4. Save with `filter_leave_report` ("Filter by Designation") set to a value with leading and trailing whitespace.
  5. Save with `filter_leave_report` ("Filter by Designation") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_REPORTS-T2-009 — `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`
- **Tags:** @tier2 @hrm-reports @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 5 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_REPORTS-T2-010 — `query_time` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=asset-report`
- **Tags:** @tier2 @hrm-reports @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=asset-report`.
  2. Save with `query_time` set to the first real option.
  3. Save with `query_time` set to the last option.
  4. Save with `query_time` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_REPORTS-T2-011 — `category` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=asset-report`
- **Tags:** @tier2 @hrm-reports @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=asset-report`.
  2. Save with `category` set to the first real option.
  3. Save with `category` set to the last option.
  4. Save with `category` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_REPORTS-T2-012 — `admin.php?page=erp-hr&section=report&sub-section=report&type=asset-report` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=asset-report`
- **Tags:** @tier2 @hrm-reports @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=asset-report`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_REPORTS-T2-013 — `location` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report`
- **Tags:** @tier2 @hrm-reports @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report`.
  2. Save with `location` set to the first real option.
  3. Save with `location` set to the last option.
  4. Save with `location` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_REPORTS-T2-014 — `department` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report`
- **Tags:** @tier2 @hrm-reports @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report`.
  2. Save with `department` set to the first real option.
  3. Save with `department` set to the last option.
  4. Save with `department` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_REPORTS-T2-015 — `query_time` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report`
- **Tags:** @tier2 @hrm-reports @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report`.
  2. Save with `query_time` set to the first real option.
  3. Save with `query_time` set to the last option.
  4. Save with `query_time` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_REPORTS-T2-016 — `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report`
- **Tags:** @tier2 @hrm-reports @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 3 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.
