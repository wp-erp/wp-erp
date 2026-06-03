<?php
/**
 * WP-ERP HR — `erp/v2/employees/{user_id}/performance` REST controller.
 *
 * Endpoint:
 *   GET /erp/v2/employees/{user_id}/performance — the employee's performance
 *   records grouped into the three legacy buckets (reviews / comments / goals).
 *
 * Read-only: delegates to the unchanged v1 `Employee::get_performances()`.
 * Rating codes are resolved to labels and reviewer/supervisor IDs to names
 * server-side — exactly the lookups the legacy `tab-performance.php` view does.
 * `erp/v1` stays untouched.
 *
 * Permission: `erp_create_review` on the target employee (the meta-cap resolver
 * also grants the reporting supervisor), matching the legacy tab gate.
 */

namespace WeDevs\ERP\HRM\API\V2;

use WeDevs\ERP\HRM\Employee;
use WP_REST_Request;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

class EmployeePerformanceControllerV2 extends RestControllerV2 {

	/**
	 * @var string
	 */
	protected $rest_base = 'employees';

	/**
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<user_id>[\d]+)/performance',
			[
				'args' => [
					'user_id' => [
						'description'       => __( 'Unique employee user ID.', 'erp' ),
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
						'validate_callback' => 'rest_validate_request_arg',
					],
				],
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'permission_view' ],
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_item' ],
					'permission_callback' => [ $this, 'permission_manage' ],
					'args'                => $this->get_create_params(),
				],
				'schema' => [ $this, 'get_item_schema' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<user_id>[\d]+)/performance/(?P<id>[\d]+)',
			[
				'args' => [
					'user_id' => [
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
						'validate_callback' => 'rest_validate_request_arg',
					],
					'id'      => [
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
						'validate_callback' => 'rest_validate_request_arg',
					],
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_item' ],
					'permission_callback' => [ $this, 'permission_delete' ],
				],
			]
		);
	}

	/**
	 * Adding a performance record requires the create-review meta cap.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return bool
	 */
	public function permission_manage( $request ): bool {
		return $this->permission_cap( 'erp_create_review', (int) $request['user_id'] );
	}

	/**
	 * Deleting a performance record requires the delete-review meta cap.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return bool
	 */
	public function permission_delete( $request ): bool {
		return $this->permission_cap( 'erp_delete_review', (int) $request['user_id'] );
	}

	/**
	 * POST /erp/v2/employees/{user_id}/performance
	 *
	 * Delegates to the unchanged `Employee::add_performance()` (same as the v1
	 * `create_performance` controller), preserving its per-type validation.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_item( $request ) {
		$user_id  = (int) $request['user_id'];
		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'Invalid employee id.', 'erp' ), [ 'status' => 404 ] );
		}

		$result = $employee->add_performance( $request->get_params() );

		if ( is_wp_error( $result ) ) {
			return new \WP_Error(
				$result->get_error_code() ?: 'rest_performance_error',
				$result->get_error_message() ?: __( 'The performance record could not be saved.', 'erp' ),
				[ 'status' => 400 ]
			);
		}

		$response = rest_ensure_response( [ 'created' => true ] );
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * DELETE /erp/v2/employees/{user_id}/performance/{id}
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		$user_id  = (int) $request['user_id'];
		$id       = (int) $request['id'];
		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'Invalid employee id.', 'erp' ), [ 'status' => 404 ] );
		}

		$result = $employee->delete_performance( $id );

		if ( is_wp_error( $result ) ) {
			return new \WP_Error(
				$result->get_error_code() ?: 'rest_performance_invalid_id',
				$result->get_error_message() ?: __( 'The performance record could not be deleted.', 'erp' ),
				[ 'status' => 404 ]
			);
		}

		return rest_ensure_response( [ 'deleted' => true, 'id' => $id ] );
	}

	/**
	 * Write params for create — union across the three performance types.
	 *
	 * @return array
	 */
	public function get_create_params(): array {
		$str = [ 'type' => 'string' ];
		return [
			'type'                  => [ 'type' => 'string', 'required' => true, 'enum' => [ 'reviews', 'comments', 'goals' ] ],
			'performance_date'      => $str,
			// reviews
			'reporting_to'          => [ 'type' => 'integer' ],
			'job_knowledge'         => $str,
			'work_quality'          => $str,
			'attendance'            => $str,
			'communication'         => $str,
			'dependablity'          => $str,
			// comments
			'reviewer'              => [ 'type' => 'integer' ],
			'comments'              => $str,
			// goals
			'goal_description'      => $str,
			'employee_assessment'   => $str,
			'supervisor'            => [ 'type' => 'integer' ],
			'supervisor_assessment' => $str,
			'completion_date'       => $str,
		];
	}

	/**
	 * Reading reviews requires the create-review meta cap on the target.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return bool
	 */
	public function permission_view( $request ): bool {
		return $this->permission_cap( 'erp_create_review', (int) $request['user_id'] );
	}

