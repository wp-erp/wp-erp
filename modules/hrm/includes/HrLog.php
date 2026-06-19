<?php

namespace WeDevs\ERP\HRM;

use WeDevs\ERP\Framework\Traits\Hooker;
use WeDevs\ERP\HRM\Models\Dependents;
use WeDevs\ERP\HRM\Models\Designation;
use WeDevs\ERP\HRM\Models\Employee;
use WeDevs\ERP\HRM\Models\LeaveRequest;

/**
 * Ajax handler
 */
class HrLog {
    use Hooker;

    /**
     * Load autometically when class inistantiate
     *
     * @since 0.1
     *
     * @return void
     */
    public function __construct() {

        // Employee
        $this->action( 'erp_hr_log_employee_new', 'create_employee', 10, 2 );
        $this->action( 'erp_hr_delete_employee', 'delete_employee', 10 );
        $this->action( 'erp_hr_employee_update', 'update_employee', 10, 2 );

        // Employee experience
        $this->action( 'erp_hr_employee_experience_new', 'create_experience' );
        $this->action( 'erp_hr_employee_experience_delete', 'delete_experience' );

        // Employee education
        $this->action( 'erp_hr_employee_education_create', 'create_education' );
        $this->action( 'erp_hr_employee_education_delete', 'delete_education' );

        // Employee dependents
        $this->action( 'erp_hr_employee_dependents_create', 'create_dependents' );
        $this->action( 'erp_hr_employee_dependents_delete', 'delete_dependents' );

        // Employee employment status
        $this->action( 'erp_hr_employee_employment_history_create', 'create_employment_status' );
        $this->action( 'erp_hr_employee_employment_history_delete', 'delete_employment_status' );

        // Employee compensation
        $this->action( 'erp_hr_employee_compensation_history_create', 'create_compensation' );
        $this->action( 'erp_hr_employee_compensation_history_delete', 'delete_compensation' );

        // Employee job info
        $this->action( 'erp_hr_employee_job_history_create', 'create_job_info' );
        $this->action( 'erp_hr_employee_job_history_delete', 'delete_job_info' );

        // Department
        $this->action( 'erp_hr_dept_new', 'create_department', 10, 2 );
        $this->action( 'erp_hr_dept_delete', 'delete_department', 10 );
        $this->action( 'erp_hr_dept_before_updated', 'update_department', 10, 2 );

        // Designation
        $this->action( 'erp_hr_desig_new', 'create_designation', 10, 2 );
        $this->action( 'erp_hr_desig_delete', 'delete_designation', 10 );
        $this->action( 'erp_hr_desig_before_updated', 'update_designation', 10, 2 );

        //Leave Policy
        $this->action( 'erp_hr_leave_insert_policy', 'create_policy', 10, 1 );
        $this->action( 'erp_hr_leave_policy_delete', 'delete_policy', 10 );
        $this->action( 'erp_hr_leave_before_policy_updated', 'update_policy', 10, 2 );

        //Leave Request
        $this->action( 'erp_hr_leave_new', 'create_leave_request', 10, 3 );
        $this->action( 'erp_hr_leave_update', 'update_leave_request', 10, 2 );

        //Entitlement
        $this->action( 'erp_hr_leave_insert_new_entitlement', 'create_entitlement', 10, 2 );

        //Holiday
        $this->action( 'erp_hr_new_holiday', 'create_holiday', 10, 2 );
        $this->action( 'erp_hr_leave_holiday_delete', 'delete_holiday', 10 );
        $this->action( 'erp_hr_before_update_holiday', 'update_holiday', 10, 2 );

        // Announcement
        $this->action( 'transition_post_status', 'announcment_log', 10, 3 );
        $this->action( 'before_delete_post', 'announcment_delete_log' );
    }

    /**
     * Add log when new employee created
     *
     * @since 0.1
     *
     * @param int   $emp_id
     * @param array $fields
     *
     * @return void
     */
    public function create_employee( $emp_id, $fields ) {
        erp_log()->add( [
            'sub_component' => 'employee',
            'message'       => sprintf( __( '<strong>%s</strong> employee has been created', 'erp' ), $fields['personal']['first_name'] ),
            'created_by'    => get_current_user_id(),
        ] );
    }

