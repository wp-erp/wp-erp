<?php

namespace WeDevs\ERP\HRM;

/**
 * The designation class
 */
class Designation extends \WeDevs\ERP\Item {

    /**
     * Get a company by ID
     *
     * @param  int  company id
     *
     * @return object wpdb object
     */
    protected function get_by_id( $designation_id ) {
        global $wpdb;

        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}erp_hr_designations WHERE id = %d", $designation_id ) );
    }

    /**
     * Get number of employee belongs to this department
     *
     * @return int
     */
    public function num_of_employees() {
        return \WeDevs\ERP\HRM\Models\Employee::where( [ 'status' => 'active', 'designation' => $this->id ] )->count();
    }
}
