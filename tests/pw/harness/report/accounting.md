# Harness — accounting (Vue SPA)

Hash-routed app at `admin.php?page=erp-accounting`. Routes walked in-page.

31 routes.

### #/dashboard

- **URL:** `admin.php?page=erp-accounting#/dashboard`
- **Heading:** Accounting
- **Buttons:** `More`

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `query_time` | `att-filter-duration` | select |  |  | This Month=this_month, Last Month=last_month, This Quarter=this_quarter, Last Quarter=last_quarter, This Year=this_year, Last Year=last_year |


### #/users/customers

- **URL:** `admin.php?page=erp-accounting#/users/customers`
- **Heading:** Accounting
- **Buttons:** `More`, `Import`, `Export`, `Search`
- **List columns** (`wperp-table people-table table-striped table-dark`): Name · Company · Email · Phone · Actions

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | search |  | Search People |  |
| — | `—` | `—` | checkbox |  |  |  |
| — | `—` | `—` | checkbox |  |  |  |


### #/users/vendors

- **URL:** `admin.php?page=erp-accounting#/users/vendors`
- **Heading:** Accounting
- **Buttons:** `More`, `Import`, `Export`, `Search`
- **List columns** (`wperp-table people-table table-striped table-dark`): Name · Company · Email · Phone · Actions

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | search |  | Search People |  |
| — | `—` | `—` | checkbox |  |  |  |
| — | `—` | `—` | checkbox |  |  |  |


### #/users/employees

- **URL:** `admin.php?page=erp-accounting#/users/employees`
- **Heading:** Accounting
- **Buttons:** `More`, `Show more details`
- **List columns** (`wperp-table table-striped table-dark`): Name · Designation · Department · Email · Phone

_No form fields on this screen._


### #/transactions/sales

- **URL:** `admin.php?page=erp-accounting#/transactions/sales`
- **Heading:** Accounting
- **Buttons:** `More`, `Submit`
- **List columns** (`wperp-table table-striped table-dark widefat table2 transact`): Voucher No. · Type · Ref · Customer · Trn Date · Due Date · Balance · Total · Status

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | input |  |  |  |
| — | `—` | `—` | input |  |  |  |
| — | `—` | `—` | select |  |  | All=, Draft=1, Awaiting Payment=2, Pending=3, Paid=4, Partially Paid=5, Approved=6, Closed=7, Void=8, Returned=9, Partially Returned=10 |
| — | `—` | `—` | select |  |  | All=, Invoice=invoice, Receive=payment, Payment=return_payment, Estimate=estimate |
| — | `—` | `—` | button |  |  |  |
| — | `—` | `—` | reset |  |  |  |
| — | `—` | `—` | submit |  |  |  |
| — | `—` | `—` | checkbox |  |  |  |
| — | `—` | `—` | checkbox |  |  |  |


### #/transactions/expenses

- **URL:** `admin.php?page=erp-accounting#/transactions/expenses`
- **Heading:** Accounting
- **Buttons:** `More`, `Submit`
- **List columns** (`wperp-table table-striped table-dark widefat table2 transact`): Voucher No. · Type · Ref · People · Trn Date · Due Date · Due · Total · Status

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | input |  |  |  |
| — | `—` | `—` | input |  |  |  |
| — | `—` | `—` | select |  |  | All=, Draft=1, Awaiting Payment=2, Pending=3, Paid=4, Partially Paid=5, Approved=6, Closed=7, Void=8, Returned=9, Partially Returned=10 |
| — | `—` | `—` | select |  |  | All=, Expense=expense, Bill=bill, Bill Payment=pay_bill, Check=check |
| — | `—` | `—` | select |  |  | All=, pwerpFirst Lastytlo=5, pwerpFirst Lastlsdk=4, pwerpFirst Lasthasu=3, pwerpFirst Lastushu=2, pwerpFirst LastProbe=1 |
| — | `—` | `—` | button |  |  |  |
| — | `—` | `—` | reset |  |  |  |
| — | `—` | `—` | submit |  |  |  |
| — | `—` | `—` | checkbox |  |  |  |
| — | `—` | `—` | checkbox |  |  |  |


