<?php
namespace WeDevs\ERP\CRM;

/**
 * Contact List table class
 *
 * @package weDevs|wperp
 */
class Contact_List_Table extends \WP_List_Table {

    private $counts = array();

    function __construct() {
        global $status, $page;

        parent::__construct( array(
            'singular' => 'contact',
            'plural'   => 'contacts',
            'ajax'     => false
        ) );
    }

    /**
     * Render extra filtering option in
     * top of the table
     *
     * @since 0.1
     *
     * @param  string $which
     *
     * @return void
     */
    function extra_tablenav( $which ) {
        if ( $which != 'top' ) {
            return;
        }

        // $selected_desingnation = ( isset( $_GET['filter_designation'] ) ) ? $_GET['filter_designation'] : 0;
        // $selected_department   = ( isset( $_GET['filter_department'] ) ) ? $_GET['filter_department'] : 0;
        // $selected_type   = ( isset( $_GET['filter_employment_type'] ) ) ? $_GET['filter_employment_type'] : '';
        ?>
        <div class="alignleft actions">

            <label class="screen-reader-text" for="filter_contact"><?php _e( 'Filter by Contact', 'wp-erp' ) ?></label>
            <select name="filter_contact" id="filter_contact">
                <option value="">Test</option>
                 <option value="">Test3</option>
                 <option value="">Test4</option>
            </select>

            <?php
            submit_button( __( 'Filter' ), 'button', 'filter_employee', false );
        echo '</div>';
    }

    /**
     * Message to show if no contacts found
     *
     * @return void
     */
    function no_items() {
        _e( 'No contact found.', 'wp-erp' );
    }

    /**
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $contact, $column_name ) {

        switch ( $column_name ) {
            case 'email':
                return 'contact@gmail.com';

            case 'phone_number':
                return '+8801920241162';

            case 'life_statges':
                return 'Lead';

            case 'created_at':
                return date( 'Y-m-d' );

            default:
                return isset( $contact->$column_name ) ? $contact->$column_name : '';
        }
    }

    public function current_action() {

        // if ( isset( $_REQUEST['filter_employee'] ) ) {
        //     return 'filter_employee';
        // }

        if ( isset( $_REQUEST['contact_search'] ) ) {
            return 'contact_search';
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
            'name' => array( 'name', false ),
            'created_at' => array( 'created_at', false ),
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
            'cb'           => '<input type="checkbox" />',
            'name'         => __( 'Contact Name', 'wp-erp' ),
            'email'        => __( 'Email', 'wp-erp' ),
            'phone_number' => __( 'Phone', 'wp-erp' ),
            'life_statges' => __( 'Type', 'wp-erp' ),
            'created_at'   => __( 'Created at', 'wp-erp' ),
        );

        return apply_filters( 'erp_hr_contact_table_cols', $columns );
    }

    /**
     * Render the employee name column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_name( $contact ) {
        $actions           = array();
        $delete_url        = '';

        return sprintf( '<a href="%3$s"><strong>%1$s</strong></a> %2$s', 'Sabbir Ahmed', $this->row_actions( $actions ), admin_url( 'admin.php' ) );
    }

    /**
     * Set the bulk actions
     *
     * @return array
     */
    function get_bulk_actions() {
        $actions = array(
            'email'  => __( 'Send Email', 'wp-erp' ),
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
     * @param  object  $item
     *
     * @return string
     */
    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="contact_id[]" value="%s" />', $item->id
        );
    }

    /**
     * Set the views
     *
     * @return array
     */
    public function get_views() {
        $status_links   = array();
        $base_link      = admin_url( 'admin.php?page=erp-hr-employee' );

        $status_links[ 'trash' ] = sprintf( '<a href="%s" class="status-trash">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => 'trash' ), $base_link ), __( 'Trash', 'wp-erp' ), erp_hr_count_trashed_employees() );

        return $status_links;
    }

    public function search_box( $text, $input_id ) {
        if ( empty( $_REQUEST['s'] ) && !$this->has_items() )
            return;

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

        if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
            echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
        }

        if ( ! empty( $_REQUEST['detached'] ) ) {
            echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
        }
        ?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
            <input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
            <?php submit_button( $text, 'button', 'employee_search', false, array( 'id' => 'search-submit' ) ); ?>
        </p>
        <?php
    }

    /**
     * Prepare the class items
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
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '2';

        // only ncessary because we have sample data
        $args = array(
            'offset' => $offset,
            'number' => $per_page,
        );

        if ( isset( $_REQUEST['s'] ) && !empty( $_REQUEST['s'] ) ) {
            $args['s'] = $_REQUEST['s'];
        }

        if ( isset( $_REQUEST['orderby'] ) && !empty( $_REQUEST['orderby'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
        }

        if ( isset( $_REQUEST['status'] ) && !empty( $_REQUEST['status'] ) ) {
            if ( $_REQUEST['status'] != 'all' ) {
                $args['status'] = $_REQUEST['status'];
            }
        }

        if ( isset( $_REQUEST['order'] ) && !empty( $_REQUEST['order'] ) ) {
            $args['order'] = $_REQUEST['order'];
        }


        // $this->counts = erp_get_peoples_count();
        $this->items  = erp_get_peoples( $args );

        $this->set_pagination_args( array(
            'total_items' => erp_get_peoples_count(),
            'per_page'    => $per_page
        ) );
    }

}