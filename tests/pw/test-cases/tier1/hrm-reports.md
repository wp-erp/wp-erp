# Tier 1 — hrm-reports

Happy path — the screen loads, every field and label renders as captured, and the primary create/read/update/delete flow succeeds.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**24 cases** (24 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### HRM_REPORTS-T1-001 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=report`
- **Tags:** @tier1 @hrm-reports @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=report`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "View Report".
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_REPORTS-T1-002 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=age-profile`
- **Tags:** @tier1 @hrm-reports @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=age-profile`.
  3. Confirm the action buttons render: "Apply", "Screen Options".
  4. Confirm the list columns render: Department, Under 18 year, 18 to 25 year, 26 to 35 year, 36 to 45 year, 46 to 55 year, 56 to 65 year, 65+ year.
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_REPORTS-T1-003 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=salary-history`
- **Tags:** @tier1 @hrm-reports @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=salary-history`.
  3. Confirm the action buttons render: "Apply", "Screen Options".
  4. Confirm the list columns render: Employee, Date, Pay Rate, Pay type, Employee ID.
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_REPORTS-T1-004 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=headcount`
- **Tags:** @tier1 @hrm-reports @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=headcount`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "Filter".
  4. Confirm the list columns render: Name, Hire Date, Job Title, Department, Location, Status.
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_REPORTS-T1-005 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`
- **Tags:** @tier1 @hrm-reports @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "Filter", "Show more details".
  4. Confirm the list columns render: Name.
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_REPORTS-T1-006 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=asset-report`
- **Tags:** @tier1 @hrm-reports @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=asset-report`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "Filter".
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_REPORTS-T1-007 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report`
- **Tags:** @tier1 @hrm-reports @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "Filter".
  4. Confirm the list columns render: Date, Total, Present, Leave, Absent, Comment.
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_REPORTS-T1-008 — Save `admin.php?page=erp-hr&section=report&sub-section=report&type=headcount` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=headcount`
- **Tags:** @tier1 @hrm-reports @crud
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=headcount`.
  2. Fill all 2 fields with valid data.
  3. Submit with "Apply".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_REPORTS-T1-009 — `admin.php?page=erp-hr&section=report&sub-section=report&type=headcount` renders all 2 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=headcount`
- **Tags:** @tier1 @hrm-reports @labels
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=headcount`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 2 fields render:
      - `year` — type `select`
      - `department` — type `select`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_REPORTS-T1-010 — `year` offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=headcount`
- **Tags:** @tier1 @hrm-reports @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=headcount`.
  2. Read every option of `year`.
- **Expected:** The options are exactly: "-Select Year-" (`-1`), "2026" (`2026`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_REPORTS-T1-011 — `department` offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=headcount`
- **Tags:** @tier1 @hrm-reports @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=headcount`.
  2. Read every option of `department`.
- **Expected:** The options are exactly: "- Select Department -" (`-1`), "General Management" (`1`), "Operations Department" (`2`), "Finance Department" (`3`), "Sales Department" (`4`), "Human Resource Department" (`5`), "Purchase Department" (`6`), "Engineering Department" (`7`), "Production Department" (`8`), "Procurement Department" (`9`), "pwerp_dept_2vjg9u" (`24`), "pwerp_dept_n40ldm" (`33`), "pwerp_dept_sfcrvj" (`41`), "pwerp_dept_t6il8m" (`59`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_REPORTS-T1-012 — Save `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`
- **Tags:** @tier1 @hrm-reports @crud
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`.
  2. Fill all 5 fields with valid data.
  3. Submit with "Apply".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_REPORTS-T1-013 — `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves` renders all 5 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`
- **Tags:** @tier1 @hrm-reports @labels
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 5 fields render:
      - `filter_year` ("Filter by Designation") — type `select`
      - `filter_designation` ("Filter by Designation") — type `select`
      - `filter_department` ("Filter by Designation") — type `select`
      - `filter_employment_type` ("Filter by Designation") — type `select`
      - `filter_leave_report` ("Filter by Designation") — type `submit`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_REPORTS-T1-014 — `filter_year` ("Filter by Designation") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`
- **Tags:** @tier1 @hrm-reports @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`.
  2. Read every option of `filter_year` ("Filter by Designation").
- **Expected:** The options are exactly: "Select year" (``), "Custom" (`custom`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_REPORTS-T1-015 — `filter_designation` ("Filter by Designation") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`
- **Tags:** @tier1 @hrm-reports @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`.
  2. Read every option of `filter_designation` ("Filter by Designation").
- **Expected:** The options are exactly: "- Select Designation -" (`-1`), "Business Executive" (`18`), "Business Manager" (`10`), "CEO" (`3`), "Customer Support Executive" (`20`), "Engineer" (`16`), "Finance/Accounts Manager" (`12`), "Hiring Manager" (`14`), "Human Resource Manager" (`13`), "Junior Engineer" (`17`), "Managing Director" (`4`), "Marketing Executive" (`19`), "Marketing Manager" (`9`), "Operations Manager" (`8`), "President" (`1`), "Product Manager" (`5`), "Program Manager" (`7`), "Project Manager" (`6`), "pwerp_desig_3caprx" (`42`), "pwerp_desig_so8jlt" (`29`), "pwerp_desig_y7cni4" (`38`), "pwerp_desig_ygsll6" (`54`), "Senior Engineer" (`15`), "Technology Manager" (`11`), "Vice President" (`2`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_REPORTS-T1-016 — `filter_department` ("Filter by Designation") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`
- **Tags:** @tier1 @hrm-reports @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`.
  2. Read every option of `filter_department` ("Filter by Designation").
- **Expected:** The options are exactly: "- Select Department -" (`-1`), "General Management" (`1`), "Operations Department" (`2`), "Finance Department" (`3`), "Sales Department" (`4`), "Human Resource Department" (`5`), "Purchase Department" (`6`), "Engineering Department" (`7`), "Production Department" (`8`), "Procurement Department" (`9`), "pwerp_dept_2vjg9u" (`24`), "pwerp_dept_n40ldm" (`33`), "pwerp_dept_sfcrvj" (`41`), "pwerp_dept_t6il8m" (`59`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_REPORTS-T1-017 — `filter_employment_type` ("Filter by Designation") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`
- **Tags:** @tier1 @hrm-reports @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`.
  2. Read every option of `filter_employment_type` ("Filter by Designation").
- **Expected:** The options are exactly: "- Select Employment Type -" (`-1`), "Full Time" (`permanent`), "Part Time" (`parttime`), "On Contract" (`contract`), "Temporary" (`temporary`), "Trainee" (`trainee`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_REPORTS-T1-018 — Save `admin.php?page=erp-hr&section=report&sub-section=report&type=asset-report` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=asset-report`
- **Tags:** @tier1 @hrm-reports @crud
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=asset-report`.
  2. Fill all 2 fields with valid data.
  3. Submit with "Apply".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_REPORTS-T1-019 — `admin.php?page=erp-hr&section=report&sub-section=report&type=asset-report` renders all 2 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=asset-report`
- **Tags:** @tier1 @hrm-reports @labels
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=asset-report`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 2 fields render:
      - `query_time` — type `select`
      - `category` — type `select`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_REPORTS-T1-020 — `query_time` offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=asset-report`
- **Tags:** @tier1 @hrm-reports @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=asset-report`.
  2. Read every option of `query_time`.
- **Expected:** The options are exactly: "— All Time —" (`-1`), "This Month" (`this_month`), "Last Month" (`last_month`), "This Quarter" (`this_quarter`), "Last Quarter" (`last_quarter`), "This Year" (`this_year`), "last Year" (`last_year`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_REPORTS-T1-021 — Save `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report`
- **Tags:** @tier1 @hrm-reports @crud
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report`.
  2. Fill all 3 fields with valid data.
  3. Submit with "Apply".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_REPORTS-T1-022 — `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report` renders all 3 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report`
- **Tags:** @tier1 @hrm-reports @labels
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 3 fields render:
      - `location` — type `select`
      - `department` — type `select`
      - `query_time` — type `select`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_REPORTS-T1-023 — `department` offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report`
- **Tags:** @tier1 @hrm-reports @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report`.
  2. Read every option of `department`.
- **Expected:** The options are exactly: "- Select Department -" (`-1`), "General Management" (`1`), "Operations Department" (`2`), "Finance Department" (`3`), "Sales Department" (`4`), "Human Resource Department" (`5`), "Purchase Department" (`6`), "Engineering Department" (`7`), "Production Department" (`8`), "Procurement Department" (`9`), "pwerp_dept_2vjg9u" (`24`), "pwerp_dept_n40ldm" (`33`), "pwerp_dept_sfcrvj" (`41`), "pwerp_dept_t6il8m" (`59`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_REPORTS-T1-024 — `query_time` offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report`
- **Tags:** @tier1 @hrm-reports @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report`.
  2. Read every option of `query_time`.
- **Expected:** The options are exactly: "This Month" (`this_month`), "Last Month" (`last_month`), "This Quarter" (`this_quarter`), "Last Quarter" (`last_quarter`), "This Year" (`this_year`), "Last Year" (`last_year`), "Custom" (`custom`).
- **Oracle:** UI — `<option>` label/value pairs.
