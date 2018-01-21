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

        $query         = \WeDevs\ERP\HRM\Models\Employee::where( 'status', 'active' )->select( 'user_id' );
        $employees_obj = $query->skip( $offset )->take( $per_page )->get()->toArray();

        $employees     = wp_list_pluck( $employees_obj, 'user_id' );
        $reports       = erp_get_leave_report( $employees, '2017-01-01', '2017-12-31' );
        $this->reports = $reports;
        $this->items   = $employees;
        $this->set_pagination_args( array(
            'total_items' => $query->count(),
            'per_page'    => $per_page
        ) );

    }
}

