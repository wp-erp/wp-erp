<?php
namespace WeDevs\ERP\HRM;

/**
 * The department class
 */
class Department extends \WeDevs\ERP\Item {

    /**
     * Get a company by ID
     *
     * @param  int  company id
     *
     * @return object  wpdb object
     */
    protected function get_by_id( $department_id ) {
        global $wpdb;

        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}erp_hr_depts WHERE id = %d", $department_id ) );
    }

    /**
     * Get number of employee belongs to this department
     *
     * @return int
     */
    public function num_of_employees() {
        global $wpdb;

        $sql = "SELECT COUNT(id) FROM {$wpdb->prefix}erp_hr_employees WHERE status = 1 AND department = %d";
        $number = (int) $wpdb->get_var( $wpdb->prepare( $sql, $this->id ) );

        return $number;
    }

    /**
     * Get the name of department lead
     *
     * @return string
     */
    public function get_lead() {
        $employee = new Employee( intval( $this->lead ) );

        if ( ! $employee->id ) {
            return false;
        }

        return $employee;
    }
}