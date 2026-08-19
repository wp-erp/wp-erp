# Tier 1 — hrm-people

Happy path — the screen loads, every field and label renders as captured, and the primary create/read/update/delete flow succeeds.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**108 cases** (105 derived from the harness, 3 hand-written business flows).

## Business flows

#### HRM_PEOPLE-F1-001 — Employee lifecycle: hire, edit, terminate, restore

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @flow
- **Preconditions:** At least one department and one designation exist.
- **Steps:**
  1. Create an employee via the "Add New Employee" modal (`tmpl-erp-new-employee`) with all 8 required fields.
  2. Open the new employee's profile and confirm the personal and job-info tabs echo what was saved.
  3. Change Job Title on the Job Info tab and save.
  4. Terminate the employee via `tmpl-erp-employment-terminate`.
  5. Confirm the employee moves out of the active list, then restore them.
- **Expected:** Each step succeeds; the profile reflects each change; a terminated employee leaves the active list and returns on restore.
- **Oracle:** `wp_erp_hr_employees.status` transitions, plus a row in `wp_erp_hr_employee_history` for the job-title change.

#### HRM_PEOPLE-F1-002 — A new employee gets a WordPress user in the `employee` role

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @flow
- **Steps:**
  1. Create an employee with a previously unused e-mail address.
- **Expected:** A WordPress user is created for that address and holds the `employee` role.
- **Oracle:** `wp_users` row + `wp_capabilities` usermeta contains `employee`.

