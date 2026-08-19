# Tier 1 — hrm-recruitment

Happy path — the screen loads, every field and label renders as captured, and the primary create/read/update/delete flow succeeds.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**43 cases** (42 derived from the harness, 1 hand-written business flows).

## Business flows

#### HRM_RECRUITMENT-F1-001 — Job opening → candidate → stage → shortlist

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`
- **Tags:** @tier1 @hrm-recruitment @pro @flow
- **Steps:**
  1. Publish a job opening.
  2. Add a candidate against it.
  3. Move the candidate through the configured stages.
  4. Shortlist the candidate.
- **Expected:** The candidate appears under the opening, the stage change is recorded, and the shortlist state persists across a reload.
- **Oracle:** REST `erp/v1/hrm/recruitment/candidates/{id}/stage` and `/shortlist` + the list rendering.

## Screen & field coverage

#### HRM_RECRUITMENT-T1-001 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=job-opening`
- **Tags:** @tier1 @hrm-recruitment @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=recruitment&sub-section=job-opening`.
  3. Confirm the action buttons render: "Apply", "Screen Options".
  4. Confirm the list columns render: Select All, Job Title Sort descending., Applicants Sort descending., Status, Created On, Expire Date, Publish Date, Action.
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_RECRUITMENT-T1-002 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`
- **Tags:** @tier1 @hrm-recruitment @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "Next →".
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_RECRUITMENT-T1-003 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list`
- **Tags:** @tier1 @hrm-recruitment @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "Filter", "Download All CV".
  4. Confirm the list columns render: Select All, Name Sort descending., Stage Sort descending., Rating Sort descending., Date Sort descending., Action.
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_RECRUITMENT-T1-004 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=add_candidate`
- **Tags:** @tier1 @hrm-recruitment @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=recruitment&sub-section=add_candidate`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "Show form".
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_RECRUITMENT-T1-005 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=stages`
- **Tags:** @tier1 @hrm-recruitment @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=recruitment&sub-section=stages`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "Edit", "Delete".
  4. Confirm the list columns render: Stage Name, Jobs Using, Candidates, Actions.
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_RECRUITMENT-T1-006 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=reports`
- **Tags:** @tier1 @hrm-recruitment @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=recruitment&sub-section=reports`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "Generate".
  4. Confirm the list columns render: Opening, Created, # Candidates Added, How are the candidates distributed, In Process, Archived, Unscreened, Other.
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_RECRUITMENT-T1-007 — HR Questionnaire loads and renders its controls

- **Surface:** `edit.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier1 @hrm-recruitment @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `edit.php?post_type=erp_hr_questionnaire`.
  3. Confirm the action buttons render: "Apply", "Screen Options".
  4. Confirm the list columns render: Select All, Title Sort ascending., Date, Total Question, Created On, Modified.
- **Expected:** The screen returns 200, renders its heading "HR Questionnaire", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_RECRUITMENT-T1-008 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=todo-calendar`
- **Tags:** @tier1 @hrm-recruitment @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=recruitment&sub-section=todo-calendar`.
  3. Confirm the action buttons render: "Apply", "Screen Options".
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_RECRUITMENT-T1-009 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`
- **Tags:** @tier1 @hrm-recruitment @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "Clear Filters", "Card View", "List View", "Previous".
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_RECRUITMENT-T1-010 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Tags:** @tier1 @hrm-recruitment @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "Generate Job Description", "Clear Form", "Save as Job Post", "Regenerate".
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_RECRUITMENT-T1-011 — Add New HR Questionnaire loads and renders its controls

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier1 @hrm-recruitment @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `post-new.php?post_type=erp_hr_questionnaire`.
  3. Confirm the action buttons render: "Screen Options", "Move up", "Move down", "Toggle panel: Publish", "Save", "Save Draft".
- **Expected:** The screen returns 200, renders its heading "Add New HR Questionnaire", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_RECRUITMENT-T1-012 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier1 @hrm-recruitment @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "More", "← Back to Jobs", "Generate with AI", "Save".
  4. Confirm the list columns render: Job Title, AI Candidate Processing, Actions.
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_RECRUITMENT-T1-013 — HR loads and renders its controls

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings`
- **Tags:** @tier1 @hrm-recruitment @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings`.
  3. Confirm the action buttons render: "Apply", "Screen Options", "More", "Save Settings", "Save Model".
- **Expected:** The screen returns 200, renders its heading "HR", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### HRM_RECRUITMENT-T1-014 — Save `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`
- **Tags:** @tier1 @hrm-recruitment @crud
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`.
  2. Fill all 8 fields with valid data.
  3. Submit with "Apply".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_RECRUITMENT-T1-015 — `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening` renders all 8 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`
- **Tags:** @tier1 @hrm-recruitment @labels
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 8 fields render:
      - `opening_title` — type `text`
      - `opening_description` — type `textarea`
      - `create_opening` — type `submit`
      - `wp-link-url` ("URL") — type `text`
      - `wp-link-text` ("Link Text") — type `text`
      - `wp-link-target` ("Open link in a new tab") — type `checkbox`
      - `wp-link-search` ("Search") — type `search`
      - `wp-link-submit` — type `submit`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_RECRUITMENT-T1-016 — Save `admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list`
- **Tags:** @tier1 @hrm-recruitment @crud
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list`.
  2. Fill all 3 fields with valid data.
  3. Submit with "Apply".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_RECRUITMENT-T1-017 — `admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list` renders all 3 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list`
- **Tags:** @tier1 @hrm-recruitment @labels
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 3 fields render:
      - `filter_status` — type `select`
      - `filter_status_button` — type `submit`
      - `download_all_cv` — type `submit`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_RECRUITMENT-T1-018 — `filter_status` offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list`
