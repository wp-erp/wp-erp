# Harness — Vue screens (payroll, attendance, deals, CRM, company)

18 screens.

### admin.php?page=erp-company

- **URL:** `admin.php?page=erp-company`
- **Heading:** Company Details
- **Tabs:** Overview · People · Leave · Payroll · Attendance · Assets · Documents · Training · Recruitment · Reports · Help · Contacts · Tasks · Deals · Integrations · Dashboard · Users · Transactions · Products · Settings

_No form fields on this screen._


### admin.php?page=custom-field-builder

- **URL:** `admin.php?page=custom-field-builder`
- **Heading:** Custom Field Builder
- **Buttons:** `Add New Field`, `Save Changes`
- **Tabs:** Overview · People · Leave · Payroll · Attendance · Assets · Documents · Training · Recruitment · Reports · Help · Contacts · Tasks · Deals · Integrations · Dashboard · Users · Transactions · Products · Settings · Employee · Contact · Company · Customer · Vendor

_No form fields on this screen._


### admin.php?page=erp-crm&section=dashboard

- **URL:** `admin.php?page=erp-crm&section=dashboard`
- **Heading:** CRM
- **Buttons:** `More`, `today`, `month`, `week`, `day`
- **Tabs:** Overview · People · Leave · Payroll · Attendance · Assets · Documents · Training · Recruitment · Reports · Help · Contacts · Tasks · Deals · Integrations · Dashboard · Users · Transactions · Products · Settings · All Deals · Activities · Activity Report · Customer Report · Growth Report
- **List columns** (``): SunMonTueWedThuFriSat · Sun · Mon · Tue · Wed · Thu · Fri · Sat · 26 · 27 · 28 · 29 · 30 · 31 · 1 · 2 · 3 · 4 · 5 · 6 · 7 · 8 · 9 · 10 · 11 · 12 · 13 · 14 · 15 · 16 · 17 · 18 · 19 · 20 · 21 · 22 · 23 · 24 · 25 · 26 · 27 · 28 · 29 · 30 · 31 · 1 · 2 · 3 · 4 · 5
- **List columns** (``): Sun · Mon · Tue · Wed · Thu · Fri · Sat
- **List columns** (``): 26 · 27 · 28 · 29 · 30 · 31 · 1
- **List columns** (``): 2 · 3 · 4 · 5 · 6 · 7 · 8
- **List columns** (``): 9 · 10 · 11 · 12 · 13 · 14 · 15
- **List columns** (``): 16 · 17 · 18 · 19 · 20 · 21 · 22
- **List columns** (``): 23 · 24 · 25 · 26 · 27 · 28 · 29
- **List columns** (``): 30 · 31 · 1 · 2 · 3 · 4 · 5

_No form fields on this screen._


### admin.php?page=erp-crm&section=deals

- **URL:** `admin.php?page=erp-crm&section=deals`
- **Heading:** CRM
- **Buttons:** `More`, `Pipeline`, `This month`, `Open Deals`, `Absolute`, `Percentage`, `Other Reasons`, `Number of Win`, `All`, `All Owners`, `Value`, `Count`, `All Agents`, `×`, `Close`
- **Tabs:** Overview · People · Leave · Payroll · Attendance · Assets · Documents · Training · Recruitment · Reports · Help · Contacts · Tasks · Deals · Integrations · Dashboard · Users · Transactions · Products · Settings · All Deals · Activities · Activity Report · Customer Report · Growth Report
- **List columns** (`wp-list-table widefat striped`): Stage · Counts of deals reached the stage · Values of deals reached the stage · Average deal value (USD) · Average time until the stage reached (days)
- **List columns** (`wp-list-table widefat striped`): Type · Total · Open · Done
- **List columns** (`wp-list-table widefat striped`): Title · Value · Company · Contact · Exp. close date · Owner

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `qlStartDate` | text |  | From Date |  |
| — | `—` | `qlEndDate` | text |  | To Date |  |
| From | `—` | `lgStartDate` | text |  | Start Date |  |
| To | `—` | `lgEndDate` | text |  | End Date |  |
| From | `—` | `lrStartDate` | text |  | Start Date |  |
| To | `—` | `lrEndDate` | text |  | End Date |  |
| Time Filter | `—` | `—` | checkbox |  |  |  |
| Time Filter | `—` | `—` | number |  | Time Step (Mins) |  |
| Time Filter | `—` | `—` | number |  | Max Interval (Mins) |  |
| From | `—` | `dealLostReasonStartDate` | text |  | Start Date |  |
| To | `—` | `dealLostReasonEndDate` | text |  | End Date |  |
| — | `—` | `topSalesPersonStartDate` | text |  | Start Date |  |
| — | `—` | `topSalesPersonEndDate` | text |  | End Date |  |
| — | `—` | `wld-start-date` | text |  | From Date |  |
| — | `—` | `wld-end-date` | text |  | To Date |  |
| — | `—` | `fwm-start-date` | text |  | From Date |  |
| — | `—` | `fwm-end-date` | text |  | To Date |  |