### #/transactions/purchases

- **URL:** `admin.php?page=erp-accounting#/transactions/purchases`
- **Heading:** Accounting
- **Buttons:** `More`, `Submit`
- **List columns** (`wperp-table table-striped table-dark widefat table2 transact`): Voucher No. · Type · Ref · Customer · Trn Date · Due Date · Balance · Total · Status

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | input |  |  |  |
| — | `—` | `—` | input |  |  |  |
| — | `—` | `—` | select |  |  | All=, Draft=1, Awaiting Payment=2, Pending=3, Paid=4, Partially Paid=5, Approved=6, Closed=7, Void=8, Returned=9, Partially Returned=10 |
| — | `—` | `—` | select |  |  | All=, Purchase=purchase, Payment=pay_purchase, Receive=receive_pay_purchase |
| — | `—` | `—` | button |  |  |  |
| — | `—` | `—` | reset |  |  |  |
| — | `—` | `—` | submit |  |  |  |
| — | `—` | `—` | checkbox |  |  |  |
| — | `—` | `—` | checkbox |  |  |  |


### #/transactions/journals

- **URL:** `admin.php?page=erp-accounting#/transactions/journals`
- **Heading:** Accounting
- **Buttons:** `More`
- **List columns** (`wp-ListTable widefat fixed journal-list`): Voucher No. · Date · Particulars · Amount

_No form fields on this screen._


### #/transactions/reimbursements

- **URL:** `admin.php?page=erp-accounting#/transactions/reimbursements`
- **Heading:** Accounting
- **Buttons:** `More`
- **List columns** (`wperp-table table-striped table-dark widefat reimbursement-l`): Voucher No · People Name · Amount · Transaction Date · Voucher Type

_No form fields on this screen._


### #/products/product-service

- **URL:** `admin.php?page=erp-accounting#/products/product-service`
- **Heading:** Accounting
- **Buttons:** `More`, `Import`, `Export`, `Search`
- **List columns** (`wperp-table table-striped table-dark widefat table2 product-`): Product Name · Sale Price · Cost Price · Product Category · Tax Category · Product Type · Vendor · Actions

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | search |  | Search Products |  |
| — | `—` | `—` | checkbox |  |  |  |
| — | `—` | `—` | checkbox |  |  |  |


### #/products/product-categories

- **URL:** `admin.php?page=erp-accounting#/products/product-categories`
- **Heading:** Accounting
- **Buttons:** `More`, `Save`
- **List columns** (`wp-list-table widefat fixed striped`): Category Name · Actions

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| Category Name | `—` | `—` | text |  |  |  |
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | submit |  |  |  |


### #/products/inventory

- **URL:** `admin.php?page=erp-accounting#/products/inventory`
- **Heading:** Accounting
- **Buttons:** `More`
- **List columns** (`wperp-table table-striped table-dark widefat table2 inventor`): Product Name · Sale Price · Cost Price · Stock · Product Category · Tax Category · Vendor

_No form fields on this screen._


### #/settings/charts

- **URL:** `admin.php?page=erp-accounting#/settings/charts`
- **Heading:** Accounting
- **Buttons:** `More`, `Show more details`
- **List columns** (`wperp-table table-striped table-dark widefat table2 chart-li`): Code · Name · Balance · Count · Actions
- **List columns** (`wperp-table table-striped table-dark widefat table2 chart-li`): Code · Name · Balance · Count · Actions
- **List columns** (`wperp-table table-striped table-dark widefat table2 chart-li`): Code · Name · Balance · Count · Actions
- **List columns** (`wperp-table table-striped table-dark widefat table2 chart-li`): Code · Name · Balance · Count · Actions
- **List columns** (`wperp-table table-striped table-dark widefat table2 chart-li`): Code · Name · Balance · Count · Actions
- **List columns** (`wperp-table table-striped table-dark widefat table2 chart-li`): Code · Name · Balance · Count · Actions
- **List columns** (`wperp-table table-striped table-dark widefat table2 chart-li`): Code · Name · Balance · Count · Actions

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | text |  |  |  |
| — | `paged` | `—` | text |  |  |  |
| — | `paged` | `—` | text |  |  |  |
| — | `paged` | `—` | text |  |  |  |


