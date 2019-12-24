<?php
namespace WeDevs\ERP\HRM;

use user_switching ;

/**
 * List table class
 */
class Employee_List_Table extends \WP_List_Table {

    private $counts = array();
    private $page_status = '';

    function __construct() {
        global $status, $page, $page_status;

        parent::__construct( array(
            'singular' => 'employee',
            'plural'   => 'employees',
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

        $selected_desingnation = ( isset( $_GET['filter_designation'] ) ) ? sanitize_text_field( wp_unslash( $_GET['filter_designation'] ) ) : 0;
        $selected_department   = ( isset( $_GET['filter_department'] ) ) ? sanitize_text_field( wp_unslash( $_GET['filter_department'] ) ) : 0;
        $selected_type   = ( isset( $_GET['filter_employment_type'] ) ) ? sanitize_text_field( wp_unslash( $_GET['filter_employment_type'] ) ) : '';
        ?>
        <div class="alignleft actions">

            <label class="screen-reader-text" for="new_role"><?php esc_html_e( 'Filter by Designation', 'erp' ) ?></label>
            <select name="filter_designation" id="filter_designation">
                <?php echo wp_kses( erp_hr_get_designation_dropdown( $selected_desingnation ), array(
                    'option' => array(
                        'value' => array(),
                        'selected' => array()
                    )
                ) ); ?>
            </select>

            <label class="screen-reader-text" for="new_role"><?php esc_html_e( 'Filter by Designation', 'erp' ) ?></label>
            <select name="filter_department" id="filter_department">
                <?php echo wp_kses( erp_hr_get_departments_dropdown( $selected_department ), array(
                    'option' => array(
                        'value' => array(),
                        'selected' => array()
                    ),
                ) ); ?>
            </select>

            <label class="screen-reader-text" for="new_role"><?php esc_html_e( 'Filter by Employment Type', 'erp' ) ?></label>
            <select name="filter_employment_type" id="filter_employment_type">
                <option value="-1"><?php esc_html_e( '- Select Employment Type -', 'erp' ) ?></option>
                <?php
                    $types = erp_hr_get_employee_types();

                    foreach ( $types as $key => $title ) {
                        echo sprintf( "<option value='%s'%s>%s</option>\n", esc_html( $key ), selected( $selected_type, esc_html( $key ), false ), esc_html( $title ) );
                    }
                ?>
            </select>

            <?php
            submit_button( __( 'Filter' ), 'button', 'filter_employee', false );
        echo '</div>';
    }

    /**
     * Message to show if no employee found
     *
     * @return void
     */
    function no_items() {
        esc_html_e( 'No employee found.', 'erp' );
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
                return $employee->get_designation('view');

            case 'department':
                return $employee->get_department('view');

            case 'type':
                return $employee->get_type('view');

            case 'date_of_hire':
                return $employee->get_joined_date();

            case 'status':
                return $this->get_employee_status_styled_text( $employee->status );

            default:
                return isset( $employee->$column_name ) ? $employee->$column_name : '';
        }
    }

    public function current_action() {

        if ( isset( $_REQUEST['filter_employee'] ) ) {
            return 'filter_employee';
        }

        if ( isset( $_REQUEST['employee_search'] ) ) {
            return 'employee_search';
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
            'name'         => array( 'employee_name', false ),
            'date_of_hire' => array( 'hiring_date', false ),
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
            'name'         => __( 'Employee Name', 'erp' ),
            'designation'  => __( 'Designation', 'erp' ),
            'department'   => __( 'Department', 'erp' ),
            'type'         => __( 'Employment Type', 'erp' ),
            'date_of_hire' => __( 'Joined', 'erp' ),
            'status'       => __( 'Status', 'erp' ),
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
        $actions     = array();
        $delete_url  = '';
        $data_hard   = ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] == 'trash' ) ? 1 : 0;
        $delete_text = ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] == 'trash' ) ? __( 'Permanent Delete', 'erp' ) : __( 'Delete', 'erp' );
        $get_wp_user = get_user_by( 'id', $employee->get_user_id() );

        if ( current_user_can( 'erp_edit_employee', $employee->get_user_id() ) ) {
            $actions['edit']   =  sprintf( '<a href="%s" data-id="%d"  title="%s">%s</a>', $delete_url, $employee->get_user_id(), __( 'Edit this item', 'erp' ), __( 'Edit', 'erp' ) );
        }

        if ( current_user_can( 'erp_delete_employee' ) ) {
            $actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" data-hard=%d title="%s">%s</a>', $delete_url, $employee->get_user_id(), $data_hard, __( 'Delete this item', 'erp' ), $delete_text );
        }

        if ( class_exists( 'user_switching' ) ) {
            if ( current_user_can( 'erp_edit_employee' ) ) {
                $actions['switch_to_emp'] = sprintf( '<a href="%s" class="" data-id="%d" data-hard=%d title="%s">%s</a>', user_switching::switch_to_url( $get_wp_user ), $employee->get_user_id(), $data_hard, __( 'Switch to', 'erp' ),  __( 'Switch to', 'erp' ) );
            }
        }

        if ( $data_hard ) {
            $actions['restore'] = sprintf( '<a href="%s" class="submitrestore" data-id="%d" title="%s">%s</a>', $delete_url, $employee->get_user_id(),  __( 'Restore this item', 'erp' ), __( 'Restore', 'erp' ) );
        }
        $actions = apply_filters( 'erp_employee_row_actions', $actions, $employee->get_user_id() );
        return sprintf( '%4$s <a href="%3$s"><strong>%1$s</strong></a> %2$s', $employee->get_full_name(), $this->row_actions( $actions ), erp_hr_url_single_employee( $employee->get_user_id() ), $employee->get_avatar() );
    }

    /**
     * Set the bulk actions
     *
     * @return array
     */
    function get_bulk_actions() {
        $actions = [];

        if ( ! current_user_can( erp_hr_get_manager_role() ) ) {
            return $actions;
        }

        $actions = [
            'delete'  => __( 'Move to Trash', 'erp' ),
        ];

        if ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] == 'trash' ) {
            unset( $actions['delete'] );

            $actions['permanent_delete'] = __( 'Permanent Delete', 'erp' );
            $actions['restore'] = __( 'Restore', 'erp' );
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
            '<input type="checkbox" name="employee_id[]" value="%s" />', $item->id
        );
    }

    /**
     * Render the status column
     *
     * @param  string  $status
     *
     * @return string
     */
    function get_employee_status_styled_text( $status ) {
        switch ( $status ) {
            case 'active':
                $status = '<span class="status-active">Active</span>';
                break;

            case 'inactive':
                $status = '<span class="status-inactive">Inactive</span>';
                break;

            case 'terminated':
                $status = '<span class="status-terminated">Terminated</span>';
                break;

            case 'deceased':
                $status = '<span class="status-deceased">Deceased</span>';
                break;

            case 'resigned':
                $status = '<span class="status-resigned">Resigned</span>';
                break;
        }

        return $status;
    }

    /**
     * Set the views
     *
     * @return array
     */
    public function get_views() {
        $status_links   = array();
        $base_link      = admin_url( 'admin.php?page=erp-hr&section=employee&orderby=employee_name&order=asc' );

        foreach ($this->counts as $key => $value) {
            $class = ( $key == $this->page_status ) ? 'current' : 'status-' . $key;
            $status_links[ $key ] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => $key ), $base_link ), $class, $value['label'], $value['count'] );
        }

        $status_links[ 'trash' ] = sprintf( '<a href="%s" class="status-trash">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => 'trash' ), $base_link ), __( 'Trash', 'erp' ), erp_hr_count_trashed_employees() );

        return $status_links;
    }

    /**
     * Search form for employee table
     *
     * @since 0.1
     *
     * @param  string $text
     * @param  string $input_id
     *
     * @return void
     */
    public function search_box( $text, $input_id ) {
        if ( empty( $_REQUEST['s'] ) && !$this->has_items() )
            return;

        $input_id = $input_id . '-search-input';

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            echo '<input type="hidden" name="orderby" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) ) . '" />';
        }

        if ( ! empty( $_REQUEST['order'] ) ) {
            echo '<input type="hidden" name="order" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) . '" />';
        }

        if ( ! empty( $_REQUEST['status'] ) ) {
            echo '<input type="hidden" name="status" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) ) . '" />';
        }

        if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
            echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['post_mime_type'] ) ) ) . '" />';
        }

        if ( ! empty( $_REQUEST['detached'] ) ) {
            echo '<input type="hidden" name="detached" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['detached'] ) ) ) . '" />';
        }
        ?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
            <input type="search" id="<?php echo esc_attr( $input_id ) ?>" name="s" value="<?php _admin_search_query(); ?>" />
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
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : 'active';

        // only ncessary because we have sample data
        $args = array(
            'offset' => $offset,
            'number' => $per_page,
        );

        if ( isset( $_REQUEST['s'] ) && !empty( $_REQUEST['s'] ) ) {
            $args['s'] = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
        }

        if ( isset( $_REQUEST['orderby'] ) && !empty( $_REQUEST['orderby'] ) ) {
            $args['orderby'] = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
        }

        if ( isset( $_REQUEST['status'] ) && !empty( $_REQUEST['status'] ) && current_user_can( erp_hr_get_manager_role() ) ) {
            $args['status'] = sanitize_text_field( wp_unslash( $_REQUEST['status'] ) );
        }

        if ( isset( $_REQUEST['order'] ) && !empty( $_REQUEST['order'] ) ) {
            $args['order'] = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) );
        }

        if ( isset( $_REQUEST['filter_designation'] ) && !empty( $_REQUEST['filter_designation'] ) ) {
            $args['designation'] = sanitize_text_field( wp_unslash( $_REQUEST['filter_designation'] ) );
        }

        if ( isset( $_REQUEST['filter_department'] ) && !empty( $_REQUEST['filter_department'] ) ) {
            $args['department'] = sanitize_text_field( wp_unslash( $_REQUEST['filter_department'] ) );
        }

        if ( isset( $_REQUEST['filter_employment_type'] ) && !empty( $_REQUEST['filter_employment_type'] ) ) {
            $args['type'] = sanitize_text_field( wp_unslash( $_REQUEST['filter_employment_type'] ) );
        }

        $this->counts = erp_hr_employee_get_status_count();
        $this->items  = erp_hr_get_employees( $args );

        $args['count'] = true;
        $total_items = erp_hr_get_employees( $args );


        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ) );
    }

}
