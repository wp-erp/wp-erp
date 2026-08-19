# Harness — hrm

Captured from the running site at `localhost:8888` (wp-erp 1.17.8 + erp-pro 1.7.0).

33 screens.

### admin.php?page=erp-hr&section=dashboard

- **URL:** `admin.php?page=erp-hr&section=dashboard`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| — | `—` | `—` | select |  |  | {{ i18n.today }}=today, {{ i18n.yesterday }}=yesterday, {{ i18n.thisMonth }}=this_month, {{ i18n.lastMonth }}=last_month, {{ i18n.thisQuarter }}=this_quarter, {{ i18n.thisYear }}=this_year |


### admin.php?page=erp-hr&section=people

- **URL:** `admin.php?page=erp-hr&section=people`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `Import`, `Export`, `Cancel`, `Reset`, `Show more details`
- **List columns** (`wp-list-table widefat fixed striped table-view-list employees`): Select All · Employee Name Sort ascending. · Designation · Department · Employment Type · Hire Date Sort ascending. · Status

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| Search: | `s` | `erp-employee-search-search-input` | search |  | Search Employee |  |
| Select bulk action | `action` | `bulk-action-selector-top` | select |  |  | Bulk actions=-1, Move to Trash=delete |
| — | `bulk_action` | `doaction` | submit |  |  |  |
| — | `filter_designation` | `filter_designation` | select |  |  | Designation=-1, Business Executive=18, Business Manager=10, CEO=3, Customer Support Executive=20, Engineer=16, Finance/Accounts Manager=12, Hiring Manager=14, Human Resource Manager=13, Junior Engineer=17, Managing Director=4, Marketing Executive=19 … +13 |
| — | `filter_department` | `filter_department` | select |  |  | Department=-1, General Management=1, Operations Department=2, Finance Department=3, Sales Department=4, Human Resource Department=5, Purchase Department=6, Engineering Department=7, Production Department=8, Procurement Department=9, pwerp_dept_2vjg9u=24, pwerp_dept_n40ldm=33 … +2 |
| — | `filter_employment_type` | `filter_employment_type` | select |  |  | Employment Type=-1, Full Time=permanent, Part Time=parttime, On Contract=contract, Temporary=temporary, Trainee=trainee |
| — | `hide_filter` | `—` | submit |  |  |  |
| — | `reset_filter` | `—` | submit |  |  |  |
| — | `filter_employee` | `filter` | submit |  |  |  |
| Current Page | `paged` | `current-page-selector` | text |  |  |  |
| Select All | `—` | `cb-select-all-1` | checkbox |  |  |  |
| — | `employee_id[]` | `—` | checkbox |  |  |  |
| — | `employee_id[]` | `—` | checkbox |  |  |  |
| — | `employee_id[]` | `—` | checkbox |  |  |  |
| — | `employee_id[]` | `—` | checkbox |  |  |  |
| — | `employee_id[]` | `—` | checkbox |  |  |  |
| — | `employee_id[]` | `—` | checkbox |  |  |  |
| Select All | `—` | `cb-select-all-2` | checkbox |  |  |  |
| Select bulk action | `action2` | `bulk-action-selector-bottom` | select |  |  | Bulk actions=-1, Move to Trash=delete |
| — | `bulk_action` | `doaction2` | submit |  |  |  |


### admin.php?page=erp-hr&section=people&sub-section=employee

