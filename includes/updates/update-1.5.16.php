<?php
namespace WeDevs\ERP\Updates;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function move_ledger_1403_to_expense() {
    global $wpdb;

    // get chart_id for expenses
    $expense_chart_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}erp_acct_chart_of_accounts WHERE slug = %s",
            array( 'expense' )
        )
    );

    // update chart_id for 1403
    if ( null !== $expense_chart_id && $expense_chart_id > 0 ) {
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->prefix}erp_acct_ledgers SET chart_id = %d WHERE code = %d",
                array( $expense_chart_id, 1403 )
            )
        );
    }
}
move_ledger_1403_to_expense();
