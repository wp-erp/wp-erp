# Harness — hrm-training-recruitment-cpt

Captured from the running site at `localhost:8888` (wp-erp 1.17.8 + erp-pro 1.7.0).

4 screens.

### edit.php?post_type=erp_hr_training

- **URL:** `edit.php?post_type=erp_hr_training`
- **Heading:** Training
- **Buttons:** `Apply`, `Screen Options`
- **List columns** (`wp-list-table widefat fixed striped table-view-list posts`): Select All · Title Sort ascending. · Training Subject · Description · Duration · Participants

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Training Subject | `training_subject-hide` | `training_subject-hide` | checkbox |  |  |  |
| Description | `description-hide` | `description-hide` | checkbox |  |  |  |
| Duration | `duration-hide` | `duration-hide` | checkbox |  |  |  |
| Participants | `participant-hide` | `participant-hide` | checkbox |  |  |  |
| Number of items per page: | `wp_screen_options[value]` | `edit_erp_hr_training_per_page` | number |  |  |  |
| Compact view | `mode` | `list-view-mode` | radio |  |  |  |
| Extended view | `mode` | `excerpt-view-mode` | radio |  |  |  |
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| Select All | `—` | `cb-select-all-1` | checkbox |  |  |  |
| Select All | `—` | `cb-select-all-2` | checkbox |  |  |  |


### post-new.php?post_type=erp_hr_training

- **URL:** `post-new.php?post_type=erp_hr_training`
- **Heading:** Add New Training
- **Buttons:** `Screen Options`, `Move up`, `Move down`, `Toggle panel: Publish`, `Save`, `Save Draft`, `Preview (opens in a new tab)`, `OK`, `Publish`, `Toggle panel: Slug`, `Toggle panel: HR Training Options`

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Slug | `slugdiv-hide` | `slugdiv-hide` | checkbox |  |  |  |
| HR Training Options | `erp-hr-training-meta-box-hide` | `erp-hr-training-meta-box-hide` | checkbox |  |  |  |
| 1 column | `screen_columns` | `—` | radio |  |  |  |
| 2 columns | `screen_columns` | `—` | radio |  |  |  |
| Enable full-height editor and distraction-free functionality. | `—` | `editor-expand-toggle` | checkbox |  |  |  |
| Add title | `post_title` | `title` | text |  |  |  |
| — | `save` | `save` | submit |  |  |  |
| — | `save` | `save-post` | submit |  |  |  |
| Set status | `post_status` | `post_status` | select |  |  | Pending Review=pending, Draft=draft |
| Public | `visibility` | `visibility-radio-public` | radio |  |  |  |
| Password protected | `visibility` | `visibility-radio-password` | radio |  |  |  |
| Password: | `post_password` | `post_password` | text |  |  |  |
| Private | `visibility` | `visibility-radio-private` | radio |  |  |  |
| Month 01-Jan 02-Feb 03-Mar 04-Apr 05-May 06-Jun 07-Jul 08-Aug 09-Sep 10-Oct 11-Nov 12-Dec | `mm` | `mm` | select | yes |  | 01-Jan=01, 02-Feb=02, 03-Mar=03, 04-Apr=04, 05-May=05, 06-Jun=06, 07-Jul=07, 08-Aug=08, 09-Sep=09, 10-Oct=10, 11-Nov=11, 12-Dec=12 |
| Day | `jj` | `jj` | text | yes |  |  |
| Year | `aa` | `aa` | text | yes |  |  |
| Hour | `hh` | `hh` | text | yes |  |  |
| Minute | `mn` | `mn` | text | yes |  |  |
| — | `publish` | `publish` | submit |  |  |  |
| Slug | `post_name` | `post_name` | text |  |  |  |
| Training Subject (Skill): | `training_subject` | `traning-subject` | text |  |  |  |
| Assign To | `training_type` | `training_type` | select |  |  | -- Select --=, All Employees=all_employee, Selected Employee=selected_employee, By Department=by_department, By Designation=by_designation |
| Select Employees | `employees[]` | `employees` | select |  |  | pwerpFirst Q Lastushu=7, pwerpFirst Q Lasthasu=8, pwerpFirst Q Lastlsdk=9, pwerpFirst Q Lastytlo=10, pwerpFirst LastProbe=6, erp_employee=5 |
| Select Departments | `departments[]` | `departments` | select |  |  | General Management=1, Operations Department=2, Finance Department=3, Sales Department=4, Human Resource Department=5, Purchase Department=6, Engineering Department=7, Production Department=8, Procurement Department=9, pwerp_dept_2vjg9u=24, pwerp_dept_n40ldm=33, pwerp_dept_sfcrvj=41 … +1 |
| Select Designations | `designations[]` | `designations` | select |  |  | Business Executive=18, Business Manager=10, CEO=3, Customer Support Executive=20, Engineer=16, Finance/Accounts Manager=12, Hiring Manager=14, Human Resource Manager=13, Junior Engineer=17, Managing Director=4, Marketing Executive=19, Marketing Manager=9 … +12 |
| Duration | `training_frequency` | `erp-training-frequency` | text |  |  |  |
| Auto assigned for new employee | `auto_assigned` | `—` | checkbox |  |  |  |
| Description: | `description` | `description` | textarea |  |  |  |


