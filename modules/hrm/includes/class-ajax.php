<?php
namespace WeDevs\ERP\HRM;

use WeDevs\ERP\Abstract_Ajax;

/**
 * Ajax handler
 *
 * @package WP-ERP
 */
class Ajax_Handler extends Abstract_Ajax {

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

        add_action( 'wp_ajax_erp-hr-leave-policy-create', array($this, 'leave_policy_create') );
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
            $this->send_success( $department );
        }

        $this->send_success( __( 'Something went wrong!', 'wp-erp' ) );
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
            $this->send_error( $dept_id->get_error_message() );
        }

        $this->send_success( array(
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
                $this->send_error( $deleted->get_error_message() );
            }

            $this->send_success( __( 'Department has been deleted', 'wp-erp' ) );
        }

        $this->send_error( __( 'Something went worng!', 'wp-erp' ) );
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
            $this->send_error( $desig_id->get_error_message() );
        }

        $this->send_success( array(
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
            $this->send_success( $designation );
        }

        $this->send_error( __( 'Something went wrong!', 'wp-erp' ) );
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
                $this->send_error( $deleted->get_error_message() );
            }

            $this->send_success( __( 'Designation has been deleted', 'wp-erp' ) );
        }

        $this->send_error( __( 'Something went wrong!', 'wp-erp' ) );
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
            $this->send_error( $employee_id->get_error_message() );
        }

        $employee               = new Employee( $employee_id );
        $data                   = $employee->to_array();
        $data['work']['joined'] = $employee->get_joined_date();
        $data['work']['type']   = $employee->get_type();
        $data['url']            = $employee->get_details_url();

        $this->send_success( $data );
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
            $this->send_error( __( 'Employee does not exists.', 'wp-erp' ) );
        }

        $employee = new Employee( $user );
        $this->send_success( $employee->to_array() );
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
        $this->send_success( __( 'Employee has been removed successfully', 'wp-erp' ) );
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
            $this->send_error( __( 'Status error', 'wp-erp' ) );
        }

        $employee = new Employee( $employee_id );

        if ( $employee->id ) {
            $employee->update_employment_status( $status, $date, $comment );
            $this->send_success();
        }

        $this->send_error( __( 'Something went wrong!', 'wp-erp' ) );
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
            $this->send_error( __( 'Enter a valid pay rate.', 'wp-erp' ) );
        }

        if ( ! array_key_exists( $pay_type, $types ) ) {
            $this->send_error( __( 'Pay Type does not exists.', 'wp-erp' ) );
        }

        if ( ! array_key_exists( $reason, $reasons ) ) {
            $this->send_error( __( 'Reason does not exists.', 'wp-erp' ) );
        }

        $employee = new Employee( $employee_id );

        if ( $employee->id ) {
            $employee->update_compensation( $pay_rate, $pay_type, $reason, $date, $comment );
            $this->send_success();
        }

        $this->send_error( __( 'Something went wrong!', 'wp-erp' ) );
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

        $this->send_success();
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
            $this->send_success();
        }

        $this->send_error( __( 'Something went wrong!', 'wp-erp' ) );
    }

    /**
     * Create or update a leave policy
     *
     * @return void
     */
    public function leave_policy_create() {
        $this->verify_nonce( 'erp-leave-policy' );

        $policy_id = isset( $_POST['policy-id'] ) ? intval( $_POST['policy-id'] ) : 0;
        $name      = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
        $days      = isset( $_POST['days'] ) ? intval( $_POST['days'] ) : '';
        $color     = isset( $_POST['color'] ) ? sanitize_text_field( $_POST['color'] ) : '';

        $policy_id = erp_hr_leave_insert_policy( array(
            'id'    => $policy_id,
            'name'  => $name,
            'value' => $days,
            'color' => $color
        ) );

        if ( is_wp_error( $policy_id ) ) {
            $this->send_error( $policy_id->get_error_message() );
        }

        $this->send_success();
    }
}

new Ajax_Handler();