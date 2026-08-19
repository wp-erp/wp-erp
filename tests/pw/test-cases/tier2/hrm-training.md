# Tier 2 — hrm-training

Edge cases — boundaries, optional-field omission, empty states, unusual but legal input.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**40 cases** (40 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### HRM_TRAINING-T2-001 — `training-id` ("Training *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open modal form `tmpl-employee-assign-new-training`.
  2. Save with `training-id` ("Training *") set to the first real option.
  3. Save with `training-id` ("Training *") set to the last option.
  4. Save with `training-id` ("Training *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-002 — `training-completed-date` ("Completed Date *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open modal form `tmpl-employee-assign-new-training`.
  2. Save with `training-completed-date` ("Completed Date *") set to today.
  3. Save with `training-completed-date` ("Completed Date *") set to a leap day (29 Feb).
  4. Save with `training-completed-date` ("Completed Date *") set to a date before the company financial-year start.
  5. Save with `training-completed-date` ("Completed Date *") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-003 — `training-trainer` ("Trainer's name *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open modal form `tmpl-employee-assign-new-training`.
  2. Save with `training-trainer` ("Trainer's name *") set to a single character.
  3. Save with `training-trainer` ("Trainer's name *") set to a 255-character value.
  4. Save with `training-trainer` ("Trainer's name *") set to a value with leading and trailing whitespace.
  5. Save with `training-trainer` ("Trainer's name *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-004 — `trainer-phone` ("Trainer' Phone No.") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open modal form `tmpl-employee-assign-new-training`.
  2. Save with `trainer-phone` ("Trainer' Phone No.") set to a single character.
  3. Save with `trainer-phone` ("Trainer' Phone No.") set to a 255-character value.
  4. Save with `trainer-phone` ("Trainer' Phone No.") set to a value with leading and trailing whitespace.
  5. Save with `trainer-phone` ("Trainer' Phone No.") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-005 — `training-cost` ("Cost") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open modal form `tmpl-employee-assign-new-training`.
  2. Save with `training-cost` ("Cost") set to a single character.
  3. Save with `training-cost` ("Cost") set to a 255-character value.
  4. Save with `training-cost` ("Cost") set to a value with leading and trailing whitespace.
  5. Save with `training-cost` ("Cost") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-006 — `training-credit` ("Credit") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open modal form `tmpl-employee-assign-new-training`.
  2. Save with `training-credit` ("Credit") set to a single character.
  3. Save with `training-credit` ("Credit") set to a 255-character value.
  4. Save with `training-credit` ("Credit") set to a value with leading and trailing whitespace.
  5. Save with `training-credit` ("Credit") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-007 — `training-hours` ("Hours") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open modal form `tmpl-employee-assign-new-training`.
  2. Save with `training-hours` ("Hours") set to a single character.
  3. Save with `training-hours` ("Hours") set to a 255-character value.
  4. Save with `training-hours` ("Hours") set to a value with leading and trailing whitespace.
  5. Save with `training-hours` ("Hours") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-008 — `training-notes` ("Notes") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open modal form `tmpl-employee-assign-new-training`.
  2. Save with `training-notes` ("Notes") set to a 5,000-character body.
  3. Save with `training-notes` ("Notes") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-009 — `training-rate` ("Rating *") accepts its edge values

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open modal form `tmpl-employee-assign-new-training`.
  2. Save with `training-rate` ("Rating *") set to 0.
  3. Save with `training-rate` ("Rating *") set to a negative value.
  4. Save with `training-rate` ("Rating *") set to a decimal where an integer is expected.
  5. Save with `training-rate` ("Rating *") set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-010 — modal form `tmpl-employee-assign-new-training` saves with only its required fields

- **Surface:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open modal form `tmpl-employee-assign-new-training`.
  2. Fill only: `training-id` ("Training *"), `training-completed-date` ("Completed Date *"), `training-trainer` ("Trainer's name *"), `training-rate` ("Rating *").
  3. Submit.
- **Expected:** The record saves. The 5 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_TRAINING-T2-011 — `mode` ("Compact view") accepts its edge values

- **Surface:** `edit.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `edit.php?post_type=erp_hr_training`.
  2. Save with `mode` ("Compact view") set to a single character.
  3. Save with `mode` ("Compact view") set to a 255-character value.
  4. Save with `mode` ("Compact view") set to a value with leading and trailing whitespace.
  5. Save with `mode` ("Compact view") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-012 — `mode` ("Extended view") accepts its edge values

- **Surface:** `edit.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `edit.php?post_type=erp_hr_training`.
  2. Save with `mode` ("Extended view") set to a single character.
  3. Save with `mode` ("Extended view") set to a 255-character value.
  4. Save with `mode` ("Extended view") set to a value with leading and trailing whitespace.
  5. Save with `mode` ("Extended view") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-013 — `edit.php?post_type=erp_hr_training` saves with only its required fields

- **Surface:** `edit.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `edit.php?post_type=erp_hr_training`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### HRM_TRAINING-T2-014 — `screen_columns` ("1 column") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `screen_columns` ("1 column") set to a single character.
  3. Save with `screen_columns` ("1 column") set to a 255-character value.
  4. Save with `screen_columns` ("1 column") set to a value with leading and trailing whitespace.
  5. Save with `screen_columns` ("1 column") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-015 — `screen_columns` ("2 columns") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `screen_columns` ("2 columns") set to a single character.
  3. Save with `screen_columns` ("2 columns") set to a 255-character value.
  4. Save with `screen_columns` ("2 columns") set to a value with leading and trailing whitespace.
  5. Save with `screen_columns` ("2 columns") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-016 — `editor-expand-toggle` ("Enable full-height editor and distraction-free functionality.") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `editor-expand-toggle` ("Enable full-height editor and distraction-free functionality.") set to checked.
  3. Save with `editor-expand-toggle` ("Enable full-height editor and distraction-free functionality.") set to unchecked.
  4. Save with `editor-expand-toggle` ("Enable full-height editor and distraction-free functionality.") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-017 — `post_title` ("Add title") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `post_title` ("Add title") set to a single character.
  3. Save with `post_title` ("Add title") set to a 255-character value.
  4. Save with `post_title` ("Add title") set to a value with leading and trailing whitespace.
  5. Save with `post_title` ("Add title") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-018 — `save` accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `save` set to a single character.
  3. Save with `save` set to a 255-character value.
  4. Save with `save` set to a value with leading and trailing whitespace.
  5. Save with `save` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-019 — `save` accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `save` set to a single character.
  3. Save with `save` set to a 255-character value.
  4. Save with `save` set to a value with leading and trailing whitespace.
  5. Save with `save` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-020 — `post_status` ("Set status") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `post_status` ("Set status") set to the first real option.
  3. Save with `post_status` ("Set status") set to the last option.
  4. Save with `post_status` ("Set status") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-021 — `visibility` ("Public") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `visibility` ("Public") set to a single character.
  3. Save with `visibility` ("Public") set to a 255-character value.
  4. Save with `visibility` ("Public") set to a value with leading and trailing whitespace.
  5. Save with `visibility` ("Public") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-022 — `visibility` ("Password protected") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `visibility` ("Password protected") set to a single character.
  3. Save with `visibility` ("Password protected") set to a 255-character value.
  4. Save with `visibility` ("Password protected") set to a value with leading and trailing whitespace.
  5. Save with `visibility` ("Password protected") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-023 — `post_password` ("Password:") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `post_password` ("Password:") set to a single character.
  3. Save with `post_password` ("Password:") set to a 255-character value.
  4. Save with `post_password` ("Password:") set to a value with leading and trailing whitespace.
  5. Save with `post_password` ("Password:") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-024 — `visibility` ("Private") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `visibility` ("Private") set to a single character.
  3. Save with `visibility` ("Private") set to a 255-character value.
  4. Save with `visibility` ("Private") set to a value with leading and trailing whitespace.
  5. Save with `visibility` ("Private") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-025 — `mm` ("Month 01-Jan 02-Feb 03-Mar 04-Apr 05-May 06-Jun 07-Jul 08-Aug 09-Sep 10-Oct 11-Nov 12-Dec") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `mm` ("Month 01-Jan 02-Feb 03-Mar 04-Apr 05-May 06-Jun 07-Jul 08-Aug 09-Sep 10-Oct 11-Nov 12-Dec") set to the first real option.
  3. Save with `mm` ("Month 01-Jan 02-Feb 03-Mar 04-Apr 05-May 06-Jun 07-Jul 08-Aug 09-Sep 10-Oct 11-Nov 12-Dec") set to the last option.
  4. Save with `mm` ("Month 01-Jan 02-Feb 03-Mar 04-Apr 05-May 06-Jun 07-Jul 08-Aug 09-Sep 10-Oct 11-Nov 12-Dec") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-026 — `jj` ("Day") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `jj` ("Day") set to a single character.
  3. Save with `jj` ("Day") set to a 255-character value.
  4. Save with `jj` ("Day") set to a value with leading and trailing whitespace.
  5. Save with `jj` ("Day") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-027 — `aa` ("Year") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `aa` ("Year") set to a single character.
  3. Save with `aa` ("Year") set to a 255-character value.
  4. Save with `aa` ("Year") set to a value with leading and trailing whitespace.
  5. Save with `aa` ("Year") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-028 — `hh` ("Hour") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `hh` ("Hour") set to a single character.
  3. Save with `hh` ("Hour") set to a 255-character value.
  4. Save with `hh` ("Hour") set to a value with leading and trailing whitespace.
  5. Save with `hh` ("Hour") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-029 — `mn` ("Minute") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `mn` ("Minute") set to a single character.
  3. Save with `mn` ("Minute") set to a 255-character value.
  4. Save with `mn` ("Minute") set to a value with leading and trailing whitespace.
  5. Save with `mn` ("Minute") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-030 — `publish` accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `publish` set to a single character.
  3. Save with `publish` set to a 255-character value.
  4. Save with `publish` set to a value with leading and trailing whitespace.
  5. Save with `publish` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-031 — `post_name` ("Slug") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `post_name` ("Slug") set to a single character.
  3. Save with `post_name` ("Slug") set to a 255-character value.
  4. Save with `post_name` ("Slug") set to a value with leading and trailing whitespace.
  5. Save with `post_name` ("Slug") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-032 — `training_subject` ("Training Subject (Skill):") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `training_subject` ("Training Subject (Skill):") set to a single character.
  3. Save with `training_subject` ("Training Subject (Skill):") set to a 255-character value.
  4. Save with `training_subject` ("Training Subject (Skill):") set to a value with leading and trailing whitespace.
  5. Save with `training_subject` ("Training Subject (Skill):") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-033 — `training_type` ("Assign To") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `training_type` ("Assign To") set to the first real option.
  3. Save with `training_type` ("Assign To") set to the last option.
  4. Save with `training_type` ("Assign To") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-034 — `employees[]` ("Select Employees") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `employees[]` ("Select Employees") set to the first real option.
  3. Save with `employees[]` ("Select Employees") set to the last option.
  4. Save with `employees[]` ("Select Employees") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-035 — `departments[]` ("Select Departments") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `departments[]` ("Select Departments") set to the first real option.
  3. Save with `departments[]` ("Select Departments") set to the last option.
  4. Save with `departments[]` ("Select Departments") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-036 — `designations[]` ("Select Designations") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `designations[]` ("Select Designations") set to the first real option.
  3. Save with `designations[]` ("Select Designations") set to the last option.
  4. Save with `designations[]` ("Select Designations") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-037 — `training_frequency` ("Duration") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `training_frequency` ("Duration") set to a single character.
  3. Save with `training_frequency` ("Duration") set to a 255-character value.
  4. Save with `training_frequency` ("Duration") set to a value with leading and trailing whitespace.
  5. Save with `training_frequency` ("Duration") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-038 — `auto_assigned` ("Auto assigned for new employee") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `auto_assigned` ("Auto assigned for new employee") set to checked.
  3. Save with `auto_assigned` ("Auto assigned for new employee") set to unchecked.
  4. Save with `auto_assigned` ("Auto assigned for new employee") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-039 — `description` ("Description:") accepts its edge values

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Save with `description` ("Description:") set to a 5,000-character body.
  3. Save with `description` ("Description:") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### HRM_TRAINING-T2-040 — `post-new.php?post_type=erp_hr_training` saves with only its required fields

- **Surface:** `post-new.php?post_type=erp_hr_training`
- **Tags:** @tier2 @hrm-training @edge
- **Steps:**
  1. Open `post-new.php?post_type=erp_hr_training`.
  2. Fill only: `mm` ("Month 01-Jan 02-Feb 03-Mar 04-Apr 05-May 06-Jun 07-Jul 08-Aug 09-Sep 10-Oct 11-Nov 12-Dec"), `jj` ("Day"), `aa` ("Year"), `hh` ("Hour"), `mn` ("Minute").
  3. Submit.
- **Expected:** The record saves. The 21 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.
