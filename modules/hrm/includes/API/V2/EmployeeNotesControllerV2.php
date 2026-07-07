<?php
/**
 * WP-ERP HR — `erp/v2/employees/{user_id}/notes` REST controller.
 *
 * Endpoints:
 *   GET    /erp/v2/employees/{user_id}/notes            — paginated note list.
 *   POST   /erp/v2/employees/{user_id}/notes            — add a note.
 *   DELETE /erp/v2/employees/{user_id}/notes/{note_id}  — delete a note.
 *
 * Every mutation delegates to the unchanged v1 model layer
 * (`Employee::get_notes()`, `Employee::add_note()`, `Employee::delete_note()`)
 * so the legacy `erp_hr_employee_note_new` hook and the
 * `erp_hrm_get_notes_query` filter keep firing. Only the request/response
 * envelope is the modern v2 contract. `erp/v1` stays untouched.
 *
 * Permissions mirror the proven v1 controller: `erp_manage_review` (a meta cap
 * granted to HR managers and an employee's reporting supervisor) gates
 * read/create; `erp_edit_employee` gates delete. The target employee id is
 * passed to the cap check so the meta-cap resolver can scope it.
 */

namespace WeDevs\ERP\HRM\API\V2;

use WeDevs\ERP\HRM\Employee;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

class EmployeeNotesControllerV2 extends RestControllerV2 {

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
			'/' . $this->rest_base . '/(?P<user_id>[\d]+)/notes',
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
			'/' . $this->rest_base . '/(?P<user_id>[\d]+)/notes/(?P<note_id>[\d]+)',
			[
				'args' => [
					'user_id' => [
						'description'       => __( 'Unique employee user ID.', 'erp' ),
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
						'validate_callback' => 'rest_validate_request_arg',
					],
					'note_id' => [
						'description'       => __( 'Unique note ID.', 'erp' ),
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
	 * Viewing notes requires the review-management meta cap on the target employee.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return bool
	 */
	public function permission_view( $request ): bool {
		return $this->permission_cap( 'erp_manage_review', (int) $request['user_id'] );
	}

	/**
	 * Adding a note requires the review-management meta cap on the target employee.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return bool
	 */
	public function permission_manage( $request ): bool {
		return $this->permission_cap( 'erp_manage_review', (int) $request['user_id'] );
	}

	/**
	 * Deleting a note requires the edit-employee cap on the target employee.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return bool
	 */
	public function permission_delete( $request ): bool {
		return $this->permission_cap( 'erp_edit_employee', (int) $request['user_id'] );
	}

	/**
	 * GET /erp/v2/employees/{user_id}/notes
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function get_items( $request ) {
		$user_id  = (int) $request['user_id'];
		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'Invalid employee id.', 'erp' ), [ 'status' => 404 ] );
		}

		$page     = max( 1, (int) ( $request['page'] ?? 1 ) );
		$per_page = max( 1, min( 100, (int) ( $request['per_page'] ?? 20 ) ) );
		$offset   = ( $page - 1 ) * $per_page;

		$notes = $employee->get_notes( $per_page, $offset );
		$total = (int) $employee->get_erp_user()->notes()->count();

		$items = [];
		foreach ( $notes as $note ) {
			$items[] = $this->prepare_item_for_response( $note, $request );
		}

		$response = rest_ensure_response( $items );
		return $this->paginate( $response, $request, $total );
	}

	/**
	 * POST /erp/v2/employees/{user_id}/notes
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function create_item( $request ) {
		$user_id  = (int) $request['user_id'];
		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'Invalid employee id.', 'erp' ), [ 'status' => 404 ] );
		}

		$comment = trim( (string) ( $request['comment'] ?? '' ) );

		if ( '' === $comment ) {
			return new \WP_Error( 'rest_note_empty', __( 'The note cannot be empty.', 'erp' ), [ 'status' => 400 ] );
		}

		// Delegate to the unchanged model. `true` returns the created note object
		// (it fires `erp_hr_employee_note_new` either way).
		$note = $employee->add_note( $comment, null, true );

		if ( ! $note ) {
			return new \WP_Error( 'rest_note_create_failed', __( 'The note could not be saved.', 'erp' ), [ 'status' => 500 ] );
		}

		$response = rest_ensure_response( $this->prepare_item_for_response( $note, $request ) );
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * DELETE /erp/v2/employees/{user_id}/notes/{note_id}
	 *
	 * Surfaces the legacy "note does not belong to this user" guard verbatim:
	 * `Employee::delete_note()` returns an `invalid-note-id` WP_Error which we
	 * map to HTTP 404.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		$user_id  = (int) $request['user_id'];
		$note_id  = (int) $request['note_id'];
		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'Invalid employee id.', 'erp' ), [ 'status' => 404 ] );
		}

		$result = $employee->delete_note( $note_id );

		if ( is_wp_error( $result ) ) {
			return new \WP_Error(
				$result->get_error_code() ?: 'rest_note_invalid_id',
				$result->get_error_message() ?: __( 'The note could not be deleted.', 'erp' ),
				[ 'status' => 404 ]
			);
		}

		return rest_ensure_response( [ 'deleted' => true, 'id' => $note_id ] );
	}

	/**
	 * Reshape an `Employee_Note` model into the v2 row.
	 *
	 * @param mixed           $note    An `Employee_Note` model instance.
	 * @param WP_REST_Request $request Request.
	 *
	 * @return array
	 */
	public function prepare_item_for_response( $note, $request ) {
		unset( $request );

		$comment_by = $this->cast_int_or_null( $note->comment_by ?? null );

		$author_name   = '';
		$author_avatar = '';
		if ( $comment_by ) {
			$user = get_userdata( $comment_by );
			if ( $user ) {
				$author_name = (string) $user->display_name;
			}
			$author_avatar = (string) get_avatar_url( $comment_by );
		}

		return [
			'id'                => (int) ( $note->id ?? 0 ),
			'user_id'           => $this->cast_int_or_null( $note->user_id ?? null ),
			'comment'           => $this->cast_string_or_null( $note->comment ?? '' ) ?? '',
			'comment_by'        => $comment_by,
			'author_name'       => $author_name,
			'author_avatar_url' => $author_avatar,
			'created_at'        => $this->cast_date_iso( isset( $note->created_at ) ? (string) $note->created_at : null ),
		];
	}

	/**
	 * Write params for create.
	 *
	 * @return array
	 */
	public function get_write_params(): array {
		return [
			'comment' => [
				'description'       => __( 'The note content.', 'erp' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_textarea_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
		];
	}

	/**
	 * JSON Schema for a single note.
	 *
	 * @return array
	 */
	public function get_item_schema(): array {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'employee_note',
			'type'       => 'object',
			'properties' => [
				'id'                => [ 'type' => 'integer' ],
				'user_id'           => [ 'type' => [ 'integer', 'null' ] ],
				'comment'           => [ 'type' => 'string' ],
				'comment_by'        => [ 'type' => [ 'integer', 'null' ] ],
				'author_name'       => [ 'type' => 'string' ],
				'author_avatar_url' => [ 'type' => 'string' ],
				'created_at'        => [ 'type' => [ 'string', 'null' ] ],
			],
		];
	}
}
