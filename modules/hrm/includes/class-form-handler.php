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
        add_action( 'admin_init', array( $this, 'handle_employee_status_update' ) );
        add_action( 'admin_init', array( $this, 'handle_leave_calendar_filter' ) );

        $hr_management = sanitize_title( __( 'HR Management', 'erp' ) );
        add_action( "load-{$hr_management}_page_erp-hr-employee", array( $this, 'employee_bulk_action' ) );
        add_action( "load-{$hr_management}_page_erp-hr-designation", array( $this, 'designation_bulk_action' ) );
        add_action( "load-{$hr_management}_page_erp-hr-depts", array( $this, 'department_bulk_action' ) );
        add_action( "load-{$hr_management}_page_erp-hr-reporting", array( $this, 'reporting_headcount_bulk_action' ) );

        $leave = sanitize_title( __( 'Leave', 'erp' ) );
        add_action( 'load-toplevel_page_erp-leave', array( $this, 'leave_request_bulk_action' ) );
        add_action( "load-{$leave}_page_erp-leave-assign", array( $this, 'entitlement_bulk_action' ) );
        add_action( "load-{$leave}_page_erp-holiday-assign", array( $this, 'holiday_action' ) );
        add_action( "load-{$leave}_page_erp-leave-policies", array( $this, 'leave_policies' ) );
    }

    /**
     * Hnadle leave calendar filter
     *
     * @since 0.1
     *
     * @return void
     */
    public function handle_leave_calendar_filter() {
        if ( ! isset( $_POST['erp_leave_calendar_filter'] ) ) {
            return;
        }
        $designation = isset( $_POST['designation'] ) ? $_POST['designation'] : '';
        $department  = isset( $_POST['department'] ) ? $_POST['department'] : '';
        $url = admin_url("admin.php?page=erp-leave-calendar&designation=$designation&department=$department");
        wp_redirect( $url );
        exit();
    }

    /**
     * Check is current page actions
     *
     * @since 0.1
     *
     * @param  integer $page_id
     * @param  integer $bulk_action
     *
     * @return boolean
     */
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

    /**
     * Handle leave policies bulk action
     *
     * @since 0.1
     *
     * @return void [redirection]
     */
    public function leave_policies() {
        // Check nonce validaion
        if ( ! $this->verify_current_page_screen( 'erp-leave-policies', 'bulk-leave_policies' ) ) {
            return;
        }

        // Check permission
        if ( ! current_user_can( 'erp_leave_manage' ) ) {
            wp_die( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        if ( isset( $_POST['action'] ) && $_POST['action'] == 'trash' ) {

            if ( isset( $_POST['policy_id'] ) ) {
                erp_hr_leave_policy_delete( $_POST['policy_id'] );
            }
        }

        return true;
    }

    /**
     * Handle entitlement bulk actions
     *
     * @since 0.1
     *
     * @return void
     */
    public function entitlement_bulk_action() {
        if ( ! $this->verify_current_page_screen( 'erp-leave-assign', 'bulk-entitlements' ) ) {
            return;
        }

        // Check permission
        if ( ! current_user_can( 'erp_leave_manage' ) ) {
            wp_die( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        $employee_table = new \WeDevs\ERP\HRM\Entitlement_List_Table();
        $action = $employee_table->current_action();

        if ( $action ) {
            $redirect = remove_query_arg( array('_wp_http_referer', '_wpnonce', 'filter_entitlement' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );

            if ( $action == 'filter_entitlement' ) {
                wp_redirect( $redirect );
                exit();
            }

            if ( $action == 'entitlement_delete' ) {
                if ( isset( $_GET['entitlement_id'] ) && !empty( $_GET['entitlement_id'] ) ) {
                    foreach ( $_GET['entitlement_id'] as $key => $ent_id ) {
                        $entitlement_data = \WeDevs\ERP\HRM\Models\Leave_Entitlement::select( 'user_id','policy_id' )->find( $ent_id )->toArray();
                        erp_hr_delete_entitlement( $ent_id, $entitlement_data['user_id'], $entitlement_data['policy_id'] );
                    }
                }

                wp_redirect( $redirect );
                exit();

            }
        }
    }

    /**
     * Leave request bulk actions
     *
     * @since 1.0
     *
     * @return void redirect
     */
    public function leave_request_bulk_action() {
        // Check nonce validaion
        if ( ! $this->verify_current_page_screen( 'erp-leave', 'bulk-leaves' ) ) {
            return;
        }

        // Check permission
        if ( ! current_user_can( 'erp_leave_manage' ) ) {
            wp_die( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        $leave_request_table = new \WeDevs\ERP\HRM\Leave_Requests_List_Table();
        $action              = $leave_request_table->current_action();

        if ( $action ) {

            $redirect = remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );

            switch ( $action ) {

                case 'delete' :

                    if ( isset( $_GET['request_id'] ) && !empty( $_GET['request_id'] ) ) {
                        foreach ( $_GET['request_id'] as $key => $request_id ) {
                            erp_hr_leave_request_update_status( $request_id, 3 );
                        }
                    }

                    wp_redirect( $redirect );
                    exit();

                case 'approved' :
                    if ( isset( $_GET['request_id'] ) && !empty( $_GET['request_id'] ) ) {
                        foreach ( $_GET['request_id'] as $key => $request_id ) {
                            erp_hr_leave_request_update_status( $request_id, 1 );

                            $approved_email = wperp()->emailer->get_email( 'Approved_Leave_Request' );

                            if ( is_a( $approved_email, '\WeDevs\ERP\Email') ) {
                                $approved_email->trigger( $request_id );
                            }

                        }
                    }

                    wp_redirect( $redirect );
                    exit();

                case 'reject' :
                    if ( isset( $_GET['request_id'] ) && !empty( $_GET['request_id'] ) ) {
                        foreach ( $_GET['request_id'] as $key => $request_id ) {
                            erp_hr_leave_request_update_status( $request_id, 3 );

                            $rejected_email = wperp()->emailer->get_email( 'Rejected_Leave_Request' );

                            if ( is_a( $rejected_email, '\WeDevs\ERP\Email') ) {
                                $rejected_email->trigger( $request_id );
                            }
                        }
                    }

                    wp_redirect( $redirect );
                    exit();

                case 'pending':
                    if ( isset( $_GET['request_id'] ) && !empty( $_GET['request_id'] ) ) {
                        foreach ( $_GET['request_id'] as $key => $request_id ) {
                            erp_hr_leave_request_update_status( $request_id, 2 );
                        }
                    }

                    wp_redirect( $redirect );
                    exit();

            }
        }

    }

    /**
     * Handle Employee Bulk actions
     *
     * @since 0.1
     *
     * @return void [redirection]
     */
    public function employee_bulk_action() {
        // Nonce validation
        if ( ! $this->verify_current_page_screen( 'erp-hr-employee', 'bulk-employees' ) ) {
            return;
        }

        // Check permission if not hr manager then go out from here
        if ( ! current_user_can( erp_hr_get_manager_role() ) ) {
            wp_die( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        $employee_table = new \WeDevs\ERP\HRM\Employee_List_Table();
        $action = $employee_table->current_action();

        if ( $action ) {

            $redirect = remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'filter_employee' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );

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
     * Handle designation bulk action
     *
     * @since 0.1
     *
     * @return void [redirection]
     */
    public function designation_bulk_action() {
        if ( ! $this->verify_current_page_screen( 'erp-hr-designation', 'bulk-designations' ) ) {
            return;
        }

        // Check permission if not hr manager then go out from here
        if ( ! current_user_can( erp_hr_get_manager_role() ) ) {
            wp_die( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        $employee_table = new \WeDevs\ERP\HRM\Designation_List_Table();
        $action = $employee_table->current_action();

        if ( $action ) {

            $redirect = remove_query_arg( array('_wp_http_referer', '_wpnonce', 'action', 'action2' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );

            switch ( $action ) {

                case 'designation_delete' :

                    if ( isset( $_GET['desig'] ) && !empty( $_GET['desig'] ) ) {
                        $not_deleted_item = erp_hr_delete_designation( $_GET['desig'] );
                    }

                    if ( ! empty ( $not_deleted_item ) ) {
                        $redirect = add_query_arg( array( 'desig_delete' => implode( ',', $not_deleted_item ) ), $redirect );
                    }

                    wp_redirect( $redirect );
                    exit();
            }
        }
    }

    /**
     * Department handle bulk action
     *
     * @since 0.1
     *
     * @return void [redirection]
     */
    public function department_bulk_action() {
        // Check nonce validation
        if ( ! $this->verify_current_page_screen( 'erp-hr-depts', 'bulk-departments' ) ) {
            return;
        }

        // Check permission if not hr manager then go out from here
        if ( ! current_user_can( erp_hr_get_manager_role() ) ) {
            wp_die( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }


        $employee_table = new \WeDevs\ERP\HRM\Department_List_Table();
        $action         = $employee_table->current_action();

        if ( $action ) {

            $redirect = remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'action', 'action2' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );
            $resp     = [];

            switch ( $action ) {

                case 'delete_department' :

                    if ( isset( $_GET['department_id'] ) && $_GET['department_id'] ) {
                        foreach ( $_GET['department_id'] as $key => $dept_id ) {
                            $resp[] = erp_hr_delete_department( $dept_id );
                        }
                    }

                    if ( in_array ( false, $resp ) ) {
                        $redirect = add_query_arg( array( 'department_delete' => 'item_deleted' ), $redirect );
                    }

                    wp_redirect( $redirect );
                    exit();
            }
        }
    }

    /**
     * Remove all holiday
     *
     * @since 0.1
     *
     * @return void
     */
    public function holiday_action() {
        // Check nonce validation
        if ( ! $this->verify_current_page_screen( 'erp-holiday-assign', 'bulk-holiday' ) ) {
            return;
        }

        // Check permission
        if ( ! current_user_can( 'erp_leave_manage' ) ) {
            wp_die( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        $this->remove_holiday( $_GET );

        $query_arg = add_query_arg( array( 's' => $_GET['s'], 'from' => $_GET['from'], 'to' => $_GET['to'] ), $_GET['_wp_http_referer'] );
        wp_redirect( $query_arg );
        exit();
    }

    /**
     * Handle hoiday remove functionality
     *
     * @since 0.1
     *
     * @param array $get
     *
     * @return boolean
     */
    public function remove_holiday( $get ) {

        // Check permission
        if ( ! current_user_can( 'erp_leave_manage' ) ) {
            wp_die( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        if ( isset( $get['action'] ) && $get['action'] == 'trash' ) {
            if ( isset( $get['holiday_id'] ) ) {
                erp_hr_delete_holidays( $get['holiday_id'] );
                return true;
            }
        }

        if ( isset( $get['action2'] ) && $get['action2'] == 'trash' ) {
            if ( isset( $get['holiday_id'] ) ) {
                erp_hr_delete_holidays( $get['holiday_id'] );
                return true;
            }
        }

        return false;
    }

    /**
     * Add entitlement with leave policies to employees
     *
     * @since 0.1
     *
     * @return void
     */
    public function leave_entitlement() {

        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'erp-hr-leave-assign' ) ) {
            die( __( 'Something went wrong!', 'erp' ) );
        }

        if ( ! current_user_can( 'erp_leave_manage' ) ) {
            wp_die( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        $affected        = 0;
        $errors          = array();
        $employees       = array();
        $cur_year        = (int) date( 'Y' );
        $page_url        = admin_url( 'admin.php?page=erp-leave-assign&tab=assignment' );

        $is_single       = ! isset( $_POST['assignment_to'] );
        $leave_policy    = isset( $_POST['leave_policy'] ) ? intval( $_POST['leave_policy'] ) : '-1';
        $leave_period    = isset( $_POST['leave_period'] ) ? $_POST['leave_period'] : '-1';
        $single_employee = isset( $_POST['single_employee'] ) ? intval( $_POST['single_employee'] ) : '-1';
        $location        = isset( $_POST['location'] ) ? intval( $_POST['location'] ) : '-1';
        $department      = isset( $_POST['department'] ) ? intval( $_POST['department'] ) : '-1';
        $comment         = isset( $_POST['comment'] ) ? wp_kses_post( $_POST['comment'] ) : '-1';

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
            $emp->id      = $user->ID;
            $emp->display_name = $user->display_name;

            $employees[] = $emp;
        }

        if ( $employees ) {
            $from_date = $leave_period;
            $to_date   = date( 'Y-m-t H:i:s', strtotime( '+11 month', strtotime( $leave_period ) ) );
            $policy    = erp_hr_leave_get_policy( $leave_policy );

            if ( ! $policy ) {
                return;
            }

            foreach ($employees as $employee) {
                $data = array(
                    'user_id'   => $employee->id,
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

            $redirect_to = add_query_arg( array( 'affected' => $affected ), $page_url );
            wp_safe_redirect( $redirect_to );
            exit;
        }
    }

    /**
     * Submit a new leave request
     *
     * @since 0.1
     *
     * @return void
     */
    public function leave_request() {

        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'erp-leave-req-new' ) ) {
            die( __( 'Something went wrong!', 'erp' ) );
        }

        if ( ! current_user_can( 'erp_leave_create_request' ) ) {
            wp_die( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
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

    /**
     * Leave Request Status change
     *
     * @since 0.1
     *
     * @return void
     */
    public function leave_request_status_change() {

        // If not leave bulk action then go out from here
        if ( ! isset( $_GET['leave_action'] ) ) {
            return;
        }

        // Verify the nonce validation
        if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'erp-hr-leave-req-nonce' ) ) {
            return;
        }

        // Check permission if not have then bell out :)
        if ( ! current_user_can( 'erp_leave_manage' ) ) {
            wp_die( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
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

            // notification email
            if ( 1 === $status ) {

                $approved_email = wperp()->emailer->get_email( 'Approved_Leave_Request' );

                if ( is_a( $approved_email, '\WeDevs\ERP\Email') ) {
                    $approved_email->trigger( $request_id );
                }

            } else if ( 3 === $status ) {

                $rejected_email = wperp()->emailer->get_email( 'Rejected_Leave_Request' );

                if ( is_a( $rejected_email, '\WeDevs\ERP\Email') ) {
                    $rejected_email->trigger( $request_id );
                }
            }

            // redirect the user back
            $redirect_to = remove_query_arg( array('status'), admin_url( 'admin.php?page=erp-leave' ) );
            $redirect_to = add_query_arg( array( 'status' => $status ), $redirect_to );

            wp_redirect( $redirect_to );
            exit;
        }
    }

    /**
     * Employee Status Update
     *
     * @since 0.1
     *
     * @return void
     */
    public function handle_employee_status_update() {
        // If not submit this form then return
        if ( ! isset( $_POST['employee_status'] ) ) {
            return;
        }

        // Nonce validaion
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'wp-erp-hr-employee-update-nonce' ) ) {
            return;
        }

        // Check permission
        if ( ! current_user_can( erp_hr_get_manager_role() ) ) {
            wp_die( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        if ( $_POST['employee_status'] == 'terminated' ) {
            \WeDevs\ERP\HRM\Models\Employee::where( 'user_id', '=', $_POST['user_id'] )->update( [ 'status' => $_POST['employee_status'], 'termination_date' => current_time( 'mysql' ) ] );
        } else {
            \WeDevs\ERP\HRM\Models\Employee::where( 'user_id', '=', $_POST['user_id'] )->update( ['status' => $_POST['employee_status'],  'termination_date' => '' ] );
        }

        wp_redirect( $_POST['_wp_http_referer'] );
        exit();
    }

    /**
     * Employee Permission Management
     *
     * @since 0.1
     *
     * @return void
     */
    public function employee_permission() {
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'wp-erp-hr-employee-permission-nonce' ) ) {
            return;
        }

        $hr_manager_role = erp_hr_get_manager_role();

        if ( ! current_user_can( $hr_manager_role ) ) {
            wp_die( __( 'Permission Denied!', 'erp' ) );
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

        do_action( 'erp_hr_after_employee_permission_set', $_POST, $user );
    }

    /**
     * Reporting Headcount Form Submit Handler
     *
     * @since 0.1
     *
     * @return void
     */
    public function reporting_headcount_bulk_action() {

        if ( isset( $_REQUEST['filter_headcount'] ) ) {

            if ( ! $this->verify_current_page_screen( 'erp-hr-reporting', 'epr-rep-headcount' ) ) {
                return;
            }

            $redirect = remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'filter_headcount' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );

            wp_redirect( $redirect );
        }

    }
}

new Form_Handler();
