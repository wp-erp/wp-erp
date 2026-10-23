<?php
/**
 * WP-ERP HR — `erp/v2/countries` REST controller.
 *
 * Endpoints:
 *   GET /erp/v2/countries               — `[ { value, label } ]` for the country selects.
 *   GET /erp/v2/countries/{code}/states — `[ { value, label } ]` for one country's states.
 *
 * The lists used to ride the boot payload, which put about 170 KB of country and
 * state names into every HR admin page although only the employee and work
 * location forms read them. The forms now fetch them when they open.
 *
 * Countries and states are public reference data, so any logged-in user may read
 * them (an employee editing their own address needs them too).
 */

namespace WeDevs\ERP\HRM\API\V2;

use WeDevs\ERP\Countries;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

\defined( 'ABSPATH' ) || exit;

class CountriesController extends RestController {

	/**
	 * @var string
	 */
	protected $rest_base = 'countries';

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
					'permission_callback' => [ $this, 'permission_logged_in' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<code>[A-Za-z]{2})/states',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_states' ],
					'permission_callback' => [ $this, 'permission_logged_in' ],
				],
			]
		);
	}

	/**
	 * GET /erp/v2/countries
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_items( $request ) {
		if ( ! class_exists( '\WeDevs\ERP\Countries' ) ) {
			return rest_ensure_response( [] );
		}

		return rest_ensure_response( self::to_options( Countries::instance()->get_countries() ) );
	}

	/**
	 * GET /erp/v2/countries/{code}/states
	 *
	 * A country without states answers an empty list, so the form falls back to
	 * a free-text state field.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_states( $request ) {
		if ( ! class_exists( '\WeDevs\ERP\Countries' ) ) {
			return rest_ensure_response( [] );
		}

		$code   = strtoupper( sanitize_text_field( (string) $request['code'] ) );
		$states = Countries::instance()->get_states( $code );

		return rest_ensure_response( is_array( $states ) ? self::to_options( $states ) : [] );
	}

	/**
	 * Map `code => name` to `[ { value, label } ]`.
	 *
	 * The source names are HTML-encoded (`&#197;land Islands`), which the legacy
	 * PHP `<select>` got decoded for free. React escapes text, so decode once here.
	 *
	 * @param mixed $list Code => name map.
	 *
	 * @return array
	 */
	private static function to_options( $list ): array {
		$options = [];

		foreach ( (array) $list as $code => $name ) {
			$options[] = [
				'value' => (string) $code,
				'label' => html_entity_decode( (string) $name, ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
			];
		}

		return $options;
	}
}
