<?php
/**
 * WP-ERP HR — `erp/v2/departments` REST controller.
 *
 * Endpoints:
 *   GET    /erp/v2/departments            — paginated department list.
 *   POST   /erp/v2/departments            — create a department.
 *   GET    /erp/v2/departments/{id}       — single department.
 *   PUT    /erp/v2/departments/{id}       — update a department.
 *   DELETE /erp/v2/departments/{id}       — delete a department.
 *
 * Every mutation delegates to the unchanged v1 model layer
 * (`erp_hr_create_department()`, `erp_hr_delete_department()`) so all legacy
 * hooks (`erp_hr_dept_new`, `erp_hr_dept_*_updated`, `erp_hr_dept_delete`),
 * the child re-parenting on delete, the "department not empty" guard and the
 * cache purge keep firing. Only the request/response envelope is the modern v2
 * contract. `erp/v1` stays untouched.
 */

namespace WeDevs\ERP\HRM\API\V2;

use WeDevs\ERP\HRM\Department;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

\defined( 'ABSPATH' ) || exit;

class DepartmentsController extends RestController {

	/**
	 * @var string
	 */
	protected $rest_base = 'departments';

	/**
	 * Allowed orderby keys (whitelisted against the v1 model query).
	 */
	private const ORDERBY = [ 'id', 'title', 'created_at' ];

	/**
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'permission_view' ],
					'args'                => $this->get_collection_params(),
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_item' ],
					'permission_callback' => [ $this, 'permission_manage' ],
					'args'                => $this->get_write_params(),
				],
				'schema' => [ $this, 'get_item_schema' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			[
				'args' => [
					'id' => [
						'description'       => __( 'Unique department ID.', 'erp' ),
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
						'validate_callback' => 'rest_validate_request_arg',
					],
				],
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_item' ],
					'permission_callback' => [ $this, 'permission_view' ],
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_item' ],
					'permission_callback' => [ $this, 'permission_manage' ],
					'args'                => $this->get_write_params(),
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_item' ],
					'permission_callback' => [ $this, 'permission_manage' ],
				],
				'schema' => [ $this, 'get_item_schema' ],
			]
		);
	}

	/**
	 * Listing requires the shared HR view capability (managers + employees).
	 *
	 * @return bool
	 */
	public function permission_view(): bool {
		return $this->permission_cap( 'erp_view_list' );
	}

	/**
	 * Create / update / delete require the department-management capability.
	 *
	 * @return bool
	 */
	public function permission_manage(): bool {
		return $this->permission_cap( 'erp_manage_department' );
	}

