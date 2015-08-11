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
     * @global WP_Query $wp_query
     * @global int $per_page
     * @param array $posts
     * @param int $level
     */
    public function display_rows( $departments = array(), $level = 0 ) {
        
        global $per_page;
        $departments = erp_array_to_object( \WeDevs\ERP\HRM\Models\Department::all()->toArray() );
      
        if ( empty( $departments ) )
            $departments = array();

        //if ( $this->hierarchical_display ) {
            $this->_display_rows_hierarchical( $departments, $this->get_pagenum(), $per_page );
        //} else {
         //   $this->_display_rows( $posts, $level );
        //}
    }

    /**
     * @global wpdb $wpdb
     * @param array $pages
     * @param int $pagenum
     * @param int $per_page
     * @return false|null
     */
    private function _display_rows_hierarchical( $pages, $pagenum = 1, $per_page = 20 ) {
       

        $level = 0;


        /*
         * Arrange pages into two parts: top level pages and children_pages
         * children_pages is two dimensional array, eg.
         * children_pages[10][] contains all sub-pages whose parent is 10.
         * It only takes O( N ) to arrange this and it takes O( 1 ) for subsequent lookup operations
         * If searching, ignore hierarchy and treat everything as top level
         */
        if ( empty( $_REQUEST['s'] ) ) {

            $top_level_pages = array();
            $children_pages = array();

            foreach ( $pages as $page ) {

                // Catch and repair bad pages.
                // if ( $page->post_parent == $page->ID ) {
                //     $page->post_parent = 0;
                //     $wpdb->update( $wpdb->posts, array( 'post_parent' => 0 ), array( 'ID' => $page->ID ) );
                //     clean_post_cache( $page );
                // }

                if ( 0 == $page->parent )
                    $top_level_pages[] = $page;
                else
                    $children_pages[ $page->parent ][] = $page;
            }

            $pages = &$top_level_pages;
        }
  
        $count = 0;
        $start = ( $pagenum - 1 ) * $per_page;
        $end = $start + $per_page;
        $to_display = array();

        foreach ( $pages as $page ) {
            if ( $count >= $end )
                break;

            if ( $count >= $start ) {
                $to_display[$page->id] = $level;
            }

            $count++;

            if ( isset( $children_pages ) )
                $this->_page_rows( $children_pages, $count, $page->id, $level + 1, $pagenum, $per_page, $to_display );
        }

        // If it is the last pagenum and there are orphaned pages, display them with paging as well.
        if ( isset( $children_pages ) && $count < $end ){
            foreach ( $children_pages as $orphans ){
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
     
        foreach ( $to_display as $page_id => $level ) {
           
            $this->single_row( $page_id, $level );
        }
    }

        /**
     * Given a top level page ID, display the nested hierarchy of sub-pages
     * together with paging support
     *
     * @since 3.1.0 (Standalone function exists since 2.6.0)
     * @since 4.2.0 Added the `$to_display` parameter.
     *
     * @param array $children_pages
     * @param int $count
     * @param int $parent
     * @param int $level
     * @param int $pagenum
     * @param int $per_page
     * @param array $to_display List of pages to be displayed. Passed by reference.
     */
    private function _page_rows( &$children_pages, &$count, $parent, $level, $pagenum, $per_page, &$to_display ) {

        if ( ! isset( $children_pages[$parent] ) )
            return;

        $start = ( $pagenum - 1 ) * $per_page;
        $end = $start + $per_page;
       
        foreach ( $children_pages[$parent] as $page ) {

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

            $this->_page_rows( $children_pages, $count, $page->id, $level + 1, $pagenum, $per_page, $to_display );
        }

        unset( $children_pages[$parent] ); //required in order to keep track of orphans
    }

    /**
     * @global string $mode
     * @param WP_Post $post
     * @param int $level
     */
    public function single_row( $post, $level = 0 ) {
      
        //  if ( $lead = $department->get_lead() ) {
        //     $lead_link = $lead->get_link();
        // } else {
        //     $lead_link = '-';
        // }
        $department = (object) \WeDevs\ERP\HRM\Models\Department::find($post)->toArray();
        
        echo '<tr>';
        foreach ( reset( $this->get_column_info() ) as $column_name => $column_title ) {
            switch ( $column_name ) {
                case 'name':
                        echo '<td>';
                    $pad = str_repeat( '&#8212; ', $level );
                    echo $pad.$department->title;
                    echo '</td>';
                case 'lead':
                     echo '<td>';
                    echo 'lead';
                    echo '</td>';
                case 'number_employee':
                     echo '<td>';
                    echo 'number of employee';
                    echo '</td>';
                default:
                    echo '<td>';
                    echo '';
                    echo '</td>';
            }
        }
        echo '</tr>';

    }

    /**
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    // function column_default( $department, $column_name ) {
        
    //     if ( $lead = $department->get_lead() ) {
    //         $lead_link = $lead->get_link();
    //     } else {
    //         $lead_link = '-';
    //     }

    //     switch ( $column_name ) {
    //         case 'name':
    //             echo $department->title;

    //         case 'lead':
    //             return $lead_link;

    //         default:
    //             return isset( $department->$column_name ) ? $department->$column_name : '';
    //     }
    // }

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
    // function column_name( $department ) {

    //     $padding    = str_repeat( '&#8212; ', $department->get_depth( $department, 5 ) );



    //     $actions           = array();
    //     $delete_url        = '';
    //     $actions['edit']   = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', $delete_url, $department->id, __( 'Edit this item', 'wp-erp' ), __( 'Edit', 'wp-erp' ) );
    //     $actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" title="%s">%s</a>', $delete_url, $department->id, __( 'Delete this item', 'wp-erp' ), __( 'Delete', 'wp-erp' ) );

   


    //     return sprintf( '<a href="#"><strong>%1$s</strong></a> %2$s', $padding.$department->title, $this->row_actions( $actions ) );
    // }

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

        global $per_page;
        $columns               = $this->get_columns();
        $hidden                = array( );
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $per_page       = 2;
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