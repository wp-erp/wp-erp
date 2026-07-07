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

class FinancialYearsControllerV2 extends RestControllerV2 {

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
	 * from the payload (skipped when the year is still FK-referenced).
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

		$result = erp_settings_save_leave_years( $clean );

		if ( is_wp_error( $result ) ) {
			return new \WP_Error( 'rest_financial_year_error', $result->get_error_message(), [ 'status' => 400 ] );
		}

		return $this->get_items( $request );
	}
}
