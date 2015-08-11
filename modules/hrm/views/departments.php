<?php
/**
 * List table class
 */
class Deparment_List_Table extends WP_List_Table {

    function __construct() {
        global $status, $page;

        parent::__construct( array(
            'singular' => 'department',
            'plural'   => 'departments',
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
     * Message to show if no department found
     *
     * @return void
     */
    function no_items() {
        _e( 'No department found.', 'wp-erp' );
    }

    /**
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $department, $column_name ) {


        if ( $lead = $department->get_lead() ) {
            $lead_link = $lead->get_link();
        } else {
            $lead_link = '-';
        }

        switch ( $column_name ) {
            case 'name':
                echo $department->title;

            case 'lead':
                return $lead_link;

            case 'number_employee':
                return $department->num_of_employees();

            default:
                return isset( $department->$column_name ) ? $department->$column_name : '';
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
            'name'         => __( 'Department', 'wp-erp' ),
            'lead'  => __( 'Department Lead', 'wp-erp' ),
            'number_employee'   => __( 'No. of Employees', 'wp-erp' )
        );

        return apply_filters( 'erp_hr_department_table_cols', $columns );
    }

    /**
     * Render the employee name column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_name( $department ) {

        $padding    = str_repeat( '&#8212; ', $department->get_depth( $department, 5 ) );


        $actions           = array();
        $delete_url        = '';
        $actions['edit']   = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', $delete_url, $department->id, __( 'Edit this item', 'wp-erp' ), __( 'Edit', 'wp-erp' ) );
        $actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" title="%s">%s</a>', $delete_url, $department->id, __( 'Delete this item', 'wp-erp' ), __( 'Delete', 'wp-erp' ) );

        return sprintf( '<a href="#"><strong>%1$s</strong></a> %2$s', $padding.$department->title, $this->row_actions( $actions ) );
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
            '<input type="checkbox" name="dept[]" value="%s" />', $item->id
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

        $this->items  = erp_hr_get_departments( $args );

        $this->set_pagination_args( array(
            'total_items' => erp_hr_count_departments(),
            'per_page'    => $per_page
        ) );
    }

}

?>

<div class="wrap erp-hr-depts">

    <h2><?php _e( 'Departments', 'wp-erp' ); ?> <a href="#" id="erp-new-dept" class="add-new-h2"><?php _e( 'Add New', 'wp-erp' ); ?></a></h2>

    <div id="erp-dept-table-wrap">

        <div class="list-table-inner">

            <form method="post">
                <input type="hidden" name="page" value="erp-hr-depts">
                <?php
                $employee_table = new Deparment_List_Table();
                $employee_table->prepare_items();
                $employee_table->views();

                $employee_table->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->

</div>





<div class="wrap erp-hr-depts">

    <h2><?php _e( 'Departments', 'wp-erp' ); ?> <a href="#" id="erp-new-dept" class="add-new-h2"><?php _e( 'Add New', 'wp-erp' ); ?></a></h2>

    <form action="" method="get">
        <p class="search-box">
            <input type="hidden" name="page" value="erp-hr-depts">
            <label for="post-search-input" class="screen-reader-text">Search Departments:</label>
            <input type="search" value="<?php _admin_search_query(); ?>" name="s" id="post-search-input">
            <?php wp_nonce_field(); ?>
            <input type="submit" value="Search Department" class="button" id="search-submit">
        </p>
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
                <select name="action" id="bulk-action-selector-top">
                    <option value="-1" selected="selected">Bulk Actions</option>
                    <option value="trash">Move to Trash</option>
                </select>
                <input type="submit" name="" id="doaction" class="button action" value="Apply">
            </div>
        </div>

        <div id="erp-dept-table-wrap">

            <table class="wp-list-table widefat fixed department-list-table">
                <thead>
                    <tr>
                        <th scope="col" id="cb" class="manage-column column-cb check-column" style="">
                            <input id="cb-select-all-1" type="checkbox">
                        </th>
                        <th class="col-"><?php _e( 'Department', 'accounting' ); ?></th>
                        <th class="col-"><?php _e( 'Department Lead', 'accounting' ); ?></th>
                        <th class="col-"><?php _e( 'No. of Employees', 'accounting' ); ?></th>
                    </tr>
                </thead>

                <tfoot>
                    <tr>
                        <th scope="col" id="cb" class="manage-column column-cb check-column" style="">
                            <input id="cb-select-all-1" type="checkbox">
                        </th>
                        <th class="col-"><?php _e( 'Department', 'accounting' ); ?></th>
                        <th class="col-"><?php _e( 'Department Lead', 'accounting' ); ?></th>
                        <th class="col-"><?php _e( 'No. of Employees', 'accounting' ); ?></th>
                    </tr>
                </tfoot>

                <tbody id="the-list">
                    <?php
                    $args = [];
                    if ( isset( $_GET['s'] ) ) {
                        $args['s'] = $_GET['s'];
                    }

                    $departments = erp_hr_get_departments( $args );

                    if ( $departments ) {

                        $walker = new \WeDevs\ERP\HRM\Department_Walker();
                        $walker->walk( $departments, 5 );

                    } else {
                        ?>
                        <tr class="alternate no-rows">
                            <td colspan="4">
                                <?php _e( 'No departments found!', 'wp-erp' ); ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div><!-- #erp-dept-table-wrap -->

        <div class="tablenav bottom">
            <div class="alignleft actions bulkactions">
                <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
                <select name="action" id="bulk-action-selector-top">
                    <option value="-1" selected="selected">Bulk Actions</option>
                    <option value="trash">Move to Trash</option>
                </select>
                <input type="submit" name="" id="doaction" class="button action" value="Apply">
            </div>
        </div>
    </form>




























































</div>