- **URL:** `admin.php?page=erp-hr&section=people&sub-section=employee`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `Import`, `Export`, `Cancel`, `Reset`, `Show more details`
- **List columns** (`wp-list-table widefat fixed striped table-view-list employees`): Select All · Employee Name Sort ascending. · Designation · Department · Employment Type · Hire Date Sort ascending. · Status

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| Search: | `s` | `erp-employee-search-search-input` | search |  | Search Employee |  |
| Select bulk action | `action` | `bulk-action-selector-top` | select |  |  | Bulk actions=-1, Move to Trash=delete |
| — | `bulk_action` | `doaction` | submit |  |  |  |
| — | `filter_designation` | `filter_designation` | select |  |  | Designation=-1, Business Executive=18, Business Manager=10, CEO=3, Customer Support Executive=20, Engineer=16, Finance/Accounts Manager=12, Hiring Manager=14, Human Resource Manager=13, Junior Engineer=17, Managing Director=4, Marketing Executive=19 … +13 |
| — | `filter_department` | `filter_department` | select |  |  | Department=-1, General Management=1, Operations Department=2, Finance Department=3, Sales Department=4, Human Resource Department=5, Purchase Department=6, Engineering Department=7, Production Department=8, Procurement Department=9, pwerp_dept_2vjg9u=24, pwerp_dept_n40ldm=33 … +2 |
| — | `filter_employment_type` | `filter_employment_type` | select |  |  | Employment Type=-1, Full Time=permanent, Part Time=parttime, On Contract=contract, Temporary=temporary, Trainee=trainee |
| — | `hide_filter` | `—` | submit |  |  |  |
| — | `reset_filter` | `—` | submit |  |  |  |
| — | `filter_employee` | `filter` | submit |  |  |  |
| Current Page | `paged` | `current-page-selector` | text |  |  |  |
| Select All | `—` | `cb-select-all-1` | checkbox |  |  |  |
| — | `employee_id[]` | `—` | checkbox |  |  |  |
| — | `employee_id[]` | `—` | checkbox |  |  |  |
| — | `employee_id[]` | `—` | checkbox |  |  |  |
| — | `employee_id[]` | `—` | checkbox |  |  |  |
| — | `employee_id[]` | `—` | checkbox |  |  |  |
| — | `employee_id[]` | `—` | checkbox |  |  |  |
| Select All | `—` | `cb-select-all-2` | checkbox |  |  |  |
| Select bulk action | `action2` | `bulk-action-selector-bottom` | select |  |  | Bulk actions=-1, Move to Trash=delete |
| — | `bulk_action` | `doaction2` | submit |  |  |  |


### admin.php?page=erp-hr&section=people&sub-section=departments

- **URL:** `admin.php?page=erp-hr&section=people&sub-section=departments`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |


### admin.php?page=erp-hr&section=people&sub-section=designation

- **URL:** `admin.php?page=erp-hr&section=people&sub-section=designation`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `Next page›`, `Last page»`, `Show more details`
- **List columns** (`wp-list-table widefat fixed striped designation-list-table designations`): Select All · Title Sort descending. · No. of Employees

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| Select bulk action | `action` | `bulk-action-selector-top` | select |  |  | Bulk actions=-1, Delete=designation_delete |
| — | `bulk_action` | `doaction` | submit |  |  |  |
| Current Page | `paged` | `current-page-selector` | text |  |  |  |
| Select All | `—` | `cb-select-all-1` | checkbox |  |  |  |
| — | `desig[]` | `—` | checkbox |  |  |  |
| — | `desig[]` | `—` | checkbox |  |  |  |
| — | `desig[]` | `—` | checkbox |  |  |  |
| — | `desig[]` | `—` | checkbox |  |  |  |
| — | `desig[]` | `—` | checkbox |  |  |  |
| — | `desig[]` | `—` | checkbox |  |  |  |
| — | `desig[]` | `—` | checkbox |  |  |  |
| — | `desig[]` | `—` | checkbox |  |  |  |
| — | `desig[]` | `—` | checkbox |  |  |  |
| — | `desig[]` | `—` | checkbox |  |  |  |
| — | `desig[]` | `—` | checkbox |  |  |  |
| — | `desig[]` | `—` | checkbox |  |  |  |
| — | `desig[]` | `—` | checkbox |  |  |  |
| — | `desig[]` | `—` | checkbox |  |  |  |
| — | `desig[]` | `—` | checkbox |  |  |  |
| — | `desig[]` | `—` | checkbox |  |  |  |
| — | `desig[]` | `—` | checkbox |  |  |  |
| — | `desig[]` | `—` | checkbox |  |  |  |
| — | `desig[]` | `—` | checkbox |  |  |  |
| — | `desig[]` | `—` | checkbox |  |  |  |
| Select All | `—` | `cb-select-all-2` | checkbox |  |  |  |
| Select bulk action | `action2` | `bulk-action-selector-bottom` | select |  |  | Bulk actions=-1, Delete=designation_delete |
| — | `bulk_action` | `doaction2` | submit |  |  |  |