### #/settings/banks

- **URL:** `admin.php?page=erp-accounting#/settings/banks`
- **Heading:** Accounting
- **Buttons:** `More`

_No form fields on this screen._


### #/settings/taxes/tax-rates

- **URL:** `admin.php?page=erp-accounting#/settings/taxes/tax-rates`
- **Heading:** Accounting
- **Buttons:** `More`
- **List columns** (`wp-ListTable widefat fixed tax-rate-list wperp-table table-s`): Tax Zone Name · Actions

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | checkbox |  |  |  |
| — | `—` | `—` | checkbox |  |  |  |


### #/settings/taxes/tax-records

- **URL:** `admin.php?page=erp-accounting#/settings/taxes/tax-records`
- **Heading:** Accounting
- **Buttons:** `More`
- **List columns** (`wp-ListTable widefat fixed tax-records-list wperp-table tabl`): Voucher No · Agency · Date · Amount · Actions

_No form fields on this screen._


### #/reports

- **URL:** `admin.php?page=erp-accounting#/reports`
- **Heading:** Accounting
- **Buttons:** `More`

_No form fields on this screen._


### #/opening-balance

- **URL:** `admin.php?page=erp-accounting#/opening-balance`
- **Heading:** Accounting
- **Buttons:** `More`, `Add Agency`, `Save`
- **List columns** (`wperp-table wperp-form-table erp-accordion-expand-body`): Agency · Debit · Credit
- **List columns** (`wperp-table wperp-form-table erp-accordion-expand-body`): Account · Debit · Credit
- **List columns** (`wperp-table wperp-form-table erp-accordion-expand-body`): Account · Debit · Credit
- **List columns** (`wperp-table wperp-form-table erp-accordion-expand-body`): Account · Debit · Credit
- **List columns** (`wperp-table wperp-form-table erp-accordion-expand-body`): Account · Debit · Credit

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | number |  |  |  |
| — | `—` | `—` | text |  |  |  |
| — | `—` | `—` | text |  |  |  |
| Description | `—` | `—` | textarea |  | Internal Information |  |


### #/invoices/new

- **URL:** `admin.php?page=erp-accounting#/invoices/new`
- **Heading:** Accounting
- **Buttons:** `More`, `Add Line`, `Bold`, `Italic`, `Strikethrough`, `Link`, `Heading`, `Quote`, `Code`, `Bullets`, `Numbers`, `Decrease Level`, `Increase Level`, `Attach Files`, `Undo`, `Redo`, `Save`, `Save and New`, `Save as Draft`
- **List columns** (`wperp-table wperp-form-table`): Product/Service · Qty · Unit Price · Amount · Tax

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | input |  |  |  |
| — | `—` | `—` | input |  |  |  |
| Billing Address | `—` | `—` | textarea |  | Type here |  |
| — | `—` | `—` | text |  | Please search |  |
| Please search Oops! No elements found. List is empty. | `qty` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | checkbox |  |  |  |
| — | `—` | `—` | text |  | Please search |  |
| Please search Oops! No elements found. List is empty. | `qty` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | checkbox |  |  |  |
| — | `—` | `—` | text |  | Please search |  |
| Please search Oops! No elements found. List is empty. | `qty` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | checkbox |  |  |  |
| — | `—` | `—` | select |  |  | Discount percent=discount-percent, Discount value=discount-value |
| — | `—` | `—` | text |  | discount-percent |  |
| — | `—` | `—` | text |  | Select sales tax |  |
| — | `—` | `—` | text |  |  |  |
| — | `—` | `—` | text |  |  |  |
| Particulars | `—` | `—` | textarea |  | Particulars |  |
| Select files | `attachments[]` | `attachment` | file |  |  |  |
| — | `href` | `—` | url | yes | Enter a URL… |  |
| — | `—` | `—` | button |  |  |  |
| — | `—` | `—` | button |  |  |  |


### #/estimates/new

