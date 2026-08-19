# Tier 1 — hrm-training

Happy path — the screen loads, every field and label renders as captured, and the primary create/read/update/delete flow succeeds.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**14 cases** (14 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### HRM_TRAINING-T1-001 — Training loads and renders its controls

- **Surface:** `edit.php?post_type=erp_hr_training`
- **Tags:** @tier1 @hrm-training @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `edit.php?post_type=erp_hr_training`.
  3. Confirm the action buttons render: "Apply", "Screen Options".
  4. Confirm the list columns render: Select All, Title Sort ascending., Training Subject, Description, Duration, Participants.
- **Expected:** The screen returns 200, renders its heading "Training", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_TRAINING-T1-002 — Add New Training loads and renders its controls

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier1 @hrm-training @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `post-new.php?post_type=erp_hr_training`.
  3. Confirm the action buttons render: "Screen Options", "Move up", "Move down", "Toggle panel: Publish", "Save", "Save Draft".
- **Expected:** The screen returns 200, renders its heading "Add New Training", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_TRAINING-T1-003 — Save modal form `tmpl-employee-assign-new-training` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-training @crud
- **Steps:**
  1. Open modal form `tmpl-employee-assign-new-training`.
  2. Fill all 9 fields with valid data (required: `training-id` ("Training *"), `training-completed-date` ("Completed Date *"), `training-trainer` ("Trainer's name *"), `training-rate` ("Rating *")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_TRAINING-T1-004 — modal form `tmpl-employee-assign-new-training` renders all 9 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier1 @hrm-training @labels
- **Steps:**
  1. Open modal form `tmpl-employee-assign-new-training`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 9 fields render:
      - `training-id` ("Training *") — type `select`, **required**
      - `training-completed-date` ("Completed Date *") — type `text`, **required**
      - `training-trainer` ("Trainer's name *") — type `text`, **required**
      - `trainer-phone` ("Trainer' Phone No.") — type `text`
      - `training-cost` ("Cost") — type `text`
      - `training-credit` ("Credit") — type `text`
      - `training-hours` ("Hours") — type `text`
      - `training-notes` ("Notes") — type `textarea`
      - `training-rate` ("Rating *") — type `number`, **required**, placeholder "Rate between 1 to 10"
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_TRAINING-T1-005 — Save `edit.php?post_type=erp_hr_training` with every field completed

- **Surface:** `edit.php?post_type=erp_hr_training`
- **Tags:** @tier1 @hrm-training @crud
- **Steps:**
  1. Open `edit.php?post_type=erp_hr_training`.
  2. Fill all 2 fields with valid data.
  3. Submit with "Apply".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_TRAINING-T1-006 — `edit.php?post_type=erp_hr_training` renders all 2 fields with their labels

- **Surface:** `edit.php?post_type=erp_hr_training`
- **Tags:** @tier1 @hrm-training @labels
- **Steps:**
  1. Open `edit.php?post_type=erp_hr_training`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 2 fields render:
      - `mode` ("Compact view") — type `radio`
      - `mode` ("Extended view") — type `radio`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_TRAINING-T1-007 — Save `post-new.php?post_type=erp_hr_training` with every field completed

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier1 @hrm-training @crud
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Fill all 26 fields with valid data (required: `mm` ("Month 01-Jan 02-Feb 03-Mar 04-Apr 05-May 06-Jun 07-Jul 08-Aug 09-Sep 10-Oct 11-Nov 12-Dec"), `jj` ("Day"), `aa` ("Year"), `hh` ("Hour"), `mn` ("Minute")).
  3. Submit with "Save".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_TRAINING-T1-008 — `post-new.php?post_type=erp_hr_training` renders all 26 fields with their labels

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier1 @hrm-training @labels
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 26 fields render:
      - `screen_columns` ("1 column") — type `radio`
      - `screen_columns` ("2 columns") — type `radio`
      - `editor-expand-toggle` ("Enable full-height editor and distraction-free functionality.") — type `checkbox`
      - `post_title` ("Add title") — type `text`
      - `save` — type `submit`
      - `save` — type `submit`
      - `post_status` ("Set status") — type `select`
      - `visibility` ("Public") — type `radio`
      - `visibility` ("Password protected") — type `radio`
      - `post_password` ("Password:") — type `text`
      - `visibility` ("Private") — type `radio`
      - `mm` ("Month 01-Jan 02-Feb 03-Mar 04-Apr 05-May 06-Jun 07-Jul 08-Aug 09-Sep 10-Oct 11-Nov 12-Dec") — type `select`, **required**
      - `jj` ("Day") — type `text`, **required**
      - `aa` ("Year") — type `text`, **required**
      - `hh` ("Hour") — type `text`, **required**
      - `mn` ("Minute") — type `text`, **required**
      - `publish` — type `submit`
      - `post_name` ("Slug") — type `text`
      - `training_subject` ("Training Subject (Skill):") — type `text`
      - `training_type` ("Assign To") — type `select`
      - `employees[]` ("Select Employees") — type `select`
      - `departments[]` ("Select Departments") — type `select`
      - `designations[]` ("Select Designations") — type `select`
      - `training_frequency` ("Duration") — type `text`
      - `auto_assigned` ("Auto assigned for new employee") — type `checkbox`
      - `description` ("Description:") — type `textarea`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_TRAINING-T1-009 — `post_status` ("Set status") offers its full option set

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier1 @hrm-training @options
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Read every option of `post_status` ("Set status").
- **Expected:** The options are exactly: "Pending Review" (`pending`), "Draft" (`draft`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_TRAINING-T1-010 — `mm` ("Month 01-Jan 02-Feb 03-Mar 04-Apr 05-May 06-Jun 07-Jul 08-Aug 09-Sep 10-Oct 11-Nov 12-Dec") offers its full option set

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier1 @hrm-training @options
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Read every option of `mm` ("Month 01-Jan 02-Feb 03-Mar 04-Apr 05-May 06-Jun 07-Jul 08-Aug 09-Sep 10-Oct 11-Nov 12-Dec").
- **Expected:** The options are exactly: "01-Jan" (`01`), "02-Feb" (`02`), "03-Mar" (`03`), "04-Apr" (`04`), "05-May" (`05`), "06-Jun" (`06`), "07-Jul" (`07`), "08-Aug" (`08`), "09-Sep" (`09`), "10-Oct" (`10`), "11-Nov" (`11`), "12-Dec" (`12`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_TRAINING-T1-011 — `training_type` ("Assign To") offers its full option set

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier1 @hrm-training @options
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Read every option of `training_type` ("Assign To").
- **Expected:** The options are exactly: "-- Select --" (``), "All Employees" (`all_employee`), "Selected Employee" (`selected_employee`), "By Department" (`by_department`), "By Designation" (`by_designation`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_TRAINING-T1-012 — `employees[]` ("Select Employees") offers its full option set

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier1 @hrm-training @options
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Read every option of `employees[]` ("Select Employees").
- **Expected:** The options are exactly: "pwerpFirst Q Lastushu" (`7`), "pwerpFirst Q Lasthasu" (`8`), "pwerpFirst Q Lastlsdk" (`9`), "pwerpFirst Q Lastytlo" (`10`), "pwerpFirst LastProbe" (`6`), "erp_employee" (`5`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_TRAINING-T1-013 — `departments[]` ("Select Departments") offers its full option set

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier1 @hrm-training @options
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Read every option of `departments[]` ("Select Departments").
- **Expected:** The options are exactly: "General Management" (`1`), "Operations Department" (`2`), "Finance Department" (`3`), "Sales Department" (`4`), "Human Resource Department" (`5`), "Purchase Department" (`6`), "Engineering Department" (`7`), "Production Department" (`8`), "Procurement Department" (`9`), "pwerp_dept_2vjg9u" (`24`), "pwerp_dept_n40ldm" (`33`), "pwerp_dept_sfcrvj" (`41`), "pwerp_dept_t6il8m" (`59`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_TRAINING-T1-014 — `designations[]` ("Select Designations") offers its full option set

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier1 @hrm-training @options
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Read every option of `designations[]` ("Select Designations").
- **Expected:** The options are exactly: "Business Executive" (`18`), "Business Manager" (`10`), "CEO" (`3`), "Customer Support Executive" (`20`), "Engineer" (`16`), "Finance/Accounts Manager" (`12`), "Hiring Manager" (`14`), "Human Resource Manager" (`13`), "Junior Engineer" (`17`), "Managing Director" (`4`), "Marketing Executive" (`19`), "Marketing Manager" (`9`), "Operations Manager" (`8`), "President" (`1`), "Product Manager" (`5`), "Program Manager" (`7`), "Project Manager" (`6`), "pwerp_desig_3caprx" (`42`), "pwerp_desig_so8jlt" (`29`), "pwerp_desig_y7cni4" (`38`), "pwerp_desig_ygsll6" (`54`), "Senior Engineer" (`15`), "Technology Manager" (`11`), "Vice President" (`2`).
- **Oracle:** UI — `<option>` label/value pairs.
