<?php

namespace WeDevs\ERP\HRM;

use WeDevs\ERP\HRM\Models\Leave;
use WP_List_Table;

/**
 * List table class
 */
class LeavePolicyNameListTable extends WP_List_Table {

    protected $counts;

    public function __construct() {
        parent::__construct( [
            'singular' => 'type',
            'plural'   => 'types',
            'ajax'     => false,
        ] );

        $this->table_css();
    }

    /**
     * Message to show if no requests found
     *
     * @return void
     */
    public function no_items() {
        esc_html_e( 'No leave types found.', 'erp' );
    }

    /**
     * Get the bulk actions
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = [
            'delete'  => __( 'Delete', 'erp' ),
        ];

        return $actions;
    }

    /**
     * Default column values if no callback found
     *
     * @param object $item
     * @param string $column_name
     *
     * @return string
     */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'name':
                return stripslashes( $item->name );

            case 'description':
                return $item->description;

            case 'created_at':
                return erp_format_date( $item->created_at );

            default:
                return isset( $item->$column_name ) ? $item->$column_name : '';
        }
    }

    /**
     * Get the column names
     *
     * @return array
     */
    public function get_columns() {
        $columns = [
            'cb'          => '<input type="checkbox" />',
            'name'        => esc_html__( 'Leave Type', 'erp' ),
            'description' => esc_html__( 'Description', 'erp' ),
            'created_at'  => esc_html__( 'Created At', 'erp' ),
        ];

        return $columns;
    }

    /**
     * Render the employee name column
     *
     * @param object $item
     *
     * @return string
     */
    public function column_name( $item ) {
        $actions = [];

        $params = [
            'page'        => 'erp-hr',
            'section'     => 'leave',
            'sub-section' => 'policies',
            'type'        => 'policy-name',
            'id'          => $item->id,
        ];

        $params['action']   = 'edit';
        $edit_url           = add_query_arg( $params, admin_url( 'admin.php' ) );

        $params['action']   = 'delete';
        $params['_wpnonce'] = wp_create_nonce( 'delete_policy_name' );
        $delete_url         = add_query_arg( $params, admin_url( 'admin.php' ) );

        $actions['edit']    = sprintf( '<a href="%s" class="erp-hr-leave-type-edit" data-id="%d">%s</a>', $edit_url, $item->id, esc_html__( 'Edit', 'erp' ) );
        $actions['delete']  = sprintf( '<a href="%s" class="submitdelete erp-hr-leave-type-delete" data-id="%d">%s</a>', $delete_url, $item->id, esc_html__( 'Delete', 'erp' ) );

        return sprintf( '<strong>%s</strong> %2$s', $item->name, $this->row_actions( $actions ) );
    }

    /**
     * Render the checkbox column
     *
     * @param object $item
     *
     * @return string
     */
    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="ids[]" value="%s" />', $item->id
        );
    }

    public function process_bulk_action() {
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

        if ( $action !== 'delete' ) {
            return;
        }

        if ( empty( $_POST['_wpnonce'] ) ) {
            wp_die( esc_html__( 'Error: Nonce verification failed', 'erp' ) );
        }

        $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
        $action = 'bulk-' . $this->_args['plural'];

        if ( ! wp_verify_nonce( $nonce, $action ) ) {
            wp_die( esc_html__( 'Error: Nonce verification failed', 'erp' ) );
        }

        // Check permission
        if ( ! current_user_can( 'erp_leave_manage' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        $ids = array_map( 'intval', wp_unslash( $_REQUEST['ids'] ) );

        foreach ( $ids as $id ) {
            erp_hr_remove_leave_policy_name( $id );
        }

        return;
    }

    /**
     * Prepare the class items
     *
     * @return void
     */
    public function prepare_items() {
        $this->process_bulk_action();

        $columns               = $this->get_columns();
        $hidden                = [];
        $this->_column_headers = [ $columns, $hidden ];

        $this->counts = erp_hr_get_leave_policy_names( ['count' => true ] );
        $this->items  = erp_hr_get_leave_policy_names();
    }
}
