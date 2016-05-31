<?php

namespace WeDevs\ERP\Accounting;

if ( ! class_exists ( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class
 */
class Vendor_List_Table extends Customer_List_Table {

    protected $expense_slug;

    function __construct() {

        parent::__construct( array(
            'singular' => 'vendor',
            'plural'   => 'vendors',
            'ajax'     => false
        ) );

        $this->slug = 'erp-accounting-vendors';
        $this->type = 'vendor';
        $this->expense_slug = 'erp-accounting-expense';
    }

    /**
     * Message to show if no designation found
     *
     * @return void
     */
    function no_items() {
        _e( 'No vendor found!', 'wp-erp-ac' );
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'       => '<input type="checkbox" />',
            'customer' => __( 'Vendor', 'wp-erp-ac' ),
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
        $delete_text       = ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] == 'trash' ) ? __( 'Permanent Delete', 'accounting' ) : __( 'Delete', 'accounting' );

        $actions            = array();
        if ( erp_ac_current_user_can_edit_vendor( $item->created_by ) ) {
            $actions['edit']    = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', admin_url( 'admin.php?page=' . $this->slug . '&action=edit&id=' . $item->id ), $item->id, __( 'Edit this item', 'wp-erp-ac' ), __( 'Edit', 'wp-erp-ac' ) );
        }

        if ( erp_ac_create_expenses_voucher() || erp_ac_publish_expenses_voucher() ) {

            $actions['invoice'] = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', admin_url( 'admin.php?page=' . $this->expense_slug . '&action=new&type=payment_voucher&vendor_id=' . $item->id ), $item->id, __( 'Create Voucher', 'wp-erp-ac' ), __( 'Payment Voucher', 'wp-erp-ac' ) );
        }

        if ( erp_ac_create_expenses_credit() || erp_ac_publish_expenses_credit() ) {
            $actions['expense'] = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', admin_url( 'admin.php?page=' . $this->expense_slug . '&action=new&type=vendor_credit&vendor_id=' . $item->id ), $item->id, __( 'Create Credit', 'wp-erp-ac' ), __( 'Vendor Credit', 'wp-erp-ac' ) );
        }
        
        if ( erp_ac_current_user_can_delete_vendor( $item->created_by ) ) {
            $actions['delete'] = sprintf( '<a href="%s" class="erp-ac-submitdelete" data-id="%d" data-hard=%d title="%s" data-type="%s">%s</a>', '#', $item->id, $data_hard, __( 'Delete this item', 'accounting' ), $this->type, $delete_text );
        }

        if ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] == 'trash' ) {
            $actions['restore'] = sprintf( '<a href="#" class="erp-ac-restoreCustomer" data-id="%d" title="%s" data-type="%s">%s</a>', $item->id, __( 'Restore this item', 'accounting' ), $this->type, __( 'Restore', 'accounting' ) );
        }

        if ( ! erp_ac_current_user_can_view_single_vendor() ) {
           return get_avatar( $item->email, 32 ) . sprintf( '<strong>%1$s</strong> %2$s', $item->first_name . ' ' . $item->last_name, $this->row_actions( $actions ) );
        }

        return get_avatar( $item->email, 32 ) . sprintf( '<a href="%1$s"><strong>%2$s</strong></a> %3$s', admin_url( 'admin.php?page=' . $this->slug . '&action=view&id=' . $item->id ), $item->first_name . ' ' . $item->last_name, $this->row_actions( $actions ) );
    }

    /**
     * Set the views
     *
     * @return array
     */
    public function get_views() {

        $status_links   = array();
        $base_link      = admin_url( 'admin.php?page=erp-accounting-vendors' );

        $status_links['all']   = sprintf( '<a href="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => 'all' ), $base_link ), __( 'All', 'accounting' ), $this->customer_get_status_count( 'vendor' ) );
        $status_links['trash'] = sprintf( '<a href="%s" >%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => 'trash' ), $base_link ), __( 'Trash', 'accounting' ), $this->count_trashed_customers() );

        return $status_links;
    }

}