<?php
namespace WeDevs\ERP\CRM;

/**
 * CRM Contact Forms class
 */
class Contact_Forms_Integration {

    /**
     * The class constructor
     */
    public function __construct() {
        add_filter( 'erp_settings_pages', array( $this, 'add_erp_settings_tab' ) );

        // save admin settings via ajax
        add_filter( 'wp_ajax_erp_settings_contact_forms', function () {
            include_once WPERP_CRM_PATH . '/includes/admin/class-erp-settings-contact-forms.php';
            \ERP_Settings_Contact_Forms::init()->save_erp_settings();
        } );

        // reset admin settings via ajax
        add_filter( 'wp_ajax_erp_settings_contact_forms_reset', function () {
            include_once WPERP_CRM_PATH . '/includes/admin/class-erp-settings-contact-forms.php';
            \ERP_Settings_Contact_Forms::init()->reset_erp_settings();
        } );

        // built-in form submission hook functions
        $this->add_default_plugin_submission_hooks();

        // Hook save_submitted_form_data function to
        // the respective plugin functions
        $this->add_form_submission_actions();
    }

    /**
     * Initializes the class
     *
     * Checks for an existing instance
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
     * Add a new ERP settings tab
     *
     * @param array $settings ERP settings tabs
     *
     * @return array
     */
    public function add_erp_settings_tab( $settings ) {
        if ( erp_crm_is_current_user_manager() ) {
            include_once WPERP_CRM_PATH . '/includes/admin/class-erp-settings-contact-forms.php';
            $settings[] = \ERP_Settings_Contact_Forms::init();
        }

        return $settings;
    }

    /**
     * Integrated plugin list
     *
     * Array keys are treated as slugs, may not same as respective plugin id.
     * Hyphen is not acceptable in slug names.
     *
     * @return array
     */
    public function get_plugin_list() {
        // built-in supported plugins
        $plugins = array(

            'contact_form_7' => array(
                'title' => __( 'Contact Form 7', 'wp-erp' ),
                'is_active' => class_exists( 'WPCF7_ContactForm' )
            ),

            'ninja_forms' => array(
                'title' => 'Ninja Forms',
                'is_active' => class_exists( 'Ninja_Forms' )
            )

        );

        return apply_filters( 'erp_contact_forms_plugin_list', $plugins );
    }

    /**
     * Get the plugins which are currently installed and active
     *
     * @return return
     */
    public function get_active_plugin_list() {
        return array_filter( $this->get_plugin_list(), function ($plugin) {
            return !empty( $plugin['is_active'] );
        } );
    }

    /**
     * The required CRM Contact options
     *
     * @return array
     */
    public function get_required_crm_contact_options() {
        return apply_filters( 'erp_contact_forms_required_options', array(
            'first_name', 'last_name', 'email'
        ) );
    }

    /**
     * Available CRM contact options
     *
     * @return array
     */
    public function get_crm_contact_options() {
        $options = array();
        $contact = new \WeDevs\ERP\CRM\Contact();
        $crm_options = $contact->to_array();

        $ignore_options = apply_filters( 'erp_contact_forms_ignore_options', array(
            'id', 'user_id', 'avatar', 'life_stage', 'type'
        ));

        // add full_name as the crm contact option
        $crm_options = array_merge( array( 'full_name' => '' ), $crm_options );

        foreach ( $crm_options as $option => $option_val ) {
            if ( !in_array( $option, $ignore_options ) ) {
                if ( empty( $option_val ) ) {
                    $options[ $option ] = ucwords( str_replace( '_', ' ', $option ) );
                } else {
                    $options[ $option ] = array(
                        'title' => ucwords( str_replace( '_', ' ', $option ) ),
                        'options' => array()
                    );

                    foreach ( $option_val as $child_option => $child_option_val ) {
                        $options[ $option ]['options'][ $child_option ] = ucwords( str_replace( '_', ' ', $child_option ) );
                    }
                }
            }
        }

        return $options;
    }

    /**
     * Hook functions to the default plugin form submission actions
     *
     * @return void
     */
    protected function add_default_plugin_submission_hooks() {
        // contact form 7
        add_action( 'wpcf7_submit', array( $this, 'wpcf7_on_submit' ) );

        // contact form 7
        add_action( 'nf_save_sub', array( $this, 'ninja_forms_on_submit' ) );
    }