	/**
	 * GET /erp/v2/departments
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_items( $request ): WP_REST_Response {
		$page     = max( 1, (int) ( $request['page'] ?? 1 ) );
		$per_page = max( 1, min( 100, (int) ( $request['per_page'] ?? 20 ) ) );

		$orderby = $this->cast_enum( (string) ( $request['orderby'] ?? 'title' ), self::ORDERBY ) ?? 'title';
		$order   = strtoupper( (string) ( $request['order'] ?? 'asc' ) );
		$order   = \in_array( $order, [ 'ASC', 'DESC' ], true ) ? $order : 'ASC';

		$args = [
			'number'  => -1,
			'orderby' => $orderby,
			'order'   => $order,
			's'       => sanitize_text_field( (string) ( $request['search'] ?? '' ) ),
		];

		// erp_hr_get_departments() ignores number/offset/orderby/order — it always
		// returns the full set, parent-sorted (a deliberate hierarchy ordering we
		// must preserve). So fetch all (search-filtered), then derive the true
		// search-aware total and the requested page slice here in the controller.
		// Otherwise departments beyond #100 are silently lost.
		$departments = array_values(
			array_filter(
				(array) erp_hr_get_departments( $args ),
				static function ( $department ) {
					return $department instanceof Department;
				}
			)
		);

		$total  = \count( $departments );
		$offset = ( $page - 1 ) * $per_page;
		$page_departments = \array_slice( $departments, $offset, $per_page );

		$this->prime_rows( $page_departments );

		$items = [];
		foreach ( $page_departments as $department ) {
			$items[] = $this->prepare_item_for_response( $department, $request );
		}

		// The batch is for this page only: a later single-row response must
		// read fresh values.
		$this->primed = [];

		$response = rest_ensure_response( $items );
		return $this->paginate( $response, $request, $total );
	}

	/**
	 * GET /erp/v2/departments/{id}
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function get_item( $request ) {
		$department = new Department( (int) $request['id'] );

		if ( ! $department->id ) {
			return new \WP_Error( 'rest_department_invalid_id', __( 'Invalid department id.', 'erp' ), [ 'status' => 404 ] );
		}

		return rest_ensure_response( $this->prepare_item_for_response( $department, $request ) );
	}

	/**
	 * POST /erp/v2/departments
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function create_item( $request ) {
		$data = $this->prepare_item_for_database( $request );

		if ( isset( $data['title'] ) ) {
			$dup = $this->duplicate_title_error( (string) $data['title'], 0 );
			if ( $dup ) {
				return $dup;
			}
		}

		$invalid = $this->relation_error( $data, null );
		if ( $invalid ) {
			return $invalid;
		}

		$id = erp_hr_create_department( $data );

		if ( is_wp_error( $id ) ) {
			return $this->to_rest_error( $id );
		}

		$response = rest_ensure_response( $this->prepare_item_for_response( new Department( (int) $id ), $request ) );
		$response->set_status( 201 );
		$response->header(
			'Location',
			rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, (int) $id ) )
		);

		return $response;
	}

	/**
	 * PUT /erp/v2/departments/{id}
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function update_item( $request ) {
		$dept_id    = (int) $request['id'];
		$department = new Department( $dept_id );

		if ( ! $department->id ) {
			return new \WP_Error( 'rest_department_invalid_id', __( 'Invalid department id.', 'erp' ), [ 'status' => 404 ] );
		}

		$data       = $this->prepare_item_for_database( $request );
		$data['id'] = $dept_id;

		// A department cannot be its own parent (mirrors the legacy guard).
		if ( isset( $data['parent'] ) && (int) $data['parent'] === $dept_id ) {
			$data['parent'] = 0;
		}

		if ( isset( $data['title'] ) ) {
			$dup = $this->duplicate_title_error( (string) $data['title'], $dept_id );
			if ( $dup ) {
				return $dup;
			}
		}

		$invalid = $this->relation_error( $data, $department );
		if ( $invalid ) {
			return $invalid;
		}

		$id = erp_hr_create_department( $data );

		if ( is_wp_error( $id ) ) {
			return $this->to_rest_error( $id );
		}

		return rest_ensure_response( $this->prepare_item_for_response( new Department( $dept_id ), $request ) );
	}

	/**
	 * A `WP_Error` when another department already uses this title (case-insensitive),
	 * else null. Mirrors the legacy `department_create` duplicate guard.
	 *
	 * @param string $title      Proposed title.
	 * @param int    $exclude_id Department id to exclude (0 on create).
	 *
	 * @return \WP_Error|null
	 */
	protected function duplicate_title_error( string $title, int $exclude_id ) {
		$exist = \WeDevs\ERP\HRM\Models\Department::where( 'id', '!=', $exclude_id )
			->where( 'title', 'like', $title )->first();

		if ( $exist && (int) $exist->id !== $exclude_id ) {
			return new \WP_Error( 'rest_department_duplicate', __( 'Multiple department with the same name is not allowed.', 'erp' ), [ 'status' => 400 ] );
		}

		return null;
	}

	/**
	 * A `WP_Error` when the submitted parent or lead cannot be saved, else null.
	 *
	 * The list is built by walking down from the top-level departments
	 * (`erp_parent_sort()`), so a department whose parent is missing, or whose
	 * parent chain loops back to itself, drops out of every list and dropdown.
	 * A value that is not changing is left alone, so a department saved before
	 * this check can still be renamed.
	 *
	 * @param array           $data       Prepared department data.
	 * @param Department|null $department The department being updated (null on create).
	 *
	 * @return \WP_Error|null
	 */
	protected function relation_error( array $data, $department ) {
		$dept_id = $department ? (int) $department->id : 0;

		if ( ! empty( $data['parent'] ) && ( ! $department || (int) $department->parent !== (int) $data['parent'] ) ) {
			$seen      = [];
			$parent_id = (int) $data['parent'];

			while ( $parent_id > 0 && ! isset( $seen[ $parent_id ] ) ) {
				if ( $parent_id === $dept_id ) {
					return new \WP_Error( 'rest_department_parent_cycle', __( 'A department cannot be placed under one of its own sub-departments.', 'erp' ), [ 'status' => 400 ] );
				}

				$seen[ $parent_id ] = true;
				$parent             = \WeDevs\ERP\HRM\Models\Department::find( $parent_id );

				if ( ! $parent ) {
					// Only the submitted parent has to exist: a gap further up is
					// old data, not something this request introduces.
					if ( 1 === \count( $seen ) ) {
						return new \WP_Error( 'rest_department_invalid_parent', __( 'The selected parent department does not exist.', 'erp' ), [ 'status' => 400 ] );
					}

					break;
				}

				$parent_id = (int) $parent->parent;
			}
		}

		if ( ! empty( $data['lead'] ) && ( ! $department || (int) $department->lead !== (int) $data['lead'] ) ) {
			if ( ! \WeDevs\ERP\HRM\Models\Employee::where( 'user_id', (int) $data['lead'] )->exists() ) {
				return new \WP_Error( 'rest_department_invalid_lead', __( 'The department lead must be an employee.', 'erp' ), [ 'status' => 400 ] );
			}
		}

		return null;
	}

