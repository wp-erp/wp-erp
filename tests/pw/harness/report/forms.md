# Harness — modal & template forms

ERP renders most create/edit forms from `<script type="text/html">` templates that only
reach the DOM when a modal opens. These were parsed straight out of the templates, so the
field list is complete rather than whatever happened to be open.

46 templates, 257 fields.

### `tmpl-asset-category-new`

- **Seen on:** `admin.php?page=erp-hr&section=asset&sub-section=asset`, `admin.php?page=erp-hr&section=asset&sub-section=asset-allottment`, `admin.php?page=erp-hr&section=asset&sub-section=asset-request`

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Category Name * | `cat_name` | `cat_name` | text | yes |  |  |


### `tmpl-employee-assign-new-training`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+16)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Training * | `training-id` | `training-id` | select | yes |  | Select=0 |
| Completed Date * | `training-completed-date` | `training-completed-date` | text | yes |  |  |
| Trainer's name * | `training-trainer` | `training-trainer` | text | yes |  |  |
| Trainer' Phone No. | `trainer-phone` | `trainer-phone` | text |  |  |  |
| Cost | `training-cost` | `training-cost` | text |  |  |  |
| Credit | `training-credit` | `training-credit` | text |  |  |  |
| Hours | `training-hours` | `training-hours` | text |  |  |  |
| Notes | `training-notes` | `training-notes` | textarea |  |  |  |
| Rating * | `training-rate` | `training-rate` | number | yes | Rate between 1 to 10 |  |


### `tmpl-erp-address`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+16)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Location Name * | `location_name` | `location_name` | text | yes |  |  |
| Address Line 1 * | `address_1` | `address_1` | text | yes |  |  |
| Address Line 2 | `address_2` | `address_2` | text |  |  |  |
| City | `city` | `city` | text |  |  |  |
| Country * | `country` | `erp-popup-country` | select |  |  | - Select -=-1, Åland Islands=AX, Afghanistan=AF, Albania=AL, Algeria=DZ, Andorra=AD, Angola=AO, Anguilla=AI, Antarctica=AQ, Antigua and Barbuda=AG, Argentina=AR, Armenia=AM … +233 |
| Province / State | `state` | `erp-state` | select |  |  | - Select -=0 |
| Postal / Zip Code | `zip` | `zip` | text |  |  |  |


### `tmpl-erp-allotment-new`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+11)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Category * | `category_id` | `category_id` | select | yes |  | — Select Category —=-1 |
| Item Name * | `item_group` | `item_group` | select | yes |  | —Select Group—=-1 |
| Item * | `item` | `item` | select | yes |  | —Select Item—=-1 |
| Allot To * | `allotted_to` | `allotted_to` | select | yes |  | - Select Employee -=0, pwerpFirst Q Lastushu=7, pwerpFirst Q Lasthasu=8, pwerpFirst Q Lastlsdk=9, pwerpFirst Q Lastytlo=10, pwerpFirst LastProbe=6, erp_employee=5 |
| Given Date * | `given_date` | `given_date` | text | yes |  |  |
| Returnable? | `is_returnable` | `is_returnable` | checkbox |  |  |  |
| Return Date * | `return_date` | `return_date` | text | yes |  |  |


### `tmpl-erp-asset-edit`

- **Seen on:** `admin.php?page=erp-hr&section=asset&sub-section=asset`, `admin.php?page=erp-hr&section=asset&sub-section=asset-allottment`, `admin.php?page=erp-hr&section=asset&sub-section=asset-request`
- **Buttons:** `Delete`

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Category * | `category_id` | `category_id` | select | yes |  | — Select Category —=-1 |
| Item Group * | `item_group` | `item_group` | text | yes |  |  |
| Item Code * | `items[{{i}}][item_code]` | `items[{{i}}][item_code]` | text | yes |  |  |
| Model No | `items[{{i}}][model_no]` | `items[{{i}}][model_no]` | text |  |  |  |
| Manufacturer | `items[{{i}}][manufacturer]` | `items[{{i}}][manufacturer]` | text |  |  |  |
| Price | `items[{{i}}][price]` | `items[{{i}}][price]` | text |  |  |  |
| Expiry Date | `items[{{i}}][date_exp]` | `items[{{i}}][date_exp]` | text |  |  |  |
| Warranty Till | `items[{{i}}][date_warr]` | `items[{{i}}][date_warr]` | text |  |  |  |
| Allottable? | `items[{{i}}][allottable]` | `items[{{i}}][allottable]` | checkbox |  |  |  |
| Serial/License Info | `items[{{i}}][item_serial]` | `items[{{i}}][item_serial]` | textarea |  |  |  |
| Description | `items[{{i}}][item_desc]` | `items[{{i}}][item_desc]` | textarea |  |  |  |


