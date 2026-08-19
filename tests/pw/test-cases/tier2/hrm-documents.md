# Tier 2 — hrm-documents

Edge cases — boundaries, optional-field omission, empty states, unusual but legal input.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**15 cases** (15 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### HRM_DOCUMENTS-T2-001 — `0` ("Home") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-documents @edge
- **Steps:**
  1. Open modal form `tmpl-erp-doc-tree-template`.
  2. Save with `0` ("Home") set to checked.
  3. Save with `0` ("Home") set to unchecked.
  4. Save with `0` ("Home") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_DOCUMENTS-T2-002 — modal form `tmpl-erp-doc-tree-template` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-documents @edge
- **Steps:**
  1. Open modal form `tmpl-erp-doc-tree-template`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_DOCUMENTS-T2-003 — `share_by` ("Share files by") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-documents @edge
- **Steps:**
  1. Open modal form `tmpl-erp-doc-share-template`.
  2. Save with `share_by` ("Share files by") set to the first real option.
  3. Save with `share_by` ("Share files by") set to the last option.
  4. Save with `share_by` ("Share files by") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_DOCUMENTS-T2-004 — `department` ("Department") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-documents @edge
- **Steps:**
  1. Open modal form `tmpl-erp-doc-share-template`.
  2. Save with `department` ("Department") set to the first real option.
  3. Save with `department` ("Department") set to the last option.
  4. Save with `department` ("Department") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_DOCUMENTS-T2-005 — `designation` ("Designation") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-documents @edge
- **Steps:**
  1. Open modal form `tmpl-erp-doc-share-template`.
  2. Save with `designation` ("Designation") set to the first real option.
  3. Save with `designation` ("Designation") set to the last option.
  4. Save with `designation` ("Designation") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_DOCUMENTS-T2-006 — `selected_emp[]` ("Selected employees") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-documents @edge
- **Steps:**
  1. Open modal form `tmpl-erp-doc-share-template`.
  2. Save with `selected_emp[]` ("Selected employees") set to the first real option.
  3. Save with `selected_emp[]` ("Selected employees") set to the last option.
  4. Save with `selected_emp[]` ("Selected employees") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_DOCUMENTS-T2-007 — modal form `tmpl-erp-doc-share-template` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-documents @edge
- **Steps:**
  1. Open modal form `tmpl-erp-doc-share-template`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 4 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_DOCUMENTS-T2-008 — `source` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=documents`
- **Tags:** @tier2 @hrm-documents @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=documents`.
  2. Save with `source` set to the first real option.
  3. Save with `source` set to the last option.
  4. Save with `source` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_DOCUMENTS-T2-009 — `search_input` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=documents`
- **Tags:** @tier2 @hrm-documents @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=documents`.
  2. Save with `search_input` set to a single character.
  3. Save with `search_input` set to a 255-character value.
  4. Save with `search_input` set to a value with leading and trailing whitespace.
  5. Save with `search_input` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_DOCUMENTS-T2-010 — `btn_create_folder` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=documents`
- **Tags:** @tier2 @hrm-documents @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=documents`.
  2. Save with `btn_create_folder` set to a single character.
  3. Save with `btn_create_folder` set to a 255-character value.
  4. Save with `btn_create_folder` set to a value with leading and trailing whitespace.
  5. Save with `btn_create_folder` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_DOCUMENTS-T2-011 — `btn_moveto_folder` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=documents`
- **Tags:** @tier2 @hrm-documents @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=documents`.
  2. Save with `btn_moveto_folder` set to a single character.
  3. Save with `btn_moveto_folder` set to a 255-character value.
  4. Save with `btn_moveto_folder` set to a value with leading and trailing whitespace.
  5. Save with `btn_moveto_folder` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_DOCUMENTS-T2-012 — `btn_delete_folder` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=documents`
- **Tags:** @tier2 @hrm-documents @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=documents`.
  2. Save with `btn_delete_folder` set to a single character.
  3. Save with `btn_delete_folder` set to a 255-character value.
  4. Save with `btn_delete_folder` set to a value with leading and trailing whitespace.
  5. Save with `btn_delete_folder` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_DOCUMENTS-T2-013 — `btn_share` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=documents`
- **Tags:** @tier2 @hrm-documents @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=documents`.
  2. Save with `btn_share` set to a single character.
  3. Save with `btn_share` set to a 255-character value.
  4. Save with `btn_share` set to a value with leading and trailing whitespace.
  5. Save with `btn_share` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_DOCUMENTS-T2-014 — `checkall` ("Check All") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=documents`
- **Tags:** @tier2 @hrm-documents @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=documents`.
  2. Save with `checkall` ("Check All") set to checked.
  3. Save with `checkall` ("Check All") set to unchecked.
  4. Save with `checkall` ("Check All") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_DOCUMENTS-T2-015 — `admin.php?page=erp-hr&section=documents` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=documents`
- **Tags:** @tier2 @hrm-documents @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=documents`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 7 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.
