# Tier 2 — hrm-people

Edge cases — boundaries, optional-field omission, empty states, unusual but legal input.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**184 cases** (181 derived from the harness, 3 hand-written business flows).

## Business flows

#### HRM_PEOPLE-F2-001 — Duplicate e-mail on a second employee is handled

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Create an employee with e-mail X.
  2. Create a second employee with the same e-mail X.
- **Expected:** The second create is rejected with a message about the existing user, or reuses the existing WordPress user deliberately — not a 500 and not a duplicate WP user.
- **Oracle:** UI message + `wp_users` count for that address.

#### HRM_PEOPLE-F2-002 — Employee list filters combine correctly

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Filter by `filter_designation`, then add `filter_department`, then add `filter_employment_type`.
  2. Apply "Reset".
- **Expected:** Filters intersect (AND), the row count only narrows, and Reset restores the unfiltered list.
- **Oracle:** Row count per filter combination vs the equivalent DB query.

#### HRM_PEOPLE-F2-003 — CSV import and export round-trip

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Export employees via `tmpl-erp-employee-export-csv`.
  2. Import that same file via `tmpl-erp-employee-import-csv`.
- **Expected:** The import reports how many rows it processed and creates no duplicates for employees that already exist.
- **Oracle:** Employee row count before vs after + the import result message.

## Screen & field coverage

