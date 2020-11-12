<?php


namespace WeDevs\ERP\Accounting\Includes\Classes;


class Common {

    /**
     * get closest financial year by start date
     * @param $startDate
     * @return array|object|void|null
     */
    public static function getClosestFinYear( $startDate ) {
        global $wpdb;

        $sql = "SELECT id, name, start_date, end_date FROM {$wpdb->prefix}erp_acct_financial_years WHERE start_date <= '%s' ORDER BY start_date DESC LIMIT 1";
       return $wpdb->get_row( $wpdb->prepare( $sql, $startDate ), ARRAY_A );
    }


}