### admin.php?page=erp-crm&section=integration

- **URL:** `admin.php?page=erp-crm&section=integration`
- **Heading:** CRM
- **Buttons:** `More`, `Configure`
- **Tabs:** Overview · People · Leave · Payroll · Attendance · Assets · Documents · Training · Recruitment · Reports · Help · Contacts · Tasks · Deals · Integrations · Dashboard · Users · Transactions · Products · Settings · All Deals · Activities · Activity Report · Customer Report · Growth Report · Hubspot · Mailchimp · Salesforce · Helpscout

_No form fields on this screen._


### admin.php?page=erp-crm&section=reports

- **URL:** `admin.php?page=erp-crm&section=reports`
- **Heading:** CRM
- **Buttons:** `More`, `View Report`
- **Tabs:** Overview · People · Leave · Payroll · Attendance · Assets · Documents · Training · Recruitment · Reports · Help · Contacts · Tasks · Deals · Integrations · Dashboard · Users · Transactions · Products · Settings · All Deals · Activities · Activity Report · Customer Report · Growth Report

_No form fields on this screen._


### admin.php?page=erp-hr&section=attendance

- **URL:** `admin.php?page=erp-hr&section=attendance`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `More`, `Go to`, `Filter`
- **Tabs:** Overview · People · Leave · Payroll · Attendance · Assets · Documents · Training · Recruitment · Reports · Help · Contacts · Tasks · Deals · Integrations · Dashboard · Users · Transactions · Products · Settings · Requests · Leave Entitlements · Holidays · Policies · Calendar · Pay Calendar · Pay Run List · Bulk pay item edit · Shifts · Tools · Assign Bulk Shift · Allotments · Job Opening · Add Opening · Question Sets · Candidates · Add candidate · Stages · AI Talent Pool · AI Job Settings · AI Job Writer · AI Model Settings · Age Profile · Salary History · Gender Profile · Years of Service · Head Count · Leaves · Attendance (Date Based) · Attendance (Employee Based)
- **List columns** (`wp-list-table widefat fixed striped`): Date · Attended · Absent · Presence · Actions

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Item Name | `item_group-hide` | `item_group-hide` | checkbox |  |  |  |
| Type | `type-hide` | `type-hide` | checkbox |  |  |  |
| Category | `category_id-hide` | `category_id-hide` | checkbox |  |  |  |
| Reg Date | `date_reg-hide` | `date_reg-hide` | checkbox |  |  |  |
| Expiry Date | `date_expiry-hide` | `date_expiry-hide` | checkbox |  |  |  |
| Warranty Till | `date_warranty-hide` | `date_warranty-hide` | checkbox |  |  |  |
| Available/Total | `status-hide` | `status-hide` | checkbox |  |  |  |
| Assets | `wp_screen_options[value]` | `erp_assets_per_page` | number |  |  |  |
| Item Name | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| — | `—` | `—` | date |  | Type here |  |
| — | `—` | `—` | date |  | Start date |  |
| — | `—` | `—` | date |  | Start date |  |
| Date | `—` | `—` | checkbox |  |  |  |
| Date | `—` | `—` | checkbox |  |  |  |


### admin.php?page=erp-hr&section=payroll&sub-section=dashboard

