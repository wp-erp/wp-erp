# Tier 2 — hrm-recruitment

Edge cases — boundaries, optional-field omission, empty states, unusual but legal input.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**85 cases** (85 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### HRM_RECRUITMENT-T2-001 — `opening_title` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`.
  2. Save with `opening_title` set to a single character.
  3. Save with `opening_title` set to a 255-character value.
  4. Save with `opening_title` set to a value with leading and trailing whitespace.
  5. Save with `opening_title` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-002 — `opening_description` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`.
  2. Save with `opening_description` set to a 5,000-character body.
  3. Save with `opening_description` set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-003 — `create_opening` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`.
  2. Save with `create_opening` set to a single character.
  3. Save with `create_opening` set to a 255-character value.
  4. Save with `create_opening` set to a value with leading and trailing whitespace.
  5. Save with `create_opening` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-004 — `wp-link-url` ("URL") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`.
  2. Save with `wp-link-url` ("URL") set to a single character.
  3. Save with `wp-link-url` ("URL") set to a 255-character value.
  4. Save with `wp-link-url` ("URL") set to a value with leading and trailing whitespace.
  5. Save with `wp-link-url` ("URL") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-005 — `wp-link-text` ("Link Text") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`.
  2. Save with `wp-link-text` ("Link Text") set to a single character.
  3. Save with `wp-link-text` ("Link Text") set to a 255-character value.
  4. Save with `wp-link-text` ("Link Text") set to a value with leading and trailing whitespace.
  5. Save with `wp-link-text` ("Link Text") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-006 — `wp-link-target` ("Open link in a new tab") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`.
  2. Save with `wp-link-target` ("Open link in a new tab") set to checked.
  3. Save with `wp-link-target` ("Open link in a new tab") set to unchecked.
  4. Save with `wp-link-target` ("Open link in a new tab") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-007 — `wp-link-search` ("Search") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`.
  2. Save with `wp-link-search` ("Search") set to a single character.
  3. Save with `wp-link-search` ("Search") set to a 255-character value.
  4. Save with `wp-link-search` ("Search") set to a value with leading and trailing whitespace.
  5. Save with `wp-link-search` ("Search") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-008 — `wp-link-submit` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`.
  2. Save with `wp-link-submit` set to a single character.
  3. Save with `wp-link-submit` set to a 255-character value.
  4. Save with `wp-link-submit` set to a value with leading and trailing whitespace.
  5. Save with `wp-link-submit` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-009 — `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 8 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_RECRUITMENT-T2-010 — `filter_status` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list`.
  2. Save with `filter_status` set to the first real option.
  3. Save with `filter_status` set to the last option.
  4. Save with `filter_status` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-011 — `filter_status_button` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list`.
  2. Save with `filter_status_button` set to a single character.
  3. Save with `filter_status_button` set to a 255-character value.
  4. Save with `filter_status_button` set to a value with leading and trailing whitespace.
  5. Save with `filter_status_button` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-012 — `download_all_cv` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list`.
  2. Save with `download_all_cv` set to a single character.
  3. Save with `download_all_cv` set to a 255-character value.
  4. Save with `download_all_cv` set to a value with leading and trailing whitespace.
  5. Save with `download_all_cv` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-013 — `admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 3 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_RECRUITMENT-T2-014 — `job_id` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=add_candidate`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=add_candidate`.
  2. Save with `job_id` set to the first real option.
  3. Save with `job_id` set to the last option.
  4. Save with `job_id` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-015 — `admin.php?page=erp-hr&section=recruitment&sub-section=add_candidate` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=add_candidate`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=add_candidate`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_RECRUITMENT-T2-016 — `mode` ("Compact view") accepts its edge values

- **Surface:** `edit.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `edit.php?post_type=erp_hr_questionnaire`.
  2. Save with `mode` ("Compact view") set to a single character.
  3. Save with `mode` ("Compact view") set to a 255-character value.
  4. Save with `mode` ("Compact view") set to a value with leading and trailing whitespace.
  5. Save with `mode` ("Compact view") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-017 — `mode` ("Extended view") accepts its edge values

- **Surface:** `edit.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `edit.php?post_type=erp_hr_questionnaire`.
  2. Save with `mode` ("Extended view") set to a single character.
  3. Save with `mode` ("Extended view") set to a 255-character value.
  4. Save with `mode` ("Extended view") set to a value with leading and trailing whitespace.
  5. Save with `mode` ("Extended view") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-018 — `edit.php?post_type=erp_hr_questionnaire` saves with only its required fields

- **Surface:** `edit.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `edit.php?post_type=erp_hr_questionnaire`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_RECRUITMENT-T2-019 — `search-candidate` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`.
  2. Save with `search-candidate` set to today.
  3. Save with `search-candidate` set to a leap day (29 Feb).
  4. Save with `search-candidate` set to a date before the company financial-year start.
  5. Save with `search-candidate` set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-020 — `filter-job` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`.
  2. Save with `filter-job` set to the first real option.
  3. Save with `filter-job` set to the last option.
  4. Save with `filter-job` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-021 — `filter-score` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`.
  2. Save with `filter-score` set to the first real option.
  3. Save with `filter-score` set to the last option.
  4. Save with `filter-score` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-022 — `filter-skills` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`.
  2. Save with `filter-skills` set to the first real option.
  3. Save with `filter-skills` set to the last option.
  4. Save with `filter-skills` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-023 — `filter-experience-type` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`.
  2. Save with `filter-experience-type` set to the first real option.
  3. Save with `filter-experience-type` set to the last option.
  4. Save with `filter-experience-type` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-024 — `filter-experience` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`.
  2. Save with `filter-experience` set to the first real option.
  3. Save with `filter-experience` set to the last option.
  4. Save with `filter-experience` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-025 — `select-all-candidates` ("Select All") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`.
  2. Save with `select-all-candidates` ("Select All") set to today.
  3. Save with `select-all-candidates` ("Select All") set to a leap day (29 Feb).
  4. Save with `select-all-candidates` ("Select All") set to a date before the company financial-year start.
  5. Save with `select-all-candidates` ("Select All") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-026 — `auto-refresh-toggle` ("Auto-refresh") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`.
  2. Save with `auto-refresh-toggle` ("Auto-refresh") set to checked.
  3. Save with `auto-refresh-toggle` ("Auto-refresh") set to unchecked.
  4. Save with `auto-refresh-toggle` ("Auto-refresh") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-027 — `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 8 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_RECRUITMENT-T2-028 — `job_title` ("Job Title *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`.
  2. Save with `job_title` ("Job Title *") set to a single character.
  3. Save with `job_title` ("Job Title *") set to a 255-character value.
  4. Save with `job_title` ("Job Title *") set to a value with leading and trailing whitespace.
  5. Save with `job_title` ("Job Title *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-029 — `company_name` ("Company Name") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`.
  2. Save with `company_name` ("Company Name") set to a single character.
  3. Save with `company_name` ("Company Name") set to a 255-character value.
  4. Save with `company_name` ("Company Name") set to a value with leading and trailing whitespace.
  5. Save with `company_name` ("Company Name") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-030 — `department` ("Department") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`.
  2. Save with `department` ("Department") set to the first real option.
  3. Save with `department` ("Department") set to the last option.
  4. Save with `department` ("Department") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-031 — `experience_level` ("Minimum Experience") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`.
  2. Save with `experience_level` ("Minimum Experience") set to the first real option.
  3. Save with `experience_level` ("Minimum Experience") set to the last option.
  4. Save with `experience_level` ("Minimum Experience") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-032 — `employment_type` ("Employment Type") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`.
  2. Save with `employment_type` ("Employment Type") set to the first real option.
  3. Save with `employment_type` ("Employment Type") set to the last option.
  4. Save with `employment_type` ("Employment Type") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-033 — `location` ("Location") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`.
  2. Save with `location` ("Location") set to a single character.
  3. Save with `location` ("Location") set to a 255-character value.
  4. Save with `location` ("Location") set to a value with leading and trailing whitespace.
  5. Save with `location` ("Location") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-034 — `salary_range` ("Salary Range") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`.
  2. Save with `salary_range` ("Salary Range") set to a single character.
  3. Save with `salary_range` ("Salary Range") set to a 255-character value.
  4. Save with `salary_range` ("Salary Range") set to a value with leading and trailing whitespace.
  5. Save with `salary_range` ("Salary Range") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-035 — `key_responsibilities` ("Key Responsibilities") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`.
  2. Save with `key_responsibilities` ("Key Responsibilities") set to a 5,000-character body.
  3. Save with `key_responsibilities` ("Key Responsibilities") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-036 — `required_skills` ("Required Skills") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`.
  2. Save with `required_skills` ("Required Skills") set to a 5,000-character body.
  3. Save with `required_skills` ("Required Skills") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-037 — `preferred_skills` ("Preferred Skills (Optional)") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`.
  2. Save with `preferred_skills` ("Preferred Skills (Optional)") set to a 5,000-character body.
  3. Save with `preferred_skills` ("Preferred Skills (Optional)") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-038 — `education_requirements` ("Education Requirements") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`.
  2. Save with `education_requirements` ("Education Requirements") set to a 5,000-character body.
  3. Save with `education_requirements` ("Education Requirements") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-039 — `company_culture` ("Company Culture (Optional)") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`.
  2. Save with `company_culture` ("Company Culture (Optional)") set to a 5,000-character body.
  3. Save with `company_culture` ("Company Culture (Optional)") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-040 — `benefits` ("Benefits & Perks (Optional)") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`.
  2. Save with `benefits` ("Benefits & Perks (Optional)") set to a 5,000-character body.
  3. Save with `benefits` ("Benefits & Perks (Optional)") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-041 — `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`.
  2. Fill only: `job_title` ("Job Title *").
  3. Submit.
- **Expected:** The record saves. The 12 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_RECRUITMENT-T2-042 — `screen_columns` ("1 column") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. Save with `screen_columns` ("1 column") set to a single character.
  3. Save with `screen_columns` ("1 column") set to a 255-character value.
  4. Save with `screen_columns` ("1 column") set to a value with leading and trailing whitespace.
  5. Save with `screen_columns` ("1 column") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-043 — `screen_columns` ("2 columns") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. Save with `screen_columns` ("2 columns") set to a single character.
  3. Save with `screen_columns` ("2 columns") set to a 255-character value.
  4. Save with `screen_columns` ("2 columns") set to a value with leading and trailing whitespace.
  5. Save with `screen_columns` ("2 columns") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-044 — `editor-expand-toggle` ("Enable full-height editor and distraction-free functionality.") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. Save with `editor-expand-toggle` ("Enable full-height editor and distraction-free functionality.") set to checked.
  3. Save with `editor-expand-toggle` ("Enable full-height editor and distraction-free functionality.") set to unchecked.
  4. Save with `editor-expand-toggle` ("Enable full-height editor and distraction-free functionality.") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-045 — `post_title` ("Add title") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. Save with `post_title` ("Add title") set to a single character.
  3. Save with `post_title` ("Add title") set to a 255-character value.
  4. Save with `post_title` ("Add title") set to a value with leading and trailing whitespace.
  5. Save with `post_title` ("Add title") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-046 — `save` accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. Save with `save` set to a single character.
  3. Save with `save` set to a 255-character value.
  4. Save with `save` set to a value with leading and trailing whitespace.
  5. Save with `save` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-047 — `save` accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. Save with `save` set to a single character.
  3. Save with `save` set to a 255-character value.
  4. Save with `save` set to a value with leading and trailing whitespace.
  5. Save with `save` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-048 — `post_status` ("Set status") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. Save with `post_status` ("Set status") set to the first real option.
  3. Save with `post_status` ("Set status") set to the last option.
  4. Save with `post_status` ("Set status") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-049 — `visibility` ("Public") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. Save with `visibility` ("Public") set to a single character.
  3. Save with `visibility` ("Public") set to a 255-character value.
  4. Save with `visibility` ("Public") set to a value with leading and trailing whitespace.
  5. Save with `visibility` ("Public") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-050 — `visibility` ("Password protected") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. Save with `visibility` ("Password protected") set to a single character.
  3. Save with `visibility` ("Password protected") set to a 255-character value.
  4. Save with `visibility` ("Password protected") set to a value with leading and trailing whitespace.
  5. Save with `visibility` ("Password protected") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-051 — `post_password` ("Password:") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. Save with `post_password` ("Password:") set to a single character.
  3. Save with `post_password` ("Password:") set to a 255-character value.
  4. Save with `post_password` ("Password:") set to a value with leading and trailing whitespace.
  5. Save with `post_password` ("Password:") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-052 — `visibility` ("Private") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. Save with `visibility` ("Private") set to a single character.
  3. Save with `visibility` ("Private") set to a 255-character value.
  4. Save with `visibility` ("Private") set to a value with leading and trailing whitespace.
  5. Save with `visibility` ("Private") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-053 — `mm` ("Month 01-Jan 02-Feb 03-Mar 04-Apr 05-May 06-Jun 07-Jul 08-Aug 09-Sep 10-Oct 11-Nov 12-Dec") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. Save with `mm` ("Month 01-Jan 02-Feb 03-Mar 04-Apr 05-May 06-Jun 07-Jul 08-Aug 09-Sep 10-Oct 11-Nov 12-Dec") set to the first real option.
  3. Save with `mm` ("Month 01-Jan 02-Feb 03-Mar 04-Apr 05-May 06-Jun 07-Jul 08-Aug 09-Sep 10-Oct 11-Nov 12-Dec") set to the last option.
  4. Save with `mm` ("Month 01-Jan 02-Feb 03-Mar 04-Apr 05-May 06-Jun 07-Jul 08-Aug 09-Sep 10-Oct 11-Nov 12-Dec") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-054 — `jj` ("Day") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. Save with `jj` ("Day") set to a single character.
  3. Save with `jj` ("Day") set to a 255-character value.
  4. Save with `jj` ("Day") set to a value with leading and trailing whitespace.
  5. Save with `jj` ("Day") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-055 — `aa` ("Year") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. Save with `aa` ("Year") set to a single character.
  3. Save with `aa` ("Year") set to a 255-character value.
  4. Save with `aa` ("Year") set to a value with leading and trailing whitespace.
  5. Save with `aa` ("Year") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-056 — `hh` ("Hour") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. Save with `hh` ("Hour") set to a single character.
  3. Save with `hh` ("Hour") set to a 255-character value.
  4. Save with `hh` ("Hour") set to a value with leading and trailing whitespace.
  5. Save with `hh` ("Hour") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-057 — `mn` ("Minute") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. Save with `mn` ("Minute") set to a single character.
  3. Save with `mn` ("Minute") set to a 255-character value.
  4. Save with `mn` ("Minute") set to a value with leading and trailing whitespace.
  5. Save with `mn` ("Minute") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-058 — `publish` accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. Save with `publish` set to a single character.
  3. Save with `publish` set to a 255-character value.
  4. Save with `publish` set to a value with leading and trailing whitespace.
  5. Save with `publish` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-059 — `post_name` ("Slug") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. Save with `post_name` ("Slug") set to a single character.
  3. Save with `post_name` ("Slug") set to a 255-character value.
  4. Save with `post_name` ("Slug") set to a value with leading and trailing whitespace.
  5. Save with `post_name` ("Slug") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-060 — `post-new.php?post_type=erp_hr_questionnaire` saves with only its required fields

- **Surface:** `post-new.php?post_type=erp_hr_questionnaire`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_questionnaire`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 18 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_RECRUITMENT-T2-061 — `ai-job-search` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Save with `ai-job-search` set to a single character.
  3. Save with `ai-job-search` set to a 255-character value.
  4. Save with `ai-job-search` set to a value with leading and trailing whitespace.
  5. Save with `ai-job-search` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-062 — `ai-processing-toggle` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Save with `ai-processing-toggle` set to checked.
  3. Save with `ai-processing-toggle` set to unchecked.
  4. Save with `ai-processing-toggle` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-063 — `category_detected` ("Category Detected") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Save with `category_detected` ("Category Detected") set to a single character.
  3. Save with `category_detected` ("Category Detected") set to a 255-character value.
  4. Save with `category_detected` ("Category Detected") set to a value with leading and trailing whitespace.
  5. Save with `category_detected` ("Category Detected") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-064 — `core_skills_detected` ("Core Skills Detected") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Save with `core_skills_detected` ("Core Skills Detected") set to a 5,000-character body.
  3. Save with `core_skills_detected` ("Core Skills Detected") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-065 — `secondary_skills_detected` ("Secondary Skills Detected") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Save with `secondary_skills_detected` ("Secondary Skills Detected") set to a 5,000-character body.
  3. Save with `secondary_skills_detected` ("Secondary Skills Detected") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-066 — `tools_detected` ("Tool & Platform Use Detected") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Save with `tools_detected` ("Tool & Platform Use Detected") set to a 5,000-character body.
  3. Save with `tools_detected` ("Tool & Platform Use Detected") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-067 — `minimum_experience_display` ("Minimum Experience") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Save with `minimum_experience_display` ("Minimum Experience") set to a single character.
  3. Save with `minimum_experience_display` ("Minimum Experience") set to a 255-character value.
  4. Save with `minimum_experience_display` ("Minimum Experience") set to a value with leading and trailing whitespace.
  5. Save with `minimum_experience_display` ("Minimum Experience") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-068 — `experience_type_display` ("Experience Type") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Save with `experience_type_display` ("Experience Type") set to a single character.
  3. Save with `experience_type_display` ("Experience Type") set to a 255-character value.
  4. Save with `experience_type_display` ("Experience Type") set to a value with leading and trailing whitespace.
  5. Save with `experience_type_display` ("Experience Type") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-069 — `experience_preference` ("Preferred (Partial credit for related experiences)") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Save with `experience_preference` ("Preferred (Partial credit for related experiences)") set to a single character.
  3. Save with `experience_preference` ("Preferred (Partial credit for related experiences)") set to a 255-character value.
  4. Save with `experience_preference` ("Preferred (Partial credit for related experiences)") set to a value with leading and trailing whitespace.
  5. Save with `experience_preference` ("Preferred (Partial credit for related experiences)") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-070 — `experience_preference` ("Required (Strict: only exact match gets points)") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Save with `experience_preference` ("Required (Strict: only exact match gets points)") set to a single character.
  3. Save with `experience_preference` ("Required (Strict: only exact match gets points)") set to a 255-character value.
  4. Save with `experience_preference` ("Required (Strict: only exact match gets points)") set to a value with leading and trailing whitespace.
  5. Save with `experience_preference` ("Required (Strict: only exact match gets points)") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-071 — `education_expectation` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Save with `education_expectation` set to a single character.
  3. Save with `education_expectation` set to a 255-character value.
  4. Save with `education_expectation` set to a value with leading and trailing whitespace.
  5. Save with `education_expectation` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-072 — `certification` accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Save with `certification` set to a 5,000-character body.
  3. Save with `certification` set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-073 — `weight_skills_match` ("Skills") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Save with `weight_skills_match` ("Skills") set to 0.
  3. Save with `weight_skills_match` ("Skills") set to a negative value.
  4. Save with `weight_skills_match` ("Skills") set to a decimal where an integer is expected.
  5. Save with `weight_skills_match` ("Skills") set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-074 — `weight_experience_match` ("Experience") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Save with `weight_experience_match` ("Experience") set to 0.
  3. Save with `weight_experience_match` ("Experience") set to a negative value.
  4. Save with `weight_experience_match` ("Experience") set to a decimal where an integer is expected.
  5. Save with `weight_experience_match` ("Experience") set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-075 — `weight_education_fit` ("Education") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Save with `weight_education_fit` ("Education") set to 0.
  3. Save with `weight_education_fit` ("Education") set to a negative value.
  4. Save with `weight_education_fit` ("Education") set to a decimal where an integer is expected.
  5. Save with `weight_education_fit` ("Education") set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-076 — `weight_certification` ("Certifications") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Save with `weight_certification` ("Certifications") set to 0.
  3. Save with `weight_certification` ("Certifications") set to a negative value.
  4. Save with `weight_certification` ("Certifications") set to a decimal where an integer is expected.
  5. Save with `weight_certification` ("Certifications") set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-077 — `weight_soft_skills` ("Soft Skills") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Save with `weight_soft_skills` ("Soft Skills") set to 0.
  3. Save with `weight_soft_skills` ("Soft Skills") set to a negative value.
  4. Save with `weight_soft_skills` ("Soft Skills") set to a decimal where an integer is expected.
  5. Save with `weight_soft_skills` ("Soft Skills") set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-078 — `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`.
  2. Fill only: `weight_skills_match` ("Skills"), `weight_experience_match` ("Experience"), `weight_education_fit` ("Education"), `weight_certification` ("Certifications"), `weight_soft_skills` ("Soft Skills").
  3. Submit.
- **Expected:** The record saves. The 12 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_RECRUITMENT-T2-079 — `erp_rec_gemini_api_key` ("API Key") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings`.
  2. Save with `erp_rec_gemini_api_key` ("API Key") set to a single character.
  3. Save with `erp_rec_gemini_api_key` ("API Key") set to a 255-character value.
  4. Save with `erp_rec_gemini_api_key` ("API Key") set to a value with leading and trailing whitespace.
  5. Save with `erp_rec_gemini_api_key` ("API Key") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-080 — `erp_rec_anthropic_api_key` ("API Key") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings`.
  2. Save with `erp_rec_anthropic_api_key` ("API Key") set to a single character.
  3. Save with `erp_rec_anthropic_api_key` ("API Key") set to a 255-character value.
  4. Save with `erp_rec_anthropic_api_key` ("API Key") set to a value with leading and trailing whitespace.
  5. Save with `erp_rec_anthropic_api_key` ("API Key") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-081 — `erp_rec_openai_api_key` ("API Key") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings`.
  2. Save with `erp_rec_openai_api_key` ("API Key") set to a single character.
  3. Save with `erp_rec_openai_api_key` ("API Key") set to a 255-character value.
  4. Save with `erp_rec_openai_api_key` ("API Key") set to a value with leading and trailing whitespace.
  5. Save with `erp_rec_openai_api_key` ("API Key") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-082 — `erp_rec_openrouter_api_key` ("API Key") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings`.
  2. Save with `erp_rec_openrouter_api_key` ("API Key") set to a single character.
  3. Save with `erp_rec_openrouter_api_key` ("API Key") set to a 255-character value.
  4. Save with `erp_rec_openrouter_api_key` ("API Key") set to a value with leading and trailing whitespace.
  5. Save with `erp_rec_openrouter_api_key` ("API Key") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-083 — `submit` ("API Key") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings`.
  2. Save with `submit` ("API Key") set to a single character.
  3. Save with `submit` ("API Key") set to a 255-character value.
  4. Save with `submit` ("API Key") set to a value with leading and trailing whitespace.
  5. Save with `submit` ("API Key") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-084 — `ai-model-selector` ("Select AI Model") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings`.
  2. Save with `ai-model-selector` ("Select AI Model") set to the first real option.
  3. Save with `ai-model-selector` ("Select AI Model") set to the last option.
  4. Save with `ai-model-selector` ("Select AI Model") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_RECRUITMENT-T2-085 — `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings`
- **Tags:** @tier2 @hrm-recruitment @edge
- **Steps:**
  1. Open `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 6 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.
