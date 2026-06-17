<?php
namespace WeDevs\ERP\HRM;

use ICal;
use WeDevs\ERP\Framework\Traits\Ajax;
use WeDevs\ERP\Framework\Traits\Hooker;
use WeDevs\ERP\HRM\Models\FinancialYear;
use WeDevs\ERP\HRM\Models\LeaveEntitlement;
use WeDevs\ERP\HRM\Models\LeavePolicy;

/**
 * Ajax handler
 */
class AjaxHandler {
	use Ajax;
	use Hooker;

	/**
	 * Bind all the ajax event for HRM
	 *
	 * @since 0.1
	 *
	 * @return void
	 */
	public function __construct() {

		// Department
		$this->action( 'wp_ajax_erp-hr-new-dept', 'department_create' );
		$this->action( 'wp_ajax_erp-hr-del-dept', 'department_delete' );
		$this->action( 'wp_ajax_erp-hr-get-dept', 'department_get' );
		$this->action( 'wp_ajax_erp-hr-update-dept', 'department_create' );

		// Designation
		$this->action( 'wp_ajax_erp-hr-new-desig', 'designation_create' );
		$this->action( 'wp_ajax_erp-hr-get-desig', 'designation_get' );
		$this->action( 'wp_ajax_erp-hr-update-desig', 'designation_create' );
		$this->action( 'wp_ajax_erp-hr-del-desig', 'designation_delete' );

		// Employee
		$this->action( 'wp_ajax_erp-hr-employee-new', 'employee_create' );
		$this->action( 'wp_ajax_erp-hr-emp-get', 'employee_get' );
		$this->action( 'wp_ajax_erp-hr-emp-delete', 'employee_remove' );
		$this->action( 'wp_ajax_erp-hr-emp-restore', 'employee_restore' );
		$this->action( 'wp_ajax_erp-hr-emp-update-status', 'employee_update_employment' );
		$this->action( 'wp_ajax_erp-hr-emp-update-type', 'employee_update_employment' );
		$this->action( 'wp_ajax_erp-hr-emp-update-comp', 'employee_update_compensation' );
		$this->action( 'wp_ajax_erp-hr-emp-delete-history', 'employee_remove_history' );
		$this->action( 'wp_ajax_erp-hr-emp-update-jobinfo', 'employee_update_job_info' );
		$this->action( 'wp_ajax_erp-hr-empl-leave-history', 'get_employee_leave_history' );
		$this->action( 'wp_ajax_erp-hr-employee-new-note', 'employee_add_note' );
		$this->action( 'wp_ajax_erp-load-more-notes', 'employee_load_note' );
		$this->action( 'wp_ajax_erp-delete-employee-note', 'employee_delete_note' );
		$this->action( 'wp_ajax_erp-hr-emp-update-terminate-reason', 'employee_terminate' );
		$this->action( 'wp_ajax_erp-hr-emp-activate', 'employee_termination_reactive' );
		$this->action( 'wp_ajax_erp-hr-convert-wp-to-employee', 'employee_create_from_wp_user' );
		$this->action( 'wp_ajax_erp_hr_check_user_exist', 'check_user' );
		$this->action( 'wp_ajax_erp_hr_employee_get_job_history', 'get_job_history' );
		$this->action( 'wp_ajax_erp_hr_emp_update_job_history', 'update_job_history' );

		// Dashaboard
		$this->action( 'wp_ajax_erp_hr_announcement_mark_read', 'mark_read_announcement' );
		$this->action( 'wp_ajax_erp_hr_announcement_view', 'view_announcement' );

		// Birthday Wish
		$this->action( 'wp_ajax_erp_hr_birthday_wish', 'birthday_wish' );

		// Performance
		$this->action( 'wp_ajax_erp-hr-emp-update-performance-reviews', 'employee_update_performance' );
		$this->action( 'wp_ajax_erp-hr-emp-update-performance-comments', 'employee_update_performance' );
		$this->action( 'wp_ajax_erp-hr-emp-update-performance-goals', 'employee_update_performance' );
		$this->action( 'wp_ajax_erp-hr-emp-delete-performance', 'employee_delete_performance' );

		// work experience
		$this->action( 'wp_ajax_erp-hr-create-work-exp', 'employee_work_experience_create' );
		$this->action( 'wp_ajax_erp-hr-emp-delete-exp', 'employee_work_experience_delete' );

		// education
		$this->action( 'wp_ajax_erp-hr-create-education', 'employee_education_create' );
		$this->action( 'wp_ajax_erp-hr-emp-delete-education', 'employee_education_delete' );

		// dependents
		$this->action( 'wp_ajax_erp-hr-create-dependent', 'employee_dependent_create' );
		$this->action( 'wp_ajax_erp-hr-emp-delete-dependent', 'employee_dependent_delete' );

		// leave policy
		$this->action( 'wp_ajax_erp-hr-leave-policy-delete', 'leave_policy_delete' );
		$this->action( 'wp_ajax_erp-hr-leave-request-req-date', 'leave_request_dates' );
		$this->action( 'wp_ajax_erp-hr-leave-employee-assign-policies', 'leave_assign_employee_policy' );
		$this->action( 'wp_ajax_erp-hr-leave-policies-availablity', 'leave_available_days' );
		$this->action( 'wp_ajax_erp-hr-leave-req-new', 'leave_request' );

		// leave type
		$this->action( 'wp_ajax_erp-hr-leave-type-delete', 'leave_type_delete' );
		$this->action( 'wp_ajax_erp-hr-leave-type-bulk-delete', 'leave_type_bulk_delete' );
		$this->action( 'wp_ajax_erp-hr-leave-type-create', 'leave_type_create_or_update' );
		$this->action( 'wp_ajax_erp-hr-get-leave-type', 'get_leave_type' );

		// leave holiday
		$this->action( 'wp_ajax_erp_hr_holiday_create', 'holiday_create' );
		$this->action( 'wp_ajax_erp-hr-get-holiday', 'get_holiday' );
		$this->action( 'wp_ajax_erp-hr-import-ical', 'import_ical' );
		$this->action( 'wp_ajax_erp_hr_holiday_import', 'import_holiday' );

		// leave entitlement
		$this->action( 'wp_ajax_erp-hr-leave-entitlement-delete', 'remove_entitlement' );
		$this->action( 'wp_ajax_erp-hr-leave-get-policies', 'get_policies_for_entitlement' );

		// leave get filtered employees
		$this->action( 'wp_ajax_erp-hr-leave-get-employees', 'get_employees' );

		// leave approved
		$this->action( 'wp_ajax_erp_hr_leave_approve', 'leave_approve' );

		// leave rejected
		$this->action( 'wp_ajax_erp_hr_leave_reject', 'leave_reject' );
		$this->action( 'wp_ajax_erp-hr-leave-request-delete', 'remove_leave_request' );

		// script reload
		$this->action( 'wp_ajax_erp_hr_script_reload', 'employee_template_refresh' );
		$this->action( 'wp_ajax_erp_hr_new_dept_tmp_reload', 'new_dept_tmp_reload' );
		$this->action( 'wp_ajax_erp-hr-holiday-delete', 'holiday_remove' );

		// Get leave & holiday data for hr dashboard calender
		$this->action( 'wp_ajax_erp-hr-get-leave-by-date', 'get_leave_holiday_by_date' );

		// AJAX hooks for employee requests
		$this->action( 'wp_ajax_erp_hr_employee_get_requests', 'get_employee_requests' );
		$this->action( 'wp_ajax_erp_hr_get_total_pending_requests', 'get_total_pending_requests' );
		$this->action( 'wp_ajax_erp_hr_employee_requests_bulk_action', 'employee_requests_bulk_action' );

		// AJAX hooks for Settings Actions
		$this->action( 'wp_ajax_erp-settings-get-hr-financial-years', 'erp_settings_get_hr_financial_years' );
		$this->action( 'wp_ajax_erp-settings-financial-years-save', 'erp_settings_save_hr_financial_years' );

		// Get filtered employee
		$this->action( 'wp_ajax_search_live_employee', 'search_live_employee' );
	}

	/**
	 * Search live employee.
	 *
	 * @return void
	 */
	public function search_live_employee() {
		$this->verify_hrm_nonce();

        $employee_name = isset( $_POST['employee_name'] ) ? sanitize_text_field( wp_unslash( $_POST['employee_name'] ) ) : ''; // phpcs:ignore.
		$users_found   = erp_hr_get_employees( array( 's' => $employee_name ) );
		$employees     = array();
		foreach ( $users_found as $employee ) {
			$employee                        = ( new Employee( $employee->get_data()['user_id'] ) )->to_array();
			$designation                     = ! empty( $employee['work']['designation'] ) ? erp_hr_get_single_designation( (int) $employee['work']['designation'] ) : array();
			$employee['work']['designation'] = ! empty( $designation ) ? $designation->data : '';
			$employees[]                     = $employee;
		}

		if ( empty( $employees ) ) {
			$this->send_error( array( 'data' => __( 'Sorry, no employee found!', 'erp' ) ) );
		}

		$this->send_success( $employees );
	}