### `tmpl-erp-asset-new`

- **Seen on:** `admin.php?page=erp-hr&section=asset&sub-section=asset`, `admin.php?page=erp-hr&section=asset&sub-section=asset-allottment`, `admin.php?page=erp-hr&section=asset&sub-section=asset-request`

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Category * | `category_id` | `category_id` | select | yes |  | — Select Category —=-1 |
| Item Name * | `item_group` | `item_group` | text | yes |  |  |
| Asset Type * | `asset_type` | `asset_type` | select | yes |  | Single Item=single, Multiple Items=variable |
| Item Code * | `items[1][item_code]` | `items[1][item_code]` | text | yes |  |  |
| Model No | `items[1][model_no]` | `items[1][model_no]` | text |  |  |  |
| Description | `items[1][item_desc]` | `items[1][item_desc]` | textarea |  |  |  |
| Serial/License Info | `items[1][item_serial]` | `items[1][item_serial]` | textarea |  |  |  |
| Manufacturer | `items[1][manufacturer]` | `items[1][manufacturer]` | text |  |  |  |
| Price | `items[1][price]` | `items[1][price]` | number |  |  |  |
| Warranty Till | `items[1][date_warr]` | `items[1][date_warr]` | text |  |  |  |
| Expiry Date | `items[1][date_exp]` | `items[1][date_exp]` | text |  |  |  |
| Allottable? | `items[1][allottable]` | `items[1][allottable]` | checkbox |  |  |  |


### `tmpl-erp-asset-request-reject`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+11)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Reject Reason | `reject_reason` | `reject_reason` | textarea |  |  |  |


### `tmpl-erp-asset-request-reply`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+11)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Category * | `category_id` | `category_id` | select | yes |  | — Select Category —=-1 |
| Item Name * | `item_group` | `item_group` | select | yes |  | —Select Group—=-1 |
| Item * | `item` | `item` | select | yes |  | —Select Item—=-1 |
| Given Date * | `given_date` | `given_date` | text | yes |  |  |
| Returnable? | `is_returnable` | `is_returnable` | checkbox |  |  |  |
| Return Date * | `return_date` | `return_date` | text | yes |  |  |
| Instructions | `reply_msg` | `reply_msg` | textarea |  |  |  |


### `tmpl-erp-asset-return`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+11)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Return Date * | `return_date` | `return_date` | text | yes |  |  |
| Return Note | `return_note` | `return_note` | textarea |  |  |  |
| Lost/Damaged | `is_dissmissed` | `is_dissmissed` | checkbox |  |  |  |


### `tmpl-erp-crm-customer-schedules`

- **Seen on:** `admin.php?page=erp-crm&section=task`

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `schedule_title` | `—` | text | yes | Enter Schedule Title |  |
| — | `user_id` | `assign-contact` | select | yes |  | (blank)= |
| Start | `start_date` | `—` | text |  | yy-mm-dd |  |
| Start | `start_time` | `—` | text | yes | 12.00pm |  |
| End | `end_date` | `—` | text | yes | yy-mm-dd |  |
| End | `end_time` | `—` | text | yes | 12.00pm |  |
| — | `all_day` | `—` | checkbox |  |  |  |
| — | `invite_contact[]` | `erp-crm-activity-invite-contact` | select |  |  | Me ( admin )=1, erp_crm_agent=11, erp_crm_manager=3 |
| Schedule Type | `schedule_type` | `schedule_type` | select | yes |  | --Select--=, Meeting=meeting, Call=call |
| — | `allow_notification` | `—` | checkbox |  |  |  |
| Notify Via | `notification_via` | `notification_via` | select |  |  | --Select--=, Email=email, SMS=sms |
| Notify before | `notification_time_interval` | `—` | text |  | 10 |  |
| Notify before | `notification_time` | `notification_time` | select |  |  | -Select-=, minute=minute, hour=hour, day=day |
| — | `user_id` | `assign-contact` | select | yes |  | (blank)= |
| — | `log_type` | `erp-crm-feed-log-type` | select | yes |  | -- Select type --=, Log a Call=call, Log a Meeting=meeting, Log an Email=email, Log an SMS=sms |
| — | `log_time` | `—` | text | yes | 12.00pm |  |
| — | `log_date` | `—` | text |  | yy-mm-dd |  |
| Subject | `email_subject` | `—` | text |  | Subject log... |  |
| — | `invite_contact[]` | `erp-crm-activity-invite-contact` | select |  |  | Me ( admin )=1, erp_crm_agent=11, erp_crm_manager=3 |


