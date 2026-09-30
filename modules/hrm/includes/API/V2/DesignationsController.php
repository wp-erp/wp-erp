<?php
/**
 * WP-ERP HR — `erp/v2/designations` REST controller.
 *
 * Endpoints:
 *   GET    /erp/v2/designations          — paginated designation list.
 *   POST   /erp/v2/designations          — create a designation.
 *   GET    /erp/v2/designations/{id}     — single designation.
 *   PUT    /erp/v2/designations/{id}     — update a designation.
 *   DELETE /erp/v2/designations/{id}     — delete a designation.
 *
 * Every mutation delegates to the unchanged v1 model layer
 * (`erp_hr_create_designation()`, `erp_hr_delete_designation()`) so all legacy
 * hooks (`erp_hr_desig_new`, `erp_hr_desig_*_updated`, `erp_hr_desig_delete`),
 * the "designation not empty" guard and the cache purge keep firing. Only the
 * request/response envelope is the modern v2 contract. `erp/v1` stays untouched.
 */

namespace WeDevs\ERP\HRM\API\V2;

use WeDevs\ERP\HRM\Designation;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

\defined( 'ABSPATH' ) || exit;

class DesignationsController extends RestController {

	/**
	 * @var string
	 */
	protected $rest_base = 'designations';

	/**
	 * Allowed orderby keys.
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
						'description'       => __( 'Unique designation ID.', 'erp' ),
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
	 * Create / update / delete require the designation-management capability.
	 *
	 * @return bool
	 */
	public function permission_manage(): bool {
		return $this->permission_cap( 'erp_manage_designation' );
	}

	/**
	 * GET /erp/v2/designations
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

		$search = sanitize_text_field( (string) ( $request['search'] ?? '' ) );

		// `erp_hr_get_designations()` ignores its `s` argument and
		// `erp_hr_count_designation()` counts every row, so a search returned
		// the full list with the unfiltered total. Query the model directly.
		$query = \WeDevs\ERP\HRM\Models\Designation::query();

		if ( '' !== $search ) {
			global $wpdb;

			$query->where( 'title', 'LIKE', '%' . $wpdb->esc_like( $search ) . '%' );
		}

		$total = (int) $query->count();
		$rows  = $query->orderBy( $orderby, $order )
			->skip( ( $page - 1 ) * $per_page )
			->take( $per_page )
			->get()
			->toArray();

		$this->prime_rows( array_map( 'intval', wp_list_pluck( $rows, 'id' ) ) );

		$items = [];
		foreach ( $rows as $row ) {
			if ( (int) $row['id'] > 0 ) {
				$items[] = $this->prepare_item_for_response( new Designation( (object) $row ), $request );
			}
		}

		// The batch is for this page only: a later single-row response must
		// read fresh values.
		$this->primed = [];

		$response = rest_ensure_response( $items );
		return $this->paginate( $response, $request, $total );
	}

	/**
	 * GET /erp/v2/designations/{id}
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function get_item( $request ) {
		$designation = new Designation( (int) $request['id'] );

		if ( ! $designation->id ) {
			return new \WP_Error( 'rest_designation_invalid_id', __( 'Invalid designation id.', 'erp' ), [ 'status' => 404 ] );
		}

		return rest_ensure_response( $this->prepare_item_for_response( $designation, $request ) );
	}

	/**
	 * POST /erp/v2/designations
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

		$id = erp_hr_create_designation( $data );

		if ( is_wp_error( $id ) ) {
			return $this->to_rest_error( $id );
		}

		$response = rest_ensure_response( $this->prepare_item_for_response( new Designation( (int) $id ), $request ) );
		$response->set_status( 201 );
		$response->header(
			'Location',
			rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, (int) $id ) )
		);

		return $response;
	}

	/**
	 * PUT /erp/v2/designations/{id}
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function update_item( $request ) {
		$desig_id    = (int) $request['id'];
		$designation = new Designation( $desig_id );

		if ( ! $designation->id ) {
			return new \WP_Error( 'rest_designation_invalid_id', __( 'Invalid designation id.', 'erp' ), [ 'status' => 404 ] );
		}

		$data       = $this->prepare_item_for_database( $request );
		$data['id'] = $desig_id;

		if ( isset( $data['title'] ) ) {
			$dup = $this->duplicate_title_error( (string) $data['title'], $desig_id );
			if ( $dup ) {
				return $dup;
			}
		}

		$id = erp_hr_create_designation( $data );

		if ( is_wp_error( $id ) ) {
			return $this->to_rest_error( $id );
		}

		return rest_ensure_response( $this->prepare_item_for_response( new Designation( $desig_id ), $request ) );
	}

	/**
	 * A `WP_Error` when another designation already uses this title (case-insensitive),
	 * else null. Mirrors the legacy `designation_create` duplicate guard.
	 *
	 * @param string $title      Proposed title.
	 * @param int    $exclude_id Designation id to exclude (0 on create).
	 *
	 * @return \WP_Error|null
	 */
	protected function duplicate_title_error( string $title, int $exclude_id ) {
		$exist = \WeDevs\ERP\HRM\Models\Designation::where( 'id', '!=', $exclude_id )
			->where( 'title', 'like', $title )->first();

		if ( $exist && (int) $exist->id !== $exclude_id ) {
			return new \WP_Error( 'rest_designation_duplicate', __( 'Multiple designation with the same name is not allowed.', 'erp' ), [ 'status' => 400 ] );
		}

		return null;
	}

