# Tier 2 — hrm-leave

Edge cases — boundaries, optional-field omission, empty states, unusual but legal input.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**54 cases** (51 derived from the harness, 3 hand-written business flows).

## Business flows

#### HRM_LEAVE-F2-001 — A request exceeding the entitlement is refused

- **Surface:** `admin.php?page=erp-hr&section=leave`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. With a balance of N days, request N+1 days.
- **Expected:** The request is refused with a balance message; no partial request is stored.
- **Oracle:** UI message + request row count unchanged.

#### HRM_LEAVE-F2-002 — Overlapping leave requests are detected

- **Surface:** `admin.php?page=erp-hr&section=leave`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Approve a request for 10–12 March.
  2. Raise a second request for 11–13 March for the same employee.
- **Expected:** The overlap is reported rather than silently double-booking the days.
- **Oracle:** UI message + the two requests' date ranges.

#### HRM_LEAVE-F2-003 — A request whose end date precedes its start date is refused

- **Surface:** `admin.php?page=erp-hr&section=leave`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Set the start date after the end date and submit.
- **Expected:** A date-order validation message; no request is created.
- **Oracle:** UI message + row count.

## Screen & field coverage

#### HRM_LEAVE-T2-001 — `reason` ("Reason") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open modal form `tmpl-erp-hr-leave-approve-js-tmp`.
  2. Save with `reason` ("Reason") set to a 5,000-character body.
  3. Save with `reason` ("Reason") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-002 — modal form `tmpl-erp-hr-leave-approve-js-tmp` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open modal form `tmpl-erp-hr-leave-approve-js-tmp`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_LEAVE-T2-003 — `reason` ("Reason *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open modal form `tmpl-erp-hr-leave-reject-js-tmp`.
  2. Save with `reason` ("Reason *") set to a 5,000-character body.
  3. Save with `reason` ("Reason *") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-004 — `title` ("Holiday Name *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open modal form `tmpl-erp-hr-holiday-js-tmp`.
  2. Save with `title` ("Holiday Name *") set to a single character.
  3. Save with `title` ("Holiday Name *") set to a 255-character value.
  4. Save with `title` ("Holiday Name *") set to a value with leading and trailing whitespace.
  5. Save with `title` ("Holiday Name *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-005 — `start_date` ("Start Date *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open modal form `tmpl-erp-hr-holiday-js-tmp`.
  2. Save with `start_date` ("Start Date *") set to today.
  3. Save with `start_date` ("Start Date *") set to a leap day (29 Feb).
  4. Save with `start_date` ("Start Date *") set to a date before the company financial-year start.
  5. Save with `start_date` ("Start Date *") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-006 — `range` ("Range") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open modal form `tmpl-erp-hr-holiday-js-tmp`.
  2. Save with `range` ("Range") set to checked.
  3. Save with `range` ("Range") set to unchecked.
  4. Save with `range` ("Range") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-007 — `end_date` ("End Date") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open modal form `tmpl-erp-hr-holiday-js-tmp`.
  2. Save with `end_date` ("End Date") set to today.
  3. Save with `end_date` ("End Date") set to a leap day (29 Feb).
  4. Save with `end_date` ("End Date") set to a date before the company financial-year start.
  5. Save with `end_date` ("End Date") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-008 — `description` ("Description") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open modal form `tmpl-erp-hr-holiday-js-tmp`.
  2. Save with `description` ("Description") set to a 5,000-character body.
  3. Save with `description` ("Description") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-009 — modal form `tmpl-erp-hr-holiday-js-tmp` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open modal form `tmpl-erp-hr-holiday-js-tmp`.
  2. Fill only: `title` ("Holiday Name *"), `start_date` ("Start Date *").
  3. Submit.
- **Expected:** The record saves. The 3 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_LEAVE-T2-010 — `employee_name` ("Employee name") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave`.
  2. Save with `employee_name` ("Employee name") set to a single character.
  3. Save with `employee_name` ("Employee name") set to a 255-character value.
  4. Save with `employee_name` ("Employee name") set to a value with leading and trailing whitespace.
  5. Save with `employee_name` ("Employee name") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-011 — `financial_year` ("Financial year") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave`.
  2. Save with `financial_year` ("Financial year") set to the first real option.
  3. Save with `financial_year` ("Financial year") set to the last option.
  4. Save with `financial_year` ("Financial year") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-012 — `leave_policy` ("Leave Policy") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave`.
  2. Save with `leave_policy` ("Leave Policy") set to the first real option.
  3. Save with `leave_policy` ("Leave Policy") set to the last option.
  4. Save with `leave_policy` ("Leave Policy") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-013 — `filter_leave_status[]` ("Approved") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave`.
  2. Save with `filter_leave_status[]` ("Approved") set to checked.
  3. Save with `filter_leave_status[]` ("Approved") set to unchecked.
  4. Save with `filter_leave_status[]` ("Approved") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-014 — `filter_leave_status[]` ("Pending") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave`.
  2. Save with `filter_leave_status[]` ("Pending") set to checked.
  3. Save with `filter_leave_status[]` ("Pending") set to unchecked.
  4. Save with `filter_leave_status[]` ("Pending") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-015 — `filter_leave_status[]` ("Rejected") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave`.
  2. Save with `filter_leave_status[]` ("Rejected") set to checked.
  3. Save with `filter_leave_status[]` ("Rejected") set to unchecked.
  4. Save with `filter_leave_status[]` ("Rejected") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-016 — `filter_leave_year` ("Date range") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave`.
  2. Save with `filter_leave_year` ("Date range") set to the first real option.
  3. Save with `filter_leave_year` ("Date range") set to the last option.
  4. Save with `filter_leave_year` ("Date range") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-017 — `hide_filter` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave`.
  2. Save with `hide_filter` set to a single character.
  3. Save with `hide_filter` set to a 255-character value.
  4. Save with `hide_filter` set to a value with leading and trailing whitespace.
  5. Save with `hide_filter` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-018 — `leave_filter_reset` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave`.
  2. Save with `leave_filter_reset` set to a single character.
  3. Save with `leave_filter_reset` set to a 255-character value.
  4. Save with `leave_filter_reset` set to a value with leading and trailing whitespace.
  5. Save with `leave_filter_reset` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-019 — `filter_employee_search` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave`.
  2. Save with `filter_employee_search` set to a single character.
  3. Save with `filter_employee_search` set to a 255-character value.
  4. Save with `filter_employee_search` set to a value with leading and trailing whitespace.
  5. Save with `filter_employee_search` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-020 — `admin.php?page=erp-hr&section=leave` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=leave`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 10 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_LEAVE-T2-021 — `employee_name` ("Employee name") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Save with `employee_name` ("Employee name") set to a single character.
  3. Save with `employee_name` ("Employee name") set to a 255-character value.
  4. Save with `employee_name` ("Employee name") set to a value with leading and trailing whitespace.
  5. Save with `employee_name` ("Employee name") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-022 — `financial_year` ("Financial year") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Save with `financial_year` ("Financial year") set to the first real option.
  3. Save with `financial_year` ("Financial year") set to the last option.
  4. Save with `financial_year` ("Financial year") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-023 — `leave_policy` ("Leave Policy") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Save with `leave_policy` ("Leave Policy") set to the first real option.
  3. Save with `leave_policy` ("Leave Policy") set to the last option.
  4. Save with `leave_policy` ("Leave Policy") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-024 — `filter_leave_status[]` ("Approved") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Save with `filter_leave_status[]` ("Approved") set to checked.
  3. Save with `filter_leave_status[]` ("Approved") set to unchecked.
  4. Save with `filter_leave_status[]` ("Approved") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-025 — `filter_leave_status[]` ("Pending") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Save with `filter_leave_status[]` ("Pending") set to checked.
  3. Save with `filter_leave_status[]` ("Pending") set to unchecked.
  4. Save with `filter_leave_status[]` ("Pending") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-026 — `filter_leave_status[]` ("Rejected") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Save with `filter_leave_status[]` ("Rejected") set to checked.
  3. Save with `filter_leave_status[]` ("Rejected") set to unchecked.
  4. Save with `filter_leave_status[]` ("Rejected") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-027 — `filter_leave_year` ("Date range") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Save with `filter_leave_year` ("Date range") set to the first real option.
  3. Save with `filter_leave_year` ("Date range") set to the last option.
  4. Save with `filter_leave_year` ("Date range") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-028 — `hide_filter` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Save with `hide_filter` set to a single character.
  3. Save with `hide_filter` set to a 255-character value.
  4. Save with `hide_filter` set to a value with leading and trailing whitespace.
  5. Save with `hide_filter` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-029 — `leave_filter_reset` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Save with `leave_filter_reset` set to a single character.
  3. Save with `leave_filter_reset` set to a 255-character value.
  4. Save with `leave_filter_reset` set to a value with leading and trailing whitespace.
  5. Save with `leave_filter_reset` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-030 — `filter_employee_search` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Save with `filter_employee_search` set to a single character.
  3. Save with `filter_employee_search` set to a 255-character value.
  4. Save with `filter_employee_search` set to a value with leading and trailing whitespace.
  5. Save with `filter_employee_search` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-031 — `admin.php?page=erp-hr&section=leave&sub-section=leave-requests` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 10 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_LEAVE-T2-032 — `from` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=holidays`.
  2. Save with `from` set to a single character.
  3. Save with `from` set to a 255-character value.
  4. Save with `from` set to a value with leading and trailing whitespace.
  5. Save with `from` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-033 — `to` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=holidays`.
  2. Save with `to` set to a single character.
  3. Save with `to` set to a 255-character value.
  4. Save with `to` set to a value with leading and trailing whitespace.
  5. Save with `to` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-034 — `filter` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=holidays`.
  2. Save with `filter` set to a single character.
  3. Save with `filter` set to a 255-character value.
  4. Save with `filter` set to a value with leading and trailing whitespace.
  5. Save with `filter` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-035 — `erp-ical-input` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=holidays`.
  2. Save with `erp-ical-input` set to a single character.
  3. Save with `erp-ical-input` set to a 255-character value.
  4. Save with `erp-ical-input` set to a value with leading and trailing whitespace.
  5. Save with `erp-ical-input` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-036 — `admin.php?page=erp-hr&section=leave&sub-section=holidays` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=holidays`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 4 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_LEAVE-T2-037 — `department` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar`.
  2. Save with `department` set to the first real option.
  3. Save with `department` set to the last option.
  4. Save with `department` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-038 — `designation` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar`.
  2. Save with `designation` set to the first real option.
  3. Save with `designation` set to the last option.
  4. Save with `designation` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-039 — `erp_leave_calendar_filter` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar`.
  2. Save with `erp_leave_calendar_filter` set to a single character.
  3. Save with `erp_leave_calendar_filter` set to a 255-character value.
  4. Save with `erp_leave_calendar_filter` set to a value with leading and trailing whitespace.
  5. Save with `erp_leave_calendar_filter` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-040 — `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 3 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_LEAVE-T2-041 — `employee_name` ("Employee name") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Save with `employee_name` ("Employee name") set to a single character.
  3. Save with `employee_name` ("Employee name") set to a 255-character value.
  4. Save with `employee_name` ("Employee name") set to a value with leading and trailing whitespace.
  5. Save with `employee_name` ("Employee name") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-042 — `financial_year` ("Financial year") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Save with `financial_year` ("Financial year") set to the first real option.
  3. Save with `financial_year` ("Financial year") set to the last option.
  4. Save with `financial_year` ("Financial year") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-043 — `leave_policy` ("Leave Policy") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Save with `leave_policy` ("Leave Policy") set to the first real option.
  3. Save with `leave_policy` ("Leave Policy") set to the last option.
  4. Save with `leave_policy` ("Leave Policy") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-044 — `filter_leave_status[]` ("Approved") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Save with `filter_leave_status[]` ("Approved") set to checked.
  3. Save with `filter_leave_status[]` ("Approved") set to unchecked.
  4. Save with `filter_leave_status[]` ("Approved") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-045 — `filter_leave_status[]` ("Pending") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Save with `filter_leave_status[]` ("Pending") set to checked.
  3. Save with `filter_leave_status[]` ("Pending") set to unchecked.
  4. Save with `filter_leave_status[]` ("Pending") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-046 — `filter_leave_status[]` ("Rejected") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Save with `filter_leave_status[]` ("Rejected") set to checked.
  3. Save with `filter_leave_status[]` ("Rejected") set to unchecked.
  4. Save with `filter_leave_status[]` ("Rejected") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-047 — `filter_leave_year` ("Date range") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Save with `filter_leave_year` ("Date range") set to the first real option.
  3. Save with `filter_leave_year` ("Date range") set to the last option.
  4. Save with `filter_leave_year` ("Date range") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-048 — `hide_filter` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Save with `hide_filter` set to a single character.
  3. Save with `hide_filter` set to a 255-character value.
  4. Save with `hide_filter` set to a value with leading and trailing whitespace.
  5. Save with `hide_filter` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-049 — `leave_filter_reset` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Save with `leave_filter_reset` set to a single character.
  3. Save with `leave_filter_reset` set to a 255-character value.
  4. Save with `leave_filter_reset` set to a value with leading and trailing whitespace.
  5. Save with `leave_filter_reset` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-050 — `filter_employee_search` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Save with `filter_employee_search` set to a single character.
  3. Save with `filter_employee_search` set to a 255-character value.
  4. Save with `filter_employee_search` set to a value with leading and trailing whitespace.
  5. Save with `filter_employee_search` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_LEAVE-T2-051 — `admin.php?page=erp-hr&section=leave&sub-section=leave-requests` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier2 @hrm-leave @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 10 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.