### `tmpl-erp-crm-export-customer`

- **Seen on:** `admin.php?page=erp-crm&section=contact`

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `selecctall` | checkbox |  |  |  |


### `tmpl-erp-crm-import-customer`

- **Seen on:** `admin.php?page=erp-crm&section=contact`
- **Buttons:** `Download Sample CSV`

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| CSV File * | `csv_file` | `csv_file` | file | yes |  |  |
| Contact Owner | `contact_owner` | `contact_owner` | select |  |  | Admin <wordpress@example.com>=1, Erp_crm_agent <erp_crm_agent@erp.test>=11, Erp_crm_manager <crm.manager@example.test>=3 |
| Life Stage | `life_stage` | `life_stage` | select |  |  | Customer=customer, Lead=lead, Opportunity=opportunity, Subscriber=subscriber |
| Contact Group | `contact_group` | `—` | select |  |  | — Select Group —= |


### `tmpl-erp-crm-import-users`

- **Seen on:** `admin.php?page=erp-crm&section=contact`

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| User Role | `user_role` | `user_role` | select |  |  | Administrator=administrator, Editor=editor, Author=author, Contributor=contributor, Subscriber=subscriber, HR Manager=erp_hr_manager, Employee=employee, CRM Manager=erp_crm_manager, CRM Agent=erp_crm_agent, Accounting Manager=erp_ac_manager, Customer=customer, Shop Manager=shop_manager … +1 |
| Contact Owner | `contact_owner` | `contact_owner` | select |  |  | Admin <wordpress@example.com>=1, Erp_crm_agent <erp_crm_agent@erp.test>=11, Erp_crm_manager <crm.manager@example.test>=3 |
| Life Stage | `life_stage` | `life_stage` | select |  |  | Customer=customer, Lead=lead, Opportunity=opportunity, Subscriber=subscriber |
| Contact Group | `contact_group` | `—` | select |  |  | — Select Group —= |


### `tmpl-erp-crm-new-contact`

- **Seen on:** `admin.php?page=erp-crm&section=contact`
- **Buttons:** `Upload Photo`

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| First Name * | `contact[main][first_name]` | `first_name` | text | yes |  |  |
| Last Name | `contact[main][last_name]` | `last_name` | text |  |  |  |
| Company Name * | `contact[main][company]` | `company` | text | yes |  |  |
| Email * | `contact[main][email]` | `erp-crm-new-contact-email` | email | yes |  |  |
| Phone Number | `contact[main][phone]` | `contact[main][phone]` | text |  |  |  |
| Life Stage * | `contact[meta][life_stage]` | `contact[meta][life_stage]` | select | yes |  | --Select Stage--=, Customer=customer, Lead=lead, Opportunity=opportunity, Subscriber=subscriber |
| Contact Owner * | `contact[meta][contact_owner]` | `erp-crm-contact-owner-id` | select | yes |  | --Select--=, admin (wordpress@example.com)=1, erp_crm_agent (erp_crm_agent@erp.test)=11, erp_crm_manager (crm.manager@example.test)=3 |
| Show Advanced Fields | `—` | `advanced_fields` | checkbox |  |  |  |
| Date of Birth | `contact[meta][date_of_birth]` | `contact[meta][date_of_birth]` | text |  |  |  |
| Age (years) | `contact[meta][contact_age]` | `contact[meta][contact_age]` | number |  |  |  |
| Mobile | `contact[main][mobile]` | `contact[main][mobile]` | text |  |  |  |
| Website | `contact[main][website]` | `contact[main][website]` | text |  |  |  |
| Fax Number | `contact[main][fax]` | `contact[main][fax]` | text |  |  |  |
| Address 1 | `contact[main][street_1]` | `contact[main][street_1]` | text |  |  |  |
| Address 2 | `contact[main][street_2]` | `contact[main][street_2]` | text |  |  |  |
| City | `contact[main][city]` | `contact[main][city]` | text |  |  |  |
| Country | `contact[main][country]` | `erp-popup-country` | select |  |  | - Select -=-1, Åland Islands=AX, Afghanistan=AF, Albania=AL, Algeria=DZ, Andorra=AD, Angola=AO, Anguilla=AI, Antarctica=AQ, Antigua and Barbuda=AG, Argentina=AR, Armenia=AM … +233 |
| Province / State | `contact[main][state]` | `erp-state` | select |  |  | - Select -= |
| Post Code/Zip Code | `contact[main][postal_code]` | `contact[main][postal_code]` | text |  |  |  |
| Contact Source | `contact[meta][source]` | `erp-source` | select |  |  | Advertisement=advert, Chat=chat, Contact Form=contact_form, Employee Referral=employee_referral, External Referral=external_referral, Marketing campaign=marketing_campaign, Newsletter=newsletter, OnlineStore=online_store, Optin Forms=optin_form, Partner=partner, Phone Call=phone, Public Relations=public_relations … +8 |
| Others | `contact[main][other]` | `contact[main][other]` | text |  |  |  |
| Notes | `contact[main][notes]` | `contact[main][notes]` | textarea |  |  |  |
| Facebook | `contact[social][facebook]` | `contact[social][facebook]` | text |  |  |  |
| Twitter | `contact[social][twitter]` | `contact[social][twitter]` | text |  |  |  |
| Google Plus | `contact[social][googleplus]` | `contact[social][googleplus]` | text |  |  |  |
| Linkedin | `contact[social][linkedin]` | `contact[social][linkedin]` | text |  |  |  |


