# Tier 1 — hrm-documents

Happy path — the screen loads, every field and label renders as captured, and the primary create/read/update/delete flow succeeds.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**12 cases** (12 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### HRM_DOCUMENTS-T1-001 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=documents`
- **Tags:** @tier1 @hrm-documents @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=documents`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "Upload".
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_DOCUMENTS-T1-002 — Save modal form `tmpl-erp-doc-tree-template` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-documents @crud
- **Steps:**
  1. Open modal form `tmpl-erp-doc-tree-template`.
  2. Fill all 1 fields with valid data.
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_DOCUMENTS-T1-003 — modal form `tmpl-erp-doc-tree-template` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-documents @labels
- **Steps:**
  1. Open modal form `tmpl-erp-doc-tree-template`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `0` ("Home") — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_DOCUMENTS-T1-004 — Save modal form `tmpl-erp-doc-share-template` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-documents @crud
- **Steps:**
  1. Open modal form `tmpl-erp-doc-share-template`.
  2. Fill all 4 fields with valid data.
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_DOCUMENTS-T1-005 — modal form `tmpl-erp-doc-share-template` renders all 4 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-documents @labels
- **Steps:**
  1. Open modal form `tmpl-erp-doc-share-template`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 4 fields render:
      - `share_by` ("Share files by") — type `select`
      - `department` ("Department") — type `select`
      - `designation` ("Designation") — type `select`
      - `selected_emp[]` ("Selected employees") — type `select`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_DOCUMENTS-T1-006 — `share_by` ("Share files by") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-documents @options
- **Steps:**
  1. Open modal form `tmpl-erp-doc-share-template`.
  2. Read every option of `share_by` ("Share files by").
- **Expected:** The options are exactly: "All employees" (`all_employees`), "By department" (`by_department`), "By designation" (`by_designation`), "By employee" (`by_employee`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_DOCUMENTS-T1-007 — `department` ("Department") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-documents @options
- **Steps:**
  1. Open modal form `tmpl-erp-doc-share-template`.
  2. Read every option of `department` ("Department").
- **Expected:** The options are exactly: "All Department" (`-1`), "General Management" (`1`), "Operations Department" (`2`), "Finance Department" (`3`), "Sales Department" (`4`), "Human Resource Department" (`5`), "Purchase Department" (`6`), "Engineering Department" (`7`), "Production Department" (`8`), "Procurement Department" (`9`), "pwerp_dept_2vjg9u" (`24`), "pwerp_dept_n40ldm" (`33`), "pwerp_dept_sfcrvj" (`41`), "pwerp_dept_t6il8m" (`59`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_DOCUMENTS-T1-008 — `designation` ("Designation") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-documents @options
- **Steps:**
  1. Open modal form `tmpl-erp-doc-share-template`.
  2. Read every option of `designation` ("Designation").
- **Expected:** The options are exactly: "All Designations" (`-1`), "Business Executive" (`18`), "Business Manager" (`10`), "CEO" (`3`), "Customer Support Executive" (`20`), "Engineer" (`16`), "Finance/Accounts Manager" (`12`), "Hiring Manager" (`14`), "Human Resource Manager" (`13`), "Junior Engineer" (`17`), "Managing Director" (`4`), "Marketing Executive" (`19`), "Marketing Manager" (`9`), "Operations Manager" (`8`), "President" (`1`), "Product Manager" (`5`), "Program Manager" (`7`), "Project Manager" (`6`), "pwerp_desig_3caprx" (`42`), "pwerp_desig_so8jlt" (`29`), "pwerp_desig_y7cni4" (`38`), "pwerp_desig_ygsll6" (`54`), "Senior Engineer" (`15`), "Technology Manager" (`11`), "Vice President" (`2`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_DOCUMENTS-T1-009 — `selected_emp[]` ("Selected employees") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-documents @options
- **Steps:**
  1. Open modal form `tmpl-erp-doc-share-template`.
  2. Read every option of `selected_emp[]` ("Selected employees").
- **Expected:** The options are exactly: "pwerpFirst Q Lastushu" (`7`), "pwerpFirst Q Lasthasu" (`8`), "pwerpFirst Q Lastlsdk" (`9`), "pwerpFirst Q Lastytlo" (`10`), "pwerpFirst LastProbe" (`6`), "erp_employee" (`5`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_DOCUMENTS-T1-010 — Save `admin.php?page=erp-hr&section=documents` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=documents`
- **Tags:** @tier1 @hrm-documents @crud
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=documents`.
  2. Fill all 7 fields with valid data.
  3. Submit with "Apply".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_DOCUMENTS-T1-011 — `admin.php?page=erp-hr&section=documents` renders all 7 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=documents`
- **Tags:** @tier1 @hrm-documents @labels
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=documents`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 7 fields render:
      - `source` — type `select`
      - `search_input` — type `text`, placeholder "Search"
      - `btn_create_folder` — type `button`
      - `btn_moveto_folder` — type `button`
      - `btn_delete_folder` — type `button`
      - `btn_share` — type `button`
      - `checkall` ("Check All") — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_DOCUMENTS-T1-012 — `source` offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=documents`
- **Tags:** @tier1 @hrm-documents @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=documents`.
  2. Read every option of `source`.
- **Expected:** The options are exactly: "Owned by me" (`owned_by_me`), "My Dropbox" (`my_dropbox`), "Shared with me" (`shared_with_me`).
- **Oracle:** UI — `<option>` label/value pairs.
