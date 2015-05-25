<?php
/**
 * List table class
 */
class Leave_Requests_List_Table extends WP_List_Table {

    private $counts = array();
    private $page_status;

    function __construct() {
        global $status, $page;

        parent::__construct( array(
            'singular' => 'leave',
            'plural'   => 'leaves',
            'ajax'     => false
        ) );

        $this->table_css();
    }

    /**
     * Table column width css
     *
     * @return void
     */
    function table_css() {
        echo '<style type="text/css">';
        echo '.wp-list-table .column-days { width: 8%; }';
        echo '.wp-list-table .column-balance { width: 8%; }';
        echo '.wp-list-table .column-status { width: 8%; }';
        echo '.wp-list-table .column-comments { width: 25%; }';
        echo '</style>';
    }

    /**
     * Message to show if no requests found
     *
     * @return void
     */
    function no_items() {
        _e( 'No requests found.', 'wp-erp' );
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
            case 'date':
                return erp_format_date( $item->created_on );

            case 'policy':
                return stripslashes( $item->policy_name );

            case 'status':
                return '<span class="status-' . $item->status . '">' . erp_hr_leave_request_get_statuses( $item->status ) . '</span>';

            default:
                return isset( $item->$column_name ) ? $item->$column_name : '';
        }
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    function get_sortable_columns() {
        $sortable_columns = array(
            'date' => array( 'created_on', true ),
            'days' => array( 'days', false ),
        );

        return $sortable_columns;
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'       => '<input type="checkbox" />',
            'name'     => __( 'Employee Name', 'wp-erp' ),
            'date'     => __( 'Date', 'wp-erp' ),
            'policy'   => __( 'Leave Policy', 'wp-erp' ),
            'days'     => __( 'Days', 'wp-erp' ),
            'balance'  => __( 'Balance', 'wp-erp' ),
            'status'   => __( 'Status', 'wp-erp' ),
            'reason'   => __( 'Reason', 'wp-erp' )
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
        $tpl         = '?page=erp-leave&leave_action=%s&id=%d';
        $nonce       = 'erp-hr-leave-req-nonce';
        $actions     = array();

        $delete_url  = wp_nonce_url( sprintf( $tpl, 'delete', $item->id ), $nonce );
        $reject_url  = wp_nonce_url( sprintf( $tpl, 'reject', $item->id ), $nonce );
        $approve_url = wp_nonce_url( sprintf( $tpl, 'approve', $item->id ), $nonce );
        $pending_url = wp_nonce_url( sprintf( $tpl, 'pending', $item->id ), $nonce );

        $actions['delete'] = sprintf( '<a href="%s">%s</a>', $delete_url, __( 'Delete', 'wp-erp' ) );

        if ( $item->status == '2' ) {

            $actions['reject']   = sprintf( '<a href="%s">%s</a>', $reject_url, __( 'Reject', 'wp-erp' ) );
            $actions['approved'] = sprintf( '<a href="%s">%s</a>', $approve_url, __( 'Approve', 'wp-erp' ) );

        } elseif ( $item->status == '1' ) {

            $actions['pending'] = sprintf( '<a href="%s">%s</a>', $pending_url, __( 'Mark Pending', 'wp-erp' ) );

        } elseif ( $item->status == '3') {
            $actions['approved'] = sprintf( '<a href="%s">%s</a>', $approve_url, __( 'Approve', 'wp-erp' ) );
        }

        return sprintf( '<a href="%3$s"><strong>%1$s</strong></a> %2$s', $item->display_name, $this->row_actions( $actions ), erp_hr_url_single_employee( $item->user_id ) );
    }

    /**
     * Set the bulk actions
     *
     * @return array
     */
    function get_bulk_actions() {
        $actions = array(
            'delete'  => __( 'Delete', 'wp-erp' ),
            'approve' => __( 'Approve', 'wp-erp' ),
            'cancel'  => __( 'Reject', 'wp-erp' )
        );
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
            '<input type="checkbox" name="request_id[]" value="%s" />', $item->id
        );
    }

    /**
     * Set the views
     *
     * @return array
     */
    public function get_views() {
        $status_links   = array();
        $base_link      = admin_url( 'admin.php?page=erp-leave' );

        foreach ($this->counts as $key => $value) {
            $class = ( $key == $this->page_status ) ? 'current' : 'status-' . $key;
            $status_links[ $key ] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => $key ), $base_link ), $class, $value['label'], $value['count'] );
        }

        return $status_links;
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
            'status' => $this->page_status
        );

        $this->counts = erp_hr_leave_get_requests_count();
        $this->items  = erp_hr_leave_get_requests( $args );

        $this->set_pagination_args( array(
            'total_items' => $this->counts[ $this->page_status ]['count'],
            'per_page'    => $per_page
        ) );
    }

}

?>

<div class="wrap erp-hr-leave-requests">

    <h2><?php _e( 'Leave Requests', 'wp-erp' ); ?> <a href="<?php echo add_query_arg( array( 'view' => 'new' ) ); ?>" class="add-new-h2"><?php _e( 'New Request', 'wp-erp' ); ?></a></h2>

    <div class="list-table-wrap">
        <div class="list-table-inner">

            <form method="post">
                <input type="hidden" name="page" value="erp-leave">
                <?php
                $requests_table = new Leave_Requests_List_Table();
                $requests_table->prepare_items();
                $requests_table->views();

                $requests_table->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->
</div><!-- .wrap -->