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
     * Display Row
     *
     * @param array $departments
     * @param int $level
     *
     * @return void
     */
    public function display_rows( $departments = array(), $level = 0 ) {

        global $per_page;
        $results = new \WeDevs\ERP\HRM\Models\Department();
        $departments = erp_array_to_object( $results->orderby('id', 'desc')->get()->toArray() );
        $this->_display_rows_hierarchical( $departments, $this->get_pagenum(), $per_page );
    }

    /**
     * Display Row hierarchical
     *
     * @param array departments
     * @param integer $pagenum
     * @param integer $per_page
     *
     * @return void
     */
    private function _display_rows_hierarchical( $departments, $pagenum = 1, $per_page = 20 ) {

        $level = 0;

        if ( empty( $_REQUEST['s'] ) ) {

            $top_level_departments = array();
            $children_departments = array();

            foreach ( $departments as $page ) {

                if ( 0 == $page->parent )
                    $top_level_departments[] = $page;
                else
                    $children_departments[ $page->parent ][] = $page;
            }

            $departments = &$top_level_departments;
        }

        $count = 0;
        $start = ( $pagenum - 1 ) * $per_page;
        $end = $start + $per_page;
        $to_display = array();

        foreach ( $departments as $page ) {
            if ( $count >= $end )
                break;

            if ( $count >= $start ) {
                $to_display[$page->id] = $level;
            }

            $count++;

            if ( isset( $children_departments ) )
                $this->_page_rows( $children_departments, $count, $page->id, $level + 1, $pagenum, $per_page, $to_display );
        }

        // If it is the last pagenum and there are orphaned departments, display them with paging as well.
        if ( isset( $children_departments ) && $count < $end ){
            foreach ( $children_departments as $orphans ){
                foreach ( $orphans as $op ) {
                    if ( $count >= $end )
                        break;

                    if ( $count >= $start ) {
                        $to_display[$op->ID] = 0;
                    }

                    $count++;
                }
            }
        }

        foreach ( $to_display as $department_id => $level ) {

            $this->single_row( $department_id, $level );
        }
    }

    /**
     * Single Page row
     *
     * @param array $children_departments
     * @param integer $count
     * @param integer $parent
     * @param integer $level
     * @param integer $pagenum
     * @param integer $per_page
     * @param array $to_display List of pages to be displayed. Passed by reference.
     *
     * @return void
     */
    private function _page_rows( &$children_departments, &$count, $parent, $level, $pagenum, $per_page, &$to_display ) {

        if ( ! isset( $children_departments[$parent] ) )
            return;

        $start = ( $pagenum - 1 ) * $per_page;
        $end = $start + $per_page;

        foreach ( $children_departments[$parent] as $page ) {

            if ( $count >= $end )
                break;

            // If the page starts in a subtree, print the parents.
            if ( $count == $start && $page->parent > 0 ) {
                $my_parents = array();
                $my_parent = $page->parent;
                while ( $my_parent ) {
                    // Get the ID from the list or the attribute if my_parent is an object
                    $parent_id = $my_parent;
                    if ( is_object( $my_parent ) ) {
                        $parent_id = $my_parent->id;
                    }

                    $my_parent = (object) \WeDevs\ERP\HRM\Models\Department::find($parent_id)->toArray();//get_post( $parent_id );
                    $my_parents[] = $my_parent;
                    if ( !$my_parent->parent )
                        break;
                    $my_parent = $my_parent->parent;
                }
                $num_parents = count( $my_parents );
                while ( $my_parent = array_pop( $my_parents ) ) {
                    $to_display[$my_parent->id] = $level - $num_parents;
                    $num_parents--;
                }
            }

            if ( $count >= $start ) {
                $to_display[$page->id] = $level;
            }

            $count++;

            $this->_page_rows( $children_departments, $count, $page->id, $level + 1, $pagenum, $per_page, $to_display );
        }

        unset( $children_departments[$parent] ); //required in order to keep track of orphans
    }

    /**
     * Render Single row
     *
     * @param init $department_id
     * @param integer $level
     *
     * @return void [html]
     */
    public function single_row( $department_id, $level = 0 ) {

        $department = new \WeDevs\ERP\HRM\Department( $department_id );

        echo '<tr>';
        foreach ( reset( $this->get_column_info() ) as $column_name => $column_title ) {
            switch ( $column_name ) {
                case 'cb':
                    ?>
                    <th scope="row" class="check-column">

                        <label class="screen-reader-text" for="cb-select-<?php the_ID(); ?>"><?php printf( __( 'Select %s' ), $department->title ); ?></label>
                        <input id="cb-select-<?php the_ID(); ?>" type="checkbox" name="department_id[]" value="<?php echo $department->id; ?>" />
                        <div class="locked-indicator"></div>

                    </th>
                    <?php
                    break;

                case 'name':
                    echo '<td>';
                        $pad = str_repeat( '&#8212; ', $level );

                        $actions           = array();
                        $delete_url        = '';
                        $actions['edit']   = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', $delete_url, $department->id, __( 'Edit this item', 'wp-erp' ), __( 'Edit', 'wp-erp' ) );
                        $actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" title="%s">%s</a>', $delete_url, $department->id, __( 'Delete this item', 'wp-erp' ), __( 'Delete', 'wp-erp' ) );

                        printf( '<a href="#"><strong>%1$s %2$s</strong></a> %3$s', $pad, $department->title, $this->row_actions( $actions ) );
                    echo '</td>';
                    break;

                case 'lead':
                    echo '<td>';
                        if ( $new_lead = $department->get_lead() ) {
                             echo $new_lead->get_link();
                        } else {
                           echo '-';
                        }
                    echo '</td>';
                    break;

                case 'number_employee':
                    echo '<td>';
                        echo $department->num_of_employees();
                    echo '</td>';
                    break;

                default:
                    echo '<td>';
                        echo '';
                    echo '</td>';
                    break;
            }
        }
        echo '</tr>';

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
     * Prepare the class items
     *
     * @return void
     */
    function prepare_items() {

        global $per_page;
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

    <h2><?php _e( 'Departments', 'wp-erp' ); ?> <a href="#" id="erp-new-dept" class="add-new-h2" data-single="1"><?php _e( 'Add New', 'wp-erp' ); ?></a></h2>

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