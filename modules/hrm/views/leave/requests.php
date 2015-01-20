<?php
/**
 * List table class
 */
class Leave_Requests_List_Table extends WP_List_Table {

    function __construct() {
        global $status, $page;

        parent::__construct( array(
            'singular' => 'leave',
            'plural'   => 'erp-hr-leave-requests',
            'ajax'     => false        //does this table support ajax?
        ) );

        $this->admin_header();
    }

    /**
     * Table column width css
     *
     * @return void
     */
    function admin_header() {
        $page = ( isset( $_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
        if ( 'erp-leave' != $page )
            return;

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
                return erp_hr_leave_request_get_statuses( $item->status );

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
            'comments' => __( 'Comments', 'wp-erp' )
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
        $actions = array(
            'edit'   => sprintf( '<a href="?page=%s&action=%s&book=%s">Edit</a>', $_REQUEST['page'], 'edit', $item->id ),
            'delete' => sprintf( '<a href="?page=%s&action=%s&book=%s">Delete</a>', $_REQUEST['page'], 'delete', $item->id ),
        );

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
     * Fetch the leave requests
     *
     * @param  array   $args
     *
     * @return array
     */
    public function fetch_requests( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'number'  => 20,
            'offset'  => 0,
            'user_id' => 0,
            'status'  => '',
            'orderby' => 'created_on',
            'order'   => 'DESC',
        );

        $args  = wp_parse_args( $args, $defaults );
        $where = '';

        if ( 'all' != $args['status'] && $args['status'] != '' ) {

            if ( empty( $where ) ) {
                $where .= " WHERE";
            } else {
                $where .= " AND";
            }

            if ( is_array( $args['status'] ) ) {
                $where .= " `status` IN('" . implode( "','", array_map( 'intval', $args['status'] ) ) . "') ";
            } else {
                $where .= " `status` = '" . intval( $args['status'] ) . "' ";
            }
        }

        $cache_key = md5( 'erp_hr_leave_requests' . serialize( $args ) );
        $requests  = wp_cache_get( $cache_key, 'wp-erp' );

        $sql = "SELECT req.id, req.user_id, u.display_name, req.policy_id, pol.name as policy_name, req.status, req.comments, req.created_on, ( SELECT count(id) FROM wp_erp_hr_leaves WHERE request_id = req.id) as days
            FROM {$wpdb->prefix}erp_hr_leave_requests AS req
            LEFT JOIN {$wpdb->prefix}erp_hr_leave_policies AS pol ON pol.id = req.policy_id
            LEFT JOIN $wpdb->users AS u ON req.user_id = u.ID
            $where
            ORDER BY {$args['orderby']} {$args['order']}
            LIMIT %d,%d;";
        // echo $sql;

        if ( $requests === false ) {
            $requests = $wpdb->get_results( $wpdb->prepare( $sql, absint( $args['offset'] ), absint( $args['number'] ) ) );
            wp_cache_set( $cache_key, $requests, 'wp-erp', HOUR_IN_SECONDS );
        }

        return $requests;
    }

    /**
     * Get leave requests cound
     *
     * @return array
     */
    public function get_counts() {
        global $wpdb;

        $statuses = erp_hr_leave_request_get_statuses();
        $counts   = array();

        foreach ($statuses as $status => $label) {
            $counts[ $status ] = array( 'count' => 0, 'label' => $label );
        }

        $cache_key = 'erp-hr-leave-request-counts';
        $results = wp_cache_get( $cache_key, 'wp-erp' );

        if ( false === $results ) {
            $sql     = "SELECT status, COUNT(id) as num FROM {$wpdb->prefix}erp_hr_leave_requests GROUP BY status;";
            $results = $wpdb->get_results( $sql );

            wp_cache_set( $cache_key, $results, 'wp-erp' );
        }

        foreach ($results as $row) {
            if ( array_key_exists( $row->status, $counts ) ) {
                $counts[ $row->status ]['count'] = (int) $row->num;
            }

            $counts['all']['count'] += (int) $row->num;
        }

        return $counts;
    }

    /**
     * Set the views
     *
     * @return array
     */
    public function get_views() {
        $counts       = $this->get_counts();
        $status_links = array();
        $base_link    = admin_url( 'admin.php?page=erp-leave' );

        foreach ($counts as $key => $value) {
            if ( $value['count'] ) {
                $status_links[ $key ] = sprintf( '<a href="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => $key ), $base_link ), $value['label'], $value['count'] );
            }
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
        $count                 = $this->get_counts();
        $status                = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all';

        // only ncessary because we have sample data
        $args = array(
            'offset' => $offset,
            'number' => $per_page,
            'status' => $status
        );

        $this->items = $this->fetch_requests( $args );

        $this->set_pagination_args( array(
            'total_items' => $count[ $status ]['count'],
            'per_page'    => $per_page
        ) );
    }

}

?>

<div class="wrap erp-hr-leave-requests">

    <h2><?php _e( 'Leave Requests', 'wp-erp' ); ?></h2>

    <form method="post">
        <input type="hidden" name="page" value="ttest_list_table">
        <?php
        $myListTable = new Leave_Requests_List_Table();
        $myListTable->prepare_items();
        echo $myListTable->views();

        $myListTable->display();
        ?>
    </form>

</div>