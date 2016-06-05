<?php
namespace WeDevs\ERP\Accounting;

if ( ! class_exists ( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class
 */
class Journal_List_Table extends Transaction_List_Table {

    function __construct() {
        parent::__construct();

        $this->type = 'journal';
        $this->slug = 'erp-accounting-journal';
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'         => '<input type="checkbox" />',
            'ref'        => __( 'Ref', 'accounting' ),
            'issue_date' => __( 'Date', 'accounting' ),
            'summary'    => __( 'Summary', 'accounting' ),
            'total'      => __( 'Total', 'accounting' ),
        );

        return $columns;
    }
}