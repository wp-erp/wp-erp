<?php

/**
 * Get bediq option from settings framework
 *
 * @param  sting  $option_name name of the option
 * @param  string $section     name of the section. if it's a separate option, don't provide any
 * @param  string $default     default option
 * @return string
 */
function bediq_get_option( $option_name, $section = false, $default = '' ) {

    if ( $section ) {
        $option = get_option( $section );

        if ( isset( $option[$option_name] ) ) {
            return $option[$option_name];
        } else {
            return $default;
        }
    } else {
        return get_option( $option_name, $default );
    }
}

/**
 * Get bedIQ logo
 *
 * @return string url of the logo
 */
function bediq_get_site_logo() {
    $logo = (int) bediq_get_option( 'logo', 'bediq_settings_design' );

    if ( $logo ) {
        $logo_url = wp_get_attachment_url( $logo );

        return $logo_url;
    }
}