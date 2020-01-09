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

       // add_menu_page( __( 'CRM', 'erp' ), 'CRM', 'erp_crm_manage_dashboard', 'erp-sales', [ $this, 'dashboard_page' ], 'dashicons-chart-bar', null );
        add_submenu_page( 'erp', __( 'CRM', 'erp' ), 'CRM', 'erp_crm_manage_dashboard', 'erp-crm', [ $this, 'router' ]);

        erp_add_menu_header( 'crm', 'CRM', '<svg id="Group_236" data-name="Group 236" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 394.3 230.948"><defs><style>.cls-1{fill:#9da2a7}</style></defs><path id="Path_282" data-name="Path 282" class="cls-1" d="M248.8,117.8q-24-27.9-48.1-55.5c-6.5-7.4-7.8-6.4-18,.8-5,3.5-9.8,7.1-14.6,10.8C153.8,84.7,138,90.5,119.8,88.1c-10.5-1.4-20.2-4.6-27.2-13-7.6-9-7.5-24.3.3-30.5,9.8-7.8,19.9-15.2,30.6-23.3a1.636,1.636,0,0,0-.7-2.9,15.438,15.438,0,0,0-3-.4c-12.3,0-22,5.2-30.3,14-4.3,4.6-8.9,8.9-15.5,10.4-1.3.3-3.5,2.6-3.5,4.2v108c0,4,2,5,5.5,4.5,4.7-.6,8.2,1.3,11.7,4.3,18.9,16.1,37.5,32.5,57,47.9a138.3,138.3,0,0,0,29.8,17.8c10.9,4.7,18.1.2,22.3-10.8.3-.9.8-1.7,1.3-2.9,2.8,1.3,5.3,2.6,7.9,3.6a65,65,0,0,0,8.5,2.8c9.8,2.3,15.8-1.7,17.6-11.7a33.511,33.511,0,0,1,1.2-4c7.3,4.3,14,9,22.3,6.8,8.9-2.3,11.5-10.2,13.7-16.7,6.4,1,12.3,2.6,18.3,2.8a12.814,12.814,0,0,0,12.9-8.6c2.1-5.6,1.6-10.6-2.8-15.6C281.1,155.9,265.1,136.7,248.8,117.8Z"/><path id="Path_283" data-name="Path 283" class="cls-1" d="M323.7,118.6V51.3h0V43.7c0-3-.2-3.6-3.3-4-25.7-3.1-50.8-11.5-74.6-24.8-1.4-.8-2.7-1.5-4.1-2.3-6.2-3.5-12.5-7.2-19.1-9.5A59.949,59.949,0,0,0,202.4,0a132.44,132.44,0,0,0-18.7,1.5c-10.7,1.4-18.3,4.3-24.6,9.4-12,9.8-24.4,19.5-36.4,28.9-6.3,4.9-12.6,9.9-18.8,14.8-3.4,2.7-3.8,4.8-1.4,8.7,3.5,5.8,12.8,12.6,22.4,11.7,5-.4,20.8-5.7,29.2-10.4,8-4.5,15.5-10.5,22.8-16.3,1.1-.9,2.1-1.7,3.2-2.6a22.094,22.094,0,0,1,13.6-5.2c5.6,0,10.8,2.7,15.1,7.7,1.2,1.5,2.5,2.9,3.8,4.3.9,1,1.9,2.1,2.8,3.2l19.2,22.1c25.3,29.1,51.5,59.2,77.1,88.9a5.3,5.3,0,0,0,4.4,2.2,15.215,15.215,0,0,0,4.3-.8c2.8-.8,3.7-2.3,3.6-6C323.7,147.7,323.7,132.9,323.7,118.6Z"/><path id="Path_284" data-name="Path 284" class="cls-1" d="M379.3,22.6h-33a6.018,6.018,0,0,0-6,6v148a6.018,6.018,0,0,0,6,6h33a14.98,14.98,0,0,0,15-15V37.6h0A14.98,14.98,0,0,0,379.3,22.6Z"/><path id="Path_285" data-name="Path 285" class="cls-1" d="M48,22.6H15a14.98,14.98,0,0,0-15,15H0v130a14.98,14.98,0,0,0,15,15H48a6.018,6.018,0,0,0,6-6V28.6A6.018,6.018,0,0,0,48,22.6Z"/></svg>' );

        erp_add_menu( 'crm', [
            'title'      => __( 'Overview', 'erp' ),
            'capability' => 'erp_crm_manage_dashboard',
            'slug'       => 'dashboard',
            'callback'   => [ $this, 'dashboard_page' ],
            'position'   => 1
        ] );

        erp_add_menu( 'crm', [
            'title'      => __( 'Contacts', 'erp' ),
            'capability' => 'erp_crm_list_contact',
            'slug'       => 'contacts',
            'callback'   => [ $this, 'contact_page' ],
            'position'   => 5
        ] );

        erp_add_menu( 'crm', [
            'title'      => __( 'Companies', 'erp' ),
            'capability' => 'erp_crm_list_contact',
            'slug'       => 'companies',
            'callback'   => [ $this, 'company_page' ],
            'position'   => 10
        ] );

        erp_add_menu( 'crm', [
            'title'      => __( 'Activities', 'erp' ),
            'capability' => 'erp_crm_manage_activites',
            'slug'       => 'activities',
            'callback'   => [ $this, 'activity_page' ],
            'position'   => 15
        ] );

        erp_add_menu( 'crm', [
            'title'      => __( 'Schedules', 'erp' ),
            'capability' => 'erp_crm_manage_schedules',
            'slug'       => 'schedules',
            'callback'   => [ $this, 'schedules_page' ],
            'position'   => 20
        ] );

        erp_add_menu( 'crm', [
            'title'      => __( 'Contact Groups', 'erp' ),
            'capability' => 'erp_crm_manage_groups',
            'slug'       => 'contact-groups',
            'callback'   => [ $this, 'contact_group_page' ],
            'position'   => 25
        ] );

        erp_add_menu( 'crm', [
            'title'      => __( 'Reports', 'erp' ),
            'capability' => 'erp_crm_manage_dashboard',
            'slug'       => 'reports',
            'callback'   => [ $this, 'page_reports' ],
            'position'   => 99
        ] );

        erp_add_submenu( 'crm','reports', [
            'title'      => __( 'Activity Report', 'erp' ),
            'capability' => 'erp_crm_manage_dashboard',
            'slug'       => 'reports&type=activity-report',
            'callback'   => [ $this, 'page_reports' ],
            'position'   => 5
        ] );

        erp_add_submenu( 'crm','reports', [
            'title'      => __( 'Customer Report', 'erp' ),
            'capability' => 'erp_crm_manage_dashboard',
            'slug'       => 'reports&type=customer-report',
            'callback'   => [ $this, 'page_reports' ],
            'position'   => 5
        ] );

        erp_add_submenu( 'crm','reports', [
            'title'      => __( 'Growth Report', 'erp' ),
            'capability' => 'erp_crm_manage_dashboard',
            'slug'       => 'reports&type=growth-report',
            'callback'   => [ $this, 'page_reports' ],
            'position'   => 10
        ] );

        erp_add_menu( 'crm', [
            'title'      =>   __( '<span class="erp-help">Help</span>', 'erp' ),
            'capability' => 'erp_crm_manage_dashboard',
            'slug'       => 'help',
            'callback'   => [ $this, 'help_page' ],
            'position'   => 100
        ] );

