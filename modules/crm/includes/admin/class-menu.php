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

        add_menu_page( __( 'CRM', 'wp-erp' ), __( 'CRM', 'wp-erp' ), $capabilities, 'erp-sales', array( $this, 'dashboard_page' ), 'dashicons-chart-bar', null );

        $overview = add_submenu_page( 'erp-sales', __( 'Overview', 'wp-erp' ), __( 'Overview', 'wp-erp' ), $capabilities, 'erp-sales', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-sales', __( 'Contacts', 'wp-erp' ), __( 'Contacts', 'wp-erp' ), $capabilities, 'erp-sales-customers', array( $this, 'contact_page' ) );
        add_submenu_page( 'erp-sales', __( 'Companies', 'wp-erp' ), __( 'Companies', 'wp-erp' ), $capabilities, 'erp-sales-companies', array( $this, 'company_page' ) );
        add_submenu_page( 'erp-sales', __( 'Activities', 'wp-erp' ), __( 'Activities', 'wp-erp' ), $capabilities, 'erp-sales-activities', array( $this, 'activity_page' ) );
        add_submenu_page( 'erp-sales', __( 'Schedules', 'wp-erp' ), __( 'Schedules', 'wp-erp' ), $capabilities, 'erp-sales-schedules', array( $this, 'schedules_page' ) );
        add_submenu_page( 'erp-sales', __( 'Contact Groups', 'wp-erp' ), __( 'Contact Groups', 'wp-erp' ), $capabilities, 'erp-sales-contact-groups', array( $this, 'contact_group_page' ) );

        // add_submenu_page( 'erp-sales', __( 'Campaigns', 'wp-erp' ), __( 'Campaigns', 'wp-erp' ), $capabilities, 'erp-sales-campaigns', array( $this, 'campaigns_page' ) );
        // add_submenu_page( 'erp-sales', __( 'Oppurtunity', 'wp-erp' ), __( 'Oppurtunity', 'wp-erp' ), $capabilities, 'erp-sales-oppurtunity', array( $this, 'dashboard_page' ) );
        // add_submenu_page( 'erp-sales', __( 'Customer Category', 'wp-erp' ), __( 'Customer Category', 'wp-erp' ), $capabilities, 'erp-sales-category', array( $this, 'dashboard_page' ) );
        // add_submenu_page( 'erp-sales', __( 'Newsletter', 'wp-erp' ), __( 'Newsletter', 'wp-erp' ), $capabilities, 'erp-sales-newsletter', array( $this, 'dashboard_page' ) );
        // add_submenu_page( 'erp-sales', __( 'Sales Team', 'wp-erp' ), __( 'Sales Team', 'wp-erp' ), $capabilities, 'erp-sales-team', array( $this, 'dashboard_page' ) );

        add_action( 'admin_print_styles-' . $overview, array( $this, 'crm_calendar_script' ) );

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
                    wp_die( __( 'Contact not found!', 'wp-erp' ) );
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
                    wp_die( __( 'Company not found!', 'wp-erp' ) );
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
        echo "Schedule Page. This page will display all scheduled calls and meetings";
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
