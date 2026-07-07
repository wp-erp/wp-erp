<?php
/**
 * WP-ERP HR — `erp/v2/leave-types` REST controller.
 *
 * Endpoints:
 *   GET    /erp/v2/leave-types              — leave-type list (low cardinality, all in one page).
 *   POST   /erp/v2/leave-types              — create a leave type.
 *   GET    /erp/v2/leave-types/{id}         — single leave type.
 *   PUT    /erp/v2/leave-types/{id}         — update a leave type.
 *   DELETE /erp/v2/leave-types/{id}         — delete a leave type.
 *   POST   /erp/v2/leave-types/bulk-delete  — delete many leave types.
 *
 * Mirrors the legacy AJAX handlers `AjaxHandler::leave_type_create_or_update()`,
 * `get_leave_type()`, `leave_type_delete()` and `leave_type_bulk_delete()` —
 * same `erp_leave_manage` cap, same `erp_hr_insert_leave_policy_name()` /
 * `erp_hr_remove_leave_policy_name()` model calls (which carry the unique-name
 * guard and the "associated with a policy" delete guard), same
 * `leave_policy_name` cache purge. Only the request/response envelope is the
 * modern v2 contract. `erp/v1` and the AJAX layer stay untouched.
 *
 * A "leave type" is a row in `erp_hr_leaves` (the `Models\Leave` model);
 * historically the model fn is named `*_leave_policy_name`, but the admin UI and
 * this controller surface it as "Leave Type".
 */

namespace WeDevs\ERP\HRM\API\V2;

use WeDevs\ERP\HRM\Models\Leave;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

\defined( 'ABSPATH' ) || exit;

class LeaveTypesControllerV2 extends RestControllerV2 {