- **Tags:** @tier1 @hrm-recruitment @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list`.
  2. Read every option of `filter_status`.
- **Expected:** The options are exactly: "- Select All -" (`-1`), "Rejected" (`rejected`), "Withdrawn" (`withdrawn`), "Declined Offer" (`decline_offer`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_RECRUITMENT-T1-019 — Save `admin.php?page=erp-hr&section=recruitment&sub-section=add_candidate` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=add_candidate`
- **Tags:** @tier1 @hrm-recruitment @crud
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=add_candidate`.
  2. Fill all 1 fields with valid data.
  3. Submit with "Apply".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_RECRUITMENT-T1-020 — `admin.php?page=erp-hr&section=recruitment&sub-section=add_candidate` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=add_candidate`
- **Tags:** @tier1 @hrm-recruitment @labels
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=add_candidate`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `job_id` — type `select`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_RECRUITMENT-T1-021 — Save `edit.php?post_type=erp_hr_questionnaire` with every field completed

- **Surface:** `edit.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier1 @hrm-recruitment @crud
- **Steps:**
  1. Open `edit.php?post_type=erp_hr_questionnaire`.
  2. Fill all 2 fields with valid data.
  3. Submit with "Apply".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_RECRUITMENT-T1-022 — `edit.php?post_type=erp_hr_questionnaire` renders all 2 fields with their labels

- **Surface:** `edit.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier1 @hrm-recruitment @labels
- **Steps:**
  1. Open `edit.php?post_type=erp_hr_questionnaire`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 2 fields render:
      - `mode` ("Compact view") — type `radio`
      - `mode` ("Extended view") — type `radio`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_RECRUITMENT-T1-023 — Save `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`
- **Tags:** @tier1 @hrm-recruitment @crud
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`.
  2. Fill all 8 fields with valid data.
  3. Submit with "Apply".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_RECRUITMENT-T1-024 — `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis` renders all 8 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`
- **Tags:** @tier1 @hrm-recruitment @labels
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 8 fields render:
      - `search-candidate` — type `text`, placeholder "Search candidates..."
      - `filter-job` — type `select`
      - `filter-score` — type `select`
      - `filter-skills` — type `select`
      - `filter-experience-type` — type `select`
      - `filter-experience` — type `select`
      - `select-all-candidates` ("Select All") — type `checkbox`
      - `auto-refresh-toggle` ("Auto-refresh") — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_RECRUITMENT-T1-025 — `filter-score` offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`
