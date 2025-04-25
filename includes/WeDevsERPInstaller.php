<?php

namespace WeDevs\ERP;

use DateTime;

/**
 * Installation related functions and actions.
 *
 * @author Tareq Hasan
 */

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Installer Class
 */
class WeDevsERPInstaller {
    use \WeDevs\ERP\Framework\Traits\Hooker;

    /**
     * Binding all events
     *
     * @since 1.0
     *
     * @return void
     */
    public function __construct() {
        $this->set_default_modules();

        // register_activation_hook( WPERP_FILE, [ $this, 'activate' ] );
        register_deactivation_hook( WPERP_FILE, [ $this, 'deactivate' ] );

        $this->action( 'admin_menu', 'welcome_screen_menu' );
        $this->action( 'admin_head', 'welcome_screen_menu_remove' );
    }

    /**
     * Placeholder for activation function
     * Nothing being called here yet.
     *
     * @since 1.0
     * @since save plugin install date
     * @since save plugin install date
     *
     * @return void
     */
    public function activate() {

        if ( !  function_exists( 'slugify' ) ) {
            require_once WPERP_PATH . '/modules/accounting/includes/functions/common.php';
        }
        if ( ! function_exists( 'erp_get_version' ) ) {
            require_once WPERP_PATH . '/includes/functions.php';
        }
        $current_erp_version = get_option( 'wp_erp_version', null );
        $current_db_version  = get_option( 'wp_erp_db_version', null );

        $this->create_tables();
        $this->populate_data();

        if ( is_null( $current_erp_version ) ) {
            $this->set_role();
        }

        $this->create_roles(); // @TODO: Needs to change later :)
        $this->create_cron_jobs();
        $this->setup_default_emails();

        // does it needs any update?
        $updater = new \WeDevs\ERP\Updates();
        $updater->perform_updates();

        if ( is_null( $current_erp_version ) && is_null( $current_db_version ) && apply_filters( 'erp_enable_setup_wizard', true ) ) {
            set_transient( '_erp_activation_redirect', 1, 30 );
        }

        // update to latest version
        $latest_version = erp_get_version();
        update_option( 'wp_erp_version', $latest_version );
        update_option( 'wp_erp_db_version', $latest_version );

        //save install date
        if ( false == get_option( 'wp_erp_install_date' ) ) {
            update_option( 'wp_erp_install_date', current_time( 'timestamp' ) );
        }
    }

    /**
     * Include required files to prevent fatal errors
     *
     * @return void
     */
    public function includes() {
        include_once WPERP_MODULES . '/hrm/includes/functions-capabilities.php';
        include_once WPERP_MODULES . '/crm/includes/functions-capabilities.php';
        include_once WPERP_MODULES . '/accounting/includes/functions/capabilities.php';
    }

    /**
     * Set default mail subject, heading and body
     *
     * @since 1.0
     *
     * @return void
     */
    public function setup_default_emails() {

        //Employee welcome
        $welcome = [
            'subject' => 'Welcome {full_name} to {company_name}',
            'heading' => 'Welcome Onboard {first_name}!',
            'body'    => 'Dear {full_name},

Welcome aboard as a <strong>{job_title}</strong> in our <strong>{dept_title}</strong> team at <strong>{company_name}</strong>! I am pleased to have you working with us. You were selected for employment due to the attributes that you displayed that appear to match the qualities I look for in an employee.

I’m looking forward to seeing you grow and develop into an outstanding employee that exhibits a high level of care, concern, and compassion for others. I hope that you will find your work to be rewarding, challenging, and meaningful.

Your <strong>{type}</strong> employment will start from <strong>{joined_date}</strong> and you will be reporting to <strong>{reporting_to}</strong>.

Please take your time and review our yearly goals so that you can know what is expected and make a positive contribution. Again, I look forward to seeing you grow as a professional while enhancing the lives of the clients entrusted in your care.

Sincerely,
Manager Name
CEO, Company Name

{login_info}',
        ];

        update_option( 'erp_email_settings_employee-welcome', $welcome );

        //New Leave Request
        $new_leave_request = [
            'subject' => 'New leave request received from {employee_name}',
            'heading' => 'New Leave Request',
            'body'    => 'Hello,

A new leave request has been received from {employee_url}.

<strong>Leave type:</strong> {leave_type}
<strong>Date:</strong> {date_from} to {date_to}
<strong>Days:</strong> {no_days}
<strong>Reason:</strong> {reason}

Please approve/reject this leave application by going following:

{requests_url}

Thanks.',
        ];

        update_option( 'erp_email_settings_new-leave-request', $new_leave_request );

        //Approved Leave Request
        $approved_request = [
            'subject' => 'Your leave request has been approved',
            'heading' => 'Leave Request Approved',
            'body'    => 'Hello {employee_name},

Your <strong>{leave_type}</strong> type leave request for <strong>{no_days} days</strong> from {date_from} to {date_to} has been approved.

Regards
Manager Name
Company',
        ];

        update_option( 'erp_email_settings_approved-leave-request', $approved_request );

        //Rejected Leave Request
        $reject_request = [
            'subject' => 'Your leave request has been rejected',
            'heading' => 'Leave Request Rejected',
            'body'    => 'Hello {employee_name},

Your <strong>{leave_type}</strong> type leave request for <strong>{no_days} days</strong> from {date_from} to {date_to} has been rejected.

The reason of rejection is: {reject_reason}

Regards
Manager Name
Company',
        ];

        update_option( 'erp_email_settings_rejected-leave-request', $reject_request );

        // New Task Assigned
        $new_task_assigned = [
            'subject' => 'New task has been assigned to you',
            'heading' => 'New Task Assigned',
            'body'    => 'Hello {employee_name},

A new task <strong>{task_title}</strong> has been assigned to you by {created_by}.
Due Date: {due_date}

Regards
Manager Name
Company',
        ];

        update_option( 'erp_email_settings_new-task-assigned', $new_task_assigned );

        // New Contact Assigned
        $new_contact_assigned = [
            'subject' => 'New contact has been assigned to you',
            'heading' => 'New Contact Assigned',
            'body'    => 'Hello {employee_name},

A new contact <strong>{contact_name}</strong> has been assigned to you by {created_by}.

Regards
Manager Name
Company',
        ];

        update_option( 'erp_email_settings_new-contact-assigned', $new_contact_assigned );

        //Employee hiring date anniversary
        $hiring_date_anniversary = [
            'subject' => 'Congratulation for Your Work Anniversary',
            'heading' => 'Congratulation for Passing One More Year With Us :)',
            'body'    => 'Congratulations {full_name}!

You have been a model employee for {total_year} years now. You are one of my few original employees and have certainly become an asset to this company. I appreciate the selfless service you\'ve given for so many years. Without the loyalty and hard work of experts like you who helped us get things started, we could never have achieved our present stature. I hope the gift I sent will reflect the high esteem I have for you.

May you enjoy the fruits of your labors for years to come',
        ];

        update_option( 'erp_email_settings_hiring-anniversary-wish', $hiring_date_anniversary );

        $govt_holiday_reminder = [
            'subject' => 'Upcoming government holiday reminder',
            'heading' => 'Reminder',
            'body'    => 'Hello {full_name}

This is an official announcement for all employees that the {holiday_name} holidays will be observed {holiday_duration}.Let us take the days off on this {holiday_name} and the office will re-open the next day,{reopen_day}. See you all back to work again.Regards

HR Manager
XYZ Limited',
        ];

        update_option( 'erp_email_settings_govt-holiday-reminder', $govt_holiday_reminder );

        /**** Accounting email template ****/

        $transectional_email = [
            'subject' => 'New invoice has been created',
            'heading' => 'New transaction invoice',
            'body'    => 'Dear {customer_name},

We are contacting you in regard to a new invoice #{invoice_ID} that has been created on your account. You may find the invoice attached. Please pay the balance of {amount} by {due_date}.


Kind Regards,
Account Manager
{company_name}
',
        ];

        update_option( 'erp_email_settings_transectional-email', $transectional_email );

        $transectional_email_payments = [
            'subject' => 'An invoice has been paid',
            'heading' => 'New transaction payment',
            'body'    => 'Dear {customer_name},

I just wanted to drop you a quick note to let you know that we have received your recent payment in respect of invoice {invoice_ID}. Thank you very much. We really appreciate it.

Kind Regards,
Account Manager
{company_name}
',
        ];

        update_option( 'erp_email_settings_transectional-email-payments', $transectional_email_payments );

        $transectional_email_purchase = [
            'subject' => 'An purchase has been created',
            'heading' => 'New transaction purchase',
            'body'    => 'Dear {vendor_name},

I just wanted to drop you a quick note to let you know that we have created a purchase invoice to pay you in respect of invoice {invoice_ID}. Thank you very much. We really appreciate it.

Kind Regards,
Account Manager
{company_name}
',
        ];

        update_option( 'erp_email_settings_transectional-email-purchase', $transectional_email_purchase );

        $transectional_email_estimate = [
            'subject' => 'An estimate has been created',
            'heading' => 'New transaction estimate',
            'body'    => 'Hi {customer_name},

Thanks for providing us the opportunity to do business with you. You will find an estimate containing each of the products/services we are proposing to complete attached with this email. Please review the estimate and reply to this email at your earliest convenience. We look forward to doing business together. If you have any questions, feel free to contact us.


Best Regards,
Project Manager
{company_name}
',
        ];

        update_option( 'erp_email_settings_transectional-email-estimate', $transectional_email_estimate );

        $transectional_email_purchase_order = [
            'subject' => 'An purchase order has been created',
            'heading' => 'New transaction purchase order',
            'body'    => 'Dear (vendor_name)

With reference to our discussion, we would like to inform you that the order of {invoice_ID} has been approved. Please proceed the delivery of the product further.

Please feel free to contact me if you need any sort of clarification. Please dispatch the goods latest by the promised time.

We hope to have a long term business association with you.


Best Regards,
Account Manager
{company_name}
',
        ];

        update_option( 'erp_email_settings_transectional-email-purchase-order', $transectional_email_purchase_order );

        $transectional_email_pay_purchase = [
            'subject' => 'An invoice has been paid',
            'heading' => 'New transaction pay purchase',
            'body'    => 'Dear {vendor_name},

We are contacting you in regard to a new invoice #{invoice_ID} that has been created on your account. You may find the invoice attached.


Kind Regards,
Account Manager
{company_name}',
        ];

        update_option( 'erp_email_settings_transectional-email-pay-purchase', $transectional_email_pay_purchase );

        /***/
    }

