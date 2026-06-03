<?php
/**
 * WP-ERP HR — `erp/v2/holidays` REST controller.
 *
 * Endpoints:
 *   GET    /erp/v2/holidays              — paginated holiday list (search / date / year filters).
 *   POST   /erp/v2/holidays              — create a holiday.
 *   GET    /erp/v2/holidays/{id}         — single holiday.
 *   PUT    /erp/v2/holidays/{id}         — update a holiday.
 *   DELETE /erp/v2/holidays/{id}         — delete a holiday.
 *   POST   /erp/v2/holidays/parse        — parse an uploaded ICS/CSV file → preview rows (no insert).
 *   POST   /erp/v2/holidays/import       — bulk-insert an array of preview rows.
 *
 * Mirrors the legacy AJAX handlers `AjaxHandler::holiday_create()`,
 * `get_holiday()`, `holiday_remove()`, `import_ical()` and `import_holiday()` —
 * same `erp_leave_manage` cap, same `erp_hr_leave_insert_holiday()` /
 * `erp_hr_get_holidays()` / `erp_hr_delete_holidays()` model calls, same
 * `range`/`end_date` 23:59:59 normalisation, same `erp_hr_new_holiday` /
 * `erp_hr_*_update_holiday` / `erp_hr_leave_holiday_delete` hooks (fired inside
 * the model). Only the request/response envelope is the modern v2 contract.
 * `erp/v1` and the AJAX layer stay untouched.
 */

namespace WeDevs\ERP\HRM\API\V2;

use WeDevs\ERP\HRM\Models\LeaveHoliday;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

\defined( 'ABSPATH' ) || exit;

class HolidaysControllerV2 extends RestControllerV2 {

	/**
	 * @var string
	 */
	protected $rest_base = 'holidays';

