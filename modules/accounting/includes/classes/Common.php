<?php
namespace WeDevs\ERP\Accounting\Includes\Classes;

/**
 * Class for common methods
 *
 */
class Common {

    /**
     * Retrievesclosest financial year by start date
     *
     * @param $start_date
     *
     * @return array
     */
    public static function closest_financial_year( $start_date ) {
        global $wpdb;

        $start_date = ! empty( $start_date )
                      ? erp_current_datetime()->modify( $start_date )->format( 'Y-m-d' )
                      : erp_current_datetime()->format( 'Y-m-d' );

        $sql = "SELECT id, name, start_date, end_date
                FROM {$wpdb->prefix}erp_acct_financial_years
                WHERE start_date <= '%s'
                ORDER BY start_date DESC
                LIMIT 1";

       $year = $wpdb->get_row( $wpdb->prepare( $sql, $start_date ), ARRAY_A );

       return ! is_wp_error( $year ) ? $year : [];
    }
}