    /**
     * Create cron jobs
     *
     * @return void
     */
    public function create_cron_jobs() {
        // schedule per minute hook
        if ( ! wp_next_scheduled( 'erp_per_minute_scheduled_events' ) ) {
            wp_schedule_event( time(), 'per_minute', 'erp_per_minute_scheduled_events' );
        }

        // schedule daily hook
        if ( ! wp_next_scheduled( 'erp_daily_scheduled_events' ) ) {
            wp_schedule_event( time(), 'daily', 'erp_daily_scheduled_events' );
        }

        // schedule weekly hook
        if ( ! wp_next_scheduled( 'erp_weekly_scheduled_events' ) ) {
            wp_schedule_event( time(), 'weekly', 'erp_weekly_scheduled_events' );
        }
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate() {
        wp_clear_scheduled_hook( 'erp_per_minute_scheduled_events' );
        wp_clear_scheduled_hook( 'erp_daily_scheduled_events' );
        wp_clear_scheduled_hook( 'erp_weekly_scheduled_events' );

        remove_role( 'erp_crm_manager' );
        remove_role( 'erp_crm_agent' );
    }

    /**
     * Welcome screen menu page cb
     *
     * @since 1.0
     *
     * @return void
     */
    public function welcome_screen_menu() {
        add_dashboard_page( __( 'Welcome to WP ERP', 'erp' ), 'WP ERP', 'manage_options', 'erp-welcome', [
            $this,
            'welcome_screen_content',
        ] );
    }

    /**
     * Welcome screen menu remove
     *
     * @since 1.0
     *
     * @return void
     */
    public function welcome_screen_menu_remove() {
        remove_submenu_page( 'index.php', 'erp-welcome' );
    }

    /**
     * Render welcome screen content
     *
     * @since 1.0
     *
     * @return void
     */
    public function welcome_screen_content() {
        include WPERP_VIEWS . '/welcome-screen.php';
    }

    /**
     * Create necessary table for ERP & HRM
     *
     * @since 1.0
     *
     * @return void
     */
    public function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $table_schema = [
            "CREATE TABLE `{$wpdb->prefix}erp_company_locations` (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                company_id int(11) unsigned DEFAULT NULL,
                name varchar(255) DEFAULT NULL,
                address_1 varchar(255) DEFAULT NULL,
                address_2 varchar(255) DEFAULT NULL,
                city varchar(100) DEFAULT NULL,
                state varchar(100) DEFAULT NULL,
                zip int(6) DEFAULT NULL,
                country varchar(5) DEFAULT NULL,
                fax varchar(20) DEFAULT NULL,
                phone varchar(20) DEFAULT NULL,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY (id),
                KEY company_id (company_id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_hr_depts` (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                title varchar(200) NOT NULL DEFAULT '',
                `description` text,
                `lead` int(11) unsigned DEFAULT '0',
                parent int(11) unsigned DEFAULT '0',
                `status` tinyint(1) unsigned DEFAULT '1',
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_hr_designations` (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                title varchar(200) NOT NULL DEFAULT '',
                `description` text,
                `status` tinyint(1) DEFAULT '1',
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_hr_employees` (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
                employee_id varchar(20) DEFAULT NULL,
                designation int(11) unsigned NOT NULL DEFAULT '0',
                department int(11) unsigned NOT NULL DEFAULT '0',
                `location` int(10) unsigned NOT NULL DEFAULT '0',
                hiring_source varchar(20) NOT NULL,
                hiring_date date NOT NULL,
                termination_date date NOT NULL,
                date_of_birth date NOT NULL,
                reporting_to bigint(20) unsigned NOT NULL DEFAULT '0',
                pay_rate decimal(20,2) unsigned NOT NULL DEFAULT '0',
                pay_type varchar(20) NOT NULL DEFAULT '',
                `type` varchar(20) NOT NULL,
                `status` varchar(10) NOT NULL DEFAULT '',
                deleted_at datetime DEFAULT NULL,
                PRIMARY KEY (id),
                KEY user_id (user_id),
                KEY employee_id (employee_id),
                KEY designation (designation),
                KEY department (department),
                KEY `status` (`status`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_hr_employee_history` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
                `module` varchar(20) DEFAULT NULL,
                `category` varchar(20) DEFAULT NULL,
                `type` varchar(20) DEFAULT NULL,
                `comment` text,
                `data` longtext,
                `date` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`),
                KEY `module` (`module`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_hr_employee_notes` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
                `comment` text NOT NULL,
                `comment_by` bigint(20) unsigned NOT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leaves (
                id smallint(6) NOT NULL AUTO_INCREMENT,
                name varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                created_at int(11) DEFAULT NULL,
                updated_at int(11) DEFAULT NULL,
                PRIMARY KEY  (id)
              ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leave_policies (
                id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                leave_id smallint(5) UNSIGNED NOT NULL,
                `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `days` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                color varchar(10) DEFAULT NULL,
                apply_limit tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                employee_type enum('-1','permanent','parttime','contract','temporary','trainee') NOT NULL DEFAULT 'permanent',
                department_id int(11) NOT NULL DEFAULT '-1',
                location_id int(11) NOT NULL DEFAULT '-1',
                designation_id int(11) NOT NULL DEFAULT '-1',
                gender enum('-1','male','female','other') NOT NULL DEFAULT '-1',
                marital enum('-1','single','married','widowed') NOT NULL DEFAULT '-1',
                f_year smallint(5) UNSIGNED DEFAULT NULL,
                apply_for_new_users tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                carryover_days tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                carryover_uses_limit tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                encashment_days tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                encashment_based_on enum('pay_rate','basic','gross') DEFAULT NULL,
                forward_default enum('encashment','carryover') NOT NULL DEFAULT 'encashment',
                applicable_from_days smallint(5) UNSIGNED NOT NULL DEFAULT '0',
                accrued_amount decimal(10,2) NOT NULL DEFAULT '0.00',
                accrued_max_days smallint(4) UNSIGNED NOT NULL DEFAULT '0',
                halfday_enable tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                created_at int(11) DEFAULT NULL,
                updated_at int(11) DEFAULT NULL,
                PRIMARY KEY  (id),
                KEY leave_id (leave_id),
                KEY f_year (f_year)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leave_policies_segregation (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                leave_policy_id bigint(20) UNSIGNED NOT NULL,
                jan tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                feb tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                mar tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                apr tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                may tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                jun tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                jul tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                aug tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                sep tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                oct tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                nov tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                decem tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                created_at int(11) DEFAULT NULL,
                updated_at int(11) DEFAULT NULL,
                PRIMARY KEY  (id),
                KEY leave_policy_id (leave_policy_id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leave_entitlements (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) UNSIGNED NOT NULL,
                leave_id smallint(6) UNSIGNED NOT NULL,
                created_by bigint(20) UNSIGNED DEFAULT NULL,
                trn_id bigint(20) UNSIGNED NOT NULL,
                trn_type enum('leave_policies','leave_approval_status','leave_encashment_requests','leave_entitlements','unpaid_leave','leave_encashment', 'leave_carryforward', 'manual_leave_policies', 'Accounts', 'others', 'leave_accrual', 'carry_forward_leave_expired') NOT NULL DEFAULT 'leave_policies',
                day_in decimal(5,1) UNSIGNED NOT NULL DEFAULT '0.0',
                day_out decimal(5,1) UNSIGNED NOT NULL DEFAULT '0.0',
                `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                f_year smallint(6) NOT NULL,
                created_at int(11) DEFAULT NULL,
                updated_at int(11) DEFAULT NULL,
                PRIMARY KEY  (id),
                KEY comp_key_1 (`user_id`,leave_id,f_year,trn_type),
                KEY trn_id (trn_id),
                KEY leave_id (leave_id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leave_requests (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) UNSIGNED NOT NULL,
                leave_id smallint(6) UNSIGNED NOT NULL,
                leave_entitlement_id bigint(20) UNSIGNED NOT NULL default '0',
                day_status_id smallint(5) UNSIGNED NOT NULL DEFAULT '1',
                `days` decimal(5,1) UNSIGNED NOT NULL DEFAULT '0.0',
                `start_date` int(11) NOT NULL,
                end_date int(11) NOT NULL,
                reason text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                last_status smallint(6) UNSIGNED NOT NULL DEFAULT '2',
                created_by bigint(20) UNSIGNED DEFAULT NULL,
                created_at int(11) DEFAULT NULL,
                updated_at int(11) DEFAULT NULL,
                PRIMARY KEY  (id),
                KEY `user_id` (`user_id`),
                KEY user_leave (`user_id`,leave_id),
                KEY user_entitlement (`user_id`,leave_entitlement_id),
                KEY last_status (last_status),
                KEY leave_entitlement_id (leave_entitlement_id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leave_approval_status (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                leave_request_id bigint(20) UNSIGNED NOT NULL,
                approval_status_id tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                approved_by bigint(20) UNSIGNED DEFAULT NULL,
                `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                created_at int(11) DEFAULT NULL,
                updated_at int(11) DEFAULT NULL,
                PRIMARY KEY  (id),
                KEY leave_request_id (leave_request_id),
                KEY approval_status_id (approval_status_id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leave_request_details (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                leave_request_id bigint(20) UNSIGNED NOT NULL,
                leave_approval_status_id bigint(20) UNSIGNED NOT NULL,
                workingday_status tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
                `user_id` bigint(20) UNSIGNED NOT NULL,
                f_year smallint(6) NOT NULL,
                leave_date int(11) NOT NULL,
                created_at int(11) DEFAULT NULL,
                updated_at int(11) DEFAULT NULL,
                PRIMARY KEY  (id),
                KEY leave_request_id (leave_request_id),
                KEY `user_id` (`user_id`),
                KEY user_fyear_leave (`user_id`,f_year,leave_date)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leave_encashment_requests (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) UNSIGNED NOT NULL,
                leave_id smallint(6) UNSIGNED NOT NULL,
                approved_by bigint(20) UNSIGNED DEFAULT NULL,
                approval_status_id tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
                encash_days decimal(4,1) UNSIGNED NOT NULL DEFAULT '0.0',
                forward_days decimal(4,1) UNSIGNED NOT NULL DEFAULT '0.0',
                amount decimal(20,2) NOT NULL DEFAULT '0.00',
                total decimal(20,2) NOT NULL DEFAULT '0.00',
                f_year smallint(5) UNSIGNED NOT NULL,
                created_at int(11) DEFAULT NULL,
                updated_at int(11) DEFAULT NULL,
                PRIMARY KEY  (id),
                KEY `user_id` (`user_id`),
                KEY leave_id (leave_id),
                KEY f_year (f_year)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leaves_unpaid (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                leave_id smallint(6) UNSIGNED NOT NULL,
                leave_request_id bigint(20) UNSIGNED NOT NULL,
                leave_approval_status_id bigint(20) UNSIGNED NOT NULL,
                `user_id` bigint(20) UNSIGNED NOT NULL,
                `days` decimal(4,1) UNSIGNED NOT NULL DEFAULT '0.0',
                amount decimal(20,2) NOT NULL DEFAULT '0.00',
                total decimal(20,2) NOT NULL DEFAULT '0.00',
                f_year smallint(5) UNSIGNED NOT NULL,
                created_at int(11) DEFAULT NULL,
                updated_at int(11) DEFAULT NULL,
                PRIMARY KEY  (id),
                KEY `user_id` (`user_id`),
                KEY leave_id (leave_id),
                KEY f_year (f_year),
                KEY leave_request_id (leave_request_id),
                KEY leave_approval_status_id (leave_approval_status_id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_financial_years (
                id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                fy_name varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `start_date` int(11) DEFAULT NULL,
                end_date int(11) DEFAULT NULL,
                `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                created_by bigint(20) UNSIGNED DEFAULT NULL,
                updated_by bigint(20) UNSIGNED DEFAULT NULL,
                created_at int(11) DEFAULT NULL,
                updated_at int(11) DEFAULT NULL,
                PRIMARY KEY  (id),
                KEY year_search (start_date,end_date),
                KEY start_date (start_date),
                KEY end_date (end_date)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_user_leaves` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` int(11) DEFAULT NULL,
                `request_id` int(11) DEFAULT NULL,
                `title` varchar(255) DEFAULT NULL,
                `date` date DEFAULT NULL,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_holidays_indv` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `holiday_id` int(11) DEFAULT NULL,
                `title` varchar(255) DEFAULT NULL,
                `date` date DEFAULT NULL,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_hr_holiday` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `title` varchar(200) NOT NULL,
                `start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                `description` text NOT NULL,
                `range_status` varchar(5) NOT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_hr_work_exp` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `employee_id` int(11) DEFAULT NULL,
                `company_name` varchar(100) DEFAULT NULL,
                `job_title` varchar(100) DEFAULT NULL,
                `from` date DEFAULT NULL,
                `to` date DEFAULT NULL,
                `description` text,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `employee_id` (`employee_id`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_hr_education` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `employee_id` int(11) unsigned DEFAULT NULL,
                `school` varchar(100) DEFAULT NULL,
                `degree` varchar(100) DEFAULT NULL,
                `field` varchar(100) DEFAULT NULL,
                `result` varchar(50) DEFAULT NULL,
                `result_type` enum('grade', 'percentage') DEFAULT NULL,
                `finished` int(4) unsigned DEFAULT NULL,
                `notes` text,
                `interest` text,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `employee_id` (`employee_id`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_hr_dependents` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `employee_id` int(11) DEFAULT NULL,
                `name` varchar(100) DEFAULT NULL,
                `relation` varchar(100) DEFAULT NULL,
                `dob` date DEFAULT NULL,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `employee_id` (`employee_id`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_hr_employee_performance` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `employee_id` int(11) unsigned DEFAULT NULL,
                `reporting_to` int(11) unsigned DEFAULT NULL,
                `job_knowledge` varchar(100) DEFAULT NULL,
                `work_quality` varchar(100) DEFAULT NULL,
                `attendance` varchar(100) DEFAULT NULL,
                `communication` varchar(100) DEFAULT NULL,
                `dependablity` varchar(100) DEFAULT NULL,
                `reviewer` int(11) unsigned DEFAULT NULL,
                `comments` text,
                `completion_date` datetime DEFAULT NULL,
                `goal_description` text,
                `employee_assessment` text,
                `supervisor` int(11) unsigned DEFAULT NULL,
                `supervisor_assessment` text,
                `type` text,
                `performance_date` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `employee_id` (`employee_id`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_hr_announcement` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) unsigned NOT NULL,
                `post_id` bigint(11) NOT NULL,
                `status` varchar(30) NOT NULL,
                `email_status` varchar(30) NOT NULL,
                PRIMARY KEY (id),
                KEY `user_id` (`user_id`),
                KEY `post_id` (`post_id`),
                KEY `status` (`status`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_peoples` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) unsigned DEFAULT '0',
                `first_name` varchar(60) DEFAULT NULL,
                `last_name` varchar(60) DEFAULT NULL,
                `company` varchar(60) DEFAULT NULL,
                `email` varchar(100) DEFAULT NULL,
                `phone` varchar(20) DEFAULT NULL,
                `mobile` varchar(20) DEFAULT NULL,
                `other` varchar(50) DEFAULT NULL,
                `website` varchar(100) DEFAULT NULL,
                `fax` varchar(20) DEFAULT NULL,
                `notes` text,
                `street_1` varchar(255) DEFAULT NULL,
                `street_2` varchar(255) DEFAULT NULL,
                `city` varchar(80) DEFAULT NULL,
                `state` varchar(50) DEFAULT NULL,
                `postal_code` varchar(10) DEFAULT NULL,
                `country` varchar(20) DEFAULT NULL,
                `currency` varchar(5) DEFAULT NULL,
                `life_stage` VARCHAR(100) DEFAULT NULL,
                `contact_owner` bigint(20) DEFAULT NULL,
                `hash` VARCHAR(40) DEFAULT NULL,
                `created_by` BIGINT(20) DEFAULT NULL,
                `created` datetime DEFAULT NULL,
                PRIMARY KEY  (`id`),
                KEY `user_id` (`user_id`),
                KEY `first_name` (`first_name`),
                KEY `last_name` (`last_name`),
                KEY `email` (`email`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_peoplemeta` (
                `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `erp_people_id` bigint(20) DEFAULT NULL,
                `meta_key` varchar(255) DEFAULT NULL,
                `meta_value` longtext,
                PRIMARY KEY (`meta_id`),
                KEY `erp_people_id` (`erp_people_id`),
                KEY `meta_key` (`meta_key`(191))
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_people_types` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(20) DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `name` (`name`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_people_type_relations` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `people_id` bigint(20) unsigned DEFAULT NULL,
                `people_types_id` int(11) unsigned DEFAULT NULL,
                `deleted_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `people_id` (`people_id`),
                KEY `people_types_id` (`people_types_id`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_audit_log` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `component` varchar(50) NOT NULL DEFAULT '',
                `sub_component` varchar(50) NOT NULL DEFAULT '',
                `data_id` bigint(20) DEFAULT NULL,
                `old_value` longtext,
                `new_value` longtext,
                `message` longtext,
                `changetype` varchar(10) DEFAULT NULL,
                `created_by` bigint(20) unsigned DEFAULT NULL,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `component` (`component`),
                KEY `sub_component` (`sub_component`),
                KEY `changetype` (`changetype`),
                KEY `created_by` (`created_by`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_crm_customer_companies` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `customer_id` bigint(20) DEFAULT NULL,
                `company_id` bigint(50) DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `customer_id` (`customer_id`),
                KEY `company_id` (`company_id`)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_crm_customer_activities (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` int(11) DEFAULT NULL,
                `type` varchar(255) DEFAULT NULL,
                `message` longtext,
                email_subject text,
                log_type varchar(255) DEFAULT NULL,
                `start_date` datetime DEFAULT NULL,
                end_date datetime DEFAULT NULL,
                created_by int(11) DEFAULT NULL,
                extra longtext,
                sent_notification tinyint(4) DEFAULT '0',
                done_at datetime DEFAULT NULL,
                created_at datetime DEFAULT NULL,
                updated_at datetime DEFAULT NULL,
                PRIMARY KEY  (id),
                KEY `user_id` (`user_id`),
                KEY `type` (`type`),
                KEY log_type (log_type),
                KEY created_by (created_by)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_crm_activities_task` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `activity_id` int(11) DEFAULT NULL,
                `user_id` int(11) DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `activity_id` (`activity_id`),
                KEY `user_id` (`user_id`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_crm_contact_group` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(255) DEFAULT NULL,
                `description` text,
                `private` TINYINT(1) DEFAULT NULL,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY  (`id`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_crm_contact_subscriber` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` int(11) DEFAULT NULL,
                `group_id` int(11) DEFAULT NULL,
                `status` varchar(25) DEFAULT NULL,
                `subscribe_at` datetime DEFAULT NULL,
                `unsubscribe_at` datetime DEFAULT NULL,
                `hash` VARCHAR(40) DEFAULT NULL,
                PRIMARY KEY  (`id`),
                UNIQUE KEY `user_group` (`user_id`,`group_id`),
                KEY `status` (`status`),
                KEY `hash` (`hash`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_crm_save_search` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `user_id` int(11) DEFAULT NULL,
              `type` VARCHAR(255) DEFAULT NULL,
              `global` tinyint(4) DEFAULT '0',
              `search_name` text,
              `search_val` text,
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_crm_save_email_replies` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `name` text,
              `subject` text,
              `template` longtext,
              PRIMARY KEY (`id`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_voucher_no` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `type` varchar(255) DEFAULT NULL,
                `currency` varchar(50) DEFAULT NULL,
                `editable` tinyint DEFAULT 0,
                `created_at` date DEFAULT NULL,
                `created_by` varchar(50) DEFAULT NULL,
                `updated_at` date DEFAULT NULL,
                `updated_by` varchar(50) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_bill_account_details` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `bill_no` int(11) DEFAULT NULL,
                `trn_no` int(11) DEFAULT NULL,
                `trn_date` date DEFAULT NULL,
                `particulars` varchar(255) DEFAULT NULL,
                `debit` decimal(20,2) DEFAULT 0,
                `credit` decimal(20,2) DEFAULT 0,
                `created_at` date DEFAULT NULL,
                `created_by` varchar(50) DEFAULT NULL,
                `updated_at` date DEFAULT NULL,
                `updated_by` varchar(50) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_bill_details` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `trn_no` int(11) DEFAULT NULL,
                `ledger_id` int(11) DEFAULT NULL,
                `particulars` varchar(255) DEFAULT NULL,
                `amount` decimal(20,2) DEFAULT 0,
                `created_at` date DEFAULT NULL,
                `created_by` varchar(50) DEFAULT NULL,
                `updated_at` date DEFAULT NULL,
                `updated_by` varchar(50) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_bills` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `voucher_no` int(11) DEFAULT NULL,
                `vendor_id` int(11) DEFAULT NULL,
                `vendor_name` varchar(255) DEFAULT NULL,
                `address` varchar(255) DEFAULT NULL,
                `trn_date` date DEFAULT NULL,
                `due_date` date DEFAULT NULL,
                `ref` varchar(255) DEFAULT NULL,
                `amount` decimal(20,2) DEFAULT 0,
                `particulars` varchar(255) DEFAULT NULL,
                `status` int(11) DEFAULT NULL,
                `attachments` varchar(255) DEFAULT NULL,
                `created_at` date DEFAULT NULL,
                `created_by` varchar(50) DEFAULT NULL,
                `updated_at` date DEFAULT NULL,
                `updated_by` varchar(50) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_chart_of_accounts` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) DEFAULT NULL,
                `slug` varchar(255) DEFAULT NULL,
                `created_at` date DEFAULT NULL,
                `created_by` varchar(50) DEFAULT NULL,
                `updated_at` date DEFAULT NULL,
                `updated_by` varchar(50) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_currency_info` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) DEFAULT NULL,
                `sign` varchar(255) DEFAULT NULL,
                `created_at` date DEFAULT NULL,
                `created_by` varchar(50) DEFAULT NULL,
                `updated_at` date DEFAULT NULL,
                `updated_by` varchar(50) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_invoice_account_details` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `invoice_no` int(11) DEFAULT NULL,
                `trn_no` int(11) DEFAULT NULL,
                `trn_date` date DEFAULT NULL,
                `particulars` varchar(255) DEFAULT NULL,
                `debit` decimal(20,2) DEFAULT 0,
                `credit` decimal(20,2) DEFAULT 0,
                `created_at` date DEFAULT NULL,
                `created_by` varchar(50) DEFAULT NULL,
                `updated_at` date DEFAULT NULL,
                `updated_by` varchar(50) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_acct_invoice_details (
                id int(11) NOT NULL AUTO_INCREMENT,
                trn_no int(11) DEFAULT NULL,
                product_id int(11) DEFAULT NULL,
                qty decimal(10,2) DEFAULT NULL,
                unit_price decimal(20,2) DEFAULT 0,
                discount decimal(20,2) DEFAULT 0,
                shipping decimal(20,2) DEFAULT 0,
                tax decimal(20,2) DEFAULT 0,
                tax_cat_id int(11) DEFAULT NULL,
                item_total decimal(20,2) DEFAULT 0,
                ecommerce_type varchar(255) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_invoice_receipts` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `voucher_no` int(11) DEFAULT NULL,
                `customer_id` int(11) DEFAULT NULL,
                `customer_name` varchar(255) DEFAULT NULL,
                `trn_date` date DEFAULT NULL,
                `amount` decimal(20,2) DEFAULT 0,
                `transaction_charge` decimal(20,2) DEFAULT 0,
                `ref` varchar(255) DEFAULT NULL,
                `particulars` varchar(255) DEFAULT NULL,
                `attachments` varchar(255) DEFAULT NULL,
                `status` int(11) DEFAULT NULL,
                `trn_by` varchar(255) DEFAULT NULL,
                `trn_by_ledger_id` int(11) DEFAULT NULL,
                `created_at` date DEFAULT NULL,
                `created_by` varchar(50) DEFAULT NULL,
                `updated_at` date DEFAULT NULL,
                `updated_by` varchar(50) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_invoice_receipts_details` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `voucher_no` int(11) DEFAULT NULL,
                `invoice_no` int(11) DEFAULT NULL,
                `amount` decimal(20,2) DEFAULT 0,
                `created_at` date DEFAULT NULL,
                `created_by` varchar(50) DEFAULT NULL,
                `updated_at` date DEFAULT NULL,
                `updated_by` varchar(50) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_invoices` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `voucher_no` int(11) DEFAULT NULL,
                `customer_id` int(11) DEFAULT NULL,
                `customer_name` varchar(255) DEFAULT NULL,
                `trn_date` date DEFAULT NULL,
                `due_date` date DEFAULT NULL,
                `billing_address` varchar(255) DEFAULT NULL,
                `amount` decimal(20,2) DEFAULT 0,
                `discount` decimal(20,2) DEFAULT 0,
                `discount_type` varchar(255) DEFAULT NULL,
                `shipping` decimal(20,2) DEFAULT 0,
                `shipping_tax` decimal(20,2) DEFAULT 0,
                `tax` decimal(20,2) DEFAULT 0,
                `tax_zone_id` int(11) DEFAULT NULL,
                `estimate` boolean DEFAULT NULL,
                `attachments` varchar(255) DEFAULT NULL,
                `status` int(11) DEFAULT NULL,
                `particulars` varchar(255) DEFAULT NULL,
                `additional_notes` TEXT NULL DEFAULT NULL,
                `created_at` date DEFAULT NULL,
                `created_by` varchar(50) DEFAULT NULL,
                `updated_at` date DEFAULT NULL,
                `updated_by` varchar(50) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_journal_details` (
                id int(11) NOT NULL AUTO_INCREMENT,
                trn_no int(11) DEFAULT NULL,
                ledger_id int(11) DEFAULT NULL,
                particulars varchar(255) DEFAULT NULL,
                debit decimal(20,2) DEFAULT 0,
                credit decimal(20,2) DEFAULT 0,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_journals` (
                id int(11) NOT NULL AUTO_INCREMENT,
                trn_date date DEFAULT NULL,
                ref varchar(255) DEFAULT NULL,
                voucher_no int(11) DEFAULT NULL,
                voucher_amount decimal(20,2) DEFAULT 0,
                particulars varchar(255) DEFAULT NULL,
                attachments varchar(255) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_ledger_categories` (
                id int(11) NOT NULL AUTO_INCREMENT,
                name varchar(255) DEFAULT NULL,
                slug varchar(255) DEFAULT NULL,
                chart_id int(11) DEFAULT NULL,
                parent_id int(11) DEFAULT NULL,
                `system` tinyint(1) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_ledger_details` (
                id int(11) NOT NULL AUTO_INCREMENT,
                ledger_id int(11) DEFAULT NULL,
                trn_no int(11) DEFAULT NULL,
                particulars varchar(255) DEFAULT NULL,
                debit decimal(20,2) DEFAULT 0,
                credit decimal(20,2) DEFAULT 0,
                trn_date date DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_ledger_settings` (
                id int(11) NOT NULL AUTO_INCREMENT,
                ledger_id int(11) DEFAULT NULL,
                short_code varchar(255) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_ledgers` (
                id int(11) NOT NULL AUTO_INCREMENT,
                chart_id int(11) DEFAULT NULL,
                category_id int(11) DEFAULT NULL,
                `name` varchar(255) DEFAULT NULL,
                slug varchar(255) DEFAULT NULL,
                code int(11) DEFAULT NULL,
                unused tinyint(1) DEFAULT NULL,
                `system` tinyint(1) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_cash_at_banks` (
                id int(11) NOT NULL AUTO_INCREMENT,
                ledger_id int(11) DEFAULT NULL,
                `name` varchar(255) DEFAULT NULL,
                balance decimal(20,2) DEFAULT 0,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_transfer_voucher` (
                id int(11) NOT NULL AUTO_INCREMENT,
                voucher_no int(11) DEFAULT NULL,
                trn_date date DEFAULT NULL,
                amount decimal(20,2) DEFAULT NULL,
                ac_from int(11) DEFAULT NULL,
                ac_to int(11) DEFAULT NULL,
                particulars varchar(255) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_opening_balances` (
                id int(11) NOT NULL AUTO_INCREMENT,
                financial_year_id int(11) DEFAULT NULL,
                chart_id int(11) DEFAULT NULL,
                ledger_id int(11) DEFAULT NULL,
                `type` varchar(50) DEFAULT NULL,
                debit decimal(20,2) DEFAULT 0,
                credit decimal(20,2) DEFAULT 0,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_financial_years` (
                id int(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) DEFAULT NULL,
                `start_date` date DEFAULT NULL,
                end_date date DEFAULT NULL,
                `description` varchar(255) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_pay_bill` (
                id int(11) NOT NULL AUTO_INCREMENT,
                voucher_no int(11) DEFAULT NULL,
                vendor_id int(11) DEFAULT NULL,
                vendor_name varchar(255) DEFAULT NULL,
                trn_date date DEFAULT NULL,
                amount decimal(20,2) DEFAULT 0,
                trn_by varchar(255) DEFAULT NULL,
                trn_by_ledger_id int(11) DEFAULT NULL,
                particulars varchar(255) DEFAULT NULL,
                ref varchar(255) DEFAULT NULL,
                attachments varchar(255) DEFAULT NULL,
                `status` int(11) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_pay_bill_details` (
                id int(11) NOT NULL AUTO_INCREMENT,
                voucher_no int(11) DEFAULT NULL,
                bill_no int(11) DEFAULT NULL,
                amount decimal(20,2) DEFAULT 0,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_pay_purchase` (
                id int(11) NOT NULL AUTO_INCREMENT,
                voucher_no int(11) DEFAULT NULL,
                vendor_id int(11) DEFAULT NULL,
                vendor_name varchar(255) DEFAULT NULL,
                trn_date date DEFAULT NULL,
                amount decimal(20,2) DEFAULT 0,
                trn_by varchar(255) DEFAULT NULL,
                transaction_charge decimal(20,2) DEFAULT 0,
                ref varchar(255) DEFAULT NULL,
                trn_by_ledger_id int(11) DEFAULT NULL,
                particulars varchar(255) DEFAULT NULL,
                attachments varchar(255) DEFAULT NULL,
                `status` int(11) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_acct_pay_purchase_details (
                id int(11) NOT NULL AUTO_INCREMENT,
                voucher_no int(11) DEFAULT NULL,
                purchase_no int(11) DEFAULT NULL,
                amount decimal(20,2) DEFAULT 0,
                tax_cat_id int(11) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_people_account_details` (
                id int(11) NOT NULL AUTO_INCREMENT,
                people_id varchar(255) DEFAULT NULL,
                trn_no int(11) DEFAULT NULL,
                trn_date date DEFAULT NULL,
                trn_by varchar(255) DEFAULT NULL,
                voucher_type varchar(255) DEFAULT NULL,
                particulars varchar(255) DEFAULT NULL,
                debit decimal(20,2) DEFAULT 0,
                credit decimal(20,2) DEFAULT 0,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_people_trn` (
                id int(11) NOT NULL AUTO_INCREMENT,
                people_id varchar(255) DEFAULT NULL,
                voucher_no int(11) DEFAULT NULL,
                amount decimal(20,2) DEFAULT 0,
                trn_date date DEFAULT NULL,
                trn_by varchar(255) DEFAULT NULL,
                particulars varchar(255) DEFAULT NULL,
                voucher_type varchar(255) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_people_trn_details` (
                id int(11) NOT NULL AUTO_INCREMENT,
                people_id varchar(255) DEFAULT NULL,
                voucher_no int(11) DEFAULT NULL,
                trn_date date DEFAULT NULL,
                particulars varchar(255) DEFAULT NULL,
                debit decimal(20,2) DEFAULT 0,
                credit decimal(20,2) DEFAULT 0,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_product_categories` (
                id int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) DEFAULT NULL,
                parent int(11) NOT NULL DEFAULT 0,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_product_types` (
                id int(11) NOT NULL AUTO_INCREMENT,
                name varchar(255) DEFAULT NULL,
                slug varchar(255) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_products` (
                id int(11) NOT NULL AUTO_INCREMENT,
                name varchar(255) DEFAULT NULL,
                product_type_id int(11) DEFAULT NULL,
                category_id int(11) DEFAULT NULL,
                tax_cat_id int(11) DEFAULT NULL,
                vendor int(11) DEFAULT NULL,
                cost_price decimal(20,2) DEFAULT 0,
                sale_price decimal(20,2) DEFAULT 0,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_product_details` (
                id int(11) NOT NULL AUTO_INCREMENT,
                product_id int(11) DEFAULT NULL,
                trn_no int(11) DEFAULT NULL,
                stock_in int(11) DEFAULT NULL,
                stock_out int(11) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_purchase` (
                id int(11) NOT NULL AUTO_INCREMENT,
                voucher_no int(11) DEFAULT NULL,
                vendor_id int(11) DEFAULT NULL,
                vendor_name varchar(255) DEFAULT NULL,
                billing_address varchar(255) DEFAULT NULL,
                trn_date date DEFAULT NULL,
                due_date date DEFAULT NULL,
                amount decimal(20,2) DEFAULT 0,
                tax decimal(20,2) DEFAULT NULL ,
                tax_zone_id integer DEFAULT NULL,
                ref varchar(255) DEFAULT NULL,
                `status` int(11) DEFAULT NULL,
                purchase_order boolean DEFAULT NULL,
                attachments varchar(255) DEFAULT NULL,
                particulars varchar(255) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_purchase_account_details` (
                id int(11) NOT NULL AUTO_INCREMENT,
                purchase_no int(11) DEFAULT NULL,
                trn_no int(11) DEFAULT NULL,
                trn_date date DEFAULT NULL,
                particulars varchar(255) DEFAULT NULL,
                debit decimal(20,2) DEFAULT 0,
                credit decimal(20,2) DEFAULT 0,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_acct_purchase_details (
                id int(11) NOT NULL AUTO_INCREMENT,
                trn_no int(11) DEFAULT NULL,
                product_id int(11) DEFAULT NULL,
                qty decimal(10,2) DEFAULT NULL,
                price decimal(20,2) DEFAULT 0,
                amount decimal(20,2) DEFAULT 0,
                tax decimal(20,2) DEFAULT NULL,
                tax_cat_id int(11) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_acct_purchase_details_tax (
                id int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                invoice_details_id int(20) NOT NULL,
                agency_id int(20) DEFAULT NULL,
                tax_rate decimal(20,2) NOT NULL,
                created_at datetime DEFAULT NULL,
                created_by int(20) DEFAULT NULL,
                updated_at datetime DEFAULT NULL,
                updated_by int(20) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_tax_categories` (
                id int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(100) DEFAULT NULL,
                `description` varchar(255) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_taxes` (
                id int(11) NOT NULL AUTO_INCREMENT,
                tax_rate_name varchar(255) DEFAULT NULL,
                tax_number varchar(100) DEFAULT NULL,
                `default` boolean DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_tax_cat_agency` (
                id int(11) NOT NULL AUTO_INCREMENT,
                tax_id int(11) DEFAULT NULL,
                component_name varchar(255) DEFAULT NULL,
                tax_cat_id int(11) DEFAULT NULL,
                agency_id int(11) DEFAULT NULL,
                tax_rate decimal(20,2) DEFAULT 0,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_tax_agencies` (
                id int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) DEFAULT NULL,
                ecommerce_type varchar(255) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_tax_pay` (
                id int(11) NOT NULL AUTO_INCREMENT,
                voucher_no int(11) DEFAULT NULL,
                trn_date date DEFAULT NULL,
                particulars varchar(255) DEFAULT NULL,
                amount decimal(20,2) DEFAULT 0,
                voucher_type varchar(255) DEFAULT NULL,
                trn_by int(11) DEFAULT NULL,
                agency_id int(11) DEFAULT NULL,
                ledger_id int(11) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_tax_agency_details` (
                id int(11) NOT NULL AUTO_INCREMENT,
                agency_id int(11) DEFAULT NULL,
                trn_no int(11) DEFAULT NULL,
                trn_date date DEFAULT NULL,
                particulars varchar(255) DEFAULT NULL,
                debit decimal(20, 2) DEFAULT 0,
                credit decimal(20, 2) DEFAULT 0,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_invoice_details_tax` (
                id int(11) NOT NULL AUTO_INCREMENT,
                invoice_details_id int(11) DEFAULT NULL,
                agency_id int(11) DEFAULT NULL,
                tax_rate decimal(20,2) DEFAULT 0,
                tax_amount decimal(20,2) DEFAULT 0,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_trn_status_types` (
                id int(11) NOT NULL AUTO_INCREMENT,
                type_name varchar(255) DEFAULT NULL,
                slug varchar(255) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_acct_synced_taxes (
                id int NOT NULL AUTO_INCREMENT,
                system_id bigint NOT NULL,
                sync_id bigint DEFAULT NULL,
                sync_slug varchar(100) DEFAULT NULL,
                sync_type varchar(100) DEFAULT NULL,
                sync_source varchar(100) DEFAULT NULL,
                PRIMARY KEY  (id),
                KEY system_id (system_id),
                KEY sync_id (sync_id),
                KEY sync_slug (sync_slug),
                KEY sync_type (sync_type),
                KEY sync_source (sync_source)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_payment_methods` (
                id int(11) NOT NULL AUTO_INCREMENT,
                name varchar(255) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_expenses` (
                id int(11) NOT NULL AUTO_INCREMENT,
                voucher_no int(11) DEFAULT NULL,
                people_id int(11) DEFAULT NULL,
                people_name varchar(255) DEFAULT NULL,
                address varchar(255) DEFAULT NULL,
                trn_date date DEFAULT NULL,
                amount decimal(20,2) DEFAULT 0,
                ref varchar(255) DEFAULT NULL,
                check_no varchar(255) DEFAULT NULL,
                particulars varchar(255) DEFAULT NULL,
                status int(11) DEFAULT NULL,
                trn_by int(11) DEFAULT NULL,
                transaction_charge decimal(20,2) DEFAULT 0,
                trn_by_ledger_id int(11) DEFAULT NULL,
                attachments varchar(255) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_expense_details` (
                id int(11) NOT NULL AUTO_INCREMENT,
                trn_no int(11) DEFAULT NULL,
                ledger_id int(11) DEFAULT NULL,
                particulars varchar(255) DEFAULT NULL,
                amount decimal(20,2) DEFAULT 0,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_acct_expense_checks` (
                id int(11) NOT NULL AUTO_INCREMENT,
                trn_no int(11) DEFAULT NULL,
                check_no varchar(255) DEFAULT NULL,
                voucher_type varchar(255) DEFAULT NULL,
                amount decimal(20,2) DEFAULT 0,
                bank varchar(255) DEFAULT NULL,
                `name` varchar(255) DEFAULT NULL,
                pay_to varchar(255) DEFAULT NULL,
                created_at date DEFAULT NULL,
                created_by varchar(50) DEFAULT NULL,
                updated_at date DEFAULT NULL,
                updated_by varchar(50) DEFAULT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",
        ];

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        foreach ( $table_schema as $table ) {
              $erp_table_created =  dbDelta( $table );
        }
    }

    /**
     * Populate tables with initial data
     *
     * @return void
     */
    public function populate_data() {
        global $wpdb;
        $current_date = gmdate( 'Y-m-d' );

        // Subscription pages
        $subscription_settings = get_option( 'erp_settings_erp-crm_subscription', [] );

        if ( empty( $subscription_settings ) ) {
            // insert default erp subscription form settings
            $args = [
                'post_title'     => __( 'ERP Subscription', 'erp' ),
                'post_content'   => '',
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
            ];

            $page_id = wp_insert_post( $args );

            $settings = [
                'is_enabled'           => 'yes',
                'email_subject'        => sprintf( __( 'Confirm your subscription to %s', 'erp' ), get_bloginfo( 'name' ) ),
                'email_content'        => sprintf(
                    __( "Hello!\n\nThanks so much for signing up for our newsletter.\nWe need you to activate your subscription to the list(s): [contact_groups_to_confirm] by clicking the link below: \n\n[activation_link]Click here to confirm your subscription.[/activation_link]\n\nThank you,\n\n%s", 'erp' ),
                    get_bloginfo( 'name' )
                ),
                'page_id'              => $page_id,
                'confirm_page_title'   => __( 'You are now subscribed!', 'erp' ),
                'confirm_page_content' => __( "We've added you to our email list. You'll hear from us shortly.", 'erp' ),
                'unsubs_page_title'    => __( 'You are now unsubscribed', 'erp' ),
                'unsubs_page_content'  => __( 'You are successfully unsubscribed from list(s):', 'erp' ),
            ];

            update_option( 'erp_settings_erp-crm_subscription', $settings );
        }

        // check if people_types exists
        if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_people_types` LIMIT 0, 1" ) ) {
            $wpdb->query( "INSERT INTO `{$wpdb->prefix}erp_people_types` (`id`, `name`)
            VALUES (1, 'contact'), (2, 'company'), (3, 'customer'), (4, 'vendor'), (5, 'employee')" );
        }

        // Add Standard Preset Data for Department in erp_hr_depts
        if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_hr_depts` LIMIT 0, 1" ) ) {
            $wpdb->query(
                "INSERT INTO `{$wpdb->prefix}erp_hr_depts` (`id`, `title`, `created_at`, `updated_at`)
                VALUES (1, 'General Management', '{$current_date}', '{$current_date}'),
                (2, 'Operations Department', '{$current_date}', '{$current_date}'),
                (3, 'Finance Department', '{$current_date}', '{$current_date}'),
                (4, 'Sales Department', '{$current_date}', '{$current_date}'),
                (5, 'Human Resource Department', '{$current_date}', '{$current_date}'),
                (6, 'Purchase Department', '{$current_date}', '{$current_date}'),
                (7, 'Engineering Department', '{$current_date}', '{$current_date}'),
                (8, 'Production Department', '{$current_date}', '{$current_date}'),
                (9, 'Procurement Department', '{$current_date}', '{$current_date}')"
            );
        }

        // Add Standard Preset Data for Designation in erp_hr_designations
        if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_hr_designations` LIMIT 0, 1" ) ) {
            $wpdb->query(
                "INSERT INTO `{$wpdb->prefix}erp_hr_designations` (`id`, `title`, `created_at`, `updated_at`)
                VALUES (1, 'President', '{$current_date}', '{$current_date}'),
                (2, 'Vice President', '{$current_date}', '{$current_date}'),
                (3, 'CEO', '{$current_date}', '{$current_date}'),
                (4, 'Managing Director', '{$current_date}', '{$current_date}'),
                (5, 'Product Manager', '{$current_date}', '{$current_date}'),
                (6, 'Project Manager', '{$current_date}', '{$current_date}'),
                (7, 'Program Manager', '{$current_date}', '{$current_date}'),
                (8, 'Operations Manager', '{$current_date}', '{$current_date}'),
                (9, 'Marketing Manager', '{$current_date}', '{$current_date}'),
                (10, 'Business Manager', '{$current_date}', '{$current_date}'),
                (11, 'Technology Manager', '{$current_date}', '{$current_date}'),
                (12, 'Finance/Accounts Manager', '{$current_date}', '{$current_date}'),
                (13, 'Human Resource Manager', '{$current_date}', '{$current_date}'),
                (14, 'Hiring Manager', '{$current_date}', '{$current_date}'),
                (15, 'Senior Engineer', '{$current_date}', '{$current_date}'),
                (16, 'Engineer', '{$current_date}', '{$current_date}'),
                (17, 'Junior Engineer', '{$current_date}', '{$current_date}'),
                (18, 'Business Executive', '{$current_date}', '{$current_date}'),
                (19, 'Marketing Executive', '{$current_date}', '{$current_date}'),
                (20, 'Customer Support Executive', '{$current_date}', '{$current_date}')"
            );
        }

        /* ===========
         * Accounting
         * ============
         */

        // insert chart of accounts
        if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_acct_chart_of_accounts` LIMIT 0, 1" ) ) {
            $charts = [ 'Asset', 'Liability', 'Equity', 'Income', 'Expense', 'Asset & Liability', 'Bank' ];


            for ( $i = 0; $i < count( $charts ); $i++ ) {
                $wpdb->insert( "{$wpdb->prefix}erp_acct_chart_of_accounts", [
                    'name' => $charts[ $i ],
                    'slug' => slugify( $charts[ $i ] ),
                ] );
            }
        }

        // insert ledgers
        $old_ledgers = [];
        $ledgers     = [];

        require_once WPERP_INCLUDES . '/ledgers.php';

        // insert ledgers
        if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_acct_ledgers` LIMIT 0, 1" ) ) {
            $old_codes = [];

            foreach ( $old_ledgers as $value ) {
                $old_codes[] = $value['code'];

                $wpdb->insert(
                    "{$wpdb->prefix}erp_acct_ledgers",
                    [
                        'chart_id' => $value['chart_id'],
                        'name'     => $value['name'],
                        'slug'     => slugify( $value['name'] ),
                        'code'     => $value['code'],
                        'unused'   => isset( $value['unused'] ) ? $value['unused'] : null,
                        'system'   => $value['system'],
                    ]
                );
            }

            if (! function_exists( 'erp_acct_get_chart_id_by_slug' ) ) {
                require_once WPERP_PATH . '/modules/accounting/includes/functions/ledger-accounts.php';
            }
            foreach ( array_keys( $ledgers ) as $array_key ) {
                foreach ( $ledgers[ $array_key ] as $value ) {
                    if ( in_array( $value['code'], $old_codes ) ) {
                        $value['code'] = $value['code'] . '0';
                    }

                    $wpdb->insert(
                        "{$wpdb->prefix}erp_acct_ledgers",
                        [
                            'chart_id' => erp_acct_get_chart_id_by_slug( $array_key ),
                            'name'     => $value['name'],
                            'slug'     => slugify( $value['name'] ),
                            'code'     => $value['code'],
                            'system'   => $value['system'],
                        ]
                    );
                }
            }
            update_option( 'erp_acct_new_ledgers', true );
        }

        // insert ledger categories
        if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_acct_ledger_categories` LIMIT 0, 1" ) ) {
            $wpdb->query( "INSERT INTO `{$wpdb->prefix}erp_acct_ledger_categories`
                (id,name,chart_id)
                    VALUES
                (1,'Current Asset',1),
                (2,'Fixed Asset',1),
                (3,'Inventory',1),
                (4,'Non-current Asset',1),
                (5,'Prepayment',1),
                (6,'Bank & Cash',1),
                (7,'Current Liability',2),
                (8,'Liability',2),
                (9,'Non-current Liability',2),
                (10,'Depreciation',3),
                (11,'Direct Costs',3),
                (12,'Expense',3),
                (13,'Revenue',4),
                (14,'Sales',4),
                (15,'Other Income',4),
                (16,'Equity',5);" );
        }

        // insert payment methods
        if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_acct_payment_methods` LIMIT 0, 1" ) ) {
            $methods = [ 'Cash', 'Bank', 'Check' ];

            for ( $i = 0; $i < count( $methods ); $i++ ) {
                $wpdb->insert( "{$wpdb->prefix}erp_acct_payment_methods", [
                    'name' => $methods[ $i ],
                ] );
            }
        }

        // insert status types
        if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_acct_trn_status_types` LIMIT 0, 1" ) ) {
            $statuses = [
                'Draft',
                'Awaiting Payment',
                'Pending',
                'Paid',
                'Partially Paid',
                'Approved',
                'Closed',
                'Void',
                'Returned',
                'Partially Returned',
            ];

            for ( $i = 0; $i < count( $statuses ); $i++ ) {
                $wpdb->insert( "{$wpdb->prefix}erp_acct_trn_status_types", [
                    'type_name' => $statuses[ $i ],
                    'slug'      => slugify( $statuses[ $i ] ),
                ] );
            }
        }

        // insert product types
        if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_acct_product_types` LIMIT 0, 1" ) ) {
            $wpdb->query( "INSERT INTO `{$wpdb->prefix}erp_acct_product_types` (`id`, `name`, `slug`)
            VALUES (1, 'Inventory', 'inventory'), (2, 'Service', 'service')" );
        }

        // insert currency info
        if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_acct_currency_info` LIMIT 0, 1" ) ) {
            $currency_symbols = [
                'AED' => 'د.إ',
                'AFN' => '؋',
                'ALL' => 'L',
                'AMD' => 'AMD',
                'ANG' => 'ƒ',
                'AOA' => 'Kz',
                'ARS' => '$',
                'AUD' => '$',
                'AWG' => 'ƒ',
                'AZN' => '₼',
                'BAM' => 'KM',
                'BBD' => '$',
                'BDT' => '৳',
                'BGN' => 'лв',
                'BHD' => '.د.ب',
                'BIF' => 'Fr',
                'BMD' => '$',
                'BND' => '$',
                'BOB' => 'Bs.',
                'BRL' => 'R$',
                'BSD' => '$',
                'BTN' => 'Nu.',
                'BWP' => 'P',
                'BYN' => 'Br',
                'BYR' => 'Br',
                'BZD' => '$',
                'CAD' => '$',
                'CDF' => 'Fr',
                'CHF' => 'Fr',
                'CLP' => '$',
                'CNY' => '¥',
                'COP' => '$',
                'CRC' => '₡',
                'CUC' => '$',
                'CUP' => '$',
                'CVE' => '$',
                'CZK' => 'Kč',
                'DJF' => 'Fr',
                'DKK' => 'kr',
                'DOP' => '$',
                'DZD' => 'د.ج',
                'EGP' => '£',
                'ERN' => 'Nfk',
                'ETB' => 'Br',
                'EUR' => '€',
                'FJD' => '$',
                'FKP' => '£',
                'GBP' => '£',
                'GEL' => 'GEL',
                'GGP' => '£',
                'GHS' => '₵',
                'GIP' => '£',
                'GMD' => 'D',
                'GNF' => 'Fr',
                'GTQ' => 'Q',
                'GYD' => '$',
                'HKD' => '$',
                'HNL' => 'L',
                'HRK' => 'kn',
                'HTG' => 'G',
                'HUF' => 'Ft',
                'IDR' => 'Rp',
                'ILS' => '₪',
                'IMP' => '£',
                'INR' => '₹',
                'IQD' => 'ع.د',
                'IRR' => '﷼',
                'ISK' => 'kr',
                'JEP' => '£',
                'JMD' => '$',
                'JOD' => 'د.ا',
                'JPY' => '¥',
                'KES' => 'Sh',
                'KGS' => 'с',
                'KHR' => '៛',
                'KMF' => 'Fr',
                'KPW' => '₩',
                'KRW' => '₩',
                'KWD' => 'د.ك',
                'KYD' => '$',
                'KZT' => 'KZT',
                'LAK' => '₭',
                'LBP' => 'ل.ل',
                'LKR' => 'Rs',
                'LRD' => '$',
                'LSL' => 'L',
                'LYD' => 'ل.د',
                'MAD' => 'د.م.',
                'MDL' => 'L',
                'MGA' => 'Ar',
                'MKD' => 'ден',
                'MMK' => 'Ks',
                'MNT' => '₮',
                'MOP' => 'P',
                'MRO' => 'UM',
                'MUR' => '₨',
                'MVR' => 'MVR',
                'MWK' => 'MK',
                'MXN' => '$',
                'MYR' => 'RM',
                'MZN' => 'MT',
                'NAD' => '$',
                'NGN' => '₦',
                'NIO' => 'C$',
                'NOK' => 'kr',
                'NPR' => '₨',
                'NZD' => '$',
                'OMR' => 'ر.ع.',
                'PAB' => 'B/.',
                'PEN' => 'S/.',
                'PGK' => 'K',
                'PHP' => '₱',
                'PKR' => '₨',
                'PLN' => 'zł',
                'PRB' => 'р.',
                'PYG' => '₲',
                'QAR' => 'ر.ق',
                'RON' => 'lei',
                'RSD' => 'дин',
                'RUB' => '₽',
                'RWF' => 'Fr',
                'SAR' => 'ر.س',
                'SBD' => '$',
                'SCR' => '₨',
                'SDG' => 'ج.س.',
                'SEK' => 'kr',
                'SGD' => '$',
                'SHP' => '£',
                'SLL' => 'Le',
                'SOS' => 'Sh',
                'SRD' => '$',
                'SSP' => '£',
                'STD' => 'Db',
                'SYP' => '£',
                'SZL' => 'L',
                'THB' => '฿',
                'TJS' => 'ЅМ',
                'TMT' => 'm',
                'TND' => 'د.ت',
                'TOP' => 'T$',
                'TRY' => 'TRY',
                'TTD' => '$',
                'TVD' => '$',
                'TWD' => '$',
                'TZS' => 'Sh',
                'UAH' => '₴',
                'UGX' => 'Sh',
                'USD' => '$',
                'UYU' => '$',
                'UZS' => 'UZS',
                'VEF' => 'Bs',
                'VND' => '₫',
                'VUV' => 'Vt',
                'WST' => 'T',
                'XAF' => 'Fr',
                'XCD' => '$',
                'XOF' => 'Fr',
                'XPF' => 'Fr',
                'YER' => '﷼',
                'ZAR' => 'R',
                'ZMW' => 'ZK',
                'ZWL' => '$',
            ];

            foreach ( $currency_symbols as $key => $val ) {
                $wpdb->query( $wpdb->prepare( "INSERT INTO `{$wpdb->prefix}erp_acct_currency_info` (`name`, `sign`)
                VALUES ( %s, %s )", $key, $val ) );
            }
        }

        //Insert default financial years
        if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_acct_financial_years` LIMIT 0, 1" ) ) {
            $general         = get_option( 'erp_settings_general', [] );
            $financial_month = isset( $general['gen_financial_month'] ) ? $general['gen_financial_month'] : '1';

            $start_date = new DateTime( gmdate( 'Y-' . $financial_month . '-1' ) );

            $start_date = $start_date->format( 'Y-m-d' );

            $end_date = gmdate( 'Y-m-d', strtotime( '+1 year', strtotime( $start_date ) ) );
            $end_date = new DateTime( $end_date );
            $end_date->modify( '-1 day' );

            $end_date = $end_date->format( 'Y-m-d' );

            $wpdb->insert( $wpdb->prefix . 'erp_acct_financial_years', [
                'name'       => gmdate( 'Y' ),
                'start_date' => $start_date,
                'end_date'   => $end_date,
                'created_at' => gmdate( 'Y-m-d' ),
                'created_by' => get_current_user_id(),
            ] );
        }
    }

    /**
     * Set default module for initial erp setup
     *
     * @since 1.0
     *
     * @return void
     */
    public function set_default_modules() {
        if ( get_option( 'wp_erp_version' ) ) {
            return;
        }

        $default = [
            'hrm' => [
                'title'       => __( 'HR Management', 'erp' ),
                'slug'        => 'erp-hrm',
                'description' => __( 'Human Resource Mnanagement', 'erp' ),
                'callback'    => '\WeDevs\ERP\HRM\HRM',
                'modules'     => apply_filters( 'erp_hr_modules', [] ),
            ],

            'crm' => [
                'title'       => __( 'CR Management', 'erp' ),
                'slug'        => 'erp-crm',
                'description' => __( 'Customer Relationship Management', 'erp' ),
                'callback'    => '\WeDevs\ERP\CRM\CRM',
                'modules'     => apply_filters( 'erp_crm_modules', [] ),
            ],

            'accounting' => [
                'title'       => __( 'Accounting Management', 'erp' ),
                'slug'        => 'erp-accounting',
                'description' => __( 'Accounting Management', 'erp' ),
                'callback'    => '\WeDevs\ERP\Accounting\Accounting',
                'modules'     => apply_filters( 'erp_accounting_modules', [] ),
            ],
        ];

        update_option( 'erp_modules', $default );
    }

    /**
     * Create user roles and capabilities
     *
     * @since 1.0
     *
     * @return void
     */
    public function create_roles() {
        $this->includes();

        $roles_hr = erp_hr_get_roles();

        if ( $roles_hr ) {
            foreach ( $roles_hr as $key => $role ) {
                add_role( $key, $role['name'], $role['capabilities'] );
            }
        }

        $roles_crm = erp_crm_get_roles();

        if ( $roles_crm ) {
            foreach ( $roles_crm as $key => $role ) {
                add_role( $key, $role['name'], $role['capabilities'] );
            }
        }

        $roles_ac = erp_ac_get_roles();

        if ( $roles_ac ) {
            foreach ( $roles_ac as $key => $role ) {
                add_role( $key, $role['name'], $role['capabilities'] );
            }
        }
    }

    /**
     * Set erp_hr_manager role for admin user
     *
     * @since 1.0
     *
     * @return void
     */
    public function set_role() {
        $this->includes();

        $admins = get_users( [ 'role' => 'administrator' ] );

        if ( $admins ) {
            foreach ( $admins as $user ) {
                $user->add_role( erp_hr_get_manager_role() );
                $user->add_role( erp_crm_get_manager_role() );
                $user->add_role( erp_ac_get_manager_role() );
            }
        }
    }
}