	/**
	 * Allowed orderby keys (whitelisted against the model query).
	 */
	private const ORDERBY = [ 'id', 'title', 'start', 'end', 'created_at' ];

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
			'/' . $this->rest_base . '/parse',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'parse_file' ],
					'permission_callback' => [ $this, 'permission_manage' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/import',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'import_items' ],
					'permission_callback' => [ $this, 'permission_manage' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			[
				'args' => [
					'id' => [
						'description'       => __( 'Unique holiday ID.', 'erp' ),
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
	 * Listing the holiday-management page requires `erp_leave_manage` — the same
	 * cap the legacy `AdminMenu` Holidays submenu uses. (Employee-facing holiday
	 * data is served by the separate leave-calendar endpoint, not this admin
	 * list.)
	 *
	 * @return bool
	 */
	public function permission_view(): bool {
		return $this->permission_cap( 'erp_leave_manage' );
	}

	/**
	 * Create / update / delete / import require the leave-management capability —
	 * same gate as every legacy holiday AJAX handler.
	 *
	 * @return bool
	 */
	public function permission_manage(): bool {
		return $this->permission_cap( 'erp_leave_manage' );
	}

	/**
	 * GET /erp/v2/holidays
	 *
	 * Filters: `search` (title/description LIKE), `year` (calendar year →
	 * Jan 1 .. Dec 31), or explicit `from` / `to` (override `year`).
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_items( $request ): WP_REST_Response {
		$page     = max( 1, (int) ( $request['page'] ?? 1 ) );
		$per_page = max( 1, min( 100, (int) ( $request['per_page'] ?? 20 ) ) );

		$orderby = $this->cast_enum( (string) ( $request['orderby'] ?? 'start' ), self::ORDERBY ) ?? 'start';
		$order   = strtoupper( (string) ( $request['order'] ?? 'asc' ) );
		$order   = \in_array( $order, [ 'ASC', 'DESC' ], true ) ? $order : 'ASC';

		$args = [
			'number'  => $per_page,
			'offset'  => ( $page - 1 ) * $per_page,
			'orderby' => $orderby,
			'order'   => $order,
			's'       => sanitize_text_field( (string) ( $request['search'] ?? '' ) ),
		];

		[ $from, $to ] = $this->resolve_date_window( $request );
		if ( '' !== $from ) {
			$args['from'] = $from;
		}
		if ( '' !== $to ) {
			$args['to'] = $to;
		}

		$holidays = (array) erp_hr_get_holidays( $args );
		$total    = (int) erp_hr_count_holidays( $args );

		$items = [];
		foreach ( $holidays as $holiday ) {
			$items[] = $this->prepare_item_for_response( $holiday, $request );
		}

		$response = rest_ensure_response( $items );
		return $this->paginate( $response, $request, $total );
	}

	/**
	 * GET /erp/v2/holidays/{id}
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function get_item( $request ) {
		$id       = (int) $request['id'];
		$holidays = (array) erp_hr_get_holidays( [ 'id' => $id, 'number' => -1 ] );
		$holiday  = reset( $holidays );

		if ( empty( $holiday ) ) {
			return new \WP_Error( 'rest_holiday_invalid_id', __( 'Invalid holiday id.', 'erp' ), [ 'status' => 404 ] );
		}

		return rest_ensure_response( $this->prepare_item_for_response( $holiday, $request ) );
	}

	/**
	 * POST /erp/v2/holidays
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function create_item( $request ) {
		$id = erp_hr_leave_insert_holiday( $this->prepare_item_for_database( $request ) );

		if ( is_wp_error( $id ) ) {
			return $this->to_rest_error( $id );
		}

		$holidays = (array) erp_hr_get_holidays( [ 'id' => (int) $id, 'number' => -1 ] );
		$response = rest_ensure_response( $this->prepare_item_for_response( reset( $holidays ), $request ) );
		$response->set_status( 201 );
		$response->header(
			'Location',
			rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, (int) $id ) )
		);

		return $response;
	}

	/**
	 * PUT /erp/v2/holidays/{id}
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function update_item( $request ) {
		$holiday_id = (int) $request['id'];

		$existing = (array) erp_hr_get_holidays( [ 'id' => $holiday_id, 'number' => -1 ] );
		if ( empty( reset( $existing ) ) ) {
			return new \WP_Error( 'rest_holiday_invalid_id', __( 'Invalid holiday id.', 'erp' ), [ 'status' => 404 ] );
		}

		$data       = $this->prepare_item_for_database( $request );
		$data['id'] = $holiday_id;

		$id = erp_hr_leave_insert_holiday( $data );

		if ( is_wp_error( $id ) ) {
			return $this->to_rest_error( $id );
		}

		$holidays = (array) erp_hr_get_holidays( [ 'id' => $holiday_id, 'number' => -1 ] );
		return rest_ensure_response( $this->prepare_item_for_response( reset( $holidays ), $request ) );
	}

	/**
	 * DELETE /erp/v2/holidays/{id}
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		$holiday_id = (int) $request['id'];

		$existing = (array) erp_hr_get_holidays( [ 'id' => $holiday_id, 'number' => -1 ] );
		if ( empty( reset( $existing ) ) ) {
			return new \WP_Error( 'rest_holiday_invalid_id', __( 'Invalid holiday id.', 'erp' ), [ 'status' => 404 ] );
		}

		erp_hr_delete_holidays( [ 'id' => $holiday_id ] );

		return rest_ensure_response( [ 'deleted' => true, 'id' => $holiday_id ] );
	}

	/**
	 * POST /erp/v2/holidays/parse
	 *
	 * Parse an uploaded `.ics` / `.csv` file into preview rows (current calendar
	 * year only, duplicates skipped) — mirrors `AjaxHandler::import_ical()`. Does
	 * NOT persist anything; the client posts the chosen rows back to `/import`.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function parse_file( $request ) {
		unset( $request );

		if ( empty( $_FILES['file']['tmp_name'] ) ) {
			return new \WP_Error( 'rest_no_file', __( 'File upload error!', 'erp' ), [ 'status' => 400 ] );
		}

		$first_day_of_year = strtotime( gmdate( 'Y-01-01 00:00:00' ) );
		$last_day_of_year  = strtotime( gmdate( 'Y-12-31 23:59:59' ) );

		$holiday_model = new LeaveHoliday();
		$temp_name     = sanitize_url( wp_unslash( $_FILES['file']['tmp_name'] ) );

		// CSV branch — delegate to the shared parser used by the legacy handler.
		$mimes = [ 'application/vnd.ms-excel', 'text/csv' ];
		$type  = isset( $_FILES['file']['type'] ) ? sanitize_mime_type( wp_unslash( $_FILES['file']['type'] ) ) : '';

		if ( \in_array( $type, $mimes, true ) ) {
			$parsed = import_holidays_csv( $temp_name );

			// import_holidays_csv() returns either a string error message or a
			// rows array; normalise to the v2 preview shape.
			if ( \is_array( $parsed ) ) {
				return rest_ensure_response( [ 'rows' => array_values( $parsed ) ] );
			}

			return rest_ensure_response( [ 'rows' => [], 'message' => (string) $parsed ] );
		}

		// ICS branch.
		if ( ! class_exists( '\ICal' ) && ! class_exists( '\ICal\ICal' ) ) {
			return new \WP_Error( 'rest_ical_unavailable', __( 'iCal parser unavailable.', 'erp' ), [ 'status' => 500 ] );
		}

		$ical_class = class_exists( '\ICal\ICal' ) ? '\ICal\ICal' : '\ICal';
		$ical       = new $ical_class( $temp_name );
		$events     = $ical->events();
		$rows       = [];

		foreach ( $events as $event ) {
			$start = strtotime( $event['DTSTART'] );
			$end   = strtotime( $event['DTEND'] );

			if ( $start < $first_day_of_year || $end > $last_day_of_year ) {
				continue;
			}

			$title       = sanitize_text_field( wp_unslash( $event['SUMMARY'] ) );
			$start       = gmdate( 'Y-m-d 00:00:00', $start );
			$end         = gmdate( 'Y-m-d 23:59:59', $end );
			$description = ( ! empty( $event['DESCRIPTION'] ) ) ? sanitize_text_field( wp_unslash( $event['DESCRIPTION'] ) ) : $title;

			$dup = $holiday_model->where( 'title', '=', $title )->where( 'start', '=', $start );
			if ( $dup->count() ) {
				continue;
			}

			$days = erp_date_duration( $start, $end );

			$rows[] = [
				'title'       => $title,
				'start'       => $start,
				'end'         => $end,
				'description' => $description,
				'duration'    => $days . ' ' . _n( 'day', 'days', $days, 'erp' ),
			];
		}

		return rest_ensure_response( [ 'rows' => $rows ] );
	}

	/**
	 * POST /erp/v2/holidays/import
	 *
	 * Bulk-insert preview rows. Body: `{ holidays: [ { title, start, end,
	 * description } ] }`. Mirrors `AjaxHandler::import_holiday()` — each row goes
	 * through `erp_hr_leave_insert_holiday()`, failures collected by index.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function import_items( $request ) {
		$rows = $request['holidays'];

		if ( ! \is_array( $rows ) || empty( $rows ) ) {
			return new \WP_Error( 'rest_no_rows', __( 'No holiday data provided.', 'erp' ), [ 'status' => 400 ] );
		}

		$imported = 0;
		$failed   = [];

		foreach ( array_values( $rows ) as $index => $row ) {
			$id = erp_hr_leave_insert_holiday(
				[
					'title'       => isset( $row['title'] ) ? sanitize_text_field( $row['title'] ) : '',
					'start'       => isset( $row['start'] ) ? sanitize_text_field( $row['start'] ) : '',
					'end'         => isset( $row['end'] ) ? sanitize_text_field( $row['end'] ) : '',
					'description' => isset( $row['description'] ) ? sanitize_text_field( $row['description'] ) : '',
				]
			);

			if ( is_wp_error( $id ) ) {
				$failed[] = (int) $index + 1;
			} else {
				++$imported;
			}
		}

		return rest_ensure_response(
			[
				'imported' => $imported,
				'failed'   => $failed,
				'total'    => \count( $rows ),
			]
		);
	}

	/**
	 * Resolve the `from` / `to` date window from `from`/`to`/`year` request args.
	 *
	 * Explicit `from`/`to` win; otherwise a `year` expands to Jan 1 .. Dec 31.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return array{0:string,1:string} [ from, to ] in `Y-m-d H:i:s`, or '' each.
	 */
	private function resolve_date_window( $request ): array {
		$from = sanitize_text_field( (string) ( $request['from'] ?? '' ) );
		$to   = sanitize_text_field( (string) ( $request['to'] ?? '' ) );

		if ( '' !== $from || '' !== $to ) {
			return [
				'' !== $from ? gmdate( 'Y-m-d 00:00:00', strtotime( $from ) ) : '',
				'' !== $to ? gmdate( 'Y-m-d 23:59:59', strtotime( $to ) ) : '',
			];
		}

		$year = (int) ( $request['year'] ?? 0 );
		if ( $year >= 1970 && $year <= 9999 ) {
			return [
				sprintf( '%04d-01-01 00:00:00', $year ),
				sprintf( '%04d-12-31 23:59:59', $year ),
			];
		}

		return [ '', '' ];
	}

	/**
	 * Map the flat v2 payload onto the args `erp_hr_leave_insert_holiday()` expects.
	 *
	 * Replicates the AJAX `range`/`end_date` normalisation: when `range` is off
	 * (single-day) the end date collapses to the start day at 23:59:59; otherwise
	 * the supplied end date is pinned to 23:59:59.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return array
	 */
	protected function prepare_item_for_database( $request ): array {
		$title      = isset( $request['title'] ) ? sanitize_text_field( $request['title'] ) : '';
		$start_date = isset( $request['start'] ) ? sanitize_text_field( $request['start'] ) : '';
		$desc       = isset( $request['description'] ) ? sanitize_text_field( $request['description'] ) : '';
		$range      = $this->cast_bool( $request['range'] ?? false );

		$end_raw  = ( isset( $request['end'] ) && '' !== $request['end'] ) ? sanitize_text_field( $request['end'] ) : $start_date;
		$end_date = $range
			? gmdate( 'Y-m-d 23:59:59', strtotime( $end_raw ) )
			: gmdate( 'Y-m-d 23:59:59', strtotime( $start_date ) );

		return [
			'title'       => $title,
			'start'       => $start_date,
			'end'         => $end_date,
			'description' => $desc,
		];
	}

	/**
	 * Reshape a holiday row (stdClass from `erp_hr_get_holidays()`) into the v2 row.
	 *
	 * @param mixed           $holiday A holiday stdClass / array.
	 * @param WP_REST_Request $request Request.
	 *
	 * @return array
	 */
	public function prepare_item_for_response( $holiday, $request ) {
		unset( $request );

		$holiday = (object) $holiday;

		$start = $this->cast_date_iso( $holiday->start ?? null );
		$end   = $this->cast_date_iso( $holiday->end ?? null );

		$duration = 0;
		if ( ! empty( $holiday->start ) && ! empty( $holiday->end ) ) {
			$duration = (int) erp_date_duration( $holiday->start, $holiday->end );
		}

		return [
			'id'          => (int) ( $holiday->id ?? 0 ),
			'title'       => $this->cast_string_or_null( $holiday->title ?? '' ) ?? '',
			'start'       => $start,
			'end'         => $end,
			'description' => $this->cast_string_or_null( $holiday->description ?? '' ) ?? '',
			'duration'    => $duration,
			'range'       => $start !== $end,
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
				'description'       => __( 'Holiday title.', 'erp' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'start'       => [
				'description'       => __( 'Start date (Y-m-d).', 'erp' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'end'         => [
				'description'       => __( 'End date (Y-m-d). Defaults to the start date for single-day holidays.', 'erp' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'range'       => [
				'description'       => __( 'Whether this is a multi-day (date-range) holiday.', 'erp' ),
				'type'              => 'boolean',
				'default'           => false,
			],
			'description' => [
				'description'       => __( 'Holiday description.', 'erp' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Collection params: pagination + sort + date filters.
	 *
	 * @return array
	 */
	public function get_collection_params(): array {
		$params = parent::get_collection_params();

		$params['orderby'] = [
			'description'       => __( 'Sort field.', 'erp' ),
			'type'              => 'string',
			'default'           => 'start',
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
		$params['year'] = [
			'description'       => __( 'Filter to a calendar year (Jan 1 – Dec 31).', 'erp' ),
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		];
		$params['from'] = [
			'description'       => __( 'Filter holidays starting on or after this date.', 'erp' ),
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		];
		$params['to'] = [
			'description'       => __( 'Filter holidays ending on or before this date.', 'erp' ),
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
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
			$error->get_error_code() ?: 'rest_holiday_error',
			$error->get_error_message() ?: __( 'The holiday could not be saved.', 'erp' ),
			[ 'status' => $status ]
		);
	}

	/**
	 * JSON Schema for a single holiday.
	 *
	 * @return array
	 */
	public function get_item_schema(): array {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'holiday',
			'type'       => 'object',
			'properties' => [
				'id'          => [ 'type' => 'integer' ],
				'title'       => [ 'type' => 'string' ],
				'start'       => [ 'type' => [ 'string', 'null' ] ],
				'end'         => [ 'type' => [ 'string', 'null' ] ],
				'description' => [ 'type' => 'string' ],
				'duration'    => [ 'type' => 'integer' ],
				'range'       => [ 'type' => 'boolean' ],
			],
		];
	}
}
