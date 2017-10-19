<?php
namespace WeDevs\ERP\CRM\ContactForms;

use WeDevs\ERP\Framework\Traits\Ajax;
use WeDevs\ERP\Framework\Traits\Hooker;
use WeDevs\ERP\Framework\ERP_Settings_Page;
use WeDevs\ERP\CRM\Contact;

trait ContactForms {

    use Ajax;
    use Hooker;

    /**
     * Integrated plugin list
     *
     * Array keys are treated as slugs, may not same as respective plugin id.
     * Hyphen is not acceptable in slug names.
     *
     * @return array
     */
    public function get_plugin_list() {
        return apply_filters( 'erp_contact_forms_plugin_list', [] );
    }

    /**
     * Get the plugins which are currently installed and active
     *
     * @return return
     */
    public function get_active_plugin_list() {
        return array_filter( $this->get_plugin_list(), function ( $plugin ) {
            return !empty( $plugin['is_active'] );
        } );
    }

    /**
     * The required CRM Contact options
     *
     * @return array
     */
    public function get_required_crm_contact_options() {
        return apply_filters( 'erp_contact_forms_required_options', [
            'first_name', 'last_name', 'email'
        ] );
    }

    /**
     * Available CRM contact options/fields
     *
     * @return array
     */
    public function get_crm_contact_options() {
        $options = [];
        $contact = new Contact( null, 'contact' );
        $crm_options = $contact->to_array();

        $ignore_options = apply_filters( 'erp_contact_forms_ignore_options', [
            'id', 'user_id', 'avatar', 'life_stage', 'type', 'source'
        ] );

        // add full_name as the crm contact option
        $crm_options = array_merge( [ 'full_name' => '' ], $crm_options );

        foreach ( $crm_options as $option => $option_val ) {

            if ( !in_array( $option, $ignore_options ) ) {

                if ( empty( $option_val ) ) {
                    $options[ $option ] = ucwords( str_replace( '_', ' ', $option ) );
                } else {
                    $options[ $option ] = [
                        'title' => ucwords( str_replace( '_', ' ', $option ) ),
                        'options' => []
                    ];

                    foreach ( $option_val as $child_option => $child_option_val ) {
                        $options[ $option ]['options'][ $child_option ] = ucwords( str_replace( '_', ' ', $child_option ) );
                    }
                }

            }

        }

        return $options;
    }

}
