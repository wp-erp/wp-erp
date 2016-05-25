<?php
namespace WeDevs\ERP\Accounting;

if ( ! class_exists ( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class
 */
class Sales_Transaction_List_Table extends Transaction_List_Table {

    function __construct() {
        parent::__construct();

        $this->type = 'sales';
        $this->slug = 'erp-accounting-sales';
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'         => '<input type="checkbox" />',
            'issue_date' => __( 'Date', 'accounting' ),
            'form_type'  => __( 'Type', 'accounting' ),
            'ref'        => __( 'Ref', 'accounting' ),
            'user_id'    => __( 'Customer', 'accounting' ),
            'due_date'   => __( 'Due Date', 'accounting' ),
            'due'        => __( 'Due', 'accounting' ),
            'total'      => __( 'Total', 'accounting' ),
            'status'     => __( 'Status', 'accounting' ),
        );

        return $columns;
    }

    /**
     * Get form types
     *
     * @return array
     */
    public function get_form_types() {
        return erp_ac_get_sales_form_types();
    }


}