### admin.php?page=erp-hr&section=people&sub-section=location

- **URL:** `admin.php?page=erp-hr&section=people&sub-section=location`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |


### admin.php?page=erp-hr&section=leave

- **URL:** `admin.php?page=erp-hr&section=leave`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `Close dialog`

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
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


### admin.php?page=erp-hr&section=leave&sub-section=leave-requests

- **URL:** `admin.php?page=erp-hr&section=leave&sub-section=leave-requests`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `Close dialog`

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
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


### admin.php?page=erp-hr&section=leave&sub-section=leave-entitlements

- **URL:** `admin.php?page=erp-hr&section=leave&sub-section=leave-entitlements`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`
- **List columns** (`wp-list-table widefat fixed striped entitlement-list-table entitlements`): Employee Name Sort descending. · Leave Policy · Validity · Available · Spent

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |


### admin.php?page=erp-hr&section=leave&sub-section=holidays

- **URL:** `admin.php?page=erp-hr&section=leave&sub-section=holidays`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `Filter`
- **List columns** (`wp-list-table widefat fixed striped erp-leave-policy-list-table holiday`): Select All · Title · Start Date Sort descending. · End Date · Duration · Description

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| — | `from` | `—` | text |  | From date |  |
| — | `to` | `—` | text |  | To date |  |
| — | `filter` | `filter` | submit |  |  |  |
| Select All | `—` | `cb-select-all-1` | checkbox |  |  |  |
| Select All | `—` | `cb-select-all-2` | checkbox |  |  |  |
| — | `—` | `erp-ical-input` | file |  |  |  |


### admin.php?page=erp-hr&section=leave&sub-section=policies

- **URL:** `admin.php?page=erp-hr&section=leave&sub-section=policies`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`
- **List columns** (`wp-list-table widefat fixed striped erp-leave-policy-list-table leave_policies`): Policy Name Sort descending. · Year · Description · Days · Calendar Color · Type · Department · Designation · Location · Gender · Marital

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |


### admin.php?page=erp-hr&section=leave&sub-section=leave-calendar

- **URL:** `admin.php?page=erp-hr&section=leave&sub-section=leave-calendar`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `Filter`

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| — | `department` | `department` | select |  |  | - Select Department -=-1, General Management=1, Operations Department=2, Finance Department=3, Sales Department=4, Human Resource Department=5, Purchase Department=6, Engineering Department=7, Production Department=8, Procurement Department=9, pwerp_dept_2vjg9u=24, pwerp_dept_n40ldm=33 … +2 |
| — | `designation` | `designation` | select |  |  | - Select Designation -=-1, Business Executive=18, Business Manager=10, CEO=3, Customer Support Executive=20, Engineer=16, Finance/Accounts Manager=12, Hiring Manager=14, Human Resource Manager=13, Junior Engineer=17, Managing Director=4, Marketing Executive=19 … +13 |
| — | `erp_leave_calendar_filter` | `—` | submit |  |  |  |


### admin.php?page=erp-hr&section=asset&sub-section=asset

