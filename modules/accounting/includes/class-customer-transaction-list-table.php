<?php
namespace WeDevs\ERP\Accounting;

if ( ! class_exists ( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class
 */
class Customer_Transaction_List_Table extends Sales_Transaction_List_Table {

    private $customer_id;

    function __construct( $customer_id = 0 ) {
        parent::__construct();

        $this->customer_id = $customer_id;
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'         => '<input type="checkbox" />',
            'issue_date' => __( 'Date', 'erp' ),
            'form_type'  => __( 'Type', 'erp' ),
            'ref'        => __( 'Ref', 'erp' ),
            'due_date'   => __( 'Due Date', 'erp' ),
            'due'        => __( 'Due', 'erp' ),
            'total'      => __( 'Total', 'erp' ),
            'status'     => __( 'Status', 'erp' ),
        );

        return $columns;
    }

    /**
     * Get all transactions
     *
     * @param  array  $args
     *
     * @return array
     */
    protected function get_transactions( $args ) {
        $args['user_id'] = $this->customer_id;

        return erp_ac_get_all_transaction( $args );
    }

    /**
     * Get transaction count
     *
     * @param  array  $args
     *
     * @return int
     */
    protected function get_transaction_count( $args ) {
        return erp_ac_get_transaction_count( $args['type'], $this->customer_id );
    }

}