	/**
	 * DELETE /erp/v2/designations/{id}
	 *
	 * Surfaces the legacy "designation not empty" guard verbatim: if active
	 * employees still hold this designation, `erp_hr_delete_designation()`
	 * returns a `not-empty` WP_Error which we map to HTTP 409.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		$desig_id    = (int) $request['id'];
		$designation = new Designation( $desig_id );

		if ( ! $designation->id ) {
			return new \WP_Error( 'rest_designation_invalid_id', __( 'Invalid designation id.', 'erp' ), [ 'status' => 404 ] );
		}

		$result = erp_hr_delete_designation( $desig_id );

		if ( is_wp_error( $result ) ) {
			return $this->to_rest_error( $result, 409 );
		}

		return rest_ensure_response( [ 'deleted' => true, 'id' => $desig_id ] );
	}

	/**
	 * Map the flat v2 payload onto the args `erp_hr_create_designation()` expects.
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

		return $data;
	}

	/**
	 * Reshape a Designation domain object into the v2 row.
	 *
	 * @param mixed           $designation A `WeDevs\ERP\HRM\Designation` instance.
	 * @param WP_REST_Request $request     Request.
	 *
	 * @return array
	 */
	public function prepare_item_for_response( $designation, $request ) {
		unset( $request );

		if ( ! ( $designation instanceof Designation ) ) {
			return [];
		}

		$id = (int) $designation->id;

		return [
			'id'              => $id,
			'title'           => $this->cast_string_or_null( $designation->title ) ?? '',
			'description'     => $this->cast_string_or_null( $designation->description ) ?? '',
			'total_employees' => isset( $this->primed[ $id ] ) ? $this->primed[ $id ]['total'] : (int) $designation->num_of_employees(),
			'employees'       => isset( $this->primed[ $id ] ) ? $this->primed[ $id ]['employees'] : $this->employee_previews( 'designation', $id ),
		];
	}

	/**
	 * Employee count and avatar previews for a page of designations, keyed by
	 * designation id.
	 *
	 * @var array
	 */
	private $primed = [];

	/**
	 * Load the employee count and the first three employee previews of every
	 * listed designation in a fixed number of queries. The per-row path built
	 * three `Employee` objects per designation, about a thousand queries for a
	 * page of 16.
	 *
	 * @param int[] $ids Designation ids.
	 *
	 * @return void
	 */
	private function prime_rows( array $ids ): void {
		global $wpdb;

		$ids = array_values( array_filter( array_map( 'absint', $ids ) ) );

		if ( empty( $ids ) ) {
			return;
		}

		$primed = array_fill_keys( $ids, [ 'total' => 0, 'users' => [], 'employees' => [] ] );

		$rows = $wpdb->get_results(
			"SELECT user_id, designation
			 FROM {$wpdb->prefix}erp_hr_employees
			 WHERE status = 'active'
			   AND deleted_at IS NULL
			   AND designation IN ( " . implode( ',', $ids ) . ' )
			 ORDER BY id ASC'
		);

		$preview_ids = [];
		foreach ( (array) $rows as $row ) {
			$id = (int) $row->designation;
			$primed[ $id ]['total']++;

			if ( \count( $primed[ $id ]['users'] ) < 3 ) {
				$primed[ $id ]['users'][] = (int) $row->user_id;
				$preview_ids[]            = (int) $row->user_id;
			}
		}

		$people = erp_hr_get_employee_display_data( $preview_ids, 40 );

		foreach ( $primed as $id => $row ) {
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
				'description'       => __( 'Designation name.', 'erp' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'description' => [
				'description'       => __( 'Designation description.', 'erp' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_textarea_field',
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
			$error->get_error_code() ?: 'rest_designation_error',
			$error->get_error_message() ?: __( 'The designation could not be saved.', 'erp' ),
			[ 'status' => $status ]
		);
	}

	/**
	 * JSON Schema for a single designation.
	 *
	 * @return array
	 */
	public function get_item_schema(): array {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'designation',
			'type'       => 'object',
			'properties' => [
				'id'              => [ 'type' => 'integer' ],
				'title'           => [ 'type' => 'string' ],
				'description'     => [ 'type' => 'string' ],
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
