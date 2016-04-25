<?php
namespace WeDevs\ERP\CRM\ContactForms;

/**
 * CRM Contact Forms class
 */
class Contact_Forms_Integration {

    use ContactForms;

    /**
     * ERP_Settings_Contact_Forms class instance
     *
     * @var object
     */
    protected $settings;

    /**
     * The class constructor
     */
    public function __construct() {
        // ajax hooks
        $this->action( 'wp_ajax_erp_settings_save_contact_form', 'save_erp_settings' );
        $this->action( 'wp_ajax_erp_settings_reset_contact_form', 'reset_erp_settings' );

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
     * Save admin settings via ajax
     *
     * @return void
     */
    public function save_erp_settings() {
        ERP_Settings_Contact_Forms::init()->save_erp_settings();
    }

    /**
     * Reset admin settings via ajax
     *
     * @return void
     */
    public function reset_erp_settings() {
        ERP_Settings_Contact_Forms::init()->reset_erp_settings();
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
            $this->action( "wperp_integration_{$slug}_form_submit", 'save_submitted_form_data', 10, 3 );
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
            $settings = $cfi_settings[ $plugin ][ $form_id ]['map'];

            $contact = [
                'type' => 'contact'
            ];

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
                erp_people_update_meta( $people_id, 'source', 'contact_form' );

                if ( !empty( $cfi_settings[ $plugin ][ $form_id ]['contact_group'] ) ) {
                    $groups = array( $cfi_settings[ $plugin ][ $form_id ]['contact_group'] );
                    erp_crm_edit_contact_subscriber( $groups, $people_id );
                }
            }

        }
    }

}
