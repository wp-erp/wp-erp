<?php
/**
 * WP-ERP HR — `erp/v2/company-locations` REST controller.
 *
 * Endpoints:
 *   GET  /erp/v2/company-locations — id + title list for the Location selects.
 *   POST /erp/v2/company-locations — create a work location.
 *
 * Work locations belong to the company (`erp_company_locations`), not to HR, and
 * were previously reachable only from the legacy Company screen — its
 * `wp_ajax_erp-company-location` handler. That left the React employee form with
 * a dead-end Location dropdown on a fresh site. This exposes the same model call
 * (`Company::create_location()`, so its validation and the `erp_company_location_*`
 * hooks keep firing) to the v2 namespace the React admin speaks, behind the same
 * `manage_options` gate that guards the Company page itself.
 */

namespace WeDevs\ERP\HRM\API\V2;

use WeDevs\ERP\Company;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

\defined( 'ABSPATH' ) || exit;

class CompanyLocationsControllerV2 extends RestControllerV2 {

	/**
	 * @var string
	 */
	protected $rest_base = 'company-locations';

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
	}

	/**
	 * Reading the list is what every employee/leave form needs to fill its
	 * Location select, so it rides the same gate as the rest of those forms.
	 *
	 * @return bool
	 */
	public function permission_view(): bool {
		return $this->permission_cap( 'erp_view_list' );
	}

	/**
	 * Creating a location writes to the company record. The legacy Company page
	 * (`AdminMenu.php` → `erp-company`) gates on `manage_options`; match it.
	 *
	 * @return bool
	 */
	public function permission_manage(): bool {
		return $this->permission_cap( 'manage_options' );
	}

	/**
	 * GET /erp/v2/company-locations
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_items( $request ) {
		$items = [];

		foreach ( (array) erp_company_get_location_dropdown_raw() as $id => $title ) {
			$items[] = [
				'id'    => (int) $id,
				'title' => (string) $title,
			];
		}

		return rest_ensure_response( $items );
	}

	/**
	 * POST /erp/v2/company-locations
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
	public function create_item( $request ) {
		$company     = new Company();
		$location_id = $company->create_location(
			[
				'id'        => 0,
				'name'      => sanitize_text_field( (string) $request['name'] ),
				'address_1' => sanitize_text_field( (string) $request['address_1'] ),
				'address_2' => sanitize_text_field( (string) ( $request['address_2'] ?? '' ) ),
				'city'      => sanitize_text_field( (string) ( $request['city'] ?? '' ) ),
				'state'     => sanitize_text_field( (string) ( $request['state'] ?? '' ) ),
				'zip'       => sanitize_text_field( (string) ( $request['zip'] ?? '' ) ),
				'country'   => sanitize_text_field( (string) $request['country'] ),
			]
		);

		if ( is_wp_error( $location_id ) ) {
			return new \WP_Error(
				$location_id->get_error_code() ?: 'rest_company_location_error',
				$location_id->get_error_message() ?: __( 'The location could not be saved.', 'erp' ),
				[ 'status' => 400 ]
			);
		}

		$response = rest_ensure_response(
			[
				'id'    => (int) $location_id,
				'title' => sanitize_text_field( (string) $request['name'] ),
			]
		);
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Write params. Mirrors `Company::create_location()`'s own requirements —
	 * name, address line 1 and country are the three it refuses to save without.
	 *
	 * @return array
	 */
	public function get_write_params(): array {
		$text = static function ( $description, $required = false ) {
			return [
				'description'       => $description,
				'type'              => 'string',
				'required'          => $required,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			];
		};

		return [
			'name'      => $text( __( 'Location name.', 'erp' ), true ),
			'address_1' => $text( __( 'Address line 1.', 'erp' ), true ),
			'address_2' => $text( __( 'Address line 2.', 'erp' ) ),
			'city'      => $text( __( 'City.', 'erp' ) ),
			'state'     => $text( __( 'State / province code.', 'erp' ) ),
			'zip'       => $text( __( 'Post code / ZIP.', 'erp' ) ),
			'country'   => $text( __( 'Country code.', 'erp' ), true ),
		];
	}

	/**
	 * JSON Schema for a single location option.
	 *
	 * @return array
	 */
	public function get_item_schema(): array {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'company_location',
			'type'       => 'object',
			'properties' => [
				'id'    => [
					'description' => __( 'Unique location ID.', 'erp' ),
					'type'        => 'integer',
					'context'     => [ 'view' ],
					'readonly'    => true,
				],
				'title' => [
					'description' => __( 'Location name.', 'erp' ),
					'type'        => 'string',
					'context'     => [ 'view' ],
				],
			],
		];
	}
}
