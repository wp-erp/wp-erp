<?php
/**
 * List table class
 */
class Leave_Holiday_List_Table extends WP_List_Table {

    function __construct() {
        global $status, $page;

        parent::__construct( array(
            'singular' => 'holiday',
            'plural'   => 'holiday',
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
        _e( 'No policies found.', 'wp-erp' );
    }

    /**
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $holiday, $column_name ) {
       
        switch ( $column_name ) {
            case 'name':
                return $holiday->title;
            case 'start':
                return $holiday->start;

            case 'end':
                return $holiday->end;

            case 'description':
                return $holiday->description;
            default:
                return '';
        }
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
			'cb'          => '<input type="checkbox" />',
			'name'       => __( 'Title', 'wp-erp' ),
			'start'       => __( 'Start Days', 'wp-erp' ),
			'end'         => __( 'End Days', 'wp-erp' ),
			'description' => __( 'Description', 'wp-erp' )
        );

        return apply_filters( 'erp_hr_holiday_table_cols', $columns );
    }

    /**
     * Render the leave policy name column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_name( $holiday ) {

        $actions           = array();
        $delete_url        = '';
        $actions['edit']   = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', $delete_url, $holiday->id, __( 'Edit this item', 'wp-erp' ), __( 'Edit', 'wp-erp' ) );
        $actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" title="%s">%s</a>', $delete_url, $holiday->id, __( 'Delete this item', 'wp-erp' ), __( 'Delete', 'wp-erp' ) );

        return sprintf( '<a href="#" class="link" data-id="%3$s"><strong>%1$s</strong></a> %2$s', esc_html( $holiday->title ), $this->row_actions( $actions ), $holiday->id );
    }

    /**
     * Modify single row element
     *
     * @param  array $item
     *
     * @return void
     */
    function single_row( $item ) {
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
            'title' => array( 'title', true ),
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
            'trash'  => __( 'Move to Trash', 'wp-erp' ),
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
            '<input type="checkbox" name="holiday_id[]" value="%s" />', $item->id
        );
    }

    /**
     * Set the views
     *
     * @return array
     */
    public function get_views_() {
        $status_links   = array();
        $base_link      = admin_url( 'admin.php?page=erp-leave' );

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
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '2';

        // only ncessary because we have sample data
        $args = array(
            'offset' => $offset,
            'number' => $per_page,
        );

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order'] = $_REQUEST['order'] ;
        }

        $this->items  = erp_hr_get_holidays( $args );

        $this->set_pagination_args( array(
            'total_items' => erp_hr_count_holidays(),
            'per_page'    => $per_page
        ) );
    }

}

?>

<div class="wrap erp-hr-holiday-wrap">
    <h2><?php _e( 'Holiday', 'wp-erp' ); ?> <a href="#" id="erp-hr-new-holiday" class="add-new-h2"><?php _e( 'Add New', 'wp-erp' ); ?></a></h2>

    <div class="list-table-wrap">
        <div class="list-wrap-inner">

            <form method="post">
                <input type="hidden" name="page" value="erp-hr-designation">
                <?php
                $holiday = new Leave_Holiday_List_Table();
                $holiday->prepare_items();
                $holiday->views();

                $holiday->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->

</div>