- **URL:** `admin.php?page=erp-hr&section=payroll&sub-section=dashboard`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `More`
- **Tabs:** Overview · People · Leave · Payroll · Attendance · Assets · Documents · Training · Recruitment · Reports · Help · Contacts · Tasks · Deals · Integrations · Dashboard · Users · Transactions · Products · Settings · Requests · Leave Entitlements · Holidays · Policies · Calendar · Pay Calendar · Pay Run List · Bulk pay item edit · Shifts · Tools · Assign Bulk Shift · Allotments · Job Opening · Add Opening · Question Sets · Candidates · Add candidate · Stages · AI Talent Pool · AI Job Settings · AI Job Writer · AI Model Settings · Age Profile · Salary History · Gender Profile · Years of Service · Head Count · Leaves · Attendance (Date Based) · Attendance (Employee Based)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Item Name | `item_group-hide` | `item_group-hide` | checkbox |  |  |  |
| Type | `type-hide` | `type-hide` | checkbox |  |  |  |
| Category | `category_id-hide` | `category_id-hide` | checkbox |  |  |  |
| Reg Date | `date_reg-hide` | `date_reg-hide` | checkbox |  |  |  |
| Expiry Date | `date_expiry-hide` | `date_expiry-hide` | checkbox |  |  |  |
| Warranty Till | `date_warranty-hide` | `date_warranty-hide` | checkbox |  |  |  |
| Available/Total | `status-hide` | `status-hide` | checkbox |  |  |  |
| Assets | `wp_screen_options[value]` | `erp_assets_per_page` | number |  |  |  |
| Item Name | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |


### admin.php?page=erp-hr&section=payroll&sub-section=calendar

- **URL:** `admin.php?page=erp-hr&section=payroll&sub-section=calendar`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `More`, `Add New Pay Calendar`
- **Tabs:** Overview · People · Leave · Payroll · Attendance · Assets · Documents · Training · Recruitment · Reports · Help · Contacts · Tasks · Deals · Integrations · Dashboard · Users · Transactions · Products · Settings · Requests · Leave Entitlements · Holidays · Policies · Calendar · Pay Calendar · Pay Run List · Bulk pay item edit · Shifts · Tools · Assign Bulk Shift · Allotments · Job Opening · Add Opening · Question Sets · Candidates · Add candidate · Stages · AI Talent Pool · AI Job Settings · AI Job Writer · AI Model Settings · Age Profile · Salary History · Gender Profile · Years of Service · Head Count · Leaves · Attendance (Date Based) · Attendance (Employee Based)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Item Name | `item_group-hide` | `item_group-hide` | checkbox |  |  |  |
| Type | `type-hide` | `type-hide` | checkbox |  |  |  |
| Category | `category_id-hide` | `category_id-hide` | checkbox |  |  |  |
| Reg Date | `date_reg-hide` | `date_reg-hide` | checkbox |  |  |  |
| Expiry Date | `date_expiry-hide` | `date_expiry-hide` | checkbox |  |  |  |
| Warranty Till | `date_warranty-hide` | `date_warranty-hide` | checkbox |  |  |  |
| Available/Total | `status-hide` | `status-hide` | checkbox |  |  |  |
| Assets | `wp_screen_options[value]` | `erp_assets_per_page` | number |  |  |  |
| Item Name | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |


### admin.php?page=erp-hr&section=payroll&sub-section=payrun

