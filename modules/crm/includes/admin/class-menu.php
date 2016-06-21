<?php
namespace WeDevs\ERP\CRM;

/**
 * Admin Menu
 */
class Admin_Menu {

    /**
     * Kick-in the class
     *
     * @since 1.0
     *
     * @return void
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    }

    /**
     * Add menu items
     *
     * @since 1.0
     *
     * @return void
     */
    public function admin_menu() {

        $capabilities = erp_crm_get_manager_role();

        add_menu_page( __( 'CRM', 'erp' ), __( 'CRM', 'erp' ), 'erp_crm_manage_dashboard', 'erp-sales', [ $this, 'dashboard_page' ], 'dashicons-chart-bar', null );

        $overview = add_submenu_page( 'erp-sales', __( 'Overview', 'erp' ), __( 'Overview', 'erp' ), 'erp_crm_manage_dashboard', 'erp-sales', [ $this, 'dashboard_page' ] );
        add_submenu_page( 'erp-sales', __( 'Contacts', 'erp' ), __( 'Contacts', 'erp' ), 'erp_crm_list_contact', 'erp-sales-customers', [ $this, 'contact_page' ] );
        add_submenu_page( 'erp-sales', __( 'Companies', 'erp' ), __( 'Companies', 'erp' ), 'erp_crm_list_contact', 'erp-sales-companies', [ $this, 'company_page' ] );
        add_submenu_page( 'erp-sales', __( 'Activities', 'erp' ), __( 'Activities', 'erp' ), 'erp_crm_manage_activites', 'erp-sales-activities', [ $this, 'activity_page' ] );
        $schedule = add_submenu_page( 'erp-sales', __( 'Schedules', 'erp' ), __( 'Schedules', 'erp' ), 'erp_crm_manage_schedules', 'erp-sales-schedules', [ $this, 'schedules_page' ] );
        add_submenu_page( 'erp-sales', __( 'Contact Groups', 'erp' ), __( 'Contact Groups', 'erp' ), 'erp_crm_manage_groups', 'erp-sales-contact-groups', [ $this, 'contact_group_page' ] );

        add_action( 'admin_print_styles-' . $overview, array( $this, 'crm_calendar_script' ) );
        add_action( 'admin_print_styles-' . $schedule, array( $this, 'crm_calendar_script' ) );

    }

    /**
     * Add calendar script in Overview
     *
     * @since 1.0
     *
     * @return void
     */
    public function crm_calendar_script() {
        wp_enqueue_script( 'erp-fullcalendar' );
        wp_enqueue_style( 'erp-fullcalendar' );
    }

    /**
     * Handles the dashboard page
     *
     * @since 1.0
     *
     * @return void
     */
    public function dashboard_page() {
        include WPERP_CRM_VIEWS . '/dashboard.php';
    }

    /**
     * Handles the dashboard page
     *
     * @since 1.0
     *
     * @return void
     */
    public function contact_page() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $id     = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

        switch ( $action ) {

            case 'view':
                $customer = new Contact( $id );

                if ( ! $customer->id ) {
                    wp_die( __( 'Contact not found!', 'erp' ) );
                }

                $template = WPERP_CRM_VIEWS . '/contact/single.php';
                break;

            default:
                $template = WPERP_CRM_VIEWS . '/contact.php';
                break;
        }

        $template = apply_filters( 'erp_contact_template', $template, $action, $id );

        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    /**
     * Handles the dashboard page
     *
     * @since 1.0
     *
     * @return void
     */
    public function company_page() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $id     = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

        switch ( $action ) {

            case 'view':
                $customer = new Contact( $id );

                if ( ! $customer->id ) {
                    wp_die( __( 'Company not found!', 'erp' ) );
                }

                $template = WPERP_CRM_VIEWS . '/company/single.php';
                break;

            default:
                $template = WPERP_CRM_VIEWS . '/company.php';
                break;
        }

        $template = apply_filters( 'erp_company_template', $template, $action, $id );

        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    /**
     * Handles the dashboard page
     *
     * @since 1.0
     *
     * @return void
     */
    public function leads_page() {

        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $id     = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

        switch ($action) {

            case 'view':
                $template = WPERP_CRM_VIEWS . '/leads/single.php';
                break;

            default:
                $template = WPERP_CRM_VIEWS . '/leads.php';
                break;
        }

        $template = apply_filters( 'erp_leads_template', $template, $action, $id );

        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    /**
     * Handles the dashboard page
     *
     * @since 1.0
     *
     * @return void
     */
    public function oppurtunity_page() {
        include WPERP_CRM_VIEWS . '/dashboard.php';
    }

    /**
     * Schedule Page
     *
     * @since 1.0
     *
     * @return void
     */
    public function schedules_page() {
        include WPERP_CRM_VIEWS . '/schedules.php';
    }

    /**
     * Activity Page
     *
     * @since 1.0
     *
     * @return void
     */
    public function activity_page() {
        include WPERP_CRM_VIEWS . '/activities.php';
    }

    /**
     * Contact Group Page
     *
     * @since 1.0
     *
     * @return void
     */
    public function contact_group_page() {

        $action = isset( $_GET['groupaction'] ) ? $_GET['groupaction'] : 'list';
        $id     = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

        switch ($action) {

            case 'view-subscriber':
                $template = WPERP_CRM_VIEWS . '/contact-group/subscribe-contact.php';
                break;

            default:
                $template = WPERP_CRM_VIEWS . '/contact-groups.php';
                break;
        }

        $template = apply_filters( 'erp_contact_group_template', $template, $action, $id );

        if ( file_exists( $template ) ) {
            include $template;
        }

    }

    public function single_page_app() {
        include WPERP_CRM_VIEWS . '/single-page-table.php';
    }

    /**
     * Campaigns Page
     *
     * @since 1.0
     *
     * @return void
     */
    public function campaigns_page() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $id     = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

        switch ($action) {

            case 'view-subscriber':
                $template = WPERP_CRM_VIEWS . '/contact-group/subscribe-contact.php';
                break;

            default:
                $template = WPERP_CRM_VIEWS . '/campaigns.php';
                break;
        }

        $template = apply_filters( 'erp_campaign_template', $template, $action, $id );

        if ( file_exists( $template ) ) {
            include $template;
        }
    }
}

//new Admin_Menu();
