<?php

namespace WeDevs\ERP\HRM\CLI\Seed;

use WP_CLI;
use WP_CLI_Command;

abstract class AbstractSeeder extends WP_CLI_Command {

    protected function ensure_admin() {
        wp_set_current_user( 1 );
    }

    protected function store_ids( $key, $ids ) {
        set_transient( '_erp_seed_' . $key, $ids, HOUR_IN_SECONDS );
    }

    protected function get_ids( $key ) {
        return get_transient( '_erp_seed_' . $key );
    }

    protected function progress( $label, $count ) {
        return \WP_CLI\Utils\make_progress_bar( $label, $count );
    }

    protected function suppress_emails() {
        add_filter( 'pre_wp_mail', '__return_false' );
    }

    protected function is_pro_active() {
        return class_exists( '\WeDevs\ERPPro\ERPPro' );
    }

    protected function table_exists( $table_name ) {
        global $wpdb;

        $result = $wpdb->get_var(
            $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . $table_name )
        );

        return ! empty( $result );
    }

    protected function get_financial_year_ids() {
        $ids = $this->get_ids( 'financial_year_ids' );

        if ( ! empty( $ids ) ) {
            return $ids;
        }

        global $wpdb;

        $results = $wpdb->get_results(
            "SELECT id FROM {$wpdb->prefix}erp_hr_financial_years ORDER BY start_date ASC"
        );

        return wp_list_pluck( $results, 'id' );
    }

    protected function get_financial_years() {
        global $wpdb;

        return $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}erp_hr_financial_years ORDER BY start_date ASC"
        );
    }

    protected function get_department_ids() {
        $ids = $this->get_ids( 'department_ids' );

        if ( ! empty( $ids ) ) {
            return $ids;
        }

        global $wpdb;

        $results = $wpdb->get_results(
            "SELECT id FROM {$wpdb->prefix}erp_hr_depts ORDER BY id ASC"
        );

        return wp_list_pluck( $results, 'id' );
    }

    protected function get_designation_ids() {
        $ids = $this->get_ids( 'designation_ids' );

        if ( ! empty( $ids ) ) {
            return $ids;
        }

        global $wpdb;

        $results = $wpdb->get_results(
            "SELECT id FROM {$wpdb->prefix}erp_hr_designations ORDER BY id ASC"
        );

        return wp_list_pluck( $results, 'id' );
    }

    protected function get_employee_user_ids() {
        $ids = $this->get_ids( 'employee_user_ids' );

        if ( ! empty( $ids ) ) {
            return $ids;
        }

        global $wpdb;

        $results = $wpdb->get_results(
            "SELECT user_id FROM {$wpdb->prefix}erp_hr_employees WHERE status = 'active' ORDER BY user_id ASC"
        );

        return wp_list_pluck( $results, 'user_id' );
    }

    protected function get_leave_type_ids() {
        $ids = $this->get_ids( 'leave_type_ids' );

        if ( ! empty( $ids ) ) {
            return $ids;
        }

        global $wpdb;

        $results = $wpdb->get_results(
            "SELECT id FROM {$wpdb->prefix}erp_hr_leaves ORDER BY id ASC"
        );

        return wp_list_pluck( $results, 'id' );
    }

    protected function get_leave_policy_ids() {
        $ids = $this->get_ids( 'leave_policy_ids' );

        if ( ! empty( $ids ) ) {
            return $ids;
        }

        global $wpdb;

        $results = $wpdb->get_results(
            "SELECT id FROM {$wpdb->prefix}erp_hr_leave_policies ORDER BY id ASC"
        );

        return wp_list_pluck( $results, 'id' );
    }
}
