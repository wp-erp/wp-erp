<?php

namespace WeDevs\ERP\Accounting\Classes;

use Plugin_Upgrader;

require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

class PDFInstall {

    /**
     * Install Plugin
     *
     * @param $url
     *
     * @return int
     */
    public function install_plugin( $url ) {
        if ( strstr( $url, '.zip' ) !== false ) {
            $download_link = $url;
        } else {
            $slug          = explode( '/', $url );
            $slug          = $slug[ count( $slug ) - 2 ];
            $api           = plugins_api(
                'plugin_information',
                [
                    'slug'   => $slug,
                    'fields' => [ 'sections' => 'false' ],
                ]
            );
            $download_link = $api->download_link;
        }

        $upgrader = new Plugin_Upgrader();

        if ( ! $upgrader->install( $download_link ) ) {
            return 0;
        }

        $plugin_to_activate = $upgrader->plugin_info();
        $this->activate_pdf_plugin( $plugin_to_activate );

        return 1;
    }

    /**
     * Activate plugin
     *
     * @param $plugin_to_activate
     */
    public function activate_pdf_plugin( $plugin_to_activate ) {
        $activate = activate_plugin( $plugin_to_activate );
        wp_cache_flush();

        $this->show_activation_notice();
    }

    /**
     * Show plugin activation notice
     *
     * @param $plugin_to_activate
     */
    public function show_activation_notice() {
        ?>
        <div class="updated notice is-dismissible">
            <p>
                <?php
                /* translators: 1) opening strong tag, 2) closing strong tag */
                printf( esc_html__( 'Plugin %1$sactivated%2$s', 'erp' ), '<strong>', '</strong>' );
                ?>.
            </p>
        </div>
        <?php
    }
}
