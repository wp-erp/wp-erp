<?php

namespace WeDevs\ERP;

/**
 * ERP Status menu
 */
class Status {

    /**
     * Kick-in the class
     */
    public function __construct() {
        $this->status_scripts();

        include __DIR__ . '/Framework/views/status-page.php';
    }

    /**
     * Get status scripts
     *
     * @return void
     */
    public function status_scripts() {
        wp_enqueue_script( 'erp-system-status' );
    }

    /**
     * Include status report file
     *
     * @return void
     */
    public static function status_report() {
        include_once __DIR__ . '/Framework/views/status-report.php';
    }

    /**
     * Get latest version of a theme by slug.
     *
     * @param object $theme WP_Theme object
     *
     * @return string version number if found
     */
    public static function get_latest_theme_version( $theme ) {
        include_once ABSPATH . 'wp-admin/includes/theme.php';

        $api = themes_api( 'theme_information', [
            'slug'     => $theme->get_stylesheet(),
            'fields'   => [
                'sections' => false,
                'tags'     => false,
            ],
        ] );

        $update_theme_version = 0;

        // Check .org for updates.
        if ( is_object( $api ) && ! is_wp_error( $api ) ) {
            $update_theme_version = $api->version;
        }

        return $update_theme_version;
    }
}