	/**
	 * DELETE /erp/v2/departments/{id}
	 *
	 * Surfaces the legacy "department not empty" guard verbatim: if active
	 * employees are still assigned, `erp_hr_delete_department()` returns a
	 * `not-empty` WP_Error which we map to HTTP 409.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		$dept_id    = (int) $request['id'];
		$department = new Department( $dept_id );

		if ( ! $department->id ) {
			return new \WP_Error( 'rest_department_invalid_id', __( 'Invalid department id.', 'erp' ), [ 'status' => 404 ] );
		}

		$result = erp_hr_delete_department( $dept_id );

		if ( is_wp_error( $result ) ) {
			return $this->to_rest_error( $result, 409 );
		}

		return rest_ensure_response( [ 'deleted' => true, 'id' => $dept_id ] );
	}

	/**
	 * Map the flat v2 payload onto the args `erp_hr_create_department()` expects.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return array
	 */
	protected function prepare_item_for_database( $request ): array {
		$data = [];

		if ( isset( $request['title'] ) ) {
			$data['title'] = sanitize_text_field( $request['title'] );
		}
		if ( isset( $request['description'] ) ) {
			$data['description'] = sanitize_textarea_field( $request['description'] );
		}
		if ( isset( $request['lead'] ) ) {
			$data['lead'] = absint( $request['lead'] );
		}
		if ( isset( $request['parent'] ) ) {
			$data['parent'] = absint( $request['parent'] );
		}

		return $data;
	}

	/**
	 * Reshape a Department domain object into the v2 row.
	 *
	 * @param mixed           $department A `WeDevs\ERP\HRM\Department` instance.
	 * @param WP_REST_Request $request    Request.
	 *
	 * @return array
	 */
	public function prepare_item_for_response( $department, $request ) {
		unset( $request );

		if ( ! ( $department instanceof Department ) ) {
			return [];
		}

		$id        = (int) $department->id;
		$lead_id   = $this->cast_int_or_null( $department->lead );
		$parent_id = $this->cast_int_or_null( $department->parent );
		$primed    = isset( $this->primed[ $id ] ) ? $this->primed[ $id ] : null;

		$lead_name = '';
		if ( $primed ) {
			$lead_name = $primed['lead_name'];
		} elseif ( $lead_id ) {
			$lead = $department->get_lead();
			if ( $lead ) {
				$lead_name = (string) $lead->get_full_name();
			}
		}

		$parent_title = '';
		if ( $primed ) {
			$parent_title = $primed['parent_title'];
		} elseif ( $parent_id ) {
			$parent       = new Department( $parent_id );
			$parent_title = (string) $parent->title;
		}

		return [
			'id'              => $id,
			'title'           => $this->cast_string_or_null( $department->title ) ?? '',
			'description'     => $this->cast_string_or_null( $department->description ) ?? '',
			'lead'            => $lead_id,
			'lead_name'       => $lead_name,
			'parent'          => $parent_id,
			'parent_title'    => $parent_title,
			'total_employees' => $primed ? $primed['total'] : (int) $department->num_of_employees(),
			'employees'       => $primed ? $primed['employees'] : $this->employee_previews( 'department', $id ),
		];
	}

	/**
	 * Lead name, parent title, employee count and avatar previews for a page of
	 * departments, keyed by department id.
	 *
	 * @var array
	 */
	private $primed = [];

