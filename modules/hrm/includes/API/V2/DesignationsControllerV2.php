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

class DesignationsControllerV2 extends RestControllerV2 {

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

		$args = [
			'number'  => $per_page,
			'offset'  => ( $page - 1 ) * $per_page,
			'orderby' => $orderby,
			'order'   => $order,
			's'       => sanitize_text_field( (string) ( $request['search'] ?? '' ) ),
		];

		$designations = (array) erp_hr_get_designations( $args );
		$total        = (int) erp_hr_count_designation();

		// `erp_hr_get_designations()` returns plain objects (via erp_array_to_object),
		// not `Designation` domain instances — so wrap each by id to get the model
		// methods (`num_of_employees()`) the response shape needs.
		$items = [];
		foreach ( $designations as $row ) {
			$id = is_object( $row ) ? (int) ( $row->id ?? 0 ) : (int) ( is_array( $row ) ? ( $row['id'] ?? 0 ) : 0 );
			if ( $id > 0 ) {
				$items[] = $this->prepare_item_for_response( new Designation( $id ), $request );
			}
		}

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

		return [
			'id'              => (int) $designation->id,
			'title'           => $this->cast_string_or_null( $designation->title ) ?? '',
			'description'     => $this->cast_string_or_null( $designation->description ) ?? '',
			'total_employees' => (int) $designation->num_of_employees(),
			'employees'       => $this->employee_previews( 'designation', (int) $designation->id ),
		];
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
