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
            'request'     => __( 'Request', 'erp' ),
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
                    ? erp_format_date( $item->start_date, 'M d' )
                    : erp_format_date( $item->start_date, 'M d' ) . ' &mdash; ' . erp_format_date( $item->end_date, 'M d' );

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
                    $file_link = esc_url( wp_get_attachment_url( $la ) );
                    $file_name = esc_attr( basename( $file_link ) );
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
            <label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
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
            'from_date' => [ 'start_date', false ],
            'to_date'   => [ 'end_date', false ],
            'name'      => [ 'display_name', false ],
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
        $tpl         = '?page=erp-hr&section=leave&leave_action=%s&id=%d&filter_year=%d';
        $nonce       = 'erp-hr-leave-req-nonce';
        $actions     = [];

        $delete_url  = wp_nonce_url( sprintf( $tpl, 'delete', $item->id, $item->f_year ), $nonce );
        $reject_url  = wp_nonce_url( sprintf( $tpl, 'reject', $item->id, $item->f_year ), $nonce );
        $approve_url = wp_nonce_url( sprintf( $tpl, 'approve', $item->id, $item->f_year ), $nonce );
        $pending_url = wp_nonce_url( sprintf( $tpl, 'pending', $item->id, $item->f_year ), $nonce );

        if ( erp_get_option( 'erp_debug_mode', 'erp_settings_general', 0 ) ) {
            $actions['delete'] = sprintf( '<a href="%s" data-id="%d" class="submitdelete">%s</a>', $delete_url, $item->id, __( 'Delete', 'erp' ) );
        }

        if ( $item->status == '2' || $item->status == '4' ) {
            $actions['approved']   = sprintf( '<a class="erp-hr-leave-approve-btn" data-id="%s" href="%s">%s</a>', $item->id, $approve_url, __( 'Approve', 'erp' ) );
            $actions['reject']     = sprintf( '<a class="erp-hr-leave-reject-btn" data-id="%s" href="%s">%s</a>', $item->id, $reject_url, __( 'Reject', 'erp' ) );
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

        $status_links   = [];
        $base_link      = admin_url( 'admin.php?page=erp-hr&section=leave&filter_year=' . $f_year );

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
        $columns               = $this->get_columns();
        $hidden                = [];
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = [ $columns, $hidden, $sortable ];

        $per_page              = 20;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page - 1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '2';

        // get current year as default f_year
        $current_f_year = erp_hr_get_financial_year_from_date();
        $f_year         = isset( $_GET['filter_year'] ) ? absint( wp_unslash( $_GET['filter_year'] ) ) : ( ! empty( $current_f_year ) ? $current_f_year->id : '' );

        // only necessary because we have sample data
        $args = [
            'offset'  => $offset,
            'number'  => $per_page,
            'status'  => $this->page_status,
            'f_year'  => isset( $_GET['filter_year'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_year'] ) ) : $f_year,
            'orderby' => isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'created_at',
            'order'   => isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'DESC',
            's'       => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
        ];

        if ( erp_hr_is_current_user_dept_lead() && ! current_user_can( 'erp_leave_manage' ) ) {
            $args['lead'] = get_current_user_id();
        }

        $this->counts = erp_hr_leave_get_requests_count( $f_year );

        $query_results  = erp_hr_get_leave_requests( $args );
        $this->items    = $query_results['data'];
        $total          = $query_results['total'];

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
     * @param string $which
     *
     * @return void
     */
    public function filter_option() {
        $selected_leave_policy   = ( isset( $_GET['leave_policy'] ) ) ? sanitize_text_field( wp_unslash( $_GET['leave_policy'] ) ) : 0;
        $filter_leave_status   = ( isset( $_GET['filter_leave_status'] ) ) ? sanitize_text_field( wp_unslash( $_GET['filter_leave_status'] ) ) : 'all';
        $filter_financial_year   = ( isset( $_GET['financial_year'] ) ) ? sanitize_text_field( wp_unslash( $_GET['financial_year'] ) ) : 'all';
        $financial_years = [];
        $current_f_year  = erp_hr_get_financial_year_from_date();
        foreach ( FinancialYear::all() as $f_year ) {
            $financial_years[ $f_year['id'] ] = $f_year['fy_name'];
        }
        ?>

        <div class="wperp-filter-dropdown" style="margin: -46px 0 0 0;">
            <a style="background-color: #1a9ed4 !important; color: rgb(255, 255, 255);" class="wperp-btn btn--default"><span class="dashicons dashicons-filter"></span>Filters<span class="dashicons dashicons-arrow-down-alt2"></span></a>

            <div class="erp-dropdown-filter-content" id="erp-dropdown-content">
                <div class="wperp-filter-panel wperp-filter-panel-default" style="width: 350px !important;">
                    <h3><?php esc_html_e( 'Filter Leave Request', 'erp' ); ?></h3>
                    <div class="wperp-filter-panel-body">
                        <label class="screen-reader-text" for="new_role"><?php esc_html_e( 'Employee name', 'erp' ); ?></label>
                        <input autocomplete="off" type="text" name="employee_name" id="employee_name" placeholder="<?php esc_attr_e( 'Search by employee name', 'erp' ); ?>" />

                        <select name='financial_year' id='financial_year'>
                            <?php
                            foreach ( $financial_years as $key => $year ) {
                                echo sprintf( "<option value='%s'%s>%s</option>\n", esc_html( $key ), selected( $filter_financial_year, esc_html( $key ), false ), esc_html( $year ) );
                            }
                            ?>
                        </select>
                        <label class="screen-reader-text" for="new_role"><?php esc_html_e( 'Filter by Policy', 'erp' ); ?></label>
                        <select name='leave_policy' id='leave_policy'>
                            <option value=''><?php echo esc_attr__( 'All Policy', 'erp' ); ?></option>
                            <?php
							foreach ( $policy_data as $policy ) {
								$selected = $policy['policy_id'] == $selected_leave_policy ? 'selected="selected"' : '';
								echo sprintf( "<option value='%s' %s>%s</option>", esc_attr( $policy['policy_id'] ), esc_attr( $selected ), esc_html( $policy['name'] ) );
							}
							?>
                        </select>

                        <label class="screen-reader-text" for="new_role"><?php esc_html_e( 'Filter by Leave status', 'erp' ); ?></label>
                        <select name="filter_leave_status" id="filter_leave_status">
                            <?php
                            foreach ( $this->counts as $key => $title ) {
                                echo sprintf( "<option value='%s'%s>%s</option>\n", esc_html( $key ), selected( $filter_leave_status, esc_html( $key ), false ), esc_html( $title['label'] ) );
                            }
                            ?>
                        </select>
                    </div>

                    <div class="wperp-filter-panel-footer">
                        <input type="submit" class="wperp-btn btn--cancel btn--filter" value="<?php esc_attr_e( 'Cancel', 'erp' ); ?>" name="hide_filter">
                        <input type="submit" class="wperp-btn btn--reset btn--filter" value="<?php esc_attr_e( 'Reset', 'erp' ); ?>" name="reset_filter">
                        <input type="submit" name="filter_employee" id="filter" class="wperp-btn btn--primary" value="<?php esc_attr_e( 'Apply', 'erp' ); ?>">
                    </div>
                </div>
            </div>
        </div>
        <script type='text/javascript'>
            ;jQuery(function ($) {
                var policies = <?php
                    $policies = \WeDevs\ERP\HRM\Models\LeavePolicy::all();
                    $result = [];

                    foreach ( $policies as $policy ) {
                        $result[ $policy['f_year'] ][] = [
                            'name'          => $policy->leave->name,
                            'policy_id'     => $policy['id'],
                            'employee_type' => $policy['employee_type'],
                        ];
                    }
                    echo wp_json_encode( $result );
                    ?>;
                $('#financial_year').on('change', function (e) {
                    var f_year = $('#financial_year').val();
                    if (policies[f_year]) {
                        $.each(policies[f_year], function (id, policy) {
                            if (employee_type != '' && policy.employee_type != employee_type) {
                                return;
                            }
                            var option = new Option(policy.name, policy.policy_id);
                            $('#leave_policy').append(option);
                        });
                    }
                });

            });
        </script>
		<?php
    }
}