- **URL:** `admin.php?page=erp-hr&section=asset&sub-section=asset`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `Filter`
- **List columns** (`wp-list-table widefat fixed striped assets`): Select All · Item Name · Type · Category Sort descending. · Reg Date Sort descending. · Expiry Date Sort descending. · Warranty Till Sort descending. · Available/Total

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| — | `category_id` | `category` | select |  |  | — All Category —=-1 |
| — | `filter_category` | `filter_category` | submit |  |  |  |
| Select All | `—` | `cb-select-all-1` | checkbox |  |  |  |
| Select All | `—` | `cb-select-all-2` | checkbox |  |  |  |


### admin.php?page=erp-hr&section=asset&sub-section=asset-allottment

- **URL:** `admin.php?page=erp-hr&section=asset&sub-section=asset-allottment`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`
- **List columns** (`wp-list-table widefat fixed striped allottments`): Select All · Item Name · Model No · Asset Code · Given To · Given Date Sort descending. · Return Date · Status

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Item Name | `item_group-hide` | `item_group-hide` | checkbox |  |  |  |
| Model No | `model_no-hide` | `model_no-hide` | checkbox |  |  |  |
| Asset Code | `item_code-hide` | `item_code-hide` | checkbox |  |  |  |
| Given To | `alloted_to-hide` | `alloted_to-hide` | checkbox |  |  |  |
| Given Date | `date_given-hide` | `date_given-hide` | checkbox |  |  |  |
| Return Date | `date_return-hide` | `date_return-hide` | checkbox |  |  |  |
| Status | `status-hide` | `status-hide` | checkbox |  |  |  |
| Allottment | `wp_screen_options[value]` | `erp_assets_allott_per_page` | number |  |  |  |
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| Select All | `—` | `cb-select-all-1` | checkbox |  |  |  |
| Select All | `—` | `cb-select-all-2` | checkbox |  |  |  |


### admin.php?page=erp-hr&section=asset&sub-section=asset-request

- **URL:** `admin.php?page=erp-hr&section=asset&sub-section=asset-request`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`
- **List columns** (`wp-list-table widefat fixed striped requests`): Select All · Employee Name · Requested Category · Requested Item · Request Date Sort descending. · Given Item · Given Date Sort descending. · Status Sort descending.

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Employee Name | `employee_name-hide` | `employee_name-hide` | checkbox |  |  |  |
| Requested Category | `req_item_category-hide` | `req_item_category-hide` | checkbox |  |  |  |
| Requested Item | `req_item_name-hide` | `req_item_name-hide` | checkbox |  |  |  |
| Request Date | `date_requested-hide` | `date_requested-hide` | checkbox |  |  |  |
| Given Item | `given_item-hide` | `given_item-hide` | checkbox |  |  |  |
| Given Date | `date_replied-hide` | `date_replied-hide` | checkbox |  |  |  |
| Status | `status-hide` | `status-hide` | checkbox |  |  |  |
| Requests | `wp_screen_options[value]` | `erp_assets_request_per_page` | number |  |  |  |
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| Select All | `—` | `cb-select-all-1` | checkbox |  |  |  |
| Select All | `—` | `cb-select-all-2` | checkbox |  |  |  |


### admin.php?page=erp-hr&section=documents

- **URL:** `admin.php?page=erp-hr&section=documents`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `Upload`

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| — | `—` | `source` | select |  |  | Owned by me=owned_by_me, My Dropbox=my_dropbox, Shared with me=shared_with_me |
| — | `search_input` | `search_input` | text |  | Search |  |
| — | `btn_create_folder` | `btn_create_folder` | button |  |  |  |
| — | `btn_moveto_folder` | `btn_moveto_folder` | button |  |  |  |
| — | `btn_delete_folder` | `btn_delete_folder` | button |  |  |  |
| — | `btn_share` | `btn_share` | button |  |  |  |
| Check All | `—` | `checkall` | checkbox |  |  |  |
| — | `—` | `—` | checkbox |  |  |  |
| — | `—` | `—` | checkbox |  |  |  |


### admin.php?page=erp-hr&section=recruitment&sub-section=job-opening

