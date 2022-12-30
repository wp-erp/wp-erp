<?php

namespace WeDevs\ERP\Admin;

/**
 * Administration Module Class
 */
class AdminModule {
    public function __construct() {
        $this->output();

        // Enqueue toaster for this page only
        wp_enqueue_style( 'erp-toastr' );
        wp_enqueue_script( 'erp-toastr' );
    }

    public function output() {
        require_once WPERP_VIEWS . '/modules.php';
    }
}