- **URL:** `admin.php?page=erp-accounting#/estimates/new`
- **Heading:** Accounting
- **Buttons:** `More`, `Add Line`, `Bold`, `Italic`, `Strikethrough`, `Link`, `Heading`, `Quote`, `Code`, `Bullets`, `Numbers`, `Decrease Level`, `Increase Level`, `Attach Files`, `Undo`, `Redo`, `Save`, `Save and New`, `Save as Draft`
- **List columns** (`wperp-table wperp-form-table`): Product/Service · Qty · Unit Price · Amount · Tax

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | input |  |  |  |
| — | `—` | `—` | input |  |  |  |
| Billing Address | `—` | `—` | textarea |  | Type here |  |
| — | `—` | `—` | text |  | Please search |  |
| Please search Oops! No elements found. List is empty. | `qty` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | checkbox |  |  |  |
| — | `—` | `—` | text |  | Please search |  |
| Please search Oops! No elements found. List is empty. | `qty` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | checkbox |  |  |  |
| — | `—` | `—` | text |  | Please search |  |
| Please search Oops! No elements found. List is empty. | `qty` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | checkbox |  |  |  |
| — | `—` | `—` | select |  |  | Discount percent=discount-percent, Discount value=discount-value |
| — | `—` | `—` | text |  | discount-percent |  |
| — | `—` | `—` | text |  | Select sales tax |  |
| — | `—` | `—` | text |  |  |  |
| — | `—` | `—` | text |  |  |  |
| Particulars | `—` | `—` | textarea |  | Particulars |  |
| Select files | `attachments[]` | `attachment` | file |  |  |  |
| — | `href` | `—` | url | yes | Enter a URL… |  |
| — | `—` | `—` | button |  |  |  |
| — | `—` | `—` | button |  |  |  |


### #/payments/new

- **URL:** `admin.php?page=erp-accounting#/payments/new`
- **Heading:** Accounting
- **Buttons:** `More`, `Save`, `Save and New`, `Save as Draft`
- **List columns** (`wperp-table wperp-form-table`): Voucher No · Due Date · Total · Balance · Amount

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | text |  | Please search |  |
| Reference | `—` | `—` | text |  |  |  |
| — | `—` | `—` | input |  |  |  |
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | text |  | Select Account |  |
| Billing Address | `—` | `—` | textarea |  | Type here |  |
| — | `finalamount` | `—` | text |  |  |  |
| Particulars | `—` | `—` | textarea |  | Internal Information |  |
| Select files | `attachments[]` | `attachment` | file |  |  |  |


### #/bills/new

- **URL:** `admin.php?page=erp-accounting#/bills/new`
- **Heading:** Accounting
- **Buttons:** `More`, `Add Line`, `Save`, `Save and New`, `Save as Draft`
- **List columns** (`wperp-table wperp-form-table`): SL No. · Account · Description · Amount · Total

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | input |  |  |  |
| — | `—` | `—` | input |  |  |  |
| Reference No | `—` | `—` | text |  |  |  |
| Billing Address | `—` | `—` | textarea |  | Type here |  |
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | textarea |  | Particulars |  |
| — | `amount` | `—` | text |  |  |  |
| — | `—` | `—` | text |  |  |  |
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | textarea |  | Particulars |  |
| — | `amount` | `—` | text |  |  |  |
| — | `—` | `—` | text |  |  |  |
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | textarea |  | Particulars |  |
| — | `amount` | `—` | text |  |  |  |
| — | `—` | `—` | text |  |  |  |
| — | `finalamount` | `—` | text |  |  |  |
| Particulars | `—` | `—` | textarea |  | Internal Information |  |
| Select files | `attachments[]` | `attachment` | file |  |  |  |


### #/pay-bills/new

- **URL:** `admin.php?page=erp-accounting#/pay-bills/new`
- **Heading:** Accounting
- **Buttons:** `More`, `Save`, `Save and New`, `Save as Draft`
- **List columns** (`wperp-table wperp-form-table`): Bill No · Due Date · Total · Due · Amount

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | text |  | Please search |  |
| Reference NO | `—` | `—` | text |  |  |  |
| — | `—` | `—` | input |  |  |  |
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | text |  | Select Account |  |
| Billing Address | `—` | `—` | textarea |  | Type here |  |
| — | `—` | `—` | text |  |  |  |
| Particulars | `—` | `—` | textarea |  | Internal Information |  |
| Select files | `attachments[]` | `attachment` | file |  |  |  |


