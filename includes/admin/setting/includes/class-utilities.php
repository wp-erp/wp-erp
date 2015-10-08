<?php

/**
 * bedIQ Utilities class
 */
class bedIQ_Site_Utilites {

    function __construct() {
        add_action( 'wp_head', array($this, 'print_favicon') );
        add_action( 'wp_head', array($this, 'print_touch_icons') );
    }

    public function print_favicon() {
        $favicon = (int) bediq_get_option( 'favicon', 'bediq_settings_design' );

        if ( $favicon ) {
            $url = wp_get_attachment_url( $favicon );

            printf( '<link rel="shortcut icon" type="image/x-icon" href="%s" />%s', esc_url( $url ), "\n" );
        }
    }

    public function print_touch_icons() {
        $design = bediq_get_option( 'bediq_settings_design' );

        if ( isset( $design['72'] ) && $design['72'] !== '0' ) {
            $url = wp_get_attachment_url( $design['72'] );

            printf( '<link rel="apple-touch-icon" sizes="72x72" href="%s" />%s', esc_url( $url ), "\n" );
        }

        if ( isset( $design['76'] ) && $design['76'] !== '0' ) {
            $url = wp_get_attachment_url( $design['76'] );

            printf( '<link rel="apple-touch-icon" sizes="76x76" href="%s" />%s', esc_url( $url ), "\n" );
        }

        if ( isset( $design['114'] ) && $design['114'] !== '0' ) {
            $url = wp_get_attachment_url( $design['114'] );

            printf( '<link rel="apple-touch-icon" sizes="114x114" href="%s" />%s', esc_url( $url ), "\n" );
        }

        if ( isset( $design['120'] ) && $design['120'] !== '0' ) {
            $url = wp_get_attachment_url( $design['120'] );

            printf( '<link rel="apple-touch-icon" sizes="120x120" href="%s" />%s', esc_url( $url ), "\n" );
        }

        if ( isset( $design['144'] ) && $design['144'] !== '0' ) {
            $url = wp_get_attachment_url( $design['144'] );

            printf( '<link rel="apple-touch-icon" sizes="144x144" href="%s" />%s', esc_url( $url ), "\n" );
        }

        if ( isset( $design['152'] ) && $design['152'] !== '0' ) {
            $url = wp_get_attachment_url( $design['152'] );

            printf( '<link rel="apple-touch-icon" sizes="152x152" href="%s" />%s', esc_url( $url ), "\n" );
        }
    }

    public function print_google_analytics() {

    }
}

new bedIQ_Site_Utilites();