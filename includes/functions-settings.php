<?php

use \WeDevs\ERP\HRM\Models\Financial_Year;

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

            if ( empty ( $option_value ) ) {
                $option_value = ! empty ( $option['default'] ) ? $option['default'] : '';
            }

            // Process option value for different type input
            switch ( $option['type'] ) {
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