- **Tags:** @tier1 @hrm-recruitment @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`.
  2. Read every option of `filter-score`.
- **Expected:** The options are exactly: "All Scores" (``), "90-100 (Excellent)" (`90-100`), "80-89 (Very Good)" (`80-89`), "70-79 (Good)" (`70-79`), "60-69 (Average)" (`60-69`), "0-59 (Below Average)" (`0-59`), "❌ Failed to Score" (`failed`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_RECRUITMENT-T1-026 — `filter-skills` offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`
- **Tags:** @tier1 @hrm-recruitment @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`.
  2. Read every option of `filter-skills`.
- **Expected:** The options are exactly: "All Skills Levels" (``), "Excellent (90-100%)" (`90-100`), "Good (70-89%)" (`70-89`), "Average (50-69%)" (`50-69`), "Poor (0-49%)" (`0-49`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_RECRUITMENT-T1-027 — `filter-experience-type` offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`
- **Tags:** @tier1 @hrm-recruitment @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`.
  2. Read every option of `filter-experience-type`.
- **Expected:** The options are exactly: "Role-Specific Experience" (`relevant`), "Total Experience" (`total`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_RECRUITMENT-T1-028 — `filter-experience` offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`
- **Tags:** @tier1 @hrm-recruitment @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`.
  2. Read every option of `filter-experience`.
- **Expected:** The options are exactly: "All Experience Levels" (``), "Fresh (0-1 years)" (`0-1`), "Junior (1-3 years)" (`1-3`), "Mid-level (3-5 years)" (`3-5`), "Senior (5-8 years)" (`5-8`), "Expert (8+ years)" (`8-999`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_RECRUITMENT-T1-029 — Save `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Tags:** @tier1 @hrm-recruitment @crud
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`.
  2. Fill all 13 fields with valid data (required: `job_title` ("Job Title *")).
  3. Submit with "Save as Job Post".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_RECRUITMENT-T1-030 — `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer` renders all 13 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Tags:** @tier1 @hrm-recruitment @labels
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 13 fields render:
      - `job_title` ("Job Title *") — type `text`, **required**
      - `company_name` ("Company Name") — type `text`
      - `department` ("Department") — type `select`
      - `experience_level` ("Minimum Experience") — type `select`
      - `employment_type` ("Employment Type") — type `select`
      - `location` ("Location") — type `text`, placeholder "e.g., New York, NY or Remote"
      - `salary_range` ("Salary Range") — type `text`, placeholder "e.g., $50,000 - $70,000"
      - `key_responsibilities` ("Key Responsibilities") — type `textarea`, placeholder "Describe the main responsibilities of this role..."
      - `required_skills` ("Required Skills") — type `textarea`, placeholder "List the essential skills and qualifications..."
      - `preferred_skills` ("Preferred Skills (Optional)") — type `textarea`, placeholder "List any preferred or nice-to-have skills..."
      - `education_requirements` ("Education Requirements") — type `textarea`, placeholder "Specify education requirements..."
      - `company_culture` ("Company Culture (Optional)") — type `textarea`, placeholder "Describe your company culture and values..."
      - `benefits` ("Benefits & Perks (Optional)") — type `textarea`, placeholder "List benefits and perks offered..."
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_RECRUITMENT-T1-031 — `department` ("Department") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Tags:** @tier1 @hrm-recruitment @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`.
  2. Read every option of `department` ("Department").
