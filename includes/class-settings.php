<?php

namespace WeDevs\ERP;

use WeDevs\ERP\Framework\Ajax_Handler;
use WeDevs\ERP\Framework\Settings_Assets;

/**
 * Administration Settings menu
 */
class Settings {

    /**
     * Kick-in the class
     */
    public function __construct() {
        $this->settings_scripts();

        require_once __DIR__ . '/framework/class-settings-page.php';
        require_once __DIR__ . '/framework/class-settings.php';

        include __DIR__ . '/framework/views/settings-page.php';
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

        // Handle Ajax works for settings
        new Ajax_Handler();

        // Add Menus
        $this->addMenus();

        // Add/Register scripts for SPA
        new Settings_Assets();
    }


    public function addMenus()
    {
        $general = 'erp_settings_view_general';

        erp_add_menu(
            'settings',
            [
                'title'      => __( 'General', 'erp' ),
                'capability' => $general,
                'slug'       => 'general',
                'position'   => 1,
            ]
        );
    }
}
