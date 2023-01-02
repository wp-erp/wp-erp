<?php

namespace WeDevs\ERP;

/**
 * ERP Danger Zone Class
 *
 * Reset all data of WP ERP and make a fresh installation
 *
 * @since 1.9.0
 */
class DangerZone {

    /**
     * Kick-in the class
     */
    public function __construct() {
        $this->get_scripts();
        $this->get_views();
    }

    /**
     * Get Danger zone scripts
     *
     * @since 1.9.0
     *
     * @return void
     */
    public function get_scripts() {
        wp_localize_script( 'erp-script', 'wpErpDangerZone', [
            'resetErp'                   => __( 'Reset WP ERP?', 'erp' ),
            'areYouSureReset'            => __( 'Are you sure you want to reset WP ERP?', 'erp' ),
            'yesResetIt'                 => __( 'Yes, Reset Now', 'erp' ),
            'confirmResetBeforeContinue' => __( 'Please confirm the "Reset" text to reset WP ERP', 'erp' ),
            'somethingWrong'             => __( 'Something went wrong, Please try again !', 'erp' ),
            'trashIcon'                  => WPERP_ASSETS . '/images/trash-circle.png',
        ] );
    }

    /**
     * Get Views
     *
     * @since 1.9.0
     *
     * @return void
     */
    public function get_views() {
        include __DIR__ . '/Admin/views/tools/danger-zone.php';
    }
}
