<?php

namespace WeDevs\ERP\CRM\CLI\Seed;

use WP_CLI;
use WP_CLI_Command;

abstract class AbstractCrmSeeder extends WP_CLI_Command {

    /**
     * Ensure admin user is authenticated.
     */
    protected function ensure_admin() {
        wp_set_current_user( 1 );
    }

    /**
     * Store IDs for later use in subsequent seeders.
     *
     * @param string $key
     * @param array  $ids
     */
    protected function store_ids( $key, $ids ) {
        set_transient( '_erp_crm_seed_' . $key, $ids, HOUR_IN_SECONDS );
    }

    /**
     * Get stored IDs from a previous seeder.
     *
     * @param string $key
     * @return array
     */
    protected function get_ids( $key ) {
        return get_transient( '_erp_crm_seed_' . $key ) ?: [];
    }

    /**
     * Create a progress bar.
     *
     * @param string $label
     * @param int    $count
     * @return \WP_CLI\Utils\ProgressBar
     */
    protected function progress( $label, $count ) {
        return \WP_CLI\Utils\make_progress_bar( $label, $count );
    }

    /**
     * Suppress email sending during seeding.
     */
    protected function suppress_emails() {
        add_filter( 'pre_wp_mail', '__return_false' );
    }

    /**
     * Get a random element from an array.
     *
     * @param array $array
     * @return mixed
     */
    protected function random_element( $array ) {
        if ( empty( $array ) ) {
            return null;
        }
        return $array[ array_rand( $array ) ];
    }

    /**
     * Generate a random date between two dates.
     *
     * @param string $start_date Y-m-d format
     * @param string $end_date   Y-m-d format
     * @return string Y-m-d format
     */
    protected function random_date_between( $start_date, $end_date ) {
        $start_ts = strtotime( $start_date );
        $end_ts   = strtotime( $end_date );
        $rand_ts  = mt_rand( $start_ts, $end_ts );
        return date( 'Y-m-d', $rand_ts );
    }

    /**
     * Generate a random datetime between two dates.
     *
     * @param string $start_date Y-m-d format
     * @param string $end_date   Y-m-d format
     * @return string Y-m-d H:i:s format
     */
    protected function random_datetime_between( $start_date, $end_date ) {
        $date = $this->random_date_between( $start_date, $end_date );
        $hour = mt_rand( 8, 18 );
        $min  = mt_rand( 0, 59 );
        return $date . ' ' . str_pad( $hour, 2, '0', STR_PAD_LEFT ) . ':' . str_pad( $min, 2, '0', STR_PAD_LEFT ) . ':00';
    }

    /**
     * Get stored contact group IDs.
     *
     * @return array
     */
    protected function get_contact_group_ids() {
        return $this->get_ids( 'contact_group_ids' );
    }

    /**
     * Get stored company IDs.
     *
     * @return array
     */
    protected function get_company_ids() {
        return $this->get_ids( 'company_ids' );
    }

    /**
     * Get stored contact IDs.
     *
     * @return array
     */
    protected function get_contact_ids() {
        return $this->get_ids( 'contact_ids' );
    }

    /**
     * Get stored pipeline IDs.
     *
     * @return array
     */
    protected function get_pipeline_ids() {
        return $this->get_ids( 'pipeline_ids' );
    }

    /**
     * Get stored deal IDs.
     *
     * @return array
     */
    protected function get_deal_ids() {
        return $this->get_ids( 'deal_ids' );
    }

    /**
     * Check if a database table exists.
     *
     * @param string $table_name Table name without prefix
     * @return bool
     */
    protected function table_exists( $table_name ) {
        global $wpdb;
        $table = $wpdb->prefix . $table_name;
        return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
    }
}
