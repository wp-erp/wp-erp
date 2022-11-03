<?php

namespace WeDevs\ERP\Settings;

/**
 * Integration class
 */
class License extends Template {

    /**
     * Class constructor
     */
    public function __construct() {
        $this->id    = 'erp-license';
        $this->label = __( 'Licenses', 'erp' );
        $this->icon  = WPERP_ASSETS . '/images/wperp-settings/integration.png';

        $this->extra = [
            'extensions' => $this->get_licenses()
        ];

        add_action( 'erp_admin_field_licenses', [ $this, 'integrations' ] );
    }

    /**
     * Retrieves extension-wise license details
     *
     * @since 1.10.0
     *
     * @return void
     */
    public function get_licenses() {
        $extensions = erp_addon_licenses();

        foreach ( $extensions as &$ext ) {
            $ext['status'] = erp_get_license_status( $ext );
        }
    }

    /**
     * Get section fields
     *
     * @since 1.10.0
     *
     * @return array
     */
    public function get_section_fields( $section = false ) {
        $fields = [
            [
                'title' => __( 'License Manager', 'erp' ),
                'desc'  => sprintf( __( 'Enter your extension license keys here to receive updates for purchased extensions. Visit <a href="%s" target="_blank">your account</a> page.', 'erp' ), 'https://wperp.com/my-account/' ),
                'type'  => 'title',
                'id'    => 'integration_settings',
            ],
            [ 'type' => 'sectionend', 'id' => 'script_styling_options' ],
        ]; // End general settings

        return apply_filters( 'erp_integration_settings', $fields );
    }
}
