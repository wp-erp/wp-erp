<?php
namespace WeDevs\ERP\HRM;

/**
 * Handle the form submissions
 *
 * Although our most of the forms uses ajax and popup, some
 * are needed to submit via regular form submits. This class
 * Handles those form submission in this module
 *
 * @package WP ERP
 * @subpackage HRM
 */
class Form_Handler {

    /**
     * Hook 'em all
     */
    public function __construct() {
        add_action( 'erp_action_hr-leave-assign-policy', array( $this, 'leave_entitlement' ) );
        add_action( 'erp_action_hr-leave-req-new', array( $this, 'leave_request' ) );

        // permission
        add_action( 'erp_action_erp-hr-employee-permission', array( $this, 'employee_permission' ) );

        add_action( 'admin_init', array( $this, 'leave_request_status_change' ) );
        add_action( 'load-leave_page_erp-holiday-assign', array( $this, 'holiday_action') );
        add_action( 'load-hr-management_page_erp-hr-employee', array( $this, 'employee_bulk_action') );
        add_action( 'load-leave_page_erp-leave-policies', array( $this, 'leave_policies') );

        //After create employee apply leave policy
        add_action( 'erp_hr_employee_new', array( $this, 'apply_new_employee_policy' ), 10, 2 );
    }

    /**
     * After create employee apply leave policy
     * @param  int $user_id
     * @param  array $data
     * @return void
     */
    function apply_new_employee_policy( $user_id ) {
        $employee_obj = new \WeDevs\ERP\HRM\Employee( intval( $user_id ) );

        $employee    = $employee_obj->to_array();
        $department  = isset( $employee['work']['department'] ) ? $employee['work']['department'] : '';
        $designation = isset( $employee['work']['designation'] ) ? $employee['work']['designation'] : '';
        $gender      = isset( $employee['personal']['gender'] ) ? $employee['personal']['gender'] : '';
        $location    = isset( $employee['work']['location'] ) ? $employee['work']['location'] : '';
        $marital     = isset( $employee['personal']['marital_status'] ) ? $employee['personal']['marital_status'] : '';

        $policies = \WeDevs\ERP\HRM\Models\Leave_Policies::all()->toArray();

        $selected_policy = [];
        $schedule        = [];

        foreach ( $policies as $key => $policy ) {

            if ( $policy['activate'] == 3 ) {
                continue;
            }

            if ( $policy['department'] != '-1' && $policy['department'] != $department) {
                continue;
            }

            if ( $policy['designation'] != '-1' && $policy['designation'] != $designation ) {
                continue;
            }

            if ( $policy['gender'] != '-1' && $policy['gender'] != $gender ) {
                continue;
            }

            if ( $policy['location'] != '-1' && $policy['location'] != $location ) {
                continue;
            }

            if ( $policy['marital'] != '-1' && $policy['marital'] != $marital ) {
                continue;
            }
            $schedule[]        = $policy['activate'] == '2' ? true : false;
            $selected_policy[] = $policy;
        }

        foreach ( $selected_policy as $key => $leave_policy ) {

            if ( $schedule[$key] ) {
                erp_hr_apply_schedule_leave_policy( $user_id, $leave_policy );
            } else {
                erp_hr_apply_leave_policy( $user_id, $leave_policy );
            }
        }

    }

    public function verify_current_page_screen( $page_id, $bulk_action ) {

        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! isset( $_GET['page'] ) ) {
            return false;
        }

