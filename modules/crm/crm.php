<?php
namespace WeDevs\ERP\CRM;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * The HRM Class
 *
 * This is loaded in `init` action hook
 */
class Customer_Relationship {

    use Hooker;

    /**
     * Initializes the WeDevs_ERP() class
     *
     * Checks for an existing WeDevs_ERP() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Kick-in the class
     *
     * @return void
     */
    public function __construct() {
        // prevent duplicate loading
        if ( did_action( 'erp_crm_loaded' ) ) {
            return;
        }

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        // Initialize the classes
        $this->init_classes();

        // Initialize the action hooks
        $this->init_actions();

        // Initialize the filter hooks
        $this->init_filters();

        // Trigger after CRM module loaded
        do_action( 'erp_crm_loaded' );
    }

    /**
     * Define the plugin constants
     *
     * @return void
     */
    private function define_constants() {
        define( 'WPERP_CRM_FILE', __FILE__ );
        define( 'WPERP_CRM_PATH', dirname( __FILE__ ) );
        define( 'WPERP_CRM_VIEWS', dirname( __FILE__ ) . '/views' );
        define( 'WPERP_CRM_JS_TMPL', WPERP_CRM_VIEWS . '/js-templates' );
        define( 'WPERP_CRM_ASSETS', plugins_url( '/assets', __FILE__ ) );
    }

    /**
     * Include the required files
     *
     * @return void
     */
    private function includes() {
        require_once WPERP_CRM_PATH . '/includes/actions-filters.php';
        require_once WPERP_CRM_PATH . '/includes/functions-localize.php';
        require_once WPERP_CRM_PATH . '/includes/functions-customer.php';
        require_once WPERP_CRM_PATH . '/includes/functions-dashboard.php';
        require_once WPERP_CRM_PATH . '/includes/functions-reporting.php';
        require_once WPERP_CRM_PATH . '/includes/functions-capabilities.php';
        require_once WPERP_CRM_PATH . '/includes/contact-forms/class-contact-forms-integration.php';

        // cli command
        if ( defined('WP_CLI') && WP_CLI ) {
            include WPERP_CRM_PATH . '/includes/cli/commands.php';
        }
    }

    /**
     * Init classes
     *
     * @return void
     */
    private function init_classes() {
        if ( is_admin() ) {
            new Ajax_Handler();
            new Form_Handler();
            new Admin_Menu();
            new User_Profile();
            new Emailer();
            new Admin_Dashboard();
        }

        Subscription::instance();
    }

    /**
     * Initialize WordPress action hooks
     *
     * @return void
     */
    private function init_actions() {
        $this->action( 'admin_enqueue_scripts', 'admin_scripts' );
        $this->action( 'admin_footer', 'load_js_template', 10 );

        $this->action( 'admin_enqueue_scripts', 'load_settings_scripts' );
        $this->action( 'admin_footer', 'load_settings_js_templates', 10 );
    }

    /**
     * Initialize WordPress filter hooks
     *
     * @return void
     */
    private function init_filters() {

    }

