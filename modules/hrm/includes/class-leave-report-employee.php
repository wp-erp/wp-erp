<?php

namespace WeDevs\ERP\HRM;

/**
 * List table class
 */
if ( ! class_exists( 'WP_List_Table' ) ) {

    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Leave_Report_Employee_Based extends \WP_List_Table {
    protected $reports;
    protected $policies;

    function __construct() {
        parent::__construct( array(
            'singular' => 'leave',
            'plural'   => 'leaves',
            'ajax'     => false
        ) );
        $this->table_css();
        $this->policies = \WeDevs\ERP\HRM\Models\Leave_Policies::select( 'name', 'id' )->get();
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
        $selected_desingnation = ( isset( $_GET['filter_designation'] ) ) ? $_GET['filter_designation'] : 0;
        $selected_department   = ( isset( $_GET['filter_department'] ) ) ? $_GET['filter_department'] : 0;
        $selected_type         = ( isset( $_GET['filter_employment_type'] ) ) ? $_GET['filter_employment_type'] : '';
        $selected_time         = ( isset( $_GET['filter_year'] ) ) ? $_GET['filter_year'] : date( 'Y' );
        $current_year          = date( 'Y' );
        ?>
        <div class="actions alignleft">
            <label class="screen-reader-text" for="new_role"><?php _e( 'Filter by Designation', 'erp' ) ?></label>
            <select name="filter_year" id="filter_year">
                <?php
                for ( $i = 0; $i <= 5; $i ++ ) {
                    $year = $current_year - $i;
                    echo sprintf( "<option value='%s'%s>%s</option>\n", $year, selected( $selected_time, $year, false ), $year );

                }
                ?>
            </select>

            <select name="filter_designation" id="filter_designation">
                <?php echo erp_hr_get_designation_dropdown( $selected_desingnation ); ?>
            </select>

            <label class="screen-reader-text" for="new_role"><?php _e( 'Filter by Designation', 'erp' ) ?></label>
            <select name="filter_department" id="filter_department">
                <?php echo erp_hr_get_departments_dropdown( $selected_department ); ?>
            </select>

            <label class="screen-reader-text" for="new_role"><?php _e( 'Filter by Employment Type', 'erp' ) ?></label>
            <select name="filter_employment_type" id="filter_employment_type">
                <option value="-1"><?php _e( '- Select Employment Type -', 'erp' ) ?></option>
                <?php
                $types = erp_hr_get_employee_types();

                foreach ( $types as $key => $title ) {
                    echo sprintf( "<option value='%s'%s>%s</option>\n", $key, selected( $selected_type, $key, false ), $title );
                }
                ?>
            </select>

            <?php wp_nonce_field( 'epr-rep-leaves' ); ?>
            <?php submit_button( __( 'Filter' ), 'apply', 'filter_leave_report', false ); ?>
        </div>
        <?php
    }


    function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', $this->_args['plural'] );
    }

    /**
     * Message to show if no report found
     *
     * @return void
     */
    function no_items() {

        _e( 'No Record Found', 'erp-attendance' );
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {

        $columns = array( 'name' => 'Name' );

        foreach ( $this->policies as $policy ) {
            $columns[ $policy->id ] = __( $policy->name, 'erp' );
        }

        return $columns;
    }

    /**
     * Default column values if no callback found
     *
     * @param  object $item
     * @param  string $column_name
     *
     * @return string
     */
    function column_default( $item, $column_name ) {
        $report = isset( $this->reports[ $item ] ) ? $this->reports[ $item ] : [];
        if ( isset( $report[ $column_name ] ) ) {
            $summary = $report[ $column_name ];

            return $summary['spent'] . '/' . $summary['days'];
        } elseif ( $column_name == 'name' ) {
            $user = get_user_by( 'ID', $item );
            $url  = admin_url( "admin.php?page=erp-hr-employee&amp;action=view&amp;id={$item}" );
            $name = $user->first_name . ' ' . $user->middle_name . ' ' . $user->last_name;

            return "<a href='{$url}'><strong>{$name}</strong></a>";
        } else {
            return ' - ';
        }

    }

    protected function get_user_full_name( \WP_User $user ) {
        $name = array();
        if ( $user->first_name ) {
            $name[] = $user->first_name;
        }

        if ( $user->middle_name ) {
            $name[] = $user->middle_name;
        }

        if ( $user->last_name ) {
            $name[] = $user->last_name;
        }

        return implode( ' ', $name );
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

        $per_page     = 20;
        $current_page = $this->get_pagenum();
        $offset       = ( $current_page - 1 ) * $per_page;

        $selected_desingnation = ( isset( $_GET['filter_designation'] ) ) ? $_GET['filter_designation'] : 0;
        $selected_department   = ( isset( $_GET['filter_department'] ) ) ? $_GET['filter_department'] : 0;
        $selected_type         = ( isset( $_GET['filter_employment_type'] ) ) ? $_GET['filter_employment_type'] : '';
        $selected_time         = ( isset( $_GET['filter_year'] ) ) ? $_GET['filter_year'] : date( 'Y' );

        $query = \WeDevs\ERP\HRM\Models\Employee::where( 'status', 'active' )->select( 'user_id' );

        if ( $selected_department && '-1' != $selected_department ) {
            $query->where( 'department', intval( $selected_department ) );
        }

        if ( $selected_desingnation && '-1' != $selected_desingnation ) {
            $query->where( 'designation', intval( $selected_desingnation ) );
        }

        if ( $selected_type && '-1' != $selected_type ) {
            $query->where( 'type', $selected_type );
        }

        $total_count = $query->count();

        $employees_obj = $query->skip( $offset )->take( $per_page )->get()->toArray();

        $employees = wp_list_pluck( $employees_obj, 'user_id' );
        $reports   = erp_get_leave_report( $employees, $selected_time . '-01-01', $selected_time . '-12-31' );

        $this->reports = $reports;
        $this->items   = $employees;
        $this->set_pagination_args( array(
            'total_items' => $total_count,
            'per_page'    => $per_page
        ) );

    }

}

