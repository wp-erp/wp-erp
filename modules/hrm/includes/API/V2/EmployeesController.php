<?php
/**
 * WP-ERP HR — `erp/v2/employees` REST controller.
 *
 * Endpoints:
 *   GET /erp/v2/employees — DataView-shaped paginated employee list.
 *
 * Delegates to the existing v1 model layer (`erp_hr_get_employees`) so that
 * every existing hook (`erp_hr_get_employees_result`, cache key, gender/marital
 * meta joins) keeps firing. The shape is reformatted into the v2 typing
 * contract: int IDs, ISO-8601 dates, real booleans, null for absent optional
 * fields, embedded department/designation/location objects.
 *
 * Pro extension contract:
 *   apply_filters( 'erp_hr_v2_employees_query_args',   $args, $request )
 *   apply_filters( 'erp_hr_v2_employees_response_item', $item, $employee )
 *
 * Mutations (create / update / delete / restore / terminate / reactivate) all
 * delegate to the unchanged v1 model layer (`Employee::create_employee()`,
 * `erp_employee_delete()`, `Employee::terminate()`, …) so every legacy hook,
 * audit-log entry and cache purge keeps firing — only the request/response
 * envelope is the modern v2 contract. `erp/v1` stays untouched.
 */

namespace WeDevs\ERP\HRM\API\V2;

use WeDevs\ERP\HRM\Employee;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

class EmployeesController extends RestController {

	/**
	 * @var string
	 */
	protected $rest_base = 'employees';

	/**
	 * Per-request memo of department / designation / location names by id.
	 *
	 * @var array
	 */
	private $lookup_names = [];

