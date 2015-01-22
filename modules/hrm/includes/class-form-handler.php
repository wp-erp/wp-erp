<?php
namespace WeDevs\ERP\HRM;

/**
 * Handle the form submission
 */
class Form_Handler {

    public function __construct() {
        add_action( 'erp_action_hr-leave-assign-policy', array( $this, 'leave_entitlement' ) );
        add_action( 'erp_action_hr-leave-req-new', array( $this, 'leave_request' ) );
    }

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
            $company_id = erp_get_current_company_id();

            $employees = erp_hr_employees_get_by_location_department( $company_id, $location, $department );
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
}

new Form_Handler();