<?php
namespace WeDevs\ERP\HRM;

use WeDevs\ERP\Framework\Traits\Ajax;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Ajax handler
 *
 * @package WP-ERP
 */
class Ajax_Handler {

    use Ajax;
    use Hooker;

    /**
     * Bind all the ajax event for HRM
     *
     */
    public function __construct() {
        $this->action( 'wp_ajax_erp-hr-new-dept', 'department_create' );
        $this->action( 'wp_ajax_erp-hr-del-dept', 'department_delete' );
        $this->action( 'wp_ajax_erp-hr-get-dept', 'department_get' );
        $this->action( 'wp_ajax_erp-hr-update-dept', 'department_create' );

        $this->action( 'wp_ajax_erp-hr-new-desig', 'designation_create' );
        $this->action( 'wp_ajax_erp-hr-get-desig', 'designation_get' );
        $this->action( 'wp_ajax_erp-hr-update-desig', 'designation_create' );
        $this->action( 'wp_ajax_erp-hr-del-desig', 'designation_delete' );

        $this->action( 'wp_ajax_erp-hr-employee-new', 'employee_create' );
        $this->action( 'wp_ajax_erp-hr-emp-get', 'employee_get' );
        $this->action( 'wp_ajax_erp-hr-emp-delete', 'employee_remove' );
        $this->action( 'wp_ajax_erp-hr-emp-update-status', 'employee_update_employment' );
        $this->action( 'wp_ajax_erp-hr-emp-update-comp', 'employee_update_compensation' );
        $this->action( 'wp_ajax_erp-hr-emp-delete-history', 'employee_remove_history' );
        $this->action( 'wp_ajax_erp-hr-emp-update-jobinfo', 'employee_update_job_info' );
        $this->action( 'wp_ajax_erp-hr-empl-leave-history', 'get_employee_leave_history' );
        $this->action( 'wp_ajax_erp-hr-employee-new-note', 'employee_add_note' );

        $this->action( 'wp_ajax_erp-hr-leave-policy-create', 'leave_policy_create' );
        $this->action( 'wp_ajax_erp-hr-leave-policy-delete', 'leave_policy_delete' );

        $this->action( 'wp_ajax_erp-hr-leave-request-req-date', 'leave_request_dates' );
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
            $department = new Department( $id );
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
            $designation = new Designation( $id );
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
     * @return void
     */
    public function employee_remove_history() {
        $this->verify_nonce( 'wp-erp-hr-nonce' );

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        erp_hr_employee_remove_history( $id );

        $this->send_success();
    }

    /**
     * Update job information
     *
     * @return void
     */
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
     * Add a new note
     *
     * @return void
     */
    public function employee_add_note() {
        $this->verify_nonce( 'wp-erp-hr-employee-nonce' );

        $employee_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
        $note        = isset( $_POST['note'] ) ? strip_tags( $_POST['note'] ) : 0;
        $note_by     = get_current_user_id();

        $employee = new Employee( $employee_id );

        if ( $employee->id ) {
            $employee->add_note( $note, $note_by );
        }

        $this->send_success();
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

    /**
     * Delete a leave policy
     *
     * @return void
     */
    public function leave_policy_delete() {
        $this->verify_nonce( 'wp-erp-hr-nonce' );

        // @TODO: check permission

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        if ( $id ) {
            $deleted = erp_hr_leave_policy_delete( $id );

            if ( is_wp_error( $deleted ) ) {
                $this->send_error( $deleted->get_error_message() );
            }

            $this->send_success( __( 'Policy has been deleted', 'wp-erp' ) );
        }

        $this->send_error( __( 'Something went worng!', 'wp-erp' ) );
    }

    /**
     * Gets the leave dates
     *
     * Returns the date list between the start and end date of the
     * two dates
     *
     * @return void
     */
    public function leave_request_dates() {
        $this->verify_nonce( 'wp-erp-hr-nonce' );

        $id         = isset( $_POST['employee_id'] ) ? intval( $_POST['employee_id'] ) : get_current_user_id();
        $start_date = isset( $_POST['from'] ) ? sanitize_text_field( $_POST['from'] ) : date_i18n( 'Y-m-d' );
        $end_date   = isset( $_POST['to'] ) ? sanitize_text_field( $_POST['to'] ) : date_i18n( 'Y-m-d' );

        $days = erp_hr_get_work_days_between_dates( $start_date, $end_date );

        if ( is_wp_error( $days ) ) {
            $this->send_error( $days->get_error_message() );
        }

        // just a bit more readable date format
        foreach ($days['days'] as &$date) {
            $date['date'] = erp_format_date( $date['date'], 'D, M d' );
        }

        $days['total'] = sprintf( '%d %s', $days['total'], _n( 'day', 'days', $days['total'], 'wp-erp' ) );

        $this->send_success( $days );
    }

    /**
     * Get employee leave history
     *
     * @return void
     */
    public function get_employee_leave_history() {
        $this->verify_nonce( 'erp-hr-empl-leave-history' );

        $year        = isset( $_POST['year'] ) ? intval( $_POST['year'] ) : date( 'Y' );
        $employee_id = isset( $_POST['employee_id'] ) ? intval( $_POST['employee_id'] ) : 0;
        $policy      = isset( $_POST['leave_policy'] ) ? intval( $_POST['leave_policy'] ) : 'all';

        $args = array(
            'year'      => $year,
            'user_id'   => $employee_id,
            'status'    => 1,
            'orderby'   => 'req.start_date'
        );

        if ( $policy != 'all' ) {
            $args['policy_id'] = $policy;
        }

        $requests = erp_hr_leave_get_requests( $args );

        ob_start();
        include WPERP_HRM_VIEWS . '/employee/tab-leave-history.php';
        $content = ob_get_clean();

        $this->send_success( $content );
    }
}