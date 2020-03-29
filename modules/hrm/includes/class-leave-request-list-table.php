<?php
namespace WeDevs\ERP\HRM;

use WeDevs\ERP\HRM\Models\Financial_Year;

/**
 * List table class
 */
class Leave_Requests_List_Table extends \WP_List_Table {

    private $counts = array();
    private $page_status;

    function __construct() {
        global $status, $page;

        parent::__construct( array(
            'singular' => 'leave-request',
            'plural'   => 'leave-requests',
            'ajax'     => false
        ) );

        $this->table_css();
    }

    function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', 'request-list-table', 'leaves', $this->_args['plural'] );
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

        $f_year = get_financial_year_from_date();
        $f_year = ! empty( $f_year ) ? $f_year->id : '';

        $financial_years =  array( '' => esc_attr__( 'select year', 'erp') ) +  wp_list_pluck( Financial_Year::all(), 'fy_name', 'id' );

        $selected_year = ( isset( $_GET['filter_year'] ) ) ? absint( wp_unslash( $_GET['filter_year'] ) ) : $f_year;
        ?>
        <div class="alignleft actions">

            <label class="screen-reader-text" for="filter_year"><?php esc_html_e( 'Filter by year', 'erp' ) ?></label>
            <input type="hidden" name="status" value="<?php echo esc_html( $this->page_status ); ?>">
            <select name="filter_year" id="filter_year">
                <?php
                foreach ( $financial_years as $f_id => $f_name ) {
                    echo sprintf( "<option value='%s'%s>%s</option>\n", esc_html( $f_id ), selected( $selected_year, $f_id, false ), esc_html( $f_name ) );
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
     * Get the column names
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            //'cb'        => '<input type="checkbox" />',
            'name'      => __( 'Employee Name', 'erp' ),
            'policy'    => __( 'Leave Policy', 'erp' ),
            'from_date' => __( 'From Date', 'erp' ),
            'to_date'   => __( 'To Date', 'erp' ),
            //'entitlement'    => __( 'Entitled Days', 'erp' ),
            'days'      => __( 'Request', 'erp' ),
            'available' => __( 'Available', 'erp' ),
            'extra'         => __( 'Extra Leaves', 'erp' ),
            'status'    => __( 'Status', 'erp' ),
            'leave_attachment'  => __( 'Attachment', 'erp' ),
            'reason'    => __( 'Leave Reason', 'erp' ),
        );

        if ( isset( $_GET['status'] ) && $_GET['status'] == 3 ) {
            $columns['message'] =  __( 'Reject Reason', 'erp' );
        }

        return $columns;
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

            // case 'name':
            //     return esc_attr( $item->name );

            case 'policy':
                return esc_attr( $item->policy_name );

            case 'from_date':
                return erp_format_date( $item->start_date );

            case 'to_date':
                return erp_format_date( $item->end_date );

            case 'status':
                return '<span class="status-' . $item->status . '">' . erp_hr_leave_request_get_statuses( $item->status ) . '</span>';

            case 'days':
                if ( $item->day_status_id != '1' ) {
                    $days = erp_hr_leave_request_get_day_statuses( $item->day_status_id );
                } else {
                    $days = number_format_i18n( $item->days ) . ' ' . esc_attr__( 'days', 'erp' );
                }

                return sprintf( '<span>%s</span>', $days );

            case 'reason':
                return stripslashes( $item->reason );

            case 'entitlement':
                return sprintf( '<span class="green">%d %s</span>', number_format_i18n( $item->entitlement ), __( 'days', 'erp' ) );

            case 'available':
                return floatval( $item->available ) > 0 ? sprintf( '<span class="green">%s %s</span>', erp_number_format_i18n( $item->available ), __( 'days', 'erp' ) ) : '-';

            case 'extra':
                return floatval( $item->extra_leaves ) > 0 ? sprintf( '<span class="red">%s %s</span>', erp_number_format_i18n( $item->extra_leaves ), __( 'days', 'erp' ) ) : '-';

            case 'message':
                return stripslashes( $item->message );

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
            'from_date' => array( 'start_date', false ),
            'to_date' => array( 'end_date', false ),
            'name'      => array( 'display_name', false ),
        );

        return $sortable_columns;
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
            $actions['delete'] = sprintf( '<a href="%s" data-id="%d" class="submitdelete">%s</a>', $delete_url, $item->id, __( 'Delete', 'erp' ) );
        }

        if ( $item->status == '2' || $item->status == '4' ) {
            $actions['approved']   = sprintf( '<a class="erp-hr-leave-approve-btn" data-id="%s" href="%s">%s</a>', $item->id, $approve_url, __( 'Approve', 'erp' ) );
            $actions['reject']   = sprintf( '<a class="erp-hr-leave-reject-btn" data-id="%s" href="%s">%s</a>', $item->id, $reject_url, __( 'Reject', 'erp' ) );

            $actions = \apply_filters( 'erp_leave_request_row_actions', $actions, $item->id );

        } elseif ( $item->status == '1' ) {
            $actions['reject']   = sprintf( '<a class="erp-hr-leave-reject-btn" data-id="%s" href="%s">%s</a>', $item->id, $reject_url, __( 'Reject', 'erp' ) );

        } elseif ( $item->status == '3') {
            $actions['approved']   = sprintf( '<a class="erp-hr-leave-approve-btn" data-id="%s" href="%s">%s</a>', $item->id, $approve_url, __( 'Approve', 'erp' ) );
        }

        return sprintf(
            '<a href="%3$s"><strong>%1$s</strong></a>' . apply_filters( 'erp_leave_request_employee_name_column', '', $item->id ) . '%2$s',
            $item->name,
            $this->row_actions( $actions ),
            erp_hr_url_single_employee( $item->user_id )
        );
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

        if ( $this->page_status == '2' || $this->page_status == '4' ) {
            $actions['approved'] = __( 'Approve', 'erp' );
            $actions['reject']   = __( 'Reject', 'erp' );
        } elseif ( $this->page_status == '1' ) {
            $actions['reject']   = __( 'Reject', 'erp' );
        } elseif ( $this->page_status == '3') {
            $actions['approved'] = __( 'Approve', 'erp' );
        } else {
            $actions['reject']   = __( 'Reject', 'erp' );
            $actions['approved'] = __( 'Approve', 'erp' );
            $actions['pending']  = __( 'Mark Pending', 'erp' );
        }

        //return $actions;
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
        $offset                = ( $current_page - 1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '2';

        // get current year as default f_year
        $f_year = get_financial_year_from_date();
        $f_year = ! empty( $f_year ) ? $f_year->id : '';

        // only necessary because we have sample data
        $args = array(
            'offset'  => $offset,
            'number'  => $per_page,
            'status'  => $this->page_status,
            'f_year'  => isset( $_GET['filter_year'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_year'] ) ) : $f_year,
            'orderby' => isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'created_at',
            'order'   => isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'DESC',
            's'       => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''
        );

        if ( erp_hr_is_current_user_dept_lead() && ! current_user_can( 'erp_leave_manage' ) ) {
            $args['lead'] = get_current_user_id();
        }

        $this->counts = erp_hr_leave_get_requests_count();

        $query_results  = erp_hr_get_leave_requests( $args );
        $this->items    = $query_results['data'];
        $total          = $query_results['total'];

        $this->set_pagination_args( array(
            'total_items' => $total,
            'per_page'    => $per_page
        ) );
    }
}
