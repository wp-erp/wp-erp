<?php

namespace WeDevs\ERP\HRM;

use stdClass;
use WeDevs\ERP\ErpErrors;
use WeDevs\ERP\HRM\Models\FinancialYear;
use WeDevs\ERP\HRM\Models\LeavePolicy;
use WP_Error;

/**
 * Handle the form submissions
 *
 * Although our most of the forms uses ajax and popup, some
 * are needed to submit via regular form submits. This class
 * Handles those form submission in this module
 */
class FormHandler {

	/**
	 * Hook 'em all
	 */
	public function __construct() {
		add_action( 'erp_action_hr-leave-assign-policy', array( $this, 'leave_entitlement' ) );
		add_action( 'erp_action_hr-leave-req-new', array( $this, 'leave_request' ) );

		// permission
		add_action( 'erp_action_erp-hr-employee-permission', array( $this, 'employee_permission' ) );

		// add_action( 'admin_init', array( $this, 'leave_request_status_change' ) );
		add_action( 'admin_init', array( $this, 'handle_employee_status_update' ) );
		add_action( 'admin_init', array( $this, 'handle_leave_calendar_filter' ) );
		add_action( 'admin_init', array( $this, 'insert_financial_years' ) );
		add_action( 'load-wp-erp_page_erp-hr', array( $this, 'handle_actions' ) );

		// $hr_management = sanitize_title( esc_html__( 'HR Management', 'erp' ) );

		// add_action( "load-{$hr_management}_page_erp-hr-employee", array( $this, 'employee_bulk_action' ) );
		// add_action( "load-{$hr_management}_page_erp-hr-designation", array( $this, 'designation_bulk_action' ) );
		// add_action( "load-{$hr_management}_page_erp-hr-depts", array( $this, 'department_bulk_action' ) );
		// add_action( "load-{$hr_management}_page_erp-hr-reporting", array( $this, 'reporting_bulk_action' ) );

		// $leave = sanitize_title( esc_html__( 'Leave', 'erp' ) );
		// add_action( 'load-toplevel_page_erp-leave', array( $this, 'leave_request_bulk_action' ) );
		// add_action( "load-{$leave}_page_erp-leave-assign", array( $this, 'entitlement_bulk_action' ) );
		// add_action( "load-{$leave}_page_erp-holiday-assign", array( $this, 'holiday_action' ) );
		// add_action( "load-{$leave}_page_erp-leave-policies", array( $this, 'leave_policies' ) );
		// add_action( "load-leaves_page_erp-hr-reporting", array( $this, 'reporting_leaves_bulk_action' ) );

		// Leave policies
		add_action( 'erp_action_hr-leave-policy-create', array( $this, 'leave_policy_create' ) );
	}

	/**
	 * Handle bulk action
	 *
	 * @since 1.3.14
	 */
	public function handle_actions() {
		$section     = ! empty( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : false;
		$sub_section = ! empty( $_GET['sub-section'] ) ? sanitize_text_field( wp_unslash( $_GET['sub-section'] ) ) : false;

		if ( ! $section ) {
			return;
		}

		switch ( $section ) {
			case 'people':
				switch ( $sub_section ) {
					case 'employee':
						$this->employee_bulk_action();
						break;

					case 'department':
						$this->department_bulk_action();
						break;

					case 'designation':
						$this->designation_bulk_action();
						break;

					case 'announcement':
						$this->announcement_bulk_action();
						break;

					default:
						return;
				}
				break;

			case 'report':
				$this->reporting_bulk_action();
				break;

			case 'leave':
				$this->handle_leave_bulk_actions();
				break;

			default:
		}
	}

	/**
	 * Handle bulk actions for leave section
	 *
	 * @since 1.3.14
	 */
	public function handle_leave_bulk_actions() {
		if ( empty( $_GET['sub-section'] ) ) {
			// $this->leave_request_bulk_action();
			return;
		}

		$type = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '';

		switch ( $_GET['sub-section'] ) {
			case 'leave-requests':
				$this->leave_request_bulk_action();
				break;

			case 'leave-entitlements':
				$this->entitlement_bulk_action();
				break;

			case 'holidays':
				$this->holiday_action();
				break;

			case 'policies':
				if ( $type !== 'policy-name' ) {
					$this->leave_policies();
				}
				break;
			default:
		}
	}

	/**
	 * Handle leave calendar filter
	 *
	 * @since 0.1
	 *
	 * @return void
	 */
	public function handle_leave_calendar_filter() {
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'erp_calendar_filter' ) ) {
			return;
		}

		if ( ! isset( $_POST['erp_leave_calendar_filter'] ) ) {
			return;
		}

