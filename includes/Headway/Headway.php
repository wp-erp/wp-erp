<?php

namespace WeDevs\ERP\Headway;

class Headway {

    public function __construct() {
        add_filter( 'script_loader_tag', [ $this, 'add_async_attribute' ], 10, 3 );
        add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ] );
    }

    public function register_assets() {
        wp_register_script(
            'erp-headway',
            plugin_dir_url( __FILE__ ) . 'headway.js',
            [ 'jquery' ],
            WPERP_VERSION,
            true
        );

        wp_register_style(
            'erp-headway',
            plugin_dir_url( __FILE__ ) . 'headway.css',
            [],
            WPERP_VERSION
        );
    }

    public function add_async_attribute( $tag, $handle, $src ) {
        if ( 'erp-headway' === $handle ) {
            return str_replace( ' src', ' async src', $tag );
        }
        return $tag;
    }
}