- **Expected:** The options are exactly: "Select Department" (``), "General Management" (`1`), "Operations Department" (`2`), "Finance Department" (`3`), "Sales Department" (`4`), "Human Resource Department" (`5`), "Purchase Department" (`6`), "Engineering Department" (`7`), "Production Department" (`8`), "Procurement Department" (`9`), "pwerp_dept_2vjg9u" (`24`), "pwerp_dept_n40ldm" (`33`), "pwerp_dept_sfcrvj" (`41`), "pwerp_dept_t6il8m" (`59`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_RECRUITMENT-T1-032 — `experience_level` ("Minimum Experience") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Tags:** @tier1 @hrm-recruitment @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`.
  2. Read every option of `experience_level` ("Minimum Experience").
- **Expected:** The options are exactly: "Select Minimum Experience" (``), "Fresher" (`Fresher`), "1 Year" (`1 Year`), "2 Years" (`2 Years`), "3 Years" (`3 Years`), "4 Years" (`4 Years`), "5 Years" (`5 Years`), "6 Years" (`6 Years`), "7 Years" (`7 Years`), "8 Years" (`8 Years`), "9 Years" (`9 Years`), "10 Years" (`10 Years`), "Above 10 Years" (`Above 10 Years`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_RECRUITMENT-T1-033 — `employment_type` ("Employment Type") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Tags:** @tier1 @hrm-recruitment @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`.
  2. Read every option of `employment_type` ("Employment Type").
- **Expected:** The options are exactly: "Select Employment Type" (``), "Full Time" (`permanent`), "Part Time" (`parttime`), "On Contract" (`contract`), "Temporary" (`temporary`), "Trainee" (`trainee`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_RECRUITMENT-T1-034 — Save `post-new.php?post_type=erp_hr_questionnaire` with every field completed

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier1 @hrm-recruitment @crud
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. Fill all 18 fields with valid data.
  3. Submit with "Save".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_RECRUITMENT-T1-035 — `post-new.php?post_type=erp_hr_questionnaire` renders all 18 fields with their labels

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier1 @hrm-recruitment @labels
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 18 fields render:
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
      - `mm` ("Month 01-Jan 02-Feb 03-Mar 04-Apr 05-May 06-Jun 07-Jul 08-Aug 09-Sep 10-Oct 11-Nov 12-Dec") — type `select`
      - `jj` ("Day") — type `text`
      - `aa` ("Year") — type `text`
      - `hh` ("Hour") — type `text`
      - `mn` ("Minute") — type `text`
      - `publish` — type `submit`
      - `post_name` ("Slug") — type `text`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_RECRUITMENT-T1-036 — `post_status` ("Set status") offers its full option set

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier1 @hrm-recruitment @options
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. Read every option of `post_status` ("Set status").
- **Expected:** The options are exactly: "Pending Review" (`pending`), "Draft" (`draft`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_RECRUITMENT-T1-037 — `mm` ("Month 01-Jan 02-Feb 03-Mar 04-Apr 05-May 06-Jun 07-Jul 08-Aug 09-Sep 10-Oct 11-Nov 12-Dec") offers its full option set

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier1 @hrm-recruitment @options
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. Read every option of `mm` ("Month 01-Jan 02-Feb 03-Mar 04-Apr 05-May 06-Jun 07-Jul 08-Aug 09-Sep 10-Oct 11-Nov 12-Dec").
- **Expected:** The options are exactly: "01-Jan" (`01`), "02-Feb" (`02`), "03-Mar" (`03`), "04-Apr" (`04`), "05-May" (`05`), "06-Jun" (`06`), "07-Jul" (`07`), "08-Aug" (`08`), "09-Sep" (`09`), "10-Oct" (`10`), "11-Nov" (`11`), "12-Dec" (`12`).
- **Oracle:** UI — `<option>` label/value pairs.

#### HRM_RECRUITMENT-T1-038 — Save `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier1 @hrm-recruitment @crud
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Fill all 17 fields with valid data (required: `weight_skills_match` ("Skills"), `weight_experience_match` ("Experience"), `weight_education_fit` ("Education"), `weight_certification` ("Certifications"), `weight_soft_skills` ("Soft Skills")).
  3. Submit with "Save".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_RECRUITMENT-T1-039 — `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings` renders all 17 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier1 @hrm-recruitment @labels
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 17 fields render:
      - `ai-job-search` — type `search`, placeholder "Search jobs..."
      - `ai-processing-toggle` — type `checkbox`
      - `category_detected` ("Category Detected") — type `text`, placeholder "Product category automatically detected from the job posting. Edit if needed."
      - `core_skills_detected` ("Core Skills Detected") — type `textarea`, placeholder "List the key skills candidates should have. Add, remove, or edit as needed. Use comma-separated values."
      - `secondary_skills_detected` ("Secondary Skills Detected") — type `textarea`, placeholder "Optional additional skills that may be relevant for this role."
      - `tools_detected` ("Tool & Platform Use Detected") — type `textarea`, placeholder "Specify tools or platforms candidates should be familiar with (e.g., Jira, Mixpanel)."
      - `minimum_experience_display` ("Minimum Experience") — type `text`
      - `experience_type_display` ("Experience Type") — type `text`
      - `experience_preference` ("Preferred (Partial credit for related experiences)") — type `radio`
      - `experience_preference` ("Required (Strict: only exact match gets points)") — type `radio`
      - `education_expectation` — type `text`, placeholder "Enter the minimum education required for this role (e.g., Bachelor's in CS, Business)."
      - `certification` — type `textarea`, placeholder "List any certifications that are expected or preferred for candidates."
      - `weight_skills_match` ("Skills") — type `number`, **required**
      - `weight_experience_match` ("Experience") — type `number`, **required**
      - `weight_education_fit` ("Education") — type `number`, **required**
      - `weight_certification` ("Certifications") — type `number`, **required**
      - `weight_soft_skills` ("Soft Skills") — type `number`, **required**
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_RECRUITMENT-T1-040 — Save `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings` with every field completed

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings`
- **Tags:** @tier1 @hrm-recruitment @crud
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings`.
  2. Fill all 6 fields with valid data.
  3. Submit with "Save Settings".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### HRM_RECRUITMENT-T1-041 — `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings` renders all 6 fields with their labels

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings`
- **Tags:** @tier1 @hrm-recruitment @labels
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 6 fields render:
      - `erp_rec_gemini_api_key` ("API Key") — type `password`
      - `erp_rec_anthropic_api_key` ("API Key") — type `password`
      - `erp_rec_openai_api_key` ("API Key") — type `password`
      - `erp_rec_openrouter_api_key` ("API Key") — type `password`
      - `submit` ("API Key") — type `submit`
      - `ai-model-selector` ("Select AI Model") — type `select`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### HRM_RECRUITMENT-T1-042 — `ai-model-selector` ("Select AI Model") offers its full option set

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings`
- **Tags:** @tier1 @hrm-recruitment @options
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings`.
  2. Read every option of `ai-model-selector` ("Select AI Model").
- **Expected:** The options are exactly: "Gemini 1.5 Flash" (`gemini-1.5-flash-latest`), "Gemini 1.5 Pro" (`gemini-1.5-pro-latest`), "Gemini 2.0 Flash (Experimental)" (`gemini-2.0-flash-exp`).
- **Oracle:** UI — `<option>` label/value pairs.