    /**
     * Enqueue admin scripts
     *
     * @since 1.0.0
     * @since 1.2.2 Remove other select2 sources from contact groups page
     *
     * @param string $hook
     *
     * @return void
     */
    public function admin_scripts( $hook ) {
//        $hook = str_replace( sanitize_title( __( 'CRM', 'erp' ) ) , 'crm', $hook );

        if ( 'wp-erp_page_erp-crm' != $hook && 'wp-erp_page_erp-settings' !== $hook ) {
            return;
        }

//        $crm_pages = [
//            'toplevel_page_erp-sales',
//            'crm_page_erp-sales-customers',
//            'crm_page_erp-sales-companies',
//            'crm_page_erp-sales-schedules',
//            'crm_page_erp-sales-activities',
//            'erp-settings_page_erp-settings',
//            'crm_page_erp-sales-contact-groups',
//            'crm_page_erp-sales-reports',
//            'wp-erp_page_erp-crm',
//        ];
//
//        if ( ! in_array( $hook , $crm_pages ) ) {
//            return;
//        }

        $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '';

        wp_enqueue_media();
        wp_enqueue_style( 'erp-tiptip' );
        wp_enqueue_script( 'erp-tiptip' );
        wp_enqueue_script( 'erp-vuejs', false, [ 'jquery', 'erp-script' ], false, true );
        wp_enqueue_script( 'erp-vue-table', WPERP_CRM_ASSETS . "/js/vue-table$suffix.js", array( 'erp-vuejs', 'jquery' ), date( 'Ymd' ), true );

        wp_enqueue_style( 'email-attachment', WPERP_CRM_ASSETS . "/css/email-attachment.css", array(), false );

        $localize_script = apply_filters( 'erp_crm_localize_script', array(
            'ajaxurl'               => admin_url( 'admin-ajax.php' ),
            'nonce'                 => wp_create_nonce( 'wp-erp-crm-nonce' ),
            'popup'                 => array(
                'customer_title'         => __( 'Add New Customer', 'erp' ),
                'customer_update_title'  => __( 'Edit Customer', 'erp' ),
                'customer_social_title'  => __( 'Customer Social Profile', 'erp' ),
                'customer_assign_group'  => __( 'Add to Contact groups', 'erp' ),
            ),
            'asset_url'                   => WPERP_ASSETS,
            'add_submit'                  => __( 'Add New', 'erp' ),
            'update_submit'               => __( 'Update', 'erp' ),
            'save_submit'                 => __( 'Save', 'erp' ),
            'customer_upload_photo'       => __( 'Upload Photo', 'erp' ),
            'customer_set_photo'          => __( 'Set Photo', 'erp' ),
            'confirm'                     => __( 'Are you sure?', 'erp' ),
            'delConfirmCustomer'          => __( 'Are you sure to delete?', 'erp' ),
            'delConfirm'                  => __( 'Are you sure to delete this?', 'erp' ),
            'checkedConfirm'              => __( 'Select atleast one group', 'erp' ),
            'contact_exit'                => __( 'Already exists as a contact or company', 'erp' ),
            'make_contact_text'           => __( 'This user already exists! Do you want to make this user as a', 'erp' ),
            'wpuser_make_contact_text'    => __( 'This is wp user! Do you want to create this user as a', 'erp' ),
            'create_contact_text'         => __( 'Create new', 'erp' ),
            'current_user_id'             => get_current_user_id(),
            'successfully_created_wpuser' => __( 'WP User created successfully', 'erp' ),
        ) );

        $contact_actvity_localize = apply_filters( 'erp_crm_contact_localize_var', [
            'ajaxurl'              => admin_url( 'admin-ajax.php' ),
            'nonce'                => wp_create_nonce( 'wp-erp-crm-customer-feed' ),
            'current_user_id'      => get_current_user_id(),
            'isAdmin'              => current_user_can( 'manage_options' ),
            'isCrmManager'         => current_user_can( 'erp_crm_manager' ),
            'isAgent'              => current_user_can( 'erp_crm_agent' ),
            'confirm'              => __( 'Are you sure?', 'erp' ),
            'date_format'          => get_option( 'date_format' ),
            'timeline_feed_header' => apply_filters( 'erp_crm_contact_timeline_feeds_header', '' ),
            'timeline_feed_body'   => apply_filters( 'erp_crm_contact_timeline_feeds_body', '' ),
        ] );

        /**
         * This below block is only needed for translations
         * Pleeease find some proper place for me
         */
        ?>
        <script>
            window.erpLocale = JSON.parse('<?php echo wp_kses_post( wp_slash(
                json_encode( apply_filters( 'erp_localized_data', [] ) )
            ) ); ?>');
        </script>

        <?php
        wp_enqueue_script( 'erp-crm-i18n', WPERP_ASSETS . "/js/i18n.js", array(), date( 'Ymd' ), true );
        /**
         * This above block is only needed for translations
         */

        $section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'dashboard' ;

