=== WP ERP ===
Contributors: tareq1988, wedevs, sabbir1991, asaquzzaman
Tags: small business, SME, contact, contacts, CRM, Customer Relationship Management, employee, leave management, hr, hrm, human resource management, job, jobs, job listing, lead management, opportunity, schedule, task, lead, holiday, company
Requires at least: 4.4
Tested up to: 4.5.2
Stable tag: trunk
License: GPLv2
Donate Link: https://tareq.co/donate

An Open Source ERP & CRM Solution for WordPress

== Description ==
WP ERP is the framework for weDevs's enterprise resource management system. This plugin includes -

* HR Module
* CRM Module
* Accounting Module

Other available modules

* Project Management via [WP Project Manager](https://wordpress.org/plugins/wedevs-project-manager/)

= Links =
* [Github](https://github.com/wp-erp/wp-erp)
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

= Contribute =
This may have bugs and lack of many features. If you want to contribute on this project, you are more than welcome. Please fork the repository from [Github](https://github.com/wp-erp/wp-erp).


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

== Frequently Asked Questions ==
**Q.** Do you have any limit on customers, users or clients?

  => No. We did not put any limit on anything. You can create as much entries as you want.

== Changelog ==

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

**v1.1.0 -> June 8, 2016**

 * [new] Merge accounting module
 * [new] Currency formating
 * [new] Income tax settings
 * [new] Income tax report
 * [new] Income statement report
 * [new] Balance sheet report
 * [new] Permission management system
 * [new] Save as draft for all transaction
 * [improved] All transaction table with balance column and short view popup link
 * [update] Transaction query update for current financial year.
 * [update] Include tax field in transaction form
 * [fix] Bank chart
 * [fix] Customer and vendor create time email field is required
 * [fix] ref number make unique


== Upgrade Notice ==

Nothing here right now.