	/**
	 * Allowed orderby keys (whitelisted against v1 model semantics).
	 */
	private const ORDERBY_MAP = [
		'full_name' => 'employee_name',
		'email'     => 'user_email',
		'hire_date' => 'hiring_date',
		'status'    => 'status',
	];

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
					'permission_callback' => [ $this, 'permission_list_employees' ],
					'args'                => $this->get_collection_params(),
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_item' ],
					'permission_callback' => [ $this, 'permission_create_employee' ],
					'args'                => $this->get_create_params(),
				],
				'schema' => [ $this, 'get_item_schema' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/import',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'import_items' ],
					'permission_callback' => [ $this, 'permission_create_employee' ],
					'args'                => [
						'employees' => [
							'description' => __( 'Array of employee rows to create.', 'erp' ),
							'type'        => 'array',
							'required'    => true,
						],
					],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/counts',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_counts' ],
					'permission_callback' => [ $this, 'permission_list_employees' ],
					'args'                => $this->get_counts_params(),
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<user_id>[\d]+)',
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
					'callback'            => [ $this, 'get_item' ],
					'permission_callback' => [ $this, 'permission_view_employee' ],
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_item' ],
					'permission_callback' => [ $this, 'permission_edit_employee' ],
					'args'                => $this->get_create_params(),
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_item' ],
					'permission_callback' => [ $this, 'permission_delete_employee' ],
					'args'                => [
						'force' => [
							'description' => __( 'Permanently delete instead of moving to trash.', 'erp' ),
							'type'        => 'boolean',
							'default'     => false,
						],
					],
				],
				'schema' => [ $this, 'get_item_schema' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<user_id>[\d]+)/restore',
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
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'restore_item' ],
					'permission_callback' => [ $this, 'permission_delete_employee' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<user_id>[\d]+)/terminate',
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
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'terminate_item' ],
					'permission_callback' => [ $this, 'permission_terminate_employee' ],
					'args'                => $this->get_terminate_params(),
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<user_id>[\d]+)/reactivate',
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
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'reactivate_item' ],
					'permission_callback' => [ $this, 'permission_reactivate_employee' ],
				],
			]
		);

		// Profile photo upload / remove. Mirrors the legacy `personal[photo_id]`
		// write (a media-frame attachment id stored in user meta `photo_id`).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<user_id>[\d]+)/avatar',
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
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'upload_avatar' ],
					'permission_callback' => [ $this, 'permission_edit_employee' ],
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_avatar' ],
					'permission_callback' => [ $this, 'permission_edit_employee' ],
				],
			]
		);
	}

	/**
	 * POST /erp/v2/employees/{user_id}/avatar
	 *
	 * Accepts a multipart `photo` file, stores it as a media attachment, and
	 * points the employee's `photo_id` user meta at it — the same meta the legacy
	 * form wrote. Returns the new `photo_id` + resolved `avatar_url`.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function upload_avatar( $request ) {
		$user_id  = (int) $request['user_id'];
		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'Invalid employee id.', 'erp' ), [ 'status' => 404 ] );
		}

		$files = $request->get_file_params();
		if ( empty( $files['photo'] ) ) {
			return new \WP_Error( 'rest_no_photo', __( 'No photo provided.', 'erp' ), [ 'status' => 400 ] );
		}

		// Legacy 2 MB cap, enforced here rather than relying on the client to
		// send the `X-ERP-Upload` header that `erp_enforce_react_upload_size()` keys on.
		if ( (int) ( $files['photo']['size'] ?? 0 ) > 2 * 1024 * 1024 ) {
			return new \WP_Error( 'rest_upload_too_large', __( 'File size cannot be greater than 2MB.', 'erp' ), [ 'status' => 400 ] );
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		// media_handle_upload reads from the global $_FILES entry by key.
		$attachment_id = media_handle_upload( 'photo', 0 );

		if ( is_wp_error( $attachment_id ) ) {
			return new \WP_Error( 'rest_avatar_upload_failed', $attachment_id->get_error_message(), [ 'status' => 400 ] );
		}

		if ( ! wp_attachment_is_image( (int) $attachment_id ) ) {
			wp_delete_attachment( (int) $attachment_id, true );

			return new \WP_Error( 'rest_invalid_photo', __( 'The profile photo must be an image attachment.', 'erp' ), [ 'status' => 400 ] );
		}

		// Mark the upload as this employee's photo: delete_avatar() only removes
		// attachments it can tie back to the employee.
		update_post_meta( (int) $attachment_id, '_erp_hr_employee_photo', $user_id );
		update_user_meta( $user_id, 'photo_id', (int) $attachment_id );
		clean_user_cache( $user_id );

		$fresh = new Employee( $user_id );

		return rest_ensure_response(
			[
				'photo_id'   => (int) $attachment_id,
				'avatar_url' => $fresh->get_avatar_url( 80 ) ?: null,
			]
		);
	}

	/**
	 * DELETE /erp/v2/employees/{user_id}/avatar
	 *
	 * Removes the photo attachment + clears the `photo_id` meta; `avatar_url`
	 * then falls back to the Gravatar/mystery-person default.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function delete_avatar( $request ) {
		$user_id  = (int) $request['user_id'];
		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'Invalid employee id.', 'erp' ), [ 'status' => 404 ] );
		}

		$photo_id = (int) get_user_meta( $user_id, 'photo_id', true );
		if ( $photo_id ) {
			// `photo_id` is plain user meta, so it can point at any attachment.
			// Unlink it always, but only delete a file that is this employee's photo.
			if ( null === $this->photo_error( $photo_id, $user_id ) ) {
				wp_delete_attachment( $photo_id, true );
			}
			delete_user_meta( $user_id, 'photo_id' );
			clean_user_cache( $user_id );
		}

		$fresh = new Employee( $user_id );

		return rest_ensure_response(
			[
				'photo_id'   => null,
				'avatar_url' => $fresh->get_avatar_url( 80 ) ?: null,
			]
		);
	}

	/**
	 * Whether an attachment may be used (and later removed) as an employee's
	 * profile photo: it must be an image, and either uploaded for that employee
	 * through the avatar endpoint, authored by that employee, uploaded by the
	 * current user (the create form uploads the photo before the employee
	 * exists), or deletable by the current user anyway.
	 *
	 * @param int $photo_id Attachment ID.
	 * @param int $user_id  Employee user ID (0 when the employee does not exist yet).
	 *
	 * @return \WP_Error|null WP_Error to reject, null when allowed.
	 */
	private function photo_error( int $photo_id, int $user_id ) {
		if ( 'attachment' !== get_post_type( $photo_id ) || ! wp_attachment_is_image( $photo_id ) ) {
			return new \WP_Error( 'rest_invalid_photo', __( 'The profile photo must be an image attachment.', 'erp' ), [ 'status' => 400 ] );
		}

		$owned = $user_id && (
			(int) get_post_meta( $photo_id, '_erp_hr_employee_photo', true ) === $user_id
			|| (int) get_post_field( 'post_author', $photo_id ) === $user_id
		);

		$own_upload = (int) get_post_field( 'post_author', $photo_id ) === get_current_user_id();

		if ( ! $owned && ! $own_upload && ! current_user_can( 'delete_post', $photo_id ) ) {
			return new \WP_Error( 'rest_cannot_use_photo', __( 'You are not allowed to use this attachment as a profile photo.', 'erp' ), [ 'status' => 403 ] );
		}

		return null;
	}

	/**
	 * Whether the current user may change the login email of the target account.
	 *
	 * The login email is an account credential: whoever controls it can reset the
	 * password. So an HR manager may correct it only for an account that holds no
	 * role the manager could not grant themselves, and never for an administrator.
	 *
	 * @param int $user_id Target user ID.
	 *
	 * @return bool
	 */
	private function can_change_login_email( int $user_id ): bool {
		$target = get_userdata( $user_id );

		if ( ! $target ) {
			return false;
		}

		if ( in_array( 'administrator', (array) $target->roles, true ) && ! current_user_can( 'administrator' ) ) {
			return false;
		}

		// An employee may not change their own login email here: it would let
		// whoever holds the session take over password resets with no
		// confirmation. WordPress grants `edit_user` on oneself, so this must
		// come before that check. HR managers keep the ability.
		if ( get_current_user_id() === $user_id && ! current_user_can( 'erp_edit_employee' ) ) {
			return false;
		}

		if ( current_user_can( 'edit_user', $user_id ) ) {
			return true;
		}

		if ( ! current_user_can( 'erp_edit_employee' ) ) {
			return false;
		}

		// Compare capabilities directly rather than through
		// `erp_can_current_user_assign_role()`: that helper also counts the
		// deprecated `level_N` caps (subscriber carries `level_0`), which an HR
		// manager role never holds, so any second role blocked the correction.
		if ( current_user_can( 'promote_users' ) ) {
			return true;
		}

		$user_caps = array_filter( (array) wp_get_current_user()->allcaps );

		/** This filter is documented in includes/functions.php */
		$meta_caps = apply_filters( 'erp_role_comparison_ignored_caps', [
			'edit_post',
			'read_post',
			'delete_post',
			'edit_page',
			'read_page',
			'delete_page',
			'edit_comment',
			'edit_user',
			'delete_user',
			'remove_user',
			'add_user_to_blog',
		] );

		foreach ( (array) $target->roles as $role ) {
			$role_object = get_role( $role );

			if ( ! $role_object ) {
				return false;
			}

			foreach ( (array) $role_object->capabilities as $cap => $granted ) {
				if ( ! $granted
					|| in_array( $cap, $meta_caps, true )
					|| preg_match( '/^level_\d+$/', (string) $cap ) ) {
					continue;
				}

				if ( empty( $user_caps[ $cap ] ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * GET /erp/v2/employees/counts
	 *
	 * Returns per-status counts so the Employees table can render tab badges
	 * (`All (250) | Active (200) | …`) in a single round-trip. Honors the same
	 * search / department / designation / location filters as the list endpoint
	 * so the counts always match the currently-filtered cohort.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_counts( $request ): WP_REST_Response {
		$base_args = [
			'number'      => -1,
			'offset'      => 0,
			'department'  => $this->cast_int_or_null( $request['department_id']  ?? null ) ?? -1,
			'designation' => $this->cast_int_or_null( $request['designation_id'] ?? null ) ?? -1,
			'location'    => $this->cast_int_or_null( $request['location_id']    ?? null ) ?? -1,
			'type'        => '' !== (string) ( $request['employee_type'] ?? '' ) ? sanitize_text_field( (string) $request['employee_type'] ) : -1,
			's'           => sanitize_text_field( (string) ( $request['search'] ?? '' ) ),
			'count'       => true,
		];

		$statuses = $this->allowed_employee_status_keys();
		$is_hr    = current_user_can( 'erp_view_employee' );
		$counts   = [];
		$total    = 0;
		foreach ( $statuses as $status ) {
			$args               = $base_args;
			$args['status']     = $status;
			// Same rule as the list: a non-HR viewer only learns about active staff.
			$count              = ( $is_hr || 'active' === $status ) ? (int) erp_hr_get_employees( $args ) : 0;
			$counts[ $status ]  = $count;
			$total             += $count;
		}

		$trash_args           = $base_args;
		$trash_args['status'] = 'trash';
		$counts['trash']      = $is_hr ? (int) erp_hr_get_employees( $trash_args ) : 0;

		$payload = [
			'all'       => $total,
			'by_status' => $counts,
		];

		/**
		 * Filter the v2 employee counts payload.
		 *
		 * Pro plugins extend with additional buckets (e.g., pro-only statuses).
		 *
		 * @since 1.13.5
		 *
		 * @param array           $payload Counts payload.
		 * @param WP_REST_Request $request Request.
		 */
		$payload = (array) apply_filters( 'erp_hr_v2_employees_counts', $payload, $request );

		return rest_ensure_response( $payload );
	}

	/**
	 * Query params for `GET /employees/counts`. Mirrors the list filters that
	 * scope the count (search + department + designation + location).
	 *
	 * @return array
	 */
	public function get_counts_params(): array {
		return [
			'search' => [
				'description'       => __( 'Search across name and email.', 'erp' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'department_id'  => [
				'description'       => __( 'Filter counts by department ID.', 'erp' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'designation_id' => [
				'description'       => __( 'Filter counts by designation ID.', 'erp' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'location_id'    => [
				'description'       => __( 'Filter counts by location ID.', 'erp' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'employee_type'  => [
				'description'       => __( 'Filter by employment type slug.', 'erp' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
		];
	}

	/**
	 * Permission callback for the list endpoint.
	 *
	 * @return bool
	 */
	public function permission_list_employees(): bool {
		return $this->permission_cap( 'erp_list_employee' );
	}

	/**
	 * Permission callback for the create endpoint.
	 *
	 * @return bool
	 */
	public function permission_create_employee(): bool {
		return $this->permission_cap( 'erp_create_employee' );
	}

	/**
	 * Permission callback for the single-read endpoint. Mirrors legacy
	 * `single.php`: a manager (`erp_view_employee`), the employee viewing their
	 * own record, OR any HR-listed user (`erp_list_employee`) viewing a peer —
	 * the latter receives a basic-info-only record (sensitive fields stripped in
	 * `get_item()`), matching the legacy peer view.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return bool
	 */
	public function permission_view_employee( $request ): bool {
		return $this->permission_cap( 'erp_view_employee' )
			|| $this->permission_cap( 'erp_list_employee' )
			|| (int) $request['user_id'] === get_current_user_id();
	}

	/**
	 * Permission callback for the update + avatar endpoints.
	 *
	 * Meta-mapped against the target user so an employee may edit their OWN
	 * profile (and a department lead their reports), matching the legacy AJAX
	 * gate `current_user_can( 'erp_edit_employee', $user_id )`
	 * (AjaxHandler::ajax_update_employee). A bare cap check would 403 self-edit.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return bool
	 */
	public function permission_edit_employee( $request ): bool {
		return $this->permission_cap( 'erp_edit_employee', (int) $request['user_id'] );
	}

	/**
	 * Permission callback for the reactivate endpoint. Un-terminating an
	 * employee is a manager action only — never self-service — so this keeps the
	 * bare `erp_edit_employee` (manager) cap rather than the meta-mapped variant.
	 *
	 * @return bool
	 */
	public function permission_reactivate_employee(): bool {
		return $this->permission_cap( 'erp_edit_employee' );
	}

	/**
	 * Permission callback for the delete + restore endpoints. Mirrors the v1
	 * controller's `erp_delete_employee` gate.
	 *
	 * @return bool
	 */
	public function permission_delete_employee(): bool {
		return $this->permission_cap( 'erp_delete_employee' );
	}

	/**
	 * Permission callback for the terminate endpoint. Mirrors the v1
	 * controller's `erp_can_terminate` gate.
	 *
	 * @return bool
	 */
	public function permission_terminate_employee(): bool {
		return $this->permission_cap( 'erp_can_terminate' );
	}

	/**
	 * POST /erp/v2/employees
	 *
	 * Creates an employee through the exact same model path the legacy Vue admin
	 * used (`Employee::create_employee()`), so every hook
	 * (`erp_hr_employee_args`, `erp_hr_employee_after_create`, audit logging,
	 * welcome email) keeps firing. Only the request/response envelope is the
	 * modern v2 contract — the write itself is unchanged from v1.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function create_item( $request ) {
		$invalid = $this->validate_extension_fields( $request, 'create' );
		if ( is_wp_error( $invalid ) ) {
			return $invalid;
		}

		$item_data = $this->prepare_item_for_database( $request );

		$invalid = $this->validate_employee_rules( $item_data );
		if ( $invalid ) {
			return $invalid;
		}

		if ( ! empty( $item_data['personal']['photo_id'] ) ) {
			$photo_error = $this->photo_error( (int) $item_data['personal']['photo_id'], 0 );
			if ( $photo_error ) {
				return $photo_error;
			}
		}

		$employee = new Employee( null );
		$created  = $employee->create_employee( $item_data );

		if ( is_wp_error( $created ) ) {
			return $this->as_bad_request( $created );
		}

		$new_employee = new Employee( $created->user_id );

		/**
		 * Fires after a React-created employee is saved. Pro consumers (e.g. the
		 * Custom Field Builder) persist their `additional` meta from the request.
		 *
		 * @param int             $user_id    New employee user id.
		 * @param array           $additional Custom field values keyed by meta key.
		 * @param WP_REST_Request $request    The create request.
		 */
		do_action( 'erp_hr_rest_employee_saved', $new_employee->get_user_id(), (array) $request->get_param( 'additional' ), $request );

		// Optional welcome email — mirrors the v1 create flow.
		if ( ! empty( $request['user_notification'] ) ) {
			$emailer    = wperp()->emailer->get_email( 'NewEmployeeWelcome' );
			$send_login = ! empty( $request['login_info'] );

			if ( is_a( $emailer, '\WeDevs\ERP\Email' ) ) {
				$emailer->trigger( $new_employee->get_user_id(), $send_login );
			}
		}

		$item     = $this->prepare_item_for_response( $new_employee, $request );
		$response = rest_ensure_response( $item );
		$response->set_status( 201 );
		$response->header(
			'Location',
			rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $new_employee->get_user_id() ) )
		);

		return $response;
	}

	/**
	 * POST /erp/v2/employees/import
	 *
	 * Bulk-create employees from parsed CSV rows. Mirrors the legacy bulk path
	 * (`EmployeesController::create_employees` → `Employee::create_employee()`),
	 * reusing this controller's `prepare_item_for_database()` so every row goes
	 * through the same sanitisation + hooks as a single create.
	 *
	 * Unlike the v1 handler (which aborted on the first error), this processes
	 * every row and returns a per-row summary so the import UI can report which
	 * rows failed and why — a no-op on success counts, no partial rollback.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function import_items( $request ) {
		$rows = $request->get_param( 'employees' );

		if ( ! is_array( $rows ) || empty( $rows ) ) {
			return new \WP_Error(
				'rest_import_empty',
				__( 'No employee rows were supplied.', 'erp' ),
				[ 'status' => 400 ]
			);
		}

		/**
		 * Filters the maximum number of employee rows one import request may carry.
		 *
		 * @since 1.18.0
		 *
		 * @param int $max_rows Maximum rows per request. Default 500.
		 */
		$max_rows = (int) apply_filters( 'erp_hr_employee_import_max_rows', 500 );

		if ( $max_rows > 0 && count( $rows ) > $max_rows ) {
			return new \WP_Error(
				'rest_import_too_many_rows',
				sprintf(
					/* translators: %d: maximum number of rows */
					__( 'Too many rows. Import at most %d employees at a time.', 'erp' ),
					$max_rows
				),
				[ 'status' => 400 ]
			);
		}

		$created = 0;
		$failed  = [];

		foreach ( array_values( $rows ) as $index => $row ) {
			$line = $index + 1;

			if ( ! is_array( $row ) ) {
				$failed[] = [
					'row'     => $line,
					'email'   => '',
					'message' => __( 'Malformed row.', 'erp' ),
				];
				continue;
			}

			// Minimal required-field guard before hitting the model.
			if ( empty( $row['first_name'] ) || empty( $row['last_name'] ) || empty( $row['email'] ) ) {
				$failed[] = [
					'row'     => $line,
					'email'   => isset( $row['email'] ) ? sanitize_email( (string) $row['email'] ) : '',
					'message' => __( 'first_name, last_name and email are required.', 'erp' ),
				];
				continue;
			}

			$data     = $this->prepare_item_for_database( $row );
			if ( ! empty( $data['personal']['photo_id'] ) && $this->photo_error( (int) $data['personal']['photo_id'], 0 ) ) {
				unset( $data['personal']['photo_id'] );
			}
			$employee = new Employee( null );
			$result   = $employee->create_employee( $data );

			if ( is_wp_error( $result ) ) {
				$failed[] = [
					'row'     => $line,
					'email'   => sanitize_email( (string) $row['email'] ),
					'message' => $result->get_error_message(),
				];
				continue;
			}

			$created++;
		}

		return rest_ensure_response(
			[
				'total'   => count( $rows ),
				'created' => $created,
				'failed'  => $failed,
			]
		);
	}

	/**
	 * GET /erp/v2/employees/{user_id}
	 *
	 * Returns the flat, edit-shaped record the React form binds to (field keys
	 * match the create payload exactly), so the Edit page can prefill without
	 * any client-side reshaping.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function get_item( $request ) {
		$user_id  = (int) $request['user_id'];
		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'Invalid employee id.', 'erp' ), [ 'status' => 404 ] );
		}

		// `is_employee()` answers yes for every administrator, even one with no
		// HR record. Only a manager may read such an account here; anyone else
		// would otherwise get the admin's login email.
		$has_hr_record = (bool) ( $employee->get_erp_user() && $employee->get_erp_user()->exists );
		if ( ! $has_hr_record
			&& get_current_user_id() !== $user_id
			&& ! current_user_can( 'erp_view_employee' ) ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'Invalid employee id.', 'erp' ), [ 'status' => 404 ] );
		}

		$data = $this->get_edit_data( $employee );

		// Resolved display names for the read-only detail view (the edit form
		// ignores these — it only reads the *_id / scalar keys).
		$reporting               = $this->embed_reporting_to( $this->cast_int_or_null( $employee->get_reporting_to() ) );
		$data['department_name']   = (string) $employee->get_department( 'view' );
		$data['designation_name']  = (string) $employee->get_designation( 'view' );
		$data['location_name']     = (string) $employee->get_location( 'view' );
		$data['reporting_to_name'] = $reporting['full_name'] ?? '';

		// Field-level privacy (mirrors legacy tab-general.php:26 / tab-job.php:126,
		// and the v4 client guards): pay + personal + address + bio are visible
		// only to the employee themselves or an HR manager (erp_edit_employee).
		// Defense-in-depth — the route 403 already blocks peers, this stays correct
		// if that gate is ever loosened (e.g. a peer-directory mode).
		$can_see_private = get_current_user_id() === $user_id
			|| current_user_can( 'erp_edit_employee', $user_id );
		if ( ! $can_see_private ) {
			$private_fields = [
				'pay_rate', 'pay_type',
				'date_of_birth', 'gender', 'marital_status', 'blood_group', 'nationality',
				'driving_license', 'hobbies', 'father_name', 'mother_name', 'spouse_name',
				'street_1', 'street_2', 'city', 'state', 'country', 'postal_code',
				'description',
				// Contact details: the legacy peer view exposes name / employee id /
				// work e-mail only, so the phone numbers and secondary addresses
				// must not travel to a peer either.
				'work_phone', 'phone', 'mobile', 'other_email', 'user_url',
			];
			foreach ( $private_fields as $field ) {
				if ( array_key_exists( $field, $data ) ) {
					$data[ $field ] = '';
				}
			}

			// Termination reason / rehire eligibility is HR-only, and its shape is
			// an array|null rather than a string.
			$data['termination'] = null;
		}

		return rest_ensure_response( $data );
	}

	/**
	 * PUT /erp/v2/employees/{user_id}
	 *
	 * Updates an employee through the unchanged `Employee::update_employee()`
	 * model (same path the legacy admin used), so every hook keeps firing. Only
	 * the envelope is v2.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function update_item( $request ) {
		$user_id  = (int) $request['user_id'];
		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'Invalid employee id.', 'erp' ), [ 'status' => 404 ] );
		}

		$invalid = $this->validate_extension_fields( $request, 'edit' );
		if ( is_wp_error( $invalid ) ) {
			return $invalid;
		}

		$data = $this->prepare_item_for_database( $request );

		// Field-level access (legacy parity): Employee ID, job/org and compensation
		// fields are manager-only. An employee editing their OWN profile reaches
		// this endpoint via the meta-mapped `erp_edit_employee` cap but does NOT
		// hold the bare manager cap — strip those fields so a crafted request can't
		// bypass the read-only form inputs. `date_of_birth` stays (a self-editable
		// personal field). Mirrors the legacy form's capability-gated inputs.
		if ( ! current_user_can( 'erp_edit_employee' ) ) {
			foreach ( [
				'employee_id', 'hiring_source', 'hiring_date', 'end_date',
				'pay_rate', 'pay_type', 'type', 'status',
				'designation', 'department', 'reporting_to', 'location',
			] as $manager_only ) {
				unset( $data['work'][ $manager_only ] );
			}
			unset( $data['personal']['employee_id'] );
		}

		if ( ! empty( $data['personal']['photo_id'] ) && (int) $data['personal']['photo_id'] !== (int) $employee->get_photo_id() ) {
			$photo_error = $this->photo_error( (int) $data['personal']['photo_id'], $user_id );
			if ( $photo_error ) {
				return $photo_error;
			}
		}

		if ( isset( $data['user_email'] )
			&& 0 !== strcasecmp( (string) $data['user_email'], (string) $employee->user_email )
			&& ! $this->can_change_login_email( $user_id ) ) {
			return new \WP_Error(
				'rest_cannot_change_email',
				get_current_user_id() === $user_id
					? __( 'You cannot change your own login email. Ask HR to change it.', 'erp' )
					: __( 'You are not allowed to change the email address of this user.', 'erp' ),
				[ 'status' => 403 ]
			);
		}

		$invalid = $this->validate_employee_rules( $data, $employee );
		if ( $invalid ) {
			return $invalid;
		}

		$updated = $employee->update_employee( $data );

		if ( is_wp_error( $updated ) ) {
			return $this->as_bad_request( $updated );
		}

		/** @see create_item() — same hook, fires for React-driven edits. */
		do_action( 'erp_hr_rest_employee_saved', $user_id, (array) $request->get_param( 'additional' ), $request );

		$response = rest_ensure_response( $this->get_edit_data( new Employee( $user_id ) ) );
		$response->set_status( 200 );

		return $response;
	}

	/**
	 * DELETE /erp/v2/employees/{user_id}
	 *
	 * Soft-deletes (trash) by default, or permanently deletes when `force=true`.
	 * Delegates to the unchanged `erp_employee_delete()` so every legacy hook
	 * (`erp_hr_delete_employee`, `erp_hr_after_delete_employee`), role swap and
	 * cache purge keeps firing — identical to the v1 admin path.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		$user_id  = (int) $request['user_id'];
		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'Invalid employee id.', 'erp' ), [ 'status' => 404 ] );
		}

		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'No employee found', 'erp' ), [ 'status' => 404 ] );
		}

		$hard = (int) ( (bool) ( $request['force'] ?? false ) );

		// Mirror `AjaxHandler::employee_remove()`: only delete when the user still
		// carries (or last carried) the employee role; honor the hard-delete filter
		// and fire the trash status hook.
		$last_user_role = get_user_meta( $user->ID, 'erp_last_removed_role', true );

		if ( ! in_array( 'employee', (array) $user->roles, true ) && 'employee' !== $last_user_role ) {
			return new \WP_Error( 'rest_employee_not_deletable', __( 'This user does not have the employee role and cannot be deleted from here.', 'erp' ), [ 'status' => 409 ] );
		}

		$hard = apply_filters( 'erp_employee_delete_hard', $hard );
		erp_employee_delete( $user_id, $hard );

		do_action( 'erp_hr_employee_after_update_status', $user_id, 'trash', erp_current_datetime()->format( 'Y-m-d' ) );

		$response = rest_ensure_response(
			[
				'deleted' => true,
				'force'   => (bool) $hard,
				'user_id' => $user_id,
			]
		);
		$response->set_status( 200 );

		return $response;
	}

	/**
	 * POST /erp/v2/employees/{user_id}/restore
	 *
	 * Restores a trashed employee via the unchanged `erp_employee_restore()`.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function restore_item( $request ) {
		$user_id = (int) $request['user_id'];
		$user    = get_user_by( 'id', $user_id );

		if ( ! $user ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'No employee found', 'erp' ), [ 'status' => 404 ] );
		}

		if ( ! \WeDevs\ERP\HRM\Models\Employee::withTrashed()->where( 'user_id', $user_id )->exists() ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'No employee found', 'erp' ), [ 'status' => 404 ] );
		}

		$user = apply_filters( 'pre_erp_hr_employee_args', $user );
		if ( is_wp_error( $user ) ) {
			return new \WP_Error( 'rest_employee_restore_failed', $user->get_error_message(), [ 'status' => 400 ] );
		}

		// Mirror `AjaxHandler::employee_restore()`: restore roles too when the user
		// no longer carries the employee role (trashed-with-role case).
		if ( in_array( 'employee', (array) $user->roles, true ) ) {
			erp_employee_restore( $user_id );
		} else {
			erp_employee_restore( $user_id, true );
		}

		return rest_ensure_response(
			[
				'restored' => true,
				'user_id'  => $user_id,
			]
		);
	}

	/**
	 * POST /erp/v2/employees/{user_id}/terminate
	 *
	 * Terminates an employee through the unchanged `Employee::terminate()` model
	 * (same path the legacy admin used). All four fields are required and the
	 * model enforces the same validation, sets status to `terminated`, writes the
	 * `_erp_hr_termination` meta and fires every legacy hook.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function terminate_item( $request ) {
		$user_id  = (int) $request['user_id'];
		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'Invalid employee id.', 'erp' ), [ 'status' => 404 ] );
		}

		$fields = [
			'user_id'             => $user_id,
			'terminate_date'      => empty( $request['terminate_date'] )
				? current_time( 'mysql' )
				: sanitize_text_field( $request['terminate_date'] ),
			'termination_type'    => sanitize_text_field( (string) ( $request['termination_type'] ?? '' ) ),
			'termination_reason'  => sanitize_text_field( (string) ( $request['termination_reason'] ?? '' ) ),
			'eligible_for_rehire' => sanitize_text_field( (string) ( $request['eligible_for_rehire'] ?? '' ) ),
		];

		// The termination date has to be a real day, on or after the date of hire.
		// Re-saving the termination already on record with the same date is not
		// re-checked, so an old termination stays editable.
		$stored_termination = (array) get_user_meta( $user_id, '_erp_hr_termination', true );
		$same_date          = ! empty( $stored_termination['terminate_date'] )
			&& $this->calendar_day( (string) $stored_termination['terminate_date'] ) === $this->calendar_day( $fields['terminate_date'] );

		if ( ! empty( $request['terminate_date'] ) && ! $same_date ) {
			if ( ! erp_is_valid_date( $fields['terminate_date'] ) ) {
				return new \WP_Error( 'rest_invalid_terminate_date', __( 'Enter a valid termination date.', 'erp' ), [ 'status' => 400 ] );
			}

			$hired = $this->calendar_day( (string) $employee->get_hiring_date() );

			if ( '' !== $hired && $this->calendar_day( $fields['terminate_date'] ) < $hired ) {
				return new \WP_Error( 'rest_invalid_terminate_date', __( 'Termination date cannot be earlier than the date of hire.', 'erp' ), [ 'status' => 400 ] );
			}
		}

		// Delegate to the frozen v1 helper: it runs `Employee::terminate()` and
		// fires `erp_hr_employee_update` with the correct ( $user_id, $old_data )
		// args. Re-rolling it here previously fired that audit hook with a single
		// Employee object, which crashed HrLog::update_employee() (needs 2 args).
		$result = erp_hr_employee_terminate( $fields );

		// The v1 helper returns a WP_Error or a plain error string on failure, and
		// a truthy result on success — normalize both error shapes to a 400.
		if ( is_wp_error( $result ) ) {
			return new \WP_Error(
				'rest_employee_terminate_failed',
				implode( ' ', (array) $result->get_error_messages() ),
				[ 'status' => 400 ]
			);
		}

		if ( \is_string( $result ) && '' !== $result ) {
			return new \WP_Error( 'rest_employee_terminate_failed', $result, [ 'status' => 400 ] );
		}

		$response = rest_ensure_response( $this->get_edit_data( new Employee( $user_id ) ) );
		$response->set_status( 200 );

		return $response;
	}

	/**
	 * POST /erp/v2/employees/{user_id}/reactivate
	 *
	 * Reverses a termination: sets the employee status back to `active` and clears
	 * the `_erp_hr_termination` meta. Mirrors the legacy AJAX
	 * `employee_termination_reactive()` exactly.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function reactivate_item( $request ) {
		$user_id  = (int) $request['user_id'];
		$employee = new Employee( $user_id );

		if ( ! $employee->is_employee() ) {
			return new \WP_Error( 'rest_employee_invalid_id', __( 'Invalid employee id.', 'erp' ), [ 'status' => 404 ] );
		}

		// Record the status change in the employment history, the same way
		// `Employee::terminate()` does, so the Job tab shows the reactivation.
		$employee->update_employment_status(
			[
				'module'   => 'employee',
				'category' => 'active',
				'comments' => __( 'Reactivated', 'erp' ),
			]
		);

		// The history call skips the status column when a future-dated history
		// row exists, so set it explicitly as before.
		\WeDevs\ERP\HRM\Models\Employee::where( 'user_id', $user_id )->update( [ 'status' => 'active' ] );
		delete_user_meta( $user_id, '_erp_hr_termination' );

		erp_hrm_purge_cache( [ 'list' => 'employee' ] );

		$response = rest_ensure_response( $this->get_edit_data( new Employee( $user_id ) ) );
		$response->set_status( 200 );

		return $response;
	}

	/**
	 * Argument schema for `POST /employees/{user_id}/terminate`. The four
	 * termination fields mirror the legacy form; the model performs the
	 * required-field + enum validation, so here we only sanitize.
	 *
	 * @return array
	 */
	public function get_terminate_params(): array {
		return [
			'terminate_date'      => [
				'description'       => __( 'Termination date (Y-m-d).', 'erp' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'termination_type'    => [
				'description'       => __( 'Termination type.', 'erp' ),
				'type'              => 'string',
				'required'          => true,
				'enum'              => array_keys( erp_hr_get_terminate_type() ),
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'termination_reason'  => [
				'description'       => __( 'Termination reason.', 'erp' ),
				'type'              => 'string',
				'required'          => true,
				'enum'              => array_keys( erp_hr_get_terminate_reason() ),
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'eligible_for_rehire' => [
				'description'       => __( 'Eligible for rehire.', 'erp' ),
				'type'              => 'string',
				'required'          => true,
				'enum'              => array_keys( erp_hr_get_terminate_rehire_options() ),
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
		];
	}

	/**
	 * Build the flat edit-shaped payload from an Employee. Reads the model's
	 * flattened data (`get_data`) for the bulk of fields and overlays the
	 * computed/typed fields (IDs as int|null, dates as ISO, email from the WP
	 * user). Keys mirror `get_create_params()`.
	 *
	 * @param Employee $employee Employee.
	 *
	 * @return array
	 */
	/**
	 * Let extensions reject a create/update before anything is written.
	 *
	 * The `additional` bucket is owned by pro (the Custom Field Builder), so the
	 * rules that apply to it live there too — this is the hook that lets them run
	 * server-side instead of trusting the client to have checked.
	 *
	 * @param WP_REST_Request $request The create/update request.
	 * @param string          $mode    'create' or 'edit'.
	 *
	 * @return \WP_Error|null WP_Error to reject, null to proceed.
	 */
	private function validate_extension_fields( $request, string $mode ) {
		/**
		 * Filter to validate extension-owned fields on a v2 employee write.
		 *
		 * @since 1.18.1
		 *
		 * @param \WP_Error|null  $error   Error to return, or null to allow.
		 * @param WP_REST_Request $request The request being validated.
		 * @param string          $mode    'create' or 'edit'.
		 */
		$error = apply_filters( 'erp_hr_v2_employee_validate', null, $request, $mode );

		return is_wp_error( $error ) ? $error : null;
	}

	/**
	 * Value rules for a create or edit, checked before anything is written.
	 *
	 * The legacy form saved both create and edit through
	 * `Employee::create_employee()`, so every field rule held on edit too. The v2
	 * edit path goes through `update_employee()`, which checks nothing, so the
	 * same rules run here for both modes. Also enforces the date order the
	 * React form checks (birth before hire, end date not before hire), reading
	 * the stored value for any date the request leaves out.
	 *
	 * @param array         $data     Output of prepare_item_for_database().
	 * @param Employee|null $employee The employee being edited, null on create.
	 *
	 * @return \WP_Error|null WP_Error (400) to reject, null when valid.
	 */
	private function validate_employee_rules( array $data, $employee = null ) {
		$personal = (array) ( $data['personal'] ?? [] );
		$work     = (array) ( $data['work'] ?? [] );
		$user_id  = $employee ? (int) $employee->get_user_id() : 0;

		$fail = static function ( string $code, string $message ) {
			return new \WP_Error( $code, $message, [ 'status' => 400 ] );
		};

		$name_fields = [
			'first_name'  => __( 'Enter a valid first name. Use letters only, no digits or symbols.', 'erp' ),
			'middle_name' => __( 'Enter a valid middle name. Use letters only, no digits or symbols.', 'erp' ),
			'last_name'   => __( 'Enter a valid last name. Use letters only, no digits or symbols.', 'erp' ),
			'father_name' => __( "Enter a valid father's name. Use letters only, no digits or symbols.", 'erp' ),
			'mother_name' => __( "Enter a valid mother's name. Use letters only, no digits or symbols.", 'erp' ),
			'spouse_name' => __( "Enter a valid spouse's name. Use letters only, no digits or symbols.", 'erp' ),
		];

		if ( \array_key_exists( 'first_name', $personal ) && '' === trim( (string) $personal['first_name'] ) ) {
			return $fail( 'rest_empty_first_name', __( 'First name is required.', 'erp' ) );
		}
		if ( \array_key_exists( 'last_name', $personal ) && '' === trim( (string) $personal['last_name'] ) ) {
			return $fail( 'rest_empty_last_name', __( 'Last name is required.', 'erp' ) );
		}
		foreach ( $name_fields as $key => $message ) {
			if ( ! empty( $personal[ $key ] ) && ! erp_is_valid_name( $personal[ $key ] ) ) {
				return $fail( 'rest_invalid_' . $key, $message );
			}
		}

		if ( ! empty( $personal['employee_id'] ) ) {
			if ( ! erp_is_valid_employee_id( $personal['employee_id'] ) ) {
				return $fail( 'rest_invalid_employee_id', __( 'Employee ID can contain only letters, numbers and hyphens.', 'erp' ) );
			}
			$taken = \WeDevs\ERP\HRM\Models\Employee::where( 'employee_id', $personal['employee_id'] )
				->where( 'user_id', '!=', $user_id )
				->exists();
			if ( $taken ) {
				return $fail( 'rest_employee_id_exists', __( 'Another employee already uses this employee ID.', 'erp' ) );
			}
		}

		if ( \array_key_exists( 'user_email', $data ) ) {
			if ( ! is_email( (string) $data['user_email'] ) ) {
				return $fail( 'rest_invalid_email', __( 'Enter a valid email address.', 'erp' ) );
			}
			// Create keeps the model's own check (it offers the convert-a-WP-user
			// flow); on edit any other account holding the address blocks it.
			if ( $employee && 0 !== strcasecmp( (string) $data['user_email'], (string) $employee->user_email )
				&& erp_is_employee_exist( (string) $data['user_email'], $user_id ) ) {
				return $fail( 'rest_email_exists', __( 'Another user already uses this email address.', 'erp' ) );
			}
		}

		if ( ! empty( $personal['other_email'] ) && ! is_email( $personal['other_email'] ) ) {
			return $fail( 'rest_invalid_other_email', __( 'Enter a valid other email address.', 'erp' ) );
		}

		$phones = [
			'work_phone' => __( 'Enter a valid work phone number.', 'erp' ),
			'mobile'     => __( 'Enter a valid mobile number.', 'erp' ),
			'phone'      => __( 'Enter a valid phone number.', 'erp' ),
		];
		foreach ( $phones as $key => $message ) {
			if ( ! empty( $personal[ $key ] ) && ! erp_is_valid_contact_no( $personal[ $key ] ) ) {
				return $fail( 'rest_invalid_' . $key, $message );
			}
		}

		$enums = [
			'type'          => [ $work, erp_hr_get_employee_types(), __( 'Select a valid employee type.', 'erp' ) ],
			'status'        => [ $work, erp_hr_get_employee_statuses(), __( 'Select a valid employee status.', 'erp' ) ],
			'hiring_source' => [ $work, erp_hr_get_employee_sources(), __( 'Select a valid source of hire.', 'erp' ) ],
			'pay_type'      => [ $work, erp_hr_get_pay_type(), __( 'Select a valid pay type.', 'erp' ) ],
			'gender'        => [ $personal, erp_hr_get_genders(), __( 'Select a valid gender.', 'erp' ) ],
			'marital_status' => [ $personal, erp_hr_get_marital_statuses(), __( 'Select a valid marital status.', 'erp' ) ],
		];
		foreach ( $enums as $key => [ $bucket, $allowed, $message ] ) {
			$value = (string) ( $bucket[ $key ] ?? '' );
			if ( '' !== $value && '-1' !== $value && ! \array_key_exists( $value, (array) $allowed ) ) {
				return $fail( 'rest_invalid_' . $key, $message );
			}
		}

		if ( ! empty( $work['department'] ) && ! \array_key_exists( $work['department'], (array) erp_hr_get_departments_fresh() ) ) {
			return $fail( 'rest_invalid_department', __( 'Select a valid department.', 'erp' ) );
		}
		if ( ! empty( $work['designation'] ) && ! \array_key_exists( $work['designation'], (array) erp_hr_get_designations_fresh() ) ) {
			return $fail( 'rest_invalid_designation', __( 'Select a valid job title.', 'erp' ) );
		}
		if ( ! empty( $work['location'] ) && ! \array_key_exists( $work['location'], (array) erp_company_get_location_dropdown_raw() ) ) {
			return $fail( 'rest_invalid_location', __( 'Select a valid location.', 'erp' ) );
		}

		if ( ! empty( $work['pay_rate'] ) && ! erp_is_valid_currency_amount( $work['pay_rate'] ) ) {
			return $fail( 'rest_invalid_pay_rate', __( 'Enter a valid pay rate.', 'erp' ) );
		}
		if ( ! empty( $personal['user_url'] ) && ! erp_is_valid_url( $personal['user_url'] ) ) {
			return $fail( 'rest_invalid_user_url', __( 'Enter a valid website URL.', 'erp' ) );
		}
		if ( ! empty( $personal['city'] ) && erp_contains_disallowed_chars( $personal['city'] ) ) {
			return $fail( 'rest_invalid_city', __( 'Remove the special characters from the city name.', 'erp' ) );
		}
		if ( ! empty( $personal['postal_code'] ) && ! erp_is_valid_zip_code( $personal['postal_code'] ) ) {
			return $fail( 'rest_invalid_postal_code', __( 'Enter a valid post code or zip code.', 'erp' ) );
		}

		$dates = [
			'hiring_date'   => __( 'Enter a valid date of hire.', 'erp' ),
			'end_date'      => __( 'Enter a valid employee end date.', 'erp' ),
			'date_of_birth' => __( 'Enter a valid date of birth.', 'erp' ),
		];
		$stored  = $employee ? $this->get_edit_data( $employee ) : [];
		$ymd     = [];
		$changed = [];
		foreach ( $dates as $key => $message ) {
			$sent  = \array_key_exists( $key, $work );
			$value = $sent ? trim( (string) $work[ $key ] ) : (string) ( $stored[ $key ] ?? '' );
			// A sent date was always checked (legacy create_employee() did); a
			// stored one the request leaves out is not re-checked.
			if ( $sent && '' !== $value && ! erp_is_valid_date( $value ) ) {
				return $fail( 'rest_invalid_' . $key, $message );
			}
			$ymd[ $key ] = $this->calendar_day( $value );
			// The date-order rules below are new: on an edit they run only when a
			// date they read is changed, so an old record left as it was saves.
			$changed[ $key ] = ! $employee || ( $sent && $ymd[ $key ] !== $this->calendar_day( (string) ( $stored[ $key ] ?? '' ) ) );
		}

		if ( '' !== $ymd['date_of_birth'] && ( $changed['date_of_birth'] || $changed['hiring_date'] ) ) {
			if ( $changed['date_of_birth'] && $ymd['date_of_birth'] > current_time( 'Y-m-d' ) ) {
				return $fail( 'rest_invalid_date_of_birth', __( 'Date of birth cannot be in the future.', 'erp' ) );
			}
			if ( '' !== $ymd['hiring_date'] && $ymd['date_of_birth'] >= $ymd['hiring_date'] ) {
				return $fail( 'rest_invalid_date_of_birth', __( 'Date of birth must be earlier than the date of hire.', 'erp' ) );
			}
		}
		if ( '' !== $ymd['end_date'] && '' !== $ymd['hiring_date'] && ( $changed['end_date'] || $changed['hiring_date'] ) && $ymd['end_date'] < $ymd['hiring_date'] ) {
			return $fail( 'rest_invalid_end_date', __( 'Employee end date cannot be earlier than the date of hire.', 'erp' ) );
		}

		return null;
	}

	/**
	 * A date value as its `Y-m-d` calendar day ('' when empty). A leading
	 * `Y-m-d` is kept as-is so a timezone offset cannot shift the day.
	 *
	 * @param string $value Date or datetime string.
	 *
	 * @return string
	 */
	private function calendar_day( string $value ): string {
		$value = trim( $value );

		if ( '' === $value ) {
			return '';
		}

		if ( preg_match( '/^\d{4}-\d{2}-\d{2}/', $value ) ) {
			return substr( $value, 0, 10 );
		}

		return wp_date( 'Y-m-d', (int) strtotime( $value ) );
	}

	/**
	 * Re-issue a model WP_Error as a 400, so a rejected value is reported as a
	 * client error instead of the REST default 500.
	 *
	 * @param \WP_Error $error Error from the Employee model.
	 *
	 * @return \WP_Error
	 */
	private function as_bad_request( \WP_Error $error ): \WP_Error {
		$data = $error->get_error_data();

		if ( \is_array( $data ) && isset( $data['status'] ) ) {
			return $error;
		}

		return new \WP_Error(
			$error->get_error_code() ?: 'rest_employee_invalid',
			$error->get_error_message() ?: __( 'The employee could not be saved.', 'erp' ),
			[ 'status' => 400 ]
		);
	}

	private function get_edit_data( Employee $employee ): array {
		$flat = (array) $employee->get_data( [], true );

		$str = static function ( $key ) use ( $flat ): string {
			return isset( $flat[ $key ] ) && ! is_array( $flat[ $key ] ) ? (string) $flat[ $key ] : '';
		};

		return [
			'user_id'         => (int) $employee->get_user_id(),
			'full_name'       => (string) $employee->get_full_name(),
			'avatar_url'      => $employee->get_avatar_url( 80 ) ?: null,
			'employee_id'     => (string) $employee->employee_id,
			'first_name'      => (string) $employee->first_name,
			'middle_name'     => $str( 'middle_name' ),
			'last_name'       => (string) $employee->last_name,
			'email'           => (string) $employee->user_email,
			'type'            => $str( 'type' ),
			'status'          => $str( 'status' ),
			'hiring_date'     => $this->cast_date_iso( $str( 'hiring_date' ) ) ?? '',
			'end_date'        => $this->cast_date_iso( $str( 'end_date' ) ) ?? '',
			'date_of_birth'   => $this->cast_date_iso( $str( 'date_of_birth' ) ) ?? '',
			'department'      => $this->cast_int_or_null( $employee->department ),
			'designation'     => $this->cast_int_or_null( $employee->designation ),
			'location'        => $this->cast_int_or_null( $employee->location ),
			'reporting_to'    => $this->cast_int_or_null( $employee->get_reporting_to() ),
			'hiring_source'   => $str( 'hiring_source' ),
			'pay_rate'        => $str( 'pay_rate' ),
			'pay_type'        => (string) $employee->pay_type,
			'work_phone'      => $str( 'work_phone' ),
			'other_email'     => $str( 'other_email' ),
			'phone'           => $str( 'phone' ),
			'mobile'          => $str( 'mobile' ),
			'blood_group'     => $str( 'blood_group' ),
			'gender'          => $str( 'gender' ),
			'marital_status'  => $str( 'marital_status' ),
			'nationality'     => $str( 'nationality' ),
			'driving_license' => $str( 'driving_license' ),
			'hobbies'         => $str( 'hobbies' ),
			'user_url'        => $str( 'user_url' ),
			'description'     => $str( 'description' ),
			'street_1'        => $str( 'street_1' ),
			'street_2'        => $str( 'street_2' ),
			'city'            => $str( 'city' ),
			'country'         => $str( 'country' ),
			'state'           => $str( 'state' ),
			'postal_code'     => $str( 'postal_code' ),
			'father_name'     => $str( 'father_name' ),
			'mother_name'     => $str( 'mother_name' ),
			'spouse_name'     => $str( 'spouse_name' ),
			// Termination details (mirrors legacy tab-general.php:348 "Termination"
			// postbox) — null unless the employee is terminated. Read the same
			// `_erp_hr_termination` meta `Employee::terminate()` writes, with the
			// slug→label maps resolved server-side so the client renders parity text.
			'termination'     => $this->build_termination( (int) $employee->get_user_id() ),
		];
	}

	/**
	 * Build the termination-details payload from the `_erp_hr_termination` user
	 * meta (the exact array `Employee::terminate()` persists). Returns null when
	 * the employee has never been terminated / was reactivated (meta cleared).
	 * Labels resolve through the same helper maps the legacy profile view used.
	 *
	 * @param int $user_id Employee user ID.
	 *
	 * @return array|null
	 */
	private function build_termination( int $user_id ): ?array {
		$meta = get_user_meta( $user_id, '_erp_hr_termination', true );

		if ( empty( $meta ) || ! is_array( $meta ) ) {
			return null;
		}

		$type   = (string) ( $meta['termination_type'] ?? '' );
		$reason = (string) ( $meta['termination_reason'] ?? '' );
		$rehire = (string) ( $meta['eligible_for_rehire'] ?? '' );
		$date   = (string) ( $meta['terminate_date'] ?? '' );

		return [
			'terminate_date'            => $this->cast_date_iso( $date ) ?? $date,
			'termination_type'          => $type,
			'termination_type_label'    => '' !== $type ? (string) erp_hr_get_terminate_type( $type ) : '',
			'termination_reason'        => $reason,
			'termination_reason_label'  => '' !== $reason ? (string) erp_hr_get_terminate_reason( $reason ) : '',
			'eligible_for_rehire'       => $rehire,
			'eligible_for_rehire_label' => '' !== $rehire ? (string) erp_hr_get_terminate_rehire_options( $rehire ) : '',
		];
	}

	/**
	 * Latest status-change date for the employee from the
	 * `erp_hr_employee_history` table, matching the status slug as the history
	 * `category` (`inactive` / `terminated` / `deceased` / `resigned`). Mirrors
	 * the legacy list's `get_employee_status_update_date()` used to render the
	 * status-adaptive "Terminated At" / "Inactive From" column. `active` (and the
	 * default) has no history date.
	 *
	 * @param int    $user_id Employee user ID.
	 * @param string $status  Current employee status slug.
	 * @param mixed  $deleted_at The employee row's `deleted_at` value, if any.
	 *
	 * @return string|null ISO date or null.
	 */
	private function status_update_date( int $user_id, string $status, $deleted_at = null ): ?string {
		global $wpdb;

		// Trashing does not write an employee-history row and does not change the
		// employee's own `status` — it only stamps `erp_hr_employees.deleted_at`,
		// which is what the legacy list printed under "Trashed At", and what the
		// trash tab itself filters on. So look at that column before anything else:
		// a row carrying it IS trashed, whatever `status` still says. The caller
		// passes the column from the employee row it already loaded.

		if ( ! empty( $deleted_at ) ) {
			return $this->cast_date_iso( (string) $deleted_at ) ?? (string) $deleted_at;
		}

		if ( '' === $status || 'active' === $status ) {
			return null;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$date = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT `date` FROM {$wpdb->prefix}erp_hr_employee_history WHERE user_id = %d AND module = 'employee' AND category = %s ORDER BY `date` DESC LIMIT 1",
				$user_id,
				$status
			)
		);

		if ( empty( $date ) ) {
			return null;
		}

		return $this->cast_date_iso( (string) $date ) ?? (string) $date;
	}

	/**
	 * Flatten the incoming v2 create payload into the nested
	 * `[ 'personal' => [...], 'work' => [...], 'user_email' => ... ]` structure
	 * that `Employee::create_employee()` expects. Same field map as the v1
	 * controller's `prepare_item_for_database()` — kept here so v1 stays
	 * untouched.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return array
	 */
	protected function prepare_item_for_database( $request ): array {
		$prepared = [];

		// Required / identity.
		if ( isset( $request['first_name'] ) ) {
			$prepared['personal']['first_name'] = sanitize_text_field( $request['first_name'] );
		}
		if ( isset( $request['last_name'] ) ) {
			$prepared['personal']['last_name'] = sanitize_text_field( $request['last_name'] );
		}
		if ( isset( $request['middle_name'] ) ) {
			$prepared['personal']['middle_name'] = sanitize_text_field( $request['middle_name'] );
		}
		if ( isset( $request['email'] ) ) {
			$prepared['user_email'] = sanitize_email( $request['email'] );
		}
		if ( isset( $request['employee_id'] ) ) {
			$employee_id                         = sanitize_text_field( $request['employee_id'] );
			$prepared['work']['employee_id']     = $employee_id;
			// Also set on `personal` so the model's erp_is_valid_employee_id()
			// format check fires (it reads `personal`), mirroring the legacy form.
			$prepared['personal']['employee_id'] = $employee_id;
		}

		// Work.
		$work_text = [ 'hiring_source', 'hiring_date', 'end_date', 'date_of_birth', 'pay_rate', 'pay_type', 'type', 'status' ];
		foreach ( $work_text as $key ) {
			if ( isset( $request[ $key ] ) ) {
				$prepared['work'][ $key ] = sanitize_text_field( $request[ $key ] );
			}
		}
		$work_int = [ 'designation', 'department', 'reporting_to', 'location' ];
		foreach ( $work_int as $key ) {
			if ( isset( $request[ $key ] ) ) {
				$prepared['work'][ $key ] = absint( $request[ $key ] );
			}
		}

		// Personal.
		$personal_text = [
			'other_email', 'phone', 'work_phone', 'mobile', 'blood_group', 'gender',
			'marital_status', 'nationality', 'driving_license', 'hobbies', 'street_1',
			'street_2', 'city', 'country', 'state', 'postal_code', 'father_name',
			'mother_name', 'spouse_name',
		];
		foreach ( $personal_text as $key ) {
			if ( isset( $request[ $key ] ) ) {
				$prepared['personal'][ $key ] = sanitize_text_field( $request[ $key ] );
			}
		}
		if ( isset( $request['user_url'] ) ) {
			$prepared['personal']['user_url'] = esc_url_raw( $request['user_url'] );
		}
		if ( isset( $request['description'] ) ) {
			$prepared['personal']['description'] = sanitize_textarea_field( $request['description'] );
		}
		if ( isset( $request['photo_id'] ) ) {
			$prepared['personal']['photo_id'] = absint( $request['photo_id'] );
		}

		return $prepared;
	}

	/**
	 * Argument schema for `POST /employees`. Validates the modern flat payload
	 * the React create form submits.
	 *
	 * @return array
	 */
	public function get_create_params(): array {
		$text     = static function ( $description, $required = false ) {
			return [
				'description'       => $description,
				'type'              => 'string',
				'required'          => $required,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			];
		};
		$integer  = static function ( $description ) {
			return [
				'description'       => $description,
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			];
		};

		return [
			'first_name'        => $text( __( 'Employee first name.', 'erp' ), true ),
			'middle_name'       => $text( __( 'Employee middle name.', 'erp' ) ),
			'last_name'         => $text( __( 'Employee last name.', 'erp' ), true ),
			'employee_id'       => $text( __( 'Employee ID.', 'erp' ) ),
			'email'             => [
				'description'       => __( 'Employee email address.', 'erp' ),
				'type'              => 'string',
				'format'            => 'email',
				'required'          => true,
				'sanitize_callback' => 'sanitize_email',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'type'              => $text( __( 'Employment type.', 'erp' ) ),
			'status'            => $text( __( 'Employment status.', 'erp' ) ),
			'hiring_date'       => $text( __( 'Date of hire (Y-m-d).', 'erp' ) ),
			'end_date'          => $text( __( 'Employment end date (Y-m-d).', 'erp' ) ),
			'date_of_birth'     => $text( __( 'Date of birth (Y-m-d).', 'erp' ) ),
			'department'        => $integer( __( 'Department ID.', 'erp' ) ),
			'designation'       => $integer( __( 'Designation ID.', 'erp' ) ),
			'location'          => $integer( __( 'Location ID.', 'erp' ) ),
			'reporting_to'      => $integer( __( 'Reporting manager user ID.', 'erp' ) ),
			'hiring_source'     => $text( __( 'Source of hire.', 'erp' ) ),
			'pay_rate'          => $text( __( 'Pay rate.', 'erp' ) ),
			'pay_type'          => $text( __( 'Pay type.', 'erp' ) ),
			'work_phone'        => $text( __( 'Work phone.', 'erp' ) ),
			'other_email'       => $text( __( 'Other email.', 'erp' ) ),
			'phone'             => $text( __( 'Phone.', 'erp' ) ),
			'mobile'            => $text( __( 'Mobile.', 'erp' ) ),
			'blood_group'       => $text( __( 'Blood group.', 'erp' ) ),
			'gender'            => $text( __( 'Gender.', 'erp' ) ),
			'marital_status'    => $text( __( 'Marital status.', 'erp' ) ),
			'nationality'       => $text( __( 'Nationality.', 'erp' ) ),
			'driving_license'   => $text( __( 'Driving license.', 'erp' ) ),
			'hobbies'           => $text( __( 'Hobbies.', 'erp' ) ),
			'user_url'          => [
				'description'       => __( 'Website URL.', 'erp' ),
				'type'              => 'string',
				'format'            => 'uri',
				'sanitize_callback' => 'esc_url_raw',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'description'       => [
				'description'       => __( 'Biography.', 'erp' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_textarea_field',
			],
			'street_1'          => $text( __( 'Address line 1.', 'erp' ) ),
			'street_2'          => $text( __( 'Address line 2.', 'erp' ) ),
			'city'              => $text( __( 'City.', 'erp' ) ),
			'country'           => $text( __( 'Country.', 'erp' ) ),
			'state'             => $text( __( 'State / province.', 'erp' ) ),
			'postal_code'       => $text( __( 'Postal / ZIP code.', 'erp' ) ),
			'father_name'       => $text( __( "Father's name.", 'erp' ) ),
			'mother_name'       => $text( __( "Mother's name.", 'erp' ) ),
			'spouse_name'       => $text( __( "Spouse's name.", 'erp' ) ),
			'photo_id'          => $integer( __( 'Profile photo attachment ID.', 'erp' ) ),
			'user_notification' => [
				'description' => __( 'Send the new employee a welcome email.', 'erp' ),
				'type'        => 'boolean',
				'default'     => false,
			],
			'login_info'        => [
				'description' => __( 'Include login details in the welcome email.', 'erp' ),
				'type'        => 'boolean',
				'default'     => false,
			],
		];
	}

	/**
	 * GET /erp/v2/employees
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_items( $request ): WP_REST_Response {
		$page     = max( 1, (int) ( $request['page'] ?? 1 ) );
		$per_page = max( 1, min( 100, (int) ( $request['per_page'] ?? 20 ) ) );

		$orderby_key = $this->cast_enum(
			(string) ( $request['orderby'] ?? 'hire_date' ),
			array_keys( self::ORDERBY_MAP )
		);
		$orderby = self::ORDERBY_MAP[ $orderby_key ?? 'hire_date' ] ?? 'hiring_date';

		$order = strtoupper( (string) ( $request['order'] ?? 'desc' ) );
		$order = in_array( $order, [ 'ASC', 'DESC' ], true ) ? $order : 'DESC';

		$status = (string) ( $request['status'] ?? 'active' );
		$status = $this->cast_enum( $status, $this->allowed_statuses() ) ?? 'active';

		// The directory an employee sees is the people working here now. Who was
		// terminated, resigned or trashed is for HR only.
		if ( ! current_user_can( 'erp_view_employee' ) ) {
			$status = 'active';
		}

		$args = [
			'number'      => $per_page,
			'offset'      => ( $page - 1 ) * $per_page,
			'orderby'     => $orderby,
			'order'       => $order,
			'status'      => $status,
			'department'  => $this->cast_int_or_null( $request['department_id'] ?? null ) ?? -1,
			'designation' => $this->cast_int_or_null( $request['designation_id'] ?? null ) ?? -1,
			'location'    => $this->cast_int_or_null( $request['location_id'] ?? null ) ?? -1,
			'type'        => '' !== (string) ( $request['employee_type'] ?? '' ) ? sanitize_text_field( (string) $request['employee_type'] ) : -1,
			's'           => sanitize_text_field( (string) ( $request['search'] ?? '' ) ),
		];

		/**
		 * Filter the args passed to `erp_hr_get_employees()` for the v2 list.
		 *
		 * Pro plugins inject extra WHERE filters here without touching the
		 * controller.
		 *
		 * @since 1.13.5
		 *
		 * @param array           $args    Args for `erp_hr_get_employees()`.
		 * @param WP_REST_Request $request The REST request.
		 */
		$args = (array) apply_filters( 'erp_hr_v2_employees_query_args', $args, $request );

		$employees = (array) erp_hr_get_employees( $args );

		$count_args          = $args;
		$count_args['count'] = true;
		$total               = (int) erp_hr_get_employees( $count_args );

		// Prime the photo attachments in one query instead of two per row.
		$photo_ids = [];
		foreach ( $employees as $employee ) {
			if ( $employee instanceof Employee && $employee->get_photo_id() ) {
				$photo_ids[] = (int) $employee->get_photo_id();
			}
		}
		if ( $photo_ids ) {
			_prime_post_caches( array_unique( $photo_ids ), false, true );
		}

		$items = [];
		foreach ( $employees as $employee ) {
			if ( ! ( $employee instanceof Employee ) ) {
				continue;
			}
			$items[] = $this->prepare_item_for_response( $employee, $request );
		}

		$response = rest_ensure_response( $items );
		return $this->paginate( $response, $request, $total );
	}

	/**
	 * Reshape a v1 Employee domain object into the v2 DataView row.
	 *
	 * @param mixed           $employee A `WeDevs\ERP\HRM\Employee` instance.
	 * @param WP_REST_Request $request  The REST request.
	 *
	 * @return array
	 */
	public function prepare_item_for_response( $employee, $request ) {
		unset( $request );

		if ( ! ( $employee instanceof Employee ) ) {
			return [];
		}

		$user_id        = (int) $employee->get_user_id();
		$department_id  = $this->cast_int_or_null( $employee->department );
		$designation_id = $this->cast_int_or_null( $employee->designation );
		$location_id    = $this->cast_int_or_null( $employee->location );
		$reporting_id   = $this->cast_int_or_null( $employee->get_reporting_to() );

		$status_slug = (string) $employee->get_status();
		// Status-adaptive date (legacy list "Terminated At" / "Inactive From"
		// column): the latest `erp_hr_employee_history` row whose category matches
		// the current status. `active` has no such date.
		$erp_row     = $employee->get_erp_user() ? $employee->get_erp_user()->getAttributes() : [];
		$status_date = $this->status_update_date( $user_id, $status_slug, $erp_row['deleted_at'] ?? null );

		$item = [
			'id'               => $user_id,
			'user_id'          => $user_id,
			'employee_id'      => $this->cast_string_or_null( $employee->employee_id ) ?? '',
			'full_name'        => $this->cast_string_or_null( $employee->get_full_name() ) ?? '',
			'first_name'       => $this->cast_string_or_null( $employee->first_name ) ?? '',
			'last_name'        => $this->cast_string_or_null( $employee->last_name ) ?? '',
			'email'            => $this->cast_string_or_null( $employee->user_email ) ?? '',
			'avatar_url'       => $employee->get_avatar_url( 80 ) ?: null,
			'status'           => $this->cast_enum( $status_slug, $this->allowed_employee_status_keys() ),
			'employee_type'    => $this->cast_string_or_null( $employee->get_type() ),
			'hire_date'        => $this->cast_date_iso( $employee->get_hiring_date() ),
			'termination_date' => 'terminated' === $status_slug ? $status_date : null,
			'status_date'      => $status_date,
			'is_active'        => 'active' === $status_slug,
			'department'       => $department_id
				? $this->embed_department( $department_id, $this->lookup_name( 'department', $department_id, $employee ) )
				: null,
			'designation'      => $designation_id
				? $this->embed_designation( $designation_id, $this->lookup_name( 'designation', $designation_id, $employee ) )
				: null,
			'location'         => $location_id
				? $this->embed_location( $location_id, $this->lookup_name( 'location', $location_id, $employee ) )
				: null,
			'reporting_to'     => $this->embed_reporting_to( $reporting_id ),
			'phone'            => $this->cast_string_or_null( $employee->get_phone() ),
			'pay_type'         => $this->cast_string_or_null( $employee->pay_type ),
			'extra'            => [],
		];

		// The list route only requires `erp_list_employee`, which every employee
		// holds, so a peer row must carry the same basic-info-only shape
		// `get_item()` returns. Managers and the employee's own row are unaffected.
		if ( $user_id !== get_current_user_id()
			&& ! current_user_can( 'erp_view_employee' )
			&& ! current_user_can( 'erp_edit_employee', $user_id ) ) {
			$item['phone']    = null;
			$item['pay_type'] = null;
		}

		// "Switch to" (User Switching) impersonation URL — mirrors the legacy
		// EmployeeListTable row action: only when the User Switching plugin is
		// active AND the current user may edit employees. The URL is nonce'd and
		// bound to the CURRENT admin session, so it's computed per request/row.
		// Exact legacy parity (EmployeeListTable.php:242-245): guard on
		// user_switching being active + `erp_edit_employee`, and build the URL
		// with switch_to_url(). No `switch_to_user` cap check — matches legacy.
		if ( class_exists( 'user_switching' ) && current_user_can( 'erp_edit_employee' ) ) {
			$wp_user = get_user_by( 'id', $user_id );
			if ( $wp_user ) {
				// switch_to_url() returns an esc_html'd URL with `&amp;`
				// separators (fine for the legacy `<a href>`, where the browser
				// decodes them on click). React navigates via
				// `window.location.href`, which does NOT decode entities, so the
				// `&amp;` would corrupt the query (user_id/_wpnonce lost) → "Could
				// not switch users." Decode to plain `&` so JS navigation lands on
				// the same URL the legacy link did.
				$switch_url = html_entity_decode( \user_switching::switch_to_url( $wp_user ), ENT_QUOTES );
				if ( $switch_url ) {
					$item['extra']['switch_to_url'] = esc_url_raw( $switch_url );
				}
			}
		}

		/**
		 * Filter the v2 employee response item.
		 *
		 * Pro plugins append fields to `$item['extra'][*]`. Free never registers
		 * this filter. Pro must never replace a free field.
		 *
		 * @since 1.13.5
		 *
		 * @param array    $item     The response item.
		 * @param Employee $employee The Employee domain object.
		 */
		$item = (array) apply_filters( 'erp_hr_v2_employees_response_item', $item, $employee );

		return $item;
	}

	/**
	 * Resolve a department / designation / location name once per id per request.
	 * The name depends only on the id, so rows sharing an id reuse the first lookup.
	 *
	 * @param string   $type     'department', 'designation' or 'location'.
	 * @param int      $id       Lookup ID.
	 * @param Employee $employee Employee carrying that ID.
	 *
	 * @return string
	 */
	private function lookup_name( string $type, int $id, Employee $employee ): string {
		if ( ! isset( $this->lookup_names[ $type ][ $id ] ) ) {
			$this->lookup_names[ $type ][ $id ] = (string) $employee->{"get_{$type}"}( 'view' );
		}

		return $this->lookup_names[ $type ][ $id ];
	}

	/**
	 * Embed a department object using the data already on the Employee.
	 *
	 * @param int    $id   Department ID.
	 * @param string $name Department name as returned by Employee::get_department('view').
	 *
	 * @return array|null
	 */
	private function embed_department( int $id, string $name ): ?array {
		if ( $id <= 0 ) {
			return null;
		}
		return [
			'id'   => $id,
			'name' => $this->cast_string_or_null( $name ) ?? '',
		];
	}

	/**
	 * Embed a designation object.
	 *
	 * @param int    $id   Designation ID.
	 * @param string $name Designation name.
	 *
	 * @return array|null
	 */
	private function embed_designation( int $id, string $name ): ?array {
		if ( $id <= 0 ) {
			return null;
		}
		return [
			'id'   => $id,
			'name' => $this->cast_string_or_null( $name ) ?? '',
		];
	}

	/**
	 * Embed a location object.
	 *
	 * @param int    $id   Location ID.
	 * @param string $name Location name.
	 *
	 * @return array|null
	 */
	private function embed_location( int $id, string $name ): ?array {
		if ( $id <= 0 ) {
			return null;
		}
		return [
			'id'   => $id,
			'name' => $this->cast_string_or_null( $name ) ?? '',
		];
	}

	/**
	 * Embed the reporting-to user, resolved into id + display name.
	 *
	 * @param int|null $reporting_id Reporting user ID.
	 *
	 * @return array|null
	 */
	private function embed_reporting_to( ?int $reporting_id ): ?array {
		if ( ! $reporting_id ) {
			return null;
		}

		$reporting_user = get_userdata( $reporting_id );
		if ( ! $reporting_user ) {
			return null;
		}

		return [
			'id'        => (int) $reporting_user->ID,
			'full_name' => $this->cast_string_or_null( $reporting_user->display_name ) ?? '',
		];
	}

	/**
	 * Allowed status query values (including the special `all` + `trash`).
	 *
	 * @return string[]
	 */
	private function allowed_statuses(): array {
		return array_merge(
			$this->allowed_employee_status_keys(),
			[ 'all', 'trash' ]
		);
	}

	/**
	 * Allowed concrete employee status keys (no `all`, no `trash`).
	 *
	 * @return string[]
	 */
	private function allowed_employee_status_keys(): array {
		$statuses = (array) erp_hr_get_employee_statuses();
		return array_keys( $statuses );
	}

	/**
	 * Collection params for `GET /employees`.
	 *
	 * @return array
	 */
	public function get_collection_params(): array {
		$params = parent::get_collection_params();

		$params['status'] = [
			'description'       => __( 'Employee status filter.', 'erp' ),
			'type'              => 'string',
			'default'           => 'active',
			'enum'              => $this->allowed_statuses(),
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		];

		$params['department_id'] = [
			'description'       => __( 'Filter by department ID.', 'erp' ),
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		];

		$params['designation_id'] = [
			'description'       => __( 'Filter by designation ID.', 'erp' ),
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		];

		$params['location_id'] = [
			'description'       => __( 'Filter by location ID.', 'erp' ),
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		];

		$params['orderby'] = [
			'description'       => __( 'Sort field.', 'erp' ),
			'type'              => 'string',
			'default'           => 'hire_date',
			'enum'              => array_keys( self::ORDERBY_MAP ),
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		];

		$params['order'] = [
			'description'       => __( 'Sort direction.', 'erp' ),
			'type'              => 'string',
			'default'           => 'desc',
			'enum'              => [ 'asc', 'desc' ],
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		];

		return $params;
	}

	/**
	 * JSON Schema for a single employee list row.
	 *
	 * @return array
	 */
	public function get_item_schema(): array {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'employee-list-item',
			'type'       => 'object',
			'properties' => [
				'id'               => [ 'type' => 'integer' ],
				'user_id'          => [ 'type' => 'integer' ],
				'employee_id'      => [ 'type' => 'string' ],
				'full_name'        => [ 'type' => 'string' ],
				'first_name'       => [ 'type' => 'string' ],
				'last_name'        => [ 'type' => 'string' ],
				'email'            => [ 'type' => 'string' ],
				'avatar_url'       => [ 'type' => [ 'string', 'null' ] ],
				'status'           => [
					'type' => [ 'string', 'null' ],
					'enum' => array_merge( $this->allowed_employee_status_keys(), [ null ] ),
				],
				'employee_type'    => [ 'type' => [ 'string', 'null' ] ],
				'hire_date'        => [ 'type' => [ 'string', 'null' ] ],
				'termination_date' => [ 'type' => [ 'string', 'null' ] ],
				'status_date'      => [ 'type' => [ 'string', 'null' ] ],
				'is_active'        => [ 'type' => 'boolean' ],
				'department'       => $this->embedded_lookup_schema(),
				'designation'      => $this->embedded_lookup_schema(),
				'location'         => $this->embedded_lookup_schema(),
				'reporting_to'     => [
					'type'       => [ 'object', 'null' ],
					'properties' => [
						'id'        => [ 'type' => 'integer' ],
						'full_name' => [ 'type' => 'string' ],
					],
				],
				'phone'            => [ 'type' => [ 'string', 'null' ] ],
				'pay_type'         => [ 'type' => [ 'string', 'null' ] ],
				'extra'            => [
					'type'                 => 'object',
					'additionalProperties' => true,
				],
			],
		];
	}

	/**
	 * Schema for an embedded `{ id, name }` lookup (department / designation / location).
	 *
	 * @return array
	 */
	private function embedded_lookup_schema(): array {
		return [
			'type'       => [ 'object', 'null' ],
			'properties' => [
				'id'   => [ 'type' => 'integer' ],
				'name' => [ 'type' => 'string' ],
			],
		];
	}
}
