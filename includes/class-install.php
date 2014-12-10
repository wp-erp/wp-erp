<?php

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Installer Class
 *
 * @package payroll
 */
class WDP_Install {

    function __construct() {
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
    }


    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate() {
        update_option( 'payroll_version', PAYROLL_VERSION );
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate() {

    }
}

new WDP_Install();