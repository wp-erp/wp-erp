<?php
namespace WeDevs\ERP\HRM;

/**
 * List table class
 */
if ( ! class_exists( 'WP_List_Table' ) ) {

    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Leave_Report_Employee_Based extends \WP_List_Table {

    function __construct() {
        parent::__construct( array(
            'singular' => 'leave',
            'plural'   => 'leaves',
            'ajax'     => false
        ) );

        $this->table_css();
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

        $columns = array();

        $policies = \WeDevs\ERP\HRM\Models\Leave_Policies::select('name')->get();

        foreach ($policies as $policy) {
            $column_slug = sanitize_title($policy->name);
            $columns[$column_slug] = __($policy->name, 'erp');
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
        $employee = new Employee( absint( $item->user_id ) );
        $report   = $employee->get_attendance_report( $this->duration['start'], $this->duration['end'] );
//        var_dump($report);
        switch ( $column_name ) {
            case 'name':
                return $employee->get_full_name();
                break;
            case 'days':
                return ! empty( $report['total_days'] ) ? $report['total_days'] : ' - ';
                break;
            case 'present':
                return ! empty( $report['total_present'] ) ? $report['total_present'] : ' - ';
                break;
            case 'absent':
                return ! empty( $report['total_absent'] ) ? $report['total_absent'] : ' - ';
                break;
            case 'leave':
                return ! empty( $report['total_leaves'] ) ? $report['total_leaves'] : ' - ';
                break;
            case 'worked':
                return erp_att_second_to_hour_min($report['total_worked']);
                break;
            case 'avg_work':
                return erp_att_second_to_hour_min($report['avg_work_time']) ;
                break;
            case 'checkin':
                return ! empty( $report['avg_check_in'] ) ? $report['avg_check_in'] : ' - ';
                break;
            case 'checkout':
                return ! empty( $report['avg_check_out'] ) ? $report['avg_check_out'] : ' - ';
                break;
        }
    }

    /**
     * Prepare the class items
     *
     * @return void
     */
    function prepare_items() {

        if ( 'custom' == $selected_query_time ) {
            $this->duration['start'] = $selected_start;
            $this->duration['end']   = $selected_end;
        } else {
            $this->duration = erp_att_get_start_end_date( $selected_query_time );
        }

        $columns               = $this->get_columns();
        $hidden                = array();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $per_page     = 20;
        $current_page = $this->get_pagenum();
        $offset       = ( $current_page - 1 ) * $per_page;


        $query = \WeDevs\ERP\HRM\Attendance\Models\Employee::where( 'status', 'active' );

        //if filter by location
        if ( $selected_location && '-1' != $selected_location ) {
            $query->where( 'location', $selected_location );
        }

        if ( $selected_department && '-1' != $selected_department ) {
            $query->where( 'department', $selected_department );
        }

        $this->items = $query->skip( $offset )->take( $per_page )->get();

        $this->set_pagination_args( array(
            'total_items' => $query->count(),
            'per_page'    => $per_page
        ) );

    }
}