- **URL:** `admin.php?page=erp-hr&section=payroll&sub-section=payrun`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `More`, `Filter`
- **Tabs:** Overview · People · Leave · Payroll · Attendance · Assets · Documents · Training · Recruitment · Reports · Help · Contacts · Tasks · Deals · Integrations · Dashboard · Users · Transactions · Products · Settings · Requests · Leave Entitlements · Holidays · Policies · Calendar · Pay Calendar · Pay Run List · Bulk pay item edit · Shifts · Tools · Assign Bulk Shift · Allotments · Job Opening · Add Opening · Question Sets · Candidates · Add candidate · Stages · AI Talent Pool · AI Job Settings · AI Job Writer · AI Model Settings · Age Profile · Salary History · Gender Profile · Years of Service · Head Count · Leaves · Attendance (Date Based) · Attendance (Employee Based)
- **List columns** (`wp-list-table widefat fixed striped table-view-list payruns`): Pay Period · Pay Run · Payment Date Sort descending. · Employees · Net Pay + Tax · Status Sort descending. · Action

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Item Name | `item_group-hide` | `item_group-hide` | checkbox |  |  |  |
| Type | `type-hide` | `type-hide` | checkbox |  |  |  |
| Category | `category_id-hide` | `category_id-hide` | checkbox |  |  |  |
| Reg Date | `date_reg-hide` | `date_reg-hide` | checkbox |  |  |  |
| Expiry Date | `date_expiry-hide` | `date_expiry-hide` | checkbox |  |  |  |
| Warranty Till | `date_warranty-hide` | `date_warranty-hide` | checkbox |  |  |  |
| Available/Total | `status-hide` | `status-hide` | checkbox |  |  |  |
| Assets | `wp_screen_options[value]` | `erp_assets_per_page` | number |  |  |  |
| Item Name | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| Filter by Status | `filter_payrun_status` | `filter_payrun_status` | select |  |  | - Select All -=-1 |
| Filter by Status | `filter_status_button` | `filter_status_button` | submit |  |  |  |


### admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit

- **URL:** `admin.php?page=erp-hr&section=payroll&sub-section=bulk-pay-item-edit`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `More`, `Search`, `Update`
- **Tabs:** Overview · People · Leave · Payroll · Attendance · Assets · Documents · Training · Recruitment · Reports · Help · Contacts · Tasks · Deals · Integrations · Dashboard · Users · Transactions · Products · Settings · Requests · Leave Entitlements · Holidays · Policies · Calendar · Pay Calendar · Pay Run List · Bulk pay item edit · Shifts · Tools · Assign Bulk Shift · Allotments · Job Opening · Add Opening · Question Sets · Candidates · Add candidate · Stages · AI Talent Pool · AI Job Settings · AI Job Writer · AI Model Settings · Age Profile · Salary History · Gender Profile · Years of Service · Head Count · Leaves · Attendance (Date Based) · Attendance (Employee Based)
- **List columns** (`wp-list-table widefat fixed striped table-rec-reports`): SL · Employee · Department · Designation · Total Payment

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Item Name | `item_group-hide` | `item_group-hide` | checkbox |  |  |  |
| Type | `type-hide` | `type-hide` | checkbox |  |  |  |
| Category | `category_id-hide` | `category_id-hide` | checkbox |  |  |  |
| Reg Date | `date_reg-hide` | `date_reg-hide` | checkbox |  |  |  |
| Expiry Date | `date_expiry-hide` | `date_expiry-hide` | checkbox |  |  |  |
| Warranty Till | `date_warranty-hide` | `date_warranty-hide` | checkbox |  |  |  |
| Available/Total | `status-hide` | `status-hide` | checkbox |  |  |  |
| Assets | `wp_screen_options[value]` | `erp_assets_per_page` | number |  |  |  |
| Item Name | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| SL | `pay_items` | `pay_items` | select |  |  | Select a pay item=-1, Travel Allowance=1, Accomodation Allowance=2, City Compensatory Allowance=3, Pay Adjustment=4, OverTime=5, Variable Pay=6, Bonus=7, Holiday Pay=8, Service Charge=9, Provident Fund=10, Loan=11 … +10 |
| SL | `emp_dept` | `emp_dept` | select |  |  | All Department=-1, General Management=1, Operations Department=2, Finance Department=3, Sales Department=4, Human Resource Department=5, Purchase Department=6, Engineering Department=7, Production Department=8, Procurement Department=9, pwerp_dept_2vjg9u=24, pwerp_dept_n40ldm=33 … +2 |
| SL | `emp_desig` | `emp_desig` | select |  |  | All Designations=-1, Business Executive=18, Business Manager=10, CEO=3, Customer Support Executive=20, Engineer=16, Finance/Accounts Manager=12, Hiring Manager=14, Human Resource Manager=13, Junior Engineer=17, Managing Director=4, Marketing Executive=19 … +13 |
| SL | `emp_name` | `emp_name` | search |  | Search an employee |  |


