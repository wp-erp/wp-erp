<?php
namespace WeDevs\ERP\HRM;

use \WeDevs\ERP\HRM\Models\Leave;

/**
 * List table class
 */
class Leave_Policy_Name_List_Table extends \WP_List_Table {

    function __construct() {
        parent::__construct( array(
            'singular' => 'name',
            'plural'   => 'names',
            'ajax'     => false
        ) );

        $this->table_css();
    }

    /**
     * Message to show if no requests found
     *
     * @return void
     */
    function no_items() {
        esc_html_e( 'No requests found.', 'erp' );
    }

    /**
     * Get the bulk actions
     *
     * @return array
     */
    function get_bulk_actions() {
        $actions = array(
            'delete'  => __( 'Delete', 'erp' ),
        );

        return $actions;
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
    function get_columns() {
        $columns = array(
            'cb'          => '<input type="checkbox" />',
            'name'        => esc_html__( 'Policy Name', 'erp' ),
            'description' => esc_html__( 'Description', 'erp' ),
            'created_at'  => esc_html__( 'Created At', 'erp' ),
        );

        return $columns;
    }

    /**
     * Render the employee name column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_name( $item ) {
        $actions = array();

        $params = array(
            'page'        => 'erp-hr',
            'section'     => 'leave',
            'sub-section' => 'policies',
            'type'        => 'policy-name',
            'id'          => $item->id
        );

        $params['action'] = 'edit';
        $edit_url = add_query_arg( $params, admin_url( 'admin.php' ) );

        $params['action'] = 'delete';
        $params['_wpnonce'] = wp_create_nonce( 'delete_policy_name' );
        $delete_url = add_query_arg( $params, admin_url( 'admin.php' ) );

        $actions['edit']   = sprintf( '<a href="%s" data-id="%d">%s</a>', $edit_url, $item->id, esc_html__( 'Edit', 'erp' ) );
        $actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d">%s</a>', $delete_url, $item->id, esc_html__( 'Delete', 'erp' ) );

        return sprintf( '<strong>%s</strong> %2$s', $item->name, $this->row_actions( $actions ) );
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
            '<input type="checkbox" name="ids[]" value="%s" />', $item->id
        );
    }

    public function process_bulk_action() {
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

        if ( $action !== 'delete' ) {
            return;
        }

        // security check!
        if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {
            $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
            $action = 'bulk-' . $this->_args['plural'];

            if ( ! wp_verify_nonce( $nonce, $action ) ) {
                wp_die( 'Nope! Security check failed!' );
            }
        }

        $ids = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['ids'] ) );

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
    function prepare_items() {
        $this->process_bulk_action();

        $columns               = $this->get_columns();
        $hidden                = array();
        $this->_column_headers = array( $columns, $hidden );

        $this->counts = Leave::count();
        $this->items  = Leave::all();
    }

}
