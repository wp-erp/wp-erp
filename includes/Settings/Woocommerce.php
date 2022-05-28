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
        $this->id    = 'erp-woocommerce';
        $this->label = __( 'WooCommerce', 'erp' );
        $this->icon  = WPERP_ASSETS . '/images/wperp-settings/woocommerce.png';
        $this->extra = [
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
        return sprintf(
            /* translators: 1) <br> tag, 2) <br> tag, 3) opening anchor tag with link, 4) closing anchor tag */
            __( 'We\'re Sorry, WooCommerce Integration Is Not %1$s Available on WP ERP Free. Please Upgrade to %2$s %3$sWP ERP Pro%4$s to Unlock This feature.', 'erp' ),
            '<br>',
            '<br>',
            '<a target="_blank" href="https://wperp.com/pricing/?nocache&utm_medium=modules&utm_source=erp-settings-page">',
            '</a>'
        );
    }
}