### admin.php?page=erp-hr&section=payroll&sub-section=reports

- **URL:** `admin.php?page=erp-hr&section=payroll&sub-section=reports`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `More`, `View Report`
- **Tabs:** Overview · People · Leave · Payroll · Attendance · Assets · Documents · Training · Recruitment · Reports · Help · Contacts · Tasks · Deals · Integrations · Dashboard · Users · Transactions · Products · Settings · Requests · Leave Entitlements · Holidays · Policies · Calendar · Pay Calendar · Pay Run List · Bulk pay item edit · Shifts · Tools · Assign Bulk Shift · Allotments · Job Opening · Add Opening · Question Sets · Candidates · Add candidate · Stages · AI Talent Pool · AI Job Settings · AI Job Writer · AI Model Settings · Age Profile · Salary History · Gender Profile · Years of Service · Head Count · Leaves · Attendance (Date Based) · Attendance (Employee Based)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Item Name | `item_group-hide` | `item_group-hide` | checkbox |  |  |  |
| Type | `type-hide` | `type-hide` | checkbox |  |  |  |
| Category | `category_id-hide` | `category_id-hide` | checkbox |  |  |  |
| Reg Date | `date_reg-hide` | `date_reg-hide` | checkbox |  |  |  |
| Expiry Date | `date_expiry-hide` | `date_expiry-hide` | checkbox |  |  |  |
| Warranty Till | `date_warranty-hide` | `date_warranty-hide` | checkbox |  |  |  |
| Available/Total | `status-hide` | `status-hide` | checkbox |  |  |  |
| Assets | `wp_screen_options[value]` | `erp_assets_per_page` | number |  |  |  |
| Item Name | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |


### admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings

