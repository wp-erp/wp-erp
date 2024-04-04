<?php

/**
 * Get start and end date from a specific time
 *
 * @return array
 */
if ( !function_exists( 'erp_att_get_start_end_date' ) ) {
    function erp_att_get_start_end_date( $time = '' ) {
        $duration = [];

        $start_date = current_time( 'Y-m-d' );
        $end_date   = current_time( 'Y-m-d' );

        if ( $time ) {
            switch ( $time ) {

            case 'today':

                $start_date = current_time( 'Y-m-d' );
                $end_date   = $start_date;
                break;

            case 'yesterday':

                $today      = strtotime( current_time( 'Y-m-d' ) );
                $start_date = gmdate( 'Y-m-d', strtotime( '-1 days', $today ) );
                $end_date   = $start_date;
                break;

            case 'last_7_days':

                $end_date   = current_time( 'Y-m-d' );
                $start_date = gmdate( 'Y-m-d', strtotime( '-6 days', strtotime( $end_date ) ) );
                break;

            case 'this_month':

                $start_date = gmdate( 'Y-m-d', strtotime( 'first day of this month' ) );
                $end_date   = gmdate( 'Y-m-d', current_time( 'timestamp' ) );
                break;

            case 'last_month':

                $start_date = gmdate( 'Y-m-d', strtotime( 'first day of previous month' ) );
                $end_date   = gmdate( 'Y-m-d', strtotime( 'last day of previous month' ) );
                break;

            case 'this_quarter':

                $current_month = gmdate( 'm' );
                $current_year  = gmdate( 'Y' );

                if ( $current_month >= 1 && $current_month <= 3 ) {
                    $start_date = gmdate( 'Y-m-d', strtotime( '1-January-' . $current_year ) );
                    $end_date   = gmdate( 'Y-m-d', strtotime( '31-March-' . $current_year ) );
                } elseif ( $current_month >= 4 && $current_month <= 6 ) {
                    $start_date = gmdate( 'Y-m-d', strtotime( '1-April-' . $current_year ) );
                    $end_date   = gmdate( 'Y-m-d', strtotime( '30-June-' . $current_year ) );
                } elseif ( $current_month >= 7 && $current_month <= 9 ) {
                    $start_date = gmdate( 'Y-m-d', strtotime( '1-July-' . $current_year ) );
                    $end_date   = gmdate( 'Y-m-d', strtotime( '30-September-' . $current_year ) );
                } elseif ( $current_month >= 10 && $current_month <= 12 ) {
                    $start_date = gmdate( 'Y-m-d', strtotime( '1-October-' . $current_year ) );
                    $end_date   = gmdate( 'Y-m-d', strtotime( '31-December-' . $current_year ) );
                }
                break;

            case 'last_quarter':

                $current_month = gmdate( 'm' );
                $current_year  = gmdate( 'Y' );

                if ( $current_month >= 1 && $current_month <= 3 ) {
                    $start_date = gmdate( 'Y-m-d', strtotime( '1-October-' . ( $current_year - 1 ) ) );
                    $end_date   = gmdate( 'Y-m-d', strtotime( '31-December-' . ( $current_year - 1 ) ) );
                } elseif ( $current_month >= 4 && $current_month <= 6 ) {
                    $start_date = gmdate( 'Y-m-d', strtotime( '1-January-' . $current_year ) );
                    $end_date   = gmdate( 'Y-m-d', strtotime( '31-March-' . $current_year ) );
                } elseif ( $current_month >= 7 && $current_month <= 9 ) {
                    $start_date = gmdate( 'Y-m-d', strtotime( '1-April-' . $current_year ) );
                    $end_date   = gmdate( 'Y-m-d', strtotime( '30-June-' . $current_year ) );
                } elseif ( $current_month >= 10 && $current_month <= 12 ) {
                    $start_date = gmdate( 'Y-m-d', strtotime( '1-July-' . $current_year ) );
                    $end_date   = gmdate( 'Y-m-d', strtotime( '30-September-' . $current_year ) );
                }
                break;

            case 'last_year':

                $start_date = gmdate( 'Y-01-01', strtotime( '-1 year' ) );
                $end_date   = gmdate( 'Y-12-31', strtotime( '-1 year' ) );
                break;

            case 'this_year':

                $start_date = gmdate( 'Y-01-01' );
                $end_date   = gmdate( 'Y-12-31' );
                break;

            case 'custom':

                $start_date = isset( $_REQUEST['start'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['start'] ) ) : gmdate( 'Y-m-d' );
                $end_date   = isset( $_REQUEST['end'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['end'] ) ) : gmdate( 'Y-m-d' );
                break;

            default:
                break;
        }
        }

        $duration = [
        'start' => $start_date,
        'end'   => $end_date,
    ];

        return $duration;
    }
}

/**
 * Delete all the entitlements those created for inactive employees
 * Version 1.3.4 updated
 *
 * @return void
 */
function wperp_update_remove_entitlements_1_3_4() {
    $period = erp_att_get_start_end_date( 'this_year' );

    $employees    = \WeDevs\ERP\HRM\Models\Employee::select( 'user_id' )->where( 'status', '!=', 'active' )->get()->toArray();

    if ( $period && $employees ) {
        $entitlements = \WeDevs\ERP\HRM\Models\LeaveEntitlement::select( 'id' )
            ->whereIn( 'user_id', $employees )
            ->whereDate( 'from_date', '>=', $period['start'] )
            ->whereDate( 'to_date', '<=', $period['end'] )
            ->delete();
    }
}

wperp_update_remove_entitlements_1_3_4();
