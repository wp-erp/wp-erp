<?php

namespace WeDevs\ERP\Accounting;

if ( ! class_exists ( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class
 */
class Customer_List_Table extends \WP_List_Table {

    private $all_customers = array();
    private $page_status = '';

    protected $slug = null;
    protected $type = null;
    protected $user_balance = null;

    function __construct() {
        $this->slug = 'erp-accounting-customers';
        $this->type = 'customer';

        parent::__construct( array(
            'singular' => 'customer',
            'plural'   => 'customers',
            'ajax'     => false
        ) );
    }

    function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', 'vendor-list-table', $this->_args['plural'] );
    }

    /**
     * Message to show if no designation found
     *
     * @return void
     */
    function no_items() {
        _e( 'No customer found!', 'wp-erp-ac' );
    }

    /**
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'customer':
                return $item->first_name . ' ' . $item->last_name;

            case 'company':
                return $item->company;

            case 'email':
                return $item->email;

            case 'phone':
                return $item->phone;

            case 'balance':
                return '$0';

            default:
                return isset( $item->$column_name ) ? $item->$column_name : '';
        }
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'       => '<input type="checkbox" />',
            'customer' => __( 'Customer', 'wp-erp-ac' ),
            'company'  => __( 'Company', 'wp-erp-ac' ),
            'email'    => __( 'Email', 'wp-erp-ac' ),
            'phone'    => __( 'Phone', 'wp-erp-ac' ),
            'balance'  => __( 'Balance', 'wp-erp-ac' ),
        );

        return $columns;
    }

    /**
     * Render the designation name column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_customer( $item ) {

        $data_hard         = ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] == 'trash' ) ? 1 : 0;
        $delete_text       = ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] == 'trash' ) ? __( 'Permanent Delete', 'erp' ) : __( 'Delete', 'erp' );
        $delete_text       = ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] == 'trash' ) ? __( 'Permanent Delete', 'erp' ) : __( 'Delete', 'erp' );
        $actions           = array();
        $created_by        = isset( $item->created_by ) ? intval( $item->created_by ) : 0;

        if ( erp_ac_current_user_can_edit_customer( $created_by ) ) {
            $actions['edit']    = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', admin_url( 'admin.php?page=' . $this->slug . '&action=edit&id=' . $item->id ), $item->id, __( 'Edit this item', 'wp-erp-ac' ), __( 'Edit', 'wp-erp-ac' ) );
        }

        $actions['invoice'] = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', admin_url( 'admin.php?page=erp-accounting-sales&action=new&type=invoice&customer=true&id=' . $item->id ), $item->id, __( 'Create Invoice', 'wp-erp-ac' ), __( 'Create Invoice', 'wp-erp-ac' ) );

        if ( erp_ac_current_user_can_delete_customer( $created_by ) ) {
            $actions['delete'] = sprintf( '<a href="%s" class="erp-ac-submitdelete" data-id="%d" data-hard=%d title="%s" data-type="%s">%s</a>', '#', $item->id, $data_hard, __( 'Delete this item', 'erp' ), $this->type, $delete_text );
        }

        if ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] == 'trash' ) {
            $actions['restore'] = sprintf( '<a href="#" class="erp-ac-restoreCustomer" data-id="%d" title="%s" data-type="%s">%s</a>', $item->id, __( 'Restore this item', 'erp' ), $this->type, __( 'Restore', 'erp' ) );
        }

        if ( erp_ac_current_user_can_view_single_customer() ) {
           return get_avatar( $item->email, 32 ) . sprintf( '<a href="%1$s"><strong>%2$s</strong></a> %3$s', admin_url( 'admin.php?page=' . $this->slug . '&action=view&id=' . $item->id ), $item->first_name . ' ' . $item->last_name, $this->row_actions( $actions ) );
        }

        return get_avatar( $item->email, 32 ) . sprintf( '<strong>%1$s</strong> %2$s', $item->first_name . ' ' . $item->last_name, $this->row_actions( $actions ) );
    }

    function column_balance( $item ) {
        $total_balance = 0;

        if ( isset( $this->user_balance[$item->id] ) && is_array( $this->user_balance[$item->id] ) ) {

            $user_trans = $this->user_balance[$item->id];
            foreach ( $user_trans as $key => $trans ) {
                $total_balance = $total_balance + ( $trans->trans_total - $trans->due );
            }
        }

        return erp_ac_get_price( $total_balance );
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    function get_sortable_columns() {
        $sortable_columns = array(
            'customer' => array( 'first_name', true ),
        );

        return $sortable_columns;
    }

    /**
     * Set the bulk actions
     *
     * @return array
     */
    function get_bulk_actions() {
        $actions = array(
            'trash'  => __( 'Move to Trash', 'wp-erp-ac' ),
        );

        if ( isset( $_GET['status'] ) && $_GET['status'] == 'trash' ) {
            $actions = array(
                'restore'  => __( 'Restore', 'wp-erp-ac' ),
                'delete'  => __( 'Permanent Delete', 'wp-erp-ac' ),
            );
        }
        return $actions;
    }

    /**
     * Render the checkbox column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="customer_id[]" value="%d" />', $item->id
        );
    }

    /**
     * Set the views
     *
     * @return array
     */
    public function get_views() {

        $status_links   = array();
        $base_link      = admin_url( 'admin.php?page=erp-accounting-customers' );

        $status_links['all'] = sprintf( '<a href="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => 'all' ), $base_link ), __( 'All', 'erp' ), $this->customer_get_status_count('customer') );
        $status_links['trash'] = sprintf( '<a href="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => 'trash' ), $base_link ), __( 'Trash', 'erp' ), $this->count_trashed_customers() );

        return $status_links;
    }

    /**
     * Count trash customer
     *
     * @since 1.0
     *
     * @return integer [no of trash customer]
     */
    function count_trashed_customers() {
        return \WeDevs\ERP\Framework\Models\People::trashed( $this->type )->count();
    }

    /**
     * Get customer life statges status count
     *
     * @since 1.0
     *
     * @return array
     */
    function customer_get_status_count( $type = null ) {

        $cache_key = 'erp-ac-customer-status-counts';
        $results = wp_cache_get( $cache_key, 'erp' );

        if ( false === $results ) {

            $results = \WeDevs\ERP\Framework\Models\People::type($type)->count();

            wp_cache_set( $cache_key, $results, 'erp' );
        }

        return $results ? $results : 0;
    }


    /**
     * Prepare the class items
     *
     * @return void
     */
    function prepare_items() {

        $columns               = $this->get_columns();
        $hidden                = array( );
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $per_page              = 20;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page -1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '2';

        // only ncessary because we have sample data
        $args = array(
            'offset' => $offset,
            'number' => $per_page,
            'type'   => $this->type,
            's'      => isset( $_GET['s'] ) ? $_GET['s'] : false,
            'trashed' => isset( $_GET['status'] ) && $_GET['status'] == 'trash' ? true : false
        );

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order']   = $_REQUEST['order'] ;
        }

        if ( $args['type'] == 'customer' ) {
            if ( ! erp_ac_view_other_customers() ) {
                $args['created_by'] = get_current_user_id();
            }
        }

        $this->items  = erp_get_peoples( $args );

        $users_id = wp_list_pluck( $this->items, 'id' );
        $trans_arg = [
            'user_id' => [ 'in' => $users_id ],
            'type'    => $this->slug == 'erp-accounting-vendors' ? 'expense' : 'sales',
        ];

        $transactions = erp_ac_get_all_transaction( $trans_arg );

        foreach ( $transactions as $key => $transaction ) {
            $users[$transaction->user_id][] = $transaction;
        }
        $this->user_balance = isset( $users ) ? $users : '0.00';

        $this->set_pagination_args( array(
            'total_items' => erp_get_peoples_count( $this->type ),
            'per_page'    => $per_page
        ) );
    }
}
