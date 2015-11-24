<?php
namespace WeDevs\ERP\CRM;

/**
 * Customer List table class
 *
 * @package weDevs|wperp
 */
class Customer_List_Table extends \WP_List_Table {

    private $counts = array();
    private $page_status = '';

    function __construct() {
        global $status, $page;

        parent::__construct( array(
            'singular' => 'customer',
            'plural'   => 'customers',
            'ajax'     => false
        ) );
    }

    /**
     * Render extra filtering option in
     * top of the table
     *
     * @since 1.0
     *
     * @param  string $which
     *
     * @return void
     */
    function extra_tablenav( $which ) {
        if ( $which != 'top' ) {
            return;
        }
        $selected_life_stage = ( isset( $_GET['filter_life_stage'] ) ) ? $_GET['filter_life_stage'] : '';
        ?>
        <div class="alignleft actions">
            <label class="screen-reader-text" for="filter_life_stage"><?php _e( 'Filter by Life Stage', 'wp-erp' ) ?></label>
            <select name="filter_life_stage" id="filter_life_stage">
                <?php echo erp_crm_get_life_statges_dropdown( [ '' => __('--Select--', 'wp-erp' ) ], $selected_life_stage ); ?>
            </select>
            <?php
            submit_button( __( 'Filter' ), 'button', 'filter_customer', false );
        echo '</div>';
    }

    /**
     * Message to show if no contacts found
     *
     * @since 1.0
     *
     * @return void
     */
    function no_items() {
        _e( 'No customer found.', 'wp-erp' );
    }

    /**
     * Default column values if no callback found
     *
     * @since 1.0
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $customer, $column_name ) {

        $life_stages = erp_crm_get_life_statges_dropdown_raw();
        $life_stage  = erp_people_get_meta( $customer->id, 'life_stage', true );

        switch ( $column_name ) {
            case 'email':
                return $customer->email;

            case 'phone_number':
                return $customer->phone;

            case 'life_stages':
                return isset( $life_stages[$life_stage] ) ? $life_stages[$life_stage] : '-';

            case 'created':
                return erp_format_date( $customer->created );

            default:
                return isset( $customer->$column_name ) ? $customer->$column_name : '';
        }
    }

    /**
     * Render current trggier bulk action
     *
     * @since 1.0
     *
     * @return string [type of filter]
     */
    public function current_action() {

        if ( isset( $_REQUEST['filter_life_stage'] ) ) {
            return 'filter_life_stage';
        }

        if ( isset( $_REQUEST['customer_search'] ) ) {
            return 'customer_search';
        }

        return parent::current_action();
    }

    /**
     * Get sortable columns
     *
     * @since 1.0
     *
     * @return array
     */
    function get_sortable_columns() {
        $sortable_columns = array(
            'created' => array( 'created', false ),
        );

        return $sortable_columns;
    }