		$designation = isset( $_POST['designation'] ) ? sanitize_text_field( wp_unslash( $_POST['designation'] ) ) : '';
		$department  = isset( $_POST['department'] ) ? sanitize_text_field( wp_unslash( $_POST['department'] ) ) : '';
		$url         = admin_url( "admin.php?page=erp-hr&section=leave&sub-section=leave-calendar&designation=$designation&department=$department" );
		wp_redirect( $url );
		exit();
	}

	/**
	 * Check is current page actions
	 *
	 * @since 0.1
	 *
	 * @param int $page_id
	 * @param int $bulk_action
	 *
	 * @return bool
	 */
	public function verify_current_page_screen( $page_id, $bulk_action ) {
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! isset( $_GET['page'] ) ) {
			return false;
		}

		if ( $_GET['page'] != $page_id ) {
			return false;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), $bulk_action ) ) {
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
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'bulk-leave_policies' ) ) {
			return;
		}

		// Check nonce validation
		if ( ! $this->verify_current_page_screen( 'erp-hr', 'bulk-leave_policies' ) ) {
			return;
		}

		// Check permission
		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$req_uri_bulk = ( isset( $_SERVER['REQUEST_URI'] ) ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$redirect     = remove_query_arg(
			array(
				'_wp_http_referer',
				'_wpnonce',
				'action',
				'action2',
			),
			$req_uri_bulk
		);

		if ( isset( $_REQUEST['filter_by_year'] ) ) {
			$redirect = remove_query_arg(
				array(
					'filter_by_year',
				),
				$redirect
			);

			wp_redirect( $redirect );
			exit();
		}

		if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) == 'trash' ) {
			if ( isset( $_REQUEST['policy_id'] ) ) {
				$policy_ids = $_REQUEST['policy_id'];
				array_walk(
					$policy_ids,
					function ( &$value ) {
						$value = absint( $value );
					}
				);
				erp_hr_leave_policy_delete( $policy_ids );

				$redirect = remove_query_arg(
					array(
						'policy_id',
					),
					$redirect
				);

				wp_redirect( $redirect );
				exit();
			}
		}
	}

	/**
	 * Handle entitlement bulk actions
	 *
	 * @since 0.1
	 * @since 1.6.0
	 *
	 * @return void
	 */
	public function entitlement_bulk_action() {
		if ( ! $this->verify_current_page_screen( 'erp-hr', 'bulk-entitlements' ) ) {
			return;
		}

		// Check permission
		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$employee_table = new \WeDevs\ERP\HRM\EntitlementListTable();
		$action         = $employee_table->current_action();

		if ( $action ) {
			$req_uri = ( isset( $_SERVER['REQUEST_URI'] ) ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

			$redirect = remove_query_arg(
				array(
					'_wp_http_referer',
					'_wpnonce',
					'filter_entitlement',
				),
				$req_uri
			);

			if ( $action == 'filter_entitlement' ) {
				wp_redirect( $redirect );
				exit();
			}

			if ( $action == 'entitlement_delete' ) {
				if ( isset( $_GET['entitlement_id'] ) && ! empty( $_GET['entitlement_id'] ) ) {
					$array = array_map( 'absint', wp_unslash( $_GET['entitlement_id'] ) );

					foreach ( $array as $ent_id ) {
						erp_hr_delete_entitlement( $ent_id, 0, $ent_id );
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
	 * @since 1.6.0
	 *
	 * @return void redirect
	 */
	public function leave_request_bulk_action() {
		// Check nonce validaion
		/*
		if ( ! $this->verify_current_page_screen( 'erp-hr', 'bulk-leaves' ) ) {
			return;
		}*/

		// Check permission
		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$leave_request_table = new \WeDevs\ERP\HRM\LeaveRequestsListTable();
		$action              = $leave_request_table->current_action();

		if ( $action ) {
			$page_status  = ( isset( $_GET['status'] ) && ! empty( $_GET['status'] ) ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : 'all';
			$paged        = ( isset( $_GET['paged'] ) && ! empty( $_GET['paged'] ) ) ? absint( wp_unslash( $_GET['paged'] ) ) : 1;
			$request_ids  = ( isset( $_GET['request_id'] ) && ! empty( $_GET['request_id'] ) ) ? array_map( 'absint', wp_unslash( $_GET['request_id'] ) ) : array();
			$redirect_url = admin_url( sprintf( 'admin.php?page=erp-hr&section=leave&status=%s&paged=%d', $page_status, $paged ) );

			$error = new ErpErrors( 'leave_request_status_change' );

			switch ( $action ) {

				case 'delete':
					foreach ( $request_ids as $request_id ) {
						$response = erp_hr_delete_leave_request( $request_id );

						if ( is_wp_error( $response ) ) {
							$error->add( $response );
						}
					}
					break;

				case 'approved':
					$status  = 1;
					$comment = __( 'Approved from bulk action', 'erp' );
					break;

				case 'reject':
					$status  = 3;
					$comment = __( 'Rejected from bulk action', 'erp' );
					break;

				default:
					break;

			}

			if ( 'approved' == $action || 'reject' == $action ) {
				foreach ( $request_ids as $request_id ) {
					$update_status = erp_hr_leave_request_update_status( $request_id, $status, $comment );

					if ( is_wp_error( $update_status ) ) {
						$error->add( $update_status );
					}
				}
			}

			if ( $error->has_error() ) {
				$error->save();
				$redirect_url = add_query_arg(
					array(
						'error' => 'leave_req_error',
					),
					$redirect_url
				);
			}

			wp_redirect( $redirect_url );
			exit;
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
		if ( ! $this->verify_current_page_screen( 'erp-hr', 'bulk-employees' ) ) {
			return;
		}

		// Check permission if not hr manager then go out from here
		if ( ! current_user_can( 'erp_view_list' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$employee_table = new \WeDevs\ERP\HRM\EmployeeListTable();
		$action         = $employee_table->current_action();

		if ( $action ) {
			$req_uri_bulk = ( isset( $_SERVER['REQUEST_URI'] ) ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

			$redirect = remove_query_arg(
				array(
					'_wp_http_referer',
					'_wpnonce',
					'filter_employee',
				),
				$req_uri_bulk
			);

			switch ( $action ) {

				case 'delete':
					if ( isset( $_GET['employee_id'] ) && ! empty( $_GET['employee_id'] ) ) {
						erp_employee_delete( array_map( 'absint', wp_unslash( $_GET['employee_id'] ) ), false );
					}

					wp_redirect( $redirect );
					exit();

				case 'permanent_delete':
					if ( isset( $_GET['employee_id'] ) && ! empty( $_GET['employee_id'] ) ) {
						erp_employee_delete( array_map( 'absint', wp_unslash( $_GET['employee_id'] ) ), true );
					}

					wp_redirect( $redirect );
					exit();

				case 'restore':
					if ( isset( $_GET['employee_id'] ) && ! empty( $_GET['employee_id'] ) ) {
						erp_employee_restore( array_map( 'absint', wp_unslash( $_GET['employee_id'] ) ) );
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
		// Nonce validation
		if ( ! $this->verify_current_page_screen( 'erp-hr', 'bulk-designations' ) ) {
			return;
		}

		// Check permission if not hr manager then go out from here
		if ( ! current_user_can( erp_hr_get_manager_role() ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$employee_table = new \WeDevs\ERP\HRM\DesignationListTable();
		$action         = $employee_table->current_action();

		if ( $action ) {
			$req_uri_bulk = ( isset( $_SERVER['REQUEST_URI'] ) ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			$redirect     = remove_query_arg(
				array(
					'_wp_http_referer',
					'_wpnonce',
					'action',
					'action2',
				),
				$req_uri_bulk
			);

			switch ( $action ) {

				case 'designation_delete':
					if ( isset( $_GET['desig'] ) && ! empty( $_GET['desig'] ) ) {
						$not_deleted_item = erp_hr_delete_designation( array_map( 'absint', wp_unslash( $_GET['desig'] ) ) );
					}

					if ( ! empty( $not_deleted_item ) ) {
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
		if ( ! $this->verify_current_page_screen( 'erp-hr', 'bulk-departments' ) ) {
			return;
		}

		// Check permission if not hr manager then go out from here
		if ( ! current_user_can( erp_hr_get_manager_role() ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$employee_table = new \WeDevs\ERP\HRM\DepartmentListTable();
		$action         = $employee_table->current_action();

		if ( $action ) {
			$req_uri_bulk = ( isset( $_SERVER['REQUEST_URI'] ) ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			$redirect     = remove_query_arg(
				array(
					'_wp_http_referer',
					'_wpnonce',
					'action',
					'action2',
				),
				$req_uri_bulk
			);
			$resp         = array();

			switch ( $action ) {

				case 'delete_department':
					if ( isset( $_GET['department_id'] ) ) {
						$array = array_map( 'absint', wp_unslash( $_GET['department_id'] ) );

						foreach ( $array as $dept_id ) {
							$resp[] = erp_hr_delete_department( $dept_id );
						}
					}

					if ( in_array( false, $resp ) ) {
						$redirect = add_query_arg( array( 'department_delete' => 'item_deleted' ), $redirect );
					}

					wp_redirect( $redirect );
					exit();
			}
		}
	}

	/**
	 * Announcement handle bulk action
	 *
	 * @since 1.10.0
	 *
	 * @return void [redirection]
	 */
	public function announcement_bulk_action() {
		// Check nonce validation
		if ( ! $this->verify_current_page_screen( 'erp-hr', 'bulk-announcements' ) ) {
			return;
		}

		// Check permission if not hr manager then go out from here
		if ( ! current_user_can( 'erp_manage_announcement' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$announcement_table = new \WeDevs\ERP\HRM\AnnouncementListTable();
		$action             = $announcement_table->current_action();

		if ( $action ) {
			$req_uri_bulk = ( ! empty( $_SERVER['REQUEST_URI'] ) ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			$redirect     = remove_query_arg(
				array(
					'_wp_http_referer',
					'_wpnonce',
					'action',
					'action2',
				),
				$req_uri_bulk
			);
			$fail_count   = 0;

			switch ( $action ) {
				case 'trash':
					if ( ! empty( $_GET['id'] ) ) {
						$announcement_ids = array_map( 'absint', wp_unslash( $_GET['id'] ) );
						$fail_count       = erp_hr_trash_announcements( $announcement_ids );
					}

					if ( $fail_count > 0 ) {
						$redirect = add_query_arg(
							array(
								'bulk-operation-failed' => 'failed_some_trash',
								'fail-count'            => $fail_count,
							),
							$redirect
						);
					}

					wp_redirect( $redirect );
					exit();

				case 'delete_permanently':
					if ( ! empty( $_GET['id'] ) ) {
						$announcement_ids = array_map( 'absint', wp_unslash( $_GET['id'] ) );
						$fail_count       = erp_hr_trash_announcements( $announcement_ids, true );
					}

					if ( $fail_count > 0 ) {
						$redirect = add_query_arg(
							array(
								'bulk-operation-failed' => 'failed_some_delation',
								'fail-count'            => $fail_count,
							),
							$redirect
						);
					}

					wp_redirect( $redirect );
					exit();

				case 'restore':
					if ( ! empty( $_GET['id'] ) ) {
						$announcement_ids = array_map( 'absint', wp_unslash( $_GET['id'] ) );
						$fail_count       = erp_hr_restore_announcements( $announcement_ids );
					}

					if ( $fail_count > 0 ) {
						$redirect = add_query_arg(
							array(
								'bulk-operation-failed' => 'failed_some_restoration',
								'fail-count'            => $fail_count,
							),
							$redirect
						);
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
		if ( ! $this->verify_current_page_screen( 'erp-hr', 'bulk-holiday' ) ) {
			return;
		}

		// Check permission
		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$this->remove_holiday( $_GET );

		$wp_http_referer = isset( $_GET['_wp_http_referer'] ) ? sanitize_text_field( wp_unslash( $_GET['_wp_http_referer'] ) ) : '';
		$query_arg       = add_query_arg(
			array(
				's'    => ( isset( $_GET['s'] ) ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
				'from' => ( isset( $_GET['from'] ) ) ? sanitize_text_field( wp_unslash( $_GET['from'] ) ) : '',
				'to'   => ( isset( $_GET['to'] ) ) ? sanitize_text_field( wp_unslash( $_GET['to'] ) ) : '',
			),
			$wp_http_referer
		);
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
	 * @return bool
	 */
	public function remove_holiday( $get ) {

		// Check permission
		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		if ( isset( $get['action'] ) && ( 'trash' === sanitize_text_field( wp_unslash( $get['action'] ) ) ) ) {
			if ( isset( $get['holiday_id'] ) ) {
				erp_hr_delete_holidays( array_map( 'absint', $get['holiday_id'] ) );

				return true;
			}
		}

		if ( isset( $get['action2'] ) && ( 'trash' === sanitize_text_field( wp_unslash( $get['action2'] ) ) ) ) {
			if ( isset( $get['holiday_id'] ) ) {
				erp_hr_delete_holidays( array_map( 'absint', $get['holiday_id'] ) );

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
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'erp-hr-leave-assign' ) ) {
			die( esc_html__( 'Something went wrong!', 'erp' ) );
		}

		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$affected  = 0;
		$errors    = new \WeDevs\ERP\ErpErrors( 'create_entitlements' );
		$employees = array();
		$page_url  = admin_url( 'admin.php?page=erp-hr&section=leave&sub-section=leave-entitlements&tab=assignment' );

		$department_id   = isset( $_POST['department_id'] ) ? intval( $_POST['department_id'] ) : '-1';
		$designation_id  = isset( $_POST['designation_id'] ) ? intval( $_POST['designation_id'] ) : '-1';
		$location_id     = isset( $_POST['location_id'] ) ? intval( $_POST['location_id'] ) : '-1';
		$gender          = isset( $_POST['gender'] ) ? intval( $_POST['gender'] ) : '-1';
		$marital         = isset( $_POST['marital'] ) ? intval( $_POST['marital'] ) : '-1';
		$f_year          = isset( $_POST['f_year'] ) ? intval( $_POST['f_year'] ) : '';
		$leave_policy    = isset( $_POST['leave_policy'] ) ? intval( $_POST['leave_policy'] ) : '';
		$is_single       = ! isset( $_POST['assignment_to'] );
		$single_employee = isset( $_POST['single_employee'] ) ? intval( $_POST['single_employee'] ) : '-1';
		$comment         = isset( $_POST['comment'] ) ? sanitize_text_field( wp_unslash( $_POST['comment'] ) ) : '-1';

		// save form data for future use
		$errors->add_form_data(
			array(
				'department_id'   => $designation_id,
				'designation_id'  => $designation_id,
				'location_id'     => $location_id,
				'gender'          => $gender,
				'marital'         => $marital,
				'f_year'          => $f_year,
				'leave_policy'    => $leave_policy,
				'assignment_to'   => sanitize_text_field( wp_unslash( $_POST['assignment_to'] ) ),
				'single_employee' => $single_employee,
				'comment'         => $comment,
			)
		);

		if ( $leave_policy == '' ) {
			$errors->add( new WP_Error( 'leave_policy', esc_attr__( 'Error: Please select a leave policy.', 'erp' ) ) );
		} else {
			$policy = LeavePolicy::find( $leave_policy );

			if ( ! $policy ) {
				$errors->add( new WP_Error( 'leave_policy', esc_attr__( 'Error: Invalid policy selected. Please check your input.', 'erp' ) ) );
			}
		}

		if ( $is_single && ! $single_employee ) {
			$errors->add( new WP_Error( 'single_employee', esc_attr__( 'Error: Please select an employee.', 'erp' ) ) );
		}

		// bail out if error found
		if ( $errors->has_error() ) {
			$errors->save();
			$redirect_to = add_query_arg( array( 'error' => $errors->get_key() ), $page_url );
			wp_safe_redirect( $redirect_to );
			exit;
		}

		// fetch employees if not single
		$employees = array();

		if ( ! $is_single ) {
			$employees = erp_hr_get_employees(
				array(
					'department'     => $policy->department_id,
					'location'       => $policy->location_id,
					'designation'    => $policy->designation_id,
					'gender'         => $policy->gender,
					'marital_status' => $policy->marital,
					'number'         => '-1',
					'no_object'      => true,
				)
			);
		} else {
			$user              = get_user_by( 'id', $single_employee );
			$emp               = new stdClass();
			$emp->user_id      = $user->ID;
			$emp->display_name = $user->display_name;

			$employees[] = $emp;
		}

		if ( count( $employees ) === 0 ) {
			$errors->add( esc_attr__( 'Error: No Employees Found. Please check your input.', 'erp' ) );
		}

		// bail out if error found
		if ( $errors->has_error() ) {
			$errors->save();
			$redirect_to = add_query_arg( array( 'error' => $errors->get_key() ), $page_url );
			wp_safe_redirect( $redirect_to );
			exit;
		}

		$affected = 0;

		foreach ( $employees as $employee ) {
			// get required data and send it to insert_entitlement function
			$data = array(
				'user_id'     => $employee->user_id,
				'leave_id'    => $policy->leave_id,
				'created_by'  => get_current_user_id(),
				'trn_id'      => $policy->id,
				'trn_type'    => 'leave_policies',
				'day_in'      => $policy->days,
				'day_out'     => 0,
				'description' => $comment,
				'f_year'      => $policy->f_year,
			);

			$inserted = erp_hr_leave_insert_entitlement( $data );

			if ( ! is_wp_error( $inserted ) ) {
				++$affected;
			} else {
				$errors->add( $inserted );
			}
		}

		if ( $errors->has_error() ) {
			if ( $affected ) {
				$errors->add_form_data( array( 'affected' => $affected ) );
			}
			$errors->save();
			$redirect_to = add_query_arg( array( 'error' => $errors->get_key() ), $page_url );
		} else {
			$redirect_to = add_query_arg( array( 'affected' => $affected ), $page_url );
		}

		wp_safe_redirect( $redirect_to );
		exit;
	}

	/**
	 * Submit a new leave request
	 *
	 * @since 0.1
	 *
	 * @return void
	 */
	public function leave_request() {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'erp-leave-req-new' ) ) {
			die( esc_html__( 'Something went wrong!', 'erp' ) );
		}

		if ( ! current_user_can( 'erp_leave_create_request' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$errors = new ErpErrors( 'new_leave_request' );

		if ( empty( trim( sanitize_text_field( wp_unslash( $_POST['leave_reason'] ) ) ) ) ) {
			$errors->add( esc_attr__( 'Leave reason field can not be blank.', 'erp' ) );
			$errors->save();

			$redirect_to = admin_url( 'admin.php?page=erp-hr&section=leave&view=new&error=new_leave_request' );
			wp_redirect( $redirect_to );
			exit;
		}

		$employee_id  = isset( $_POST['employee_id'] ) ? intval( $_POST['employee_id'] ) : 0;
		$leave_policy = isset( $_POST['leave_policy'] ) ? intval( $_POST['leave_policy'] ) : 0;

		// @todo: date format may need to be changed when partial leave introduced
		$start_date = isset( $_POST['leave_from'] ) ? sanitize_text_field( wp_unslash( $_POST['leave_from'] . ' 00:00:00' ) ) : date_i18n( 'Y-m-d 00:00:00' );
		$end_date   = isset( $_POST['leave_to'] ) ? sanitize_text_field( wp_unslash( $_POST['leave_to'] . ' 23:59:59' ) ) : date_i18n( 'Y-m-d 23:59:59' );

		$leave_reason = isset( $_POST['leave_reason'] ) ? wp_strip_all_tags( sanitize_text_field( wp_unslash( $_POST['leave_reason'] ) ) ) : '';

		$insert = erp_hr_leave_insert_request(
			array(
				'user_id'      => $employee_id,
				'leave_policy' => $leave_policy,
				'start_date'   => $start_date,
				'end_date'     => $end_date,
				'reason'       => $leave_reason,
			)
		);

		$entitlement = \WeDevs\ERP\HRM\Models\LeaveEntitlement::find( $leave_policy );
		$f_year_text = '';

		if ( $entitlement ) {
			$f_year_text = '&filter_year=' . $entitlement->financial_year->id;
		}

		if ( is_wp_error( $insert ) ) {
			$errors->add( $insert );
			$errors->save();
			$redirect_to = admin_url( 'admin.php?page=erp-hr&section=leave&view=new&insert_error=new_leave_request' . $f_year_text );
		} else {
			// notification email.
			$emailer = wperp()->emailer->get_email( 'NewLeaveRequest' );

			if ( is_a( $emailer, '\WeDevs\ERP\Email' ) ) {
				$emailer->trigger( $insert );
			}

			$redirect_to = admin_url( 'admin.php?page=erp-hr&section=leave&sub-section=leave-requests&status=2' . $f_year_text );
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
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'erp-hr-leave-req-nonce' ) ) {
			return;
		}

		// Check permission if not have then bell out :)
		if ( ! current_user_can( 'erp_leave_manage' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$action  = sanitize_text_field( wp_unslash( $_GET['leave_action'] ) );
		$stauses = array(
			'delete',
			'reject',
			'approve',
			'pending',
		);

		if ( ! in_array( $action, $stauses ) ) {
			return;
		}

		if ( empty( $_GET['id'] ) ) {
			return;
		}

		$request_id = absint( $_GET['id'] );
		$status     = null;

		switch ( $action ) {
			case 'delete':
				// @since 1.6.0, do nothing, we are handling this from ajax request
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
			$return = erp_hr_leave_request_update_status( $request_id, $status );
		}

		$current_f_year = erp_hr_get_financial_year_from_date();
		$f_year         = isset( $_GET['filter_year'] ) ? absint( wp_unslash( $_GET['filter_year'] ) ) : ( ! empty( $current_f_year ) ? $current_f_year->id : '' );

		$redirect_to = remove_query_arg( array( 'status' ), admin_url( 'admin.php?page=erp-hr&section=leave' ) );

		if ( is_wp_error( $return ) ) {
			$errors = new ErpErrors( 'leave_request_status_change' );
			$errors->add( $return );
			$errors->save();
			$redirect_to = add_query_arg(
				array(
					'error'       => 'leave_request_status_change',
					'filter_year' => $f_year,
				),
				$redirect_to
			);
		} else {
			$redirect_to = add_query_arg(
				array(
					'status'      => $status,
					'filter_year' => $f_year,
				),
				$redirect_to
			);
		}

		// redirect the user back
		wp_redirect( $redirect_to );
		exit;
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

		// Nonce validation
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'wp-erp-hr-employee-update-nonce' ) ) {
			return;
		}

		// Check permission
		if ( ! current_user_can( erp_hr_get_manager_role() ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$user_id         = ( isset( $_POST['user_id'] ) ) ? sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) : '';
		$employee_status = ( isset( $_POST['employee_status'] ) ) ? sanitize_text_field( wp_unslash( $_POST['employee_status'] ) ) : '';
		$wp_http_referer = ( isset( $_POST['_wp_http_referer'] ) ) ? sanitize_text_field( wp_unslash( $_POST['_wp_http_referer'] ) ) : '';

		if ( $_POST['employee_status'] == 'terminated' ) {
			\WeDevs\ERP\HRM\Models\Employee::where( 'user_id', '=', $user_id )->update(
				array(
					'status'           => $employee_status,
					'termination_date' => current_time( 'mysql' ),
				)
			);
		} else {
			\WeDevs\ERP\HRM\Models\Employee::where( 'user_id', '=', $user_id )->update(
				array(
					'status'           => $employee_status,
					'termination_date' => '',
				)
			);
		}

		wp_redirect( $wp_http_referer );
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
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'wp-erp-hr-employee-permission-nonce' ) ) {
			return;
		}

		$hr_manager_role = erp_hr_get_manager_role();

		if ( ! current_user_can( $hr_manager_role ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$employee_id    = isset( $_POST['employee_id'] ) ? absint( $_POST['employee_id'] ) : 0;
		$enable_manager = isset( $_POST['enable_manager'] ) ? filter_var( sanitize_text_field( wp_unslash( $_POST['enable_manager'] ) ), FILTER_VALIDATE_BOOLEAN ) : false;

		$user = get_user_by( 'id', $employee_id );

		if ( $enable_manager && ! user_can( $user, $hr_manager_role ) ) {
			$user->add_role( $hr_manager_role );
		} elseif ( ! $enable_manager && user_can( $user, $hr_manager_role ) ) {
			$user->remove_role( $hr_manager_role );
		}

		do_action( 'erp_hr_after_employee_permission_set', $_POST, $user );

		$redirect_to = admin_url( 'admin.php?page=erp-hr&section=people&sub-section=employee&action=view&id=' . $user->ID . '&tab=permission&msg=success' );
		wp_redirect( $redirect_to );
		wp_redirect( $redirect_to );
		exit;
	}

	/**
	 * Reporting Form Submit Handler
	 *
	 * @since 0.1
	 *
	 * @return void
	 */
	public function reporting_bulk_action() {
		if ( isset( $_REQUEST['filter_headcount'] ) ) {
			$req_uri_bulk = ( isset( $_SERVER['REQUEST_URI'] ) ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

			if ( ! $this->verify_current_page_screen( 'erp-hr', 'epr-rep-headcount' ) ) {
				return;
			}

			$redirect = remove_query_arg(
				array(
					'_wp_http_referer',
					'_wpnonce',
					'filter_headcount',
				),
				$req_uri_bulk
			);

			wp_redirect( $redirect );
		}

		if ( isset( $_REQUEST['filter_leave_report'] ) ) {
			if ( ! $this->verify_current_page_screen( 'erp-hr-reporting', 'epr-rep-leaves' ) ) {
				return;
			}

			$redirect = remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'filter_leave_report' ), $req_uri_bulk );

			wp_redirect( $redirect );
		}
	}

	/**
	 * Create leave policy
	 *
	 * @since 1.6.0
	 *
	 * @return mixed
	 */
	public function leave_policy_create() {
		// Nonce validation
		if ( ! isset( $_POST['_wpnonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ),
				'erp-leave-policy'
			)
		) {
			return;
		}

		// Check permission
		if ( ! current_user_can( erp_hr_get_manager_role() ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
		}

		$errors = new ErpErrors( 'policy_create_error' );

		$id                  = ! empty( $_POST['policy-id'] ) ? absint( wp_unslash( $_POST['policy-id'] ) ) : 0;
		$leave_id            = ! empty( $_POST['leave-id'] ) ? absint( wp_unslash( $_POST['leave-id'] ) ) : 0;
		$days                = ! empty( $_POST['days'] ) ? absint( wp_unslash( $_POST['days'] ) ) : 0;
		$f_year              = ! empty( $_POST['f-year'] ) ? absint( wp_unslash( $_POST['f-year'] ) ) : 0;
		$desc                = ! empty( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '';
		$employee_type       = ! empty( $_POST['employee_type'] ) ? sanitize_text_field( wp_unslash( $_POST['employee_type'] ) ) : '-1';
		$dept_id             = ! empty( $_POST['department'] ) ? sanitize_text_field( wp_unslash( $_POST['department'] ) ) : '-1';
		$desg_id             = ! empty( $_POST['designation'] ) ? sanitize_text_field( wp_unslash( $_POST['designation'] ) ) : '-1';
		$location_id         = ! empty( $_POST['location'] ) ? sanitize_text_field( wp_unslash( $_POST['location'] ) ) : '-1';
		$color               = ! empty( $_POST['color'] ) ? sanitize_text_field( wp_unslash( $_POST['color'] ) ) : '';
		$gender              = ! empty( $_POST['gender'] ) ? sanitize_text_field( wp_unslash( $_POST['gender'] ) ) : '-1';
		$marital             = ! empty( $_POST['marital'] ) ? sanitize_text_field( wp_unslash( $_POST['marital'] ) ) : '-1';
		$applicable          = ! empty( $_POST['applicable-from'] ) ? absint( wp_unslash( $_POST['applicable-from'] ) ) : 0;
		$apply_for_new_users = ! empty( $_POST['apply-for-new-users'] ) ? 1 : 0;

		// no need to throw this error if editing
		if ( ! $id && empty( $leave_id ) ) {
			$errors->add( __( 'Name field should not be left empty', 'erp' ) );
		}

		// no need to throw this error if editing
		if ( ! $id && $days < 0 ) {
			$errors->add( __( 'Days field should not be left empty', 'erp' ) );
		}

		if ( empty( $color ) ) {
			$errors->add( __( 'Color field should not be left empty', 'erp' ) );
		}

		if ( ! $id && empty( $f_year ) ) {
			$errors->add( __( 'Year field should not be left empty', 'erp' ) );
		}

		$errors = apply_filters( 'erp_pro_hr_leave_policy_form_errors', $errors );

		$redirect_args = array(
			'page'        => 'erp-hr',
			'section'     => 'leave',
			'sub-section' => 'policies',
		);

		if ( ! empty( $_GET['action'] ) ) {
			$redirect_args['id'] = ! empty( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
		}

		if ( ! empty( $_GET['action'] ) ) {
			$redirect_args['action'] = sanitize_key( wp_unslash( $_GET['action'] ) );
		}

		$redirect_url = add_query_arg( $redirect_args, admin_url( 'admin.php' ) );

		if ( $errors->has_error() ) {
			$errors->add_form_data( $_POST );
			$errors->save();

			wp_redirect( $redirect_url );
			exit;
		}

		$data = array(
			'leave_id'            => $leave_id,
			'employee_type'       => $employee_type,
			'description'         => $desc,
			'days'                => $days,
			'color'               => $color,
			'department_id'       => $dept_id,
			'designation_id'      => $desg_id,
			'location_id'         => $location_id,
			'gender'              => $gender,
			'marital'             => $marital,
			'f_year'              => $f_year,
			'applicable_from'     => $applicable,
			'apply_for_new_users' => $apply_for_new_users,
		);

		if ( $id ) {
			$data['id'] = $id;
		}

		$res = erp_hr_leave_insert_policy( $data );

		if ( is_wp_error( $res ) ) {
			$errors->add( $res );
			$errors->add_form_data( $_POST );
			$errors->save();
			wp_redirect( $redirect_url );
			exit;
		}

		wp_redirect(
			add_query_arg(
				array(
					'page'        => 'erp-hr',
					'section'     => 'leave',
					'sub-section' => 'policies',
					'filter_year' => $f_year,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Insert financial years
	 *
	 * @since 1.6.0
	 *
	 * @return void
	 */
	public function insert_financial_years() {
		if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
			return;
		}

		if ( ! isset( $_POST['action'] ) || sanitize_key( $_POST['action'] ) !== 'erp-hr-fyears-setting' ) {
			return;
		}

		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-settings-nonce' ) ) {
			die( esc_html__( 'Nonce failed.', 'erp' ) );
		}

		$fnames = isset( $_POST['fyear-name'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['fyear-name'] ) ) : array();
		$starts = isset( $_POST['fyear-start'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['fyear-start'] ) ) : array();
		$ends   = isset( $_POST['fyear-end'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['fyear-end'] ) ) : array();

		$current_user_id = get_current_user_id();
		$url             = admin_url( 'admin.php?page=erp-settings#/erp-hr/financial' );

		$errors = new ErpErrors( 'leave_financial_years_create' );

		foreach ( $fnames as $key => $fname ) {
			if ( strpos( $key, 'id-' ) !== false ) {
				// we have existing record
				$f_id = explode( 'id-', $key )[1]; // id-3 => 3

				$policy_exist = LeavePolicy::where( 'f_year', $f_id )->first();

				if ( $policy_exist ) {
					$errors->add(
						esc_html__(
							sprintf( 'Existing leave year associated with policy won\'t be updated. e.g. %s', $fname ),
							'erp'
						)
					);

					// we shouldn't update if there's an associated policy
					// so, let's move on to next loop
					continue;
				}

				// otherwise, update an existing one
				FinancialYear::find( $f_id )->update(
					array(
						'fy_name'     => $fname,
						'start_date'  => erp_mysqldate_to_phptimestamp( $starts[ $key ] ),
						'end_date'    => erp_mysqldate_to_phptimestamp( $ends[ $key ] ),
						'description' => esc_html__( 'Year for leave', 'erp' ),
						'updated_by'  => $current_user_id,
					)
				);

				continue;
			}

			// or create a new one
			FinancialYear::create(
				array(
					'fy_name'     => $fname,
					'start_date'  => erp_mysqldate_to_phptimestamp( $starts[ $key ] ),
					'end_date'    => erp_mysqldate_to_phptimestamp( $ends[ $key ] ),
					'description' => esc_html__( 'Year for leave', 'erp' ),
					'created_by'  => $current_user_id,
				)
			);
		}

		if ( $errors->has_error() ) {
			$errors->save();
			$url = add_query_arg( array( 'error' => 'leave_financial_years_create' ), $url );
		}

		wp_safe_redirect( $url );
		exit();
	}
}

new FormHandler();
