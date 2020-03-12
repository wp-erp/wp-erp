<?php
namespace WeDevs\ERP\HRM;

use WeDevs\ERP\HRM\Models\Financial_Year;

/**
 * List table class
 */
class Entitlement_List_Table extends \WP_List_Table {

    protected $entitlement_data = array();

    function __construct() {
        global $status, $page;

        parent::__construct( array(
            'singular' => 'entitlement',
            'plural'   => 'entitlements',
            'ajax'     => false
        ) );
    }

    function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', 'entitlement-list-table', $this->_args['plural'] );
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'            => '<input type="checkbox" />',
            'name'          => __( 'Employee Name', 'erp' ),
            'leave_policy'  => __( 'Leave Policy', 'erp' ),
            'valid_from'    => __( 'Valid From', 'erp' ),
            'valid_to'      => __( 'Valid To', 'erp' ),
            'days'          => __( 'Days', 'erp' ),
            //'scheduled'    => __( 'Scheduled', 'erp' ),
            'available'     => __( 'Available', 'erp' ),
            'extra'         => __( 'Extra Leaves', 'erp' ),
        );

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
    function extra_tablenav( $which ) {
        if ( $which != 'top' ) {
            return;
        }
        $entitlement_years = wp_list_pluck( Financial_Year::all(), 'fy_name', 'id' );

        if ( empty( $entitlement_years ) ) {
            return;
        }

        if ( ! empty( $_GET['financial_year'] ) ) {
            $selected = absint( wp_unslash( $_GET['financial_year'] ) );
        }
        else {
            $financial_year_dates = erp_get_financial_year_dates();
            $f_year_ids = get_financial_year_from_date_range( $financial_year_dates['start'], $financial_year_dates['end'] );
            $selected = is_array( $f_year_ids ) && ! empty( $f_year_ids ) ? $f_year_ids[0] : '';
        }
        ?>
            <div class="alignleft actions">
                <select name="financial_year">
                    <?php echo wp_kses( erp_html_generate_dropdown( $entitlement_years, $selected ), array(
                        'option' => array(
                            'value' => array(),
                            'selected' => array()
                        ),
                    ) ); ?>
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
    function no_items() {
        esc_html_e( 'No entitlement found.', 'erp' );
    }

    /**
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $entitlement, $column_name ) {
        $f_year = Financial_Year::find( $entitlement->f_year );
        $balance   = erp_hr_leave_get_balance( $entitlement->user_id );

        if ( isset( $balance[ $entitlement->trn_id ] ) ) {
            $scheduled = $balance[ $entitlement->trn_id ]['scheduled'];
            $available = $balance[ $entitlement->trn_id ]['available'];
        } else {
            $scheduled = '';
            $available = '';
        }

        switch ( $column_name ) {
            case 'leave_policy':
                return esc_html( $entitlement->policy_name );

            case 'valid_from':
                return erp_format_date( $f_year->start_date );

            case 'valid_to':
                return erp_format_date( $f_year->end_date );

            case 'days':
                return number_format_i18n( $entitlement->day_in );

            default:
                return isset( $entitlement->$column_name ) ? $entitlement->$column_name : '';
        }
    }

    function column_available( $entitlement ) {
        $str = '';

        if ( ! array_key_exists( $entitlement->id, $this->entitlement_data ) ) {
            $this->entitlement_data[ $entitlement->id ] = erp_hr_leave_get_balance_for_single_entitlement( $entitlement->id );
        }

        if ( array_key_exists( $entitlement->id, $this->entitlement_data ) && ! is_wp_error( $this->entitlement_data[ $entitlement->id ] ) ) {
            $str = sprintf( '<span class="green">%d %s</span>', number_format_i18n( $this->entitlement_data[ $entitlement->id ]['available'] ), __( 'days', 'erp' ) );
        }

        return $str;
    }

    function column_extra( $entitlement ) {
        $extra_leave = 0;

        if ( ! array_key_exists( $entitlement->id, $this->entitlement_data ) ) {
            $this->entitlement_data[ $entitlement->id ] = erp_hr_leave_get_balance_for_single_entitlement( $entitlement->id );
        }

        if ( array_key_exists( $entitlement->id, $this->entitlement_data ) && ! is_wp_error( $this->entitlement_data[ $entitlement->id ] ) ) {
            $extra_leave = $this->entitlement_data[ $entitlement->id ]['extra_leave'];
        }

        $class = 'green';
        if ( intval( $extra_leave ) > 0 ) {
            $class = 'red';
        }

        $str = sprintf( '<span class="%s">%d %s</span>', $class, number_format_i18n( $extra_leave ), __( 'days', 'erp' ) );

        return $str;
    }

    /**
     * Render the designation name column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_name( $entitlement ) {

        $actions           = array();
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
    function get_sortable_columns() {
        $sortable_columns = array(
            'name' => array( 'name', true ),
        );

        return $sortable_columns;
    }

    /**
     * Set the bulk actions
     *
     * @return array
     */
    function get_bulk_actions() {
        if ( erp_get_option( 'erp_debug_mode', 'erp_settings_general', 0 ) ) {
            $actions = array(
                'entitlement_delete'  => __( 'Delete', 'erp' ),
            );
        }
        else {
            $actions = array();
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
    function prepare_items() {
        $columns               = $this->get_columns();
        $hidden                = array( );
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $per_page              = 20;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page -1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash(  $_GET['status'] ) ) : '2';
        $search                = ( isset( $_REQUEST['s'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : false;

        $args = [
            'offset'      => $offset,
            'number'      => $per_page,
            'search'      => $search,
            'emp_status'  => 'active'
        ];

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = 'u.display_name';
            $args['order']   = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ;
        }

        if ( ! empty( $_GET['financial_year'] ) ) {
            $args['year'] = absint( wp_unslash( $_GET['financial_year'] ) );
        }
        else {
            // todo: get financial year id from date range
            $financial_year_dates = erp_get_financial_year_dates();
            $f_year_ids = get_financial_year_from_date_range( $financial_year_dates['start'], $financial_year_dates['end'] );
            $args['year'] = is_array( $f_year_ids ) && ! empty( $f_year_ids ) ? $f_year_ids[0] : '';
        }

        // get the items
        $items = erp_hr_leave_get_entitlements( $args );
        $this->items  = $items['data'];

        $this->set_pagination_args( array(
            'total_items' => $items['total'],
            'per_page'    => $per_page
        ) );
    }

}