        if ( $_GET['page'] != $page_id ) {
            return false;
        }

        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], $bulk_action ) ) {
            return false;
        }

        return true;
    }

    public function leave_policies() {

        if ( ! $this->verify_current_page_screen( 'erp-leave-policies', 'bulk-leave_policies' ) ) {
            return;
        }

        if ( isset( $_POST['action'] ) && $_POST['action'] == 'trash' ) {
            if ( isset( $_POST['policy_id'] ) ) {
                erp_hr_leave_policy_delete( $_POST['policy_id'] );
            }
        }

        return true;
    }

    public function employee_bulk_action() {

        if ( ! $this->verify_current_page_screen( 'erp-hr-employee', 'bulk-employees' ) ) {
            return;
        }

        $employee_table = new \WeDevs\ERP\HRM\Employee_List_Table();
        $action = $employee_table->current_action();

        if ( $action ) {

            $redirect = remove_query_arg( array('_wp_http_referer', '_wpnonce', 'filter_employee' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );

            switch ( $action ) {

                case 'delete' :

                    if ( isset( $_GET['employee_id'] ) && !empty( $_GET['employee_id'] ) ) {
                        erp_employee_delete( $_GET['employee_id'], false );
                    }

                    wp_redirect( $redirect );
                    exit();

                case 'permanent_delete' :
                    if ( isset( $_GET['employee_id'] ) && !empty( $_GET['employee_id'] ) ) {
                        erp_employee_delete( $_GET['employee_id'], true );
                    }

                    wp_redirect( $redirect );
                    exit();

                case 'restore' :
                    if ( isset( $_GET['employee_id'] ) && !empty( $_GET['employee_id'] ) ) {
                        erp_employee_restore( $_GET['employee_id'] );
                    }

                    wp_redirect( $redirect );
                    exit();

                case 'filter_employee':
                    wp_redirect( $redirect );
                    exit();

                case 'employee_search':
                    $redirect = remove_query_arg( array( 'employee_search' ), $redirect );
                    wp_redirect( $redirect );
                    exit();
            }
        }
    }

    /**
     * Remove all holiday
     *
     * @return void
     */
    function holiday_action() {

        if ( ! $this->verify_current_page_screen( 'erp-holiday-assign', 'bulk-holiday' ) ) {
            return;
        }

        $this->remove_holiday( $_GET );

        $query_arg = add_query_arg( array( 's' => $_GET['s'], 'from' => $_GET['from'], 'to' => $_GET['to'] ), $_GET['_wp_http_referer'] );
        wp_redirect( $query_arg );
        exit();

    }

    function remove_holiday( $get ) {
        if ( $get['action'] != 'trash' ) {
            return false;
        }

        if ( isset( $get['holiday_id'] ) ) {
            erp_hr_delete_holidays( $get['holiday_id'] );
            return true;
        }

        return false;
    }

    /**
     * Add entitlement with leave policies to employees
     *
     * @return void
     */
    public function leave_entitlement() {
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'erp-hr-leave-assign' ) ) {
            die( __( 'Something went wrong!', 'wp-erp' ) );
        }

        $affected        = 0;
        $errors          = array();
        $employees       = array();
        $cur_year        = (int) date( 'Y' );
        $page_url        = admin_url( 'admin.php?page=erp-leave-assign&tab=assignment' );

        $is_single       = ! isset( $_POST['assignment_to'] );
        $leave_policy    = isset( $_POST['leave_policy'] ) ? intval( $_POST['leave_policy'] ) : 0;
        $leave_period    = isset( $_POST['leave_period'] ) ? intval( $_POST['leave_period'] ) : 0;
        $single_employee = isset( $_POST['single_employee'] ) ? intval( $_POST['single_employee'] ) : 0;
        $location        = isset( $_POST['location'] ) ? intval( $_POST['location'] ) : 0;
        $department      = isset( $_POST['department'] ) ? intval( $_POST['department'] ) : 0;
        $comment         = isset( $_POST['comment'] ) ? wp_kses_post( $_POST['comment'] ) : 0;

        if ( ! $leave_policy ) {
            $errors[] = 'invalid-policy';
        }

        if ( ! in_array( $leave_period, array( $cur_year, $cur_year + 1 ) ) ) {
            $errors[] = 'invalid-period';
        }

        if ( $is_single && ! $single_employee ) {
            $errors[] = 'invalid-employee';
        }

        // bail out if error found
        if ( $errors ) {
            $first_error = reset( $errors );
            $redirect_to = add_query_arg( array( 'error' => $first_error ), $page_url );
            wp_safe_redirect( $redirect_to );
            exit;
        }

        // fetch employees if not single
        if ( ! $is_single ) {

            $employees = erp_hr_get_employees( array(
                'location'   => $location,
                'department' => $department
            ) );
        } else {

            $user              = get_user_by( 'id', $single_employee );
            $emp               = new \stdClass();
            $emp->user_id      = $user->ID;
            $emp->display_name = $user->display_name;

            $employees[] = $emp;
        }

        if ( $employees ) {
            $from_date = $leave_period . '-01-01';
            $to_date   = $leave_period . '-12-31';
            $policy    = erp_hr_leave_get_policy( $leave_policy );

            if ( ! $policy ) {
                return;
            }

            foreach ($employees as $employee) {
                if ( ! erp_hr_leave_has_employee_entitlement( $employee->user_id, $leave_policy, $leave_period ) ) {
                    $data = array(
                        'user_id'   => $employee->user_id,
                        'policy_id' => $leave_policy,
                        'days'      => $policy->value,
                        'from_date' => $from_date,
                        'to_date'   => $to_date,
                        'comments'  => $comment,
                        'status'    => 1
                    );

                    $inserted = erp_hr_leave_insert_entitlement( $data );

                    if ( ! is_wp_error( $inserted ) ) {
                        $affected += 1;
                    }
                }
            }

            $redirect_to = add_query_arg( array( 'affected' => $affected ), $page_url );
            wp_safe_redirect( $redirect_to );
            exit;
        }
    }

    /**
     * Submit a new leave request
     *
     * @return void
     */
    public function leave_request() {
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'erp-leave-req-new' ) ) {
            die( __( 'Something went wrong!', 'wp-erp' ) );
        }

        $employee_id  = isset( $_POST['employee_id'] ) ? intval( $_POST['employee_id'] ) : 0;
        $leave_policy = isset( $_POST['leave_policy'] ) ? intval( $_POST['leave_policy'] ) : 0;
        $start_date   = isset( $_POST['leave_from'] ) ? sanitize_text_field( $_POST['leave_from'] ) : date_i18n( 'Y-m-d' );
        $end_date     = isset( $_POST['leave_to'] ) ? sanitize_text_field( $_POST['leave_to'] ) : date_i18n( 'Y-m-d' );
        $leave_reason = isset( $_POST['leave_reason'] ) ? strip_tags( $_POST['leave_reason'] ) : '';

        $insert = erp_hr_leave_insert_request( array(
            'user_id'      => $employee_id,
            'leave_policy' => $leave_policy,
            'start_date'   => $start_date,
            'end_date'     => $end_date,
            'reason'       => $leave_reason
        ) );

        if ( ! is_wp_error( $insert ) ) {
            $redirect_to = admin_url( 'admin.php?page=erp-leave&view=new&msg=submitted' );
        } else {
            $redirect_to = admin_url( 'admin.php?page=erp-leave&view=new&msg=error' );
        }

        wp_redirect( $redirect_to );
        exit;
    }

    public function leave_request_status_change() {

        if ( ! isset( $_GET['leave_action'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'erp-hr-leave-req-nonce' ) ) {
            return;
        }

        $action  = $_GET['leave_action'];
        $stauses = array(
            'delete',
            'reject',
            'approve',
            'pending'
        );

        if ( ! in_array( $action, $stauses ) ) {
            return;
        }

        // @TODO: Permission check
        $request_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        $status     = null;

        switch ( $action ) {
            case 'delete':
                $status = 3;
                break;

            case 'reject':
                $status = 3;
                break;

            case 'approve':
                $status = 1;
                break;

            case 'pending':
                $status = 2;
                break;
        }

        if ( null !== $status ) {
            erp_hr_leave_request_update_status( $request_id, $status );

            $redirect_to = remove_query_arg( array('status'), admin_url( 'admin.php?page=erp-leave' ) );
            $redirect_to = add_query_arg( array( 'status' => $status ), $redirect_to );

            wp_redirect( $redirect_to );
            exit;
        }
    }

    /**
     * Employee Permission Management
     *
     * @return void
     */
    public function employee_permission() {
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'wp-erp-hr-employee-permission-nonce' ) ) {
            return;
        }

        $hr_manager_role = erp_hr_get_manager_role();

        if ( ! current_user_can( $hr_manager_role ) ) {
            wp_die( __( 'Permission Denied!', 'wp-erp' ) );
        }

        $employee_id    = isset( $_POST['employee_id'] ) ? intval( $_POST['employee_id'] ) : 0;
        $enable_manager = isset( $_POST['enable_manager'] ) ? sanitize_text_field( $_POST['enable_manager'] ) : 'off';

        if ( ! in_array( $enable_manager, [ 'on', 'off' ] ) ) {
            return;
        }

        $user = get_user_by( 'id', $employee_id );

        if ( 'on' == $enable_manager && ! user_can( $user, $hr_manager_role ) ) {

            $user->add_role( $hr_manager_role );

        } else if ( 'off' == $enable_manager && user_can( $user, $hr_manager_role ) ) {

            $user->remove_role( $hr_manager_role );
        }
    }
}

new Form_Handler();