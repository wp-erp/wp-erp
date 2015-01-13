<?php
namespace WeDevs\ERP\HRM;

/**
 * Ajax handler
 *
 * @package WP-ERP
 */
class Ajax_Handler {

    /**
     * Bind all the ajax event for HRM
     *
     * @return void
     */
    public function __construct() {
        add_action( 'wp_ajax_erp-hr-new-dept', array($this, 'department_create') );
        add_action( 'wp_ajax_erp-hr-del-dept', array($this, 'department_delete') );
        add_action( 'wp_ajax_erp-hr-get-dept', array($this, 'department_get') );
        add_action( 'wp_ajax_erp-hr-update-dept', array($this, 'department_create') );

        add_action( 'wp_ajax_erp-hr-new-desig', array($this, 'designation_create') );
        add_action( 'wp_ajax_erp-hr-get-desig', array($this, 'designation_get') );
        add_action( 'wp_ajax_erp-hr-update-desig', array($this, 'designation_create') );
        add_action( 'wp_ajax_erp-hr-del-desig', array($this, 'designation_delete') );

        add_action( 'wp_ajax_erp-hr-employee-new', array($this, 'employee_create') );
        add_action( 'wp_ajax_erp-hr-emp-get', array($this, 'employee_get') );
        add_action( 'wp_ajax_erp-hr-emp-delete', array($this, 'employee_remove') );
        add_action( 'wp_ajax_erp-hr-emp-update-status', array($this, 'employee_update_employment') );
        add_action( 'wp_ajax_erp-hr-emp-update-comp', array($this, 'employee_update_compensation') );
        add_action( 'wp_ajax_erp-hr-emp-delete-history', array($this, 'employee_remove_history') );
        add_action( 'wp_ajax_erp-hr-emp-update-jobinfo', array($this, 'employee_update_job_info') );
    }

