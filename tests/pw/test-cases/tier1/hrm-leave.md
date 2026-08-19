# Tier 1 — hrm-leave

Happy path — the screen loads, every field and label renders as captured, and the primary create/read/update/delete flow succeeds.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**31 cases** (28 derived from the harness, 3 hand-written business flows).

## Business flows

#### HRM_LEAVE-F1-001 — Policy → entitlement → request → approval consumes balance

- **Surface:** `admin.php?page=erp-hr&section=leave`
- **Tags:** @tier1 @hrm-leave @flow
- **Preconditions:** A financial year exists (Settings → HR → Financial year) and an employee is active.
- **Steps:**
  1. Create a leave policy with a known day count.
  2. Assign an entitlement from that policy to the employee.
  3. Raise a leave request for N days against it.
  4. Approve the request.
- **Expected:** The employee's available balance drops by exactly N days and the request shows as approved.
- **Oracle:** `wp_erp_hr_leave_requests` status + the entitlement balance in `wp_erp_hr_leave_entitlements`.

#### HRM_LEAVE-F1-002 — Rejecting a request leaves the balance untouched

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier1 @hrm-leave @flow
- **Steps:**
  1. Raise a request, note the balance, then reject it via `tmpl-erp-hr-leave-reject-js-tmp`.
- **Expected:** The request reads rejected and the balance is identical to before the request.
- **Oracle:** Balance before/after + request status.

#### HRM_LEAVE-F1-003 — A holiday is excluded from the leave-day count

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`
- **Tags:** @tier1 @hrm-leave @flow
- **Steps:**
  1. Create a holiday on a date inside a working week (`tmpl-erp-hr-holiday-js-tmp`).
  2. Raise a leave request spanning that date.
- **Expected:** The counted leave days exclude the holiday.
- **Oracle:** The day count the request stores vs the calendar span.

## Screen & field coverage

#### HRM_LEAVE-T1-001 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=leave`
- **Tags:** @tier1 @hrm-leave @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=leave`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "Close dialog".
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_LEAVE-T1-002 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier1 @hrm-leave @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "Close dialog".
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_LEAVE-T1-003 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-entitlements`
- **Tags:** @tier1 @hrm-leave @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-entitlements`.
  3. Confirm the action buttons render: "Apply", "Screen Options".
  4. Confirm the list columns render: Employee Name Sort descending., Leave Policy, Validity, Available, Spent.
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_LEAVE-T1-004 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`
- **Tags:** @tier1 @hrm-leave @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=leave&sub-section=holidays`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "Filter".
  4. Confirm the list columns render: Select All, Title, Start Date Sort descending., End Date, Duration, Description.
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_LEAVE-T1-005 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=policies`
- **Tags:** @tier1 @hrm-leave @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=leave&sub-section=policies`.
  3. Confirm the action buttons render: "Apply", "Screen Options".
  4. Confirm the list columns render: Policy Name Sort descending., Year, Description, Days, Calendar Color, Type, Department, Designation, Location, Gender.
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_LEAVE-T1-006 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar`
- **Tags:** @tier1 @hrm-leave @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "Filter".
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_LEAVE-T1-007 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier1 @hrm-leave @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "More", "Close dialog".
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_LEAVE-T1-008 — Save modal form `tmpl-erp-hr-leave-approve-js-tmp` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier1 @hrm-leave @crud
- **Steps:**
  1. Open modal form `tmpl-erp-hr-leave-approve-js-tmp`.
  2. Fill all 1 fields with valid data.
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_LEAVE-T1-009 — modal form `tmpl-erp-hr-leave-approve-js-tmp` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier1 @hrm-leave @labels
- **Steps:**
  1. Open modal form `tmpl-erp-hr-leave-approve-js-tmp`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `reason` ("Reason") — type `textarea`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_LEAVE-T1-010 — Save modal form `tmpl-erp-hr-leave-reject-js-tmp` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier1 @hrm-leave @crud
