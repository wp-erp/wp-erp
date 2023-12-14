<?php

namespace WeDevs\ERP\HRM;

use WeDevs\ERP\HRM\Models\FinancialYear;

/**
 * List table class
 */
class EntitlementListTable extends \WP_List_Table {
    protected $entitlement_data = [];
    protected $page_status;

    public function __construct() {
        global $status, $page;

        parent::__construct( [
            'singular' => 'entitlement',
            'plural'   => 'entitlements',
            'ajax'     => false,
        ] );
    }

    public function get_table_classes() {
        return [ 'widefat', 'fixed', 'striped', 'entitlement-list-table', $this->_args['plural'] ];
    }

    /**
     * Get the column names
     *
     * @return array
     */
    public function get_columns() {
        $columns = [
            'cb'            => '<input type="checkbox" />',
            'name'          => __( 'Employee Name', 'erp' ),
            'leave_policy'  => __( 'Leave Policy', 'erp' ),
            'validity'      => __( 'Validity', 'erp' ),
            'available'     => __( 'Available', 'erp' ),
            'spent'         => __( 'Spent', 'erp' ),
        ];

        // hide cb if debug mode is off
        if ( ! erp_get_option( 'erp_debug_mode', 'erp_settings_general', 0 ) ) {
            unset( $columns['cb'] );
        }

        return apply_filters( 'erp_hr_entitlement_table_cols', $columns );
    }

