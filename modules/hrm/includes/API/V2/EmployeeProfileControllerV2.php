<?php
/**
 * WP-ERP HR — employee "General" profile sub-entities REST controller.
 *
 * Exposes the three repeatable sections the legacy General-info tab managed —
 * Work Experience, Education, Dependents — under `erp/v2`:
 *
 *   GET    /erp/v2/employees/{user_id}/experiences         — list
 *   POST   /erp/v2/employees/{user_id}/experiences         — create or update (id => update)
 *   DELETE /erp/v2/employees/{user_id}/experiences/{id}    — delete
 *   …same shape for /educations and /dependents
 *
 * These mirror the legacy AJAX handlers verbatim (the HR admin frontend is
 * AJAX-driven, NOT `erp/v1` REST): the same field sanitization, the same model
 * calls (`Employee::add_experience()` / `add_education()` / `add_dependent()`
 * and their `delete_*` siblings), and the same `erp_hr_employee_*` hooks. Only
 * the request/response envelope is the modern v2 contract. `erp/v1` stays
 * untouched. See `AjaxHandler::employee_work_experience_create()` etc.
 *
 * Permissions match the AJAX handlers: `erp_edit_employee` on the target.
 */

namespace WeDevs\ERP\HRM\API\V2;

use WeDevs\ERP\HRM\Employee;
use WP_REST_Request;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

class EmployeeProfileControllerV2 extends RestControllerV2 {

	/**
	 * @var string
	 */
	protected $rest_base = 'employees';