### `tmpl-erp-desig-row`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=designation`

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `desig[]` | `cb-select-1` | checkbox |  |  |  |


### `tmpl-erp-doc-share-template`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=documents`, `admin.php?page=erp-crm&section=contact`

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Share files by | `share_by` | `share_by` | select |  |  | All employees=all_employees, By department=by_department, By designation=by_designation, By employee=by_employee |
| Department | `department` | `department` | select |  |  | All Department=-1, General Management=1, Operations Department=2, Finance Department=3, Sales Department=4, Human Resource Department=5, Purchase Department=6, Engineering Department=7, Production Department=8, Procurement Department=9, pwerp_dept_2vjg9u=24, pwerp_dept_n40ldm=33 … +2 |
| Designation | `designation` | `designation` | select |  |  | All Designations=-1, Business Executive=18, Business Manager=10, CEO=3, Customer Support Executive=20, Engineer=16, Finance/Accounts Manager=12, Hiring Manager=14, Human Resource Manager=13, Junior Engineer=17, Managing Director=4, Marketing Executive=19 … +13 |
| Selected employees | `selected_emp[]` | `selected_emp[]` | select |  |  | pwerpFirst Q Lastushu=7, pwerpFirst Q Lasthasu=8, pwerpFirst Q Lastlsdk=9, pwerpFirst Q Lastytlo=10, pwerpFirst LastProbe=6, erp_employee=5 |


### `tmpl-erp-doc-tree-template`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=documents`, `admin.php?page=erp-crm&section=contact`

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Home | `—` | `0` | checkbox |  |  |  |


### `tmpl-erp-employee-export-csv`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+1)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `selecctall` | checkbox |  |  |  |


### `tmpl-erp-employee-import-csv`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+1)
- **Buttons:** `Download Sample CSV`

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| CSV File * | `csv_file` | `csv_file` | file | yes |  |  |


### `tmpl-erp-employee-row`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+1)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `employee[]` | `cb-select-1` | checkbox |  |  |  |


### `tmpl-erp-employment-compensation`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+1)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Date * | `date` | `date` | text | yes |  |  |
| Pay Rate * | `pay_rate` | `pay_rate` | text | yes |  |  |
| Pay Type * | `pay_type` | `pay_type` | select | yes |  | - Select -=0, Hourly=hourly, Daily=daily, Weekly=weekly, Biweekly=biweekly, Monthly=monthly, Contract=contract |
| Change Reason | `change-reason` | `change-reason` | select |  |  | - Select -=0, Promotion=promotion, Performance=performance, Increment=increment |
| Comment | `comment` | `comment` | textarea |  | Optional comment |  |


### `tmpl-erp-employment-compensation-history`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+1)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Date * | `date` | `date` | text | yes |  |  |
| Pay Rate * | `type` | `type` | text | yes |  |  |
| Pay Type * | `category` | `category` | select | yes |  | - Select -=0, Hourly=hourly, Daily=daily, Weekly=weekly, Biweekly=biweekly, Monthly=monthly, Contract=contract |
| Change Reason | `data` | `data` | select |  |  | - Select -=0, Promotion=promotion, Performance=performance, Increment=increment |
| Comment | `comment` | `comment` | textarea |  | Optional comment |  |


### `tmpl-erp-employment-dependent`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+1)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Name * | `name` | `name` | text | yes | Name of the person |  |
| Relationship * | `relation` | `relation` | text | yes | Father |  |
| Date of Birth | `dob` | `dob` | text |  | 1988-03-18 |  |


