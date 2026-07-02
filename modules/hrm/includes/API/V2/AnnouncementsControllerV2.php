<?php
/**
 * WP-ERP HR — `erp/v2/announcements` REST controller.
 *
 * Endpoints:
 *   GET    /erp/v2/announcements                — paginated announcement list (status/search filters).
 *   POST   /erp/v2/announcements                — create + assign recipients.
 *   GET    /erp/v2/announcements/status-counts   — publish/draft/trash counts.
 *   GET    /erp/v2/announcements/form-options     — recipient pickers (departments/designations/employees).
 *   GET    /erp/v2/announcements/{id}            — single announcement (+ recipients).
 *   PUT    /erp/v2/announcements/{id}            — update + re-assign recipients.
 *   DELETE /erp/v2/announcements/{id}?force=     — trash (or permanently delete).
 *   POST   /erp/v2/announcements/{id}/restore    — restore from trash.
 *
 * Announcements are the `erp_hr_announcement` CPT. The legacy admin uses the WP
 * post editor + the `save_announcement_meta()` save_post hook, which calls
 * `erp_hr_assign_announcements_to_employees()` (recipient meta, the
 * `erp_hr_announcement` rows, the publish e-mail schedule + every hook). v2
 * mirrors that exactly: `wp_insert_post`/`wp_update_post` for the CPT then the
 * same assign call; list/trash/restore reuse `erp_hr_get_announcements()`,
 * `erp_hr_trash_announcements()`, `erp_hr_restore_announcements()`,
 * `erp_hr_get_announcements_status_counts()`. `erp/v1` + the AJAX/save layers
 * stay untouched.
 */

namespace WeDevs\ERP\HRM\API\V2;

use WeDevs\ERP\HRM\Models\Announcement;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

\defined( 'ABSPATH' ) || exit;

class AnnouncementsControllerV2 extends RestControllerV2 {

	/**
	 * @var string
	 */
	protected $rest_base = 'announcements';

	private const POST_TYPE = 'erp_hr_announcement';