    /**
     * Verify request nonce
     *
     * @param  string  the nonce action name
     *
     * @return void
     */
    public function verify_nonce( $action ) {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], $action ) ) {
            wp_send_json_error( __( 'Error: Nonce verification failed', 'wp-erp' ) );
        }
    }

    /**
     * Get a department
     *
     * @return void
     */
    public function department_get() {
        $this->verify_nonce( 'wp-erp-hr-nonce' );

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

        if ( $id ) {
            $department = new \WeDevs\ERP\HRM\Department( $id );
            wp_send_json_success( $department );
        }

        wp_send_json_success( __( 'Something went wrong!', 'wp-erp' ) );
    }

    /**
     * Create a new department
     *
     * @return void
     */
    public function department_create() {
        $this->verify_nonce( 'erp-new-dept' );

        // @TODO: check permission

        $title   = isset( $_POST['title'] ) ? trim( strip_tags( $_POST['title'] ) ) : '';
        $desc    = isset( $_POST['dept-desc'] ) ? trim( strip_tags( $_POST['dept-desc'] ) ) : '';
        $dept_id = isset( $_POST['dept_id'] ) ? intval( $_POST['dept_id'] ) : 0;
        $lead    = isset( $_POST['lead'] ) ? intval( $_POST['lead'] ) : 0;
        $parent  = isset( $_POST['parent'] ) ? intval( $_POST['parent'] ) : 0;

        // on update, ensure $parent != $dept_id
        if ( $dept_id == $parent ) {
            $parent = 0;
        }

        $dept_id = erp_hr_create_department( array(
            'id'          => $dept_id,
            'company_id'  => erp_get_current_company_id(),
            'title'       => $title,
            'description' => $desc,
            'lead'        => $lead,
            'parent'      => $parent
        ) );

        if ( is_wp_error( $dept_id ) ) {
            wp_send_json_error( $dept_id->get_error_message() );
        }

        wp_send_json_success( array(
            'id'       => $dept_id,
            'title'    => $title,
            'lead'     => $lead,
            'parent'   => $parent,
            'employee' => 0
        ) );
    }

    /**
     * Delete a department
     *
     * @return void
     */
    public function department_delete() {
        $this->verify_nonce( 'wp-erp-hr-nonce' );

        // @TODO: check permission

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        if ( $id ) {
            $deleted = erp_hr_delete_department( $id );

            if ( is_wp_error( $deleted ) ) {
                wp_send_json_error( $deleted->get_error_message() );
            }

            wp_send_json_success( __( 'Department has been deleted', 'wp-erp' ) );
        }

        wp_send_json_error( __( 'Something went worng!', 'wp-erp' ) );
    }

    /**
     * Create a new designnation
     *
     * @return void
     */
    function designation_create() {
        $this->verify_nonce( 'erp-new-desig' );

        $title    = isset( $_POST['title'] ) ? trim( strip_tags( $_POST['title'] ) ) : '';
        $desc     = isset( $_POST['desig-desc'] ) ? trim( strip_tags( $_POST['desig-desc'] ) ) : '';
        $desig_id = isset( $_POST['desig_id'] ) ? intval( $_POST['desig_id'] ) : 0;

        $desig_id = erp_hr_create_designation( array(
            'id'          => $desig_id,
            'company_id'  => erp_get_current_company_id(),
            'title'       => $title,
            'description' => $desc
        ) );

        if ( is_wp_error( $desig_id ) ) {
            wp_send_json_error( $desig_id->get_error_message() );
        }

        wp_send_json_success( array(
            'id'       => $desig_id,
            'title'    => $title,
            'employee' => 0
        ) );
    }

    /**
     * Get a department
     *
     * @return void
     */
    public function designation_get() {
        $this->verify_nonce( 'wp-erp-hr-nonce' );

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

        if ( $id ) {
            $designation = new \WeDevs\ERP\HRM\Designation( $id );
            wp_send_json_success( $designation );
        }

        wp_send_json_error( __( 'Something went wrong!', 'wp-erp' ) );
    }

    /**
     * Delete a department
     *
     * @return void
     */
    public function designation_delete() {
        $this->verify_nonce( 'wp-erp-hr-nonce' );

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        if ( $id ) {
            // @TODO: check permission
            $deleted = erp_hr_delete_designation( $id );

            if ( is_wp_error( $deleted ) ) {
                wp_send_json_error( $deleted->get_error_message() );
            }

            wp_send_json_success( __( 'Designation has been deleted', 'wp-erp' ) );
        }

        wp_send_json_error( __( 'Something went wrong!', 'wp-erp' ) );
    }

    /**
     * Create/update an employee
     *
     * @return void
     */
    public function employee_create() {
        $this->verify_nonce( 'wp-erp-hr-employee-nonce' );

        // @TODO: check permission
        unset( $_POST['_wp_http_referer'] );
        unset( $_POST['_wpnonce'] );
        unset( $_POST['action'] );

        $posted               = array_map( 'strip_tags_deep', $_POST );
        $posted['company_id'] = erp_get_current_company_id(); // make sure it's not exploited

        $employee_id          = erp_hr_employee_create( $posted );

        if ( is_wp_error( $employee_id ) ) {
            wp_send_json_error( $employee_id->get_error_message() );
        }

        $employee               = new Employee( $employee_id );
        $data                   = $employee->to_array();
        $data['work']['joined'] = $employee->get_joined_date();
        $data['work']['type']   = $employee->get_type();
        $data['url']            = $employee->get_details_url();

        wp_send_json_success( $data );
    }

    /**
     * Get an employee for ajax
     *
     * @return void
     */
    public function employee_get() {
        $this->verify_nonce( 'wp-erp-hr-nonce' );

        $employee_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;
        $user        = get_user_by( 'id', $employee_id );

        if ( ! $user ) {
            wp_send_json_error( __( 'Employee does not exists.', 'wp-erp' ) );
        }

        $employee = new Employee( $user );
        wp_send_json_success( $employee->to_array() );
    }

    /**
     * Remove an employee from the company
     *
     * @return void
     */
    public function employee_remove() {
        $this->verify_nonce( 'wp-erp-hr-nonce' );

        $employee_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;

        // @TODO: check permission
        erp_hr_employee_on_delete( $employee_id );
        wp_send_json_success( __( 'Employee has been removed successfully', 'wp-erp' ) );
    }

    /**
     * Update employment status
     *
     * @return void
     */
    public function employee_update_employment() {
        $this->verify_nonce( 'employee_update_employment' );

        // @TODO: check permission
        $employee_id = isset( $_REQUEST['employee_id'] ) ? intval( $_REQUEST['employee_id'] ) : 0;
        $date        = ( empty( $_POST['date'] ) ) ? current_time( 'mysql' ) : $_POST['date'];
        $comment     = strip_tags( $_POST['comment'] );
        $status      = strip_tags( $_POST['status'] );
        $types       = erp_hr_get_employee_types();

        if ( ! array_key_exists( $status, $types ) ) {
            wp_send_json_error( __( 'Status error', 'wp-erp' ) );
        }

        $employee = new Employee( $employee_id );

        if ( $employee->id ) {
            $employee->update_employment_status( $status, $date, $comment );
            wp_send_json_success();
        }

        wp_send_json_error( __( 'Something went wrong!', 'wp-erp' ) );
    }

    /**
     * Update employee compensation
     *
     * @return void
     */
    public function employee_update_compensation() {
        $this->verify_nonce( 'employee_update_compensation' );

        // @TODO: check permission
        $employee_id = isset( $_REQUEST['employee_id'] ) ? intval( $_REQUEST['employee_id'] ) : 0;
        $date        = ( empty( $_POST['date'] ) ) ? current_time( 'mysql' ) : $_POST['date'];
        $comment     = strip_tags( $_POST['comment'] );
        $pay_rate    = intval( $_POST['pay_rate'] );
        $pay_type    = strip_tags( $_POST['pay_type'] );
        $reason      = strip_tags( $_POST['change-reason'] );

        $types       = erp_hr_get_pay_type();
        $reasons     = erp_hr_get_pay_change_reasons();

        if ( ! $pay_rate ) {
            wp_send_json_error( __( 'Enter a valid pay rate.', 'wp-erp' ) );
        }

        if ( ! array_key_exists( $pay_type, $types ) ) {
            wp_send_json_error( __( 'Pay Type does not exists.', 'wp-erp' ) );
        }

        if ( ! array_key_exists( $reason, $reasons ) ) {
            wp_send_json_error( __( 'Reason does not exists.', 'wp-erp' ) );
        }

        $employee = new Employee( $employee_id );

        if ( $employee->id ) {
            $employee->update_compensation( $pay_rate, $pay_type, $reason, $date, $comment );
            wp_send_json_success();
        }

        wp_send_json_error( __( 'Something went wrong!', 'wp-erp' ) );
    }

    /**
     * Remove an history
     *
     * @return [type] [description]
     */
    public function employee_remove_history() {
        $this->verify_nonce( 'wp-erp-hr-nonce' );

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        erp_hr_employee_remove_history( $id );

        wp_send_json_success();
    }

    public function employee_update_job_info() {
        $this->verify_nonce( 'employee_update_jobinfo' );

        $employee_id  = isset( $_POST['employee_id'] ) ? intval( $_POST['employee_id'] ) : 0;
        $location     = isset( $_POST['location'] ) ? intval( $_POST['location'] ) : 0;
        $department   = isset( $_POST['department'] ) ? intval( $_POST['department'] ) : 0;
        $designation  = isset( $_POST['designation'] ) ? intval( $_POST['designation'] ) : 0;
        $reporting_to = isset( $_POST['reporting_to'] ) ? intval( $_POST['reporting_to'] ) : 0;
        $date         = ( empty( $_POST['date'] ) ) ? current_time( 'mysql' ) : $_POST['date'];

        $employee = new Employee( $employee_id );

        if ( $employee->id ) {
            $employee->update_job_info( $department, $designation, $reporting_to, $location, $date );
            wp_send_json_success();
        }

        wp_send_json_error( __( 'Something went wrong!', 'wp-erp' ) );
    }
}

new Ajax_Handler();