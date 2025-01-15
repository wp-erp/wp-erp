<?php

namespace WeDevs\ERP\HRM;

use WeDevs\ERP\HRM\Models\FinancialYear;
use WeDevs\ERP\HRM\Models\LeavePolicy;

/**
 * List table class
 */
class LeaveRequestsListTable extends \WP_List_Table {

    private $counts = [];

    private $page_status;

    private $users = [];

    public $empty_list = false;

    public function __construct() {
        global $status, $page;

        parent::__construct( [
            'singular' => 'leave-request',
            'plural'   => 'leave-requests',
            'ajax'     => false,
        ] );

        $this->table_css();
    }

    public function get_table_classes() {
        return [ 'widefat', 'fixed', 'striped', 'request-list-table', 'leaves', $this->_args['plural'] ];
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
    public function extra_tablenav( $which ) {
        if ( $which != 'top' ) {
            return;
        }

        $this->filter_option();
    }

    /**
     * Message to show if no requests found
     *
     * @return void
     */
    public function no_items() {
        esc_html_e( 'No requests found.', 'erp' );
    }

    /**
     * Get the column names
     *
     * @return array
     */
    public function get_columns() {
        $columns = [
            'cb'          => '<input type="checkbox" />',
            'name'        => __( 'Employee Name', 'erp' ),
            'policy'      => __( 'Policy', 'erp' ),
            'request'     => __( 'Request For', 'erp' ),
            'created_at'  => __( 'Requested On', 'erp' ),
            'available'   => __( 'Available', 'erp' ),
            'status'      => __( 'Status', 'erp' ),
            'reason'      => __( 'Reason', 'erp' ),
            'approved_by' => __( 'Approved By', 'erp' ),
        ];

        if ( isset( $_GET['status'] ) && $_GET['status'] == 3 ) {
            $columns['approved_by'] = __( 'Rejected By', 'erp' );
        }

        return $columns;
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
        global $wpdb;

        switch ( $column_name ) {

            case 'created_at':
                return erp_format_date( $item->created_at );

            case 'policy':
                return esc_html( $item->policy_name );

            case 'status':
                return sprintf( '<span class="status-%s">%s</span>', absint( $item->status ), erp_hr_leave_request_get_statuses( $item->status ) );

            case 'approved_by':
                $status = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT message, approved_by, created_at FROM {$wpdb->prefix}erp_hr_leave_approval_status WHERE leave_request_id = %d ORDER BY id DESC LIMIT 1",
                        [ $item->id ]
                    )
                );

                $approved_by = '';
                $approved_on = '';

                if ( ! empty( $status ) && null !== $status->approved_by ) {
                    $user        = get_user_by( 'id', $status->approved_by );
                    $approved_by = $user instanceof \WP_User ? esc_html( $user->display_name ) : '';
                    $approved_on = erp_format_date( $status->created_at );
                }

                if ( $approved_by && $approved_on ) {
                    $message = $status->message ? esc_html( $status->message ) : '';
                    $reason  = $message ? "<p style='white-space: nowrap; overflow: hidden; text-overflow: ellipsis;' title='{$message}'>{$message}</p>" : '';

                    return sprintf( '<p><strong>%s</strong></p><p><em>%s</em></p>%s', $approved_by, $approved_on, $reason );
                }
                break;

            case 'request':
                $request_days = $item->start_date === $item->end_date
                    ? erp_format_date( $item->start_date, 'M d, Y' )
                    : erp_format_date( $item->start_date, 'M d, Y' ) . ' - ' . erp_format_date( $item->end_date, 'M d, Y' );
                $str = '<p><strong>' . $request_days . '</strong></p>';

                if ( $item->day_status_id != '1' ) {
                    $days = erp_hr_leave_request_get_day_statuses( $item->day_status_id );
                } else {
                    $days = number_format_i18n( $item->days ) . ' ' . _n( 'day', 'days', $item->days, 'erp' );
                }
                $days = sprintf( '<span class="tooltip" title="%s">%s</span>', __( 'Request Days', 'erp' ), $days );

                $str .= "<p><em>$days</em></p>";

                return $str;

            case 'available':
                $available = '';

                if ( floatval( $item->available ) >= 0 && floatval( $item->extra_leaves ) == 0 ) {
                    if ( floatval( $item->available ) == 0 ) {
                        $available = '&mdash;';
                    } else {
                        $available = sprintf( '<span class="green tooltip" title="%s"> %s %s</span>', __( 'Available Leave', 'erp' ), erp_number_format_i18n( $item->available ), _n( 'day', 'days', $item->available + 1, 'erp' ) );
                    }
                } elseif ( floatval( $item->extra_leaves ) > 0 ) {
                    $available = sprintf( '<span class="red tooltip" title="%s"> -%s %s</span>', __( 'Extra Leave', 'erp' ), erp_number_format_i18n( $item->extra_leaves ), _n( 'day', 'days', $item->extra_leaves, 'erp' ) );
                }

                return $available;

            case 'reason':
                $attachment       = '';
                $leave_attachment = get_user_meta( $item->user_id, 'leave_document_' . $item->id );

                foreach ( $leave_attachment as $la ) {
                    $file_link  = esc_url( wp_get_attachment_url( $la ) );
                    $file_name  = esc_attr( basename( $file_link ) );
                    $attachment .= "<a target='_blank' href='{$file_link}'>{$file_name}</a><br>";
                }
                $str = '';

                if ( trim( $item->reason ) != '' ) {
                    $str .= '<p>' . esc_html( $item->reason ) . '</p>';
                }

                if ( $attachment != '' ) {
                    $str .= "<p  title='$file_name' style='white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'>$attachment</p>";
                }

                return stripslashes( $str );

            case 'message':
                return esc_html( $item->message );

            default:
                return isset( $item->$column_name ) ? esc_html( $item->$column_name ) : '';
        }
    }

    /**
     * Search employee form for leave request table
     *
     * @since 1.6.5
     *
     * @param string $text
     * @param string $input_id
     *
     * @return void
     */
    public function search_box( $text, $input_id ) {
        if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
            return;
        }

        $input_id = $input_id . '-search-input';

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            echo '<input type="hidden" name="orderby" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) ) . '" />';
        }

        if ( ! empty( $_REQUEST['order'] ) ) {
            echo '<input type="hidden" name="order" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) . '" />';
        }

        if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
            echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['post_mime_type'] ) ) ) . '" />';
        }

        if ( ! empty( $_REQUEST['detached'] ) ) {
            echo '<input type="hidden" name="detached" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['detached'] ) ) ) . '" />';
        } ?>
        <p class="search-box">
            <label for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
            <input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
            <?php submit_button( $text, 'button', 'employee_search', false, [ 'id' => 'search-submit' ] ); ?>
        </p>
        <?php
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = [
            'request' => [ 'start_date', false ],
            'to_date'   => [ 'end_date', false ],
            'name'      => [ 'display_name', false ],
            'status'    => [ 'last_status', true ],
            'created_at'   => [ 'id', true ],
            'policy'    => [ 'policy', true ],
            'available'    => [ 'available', true ],
        ];

        return $sortable_columns;
    }

    /**
     * Render the employee name column
     *
     * @param object $item
     *
     * @return string
     */
    public function column_name( $item ) {
        $tpl     = '?page=erp-hr&section=leave&leave_action=%s&id=%d&filter_year=%d';
        $nonce   = 'erp-hr-leave-req-nonce';
        $actions = [];

        $delete_url  = wp_nonce_url( sprintf( $tpl, 'delete', $item->id, $item->f_year ), $nonce );
        $reject_url  = wp_nonce_url( sprintf( $tpl, 'reject', $item->id, $item->f_year ), $nonce );
        $approve_url = wp_nonce_url( sprintf( $tpl, 'approve', $item->id, $item->f_year ), $nonce );
        $pending_url = wp_nonce_url( sprintf( $tpl, 'pending', $item->id, $item->f_year ), $nonce );

        if ( erp_get_option( 'erp_debug_mode', 'erp_settings_general', 0 ) ) {
            $actions['delete'] = sprintf( '<a href="%s" data-id="%d" class="submitdelete">%s</a>', $delete_url, $item->id, __( 'Delete', 'erp' ) );
        }

        if ( $item->status == '2' || $item->status == '4' ) {
            $actions['approved'] = sprintf( '<a class="erp-hr-leave-approve-btn" data-id="%s" href="%s">%s</a>', $item->id, $approve_url, __( 'Approve', 'erp' ) );
            $actions['reject']   = sprintf( '<a class="erp-hr-leave-reject-btn" data-id="%s" href="%s">%s</a>', $item->id, $reject_url, __( 'Reject', 'erp' ) );
        } elseif ( $item->status == '1' ) {
            $actions['reject'] = sprintf( '<a class="erp-hr-leave-reject-btn" data-id="%s" href="%s">%s</a>', $item->id, $reject_url, __( 'Reject', 'erp' ) );
        } elseif ( $item->status == '3' ) {
            $actions['approved'] = sprintf( '<a class="erp-hr-leave-approve-btn" data-id="%s" href="%s">%s</a>', $item->id, $approve_url, __( 'Approve', 'erp' ) );
        }

        return sprintf(
            apply_filters( 'erp_leave_request_employee_name_column', '', $item->id ) . '<a href="%3$s"><strong>%1$s</strong></a>' . '%2$s',
            $item->name,
            $this->row_actions( apply_filters( 'erp_leave_request_row_actions', $actions, $item ) ),
            erp_hr_url_single_employee( $item->user_id )
        );
    }

    /**
     * Set the bulk actions
     *
     * @return array
     */
    public function get_bulk_actions() {
        if ( erp_get_option( 'erp_debug_mode', 'erp_settings_general', 0 ) ) {
            $actions['delete'] = __( 'Delete', 'erp' );
        }

        if ( $this->page_status == '2' || $this->page_status == '4' ) {
            $actions['approved'] = __( 'Approve', 'erp' );
            $actions['reject']   = __( 'Reject', 'erp' );
        } elseif ( $this->page_status == '1' ) {
            $actions['reject'] = __( 'Reject', 'erp' );
        } elseif ( $this->page_status == '3' ) {
            $actions['approved'] = __( 'Approve', 'erp' );
        } else {
            $actions['reject']   = __( 'Reject', 'erp' );
            $actions['approved'] = __( 'Approve', 'erp' );
            /*$actions['pending']  = __( 'Mark Pending', 'erp' );*/
        }

        return $actions;
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
            '<input type="checkbox" name="request_id[]" value="%s" />', $item->id
        );
    }

    /**
     * Set the views
     *
     * @return array
     */
    public function get_status() {
        //         get current year as default f_year
        $current_f_year = erp_hr_get_financial_year_from_date();
        $f_year         = isset( $_GET['filter_year'] ) ? absint( wp_unslash( $_GET['filter_year'] ) ) : ( ! empty( $current_f_year ) ? $current_f_year->id : '' );

        $status_links = [];
        $base_link    = admin_url( 'admin.php?page=erp-hr&section=leave&filter_year=' . $f_year );

        foreach ( $this->counts as $key => $value ) {
            $class                = ( $key == $this->page_status ) ? 'current' : 'status-' . $key;
            $status_links[ $key ] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg( [ 'status' => $key ], $base_link ), $class, $value['label'], $value['count'] );
        }

        return $status_links;
    }

    /**
     * Prepare the class items
     *
     * @return void
     */
    public function prepare_items() {
        // phpcs:disable
        $columns               = $this->get_columns();
        $hidden                = [];
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = [ $columns, $hidden, $sortable ];

        $per_page          = 30;
        $current_page      = $this->get_pagenum();
        $offset            = ( $current_page - 1 ) * $per_page;
        $this->page_status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '2';
        if( !empty( $_GET['filter_employee_search'] ) && 'Apply' === $_GET['filter_employee_search'] ){
            $this->page_status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : 'all';
        }

        // get current year as default f_year
        $current_f_year = erp_hr_get_financial_year_from_date();
        $f_year         = isset( $_GET['filter_year'] ) ? absint( wp_unslash( $_GET['filter_year'] ) ) : ( ! empty( $current_f_year ) ? $current_f_year->id : '' );

        // only necessary because we have sample data
        $args = [
            'offset'  => $offset,
            'number'  => $per_page,
            'status'  => $this->page_status,
            'f_year'  => isset( $_GET['financial_year'] ) ? sanitize_text_field( wp_unslash( $_GET['financial_year'] ) ) : $f_year,
            'orderby' => isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'created_at',
            'order'   => isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'DESC',
            's'       => isset( $_GET['employee_name'] ) ? sanitize_text_field( wp_unslash( $_GET['employee_name'] ) ) : '',
        ];

        if ( ! empty( $_GET['leave_policy'] ) ) {
            $args['policy_id'] = sanitize_text_field( wp_unslash( $_GET['leave_policy'] ) );
        }

        if ( ! empty( $_GET['filter_leave_status'] ) ) {
            $args['status'] = map_deep( wp_unslash( $_GET['filter_leave_status'] ), 'sanitize_text_field' );
        }

        if ( ! empty( $_GET['filter_leave_year'] ) && 4 !== $_GET['filter_leave_year'] ) {
            if ( 1 === absint($_GET['filter_leave_year'] ) ) {
                $args['start_date'] = gmdate( 'Y-m-d', strtotime( '-7 days' ) );
                $args['end_date']   = gmdate( 'Y-m-d' );
            } elseif ( 2 === absint( $_GET['filter_leave_year'] ) ) {
                $args['start_date'] = gmdate( 'Y-m-d', strtotime( 'first day of previous month' ) );
                $args['end_date']   = gmdate( 'Y-m-d', strtotime( 'last day of previous month' ) );
            } elseif ( 3 === absint( $_GET['filter_leave_year'] ) ) {
                $today = date('d');
                $args['start_date'] = gmdate( 'Y-m-' . $today, strtotime( '-3 month' ) );
                $args['end_date']   = gmdate( 'Y-m-'. $today );
            }
        }

        if ( ! empty( $_GET['start_date'] ) ) {
            $args['start_date'] = sanitize_text_field( wp_unslash( $_GET['start_date'] ) );
        }
        if ( ! empty( $_GET['end_date'] ) ) {
            $args['end_date'] = sanitize_text_field( wp_unslash( $_GET['end_date'] ) );
        }

        // phpcs:enable
        if ( erp_hr_is_current_user_dept_lead() && ! current_user_can( 'erp_leave_manage' ) ) {
            $args['lead'] = get_current_user_id();
        }

        $this->counts = erp_hr_leave_get_requests_count( $f_year );

        $query_results = erp_hr_get_leave_requests( $args );
        $this->items   = $query_results['data'];
        $total         = $query_results['total'];

        if ( 0 === $total ) {
            $this->empty_list = true;
        }

        $this->set_pagination_args( [
            'total_items' => $total,
            'per_page'    => $per_page,
        ] );
    }

    /**
     * Render extra filtering option in
     * top of the table
     *
     * @since 0.1
     *
     * @return void
     */
    public function filter_option( $filtered_option = true ) {
        $policies          = LeavePolicy::all();
        $policy_data       = [];
        $leave_policy_name = '';
        foreach ( $policies as $policy ) {
            if ( ! empty( $_GET['leave_policy'] ) && (int) $policy['leave_id'] === (int) $_GET['leave_policy'] ) {
                $leave_policy_name = $policy->leave->name;
            }
            $policy_data[ $policy['f_year'] ][$policy['leave_id']] = [
                'name'          => $policy->leave->name,
                'policy_id'     => $policy['leave_id'],
                'employee_type' => $policy['employee_type'],
            ];
        }

        $financial_years = [];
        foreach ( FinancialYear::all() as $f_year ) {
            $financial_years[ $f_year['id'] ] = $f_year['fy_name'];
        }

        $filters         = [];
        $selected_f_year = '';
        if ( ! empty( $_GET['employee_name'] ) ) {
            $filters['employee_name'] = sanitize_text_field( wp_unslash( $_GET['employee_name'] ) );
        }
        if ( ! empty( $_GET['financial_year'] ) ) {
            $filters['financial_year'] = $financial_years[ sanitize_text_field( wp_unslash( $_GET['financial_year'] ) ) ];
        }
        if ( ! empty( $leave_policy_name ) ) {
            $filters['leave_policy'] = $leave_policy_name;
        }
        if ( empty( $this->counts ) ) {
            $this->counts = erp_hr_leave_get_requests_count( $selected_f_year );
        }
        if ( ! empty( $_GET['filter_leave_status'] ) ) {
            if ( is_array( $_GET['filter_leave_status'] ) ) {
                $status = [];
                foreach ( $_GET['filter_leave_status'] as $filter_leave_status ) {
                    $status[] = $this->counts[ sanitize_text_field( wp_unslash( $filter_leave_status ) ) ]['label'];
                }

                $filters['filter_leave_status'] = implode( ', ', $status);
            } else {
                $filters['filter_leave_status'] = $this->counts[ sanitize_text_field( wp_unslash( $_GET['filter_leave_status'] ) ) ]['label'];
            }
        }
        $custom_date_html = '';
        if ( ! empty( $_GET['filter_leave_year'] ) ) {
            if ( 1 === (int) $_GET['filter_leave_year'] ) {
                $filters['filter_leave_year'] = esc_html__( 'Last week', 'erp' );
            } elseif ( 2 === (int) $_GET['filter_leave_year'] ) {
                $filters['filter_leave_year'] = esc_html__( 'Last month', 'erp' );
            } elseif ( 3 === (int) $_GET['filter_leave_year'] ) {
                $filters['filter_leave_year'] = esc_html__( 'Last 3 months', 'erp' );
            } elseif ( 'custom' === $_GET['filter_leave_year'] ) {
                $start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( wp_unslash( $_GET['start_date'] ) ) : '';
                $end_date = isset( $_GET['end_date'] ) ? sanitize_text_field( wp_unslash( $_GET['end_date'] ) ) : '';
                $filters['filter_leave_year'] = gmdate( 'M d, Y', strtotime( $start_date ) ) . ' - ' . gmdate( 'M d, Y', strtotime( $end_date ) );
                $custom_date_html = '<div class="input-component" id="custom-input" style="display: flex; justify-content: space-between;">
                                     <div style="display: flex">
                                     <label for="start_date">' . esc_html__( 'From', 'erp' ) . '
                                     <input autocomplete="off" name="start_date" id="start_date" class="erp-leave-date-field" type="text" required value='. esc_attr( $start_date ) .'>&nbsp;
                                     </div>
                                     <div>
                                     <label for="end_date">' . esc_html__( 'To', 'erp' ) . '
                                     <input autocomplete="off" name="end_date" id="end_date" class="erp-leave-date-field" type="text" required value='. esc_attr( $end_date ) .'>
                                     </div>
                                     </div>';
            }
        }

        wp_localize_script( 'wp-erp-hr', 'wpErpLeavePolicies', $policy_data );

        $employee_name = ! empty( $_GET['employee_name'] ) ? sanitize_text_field( wp_unslash( $_GET['employee_name'] ) ) : '';
        ?>
        <style>
         table.leaves th#available {
             width: inherit;
         }
         table.leaves th#request {
            /* width: 150px; */
            width: inherit;

        }
        </style>
        <div id="wperp-filter-dropdown" class="wperp-filter-dropdown" style="margin: -46px 0 0 0;">
            <div id="search-main">
                <?php
                if ( $filtered_option ) {
                    $this->filtered_option( $filters );
                }
                ?>
                <div class="filter-right">
                    <a id='wperp-leave-filter-dropdown' class='wperp-btn btn--filter'>
                        <svg style='margin: 8px 10px 8px 10px;' width='17' height='12' viewBox='0 0 17 12' fill='none' xmlns='http://www.w3.org/2000/svg'>
                            <path d='M6.61111 11.6668H10.3889V9.77794H6.61111V11.6668ZM0 0.333496V2.22239H17V0.333496H0ZM2.83333 6.94461H14.1667V5.05572H2.83333V6.94461Z' fill='white' />
                        </svg><?php esc_html_e( 'Filter Leave Requests', 'erp' ); ?>&nbsp;&nbsp;&nbsp;
                    </a>
                </div>
            </div>

            <div class="erp-dropdown-filter-content" id="erp-leave-dropdown-content">
                <div class="wperp-filter-panel wperp-filter-panel-default" style="width: 450px !important;">
                    <h2 class="leave_request_title"><?php esc_html_e( 'Filter Leave Request', 'erp' ); ?></h2>
                    <div class="wperp-filter-panel-body">
                        <div class="input-component">
                            <label for="employee_name"><?php esc_html_e( 'Employee name', 'erp' ); ?></label>
                            <input value="<?php echo esc_attr( $employee_name ); ?>" autocomplete="off" type="text" name="employee_name" id="employee_name" placeholder="<?php esc_attr_e( 'Search by employee name', 'erp' ); ?>" />
                            <span id='live-employee-search'></span>
                        </div>

                        <div class='input-component' style="display: flex; justify-content: space-between; ">
                            <div>
                                <label for='financial_year'><?php esc_html_e( 'Financial year', 'erp' ); ?></label>
                                <select name='financial_year' id='financial_year'>
                                    <option value=''><?php echo esc_attr__( 'Select year', 'erp' ); ?></option>
                                    <?php
                                    foreach ( $financial_years as $key => $year ) {
                                        $selected_f_year = '';
                                        if ( ! empty( $filters['financial_year'] ) && (int) $filters['financial_year'] === (int) $year ) {
                                            $selected_f_year = 'selected=selected';
                                        }
                                        echo sprintf( "<option %s value='%s'>%s</option>\n", esc_attr( $selected_f_year ), esc_html( $key ), esc_html( $year ) );
                                    }
                                    ?>
                                </select>
                            </div>
                            <div>
                                <label for="leave_policy"><?php esc_html_e( 'Leave Policy', 'erp' ); ?></label>
                                <select name='leave_policy' id='leave_policy'>
                                    <option value=''><?php echo esc_attr__( 'All Policy', 'erp' ); ?></option>
                                    <?php
                                    if ( ! empty( $_GET['financial_year'] ) ) {
                                        foreach ( $policy_data[$_GET['financial_year']] as $policy ) {
                                            $selected = '';
                                            if ( ! empty( $filters['leave_policy'] ) && (int) $_GET['leave_policy'] === (int) esc_attr( $policy['policy_id'] ) ) {
                                                $selected = 'selected=selected';
                                            }
                                            echo sprintf( "<option %s value='%s'>%s</option>\n", esc_attr($selected), esc_attr( $policy['policy_id'] ), esc_html( $policy['name'] ) );
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class='input-component'>
                            <label for="filter_leave_status">
                                <?php esc_html_e( 'Leave status', 'erp' ); ?>
                                <span class="leave-tool-tip">
                                    <?php
                                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                    echo erp_help_tip( esc_html__( 'Select the leave request states as per your preference. Selecting none would show all leave states including Approved, Pending and Rejected.', 'erp' ) ); ?>
                                </span>
                            </label>
                            <div style="margin: 15px 0 25px 0">
                                <?php
                                foreach ( $this->counts as $key => $title ) {
                                    if ( 'all' === $key ) {
                                        continue;
                                    }
                                    $checked = '';
                                    if ( ! empty( $_GET['filter_leave_status'] ) && is_array( $_GET['filter_leave_status'] ) && in_array( $key, $_GET['filter_leave_status'] ) ) {
                                        $checked = 'checked';
                                    }
                                    echo sprintf( "<input  name='filter_leave_status[]' %s class='filter_leave_status leave-status' id='%s' type='checkbox' value='%s' ><label class='checkbox' for='%s'><span>%s</span></label>\n", esc_attr($checked), esc_html( $key ), esc_html( $key ), esc_html( $key ), esc_html( $title['label'] ) );
                                }
                                ?>
                            </div>
                        </div>
                        <div class='input-component'>
                            <label for='filter_leave_year'><?php esc_html_e( 'Date range', 'erp' ); ?></label>
                            <div>
                                <?php
                                $filter_leave_years = [
                                    ''       => __( 'Filter by date', 'erp' ),
                                    '1'      => __( 'Last week', 'erp' ),
                                    '2'      => __( 'Last month', 'erp' ),
                                    '3'      => __( 'Last 3 months', 'erp' ),
                                    'custom' => __( 'Custom', 'erp' ),
                                ];
                                ?>
                                <select name='filter_leave_year' id='filter_leave_year'>
                                    <?php
                                    foreach ( $filter_leave_years as $key => $title ) {
                                        if ( 'all' === $key ) {
                                            continue;
                                        }
                                        $selected = '';
                                        if ( ! empty( $_GET['filter_leave_year'] ) && $key == $_GET['filter_leave_year'] ) {
                                            $selected = 'selected';
                                        }
                                        echo sprintf( "<option %s value='%s'>%s</option>\n", esc_attr($selected), esc_attr( $key ), esc_html( $title ) );
                                    }
                                    ?>
                                </select>
                                <?php
                                if ( !empty( $_GET[ 'filter_leave_year' ] ) && 'custom' === $_GET[ 'filter_leave_year' ] ) {
                                    echo wp_kses($custom_date_html, array(
                                        'input' => array(
                                            'type' => array(),
                                            'name' => array(),
                                            'id' => array(),
                                            'value' => array(),
                                            'class' => array(),
                                            'placeholder' => array(),
                                            'autocomplete' => array(),
                                        ),
                                        'span' => array(
                                            'id' => array(),
                                        ),
                                    )) ;
                                }
                                ?>
                            <span id="custom-date-range-leave-filter"></span>
                            </div>
                        </div>
                    </div>

                    <div class="wperp-filter-panel-footer">
                        <input type="button" class="wperp-btn btn--cancel btn--filter-apply" value="<?php esc_attr_e( 'Cancel', 'erp' ); ?>" name="hide_filter">
                        <input type="button" class="wperp-btn btn--reset btn--filter-apply" value="<?php esc_attr_e( 'Reset', 'erp' ); ?>" data-url="<?php echo esc_url($this->get_filter_reset_url()); ?>" name="leave_filter_reset">
                        <input type="submit" name="filter_employee_search" id="filter_employee_search" class="wperp-btn btn--filter-apply" value="<?php esc_attr_e( 'Apply', 'erp' ); ?>">
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function filtered_option( $filters ) {
        if ( empty( $filters ) ) {
            return;
        }
        ?>
        <div class='filter-left'>
            <?php
            // phpcs:disable
            $clear_all_url = $this->get_filter_reset_url();

            foreach ( $filters as $key => $filter ) {
                if( empty( $filter ) ){
                    continue;
                }
                $build_url['employee_name']          = '';
                $build_url['financial_year']         = '';
                $build_url['leave_policy']           = '';
                $build_url['filter_leave_status']    = '';
                $build_url['filter_leave_year']      = '';
                $build_url['filter_employee_search'] = 'Apply';
                $build_url                        = wp_parse_args( $filters, $build_url );
                if ( ! empty( $filters['filter_leave_year'] ) ) {
                    if ( 'custom' === $_GET['filter_leave_year'] ) {
                        $build_url['start_date'] = isset( $_GET['start_date'] ) ? sanitize_text_field( wp_unslash( $_GET['start_date'] ) ) : '';
                        $build_url['end_date']   = isset( $_GET['end_date'] ) ? sanitize_text_field( wp_unslash( $_GET['end_date'] ) ) : '';
                    }
                    $build_url['filter_leave_year'] = $_GET['filter_leave_year'];
                }
                if ( ! empty( $filters['financial_year'] ) ) {
                    $build_url['financial_year'] = $_GET['financial_year'];
                }
                if ( ! empty( $filters['leave_policy'] ) ) {
                    $build_url['leave_policy'] = $_GET['leave_policy'];
                }
                if ( ! empty( $filters['filter_leave_status'] ) ) {
                    $build_url['filter_leave_status'] = $_GET['filter_leave_status'];
                }
                if ( 'filter_leave_year' === $key && 'custom' === $_GET['filter_leave_year'] ) {
                    unset( $build_url['start_date'] );
                    unset( $build_url['end_date'] );
                    $build_url['filter_leave_year'] = '';
                }

                if ( 'filter_leave_status' === $key ) {
                    $build_url[ $key ] = 'all';
                }else{
                    $build_url[ $key ] = '';
                }

                $url = count( $filters ) > 1 ? admin_url( 'admin.php?page=erp-hr&section=leave&sub-section=leave-requests&' . http_build_query( $build_url ) ) : $clear_all_url;
                // phpcs:enable
                ?>
                <div class="single-filter">
                    <div class="filter-text">
                        <?php
                        echo esc_html( $filter );
                        ?>
                    </div>
                    <a href="<?php echo esc_url( $url ); ?>" class="filter-close">
                        X
                    </a>
                </div>
                <?php
            }
            ?>
            <div class="clear-filter">
                <a href="<?php echo esc_url( $clear_all_url ); ?>">
                    <?php echo esc_html__( 'Clear filter', 'erp' ); ?>
                </a>
            </div>
        </div>
        <?php
    }

    public function get_filter_reset_url() {
        return admin_url( 'admin.php?page=erp-hr&section=leave&sub-section=leave-requests' );
    }
}