	private const ASSIGN_TYPES = [ 'all_employee', 'by_department', 'by_designation', 'selected_employee' ];

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
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/status-counts',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_status_counts' ],
					'permission_callback' => [ $this, 'permission_view' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/form-options',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'form_options' ],
					'permission_callback' => [ $this, 'permission_manage' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/restore',
			[
				'args' => [ 'id' => $this->id_arg() ],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'restore_item' ],
					'permission_callback' => [ $this, 'permission_manage' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/mark-read',
			[
				'args' => [ 'id' => $this->id_arg() ],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'mark_read' ],
					'permission_callback' => [ $this, 'permission_view' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			[
				'args' => [ 'id' => $this->id_arg() ],
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
					'args'                => [
						'force' => [
							'description' => __( 'Permanently delete instead of trashing.', 'erp' ),
							'type'        => 'boolean',
							'default'     => false,
						],
					],
				],
			]
		);
	}

	/**
	 * Viewing requires the announcement view cap.
	 *
	 * @return bool
	 */
	public function permission_view(): bool {
		return $this->permission_cap( 'erp_view_announcement' );
	}

	/**
	 * Create / update / delete require the announcement manage cap — same gate as
	 * the legacy `save_announcement_meta()` + bulk handlers.
	 *
	 * @return bool
	 */
	public function permission_manage(): bool {
		return $this->permission_cap( 'erp_manage_announcement' );
	}

	/**
	 * GET /erp/v2/announcements
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_items( $request ): WP_REST_Response {
		$page     = max( 1, (int) ( $request['page'] ?? 1 ) );
		$per_page = max( 1, min( 100, (int) ( $request['per_page'] ?? 20 ) ) );
		$status   = $this->cast_enum( (string) ( $request['status'] ?? 'publish' ), [ 'publish', 'draft', 'trash', 'any' ] ) ?? 'publish';
		$search   = sanitize_text_field( (string) ( $request['search'] ?? '' ) );

		$date_query = $this->build_date_query( $request );

		$args = [
			'numberposts' => $per_page,
			'offset'      => ( $page - 1 ) * $per_page,
			'post_status' => $status,
			's'           => $search,
		];
		if ( ! empty( $date_query ) ) {
			$args['date_query'] = $date_query;
		}

		$posts = (array) erp_hr_get_announcements( $args );

		$count_args = [ 'post_status' => $status ];
		if ( '' !== $search ) {
			$count_args['s'] = $search;
		}
		if ( ! empty( $date_query ) ) {
			$count_args['date_query'] = $date_query;
		}
		$total = (int) erp_hr_get_announcements_count( $count_args );

		$items = [];
		foreach ( $posts as $post ) {
			$items[] = $this->prepare_item_for_response( $post, $request );
		}

		$response = rest_ensure_response( $items );
		return $this->paginate( $response, $request, $total );
	}

	/**
	 * GET /erp/v2/announcements/{id}
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function get_item( $request ) {
		$post = get_post( (int) $request['id'] );

		if ( ! $post || self::POST_TYPE !== $post->post_type ) {
			return new \WP_Error( 'rest_announcement_invalid_id', __( 'Invalid announcement id.', 'erp' ), [ 'status' => 404 ] );
		}

		$row            = $this->prepare_item_for_response( $post, $request );
		$row['content'] = (string) $post->post_content;
		$row['type']    = (string) get_post_meta( $post->ID, '_announcement_type', true );
		$row['recipients'] = [
			'employees'    => array_map( 'intval', (array) get_post_meta( $post->ID, '_announcement_selected_user', true ) ),
			'departments'  => array_map( 'intval', (array) get_post_meta( $post->ID, '_announcement_department', true ) ),
			'designations' => array_map( 'intval', (array) get_post_meta( $post->ID, '_announcement_designation', true ) ),
		];

		return rest_ensure_response( $row );
	}

	/**
	 * POST /erp/v2/announcements
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function create_item( $request ) {
		$title = sanitize_text_field( (string) ( $request['title'] ?? '' ) );

		if ( '' === $title ) {
			return new \WP_Error( 'rest_announcement_no_title', __( 'Title is required.', 'erp' ), [ 'status' => 400 ] );
		}

		$status = $this->cast_enum( (string) ( $request['status'] ?? 'publish' ), [ 'publish', 'draft' ] ) ?? 'publish';

		$post_id = wp_insert_post(
			[
				'post_type'    => self::POST_TYPE,
				'post_title'   => $title,
				'post_content' => (string) ( $request['content'] ?? '' ),
				'post_status'  => $status,
			],
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return new \WP_Error( 'rest_announcement_create_failed', $post_id->get_error_message(), [ 'status' => 400 ] );
		}

		$this->assign_recipients( (int) $post_id, $request );

		$response = rest_ensure_response( $this->get_item_payload( (int) $post_id ) );
		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, (int) $post_id ) ) );

		return $response;
	}

	/**
	 * PUT /erp/v2/announcements/{id}
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function update_item( $request ) {
		$id   = (int) $request['id'];
		$post = get_post( $id );

		if ( ! $post || self::POST_TYPE !== $post->post_type ) {
			return new \WP_Error( 'rest_announcement_invalid_id', __( 'Invalid announcement id.', 'erp' ), [ 'status' => 404 ] );
		}

		$data = [ 'ID' => $id ];

		if ( isset( $request['title'] ) ) {
			$title = sanitize_text_field( (string) $request['title'] );
			if ( '' === $title ) {
				return new \WP_Error( 'rest_announcement_no_title', __( 'Title is required.', 'erp' ), [ 'status' => 400 ] );
			}
			$data['post_title'] = $title;
		}
		if ( isset( $request['content'] ) ) {
			$data['post_content'] = (string) $request['content'];
		}
		if ( isset( $request['status'] ) ) {
			$data['post_status'] = $this->cast_enum( (string) $request['status'], [ 'publish', 'draft' ] ) ?? $post->post_status;
		}

		$result = wp_update_post( $data, true );

		if ( is_wp_error( $result ) ) {
			return new \WP_Error( 'rest_announcement_update_failed', $result->get_error_message(), [ 'status' => 400 ] );
		}

		if ( null !== $request['assign_type'] ) {
			$this->assign_recipients( $id, $request );
		}

		return rest_ensure_response( $this->get_item_payload( $id ) );
	}

	/**
	 * DELETE /erp/v2/announcements/{id}
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		$id   = (int) $request['id'];
		$post = get_post( $id );

		if ( ! $post || self::POST_TYPE !== $post->post_type ) {
			return new \WP_Error( 'rest_announcement_invalid_id', __( 'Invalid announcement id.', 'erp' ), [ 'status' => 404 ] );
		}

		$force = $this->cast_bool( $request['force'] ?? false );

		$fail = erp_hr_trash_announcements( [ $id ], $force );

		if ( $fail > 0 ) {
			return new \WP_Error( 'rest_announcement_delete_failed', __( 'The announcement could not be deleted.', 'erp' ), [ 'status' => 500 ] );
		}

		return rest_ensure_response( [ 'deleted' => true, 'id' => $id, 'force' => $force ] );
	}

	/**
	 * POST /erp/v2/announcements/{id}/restore
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function restore_item( $request ) {
		$id = (int) $request['id'];

		$fail = erp_hr_restore_announcements( [ $id ] );

		if ( $fail > 0 ) {
			return new \WP_Error( 'rest_announcement_restore_failed', __( 'The announcement could not be restored.', 'erp' ), [ 'status' => 500 ] );
		}

		return rest_ensure_response( $this->get_item_payload( $id ) );
	}

	/**
	 * POST /erp/v2/announcements/{id}/mark-read
	 *
	 * Marks the announcement read for the current user — mirrors
	 * `AjaxHandler::mark_read_announcement()` (updates the `erp_hr_announcement`
	 * recipient row for this user to `read`).
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function mark_read( $request ): WP_REST_Response {
		$post_id = (int) $request['id'];
		$user_id = get_current_user_id();

		Announcement::where( 'post_id', $post_id )
			->where( 'user_id', $user_id )
			->update( [ 'status' => 'read' ] );

		return rest_ensure_response( [ 'read' => true, 'id' => $post_id ] );
	}

	/**
	 * GET /erp/v2/announcements/status-counts
	 *
	 * @return WP_REST_Response
	 */
	public function get_status_counts(): WP_REST_Response {
		$counts = (array) erp_hr_get_announcements_status_counts();

		return rest_ensure_response(
			[
				'publish' => (int) ( $counts['publish'] ?? 0 ),
				'draft'   => (int) ( $counts['draft'] ?? 0 ),
				'trash'   => (int) ( $counts['trash'] ?? 0 ),
			]
		);
	}

	/**
	 * GET /erp/v2/announcements/form-options
	 *
	 * Recipient pickers for the create/edit form.
	 *
	 * @return WP_REST_Response
	 */
	public function form_options(): WP_REST_Response {
		$departments = [];
		foreach ( (array) erp_hr_get_departments_dropdown_raw() as $dept_id => $title ) {
			if ( '' === (string) $dept_id ) {
				continue;
			}
			$departments[] = [ 'id' => (int) $dept_id, 'title' => (string) $title ];
		}

		$designations = [];
		foreach ( (array) erp_hr_get_designation_dropdown_raw() as $desig_id => $title ) {
			if ( '' === (string) $desig_id ) {
				continue;
			}
			$designations[] = [ 'id' => (int) $desig_id, 'title' => (string) $title ];
		}

		$employees      = [];
		$current_user_id = get_current_user_id();
		foreach ( (array) erp_hr_get_employees( [ 'no_object' => true, 'number' => '-1' ] ) as $emp ) {
			// Mirror the legacy picker (Announcement.php:241-242): the author can't be
			// a recipient of their own announcement, so skip the current user.
			if ( (int) $emp->user_id === (int) $current_user_id ) {
				continue;
			}
			$employees[] = [ 'id' => (int) $emp->user_id, 'name' => (string) $emp->display_name ];
		}

		return rest_ensure_response(
			[
				'assign_types' => [
					[ 'value' => 'all_employee', 'label' => __( 'All Employees', 'erp' ) ],
					[ 'value' => 'by_department', 'label' => __( 'By Department', 'erp' ) ],
					[ 'value' => 'by_designation', 'label' => __( 'By Designation', 'erp' ) ],
					[ 'value' => 'selected_employee', 'label' => __( 'Selected Employees', 'erp' ) ],
				],
				'departments'  => $departments,
				'designations' => $designations,
				'employees'    => $employees,
			]
		);
	}

	/**
	 * Resolve the recipient `selected` set from the request and call the shared
	 * assign fn — mirrors `save_announcement_meta()`.
	 *
	 * @param int             $post_id Announcement post ID.
	 * @param WP_REST_Request $request Request.
	 *
	 * @return void
	 */
	private function assign_recipients( int $post_id, $request ): void {
		$type = $this->cast_enum( (string) ( $request['assign_type'] ?? '' ), self::ASSIGN_TYPES ) ?? 'all_employee';

		$employees    = array_map( 'absint', (array) ( $request['employees'] ?? [] ) );
		$departments  = array_map( 'absint', (array) ( $request['departments'] ?? [] ) );
		$designations = array_map( 'absint', (array) ( $request['designations'] ?? [] ) );

		if ( 'by_department' === $type ) {
			$selected = $departments;
		} elseif ( 'by_designation' === $type ) {
			$selected = $designations;
		} else {
			$selected = $employees;
		}

		erp_hr_assign_announcements_to_employees( $post_id, $type, $selected );
	}

	/**
	 * Build the single-item payload (used by create/update/restore responses).
	 *
	 * @param int $post_id Announcement post ID.
	 *
	 * @return array
	 */
	private function get_item_payload( int $post_id ): array {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return [ 'id' => $post_id ];
		}

		$row                 = $this->prepare_item_for_response( $post, null );
		$row['content']      = (string) $post->post_content; // raw — the editor binds to this.
		$row['html_content'] = (string) wp_kses_post( wpautop( (string) $post->post_content ) ); // display-ready (view modal); KSES'd next to the React dangerouslySetInnerHTML sink.
		$row['type']         = (string) get_post_meta( $post_id, '_announcement_type', true );

		return $row;
	}

	/**
	 * Reshape a CPT post into the v2 list row.
	 *
	 * @param mixed           $post    WP_Post.
	 * @param WP_REST_Request $request Request (unused).
	 *
	 * @return array
	 */
	public function prepare_item_for_response( $post, $request ) {
		unset( $request );

		$recipient_count = (int) Announcement::where( 'post_id', (int) $post->ID )->count();

		// Small avatar-stack preview (up to 3 recipients) for the audience column.
		$recipients_preview = [];
		$preview            = Announcement::where( 'post_id', (int) $post->ID )->take( 3 )->get( [ 'user_id' ] );
		foreach ( $preview as $row ) {
			$employee             = new \WeDevs\ERP\HRM\Employee( (int) $row->user_id );
			$recipients_preview[] = [
				'name'   => (string) $employee->get_full_name(),
				'avatar' => $employee->get_avatar_url( 40 ) ?: null,
			];
		}

		return [
			'id'                  => (int) $post->ID,
			'title'               => (string) get_the_title( $post ),
			'excerpt'             => wp_trim_words( wp_strip_all_tags( (string) $post->post_content ), 30 ),
			'status'              => (string) $post->post_status,
			'date'                => $this->cast_date_iso( $post->post_date ),
			'author'              => (string) get_the_author_meta( 'display_name', (int) $post->post_author ),
			'recipient_count'     => $recipient_count,
			'recipients_preview'  => $recipients_preview,
		];
	}

	/**
	 * Shared `id` route arg.
	 *
	 * @return array
	 */
	private function id_arg(): array {
		return [
			'description'       => __( 'Unique announcement ID.', 'erp' ),
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		];
	}

	/**
	 * Write params for create / update.
	 *
	 * @return array
	 */
	public function get_write_params(): array {
		return [
			'title'        => [ 'description' => __( 'Announcement title.', 'erp' ), 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
			'content'      => [ 'description' => __( 'Announcement body (HTML).', 'erp' ), 'type' => 'string' ],
			'status'       => [ 'description' => __( 'publish or draft.', 'erp' ), 'type' => 'string', 'enum' => [ 'publish', 'draft' ], 'default' => 'publish' ],
			'assign_type'  => [ 'description' => __( 'Recipient strategy.', 'erp' ), 'type' => 'string', 'enum' => self::ASSIGN_TYPES ],
			'employees'    => [ 'description' => __( 'Employee user IDs (selected_employee).', 'erp' ), 'type' => 'array', 'items' => [ 'type' => 'integer' ] ],
			'departments'  => [ 'description' => __( 'Department IDs (by_department).', 'erp' ), 'type' => 'array', 'items' => [ 'type' => 'integer' ] ],
			'designations' => [ 'description' => __( 'Designation IDs (by_designation).', 'erp' ), 'type' => 'array', 'items' => [ 'type' => 'integer' ] ],
		];
	}

	/**
	 * Collection params: pagination + status + search.
	 *
	 * @return array
	 */
	public function get_collection_params(): array {
		$params = parent::get_collection_params();

		$params['status'] = [
			'description'       => __( 'Post status filter.', 'erp' ),
			'type'              => 'string',
			'default'           => 'publish',
			'enum'              => [ 'publish', 'draft', 'trash', 'any' ],
			'sanitize_callback' => 'sanitize_key',
		];

		$params['start_date'] = [
			'description'       => __( 'Only announcements published on or after this date (Y-m-d).', 'erp' ),
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		];

		$params['end_date'] = [
			'description'       => __( 'Only announcements published on or before this date (Y-m-d).', 'erp' ),
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		];

		return $params;
	}

	/**
	 * Build a WP `date_query` from the optional start_date / end_date params.
	 * Inclusive on both ends (mirrors the legacy list's date range filter).
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return array Empty when no dates supplied.
	 */
	private function build_date_query( $request ): array {
		$start = sanitize_text_field( (string) ( $request['start_date'] ?? '' ) );
		$end   = sanitize_text_field( (string) ( $request['end_date'] ?? '' ) );

		if ( '' === $start && '' === $end ) {
			return [];
		}

		$range = [ 'inclusive' => true ];
		if ( '' !== $start ) {
			$range['after'] = $start;
		}
		if ( '' !== $end ) {
			$range['before'] = $end;
		}

		return [ $range ];
	}
}
