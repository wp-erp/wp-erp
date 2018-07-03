<?php
/**
 * Installation related functions and actions.
 *
 * @author Tareq Hasan
 * @package ERP
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Installer Class
 *
 * @package ERP
 */
class WeDevs_ERP_Installer {

    use \WeDevs\ERP\Framework\Traits\Hooker;

    /**
     * Binding all events
     *
     * @since 1.0
     *
     * @return void
     */
    function __construct() {
        $this->set_default_modules();

        register_activation_hook( WPERP_FILE, array( $this, 'activate' ) );
        register_deactivation_hook( WPERP_FILE, array( $this, 'deactivate' ) );
        register_uninstall_hook( WPERP_FILE, array( 'WeDevs_ERP_Installer', 'uninstall' ) );

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
     * @return void
     */
    public function activate() {
        $current_erp_version = get_option( 'wp_erp_version', null );
        $current_db_version  = get_option( 'wp_erp_db_version', null );

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
     * Call on unistallation
     *
     * @since 1.3.13
     *
     * @return void
     */
    public static function uninstall() {
        if ( erp_get_option( 'erp_remove_tables', 'erp_settings_general' ) == 'yes' ) {

            global $wpdb;

            $erp_prefix = $wpdb->esc_like($wpdb->prefix . 'erp_');

            $results = $wpdb->get_results( "SHOW TABLES LIKE '". $erp_prefix ."%'" );

            foreach ($results as $index => $value) {
                foreach ($value as $table) {
                    $wpdb->query("DROP TABLE IF EXISTS $table");
                }
            }

        }
    }

    /**
     * Include required files to prevent fatal errors
     *
     * @return void
     */
    function includes() {
        include_once WPERP_MODULES . '/hrm/includes/functions-capabilities.php';
        include_once WPERP_MODULES . '/crm/includes/functions-capabilities.php';
        include_once WPERP_MODULES . '/accounting/includes/function-capabilities.php';
    }

    /**
     * Set default mail subject, heading and body
     *
     * @since 1.0
     *
     * @return void
     */
    function setup_default_emails() {

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

{login_info}'
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

Thanks.'
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
Company'
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
Company'
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
Company'
        ];

        update_option( 'erp_email_settings_new-task-assigned', $new_task_assigned );
    }

    /**
     * Create cron jobs
     *
     * @return void
     */
    public function create_cron_jobs() {
        wp_schedule_event( time(), 'per_minute', 'erp_per_minute_scheduled_events' );
        wp_schedule_event( time(), 'daily', 'erp_daily_scheduled_events' );
        wp_schedule_event( time(), 'weekly', 'erp_weekly_scheduled_events' );
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

        remove_role('erp_crm_manager');
        remove_role('erp_crm_agent');
    }

    /**
     * Welcome screen menu page cb
     *
     * @since 1.0
     *
     * @return void
     */
    public function welcome_screen_menu() {
        add_dashboard_page( __( 'Welcome to WP ERP', 'erp' ), 'WP ERP', 'manage_options', 'erp-welcome', array( $this, 'welcome_screen_content' ) );
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
     * Set default module for initial erp setup
     *
     * @since 1.0
     *
     * @return void
     */
    public function set_default_modules() {

        if ( get_option( 'wp_erp_version' ) ) {
            return ;
        }

        $default = [
            'hrm' => [
                'title'       => __( 'HR Management', 'erp' ),
                'slug'        => 'erp-hrm',
                'description' => __( 'Human Resource Mnanagement', 'erp' ),
                'callback'    => '\WeDevs\ERP\HRM\Human_Resource',
                'modules'     => apply_filters( 'erp_hr_modules', [ ] )
            ],

            'crm' => [
                'title'       => __( 'CR Management', 'erp' ),
                'slug'        => 'erp-crm',
                'description' => __( 'Client Resource Management', 'erp' ),
                'callback'    => '\WeDevs\ERP\CRM\Customer_Relationship',
                'modules'     => apply_filters( 'erp_crm_modules', [ ] )
            ],

            'accounting' => [
                'title'       => __( 'Accountig Management', 'erp' ),
                'slug'        => 'erp-accounting',
                'description' => __( 'Accountig Management', 'erp' ),
                'callback'    => '\WeDevs\ERP\Accounting\Accountig',
                'modules'     => apply_filters( 'erp_accounting_modules', [ ] )
            ]
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
            foreach ($roles_hr as $key => $role) {
                add_role( $key, $role['name'], $role['capabilities'] );
            }
        }

        $roles_crm = erp_crm_get_roles();

        if ( $roles_crm ) {
            foreach ($roles_crm as $key => $role) {
                add_role( $key, $role['name'], $role['capabilities'] );
            }
        }

        $roles_ac = erp_ac_get_roles();

        if ( $roles_ac ) {
            foreach ($roles_ac as $key => $role) {
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

        $admins = get_users( array( 'role' => 'administrator' ) );

        if ( $admins ) {
            foreach ($admins as $user) {
                $user->add_role( erp_hr_get_manager_role() );
                $user->add_role( erp_crm_get_manager_role() );
                $user->add_role( erp_ac_get_manager_role() );
            }
        }
    }
}

new WeDevs_ERP_Installer();