### #/purchase-orders/new

- **URL:** `admin.php?page=erp-accounting#/purchase-orders/new`
- **Heading:** Accounting
- **Buttons:** `More`, `Add Line`, `Save`, `Save and New`, `Save as Draft`
- **List columns** (`wperp-table wperp-form-table`): Product/Service · Qty · Unit Price · Amount · VAT

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | input |  |  |  |
| — | `—` | `—` | input |  |  |  |
| Reference No | `—` | `—` | text |  |  |  |
| Billing Address | `—` | `—` | textarea |  | Type here |  |
| — | `—` | `—` | text |  | Please search |  |
| Please search Oops! No elements found. List is empty. | `qty` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | checkbox |  |  |  |
| — | `—` | `—` | text |  | Please search |  |
| Please search Oops! No elements found. List is empty. | `qty` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | checkbox |  |  |  |
| — | `—` | `—` | text |  | Please search |  |
| Please search Oops! No elements found. List is empty. | `qty` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | checkbox |  |  |  |
| — | `—` | `—` | text |  | Select Purchase Vat Zone |  |
| — | `—` | `—` | text |  |  |  |
| — | `—` | `—` | text |  |  |  |
| Particulars | `—` | `—` | textarea |  | Particulars |  |
| Select files | `attachments[]` | `attachment` | file |  |  |  |


### #/purchases/new

- **URL:** `admin.php?page=erp-accounting#/purchases/new`
- **Heading:** Accounting
- **Buttons:** `More`, `Add Line`, `Save`, `Save and New`, `Save as Draft`
- **List columns** (`wperp-table wperp-form-table`): Product/Service · Qty · Unit Price · Amount · VAT

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | input |  |  |  |
| — | `—` | `—` | input |  |  |  |
| Reference No | `—` | `—` | text |  |  |  |
| Billing Address | `—` | `—` | textarea |  | Type here |  |
| — | `—` | `—` | text |  | Please search |  |
| Please search Oops! No elements found. List is empty. | `qty` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | checkbox |  |  |  |
| — | `—` | `—` | text |  | Please search |  |
| Please search Oops! No elements found. List is empty. | `qty` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | checkbox |  |  |  |
| — | `—` | `—` | text |  | Please search |  |
| Please search Oops! No elements found. List is empty. | `qty` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | number |  |  |  |
| Please search Oops! No elements found. List is empty. | `—` | `—` | checkbox |  |  |  |
| — | `—` | `—` | text |  | Select Purchase Vat Zone |  |
| — | `—` | `—` | text |  |  |  |
| — | `—` | `—` | text |  |  |  |
| Particulars | `—` | `—` | textarea |  | Particulars |  |
| Select files | `attachments[]` | `attachment` | file |  |  |  |


### #/pay-purchases/new

- **URL:** `admin.php?page=erp-accounting#/pay-purchases/new`
- **Heading:** Accounting
- **Buttons:** `More`, `Save`, `Save and New`, `Save as Draft`
- **List columns** (`wperp-table wperp-form-table`): Voucher No · Due Date · Total · Balance · Amount

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | text |  | Please search |  |
| Reference No | `—` | `—` | text |  |  |  |
| — | `—` | `—` | input |  |  |  |
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | text |  | Select Account |  |
| Billing Address | `—` | `—` | textarea |  | Type here |  |
| — | `finalamount` | `—` | text |  |  |  |
| Particulars | `—` | `—` | textarea |  | Internal Information |  |
| Select files | `attachments[]` | `attachment` | file |  |  |  |


### #/expenses/new

