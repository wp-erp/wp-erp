# Tier 2 — hrm-payroll

Edge cases — boundaries, optional-field omission, empty states, unusual but legal input.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**9 cases** (8 derived from the harness, 1 hand-written business flows).

## Business flows

#### HRM_PAYROLL-F2-001 — A pay run over a period with no employees is handled

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=payrun`
- **Tags:** @tier2 @hrm-payroll @pro @edge
- **Steps:**
  1. Generate a pay run for a calendar with no assigned employees.
- **Expected:** An empty pay run or a clear message — never a fatal and never a run with phantom rows.
- **Oracle:** Pay-run row count + rendered body.

## Screen & field coverage

#### HRM_PAYROLL-T2-001 — `filter_payrun_status` ("Filter by Status") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=payrun`
- **Tags:** @tier2 @hrm-payroll @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=payroll&sub-section=payrun`.
  2. Save with `filter_payrun_status` ("Filter by Status") set to the first real option.
  3. Save with `filter_payrun_status` ("Filter by Status") set to the last option.
  4. Save with `filter_payrun_status` ("Filter by Status") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PAYROLL-T2-002 — `filter_status_button` ("Filter by Status") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=payrun`
- **Tags:** @tier2 @hrm-payroll @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=payroll&sub-section=payrun`.
  2. Save with `filter_status_button` ("Filter by Status") set to a single character.
  3. Save with `filter_status_button` ("Filter by Status") set to a 255-character value.
  4. Save with `filter_status_button` ("Filter by Status") set to a value with leading and trailing whitespace.
  5. Save with `filter_status_button` ("Filter by Status") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PAYROLL-T2-003 — `admin.php?page=erp-hr&section=payroll&sub-section=payrun` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=payrun`
- **Tags:** @tier2 @hrm-payroll @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=payroll&sub-section=payrun`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_PAYROLL-T2-004 — `pay_items` ("SL") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`
- **Tags:** @tier2 @hrm-payroll @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`.
  2. Save with `pay_items` ("SL") set to the first real option.
  3. Save with `pay_items` ("SL") set to the last option.
  4. Save with `pay_items` ("SL") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PAYROLL-T2-005 — `emp_dept` ("SL") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`
- **Tags:** @tier2 @hrm-payroll @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`.
  2. Save with `emp_dept` ("SL") set to the first real option.
  3. Save with `emp_dept` ("SL") set to the last option.
  4. Save with `emp_dept` ("SL") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PAYROLL-T2-006 — `emp_desig` ("SL") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`
- **Tags:** @tier2 @hrm-payroll @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`.
  2. Save with `emp_desig` ("SL") set to the first real option.
  3. Save with `emp_desig` ("SL") set to the last option.
  4. Save with `emp_desig` ("SL") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PAYROLL-T2-007 — `emp_name` ("SL") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`
- **Tags:** @tier2 @hrm-payroll @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`.
  2. Save with `emp_name` ("SL") set to a single character.
  3. Save with `emp_name` ("SL") set to a 255-character value.
  4. Save with `emp_name` ("SL") set to a value with leading and trailing whitespace.
  5. Save with `emp_name` ("SL") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PAYROLL-T2-008 — `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`
- **Tags:** @tier2 @hrm-payroll @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 4 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.
