<?php
namespace WeDevs\ERP\HRM;

/**
 * List table class
 */
class Entitlement_List_Table extends \WP_List_Table {

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

    function extra_tablenav( $which ) {
        if ( $which != 'top' ) {
            return;
        }
        $filter_by_year = ( isset( $_REQUEST['filter_by_year'] ) ) ? $_REQUEST['filter_by_year'] : '';
        $date = \WeDevs\ERP\HRM\Models\Leave_Entitlement::select( 'to_date' )->distinct()->get()->toArray();
        if ( ! $date ) {
            return;
        }
        ?>
        <div class="alignleft actions">
            <label class="screen-reader-text" for="filter_by_year"><?php _e( 'Filter by Year', 'erp' ) ?></label>
            <select name="filter_by_year" id="filter_by_year">
                <?php foreach ( $date as $year ): ?>
                    <?php $year_val = date( 'Y', strtotime( $year['to_date'] ) ); ?>
                    <option value="<?php echo $year_val; ?>" <?php selected( $filter_by_year, $year_val ); ?>><?php echo $year_val; ?></option>
                <?php endforeach ?>
            </select>
            <?php
            submit_button( __( 'Filter' ), 'button', 'filter_entitlement', false );
        echo '</div>';
    }


    /**
     * Message to show if no entitlement found
     *
     * @return void
     */
    function no_items() {
        _e( 'No entitlement found.', 'erp' );
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
        $balance   = erp_hr_leave_get_balance( $entitlement->user_id );

        if ( isset( $balance[ $entitlement->policy_id ] ) ) {
            $scheduled = $balance[ $entitlement->policy_id ]['scheduled'];
            $available = $balance[ $entitlement->policy_id ]['entitlement'] - $balance[ $entitlement->policy_id ]['total'];
        } else {
            $scheduled = '';
            $available = '';
        }

        switch ( $column_name ) {
            case 'name':
                return sprintf( '<strong><a href="%s">%s</a></strong>', erp_hr_url_single_employee( $entitlement->user_id ), esc_html( $entitlement->employee_name ) );

            case 'leave_policy':
                return esc_html( $entitlement->policy_name );

            case 'valid_from':
                return erp_format_date( $entitlement->from_date );

            case 'valid_to':
                return erp_format_date( $entitlement->to_date );

            case 'days':
                return number_format_i18n( $entitlement->days );

            case 'scheduled':
                return $scheduled ? sprintf( __( '%d days', 'erp' ), number_format_i18n( $scheduled ) ) : '-';

            case 'available':
                if ( $available < 0 ) {
                    return sprintf( '<span class="red">%d %s</span>', number_format_i18n( $available ), __( 'days', 'erp' ) );
                } elseif ( $available > 0 ) {
                    return sprintf( '<span class="green">%d %s</span>', number_format_i18n( $available ), __( 'days', 'erp' ) );
                } elseif ( $available === 0 ) {
                    return sprintf( '<span class="gray">%d %s</span>', 0, __( 'days', 'erp' ) );
                } else {
                    return sprintf( '<span class="green">%d %s</span>', number_format_i18n( $entitlement->days ), __( 'days', 'erp' ) );
                }

            default:
                return isset( $entitlement->$column_name ) ? $entitlement->$column_name : '';
        }
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'           => '<input type="checkbox" />',
            'name'         => __( 'Employee Name', 'erp' ),
            'leave_policy' => __( 'Leave Policy', 'erp' ),
            'valid_from'   => __( 'Valid From', 'erp' ),
            'valid_to'     => __( 'Valid To', 'erp' ),
            'days'         => __( 'Days', 'erp' ),
            'scheduled'    => __( 'Scheduled', 'erp' ),
            'available'    => __( 'available', 'erp' )
        );

        return apply_filters( 'erp_hr_entitlement_table_cols', $columns );
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
            $actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" data-user_id="%d" data-policy_id="%d" title="%s">%s</a>', $delete_url, $entitlement->id, $entitlement->user_id, $entitlement->policy_id, __( 'Delete this item', 'erp' ), __( 'Delete', 'erp' ) );
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
        $actions = array(
            'entitlement_delete'  => __( 'Delete', 'erp' ),
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
            '<input type="checkbox" name="entitlement_id[]" value="%s" />', $item->id
        );
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
        $args = [
            'year'   => date( 'Y' ),
            'offset' => $offset,
            'number' => $per_page,
        ];

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = 'u.display_name';
            $args['order']   = $_REQUEST['order'] ;
        }

        if ( isset( $_REQUEST['filter_by_year'] ) && $_REQUEST['filter_by_year'] ) {
            $args['year'] = $_REQUEST['filter_by_year'];
        }

        $this->items  = erp_hr_leave_get_entitlements( $args );

        $this->set_pagination_args( array(
            'total_items' => erp_hr_leave_count_entitlements( date( 'Y' ) ),
            'per_page'    => $per_page
        ) );
    }
}
