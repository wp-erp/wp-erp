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
           // 'cb'         => '<input type="checkbox" />',
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
     * Count sales status
     *
     * @since  1.1.5
     *
     * @return  array
     */
    function get_counts() {
        $cache_key = 'erp-ac-sales-trnasction-counts-' . get_current_user_id();
        $results = wp_cache_get( $cache_key, 'erp' );
        $type = isset( $_REQUEST['form_type'] ) ? $_REQUEST['form_type'] : false;

        if ( false === $results ) {
            $trans = new \WeDevs\ERP\Accounting\Model\Transaction();
            $db = new \WeDevs\ORM\Eloquent\Database();
            
            if ( $type ) {
                $results = $trans->select( array( 'status', $db->raw('COUNT(id) as num') ) )
                            ->where( 'type', '=', 'sales' )
                            ->where( 'form_type', '=', $type )
                            ->groupBy('status')
                            ->get()->toArray();
            } else {
                $results = $trans->select( array( 'status', $db->raw('COUNT(id) as num') ) )
                            ->where( 'type', '=', 'sales' )
                            ->groupBy('status')
                            ->get()->toArray();    
            }

            wp_cache_set( $cache_key, $results, 'erp' );
        }
        
        $count = [];
        
        foreach ( $results as $key => $value ) {
            $count[$value['status']] = $value['num'];
        }
        
        return $count;
    }

    /**
     * Get section for sales table list
     *
     * @since  1.1.5
     * 
     * @return array
     */
    public function get_section() {
        $counts = $this->get_counts();
       
        $section = [ 
            'all'   => [
                'label' => __( 'All', 'erp' ),
                'count' => array_sum( $counts),
                'url'   => erp_ac_get_section_sales_url( 'all' )
            ],

            'draft' => [
                'label' => __( 'Draft', 'erp' ),
                'count' => isset( $counts['draft'] ) ? intval( $counts['draft'] ) : 0,
                'url'   => erp_ac_get_section_sales_url( 'draft' )
            ],

            'awaiting_payment' => [
                'label' => __( 'Awaiting Payment', 'erp' ),
                'count' => isset( $counts['awaiting_payment'] ) ? intval( $counts['awaiting_payment'] ) : 0,
                'url'   => erp_ac_get_section_sales_url( 'awaiting_payment' )
            ],

            'closed' => [
                'label' => __( 'Paid', 'erp' ),
                'count' => isset( $counts['closed'] ) ? intval( $counts['closed'] ) : 0,
                'url'   => erp_ac_get_section_sales_url( 'closed' )
            ],

            'void' => [
                'label' => __( 'Void', 'erp' ),
                'count' => isset( $counts['void'] ) ? intval( $counts['void'] ) : 0,
                'url'   => erp_ac_get_section_sales_url( 'void' )
            ]
        ];

        return $section;
    }

    /**
     * Set the views
     *
     * @return array
     */
    public function get_views() {
        $counts       = $this->get_section();
        $status_links = array();
        $base_link    = admin_url( 'admin.php?page=erp-hr-employee' );
        
        foreach ( $counts as $key => $value ) {
            $class = ( $key == $this->page_status ) ? 'current' : 'status-' . $key;
            $status_links[ $key ] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', $value['url'], $class, $value['label'], $value['count'] );
        }

        return $status_links;
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

