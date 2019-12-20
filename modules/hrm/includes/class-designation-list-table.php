<?php
namespace WeDevs\ERP\HRM;

/**
 * List table class
 */
class Designation_List_Table extends \WP_List_Table {


    function __construct() {
        global $status, $page;

        parent::__construct( array(
            'singular' => 'designation',
            'plural'   => 'designations',
            'ajax'     => false
        ) );
    }

    function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', 'designation-list-table', $this->_args['plural'] );
    }

    /**
     * Message to show if no designation found
     *
     * @return void
     */
    function no_items() {
        esc_html_e( 'No designation found.', 'erp' );
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
            'name'         => __( 'Title', 'erp' ),
            'number_employee'   => __( 'No. of Employees', 'erp' )
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
        $link_to_employee  = add_query_arg( array( 'page'=>'erp-hr', 'section' => 'employee', 'filter_designation' => $designation->id ), admin_url( 'admin.php' ) );
        $actions['edit']   = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', $delete_url, $designation->id, __( 'Edit this item', 'erp' ), __( 'Edit', 'erp' ) );
        $actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" title="%s">%s</a>', $delete_url, $designation->id, __( 'Delete this item', 'erp' ), __( 'Delete', 'erp' ) );

        return sprintf( '<a href="%3$s"><strong>%1$s</strong></a> %2$s', $designation->title, $this->row_actions( $actions ), $link_to_employee );
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
            'designation_delete'  => __( 'Delete', 'erp' ),
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
        $hidden                = array( );
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
            $args['orderby'] = 'title';
            $args['order'] = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ;
        }

        $this->items  = erp_hr_get_designations( $args );

        $this->set_pagination_args( array(
            'total_items' => erp_hr_count_designation( $args ),
            'per_page'    => $per_page
        ) );
    }

}