### `tmpl-erp-employment-education`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+1)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| School Name * | `school` | `school` | text | yes | ABC School |  |
| Degree * | `degree` | `degree` | text | yes | Bachelor in Science |  |
| Field of Study * | `field` | `field` | text | yes | Physics |  |
| Year of Completion * | `finished` | `finished` | number | yes | 2026 |  |
| Result type * | `result_type` | `result_type` | select | yes |  | - Select -=, Grade=grade, Pecentage=percentage |
| Result (Grade) * | `gpa` | `gpa` | text | yes | 5.0 |  |
| Scale (Out of) * | `scale` | `scale` | number | yes | 5.0 |  |
| Notes | `notes` | `notes` | textarea |  | Additional notes |  |
| Interests | `interest` | `interest` | textarea |  |  |  |
| Expiration date | `expiration_date` | `expiration_date` | text |  |  |  |


### `tmpl-erp-employment-job-info-history`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+1)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Date * | `date` | `date` | text | yes |  |  |
| Location | `type` | `type` | select |  |  | - Select -=0, Main Location=-1 |
| Department | `category` | `category` | select |  |  | - Select Department -=-1, General Management=1, Operations Department=2, Finance Department=3, Sales Department=4, Human Resource Department=5, Purchase Department=6, Engineering Department=7, Production Department=8, Procurement Department=9, pwerp_dept_2vjg9u=24, pwerp_dept_n40ldm=33 … +2 |
| Job Title | `comment` | `comment` | select |  |  | - Select Designation -=-1, Business Executive=18, Business Manager=10, CEO=3, Customer Support Executive=20, Engineer=16, Finance/Accounts Manager=12, Hiring Manager=14, Human Resource Manager=13, Junior Engineer=17, Managing Director=4, Marketing Executive=19 … +13 |
| Reporting To | `data` | `performance_reporting_to` | select |  |  | - Select Employee -=0, pwerpFirst Q Lastushu=7, pwerpFirst Q Lasthasu=8, pwerpFirst Q Lastlsdk=9, pwerpFirst Q Lastytlo=10, pwerpFirst LastProbe=6, erp_employee=5 |


### `tmpl-erp-employment-jobinfo`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+1)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Date * | `date` | `date` | text | yes |  |  |
| Location | `location` | `location` | select |  |  | - Select -=0, Main Location=-1 |
| Department | `department` | `department` | select |  |  | - Select Department -=-1, General Management=1, Operations Department=2, Finance Department=3, Sales Department=4, Human Resource Department=5, Purchase Department=6, Engineering Department=7, Production Department=8, Procurement Department=9, pwerp_dept_2vjg9u=24, pwerp_dept_n40ldm=33 … +2 |
| Job Title | `designation` | `designation` | select |  |  | - Select Designation -=-1, Business Executive=18, Business Manager=10, CEO=3, Customer Support Executive=20, Engineer=16, Finance/Accounts Manager=12, Hiring Manager=14, Human Resource Manager=13, Junior Engineer=17, Managing Director=4, Marketing Executive=19 … +13 |
| Reporting To | `reporting_to` | `performance_reporting_to` | select |  |  | - Select Employee -=0, pwerpFirst Q Lastushu=7, pwerpFirst Q Lasthasu=8, pwerpFirst Q Lastlsdk=9, pwerpFirst Q Lastytlo=10, pwerpFirst LastProbe=6, erp_employee=5 |


### `tmpl-erp-employment-performance-comments`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+1)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Reference Date * | `performance_date` | `performance_date` | text | yes |  |  |
| Reviewer | `reviewer` | `performance_reviewer` | select |  |  | - Select Employee -=0, pwerpFirst Q Lastushu=7, pwerpFirst Q Lasthasu=8, pwerpFirst Q Lastlsdk=9, pwerpFirst Q Lastytlo=10, pwerpFirst LastProbe=6, erp_employee=5 |
| Comments | `comments` | `performance_comments` | textarea |  |  |  |


### `tmpl-erp-employment-performance-goals`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+1)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Set Date * | `performance_date` | `performance_date` | text | yes |  |  |
| Completion Date * | `completion_date` | `performance_completion_date` | text | yes |  |  |
| Goal Description | `goal_description` | `performance_goal_description` | textarea |  |  |  |
| Employee Assessment | `employee_assessment` | `performance_employee_assessment` | textarea |  |  |  |
| Supervisor | `supervisor` | `performance_supervisor` | select |  |  | - Select Employee -=0, pwerpFirst Q Lastushu=7, pwerpFirst Q Lasthasu=8, pwerpFirst Q Lastlsdk=9, pwerpFirst Q Lastytlo=10, pwerpFirst LastProbe=6, erp_employee=5 |
| Supervisor Assessment | `supervisor_assessment` | `performance_supervisor_assessment` | textarea |  |  |  |


