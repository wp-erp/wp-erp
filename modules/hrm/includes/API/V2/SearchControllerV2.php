<?php
/**
 * WP-ERP HR — `erp/v2/search` global-search controller.
 *
 * Powers the top-bar command palette. A single query fans out across the free
 * HR masters — employees, departments, designations — each section gated by the
 * same capability its dedicated list endpoint uses, and each delegating to the
 * shared v1 helpers (`erp_hr_get_employees`, `erp_hr_get_departments`,
 * `erp_hr_get_designations`) so existing hooks/caches apply. Read-only.
 */

namespace WeDevs\ERP\HRM\API\V2;

use WeDevs\ERP\HRM\Department;
use WeDevs\ERP\HRM\Employee;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

class SearchControllerV2 extends RestControllerV2 {

	/**
	 * Rest base.
	 *
	 * @var string
	 */
	protected $rest_base = 'search';

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
					'callback'            => [ $this, 'search' ],
					'permission_callback' => [ $this, 'permission_logged_in' ],
					'args'                => [
						'q' => [
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
						],
						'limit' => [
							'type'    => 'integer',
							'default' => 5,
							'minimum' => 1,
							'maximum' => 10,
						],
					],
				],
			]
		);
	}

	/**
	 * GET /erp/v2/search?q=…
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function search( WP_REST_Request $request ): WP_REST_Response {
		$query = trim( (string) $request->get_param( 'q' ) );
		$limit = (int) ( $request->get_param( 'limit' ) ?? 5 );
		$limit = max( 1, min( 10, $limit ) );

		$payload = [
			'employees'    => [],
			'departments'  => [],
			'designations' => [],
		];

		if ( $query === '' ) {
			return rest_ensure_response( $payload );
		}

		if ( current_user_can( 'erp_list_employee' ) ) {
			$payload['employees'] = $this->search_employees( $query, $limit );
		}

		if ( current_user_can( 'erp_view_list' ) ) {
			$payload['departments']  = $this->filter_by_title( (array) erp_hr_get_departments( [ 'number' => -1 ] ), $query, $limit );
			$payload['designations'] = $this->filter_by_title( (array) erp_hr_get_designations( [ 'number' => -1 ] ), $query, $limit );
		}

		return rest_ensure_response( $payload );
	}

	/**
	 * Search employees by name via the shared v1 helper.
	 *
	 * @param string $query Search term.
	 * @param int    $limit Max rows.
	 *
	 * @return array
	 */
	private function search_employees( string $query, int $limit ): array {
		$args = [
			'number'      => $limit,
			'offset'      => 0,
			'status'      => 'active',
			'department'  => -1,
			'designation' => -1,
			'location'    => -1,
			'type'        => -1,
			's'           => $query,
		];

		$results = [];
		foreach ( (array) erp_hr_get_employees( $args ) as $employee ) {
			if ( ! ( $employee instanceof Employee ) ) {
				continue;
			}
			$results[] = [
				'id'       => (int) $employee->get_user_id(),
				'label'    => $this->cast_string_or_null( $employee->get_full_name() ) ?? '',
				'sublabel' => $this->cast_string_or_null( $employee->get_designation( 'view' ) ) ?? '',
				'avatar'   => $employee->get_avatar_url( 40 ) ?: '',
			];
		}

		return $results;
	}

	/**
	 * Case-insensitive title match over a list of department/designation
	 * objects, capped to `$limit`. Designations come back as plain objects and
	 * departments as `Department` instances — both expose `id` + `title`.
	 *
	 * @param array  $items Objects with `id` + `title`.
	 * @param string $query Search term.
	 * @param int    $limit Max rows.
	 *
	 * @return array
	 */
	private function filter_by_title( array $items, string $query, int $limit ): array {
		$needle  = function_exists( 'mb_strtolower' ) ? mb_strtolower( $query ) : strtolower( $query );
		$matches = [];

		foreach ( $items as $item ) {
			if ( $item instanceof Department ) {
				$id    = (int) $item->id;
				$title = (string) $item->title;
			} elseif ( is_object( $item ) ) {
				$id    = (int) ( $item->id ?? 0 );
				$title = (string) ( $item->title ?? '' );
			} else {
				continue;
			}

			if ( $title === '' || $id === 0 ) {
				continue;
			}

			$haystack = function_exists( 'mb_strtolower' ) ? mb_strtolower( $title ) : strtolower( $title );
			if ( strpos( $haystack, $needle ) === false ) {
				continue;
			}

			$matches[] = [
				'id'    => $id,
				'label' => $title,
			];

			if ( count( $matches ) >= $limit ) {
				break;
			}
		}

		return $matches;
	}
}
