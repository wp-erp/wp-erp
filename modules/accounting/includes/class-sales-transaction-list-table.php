<?php
namespace WeDevs\ERP\Accounting;

if ( ! class_exists ( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class
 */
class Sales_Transaction_List_Table extends Transaction_List_Table {
    private $page_status = '';

    function __construct() {
        global $page_status;
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
     * Render the issue date column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_issue_date( $item ) {
        
        if ( $$item->status == 'draft' ) {
            $actions['delete'] = sprintf( '<a href="#" class="erp-accountin-trns-row-del" data-id="%d" title="%s">%s</a>', $item->id, __( 'Delete', 'erp' ), __( 'Delete', 'erp' ) );
        }


        if ( $item->status == 'pending' || $item->status == 'draft' || $item->status == 'awaiting_payment' ) {
            $url   = admin_url( 'admin.php?page='.$this->slug.'&action=new&type=' . $item->form_type . '&transaction_id=' . $item->id );
            $actions['edit'] = sprintf( '<a href="%1s">%2s</a>', $url, __( 'Edit', 'erp' ) );
        }

        if ( ( $item->status == 'paid' || $item->status == 'closed' ) && $item->form_type == 'invoice' ) {
            //$actions['redo'] = sprintf( '<a class="erp-accounting-redo" data-type="%1$s" data-id="%2$s" href="#">%3$s</a>', $item->type, $item->id, __( 'Redo', 'erp' ) );
        }

        if ( $item->status == 'awaiting_payment' ) {
            $actions['void'] = sprintf( '<a class="erp-accounting-void" data-id="%1$s" href="#">%2$s</a>', $item->id, __( 'Void', 'erp' ) );
        }


        if ( isset( $actions ) && count( $actions ) ) {
            return sprintf( '<a href="%1$s">%2$s</a> %3$s', admin_url( 'admin.php?page=' . $this->slug . '&action=view&id=' . $item->id ), erp_format_date( $item->issue_date ), $this->row_actions( $actions ) );
        } else {
            return sprintf( '<a href="%1$s">%2$s</a>', admin_url( 'admin.php?page=' . $this->slug . '&action=view&id=' . $item->id ), erp_format_date( $item->issue_date ) );
        }
    }

    /**
     * Count sales status
     *
     * @since  1.1.6
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
     * @since  1.1.6
     * 
     * @return array
     */
    public function get_section() {
        $counts = $this->get_counts();
       
        $section = [ 
            'all'   => [
                'label' => __( 'All', 'erp' ),
                'count' => array_sum( $counts),
                'url'   => erp_ac_get_section_sales_url()
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
        $section      = isset( $_REQUEST['section'] ) ? $_REQUEST['section'] : 'all';

        foreach ( $counts as $key => $value ) {
            $key   = str_replace( '_', '-', $key );
            $class = ( $key == $section ) ? 'current' : 'status-' . $key;
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