### `tmpl-erp-employment-performance-reviews`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+1)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Review Date * | `performance_date` | `performance_date` | text | yes |  |  |
| Reporting To | `reporting_to` | `performance_reporting_to` | select |  |  | - Select Employee -=0, pwerpFirst Q Lastushu=7, pwerpFirst Q Lasthasu=8, pwerpFirst Q Lastlsdk=9, pwerpFirst Q Lastytlo=10, pwerpFirst LastProbe=6, erp_employee=5 |
| Job Knowledge | `job_knowledge` | `performance_job_knowledge` | select |  |  | - Select -=0, Very Bad=1, Poor=2, Average=3, Good=4, Excellent=5 |
| Work Quality | `work_quality` | `performance_work_quality` | select |  |  | - Select -=0, Very Bad=1, Poor=2, Average=3, Good=4, Excellent=5 |
| Attendance/Punctuality | `attendance` | `performance_attendance` | select |  |  | - Select -=0, Very Bad=1, Poor=2, Average=3, Good=4, Excellent=5 |
| Communication/Listening | `communication` | `performance_communication` | select |  |  | - Select -=0, Very Bad=1, Poor=2, Average=3, Good=4, Excellent=5 |
| Dependability | `dependablity` | `performance_dependablity` | select |  |  | - Select -=0, Very Bad=1, Poor=2, Average=3, Good=4, Excellent=5 |


### `tmpl-erp-employment-status`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+1)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Date * | `date` | `date` | text | yes |  |  |
| Employee Status | `status` | `erp-hr-employee-status-option` | select |  |  | - Select -=0, Active=active, Inactive=inactive, Terminated=terminated, Deceased=deceased, Resigned=resigned |
| Comment | `comment` | `comment` | textarea |  | Optional comment |  |


### `tmpl-erp-employment-terminate`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+1)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Termination Date * | `terminate_date` | `terminate_date` | text | yes |  |  |
| Termination Type * | `termination_type` | `termination_type` | select | yes |  | - Select -=, Voluntary=voluntary, Involuntary=involuntary |
| Termination Reason * | `termination_reason` | `termination_reason` | select | yes |  | - Select -=, Attendance=attendance, Better Employment Conditions=better_employment, Career Prospect=career_prospect, Death=death, Desertion=desertion, Dismissed=dismissed, Dissatisfaction with the job=dissatisfaction, Higher Pay=higher_pay, Other Employment=other_employement, Personality Conflicts=personality_conflicts, Relocation=relocation … +1 |
| Eligible for Rehire * | `eligible_for_rehire` | `eligible_for_rehire` | select | yes |  | - Select -=, Yes=yes, No=no, Upon Review=upon_review |


### `tmpl-erp-employment-type`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+1)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Date * | `date` | `date` | text | yes |  |  |
| Employment Type | `type` | `type` | select |  |  | - Select -=0, Full Time=permanent, Part Time=parttime, On Contract=contract, Temporary=temporary, Trainee=trainee |
| Comment | `comment` | `comment` | textarea |  | Optional comment |  |


### `tmpl-erp-employment-type-history`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Date * | `date` | `date` | text | yes |  |  |
| Employment Type | `type` | `type` | select |  |  | - Select -=0, Full Time=permanent, Part Time=parttime, On Contract=contract, Temporary=temporary, Trainee=trainee |
| Comment | `comment` | `comment` | textarea |  | Optional comment |  |


### `tmpl-erp-employment-work-experience`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+1)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Previous Company * | `company_name` | `company_name` | text | yes | ABC Corporation |  |
| Job Title * | `job_title` | `job_title` | text | yes | Project Manager |  |
| From * | `from` | `from` | text | yes | 1988-03-18 |  |
| To * | `to` | `to` | text | yes | 1988-03-18 |  |
| Job Description | `description` | `description` | textarea |  | Details about the job |  |


### `tmpl-erp-hr-emp-add-asset`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Category * | `category_id` | `category_id` | select | yes |  | — Select Category —=-1 |
| Item Group * | `item_group` | `item_group` | select | yes |  | —Select Group—=-1 |
| Item Name * | `item` | `item` | select | yes |  | —Select Item—=-1 |
| Given Date * | `given_date` | `given_date` | text | yes |  |  |
| Returnable? | `is_returnable` | `is_returnable` | checkbox |  |  |  |
| Return Date | `return_date` | `return_date` | text |  |  |  |


### `tmpl-erp-hr-emp-request-asset`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Category * | `category_id` | `category_id` | select | yes |  | — Select Category —=-1 |
| Item Name * | `item_group` | `item_group` | select | yes |  | —Select Group—=-1 |
| If Unavailable | `not_in_list` | `not_in_list` | checkbox |  |  |  |
| Request Details | `request_desc` | `request_desc` | textarea |  |  |  |


