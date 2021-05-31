<?php

/**
 * Get General Options Settings Data
 *
 * @since 1.8.6
 *
 * @return array general settings data
 */
function erp_settings_get_general () {
    return get_option( 'erp_settings_general', [] );
}

/**
 * Get Days
 *
 * @since 1.8.6
 *
 * @return array days
 */
function erp_settings_get_days () {
    return [ 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' ];
}

/**
 * Get Workdays Settings Data
 *
 * @since 1.8.6
 *
 * @return array workdays settings data
 */
function erp_settings_get_workdays () {
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
function erp_settings_save_workdays ( $posted = [] ) {
    foreach ( erp_settings_get_days() as $option ) {
        $value = isset( $posted[ $option ] ) ? sanitize_text_field( wp_unslash( $posted[ $option ] ) ) : '';
        update_option( $option, $value );
    }
}

function erp_settings_update_checkbox_options ( $options = [], $posted = [] ) {
    foreach ( $options as $option ) {
        $value = isset( $posted[ $option ] ) ? sanitize_text_field( wp_unslash( $posted[ $option ] ) ) : '';

        if( $value ) {
            $value = 'yes';
        } else {
            $value = 'no';
        }
        update_option( $option, $value );
    }
}

function erp_settings_leave_options () {
    $options = [ 'enable_extra_leave', 'erp_pro_accrual_leave', 'erp_pro_carry_encash_leave', 'erp_pro_half_leave', 'erp_pro_multilevel_approval', 'erp_pro_seg_leave', 'erp_pro_sandwich_leave' ];
    return $options;
}

function erp_settings_get_leaves () {
    $data = [];

    foreach ( erp_settings_leave_options() as $option ) {
        $option = get_option( $option,  'no' );
        $data[ $option ] = ( $option === '1' || $option === true || $option === 'yes' ) ? true : false;
    }

    return $data;
}

function erp_settings_save_leaves ( $posted = [] ) {
    erp_settings_update_checkbox_options( erp_settings_leave_options(), $posted );
}
