# Tier 1 — hrm-payroll

Happy path — the screen loads, every field and label renders as captured, and the primary create/read/update/delete flow succeeds.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**13 cases** (12 derived from the harness, 1 hand-written business flows).

## Business flows

#### HRM_PAYROLL-F1-001 — Pay calendar → pay run → payslip totals reconcile

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=calendar`
- **Tags:** @tier1 @hrm-payroll @pro @flow
- **Preconditions:** At least one employee has a pay rate and pay type set.
- **Steps:**
  1. Create a pay calendar and assign the employee to it.
  2. Configure at least one earning and one deduction pay item.
  3. Generate a pay run for the current period.
  4. Open the payslip.
- **Expected:** Net pay equals gross earnings minus total deductions, for every employee in the run.
- **Oracle:** Arithmetic across `wp_erp_hr_payroll_payrun_detail` vs the payslip as rendered.

## Screen & field coverage

#### HRM_PAYROLL-T1-001 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=dashboard`
- **Tags:** @tier1 @hrm-payroll @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=payroll&sub-section=dashboard`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "More".
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_PAYROLL-T1-002 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=calendar`
- **Tags:** @tier1 @hrm-payroll @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=payroll&sub-section=calendar`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "More", "Add New Pay Calendar".
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_PAYROLL-T1-003 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=payrun`
- **Tags:** @tier1 @hrm-payroll @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=payroll&sub-section=payrun`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "More", "Filter".
  4. Confirm the list columns render: Pay Period, Pay Run, Payment Date Sort descending., Employees, Net Pay + Tax, Status Sort descending., Action.
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_PAYROLL-T1-004 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`
- **Tags:** @tier1 @hrm-payroll @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "More", "Search", "Update".
  4. Confirm the list columns render: SL, Employee, Department, Designation, Total Payment.
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_PAYROLL-T1-005 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=reports`
- **Tags:** @tier1 @hrm-payroll @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=payroll&sub-section=reports`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "More", "View Report".
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_PAYROLL-T1-006 — Save `admin.php?page=erp-hr&section=payroll&sub-section=payrun` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=payrun`
- **Tags:** @tier1 @hrm-payroll @crud
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=payroll&sub-section=payrun`.
  2. Fill all 2 fields with valid data.
  3. Submit with "Apply".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PAYROLL-T1-007 — `admin.php?page=erp-hr&section=payroll&sub-section=payrun` renders all 2 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=payrun`
- **Tags:** @tier1 @hrm-payroll @labels
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=payroll&sub-section=payrun`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 2 fields render:
      - `filter_payrun_status` ("Filter by Status") — type `select`
      - `filter_status_button` ("Filter by Status") — type `submit`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PAYROLL-T1-008 — Save `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`
- **Tags:** @tier1 @hrm-payroll @crud
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`.
  2. Fill all 4 fields with valid data.
  3. Submit with "Update".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PAYROLL-T1-009 — `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit` renders all 4 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`
- **Tags:** @tier1 @hrm-payroll @labels
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 4 fields render:
      - `pay_items` ("SL") — type `select`
      - `emp_dept` ("SL") — type `select`
      - `emp_desig` ("SL") — type `select`
      - `emp_name` ("SL") — type `search`, placeholder "Search an employee"
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PAYROLL-T1-010 — `pay_items` ("SL") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`
- **Tags:** @tier1 @hrm-payroll @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`.
  2. Read every option of `pay_items` ("SL").
- **Expected:** The options are exactly: "Select a pay item" (`-1`), "Travel Allowance" (`1`), "Accomodation Allowance" (`2`), "City Compensatory Allowance" (`3`), "Pay Adjustment" (`4`), "OverTime" (`5`), "Variable Pay" (`6`), "Bonus" (`7`), "Holiday Pay" (`8`), "Service Charge" (`9`), "Provident Fund" (`10`), "Loan" (`11`), "Advance Pay" (`12`), "Advance" (`13`), "Miscelleneous Deduction" (`14`), "Give as you earn" (`15`), "Expenses" (`16`), "Redundancy" (`17`), "Millage" (`18`), "Income Tax" (`19`), "Fedaral Tax" (`20`), "State Tax" (`21`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PAYROLL-T1-011 — `emp_dept` ("SL") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`
- **Tags:** @tier1 @hrm-payroll @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`.
  2. Read every option of `emp_dept` ("SL").
- **Expected:** The options are exactly: "All Department" (`-1`), "General Management" (`1`), "Operations Department" (`2`), "Finance Department" (`3`), "Sales Department" (`4`), "Human Resource Department" (`5`), "Purchase Department" (`6`), "Engineering Department" (`7`), "Production Department" (`8`), "Procurement Department" (`9`), "pwerp_dept_2vjg9u" (`24`), "pwerp_dept_n40ldm" (`33`), "pwerp_dept_sfcrvj" (`41`), "pwerp_dept_t6il8m" (`59`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PAYROLL-T1-012 — `emp_desig` ("SL") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`
- **Tags:** @tier1 @hrm-payroll @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`.
  2. Read every option of `emp_desig` ("SL").
- **Expected:** The options are exactly: "All Designations" (`-1`), "Business Executive" (`18`), "Business Manager" (`10`), "CEO" (`3`), "Customer Support Executive" (`20`), "Engineer" (`16`), "Finance/Accounts Manager" (`12`), "Hiring Manager" (`14`), "Human Resource Manager" (`13`), "Junior Engineer" (`17`), "Managing Director" (`4`), "Marketing Executive" (`19`), "Marketing Manager" (`9`), "Operations Manager" (`8`), "President" (`1`), "Product Manager" (`5`), "Program Manager" (`7`), "Project Manager" (`6`), "pwerp_desig_3caprx" (`42`), "pwerp_desig_so8jlt" (`29`), "pwerp_desig_y7cni4" (`38`), "pwerp_desig_ygsll6" (`54`), "Senior Engineer" (`15`), "Technology Manager" (`11`), "Vice President" (`2`).
- **Oracle:** UI — `<option>` label/value pairs.