- **URL:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-settings`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `More`, `← Back to Jobs`, `Generate with AI`, `Save`
- **Tabs:** Overview · People · Leave · Payroll · Attendance · Assets · Documents · Training · Recruitment · Reports · Help · Contacts · Tasks · Deals · Integrations · Dashboard · Users · Transactions · Products · Settings · Requests · Leave Entitlements · Holidays · Policies · Calendar · Pay Calendar · Pay Run List · Bulk pay item edit · Shifts · Tools · Assign Bulk Shift · Allotments · Job Opening · Add Opening · Question Sets · Candidates · Add candidate · Stages · AI Talent Pool · AI Job Settings · AI Job Writer · AI Model Settings · Age Profile · Salary History · Gender Profile · Years of Service · Head Count · Leaves · Attendance (Date Based) · Attendance (Employee Based)
- **List columns** (`wp-list-table widefat fixed striped ai-jobs-table`): Job Title · AI Candidate Processing · Actions

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Item Name | `item_group-hide` | `item_group-hide` | checkbox |  |  |  |
| Type | `type-hide` | `type-hide` | checkbox |  |  |  |
| Category | `category_id-hide` | `category_id-hide` | checkbox |  |  |  |
| Reg Date | `date_reg-hide` | `date_reg-hide` | checkbox |  |  |  |
| Expiry Date | `date_expiry-hide` | `date_expiry-hide` | checkbox |  |  |  |
| Warranty Till | `date_warranty-hide` | `date_warranty-hide` | checkbox |  |  |  |
| Available/Total | `status-hide` | `status-hide` | checkbox |  |  |  |
| Assets | `wp_screen_options[value]` | `erp_assets_per_page` | number |  |  |  |
| Item Name | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| — | `—` | `ai-job-search` | search |  | Search jobs... |  |
| — | `—` | `ai-processing-toggle` | checkbox |  |  |  |
| Category Detected | `category_detected` | `category-detected` | text |  | Product category automatically detected from the job posting. Edit if needed. |  |
| Core Skills Detected | `core_skills_detected` | `core-skills-detected` | textarea |  | List the key skills candidates should have. Add, remove, or edit as needed. Use comma-separated values. |  |
| Secondary Skills Detected | `secondary_skills_detected` | `secondary-skills-detected` | textarea |  | Optional additional skills that may be relevant for this role. |  |
| Tool & Platform Use Detected | `tools_detected` | `tools-detected` | textarea |  | Specify tools or platforms candidates should be familiar with (e.g., Jira, Mixpanel). |  |
| Minimum Experience | `minimum_experience_display` | `minimum-experience-display` | text |  |  |  |
| Experience Type | `experience_type_display` | `experience-type-display` | text |  |  |  |
| Preferred (Partial credit for related experiences) | `experience_preference` | `—` | radio |  |  |  |
| Required (Strict: only exact match gets points) | `experience_preference` | `—` | radio |  |  |  |
| — | `education_expectation` | `education-expectation` | text |  | Enter the minimum education required for this role (e.g., Bachelor's in CS, Business). |  |
| — | `certification` | `certification` | textarea |  | List any certifications that are expected or preferred for candidates. |  |
| Skills | `weight_skills_match` | `—` | number | yes |  |  |
| Experience | `weight_experience_match` | `—` | number | yes |  |  |
| Education | `weight_education_fit` | `—` | number | yes |  |  |
| Certifications | `weight_certification` | `—` | number | yes |  |  |
| Soft Skills | `weight_soft_skills` | `—` | number | yes |  |  |


### admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings

- **URL:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-settings`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `More`, `Save Settings`, `Save Model`
- **Tabs:** Overview · People · Leave · Payroll · Attendance · Assets · Documents · Training · Recruitment · Reports · Help · Contacts · Tasks · Deals · Integrations · Dashboard · Users · Transactions · Products · Settings · Requests · Leave Entitlements · Holidays · Policies · Calendar · Pay Calendar · Pay Run List · Bulk pay item edit · Shifts · Tools · Assign Bulk Shift · Allotments · Job Opening · Add Opening · Question Sets · Candidates · Add candidate · Stages · AI Talent Pool · AI Job Settings · AI Job Writer · AI Model Settings · Age Profile · Salary History · Gender Profile · Years of Service · Head Count · Leaves · Attendance (Date Based) · Attendance (Employee Based)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Item Name | `item_group-hide` | `item_group-hide` | checkbox |  |  |  |
| Type | `type-hide` | `type-hide` | checkbox |  |  |  |
| Category | `category_id-hide` | `category_id-hide` | checkbox |  |  |  |
| Reg Date | `date_reg-hide` | `date_reg-hide` | checkbox |  |  |  |
| Expiry Date | `date_expiry-hide` | `date_expiry-hide` | checkbox |  |  |  |
| Warranty Till | `date_warranty-hide` | `date_warranty-hide` | checkbox |  |  |  |
| Available/Total | `status-hide` | `status-hide` | checkbox |  |  |  |
| Assets | `wp_screen_options[value]` | `erp_assets_per_page` | number |  |  |  |
| Item Name | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| API Key | `erp_rec_gemini_api_key` | `erp_rec_gemini_api_key` | password |  |  |  |
| API Key | `erp_rec_anthropic_api_key` | `erp_rec_anthropic_api_key` | password |  |  |  |
| API Key | `erp_rec_openai_api_key` | `erp_rec_openai_api_key` | password |  |  |  |
| API Key | `erp_rec_openrouter_api_key` | `erp_rec_openrouter_api_key` | password |  |  |  |
| API Key | `submit` | `submit` | submit |  |  |  |
| Select AI Model | `—` | `ai-model-selector` | select |  |  | Gemini 1.5 Flash=gemini-1.5-flash-latest, Gemini 1.5 Pro=gemini-1.5-pro-latest, Gemini 2.0 Flash (Experimental)=gemini-2.0-flash-exp |


### admin.php?page=erp-workflow-new

- **URL:** `admin.php?page=erp-workflow-new`
- **Heading:** Create Workflow
- **Buttons:** `Save & Activate`, `Save Only`
- **Tabs:** Overview · People · Leave · Payroll · Attendance · Assets · Documents · Training · Recruitment · Reports · Help · Contacts · Tasks · Deals · Integrations · Dashboard · Users · Transactions · Products · Settings

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | text |  | Enter Workflow Name |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | submit |  |  |  |
| — | `—` | `—` | submit |  |  |  |


### admin.php?page=erp-hr&section=leave&sub-section=leave-requests