        switch ( $section ) {

            case 'dashboard':
                wp_enqueue_script( 'jquery' );
                wp_enqueue_script( 'erp-flotchart' );
                wp_enqueue_script( 'erp-flotchart-time' );
                wp_enqueue_script( 'erp-flotchart-axislables' );
                wp_enqueue_script( 'erp-flotchart-orerbars' );
                wp_enqueue_script( 'erp-flotchart-tooltip' );
                break;

            case 'contacts' :
                $this->load_contact_company_scripts( $suffix, $contact_actvity_localize );

                $customer = new Contact( null, 'contact' );
                $localize_script['customer_empty']    = $customer->to_array();
                $localize_script['statuses']          = erp_crm_customer_get_status_count( 'contact' );
                $localize_script['contact_type']      = 'contact';
                $localize_script['life_stages']       = erp_crm_get_life_stages_dropdown_raw();
                $localize_script['searchFields']      = erp_crm_get_serach_key( 'contact' );
                $localize_script['saveAdvanceSearch'] = erp_crm_get_save_search_item( [ 'type' => 'contact' ] );
                $localize_script['isAdmin']           = current_user_can( 'manage_options' );
                $localize_script['isCrmManager']      = current_user_can( 'erp_crm_manager' );
                $localize_script['isAgent']           = current_user_can( 'erp_crm_agent' );

                $country = \WeDevs\ERP\Countries::instance();
                wp_localize_script( 'erp-script', 'wpErpCountries', $country->load_country_states() );
                break;

            case 'companies':
                $this->load_contact_company_scripts( $suffix, $contact_actvity_localize );

                $customer = new Contact( null, 'company' );
                $localize_script['customer_empty']    = $customer->to_array();
                $localize_script['statuses']          = erp_crm_customer_get_status_count( 'company' );
                $localize_script['contact_type']      = 'company';
                $localize_script['life_stages']       = erp_crm_get_life_stages_dropdown_raw();
                $localize_script['searchFields']      = erp_crm_get_serach_key( 'company' );
                $localize_script['saveAdvanceSearch'] = erp_crm_get_save_search_item( [ 'type' => 'company' ] );
                $localize_script['isAdmin']           = current_user_can( 'manage_options' );
                $localize_script['isCrmManager']      = current_user_can( 'erp_crm_manager' );
                $localize_script['isAgent']           = current_user_can( 'erp_crm_agent' );

                $country = \WeDevs\ERP\Countries::instance();
                wp_localize_script( 'erp-script', 'wpErpCountries', $country->load_country_states() );
                break;

            case 'schedules' :
                wp_enqueue_style( 'erp-timepicker' );
                wp_enqueue_script( 'erp-timepicker' );
                wp_enqueue_script( 'underscore' );
                wp_enqueue_script( 'erp-trix-editor' );
                wp_enqueue_style( 'erp-trix-editor' );
                break;

            case 'activities' :
                wp_enqueue_script( 'underscore' );
                wp_enqueue_script( 'erp-vuejs' );
                wp_enqueue_style( 'erp-nprogress' );
                wp_enqueue_script( 'erp-nprogress' );
                wp_enqueue_script( 'wp-erp-crm-vue-component', WPERP_CRM_ASSETS . "/js/crm-components.js", apply_filters( 'crm_vue_customer_script', array( 'erp-nprogress', 'erp-script', 'erp-vuejs', 'underscore', 'erp-select2', 'erp-tiptip' ) ), date( 'Ymd' ), true );

                do_action( 'erp_crm_load_contact_vue_scripts' );

                wp_enqueue_script( 'wp-erp-crm-vue-customer', WPERP_CRM_ASSETS . "/js/crm-app$suffix.js", array( 'wp-erp-crm-vue-component', 'erp-nprogress', 'erp-script', 'erp-vuejs', 'underscore', 'erp-select2', 'erp-tiptip' ), date( 'Ymd' ), true );
                wp_enqueue_script( 'post' );

                $contact_actvity_localize['isActivityPage'] = true;
                wp_localize_script( 'wp-erp-crm-vue-component', 'wpCRMvue', $contact_actvity_localize );
                break;

            case 'reports' :
                if ( isset( $_GET['type'] ) && $_GET['type'] === 'growth-report' ) {
                    wp_enqueue_script( 'erp-momentjs' );
                    wp_enqueue_script( 'erp-crm-chart', WPERP_CRM_ASSETS . "/js/chart$suffix.min.js", array(), date( 'Ymd' ), true );
                    wp_enqueue_script( 'erp-crm-report', WPERP_CRM_ASSETS . "/js/report$suffix.js", array(), date( 'Ymd' ), true );
                }
                break;

            default :

        }