	/**
	 * @var string
	 */
	protected $rest_base = 'leave-types';

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
					'permission_callback' => [ $this, 'permission_manage' ],
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
			'/' . $this->rest_base . '/bulk-delete',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'bulk_delete' ],
					'permission_callback' => [ $this, 'permission_manage' ],
					'args'                => [
						'ids' => [
							'description' => __( 'Leave type IDs to delete.', 'erp' ),
							'type'        => 'array',
							'required'    => true,
							'items'       => [ 'type' => 'integer' ],
						],
					],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			[
				'args' => [
					'id' => [
						'description'       => __( 'Unique leave type ID.', 'erp' ),
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
						'validate_callback' => 'rest_validate_request_arg',
					],
				],
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_item' ],
					'permission_callback' => [ $this, 'permission_manage' ],
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
	 * Every leave-type operation requires the leave-management capability — same
	 * gate as every legacy leave-type AJAX handler (incl. read).
	 *
	 * @return bool
	 */
	public function permission_manage(): bool {
		return $this->permission_cap( 'erp_leave_manage' );
	}

	/**
	 * GET /erp/v2/leave-types
	 *
	 * Leave types are low-cardinality, so the whole set is returned in one page
	 * (matching the legacy list table, which calls `Leave::all()`). An optional
	 * client-side `search` narrows by name/description.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_items( $request ): WP_REST_Response {
		$types  = erp_hr_get_leave_policy_names();
		$types  = is_object( $types ) && method_exists( $types, 'toArray' ) ? $types->toArray() : (array) $types;
		$search = strtolower( sanitize_text_field( (string) ( $request['search'] ?? '' ) ) );

		$items = [];
		foreach ( $types as $type ) {
			$row = $this->prepare_item_for_response( $type, $request );

			if ( '' !== $search ) {
				$haystack = strtolower( $row['name'] . ' ' . $row['description'] );
				if ( false === strpos( $haystack, $search ) ) {
					continue;
				}
			}

			$items[] = $row;
		}

		$response = rest_ensure_response( $items );
		return $this->paginate( $response, $request, \count( $items ) );
	}

	/**
	 * GET /erp/v2/leave-types/{id}
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function get_item( $request ) {
		$leave = Leave::find( (int) $request['id'] );

		if ( empty( $leave ) ) {
			return new \WP_Error( 'rest_leave_type_invalid_id', __( 'No valid leave type found!', 'erp' ), [ 'status' => 404 ] );
		}

		return rest_ensure_response( $this->prepare_item_for_response( $leave, $request ) );
	}

	/**
	 * POST /erp/v2/leave-types
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function create_item( $request ) {
		$name = isset( $request['name'] ) ? sanitize_text_field( $request['name'] ) : '';

		if ( '' === $name ) {
			return new \WP_Error( 'rest_leave_type_no_name', __( 'Name field should not be left empty', 'erp' ), [ 'status' => 400 ] );
		}

		$id = erp_hr_insert_leave_policy_name( $this->prepare_item_for_database( $request ) );

		if ( is_wp_error( $id ) ) {
			return $this->to_rest_error( $id, 409 );
		}

		$response = rest_ensure_response( $this->prepare_item_for_response( Leave::find( (int) $id ), $request ) );
		$response->set_status( 201 );
		$response->header(
			'Location',
			rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, (int) $id ) )
		);

		return $response;
	}

	/**
	 * PUT /erp/v2/leave-types/{id}
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function update_item( $request ) {
		$id    = (int) $request['id'];
		$leave = Leave::find( $id );

		if ( empty( $leave ) ) {
			return new \WP_Error( 'rest_leave_type_invalid_id', __( 'Leave Type doesn\'t exists.', 'erp' ), [ 'status' => 404 ] );
		}

		$name = isset( $request['name'] ) ? sanitize_text_field( $request['name'] ) : '';
		if ( '' === $name ) {
			return new \WP_Error( 'rest_leave_type_no_name', __( 'Name field should not be left empty', 'erp' ), [ 'status' => 400 ] );
		}

		$data       = $this->prepare_item_for_database( $request );
		$data['id'] = $id;

		$result = erp_hr_insert_leave_policy_name( $data );

		if ( is_wp_error( $result ) ) {
			return $this->to_rest_error( $result, 409 );
		}

		return rest_ensure_response( $this->prepare_item_for_response( Leave::find( $id ), $request ) );
	}

	/**
	 * DELETE /erp/v2/leave-types/{id}
	 *
	 * Surfaces the legacy "associated with a policy" guard verbatim:
	 * `erp_hr_remove_leave_policy_name()` returns a `has_policy` WP_Error which we
	 * map to HTTP 409.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		$id    = (int) $request['id'];
		$leave = Leave::find( $id );

		if ( empty( $leave ) ) {
			return new \WP_Error( 'rest_leave_type_invalid_id', __( 'No valid leave type found!', 'erp' ), [ 'status' => 404 ] );
		}

		$result = erp_hr_remove_leave_policy_name( $id );

		if ( is_wp_error( $result ) ) {
			return $this->to_rest_error( $result, 409 );
		}

		return rest_ensure_response( [ 'deleted' => true, 'id' => $id ] );
	}

	/**
	 * POST /erp/v2/leave-types/bulk-delete
	 *
	 * Mirrors `AjaxHandler::leave_type_bulk_delete()` — best-effort: each ID goes
	 * through the guarded delete, failures (policy-associated) are skipped and
	 * reported, not fatal.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function bulk_delete( $request ) {
		$ids = array_filter( array_map( 'absint', (array) ( $request['ids'] ?? [] ) ) );

		if ( empty( $ids ) ) {
			return new \WP_Error( 'rest_leave_type_no_ids', __( 'No valid leave type found!', 'erp' ), [ 'status' => 400 ] );
		}

		$deleted = 0;
		$skipped = [];

		foreach ( $ids as $id ) {
			if ( ! is_wp_error( erp_hr_remove_leave_policy_name( $id ) ) ) {
				++$deleted;
			} else {
				$skipped[] = $id;
			}
		}

		if ( 0 === $deleted ) {
			return new \WP_Error(
				'rest_leave_type_none_deleted',
				__( 'No items were deleted as they are associated with policy', 'erp' ),
				[ 'status' => 409 ]
			);
		}

		return rest_ensure_response(
			[
				'deleted' => $deleted,
				'skipped' => array_values( $skipped ),
				'total'   => \count( $ids ),
			]
		);
	}

	/**
	 * Map the flat v2 payload onto the args `erp_hr_insert_leave_policy_name()` expects.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return array
	 */
	protected function prepare_item_for_database( $request ): array {
		return [
			'name'        => isset( $request['name'] ) ? sanitize_text_field( $request['name'] ) : '',
			'description' => isset( $request['description'] ) ? sanitize_textarea_field( $request['description'] ) : '',
		];
	}

	/**
	 * Reshape a Leave model / row into the v2 row.
	 *
	 * @param mixed           $leave   A `Models\Leave` instance or array/stdClass.
	 * @param WP_REST_Request $request Request.
	 *
	 * @return array
	 */
	public function prepare_item_for_response( $leave, $request ) {
		unset( $request );

		$leave = (object) ( is_object( $leave ) && method_exists( $leave, 'toArray' ) ? $leave->toArray() : (array) $leave );

		// `created_at` is stored as a Unix timestamp (int) in `erp_hr_leaves`;
		// surface it as an ISO-8601 string so the React list can show a
		// Created-At column (legacy `Leave` model carried it but the v2 row
		// dropped it).
		$created_raw = $leave->created_at ?? null;
		$created_at  = ( is_numeric( $created_raw ) && (int) $created_raw > 0 )
			? gmdate( 'c', (int) $created_raw )
			: $this->cast_date_iso( $created_raw );

		return [
			'id'          => (int) ( $leave->id ?? 0 ),
			'name'        => $this->cast_string_or_null( $leave->name ?? '' ) ?? '',
			'description' => $this->cast_string_or_null( $leave->description ?? '' ) ?? '',
			'created_at'  => $created_at,
		];
	}

	/**
	 * Write params for create / update.
	 *
	 * @return array
	 */
	public function get_write_params(): array {
		return [
			'name'        => [
				'description'       => __( 'Leave type name.', 'erp' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'description' => [
				'description'       => __( 'Leave type description.', 'erp' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_textarea_field',
			],
		];
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
			$error->get_error_code() ?: 'rest_leave_type_error',
			$error->get_error_message() ?: __( 'The leave type could not be saved.', 'erp' ),
			[ 'status' => $status ]
		);
	}

	/**
	 * JSON Schema for a single leave type.
	 *
	 * @return array
	 */
	public function get_item_schema(): array {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'leave_type',
			'type'       => 'object',
			'properties' => [
				'id'          => [ 'type' => 'integer' ],
				'name'        => [ 'type' => 'string' ],
				'description' => [ 'type' => 'string' ],
				'created_at'  => [ 'type' => [ 'string', 'null' ] ],
			],
		];
	}
}
