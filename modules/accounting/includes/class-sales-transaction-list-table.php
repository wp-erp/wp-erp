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
            'issue_date' => __( 'Date', 'erp' ),
            'form_type'  => __( 'Type', 'erp' ),
            'ref'        => __( 'Ref', 'erp' ),
            'user_id'    => __( 'Customer', 'erp' ),
            'due_date'   => __( 'Due Date', 'erp' ),
            'due'        => __( 'Due', 'erp' ),
            'total'      => __( 'Total', 'erp' ),
            'status'     => __( 'Status', 'erp' ),
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