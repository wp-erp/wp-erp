<?php

use \WeDevs\ERP\HRM\Models\Financial_Year;

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
 * Get Options For Settings
 *
 * @since 1.8.6
 *
 * @param array $options - Setting options
 *
 * @return array $data settings data
 */
function erp_settings_get_data ( $options = [] ) {
    $data               = [];
    $single_option      = true;
    $single_option_data = [];

    if ( ! empty ( $options['single_option'] ) ) {
        $single_option_id   = 'erp_settings_' . $options['single_option'];
        $single_option      = false;
        $single_option_data = get_option( $single_option_id );
    }

    foreach ( $options as $option ) {
        if ( ! empty ( $option['id'] ) ) {
            $option_value = $single_option ? get_option( $option['id'] ) : $single_option_data[ $option['id'] ];

            // var_dump( $option['id'] .' -->> ' .$option_value );

            switch ($option['type']) {
                case 'checkbox':
                    $option_value = $option_value === 'yes' ? true : false;
                    break;

                case 'image':
                    $option_value = (int) $option_value;
                    $option_value = $option_value ? wp_get_attachment_url( $option_value ) : '';

                default:
                    break;
            }

            $option['value'] = $option_value;

            array_push( $data, $option );
        }
    }

    return $data;
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
 * Get Settings leave options
 *
 * @since 1.8.6
 *
 * @todo get data using hook from erp-pro
 *
 * @return array $options
 */
function erp_settings_leave_options () {
    $options = [ 'enable_extra_leave', 'erp_pro_accrual_leave', 'erp_pro_carry_encash_leave', 'erp_pro_half_leave', 'erp_pro_multilevel_approval', 'erp_pro_seg_leave', 'erp_pro_sandwich_leave' ];
    return $options;
}

/**
 * Get Settings Leave Data
 *
 * @since 1.8.6
 *
 * @return array $data
 */
function erp_settings_get_leaves () {
    $data = [];

    foreach ( erp_settings_leave_options() as $option ) {
        $option_value    = get_option( $option,  'no' );
        $data[ $option ] = ( $option_value === '1' || $option_value === true || $option_value === 'yes' ) ? true : false;
    }

    return $data;
}

/**
 * Get Settings Miscellaneous Data
 *
 * @since 1.8.6
 *
 * @return array $data
 */
function erp_settings_get_miscellaneous () {
    $data['erp_hrm_remove_wp_user'] = ( get_option( 'erp_hrm_remove_wp_user',  'no' ) === 'yes' ) ? true : false;

    return $data;
}

/**
 * Get ERP Financial Years
 *
 * @since 1.8.6
 *
 * @return array $f_years
 */
function erp_get_hr_financial_years() {
    global $wpdb;

    $f_years = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}erp_hr_financial_years", ARRAY_A );

    return $f_years;
}

/**
 * ERP Settings save leave years
 *
 * @since 1.8.6
 *
 * @param array $post_data
 *
 * @return object|void
 */
function erp_settings_save_leave_years ( $post_data = [] ) {

    // Error handles
    foreach ( $post_data as $key => $data ) {
        if ( empty( $data['fy_name'] ) ) {
            return new WP_Error( 'errors', __( "Please give a financial year name on row - " . ( $key + 1 ), "erp" ) );
        }
        if ( empty( $data['start_date'] ) ) {
            return new WP_Error( 'errors', __( "Please give a financial year start date on row - " . ( $key + 1 ), "erp" ) );
        }
        if ( empty( $data['end_date'] ) ) {
            return new WP_Error( 'errors', __( "Please give a financial year end date on row - " . ( $key + 1 ), "erp" ) );
        }
        if ( strtotime( $data['end_date'] ) < strtotime( $data['start_date'] )  ) {
            return new WP_Error( 'errors', __( "Second value must be greater than the first value on row - " . ( $key + 1 ), "erp" ) );
        }
    }

    // Insert leave years
    foreach ( $post_data as $data ) {

        $data['fy_name']     = sanitize_text_field( wp_unslash( $data['fy_name'] ) );
        $data['start_date']  = strtotime( sanitize_text_field( wp_unslash( $data['start_date'] ) ) );
        $data['end_date']    = strtotime( sanitize_text_field( wp_unslash( $data['end_date'] ) ) );
        $data['description'] = sanitize_text_field( wp_unslash( $data['description'] ) );

        // If found ID, then update else create
        if ( ! empty( $data['id'] ) ) {
            $data['updated_by'] = get_current_user_id();
            Financial_Year::where( 'id', $data['id'] )->update( $data );
        } else {
            $data['created_by'] = get_current_user_id();
            Financial_Year::create( $data );
        }
    }

    return true;
}
