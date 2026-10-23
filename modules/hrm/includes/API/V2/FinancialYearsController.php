<?php
/**
 * WP-ERP HR — `erp/v2/financial-years` REST controller.
 *
 * Restores the legacy HR Settings → "Leave Years" screen (Vue `HRLeaveYears.vue`
 * + `AjaxHandler::erp_settings_get_hr_financial_years()` / `_save_*()`), which
 * had no React surface after the redesign.
 *
 * Endpoints:
 *   GET  /erp/v2/financial-years   — list every financial year (id, name, dates).
 *   POST /erp/v2/financial-years   — replace the whole set (full-table save).
 *
 * Both delegate to the unchanged helpers `erp_get_hr_financial_years()` and
 * `erp_settings_save_leave_years()` — so the same validation (name required,
 * end > start, unique name) keeps firing. Save is an ID-stable upsert: existing
 * years are updated in place (ids never move, so `f_year` FK links survive) and
 * only payload-omitted, unreferenced years are deleted. `erp/v1` stays untouched.
 */

namespace WeDevs\ERP\HRM\API\V2;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

\defined( 'ABSPATH' ) || exit;

class FinancialYearsController extends RestController {

	/**
	 * @var string
	 */
	protected $rest_base = 'financial-years';

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
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'save_items' ],
					'permission_callback' => [ $this, 'permission_manage' ],
				],
			]
		);
	}

	/**
	 * Managing financial years matches the legacy gate
	 * (`manage_options` OR `erp_hr_manager`).
	 *
	 * @return bool
	 */
	public function permission_manage(): bool {
		return current_user_can( 'manage_options' ) || current_user_can( 'erp_hr_manager' );
	}

	/**
	 * GET /erp/v2/financial-years
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_items( $request ): WP_REST_Response {
		unset( $request );

		$rows  = (array) erp_get_hr_financial_years();
		$items = [];

		foreach ( $rows as $row ) {
			$items[] = [
				'id'          => isset( $row['id'] ) ? (int) $row['id'] : 0,
				'fy_name'     => isset( $row['fy_name'] ) ? (string) $row['fy_name'] : '',
				'start_date'  => isset( $row['start_date'] ) ? (string) $row['start_date'] : '',
				'end_date'    => isset( $row['end_date'] ) ? (string) $row['end_date'] : '',
				'description' => isset( $row['description'] ) ? (string) $row['description'] : '',
			];
		}

		return rest_ensure_response( $items );
	}

	/**
	 * POST /erp/v2/financial-years
	 *
	 * Full-set save via ID-stable upsert — deleting a year = omitting its row
	 * from the payload (refused with a 409 when the year is still FK-referenced).
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function save_items( $request ) {
		$years = $request['years'] ?? [];

		if ( ! \is_array( $years ) || empty( $years ) ) {
			return new \WP_Error( 'rest_financial_year_required', __( 'Financial year is required', 'erp' ), [ 'status' => 400 ] );
		}

		$clean = [];
		foreach ( $years as $year ) {
			$clean[] = [
				'id'          => isset( $year['id'] ) ? absint( $year['id'] ) : 0,
				'fy_name'     => sanitize_text_field( (string) ( $year['fy_name'] ?? '' ) ),
				'start_date'  => sanitize_text_field( (string) ( $year['start_date'] ?? '' ) ),
				'end_date'    => sanitize_text_field( (string) ( $year['end_date'] ?? '' ) ),
				'description' => sanitize_text_field( (string) ( $year['description'] ?? '' ) ),
			];
		}

		$invalid = $this->validate_years( $clean );
		if ( $invalid ) {
			return $invalid;
		}

		$in_use = $this->in_use_removals( $clean );
		if ( $in_use ) {
			return $in_use;
		}

		$result = erp_settings_save_leave_years( $clean );

		if ( is_wp_error( $result ) ) {
			return new \WP_Error( 'rest_financial_year_error', $result->get_error_message(), [ 'status' => 400 ] );
		}

		return $this->get_items( $request );
	}

	/**
	 * A 409 naming the stored years the payload drops that leave records still
	 * use, else null. The legacy helper keeps such years (it never orphans an
	 * `f_year` link) but reports success, so the row silently came back.
	 *
	 * @param array $years Sanitized rows.
	 *
	 * @return \WP_Error|null
	 */
	private function in_use_removals( array $years ): ?\WP_Error {
		$kept  = array_filter( array_map( 'intval', wp_list_pluck( $years, 'id' ) ) );
		$names = [];

		foreach ( (array) erp_get_hr_financial_years() as $row ) {
			$id = (int) ( $row['id'] ?? 0 );

			if ( $id && ! \in_array( $id, $kept, true ) && erp_hr_financial_year_in_use( $id ) ) {
				$names[] = (string) ( $row['fy_name'] ?? $id );
			}
		}

		if ( empty( $names ) ) {
			return null;
		}

		return new \WP_Error(
			'rest_financial_year_in_use',
			/* translators: %s: comma separated financial year names */
			\sprintf( __( 'These financial years are used by leave policies, entitlements or requests and cannot be deleted: %s', 'erp' ), implode( ', ', $names ) ),
			[ 'status' => 409 ]
		);
	}

	/**
	 * Rules the legacy save helper does not check, as a 400 `WP_Error`, else null.
	 *
	 * - A date must be a real `Y-m-d` date: the helper hands it straight to
	 *   `new DateTimeImmutable()`, which throws (HTTP 500) on garbage.
	 * - Two years must not overlap: every leave date is matched to ONE financial
	 *   year, so an overlap makes a request's year (and its balance) ambiguous.
	 *
	 * Empty names/dates, end-before-start and duplicate names are left to the
	 * helper so its legacy messages keep firing.
	 *
	 * Only new rows and rows whose dates are changed are checked: two saved
	 * years that overlap and that nobody touched do not block the save, but a
	 * new or edited year must not overlap any other.
	 *
	 * @param array $years Sanitized rows.
	 *
	 * @return \WP_Error|null
	 */
	private function validate_years( array $years ): ?\WP_Error {
		$valid  = [];
		$stored = [];

		foreach ( (array) erp_get_hr_financial_years() as $row ) {
			$stored[ (int) ( $row['id'] ?? 0 ) ] = (array) $row;
		}

		foreach ( $years as $index => $year ) {
			if ( '' === $year['start_date'] || '' === $year['end_date'] ) {
				continue;
			}

			$year['touched'] = empty( $year['id'] ) || ! isset( $stored[ $year['id'] ] )
				|| $this->changed( $stored[ $year['id'] ], $year, [ 'start_date', 'end_date' ] );

			if ( ! $year['touched'] ) {
				if ( $year['end_date'] > $year['start_date'] ) {
					$valid[] = $year;
				}
				continue;
			}

			foreach ( [ $year['start_date'], $year['end_date'] ] as $date ) {
				$parsed = \DateTime::createFromFormat( '!Y-m-d', $date );
				if ( ! $parsed || $parsed->format( 'Y-m-d' ) !== $date ) {
					return new \WP_Error(
						'rest_financial_year_invalid_date',
						/* translators: %d: row number */
						\sprintf( __( 'Start and end date on row %d must be valid dates.', 'erp' ), (int) $index + 1 ),
						[ 'status' => 400 ]
					);
				}
			}

			if ( $year['end_date'] > $year['start_date'] ) {
				$valid[] = $year;
			}
		}

		$count = \count( $valid );
		for ( $i = 0; $i < $count; $i++ ) {
			for ( $j = $i + 1; $j < $count; $j++ ) {
				$a = $valid[ $i ];
				$b = $valid[ $j ];

				if ( ! $a['touched'] && ! $b['touched'] ) {
					continue;
				}

				if ( $a['start_date'] <= $b['end_date'] && $b['start_date'] <= $a['end_date'] ) {
					return new \WP_Error(
						'rest_financial_year_overlap',
						/* translators: 1: a financial year name, 2: another financial year name */
						\sprintf( __( 'Financial years %1$s and %2$s overlap. Each date can belong to one financial year only.', 'erp' ), $a['fy_name'], $b['fy_name'] ),
						[ 'status' => 400 ]
					);
				}
			}
		}

		return null;
	}
}
