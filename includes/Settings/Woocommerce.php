<?php

namespace WeDevs\ERP\Settings;

/**
 * WooCommerce settings class
 */
class Woocommerce extends Template {
    
    /**
     * Constructor function
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->id     = 'erp-woocommerce';
        $this->label  = __( 'WooCommerce', 'erp-pro' );
        $this->icon   = WPERP_ASSETS . '/images/wperp-settings/woocommerce.png';

        $this->extra  = [
            'notice' => apply_filters( 'erp_wc_integration_notice', $this->get_integration_notice() ),
        ];
    }

    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {
        $country = \WeDevs\ERP\Countries::instance();

        $fields = [];

        return apply_filters( 'erp_settings_woocommerce', $fields );
    }

    /**
     * Get sections fields
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_section_fields( $section = '' ) {
        return $this->get_settings();
    }

    /**
     * Gets integration notice
     * 
     * @since 1.2.1
     *
     * @return void
     */
    public function get_integration_notice() {
        $erp_pro_url = 'https://wperp.com/pricing/?nocache&utm_medium=modules&utm_source=erp-settings-page';
        
        return __( "We're Sorry, WooCommerce Integration Is Not<br>Available on WP ERP Free. Please Upgrade to<br><a target='_blank' href='{$erp_pro_url}'>WP ERP Pro</a> to Unlock This feature.", "erp" );
    }
}