	/**
	 * Leave approve
	 */
	public function leave_approve() {
		$this->verify_hrm_nonce();

		// Check permission
		if ( ! current_user_can( 'erp_leave_manage' ) && ! erp_hr_is_current_user_dept_lead() ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$request_id = isset( $_POST['leave_request_id'] ) ? intval( $_POST['leave_request_id'] ) : 0;
		$comments   = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';

		$update = erp_hr_leave_request_update_status( $request_id, 1, $comments );

		if ( is_wp_error( $update ) ) {
			$this->send_error( $update->get_error_message() );
		}

		$ret['redirect'] = array(
			'page'        => 'erp-hr',
			'section'     => 'leave',
			'status'      => $update->last_status,
			'filter_year' => $update->entitlement->f_year,
		);

		$this->send_success( $ret );
	}

	/**
	 * Leave reject
	 */
	public function leave_reject() {
		$this->verify_hrm_nonce();

		// Check permission
		if ( ! current_user_can( 'erp_leave_manage' ) && ! erp_hr_is_current_user_dept_lead() ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$request_id = isset( $_POST['leave_request_id'] ) ? intval( $_POST['leave_request_id'] ) : 0;
		$comments   = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';

		$update = erp_hr_leave_request_update_status( $request_id, 3, $comments );

		if ( is_wp_error( $update ) ) {
			$this->send_error( $update->get_error_message() );
		}

		$ret['redirect'] = array(
			'page'        => 'erp-hr',
			'section'     => 'leave',
			'status'      => $update->last_status,
			'filter_year' => $update->entitlement->f_year,
		);

		$this->send_success( $ret );
	}

	/**
	 * @since 1.6.0
	 */
	public function remove_leave_request() {
		$this->verify_hrm_nonce();

		// Check permission
		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			$this->send_error( esc_attr__( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$request_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

		$request_id = erp_hr_delete_leave_request( $request_id );

		if ( is_wp_error( $request_id ) ) {
			$this->send_error( $request_id->get_error_message() );
		}

		$this->send_success( $request_id );
	}

	/**
	 * Remove Holiday
	 *
	 * @since 0.1
	 *
	 * @return json
	 */
	public function holiday_remove() {
		$this->verify_hrm_nonce();

		// Check permission
		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$holiday_id = ( isset( $_POST['id'] ) ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';

		$holiday = erp_hr_delete_holidays( array( 'id' => intval( $holiday_id ) ) );
		$this->send_success();
	}

	/**
	 * Get Holiday
	 *
	 * @since 0.1
	 *
	 * @return json
	 */
	public function get_holiday() {
		$this->verify_hrm_nonce();

		$holiday_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

		$holiday = erp_hr_get_holidays(
			array(
				'id'     => $holiday_id,
				'number' => - 1,
			)
		);

		$holiday          = (array) reset( $holiday );
		$holiday['end']   = gmdate( 'Y-m-d', strtotime( $holiday['end'] ) );
		$holiday['start'] = gmdate( 'Y-m-d', strtotime( $holiday['start'] ) );

		$this->send_success( array( 'holiday' => $holiday ) );
	}

	/**
	 * Import ICal files
	 *
	 * @since 0.1
	 *
	 * @return json
	 */
	public function import_ical() {
		$this->verify_hrm_nonce();

		// Check permission
		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		if ( empty( $_FILES['ics']['tmp_name'] ) ) {
			$this->send_error( __( 'File upload error!', 'erp' ) );
		}

		/*
		 * An iCal may contain events from previous and future years.
		 * We'll import only events from current year
		 */
		$first_day_of_year = strtotime( gmdate( 'Y-01-01 00:00:00' ) );
		$last_day_of_year  = strtotime( gmdate( 'Y-12-31 23:59:59' ) );

		/*
		 * We'll ignore duplicate entries with the same title and
		 * start date in the foreach loop when inserting an entry
		 */
		$holiday_model = new \WeDevs\ERP\HRM\Models\LeaveHoliday();

		// create the ical parser object
		$temp_name = sanitize_url( wp_unslash( $_FILES['ics']['tmp_name'] ) );

		/***** Check if file is csv start */
		$mimes = array( 'application/vnd.ms-excel', 'text/csv' );

		if ( in_array( sanitize_mime_type( wp_unslash( $_FILES['ics']['type'] ) ), $mimes, true ) ) {
			$import_csv_data = import_holidays_csv( $temp_name );
			$this->send_success( $import_csv_data );
		}
		/***** Check if file is csv end */

		$ical      = new ICal( $temp_name );
		$events    = $ical->events();
		$ical_data = array();

		foreach ( $events as $event ) {
			$start = strtotime( $event['DTSTART'] );
			$end   = strtotime( $event['DTEND'] );

			if ( ( $start >= $first_day_of_year ) && ( $end <= $last_day_of_year ) ) {
				$title       = sanitize_text_field( wp_unslash( $event['SUMMARY'] ) );
				$start       = gmdate( 'Y-m-d 00:00:00', $start );
				$end         = gmdate( 'Y-m-d 23:59:59', $end );
				$description = ( ! empty( $event['DESCRIPTION'] ) ) ? sanitize_text_field( wp_unslash( $event['DESCRIPTION'] ) ) : $title;

				// check for duplicate entries
				$holiday = $holiday_model->where( 'title', '=', $title )
					->where( 'start', '=', $start );

				$days = erp_date_duration( $start, $end );
				$days = $days . ' ' . _n( 'day', 'days', $days, 'erp' );

				// insert only unique one
				if ( ! $holiday->count() ) {
					$days = erp_date_duration( $start, $end );
					$days = $days . ' ' . _n( 'day', 'days', $days, 'erp' );

					$ical_data[] = array(
						'title'       => $title,
						'start'       => $start,
						'end'         => $end,
						'description' => $description,
						'duration'    => $days,
					);
				}
			}
		}

		if ( empty( $ical_data ) ) {
			$this->send_success( __( 'No valid data found.', 'erp' ) );
		}

		$this->send_success( $ical_data );
	}

	/**
	 * Import ICal files
	 *
	 * @since 0.1
	 *
	 * @return json
	 */
	public function import_holiday() {
		$this->verify_nonce( 'erp-leave-holiday-import' );

		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$line       = 1;
		$data       = array();
		$error_list = array();
		$response   = '';

		foreach ( $_POST['title'] as $key => $title ) {
			$data[ $key ]['title'] = sanitize_text_field( wp_unslash( $title ) );
		}

		foreach ( $_POST['start'] as $key => $start ) {
			$data[ $key ]['start'] = sanitize_text_field( wp_unslash( $start ) );
		}

		foreach ( $_POST['end'] as $key => $end ) {
			$data[ $key ]['end'] = sanitize_text_field( wp_unslash( $end ) );
		}

		foreach ( $_POST['description'] as $key => $description ) {
			$data[ $key ]['description'] = sanitize_text_field( wp_unslash( $description ) );
		}

		foreach ( $data as $holiday ) {
			$title       = ( isset( $holiday['title'] ) ) ? $holiday['title'] : '';
			$start       = ( isset( $holiday['start'] ) ) ? $holiday['start'] : '';
			$end         = ( isset( $holiday['end'] ) ) ? $holiday['end'] : '';
			$description = ( isset( $holiday['description'] ) ) ? $holiday['description'] : '';

			$holiday_id = erp_hr_leave_insert_holiday(
				array(
					'title'       => $title,
					'start'       => $start,
					'end'         => $end,
					'description' => $description,
				)
			);

			if ( is_wp_error( $holiday_id ) ) {
				$error_list[] = $line;
			} else {
				$valid_import[] = $line;
			}

			++$line;
		}

		if ( count( $valid_import ) > 0 ) {
			$html_class = 'updated notice';
			$response  .= sprintf( __( 'Successfully imported %u data<br>', 'erp' ), count( $valid_import ) );
		}

		if ( count( $error_list ) > 0 ) {
			$html_class = 'error  notice';
			$err_string = implode( ',', $error_list );
			$response  .= sprintf( __( 'Something went wrong! Failed to import line no  %s.', 'erp' ), $err_string );
		}

		$response = "<div class='{$html_class}'><p>{$response}</p></div>";

		$this->send_success( $response );
	}

	/**
	 * Remove entitlement
	 *
	 * @since 0.1
	 *
	 * @return json
	 */
	public function remove_entitlement() {
		$this->verify_hrm_nonce();

		// Check permission
		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$id        = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$user_id   = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
		$policy_id = isset( $_POST['policy_id'] ) ? intval( $_POST['policy_id'] ) : 0; // @since 1.6.0 this is entitlement id

		if ( $id && $user_id && $policy_id ) {
			erp_hr_delete_entitlement( $id, $user_id, $policy_id );
			$this->send_success();
		} else {
			$this->send_error( __( 'Something went wrong! Please try again later.', 'erp' ) );
		}
	}

	/**
	 * Get filtered policies for entitlements
	 *
	 * @since 1.6.0
	 *
	 * @return json
	 */
	public function get_policies_for_entitlement() {
		$this->verify_hrm_nonce();

		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$data = array(
			'employee_type'  => isset( $_POST['employee_type'] ) ? sanitize_text_field( wp_unslash( $_POST['employee_type'] ) ) : '-1',
			'department_id'  => isset( $_POST['department_id'] ) ? sanitize_text_field( wp_unslash( $_POST['department_id'] ) ) : '-1',
			'location_id'    => isset( $_POST['location_id'] ) ? sanitize_text_field( wp_unslash( $_POST['location_id'] ) ) : '-1',
			'designation_id' => isset( $_POST['designation_id'] ) ? sanitize_text_field( wp_unslash( $_POST['designation_id'] ) ) : '-1',
			'gender'         => isset( $_POST['gender'] ) ? sanitize_text_field( wp_unslash( $_POST['gender'] ) ) : '-1',
			'marital'        => isset( $_POST['marital'] ) ? sanitize_text_field( wp_unslash( $_POST['marital'] ) ) : '-1',
			'f_year'         => isset( $_POST['f_year'] ) ? sanitize_text_field( wp_unslash( $_POST['f_year'] ) ) : '',
		);

		$this->send_success( array( 0 => __( '- Select -', 'erp' ) ) + erp_hr_leave_get_policies_dropdown_raw( $data ) );
	}

	/**
	 * Get filtered policies for entitlements
	 *
	 * @since 1.6.0
	 *
	 * @return json
	 */
	public function get_employees() {
		$this->verify_hrm_nonce();

		// Check permission
		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$policy_id = isset( $_POST['policy_id'] ) ? absint( wp_unslash( $_POST['policy_id'] ) ) : '0';

		if ( empty( $policy_id ) ) {
			$this->send_error( esc_attr__( 'Invalid Policy id.', 'erp' ) );
		}

		$policy = LeavePolicy::find( $policy_id );

		if ( ! $policy ) {
			$this->send_error( esc_attr__( 'No policy found with given policy id.', 'erp' ) );
		}

		$args = array(
			'number'         => '-1',
			'no_object'      => true,
			'department'     => $policy->department_id,
			'location'       => $policy->location_id,
			'designation'    => $policy->designation_id,
			'gender'         => $policy->gender,
			'marital_status' => $policy->marital,
			'type'           => $policy->employee_type,
		);

		$employees = erp_hr_get_employees( $args );

		$this->send_success( array( 0 => __( '- Select -', 'erp' ) ) + wp_list_pluck( $employees, 'display_name', 'user_id' ) );
	}

	/**
	 * Get employee template
	 *
	 * @since 0.1
	 *
	 * @return void
	 */
	public function employee_template_refresh() {
		ob_start();
		include WPERP_HRM_JS_TMPL . '/new-employee.php';
		$this->send_success( array( 'content' => ob_get_clean() ) );
	}

	/**
	 * Get department template
	 *
	 * @since 0.1
	 *
	 * @return void
	 */
	public function new_dept_tmp_reload() {
		ob_start();
		include WPERP_HRM_JS_TMPL . '/new-dept.php';
		$this->send_success( array( 'content' => ob_get_clean() ) );
	}

	/**
	 * Get a department
	 *
	 * @since 0.1
	 *
	 * @return void
	 */
	public function department_get() {
		$this->verify_hrm_nonce();

		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

		if ( $id ) {
			$department = new Department( $id );
			$this->send_success( $department );
		}

		$this->send_success( __( 'Something went wrong! Please try again later.', 'erp' ) );
	}

	/**
	 * Create a new department
	 *
	 * @since 0.1
	 *
	 * @return void
	 */
	public function department_create() {
		$this->verify_nonce( 'erp-new-dept' );

		// check permission
		if ( ! current_user_can( 'erp_manage_department' ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$title   = isset( $_POST['title'] ) ? trim( wp_strip_all_tags( sanitize_text_field( wp_unslash( $_POST['title'] ) ) ) ) : '';
		$desc    = isset( $_POST['dept-desc'] ) ? trim( wp_strip_all_tags( sanitize_text_field( wp_unslash( $_POST['dept-desc'] ) ) ) ) : '';
		$dept_id = isset( $_POST['dept_id'] ) ? intval( $_POST['dept_id'] ) : 0;
		$lead    = isset( $_POST['lead'] ) ? intval( $_POST['lead'] ) : 0;
		$parent  = isset( $_POST['parent'] ) ? intval( $_POST['parent'] ) : 0;

		$exist = \WeDevs\ERP\HRM\Models\Department::where( 'id', '!=', $dept_id )
			->where( 'title', 'like', $title )->first();

		if ( $exist && $dept_id !== $exist->id ) {
			$this->send_error( __( 'Multiple department with the same name is not allowed.', 'erp' ) );
		}

		// on update, ensure $parent != $dept_id
		if ( $dept_id === $parent ) {
			$parent = 0;
		}

		$dept_id = erp_hr_create_department(
			array(
				'id'          => $dept_id,
				'title'       => $title,
				'description' => $desc,
				'lead'        => $lead,
				'parent'      => $parent,
			)
		);

		if ( is_wp_error( $dept_id ) ) {
			$this->send_error( $dept_id->get_error_message() );
		}

		$this->send_success(
			array(
				'id'       => $dept_id,
				'title'    => $title,
				'lead'     => $lead,
				'parent'   => $parent,
				'employee' => 0,
			)
		);
	}

	/**
	 * Delete a department
	 *
	 * @return void
	 */
	public function department_delete() {
		$this->verify_hrm_nonce();

		// check permission
		if ( ! current_user_can( 'erp_manage_department' ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

		if ( $id ) {
			$deleted = erp_hr_delete_department( $id );

			if ( is_wp_error( $deleted ) ) {
				$this->send_error( $deleted->get_error_message() );
			}

			$this->send_success( __( 'Department has been deleted', 'erp' ) );
		}

		$this->send_error( __( 'Something went wrong! Please try again later.', 'erp' ) );
	}

	/**
	 * Create a new designnation
	 *
	 * @return void
	 */
	public function designation_create() {
		$this->verify_nonce( 'erp-new-desig' );

		// check permission
		if ( ! current_user_can( 'erp_manage_designation' ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$title    = isset( $_POST['title'] ) ? trim( wp_strip_all_tags( sanitize_text_field( wp_unslash( $_POST['title'] ) ) ) ) : '';
		$desc     = isset( $_POST['desig-desc'] ) ? trim( wp_strip_all_tags( sanitize_text_field( wp_unslash( $_POST['desig-desc'] ) ) ) ) : '';
		$desig_id = isset( $_POST['desig_id'] ) ? intval( $_POST['desig_id'] ) : 0;

		$exist = \WeDevs\ERP\HRM\Models\Designation::where( 'id', '!=', $desig_id )
			->where( 'title', 'Like', $title )->first();

		if ( $exist && $desig_id !== $exist->id ) {
			$this->send_error( __( 'Multiple designation with the same name is not allowed.', 'erp' ) );
		}

		$desig_id = erp_hr_create_designation(
			array(
				'id'          => $desig_id,
				'title'       => $title,
				'description' => $desc,
			)
		);

		if ( is_wp_error( $desig_id ) ) {
			$this->send_error( $desig_id->get_error_message() );
		}

		$this->send_success(
			array(
				'id'       => $desig_id,
				'title'    => $title,
				'employee' => 0,
			)
		);
	}

	/**
	 * Get a department
	 *
	 * @return void
	 */
	public function designation_get() {
		$this->verify_hrm_nonce();

		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

		if ( $id ) {
			$designation = erp_hr_get_single_designation( $id );
			$this->send_success( $designation );
		}

		$this->send_error( __( 'Something went wrong! Please try again later.', 'erp' ) );
	}

	/**
	 * Delete a department
	 *
	 * @return void
	 */
	public function designation_delete() {
		$this->verify_hrm_nonce();

		// check permission
		if ( ! current_user_can( 'erp_manage_designation' ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

		if ( $id ) {
			// @TODO: check permission
			$deleted = erp_hr_delete_designation( $id );

			if ( is_wp_error( $deleted ) ) {
				$this->send_error( $deleted->get_error_message() );
			}

			$this->send_success( __( 'Designation has been deleted', 'erp' ) );
		}

		$this->send_error( __( 'Something went wrong! Please try again later.', 'erp' ) );
	}

	/**
	 * Create/update an employee
	 *
	 * @return void
	 */
	public function employee_create() {
		$this->verify_nonce( 'wp-erp-hr-employee-nonce' );

		unset( $_POST['_wp_http_referer'] );
		unset( $_POST['_wpnonce'] );
		unset( $_POST['action'] );

		$posted  = map_deep( wp_unslash( $_POST ), 'sanitize_text_field' );
		$user_id = null;

		// Check permission for editing and adding new employee
		if ( isset( $posted['user_id'] ) && $posted['user_id'] ) {
			$user_id = absint( $posted['user_id'] );

			if ( ! current_user_can( 'erp_edit_employee', $user_id ) ) {
				$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
			}
		} elseif ( ! current_user_can( 'erp_create_employee' ) ) {
				$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		if ( ! empty( $posted['user_email'] ) ) {
			if ( erp_is_employee_exist( $posted['user_email'], $user_id ) ) {
				$this->send_error( __( 'User with the same email address already exist. Please try with different email.', 'erp' ) );
			}
		}

		$employee = new Employee( $user_id );

		$result = $employee->create_employee( $posted );

		if ( is_wp_error( $result ) ) {
			$this->send_error( $result->get_error_message() );
		}

		if ( ! $employee->is_employee() ) {
			$this->send_error( __( 'Could not create employee. Please try again.', 'erp' ) );
		}

		// we cached empty employee data right after creating, calling from erp_hr_employee_create method
		wp_cache_delete( 'erp-empl-' . $employee->get_user_id(), 'erp' );

		$data                   = $employee->to_array();
		$data['work']['joined'] = $employee->get_joined_date();
		$data['work']['type']   = $employee->get_type();
		$data['url']            = $employee->get_details_url();

		// user notification email
		if ( isset( $posted['user_notification'] ) && $posted['user_notification'] === 'on' ) {
			$emailer    = wperp()->emailer->get_email( 'NewEmployeeWelcome' );
			$send_login = isset( $posted['login_info'] ) ? true : false;

			if ( is_a( $emailer, '\WeDevs\ERP\Email' ) ) {
				$emailer->trigger( $employee->get_user_id(), $send_login );
			}
		}

		$this->send_success( $data );
	}

	/**
	 * Get an employee for ajax
	 *
	 * @return void
	 */
	public function employee_get() {
		$this->verify_hrm_nonce();

		$employee_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;
		$employee    = new Employee( $employee_id );

		if ( ! $employee->is_employee() ) {
			$this->send_error( __( 'Employee does not exists.', 'erp' ) );
		}

		$this->send_success( $employee->to_array() );
	}

	/**
	 * Remove an employee from the company
	 *
	 * @return void
	 */
	public function employee_remove() {
		$this->verify_hrm_nonce();

		// Check permission
		if ( ! current_user_can( 'erp_delete_employee' ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$employee_id = isset( $_REQUEST['id'] ) ? intval( wp_unslash( $_REQUEST['id'] ) ) : 0;
		$hard        = isset( $_REQUEST['hard'] ) ? intval( wp_unslash( $_REQUEST['hard'] ) ) : 0;
		$user        = get_user_by( 'id', $employee_id );

		if ( ! $user ) {
			$this->send_error( __( 'No employee found', 'erp' ) );
		}

		$last_user_role = get_user_meta( $user->ID, 'erp_last_removed_role', true );

		if ( in_array( 'employee', $user->roles, true ) || 'employee' == $last_user_role ) {
			$hard = apply_filters( 'erp_employee_delete_hard', $hard );
			erp_employee_delete( $employee_id, $hard );
		}

		do_action( 'erp_hr_employee_after_update_status', $employee_id, 'trash', erp_current_datetime()->format( 'Y-m-d' ) );

		$this->send_success( __( 'Employee has been removed successfully', 'erp' ) );
	}

	/**
	 * Restore an employee from the company
	 *
	 * @since 1.1.1
	 *
	 * @return void
	 */
	public function employee_restore() {
		$this->verify_hrm_nonce();

		$employee_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;
		$user        = get_user_by( 'id', $employee_id );

		if ( ! $user ) {
			$this->send_error( __( 'No employee found', 'erp' ) );
		}

		// Check permission
		if ( ! current_user_can( 'erp_edit_employee', $user->ID ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$user = apply_filters( 'pre_erp_hr_employee_args', $user );

		if ( is_wp_error( $user ) ) {
			$this->send_error( $user->get_error_message() );
		}

		if ( in_array( 'employee', $user->roles, true ) ) {
			erp_employee_restore( $employee_id );
		} else {
			// Restore the roles only when employee is in tashed
			erp_employee_restore( $employee_id, true );
		}

		$this->send_success( __( 'Employee has been restore successfully', 'erp' ) );
	}

	/**
	 * Update employment type
	 *
	 * @return void
	 */
	public function employee_update_employment() {
		$this->verify_nonce( 'employee_update_employment' );

		$user_id = isset( $_REQUEST['user_id'] ) ? intval( $_REQUEST['user_id'] ) : 0;

		// Check permission
		if ( ! current_user_can( 'erp_edit_employee', $user_id ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			$this->send_error( __( 'No employee found', 'erp' ) );
		}

		$args = array(
			'comments' => ( isset( $_POST['comment'] ) ) ? sanitize_text_field( wp_unslash( $_POST['comment'] ) ) : '',
			'date'     => ( isset( $_POST['date'] ) ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '',
		);

		if ( isset( $_POST['type'] ) ) {
			$args['module'] = 'employment';
			$args['type']   = sanitize_text_field( wp_unslash( $_POST['type'] ) );

			if ( 'active' !== $employee->get_status() ) {
				$args = apply_filters( 'pre_erp_hr_employee_args', $args );
			}
		} elseif ( isset( $_POST['status'] ) ) {
			$args['module']   = 'employee';
			$args['category'] = sanitize_text_field( wp_unslash( $_POST['status'] ) );

			if ( 'terminated' === $args['category'] ) {
				$this->send_success();
			} elseif ( 'active' === $args['category'] ) {
				$args = apply_filters( 'pre_erp_hr_employee_args', $args );
			}
		}

		if ( is_wp_error( $args ) ) {
			$this->send_error( $args->get_error_message() );
		}

		$old_data = $employee->get_data();
		$created  = $employee->update_employment_status( $args );

		if ( is_wp_error( $created ) ) {
			$this->send_error( $created->get_error_message() );
		}

		do_action( 'erp_hr_employee_update', $user_id, $old_data );

		$this->send_success();
	}

	/**
	 * Update employee compensation
	 *
	 * @return void
	 */
	public function employee_update_compensation() {
		$this->verify_nonce( 'employee_update_compensation' );

		$user_id = isset( $_REQUEST['user_id'] ) ? intval( $_REQUEST['user_id'] ) : 0;

		// Check permission
		if ( ! current_user_can( 'erp_edit_employee', $user_id ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			$this->send_error( __( 'No employee found', 'erp' ) );
		}

		$old_data = $employee->get_data();
		$created  = $employee->update_compensation(
			array(
				'comment'  => ( isset( $_POST['comment'] ) ) ? sanitize_text_field( wp_unslash( $_POST['comment'] ) ) : '',
				'pay_type' => ( isset( $_POST['pay_type'] ) ) ? sanitize_text_field( wp_unslash( $_POST['pay_type'] ) ) : '',
				'reason'   => ( isset( $_POST['change-reason'] ) ) ? sanitize_text_field( wp_unslash( $_POST['change-reason'] ) ) : '',
				'pay_rate' => ( isset( $_POST['pay_rate'] ) ) ? sanitize_text_field( wp_unslash( $_POST['pay_rate'] ) ) : '',
				'date'     => ( isset( $_POST['date'] ) ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '',
			)
		);

		if ( is_wp_error( $created ) ) {
			$this->send_error( $created->get_error_message() );
		}

		do_action( 'erp_hr_employee_update', $user_id, $old_data );

		$this->send_success();
	}

	/**
	 * Retrives employee job history
	 *
	 * @since 1.9.0
	 *
	 * @return mixed
	 */
	public function get_job_history() {
		$this->verify_hrm_nonce();

		global $wpdb;

		$history_id = ! empty( $_REQUEST['history_id'] ) ? intval( wp_unslash( $_REQUEST['history_id'] ) ) : '';

		$history = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}erp_hr_employee_history WHERE id = %d",
				array(
					$history_id,
				)
			)
		);

		$history->date = erp_current_datetime()->modify( $history->date )->format( 'Y-m-d' );

		$this->send_success( $history );
	}

	/**
	 * Updates employee job history
	 *
	 * @since 1.9.0
	 *
	 * @return mixed
	 */
	public function update_job_history() {
		$this->verify_hrm_nonce();

		$user_id = ! empty( $_REQUEST['user_id'] ) ? intval( wp_unslash( $_REQUEST['user_id'] ) ) : 0;

		if ( empty( $user_id ) ) {
			$this->send_error( __( 'No employee found!', 'erp' ) );
		}

		if ( ! current_user_can( 'erp_manage_jobinfo', $user_id ) ) {
			$this->send_error( __( 'You do not have sufficient permission to do this action', 'erp' ) );
		}

		$history_id = ! empty( $_REQUEST['history_id'] ) ? intval( wp_unslash( $_REQUEST['history_id'] ) ) : null;
		$module     = ! empty( $_REQUEST['module'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['module'] ) ) : '';
		$date       = ! empty( $_REQUEST['date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['date'] ) ) : '';
		$data       = ! empty( $_REQUEST['data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['data'] ) ) : '';
		$type       = ! empty( $_REQUEST['type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['type'] ) ) : '';
		$category   = ! empty( $_REQUEST['category'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['category'] ) ) : '';
		$comment    = ! empty( $_REQUEST['comment'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['comment'] ) ) : '';

		if ( empty( $history_id ) ) {
			$this->send_error( __( 'No valid history found!', 'erp' ) );
		}

		$employee = new Employee( $user_id );

		switch ( $module ) {
			case 'compensation':
				$updated = $employee->update_compensation(
					array(
						'id'       => $history_id,
						'date'     => $date,
						'pay_rate' => $type,
						'pay_type' => $category,
						'reason'   => $data,
						'comment'  => $comment,
					)
				);

				break;

			case 'job':
				$updated = $employee->update_job_info(
					array(
						'id'           => $history_id,
						'date'         => $date,
						'designation'  => $comment,
						'department'   => $category,
						'reporting_to' => $data,
						'location'     => $type,
					)
				);

				break;

			case 'employment':
				$updated = $employee->update_employment_status(
					array(
						'id'       => $history_id,
						'date'     => $date,
						'module'   => $module,
						'type'     => $type,
						'comments' => $comment,
					)
				);

				break;

			default:
				$this->send_error( __( 'No valid history module found!', 'erp' ) );
		}

		if ( is_wp_error( $updated ) ) {
			$this->send_error( $updated->get_error_message() );
		}

		$this->send_success( __( 'History updated successfully', 'erp' ) );
	}

	/**
	 * Remove an history
	 *
	 * @return void
	 */
	public function employee_remove_history() {
		$this->verify_hrm_nonce();

		$history_id = isset( $_POST['history_id'] ) ? intval( $_POST['history_id'] ) : 0;
		$user_id    = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;

		// Check permission
		if ( ! current_user_can( 'erp_edit_employee', $user_id ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			$this->send_error( __( 'Invalid Employee received.', 'erp' ) );
		}

		$delete = $employee->delete_job_history( $history_id );

		if ( is_wp_error( $delete ) ) {
			$this->send_error( $delete->get_error_message() );
		}

		$this->send_success();
	}

	/**
	 * Update job information
	 *
	 * @return void
	 */
	public function employee_update_job_info() {
		$this->verify_nonce( 'employee_update_jobinfo' );

		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;

		// Check permission
		if ( ! current_user_can( 'erp_edit_employee', $user_id ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			$this->send_error( __( 'No employee found', 'erp' ) );
		}

		$old_data = $employee->get_data();

		$created = $employee->update_job_info(
			array(
				'date'         => ( isset( $_POST['date'] ) ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '',
				'designation'  => ( isset( $_POST['designation'] ) ) ? intval( wp_unslash( $_POST['designation'] ) ) : '',
				'department'   => ( isset( $_POST['department'] ) ) ? intval( wp_unslash( $_POST['department'] ) ) : '',
				'reporting_to' => ( isset( $_POST['reporting_to'] ) ) ? intval( wp_unslash( $_POST['reporting_to'] ) ) : '',
				'location'     => ( isset( $_POST['location'] ) ) ? intval( wp_unslash( $_POST['location'] ) ) : '',
			)
		);

		if ( is_wp_error( $created ) ) {
			$this->send_error( $created->get_error_message() );
		}

		do_action( 'erp_hr_employee_update', $user_id, $old_data );

		$this->send_success();
	}

	/**
	 * Add a new note
	 *
	 * @return void
	 */
	public function employee_add_note() {
		$this->verify_nonce( 'wp-erp-hr-employee-nonce' );

		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
		$note    = isset( $_POST['note'] ) ? wp_strip_all_tags( sanitize_text_field( wp_unslash( $_POST['note'] ) ) ) : 0;
		$note_by = get_current_user_id();

		$employee = new Employee( $user_id );

		if ( $employee->is_employee() ) {
			// Check permission
			if ( ! current_user_can( 'erp_edit_employee', $user_id ) ) {
				$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
			}

			$employee->add_note( $note, $note_by );
		}

		$this->send_success();
	}

	/**
	 * Employee Load more note
	 *
	 * @return json
	 */
	public function employee_load_note() {
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'wp-erp-hr-nonce' ) ) {
			$this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
		}

		$employee_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
		$total_no    = isset( $_POST['total_no'] ) ? intval( $_POST['total_no'] ) : 0;
		$offset_no   = isset( $_POST['offset_no'] ) ? intval( $_POST['offset_no'] ) : 0;

		$employee = new Employee( $employee_id );

		$notes = $employee->get_notes( $total_no, $offset_no );

		ob_start();
		include WPERP_HRM_VIEWS . '/employee/tab-notes-row.php';
		$content = ob_get_clean();

		$this->send_success( array( 'content' => $content ) );
	}

	/**
	 * Delete Note
	 *
	 * @return json
	 */
	public function employee_delete_note() {
		check_admin_referer( 'wp-erp-hr-nonce' );

		$note_id = isset( $_POST['note_id'] ) ? intval( $_POST['note_id'] ) : 0;
		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;

		$employee = new Employee( $user_id );

		// Check permission
		if ( ! current_user_can( 'erp_edit_employee', $employee->get_user_id() ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		if ( $employee->delete_note( $note_id ) ) {
			$this->send_success();
		} else {
			$this->send_error();
		}
	}

	/**
	 * Employee Termination
	 *
	 * @since 0.1
	 *
	 * @return json
	 */
	public function employee_terminate() {
		$this->verify_nonce( 'employee_update_terminate' );

		$user_id             = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
		$terminate_date      = ( empty( $_POST['terminate_date'] ) ) ? current_time( 'mysql' ) : sanitize_text_field( wp_unslash( $_POST['terminate_date'] ) );
		$termination_type    = isset( $_POST['termination_type'] ) ? sanitize_text_field( wp_unslash( $_POST['termination_type'] ) ) : '';
		$termination_reason  = isset( $_POST['termination_reason'] ) ? sanitize_text_field( wp_unslash( $_POST['termination_reason'] ) ) : '';
		$eligible_for_rehire = isset( $_POST['eligible_for_rehire'] ) ? sanitize_text_field( wp_unslash( $_POST['eligible_for_rehire'] ) ) : '';

		$fields = array(
			'user_id'             => $user_id,
			'terminate_date'      => erp_current_datetime()->modify( $terminate_date )->format( 'Y-m-d H:i:s' ),
			'termination_type'    => $termination_type,
			'termination_reason'  => $termination_reason,
			'eligible_for_rehire' => $eligible_for_rehire,
		);

		// Check permission
		if ( ! current_user_can( 'erp_edit_employee', $user_id ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$result = erp_hr_employee_terminate( $fields );

		if ( is_wp_error( $result ) ) {
			$this->send_error( $result->get_error_message() );
		}

		$this->send_success();
	}

	/**
	 * Reactive terminate employees
	 *
	 * @since 0.1
	 *
	 * @return json
	 */
	public function employee_termination_reactive() {
		$this->verify_hrm_nonce();

		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

		if ( ! $id ) {
			$this->send_error( __( 'Something went wrong! Please try again later.', 'erp' ) );
		}

		// Check permission
		if ( ! current_user_can( 'erp_edit_employee', $id ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		\WeDevs\ERP\HRM\Models\Employee::where( 'user_id', $id )->update( array( 'status' => 'active' ) );

		delete_user_meta( $id, '_erp_hr_termination' );

		$this->send_success();
	}

	/**
	 * Check for created an employee
	 *
	 * @since 1.0
	 *
	 * @return json
	 */
	public function check_user() {
		$email = isset( $_REQUEST['email'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['email'] ) ) : false;

		if ( ! $email ) {
			$this->send_error( __( 'No email address provided', 'erp' ) );
		}

		$user = get_user_by( 'email', $email );

		// we didn't found any user with this email address
		if ( false === $user ) {
			$this->send_success();
		}

		if ( null != \WeDevs\ERP\HRM\Models\Employee::withTrashed()->whereUserId( $user->ID )->first() ) {
			$employee = new \WeDevs\ERP\HRM\Employee( intval( $user->ID ) );
			$this->send_error(
				array(
					'type' => 'employee',
					'data' => $employee->to_array(),
				)
			);
		}

		// seems like we found one
		$this->send_error(
			array(
				'type' => 'wp_user',
				'data' => $user,
			)
		);
	}

	/**
	 * Create wp user to emplyee
	 *
	 * @since 1.0
	 *
	 * @return json
	 */
	public function employee_create_from_wp_user() {
		$this->verify_hrm_nonce();

		$id = isset( $_POST['user_id'] ) ? sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) : 0;

		if ( ! $id ) {
			$this->send_error( __( 'User not found', 'erp' ) );
		}

		// Check permission
		if ( ! current_user_can( 'erp_edit_employee', $id ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$user = get_user_by( 'id', intval( $id ) );

		$user->add_role( 'employee' );

		$employee = new \WeDevs\ERP\HRM\Models\Employee();
		$exists   = $employee->where( 'user_id', '=', $user->ID )->first();

		if ( null === $exists ) {
			$employee = $employee->create(
				array(
					'user_id'     => $user->ID,
					'designation' => 0,
					'department'  => 0,
					'status'      => 'active',
				)
			);

			$this->send_success( $employee );
		} else {
			$this->send_error( __( 'Employee already exist.', 'erp' ) );
		}
	}

	/**
	 * Mark Read Announcement
	 *
	 * @since 0.1
	 *
	 * @return json|bool
	 */
	public function mark_read_announcement() {
		$this->verify_hrm_nonce();

		$row_id = ( isset( $_POST['id'] ) ) ? intval( $_POST['id'] ) : '';

		if ( ! $row_id ) {
			$this->send_error();
		}

		$user_id = get_current_user_id();
		\WeDevs\ERP\HRM\Models\Announcement::find( $row_id )->where( 'user_id', $user_id )->update( array( 'status' => 'read' ) );

		return $this->send_success();
	}

	/**
	 * View single announcment
	 *
	 * @since 0.1
	 *
	 * @return json [post array]
	 */
	public function view_announcement() {
		global $post;

		$this->verify_hrm_nonce();

		$post_id = ( isset( $_POST['id'] ) ) ? intval( $_POST['id'] ) : '';

		if ( ! $post_id ) {
			$this->send_error();
		}

		\WeDevs\ERP\HRM\Models\Announcement::where( 'post_id', $post_id )->update( array( 'status' => 'read' ) );

		$post = get_post( $post_id );
		setup_postdata( $post );

		$post_data = array(
			'title'   => get_the_title(),
			'content' => wpautop( get_the_content() ),
		);

		wp_reset_postdata();

		$this->send_success( $post_data );
	}

	/**
	 * Send birthday wish
	 *
	 * @since 1.3.5
	 *
	 * @return string
	 */
	public function birthday_wish() {
		$this->verify_hrm_nonce();

		$employee_user_id = ( isset( $_POST['employee_user_id'] ) ) ? intval( wp_unslash( $_POST['employee_user_id'] ) ) : '';

		// To prevent sending wish multiple time
		// set email already sent status: true
		setcookie( $employee_user_id, true, strtotime( 'tomorrow' ) );

		$emailer = wperp()->emailer->get_email( 'BirthdayWish' );

		if ( is_a( $emailer, '\WeDevs\ERP\Email' ) ) {
			$emailer->trigger( $employee_user_id );
		}

		$this->send_success( 'Email sent!' );
	}

	/**
	 * Employee Update Performance Reviews
	 *
	 * @since 0.1
	 */
	public function employee_update_performance() {
		$this->verify_nonce( 'employee_update_performance' );

		$employee_id        = isset( $_POST['employee_id'] ) ? intval( $_POST['employee_id'] ) : 0;
		$department_lead_id = erp_hr_get_department_lead_by_user( $employee_id );

		// Check permission
		if (
			( $employee_id && ! current_user_can( 'erp_edit_employee', $employee_id ) )
			&&
			( get_current_user_id() !== $department_lead_id )
		) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';

		if ( empty( $type ) ) {
			$this->send_error( __( 'No performance type selected', 'erp' ) );
		}

		$employee = new Employee( intval( $employee_id ) );

		if ( ! $employee->is_employee() ) {
			$this->send_error( __( 'Could not find the employee', 'erp' ) );
		}

		$performance = $employee->add_performance( $_POST );

		if ( is_wp_error( $performance ) ) {
			$this->send_error( $performance->get_error_message() );
		}

		$this->send_success();
	}

	/**
	 * Remove an Prformance
	 *
	 * @return void
	 */
	public function employee_delete_performance() {
		$this->verify_hrm_nonce();

		$id      = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;

		$department_lead_id = erp_hr_get_department_lead_by_user( $user_id );

		// Check permission
		if ( ! current_user_can( 'erp_delete_review', $user_id )
			&&
			( get_current_user_id() !== $department_lead_id )
		) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		\WeDevs\ERP\HRM\Models\Performance::find( $id )->delete();

		$this->send_success();
	}

	/**
	 * Add/edit work experience
	 *
	 * @return void
	 */
	public function employee_work_experience_create() {
		$this->verify_nonce( 'erp-work-exp-form' );

		$employee_id = isset( $_POST['employee_id'] ) ? intval( $_POST['employee_id'] ) : 0;

		// Check permission
		if ( ! current_user_can( 'erp_edit_employee', $employee_id ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$exp_id       = isset( $_POST['exp_id'] ) ? intval( $_POST['exp_id'] ) : 0;
		$company_name = isset( $_POST['company_name'] ) ? wp_strip_all_tags( sanitize_text_field( wp_unslash( $_POST['company_name'] ) ) ) : '';
		$job_title    = isset( $_POST['job_title'] ) ? wp_strip_all_tags( sanitize_text_field( wp_unslash( $_POST['job_title'] ) ) ) : '';
		$from         = isset( $_POST['from'] ) ? wp_strip_all_tags( sanitize_text_field( wp_unslash( $_POST['from'] ) ) ) : '';
		$to           = isset( $_POST['to'] ) ? wp_strip_all_tags( sanitize_text_field( wp_unslash( $_POST['to'] ) ) ) : '';
		$description  = isset( $_POST['description'] ) ? wp_strip_all_tags( sanitize_text_field( wp_unslash( $_POST['description'] ) ) ) : '';

		$fields = array(
			'id'           => $exp_id,
			'company_name' => $company_name,
			'job_title'    => $job_title,
			'from'         => $from,
			'to'           => $to,
			'description'  => $description,
		);

		$employee = new Employee( $employee_id );

		if ( ! $employee->is_employee() ) {
			$this->send_error( __( 'You have to be an employee to do this action', 'erp' ) );
		}

		$employee->add_experience( $fields );

		$this->send_success();
	}

	/**
	 * Delete a work experience
	 *
	 * @return void
	 */
	public function employee_work_experience_delete() {
		$this->verify_hrm_nonce();

		$id          = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$employee_id = isset( $_POST['employee_id'] ) ? intval( $_POST['employee_id'] ) : 0;

		$employee = new Employee( $employee_id );

		if ( ! $employee->is_employee() ) {
			$this->send_error( __( 'No employee found', 'erp' ) );
		}

		// Check permission
		if ( ! current_user_can( 'erp_edit_employee', $employee_id ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		if ( $id ) {
			do_action( 'erp_hr_employee_experience_delete', $id );
			$employee->delete_experience( $id );
		}

		$this->send_success();
	}

	/**
	 * Create/edit educational experiences
	 *
	 * @return void
	 */
	public function employee_education_create() {
		$this->verify_nonce( 'erp-hr-education-form' );

		$employee_id = isset( $_POST['employee_id'] ) ? intval( $_POST['employee_id'] ) : 0;

		// Check permission
		if ( ! current_user_can( 'erp_edit_employee', $employee_id ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$edu_id       = isset( $_POST['edu_id'] ) ? intval( $_POST['edu_id'] ) : 0;
		$school       = isset( $_POST['school'] ) ? sanitize_text_field( wp_unslash( $_POST['school'] ) ) : '';
		$degree       = isset( $_POST['degree'] ) ? sanitize_text_field( wp_unslash( $_POST['degree'] ) ) : '';
		$field        = isset( $_POST['field'] ) ? sanitize_text_field( wp_unslash( $_POST['field'] ) ) : '';
		$result_type  = isset( $_POST['result_type'] ) ? sanitize_text_field( wp_unslash( $_POST['result_type'] ) ) : null;
		$finished     = isset( $_POST['finished'] ) ? intval( $_POST['finished'] ) : '';
		$notes        = isset( $_POST['notes'] ) ? sanitize_text_field( wp_unslash( $_POST['notes'] ) ) : '';
		$interest     = isset( $_POST['interest'] ) ? sanitize_text_field( wp_unslash( $_POST['interest'] ) ) : '';
		$exp_date     = isset( $_POST['expiration_date'] ) ? sanitize_text_field( wp_unslash( $_POST['expiration_date'] ) ) : '';
		$result_gpa   = isset( $_POST['gpa'] ) ? sanitize_text_field( wp_unslash( $_POST['gpa'] ) ) : null;
		$result_scale = isset( $_POST['scale'] ) ? sanitize_text_field( wp_unslash( $_POST['scale'] ) ) : null;

		$result = array( 'gpa' => $result_gpa );

		if ( 'grade' === $result_type ) {
			$result['scale'] = $result_scale;
		}

		$fields = array(
			'id'              => $edu_id,
			'school'          => $school,
			'degree'          => $degree,
			'field'           => $field,
			'result'          => wp_json_encode( $result ),
			'result_type'     => $result_type,
			'finished'        => $finished,
			'notes'           => $notes,
			'interest'        => $interest,
			'expiration_date' => $exp_date,
		);

		$employee = new Employee( $employee_id );

		if ( ! $employee->is_employee() ) {
			$this->send_error( __( 'You have to be an employee to do this action', 'erp' ) );
		}

		$employee->add_education( $fields );

		$this->send_success();
	}

	/**
	 * Delete a work experience
	 *
	 * @return void
	 */
	public function employee_education_delete() {
		$this->verify_hrm_nonce();

		$id          = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$employee_id = isset( $_POST['employee_id'] ) ? intval( $_POST['employee_id'] ) : 0;

		$employee = new Employee( $employee_id );

		if ( ! $employee->is_employee() ) {
			$this->send_error( __( 'No employee found', 'erp' ) );
		}

		// Check permission
		if ( ! current_user_can( 'erp_edit_employee', $employee_id ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		if ( $id ) {
			do_action( 'erp_hr_employee_education_delete', $id );
			$employee->delete_education( $id );
		}

		$this->send_success();
	}

	/**
	 * Create/edit dependents
	 *
	 * @return void
	 */
	public function employee_dependent_create() {
		$this->verify_nonce( 'erp-hr-dependent-form' );

		$employee_id = isset( $_POST['employee_id'] ) ? intval( $_POST['employee_id'] ) : 0;

		// Check permission
		if ( ! current_user_can( 'erp_edit_employee', $employee_id ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$dep_id   = isset( $_POST['dep_id'] ) ? intval( $_POST['dep_id'] ) : 0;
		$name     = isset( $_POST['name'] ) ? wp_strip_all_tags( sanitize_text_field( wp_unslash( $_POST['name'] ) ) ) : '';
		$relation = isset( $_POST['relation'] ) ? wp_strip_all_tags( sanitize_text_field( wp_unslash( $_POST['relation'] ) ) ) : '';
		$dob      = isset( $_POST['dob'] ) ? wp_strip_all_tags( sanitize_text_field( wp_unslash( $_POST['dob'] ) ) ) : '';

		$fields = array(
			'id'       => $dep_id,
			'name'     => $name,
			'relation' => $relation,
			'dob'      => $dob,
		);

		$employee = new Employee( $employee_id );

		if ( ! $employee->is_employee() ) {
			$this->send_error( __( 'You have to be an employee to do this action', 'erp' ) );
		}

		$employee->add_dependent( $fields );

		$this->send_success();
	}

	/**
	 * Delete a dependent
	 *
	 * @return void
	 */
	public function employee_dependent_delete() {
		$this->verify_hrm_nonce();

		$id          = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$employee_id = isset( $_POST['employee_id'] ) ? intval( $_POST['employee_id'] ) : 0;

		$employee = new Employee( $employee_id );

		if ( ! $employee->is_employee() ) {
			$this->send_error( __( 'No employee found', 'erp' ) );
		}

		// Check permission
		if ( ! current_user_can( 'erp_edit_employee', $employee_id ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		if ( $id ) {
			do_action( 'erp_hr_employee_dependents_delete', $id );
			$employee->delete_dependent( $id );
		}

		$this->send_success();
	}

	/**
	 * Create or update a holiday
	 *
	 * @since 0.1
	 *
	 * @return void
	 */
	public function holiday_create() {
		$this->verify_nonce( 'erp-leave-holiday' );

		// Check permission
		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$holiday_id   = isset( $_POST['holiday_id'] ) ? intval( $_POST['holiday_id'] ) : 0;
		$title        = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$start_date   = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
		$end_date     = isset( $_POST['end_date'] ) && ! empty( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : $start_date;
		$end_date     = gmdate( 'Y-m-d 23:59:59', strtotime( $end_date ) );
		$description  = isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '';
		$range_status = isset( $_POST['range'] ) ? sanitize_text_field( wp_unslash( $_POST['range'] ) ) : 'off';
		$error        = true;

		if ( $range_status === 'off' ) {
			$end_date = gmdate( 'Y-m-d 23:59:59', strtotime( $start_date ) );
		}

		if ( is_wp_error( $error ) ) {
			$this->send_error( $error->get_error_message() );
		}

		$holiday_id = erp_hr_leave_insert_holiday(
			array(
				'id'          => $holiday_id,
				'title'       => $title,
				'start'       => $start_date,
				'end'         => $end_date,
				'description' => $description,
			)
		);

		if ( is_wp_error( $holiday_id ) ) {
			$this->send_error( $holiday_id->get_error_message() );
		}

		$this->send_success();
	}

	/**
	 * Delete a leave policy
	 *
	 * @since 0.1
	 *
	 * @return void
	 */
	public function leave_policy_delete() {
		$this->verify_hrm_nonce();

		// Check permission
		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

		if ( $id ) {
			erp_hr_leave_policy_delete( $id );

			$this->send_success( __( 'Policy has been deleted', 'erp' ) );
		}

		$this->send_error( __( 'Something went worng!', 'erp' ) );
	}

	/**
	 * Gets the leave dates
	 *
	 * Returns the date list between the start and end date of the
	 * two dates
	 *
	 * @since 0.1
	 *
	 * @return void
	 */
	public function leave_request_dates() {
		$this->verify_hrm_nonce();

		$id = isset( $_POST['employee_id'] ) && ! empty( $_POST['employee_id'] ) ? intval( $_POST['employee_id'] ) : false;

		if ( ! $id ) {
			$this->send_error( esc_attr__( 'Please select an employee', 'erp' ) );
		}

		$policy_id = isset( $_POST['type'] ) && ! empty( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : false;

		if ( ! $policy_id ) {
			$this->send_error( esc_attr__( 'Please select a policy', 'erp' ) );
		}

		$start_date = isset( $_POST['from'] ) ? sanitize_text_field( wp_unslash( $_POST['from'] ) ) : date_i18n( 'Y-m-d' );
		$end_date   = isset( $_POST['to'] ) ? sanitize_text_field( wp_unslash( $_POST['to'] ) ) : date_i18n( 'Y-m-d' );

		if ( $start_date > $end_date ) {
			$this->send_error( esc_attr__( 'Invalid date range', 'erp' ) );
		}

		// check if start_date and end_dates are in same f_year
		$entitlement = LeaveEntitlement::find( $policy_id );

		if ( ! $entitlement ) {
			$this->send_error( esc_attr__( 'Invalid leave policy.', 'erp' ) );
		}

		$f_year_start = erp_current_datetime()->setTimestamp( $entitlement->financial_year->start_date )->format( 'Y-m-d' );
		$f_year_end   = erp_current_datetime()->setTimestamp( $entitlement->financial_year->end_date )->format( 'Y-m-d' );

		if ( ( $start_date < $f_year_start || $start_date > $f_year_end ) || ( $end_date < $f_year_start || $end_date > $f_year_end ) ) {
			$this->send_error( sprintf( esc_attr__( 'Invalid leave duration. Please apply between %1$s and %2$s.', 'erp' ), erp_format_date( $f_year_start ), erp_format_date( $f_year_end ) ) );
		}

		// handle overlapped leaves
		$leave_record_exist = erp_hrm_is_leave_recored_exist_between_date( $start_date, $end_date, $id, $entitlement->f_year );

		if ( $leave_record_exist ) {
			$this->send_error( esc_attr__( 'Existing Leave Record found within selected range!', 'erp' ) );
		}

		$is_extra_leave_enabled = get_option( 'enable_extra_leave', 'no' );

		if ( $is_extra_leave_enabled !== 'yes' ) {
			$is_policy_valid = erp_hrm_is_valid_leave_duration( $start_date, $end_date, $policy_id, $id );

			if ( ! $is_policy_valid ) {
				$this->send_error( esc_attr__( 'Sorry! You do not have any leave left under this leave policy', 'erp' ) );
			}
		}

		$days = erp_hr_get_work_days_between_dates( $start_date, $end_date, $id );

		if ( is_wp_error( $days ) ) {
			$this->send_error( $days->get_error_message() );
		}

		// just a bit more readable date format
		foreach ( $days['days'] as &$date ) {
			$date['date'] = erp_format_date( $date['date'], 'D, M d' );
		}

		$leave_count   = $days['total'];
		$days['total'] = sprintf( '%d %s', $days['total'], _n( 'day', 'days', $days['total'], 'erp' ) );

		if ( intval( $days['sandwich'] ) === 1 ) {
			$days['total'] .= ' ' . esc_attr__( '(Sandwich rule applied)', 'erp' );
		}

		$this->send_success(
			array(
				'print'       => $days,
				'leave_count' => $leave_count,
			)
		);
	}

	/**
	 * Fetch assigning policy dropdown html
	 * according to employee id
	 *
	 * @since 0.1
	 *
	 * @return html|json
	 */
	public function leave_assign_employee_policy() {
		$this->verify_hrm_nonce();

		$employee_id = isset( $_POST['employee_id'] ) && ! empty( $_POST['employee_id'] ) ? intval( $_POST['employee_id'] ) : false;
		$f_year      = isset( $_POST['f_year'] ) && ! empty( $_POST['f_year'] ) ? intval( $_POST['f_year'] ) : false;

		if ( ! $employee_id ) {
			$this->send_error( esc_attr__( 'Please select an employee.', 'erp' ) );
		}

		if ( ! $f_year ) {
			$this->send_error( esc_attr__( 'Please select a year.', 'erp' ) );
		}

		// Check permission
		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			$error_string = esc_html__( 'No entitlement found for selected year. Please contact with HR.', 'erp' );
		}

		$financial_year = FinancialYear::find( $f_year );

		if ( ! $financial_year ) {
			$this->send_error( esc_attr__( 'Invalid year.', 'erp' ) );
		}

		$policies = erp_hr_get_assign_policy_from_entitlement( $employee_id, $financial_year->start_date );

		if ( $policies ) {
			ob_start();
			erp_html_form_input(
				array(
					'label'    => __( 'Leave Type', 'erp' ),
					'name'     => 'leave_policy',
					'id'       => 'erp-hr-leave-req-leave-policy',
					'value'    => '',
					'required' => true,
					'type'     => 'select',
					'options'  => array( '' => __( '- Select -', 'erp' ) ) + array_unique( $policies ),
				)
			);
			$content = ob_get_clean();

			return $this->send_success( $content );
		}

		$error_string = esc_html__( 'Employee is not entitled to any leave policy. Set leave entitlement to apply for leave.', 'erp' );

		return $this->send_error( $error_string );
	}

	/**
	 * Get available day for users leave policy
	 *
	 * @since 0.1
	 *
	 * @return json
	 */
	public function leave_available_days() {
		$this->verify_hrm_nonce();

		$employee_id = ! empty( $_POST['employee_id'] ) ? intval( $_POST['employee_id'] ) : false;
		$policy_id   = ! empty( $_POST['policy_id'] ) ? intval( $_POST['policy_id'] ) : false; // @since 1.6.0 this is now entitlement id
		$available   = 0;

		if ( ! $employee_id ) {
			$this->send_error( __( 'Please select an employee', 'erp' ) );
		}

		if ( ! $policy_id ) {
			$this->send_error( __( 'Please select a policy', 'erp' ) );
		}

		$balance = erp_hr_leave_get_balance_for_single_entitlement( $policy_id );

		if ( array_key_exists( 'available', $balance ) ) {
			$available = $balance['available'];
		}

		if ( array_key_exists( 'extra_leave', $balance ) ) {
			$extra_leaves = $balance['extra_leave'];
		}

		if ( $available <= 0 ) {
			$content = sprintf( '<span class="description red"> %d %s</span>', erp_number_format_i18n( $available ), _n( 'day available', 'days are available', $available + 1, 'erp' ) );
		} elseif ( $available > 0 ) {
			$content = sprintf( '<span class="description green"> %s %s</span>', erp_number_format_i18n( $available ), __( 'days are available', 'erp' ) );
		} else {
			// $leave_policy_day = \WeDevs\ERP\HRM\Models\LeavePolicy::select( 'value' )->where( 'id', $policy_id )->pluck( 'value' );
			// $content          = sprintf( '<span class="description">%d %s</span>', number_format_i18n( $leave_policy_day ), __( 'days are available', 'erp' ) );
		}

		if ( $extra_leaves > 0 ) {
			$content .= sprintf( '<span class="description red"> (%s %s)</span>', erp_number_format_i18n( $extra_leaves ), _n( 'day extra', 'days extra', $extra_leaves, 'erp' ) );
		}

		$this->send_success( $content );
	}

	/**
	 * Insert leave request for users
	 *
	 * Save leave request data from employee dashboard
	 * overview area
	 *
	 * @since 0.1
	 *
	 * @return json
	 */
	public function leave_request() {
		$this->verify_nonce( 'erp-leave-req-new' );

		$employee_id  = isset( $_POST['employee_id'] ) ? intval( $_POST['employee_id'] ) : 0;
		$leave_policy = isset( $_POST['leave_policy'] ) ? intval( $_POST['leave_policy'] ) : 0;

		if ( ! current_user_can( 'erp_leave_create_request', $employee_id ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		if ( empty( trim( sanitize_text_field( wp_unslash( $_POST['leave_reason'] ) ) ) ) ) {
			$this->send_error( __( 'Leave reason field can not be blank', 'erp' ) );
		}

		// @todo: date format may need to be changed when partial leave introduced
		$start_date = isset( $_POST['leave_from'] ) ? sanitize_text_field( wp_unslash( $_POST['leave_from'] . ' 00:00:00' ) ) : date_i18n( 'Y-m-d 00:00:00' );
		$end_date   = isset( $_POST['leave_to'] ) ? sanitize_text_field( wp_unslash( $_POST['leave_to'] . ' 23:59:59' ) ) : date_i18n( 'Y-m-d 23:59:59' );

		$leave_reason = isset( $_POST['leave_reason'] ) ? wp_strip_all_tags( sanitize_text_field( wp_unslash( $_POST['leave_reason'] ) ) ) : '';

		$request_id = erp_hr_leave_insert_request(
			array(
				'user_id'      => $employee_id,
				'leave_policy' => $leave_policy,
				'start_date'   => $start_date,
				'end_date'     => $end_date,
				'reason'       => $leave_reason,
			)
		);

		if ( ! is_wp_error( $request_id ) ) {

			// notification email.
			$emailer = wperp()->emailer->get_email( 'NewLeaveRequest' );

			if ( is_a( $emailer, '\WeDevs\ERP\Email' ) ) {
				$emailer->trigger( $request_id );
			}

			$this->send_success( __( 'Leave request has been submitted successfully!', 'erp' ) );
		} elseif ( is_wp_error( $request_id ) ) {
			$this->send_error( $request_id->get_error_message() );
		} else {
			$this->send_error( __( 'Something went wrong! Please try again later.', 'erp' ) );
		}

		exit();
	}

	/**
	 * Get employee leave history
	 *
	 * @since 0.1
	 *
	 * @return void
	 */
	public function get_employee_leave_history() {
		$this->verify_nonce( 'erp-hr-empl-leave-history' );

		$year    = isset( $_POST['f_year'] ) ? intval( $_POST['f_year'] ) : gmdate( 'Y' );
		$user_id = isset( $_POST['employee_id'] ) ? intval( $_POST['employee_id'] ) : 0;
		$policy  = isset( $_POST['leave_policy'] ) ? intval( $_POST['leave_policy'] ) : 'all';
		$status  = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'all';

		$args = array(
			'f_year'  => $year,
			'user_id' => $user_id,
			'status'  => $status,
			'orderby' => 'start_date',
		);

		if ( $policy !== 'all' ) {
			$args['policy_id'] = $policy;
		}

		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			$this->send_error( __( 'Invalid request permission.', 'erp' ) );
		}

		$requests = $employee->get_leave_requests( $args );

		ob_start();
		include WPERP_HRM_VIEWS . '/employee/tab-leave-history.php';
		$content = ob_get_clean();

		$this->send_success( $content );
	}

	/**
	 * Get leave & holiday by date
	 */
	public function get_leave_holiday_by_date() {
		$this->verify_hrm_nonce();

		$start = isset( $_POST['start'] ) ? sanitize_text_field( wp_unslash( $_POST['start'] ) ) : erp_current_datetime()->modify( 'start of this month' )->format( 'Y-m-d H:i:s' );
		$end   = isset( $_POST['end'] ) ? sanitize_text_field( wp_unslash( $_POST['end'] ) ) : erp_current_datetime()->modify( 'end of this month' )->format( 'Y-m-d H:i:s' );

		$start = erp_current_datetime()->modify( $start )->setTime( 0, 0, 0 );
		$end   = erp_current_datetime()->modify( $end )->setTime( 23, 59, 59 );

		$user_id        = get_current_user_id();
		$args           = array(
			'user_id'    => $user_id,
			'status'     => 'all',
			'number'     => '-1',
			'start_date' => $start->getTimestamp(),
			'end_date'   => $end->getTimestamp(),
		);
		$leave_requests = erp_hr_get_leave_requests( $args, false );
		$leave_requests = $leave_requests['data'];

		$start_date = $start->format( 'Y-m-d H:i:s' );
		$end_date   = $end->format( 'Y-m-d H:i:s' );

		$holiday = new \WeDevs\ERP\HRM\Models\LeaveHoliday();
		$holiday = $holiday->where(
			function ( $condition ) use ( $start_date ) {
				$condition->where( 'start', '<=', $start_date );
				$condition->where( 'end', '>=', $start_date );
			}
		);
		$holiday = $holiday->orWhere(
			function ( $condition ) use ( $end_date ) {
				$condition->where( 'start', '<=', $end_date );
				$condition->where( 'end', '>=', $end_date );
			}
		);
		$holiday = $holiday->orWhere(
			function ( $condition ) use ( $start_date, $end_date ) {
				$condition->where( 'start', '>=', $start_date );
				$condition->where( 'start', '<=', $end_date );
			}
		);
		$holiday = $holiday->orWhere(
			function ( $condition ) use ( $start_date, $end_date ) {
				$condition->where( 'end', '>=', $start_date );
				$condition->where( 'end', '<=', $end_date );
			}
		);

		$holidays        = $holiday->get()->toArray();
		$match_holidays  = array();
		$filter_holidays = apply_filters( 'filter_holidays', array(), $start_date, $end_date );

		if ( empty( $filter_holidays ) ) {
			$weekends  = array();
			$work_days = erp_hr_get_work_days();

			array_walk(
				$work_days,
				function ( $value, $key ) use ( &$weekends ) {
					if ( 0 === (int) $value ) {
						$weekends[] = $key;
					}
				}
			);

			$dates = new \DatePeriod(
				new \DateTime( $start_date ),
				new \DateInterval( 'P1D' ),
				new \DateTime( $end_date )
			);

			foreach ( $dates as $index => $date ) {
				$weekday = strtolower( $date->format( 'D' ) );
				if ( in_array( $weekday, $weekends ) ) {
					$match_holidays[] = array(
						'title'      => __( 'Weekly Holiday', 'erp' ),
						'start'      => erp_current_datetime()
							->modify( $date->format( 'Y-m-d' ) )
							->setTime( 0, 0, 0 )->format( 'Y-m-d' ),
						'end'        => erp_current_datetime()
							->modify( $date->format( 'Y-m-d' ) )
							->setTime( 23, 59, 59 )->format( 'Y-m-d' ),
						'id'         => $index,
						'background' => true,
					);
				}
			}
		}

		$holidays = array_merge( $holidays, $filter_holidays, $match_holidays );

		$events         = array();
		$holiday_events = array();
		$event_data     = array();

		foreach ( $leave_requests as $key => $leave_request ) {
			if ( 3 == $leave_request->status ) {
				continue;
			}
			// if status pending
			$event_label = $leave_request->policy_name;

			if ( 2 == $leave_request->status ) {
				$event_label .= sprintf( ' ( %s ) ', __( 'Pending', 'erp' ) );
			}

			// Half day leave
			if ( $leave_request->day_status_id != 1 ) {
				$event_label .= '(' . erp_hr_leave_request_get_day_statuses( $leave_request->day_status_id ) . ')';
			}

			$events[] = array(
				'id'     => $leave_request->id,
				'title'  => $event_label,
				'start'  => erp_current_datetime()->setTimestamp( $leave_request->start_date )->setTime( 0, 0, 0 )->format( 'Y-m-d H:i:s' ),
				'end'    => erp_current_datetime()->setTimestamp( $leave_request->end_date )->setTime( 23, 59, 59 )->format( 'Y-m-d H:i:s' ),
				'url'    => /*erp_hr_url_single_employee( $leave_request->user_id, 'leave' )*/ 'javascript:void(0)',
				'go_to'  => erp_hr_url_single_employee( $leave_request->user_id, 'leave' ),
				'color'  => $leave_request->color,
				'reason' => $leave_request->reason,
			);
		}

		foreach ( erp_array_to_object( $holidays ) as $key => $holiday ) {
			$holiday_events[] = array(
				'id'        => $holiday->id,
				'title'     => $holiday->title,
				'start'     => $holiday->start,
				'end'       => $holiday->end,
				'img'       => '',
				'url'       => 'javascript:void(0)',
				'holiday'   => true,
				'rendering' => isset( $holiday->background )
								&& $holiday->background ? 'background' : '',
				'color'     => isset( $holiday->background )
								&& $holiday->background ? '#c5bfbf' : '#FF5354',
			);
		}

		$event_data = array_merge( $events, $holiday_events );

		$this->send_success( $event_data );
	}

	/**
	 * Retrieves employee requests
	 *
	 * @since 1.8.5
	 *
	 * @return mixed
	 */
	public function get_employee_requests() {
		$this->verify_hrm_nonce();

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'erp_hr_manager' ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$request_type = ! empty( $_REQUEST['type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['type'] ) ) : '';

		if ( empty( $request_type ) || ! array_key_exists( $request_type, erp_hr_get_employee_requests_types() ) ) {
			$this->send_error( __( 'Invalid request type!', 'erp' ) );
		}

		if ( isset( $_REQUEST['date'] ) ) {
			$args['date'] = array_map( 'sanitize_text_field', (array) wp_unslash( $_REQUEST['date'] ) );
		}

		$page_no          = ! empty( $_REQUEST['page'] ) ? intval( wp_unslash( $_REQUEST['page'] ) ) : 1;
		$args['status']   = ! empty( $_REQUEST['status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) : '';
		$args['order_by'] = ! empty( $_REQUEST['order_by'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order_by'] ) ) : '';
		$args['order']    = ! empty( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : '';
		$args['user_id']  = ! empty( $_REQUEST['user_id'] ) ? intval( wp_unslash( $_REQUEST['user_id'] ) ) : 0;
		$args['number']   = ! empty( $_REQUEST['per_page'] ) ? intval( wp_unslash( $_REQUEST['per_page'] ) ) : 20;
		$args['offset']   = ( $page_no - 1 ) * $args['number'];

		switch ( $request_type ) {
			case 'leave':
				if ( ! empty( $args['date'] ) && ! empty( $args['date']['start'] ) && ! empty( $args['date']['end'] ) ) {
					$args['start_date'] = $args['date']['start'];
					$args['end_date']   = $args['date']['end'];

					unset( $args['date'] );
				}

				$results     = erp_hr_get_leave_requests( $args );
				$date_format = erp_get_date_format( 'Y-m-d' );

				if ( is_wp_error( $results ) ) {
					$this->send_error( __( 'Something went wrong! Please try again later.', 'erp' ) );
				}

				$requests = array();
				$data     = array();

				foreach ( $results['data'] as $result ) {
					$result = (array) $result;

					$data[] = array(
						'id'         => $result['id'],
						'employee'   => array(
							'id'   => $result['user_id'],
							'name' => $result['name'],
							'url'  => add_query_arg(
								array( 'id' => $result['user_id'] ),
								admin_url( 'admin.php?page=erp-hr&section=people&sub-section=employee&action=view' )
							),
						),
						'status'     => array(
							'id'    => $result['status'],
							'title' => $result['status'] == 1 ? __( 'Approved', 'erp' ) : (
										$result['status'] == 2 ? __( 'Pending', 'erp' ) : (
										$result['status'] == 3 ? __( 'Rejected', 'erp' ) : ''
									) ),
						),
						'duration'   => (int) $result['days'],
						'type'       => array(
							'id'    => 'leave',
							'title' => __( 'Leave', 'erp' ),
						),
						'reason'     => array(
							'id'    => $result['reason'],
							'title' => $result['reason'],
						),
						'start_date' => erp_format_date( $result['start_date'], $date_format ),
						'end_date'   => erp_format_date( $result['end_date'], $date_format ),
					);
				}

				$requests = array(
					'data'        => ! empty( $data ) ? $data : array(),
					'total_items' => ! empty( $results['total'] ) ? $results['total'] : 0,
				);

				break;

			default:
				$requests = apply_filters( "erp_hr_get_employee_{$request_type}_requests", $args, $request_type );
		}

		if ( is_wp_error( $requests ) ) {
			$this->send_error( __( 'Something went wrong! Please try again later.', 'erp' ) );
		}

		$this->send_success( $requests );
	}

	/**
	 * Retrieves total pending requests
	 *
	 * @since 1.8.5
	 *
	 * @return int
	 */
	public function get_total_pending_requests() {
		$requests = erp_hr_get_employee_pending_requests_count();
		$pending  = 0;

		foreach ( $requests as $count ) {
			$pending += (int) $count;
		}

		$this->send_success( $pending );
	}

	/**
	 * Processes bulk action on employee requests
	 *
	 * @since 1.8.5
	 *
	 * @return mixed
	 */
	public function employee_requests_bulk_action() {
		$this->verify_hrm_nonce();

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'erp_hr_manager' ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$request_type = ! empty( $_REQUEST['req_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['req_type'] ) ) : '';  // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( empty( $request_type ) || ! array_key_exists( $request_type, erp_hr_get_employee_requests_types() ) ) {
			$this->send_error( __( 'Invalid request type!', 'erp' ) );
		}

		$req_ids = array();

		if ( ! empty( $_REQUEST['req_id'] ) ) {
			$req_ids = array_map( 'intval', (array) wp_unslash( $_REQUEST['req_id'] ) );
		}

		$action = ! empty( $_REQUEST['action_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action_type'] ) ) : '';

		$result = (array) apply_filters( "erp_hr_employee_{$request_type}_request_bulk_action", $req_ids, $action );

		if ( is_wp_error( $result ) ) {
			$this->send_error( __( 'Something went wrong! Please try again later.', 'erp' ) );
		}

		if ( 0 === count( $result ) && 'deleted' !== $action ) {
			$this->send_error( __( 'No pending item found. Selected item(s) are already approved/rejected.', 'erp' ) );
		}

		$item_status = 'deleted' !== $action ? 'pending ' : '';

		if ( 1 === count( $result ) ) {
			$this->send_success( sprintf( __( '1 %1$s item has been %2$s successfully', 'erp' ), $item_status, $action ) );
		}

		$this->send_success(
			// translators: 1) no of items, 2) item status, 3) action
			sprintf( __( '%1$d %2$s items have been %3$s successfully', 'erp' ), count( $result ), $item_status, $action )
		);
	}

	/**
	 * Get Settings Data For HR Financial years
	 *
	 * @since 1.8.6
	 *
	 * @return void
	 */
	public function erp_settings_get_hr_financial_years() {
		$this->verify_nonce( 'erp-settings-nonce' );

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'erp_hr_manager' ) ) {
			$this->send_error( erp_get_message( array( 'type' => 'error_permission' ) ) );
		}

		$years = erp_get_hr_financial_years();

		$this->send_success( $years );
	}

	/**
	 * Save Settings for HR Financial Years
	 *
	 * @since 1.8.6
	 *
	 * @return void
	 */
	public function erp_settings_save_hr_financial_years() {
		$this->verify_nonce( 'erp-settings-nonce' );

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'erp_hr_manager' ) ) {
			$this->send_error( erp_get_message( array( 'type' => 'error_permission' ) ) );
		}

		if ( empty( $_POST['fyears'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$this->send_error(
				erp_get_message(
					array(
						'type' => 'error',
						'text' => __( 'Financial year is required', 'erp' ),
					)
				)
			);
		}

		$f_years  = map_deep( wp_unslash( $_POST['fyears'] ), 'sanitize_text_field' );  // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$inserted = erp_settings_save_leave_years( $f_years );

		if ( is_wp_error( $inserted ) ) {
			$this->send_error( $inserted->get_error_message() );
		}

		$this->send_success(
			array(
				'data'    => $inserted,
				'message' => __( 'Settings saved successfully !', 'erp' ),
			)
		);
	}

	/**
	 * Delete a leave type
	 *
	 * @since 1.10.0
	 *
	 * @return void
	 */
	public function leave_type_delete() {
		$this->verify_hrm_nonce();

		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$id = ! empty( $_POST['id'] ) ? intval( $_POST['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( empty( $id ) ) {
			$this->send_error( __( 'No valid leave type found!', 'erp' ) );
		}

		$result = erp_hr_remove_leave_policy_name( $id );

		if ( is_wp_error( $result ) ) {
			$this->send_error( $result->get_error_message() );
		}

		$this->send_success( __( 'Leave Type has been deleted', 'erp' ) );
	}

	/**
	 * Bulk Delete leave types
	 *
	 * @since 1.10.0
	 *
	 * @return void
	 */
	public function leave_type_bulk_delete() {
		$this->verify_hrm_nonce();

		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$ids = array();

		if ( ! empty( $_POST['ids'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$ids = array_map( 'intval', wp_unslash( $_POST['ids'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		if ( empty( $ids ) ) {
			$this->send_error( __( 'No valid leave type found!', 'erp' ) );
		}

		$deleted = 0;

		foreach ( $ids as $id ) {
			if ( ! is_wp_error( erp_hr_remove_leave_policy_name( $id ) ) ) {
				++$deleted;
			}
		}

		if ( $deleted === 0 ) {
			$this->send_error( __( 'No items were deleted as they are associated with policy', 'erp' ) );
		} else {
			// translators: number of items deleted
			$this->send_success( sprintf( __( '%s items deleted successfully', 'erp' ), $deleted ) );
		}
	}

	/**
	 * Create or update a new leave type
	 *
	 * @since 1.10.0
	 *
	 * @return void
	 */
	public function leave_type_create_or_update() {
		$this->verify_hrm_nonce();

		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$id          = ! empty( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$name        = ! empty( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$description = ! empty( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( empty( $name ) ) {
			$this->send_error( __( 'Name field should not be left empty', 'erp' ) );
		}

		$args = array(
			'name'        => $name,
			'description' => $description,
		);

		if ( $id ) {
			$args['id'] = $id;
		}

		$leave_type = erp_hr_insert_leave_policy_name( $args );

		if ( is_wp_error( $leave_type ) ) {
			$this->send_error( $leave_type->get_error_message() );
		}

		if ( $id ) {
			$this->send_success( __( 'Leave Type has been updated', 'erp' ) );
		} else {
			$this->send_success( __( 'Leave Type has been created', 'erp' ) );
		}
	}

	/**
	 * Get a leave type by id
	 *
	 * @since 1.10.0
	 *
	 * @return void
	 */
	public function get_leave_type() {
		$this->verify_hrm_nonce();

		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			$this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$id         = ! empty( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$leave_type = \WeDevs\ERP\HRM\Models\Leave::find( $id );

		if ( empty( $leave_type ) ) {
			$this->send_error( __( 'No valid leave type found!', 'erp' ) );
		}

		$this->send_success( $leave_type );
	}

	/**
	 * Verify default nonce for HRM Module.
	 *
	 * @since 1.10.6
	 *
	 * @return void
	 */
	private function verify_hrm_nonce() {
		$this->verify_nonce( 'wp-erp-hr-nonce' );
	}
}