    /**
     * Extra filters for the list table
     *
     * @since 0.1
     * @since 1.2.0 Using financial year or years instead of single year filtering
     *
     * @param string $which
     *
     * @return void
     */
    public function extra_tablenav( $which ) {
        if ( $which != 'top' ) {
            return;
        }
        $entitlement_years = wp_list_pluck( FinancialYear::orderBy( 'start_date', 'desc' )->get()->toArray(), 'fy_name', 'id' );

        if ( empty( $entitlement_years ) ) {
            return;
        }

        if ( ! empty( $_GET['financial_year'] ) ) {
            $selected_f_year = absint( wp_unslash( $_GET['financial_year'] ) );
        } else {
            $selected_f_year = erp_hr_get_financial_year_from_date()->id;
        }

        $policies    = \WeDevs\ERP\HRM\Models\LeavePolicy::all();
        $policy_data = [];

        foreach ( $policies as $policy ) {
            $policy_data[ $policy['f_year'] ][] = [
                'name'          => $policy->leave->name,
                'policy_id'     => $policy['id'],
                'employee_type' => $policy['employee_type'],
            ];
        }

        $selected_leave_id = '';

        if ( ! empty( $_GET['leave_policy'] ) ) {
            $selected_leave_id = absint( wp_unslash( $_GET['leave_policy'] ) );
        }

        // get employee type filter
        $employee_types = [
                ''      => esc_html__( 'Employee Types', 'erp' ),
                '-1'    => esc_html__( 'All', 'erp' ),
            ] + erp_hr_get_employee_types();
        $employee_type = isset( $_GET['filter_employee_type'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_employee_type'] ) ) : ''; ?>
            <div class="alignleft actions">
                <select name="financial_year" id="financial_year">
                    <?php echo wp_kses( erp_html_generate_dropdown( $entitlement_years, $selected_f_year ), [
                        'option' => [
                            'value'    => [],
                            'selected' => [],
                        ],
                    ] ); ?>
                </select>

                <select name="filter_employee_type" id="filter_employee_type">
                    <?php
                    foreach ( $employee_types as $type_id => $type_name ) {
                        echo sprintf( "<option value='%s'%s>%s</option>\n", esc_html( $type_id ), selected( $employee_type, $type_id, false ), esc_html( $type_name ) );
                    } ?>
                </select>

                <select name="leave_policy" id="leave_policy">
                    <option value=""><?php echo esc_attr__( 'All Policy', 'erp' ); ?></option>
                    <?php if ( array_key_exists( $selected_f_year, $policy_data ) ) {
                        foreach ( $policy_data[ $selected_f_year ] as $policy ) {
                            if ( $employee_type !== '' && $policy['employee_type'] != $employee_type ) {
                                continue;
                            }
                            $selected = $policy['policy_id'] == $selected_leave_id ? 'selected="selected"' : '';
                            echo sprintf( "<option value='%s' %s>%s</option>", esc_attr( $policy['policy_id'] ), esc_attr( $selected ), esc_html( $policy['name'] ) );
                        }
                    } ?>
                </select>

                <?php submit_button( __( 'Filter' ), 'button', 'filter_entitlement', false ); ?>
            </div>
        <?php
    }

    /**
     * Message to show if no entitlement found
     *
     * @return void
     */
    public function no_items() {
        esc_html_e( 'No entitlement found.', 'erp' );
    }

    /**
     * Default column values if no callback found
     *
     * @param object $item
     * @param string $column_name
     *
     * @return string
     */
    public function column_default( $entitlement, $column_name ) {
        switch ( $column_name ) {
            case 'leave_policy':
                return esc_html( $entitlement->policy_name );

            default:
                return isset( $entitlement->$column_name ) ? $entitlement->$column_name : '';
        }
    }

    public function column_validity( $item ) {
        $f_year = FinancialYear::find( $item->f_year );

        $str = '<p><strong>' . erp_format_date( $f_year->start_date ) . ' &mdash; ' . erp_format_date( $f_year->end_date ) . '</strong></p>';

        $days = number_format_i18n( $item->day_in ) . ' ' . _n( 'day', 'days', $item->day_in, 'erp' );
        $days = sprintf( '<span class="tooltip" title="%s">%s</span>', __( 'Entitled Days', 'erp' ), $days );

        $str .= "<p><em>$days</em></p>";

        return $str;
    }

    public function column_available( $entitlement ) {
        $str = '';

        if ( ! array_key_exists( $entitlement->id, $this->entitlement_data ) ) {
            $this->entitlement_data[ $entitlement->id ] = erp_hr_leave_get_balance_for_single_entitlement( $entitlement->id );
        }

        if ( array_key_exists( $entitlement->id, $this->entitlement_data ) && ! is_wp_error( $this->entitlement_data[ $entitlement->id ] ) ) {
            $available   = floatval( $this->entitlement_data[ $entitlement->id ]['available'] );
            $extra_leave = floatval( $this->entitlement_data[ $entitlement->id ]['extra_leave'] );

            if ( $available > 0 ) {
                $str = sprintf( '<span class="green tooltip" title="%s"> %s %s</span>', __( 'Available Leave', 'erp' ), erp_number_format_i18n( $available ), _n( 'day', 'days', $available + 1, 'erp' ) );
                if ( $extra_leave > 0 ) {
                    $str .= sprintf( '<span class="red tooltip" title="%s"> (%s %s)</span>', __( 'Extra Leave', 'erp' ), erp_number_format_i18n( $extra_leave ), _n( 'day', 'days', $extra_leave, 'erp' ) );
                }
            } elseif ( $extra_leave > 0 ) {
                $str = sprintf( '<span class="red tooltip" title="%s"> -%s %s</span>', __( 'Extra Leave', 'erp' ), erp_number_format_i18n( $extra_leave ), _n( 'day', 'days', $extra_leave, 'erp' ) );
            } elseif ( $available < 0 ) {
                $str = '<span> &mdash; </span>';
            }
        }

        return $str;
    }

    public function column_spent( $entitlement ) {
        $str = '';

        if ( ! array_key_exists( $entitlement->id, $this->entitlement_data ) ) {
            $this->entitlement_data[ $entitlement->id ] = erp_hr_leave_get_balance_for_single_entitlement( $entitlement->id );
        }

        if ( array_key_exists( $entitlement->id, $this->entitlement_data ) && ! is_wp_error( $this->entitlement_data[ $entitlement->id ] ) ) {
            $spent = floatval( $this->entitlement_data[ $entitlement->id ]['spent'] );
            $str   = $spent > 0 ? sprintf( '<span class="green">%s %s</span>', erp_number_format_i18n( $spent ), __( 'days', 'erp' ) ) : '-';
        }

        return $str;
    }

    /**
     * Render the designation name column
     *
     * @param object $item
     *
     * @return string
     */
    public function column_name( $entitlement ) {
        $actions           = [];
        $delete_url        = '';

        if ( erp_get_option( 'erp_debug_mode', 'erp_settings_general', 0 ) ) {
            $actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" data-user_id="%d" data-policy_id="%d" title="%s">%s</a>', $delete_url, $entitlement->id, $entitlement->user_id, $entitlement->id, __( 'Delete this item', 'erp' ), __( 'Delete', 'erp' ) );
        }

        return sprintf( '<a href="%3$s"><strong>%1$s</strong></a> %2$s', esc_html( $entitlement->employee_name ), $this->row_actions( $actions ), erp_hr_url_single_employee( $entitlement->user_id ) );
    }

    /**
     * Trigger current action
     *
     * @return string
     */
    public function current_action() {
        if ( isset( $_REQUEST['filter_entitlement'] ) ) {
            return 'filter_entitlement';
        }

        return parent::current_action();
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = [
            'name' => [ 'name', true ],
        ];

        return $sortable_columns;
    }

    /**
     * Set the bulk actions
     *
     * @return array
     */
    public function get_bulk_actions() {
        if ( erp_get_option( 'erp_debug_mode', 'erp_settings_general', 0 ) ) {
            $actions = [
                'entitlement_delete'  => __( 'Delete', 'erp' ),
            ];
        } else {
            $actions = [];
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
            '<input type="checkbox" name="entitlement_id[]" value="%s" />', $item->id
        );
    }

    /**
     * Prepare the class items
     *
     * @since 0.1
     * @since 1.2.0 Using `erp_get_financial_year_dates` for financial start and end dates
     *
     * @return void
     */
    public function prepare_items() {
        $columns               = $this->get_columns();
        $hidden                = [ ];
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = [ $columns, $hidden, $sortable ];

        $per_page              = 20;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page - 1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash(  $_GET['status'] ) ) : '2';
        $search                = ( isset( $_REQUEST['s'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : false;

        $args = [
            'offset'      => $offset,
            'number'      => $per_page,
            'search'      => $search,
            'emp_status'  => 'active',
        ];

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = 'u.display_name';
            $args['order']   = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) );
        }

        if ( ! empty( $_GET['financial_year'] ) ) {
            $args['year'] = absint( wp_unslash( $_GET['financial_year'] ) );
        } else {
            $current_f_year = erp_hr_get_financial_year_from_date();
            $args['year']   = null !== $current_f_year ? $current_f_year->id : '';
        }

        if ( ! empty( $_GET['leave_policy'] ) ) {
            $args['policy_id'] = absint( wp_unslash( $_GET['leave_policy'] ) );
        }

        if ( ! empty( $_GET['filter_employee_type'] ) ) {
            $args['employee_type'] = sanitize_text_field( wp_unslash( $_GET['filter_employee_type'] ) );
        }

        // get the items
        $items        = erp_hr_leave_get_entitlements( $args );
        $this->items  = $items['data'];

        $this->set_pagination_args( [
            'total_items' => $items['total'],
            'per_page'    => $per_page,
        ] );
    }
}