#### HRM_PEOPLE-T2-001 — `location_name` ("Location Name *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-address`.
  2. Save with `location_name` ("Location Name *") set to a single character.
  3. Save with `location_name` ("Location Name *") set to a 255-character value.
  4. Save with `location_name` ("Location Name *") set to a value with leading and trailing whitespace.
  5. Save with `location_name` ("Location Name *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-002 — `address_1` ("Address Line 1 *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-address`.
  2. Save with `address_1` ("Address Line 1 *") set to a single character.
  3. Save with `address_1` ("Address Line 1 *") set to a 255-character value.
  4. Save with `address_1` ("Address Line 1 *") set to a value with leading and trailing whitespace.
  5. Save with `address_1` ("Address Line 1 *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-003 — `address_2` ("Address Line 2") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-address`.
  2. Save with `address_2` ("Address Line 2") set to a single character.
  3. Save with `address_2` ("Address Line 2") set to a 255-character value.
  4. Save with `address_2` ("Address Line 2") set to a value with leading and trailing whitespace.
  5. Save with `address_2` ("Address Line 2") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-004 — `city` ("City") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-address`.
  2. Save with `city` ("City") set to a single character.
  3. Save with `city` ("City") set to a 255-character value.
  4. Save with `city` ("City") set to a value with leading and trailing whitespace.
  5. Save with `city` ("City") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-005 — `country` ("Country *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-address`.
  2. Save with `country` ("Country *") set to the first real option.
  3. Save with `country` ("Country *") set to the last option.
  4. Save with `country` ("Country *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-006 — `state` ("Province / State") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-address`.
  2. Save with `state` ("Province / State") set to the first real option.
  3. Save with `state` ("Province / State") set to the last option.
  4. Save with `state` ("Province / State") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-007 — `zip` ("Postal / Zip Code") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-address`.
  2. Save with `zip` ("Postal / Zip Code") set to a single character.
  3. Save with `zip` ("Postal / Zip Code") set to a 255-character value.
  4. Save with `zip` ("Postal / Zip Code") set to a value with leading and trailing whitespace.
  5. Save with `zip` ("Postal / Zip Code") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-008 — modal form `tmpl-erp-address` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-address`.
  2. Fill only: `location_name` ("Location Name *"), `address_1` ("Address Line 1 *").
  3. Submit.
- **Expected:** The record saves. The 5 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_PEOPLE-T2-009 — `personal[first_name]` ("First Name *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[first_name]` ("First Name *") set to a single character.
  3. Save with `personal[first_name]` ("First Name *") set to a 255-character value.
  4. Save with `personal[first_name]` ("First Name *") set to a value with leading and trailing whitespace.
  5. Save with `personal[first_name]` ("First Name *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-010 — `personal[middle_name]` ("Middle Name") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[middle_name]` ("Middle Name") set to a single character.
  3. Save with `personal[middle_name]` ("Middle Name") set to a 255-character value.
  4. Save with `personal[middle_name]` ("Middle Name") set to a value with leading and trailing whitespace.
  5. Save with `personal[middle_name]` ("Middle Name") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-011 — `personal[last_name]` ("Last Name *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[last_name]` ("Last Name *") set to a single character.
  3. Save with `personal[last_name]` ("Last Name *") set to a 255-character value.
  4. Save with `personal[last_name]` ("Last Name *") set to a value with leading and trailing whitespace.
  5. Save with `personal[last_name]` ("Last Name *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-012 — `personal[employee_id]` ("Employee ID") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[employee_id]` ("Employee ID") set to a single character.
  3. Save with `personal[employee_id]` ("Employee ID") set to a 255-character value.
  4. Save with `personal[employee_id]` ("Employee ID") set to a value with leading and trailing whitespace.
  5. Save with `personal[employee_id]` ("Employee ID") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-013 — `user_email` ("Email *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `user_email` ("Email *") set to an address with a plus tag (`a+b@example.test`).
  3. Save with `user_email` ("Email *") set to an address at the 254-character RFC limit.
  4. Save with `user_email` ("Email *") set to unicode in the local part.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-014 — `work[type]` ("Employee Type *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `work[type]` ("Employee Type *") set to the first real option.
  3. Save with `work[type]` ("Employee Type *") set to the last option.
  4. Save with `work[type]` ("Employee Type *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-015 — `work[status]` ("Employee Status *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `work[status]` ("Employee Status *") set to the first real option.
  3. Save with `work[status]` ("Employee Status *") set to the last option.
  4. Save with `work[status]` ("Employee Status *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-016 — `work[end_date]` ("Employee End Date") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `work[end_date]` ("Employee End Date") set to today.
  3. Save with `work[end_date]` ("Employee End Date") set to a leap day (29 Feb).
  4. Save with `work[end_date]` ("Employee End Date") set to a date before the company financial-year start.
  5. Save with `work[end_date]` ("Employee End Date") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-017 — `work[hiring_date]` ("Date of Hire *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `work[hiring_date]` ("Date of Hire *") set to today.
  3. Save with `work[hiring_date]` ("Date of Hire *") set to a leap day (29 Feb).
  4. Save with `work[hiring_date]` ("Date of Hire *") set to a date before the company financial-year start.
  5. Save with `work[hiring_date]` ("Date of Hire *") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-018 — `work[department]` ("Department *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `work[department]` ("Department *") set to the first real option.
  3. Save with `work[department]` ("Department *") set to the last option.
  4. Save with `work[department]` ("Department *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-019 — `work[designation]` ("Job Title *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `work[designation]` ("Job Title *") set to the first real option.
  3. Save with `work[designation]` ("Job Title *") set to the last option.
  4. Save with `work[designation]` ("Job Title *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-020 — `advanced_fields` ("Show Advanced Fields") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `advanced_fields` ("Show Advanced Fields") set to checked.
  3. Save with `advanced_fields` ("Show Advanced Fields") set to unchecked.
  4. Save with `advanced_fields` ("Show Advanced Fields") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-021 — `work[location]` ("Location") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `work[location]` ("Location") set to the first real option.
  3. Save with `work[location]` ("Location") set to the last option.
  4. Save with `work[location]` ("Location") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-022 — `work[reporting_to]` ("Reporting To") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `work[reporting_to]` ("Reporting To") set to the first real option.
  3. Save with `work[reporting_to]` ("Reporting To") set to the last option.
  4. Save with `work[reporting_to]` ("Reporting To") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-023 — `work[hiring_source]` ("Source of Hire") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `work[hiring_source]` ("Source of Hire") set to the first real option.
  3. Save with `work[hiring_source]` ("Source of Hire") set to the last option.
  4. Save with `work[hiring_source]` ("Source of Hire") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-024 — `work[pay_rate]` ("Pay Rate") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `work[pay_rate]` ("Pay Rate") set to a single character.
  3. Save with `work[pay_rate]` ("Pay Rate") set to a 255-character value.
  4. Save with `work[pay_rate]` ("Pay Rate") set to a value with leading and trailing whitespace.
  5. Save with `work[pay_rate]` ("Pay Rate") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-025 — `work[pay_type]` ("Pay Type") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `work[pay_type]` ("Pay Type") set to the first real option.
  3. Save with `work[pay_type]` ("Pay Type") set to the last option.
  4. Save with `work[pay_type]` ("Pay Type") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-026 — `personal[work_phone]` ("Work Phone") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[work_phone]` ("Work Phone") set to a single character.
  3. Save with `personal[work_phone]` ("Work Phone") set to a 255-character value.
  4. Save with `personal[work_phone]` ("Work Phone") set to a value with leading and trailing whitespace.
  5. Save with `personal[work_phone]` ("Work Phone") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-027 — `work[shift]` ("Shift") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `work[shift]` ("Shift") set to the first real option.
  3. Save with `work[shift]` ("Shift") set to the last option.
  4. Save with `work[shift]` ("Shift") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-028 — `personal[blood_group]` ("Blood Group") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[blood_group]` ("Blood Group") set to the first real option.
  3. Save with `personal[blood_group]` ("Blood Group") set to the last option.
  4. Save with `personal[blood_group]` ("Blood Group") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-029 — `personal[spouse_name]` ("Spouse's name") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[spouse_name]` ("Spouse's name") set to a single character.
  3. Save with `personal[spouse_name]` ("Spouse's name") set to a 255-character value.
  4. Save with `personal[spouse_name]` ("Spouse's name") set to a value with leading and trailing whitespace.
  5. Save with `personal[spouse_name]` ("Spouse's name") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-030 — `personal[father_name]` ("Father's name") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[father_name]` ("Father's name") set to a single character.
  3. Save with `personal[father_name]` ("Father's name") set to a 255-character value.
  4. Save with `personal[father_name]` ("Father's name") set to a value with leading and trailing whitespace.
  5. Save with `personal[father_name]` ("Father's name") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-031 — `personal[mother_name]` ("Mother's name") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[mother_name]` ("Mother's name") set to a single character.
  3. Save with `personal[mother_name]` ("Mother's name") set to a 255-character value.
  4. Save with `personal[mother_name]` ("Mother's name") set to a value with leading and trailing whitespace.
  5. Save with `personal[mother_name]` ("Mother's name") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-032 — `personal[mobile]` ("Mobile") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[mobile]` ("Mobile") set to a single character.
  3. Save with `personal[mobile]` ("Mobile") set to a 255-character value.
  4. Save with `personal[mobile]` ("Mobile") set to a value with leading and trailing whitespace.
  5. Save with `personal[mobile]` ("Mobile") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-033 — `personal[phone]` ("Phone") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[phone]` ("Phone") set to a single character.
  3. Save with `personal[phone]` ("Phone") set to a 255-character value.
  4. Save with `personal[phone]` ("Phone") set to a value with leading and trailing whitespace.
  5. Save with `personal[phone]` ("Phone") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-034 — `personal[other_email]` ("Other Email") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[other_email]` ("Other Email") set to an address with a plus tag (`a+b@example.test`).
  3. Save with `personal[other_email]` ("Other Email") set to an address at the 254-character RFC limit.
  4. Save with `personal[other_email]` ("Other Email") set to unicode in the local part.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-035 — `work[date_of_birth]` ("Date of Birth") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `work[date_of_birth]` ("Date of Birth") set to today.
  3. Save with `work[date_of_birth]` ("Date of Birth") set to a leap day (29 Feb).
  4. Save with `work[date_of_birth]` ("Date of Birth") set to a date before the company financial-year start.
  5. Save with `work[date_of_birth]` ("Date of Birth") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-036 — `personal[nationality]` ("Nationality") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[nationality]` ("Nationality") set to the first real option.
  3. Save with `personal[nationality]` ("Nationality") set to the last option.
  4. Save with `personal[nationality]` ("Nationality") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-037 — `personal[gender]` ("Gender") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[gender]` ("Gender") set to the first real option.
  3. Save with `personal[gender]` ("Gender") set to the last option.
  4. Save with `personal[gender]` ("Gender") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-038 — `personal[marital_status]` ("Marital Status") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[marital_status]` ("Marital Status") set to the first real option.
  3. Save with `personal[marital_status]` ("Marital Status") set to the last option.
  4. Save with `personal[marital_status]` ("Marital Status") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-039 — `personal[driving_license]` ("Driving License") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[driving_license]` ("Driving License") set to a single character.
  3. Save with `personal[driving_license]` ("Driving License") set to a 255-character value.
  4. Save with `personal[driving_license]` ("Driving License") set to a value with leading and trailing whitespace.
  5. Save with `personal[driving_license]` ("Driving License") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-040 — `personal[hobbies]` ("Hobbies") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[hobbies]` ("Hobbies") set to a single character.
  3. Save with `personal[hobbies]` ("Hobbies") set to a 255-character value.
  4. Save with `personal[hobbies]` ("Hobbies") set to a value with leading and trailing whitespace.
  5. Save with `personal[hobbies]` ("Hobbies") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-041 — `personal[user_url]` ("Website") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[user_url]` ("Website") set to a scheme-less host (`example.test`).
  3. Save with `personal[user_url]` ("Website") set to an `https://` URL with a query string and a fragment.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-042 — `personal[street_1]` ("Address 1") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[street_1]` ("Address 1") set to a single character.
  3. Save with `personal[street_1]` ("Address 1") set to a 255-character value.
  4. Save with `personal[street_1]` ("Address 1") set to a value with leading and trailing whitespace.
  5. Save with `personal[street_1]` ("Address 1") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-043 — `personal[street_2]` ("Address 2") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[street_2]` ("Address 2") set to a single character.
  3. Save with `personal[street_2]` ("Address 2") set to a 255-character value.
  4. Save with `personal[street_2]` ("Address 2") set to a value with leading and trailing whitespace.
  5. Save with `personal[street_2]` ("Address 2") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-044 — `personal[city]` ("City") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[city]` ("City") set to a single character.
  3. Save with `personal[city]` ("City") set to a 255-character value.
  4. Save with `personal[city]` ("City") set to a value with leading and trailing whitespace.
  5. Save with `personal[city]` ("City") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-045 — `personal[country]` ("Country") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[country]` ("Country") set to the first real option.
  3. Save with `personal[country]` ("Country") set to the last option.
  4. Save with `personal[country]` ("Country") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-046 — `personal[state]` ("Province / State") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[state]` ("Province / State") set to the first real option.
  3. Save with `personal[state]` ("Province / State") set to the last option.
  4. Save with `personal[state]` ("Province / State") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-047 — `personal[postal_code]` ("Post Code/Zip Code") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[postal_code]` ("Post Code/Zip Code") set to a single character.
  3. Save with `personal[postal_code]` ("Post Code/Zip Code") set to a 255-character value.
  4. Save with `personal[postal_code]` ("Post Code/Zip Code") set to a value with leading and trailing whitespace.
  5. Save with `personal[postal_code]` ("Post Code/Zip Code") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-048 — `personal[description]` ("Biography") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `personal[description]` ("Biography") set to a 5,000-character body.
  3. Save with `personal[description]` ("Biography") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-049 — `user_notification` ("Notification") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `user_notification` ("Notification") set to checked.
  3. Save with `user_notification` ("Notification") set to unchecked.
  4. Save with `user_notification` ("Notification") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-050 — `login_info` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Save with `login_info` set to checked.
  3. Save with `login_info` set to unchecked.
  4. Save with `login_info` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-051 — modal form `tmpl-erp-new-employee` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Fill only: `personal[first_name]` ("First Name *"), `personal[last_name]` ("Last Name *"), `user_email` ("Email *"), `work[type]` ("Employee Type *"), `work[status]` ("Employee Status *"), `work[hiring_date]` ("Date of Hire *"), `work[department]` ("Department *"), `work[designation]` ("Job Title *").
  3. Submit.
- **Expected:** The record saves. The 34 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_PEOPLE-T2-052 — `employee[]` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employee-row`.
  2. Save with `employee[]` set to checked.
  3. Save with `employee[]` set to unchecked.
  4. Save with `employee[]` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-053 — modal form `tmpl-erp-employee-row` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employee-row`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_PEOPLE-T2-054 — `date` ("Date *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-type`.
  2. Save with `date` ("Date *") set to today.
  3. Save with `date` ("Date *") set to a leap day (29 Feb).
  4. Save with `date` ("Date *") set to a date before the company financial-year start.
  5. Save with `date` ("Date *") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-055 — `type` ("Employment Type") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-type`.
  2. Save with `type` ("Employment Type") set to the first real option.
  3. Save with `type` ("Employment Type") set to the last option.
  4. Save with `type` ("Employment Type") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-056 — `comment` ("Comment") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-type`.
  2. Save with `comment` ("Comment") set to a 5,000-character body.
  3. Save with `comment` ("Comment") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-057 — modal form `tmpl-erp-employment-type` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-type`.
  2. Fill only: `date` ("Date *").
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_PEOPLE-T2-058 — `date` ("Date *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-status`.
  2. Save with `date` ("Date *") set to today.
  3. Save with `date` ("Date *") set to a leap day (29 Feb).
  4. Save with `date` ("Date *") set to a date before the company financial-year start.
  5. Save with `date` ("Date *") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-059 — `status` ("Employee Status") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-status`.
  2. Save with `status` ("Employee Status") set to the first real option.
  3. Save with `status` ("Employee Status") set to the last option.
  4. Save with `status` ("Employee Status") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-060 — `comment` ("Comment") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-status`.
  2. Save with `comment` ("Comment") set to a 5,000-character body.
  3. Save with `comment` ("Comment") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-061 — modal form `tmpl-erp-employment-status` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-status`.
  2. Fill only: `date` ("Date *").
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_PEOPLE-T2-062 — `date` ("Date *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation`.
  2. Save with `date` ("Date *") set to today.
  3. Save with `date` ("Date *") set to a leap day (29 Feb).
  4. Save with `date` ("Date *") set to a date before the company financial-year start.
  5. Save with `date` ("Date *") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-063 — `pay_rate` ("Pay Rate *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation`.
  2. Save with `pay_rate` ("Pay Rate *") set to a single character.
  3. Save with `pay_rate` ("Pay Rate *") set to a 255-character value.
  4. Save with `pay_rate` ("Pay Rate *") set to a value with leading and trailing whitespace.
  5. Save with `pay_rate` ("Pay Rate *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-064 — `pay_type` ("Pay Type *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation`.
  2. Save with `pay_type` ("Pay Type *") set to the first real option.
  3. Save with `pay_type` ("Pay Type *") set to the last option.
  4. Save with `pay_type` ("Pay Type *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-065 — `change-reason` ("Change Reason") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation`.
  2. Save with `change-reason` ("Change Reason") set to the first real option.
  3. Save with `change-reason` ("Change Reason") set to the last option.
  4. Save with `change-reason` ("Change Reason") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-066 — `comment` ("Comment") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation`.
  2. Save with `comment` ("Comment") set to a 5,000-character body.
  3. Save with `comment` ("Comment") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-067 — modal form `tmpl-erp-employment-compensation` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation`.
  2. Fill only: `date` ("Date *"), `pay_rate` ("Pay Rate *"), `pay_type` ("Pay Type *").
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_PEOPLE-T2-068 — `date` ("Date *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-jobinfo`.
  2. Save with `date` ("Date *") set to today.
  3. Save with `date` ("Date *") set to a leap day (29 Feb).
  4. Save with `date` ("Date *") set to a date before the company financial-year start.
  5. Save with `date` ("Date *") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-069 — `location` ("Location") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-jobinfo`.
  2. Save with `location` ("Location") set to the first real option.
  3. Save with `location` ("Location") set to the last option.
  4. Save with `location` ("Location") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-070 — `department` ("Department") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-jobinfo`.
  2. Save with `department` ("Department") set to the first real option.
  3. Save with `department` ("Department") set to the last option.
  4. Save with `department` ("Department") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-071 — `designation` ("Job Title") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-jobinfo`.
  2. Save with `designation` ("Job Title") set to the first real option.
  3. Save with `designation` ("Job Title") set to the last option.
  4. Save with `designation` ("Job Title") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-072 — `reporting_to` ("Reporting To") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-jobinfo`.
  2. Save with `reporting_to` ("Reporting To") set to the first real option.
  3. Save with `reporting_to` ("Reporting To") set to the last option.
  4. Save with `reporting_to` ("Reporting To") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-073 — modal form `tmpl-erp-employment-jobinfo` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-jobinfo`.
  2. Fill only: `date` ("Date *").
  3. Submit.
- **Expected:** The record saves. The 4 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_PEOPLE-T2-074 — `company_name` ("Previous Company *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-work-experience`.
  2. Save with `company_name` ("Previous Company *") set to a single character.
  3. Save with `company_name` ("Previous Company *") set to a 255-character value.
  4. Save with `company_name` ("Previous Company *") set to a value with leading and trailing whitespace.
  5. Save with `company_name` ("Previous Company *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-075 — `job_title` ("Job Title *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-work-experience`.
  2. Save with `job_title` ("Job Title *") set to a single character.
  3. Save with `job_title` ("Job Title *") set to a 255-character value.
  4. Save with `job_title` ("Job Title *") set to a value with leading and trailing whitespace.
  5. Save with `job_title` ("Job Title *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-076 — `from` ("From *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-work-experience`.
  2. Save with `from` ("From *") set to a single character.
  3. Save with `from` ("From *") set to a 255-character value.
  4. Save with `from` ("From *") set to a value with leading and trailing whitespace.
  5. Save with `from` ("From *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-077 — `to` ("To *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-work-experience`.
  2. Save with `to` ("To *") set to a single character.
  3. Save with `to` ("To *") set to a 255-character value.
  4. Save with `to` ("To *") set to a value with leading and trailing whitespace.
  5. Save with `to` ("To *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-078 — `description` ("Job Description") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-work-experience`.
  2. Save with `description` ("Job Description") set to a 5,000-character body.
  3. Save with `description` ("Job Description") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-079 — modal form `tmpl-erp-employment-work-experience` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-work-experience`.
  2. Fill only: `company_name` ("Previous Company *"), `job_title` ("Job Title *"), `from` ("From *"), `to` ("To *").
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_PEOPLE-T2-080 — `school` ("School Name *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. Save with `school` ("School Name *") set to a single character.
  3. Save with `school` ("School Name *") set to a 255-character value.
  4. Save with `school` ("School Name *") set to a value with leading and trailing whitespace.
  5. Save with `school` ("School Name *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-081 — `degree` ("Degree *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. Save with `degree` ("Degree *") set to a single character.
  3. Save with `degree` ("Degree *") set to a 255-character value.
  4. Save with `degree` ("Degree *") set to a value with leading and trailing whitespace.
  5. Save with `degree` ("Degree *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-082 — `field` ("Field of Study *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. Save with `field` ("Field of Study *") set to a single character.
  3. Save with `field` ("Field of Study *") set to a 255-character value.
  4. Save with `field` ("Field of Study *") set to a value with leading and trailing whitespace.
  5. Save with `field` ("Field of Study *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-083 — `finished` ("Year of Completion *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. Save with `finished` ("Year of Completion *") set to 0.
  3. Save with `finished` ("Year of Completion *") set to a negative value.
  4. Save with `finished` ("Year of Completion *") set to a decimal where an integer is expected.
  5. Save with `finished` ("Year of Completion *") set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-084 — `result_type` ("Result type *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. Save with `result_type` ("Result type *") set to the first real option.
  3. Save with `result_type` ("Result type *") set to the last option.
  4. Save with `result_type` ("Result type *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-085 — `gpa` ("Result (Grade) *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. Save with `gpa` ("Result (Grade) *") set to a single character.
  3. Save with `gpa` ("Result (Grade) *") set to a 255-character value.
  4. Save with `gpa` ("Result (Grade) *") set to a value with leading and trailing whitespace.
  5. Save with `gpa` ("Result (Grade) *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-086 — `scale` ("Scale (Out of) *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. Save with `scale` ("Scale (Out of) *") set to 0.
  3. Save with `scale` ("Scale (Out of) *") set to a negative value.
  4. Save with `scale` ("Scale (Out of) *") set to a decimal where an integer is expected.
  5. Save with `scale` ("Scale (Out of) *") set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-087 — `notes` ("Notes") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. Save with `notes` ("Notes") set to a 5,000-character body.
  3. Save with `notes` ("Notes") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-088 — `interest` ("Interests") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. Save with `interest` ("Interests") set to a 5,000-character body.
  3. Save with `interest` ("Interests") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-089 — `expiration_date` ("Expiration date") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. Save with `expiration_date` ("Expiration date") set to today.
  3. Save with `expiration_date` ("Expiration date") set to a leap day (29 Feb).
  4. Save with `expiration_date` ("Expiration date") set to a date before the company financial-year start.
  5. Save with `expiration_date` ("Expiration date") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-090 — modal form `tmpl-erp-employment-education` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. Fill only: `school` ("School Name *"), `degree` ("Degree *"), `field` ("Field of Study *"), `finished` ("Year of Completion *"), `result_type` ("Result type *"), `gpa` ("Result (Grade) *"), `scale` ("Scale (Out of) *").
  3. Submit.
- **Expected:** The record saves. The 3 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_PEOPLE-T2-091 — `performance_date` ("Review Date *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-reviews`.
  2. Save with `performance_date` ("Review Date *") set to today.
  3. Save with `performance_date` ("Review Date *") set to a leap day (29 Feb).
  4. Save with `performance_date` ("Review Date *") set to a date before the company financial-year start.
  5. Save with `performance_date` ("Review Date *") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-092 — `reporting_to` ("Reporting To") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-reviews`.
  2. Save with `reporting_to` ("Reporting To") set to the first real option.
  3. Save with `reporting_to` ("Reporting To") set to the last option.
  4. Save with `reporting_to` ("Reporting To") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-093 — `job_knowledge` ("Job Knowledge") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-reviews`.
  2. Save with `job_knowledge` ("Job Knowledge") set to the first real option.
  3. Save with `job_knowledge` ("Job Knowledge") set to the last option.
  4. Save with `job_knowledge` ("Job Knowledge") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-094 — `work_quality` ("Work Quality") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-reviews`.
  2. Save with `work_quality` ("Work Quality") set to the first real option.
  3. Save with `work_quality` ("Work Quality") set to the last option.
  4. Save with `work_quality` ("Work Quality") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-095 — `attendance` ("Attendance/Punctuality") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-reviews`.
  2. Save with `attendance` ("Attendance/Punctuality") set to the first real option.
  3. Save with `attendance` ("Attendance/Punctuality") set to the last option.
  4. Save with `attendance` ("Attendance/Punctuality") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-096 — `communication` ("Communication/Listening") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-reviews`.
  2. Save with `communication` ("Communication/Listening") set to the first real option.
  3. Save with `communication` ("Communication/Listening") set to the last option.
  4. Save with `communication` ("Communication/Listening") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-097 — `dependablity` ("Dependability") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-reviews`.
  2. Save with `dependablity` ("Dependability") set to the first real option.
  3. Save with `dependablity` ("Dependability") set to the last option.
  4. Save with `dependablity` ("Dependability") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-098 — modal form `tmpl-erp-employment-performance-reviews` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-reviews`.
  2. Fill only: `performance_date` ("Review Date *").
  3. Submit.
- **Expected:** The record saves. The 6 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_PEOPLE-T2-099 — `performance_date` ("Reference Date *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-comments`.
  2. Save with `performance_date` ("Reference Date *") set to today.
  3. Save with `performance_date` ("Reference Date *") set to a leap day (29 Feb).
  4. Save with `performance_date` ("Reference Date *") set to a date before the company financial-year start.
  5. Save with `performance_date` ("Reference Date *") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-100 — `reviewer` ("Reviewer") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-comments`.
  2. Save with `reviewer` ("Reviewer") set to the first real option.
  3. Save with `reviewer` ("Reviewer") set to the last option.
  4. Save with `reviewer` ("Reviewer") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-101 — `comments` ("Comments") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-comments`.
  2. Save with `comments` ("Comments") set to a 5,000-character body.
  3. Save with `comments` ("Comments") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-102 — modal form `tmpl-erp-employment-performance-comments` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-comments`.
  2. Fill only: `performance_date` ("Reference Date *").
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_PEOPLE-T2-103 — `performance_date` ("Set Date *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-goals`.
  2. Save with `performance_date` ("Set Date *") set to today.
  3. Save with `performance_date` ("Set Date *") set to a leap day (29 Feb).
  4. Save with `performance_date` ("Set Date *") set to a date before the company financial-year start.
  5. Save with `performance_date` ("Set Date *") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-104 — `completion_date` ("Completion Date *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-goals`.
  2. Save with `completion_date` ("Completion Date *") set to today.
  3. Save with `completion_date` ("Completion Date *") set to a leap day (29 Feb).
  4. Save with `completion_date` ("Completion Date *") set to a date before the company financial-year start.
  5. Save with `completion_date` ("Completion Date *") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-105 — `goal_description` ("Goal Description") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-goals`.
  2. Save with `goal_description` ("Goal Description") set to a 5,000-character body.
  3. Save with `goal_description` ("Goal Description") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-106 — `employee_assessment` ("Employee Assessment") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-goals`.
  2. Save with `employee_assessment` ("Employee Assessment") set to a 5,000-character body.
  3. Save with `employee_assessment` ("Employee Assessment") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-107 — `supervisor` ("Supervisor") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-goals`.
  2. Save with `supervisor` ("Supervisor") set to the first real option.
  3. Save with `supervisor` ("Supervisor") set to the last option.
  4. Save with `supervisor` ("Supervisor") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-108 — `supervisor_assessment` ("Supervisor Assessment") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-goals`.
  2. Save with `supervisor_assessment` ("Supervisor Assessment") set to a 5,000-character body.
  3. Save with `supervisor_assessment` ("Supervisor Assessment") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-109 — modal form `tmpl-erp-employment-performance-goals` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-goals`.
  2. Fill only: `performance_date` ("Set Date *"), `completion_date` ("Completion Date *").
  3. Submit.
- **Expected:** The record saves. The 4 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_PEOPLE-T2-110 — `name` ("Name *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-dependent`.
  2. Save with `name` ("Name *") set to a single character.
  3. Save with `name` ("Name *") set to a 255-character value.
  4. Save with `name` ("Name *") set to a value with leading and trailing whitespace.
  5. Save with `name` ("Name *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-111 — `relation` ("Relationship *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-dependent`.
  2. Save with `relation` ("Relationship *") set to a single character.
  3. Save with `relation` ("Relationship *") set to a 255-character value.
  4. Save with `relation` ("Relationship *") set to a value with leading and trailing whitespace.
  5. Save with `relation` ("Relationship *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-112 — `dob` ("Date of Birth") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-dependent`.
  2. Save with `dob` ("Date of Birth") set to a single character.
  3. Save with `dob` ("Date of Birth") set to a 255-character value.
  4. Save with `dob` ("Date of Birth") set to a value with leading and trailing whitespace.
  5. Save with `dob` ("Date of Birth") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-113 — modal form `tmpl-erp-employment-dependent` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-dependent`.
  2. Fill only: `name` ("Name *"), `relation` ("Relationship *").
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_PEOPLE-T2-114 — `title` ("Department Title *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-dept`.
  2. Save with `title` ("Department Title *") set to a single character.
  3. Save with `title` ("Department Title *") set to a 255-character value.
  4. Save with `title` ("Department Title *") set to a value with leading and trailing whitespace.
  5. Save with `title` ("Department Title *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-115 — `dept-desc` ("Description") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-dept`.
  2. Save with `dept-desc` ("Description") set to a 5,000-character body.
  3. Save with `dept-desc` ("Description") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-116 — `lead` ("Department Manager ...") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-dept`.
  2. Save with `lead` ("Department Manager ...") set to the first real option.
  3. Save with `lead` ("Department Manager ...") set to the last option.
  4. Save with `lead` ("Department Manager ...") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-117 — `parent` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-dept`.
  2. Save with `parent` set to the first real option.
  3. Save with `parent` set to the last option.
  4. Save with `parent` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-118 — modal form `tmpl-erp-new-dept` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-dept`.
  2. Fill only: `title` ("Department Title *").
  3. Submit.
- **Expected:** The record saves. The 3 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_PEOPLE-T2-119 — `title` ("Designation Title *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-desig`.
  2. Save with `title` ("Designation Title *") set to a single character.
  3. Save with `title` ("Designation Title *") set to a 255-character value.
  4. Save with `title` ("Designation Title *") set to a value with leading and trailing whitespace.
  5. Save with `title` ("Designation Title *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-120 — `desig-desc` ("Description") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-desig`.
  2. Save with `desig-desc` ("Description") set to a 5,000-character body.
  3. Save with `desig-desc` ("Description") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-121 — modal form `tmpl-erp-new-desig` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-new-desig`.
  2. Fill only: `title` ("Designation Title *").
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_PEOPLE-T2-122 — `terminate_date` ("Termination Date *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-terminate`.
  2. Save with `terminate_date` ("Termination Date *") set to today.
  3. Save with `terminate_date` ("Termination Date *") set to a leap day (29 Feb).
  4. Save with `terminate_date` ("Termination Date *") set to a date before the company financial-year start.
  5. Save with `terminate_date` ("Termination Date *") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-123 — `termination_type` ("Termination Type *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-terminate`.
  2. Save with `termination_type` ("Termination Type *") set to the first real option.
  3. Save with `termination_type` ("Termination Type *") set to the last option.
  4. Save with `termination_type` ("Termination Type *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-124 — `termination_reason` ("Termination Reason *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-terminate`.
  2. Save with `termination_reason` ("Termination Reason *") set to the first real option.
  3. Save with `termination_reason` ("Termination Reason *") set to the last option.
  4. Save with `termination_reason` ("Termination Reason *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-125 — `eligible_for_rehire` ("Eligible for Rehire *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-terminate`.
  2. Save with `eligible_for_rehire` ("Eligible for Rehire *") set to the first real option.
  3. Save with `eligible_for_rehire` ("Eligible for Rehire *") set to the last option.
  4. Save with `eligible_for_rehire` ("Eligible for Rehire *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-126 — `csv_file` ("CSV File *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employee-import-csv`.
  2. Save with `csv_file` ("CSV File *") set to a single character.
  3. Save with `csv_file` ("CSV File *") set to a 255-character value.
  4. Save with `csv_file` ("CSV File *") set to a value with leading and trailing whitespace.
  5. Save with `csv_file` ("CSV File *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-127 — `selecctall` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employee-export-csv`.
  2. Save with `selecctall` set to checked.
  3. Save with `selecctall` set to unchecked.
  4. Save with `selecctall` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-128 — modal form `tmpl-erp-employee-export-csv` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employee-export-csv`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_PEOPLE-T2-129 — `date` ("Date *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation-history`.
  2. Save with `date` ("Date *") set to today.
  3. Save with `date` ("Date *") set to a leap day (29 Feb).
  4. Save with `date` ("Date *") set to a date before the company financial-year start.
  5. Save with `date` ("Date *") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-130 — `type` ("Pay Rate *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation-history`.
  2. Save with `type` ("Pay Rate *") set to a single character.
  3. Save with `type` ("Pay Rate *") set to a 255-character value.
  4. Save with `type` ("Pay Rate *") set to a value with leading and trailing whitespace.
  5. Save with `type` ("Pay Rate *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-131 — `category` ("Pay Type *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation-history`.
  2. Save with `category` ("Pay Type *") set to the first real option.
  3. Save with `category` ("Pay Type *") set to the last option.
  4. Save with `category` ("Pay Type *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-132 — `data` ("Change Reason") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation-history`.
  2. Save with `data` ("Change Reason") set to the first real option.
  3. Save with `data` ("Change Reason") set to the last option.
  4. Save with `data` ("Change Reason") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-133 — `comment` ("Comment") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation-history`.
  2. Save with `comment` ("Comment") set to a 5,000-character body.
  3. Save with `comment` ("Comment") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-134 — modal form `tmpl-erp-employment-compensation-history` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation-history`.
  2. Fill only: `date` ("Date *"), `type` ("Pay Rate *"), `category` ("Pay Type *").
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_PEOPLE-T2-135 — `date` ("Date *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-job-info-history`.
  2. Save with `date` ("Date *") set to today.
  3. Save with `date` ("Date *") set to a leap day (29 Feb).
  4. Save with `date` ("Date *") set to a date before the company financial-year start.
  5. Save with `date` ("Date *") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-136 — `type` ("Location") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-job-info-history`.
  2. Save with `type` ("Location") set to the first real option.
  3. Save with `type` ("Location") set to the last option.
  4. Save with `type` ("Location") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-137 — `category` ("Department") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-job-info-history`.
  2. Save with `category` ("Department") set to the first real option.
  3. Save with `category` ("Department") set to the last option.
  4. Save with `category` ("Department") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-138 — `comment` ("Job Title") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-job-info-history`.
  2. Save with `comment` ("Job Title") set to the first real option.
  3. Save with `comment` ("Job Title") set to the last option.
  4. Save with `comment` ("Job Title") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-139 — `data` ("Reporting To") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-job-info-history`.
  2. Save with `data` ("Reporting To") set to the first real option.
  3. Save with `data` ("Reporting To") set to the last option.
  4. Save with `data` ("Reporting To") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-140 — modal form `tmpl-erp-employment-job-info-history` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-job-info-history`.
  2. Fill only: `date` ("Date *").
  3. Submit.
- **Expected:** The record saves. The 4 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_PEOPLE-T2-141 — `date` ("Date *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-type-history`.
  2. Save with `date` ("Date *") set to today.
  3. Save with `date` ("Date *") set to a leap day (29 Feb).
  4. Save with `date` ("Date *") set to a date before the company financial-year start.
  5. Save with `date` ("Date *") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-142 — `type` ("Employment Type") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-type-history`.
  2. Save with `type` ("Employment Type") set to the first real option.
  3. Save with `type` ("Employment Type") set to the last option.
  4. Save with `type` ("Employment Type") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-143 — `comment` ("Comment") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-type-history`.
  2. Save with `comment` ("Comment") set to a 5,000-character body.
  3. Save with `comment` ("Comment") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-144 — modal form `tmpl-erp-employment-type-history` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-employment-type-history`.
  2. Fill only: `date` ("Date *").
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_PEOPLE-T2-145 — `desig[]` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-desig-row`.
  2. Save with `desig[]` set to checked.
  3. Save with `desig[]` set to unchecked.
  4. Save with `desig[]` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-146 — modal form `tmpl-erp-desig-row` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open modal form `tmpl-erp-desig-row`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_PEOPLE-T2-147 — `filter_designation` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people`.
  2. Save with `filter_designation` set to the first real option.
  3. Save with `filter_designation` set to the last option.
  4. Save with `filter_designation` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-148 — `filter_department` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people`.
  2. Save with `filter_department` set to the first real option.
  3. Save with `filter_department` set to the last option.
  4. Save with `filter_department` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-149 — `filter_employment_type` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people`.
  2. Save with `filter_employment_type` set to the first real option.
  3. Save with `filter_employment_type` set to the last option.
  4. Save with `filter_employment_type` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-150 — `hide_filter` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people`.
  2. Save with `hide_filter` set to a single character.
  3. Save with `hide_filter` set to a 255-character value.
  4. Save with `hide_filter` set to a value with leading and trailing whitespace.
  5. Save with `hide_filter` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-151 — `reset_filter` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people`.
  2. Save with `reset_filter` set to a single character.
  3. Save with `reset_filter` set to a 255-character value.
  4. Save with `reset_filter` set to a value with leading and trailing whitespace.
  5. Save with `reset_filter` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-152 — `filter_employee` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people`.
  2. Save with `filter_employee` set to a single character.
  3. Save with `filter_employee` set to a 255-character value.
  4. Save with `filter_employee` set to a value with leading and trailing whitespace.
  5. Save with `filter_employee` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-153 — `admin.php?page=erp-hr&section=people` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 6 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_PEOPLE-T2-154 — `filter_designation` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=employee`.
  2. Save with `filter_designation` set to the first real option.
  3. Save with `filter_designation` set to the last option.
  4. Save with `filter_designation` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-155 — `filter_department` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=employee`.
  2. Save with `filter_department` set to the first real option.
  3. Save with `filter_department` set to the last option.
  4. Save with `filter_department` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-156 — `filter_employment_type` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=employee`.
  2. Save with `filter_employment_type` set to the first real option.
  3. Save with `filter_employment_type` set to the last option.
  4. Save with `filter_employment_type` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-157 — `hide_filter` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=employee`.
  2. Save with `hide_filter` set to a single character.
  3. Save with `hide_filter` set to a 255-character value.
  4. Save with `hide_filter` set to a value with leading and trailing whitespace.
  5. Save with `hide_filter` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-158 — `reset_filter` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=employee`.
  2. Save with `reset_filter` set to a single character.
  3. Save with `reset_filter` set to a 255-character value.
  4. Save with `reset_filter` set to a value with leading and trailing whitespace.
  5. Save with `reset_filter` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-159 — `filter_employee` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=employee`.
  2. Save with `filter_employee` set to a single character.
  3. Save with `filter_employee` set to a 255-character value.
  4. Save with `filter_employee` set to a value with leading and trailing whitespace.
  5. Save with `filter_employee` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-160 — `admin.php?page=erp-hr&section=people&sub-section=employee` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=employee`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 6 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_PEOPLE-T2-161 — `desig[]` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  2. Save with `desig[]` set to checked.
  3. Save with `desig[]` set to unchecked.
  4. Save with `desig[]` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-162 — `desig[]` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  2. Save with `desig[]` set to checked.
  3. Save with `desig[]` set to unchecked.
  4. Save with `desig[]` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-163 — `desig[]` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  2. Save with `desig[]` set to checked.
  3. Save with `desig[]` set to unchecked.
  4. Save with `desig[]` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-164 — `desig[]` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  2. Save with `desig[]` set to checked.
  3. Save with `desig[]` set to unchecked.
  4. Save with `desig[]` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-165 — `desig[]` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  2. Save with `desig[]` set to checked.
  3. Save with `desig[]` set to unchecked.
  4. Save with `desig[]` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-166 — `desig[]` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  2. Save with `desig[]` set to checked.
  3. Save with `desig[]` set to unchecked.
  4. Save with `desig[]` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-167 — `desig[]` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  2. Save with `desig[]` set to checked.
  3. Save with `desig[]` set to unchecked.
  4. Save with `desig[]` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-168 — `desig[]` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  2. Save with `desig[]` set to checked.
  3. Save with `desig[]` set to unchecked.
  4. Save with `desig[]` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-169 — `desig[]` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  2. Save with `desig[]` set to checked.
  3. Save with `desig[]` set to unchecked.
  4. Save with `desig[]` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-170 — `desig[]` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  2. Save with `desig[]` set to checked.
  3. Save with `desig[]` set to unchecked.
  4. Save with `desig[]` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-171 — `desig[]` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  2. Save with `desig[]` set to checked.
  3. Save with `desig[]` set to unchecked.
  4. Save with `desig[]` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-172 — `desig[]` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  2. Save with `desig[]` set to checked.
  3. Save with `desig[]` set to unchecked.
  4. Save with `desig[]` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-173 — `desig[]` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  2. Save with `desig[]` set to checked.
  3. Save with `desig[]` set to unchecked.
  4. Save with `desig[]` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-174 — `desig[]` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  2. Save with `desig[]` set to checked.
  3. Save with `desig[]` set to unchecked.
  4. Save with `desig[]` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-175 — `desig[]` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  2. Save with `desig[]` set to checked.
  3. Save with `desig[]` set to unchecked.
  4. Save with `desig[]` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-176 — `desig[]` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  2. Save with `desig[]` set to checked.
  3. Save with `desig[]` set to unchecked.
  4. Save with `desig[]` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-177 — `desig[]` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  2. Save with `desig[]` set to checked.
  3. Save with `desig[]` set to unchecked.
  4. Save with `desig[]` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-178 — `desig[]` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  2. Save with `desig[]` set to checked.
  3. Save with `desig[]` set to unchecked.
  4. Save with `desig[]` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-179 — `desig[]` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  2. Save with `desig[]` set to checked.
  3. Save with `desig[]` set to unchecked.
  4. Save with `desig[]` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-180 — `desig[]` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  2. Save with `desig[]` set to checked.
  3. Save with `desig[]` set to unchecked.
  4. Save with `desig[]` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_PEOPLE-T2-181 — `admin.php?page=erp-hr&section=people&sub-section=designation` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier2 @hrm-people @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 20 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.
