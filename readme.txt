=== WP ERP ===
Contributors: tareq1988, wedevs, ediamin, sabbir1991, asaquzzaman
Tags: small business, SME, contact, contacts, CRM, Customer Relationship Management, employee, leave management, hr, hrm, human resource management, job, jobs, job listing, lead management, opportunity, schedule, task, lead, holiday, company
Requires at least: 4.4
Tested up to: 4.7.5
Stable tag: trunk
License: GPLv2
Donate Link: https://tareq.co/donate

An Open Source HR, CRM & Accounting Solution for WordPress

== Description ==
WP ERP is the framework for weDevs's enterprise resource management system. This plugin includes -

= Core Modules =

* HR Module
* CRM Module
* Accounting Module

= Other Modules =

* Project Management via [WP Project Manager](https://wordpress.org/plugins/wedevs-project-manager/)

= Links =
* [Github](https://github.com/wp-erp/wp-erp/?utm_medium=referral&utm_source=wordpress.org&utm_campaign=WP+ERP+Readme&utm_content=Repo+Link)
* [Documentation](https://wperp.com/documentation/?utm_medium=referral&utm_source=wordpress.org&utm_campaign=WP+ERP+Readme&utm_content=Home+Page)
* [Project Site](https://wperp.com/?utm_medium=referral&utm_source=wordpress.org&utm_campaign=WP+ERP+Readme&utm_content=Home+Page)
* [Extensions](https://wperp.com/?utm_medium=referral&utm_source=wordpress.org&utm_campaign=WP+ERP+Readme&utm_content=Downloads)

= Core Features =

* Own company profile
* Branch Listing
* WordPress admin dashboard customizing features
* Audit log to check overall workflow
* 44 Currency Support
* Notification emails with custom templates and shortcode support

= HR Module Features =

* Employee profile
* Department listing
* Designations listing
* Announcement for specific employee
* Leave request management
* Leave policy
* Holiday management


= CRM Module Features =

* Lead listing
* Opportunity listing
* Customer listing
* Notes for each lead which can be only viewed by employee, HR manager and Admin
* Built in email communication system
* Logging feature for each event. Like- call, email, meeting, SMS
* Create a schedule and get notified via mail.
* Company profile listing
* Overall activity overview
* Group contacts depending on various factors

= Accounting Module =

* Invoice - Create invoice for your customer for any sales.
* Payment - Add payments to the against the invoices.
* Payment Voucher - Add your direct cash and bank purchases.
* Vendor Credit - Purchase in credit from vendors.
* Journal Entry
* Chart of Accounts
* Bank Accounts
* Tax management
* Reporting - Trial Balance, Balance Sheet, Income Statement

= Contribute =
This may have bugs and lack of many features. If you want to contribute on this project, you are more than welcome. Please fork the repository from [Github](https://github.com/wp-erp/wp-erp).


= Checkout Our Other Products =
* [Dokan - Multivendor Plugin](https://wedevs.com/products/plugins/dokan/?utm_medium=referral&utm_source=wporg&utm_campaign=WP+ERP+Readme&utm_content=Dokan)
* [WP User Frontend Pro](https://wedevs.com/products/plugins/wp-user-frontend-pro/?utm_medium=referral&utm_source=wporg&utm_campaign=WP+ERP+Readme&utm_content=WP+User+Frontend+Pro)
* [WP Project Manager](https://wedevs.com/products/plugins/wp-project-manager-pro/?utm_medium=referral&utm_source=wporg&utm_campaign=WP+ERP+Readme&utm_content=WP+Project+Manager)


== Installation ==
###Automatic Install From WordPress Dashboard

1. Login to your the admin panel
2. Navigate to Plugins -> Add New
3. Search **WP ERP**
4. Click install and activate respectively.

###Manual Install From WordPress Dashboard

If your server is not connected to the Internet, then you can use this method-

1. Download the plugin by clicking on the red button above. A ZIP file will be downloaded.
2. Login to your site’s admin panel and navigate to Plugins -> Add New -> Upload.
3. Click choose file, select the plugin file and click install

###Install Using FTP

If you are unable to use any of the methods due to internet connectivity and file permission issues, then you can use this method-

1. Download the plugin by clicking on the red button above.A ZIP file will be downloaded.
2. Unzip the file.
3. Launch your favorite FTP client. Such as FileZilla, FireFTP, CyberDuck etc. If you are a more advanced user, then you can use SSH too.
4. Upload the folder to wp-content/plugins/
5. Log in to your WordPress dashboard.
6. Navigate to Plugins -> Installed
7. Activate the plugin

== Screenshots ==

1. Plugin on-boarding
2. HR Dashboard
3. Employee Listing
4. Creating a new employee
5. Employee details page.
6. Employee profile job tab, keep track of every salary increment, status changes and department/location changes.
7. See the leave history and balances.
8. Analyze employee performance by rating in various metrics
9. Manage permissions and who can do what.
10. Departments management
11. Designation management
12. View detailed reports on your HR
13. Leave policies for your company
14. Manage leave requests from your employees.
15. CRM dashboard
16. Contacts list
17. Contact details page, log calls, meetings, tasks and schedule everything from a single page.
18. Search your contacts with a advanced search area, everything and save those searches.
19. Filter contacts with saved searches.
20. All the activities across your contacts and companies in a single page and filterable.
21. Schedules page, see whom to call, have a meeting and manage them.
22. Manage your company details, add locations if you have multiple branches/locations.
23. We log everything whats happening across the system and log everything for easy audit logging.
24. Accounting Dashboard
25. Sales transactions list
26. Expense transactions list
27. Creating new invoice (sales)
28. Creating a basic journal entry.
29. Bank Accounts
30. Chart of accounts listing

== Frequently Asked Questions ==
**Q.** Do you have any limit on customers, users or clients?

  => No. We did not put any limit on anything. You can create as much entries as you want.

**Q.** Does WP ERP support WordPress multisite installation?

  => No, WP ERP and its add-ons do not support multisite WordPress installation.

== Changelog ==

**v1.2.0 -> May 22, 2017**

[new] Add function to get employee work location id
[new] Show unconfirmed column in contact group list tables
[new] Add support for importing employee when related WP users exist
[new] Add new leave request notification recipients filter
[new] Add HR CLI class
[tweak] Refactor and fix queries for leave management
[tweak] Employee tabs URL changed to erp-my-profile. Fixes #451
[tweak] Hide message when change email address in accounting - customer page
[tweak] Remove setup_database hook from init action and call immediately
[tweak] Refactor Subscription class.
[tweak] Export employee with all kind of status
[tweak] After delete an employee, remove HR roles instead of delete the related wp user
[tweak] Improve CSV parsing during import ERP data
[fix] Audit log erroneous pagination. Fix #460
[fix] Remove rejected requests from calendar. Fixes #449
[fix] Fix request list table ordering. Fixes #450
[fix] Remove terminated employee on Who is Out widget. Fixes #455
[fix] Leave request status colors CSS. Fixes #458
[fix] Fix entitlements list table filter
[fix] Leave rejection message in Leave rejection email .Fixes #453
[fix] Template names in comments corrected
[fix] Dix expense created by one manager can be seen by other managers too. Issue#444

**v1.1.19 -> Apr 27, 2017**

[tweak] Import partial data in case of existing contacts during WP User to CRM import or CSV import
[tweak] Log each time a contact opens an email.
[tweak] Improve insert_people function
[fix] `From` name mismatch in emails sending from CRM single contact page

**v1.1.18 -> Apr 13, 2017**

[fix] Fix owner avatar in contact and company single pages
[fix] Fix export error handle when no field is given
[fix] Fix leave available count in employee profile Leave tab
[fix] Fix erp_hr_leave_get_balance query
[tweak] Load CRM scripts only in specific pages
[tweak] Check permission before convert a contact to WP User
[tweak] Prevent woocommerce from redirecting ERP users to my account page

**v1.1.17 -> Mar 30, 2017**

* [new] Add email opt-in with subscription form
* [tweak] Change employee pay_rate column int to decimal in db

**v1.1.16 -> Mar 27, 2017**

* [new] added function `erp_get_client_ip()` for getting client IP address
* [fix] Fix CRM dashboard Today's Schedule metabox
* [fix] Remove a contact from all groups after delete it
* [fix] TypeError when deleting search segment. Fixes #421.
* [fix] Update unsubscribed contact/company data properly
* [fix] Fixes CRM Dashboard metaboxes display issue.
* [fix] Using dynamic life stages instead of statics in CRM dashboard
* [fix] Append extra life stage label after the filter applied
* [tweak] Added some file types in file_upload option

**v1.1.15 -> Mar 16, 2017**

* [fix] Allow export fields like checkbox or multiselect for Contact or Company
* [fix] Fix import export undefined field_builder_contacts_fields error
* [fix] Fix condition to hide dashboard metabox
* [fix] Pagination problem on leave request page. Fixes #419

**v1.1.14 -> Mar 07, 2017**

* [new] Added CRM cli command for clearing up the contacts
* [new] Add most of the current circulating currencies
* [new] Add `post_where_queries` optoin for erp_get_peoples query
* [new] Add param to print erp meta values
* [fix] Fix contact editor error when assigned to one group in single page
* [fix] Email Template adding back slash (\) on edit with single and double quote
* [fix] Proper error handle for contact form integration
* [tweak] Hide HR Permission tab in own profile page
* [tweak] Remove terminated employees from Birthday Buddies list
* [tweak] Added created_at field to enable adding log in a past date

**v1.1.13 -> Feb 16, 2017**

* [fix] Show tasks based on permission and tab in CRM Schedule calendar
* [fix] Fix contact editor error when assigned to one group
* [fix] Update ERP_Settings_Page class. Fix #409.
* [fix] Fix core Updates class
* [fix] Fix WP User to CRM importer meta sync error
* [fix] Fix comapny default address zip error
* [new] Add function to remove select2 enqueued by other plugins

**v1.1.12 -> Feb 01, 2017**

* [fix] Fix employee editor popup country dropdown
* [fix] Fix announcement mark as read process
* [fix] Fix people conversion from another type or from WP User
* [fix] Fix people total counting issues in `erp_get_peoples` function
* [new] `erp_include_popup_markup` function to include erp-popup markup
* [new] Add toastr.js plugin
* [new] Add filterable company default location name
* [tweak] Refactor single employee permissions update process

**v1.1.11 -> Jan 22, 2017**

* [fix] Update minified JavaScript files

**v1.1.10 -> Jan 18, 2017**

* [new] Introduce REST API
* [new] Meta query support in advance search segmentaion
* [new] Date and number range type filter in crm search segmentation
* [new] Pakistani Rupee currency
* [new] Contact age filter in advance search filter CRM
* [new] Pagination system for individual ledger
* [new] Closing balance for individual ledger
* [new] Added filter `erp_crm_js_template_file_path` in js template path
* [tweak] Remove edit functionality from single transaction page
* [tweak] Change label for dashboard net income
* [tweak] Update select2 v4.0.3
* [fix] Leave request deleting issue

**v1.1.9 -> Dec 22, 2016**

* [new] Can input data from older financial year (balance c/f)
* [new] Added email validation for new customer and vendor
* [new] Include journal tax in sales tax report
* [tweak] Customers and vendors are searchable and limit lifted
* [tweak] Tax receivable accounts moved to expense
* [tweak] Date picker range enhanced
* [fix] ERP updater is now working
* [fix] Removed journal edit option from individual chart list table

**v1.1.8 -> Dec 11, 2016**

* [new] Add filter for hiding dashboard metaboxes
* [fix] Fix leave request datetime calculation
* [fix] Fix journal entry item problem
* [fix] Move tax receivable accounts from expense to assets
* [fix] Update number formating for transaction unit price
* [fix] Remove employee role checkbox from wp user edit profile
* [Update] Update moment.js to v2.17.1

**v1.1.7 -> Dec 7, 2016**

* [new] Label added in tax form
* [new] Added editing system in journal
* [new] Should be able to transform a CRM Contact into a WP_User
* [new] Add localize support for fullcalendar
* [new] Add date range in all date picker.
* [new] Add flot chart stack and categories plugins
* [new] Added age field in contact
* [fix] Joining date does not display on the employee welcome email
* [fix] Employee Privileges are Removed upon role change
* [fix] Checkbox toggling in all list tables
* [fix] Expense pie chart is set to 100% by default
* [fix] Holidays editing and deleting
* [fix] Fix screen, screen base and form handler hook for HR and Leave pages
* [fix] Calculation in dashboard all charts
* [fix] Calculation fixed in bank charts
* [fix] Dejavusanscondensed font paths for FPDF
* [fix] Change contact get query sql
* [fix] Change people meta key `_assign_crm_agent` to `contact_owner`
* [fix] Email duplication problem when people create
* [fix] Conflicting with accounting customer when contact create
* [fix] Tax amount processing during transaction
* [fix] Discount field range only 0 to 100 at transaction time
* [fix] Quantity field make grater than 0 at transaction time
* [fix] Required minimum amount for bank transfer
* [fix] Refactor & add journal new function

**v1.1.6 -> Nov 9, 2016**

* [Fix] Query fix dashboard income & expense, business expense, net income, invoice payable to and bill you need to pay
* [Fix] Fix overdue payment problem
* [Fix] Unnecessary select checkboxes in accounting module
* [Fix] Transaction list table total item query
* [Fix] Unsubscribe contact from a specific group
* [Fix] Unable to change CRM contact owner problem
* [Fix] Menu item are not in open/fixed mode when announcement is selected
* [Fix] Contact status refreshing problem during edit and add contact
* [Fix] Permission for CRM manager to delete others contacts
* [Fix] Custom field fix on csv export
* [New] Add Currency option in Settings - General Options
* [New] Unicode support added to PDF
* [New] Add new column `email_status` and `data_id` in `erp_hr_announcement` and `erp_audit_log` table respectively
* [Update] Set default current date for new invoice, payment, vendor credit and payment voucher
* [Update] Button status for all new transaction time
* [Update] Sales and expense list table according with transaction status
* [Update] Sales and expense bulk action for different transaction section
* [Update] All transaction report query
* [Update] Journal query and journal list table class for filtering only journal type transaction
* [Update] Improve announcement functionality
* [Update] Chosen js removed from core

**v1.1.5 -> Sep 19, 2016**

 * [fix] Holiday date calculation problem fixed
 * [fix] Ajax request for edit holiday
 * [fix] Change holiday listing order
 * [fix] Update leave holiday search, table end column for iCal
 * [fix] Fix payment dropdown button in Payment Voucher create page
 * [fix] Remove currency option in vendor and customer add, edit
 * [fix] Contact deleting permission issues
 * [fix] Pdf invoice class undefined problem
 * [fix] Fix issue_date problem in chart of accounting
 * [fix] Reloading employee list problem fixed in js
 * [fix] Change some style in employee note section and added loading effect
 * [new] Add letter support to company location zip code
 * [new] Added country and state select2 in accounting vendor and customer
 * [new] Custom Fields support in contact form
 * [new] Added erp_create_new_people hook if people is an existing wp user
 * [new] Added Saudi Riyal currency

**v1.1.4 -> Aug 28, 2016**

 * [fix] New expense time undefined invoice_number problem fixed
 * [fix] Ignore rejected leaves during validating duration
 * [fix] Tax calculation problem fixed
 * [fix] Announcement permission problem fixed
 * [fix] Employee can not take leave in weekend
 * [fix] Problem to take leave when no leave days available fixed
 * [fix] Duplicate row item created in payemnt and invoice fixed
 * [fix] Employee birthday check hook changes
 * [fix] Fixed save search segment reset filter functionality
 * [fix] Updated some crm permissions
 * [new] Delete functionality in save search segment
 * [new] Voucher create time from account is required
 * [new] Action hook 'erp_crm_contact_inbound_email' added

**v1.1.3 -> Aug 4, 2016**

 * [fix] Added loading feedback when submitting form for all popup
 * [fix] Invoice number formatting functionality
 * [fix] HR all capabilities problem fixed
 * [fix] Hook contact form integration to plugins_loaded hook
 * [fix] Removed logged in user check for cron job
 * [fix] Hide plugin updater for non-admin
 * [new] Life stage, contact owner & group added on CSV contact importer form
 * [new] added some hooks and filters

**v1.1.2 -> June 26, 2016**

 * [new] Settings for invoice formatting
 * [new] Set submit group button for sales payment and invoice
 * [new] Set submit group button for expense payment voucher and vendor credit
 * [new] Add email search in contact and company listing
 * [new] Display dropdown text instead of value in save search filter details
 * [new] Add contact group filter option in saved search segment
 * [new] Added Iranian Rial currency and change India currency symbol
 * [new] Bulk users to contacts importer tools added
 * [new] Contact Forms Integration: add contact owner field
 * [new] Added localization for js string in activity feeds
 * [new] CSV sample file generator added
 * [fix] Transaction update time check for invoice number uniquness
 * [fix] Transaction due date should be greater than issue date
 * [fix] Leave request quota validation problem when apply leave
 * [fix] Select2 rendering problem in expense
 * [fix] Defualt invoice prefix set at transaction time
 * [fix] Error message problem fixed when company settings updated
 * [fix] Employee edit their own Employee ID
 * [fix] Employees without manager or agent permission are listed in Activities page - Create By filter
 * [fix] Contact Source is not showing in single view sidebar in CRM
 * [fix] User's role isn't showing correctly on edit page
 * [fix] Fixing select2 derective issues
 * [fix] Announcement select2 issue fixed when select employee
 * [fix] Leave policy rendering problem in employee my profile page
 * [fix] HR dashboard calendar loading error
 * [fix] Contact editing problem
 * [fix] Line breaking problem in announcement email
 * [update] Transaction insert form filtering for table row and column
 * [update] Currency schema update
 * [update] Update query according with submit group button for sales and expenses
 * [update] transaction table column name change from invoice to invoice_number
 * [update] Table column field length increase for decimal type
 * [update] Set default invoice prefix
 * [update] Customer and vendor fields are required when add new transaction
 * [update] Vendor name is required when new verndor is created
 * [update] Save search labeling change to search segment
 * [update] Users to contacts tool progress changes
 * [update] CRM contacts CSV imported improvements
 * [update] Change crm activity component structure for extending thirdparty integration
 * [update] Change invoice url format for sharing
 * [update] Set wp mysql timezone instead of carbon

**v1.1.1 -> June 22, 2016**

 * [fix] Accounting report query optimzation
 * [fix] Partial payment amount problem fixed
 * [fix] Contact and company permission problem fixed for CRM agent
 * [fix] Bulkaction permission fixed for contact and company listing
 * [fix] Javascript null date problem fixed
 * [Fix] Fixed enable disable problem at reference number entry time
 * [Fix] Save search dropdown default value problem fixed
 * [Fix] Fixed CRM contact table after a bulk action, items don't get deselect
 * [Fix] Fixed schedule calander styling problem
 * [fix] CRM agent permission problem fixed
 * [fix] Fix assign group permission problem
 * [fix] Fix assign contact issue when deal with wp user contacts
 * [fix] Contact group edit and assign problem fixed
 * [fix] Fixed total number counting when add new contact
 * [fix] Fixed schedule notification problem
 * [fix] Timeline date issue in contact single page
 * [new] Added loading effect when assign contact owner
 * [new] Invoice number generator functionality in accounting
 * [new] Added support for Omanian Rial currency
 * [new] Added some filter and action hook in sales transaction
 * [new] Added restore functionality in HRM employee table
 * [new] Export invoices as PDF and send via email
 * [Update] Accounting dashaboard updated
 * [Update] Updated filter and hook for people query sql

**v1.1.0 -> June 8, 2016**

 * [new] Merge accounting module
 * [new] Currency formating
 * [new] Income tax settings
 * [new] Income tax report
 * [new] Income statement report
 * [new] Balance sheet report
 * [new] Permission management system
 * [new] Save as draft for all transaction
 * [new] Convert wp list table into vue js in contact and company listing page
 * [new] SMTP and IMAP/POP3 integration added into core
 * [fix] Bank chart
 * [fix] Customer and vendor create time email field is required
 * [fix] ref number make unique
 * [fix] Role updating fixed when contact edit
 * [fix] Contact group assign and editing problem fixed
 * [fix] Trix editor firefox compability fixed
 * [fix] Adding and editing feed problem fixed when using firefox browser
 * [fix] Dashboard page contact fetching error fixed
 * [fix] Activity page loading problem fixed
 * [fix] Schedule page loading problem fixed
 * [fix] Select2 conflict fixed with accounting
 * [improve] All transaction table with balance column and short view popup link
 * [imporve] Save search filter improvement
 * [imporve] People insert and fetching query optimized
 * [imporve] Contact and company single page converted into vue js
 * [imporve] Added more filter into advance search segment
 * [update] Transaction query update for current financial year.
 * [update] Include tax field in transaction form
 * [update] Vuejs updated
 * [update] Select2 updated
 * [update] Trix editor js updated

**v1.0.1 -> April 27, 2016**

 * [fix] Employee performance fetching was returning all entries
 * [fix] WP_User importing into contact was not refering the right WP_User
 * [fix] License key was not saving
 * [fix] Imported contact counting issues
 * [fix] Social field url issues in contact profile

**v1.0 -> April 25, 2016**

 * [improved] Change people table structure.
 * [new] New CRM agent role added
 * [new] CSV import/export tool added
 * [new] Added CRM email templating system
 * [new] Save reply added in CRM contact activities
 * [new] Added quick view schedules details from CRM dashboard
 * [new] Assign contact to CRM agents
 * [new] Add progress-bar when activity delete for better UX
 * [new] License management feature added
 * [new] CRM activity email read tracker
 * [new] HR reporting headcount chart now shows department-wise
 * [new] New life stage added in CRM contacts
 * [new] Added contact group and contact owner field in new contact or company creation
 * [new] Added inbounding reply emails in CRM activity
 * [new] Bulk importer WP User to CRM contacts
 * [new] Added directly replying from CRM email activities feeds
 * [new] Added address options in employee details, add and edit
 * [new] Added plugin updater functionalities
 * [new] Added more hooks and filters
 * [fix] Re-factor contact forms integration
 * [fix] Re-factor CRM permissions
 * [fix] Contact pagination problem fixed
 * [fix] Re-factor save search query builder
 * [fix] Logs and schedules add and displaying problem in schedule page
 * [fix] ERP date format problem
 * [fix] Manage user role during plugin activation and deactivation
 * [fix] Who is out widget in HR dashboard
 * [fix] Leave request bulk actions
 * [fix] All ERP users show their own attachments
 * [fix] Added file uploading permission for Employee, HR Manager, CRM Manager and CRM Agents
 * [fix] Contact mail functionality improvements
 * [fix] Fix employee termination issues
 * [fix] Leave entitlement problem fixed
 * [fix] Employee list table now focus on "active" subnav by default
 * [fix] Employee and Contact record duplication remove with better UX
 * [update] - Trix editor js, Select2, Vuejs

**v0.1 -> March 18, 2016**

* Beta Release


== Upgrade Notice ==

Nothing here right now.
