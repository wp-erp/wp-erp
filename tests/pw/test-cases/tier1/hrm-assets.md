# Tier 1 — hrm-assets

Happy path — the screen loads, every field and label renders as captured, and the primary create/read/update/delete flow succeeds.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**27 cases** (25 derived from the harness, 2 hand-written business flows).

## Business flows

#### HRM_ASSETS-F1-001 — Asset → allotment → return updates availability

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier1 @hrm-assets @pro @flow
- **Steps:**
  1. Create an asset category, then an asset with a known total quantity (`tmpl-erp-asset-new`).
  2. Allot one unit to an employee (`tmpl-erp-allotment-new`).
  3. Confirm Available/Total drops by one.
  4. Return the unit (`tmpl-erp-asset-return`).
- **Expected:** Available count decrements on allotment and increments back on return; it never exceeds the total or goes negative.
- **Oracle:** The Available/Total column vs `wp_erp_hr_assets` / `wp_erp_hr_assets_history`.

#### HRM_ASSETS-F1-002 — An asset request can be approved and rejected

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset-request`
- **Tags:** @tier1 @hrm-assets @pro @flow
- **Steps:**
  1. Raise a request as an employee (`tmpl-erp-hr-emp-request-asset`).
  2. Reply/approve it (`tmpl-erp-asset-request-reply`).
  3. Raise a second request and reject it (`tmpl-erp-asset-request-reject`).
- **Expected:** An approved request creates an allotment; a rejected one does not and records the rejection reason.
- **Oracle:** `wp_erp_hr_assets_request` status + allotment rows.

## Screen & field coverage

#### HRM_ASSETS-T1-001 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier1 @hrm-assets @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=asset&sub-section=asset`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "Filter".
  4. Confirm the list columns render: Select All, Item Name, Type, Category Sort descending., Reg Date Sort descending., Expiry Date Sort descending., Warranty Till Sort descending., Available/Total.
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_ASSETS-T1-002 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset-allottment`
- **Tags:** @tier1 @hrm-assets @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=asset&sub-section=asset-allottment`.
  3. Confirm the action buttons render: "Apply", "Screen Options".
  4. Confirm the list columns render: Select All, Item Name, Model No, Asset Code, Given To, Given Date Sort descending., Return Date, Status.
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_ASSETS-T1-003 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset-request`
- **Tags:** @tier1 @hrm-assets @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=asset&sub-section=asset-request`.
  3. Confirm the action buttons render: "Apply", "Screen Options".
  4. Confirm the list columns render: Select All, Employee Name, Requested Category, Requested Item, Request Date Sort descending., Given Item, Given Date Sort descending., Status Sort descending..
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_ASSETS-T1-004 — Save modal form `tmpl-erp-hr-emp-add-asset` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-assets @crud
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-add-asset`.
  2. Fill all 6 fields with valid data (required: `category_id` ("Category *"), `item_group` ("Item Group *"), `item` ("Item Name *"), `given_date` ("Given Date *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_ASSETS-T1-005 — modal form `tmpl-erp-hr-emp-add-asset` renders all 6 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-assets @labels
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-add-asset`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 6 fields render:
      - `category_id` ("Category *") — type `select`, **required**
      - `item_group` ("Item Group *") — type `select`, **required**
      - `item` ("Item Name *") — type `select`, **required**
      - `given_date` ("Given Date *") — type `text`, **required**
      - `is_returnable` ("Returnable?") — type `checkbox`
      - `return_date` ("Return Date") — type `text`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_ASSETS-T1-006 — Save modal form `tmpl-erp-hr-emp-request-asset` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-assets @crud
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-request-asset`.
  2. Fill all 4 fields with valid data (required: `category_id` ("Category *"), `item_group` ("Item Name *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_ASSETS-T1-007 — modal form `tmpl-erp-hr-emp-request-asset` renders all 4 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-assets @labels
- **Steps:**
  1. Open modal form `tmpl-erp-hr-emp-request-asset`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 4 fields render:
      - `category_id` ("Category *") — type `select`, **required**
      - `item_group` ("Item Name *") — type `select`, **required**
      - `not_in_list` ("If Unavailable") — type `checkbox`
      - `request_desc` ("Request Details") — type `textarea`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_ASSETS-T1-008 — Save modal form `tmpl-erp-allotment-new` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-assets @crud
- **Steps:**
  1. Open modal form `tmpl-erp-allotment-new`.
  2. Fill all 7 fields with valid data (required: `category_id` ("Category *"), `item_group` ("Item Name *"), `item` ("Item *"), `allotted_to` ("Allot To *"), `given_date` ("Given Date *"), `return_date` ("Return Date *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_ASSETS-T1-009 — modal form `tmpl-erp-allotment-new` renders all 7 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-assets @labels
- **Steps:**
  1. Open modal form `tmpl-erp-allotment-new`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 7 fields render:
      - `category_id` ("Category *") — type `select`, **required**
      - `item_group` ("Item Name *") — type `select`, **required**
      - `item` ("Item *") — type `select`, **required**
      - `allotted_to` ("Allot To *") — type `select`, **required**
      - `given_date` ("Given Date *") — type `text`, **required**
      - `is_returnable` ("Returnable?") — type `checkbox`
      - `return_date` ("Return Date *") — type `text`, **required**
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_ASSETS-T1-010 — `allotted_to` ("Allot To *") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-assets @options
- **Steps:**
  1. Open modal form `tmpl-erp-allotment-new`.
  2. Read every option of `allotted_to` ("Allot To *").
- **Expected:** The options are exactly: "- Select Employee -" (`0`), "pwerpFirst Q Lastushu" (`7`), "pwerpFirst Q Lasthasu" (`8`), "pwerpFirst Q Lastlsdk" (`9`), "pwerpFirst Q Lastytlo" (`10`), "pwerpFirst LastProbe" (`6`), "erp_employee" (`5`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_ASSETS-T1-011 — Save modal form `tmpl-erp-asset-return` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-assets @crud
- **Steps:**
  1. Open modal form `tmpl-erp-asset-return`.
  2. Fill all 3 fields with valid data (required: `return_date` ("Return Date *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_ASSETS-T1-012 — modal form `tmpl-erp-asset-return` renders all 3 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-assets @labels
- **Steps:**
  1. Open modal form `tmpl-erp-asset-return`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 3 fields render:
      - `return_date` ("Return Date *") — type `text`, **required**
      - `return_note` ("Return Note") — type `textarea`
      - `is_dissmissed` ("Lost/Damaged") — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_ASSETS-T1-013 — Save modal form `tmpl-erp-asset-request-reply` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-assets @crud
- **Steps:**
  1. Open modal form `tmpl-erp-asset-request-reply`.
  2. Fill all 7 fields with valid data (required: `category_id` ("Category *"), `item_group` ("Item Name *"), `item` ("Item *"), `given_date` ("Given Date *"), `return_date` ("Return Date *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_ASSETS-T1-014 — modal form `tmpl-erp-asset-request-reply` renders all 7 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-assets @labels
- **Steps:**
  1. Open modal form `tmpl-erp-asset-request-reply`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 7 fields render:
      - `category_id` ("Category *") — type `select`, **required**
      - `item_group` ("Item Name *") — type `select`, **required**
      - `item` ("Item *") — type `select`, **required**
      - `given_date` ("Given Date *") — type `text`, **required**
      - `is_returnable` ("Returnable?") — type `checkbox`
      - `return_date` ("Return Date *") — type `text`, **required**
      - `reply_msg` ("Instructions") — type `textarea`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_ASSETS-T1-015 — Save modal form `tmpl-erp-asset-request-reject` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-assets @crud
- **Steps:**
  1. Open modal form `tmpl-erp-asset-request-reject`.
  2. Fill all 1 fields with valid data.
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_ASSETS-T1-016 — modal form `tmpl-erp-asset-request-reject` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-assets @labels
- **Steps:**
  1. Open modal form `tmpl-erp-asset-request-reject`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `reject_reason` ("Reject Reason") — type `textarea`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_ASSETS-T1-017 — Save modal form `tmpl-erp-asset-new` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier1 @hrm-assets @crud
- **Steps:**
  1. Open modal form `tmpl-erp-asset-new`.
  2. Fill all 12 fields with valid data (required: `category_id` ("Category *"), `item_group` ("Item Name *"), `asset_type` ("Asset Type *"), `items[1][item_code]` ("Item Code *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_ASSETS-T1-018 — modal form `tmpl-erp-asset-new` renders all 12 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier1 @hrm-assets @labels
- **Steps:**
  1. Open modal form `tmpl-erp-asset-new`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 12 fields render:
      - `category_id` ("Category *") — type `select`, **required**
      - `item_group` ("Item Name *") — type `text`, **required**
      - `asset_type` ("Asset Type *") — type `select`, **required**
      - `items[1][item_code]` ("Item Code *") — type `text`, **required**
      - `items[1][model_no]` ("Model No") — type `text`
      - `items[1][item_desc]` ("Description") — type `textarea`
      - `items[1][item_serial]` ("Serial/License Info") — type `textarea`
      - `items[1][manufacturer]` ("Manufacturer") — type `text`
      - `items[1][price]` ("Price") — type `number`
      - `items[1][date_warr]` ("Warranty Till") — type `text`
      - `items[1][date_exp]` ("Expiry Date") — type `text`
      - `items[1][allottable]` ("Allottable?") — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_ASSETS-T1-019 — `asset_type` ("Asset Type *") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier1 @hrm-assets @options
- **Steps:**
  1. Open modal form `tmpl-erp-asset-new`.
  2. Read every option of `asset_type` ("Asset Type *").
- **Expected:** The options are exactly: "Single Item" (`single`), "Multiple Items" (`variable`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_ASSETS-T1-020 — Save modal form `tmpl-erp-asset-edit` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier1 @hrm-assets @crud
- **Steps:**
  1. Open modal form `tmpl-erp-asset-edit`.
  2. Fill all 11 fields with valid data (required: `category_id` ("Category *"), `item_group` ("Item Group *"), `items[{{i}}][item_code]` ("Item Code *")).
  3. Submit with "Delete".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_ASSETS-T1-021 — modal form `tmpl-erp-asset-edit` renders all 11 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier1 @hrm-assets @labels
- **Steps:**
  1. Open modal form `tmpl-erp-asset-edit`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 11 fields render:
      - `category_id` ("Category *") — type `select`, **required**
      - `item_group` ("Item Group *") — type `text`, **required**
      - `items[{{i}}][item_code]` ("Item Code *") — type `text`, **required**
      - `items[{{i}}][model_no]` ("Model No") — type `text`
      - `items[{{i}}][manufacturer]` ("Manufacturer") — type `text`
      - `items[{{i}}][price]` ("Price") — type `text`
      - `items[{{i}}][date_exp]` ("Expiry Date") — type `text`
      - `items[{{i}}][date_warr]` ("Warranty Till") — type `text`
      - `items[{{i}}][allottable]` ("Allottable?") — type `checkbox`
      - `items[{{i}}][item_serial]` ("Serial/License Info") — type `textarea`
      - `items[{{i}}][item_desc]` ("Description") — type `textarea`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_ASSETS-T1-022 — Save modal form `tmpl-asset-category-new` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier1 @hrm-assets @crud
- **Steps:**
  1. Open modal form `tmpl-asset-category-new`.
  2. Fill all 1 fields with valid data (required: `cat_name` ("Category Name *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_ASSETS-T1-023 — modal form `tmpl-asset-category-new` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier1 @hrm-assets @labels
- **Steps:**
  1. Open modal form `tmpl-asset-category-new`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `cat_name` ("Category Name *") — type `text`, **required**
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_ASSETS-T1-024 — Save `admin.php?page=erp-hr&section=asset&sub-section=asset` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier1 @hrm-assets @crud
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=asset&sub-section=asset`.
  2. Fill all 2 fields with valid data.
  3. Submit with "Apply".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_ASSETS-T1-025 — `admin.php?page=erp-hr&section=asset&sub-section=asset` renders all 2 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Tags:** @tier1 @hrm-assets @labels
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=asset&sub-section=asset`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 2 fields render:
      - `category_id` — type `select`
      - `filter_category` — type `submit`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.
