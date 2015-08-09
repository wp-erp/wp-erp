<?php
/**
 * List table class
 */
class Employee_List_Table extends WP_List_Table {

    function __construct() {
        global $status, $page;

        parent::__construct( array(
            'singular' => 'employee',
            'plural'   => 'employees',
            'ajax'     => false
        ) );

        $this->table_css();
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
     * Message to show if no employee found
     *
     * @return void
     */
    function no_items() {
        _e( 'No employee found.', 'wp-erp' );
    }

    /**
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $employee, $column_name ) {

        switch ( $column_name ) {
            case 'designation':
                return $employee->get_job_title();

            case 'department':
                return $employee->get_department_title();

            case 'type':
                return $employee->get_type();

            case 'date_of_hire':
                return $employee->get_joined_date();

            default:
                return isset( $employee->$column_name ) ? $employee->$column_name : '';
        }
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    function get_sortable_columns() {
        $sortable_columns = array(
            'date' => array( 'created_on', true ),
            'days' => array( 'days', false ),
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
            'name'         => __( 'Employee Name', 'wp-erp' ),
            'designation'  => __( 'Designation', 'wp-erp' ),
            'department'   => __( 'Department', 'wp-erp' ),
            'type'         => __( 'Employment Type', 'wp-erp' ),
            'date_of_hire' => __( 'Joined', 'wp-erp' ),
        );

        return apply_filters( 'erp_hr_employee_table_cols', $columns );
    }

    /**
     * Render the employee name column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_name( $employee ) {
        $actions           = array();
        $delete_url        = '';
        $actions['edit']   = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', $delete_url, $employee->id, __( 'Edit this item', 'wp-erp' ), __( 'Edit', 'wp-erp' ) );
        $actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" title="%s">%s</a>', $delete_url, $employee->id, __( 'Delete this item', 'wp-erp' ), __( 'Delete', 'wp-erp' ) );

        return sprintf( '%4$s <a href="%3$s"><strong>%1$s</strong></a> %2$s', $employee->get_full_name(), $this->row_actions( $actions ), erp_hr_url_single_employee( $employee->id ), $employee->get_avatar() );
    }

    /**
     * Set the bulk actions
     *
     * @return array
     */
    function get_bulk_actions() {
        $actions = array(
            'email'  => __( 'Send Email', 'wp-erp' ),
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
            '<input type="checkbox" name="employee_id[]" value="%s" />', $item->id
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

        $per_page              = 2;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page -1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '2';

        // only ncessary because we have sample data
        $args = array(
            'offset' => $offset,
            'number' => $per_page,
        );

        $this->items  = erp_hr_get_employees( $args );

        $this->set_pagination_args( array(
            'total_items' => erp_hr_count_employees(),
            'per_page'    => $per_page
        ) );
    }

}

?>

<div class="wrap erp-hr-employees" id="wp-erp">

    <h2><?php _e( 'Employee', 'wp-erp' ); ?> <a href="#" id="erp-employee-new" class="add-new-h2"><?php _e( 'Add New', 'wp-erp' ); ?></a></h2>

    <div class="list-table-wrap">
        <div class="list-table-inner">

            <form method="post">
                <input type="hidden" name="page" value="erp-hr-employee">
                <?php
                $employee_table = new Employee_List_Table();
                $employee_table->prepare_items();
                $employee_table->views();

                $employee_table->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->

</div>