        wp_localize_script( 'erp-vue-table', 'wpVueTable', [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'wp-erp-vue-table' )
        ] );

        wp_enqueue_script( 'erp-crm', WPERP_CRM_ASSETS . "/js/crm$suffix.js", array( 'erp-script', 'erp-timepicker' ), date( 'Ymd' ), true );
        wp_enqueue_script( 'erp-crm-contact', WPERP_CRM_ASSETS . "/js/crm-contacts$suffix.js", array( 'erp-vue-table', 'erp-script', 'erp-vuejs', 'underscore', 'erp-tiptip', 'jquery', 'erp-select2', 'erp-crm-i18n' ), date( 'Ymd' ), true );
        wp_localize_script( 'erp-crm', 'wpErpCrm', $localize_script );
        wp_localize_script( 'erp-crm-contact', 'wpErpCrm', $localize_script );

        erp_remove_other_select2_sources();
    }

    /**
     * Load all js template in footer
     *
     * @since 1.0
     *
     * @return void
     */
    public function load_js_template() {
        global $current_screen;
        $hook = str_replace( sanitize_title( __( 'CRM', 'erp' ) ) , 'crm', $current_screen->base );
        $section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'dashboard' ;

        switch ( $section ) {

            case 'contacts':
            case 'companies':

                erp_get_js_template( WPERP_CRM_JS_TMPL . '/new-customer.php', 'erp-crm-new-contact' );
                erp_get_js_template( WPERP_CRM_JS_TMPL . '/make-wp-user.php', 'erp-make-wp-user' );
                erp_get_js_template( WPERP_CRM_JS_TMPL . '/new-bulk-contact-group.php', 'erp-crm-new-bulk-contact-group' );
                erp_get_vue_component_template( WPERP_CRM_JS_TMPL . '/save-search-fields.php', 'erp-crm-save-search-item' );

                if ( isset( $_GET['action'] ) && $_GET['action'] == 'view' ) {
                    erp_get_js_template( WPERP_CRM_JS_TMPL . '/new-assign-company.php', 'erp-crm-new-assign-company' );
                    erp_get_js_template( WPERP_CRM_JS_TMPL . '/customer-social.php', 'erp-crm-customer-social' );
                    erp_get_js_template( WPERP_CRM_JS_TMPL . '/customer-feed-edit.php', 'erp-crm-customer-edit-feed' );
                    erp_get_js_template( WPERP_CRM_JS_TMPL . '/new-subscriber-contact.php', 'erp-crm-assign-subscriber-contact' );
                    erp_get_vue_component_template( WPERP_CRM_JS_TMPL . '/timeline-new-note.php', 'erp-crm-timeline-feed-new-note' );
                    erp_get_vue_component_template( WPERP_CRM_JS_TMPL . '/timeline-email.php', 'erp-crm-timeline-feed-email' );
                    erp_get_vue_component_template( WPERP_CRM_JS_TMPL . '/timeline-log-activity.php', 'erp-crm-timeline-feed-log-activity' );
                    erp_get_vue_component_template( WPERP_CRM_JS_TMPL . '/timeline-task.php', 'erp-crm-timeline-feed-task-note' );
                    erp_get_vue_component_template( WPERP_CRM_JS_TMPL . '/customer-newnote.php', 'erp-crm-new-note-template' );
                    erp_get_vue_component_template( WPERP_CRM_JS_TMPL . '/customer-log-activity.php', 'erp-crm-log-activity-template' );
                    erp_get_vue_component_template( WPERP_CRM_JS_TMPL . '/customer-email-note.php', 'erp-crm-email-note-template' );
                    erp_get_vue_component_template( WPERP_CRM_JS_TMPL . '/customer-schedule-note.php', 'erp-crm-schedule-note-template' );
                    erp_get_vue_component_template( WPERP_CRM_JS_TMPL . '/customer-tasks-note.php', 'erp-crm-tasks-note-template' );

                    do_action( 'erp_crm_load_vue_js_template' );
                }

                break;

            case 'contact-groups':
                erp_get_js_template( WPERP_CRM_JS_TMPL . '/new-contact-group.php', 'erp-crm-new-contact-group' );
                if( isset($_GET['groupaction']) && $_GET['groupaction'] == 'view-subscriber'){
                    erp_get_js_template( WPERP_CRM_JS_TMPL . '/new-subscriber-contact.php', 'erp-crm-assign-subscriber-contact' );
                }
                break;

            case 'dashboard':
                erp_get_js_template( WPERP_CRM_JS_TMPL . '/single-schedule-details.php', 'erp-crm-single-schedule-details' );
                break;

            case 'activities':
                erp_get_vue_component_template( WPERP_CRM_JS_TMPL . '/timeline-new-note.php', 'erp-crm-timeline-feed-new-note' );
                erp_get_vue_component_template( WPERP_CRM_JS_TMPL . '/timeline-email.php', 'erp-crm-timeline-feed-email' );
                erp_get_vue_component_template( WPERP_CRM_JS_TMPL . '/timeline-log-activity.php', 'erp-crm-timeline-feed-log-activity' );
                erp_get_vue_component_template( WPERP_CRM_JS_TMPL . '/timeline-task.php', 'erp-crm-timeline-feed-task-note' );

                do_action( 'erp_crm_load_vue_js_template' );

                break;

            case 'schedules':
                erp_get_js_template( WPERP_CRM_JS_TMPL . '/single-schedule-details.php', 'erp-crm-single-schedule-details' );
                erp_get_js_template( WPERP_CRM_JS_TMPL . '/customer-add-schedules.php', 'erp-crm-customer-schedules');
                break;

            default:
                # code...
                break;
        }

        if ( 'wp-erp_page_erp-settings' == $hook ) {
            if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'erp-crm' ) {
                erp_get_js_template( WPERP_CRM_JS_TMPL . '/new-save-replies.php', 'erp-crm-new-save-replies' );
            }
        }
    }

    /**
     * Load commong scripts for company and contacts page
     *
     * @param $suffix
     *
     * @param $contact_actvity_localize
     */
    function load_contact_company_scripts( $suffix, $contact_actvity_localize ) {
        wp_enqueue_style( 'erp-timepicker' );
        wp_enqueue_script( 'erp-timepicker' );
        wp_enqueue_script( 'erp-vuejs' );
        wp_enqueue_script( 'erp-trix-editor' );
        wp_enqueue_style( 'erp-trix-editor' );
        wp_enqueue_script( 'underscore' );
        wp_enqueue_style( 'erp-nprogress' );
        wp_enqueue_script( 'erp-nprogress' );
        wp_enqueue_script( 'wp-erp-crm-vue-component', WPERP_CRM_ASSETS . "/js/crm-components.js", array( 'erp-nprogress', 'erp-script', 'erp-vuejs', 'underscore', 'erp-select2', 'erp-tiptip' ), date( 'Ymd' ), true );

        do_action( 'erp_crm_load_contact_vue_scripts' );

        if ( isset( $_GET['action'] ) && $_GET['action'] == 'view' ) {
            wp_enqueue_script( 'wp-erp-crm-vue-customer', WPERP_CRM_ASSETS . "/js/crm-app$suffix.js", apply_filters( 'crm_vue_customer_script', array( 'erp-nprogress', 'erp-script', 'erp-vuejs', 'underscore', 'erp-select2', 'erp-tiptip' ) ), date( 'Ymd' ), true );
        }

        wp_localize_script( 'wp-erp-crm-vue-component', 'wpCRMvue', $contact_actvity_localize );
        wp_enqueue_script( 'post' );
    }

    /**
     * Load scritps for settings page
     *
     * @param $hook
     */
    function load_settings_scripts( $hook ) {
        $hook = str_replace( sanitize_title( __( 'CRM', 'erp' ) ), 'crm', $hook );

        if ( 'wp-erp_page_erp-settings' == $hook && isset( $_GET['tab'] ) && $_GET['tab'] == 'erp-crm' ) {
            wp_enqueue_script( 'erp-trix-editor' );
            wp_enqueue_style( 'erp-trix-editor' );
        }

    }

    /**
     * Load template for settings page
     */
    function load_settings_js_templates() {
        global $current_screen;
        $hook = str_replace( sanitize_title( __( 'CRM', 'erp' ) ), 'crm', $current_screen->base );

        if ( 'wp-erp_page_erp-settings' == $hook && isset( $_GET['tab'] ) && $_GET['tab'] == 'erp-crm' ) {
            erp_get_js_template( WPERP_CRM_JS_TMPL . '/new-save-replies.php', 'erp-crm-new-save-replies' );
        }
    }

}
