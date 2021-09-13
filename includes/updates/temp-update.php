<?php

function erp_run_temp_updater() {
    $temp_updated = get_option( 'erp_temp_data_updated', false );

    if ( 'yes' !== $temp_updated ) {
        erp_remove_duplicate_entry();
        erp_give_missing_employee_role();

        update_option( 'erp_temp_data_updated', 'yes' );
    }
}

add_action( 'erp_loaded', 'erp_run_temp_updater' );

function erp_remove_duplicate_entry() {
    global $wpdb;

    $user_id = 221;

    $emp_id  = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT id
            FROM {$wpdb->prefix}erp_hr_employees
            WHERE user_id = %d",
            $user_id
        )
    );

    if ( count( $emp_id ) > 1 ) {
        $wpdb->query(
            $wpdb->prepare(
                "DELETE
                FROM {$wpdb->prefix}erp_hr_employees
                WHERE id = %d",
                $emp_id[0]
            )
        );
    }
}

function erp_give_missing_employee_role() {
    global $wpdb;

    $user_id = 221;
    $caps    = maybe_serialize( array( 'employee' => true ) );

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->prefix}usermeta
            SET meta_value = %s
            WHERE meta_key = %s
            AND user_id = %s",
            [ $caps, 'wp_capabilities', $user_id ]
        )
    );
}