    /**
     * Add log when employee deleted
     *
     * @since 0.1
     *
     * @param int $emp_id
     *
     * @return void
     */
    public function delete_employee( $emp_id ) {
        if ( ! $emp_id ) {
            return;
        }

        $employee = new \WeDevs\ERP\HRM\Employee( intval( $emp_id ) );

        erp_log()->add( [
            'sub_component' => 'employee',
            'message'       => sprintf( __( '<strong>%s</strong> employee has been deleted', 'erp' ), $employee->get_full_name() ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'delete',
        ] );
    }

    /**
     * Add log when employee updated
     *
     * @since 0.1
     *
     * @param int   $emp_id
     * @param array $fields
     *
     * @return void
     */
    public function update_employee( $emp_id, $fields ) {
        if ( ! $emp_id ) {
            return;
        }

        $employee = new \WeDevs\ERP\HRM\Employee( intval( $emp_id ) );
        $old_data = erp_array_flatten( $fields );
        $new_data = erp_array_flatten( $employee->get_data() );
        $changes  = erp_get_array_diff( $new_data, $old_data, true );

        if ( empty( $changes['old_value'] ) && empty( $changes['new_value'] ) ) {
            $message = false;
        } else {
            $message = sprintf( __( '<strong>%s</strong> employee has been edited', 'erp' ), $old_data['first_name'] );

            array_walk( $changes, function ( &$key ) {
                if ( isset( $key['reporting_to'] ) ) {
                    if ( $key['reporting_to'] ) {
                        $employee            = new \WeDevs\ERP\HRM\Employee( intval( $key['reporting_to'] ) );
                        $key['reporting_to'] = $employee->get_full_name();
                    } else {
                        $key['reporting_to'] = __( 'No Manager', 'erp' );
                    }
                }

                if ( isset( $key['department'] ) ) {
                    if ( $key['department'] && $key['department'] != '-1' ) {
                        $department        = new \WeDevs\ERP\HRM\Department( intval( $key['department'] ) );
                        $key['department'] = $department->title;
                    } else {
                        $key['department'] = __( 'No Department', 'erp' );
                    }
                }

                if ( isset( $key['designation'] ) ) {
                    if ( $key['designation'] && $key['designation'] != '-1' ) {
                        $designation        = new \WeDevs\ERP\HRM\Designation( intval( $key['designation'] ) );
                        $key['designation'] = $designation->title;
                    } else {
                        $key['designation'] = __( 'No Designation', 'erp' );
                    }
                }
            } );
        }

        if ( $message ) {
            erp_log()->add( [
                'sub_component' => 'employee',
                'message'       => $message,
                'created_by'    => get_current_user_id(),
                'changetype'    => 'edit',
                'old_value'     => $changes['old_value'] ? base64_encode( maybe_serialize( $changes['old_value'] ) ) : '',
                'new_value'     => $changes['new_value'] ? base64_encode( maybe_serialize( $changes['new_value'] ) ) : '',
            ] );
        }
    }

    /**
     * Add log when new employee experience created
     *
     * @since 0.1
     *
     * @param array $fields
     *
     * @return void
     */
    public function create_experience( $fields ) {
        $employee = new \WeDevs\ERP\HRM\Employee( intval( $fields['user_id'] ) );

        erp_log()->add( [
            'sub_component' => 'employee',
            'message'       => sprintf( __( '<strong>%s</strong> employee experience has been created', 'erp' ), $employee->get_full_name() ),
            'created_by'    => get_current_user_id(),
        ] );
    }

    /**
     * Add log when employee experience deleted
     *
     * @since 0.1
     *
     * @param int $exp_id
     *
     * @return void
     */
    public function delete_experience( $exp_id ) {
        if ( ! $exp_id ) {
            return;
        }

        $exp = \WeDevs\ERP\HRM\Models\WorkExperience::find( $exp_id )->toArray();

        $employee = new \WeDevs\ERP\HRM\Employee( intval( $exp['employee_id'] ) );

        erp_log()->add( [
            'sub_component' => 'employee',
            'message'       => sprintf( __( '<strong>%s</strong> employee experience has been deleted', 'erp' ), $employee->get_full_name() ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'delete',
        ] );
    }

    /**
     * Add log when new employee education created
     *
     * @since 0.1
     *
     * @param array $fields
     *
     * @return void
     */
    public function create_education( $fields ) {
        $employee = new \WeDevs\ERP\HRM\Employee( intval( $fields['user_id'] ) );

        erp_log()->add( [
            'sub_component' => 'employee',
            'message'       => sprintf( __( '<strong>%s</strong> employee education has been created', 'erp' ), $employee->get_full_name() ),
            'created_by'    => get_current_user_id(),
        ] );
    }

    /**
     * Add log when employee education deleted
     *
     * @since 0.1
     *
     * @param int $edu_id
     *
     * @return void
     */
    public function delete_education( $edu_id ) {
        if ( ! $edu_id ) {
            return;
        }

        $exp = \WeDevs\ERP\HRM\Models\Education::find( $edu_id )->toArray();

        $employee = new \WeDevs\ERP\HRM\Employee( intval( $exp['employee_id'] ) );

        erp_log()->add( [
            'sub_component' => 'employee',
            'message'       => sprintf( __( '<strong>%s</strong> employee education has been deleted', 'erp' ), $employee->get_full_name() ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'delete',
        ] );
    }

    /**
     * Add log when new employee dependents created
     *
     * @since 0.1
     *
     * @param array $fields
     *
     * @return void
     */
    public function create_dependents( $fields ) {
        $employee = new \WeDevs\ERP\HRM\Employee( intval( $fields['user_id'] ) );

        erp_log()->add( [
            'sub_component' => 'employee',
            'message'       => sprintf( __( '<strong>%s</strong> employee dependents has been created', 'erp' ), $employee->get_full_name() ),
            'created_by'    => get_current_user_id(),
        ] );
    }

    /**
     * Add log when employee dependents deleted
     *
     * @since 0.1
     *
     * @param int $dep_id
     *
     * @return void
     */
    public function delete_dependents( $dep_id ) {
        if ( ! $dep_id ) {
            return;
        }

        $dep = \WeDevs\ERP\HRM\Models\Dependents::find( $dep_id )->toArray();

        $employee = new \WeDevs\ERP\HRM\Employee( intval( $dep['employee_id'] ) );

        erp_log()->add( [
            'sub_component' => 'employee',
            'message'       => sprintf( __( '<strong>%s</strong> employee dependents has been deleted', 'erp' ), $employee->get_full_name() ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'delete',
        ] );
    }

    /**
     * Add log when new employee employment status created
     *
     * @since 0.1
     *
     * @param array $fields
     *
     * @return void
     */
    public function create_employment_status( $eid ) {
        $employee = new \WeDevs\ERP\HRM\Employee( intval( $eid ) );

        erp_log()->add( [
            'sub_component' => 'employee',
            'message'       => sprintf( __( '<strong>%s</strong> employee employment status has been created', 'erp' ), $employee->get_full_name() ),
            'created_by'    => get_current_user_id(),
        ] );
    }

    /**
     * Add log when employee employment status deleted
     *
     * @since 0.1
     *
     * @param int $history_id
     *
     * @return void
     */
    public function delete_employment_status( $history_id ) {
        if ( ! $history_id ) {
            return;
        }

        global $wpdb;
        $query = $wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}erp_hr_employee_history WHERE id = %d", $history_id );

        $user_id = $wpdb->get_var( $query );  // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        $employee = new \WeDevs\ERP\HRM\Employee( intval( $user_id ) );

        erp_log()->add( [
            'sub_component' => 'employee',
            'message'       => sprintf( __( '<strong>%s</strong> employee employment status has been deleted', 'erp' ), $employee->get_full_name() ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'delete',
        ] );
    }

    /**
     * Add log when new employee compensation status created
     *
     * @since 0.1
     *
     * @param array $emp_id
     *
     * @return void
     */
    public function create_compensation( $emp_id ) {
        $employee = new \WeDevs\ERP\HRM\Employee( intval( $emp_id ) );

        erp_log()->add( [
            'sub_component' => 'employee',
            'message'       => sprintf( __( '<strong>%s</strong> employee compensation has been created', 'erp' ), $employee->get_full_name() ),
            'created_by'    => get_current_user_id(),
        ] );
    }

    /**
     * Add log when employee compensation deleted
     *
     * @since 0.1
     *
     * @param int $history_id
     *
     * @return void
     */
    public function delete_compensation( $history_id ) {
        if ( ! $history_id ) {
            return;
        }

        global $wpdb;
        $query = $wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}erp_hr_employee_history WHERE id = %d", $history_id );

        $user_id = $wpdb->get_var( $query );  // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        $employee = new \WeDevs\ERP\HRM\Employee( intval( $user_id ) );

        erp_log()->add( [
            'sub_component' => 'employee',
            'message'       => sprintf( __( '<strong>%s</strong> employee compensation has been deleted', 'erp' ), $employee->get_full_name() ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'delete',
        ] );
    }

    /**
     * Add log when new employee job info created
     *
     * @since 0.1
     *
     * @param array $emp_id
     *
     * @return void
     */
    public function create_job_info( $emp_id ) {
        $employee = new \WeDevs\ERP\HRM\Employee( intval( $emp_id ) );

        erp_log()->add( [
            'sub_component' => 'employee',
            'message'       => sprintf( __( '<strong>%s</strong> employee job info has been created', 'erp' ), $employee->get_full_name() ),
            'created_by'    => get_current_user_id(),
        ] );
    }

    /**
     * Add log when employee job info deleted
     *
     * @since 0.1
     *
     * @param int $history_id
     *
     * @return void
     */
    public function delete_job_info( $history_id ) {
        if ( ! $history_id ) {
            return;
        }

        global $wpdb;
        $query = $wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}erp_hr_employee_history WHERE id = %d", $history_id );

        $user_id = $wpdb->get_var( $query );  // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        $employee = new \WeDevs\ERP\HRM\Employee( intval( $user_id ) );

        erp_log()->add( [
            'sub_component' => 'employee',
            'message'       => sprintf( __( '<strong>%s</strong> employee job info has been deleted', 'erp' ), $employee->get_full_name() ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'delete',
        ] );
    }

    /**
     * Add log when new department created
     *
     * @since 0.1
     *
     * @param int   $dept_id
     * @param array $fields
     *
     * @return void
     */
    public function create_department( $dept_id, $fields ) {
        erp_log()->add( [
            'sub_component' => 'department',
            'message'       => sprintf( __( '<strong>%s</strong> department has been created', 'erp' ), $fields['title'] ),
            'created_by'    => get_current_user_id(),
        ] );
    }

    /**
     * Add log when department deleted
     *
     * @since 0.1
     *
     * @param int $dept_id
     *
     * @return void
     */
    public function delete_department( $dept_id ) {
        if ( ! $dept_id ) {
            return;
        }

        $department = new \WeDevs\ERP\HRM\Department( intval( $dept_id ) );

        erp_log()->add( [
            'sub_component' => 'department',
            'message'       => sprintf( __( '<strong>%s</strong> department has been deleted', 'erp' ), $department->title ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'delete',
        ] );
    }

    /**
     * Add log when udpate department
     *
     * @since 0.1
     *
     * @param int   $dept_id
     * @param array $fields
     *
     * @return void
     */
    public function update_department( $dept_id, $fields ) {
        if ( ! $dept_id ) {
            return;
        }

        $old_department = \WeDevs\ERP\HRM\Models\Department::find( $dept_id )->toArray();
        unset( $old_department['created_at'], $old_department['updated_at'] );

        $changes = erp_get_array_diff( $fields, $old_department, true );

        if ( empty( $changes['old_value'] ) && empty( $changes['new_value'] ) ) {
            $message = false;
        } else {
            array_walk( $changes, function ( &$key ) {
                if ( isset( $key['lead'] ) ) {
                    if ( $key['lead'] ) {
                        $employee = new \WeDevs\ERP\HRM\Employee( intval( $key['lead'] ) );
                        $key['department_lead'] = $employee->get_full_name();
                    } else {
                        $key['department_lead'] = __( 'No deparment leader', 'erp' );
                    }
                    unset( $key['lead'] );
                }

                if ( isset( $key['parent'] ) ) {
                    if ( $key['parent'] ) {
                        $department = new \WeDevs\ERP\HRM\Department( intval( $key['parent'] ) );
                        $key['parent_department'] = $department->title;
                    } else {
                        $key['parent_department'] = __( 'No Parent Department', 'erp' );
                    }
                    unset( $key['parent'] );
                }
            } );

            $message = sprintf( __( '<strong>%s</strong> department has been edited', 'erp' ), $old_department['title'] );
        }

        if ( $message ) {
            erp_log()->add( [
                'sub_component' => 'department',
                'message'       => $message,
                'created_by'    => get_current_user_id(),
                'changetype'    => 'edit',
                'old_value'     => $changes['old_value'] ? base64_encode( maybe_serialize( $changes['old_value'] ) ) : '',
                'new_value'     => $changes['new_value'] ? base64_encode( maybe_serialize( $changes['new_value'] ) ) : '',
            ] );
        }
    }

    /**
     * Add log when new designation created
     *
     * @since 0.1
     *
     * @param int   $desig_id
     * @param array $fields
     *
     * @return void
     */
    public function create_designation( $desig_id, $fields ) {
        erp_log()->add( [
            'sub_component' => 'designation',
            'message'       => sprintf( __( '<strong>%s</strong> designation has been created', 'erp' ), $fields['title'] ),
            'created_by'    => get_current_user_id(),
        ] );
    }

    /**
     * Add log when department deleted
     *
     * @since 0.1
     *
     * @param int $dept_id
     *
     * @return void
     */
    public function delete_designation( $desig ) {
        if ( ! $desig ) {
            return;
        }

        erp_log()->add( [
            'sub_component' => 'designation',
            'message'       => sprintf( __( '<strong>%s</strong> designation has been deleted', 'erp' ), $desig->title ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'delete',
        ] );
    }

    /**
     * Add log when designation updated
     *
     * @since 0.1
     *
     * @param int   $desig_id
     * @param array $fields
     *
     * @return void
     */
    public function update_designation( $desig_id, $fields ) {
        if ( ! $desig_id ) {
            return;
        }

        $old_desig = \WeDevs\ERP\HRM\Models\Designation::find( $desig_id )->toArray();
        unset( $old_desig['created_at'], $old_desig['updated_at'] );

        $changes = erp_get_array_diff( $fields, $old_desig );

        if ( empty( $changes['old_value'] ) && empty( $changes['new_value'] ) ) {
            $message = false;
        } else {
            $message = sprintf( __( '<strong>%s</strong> designation has been updated', 'erp' ), $old_desig['title'] );
        }

        if ( $message ) {
            erp_log()->add( [
                'sub_component' => 'designation',
                'message'       => $message,
                'created_by'    => get_current_user_id(),
                'changetype'    => 'edit',
                'old_value'     => $changes['old_value'],
                'new_value'     => $changes['new_value'],
            ] );
        }
    }

    /**
     * Logging for creating policy
     *
     * @since 0.1
     *
     * @param int $policy_id
     *
     * @return void
     */
    public function create_policy( $policy_id ) {
        if ( ! $policy_id ) {
            return;
        }

        erp_log()->add( [
            'sub_component' => 'leave',
            'message'       => sprintf( __( '<strong>policy %d</strong> has been created', 'erp' ), $policy_id ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'add',
        ] );
    }

    /**
     * Adding log when policy deleted
     *
     * @since 0.1
     * @since 1.2.0 Using $policy eloquent object instead of $policy_id
     *
     * @param object $policy
     *
     * @return void
     */
    public function delete_policy( $policy ) {
        if ( ! $policy ) {
            return;
        }

        erp_log()->add( [
            'sub_component' => 'leave',
            'message'       => sprintf( __( '<strong>%s</strong> policy has been deleted', 'erp' ), $policy->name ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'delete',
        ] );
    }

    /**
     * Add log when udpate policy
     *
     * @since 0.1
     *
     * @param int   $policy_id
     * @param array $old_policy
     *
     * @return void
     */
    public function update_policy( $policy_id, $old_policy ) {
        if ( ! $policy_id ) {
            return;
        }

        $new_policy = \WeDevs\ERP\HRM\Models\LeavePolicy::find( $policy_id )->toArray();

        $old_policy['effective_date'] = ! empty( $old_policy['effective_date'] )
                                        ? erp_format_date( $old_policy['effective_date'], 'Y-m-d' )
                                        : erp_current_datetime()->format( 'Y-m-d' );

        $new_policy['effective_date'] = ! empty( $new_policy['effective_date'] )
                                        ? erp_format_date( $new_policy['effective_date'], 'Y-m-d' )
                                        : erp_current_datetime()->format( 'Y-m-d' );

        if ( isset( $new_policy['activate'] ) && $new_policy['activate'] == 1 ) {
            unset( $new_policy['execute_day'], $old_policy['execute_day'] );
        }

        unset( $new_policy['updated_at'], $old_policy['updated_at'] );

        $changes = erp_get_array_diff( $new_policy, $old_policy, true );

        if ( empty( $changes['old_value'] ) && empty( $changes['new_value'] ) ) {
            $message = false;
        } else {
            array_walk( $changes, function ( &$key ) {
                if ( isset( $key['color'] ) ) {
                    $key['calender_color'] = sprintf( '<div style="width:60px; height:20px; background-color:%s"></div>', $key['color'] );
                    unset( $key['color'] );
                }

                if ( isset( $key['department'] ) ) {
                    if ( $key['department'] == '-1' ) {
                        $key['department'] = __( 'All Department', 'erp' );
                    } else {
                        $department = new \WeDevs\ERP\HRM\Department( intval( $key['department'] ) );
                        $key['department'] = $department->title;
                    }
                }

                if ( isset( $key['designation'] ) ) {
                    if ( $key['designation'] == '-1' ) {
                        $key['designation'] = __( 'All Designation', 'erp' );
                    } else {
                        $designation = new \WeDevs\ERP\HRM\Designation( intval( $key['designation'] ) );
                        $key['designation'] = $designation->title;
                    }
                }

                if ( isset( $key['location'] ) ) {
                    if ( $key['location'] == '-1' ) {
                        $key['location'] = __( 'All Location', 'erp' );
                    } else {
                        $location = erp_company_get_location_dropdown_raw();
                        $key['location'] = $location[$key['location']];
                    }
                }

                if ( isset( $key['gender'] ) ) {
                    $gender = erp_hr_get_genders( __( 'All', 'erp' ) );
                    $key['gender'] = $gender[$key['gender']];
                }

                if ( isset( $key['marital'] ) ) {
                    $marital = erp_hr_get_marital_statuses( __( 'All', 'erp' ) );
                    $key['marital'] = $marital[$key['marital']];
                }

                if ( isset( $key['activate'] ) ) {
                    $activate = [ '1' => __( 'Immediately apply after hiring', 'erp' ), '2' => __( 'Apply after X days from hiring', 'erp' ), '3' => __( 'Manually', 'erp' ) ];

                    if ( $key['activate'] == 2 ) {
                        $key['activation']   = str_replace( 'X', $key['execute_day'], $activate[$key['activate']] );
                    } else {
                        $key['activation'] = $activate[$key['activate']];
                    }

                    unset( $key['activate'] );
                    unset( $key['execute_day'] );
                }

                if ( isset( $key['effective_date'] ) ) {
                    $key['policy_effective_date'] = erp_format_date( $key['effective_date'] );
                    unset( $key['effective_date'] );
                }
            } );

            $message = sprintf( __( '<strong>%s</strong> policy has been updated', 'erp' ), $old_policy['description'] );
        }

        if ( $message ) {
            erp_log()->add( [
                'sub_component' => 'leave',
                'message'       => $message,
                'created_by'    => get_current_user_id(),
                'changetype'    => 'edit',
                'old_value'     => $changes['old_value'],
                'new_value'     => $changes['new_value'],
            ] );
        }
    }

    /**
     * Add log when someone take leave
     *
     * @since 0.1
     *
     * @param int   $request_id
     * @param array $request
     * @param array $leaves
     *
     * @return void
     */
    public function create_leave_request( $request_id, $request, $leaves ) {
        if ( ! $request_id ) {
            return;
        }

        $employee = new \WeDevs\ERP\HRM\Employee( intval( $request['user_id'] ) );

        $message = sprintf( __( '<strong>%s</strong> took leave from <strong>%s</strong> to <strong>%s</strong> for <strong>%d</strong> days', 'erp' ),
            $employee->get_full_name(),
            erp_format_date( $request['start_date'] ),
            erp_format_date( $request['end_date'] ),
            $request['days']
        );

        erp_log()->add( [
            'sub_component' => 'leave',
            'message'       => $message,
            'created_by'    => get_current_user_id(),
            'changetype'    => 'add',
        ] );
    }

    /**
     * Add log when leave request updated
     *
     * @since 1.7.2
     *
     * @param int   $request_id
     * @param array $old_data
     *
     * @return void
     */
    public function update_leave_request( $request_id, $old_data ) {
        if ( ! $request_id ) {
            return;
        }

        $employee = new \WeDevs\ERP\HRM\Employee( intval( $old_data['user_id'] ) );
        $request  = LeaveRequest::find( $request_id );
        $changes  = erp_get_array_diff( $request->toArray(), $old_data, true );

        if ( ! empty( $changes['old_value'] ) || ! empty( $changes['new_value'] ) ) {
            array_walk( $changes, function ( &$key ) {
                if ( isset( $key['last_status'] ) ) {
                    $status = [
                        1 => 'Approved',
                        2 => 'pending',
                        3 => 'Rejected',
                    ];
                    $key['status'] = $status[ $key['last_status'] ];
                    unset( $key['last_status'] );
                }
                unset( $key['updated_at'] );
            } );
        }

        $message = sprintf( __( 'A leave request by <strong>%s</strong> has been updated', 'erp' ),
            $employee->get_full_name()
        );

        erp_log()->add( [
            'sub_component' => 'leave',
            'message'       => $message,
            'created_by'    => get_current_user_id(),
            'changetype'    => 'edit',
            'old_value'     => $changes['old_value'] ? base64_encode( maybe_serialize( $changes['old_value'] ) ) : '',
            'new_value'     => $changes['new_value'] ? base64_encode( maybe_serialize( $changes['new_value'] ) ) : '',
        ] );
    }

    /**
     * Add log when entitlement created
     *
     * @since 0.1
     *
     * @param int   $entitlement_id
     * @param array $fields
     *
     * @return void
     */
    public function create_entitlement( $entitlement_id, $fields ) {
        if ( ! $entitlement_id ) {
            return;
        }

        $message  = sprintf( '%s <strong>%s</strong>', __( 'A new entitlement has been created for', 'erp' ), erp_hr_get_employee_name( intval( $fields['user_id'] ) ) );

        erp_log()->add( [
            'sub_component' => 'leave',
            'message'       => $message,
            'created_by'    => get_current_user_id(),
            'changetype'    => 'add',
        ] );
    }

    /**
     * Create hoiliday log
     *
     * @since 0.1
     *
     * @param int   $holiday_id
     * @param array $fields
     *
     * @return void
     */
    public function create_holiday( $holiday_id, $fields ) {
        if ( ! $holiday_id ) {
            return;
        }

        erp_log()->add( [
            'sub_component' => 'leave',
            'message'       => sprintf( __( 'A new holiday named <strong>%s</strong> has been created', 'erp' ), $fields['title'] ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'add',
        ] );
    }

    /**
     * Delete holiday log insert
     *
     * @since 0.1
     *
     * @param int $holiday_id
     *
     * @return void
     */
    public function delete_holiday( $holiday_id ) {
        if ( ! $holiday_id ) {
            return;
        }

        $holiday = \WeDevs\ERP\HRM\Models\LeaveHoliday::find( $holiday_id );

        if ( !$holiday ) {
            return;
        }

        erp_log()->add( [
            'sub_component' => 'leave',
            'message'       => sprintf( __( '<strong>%s</strong> holiday has been deleted', 'erp' ), $holiday->title ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'delete',
        ] );
    }

    /**
     * Add log when edit holiday
     *
     * @since 0.1
     *
     * @param int   $holiday_id
     * @param array $fields
     *
     * @return void
     */
    public function update_holiday( $holiday_id, $fields ) {
        if ( ! $holiday_id ) {
            return;
        }

        $old_holiday = \WeDevs\ERP\HRM\Models\LeaveHoliday::find( $holiday_id )->toArray();
        unset( $old_holiday['created_at'], $old_holiday['updated_at'] );

        $old_holiday['start'] = erp_format_date( $old_holiday['start'], 'Y-m-d' );
        $old_holiday['end']   = erp_format_date( $old_holiday['end'], 'Y-m-d' );

        $fields['start'] = erp_format_date( $fields['start'], 'Y-m-d' );
        $fields['end']   = erp_format_date( $fields['end'], 'Y-m-d' );

        $changes = erp_get_array_diff( $fields, $old_holiday, true );

        if ( empty( $changes['old_value'] ) && empty( $changes['new_value'] ) ) {
            $message = false;
        } else {
            array_walk( $changes, function ( &$key ) {
                if ( isset( $key['start'] ) ) {
                    $key['start_date'] = erp_format_date( $key['start'] );
                    unset( $key['start'] );
                }

                if ( isset( $key['end'] ) ) {
                    $key['end_date'] = erp_format_date( $key['end'] );
                    unset( $key['end'] );
                }
            } );
            $message = sprintf( __( '<strong>%s</strong> holiday has been updated', 'erp' ), $old_holiday['title'] );
        }

        if ( $message ) {
            erp_log()->add( [
                'sub_component' => 'leave',
                'message'       => $message,
                'created_by'    => get_current_user_id(),
                'changetype'    => 'edit',
                'old_value'     => $changes['old_value'] ? base64_encode( maybe_serialize( $changes['old_value'] ) ) : '',
                'new_value'     => $changes['new_value'] ? base64_encode( maybe_serialize( $changes['new_value'] ) ) : '',
            ] );
        }
    }

    /**
     * Add log when announcement create or edit
     *
     * @since 0.1
     *
     * @param string $new_status
     * @param string $old_status
     * @param object $post
     *
     * @return void
     */
    public function announcment_log( $new_status, $old_status, $post ) {
        if ( 'erp_hr_announcement' != $post->post_type ) {
            return;
        }

        if ( 'publish' !== $new_status ) {
            return;
        }

        $overview = add_query_arg( [ 'page' => 'erp-hr' ], admin_url( 'admin.php' ) );

        if ( 'publish' === $old_status ) {
            $message     = sprintf( __( '<strong>%s</strong> announcement has been updated', 'erp' ), $post->post_title );
            $change_type = 'edit';
        } else {
            $message     = sprintf( __( '<strong>%s</strong> announcement has been created', 'erp' ), $post->post_title );
            $change_type = 'add';
        }

        erp_log()->add( [
            'sub_component' => 'announcement',
            'message'       => $message,
            'created_by'    => get_current_user_id(),
            'changetype'    => $change_type,
        ] );
    }

    /**
     * Add log when announcement deleted
     *
     * @since 1.7.2
     *
     * @param int $post_id
     *
     * @return void
     */
    public function announcment_delete_log( $post_id ) {
        $post = \get_post( $post_id );

        if ( 'erp_hr_announcement' != $post->post_type ) {
            return;
        }

        erp_log()->add( [
            'sub_component' => 'announcement',
            'message'       => sprintf( __( '<strong>%s</strong> announcement has been deleted', 'erp' ), $post->post_title ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'delete',
        ] );
    }
}