- **URL:** `admin.php?page=erp-hr&section=recruitment&sub-section=job-opening`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`
- **List columns** (`wp-list-table widefat fixed striped table-view-list recruitments`): Select All · Job Title Sort descending. · Applicants Sort descending. · Status · Created On · Expire Date · Publish Date · Action

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| Select All | `—` | `cb-select-all-1` | checkbox |  |  |  |
| Select All | `—` | `cb-select-all-2` | checkbox |  |  |  |


### admin.php?page=erp-hr&section=recruitment&sub-section=add-opening

- **URL:** `admin.php?page=erp-hr&section=recruitment&sub-section=add-opening`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `Next →`

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| — | `opening_title` | `opening_title` | text |  |  |  |
| — | `opening_description` | `opening_description` | textarea |  |  |  |
| — | `create_opening` | `create_opening` | submit |  |  |  |
| URL | `—` | `wp-link-url` | text |  |  |  |
| Link Text | `—` | `wp-link-text` | text |  |  |  |
| Open link in a new tab | `—` | `wp-link-target` | checkbox |  |  |  |
| Search | `—` | `wp-link-search` | search |  |  |  |
| — | `wp-link-submit` | `wp-link-submit` | submit |  |  |  |


### admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list

- **URL:** `admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `Filter`, `Download All CV`
- **List columns** (`wp-list-table widefat fixed striped table-view-list jobseekers`): Select All · Name Sort descending. · Stage Sort descending. · Rating Sort descending. · Date Sort descending. · Action

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| — | `filter_status` | `filter_status_select` | select |  |  | - Select All -=-1, Rejected=rejected, Withdrawn=withdrawn, Declined Offer=decline_offer |
| — | `filter_status_button` | `filter_status_button` | submit |  |  |  |
| — | `download_all_cv` | `download_all_cv` | submit |  |  |  |
| Select All | `—` | `cb-select-all-1` | checkbox |  |  |  |
| Select All | `—` | `cb-select-all-2` | checkbox |  |  |  |


### admin.php?page=erp-hr&section=recruitment&sub-section=add_candidate

- **URL:** `admin.php?page=erp-hr&section=recruitment&sub-section=add_candidate`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `Show form`

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| — | `job_id` | `cjob_id` | select |  |  | -- select --= |


### admin.php?page=erp-hr&section=recruitment&sub-section=stages

- **URL:** `admin.php?page=erp-hr&section=recruitment&sub-section=stages`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `Edit`, `Delete`
- **List columns** (`wp-list-table widefat fixed striped stages-table`): Stage Name · Jobs Using · Candidates · Actions

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |


### admin.php?page=erp-hr&section=recruitment&sub-section=reports

- **URL:** `admin.php?page=erp-hr&section=recruitment&sub-section=reports`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `Generate`
- **List columns** (`wp-list-table widefat fixed striped table-rec-reports`): Opening · Created · # Candidates Added · How are the candidates distributed · In Process · Archived · Unscreened · Other

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |


### admin.php?page=erp-hr&section=report

- **URL:** `admin.php?page=erp-hr&section=report`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `View Report`

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |


### admin.php?page=erp-hr&section=people&sub-section=employee&action=view

- **URL:** `admin.php?page=erp-hr&section=people&sub-section=employee&action=view`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`
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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |


### admin.php?page=erp-hr&section=recruitment&sub-section=todo-calendar

- **URL:** `admin.php?page=erp-hr&section=recruitment&sub-section=todo-calendar`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`
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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |


### admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis

