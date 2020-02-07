<?php
namespace WeDevs\ERP\HRM;

/**
 * List table class
 */
class Leave_Requests_List_Table extends \WP_List_Table {

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
     * Render extra filtering option in
     * top of the table
     *
     * @since 1.3.2
     *
     * @param string $which
     *
     * @return void
     */
    function extra_tablenav( $which ) {
        if ( $which != 'top' ) {
            return;
        }

        $current_year  = date( 'Y' );
        $selected_year = ( isset( $_GET['filter_year'] ) ) ? sanitize_text_field( wp_unslash( $_GET['filter_year'] ) ) : $current_year;
        ?>
        <div class="alignleft actions">

            <label class="screen-reader-text" for="filter_year"><?php esc_html_e( 'Filter by year', 'erp' ) ?></label>
            <input type="hidden" name="status" value="<?php echo esc_html( $this->page_status ); ?>">
            <select name="filter_year" id="filter_year">
                <option value="">select year</option>
                <?php
                for ( $i = 0; $i <= 5; $i ++ ) {
                    $year = $current_year - $i;
                    echo sprintf( "<option value='%s'%s>%s</option>\n", esc_html( $year ), selected( $selected_year, $year, false ), esc_html( $year ) );
                }
                ?>
            </select>

            <?php
            submit_button( __( 'Filter' ), 'button', 'filter_by_year', false );
        echo '</div>';
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
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $item, $column_name ) {
        switch ( $column_name ) {

            case 'policy':
                return stripslashes( $item->policy_name );

            case 'from_date':
                return erp_format_date( $item->start_date );
            case 'to_date':
                return erp_format_date( $item->end_date );

            case 'status':
                return '<span class="status-' . $item->status . '">' . erp_hr_leave_request_get_statuses( $item->status ) . '</span>';

            case 'available':
                $balance = erp_hr_leave_get_balance( $item->user_id );
                $policy  = erp_hr_leave_get_policy( $item->policy_id );

                if ( isset( $balance[ $item->policy_id ] ) ) {
                    $scheduled = $balance[ $item->policy_id ]['scheduled'];
                    $available = $balance[ $item->policy_id ]['entitlement'] - $balance[ $item->policy_id ]['total'];
                } else {
                    $scheduled = 0;
                    $available = 0;
                }

                if ( $available < 0 ) {
                    return sprintf( '<span class="red">%d %s</span>', number_format_i18n( $available ), __( 'days', 'erp' ) );
                } elseif ( $available > 0 ) {
                    return sprintf( '<span class="green">%d %s</span>', number_format_i18n( $available ), __( 'days', 'erp' ) );
                } else if(  $available === 0 ){
                    return sprintf( '<span class="gray">%d %s</span>', 0, __( 'days', 'erp' ) );
                } else {
                    return sprintf( '<span class="green">%d %s</span>', number_format_i18n( $policy->value ), __( 'days', 'erp' ) );
                }

            case 'reason':
                return stripslashes( $item->reason );

            case 'comment' :
                return stripslashes( $item->comments );

            case 'leave_attachment' :

                $attachment       = "";
                $leave_attachment = get_user_meta( $item->user_id, 'leave_document_' . $item->id ) ;
                foreach ( $leave_attachment as $la ) {
                    $file_link = wp_get_attachment_url( $la );
                    $file_name = basename( $file_link );
                    $attachment .= "<a target='_blank' href='{$file_link}'>{$file_name}</a><br>";
                }
                return $attachment;

            default:
                return isset( $item->$column_name ) ? $item->$column_name : '';
        }
    }