    /**
     * Get the column names
     *
     * @since 1.0
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'           => '<input type="checkbox" />',
            'name'         => __( 'Customer Name', 'wp-erp' ),
            'email'        => __( 'Email', 'wp-erp' ),
            'phone_number' => __( 'Phone', 'wp-erp' ),
            'life_stages'  => __( 'Life Stage', 'wp-erp' ),
            'created'      => __( 'Created at', 'wp-erp' ),
        );

        return apply_filters( 'erp_hr_customer_table_cols', $columns );
    }

    /**
     * Render the customer name column
     *
     * @since 1.0
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_name( $customer ) {
        $customer = new \WeDevs\ERP\CRM\Customer( intval( $customer->id ) );

        $actions           = array();
        $delete_url        = '';
        $view_url          = $customer->get_details_url();
        $data_hard         = ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] == 'trash' ) ? 1 : 0;
        $delete_text       = ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] == 'trash' ) ? __( 'Permanent Delete', 'wp-erp' ) : __( 'Delete', 'wp-erp' );
        $customer_name     = $customer->first_name .' '. $customer->last_name;

        $actions['edit']   = sprintf( '<a href="%s" data-id="%d"  title="%s">%s</a>', $delete_url, $customer->id, __( 'Edit this customer', 'wp-erp' ), __( 'Edit', 'wp-erp' ) );
        $actions['view']   = sprintf( '<a href="%s" title="%s">%s</a>', $view_url, __( 'View this customer', 'wp-erp' ), __( 'View', 'wp-erp' ) );
        $actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" data-hard=%d title="%s">%s</a>', $delete_url, $customer->id, $data_hard, __( 'Delete this item', 'wp-erp' ), $delete_text );

        if ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] == 'trash' ) {
            $actions['restore'] = sprintf( '<a href="%s" class="restoreCustomer" data-id="%d" title="%s">%s</a>', $delete_url, $customer->id, __( 'Restore this item', 'wp-erp' ), __( 'Restore', 'wp-erp' ) );
        }

        return sprintf( '%4$s <a href="%3$s"><strong>%1$s</strong></a> %2$s', $customer->get_full_name(), $this->row_actions( $actions ), $customer->get_details_url(), $customer->get_avatar() );
    }

    /**
     * Set the bulk actions
     *
     * @since 1.0
     *
     * @return array
     */
    function get_bulk_actions() {
        $actions = array(
            // 'email'  => __( 'Send Email', 'wp-erp' ),
            'delete'  => __( 'Move to Trash', 'wp-erp' ),
        );

        if ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] == 'trash' ) {
            unset( $actions['delete'] );

            $actions['permanent_delete'] = __( 'Permanent Delete', 'wp-erp' );
            $actions['restore'] = __( 'Restore', 'wp-erp' );
        }

        return $actions;
    }

    /**
     * Render the checkbox column
     *
     * @since 1.0
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="customer_id[]" value="%s" />', $item->id
        );
    }

    /**
     * Set the filter listing views
     *
     * @since 1.0
     *
     * @return array
     */
    public function get_views() {
        $status_links   = array();
        $base_link      = admin_url( 'admin.php?page=erp-sales-customers' );

        foreach ( $this->counts as $key => $value ) {
            $class = ( $key == $this->page_status ) ? 'current' : 'status-' . $key;
            $status_links[ $key ] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => $key ), $base_link ), $class, $value['label'], $value['count'] );
        }

        $status_links[ 'trash' ] = sprintf( '<a href="%s" class="status-trash">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => 'trash' ), $base_link ), __( 'Trash', 'wp-erp' ), erp_crm_count_trashed_customers() );

        return $status_links;
    }

    /**
     * Search form for lsit table
     *
     * @since 1.0
     *
     * @param  string $text
     * @param  string $input_id
     *
     * @return void
     */
    public function search_box( $text, $input_id ) {
        if ( empty( $_REQUEST['s'] ) && !$this->has_items() ) {
            return;
        }

        $input_id = $input_id . '-search-input';

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
        }

        if ( ! empty( $_REQUEST['order'] ) ) {
            echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
        }

        if ( ! empty( $_REQUEST['status'] ) ) {
            echo '<input type="hidden" name="status" value="' . esc_attr( $_REQUEST['status'] ) . '" />';
        }

        ?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
            <input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
            <?php submit_button( $text, 'button', 'customer_search', false, array( 'id' => 'search-submit' ) ); ?>
        </p>
        <?php
    }

    /**
     * Prepare the class items
     *
     * @since 1.0
     *
     * @return void
     */
    function prepare_items() {
        $columns               = $this->get_columns();
        $hidden                = array();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $per_page              = 20;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page -1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all';

        // only ncessary because we have sample data
        $args = array(
            'offset' => $offset,
            'number' => $per_page,
        );

        // Filter for serach
        if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {
            $args['s'] = $_REQUEST['s'];
        }

        // Filter for order by
        if ( isset( $_REQUEST['orderby'] ) && ! empty( $_REQUEST['orderby'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
        }

        // Filter for order
        if ( isset( $_REQUEST['order'] ) && !empty( $_REQUEST['order'] ) ) {
            $args['order'] = $_REQUEST['order'];
        }

        // Filter for cusotmer life stage
        if ( isset( $_REQUEST['status'] ) && ! empty( $_REQUEST['status'] ) ) {
            if ( $_REQUEST['status'] != 'all' ) {
                if ( $_REQUEST['status'] == 'trash' ) {
                    $args['trashed'] = true;
                } else {
                    $args['type'] = $_REQUEST['status'];
                }
            }
        }

        // Filter by life stages
        if ( isset( $_REQUEST['filter_life_stage'] ) && $_REQUEST['filter_life_stage'] ) {
            $args['meta_query'] = [
                'meta_key' => 'life_stage',
                'meta_value' => $_REQUEST['filter_life_stage']
            ]; ;
        }

        // Total counting for customer type filter
        $this->counts = erp_crm_customer_get_status_count();

        // Prepare all item after all filters
        $this->items  = erp_get_peoples( $args );

        // Render total customer according to above filter
        $args['count'] = true;
        $total_items = erp_get_peoples( $args );

        // Set pagination according to filter
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ) );
    }

}