	/**
	 * @return void
	 */
	public function register_routes() {
		foreach ( [ 'experiences', 'educations', 'dependents' ] as $section ) {
			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base . '/(?P<user_id>[\d]+)/' . $section,
				[
					'args' => [
						'user_id' => $this->user_id_arg(),
					],
					[
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => [ $this, 'get_items' ],
						'permission_callback' => [ $this, 'permission_edit' ],
					],
					[
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => [ $this, 'create_item' ],
						'permission_callback' => [ $this, 'permission_edit' ],
					],
				]
			);

			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base . '/(?P<user_id>[\d]+)/' . $section . '/(?P<id>[\d]+)',
				[
					'args' => [
						'user_id' => $this->user_id_arg(),
						'id'      => [
							'description'       => __( 'Record ID.', 'erp' ),
							'type'              => 'integer',
							'sanitize_callback' => 'absint',
							'validate_callback' => 'rest_validate_request_arg',
						],
					],
					[
						'methods'             => WP_REST_Server::DELETABLE,
						'callback'            => [ $this, 'delete_item' ],
						'permission_callback' => [ $this, 'permission_edit' ],
					],
				]
			);
		}
	}

	/**
	 * Shared `user_id` route arg.
	 *
	 * @return array
	 */
	private function user_id_arg(): array {
		return [
			'description'       => __( 'Unique employee user ID.', 'erp' ),
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		];
	}

	/**
	 * All three sections gate on the edit-employee cap on the target — the same
	 * gate every legacy AJAX handler used.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return bool
	 */
	public function permission_edit( $request ): bool {
		return $this->permission_cap( 'erp_edit_employee', (int) $request['user_id'] );
	}

	/**
	 * Resolve the section ('experiences'|'educations'|'dependents') from the
	 * matched route, so one set of callbacks serves all three.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return string
	 */
	private function section( WP_REST_Request $request ): string {
		$route = (string) $request->get_route();
		foreach ( [ 'experiences', 'educations', 'dependents' ] as $section ) {
			if ( false !== strpos( $route, '/' . $section ) ) {
				return $section;
			}
		}
		return 'experiences';
	}

	/**
	 * Load + validate the target employee, or a WP_Error.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return Employee|\WP_Error
	 */
	private function resolve_employee( WP_REST_Request $request ) {
		$employee = new Employee( (int) $request['user_id'] );

		if ( ! $employee->is_employee() ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'Invalid employee id.', 'erp' ), [ 'status' => 404 ] );
		}

		return $employee;
	}

	/**
	 * GET — list rows for the matched section.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_items( $request ) {
		$employee = $this->resolve_employee( $request );
		if ( is_wp_error( $employee ) ) {
			return $employee;
		}

		switch ( $this->section( $request ) ) {
			case 'educations':
				$rows = $employee->get_educations( 100, 0 );
				break;
			case 'dependents':
				$rows = $employee->get_dependents( 100, 0 );
				break;
			default:
				$rows = $employee->get_experiences( 100, 0 );
		}

		$items = [];
		foreach ( $rows as $row ) {
			$items[] = is_array( $row ) ? $row : $row->toArray();
		}

		return rest_ensure_response( $items );
	}

	/**
	 * POST — create or update a row (an `id` in the body means update). Mirrors
	 * the legacy AJAX field handling exactly per section.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_item( $request ) {
		$employee = $this->resolve_employee( $request );
		if ( is_wp_error( $employee ) ) {
			return $employee;
		}

		switch ( $this->section( $request ) ) {
			case 'educations':
				$result = $employee->add_education( $this->education_fields( $request ) );
				break;
			case 'dependents':
				$result = $employee->add_dependent( $this->dependent_fields( $request ) );
				break;
			default:
				$result = $employee->add_experience( $this->experience_fields( $request ) );
		}

		if ( is_wp_error( $result ) ) {
			return new \WP_Error(
				$result->get_error_code() ?: 'rest_profile_save_failed',
				$result->get_error_message() ?: __( 'Could not save the record.', 'erp' ),
				[ 'status' => 400 ]
			);
		}

		$response = rest_ensure_response( is_array( $result ) ? $result : (array) $result );
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * DELETE — remove a row. Fires the same legacy `*_delete` action the AJAX
	 * handler fired, then delegates to the model.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		$employee = $this->resolve_employee( $request );
		if ( is_wp_error( $employee ) ) {
			return $employee;
		}

		$id = (int) $request['id'];
		if ( ! $id ) {
			return new \WP_Error( 'rest_invalid_id', __( 'Invalid record id.', 'erp' ), [ 'status' => 400 ] );
		}

		switch ( $this->section( $request ) ) {
			case 'educations':
				do_action( 'erp_hr_employee_education_delete', $id );
				$employee->delete_education( $id );
				break;
			case 'dependents':
				do_action( 'erp_hr_employee_dependents_delete', $id );
				$employee->delete_dependent( $id );
				break;
			default:
				do_action( 'erp_hr_employee_experience_delete', $id );
				$employee->delete_experience( $id );
		}

		return rest_ensure_response( [ 'deleted' => true, 'id' => $id ] );
	}

	/**
	 * Work-experience fields — mirrors `AjaxHandler::employee_work_experience_create()`.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return array
	 */
	private function experience_fields( WP_REST_Request $request ): array {
		return [
			'id'           => (int) ( $request['id'] ?? 0 ),
			'company_name' => wp_strip_all_tags( sanitize_text_field( (string) ( $request['company_name'] ?? '' ) ) ),
			'job_title'    => wp_strip_all_tags( sanitize_text_field( (string) ( $request['job_title'] ?? '' ) ) ),
			'from'         => wp_strip_all_tags( sanitize_text_field( (string) ( $request['from'] ?? '' ) ) ),
			'to'           => wp_strip_all_tags( sanitize_text_field( (string) ( $request['to'] ?? '' ) ) ),
			'description'  => wp_strip_all_tags( sanitize_text_field( (string) ( $request['description'] ?? '' ) ) ),
		];
	}

	/**
	 * Education fields — mirrors `AjaxHandler::employee_education_create()`,
	 * including the `result` JSON built from gpa/scale + result_type.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return array
	 */
	private function education_fields( WP_REST_Request $request ): array {
		$result_type = isset( $request['result_type'] ) ? sanitize_text_field( (string) $request['result_type'] ) : null;
		$result      = [ 'gpa' => isset( $request['gpa'] ) ? sanitize_text_field( (string) $request['gpa'] ) : null ];

		if ( 'grade' === $result_type ) {
			$result['scale'] = isset( $request['scale'] ) ? sanitize_text_field( (string) $request['scale'] ) : null;
		}

		return [
			'id'              => (int) ( $request['edu_id'] ?? $request['id'] ?? 0 ),
			'school'          => sanitize_text_field( (string) ( $request['school'] ?? '' ) ),
			'degree'          => sanitize_text_field( (string) ( $request['degree'] ?? '' ) ),
			'field'           => sanitize_text_field( (string) ( $request['field'] ?? '' ) ),
			'result'          => wp_json_encode( $result ),
			'result_type'     => $result_type,
			'finished'        => (int) ( $request['finished'] ?? 0 ),
			'notes'           => sanitize_text_field( (string) ( $request['notes'] ?? '' ) ),
			'interest'        => sanitize_text_field( (string) ( $request['interest'] ?? '' ) ),
			'expiration_date' => sanitize_text_field( (string) ( $request['expiration_date'] ?? '' ) ),
		];
	}

	/**
	 * Dependent fields — mirrors `AjaxHandler::employee_dependent_create()`.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return array
	 */
	private function dependent_fields( WP_REST_Request $request ): array {
		return [
			'id'       => (int) ( $request['dep_id'] ?? $request['id'] ?? 0 ),
			'name'     => wp_strip_all_tags( sanitize_text_field( (string) ( $request['name'] ?? '' ) ) ),
			'relation' => wp_strip_all_tags( sanitize_text_field( (string) ( $request['relation'] ?? '' ) ) ),
			'dob'      => wp_strip_all_tags( sanitize_text_field( (string) ( $request['dob'] ?? '' ) ) ),
		];
	}
}
