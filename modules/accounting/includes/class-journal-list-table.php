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

        $this->type = 'journal';
        $this->slug = 'erp-accounting-journal';
        
        parent::__construct();

    }


    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'         => '<input type="checkbox" />',
            'ref'        => __( 'Ref', 'erp' ),
            'issue_date' => __( 'Date', 'erp' ),
            'summary'    => __( 'Summary', 'erp' ),
            'total'      => __( 'Total', 'erp' ),
        );

        return $columns;
    }
}