- **URL:** `admin.php?page=erp-accounting#/expenses/new`
- **Heading:** Accounting
- **Buttons:** `More`, `Add Line`, `Save`, `Save and New`, `Save as Draft`
- **List columns** (`wperp-table wperp-form-table`): SL No. · Account · Description · Amount · Total

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | text |  | Please search |  |
| Reference No | `—` | `—` | text |  |  |  |
| — | `—` | `—` | input |  |  |  |
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | text |  | Select Account |  |
| Billing Address | `—` | `—` | textarea |  | Type here |  |
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | textarea |  | Particulars |  |
| — | `amount` | `—` | text |  |  |  |
| — | `—` | `—` | text |  |  |  |
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | textarea |  | Particulars |  |
| — | `amount` | `—` | text |  |  |  |
| — | `—` | `—` | text |  |  |  |
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | textarea |  | Particulars |  |
| — | `amount` | `—` | text |  |  |  |
| — | `—` | `—` | text |  |  |  |
| — | `finalamount` | `—` | text |  |  |  |
| Particulars | `—` | `—` | textarea |  | Internal Information |  |
| Select files | `attachments[]` | `attachment` | file |  |  |  |


### #/checks/new

- **URL:** `admin.php?page=erp-accounting#/checks/new`
- **Heading:** Accounting
- **Buttons:** `More`, `Add Line`, `Save`, `Save and New`, `Save as Draft`
- **List columns** (`wperp-table wperp-form-table`): SL No. · Account · Description · Amount · Total

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | text |  | Please search |  |
| Check No* | `—` | `—` | text | yes |  |  |
| — | `—` | `—` | input |  |  |  |
| — | `—` | `—` | text |  | Please search |  |
| Billing Address | `—` | `—` | textarea |  | Type here |  |
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | textarea |  | Particulars |  |
| — | `amount` | `—` | number |  |  |  |
| — | `—` | `—` | text |  |  |  |
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | textarea |  | Particulars |  |
| — | `amount` | `—` | number |  |  |  |
| — | `—` | `—` | text |  |  |  |
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | textarea |  | Particulars |  |
| — | `amount` | `—` | number |  |  |  |
| — | `—` | `—` | text |  |  |  |
| — | `finalamount` | `—` | text |  |  |  |
| Particulars | `—` | `—` | textarea |  | Internal Information |  |
| Select files | `attachments[]` | `attachment` | file |  |  |  |


### #/transactions/journals/new

- **URL:** `admin.php?page=erp-accounting#/transactions/journals/new`
- **Heading:** Accounting
- **Buttons:** `More`, `Add Line`, `Save`
- **List columns** (`wperp-table wperp-form-table new-journal-form`): SL No. · Account · Particulars · Debit · Credit

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | input |  |  |  |
| Ref. | `—` | `—` | text |  |  |  |
| Particulars | `—` | `—` | textarea |  | Internal Information |  |
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | text |  |  |  |
| — | `—` | `—` | text | yes |  |  |
| — | `—` | `—` | text | yes |  |  |
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | text |  |  |  |
| — | `—` | `—` | text | yes |  |  |
| — | `—` | `—` | text | yes |  |  |
| — | `—` | `—` | text |  |  |  |
| — | `—` | `—` | text |  |  |  |
| Select files | `attachments[]` | `attachment` | file |  |  |  |


### #/transactions/reimbursements/new

- **URL:** `admin.php?page=erp-accounting#/transactions/reimbursements/new`
- **Heading:** Accounting
- **Buttons:** `More`, `Save`, `Save and New`

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | text |  | Please search |  |
| Amount | `—` | `—` | number |  | Enter Amount |  |
| — | `—` | `—` | text |  | Enter Voucher Type |  |
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | text |  | Select Account |  |
| — | `—` | `—` | input |  |  |  |
| Particulars | `—` | `—` | textarea |  | Enter Particulars |  |


### #/settings/pay-tax

- **URL:** `admin.php?page=erp-accounting#/settings/pay-tax`
- **Heading:** Accounting
- **Buttons:** `More`, `Save`

| Label | Field name | id | Type | Required | Placeholder | Options |
|---|---|---|---|---|---|---|
| — | `—` | `—` | text |  | Please search |  |
| — | `—` | `—` | text |  | Select Account |  |
| — | `—` | `—` | input |  |  |  |
| — | `—` | `—` | text |  | Please search |  |
| Tax Amount | `—` | `—` | number |  | Enter Tax Amount |  |
| — | `—` | `—` | text |  | Enter Voucher Type |  |
| Particulars | `—` | `—` | textarea |  | Enter Particulars |  |

