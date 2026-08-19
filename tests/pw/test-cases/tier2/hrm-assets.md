# Tier 2 — hrm-assets

Edge cases — boundaries, optional-field omission, empty states, unusual but legal input.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**64 cases** (63 derived from the harness, 1 hand-written business flows).

## Business flows

#### HRM_ASSETS-F2-001 — Allotting more units than exist is refused

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset-allottment`
- **Tags:** @tier2 @hrm-assets @pro @edge
- **Steps:**
  1. With an asset of total N, allot N+1 units.
- **Expected:** Refused with a stock message; availability never goes negative.
- **Oracle:** UI message + the Available/Total figure.

## Screen & field coverage

#### HRM_ASSETS-T2-001 — `category_id` ("Category *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-add-asset`.
  2. Save with `category_id` ("Category *") set to the first real option.
  3. Save with `category_id` ("Category *") set to the last option.
  4. Save with `category_id` ("Category *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-002 — `item_group` ("Item Group *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-add-asset`.
  2. Save with `item_group` ("Item Group *") set to the first real option.
  3. Save with `item_group` ("Item Group *") set to the last option.
  4. Save with `item_group` ("Item Group *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-003 — `item` ("Item Name *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-add-asset`.
  2. Save with `item` ("Item Name *") set to the first real option.
  3. Save with `item` ("Item Name *") set to the last option.
  4. Save with `item` ("Item Name *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-004 — `given_date` ("Given Date *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-add-asset`.
  2. Save with `given_date` ("Given Date *") set to today.
  3. Save with `given_date` ("Given Date *") set to a leap day (29 Feb).
  4. Save with `given_date` ("Given Date *") set to a date before the company financial-year start.
  5. Save with `given_date` ("Given Date *") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-005 — `is_returnable` ("Returnable?") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-add-asset`.
  2. Save with `is_returnable` ("Returnable?") set to checked.
  3. Save with `is_returnable` ("Returnable?") set to unchecked.
  4. Save with `is_returnable` ("Returnable?") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-006 — `return_date` ("Return Date") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-add-asset`.
  2. Save with `return_date` ("Return Date") set to today.
  3. Save with `return_date` ("Return Date") set to a leap day (29 Feb).
  4. Save with `return_date` ("Return Date") set to a date before the company financial-year start.
  5. Save with `return_date` ("Return Date") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-007 — modal form `tmpl-erp-hr-emp-add-asset` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-add-asset`.
  2. Fill only: `category_id` ("Category *"), `item_group` ("Item Group *"), `item` ("Item Name *"), `given_date` ("Given Date *").
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_ASSETS-T2-008 — `category_id` ("Category *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-request-asset`.
  2. Save with `category_id` ("Category *") set to the first real option.
  3. Save with `category_id` ("Category *") set to the last option.
  4. Save with `category_id` ("Category *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-009 — `item_group` ("Item Name *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-request-asset`.
  2. Save with `item_group` ("Item Name *") set to the first real option.
  3. Save with `item_group` ("Item Name *") set to the last option.
  4. Save with `item_group` ("Item Name *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-010 — `not_in_list` ("If Unavailable") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-request-asset`.
  2. Save with `not_in_list` ("If Unavailable") set to checked.
  3. Save with `not_in_list` ("If Unavailable") set to unchecked.
  4. Save with `not_in_list` ("If Unavailable") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-011 — `request_desc` ("Request Details") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-request-asset`.
  2. Save with `request_desc` ("Request Details") set to a 5,000-character body.
  3. Save with `request_desc` ("Request Details") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-012 — modal form `tmpl-erp-hr-emp-request-asset` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-request-asset`.
  2. Fill only: `category_id` ("Category *"), `item_group` ("Item Name *").
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_ASSETS-T2-013 — `category_id` ("Category *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-allotment-new`.
  2. Save with `category_id` ("Category *") set to the first real option.
  3. Save with `category_id` ("Category *") set to the last option.
  4. Save with `category_id` ("Category *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-014 — `item_group` ("Item Name *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-allotment-new`.
  2. Save with `item_group` ("Item Name *") set to the first real option.
  3. Save with `item_group` ("Item Name *") set to the last option.
  4. Save with `item_group` ("Item Name *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-015 — `item` ("Item *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-allotment-new`.
  2. Save with `item` ("Item *") set to the first real option.
  3. Save with `item` ("Item *") set to the last option.
  4. Save with `item` ("Item *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-016 — `allotted_to` ("Allot To *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-allotment-new`.
  2. Save with `allotted_to` ("Allot To *") set to the first real option.
  3. Save with `allotted_to` ("Allot To *") set to the last option.
  4. Save with `allotted_to` ("Allot To *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-017 — `given_date` ("Given Date *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-allotment-new`.
  2. Save with `given_date` ("Given Date *") set to today.
  3. Save with `given_date` ("Given Date *") set to a leap day (29 Feb).
  4. Save with `given_date` ("Given Date *") set to a date before the company financial-year start.
  5. Save with `given_date` ("Given Date *") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-018 — `is_returnable` ("Returnable?") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-allotment-new`.
  2. Save with `is_returnable` ("Returnable?") set to checked.
  3. Save with `is_returnable` ("Returnable?") set to unchecked.
  4. Save with `is_returnable` ("Returnable?") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-019 — `return_date` ("Return Date *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-allotment-new`.
  2. Save with `return_date` ("Return Date *") set to today.
  3. Save with `return_date` ("Return Date *") set to a leap day (29 Feb).
  4. Save with `return_date` ("Return Date *") set to a date before the company financial-year start.
  5. Save with `return_date` ("Return Date *") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-020 — modal form `tmpl-erp-allotment-new` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-allotment-new`.
  2. Fill only: `category_id` ("Category *"), `item_group` ("Item Name *"), `item` ("Item *"), `allotted_to` ("Allot To *"), `given_date` ("Given Date *"), `return_date` ("Return Date *").
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_ASSETS-T2-021 — `return_date` ("Return Date *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-return`.
  2. Save with `return_date` ("Return Date *") set to today.
  3. Save with `return_date` ("Return Date *") set to a leap day (29 Feb).
  4. Save with `return_date` ("Return Date *") set to a date before the company financial-year start.
  5. Save with `return_date` ("Return Date *") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-022 — `return_note` ("Return Note") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-return`.
  2. Save with `return_note` ("Return Note") set to a 5,000-character body.
  3. Save with `return_note` ("Return Note") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-023 — `is_dissmissed` ("Lost/Damaged") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-return`.
  2. Save with `is_dissmissed` ("Lost/Damaged") set to checked.
  3. Save with `is_dissmissed` ("Lost/Damaged") set to unchecked.
  4. Save with `is_dissmissed` ("Lost/Damaged") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-024 — modal form `tmpl-erp-asset-return` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-return`.
  2. Fill only: `return_date` ("Return Date *").
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_ASSETS-T2-025 — `category_id` ("Category *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-request-reply`.
  2. Save with `category_id` ("Category *") set to the first real option.
  3. Save with `category_id` ("Category *") set to the last option.
  4. Save with `category_id` ("Category *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-026 — `item_group` ("Item Name *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-request-reply`.
  2. Save with `item_group` ("Item Name *") set to the first real option.
  3. Save with `item_group` ("Item Name *") set to the last option.
  4. Save with `item_group` ("Item Name *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-027 — `item` ("Item *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-request-reply`.
  2. Save with `item` ("Item *") set to the first real option.
  3. Save with `item` ("Item *") set to the last option.
  4. Save with `item` ("Item *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-028 — `given_date` ("Given Date *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-request-reply`.
  2. Save with `given_date` ("Given Date *") set to today.
  3. Save with `given_date` ("Given Date *") set to a leap day (29 Feb).
  4. Save with `given_date` ("Given Date *") set to a date before the company financial-year start.
  5. Save with `given_date` ("Given Date *") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-029 — `is_returnable` ("Returnable?") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-request-reply`.
  2. Save with `is_returnable` ("Returnable?") set to checked.
  3. Save with `is_returnable` ("Returnable?") set to unchecked.
  4. Save with `is_returnable` ("Returnable?") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-030 — `return_date` ("Return Date *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-request-reply`.
  2. Save with `return_date` ("Return Date *") set to today.
  3. Save with `return_date` ("Return Date *") set to a leap day (29 Feb).
  4. Save with `return_date` ("Return Date *") set to a date before the company financial-year start.
  5. Save with `return_date` ("Return Date *") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-031 — `reply_msg` ("Instructions") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-request-reply`.
  2. Save with `reply_msg` ("Instructions") set to a 5,000-character body.
  3. Save with `reply_msg` ("Instructions") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-032 — modal form `tmpl-erp-asset-request-reply` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-request-reply`.
  2. Fill only: `category_id` ("Category *"), `item_group` ("Item Name *"), `item` ("Item *"), `given_date` ("Given Date *"), `return_date` ("Return Date *").
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_ASSETS-T2-033 — `reject_reason` ("Reject Reason") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-request-reject`.
  2. Save with `reject_reason` ("Reject Reason") set to a 5,000-character body.
  3. Save with `reject_reason` ("Reject Reason") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-034 — modal form `tmpl-erp-asset-request-reject` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-request-reject`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_ASSETS-T2-035 — `category_id` ("Category *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-new`.
  2. Save with `category_id` ("Category *") set to the first real option.
  3. Save with `category_id` ("Category *") set to the last option.
  4. Save with `category_id` ("Category *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-036 — `item_group` ("Item Name *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-new`.
  2. Save with `item_group` ("Item Name *") set to a single character.
  3. Save with `item_group` ("Item Name *") set to a 255-character value.
  4. Save with `item_group` ("Item Name *") set to a value with leading and trailing whitespace.
  5. Save with `item_group` ("Item Name *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-037 — `asset_type` ("Asset Type *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-new`.
  2. Save with `asset_type` ("Asset Type *") set to the first real option.
  3. Save with `asset_type` ("Asset Type *") set to the last option.
  4. Save with `asset_type` ("Asset Type *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-038 — `items[1][item_code]` ("Item Code *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-new`.
  2. Save with `items[1][item_code]` ("Item Code *") set to a single character.
  3. Save with `items[1][item_code]` ("Item Code *") set to a 255-character value.
  4. Save with `items[1][item_code]` ("Item Code *") set to a value with leading and trailing whitespace.
  5. Save with `items[1][item_code]` ("Item Code *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-039 — `items[1][model_no]` ("Model No") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-new`.
  2. Save with `items[1][model_no]` ("Model No") set to a single character.
  3. Save with `items[1][model_no]` ("Model No") set to a 255-character value.
  4. Save with `items[1][model_no]` ("Model No") set to a value with leading and trailing whitespace.
  5. Save with `items[1][model_no]` ("Model No") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-040 — `items[1][item_desc]` ("Description") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-new`.
  2. Save with `items[1][item_desc]` ("Description") set to a 5,000-character body.
  3. Save with `items[1][item_desc]` ("Description") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-041 — `items[1][item_serial]` ("Serial/License Info") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-new`.
  2. Save with `items[1][item_serial]` ("Serial/License Info") set to a 5,000-character body.
  3. Save with `items[1][item_serial]` ("Serial/License Info") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-042 — `items[1][manufacturer]` ("Manufacturer") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-new`.
  2. Save with `items[1][manufacturer]` ("Manufacturer") set to a single character.
  3. Save with `items[1][manufacturer]` ("Manufacturer") set to a 255-character value.
  4. Save with `items[1][manufacturer]` ("Manufacturer") set to a value with leading and trailing whitespace.
  5. Save with `items[1][manufacturer]` ("Manufacturer") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-043 — `items[1][price]` ("Price") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-new`.
  2. Save with `items[1][price]` ("Price") set to 0.
  3. Save with `items[1][price]` ("Price") set to a negative value.
  4. Save with `items[1][price]` ("Price") set to a decimal where an integer is expected.
  5. Save with `items[1][price]` ("Price") set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-044 — `items[1][date_warr]` ("Warranty Till") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-new`.
  2. Save with `items[1][date_warr]` ("Warranty Till") set to today.
  3. Save with `items[1][date_warr]` ("Warranty Till") set to a leap day (29 Feb).
  4. Save with `items[1][date_warr]` ("Warranty Till") set to a date before the company financial-year start.
  5. Save with `items[1][date_warr]` ("Warranty Till") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-045 — `items[1][date_exp]` ("Expiry Date") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-new`.
  2. Save with `items[1][date_exp]` ("Expiry Date") set to today.
  3. Save with `items[1][date_exp]` ("Expiry Date") set to a leap day (29 Feb).
  4. Save with `items[1][date_exp]` ("Expiry Date") set to a date before the company financial-year start.
  5. Save with `items[1][date_exp]` ("Expiry Date") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-046 — `items[1][allottable]` ("Allottable?") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-new`.
  2. Save with `items[1][allottable]` ("Allottable?") set to checked.
  3. Save with `items[1][allottable]` ("Allottable?") set to unchecked.
  4. Save with `items[1][allottable]` ("Allottable?") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-047 — modal form `tmpl-erp-asset-new` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-new`.
  2. Fill only: `category_id` ("Category *"), `item_group` ("Item Name *"), `asset_type` ("Asset Type *"), `items[1][item_code]` ("Item Code *").
  3. Submit.
- **Expected:** The record saves. The 8 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_ASSETS-T2-048 — `category_id` ("Category *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-edit`.
  2. Save with `category_id` ("Category *") set to the first real option.
  3. Save with `category_id` ("Category *") set to the last option.
  4. Save with `category_id` ("Category *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-049 — `item_group` ("Item Group *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-edit`.
  2. Save with `item_group` ("Item Group *") set to a single character.
  3. Save with `item_group` ("Item Group *") set to a 255-character value.
  4. Save with `item_group` ("Item Group *") set to a value with leading and trailing whitespace.
  5. Save with `item_group` ("Item Group *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-050 — `items[{{i}}][item_code]` ("Item Code *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-edit`.
  2. Save with `items[{{i}}][item_code]` ("Item Code *") set to a single character.
  3. Save with `items[{{i}}][item_code]` ("Item Code *") set to a 255-character value.
  4. Save with `items[{{i}}][item_code]` ("Item Code *") set to a value with leading and trailing whitespace.
  5. Save with `items[{{i}}][item_code]` ("Item Code *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-051 — `items[{{i}}][model_no]` ("Model No") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-edit`.
  2. Save with `items[{{i}}][model_no]` ("Model No") set to a single character.
  3. Save with `items[{{i}}][model_no]` ("Model No") set to a 255-character value.
  4. Save with `items[{{i}}][model_no]` ("Model No") set to a value with leading and trailing whitespace.
  5. Save with `items[{{i}}][model_no]` ("Model No") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-052 — `items[{{i}}][manufacturer]` ("Manufacturer") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-edit`.
  2. Save with `items[{{i}}][manufacturer]` ("Manufacturer") set to a single character.
  3. Save with `items[{{i}}][manufacturer]` ("Manufacturer") set to a 255-character value.
  4. Save with `items[{{i}}][manufacturer]` ("Manufacturer") set to a value with leading and trailing whitespace.
  5. Save with `items[{{i}}][manufacturer]` ("Manufacturer") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-053 — `items[{{i}}][price]` ("Price") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-edit`.
  2. Save with `items[{{i}}][price]` ("Price") set to a single character.
  3. Save with `items[{{i}}][price]` ("Price") set to a 255-character value.
  4. Save with `items[{{i}}][price]` ("Price") set to a value with leading and trailing whitespace.
  5. Save with `items[{{i}}][price]` ("Price") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-054 — `items[{{i}}][date_exp]` ("Expiry Date") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-edit`.
  2. Save with `items[{{i}}][date_exp]` ("Expiry Date") set to today.
  3. Save with `items[{{i}}][date_exp]` ("Expiry Date") set to a leap day (29 Feb).
  4. Save with `items[{{i}}][date_exp]` ("Expiry Date") set to a date before the company financial-year start.
  5. Save with `items[{{i}}][date_exp]` ("Expiry Date") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-055 — `items[{{i}}][date_warr]` ("Warranty Till") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-edit`.
  2. Save with `items[{{i}}][date_warr]` ("Warranty Till") set to today.
  3. Save with `items[{{i}}][date_warr]` ("Warranty Till") set to a leap day (29 Feb).
  4. Save with `items[{{i}}][date_warr]` ("Warranty Till") set to a date before the company financial-year start.
  5. Save with `items[{{i}}][date_warr]` ("Warranty Till") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-056 — `items[{{i}}][allottable]` ("Allottable?") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-edit`.
  2. Save with `items[{{i}}][allottable]` ("Allottable?") set to checked.
  3. Save with `items[{{i}}][allottable]` ("Allottable?") set to unchecked.
  4. Save with `items[{{i}}][allottable]` ("Allottable?") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-057 — `items[{{i}}][item_serial]` ("Serial/License Info") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-edit`.
  2. Save with `items[{{i}}][item_serial]` ("Serial/License Info") set to a 5,000-character body.
  3. Save with `items[{{i}}][item_serial]` ("Serial/License Info") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-058 — `items[{{i}}][item_desc]` ("Description") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-edit`.
  2. Save with `items[{{i}}][item_desc]` ("Description") set to a 5,000-character body.
  3. Save with `items[{{i}}][item_desc]` ("Description") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-059 — modal form `tmpl-erp-asset-edit` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-erp-asset-edit`.
  2. Fill only: `category_id` ("Category *"), `item_group` ("Item Group *"), `items[{{i}}][item_code]` ("Item Code *").
  3. Submit.
- **Expected:** The record saves. The 8 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_ASSETS-T2-060 — `cat_name` ("Category Name *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open modal form `tmpl-asset-category-new`.
  2. Save with `cat_name` ("Category Name *") set to a single character.
  3. Save with `cat_name` ("Category Name *") set to a 255-character value.
  4. Save with `cat_name` ("Category Name *") set to a value with leading and trailing whitespace.
  5. Save with `cat_name` ("Category Name *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-061 — `category_id` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=asset&sub-section=asset`.
  2. Save with `category_id` set to the first real option.
  3. Save with `category_id` set to the last option.
  4. Save with `category_id` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-062 — `filter_category` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=asset&sub-section=asset`.
  2. Save with `filter_category` set to a single character.
  3. Save with `filter_category` set to a 255-character value.
  4. Save with `filter_category` set to a value with leading and trailing whitespace.
  5. Save with `filter_category` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_ASSETS-T2-063 — `admin.php?page=erp-hr&section=asset&sub-section=asset` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier2 @hrm-assets @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=asset&sub-section=asset`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.