### edit.php?post_type=erp_hr_questionnaire

- **URL:** `edit.php?post_type=erp_hr_questionnaire`
- **Heading:** HR Questionnaire
- **Buttons:** `Apply`, `Screen Options`
- **List columns** (`wp-list-table widefat fixed striped table-view-list posts`): Select All · Title Sort ascending. · Date · Total Question · Created On · Modified

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Date | `date-hide` | `date-hide` | checkbox |  |  |  |
| Total Question | `questions-hide` | `questions-hide` | checkbox |  |  |  |
| Created On | `created-hide` | `created-hide` | checkbox |  |  |  |
| Modified | `modified-hide` | `modified-hide` | checkbox |  |  |  |
| Number of items per page: | `wp_screen_options[value]` | `edit_erp_hr_questionnaire_per_page` | number |  |  |  |
| Compact view | `mode` | `list-view-mode` | radio |  |  |  |
| Extended view | `mode` | `excerpt-view-mode` | radio |  |  |  |
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| Select All | `—` | `cb-select-all-1` | checkbox |  |  |  |
| Select All | `—` | `cb-select-all-2` | checkbox |  |  |  |


### post-new.php?post_type=erp_hr_questionnaire

- **URL:** `post-new.php?post_type=erp_hr_questionnaire`
- **Heading:** Add New HR Questionnaire
- **Buttons:** `Screen Options`, `Move up`, `Move down`, `Toggle panel: Publish`, `Save`, `Save Draft`, `OK`, `Publish`, `Toggle panel: Slug`, `Toggle panel: Questionnaire Settings`, `Add New Question`
- **Tabs:** Overview · People · Leave · Payroll · Attendance · Assets · Documents · Training · Recruitment · Reports · Help · Contacts · Tasks · Deals · Integrations · Dashboard · Users · Transactions · Products · Settings · Requests · Leave Entitlements · Holidays · Policies · Calendar · Pay Calendar · Pay Run List · Bulk pay item edit · Shifts · Tools · Assign Bulk Shift · Allotments · Job Opening · Add Opening · Question Sets · Candidates · Add candidate · Stages · AI Talent Pool · AI Job Settings · AI Job Writer · AI Model Settings · Age Profile · Salary History · Gender Profile · Years of Service · Head Count · Leaves · Attendance (Date Based) · Attendance (Employee Based)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Slug | `slugdiv-hide` | `slugdiv-hide` | checkbox |  |  |  |
| Questionnaire Settings | `erp-hr-questionnaire-meta-box-hide` | `erp-hr-questionnaire-meta-box-hide` | checkbox |  |  |  |
| 1 column | `screen_columns` | `—` | radio |  |  |  |
| 2 columns | `screen_columns` | `—` | radio |  |  |  |
| Enable full-height editor and distraction-free functionality. | `—` | `editor-expand-toggle` | checkbox |  |  |  |
| Add title | `post_title` | `title` | text |  |  |  |
| — | `save` | `save` | submit |  |  |  |
| — | `save` | `save-post` | submit |  |  |  |
| Set status | `post_status` | `post_status` | select |  |  | Pending Review=pending, Draft=draft |
| Public | `visibility` | `visibility-radio-public` | radio |  |  |  |
| Password protected | `visibility` | `visibility-radio-password` | radio |  |  |  |
| Password: | `post_password` | `post_password` | text |  |  |  |
| Private | `visibility` | `visibility-radio-private` | radio |  |  |  |
| Month 01-Jan 02-Feb 03-Mar 04-Apr 05-May 06-Jun 07-Jul 08-Aug 09-Sep 10-Oct 11-Nov 12-Dec | `mm` | `mm` | select |  |  | 01-Jan=01, 02-Feb=02, 03-Mar=03, 04-Apr=04, 05-May=05, 06-Jun=06, 07-Jul=07, 08-Aug=08, 09-Sep=09, 10-Oct=10, 11-Nov=11, 12-Dec=12 |
| Day | `jj` | `jj` | text |  |  |  |
| Year | `aa` | `aa` | text |  |  |  |
| Hour | `hh` | `hh` | text |  |  |  |
| Minute | `mn` | `mn` | text |  |  |  |
| — | `publish` | `publish` | submit |  |  |  |
| Slug | `post_name` | `post_name` | text |  |  |  |

