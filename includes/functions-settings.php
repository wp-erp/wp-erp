<?php

/**
 * Get General Options Settings Data
 *
 * @since 1.8.6
 *
 * @return array general settings data
 */
function erp_settings_get_general() {
    return get_option( 'erp_settings_general', [] );
}

/**
 * Get Days
 *
 * @since 1.8.6
 *
 * @return array days
 */
function erp_settings_get_days() {
    return [ 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' ];
}

/**
 * Get Workdays Settings Data
 *
 * @since 1.8.6
 *
 * @return array workdays settings data
 */
function erp_settings_get_workdays() {
    $data = [];

    foreach ( erp_settings_get_days() as $day ) {
        $data[ $day ] = get_option( $day );
    }

    return $data;
}

/**
 * Save Workdays Settings
 *
 * @since 1.8.6
 *
 * @param array $posted
 *
 * @return void
 */
function erp_settings_save_workdays( $posted = [] ) {
    foreach ( erp_settings_get_days() as $option ) {
        $value = isset( $posted[ $option ] ) ? sanitize_text_field( wp_unslash( $posted[ $option ] ) ) : '';
        update_option( $option, $value );
    }
}