- **URL:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `More`, `Close dialog`
- **Tabs:** Overview · People · Leave · Payroll · Attendance · Assets · Documents · Training · Recruitment · Reports · Help · Contacts · Tasks · Deals · Integrations · Dashboard · Users · Transactions · Products · Settings · Requests · Leave Entitlements · Holidays · Policies · Calendar · Pay Calendar · Pay Run List · Bulk pay item edit · Shifts · Tools · Assign Bulk Shift · Allotments · Job Opening · Add Opening · Question Sets · Candidates · Add candidate · Stages · AI Talent Pool · AI Job Settings · AI Job Writer · AI Model Settings · Age Profile · Salary History · Gender Profile · Years of Service · Head Count · Leaves · Attendance (Date Based) · Attendance (Employee Based)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Item Name | `item_group-hide` | `item_group-hide` | checkbox |  |  |  |
| Type | `type-hide` | `type-hide` | checkbox |  |  |  |
| Category | `category_id-hide` | `category_id-hide` | checkbox |  |  |  |
| Reg Date | `date_reg-hide` | `date_reg-hide` | checkbox |  |  |  |
| Expiry Date | `date_expiry-hide` | `date_expiry-hide` | checkbox |  |  |  |
| Warranty Till | `date_warranty-hide` | `date_warranty-hide` | checkbox |  |  |  |
| Available/Total | `status-hide` | `status-hide` | checkbox |  |  |  |
| Assets | `wp_screen_options[value]` | `erp_assets_per_page` | number |  |  |  |
| Item Name | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| Employee name | `employee_name` | `employee_name` | text |  | Search by employee name |  |
| Financial year | `financial_year` | `financial_year` | select |  |  | Select year= |
| Leave Policy | `leave_policy` | `leave_policy` | select |  |  | All Policy= |
| Approved | `filter_leave_status[]` | `1` | checkbox |  |  |  |
| Pending | `filter_leave_status[]` | `2` | checkbox |  |  |  |
| Rejected | `filter_leave_status[]` | `3` | checkbox |  |  |  |
| Date range | `filter_leave_year` | `filter_leave_year` | select |  |  | Filter by date=, Last week=1, Last month=2, Last 3 months=3, Custom=custom |
| — | `hide_filter` | `—` | button |  |  |  |
| — | `leave_filter_reset` | `—` | button |  |  |  |
| — | `filter_employee_search` | `filter_employee_search` | submit |  |  |  |


### http://localhost:8888/wp-admin/admin.php?page=erp-crm&section=contact

- **URL:** `http://localhost:8888/wp-admin/admin.php?page=erp-crm&section=contact`
- **Heading:** CRM
- **Buttons:** `More`, `Import`, `Export`, `Add Filter`, `Or Filter`, `Cancel`, `Reset`, `Apply`
- **List columns** (`vtable wp-list-table widefat fixed striped customers`): Select All · Contact name · Email Address · Phone · Life stage · Owner · Created At

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Search Contact | `s` | `search-input` | search |  | Search Contact |  |
| — | `filter_assign_contact` | `erp-select-user-for-assign-contact` | select |  |  | (blank)= |
| — | `filter_save_filter` | `erp-select-save-advance-filter` | select |  |  | --Select save filter --= |
| — | `filter_contact_company` | `erp-select-contact-company` | select |  |  | (blank)= |
| — | `—` | `—` | submit |  |  |  |
| — | `—` | `—` | submit |  |  |  |
| — | `—` | `filter` | submit |  |  |  |
| — | `—` | `current-page-selector` | text |  |  |  |
| Select All | `—` | `cb-select-all-1` | checkbox |  |  |  |
| Select All | `—` | `cb-select-all-2` | checkbox |  |  |  |


### http://localhost:8888/wp-admin/admin.php?page=erp-company

- **URL:** `http://localhost:8888/wp-admin/admin.php?page=erp-company`
- **Heading:** Company Details
- **Tabs:** Overview · People · Leave · Payroll · Attendance · Assets · Documents · Training · Recruitment · Reports · Help · Contacts · Tasks · Deals · Integrations · Dashboard · Users · Transactions · Products · Settings

_No form fields on this screen._

