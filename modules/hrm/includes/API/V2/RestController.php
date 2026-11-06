<?php
/**
 * WP-ERP HR — Abstract base class for `erp/v2` REST controllers.
 *
 * Centralises the v2 typing contract (int IDs, ISO-8601 dates, null for absent
 * optional fields, real booleans), pagination header emission, and the
 * permission-callback helpers. Every concrete v2 controller extends this.
 *
 * Contract verified at openspec/changes/redesign-hr-free/api-mapping.md
 * (Response typing contract).
 */

namespace WeDevs\ERP\HRM\API\V2;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;

\defined( 'ABSPATH' ) || exit;

abstract class RestController extends WP_REST_Controller {

	/**
	 * Namespace shared by every v2 controller.
	 *
	 * @var string
	 */
	protected $namespace = 'erp/v2';

	/**
	 * Default collection params shared by every list endpoint.
	 *
	 * Concrete controllers add domain-specific params on top of these.
	 *
	 * @return array
	 */
	public function get_collection_params(): array {
		return [
			'page'     => [
				'description'       => __( 'Current page of the collection.', 'erp' ),
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
				'minimum'           => 1,
			],
			'per_page' => [
				'description'       => __( 'Maximum number of items per page.', 'erp' ),
				'type'              => 'integer',
				'default'           => 20,
				'minimum'           => 1,
				'maximum'           => 100,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'search'   => [
				'description'       => __( 'Limit results to those matching a string.', 'erp' ),
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
		];
	}

	// ---------------------------------------------------------------------
	// Typing helpers — every prepare_item_for_response() in a subclass uses
	// these to enforce the v2 response contract.
	// ---------------------------------------------------------------------

	/**
	 * Cast a value to int, or null if empty/missing/non-numeric.
	 *
	 * @param mixed $value Raw value.
	 *
	 * @return int|null
	 */
	protected function cast_int_or_null( $value ): ?int {
		if ( $value === null || $value === '' ) {
			return null;
		}
		if ( ! is_numeric( $value ) ) {
			return null;
		}
		return (int) $value;
	}

	/**
	 * Cast a value to float, or null if empty/missing/non-numeric.
	 *
	 * @param mixed $value Raw value.
	 *
	 * @return float|null
	 */
	protected function cast_float_or_null( $value ): ?float {
		if ( $value === null || $value === '' ) {
			return null;
		}
		if ( ! is_numeric( $value ) ) {
			return null;
		}
		return (float) $value;
	}

	/**
	 * Cast a value to bool. Handles `'0' | '1' | 0 | 1 | true | false`.
	 *
	 * @param mixed $value Raw value.
	 *
	 * @return bool
	 */
	protected function cast_bool( $value ): bool {
		if ( \is_bool( $value ) ) {
			return $value;
		}
		if ( is_numeric( $value ) ) {
			return (int) $value === 1;
		}
		if ( \is_string( $value ) ) {
			$value = strtolower( $value );
			return \in_array( $value, [ '1', 'true', 'yes', 'on' ], true );
		}
		return (bool) $value;
	}

	/**
	 * Cast a MySQL date / date-time string to ISO-8601.
	 *
	 * Returns null for `''`, `null`, `'0000-00-00'`, `'0000-00-00 00:00:00'`,
	 * or any unparseable input. Dates without time become YYYY-MM-DD; date-times
	 * become full ISO-8601 with timezone.
	 *
	 * @param mixed $value Raw value.
	 *
	 * @return string|null
	 */
	protected function cast_date_iso( $value ): ?string {
		if ( $value === null || $value === '' ) {
			return null;
		}
		if ( ! \is_string( $value ) ) {
			return null;
		}

		$value = trim( $value );

		if ( $value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00' ) {
			return null;
		}

		$timestamp = strtotime( $value );
		if ( $timestamp === false ) {
			return null;
		}

		// Pure date (YYYY-MM-DD) — return the calendar day untouched. Running it
		// through a timestamp + `gmdate` roundtrip shifts it back a day for
		// positive-offset site timezones (off-by-one on the client).
		if ( strpos( $value, ' ' ) === false && strpos( $value, 'T' ) === false ) {
			return substr( $value, 0, 10 );
		}

		// Stored datetimes are site time (current_time( 'mysql' )). Stamping them
		// with gmdate() labelled them UTC, so on a UTC+6 site a note written at
		// 11:16 showed as 5:16 pm. Read them in the site timezone instead.
		try {
			return ( new \DateTimeImmutable( $value, wp_timezone() ) )->format( 'c' );
		} catch ( \Exception $e ) {
			return null;
		}
	}

	/**
	 * Cast a stored date boundary to its bare calendar day (`Y-m-d`).
	 *
	 * Holiday rows are stored as `Y-m-d 00:00:00` / `Y-m-d 23:59:59` in site
	 * time. Anything carrying a time component travels as an instant and gets
	 * re-interpreted in the viewer's timezone, so an end-of-day boundary lands
	 * on the following date (a 17–19 Aug holiday listed as "Aug 17 – Aug 20").
	 * A calendar day has no timezone; send it as one.
	 *
	 * @param mixed $value Stored datetime.
	 *
	 * @return string|null
	 */
	protected function cast_calendar_day( $value ): ?string {
		if ( ! \is_string( $value ) ) {
			return null;
		}

		$value = trim( $value );

		if ( $value === '' || strpos( $value, '0000-00-00' ) === 0 ) {
			return null;
		}

		$timestamp = strtotime( $value );

		return $timestamp === false ? null : gmdate( 'Y-m-d', $timestamp );
	}

	/**
	 * Cast a value to a non-empty string, or null when empty.
	 *
	 * @param mixed $value Raw value.
	 *
	 * @return string|null
	 */
	protected function cast_string_or_null( $value ): ?string {
		if ( $value === null ) {
			return null;
		}
		$value = \is_string( $value ) ? trim( $value ) : (string) $value;
		return $value === '' ? null : $value;
	}

	/**
	 * Cast a value to one of the allowed enum members, or null when invalid.
	 *
	 * @param mixed    $value   Raw value.
	 * @param string[] $allowed Allowed members.
	 *
	 * @return string|null
	 */
	protected function cast_enum( $value, array $allowed ): ?string {
		if ( ! \is_string( $value ) ) {
			return null;
		}
		return \in_array( $value, $allowed, true ) ? $value : null;
	}

	// ---------------------------------------------------------------------
	// Pagination helper.
	// ---------------------------------------------------------------------

	/**
	 * Attach `X-WP-Total` + `X-WP-TotalPages` headers to a list response.
	 *
	 * @param WP_REST_Response $response    The response.
	 * @param WP_REST_Request  $request     The request.
	 * @param int              $total_items Total item count (across all pages).
	 * @param int              $per_page    Page size the handler actually used, when it
	 *                                      clamped the request's (0 reads the request).
	 *
	 * @return WP_REST_Response
	 */
	protected function paginate( WP_REST_Response $response, WP_REST_Request $request, int $total_items, int $per_page = 0 ): WP_REST_Response {
		if ( $per_page < 1 ) {
			$per_page = (int) ( $request['per_page'] ?? 20 );
		}
		if ( $per_page < 1 ) {
			$per_page = 20;
		}

		$response->header( 'X-WP-Total', (string) $total_items );
		$response->header( 'X-WP-TotalPages', (string) (int) ceil( $total_items / $per_page ) );

		return $response;
	}

	// ---------------------------------------------------------------------
	// Update helpers.
	// ---------------------------------------------------------------------

	/**
	 * Whether an update changes any of `$fields` compared with the stored record.
	 *
	 * The value rules added for the React forms run on update only when one of
	 * the fields a rule reads is changed, so a record saved before a rule existed
	 * stays editable as long as those fields are left as they were. A field the
	 * request does not send keeps its stored value, so it is not a change.
	 * Values compare as trimmed strings, numbers by value and lists as sets.
	 *
	 * @param array $stored   Stored record (field => value).
	 * @param array $incoming Incoming values (field => value).
	 * @param array $fields   Fields the rule reads.
	 *
	 * @return bool
	 */
	protected function changed( array $stored, array $incoming, array $fields ): bool {
		$normalize = static function ( $value ) {
			if ( \is_array( $value ) || \is_object( $value ) ) {
				$list = array_map(
					static function ( $item ) {
						return \is_scalar( $item ) ? trim( (string) $item ) : wp_json_encode( $item );
					},
					array_values( (array) $value )
				);
				sort( $list );

				return wp_json_encode( $list );
			}

			$value = null === $value ? '' : trim( (string) $value );

			return is_numeric( $value ) ? (string) (float) $value : $value;
		};

		foreach ( $fields as $field ) {
			if ( ! \array_key_exists( $field, $incoming ) ) {
				continue;
			}

			if ( $normalize( $incoming[ $field ] ) !== $normalize( $stored[ $field ] ?? '' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * The positive ids in a bulk request.
	 *
	 * `absint()` turns -1 into 1, so a bulk delete of `[-1]` deleted row 1.
	 * Anything that is not a positive whole number is dropped instead.
	 *
	 * @param mixed $raw The posted `ids`.
	 *
	 * @return int[]
	 */
	protected function positive_ids( $raw ): array {
		$ids = [];

		foreach ( (array) $raw as $value ) {
			if ( \is_int( $value ) || ( \is_string( $value ) && ctype_digit( $value ) ) ) {
				$id = (int) $value;

				if ( $id > 0 ) {
					$ids[] = $id;
				}
			}
		}

		return array_values( array_unique( $ids ) );
	}

	// ---------------------------------------------------------------------
	// Permission helpers.
	// ---------------------------------------------------------------------

	/**
	 * Permission helper — must be logged in.
	 *
	 * Public because controllers register it directly as a `permission_callback`
	 * (WP REST invokes it from outside the class — a protected method fatals).
	 *
	 * @return bool
	 */
	public function permission_logged_in(): bool {
		return is_user_logged_in();
	}

	/**
	 * Permission helper — current user has the given capability.
	 *
	 * @param string $cap Capability key.
	 * @param mixed  $arg Optional argument (e.g. an object ID for meta caps).
	 *
	 * @return bool
	 */
	public function permission_cap( string $cap, $arg = null ): bool {
		return $arg !== null
			? current_user_can( $cap, $arg )
			: current_user_can( $cap );
	}

	/**
	 * Build a small avatar-stack preview for an org column (department /
	 * designation). Returns up to `$limit` active employees as `{ name, avatar }`
	 * so the list UI can render an overlapping avatar group + "+N" overflow.
	 *
	 * Efficient by design: queries only the first `$limit` user IDs for the
	 * column (not the whole roster) and resolves one avatar URL each.
	 *
	 * @param string $column Employee column to match (`department`|`designation`).
	 * @param int    $id     Department / designation ID.
	 * @param int    $limit  Max previews to return.
	 *
	 * @return array<int, array{name: string, avatar: ?string}>
	 */
	protected function employee_previews( string $column, int $id, int $limit = 3 ): array {
		if ( $id <= 0 ) {
			return [];
		}

		$rows = \WeDevs\ERP\HRM\Models\Employee::where( [ 'status' => 'active', $column => $id ] )
			->take( $limit )
			->get( [ 'user_id' ] );

		$previews = [];
		foreach ( $rows as $row ) {
			$employee   = new \WeDevs\ERP\HRM\Employee( (int) $row->user_id );
			$previews[] = [
				'name'   => (string) $employee->get_full_name(),
				'avatar' => $employee->get_avatar_url( 40 ) ?: null,
			];
		}

		return $previews;
	}
}