	/**
	 * GET /erp/v2/employees/{user_id}/performance
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_items( $request ) {
		$user_id  = (int) $request['user_id'];
		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'Invalid employee id.', 'erp' ), [ 'status' => 404 ] );
		}

		// Grouped by type: reviews / comments / goals. `get_performances()` returns
		// a grouped Eloquent Collection — `toArray()` unwraps it into plain arrays
		// (a bare `(array)` cast leaves the items in a protected prop → empty).
		$collection = $employee->get_performances( 'all', 100, 0 );
		$grouped    = is_object( $collection ) && method_exists( $collection, 'toArray' )
			? $collection->toArray()
			: (array) $collection;
		$ratings    = (array) erp_performance_rating();

		$data = [
			'reviews'  => $this->map_reviews( $this->bucket( $grouped, 'reviews' ), $ratings ),
			'comments' => $this->map_comments( $this->bucket( $grouped, 'comments' ) ),
			'goals'    => $this->map_goals( $this->bucket( $grouped, 'goals' ) ),
		];

		return rest_ensure_response( $data );
	}

	/**
	 * Pull one grouped bucket out of the collection as a plain array of rows.
	 *
	 * @param array  $grouped Grouped performances.
	 * @param string $type    Bucket key.
	 *
	 * @return array
	 */
	private function bucket( array $grouped, string $type ): array {
		$rows = $grouped[ $type ] ?? [];

		// Eloquent collection → array.
		if ( is_object( $rows ) && method_exists( $rows, 'toArray' ) ) {
			$rows = $rows->toArray();
		}

		return (array) $rows;
	}

	/**
	 * Resolve an employee id to a full name, or '' when invalid.
	 *
	 * @param mixed $id Employee user id.
	 *
	 * @return string
	 */
	private function name_of( $id ): string {
		$id = $this->cast_int_or_null( $id );
		if ( ! $id ) {
			return '';
		}
		$employee = new Employee( $id );
		return $employee->is_employee() ? (string) $employee->get_full_name() : '';
	}

	/**
	 * Resolve a rating code to its label.
	 *
	 * @param mixed $code    Rating code.
	 * @param array $ratings Code → label map.
	 *
	 * @return string
	 */
	private function rating( $code, array $ratings ): string {
		$code = (string) ( $code ?? '' );
		return $code !== '' && isset( $ratings[ $code ] ) ? (string) $ratings[ $code ] : '';
	}

	/**
	 * Performance reviews.
	 *
	 * @param array $rows    Raw rows.
	 * @param array $ratings Rating map.
	 *
	 * @return array
	 */
	private function map_reviews( array $rows, array $ratings ): array {
		$out = [];
		foreach ( $rows as $row ) {
			$row   = (array) $row;
			$out[] = [
				'id'            => (int) ( $row['id'] ?? 0 ),
				'date'          => $this->cast_date_iso( $row['performance_date'] ?? null ),
				'reporting_to'  => $this->name_of( $row['reporting_to'] ?? null ),
				'job_knowledge' => $this->rating( $row['job_knowledge'] ?? '', $ratings ),
				'work_quality'  => $this->rating( $row['work_quality'] ?? '', $ratings ),
				'attendance'    => $this->rating( $row['attendance'] ?? '', $ratings ),
				'communication' => $this->rating( $row['communication'] ?? '', $ratings ),
				'dependability' => $this->rating( $row['dependablity'] ?? '', $ratings ),
			];
		}
		return $out;
	}

	/**
	 * Performance comments.
	 *
	 * @param array $rows Raw rows.
	 *
	 * @return array
	 */
	private function map_comments( array $rows ): array {
		$out = [];
		foreach ( $rows as $row ) {
			$row   = (array) $row;
			$out[] = [
				'id'       => (int) ( $row['id'] ?? 0 ),
				'date'     => $this->cast_date_iso( $row['performance_date'] ?? null ),
				'reviewer' => $this->name_of( $row['reviewer'] ?? null ),
				'comment'  => $this->cast_string_or_null( $row['comments'] ?? '' ) ?? '',
			];
		}
		return $out;
	}

	/**
	 * Performance goals.
	 *
	 * @param array $rows Raw rows.
	 *
	 * @return array
	 */
	private function map_goals( array $rows ): array {
		$out = [];
		foreach ( $rows as $row ) {
			$row   = (array) $row;
			$out[] = [
				'id'                    => (int) ( $row['id'] ?? 0 ),
				'set_date'              => $this->cast_date_iso( $row['performance_date'] ?? null ),
				'completion_date'       => $this->cast_date_iso( $row['completion_date'] ?? null ),
				'goal_description'      => $this->cast_string_or_null( $row['goal_description'] ?? '' ) ?? '',
				'employee_assessment'   => $this->cast_string_or_null( $row['employee_assessment'] ?? '' ) ?? '',
				'supervisor'            => $this->name_of( $row['supervisor'] ?? null ),
				'supervisor_assessment' => $this->cast_string_or_null( $row['supervisor_assessment'] ?? '' ) ?? '',
			];
		}
		return $out;
	}

	/**
	 * JSON Schema for the grouped performance payload.
	 *
	 * @return array
	 */
	public function get_item_schema(): array {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'employee_performance',
			'type'       => 'object',
			'properties' => [
				'reviews'  => [ 'type' => 'array' ],
				'comments' => [ 'type' => 'array' ],
				'goals'    => [ 'type' => 'array' ],
			],
		];
	}
}