    /**
     * Hook save_submitted_form_data function
     *
     * For a particular plugin, we'll need a function that provide submitted
     * data to the save_submitted_form_data function. In order to do this,
     * we'll need to supply the data in a do_action call like
     * do_action( "wperp_integration_{$slug}_form_submit", $submitted_data, $plugin_slug, $form_id )
     *
     * @return void
     */
    protected function add_form_submission_actions() {
        foreach ( $this->get_active_plugin_list() as $slug => $plugin ) {
            add_action( "wperp_integration_{$slug}_form_submit", array( $this, 'save_submitted_form_data' ), 10, 3 );
        }
    }

    /**
     * Save form sumitted data as new CRM Contact
     *
     * @param array $data submitted form data
     * @param string $plugin plugin slug defined in get_plugin_list function
     * @param string $form_id submitted form id
     *
     * @return void
     */
    public function save_submitted_form_data( $data, $plugin, $form_id ) {
        $cfi_settings = get_option( 'wperp_crm_contact_forms', '' );

        if ( ! empty( $cfi_settings[ $plugin ][ $form_id ] ) ) {
            $settings = $cfi_settings[ $plugin ][ $form_id ];

            $contact = array(
                'type' => 'contact'
            );

            foreach ( $settings as $field => $option ) {
                if ( !empty( $option ) ) {
                    if ( 'full_name' === $option ) {
                        $name_arr = explode( ' ', $data[ $field ] );

                        if ( count( $name_arr ) > 1 ) {
                            $contact[ 'last_name' ] = array_pop( $name_arr );
                            $contact[ 'first_name' ] = implode( ' ' , $name_arr );
                        }
                    } else {
                        $contact[ $option ] = $data[ $field ];
                    }
                }
            }

            if ( $people_id = erp_insert_people( $contact ) ) {
                erp_people_update_meta( $people_id, 'life_stage', 'lead' );

                $plugin_list = $this->get_plugin_list();
                erp_people_update_meta( $people_id, 'source', $plugin_list[ $plugin ]['title'] . ' - Form ID: ' . $form_id );
            }
        }
    }

    /**
     * After Contact Form 7 submission hook
     *
     * @return void
     */
    public function wpcf7_on_submit() {
        if ( empty( $_POST['_wpcf7'] ) ) {
            return;
        }

        // first check if submitted form has settings or not
        $cfi_settings = get_option( 'wperp_crm_contact_forms', '' );
        $cf7_settings = $cfi_settings['contact_form_7'];

        if ( in_array( $_POST['_wpcf7'] , array_keys( $cf7_settings ) ) ) {
            do_action( "wperp_integration_contact_form_7_form_submit", $_POST, 'contact_form_7', $_POST['_wpcf7'] );
        }
    }

    /**
     * After Ninja Forms submission hook
     *
     * @return void
     */
    public function ninja_forms_on_submit( $sub_id ) {
        $nf = Ninja_forms();
        $form_id = 0;
        $data = array();

        if ( !nf_is_freemius_on() ) {
            /* Support for non-freemius version */
            $sub = $nf->sub( $sub_id );
            $form_id = $sub->form_id;
            $data = $sub->field;

        } else {
            /* Support for freemius version */
            $sub = $nf->form()->get_sub( $sub_id );

            $formData = $_POST['formData'];
            $formData = str_replace( '\"' , '"', $formData);
            $formData = json_decode( $formData, true );

            $form_id = $formData['id'];

            foreach ( $formData['fields'] as $i => $field ) {
                $data[ $field['id'] ] = $field['value'];
            }
        }

        // first check if submitted form has settings or not
        $cfi_settings = get_option( 'wperp_crm_contact_forms', '' );
        $cf7_settings = $cfi_settings['ninja_forms'];

        if ( in_array( $form_id , array_keys( $cf7_settings ) ) ) {
            do_action( "wperp_integration_ninja_forms_form_submit", $data, 'ninja_forms', $form_id );
        }

    }

} // Contact_Forms_Integration

// execute only when CRM is loaded
add_action( 'erp_crm_loaded', function () {
    Contact_Forms_Integration::init();
} );