//        $overview = add_submenu_page( 'erp-sales', __( 'Overview', 'erp' ), __( 'Overview', 'erp' ), 'erp_crm_manage_dashboard', 'erp-sales', [ $this, 'dashboard_page' ] );
//        add_submenu_page( 'erp-sales', __( 'Contacts', 'erp' ), __( 'Contacts', 'erp' ), 'erp_crm_list_contact', 'erp-sales-customers', [ $this, 'contact_page' ] );
//        add_submenu_page( 'erp-sales', __( 'Companies', 'erp' ), __( 'Companies', 'erp' ), 'erp_crm_list_contact', 'erp-sales-companies', [ $this, 'company_page' ] );
//        add_submenu_page( 'erp-sales', __( 'Activities', 'erp' ), __( 'Activities', 'erp' ), 'erp_crm_manage_activites', 'erp-sales-activities', [ $this, 'activity_page' ] );
//        $schedule = add_submenu_page( 'erp-crm', __( 'Schedules', 'erp' ), __( 'Schedules', 'erp' ), 'erp_crm_manage_schedules', 'erp-sales-schedules', [ $this, 'schedules_page' ] );
//        add_submenu_page( 'erp-sales', __( 'Contact Groups', 'erp' ), __( 'Contact Groups', 'erp' ), 'erp_crm_manage_groups', 'erp-sales-contact-groups', [ $this, 'contact_group_page' ] );
//        add_submenu_page( 'erp-sales', __( 'Reports', 'erp' ), __( 'Reports', 'erp' ), 'erp_crm_manage_dashboard', 'erp-sales-reports', array( $this, 'page_reports' ) );
//
//        //Help page
//        add_submenu_page( 'erp-sales', __( 'Help', 'erp' ), __( '<span style="color:#f18500">Help</span>', 'erp' ), 'erp_crm_manage_dashboard', 'erp-crm-help', array( $this, 'help_page' ) );
//
//        add_action( 'admin_print_styles-' . $overview, array( $this, 'crm_calendar_script' ) );
//        add_action( 'admin_print_styles-' . $schedule, array( $this, 'crm_calendar_script' ) );

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
        enqueue_fullcalendar_locale();
        wp_enqueue_style( 'erp-fullcalendar' );
    }

    /**
     * Route to approriate template according to current menu
     *
     * @since 1.3.14
     *
     * @return void
     */
    public function router() {
        $component = 'crm';
        $menu = erp_menu();
        $menu = $menu[$component];

        $section = ( isset( $_GET['section'] ) && isset( $menu[$_GET['section']] ) ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ): 'dashboard';
        $sub = ( isset( $_GET['sub-section'] ) && !empty( $menu[$section]['submenu'][$_GET['sub-section']] ) ) ? sanitize_text_field( wp_unslash( $_GET['sub-section'] ) ) : false;

        $callback = $menu[$section]['callback'];
        if ( $sub ) {
            $callback = $menu[$section]['submenu'][$sub]['callback'];
        }

        erp_render_menu( $component );

        if ( is_callable( $callback ) ) {
            call_user_func( $callback );
        }

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
        $this->crm_calendar_script();
    }

    /**
     * Handles the dashboard page
     *
     * @since 1.0
     *
     * @return void
     */
    public function contact_page() {
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ): 'list';
        $id     = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        switch ( $action ) {

            case 'view':
                $customer = new Contact( $id );

                if ( ! $customer->id ) {
                    wp_die( esc_html__( 'Contact not found!', 'erp' ) );
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
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ): 'list';
        $id     = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

        switch ( $action ) {

            case 'view':
                $customer = new Contact( $id );

                if ( ! $customer->id ) {
                    wp_die( esc_html__( 'Company not found!', 'erp' ) );
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

        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'list';
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
        $this->crm_calendar_script();
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

        $action = isset( $_GET['groupaction'] ) ? sanitize_text_field( wp_unslash( $_GET['groupaction'] ) ) : 'list';
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
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ): 'list';
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

    /**
     * Render page reports
     *
     * @since 1.3.6
     *
     * @return void
     */
    public function page_reports() {
        $type    = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ): '';
        $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
        $limit   = 20;
        $offset  = ( $pagenum - 1 ) * $limit;

        switch ( $type ) {
            case 'customer-report':
                $template = WPERP_CRM_VIEWS . '/reports/customer-report.php';
                break;

                case 'activity-report':
                $template = WPERP_CRM_VIEWS . '/reports/activity-report.php';
                break;

            case 'growth-report':
                $template = WPERP_CRM_VIEWS . '/reports/growth-report.php';
                break;

            default:
                $template = WPERP_CRM_VIEWS . '/reports.php';
                break;
        }

        $template = apply_filters( 'erp_crm_reporting_pages', $template, $type );

        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    /**
     * Show CRM Help Page
     * @since 1.0.0
     */
    public function help_page(){
        include WPERP_CRM_VIEWS . '/help.php';
    }
}

//new Admin_Menu();