#### HRM_PEOPLE-F1-003 — Department and designation CRUD, including reassignment

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=departments`
- **Tags:** @tier1 @hrm-people @flow
- **Steps:**
  1. Create a department via `tmpl-erp-new-dept` and a designation via `tmpl-erp-new-desig`.
  2. Assign an employee to both.
  3. Rename each and confirm the employee list reflects the new names.
  4. Delete the department and observe what happens to the assigned employee.
- **Expected:** Create/rename succeed. Deleting a department in use either blocks with a message or reassigns the employee — whichever it does, it must not leave the employee pointing at a missing department.
- **Oracle:** `wp_erp_hr_depts` / `wp_erp_hr_designations` rows + the employee's `department`/`designation` columns.

## Screen & field coverage

#### HRM_PEOPLE-T1-001 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=dashboard`
- **Tags:** @tier1 @hrm-people @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=dashboard`.
  3. Confirm the action buttons render: "Apply", "Screen Options".
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_PEOPLE-T1-002 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=people`
- **Tags:** @tier1 @hrm-people @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=people`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "Import", "Export", "Cancel", "Reset".
  4. Confirm the list columns render: Select All, Employee Name Sort ascending., Designation, Department, Employment Type, Hire Date Sort ascending., Status.
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_PEOPLE-T1-003 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=people&sub-section=employee`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "Import", "Export", "Cancel", "Reset".
  4. Confirm the list columns render: Select All, Employee Name Sort ascending., Designation, Department, Employment Type, Hire Date Sort ascending., Status.
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_PEOPLE-T1-004 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=departments`
- **Tags:** @tier1 @hrm-people @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=people&sub-section=departments`.
  3. Confirm the action buttons render: "Apply", "Screen Options".
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_PEOPLE-T1-005 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier1 @hrm-people @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "Next page›", "Last page»", "Show more details".
  4. Confirm the list columns render: Select All, Title Sort descending., No. of Employees.
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_PEOPLE-T1-006 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=location`
- **Tags:** @tier1 @hrm-people @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=people&sub-section=location`.
  3. Confirm the action buttons render: "Apply", "Screen Options".
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_PEOPLE-T1-007 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee&action=view`
- **Tags:** @tier1 @hrm-people @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=people&sub-section=employee&action=view`.
  3. Confirm the action buttons render: "Apply", "Screen Options".
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_PEOPLE-T1-008 — Save modal form `tmpl-erp-address` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open modal form `tmpl-erp-address`.
  2. Fill all 7 fields with valid data (required: `location_name` ("Location Name *"), `address_1` ("Address Line 1 *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-009 — modal form `tmpl-erp-address` renders all 7 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open modal form `tmpl-erp-address`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 7 fields render:
      - `location_name` ("Location Name *") — type `text`, **required**
      - `address_1` ("Address Line 1 *") — type `text`, **required**
      - `address_2` ("Address Line 2") — type `text`
      - `city` ("City") — type `text`
      - `country` ("Country *") — type `select`
      - `state` ("Province / State") — type `select`
      - `zip` ("Postal / Zip Code") — type `text`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-010 — `country` ("Country *") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-address`.
  2. Read every option of `country` ("Country *").
- **Expected:** The options are exactly: "- Select -" (`-1`), "Åland Islands" (`AX`), "Afghanistan" (`AF`), "Albania" (`AL`), "Algeria" (`DZ`), "Andorra" (`AD`), "Angola" (`AO`), "Anguilla" (`AI`), "Antarctica" (`AQ`), "Antigua and Barbuda" (`AG`), "Argentina" (`AR`), "Armenia" (`AM`), "Aruba" (`AW`), "Australia" (`AU`), "Austria" (`AT`), "Azerbaijan" (`AZ`), "Bahamas" (`BS`), "Bahrain" (`BH`), "Bangladesh" (`BD`), "Barbados" (`BB`), "Belarus" (`BY`), "Belgium" (`BE`), "Belize" (`BZ`), "Benin" (`BJ`), "Bermuda" (`BM`) … and 220 more (see harness).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-011 — Save modal form `tmpl-erp-new-employee` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Fill all 42 fields with valid data (required: `personal[first_name]` ("First Name *"), `personal[last_name]` ("Last Name *"), `user_email` ("Email *"), `work[type]` ("Employee Type *"), `work[status]` ("Employee Status *"), `work[hiring_date]` ("Date of Hire *"), `work[department]` ("Department *"), `work[designation]` ("Job Title *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-012 — modal form `tmpl-erp-new-employee` renders all 42 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 42 fields render:
      - `personal[first_name]` ("First Name *") — type `text`, **required**
      - `personal[middle_name]` ("Middle Name") — type `text`
      - `personal[last_name]` ("Last Name *") — type `text`, **required**
      - `personal[employee_id]` ("Employee ID") — type `text`
      - `user_email` ("Email *") — type `email`, **required**
      - `work[type]` ("Employee Type *") — type `select`, **required**
      - `work[status]` ("Employee Status *") — type `select`, **required**
      - `work[end_date]` ("Employee End Date") — type `text`
      - `work[hiring_date]` ("Date of Hire *") — type `text`, **required**
      - `work[department]` ("Department *") — type `select`, **required**
      - `work[designation]` ("Job Title *") — type `select`, **required**
      - `advanced_fields` ("Show Advanced Fields") — type `checkbox`
      - `work[location]` ("Location") — type `select`
      - `work[reporting_to]` ("Reporting To") — type `select`
      - `work[hiring_source]` ("Source of Hire") — type `select`
      - `work[pay_rate]` ("Pay Rate") — type `text`
      - `work[pay_type]` ("Pay Type") — type `select`
      - `personal[work_phone]` ("Work Phone") — type `text`
      - `work[shift]` ("Shift") — type `select`
      - `personal[blood_group]` ("Blood Group") — type `select`
      - `personal[spouse_name]` ("Spouse's name") — type `text`
      - `personal[father_name]` ("Father's name") — type `text`
      - `personal[mother_name]` ("Mother's name") — type `text`
      - `personal[mobile]` ("Mobile") — type `text`
      - `personal[phone]` ("Phone") — type `text`
      - `personal[other_email]` ("Other Email") — type `email`
      - `work[date_of_birth]` ("Date of Birth") — type `text`
      - `personal[nationality]` ("Nationality") — type `select`
      - `personal[gender]` ("Gender") — type `select`
      - `personal[marital_status]` ("Marital Status") — type `select`
      - `personal[driving_license]` ("Driving License") — type `text`
      - `personal[hobbies]` ("Hobbies") — type `text`
      - `personal[user_url]` ("Website") — type `url`
      - `personal[street_1]` ("Address 1") — type `text`
      - `personal[street_2]` ("Address 2") — type `text`
      - `personal[city]` ("City") — type `text`
      - `personal[country]` ("Country") — type `select`
      - `personal[state]` ("Province / State") — type `select`
      - `personal[postal_code]` ("Post Code/Zip Code") — type `text`
      - `personal[description]` ("Biography") — type `textarea`
      - `user_notification` ("Notification") — type `checkbox`
      - `login_info` — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-013 — `work[type]` ("Employee Type *") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Read every option of `work[type]` ("Employee Type *").
- **Expected:** The options are exactly: "- Select -" (``), "Full Time" (`permanent`), "Part Time" (`parttime`), "On Contract" (`contract`), "Temporary" (`temporary`), "Trainee" (`trainee`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-014 — `work[status]` ("Employee Status *") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Read every option of `work[status]` ("Employee Status *").
- **Expected:** The options are exactly: "- Select -" (``), "Active" (`active`), "Inactive" (`inactive`), "Terminated" (`terminated`), "Deceased" (`deceased`), "Resigned" (`resigned`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-015 — `work[department]` ("Department *") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Read every option of `work[department]` ("Department *").
- **Expected:** The options are exactly: "- Select Department -" (`-1`), "General Management" (`1`), "Operations Department" (`2`), "Finance Department" (`3`), "Sales Department" (`4`), "Human Resource Department" (`5`), "Purchase Department" (`6`), "Engineering Department" (`7`), "Production Department" (`8`), "Procurement Department" (`9`), "pwerp_dept_2vjg9u" (`24`), "pwerp_dept_n40ldm" (`33`), "pwerp_dept_sfcrvj" (`41`), "pwerp_dept_t6il8m" (`59`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-016 — `work[designation]` ("Job Title *") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Read every option of `work[designation]` ("Job Title *").
- **Expected:** The options are exactly: "- Select Designation -" (`-1`), "Business Executive" (`18`), "Business Manager" (`10`), "CEO" (`3`), "Customer Support Executive" (`20`), "Engineer" (`16`), "Finance/Accounts Manager" (`12`), "Hiring Manager" (`14`), "Human Resource Manager" (`13`), "Junior Engineer" (`17`), "Managing Director" (`4`), "Marketing Executive" (`19`), "Marketing Manager" (`9`), "Operations Manager" (`8`), "President" (`1`), "Product Manager" (`5`), "Program Manager" (`7`), "Project Manager" (`6`), "pwerp_desig_3caprx" (`42`), "pwerp_desig_so8jlt" (`29`), "pwerp_desig_y7cni4" (`38`), "pwerp_desig_ygsll6" (`54`), "Senior Engineer" (`15`), "Technology Manager" (`11`), "Vice President" (`2`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-017 — `work[reporting_to]` ("Reporting To") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Read every option of `work[reporting_to]` ("Reporting To").
- **Expected:** The options are exactly: "- Select Employee -" (`0`), "pwerpFirst Q Lastushu" (`7`), "pwerpFirst Q Lasthasu" (`8`), "pwerpFirst Q Lastlsdk" (`9`), "pwerpFirst Q Lastytlo" (`10`), "pwerpFirst LastProbe" (`6`), "erp_employee" (`5`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-018 — `work[hiring_source]` ("Source of Hire") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Read every option of `work[hiring_source]` ("Source of Hire").
- **Expected:** The options are exactly: "- Select -" (`-1`), "Direct" (`direct`), "Referral" (`referral`), "Web" (`web`), "Newspaper" (`newspaper`), "Advertisement" (`advertisement`), "Social Network" (`social`), "Other" (`other`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-019 — `work[pay_type]` ("Pay Type") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Read every option of `work[pay_type]` ("Pay Type").
- **Expected:** The options are exactly: "- Select -" (`-1`), "Hourly" (`hourly`), "Daily" (`daily`), "Weekly" (`weekly`), "Biweekly" (`biweekly`), "Monthly" (`monthly`), "Contract" (`contract`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-020 — `personal[blood_group]` ("Blood Group") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Read every option of `personal[blood_group]` ("Blood Group").
- **Expected:** The options are exactly: "- Select -" (`-1`), "AB+" (`ab+`), "AB-" (`ab-`), "A+" (`a+`), "A-" (`a-`), "B+" (`b+`), "B-" (`b-`), "O+" (`o+`), "O-" (`o-`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-021 — `personal[nationality]` ("Nationality") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Read every option of `personal[nationality]` ("Nationality").
- **Expected:** The options are exactly: "- Select -" (`-1`), "Åland Islands" (`AX`), "Afghanistan" (`AF`), "Albania" (`AL`), "Algeria" (`DZ`), "Andorra" (`AD`), "Angola" (`AO`), "Anguilla" (`AI`), "Antarctica" (`AQ`), "Antigua and Barbuda" (`AG`), "Argentina" (`AR`), "Armenia" (`AM`), "Aruba" (`AW`), "Australia" (`AU`), "Austria" (`AT`), "Azerbaijan" (`AZ`), "Bahamas" (`BS`), "Bahrain" (`BH`), "Bangladesh" (`BD`), "Barbados" (`BB`), "Belarus" (`BY`), "Belgium" (`BE`), "Belize" (`BZ`), "Benin" (`BJ`), "Bermuda" (`BM`) … and 220 more (see harness).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-022 — `personal[gender]` ("Gender") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Read every option of `personal[gender]` ("Gender").
- **Expected:** The options are exactly: "Male" (`male`), "Female" (`female`), "Other" (`other`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-023 — `personal[marital_status]` ("Marital Status") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Read every option of `personal[marital_status]` ("Marital Status").
- **Expected:** The options are exactly: "Single" (`single`), "Married" (`married`), "Widowed" (`widowed`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-024 — `personal[country]` ("Country") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-new-employee`.
  2. Read every option of `personal[country]` ("Country").
- **Expected:** The options are exactly: "- Select -" (`-1`), "- Select -" (`-1`), "Åland Islands" (`AX`), "Afghanistan" (`AF`), "Albania" (`AL`), "Algeria" (`DZ`), "Andorra" (`AD`), "Angola" (`AO`), "Anguilla" (`AI`), "Antarctica" (`AQ`), "Antigua and Barbuda" (`AG`), "Argentina" (`AR`), "Armenia" (`AM`), "Aruba" (`AW`), "Australia" (`AU`), "Austria" (`AT`), "Azerbaijan" (`AZ`), "Bahamas" (`BS`), "Bahrain" (`BH`), "Bangladesh" (`BD`), "Barbados" (`BB`), "Belarus" (`BY`), "Belgium" (`BE`), "Belize" (`BZ`), "Benin" (`BJ`) … and 221 more (see harness).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-025 — Save modal form `tmpl-erp-employee-row` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open modal form `tmpl-erp-employee-row`.
  2. Fill all 1 fields with valid data.
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-026 — modal form `tmpl-erp-employee-row` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open modal form `tmpl-erp-employee-row`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `employee[]` — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-027 — Save modal form `tmpl-erp-employment-type` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open modal form `tmpl-erp-employment-type`.
  2. Fill all 3 fields with valid data (required: `date` ("Date *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-028 — modal form `tmpl-erp-employment-type` renders all 3 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open modal form `tmpl-erp-employment-type`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 3 fields render:
      - `date` ("Date *") — type `text`, **required**
      - `type` ("Employment Type") — type `select`
      - `comment` ("Comment") — type `textarea`, placeholder "Optional comment"
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-029 — `type` ("Employment Type") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-type`.
  2. Read every option of `type` ("Employment Type").
- **Expected:** The options are exactly: "- Select -" (`0`), "Full Time" (`permanent`), "Part Time" (`parttime`), "On Contract" (`contract`), "Temporary" (`temporary`), "Trainee" (`trainee`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-030 — Save modal form `tmpl-erp-employment-status` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open modal form `tmpl-erp-employment-status`.
  2. Fill all 3 fields with valid data (required: `date` ("Date *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-031 — modal form `tmpl-erp-employment-status` renders all 3 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open modal form `tmpl-erp-employment-status`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 3 fields render:
      - `date` ("Date *") — type `text`, **required**
      - `status` ("Employee Status") — type `select`
      - `comment` ("Comment") — type `textarea`, placeholder "Optional comment"
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-032 — `status` ("Employee Status") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-status`.
  2. Read every option of `status` ("Employee Status").
- **Expected:** The options are exactly: "- Select -" (`0`), "Active" (`active`), "Inactive" (`inactive`), "Terminated" (`terminated`), "Deceased" (`deceased`), "Resigned" (`resigned`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-033 — Save modal form `tmpl-erp-employment-compensation` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation`.
  2. Fill all 5 fields with valid data (required: `date` ("Date *"), `pay_rate` ("Pay Rate *"), `pay_type` ("Pay Type *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-034 — modal form `tmpl-erp-employment-compensation` renders all 5 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 5 fields render:
      - `date` ("Date *") — type `text`, **required**
      - `pay_rate` ("Pay Rate *") — type `text`, **required**
      - `pay_type` ("Pay Type *") — type `select`, **required**
      - `change-reason` ("Change Reason") — type `select`
      - `comment` ("Comment") — type `textarea`, placeholder "Optional comment"
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-035 — `pay_type` ("Pay Type *") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation`.
  2. Read every option of `pay_type` ("Pay Type *").
- **Expected:** The options are exactly: "- Select -" (`0`), "Hourly" (`hourly`), "Daily" (`daily`), "Weekly" (`weekly`), "Biweekly" (`biweekly`), "Monthly" (`monthly`), "Contract" (`contract`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-036 — `change-reason` ("Change Reason") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation`.
  2. Read every option of `change-reason` ("Change Reason").
- **Expected:** The options are exactly: "- Select -" (`0`), "Promotion" (`promotion`), "Performance" (`performance`), "Increment" (`increment`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-037 — Save modal form `tmpl-erp-employment-jobinfo` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open modal form `tmpl-erp-employment-jobinfo`.
  2. Fill all 5 fields with valid data (required: `date` ("Date *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-038 — modal form `tmpl-erp-employment-jobinfo` renders all 5 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open modal form `tmpl-erp-employment-jobinfo`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 5 fields render:
      - `date` ("Date *") — type `text`, **required**
      - `location` ("Location") — type `select`
      - `department` ("Department") — type `select`
      - `designation` ("Job Title") — type `select`
      - `reporting_to` ("Reporting To") — type `select`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-039 — `location` ("Location") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-jobinfo`.
  2. Read every option of `location` ("Location").
- **Expected:** The options are exactly: "- Select -" (`0`), "Main Location" (`-1`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-040 — `department` ("Department") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-jobinfo`.
  2. Read every option of `department` ("Department").
- **Expected:** The options are exactly: "- Select Department -" (`-1`), "General Management" (`1`), "Operations Department" (`2`), "Finance Department" (`3`), "Sales Department" (`4`), "Human Resource Department" (`5`), "Purchase Department" (`6`), "Engineering Department" (`7`), "Production Department" (`8`), "Procurement Department" (`9`), "pwerp_dept_2vjg9u" (`24`), "pwerp_dept_n40ldm" (`33`), "pwerp_dept_sfcrvj" (`41`), "pwerp_dept_t6il8m" (`59`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-041 — `designation` ("Job Title") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-jobinfo`.
  2. Read every option of `designation` ("Job Title").
- **Expected:** The options are exactly: "- Select Designation -" (`-1`), "Business Executive" (`18`), "Business Manager" (`10`), "CEO" (`3`), "Customer Support Executive" (`20`), "Engineer" (`16`), "Finance/Accounts Manager" (`12`), "Hiring Manager" (`14`), "Human Resource Manager" (`13`), "Junior Engineer" (`17`), "Managing Director" (`4`), "Marketing Executive" (`19`), "Marketing Manager" (`9`), "Operations Manager" (`8`), "President" (`1`), "Product Manager" (`5`), "Program Manager" (`7`), "Project Manager" (`6`), "pwerp_desig_3caprx" (`42`), "pwerp_desig_so8jlt" (`29`), "pwerp_desig_y7cni4" (`38`), "pwerp_desig_ygsll6" (`54`), "Senior Engineer" (`15`), "Technology Manager" (`11`), "Vice President" (`2`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-042 — `reporting_to` ("Reporting To") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-jobinfo`.
  2. Read every option of `reporting_to` ("Reporting To").
- **Expected:** The options are exactly: "- Select Employee -" (`0`), "pwerpFirst Q Lastushu" (`7`), "pwerpFirst Q Lasthasu" (`8`), "pwerpFirst Q Lastlsdk" (`9`), "pwerpFirst Q Lastytlo" (`10`), "pwerpFirst LastProbe" (`6`), "erp_employee" (`5`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-043 — Save modal form `tmpl-erp-employment-work-experience` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open modal form `tmpl-erp-employment-work-experience`.
  2. Fill all 5 fields with valid data (required: `company_name` ("Previous Company *"), `job_title` ("Job Title *"), `from` ("From *"), `to` ("To *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-044 — modal form `tmpl-erp-employment-work-experience` renders all 5 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open modal form `tmpl-erp-employment-work-experience`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 5 fields render:
      - `company_name` ("Previous Company *") — type `text`, **required**, placeholder "ABC Corporation"
      - `job_title` ("Job Title *") — type `text`, **required**, placeholder "Project Manager"
      - `from` ("From *") — type `text`, **required**, placeholder "1988-03-18"
      - `to` ("To *") — type `text`, **required**, placeholder "1988-03-18"
      - `description` ("Job Description") — type `textarea`, placeholder "Details about the job"
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-045 — Save modal form `tmpl-erp-employment-education` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. Fill all 10 fields with valid data (required: `school` ("School Name *"), `degree` ("Degree *"), `field` ("Field of Study *"), `finished` ("Year of Completion *"), `result_type` ("Result type *"), `gpa` ("Result (Grade) *"), `scale` ("Scale (Out of) *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-046 — modal form `tmpl-erp-employment-education` renders all 10 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 10 fields render:
      - `school` ("School Name *") — type `text`, **required**, placeholder "ABC School"
      - `degree` ("Degree *") — type `text`, **required**, placeholder "Bachelor in Science"
      - `field` ("Field of Study *") — type `text`, **required**, placeholder "Physics"
      - `finished` ("Year of Completion *") — type `number`, **required**, placeholder "2026"
      - `result_type` ("Result type *") — type `select`, **required**
      - `gpa` ("Result (Grade) *") — type `text`, **required**, placeholder "5.0"
      - `scale` ("Scale (Out of) *") — type `number`, **required**, placeholder "5.0"
      - `notes` ("Notes") — type `textarea`, placeholder "Additional notes"
      - `interest` ("Interests") — type `textarea`
      - `expiration_date` ("Expiration date") — type `text`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-047 — `result_type` ("Result type *") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-education`.
  2. Read every option of `result_type` ("Result type *").
- **Expected:** The options are exactly: "- Select -" (``), "Grade" (`grade`), "Pecentage" (`percentage`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-048 — Save modal form `tmpl-erp-employment-performance-reviews` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-reviews`.
  2. Fill all 7 fields with valid data (required: `performance_date` ("Review Date *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-049 — modal form `tmpl-erp-employment-performance-reviews` renders all 7 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-reviews`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 7 fields render:
      - `performance_date` ("Review Date *") — type `text`, **required**
      - `reporting_to` ("Reporting To") — type `select`
      - `job_knowledge` ("Job Knowledge") — type `select`
      - `work_quality` ("Work Quality") — type `select`
      - `attendance` ("Attendance/Punctuality") — type `select`
      - `communication` ("Communication/Listening") — type `select`
      - `dependablity` ("Dependability") — type `select`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-050 — `reporting_to` ("Reporting To") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-reviews`.
  2. Read every option of `reporting_to` ("Reporting To").
- **Expected:** The options are exactly: "- Select Employee -" (`0`), "pwerpFirst Q Lastushu" (`7`), "pwerpFirst Q Lasthasu" (`8`), "pwerpFirst Q Lastlsdk" (`9`), "pwerpFirst Q Lastytlo" (`10`), "pwerpFirst LastProbe" (`6`), "erp_employee" (`5`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-051 — `job_knowledge` ("Job Knowledge") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-reviews`.
  2. Read every option of `job_knowledge` ("Job Knowledge").
- **Expected:** The options are exactly: "- Select -" (`0`), "Very Bad" (`1`), "Poor" (`2`), "Average" (`3`), "Good" (`4`), "Excellent" (`5`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-052 — `work_quality` ("Work Quality") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-reviews`.
  2. Read every option of `work_quality` ("Work Quality").
- **Expected:** The options are exactly: "- Select -" (`0`), "Very Bad" (`1`), "Poor" (`2`), "Average" (`3`), "Good" (`4`), "Excellent" (`5`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-053 — `attendance` ("Attendance/Punctuality") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-reviews`.
  2. Read every option of `attendance` ("Attendance/Punctuality").
- **Expected:** The options are exactly: "- Select -" (`0`), "Very Bad" (`1`), "Poor" (`2`), "Average" (`3`), "Good" (`4`), "Excellent" (`5`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-054 — `communication` ("Communication/Listening") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-reviews`.
  2. Read every option of `communication` ("Communication/Listening").
- **Expected:** The options are exactly: "- Select -" (`0`), "Very Bad" (`1`), "Poor" (`2`), "Average" (`3`), "Good" (`4`), "Excellent" (`5`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-055 — `dependablity` ("Dependability") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-reviews`.
  2. Read every option of `dependablity` ("Dependability").
- **Expected:** The options are exactly: "- Select -" (`0`), "Very Bad" (`1`), "Poor" (`2`), "Average" (`3`), "Good" (`4`), "Excellent" (`5`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-056 — Save modal form `tmpl-erp-employment-performance-comments` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-comments`.
  2. Fill all 3 fields with valid data (required: `performance_date` ("Reference Date *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-057 — modal form `tmpl-erp-employment-performance-comments` renders all 3 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-comments`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 3 fields render:
      - `performance_date` ("Reference Date *") — type `text`, **required**
      - `reviewer` ("Reviewer") — type `select`
      - `comments` ("Comments") — type `textarea`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-058 — `reviewer` ("Reviewer") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-comments`.
  2. Read every option of `reviewer` ("Reviewer").
- **Expected:** The options are exactly: "- Select Employee -" (`0`), "pwerpFirst Q Lastushu" (`7`), "pwerpFirst Q Lasthasu" (`8`), "pwerpFirst Q Lastlsdk" (`9`), "pwerpFirst Q Lastytlo" (`10`), "pwerpFirst LastProbe" (`6`), "erp_employee" (`5`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-059 — Save modal form `tmpl-erp-employment-performance-goals` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-goals`.
  2. Fill all 6 fields with valid data (required: `performance_date` ("Set Date *"), `completion_date` ("Completion Date *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-060 — modal form `tmpl-erp-employment-performance-goals` renders all 6 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-goals`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 6 fields render:
      - `performance_date` ("Set Date *") — type `text`, **required**
      - `completion_date` ("Completion Date *") — type `text`, **required**
      - `goal_description` ("Goal Description") — type `textarea`
      - `employee_assessment` ("Employee Assessment") — type `textarea`
      - `supervisor` ("Supervisor") — type `select`
      - `supervisor_assessment` ("Supervisor Assessment") — type `textarea`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-061 — `supervisor` ("Supervisor") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-performance-goals`.
  2. Read every option of `supervisor` ("Supervisor").
- **Expected:** The options are exactly: "- Select Employee -" (`0`), "pwerpFirst Q Lastushu" (`7`), "pwerpFirst Q Lasthasu" (`8`), "pwerpFirst Q Lastlsdk" (`9`), "pwerpFirst Q Lastytlo" (`10`), "pwerpFirst LastProbe" (`6`), "erp_employee" (`5`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-062 — Save modal form `tmpl-erp-employment-dependent` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open modal form `tmpl-erp-employment-dependent`.
  2. Fill all 3 fields with valid data (required: `name` ("Name *"), `relation` ("Relationship *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-063 — modal form `tmpl-erp-employment-dependent` renders all 3 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open modal form `tmpl-erp-employment-dependent`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 3 fields render:
      - `name` ("Name *") — type `text`, **required**, placeholder "Name of the person"
      - `relation` ("Relationship *") — type `text`, **required**, placeholder "Father"
      - `dob` ("Date of Birth") — type `text`, placeholder "1988-03-18"
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-064 — Save modal form `tmpl-erp-new-dept` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open modal form `tmpl-erp-new-dept`.
  2. Fill all 4 fields with valid data (required: `title` ("Department Title *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-065 — modal form `tmpl-erp-new-dept` renders all 4 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open modal form `tmpl-erp-new-dept`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 4 fields render:
      - `title` ("Department Title *") — type `text`, **required**
      - `dept-desc` ("Description") — type `textarea`, placeholder "Optional"
      - `lead` ("Department Manager ...") — type `select`
      - `parent` — type `select`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-066 — `lead` ("Department Manager ...") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-new-dept`.
  2. Read every option of `lead` ("Department Manager ...").
- **Expected:** The options are exactly: "- Select Employee -" (`0`), "pwerpFirst Q Lastushu" (`7`), "pwerpFirst Q Lasthasu" (`8`), "pwerpFirst Q Lastlsdk" (`9`), "pwerpFirst Q Lastytlo" (`10`), "pwerpFirst LastProbe" (`6`), "erp_employee" (`5`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-067 — `parent` offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-new-dept`.
  2. Read every option of `parent`.
- **Expected:** The options are exactly: "- Select Department -" (`-1`), "General Management" (`1`), "Operations Department" (`2`), "Finance Department" (`3`), "Sales Department" (`4`), "Human Resource Department" (`5`), "Purchase Department" (`6`), "Engineering Department" (`7`), "Production Department" (`8`), "Procurement Department" (`9`), "pwerp_dept_2vjg9u" (`24`), "pwerp_dept_n40ldm" (`33`), "pwerp_dept_sfcrvj" (`41`), "pwerp_dept_t6il8m" (`59`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-068 — Save modal form `tmpl-erp-new-desig` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open modal form `tmpl-erp-new-desig`.
  2. Fill all 2 fields with valid data (required: `title` ("Designation Title *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-069 — modal form `tmpl-erp-new-desig` renders all 2 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open modal form `tmpl-erp-new-desig`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 2 fields render:
      - `title` ("Designation Title *") — type `text`, **required**
      - `desig-desc` ("Description") — type `textarea`, placeholder "Optional"
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-070 — Save modal form `tmpl-erp-employment-terminate` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open modal form `tmpl-erp-employment-terminate`.
  2. Fill all 4 fields with valid data (required: `terminate_date` ("Termination Date *"), `termination_type` ("Termination Type *"), `termination_reason` ("Termination Reason *"), `eligible_for_rehire` ("Eligible for Rehire *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-071 — modal form `tmpl-erp-employment-terminate` renders all 4 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open modal form `tmpl-erp-employment-terminate`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 4 fields render:
      - `terminate_date` ("Termination Date *") — type `text`, **required**
      - `termination_type` ("Termination Type *") — type `select`, **required**
      - `termination_reason` ("Termination Reason *") — type `select`, **required**
      - `eligible_for_rehire` ("Eligible for Rehire *") — type `select`, **required**
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-072 — `termination_type` ("Termination Type *") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-terminate`.
  2. Read every option of `termination_type` ("Termination Type *").
- **Expected:** The options are exactly: "- Select -" (``), "Voluntary" (`voluntary`), "Involuntary" (`involuntary`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-073 — `termination_reason` ("Termination Reason *") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-terminate`.
  2. Read every option of `termination_reason` ("Termination Reason *").
- **Expected:** The options are exactly: "- Select -" (``), "Attendance" (`attendance`), "Better Employment Conditions" (`better_employment`), "Career Prospect" (`career_prospect`), "Death" (`death`), "Desertion" (`desertion`), "Dismissed" (`dismissed`), "Dissatisfaction with the job" (`dissatisfaction`), "Higher Pay" (`higher_pay`), "Other Employment" (`other_employement`), "Personality Conflicts" (`personality_conflicts`), "Relocation" (`relocation`), "Retirement" (`retirement`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-074 — `eligible_for_rehire` ("Eligible for Rehire *") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-terminate`.
  2. Read every option of `eligible_for_rehire` ("Eligible for Rehire *").
- **Expected:** The options are exactly: "- Select -" (``), "Yes" (`yes`), "No" (`no`), "Upon Review" (`upon_review`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-075 — Save modal form `tmpl-erp-employee-import-csv` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open modal form `tmpl-erp-employee-import-csv`.
  2. Fill all 1 fields with valid data (required: `csv_file` ("CSV File *")).
  3. Submit with "Download Sample CSV".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-076 — modal form `tmpl-erp-employee-import-csv` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open modal form `tmpl-erp-employee-import-csv`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `csv_file` ("CSV File *") — type `file`, **required**
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-077 — Save modal form `tmpl-erp-employee-export-csv` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open modal form `tmpl-erp-employee-export-csv`.
  2. Fill all 1 fields with valid data.
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-078 — modal form `tmpl-erp-employee-export-csv` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open modal form `tmpl-erp-employee-export-csv`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `selecctall` — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-079 — Save modal form `tmpl-erp-employment-compensation-history` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation-history`.
  2. Fill all 5 fields with valid data (required: `date` ("Date *"), `type` ("Pay Rate *"), `category` ("Pay Type *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-080 — modal form `tmpl-erp-employment-compensation-history` renders all 5 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation-history`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 5 fields render:
      - `date` ("Date *") — type `text`, **required**
      - `type` ("Pay Rate *") — type `text`, **required**
      - `category` ("Pay Type *") — type `select`, **required**
      - `data` ("Change Reason") — type `select`
      - `comment` ("Comment") — type `textarea`, placeholder "Optional comment"
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-081 — `category` ("Pay Type *") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation-history`.
  2. Read every option of `category` ("Pay Type *").
- **Expected:** The options are exactly: "- Select -" (`0`), "Hourly" (`hourly`), "Daily" (`daily`), "Weekly" (`weekly`), "Biweekly" (`biweekly`), "Monthly" (`monthly`), "Contract" (`contract`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-082 — `data` ("Change Reason") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-compensation-history`.
  2. Read every option of `data` ("Change Reason").
- **Expected:** The options are exactly: "- Select -" (`0`), "Promotion" (`promotion`), "Performance" (`performance`), "Increment" (`increment`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-083 — Save modal form `tmpl-erp-employment-job-info-history` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open modal form `tmpl-erp-employment-job-info-history`.
  2. Fill all 5 fields with valid data (required: `date` ("Date *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-084 — modal form `tmpl-erp-employment-job-info-history` renders all 5 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open modal form `tmpl-erp-employment-job-info-history`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 5 fields render:
      - `date` ("Date *") — type `text`, **required**
      - `type` ("Location") — type `select`
      - `category` ("Department") — type `select`
      - `comment` ("Job Title") — type `select`
      - `data` ("Reporting To") — type `select`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-085 — `type` ("Location") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-job-info-history`.
  2. Read every option of `type` ("Location").
- **Expected:** The options are exactly: "- Select -" (`0`), "Main Location" (`-1`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-086 — `category` ("Department") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-job-info-history`.
  2. Read every option of `category` ("Department").
- **Expected:** The options are exactly: "- Select Department -" (`-1`), "General Management" (`1`), "Operations Department" (`2`), "Finance Department" (`3`), "Sales Department" (`4`), "Human Resource Department" (`5`), "Purchase Department" (`6`), "Engineering Department" (`7`), "Production Department" (`8`), "Procurement Department" (`9`), "pwerp_dept_2vjg9u" (`24`), "pwerp_dept_n40ldm" (`33`), "pwerp_dept_sfcrvj" (`41`), "pwerp_dept_t6il8m" (`59`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-087 — `comment` ("Job Title") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-job-info-history`.
  2. Read every option of `comment` ("Job Title").
- **Expected:** The options are exactly: "- Select Designation -" (`-1`), "Business Executive" (`18`), "Business Manager" (`10`), "CEO" (`3`), "Customer Support Executive" (`20`), "Engineer" (`16`), "Finance/Accounts Manager" (`12`), "Hiring Manager" (`14`), "Human Resource Manager" (`13`), "Junior Engineer" (`17`), "Managing Director" (`4`), "Marketing Executive" (`19`), "Marketing Manager" (`9`), "Operations Manager" (`8`), "President" (`1`), "Product Manager" (`5`), "Program Manager" (`7`), "Project Manager" (`6`), "pwerp_desig_3caprx" (`42`), "pwerp_desig_so8jlt" (`29`), "pwerp_desig_y7cni4" (`38`), "pwerp_desig_ygsll6" (`54`), "Senior Engineer" (`15`), "Technology Manager" (`11`), "Vice President" (`2`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-088 — `data` ("Reporting To") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-job-info-history`.
  2. Read every option of `data` ("Reporting To").
- **Expected:** The options are exactly: "- Select Employee -" (`0`), "pwerpFirst Q Lastushu" (`7`), "pwerpFirst Q Lasthasu" (`8`), "pwerpFirst Q Lastlsdk" (`9`), "pwerpFirst Q Lastytlo" (`10`), "pwerpFirst LastProbe" (`6`), "erp_employee" (`5`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-089 — Save modal form `tmpl-erp-employment-type-history` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open modal form `tmpl-erp-employment-type-history`.
  2. Fill all 3 fields with valid data (required: `date` ("Date *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-090 — modal form `tmpl-erp-employment-type-history` renders all 3 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open modal form `tmpl-erp-employment-type-history`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 3 fields render:
      - `date` ("Date *") — type `text`, **required**
      - `type` ("Employment Type") — type `select`
      - `comment` ("Comment") — type `textarea`, placeholder "Optional comment"
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-091 — `type` ("Employment Type") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open modal form `tmpl-erp-employment-type-history`.
  2. Read every option of `type` ("Employment Type").
- **Expected:** The options are exactly: "- Select -" (`0`), "Full Time" (`permanent`), "Part Time" (`parttime`), "On Contract" (`contract`), "Temporary" (`temporary`), "Trainee" (`trainee`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-092 — Save modal form `tmpl-erp-desig-row` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open modal form `tmpl-erp-desig-row`.
  2. Fill all 1 fields with valid data.
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-093 — modal form `tmpl-erp-desig-row` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open modal form `tmpl-erp-desig-row`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `desig[]` — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-094 — Save `admin.php?page=erp-hr&section=people` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people`.
  2. Fill all 6 fields with valid data.
  3. Submit with "Apply".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-095 — `admin.php?page=erp-hr&section=people` renders all 6 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 6 fields render:
      - `filter_designation` — type `select`
      - `filter_department` — type `select`
      - `filter_employment_type` — type `select`
      - `hide_filter` — type `submit`
      - `reset_filter` — type `submit`
      - `filter_employee` — type `submit`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-096 — `filter_designation` offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people`.
  2. Read every option of `filter_designation`.
- **Expected:** The options are exactly: "Designation" (`-1`), "Business Executive" (`18`), "Business Manager" (`10`), "CEO" (`3`), "Customer Support Executive" (`20`), "Engineer" (`16`), "Finance/Accounts Manager" (`12`), "Hiring Manager" (`14`), "Human Resource Manager" (`13`), "Junior Engineer" (`17`), "Managing Director" (`4`), "Marketing Executive" (`19`), "Marketing Manager" (`9`), "Operations Manager" (`8`), "President" (`1`), "Product Manager" (`5`), "Program Manager" (`7`), "Project Manager" (`6`), "pwerp_desig_3caprx" (`42`), "pwerp_desig_so8jlt" (`29`), "pwerp_desig_y7cni4" (`38`), "pwerp_desig_ygsll6" (`54`), "Senior Engineer" (`15`), "Technology Manager" (`11`), "Vice President" (`2`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-097 — `filter_department` offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people`.
  2. Read every option of `filter_department`.
- **Expected:** The options are exactly: "Department" (`-1`), "General Management" (`1`), "Operations Department" (`2`), "Finance Department" (`3`), "Sales Department" (`4`), "Human Resource Department" (`5`), "Purchase Department" (`6`), "Engineering Department" (`7`), "Production Department" (`8`), "Procurement Department" (`9`), "pwerp_dept_2vjg9u" (`24`), "pwerp_dept_n40ldm" (`33`), "pwerp_dept_sfcrvj" (`41`), "pwerp_dept_t6il8m" (`59`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-098 — `filter_employment_type` offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people`.
  2. Read every option of `filter_employment_type`.
- **Expected:** The options are exactly: "Employment Type" (`-1`), "Full Time" (`permanent`), "Part Time" (`parttime`), "On Contract" (`contract`), "Temporary" (`temporary`), "Trainee" (`trainee`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-099 — Save `admin.php?page=erp-hr&section=people&sub-section=employee` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=employee`.
  2. Fill all 6 fields with valid data.
  3. Submit with "Apply".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-100 — `admin.php?page=erp-hr&section=people&sub-section=employee` renders all 6 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=employee`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 6 fields render:
      - `filter_designation` — type `select`
      - `filter_department` — type `select`
      - `filter_employment_type` — type `select`
      - `hide_filter` — type `submit`
      - `reset_filter` — type `submit`
      - `filter_employee` — type `submit`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_PEOPLE-T1-101 — `filter_designation` offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=employee`.
  2. Read every option of `filter_designation`.
- **Expected:** The options are exactly: "Designation" (`-1`), "Business Executive" (`18`), "Business Manager" (`10`), "CEO" (`3`), "Customer Support Executive" (`20`), "Engineer" (`16`), "Finance/Accounts Manager" (`12`), "Hiring Manager" (`14`), "Human Resource Manager" (`13`), "Junior Engineer" (`17`), "Managing Director" (`4`), "Marketing Executive" (`19`), "Marketing Manager" (`9`), "Operations Manager" (`8`), "President" (`1`), "Product Manager" (`5`), "Program Manager" (`7`), "Project Manager" (`6`), "pwerp_desig_3caprx" (`42`), "pwerp_desig_so8jlt" (`29`), "pwerp_desig_y7cni4" (`38`), "pwerp_desig_ygsll6" (`54`), "Senior Engineer" (`15`), "Technology Manager" (`11`), "Vice President" (`2`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-102 — `filter_department` offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=employee`.
  2. Read every option of `filter_department`.
- **Expected:** The options are exactly: "Department" (`-1`), "General Management" (`1`), "Operations Department" (`2`), "Finance Department" (`3`), "Sales Department" (`4`), "Human Resource Department" (`5`), "Purchase Department" (`6`), "Engineering Department" (`7`), "Production Department" (`8`), "Procurement Department" (`9`), "pwerp_dept_2vjg9u" (`24`), "pwerp_dept_n40ldm" (`33`), "pwerp_dept_sfcrvj" (`41`), "pwerp_dept_t6il8m" (`59`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-103 — `filter_employment_type` offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-people @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=employee`.
  2. Read every option of `filter_employment_type`.
- **Expected:** The options are exactly: "Employment Type" (`-1`), "Full Time" (`permanent`), "Part Time" (`parttime`), "On Contract" (`contract`), "Temporary" (`temporary`), "Trainee" (`trainee`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_PEOPLE-T1-104 — Save `admin.php?page=erp-hr&section=people&sub-section=designation` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier1 @hrm-people @crud
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  2. Fill all 20 fields with valid data.
  3. Submit with "Apply".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_PEOPLE-T1-105 — `admin.php?page=erp-hr&section=people&sub-section=designation` renders all 20 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Tags:** @tier1 @hrm-people @labels
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=people&sub-section=designation`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 20 fields render:
      - `desig[]` — type `checkbox`
      - `desig[]` — type `checkbox`
      - `desig[]` — type `checkbox`
      - `desig[]` — type `checkbox`
      - `desig[]` — type `checkbox`
      - `desig[]` — type `checkbox`
      - `desig[]` — type `checkbox`
      - `desig[]` — type `checkbox`
      - `desig[]` — type `checkbox`
      - `desig[]` — type `checkbox`
      - `desig[]` — type `checkbox`
      - `desig[]` — type `checkbox`
      - `desig[]` — type `checkbox`
      - `desig[]` — type `checkbox`
      - `desig[]` — type `checkbox`
      - `desig[]` — type `checkbox`
      - `desig[]` — type `checkbox`
      - `desig[]` — type `checkbox`
      - `desig[]` — type `checkbox`
      - `desig[]` — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.
