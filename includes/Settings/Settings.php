<?php

namespace WeDevs\ERP\Settings;

use WeDevs\ERP\Settings\Assets;

/**
 * Administration Settings menu
 */
class Settings {

    /**
     * Kick-in the class
     */
    public function __construct() {
        $this->settings_scripts();
        $this->includes();
    }

    /**
     * Includes required files
     *
     * @return void
     */
    public function includes() {
        include_once __DIR__ . '/views/settings.php';
    }

    /**
     * Get settings scripts
     *
     * @return void
     */
    public function settings_scripts() {
        wp_enqueue_style( 'wp-color-picker' );

        wp_enqueue_media();
        wp_enqueue_script( 'erp-tiptip' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'erp-admin-settings' );

        // Add/Register scripts for SPA
        new Assets();
    }
}