    /**
     * Filter action
     * @return string
     */
    public function current_action() {

        if ( isset( $_REQUEST['filter_by_year'] ) ) {
            return 'filter_by_year';
        }

        if ( ! empty( $_REQUEST['s'] ) ) {
            return 'search_request';
        }

        return parent::current_action();
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    function get_sortable_columns() {
        $sortable_columns = array(
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
            'cb'                => '<input type="checkbox" />',
            'name'              => __( 'Employee Name', 'erp' ),
            'policy'            => __( 'Leave Policy', 'erp' ),
            'from_date'         => __( 'From Date', 'erp' ),
            'to_date'           => __( 'To Date', 'erp' ),
            'days'              => __( 'Days', 'erp' ),
            'available'         => __( 'Available', 'erp' ),
            'status'            => __( 'Status', 'erp' ),
            'reason'            => __( 'Leave Reason', 'erp' ),
            'leave_attachment'  => __( 'Attachment', 'erp' ),

        );
        if ( isset( $_GET['status'] ) && $_GET['status'] == 3 ) {
            $columns['comment'] =  __( 'Reject Reason', 'erp' );
        }
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
        $tpl         = '?page=erp-hr&section=leave&leave_action=%s&id=%d';
        $nonce       = 'erp-hr-leave-req-nonce';
        $actions     = array();

        $delete_url  = wp_nonce_url( sprintf( $tpl, 'delete', $item->id ), $nonce );
        $reject_url  = wp_nonce_url( sprintf( $tpl, 'reject', $item->id ), $nonce );
        $approve_url = wp_nonce_url( sprintf( $tpl, 'approve', $item->id ), $nonce );
        $pending_url = wp_nonce_url( sprintf( $tpl, 'pending', $item->id ), $nonce );

        if ( erp_get_option( 'erp_debug_mode', 'erp_settings_general', 0 ) ) {
            $actions['delete'] = sprintf( '<a href="%s">%s</a>', $delete_url, __( 'Delete', 'erp' ) );
        }

        if ( $item->status == '2' ) {

            $actions['reject']   = sprintf( '<a class="erp-hr-leave-reject-btn" data-id="%s" href="%s">%s</a>', $item->id, $reject_url, __( 'Reject', 'erp' ) );
            $actions['approved'] = sprintf( '<a href="%s">%s</a>', $approve_url, __( 'Approve', 'erp' ) );

        } elseif ( $item->status == '1' ) {

            $actions['pending'] = sprintf( '<a href="%s">%s</a>', $pending_url, __( 'Mark Pending', 'erp' ) );

        } elseif ( $item->status == '3') {
            $actions['approved'] = sprintf( '<a href="%s">%s</a>', $approve_url, __( 'Approve', 'erp' ) );
            $actions['pending'] = sprintf( '<a href="%s">%s</a>', $pending_url, __( 'Mark Pending', 'erp' ) );
        }

        return sprintf( '<a href="%3$s"><strong>%1$s</strong></a> %2$s', $item->display_name, $this->row_actions( $actions ), erp_hr_url_single_employee( $item->user_id ) );
    }

    /**
     * Set the bulk actions
     *
     * @return array
     */
    function get_bulk_actions() {
        if ( erp_get_option( 'erp_debug_mode', 'erp_settings_general', 0 ) ) {
            $actions['delete'] = __( 'Delete', 'erp' );
        }

        if ( $this->page_status == '2' ) {
            $actions['reject']   = __( 'Reject', 'erp' );
            $actions['approved'] = __( 'Approve', 'erp' );
        } elseif ( $this->page_status == '1' ) {
            $actions['pending'] = __( 'Mark Pending', 'erp' );
            $actions['reject']   = __( 'Reject', 'erp' );
        } elseif ( $this->page_status == '3') {
            $actions['approved'] = __( 'Approve', 'erp' );
            $actions['pending'] = __( 'Mark Pending', 'erp' );
        } else {
            $actions['reject']   = __( 'Reject', 'erp' );
            $actions['approved'] = __( 'Approve', 'erp' );
            $actions['pending'] = __( 'Mark Pending', 'erp' );
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
        $base_link      = admin_url( 'admin.php?page=erp-hr&section=leave' );

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
        $this->page_status     = isset( $_GET['status'] ) ?sanitize_text_field( wp_unslash(  $_GET['status'] ) ) : '2';

        // only necessary because we have sample data
        $args = array(
            'offset'  => $offset,
            'number'  => $per_page,
            'status'  => $this->page_status,
            'year'    => isset( $_GET['filter_year'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_year'] ) ) : '',
            'orderby' => isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'created_on',
            'order'   => isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'DESC',
            's'       => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''
        );

        $this->counts = erp_hr_leave_get_requests_count();
        $this->items  = erp_hr_get_leave_requests( $args );

        $this->set_pagination_args( array(
            'total_items' => $this->counts[ $this->page_status ]['count'],
            'per_page'    => $per_page
        ) );
    }

}