### `tmpl-erp-hr-holiday-js-tmp`

- **Seen on:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Holiday Name * | `title` | `erp-hr-holiday-title` | text | yes |  |  |
| Start Date * | `start_date` | `erp-hr-holiday-start` | text | yes |  |  |
| Range | `range` | `erp-hr-holiday-range` | checkbox |  |  |  |
| End Date | `end_date` | `erp-hr-holiday-end` | text |  |  |  |
| Description | `description` | `erp-hr-holiday-description` | textarea |  |  |  |


### `tmpl-erp-hr-leave-approve-js-tmp`

- **Seen on:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`, `admin.php?page=erp-hr&section=leave&sub-section=leave-entitlements`, `admin.php?page=erp-hr&section=leave&sub-section=holidays` (+1)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Reason | `reason` | `erp-hr-leave-approve-reason` | textarea |  |  |  |


### `tmpl-erp-hr-leave-reject-js-tmp`

- **Seen on:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`, `admin.php?page=erp-hr&section=leave&sub-section=leave-entitlements`, `admin.php?page=erp-hr&section=leave&sub-section=holidays` (+1)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Reason * | `reason` | `erp-hr-leave-reject-reason` | textarea | yes |  |  |


### `tmpl-erp-leave-days`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+12)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | text |  |  |  |


### `tmpl-erp-make-wp-user`

- **Seen on:** `admin.php?page=erp-crm&section=contact`

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Enter your email | `customeresc_attr_email` | `make-wp-user-customer-email` | email | yes | Enter your email |  |
| Role | `customer_role` | `wp-user-role` | select |  |  | Shop manager=shop_manager, Customer=customer, Accounting Manager=erp_ac_manager, Employee=employee, Subscriber=subscriber, Contributor=contributor, Author=author, Editor=editor, Administrator=administrator |
| Send password | `send_password_notification` | `send-password-to-email` | checkbox |  |  |  |


### `tmpl-erp-new-dept`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+1)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Department Title * | `title` | `dept-title` | text | yes |  |  |
| Description | `dept-desc` | `dept-desc` | textarea |  | Optional |  |
| Department Manager ... | `lead` | `dept-lead` | select |  |  | - Select Employee -=0, pwerpFirst Q Lastushu=7, pwerpFirst Q Lasthasu=8, pwerpFirst Q Lastlsdk=9, pwerpFirst Q Lastytlo=10, pwerpFirst LastProbe=6, erp_employee=5 |
| — | `parent` | `dept-parent` | select |  |  | - Select Department -=-1, General Management=1, Operations Department=2, Finance Department=3, Sales Department=4, Human Resource Department=5, Purchase Department=6, Engineering Department=7, Production Department=8, Procurement Department=9, pwerp_dept_2vjg9u=24, pwerp_dept_n40ldm=33 … +2 |


### `tmpl-erp-new-desig`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+1)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Designation Title * | `title` | `desig-title` | text | yes |  |  |
| Description | `desig-desc` | `desig-desc` | textarea |  | Optional |  |


### `tmpl-erp-new-employee`

