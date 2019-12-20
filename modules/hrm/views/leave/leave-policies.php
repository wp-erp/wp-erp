<?php
/**
 * List table class
 */
class Leave_Policies_List_Table extends WP_List_Table {

    function __construct() {
        global $status, $page;

        parent::__construct( array(
            'singular' => 'leave_policy',
            'plural'   => 'leave_policies',
            'ajax'     => false
        ) );

        $this->table_css();
    }

    /**
     * Get table classes
     *
     * @return array
     */
    function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', 'erp-leave-policy-list-table', $this->_args['plural'] );
    }
    /**
     * Table column width css
     *
     * @return void
     */
    function table_css() {
        echo '<style type="text/css">';
        echo '.wp-list-table .column-name img { float: left; margin-right: 10px; margin-top: 1px; }';
        echo '.wp-list-table .column-balance { width: 8%; }';
        echo '.wp-list-table .column-status { width: 8%; }';
        echo '.wp-list-table .column-comments { width: 25%; }';
        echo '</style>';
    }

    /**
     * Message to show if no policy found
     *
     * @return void
     */
    function no_items() {
        esc_html_e( 'No policies found.', 'erp' );
    }

    /**
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $leave_policy, $column_name ) {

        switch ( $column_name ) {
            case 'name':

            case 'leave_day':
                return number_format_i18n( $leave_policy->value, 0 );

            case 'calendar_color':
                return '<span class="leave-color" style="background-color: ' . $leave_policy->color . ' "></span>';

            default:
                return isset( $leave_policy->$column_name ) ? $leave_policy->$column_name : '';
        }
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'             => '<input type="checkbox" />',
            'name'           => __( 'Policy Name', 'erp' ),
            'leave_day'      => __( 'Leave Days', 'erp' ),
            'leave_day'      => __( 'Leave Days', 'erp' ),
            'calendar_color' => __( 'Calendar Color', 'erp' )
        );

        return apply_filters( 'erp_hr_leave_policy_table_cols', $columns );
    }

    /**
     * Render the leave policy name column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_name( $leave_policy ) {

        $actions           = array();
        $delete_url        = '';
        $actions['edit']   = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', $delete_url, $leave_policy->id, __( 'Edit this item', 'erp' ), __( 'Edit', 'erp' ) );
        $actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" title="%s">%s</a>', $delete_url, $leave_policy->id, __( 'Delete this item', 'erp' ), __( 'Delete', 'erp' ) );

        return sprintf( '<a href="#" class="link" data-id="%3$s"><strong>%1$s</strong></a> %2$s', esc_html( $leave_policy->name ), $this->row_actions( $actions ), $leave_policy->id );
    }

    /**
     * Modify single row element
     *
     * @since 0.1
     * @since 1.2.0 Remove time from effective date
     *
     * @param  array $item
     *
     * @return void
     */
    function single_row( $item ) {
        $item->effective_date = preg_replace( '/\s.+$/', '', $item->effective_date );
        ?>
            <tr data-json='<?php echo json_encode( $item ); ?>'>
                <?php $this->single_row_columns( $item ); ?>
            </tr>
        <?php
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
            'trash'  => __( 'Move to Trash', 'erp' ),
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
            '<input type="checkbox" name="policy_id[]" value="%s" />', $item->id
        );
    }

    /**
     * Set the views
     *
     * @return array
     */
    public function get_views_() {
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
        $hidden                = array();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $per_page              = 20;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page -1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '2';

        // only ncessary because we have sample data
        $args = array(
            'offset' => $offset,
            'number' => $per_page,
        );

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
            $args['order'] = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ;
        }

        $this->items  = erp_hr_leave_get_policies( $args );

        $this->set_pagination_args( array(
            'total_items' => erp_hr_count_leave_policies(),
            'per_page'    => $per_page
        ) );
    }

}

?>

<div class="wrap erp-hr-leave-policy">
    <h2><?php esc_html_e( 'Leave Policies', 'erp' ); ?> <a href="#" id="erp-leave-policy-new" class="add-new-h2"><?php esc_html_e( 'Add New', 'erp' ); ?></a></h2>

    <div class="list-table-wrap">
        <div class="list-wrap-inner">

            <form method="post">
                <input type="hidden" name="page" value="erp-hr-policies">
                <?php
                $leave_policy = new Leave_Policies_List_Table();
                $leave_policy->prepare_items();
                $leave_policy->views();

                $leave_policy->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->

</div>
