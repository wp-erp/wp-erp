<?php
/**
 * List table class
 */
class Designation_List_Table extends WP_List_Table {

    function __construct() {
        global $status, $page;

        parent::__construct( array(
            'singular' => 'designation',
            'plural'   => 'designations',
            'ajax'     => false
        ) );

        $this->table_css();
    }

    function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', 'designation-list-table', $this->_args['plural'] );
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
     * Message to show if no designation found
     *
     * @return void
     */
    function no_items() {
        _e( 'No designation found.', 'wp-erp' );
    }

    /**
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $designation, $column_name ) {

        $designation = new \WeDevs\ERP\HRM\Designation( $designation );

        switch ( $column_name ) {
            case 'name':

            case 'number_employee':
                return $designation->num_of_employees();

            default:
                return isset( $designation->$column_name ) ? $designation->$column_name : '';
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
            'name'         => __( 'Title', 'wp-erp' ),
            'number_employee'   => __( 'No. of Employees', 'wp-erp' )
        );

        return apply_filters( 'erp_hr_designation_table_cols', $columns );
    }

    /**
     * Render the designation name column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_name( $designation ) {

        $actions           = array();
        $delete_url        = '';
        $actions['edit']   = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', $delete_url, $designation->id, __( 'Edit this item', 'wp-erp' ), __( 'Edit', 'wp-erp' ) );
        $actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" title="%s">%s</a>', $delete_url, $designation->id, __( 'Delete this item', 'wp-erp' ), __( 'Delete', 'wp-erp' ) );

        return sprintf( '<a href="#"><strong>%1$s</strong></a> %2$s', $designation->title, $this->row_actions( $actions ) );
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
            '<input type="checkbox" name="desig[]" value="%s" />', $item->id
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
        $hidden                = array( );
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

        $this->items  = erp_hr_get_designations( $args );

        $this->set_pagination_args( array(
            'total_items' => erp_hr_count_designation(),
            'per_page'    => $per_page
        ) );
    }

}

?>

<div class="wrap erp erp-hr-designation">

    <h2><?php _e( 'Designation', 'wp-erp' ); ?> <a href="#" id="erp-new-designation" class="add-new-h2"><?php _e( 'Add New', 'wp-erp' ); ?></a></h2>

    <div id="erp-desig-table-wrap">

        <div class="list-table-inner">

            <form method="post">
                <input type="hidden" name="page" value="erp-hr-designation">
                <?php
                $designation = new Designation_List_Table();
                $designation->prepare_items();
                $designation->views();

                $designation->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->

</div>