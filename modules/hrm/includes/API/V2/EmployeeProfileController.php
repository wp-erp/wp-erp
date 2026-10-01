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

class EmployeeProfileController extends RestController {

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

		$section = $this->section( $request );

		switch ( $section ) {
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
			$item = is_array( $row ) ? $row : $row->toArray();

			if ( 'educations' === $section ) {
				$item = $this->decode_education_result( $item );
			}

			$items[] = $item;
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

		$invalid = $this->validate_fields( $this->section( $request ), $request, $this->stored_row( $employee, $this->section( $request ), $request ) );
		if ( $invalid ) {
			return $invalid;
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

		// Resolve the row through the employee's own relation first: an id that is
		// not theirs used to fire the delete action and then dereference null.
		$section = $this->section( $request );

		if ( ! $employee->get_erp_user()->{$section}()->find( $id ) ) {
			return new \WP_Error( 'rest_invalid_id', __( 'Invalid record id.', 'erp' ), [ 'status' => 404 ] );
		}

		switch ( $section ) {
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
	 * Value rules for the three sections, checked before the model writes. The
	 * React dialogs run the same rules with the same wording (profile-rules.ts);
	 * this side is the one that counts.
	 *
	 * Required fields are checked on every save, as the model always did. On an
	 * edit the other rules run only when a field they read is changed, so a row
	 * saved before the rules existed stays editable (e.g. an old To date before
	 * its From date, left as it was).
	 *
	 * @param string          $section 'experiences' | 'educations' | 'dependents'.
	 * @param WP_REST_Request $request Request.
	 * @param array|null      $stored  The row being edited, null on create.
	 *
	 * @return \WP_Error|null WP_Error (400) to reject, null when valid.
	 */
	private function validate_fields( string $section, WP_REST_Request $request, $stored = null ) {
		$text = static function ( $key ) use ( $request ): string {
			$value = $request[ $key ] ?? '';
			return \is_scalar( $value ) ? trim( sanitize_text_field( (string) $value ) ) : '';
		};
		$fail = static function ( string $message ) {
			return new \WP_Error( 'rest_invalid_param', $message, [ 'status' => 400 ] );
		};
		$is_day = static function ( string $value ): bool {
			return (bool) preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) && erp_is_valid_date( $value );
		};
		$check = function ( array $fields ) use ( $stored, $request ): bool {
			return null === $stored || $this->changed( $stored, $request->get_params(), $fields );
		};

		if ( 'experiences' === $section ) {
			$from = $text( 'from' );
			$to   = $text( 'to' );

			if ( '' === $text( 'company_name' ) ) {
				return $fail( __( 'Company name is required.', 'erp' ) );
			}
			if ( '' === $text( 'job_title' ) ) {
				return $fail( __( 'Job title is required.', 'erp' ) );
			}
			if ( '' === $from ) {
				return $fail( __( 'From date is required.', 'erp' ) );
			}
			if ( $check( [ 'from' ] ) && ! $is_day( $from ) ) {
				return $fail( __( 'Enter a valid From date.', 'erp' ) );
			}
			if ( '' === $to ) {
				return $fail( __( 'To date is required.', 'erp' ) );
			}
			if ( $check( [ 'to' ] ) && ! $is_day( $to ) ) {
				return $fail( __( 'Enter a valid To date.', 'erp' ) );
			}
			if ( $check( [ 'from', 'to' ] ) && $to < $from ) {
				return $fail( __( 'The To date cannot be earlier than the From date.', 'erp' ) );
			}

			return null;
		}

		if ( 'educations' === $section ) {
			$type   = $text( 'result_type' );
			$result = $text( 'gpa' );
			$scale  = $text( 'scale' );
			$year   = $text( 'finished' );
			$expiry = $text( 'expiration_date' );
			$number = '/^[0-9]+(\.[0-9]+)?$/';
			$grade  = $check( [ 'result_type', 'gpa', 'scale' ] );

			if ( '' === $text( 'school' ) ) {
				return $fail( __( 'School name is required.', 'erp' ) );
			}
			if ( '' === $text( 'degree' ) ) {
				return $fail( __( 'Degree is required.', 'erp' ) );
			}
			if ( '' === $text( 'field' ) ) {
				return $fail( __( 'Field of study is required.', 'erp' ) );
			}
			if ( ( '' === $type || $check( [ 'result_type' ] ) ) && ! \in_array( $type, [ 'grade', 'percentage' ], true ) ) {
				return $fail( __( 'Select a valid result type.', 'erp' ) );
			}
			if ( '' === $result ) {
				return $fail( __( 'Result is required.', 'erp' ) );
			}
			if ( $grade ) {
				if ( ! preg_match( $number, $result ) ) {
					return $fail( __( 'Result must be a number of 0 or more.', 'erp' ) );
				}
				if ( 'percentage' === $type && (float) $result > 100 ) {
					return $fail( __( 'Percentage cannot be more than 100.', 'erp' ) );
				}
				if ( 'grade' === $type ) {
					if ( '' === $scale ) {
						return $fail( __( 'Scale is required.', 'erp' ) );
					}
					if ( ! preg_match( $number, $scale ) || (float) $scale <= 0 ) {
						return $fail( __( 'Scale must be a number greater than 0.', 'erp' ) );
					}
					if ( (float) $result > (float) $scale ) {
						return $fail( __( 'Grade cannot be higher than the scale.', 'erp' ) );
					}
				}
			}
			if ( '' === $year ) {
				return $fail( __( 'Completion year is required.', 'erp' ) );
			}
			if ( $check( [ 'finished' ] ) && ( ! preg_match( '/^\d{4}$/', $year ) || (int) $year < 1970 || (int) $year > 2099 ) ) {
				return $fail( __( 'Completion year must be between 1970 and 2099.', 'erp' ) );
			}
			if ( $check( [ 'expiration_date' ] ) && '' !== $expiry && ! $is_day( $expiry ) ) {
				return $fail( __( 'Enter a valid expiration date.', 'erp' ) );
			}

			return null;
		}

		$dob = $text( 'dob' );

		if ( '' === $text( 'name' ) ) {
			return $fail( __( 'Name is required.', 'erp' ) );
		}
		if ( '' === $text( 'relation' ) ) {
			return $fail( __( 'Relation is required.', 'erp' ) );
		}
		if ( $check( [ 'dob' ] ) ) {
			if ( '' !== $dob && ! $is_day( $dob ) ) {
				return $fail( __( 'Enter a valid date of birth.', 'erp' ) );
			}
			if ( '' !== $dob && $dob > current_time( 'Y-m-d' ) ) {
				return $fail( __( 'Date of birth cannot be in the future.', 'erp' ) );
			}
		}

		return null;
	}

	/**
	 * The employee's stored row an edit targets, as the edit dialog reads it
	 * (education `gpa` / `scale` decoded), or null on create or when the id is
	 * not one of this employee's rows.
	 *
	 * @param Employee        $employee Employee.
	 * @param string          $section  'experiences' | 'educations' | 'dependents'.
	 * @param WP_REST_Request $request  Request.
	 *
	 * @return array|null
	 */
	private function stored_row( Employee $employee, string $section, WP_REST_Request $request ) {
		switch ( $section ) {
			case 'educations':
				$id   = (int) $this->education_fields( $request )['id'];
				$rows = $id ? $employee->get_educations( 100, 0 ) : [];
				break;
			case 'dependents':
				$id   = (int) $this->dependent_fields( $request )['id'];
				$rows = $id ? $employee->get_dependents( 100, 0 ) : [];
				break;
			default:
				$id   = (int) $this->experience_fields( $request )['id'];
				$rows = $id ? $employee->get_experiences( 100, 0 ) : [];
		}

		foreach ( $rows as $row ) {
			$item = is_array( $row ) ? $row : $row->toArray();

			if ( (int) ( $item['id'] ?? 0 ) === $id ) {
				return 'educations' === $section ? $this->decode_education_result( $item ) : $item;
			}
		}

		return null;
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
	 * Decode the stored `result` JSON of an education row back into the
	 * `gpa` / `scale` keys the edit dialog rehydrates from. `result_type`
	 * is already a column, so it is left untouched. Without this, the edit
	 * dialog renders blank GPA/scale fields (data-loss on edit).
	 *
	 * @param array $item Education row as an associative array.
	 *
	 * @return array
	 */
	private function decode_education_result( array $item ): array {
		$decoded = isset( $item['result'] ) ? json_decode( (string) $item['result'], true ) : null;

		$item['gpa']   = is_array( $decoded ) && isset( $decoded['gpa'] ) ? $decoded['gpa'] : '';
		$item['scale'] = is_array( $decoded ) && isset( $decoded['scale'] ) ? $decoded['scale'] : '';

		return $item;
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