	/**
	 * Load what the list rows need in a fixed number of queries. The per-row
	 * path built an `Employee` object for the lead and for each of the three
	 * previews, over 800 queries for a page of 10.
	 *
	 * @param Department[] $departments Departments on the page.
	 *
	 * @return void
	 */
	private function prime_rows( array $departments ): void {
		global $wpdb;

		$primed     = [];
		$lead_ids   = [];
		$parent_ids = [];

		foreach ( $departments as $department ) {
			$id = (int) $department->id;

			if ( $id <= 0 ) {
				continue;
			}

			$primed[ $id ] = [
				'lead'         => (int) $department->lead,
				'parent'       => (int) $department->parent,
				'lead_name'    => '',
				'parent_title' => '',
				'total'        => 0,
				'users'        => [],
				'employees'    => [],
			];

			$lead_ids[]   = (int) $department->lead;
			$parent_ids[] = (int) $department->parent;
		}

		if ( empty( $primed ) ) {
			return;
		}

		$rows = $wpdb->get_results(
			"SELECT user_id, department
			 FROM {$wpdb->prefix}erp_hr_employees
			 WHERE status = 'active'
			   AND deleted_at IS NULL
			   AND department IN ( " . implode( ',', array_keys( $primed ) ) . ' )
			 ORDER BY id ASC'
		);

		$preview_ids = [];
		foreach ( (array) $rows as $row ) {
			$id = (int) $row->department;
			$primed[ $id ]['total']++;

			if ( \count( $primed[ $id ]['users'] ) < 3 ) {
				$primed[ $id ]['users'][] = (int) $row->user_id;
				$preview_ids[]            = (int) $row->user_id;
			}
		}

		$parent_titles = [];
		$parent_ids    = array_values( array_unique( array_filter( $parent_ids ) ) );

		if ( $parent_ids ) {
			$parents = $wpdb->get_results( "SELECT id, title FROM {$wpdb->prefix}erp_hr_depts WHERE id IN ( " . implode( ',', $parent_ids ) . ' )' );

			foreach ( (array) $parents as $parent ) {
				$parent_titles[ (int) $parent->id ] = stripslashes( (string) $parent->title );
			}
		}

		$people = erp_hr_get_employee_display_data( array_merge( $preview_ids, $lead_ids ), 40 );

		foreach ( $primed as $id => $row ) {
			if ( ! empty( $people[ $row['lead'] ]['user_id'] ) ) {
				$primed[ $id ]['lead_name'] = (string) $people[ $row['lead'] ]['full_name'];
			}

			if ( isset( $parent_titles[ $row['parent'] ] ) ) {
				$primed[ $id ]['parent_title'] = $parent_titles[ $row['parent'] ];
			}

			foreach ( $row['users'] as $user_id ) {
				$primed[ $id ]['employees'][] = [
					'name'   => isset( $people[ $user_id ] ) ? (string) $people[ $user_id ]['full_name'] : '',
					'avatar' => ( isset( $people[ $user_id ] ) ? $people[ $user_id ]['avatar'] : get_avatar_url( 0, [ 'size' => 40 ] ) ) ?: null,
				];
			}
		}

		$this->primed = $primed;
	}

	/**
	 * Write params for create / update.
	 *
	 * @return array
	 */
	public function get_write_params(): array {
		return [
			'title'       => [
				'description'       => __( 'Department name.', 'erp' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'description' => [
				'description'       => __( 'Department description.', 'erp' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_textarea_field',
			],
			'lead'        => [
				'description'       => __( 'Department head (employee user ID).', 'erp' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'parent'      => [
				'description'       => __( 'Parent department ID.', 'erp' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			],
		];
	}

	/**
	 * Collection params: pagination + sort.
	 *
	 * @return array
	 */
	public function get_collection_params(): array {
		$params = parent::get_collection_params();

		$params['orderby'] = [
			'description'       => __( 'Sort field.', 'erp' ),
			'type'              => 'string',
			'default'           => 'title',
			'enum'              => self::ORDERBY,
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		];
		$params['order'] = [
			'description'       => __( 'Sort direction.', 'erp' ),
			'type'              => 'string',
			'default'           => 'asc',
			'enum'              => [ 'asc', 'desc' ],
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		];

		return $params;
	}

	/**
	 * Convert a model WP_Error into a REST WP_Error with an HTTP status.
	 *
	 * @param \WP_Error $error  Error from the model layer.
	 * @param int       $status HTTP status (default 400).
	 *
	 * @return \WP_Error
	 */
	private function to_rest_error( \WP_Error $error, int $status = 400 ): \WP_Error {
		return new \WP_Error(
			$error->get_error_code() ?: 'rest_department_error',
			$error->get_error_message() ?: __( 'The department could not be saved.', 'erp' ),
			[ 'status' => $status ]
		);
	}

	/**
	 * JSON Schema for a single department.
	 *
	 * @return array
	 */
	public function get_item_schema(): array {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'department',
			'type'       => 'object',
			'properties' => [
				'id'              => [ 'type' => 'integer' ],
				'title'           => [ 'type' => 'string' ],
				'description'     => [ 'type' => 'string' ],
				'lead'            => [ 'type' => [ 'integer', 'null' ] ],
				'lead_name'       => [ 'type' => 'string' ],
				'parent'          => [ 'type' => [ 'integer', 'null' ] ],
				'parent_title'    => [ 'type' => 'string' ],
				'total_employees' => [ 'type' => 'integer' ],
				'employees'       => [
					'type'  => 'array',
					'items' => [
						'type'       => 'object',
						'properties' => [
							'name'   => [ 'type' => 'string' ],
							'avatar' => [ 'type' => [ 'string', 'null' ] ],
						],
					],
				],
			],
		];
	}
}
