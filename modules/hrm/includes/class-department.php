<?php
namespace WeDevs\ERP\HRM;

/**
 * The department class
 */
class Department {

    /**
     * Initialize a department
     *
     * @param int|object  the department numeric id or a wpdb row
     */
    public function __construct( $department = '' ) {

        if ( is_object( $department ) ) {

            $this->populate( $department );

        } elseif ( is_int( $department ) ) {

            $fetched = $this->get_by_id( $department );
            $this->populate( $fetched );

        }
    }

    /**
     * [populate description]
     *
     * @param  object  the company wpdb object
     *
     * @return void
     */
    private function populate( $department ) {
        $this->id   = (int) $department->id;
        $this->name = stripslashes( $department->title );
        $this->data = $department;
    }

    /**
     * Magic method to get department data values
     *
     * @param  string
     *
     * @return string
     */
    public function __get( $key ) {
        if ( isset( $this->data->$key ) ) {
            return stripslashes( $this->data->$key );
        }
    }

    /**
     * Get a company by ID
     *
     * @param  int  company id
     *
     * @return object  wpdb object
     */
    private function get_by_id( $department_id ) {
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

        $sql = "SELECT SUM(id) FROM {$wpdb->prefix}erp_hr_employees WHERE status = 1 AND department = %d";
        $number = (int) $wpdb->get_var( $wpdb->prepare( $sql, $this->id ) );

        return $number;
    }

    public function get_lead() {
        if ( ! $this->lead ) {
            return '-';
        }
    }
}