- **URL:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-analysis`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `Clear Filters`, `Card View`, `List View`, `Previous`, `Next`, `Export as CSV`, `Export as JSON`
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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| — | `—` | `search-candidate` | text |  | Search candidates... |  |
| — | `—` | `filter-job` | select |  |  | All Jobs= |
| — | `—` | `filter-score` | select |  |  | All Scores=, 90-100 (Excellent)=90-100, 80-89 (Very Good)=80-89, 70-79 (Good)=70-79, 60-69 (Average)=60-69, 0-59 (Below Average)=0-59, ❌ Failed to Score=failed |
| — | `—` | `filter-skills` | select |  |  | All Skills Levels=, Excellent (90-100%)=90-100, Good (70-89%)=70-89, Average (50-69%)=50-69, Poor (0-49%)=0-49 |
| — | `—` | `filter-experience-type` | select |  |  | Role-Specific Experience=relevant, Total Experience=total |
| — | `—` | `filter-experience` | select |  |  | All Experience Levels=, Fresh (0-1 years)=0-1, Junior (1-3 years)=1-3, Mid-level (3-5 years)=3-5, Senior (5-8 years)=5-8, Expert (8+ years)=8-999 |
| Select All | `—` | `select-all-candidates` | checkbox |  |  |  |
| Select All | `—` | `bulk-action-selector` | select |  |  | Bulk Actions=, Reprocess Selected CVs=reprocess |
| Auto-refresh | `—` | `auto-refresh-toggle` | checkbox |  |  |  |
| All Candidates | `—` | `—` | checkbox |  |  |  |
| Filtered Results Only | `—` | `—` | checkbox |  |  |  |


### admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer

- **URL:** `admin.php?page=erp-hr&section=recruitment&sub-section=erp-rec-ai-job-writer`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `Generate Job Description`, `Clear Form`, `Save as Job Post`, `Regenerate`, `Copy to Clipboard`
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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| Job Title * | `job_title` | `job_title` | text | yes |  |  |
| Company Name | `company_name` | `company_name` | text |  |  |  |
| Department | `department` | `department` | select |  |  | Select Department=, General Management=1, Operations Department=2, Finance Department=3, Sales Department=4, Human Resource Department=5, Purchase Department=6, Engineering Department=7, Production Department=8, Procurement Department=9, pwerp_dept_2vjg9u=24, pwerp_dept_n40ldm=33 … +2 |
| Minimum Experience | `experience_level` | `experience_level` | select |  |  | Select Minimum Experience=, Fresher=Fresher, 1 Year=1 Year, 2 Years=2 Years, 3 Years=3 Years, 4 Years=4 Years, 5 Years=5 Years, 6 Years=6 Years, 7 Years=7 Years, 8 Years=8 Years, 9 Years=9 Years, 10 Years=10 Years … +1 |
| Employment Type | `employment_type` | `employment_type` | select |  |  | Select Employment Type=, Full Time=permanent, Part Time=parttime, On Contract=contract, Temporary=temporary, Trainee=trainee |
| Location | `location` | `location` | text |  | e.g., New York, NY or Remote |  |
| Salary Range | `salary_range` | `salary_range` | text |  | e.g., $50,000 - $70,000 |  |
| Key Responsibilities | `key_responsibilities` | `key_responsibilities` | textarea |  | Describe the main responsibilities of this role... |  |
| Required Skills | `required_skills` | `required_skills` | textarea |  | List the essential skills and qualifications... |  |
| Preferred Skills (Optional) | `preferred_skills` | `preferred_skills` | textarea |  | List any preferred or nice-to-have skills... |  |
| Education Requirements | `education_requirements` | `education_requirements` | textarea |  | Specify education requirements... |  |
| Company Culture (Optional) | `company_culture` | `company_culture` | textarea |  | Describe your company culture and values... |  |
| Benefits & Perks (Optional) | `benefits` | `benefits` | textarea |  | List benefits and perks offered... |  |


### admin.php?page=erp-hr&section=report&sub-section=report&type=age-profile

- **URL:** `admin.php?page=erp-hr&section=report&sub-section=report&type=age-profile`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`
- **Tabs:** Overview · People · Leave · Payroll · Attendance · Assets · Documents · Training · Recruitment · Reports · Help · Contacts · Tasks · Deals · Integrations · Dashboard · Users · Transactions · Products · Settings · Requests · Leave Entitlements · Holidays · Policies · Calendar · Pay Calendar · Pay Run List · Bulk pay item edit · Shifts · Tools · Assign Bulk Shift · Allotments · Job Opening · Add Opening · Question Sets · Candidates · Add candidate · Stages · AI Talent Pool · AI Job Settings · AI Job Writer · AI Model Settings · Age Profile · Salary History · Gender Profile · Years of Service · Head Count · Leaves · Attendance (Date Based) · Attendance (Employee Based)
- **List columns** (`widefat striped erp-report-chart`): Department · Under 18 year · 18 to 25 year · 26 to 35 year · 36 to 45 year · 46 to 55 year · 56 to 65 year · 65+ year

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |


### admin.php?page=erp-hr&section=report&sub-section=report&type=salary-history

- **URL:** `admin.php?page=erp-hr&section=report&sub-section=report&type=salary-history`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`
- **Tabs:** Overview · People · Leave · Payroll · Attendance · Assets · Documents · Training · Recruitment · Reports · Help · Contacts · Tasks · Deals · Integrations · Dashboard · Users · Transactions · Products · Settings · Requests · Leave Entitlements · Holidays · Policies · Calendar · Pay Calendar · Pay Run List · Bulk pay item edit · Shifts · Tools · Assign Bulk Shift · Allotments · Job Opening · Add Opening · Question Sets · Candidates · Add candidate · Stages · AI Talent Pool · AI Job Settings · AI Job Writer · AI Model Settings · Age Profile · Salary History · Gender Profile · Years of Service · Head Count · Leaves · Attendance (Date Based) · Attendance (Employee Based)
- **List columns** (`widefat striped`): Employee · Date · Pay Rate · Pay type · Employee ID

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |


### admin.php?page=erp-hr&section=report&sub-section=report&type=headcount

- **URL:** `admin.php?page=erp-hr&section=report&sub-section=report&type=headcount`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `Filter`
- **Tabs:** Overview · People · Leave · Payroll · Attendance · Assets · Documents · Training · Recruitment · Reports · Help · Contacts · Tasks · Deals · Integrations · Dashboard · Users · Transactions · Products · Settings · Requests · Leave Entitlements · Holidays · Policies · Calendar · Pay Calendar · Pay Run List · Bulk pay item edit · Shifts · Tools · Assign Bulk Shift · Allotments · Job Opening · Add Opening · Question Sets · Candidates · Add candidate · Stages · AI Talent Pool · AI Job Settings · AI Job Writer · AI Model Settings · Age Profile · Salary History · Gender Profile · Years of Service · Head Count · Leaves · Attendance (Date Based) · Attendance (Employee Based)
- **List columns** (`widefat striped`): Name · Hire Date · Job Title · Department · Location · Status

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| — | `year` | `—` | select |  |  | -Select Year-=-1, 2026=2026 |
| — | `department` | `—` | select |  |  | - Select Department -=-1, General Management=1, Operations Department=2, Finance Department=3, Sales Department=4, Human Resource Department=5, Purchase Department=6, Engineering Department=7, Production Department=8, Procurement Department=9, pwerp_dept_2vjg9u=24, pwerp_dept_n40ldm=33 … +2 |


### admin.php?page=erp-hr&section=report&sub-section=report&type=leaves

- **URL:** `admin.php?page=erp-hr&section=report&sub-section=report&type=leaves`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `Filter`, `Show more details`
- **Tabs:** Overview · People · Leave · Payroll · Attendance · Assets · Documents · Training · Recruitment · Reports · Help · Contacts · Tasks · Deals · Integrations · Dashboard · Users · Transactions · Products · Settings · Requests · Leave Entitlements · Holidays · Policies · Calendar · Pay Calendar · Pay Run List · Bulk pay item edit · Shifts · Tools · Assign Bulk Shift · Allotments · Job Opening · Add Opening · Question Sets · Candidates · Add candidate · Stages · AI Talent Pool · AI Job Settings · AI Job Writer · AI Model Settings · Age Profile · Salary History · Gender Profile · Years of Service · Head Count · Leaves · Attendance (Date Based) · Attendance (Employee Based)
- **List columns** (`wp-list-table widefat fixed striped leaves`): Name

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| Filter by Designation | `filter_year` | `filter_year` | select |  |  | Select year=, Custom=custom |
| Filter by Designation | `filter_designation` | `filter_designation` | select |  |  | - Select Designation -=-1, Business Executive=18, Business Manager=10, CEO=3, Customer Support Executive=20, Engineer=16, Finance/Accounts Manager=12, Hiring Manager=14, Human Resource Manager=13, Junior Engineer=17, Managing Director=4, Marketing Executive=19 … +13 |
| Filter by Designation | `filter_department` | `filter_department` | select |  |  | - Select Department -=-1, General Management=1, Operations Department=2, Finance Department=3, Sales Department=4, Human Resource Department=5, Purchase Department=6, Engineering Department=7, Production Department=8, Procurement Department=9, pwerp_dept_2vjg9u=24, pwerp_dept_n40ldm=33 … +2 |
| Filter by Designation | `filter_employment_type` | `filter_employment_type` | select |  |  | - Select Employment Type -=-1, Full Time=permanent, Part Time=parttime, On Contract=contract, Temporary=temporary, Trainee=trainee |
| Filter by Designation | `filter_leave_report` | `filter_leave_report` | submit |  |  |  |
| Current Page | `paged` | `current-page-selector` | text |  |  |  |


### admin.php?page=erp-hr&section=report&sub-section=report&type=asset-report

- **URL:** `admin.php?page=erp-hr&section=report&sub-section=report&type=asset-report`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `Filter`
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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| — | `query_time` | `asset-reporting-query` | select |  |  | — All Time —=-1, This Month=this_month, Last Month=last_month, This Quarter=this_quarter, Last Quarter=last_quarter, This Year=this_year, last Year=last_year |
| — | `category` | `—` | select |  |  | — All Categories —=-1 |


### admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report

- **URL:** `admin.php?page=erp-hr&section=report&sub-section=report&type=attendance-report`
- **Heading:** HR
- **Buttons:** `Apply`, `Screen Options`, `Filter`
- **Tabs:** Overview · People · Leave · Payroll · Attendance · Assets · Documents · Training · Recruitment · Reports · Help · Contacts · Tasks · Deals · Integrations · Dashboard · Users · Transactions · Products · Settings · Requests · Leave Entitlements · Holidays · Policies · Calendar · Pay Calendar · Pay Run List · Bulk pay item edit · Shifts · Tools · Assign Bulk Shift · Allotments · Job Opening · Add Opening · Question Sets · Candidates · Add candidate · Stages · AI Talent Pool · AI Job Settings · AI Job Writer · AI Model Settings · Age Profile · Salary History · Gender Profile · Years of Service · Head Count · Leaves · Attendance (Date Based) · Attendance (Employee Based)
- **List columns** (`widefat striped`): Date · Total · Present · Leave · Absent · Comment

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
| — | `screen-options-apply` | `screen-options-apply` | submit |  |  |  |
| — | `location` | `—` | select |  |  | Main Location=-1 |
| — | `department` | `—` | select |  |  | - Select Department -=-1, General Management=1, Operations Department=2, Finance Department=3, Sales Department=4, Human Resource Department=5, Purchase Department=6, Engineering Department=7, Production Department=8, Procurement Department=9, pwerp_dept_2vjg9u=24, pwerp_dept_n40ldm=33 … +2 |
| — | `query_time` | `att-reporting-query` | select |  |  | This Month=this_month, Last Month=last_month, This Quarter=this_quarter, Last Quarter=last_quarter, This Year=this_year, Last Year=last_year, Custom=custom |