- **Steps:**
  1. Open modal form `tmpl-erp-hr-leave-reject-js-tmp`.
  2. Fill all 1 fields with valid data (required: `reason` ("Reason *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_LEAVE-T1-011 — modal form `tmpl-erp-hr-leave-reject-js-tmp` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier1 @hrm-leave @labels
- **Steps:**
  1. Open modal form `tmpl-erp-hr-leave-reject-js-tmp`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `reason` ("Reason *") — type `textarea`, **required**
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_LEAVE-T1-012 — Save modal form `tmpl-erp-hr-holiday-js-tmp` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`
- **Tags:** @tier1 @hrm-leave @crud
- **Steps:**
  1. Open modal form `tmpl-erp-hr-holiday-js-tmp`.
  2. Fill all 5 fields with valid data (required: `title` ("Holiday Name *"), `start_date` ("Start Date *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_LEAVE-T1-013 — modal form `tmpl-erp-hr-holiday-js-tmp` renders all 5 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`
- **Tags:** @tier1 @hrm-leave @labels
- **Steps:**
  1. Open modal form `tmpl-erp-hr-holiday-js-tmp`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 5 fields render:
      - `title` ("Holiday Name *") — type `text`, **required**
      - `start_date` ("Start Date *") — type `text`, **required**
      - `range` ("Range") — type `checkbox`
      - `end_date` ("End Date") — type `text`
      - `description` ("Description") — type `textarea`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_LEAVE-T1-014 — Save `admin.php?page=erp-hr&section=leave` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=leave`
- **Tags:** @tier1 @hrm-leave @crud
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave`.
  2. Fill all 10 fields with valid data.
  3. Submit with "Apply".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_LEAVE-T1-015 — `admin.php?page=erp-hr&section=leave` renders all 10 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=leave`
- **Tags:** @tier1 @hrm-leave @labels
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 10 fields render:
      - `employee_name` ("Employee name") — type `text`, placeholder "Search by employee name"
      - `financial_year` ("Financial year") — type `select`
      - `leave_policy` ("Leave Policy") — type `select`
      - `filter_leave_status[]` ("Approved") — type `checkbox`
      - `filter_leave_status[]` ("Pending") — type `checkbox`
      - `filter_leave_status[]` ("Rejected") — type `checkbox`
      - `filter_leave_year` ("Date range") — type `select`
      - `hide_filter` — type `button`
      - `leave_filter_reset` — type `button`
      - `filter_employee_search` — type `submit`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_LEAVE-T1-016 — `filter_leave_year` ("Date range") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=leave`
- **Tags:** @tier1 @hrm-leave @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave`.
  2. Read every option of `filter_leave_year` ("Date range").
- **Expected:** The options are exactly: "Filter by date" (``), "Last week" (`1`), "Last month" (`2`), "Last 3 months" (`3`), "Custom" (`custom`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_LEAVE-T1-017 — Save `admin.php?page=erp-hr&section=leave&sub-section=leave-requests` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier1 @hrm-leave @crud
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Fill all 10 fields with valid data.
  3. Submit with "Apply".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_LEAVE-T1-018 — `admin.php?page=erp-hr&section=leave&sub-section=leave-requests` renders all 10 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier1 @hrm-leave @labels
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 10 fields render:
      - `employee_name` ("Employee name") — type `text`, placeholder "Search by employee name"
      - `financial_year` ("Financial year") — type `select`
      - `leave_policy` ("Leave Policy") — type `select`
      - `filter_leave_status[]` ("Approved") — type `checkbox`
      - `filter_leave_status[]` ("Pending") — type `checkbox`
      - `filter_leave_status[]` ("Rejected") — type `checkbox`
      - `filter_leave_year` ("Date range") — type `select`
      - `hide_filter` — type `button`
      - `leave_filter_reset` — type `button`
      - `filter_employee_search` — type `submit`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_LEAVE-T1-019 — `filter_leave_year` ("Date range") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier1 @hrm-leave @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Read every option of `filter_leave_year` ("Date range").
- **Expected:** The options are exactly: "Filter by date" (``), "Last week" (`1`), "Last month" (`2`), "Last 3 months" (`3`), "Custom" (`custom`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_LEAVE-T1-020 — Save `admin.php?page=erp-hr&section=leave&sub-section=holidays` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`
- **Tags:** @tier1 @hrm-leave @crud
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=holidays`.
  2. Fill all 4 fields with valid data.
  3. Submit with "Apply".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_LEAVE-T1-021 — `admin.php?page=erp-hr&section=leave&sub-section=holidays` renders all 4 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`
- **Tags:** @tier1 @hrm-leave @labels
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=holidays`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 4 fields render:
      - `from` — type `text`, placeholder "From date"
      - `to` — type `text`, placeholder "To date"
      - `filter` — type `submit`
      - `erp-ical-input` — type `file`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_LEAVE-T1-022 — Save `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar`
- **Tags:** @tier1 @hrm-leave @crud
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar`.
  2. Fill all 3 fields with valid data.
  3. Submit with "Apply".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_LEAVE-T1-023 — `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar` renders all 3 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar`
- **Tags:** @tier1 @hrm-leave @labels
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 3 fields render:
      - `department` — type `select`
      - `designation` — type `select`
      - `erp_leave_calendar_filter` — type `submit`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_LEAVE-T1-024 — `department` offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar`
- **Tags:** @tier1 @hrm-leave @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar`.
  2. Read every option of `department`.
- **Expected:** The options are exactly: "- Select Department -" (`-1`), "General Management" (`1`), "Operations Department" (`2`), "Finance Department" (`3`), "Sales Department" (`4`), "Human Resource Department" (`5`), "Purchase Department" (`6`), "Engineering Department" (`7`), "Production Department" (`8`), "Procurement Department" (`9`), "pwerp_dept_2vjg9u" (`24`), "pwerp_dept_n40ldm" (`33`), "pwerp_dept_sfcrvj" (`41`), "pwerp_dept_t6il8m" (`59`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_LEAVE-T1-025 — `designation` offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar`
- **Tags:** @tier1 @hrm-leave @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar`.
  2. Read every option of `designation`.
- **Expected:** The options are exactly: "- Select Designation -" (`-1`), "Business Executive" (`18`), "Business Manager" (`10`), "CEO" (`3`), "Customer Support Executive" (`20`), "Engineer" (`16`), "Finance/Accounts Manager" (`12`), "Hiring Manager" (`14`), "Human Resource Manager" (`13`), "Junior Engineer" (`17`), "Managing Director" (`4`), "Marketing Executive" (`19`), "Marketing Manager" (`9`), "Operations Manager" (`8`), "President" (`1`), "Product Manager" (`5`), "Program Manager" (`7`), "Project Manager" (`6`), "pwerp_desig_3caprx" (`42`), "pwerp_desig_so8jlt" (`29`), "pwerp_desig_y7cni4" (`38`), "pwerp_desig_ygsll6" (`54`), "Senior Engineer" (`15`), "Technology Manager" (`11`), "Vice President" (`2`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_LEAVE-T1-026 — Save `admin.php?page=erp-hr&section=leave&sub-section=leave-requests` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier1 @hrm-leave @crud
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Fill all 10 fields with valid data.
  3. Submit with "Apply".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_LEAVE-T1-027 — `admin.php?page=erp-hr&section=leave&sub-section=leave-requests` renders all 10 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier1 @hrm-leave @labels
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 10 fields render:
      - `employee_name` ("Employee name") — type `text`, placeholder "Search by employee name"
      - `financial_year` ("Financial year") — type `select`
      - `leave_policy` ("Leave Policy") — type `select`
      - `filter_leave_status[]` ("Approved") — type `checkbox`
      - `filter_leave_status[]` ("Pending") — type `checkbox`
      - `filter_leave_status[]` ("Rejected") — type `checkbox`
      - `filter_leave_year` ("Date range") — type `select`
      - `hide_filter` — type `button`
      - `leave_filter_reset` — type `button`
      - `filter_employee_search` — type `submit`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_LEAVE-T1-028 — `filter_leave_year` ("Date range") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Tags:** @tier1 @hrm-leave @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`.
  2. Read every option of `filter_leave_year` ("Date range").
- **Expected:** The options are exactly: "Filter by date" (``), "Last week" (`1`), "Last month" (`2`), "Last 3 months" (`3`), "Custom" (`custom`).
- **Oracle:** UI — `<option>` label/value pairs.