- **Seen on:** `admin.php?page=erp-hr&section=people&sub-section=employee`, `admin.php?page=erp-hr&section=people&sub-section=departments`, `admin.php?page=erp-hr&section=people&sub-section=designation` (+1)

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| First Name * | `personal[first_name]` | `first_name` | text | yes |  |  |
| Middle Name | `personal[middle_name]` | `middle_name` | text |  |  |  |
| Last Name * | `personal[last_name]` | `last_name` | text | yes |  |  |
| Employee ID | `personal[employee_id]` | `personal[employee_id]` | text |  |  |  |
| Email * | `user_email` | `erp-hr-user-email` | email | yes |  |  |
| Employee Type * | `work[type]` | `work[type]` | select | yes |  | - Select -=, Full Time=permanent, Part Time=parttime, On Contract=contract, Temporary=temporary, Trainee=trainee |
| Employee Status * | `work[status]` | `work[status]` | select | yes |  | - Select -=, Active=active, Inactive=inactive, Terminated=terminated, Deceased=deceased, Resigned=resigned |
| Employee End Date | `work[end_date]` | `work[end_date]` | text |  |  |  |
| Date of Hire * | `work[hiring_date]` | `work[hiring_date]` | text | yes |  |  |
| Department * | `work[department]` | `work[department]` | select | yes |  | - Select Department -=-1, General Management=1, Operations Department=2, Finance Department=3, Sales Department=4, Human Resource Department=5, Purchase Department=6, Engineering Department=7, Production Department=8, Procurement Department=9, pwerp_dept_2vjg9u=24, pwerp_dept_n40ldm=33 … +2 |
| Job Title * | `work[designation]` | `work[designation]` | select | yes |  | - Select Designation -=-1, Business Executive=18, Business Manager=10, CEO=3, Customer Support Executive=20, Engineer=16, Finance/Accounts Manager=12, Hiring Manager=14, Human Resource Manager=13, Junior Engineer=17, Managing Director=4, Marketing Executive=19 … +13 |
| Show Advanced Fields | `—` | `advanced_fields` | checkbox |  |  |  |
| Location | `work[location]` | `work[location]` | select |  |  | Main Location=-1 |
| Reporting To | `work[reporting_to]` | `work_reporting_to` | select |  |  | - Select Employee -=0, pwerpFirst Q Lastushu=7, pwerpFirst Q Lasthasu=8, pwerpFirst Q Lastlsdk=9, pwerpFirst Q Lastytlo=10, pwerpFirst LastProbe=6, erp_employee=5 |
| Source of Hire | `work[hiring_source]` | `work[hiring_source]` | select |  |  | - Select -=-1, Direct=direct, Referral=referral, Web=web, Newspaper=newspaper, Advertisement=advertisement, Social Network=social, Other=other |
| Pay Rate | `work[pay_rate]` | `work[pay_rate]` | text |  |  |  |
| Pay Type | `work[pay_type]` | `work[pay_type]` | select |  |  | - Select -=-1, Hourly=hourly, Daily=daily, Weekly=weekly, Biweekly=biweekly, Monthly=monthly, Contract=contract |
| Work Phone | `personal[work_phone]` | `personal[work_phone]` | text |  |  |  |
| Shift | `work[shift]` | `work[shift]` | select |  |  | - Select -=-1 |
| Blood Group | `personal[blood_group]` | `personal[blood_group]` | select |  |  | - Select -=-1, AB+=ab+, AB-=ab-, A+=a+, A-=a-, B+=b+, B-=b-, O+=o+, O-=o- |
| Spouse's name | `personal[spouse_name]` | `personal[spouse_name]` | text |  |  |  |
| Father's name | `personal[father_name]` | `personal[father_name]` | text |  |  |  |
| Mother's name | `personal[mother_name]` | `personal[mother_name]` | text |  |  |  |
| Mobile | `personal[mobile]` | `personal[mobile]` | text |  |  |  |
| Phone | `personal[phone]` | `personal[phone]` | text |  |  |  |
| Other Email | `personal[other_email]` | `personal[other_email]` | email |  |  |  |
| Date of Birth | `work[date_of_birth]` | `work[date_of_birth]` | text |  |  |  |
| Nationality | `personal[nationality]` | `personal[nationality]` | select |  |  | - Select -=-1, Åland Islands=AX, Afghanistan=AF, Albania=AL, Algeria=DZ, Andorra=AD, Angola=AO, Anguilla=AI, Antarctica=AQ, Antigua and Barbuda=AG, Argentina=AR, Armenia=AM … +233 |
| Gender | `personal[gender]` | `personal[gender]` | select |  |  | Male=male, Female=female, Other=other |
| Marital Status | `personal[marital_status]` | `personal[marital_status]` | select |  |  | Single=single, Married=married, Widowed=widowed |
| Driving License | `personal[driving_license]` | `personal[driving_license]` | text |  |  |  |
| Hobbies | `personal[hobbies]` | `personal[hobbies]` | text |  |  |  |
| Website | `personal[user_url]` | `personal[user_url]` | url |  |  |  |
| Address 1 | `personal[street_1]` | `personal[street_1]` | text |  |  |  |
| Address 2 | `personal[street_2]` | `personal[street_2]` | text |  |  |  |
| City | `personal[city]` | `personal[city]` | text |  |  |  |
| Country | `personal[country]` | `erp-popup-country` | select |  |  | - Select -=-1, - Select -=-1, Åland Islands=AX, Afghanistan=AF, Albania=AL, Algeria=DZ, Andorra=AD, Angola=AO, Anguilla=AI, Antarctica=AQ, Antigua and Barbuda=AG, Argentina=AR … +234 |
| Province / State | `personal[state]` | `erp-state` | select |  |  | - Select -= |
| Post Code/Zip Code | `personal[postal_code]` | `personal[postal_code]` | text |  |  |  |
| Biography | `personal[description]` | `personal[description]` | textarea |  |  |  |
| Notification | `user_notification` | `user_notification` | checkbox |  |  |  |
| — | `login_info` | `login_info` | checkbox |  |  |  |

