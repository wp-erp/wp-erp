<?php
/**
 * WP-ERP HR — `erp/v2/org-chart` REST controller.
 *
 * Serves the company organogram for the React Org Chart page. Faithful port of
 * the legacy pro Org Chart hierarchy (`WeDevs\ERP_PRO\Feature\HRM\Org_Chart\Helpers`):
 * the same department-lead rooting, "All Teams" multi-tree, "No Team" branch and
 * recursive `reporting_to` walk — re-homed in free so the page no longer depends
 * on the pro module.
 *
 * Endpoints:
 *   GET /erp/v2/org-chart           — full hierarchy + the department dropdown.
 *       ?dept_id=  ''  → all teams (multi-tree), -1 → no team, {id} → one team.
 */

namespace WeDevs\ERP\HRM\API\V2;

use WeDevs\ERP\HRM\Employee;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

\defined( 'ABSPATH' ) || exit;

class OrgChartControllerV2 extends RestControllerV2 {

	/**
	 * @var string
	 */
	protected $rest_base = 'org-chart';

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
					'callback'            => [ $this, 'get_chart' ],
					'permission_callback' => [ $this, 'permission_list' ],
					'args'                => [
						'dept_id' => [
							'description'       => __( 'Department filter: empty = all teams, -1 = no team, or a department id.', 'erp' ),
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						],
					],
				],
			]
		);
	}

	/**
	 * Same gate as the legacy Org Chart page (`erp_list_employee`).
	 *
	 * @return bool
	 */
	public function permission_list(): bool {
		return $this->permission_cap( 'erp_list_employee' );
	}

	/**
	 * GET /erp/v2/org-chart
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_chart( $request ): WP_REST_Response {
		$raw = $request->get_param( 'dept_id' );
		// Distinguish "all teams" ('' / null) from "no team" ('-1') from a real id.
		$dept_id = ( null === $raw || '' === $raw ) ? null : (int) $raw;

		return rest_ensure_response(
			[
				'tree'        => $this->get_employee_hierarchy( $dept_id ),
				'departments' => $this->get_dept_dropdown(),
			]
		);
	}

	/**
	 * Port of `Helpers::get_employee_hierarchy()`.
	 *
	 * @param int|null $dept_id Department id, -1 for no-team, null for all teams.
	 *
	 * @return array
	 */
	private function get_employee_hierarchy( $dept_id = null ): array {
		global $wpdb;

		if ( null === $dept_id ) {
			$data = [
				'id'        => 0,
				'name'      => '',
				'title'     => '',
				'lead'      => 0,
				'avatar'    => '',
				'dept_id'   => 0,
				'email'     => '',
				'is_array'  => true,
				'className' => 'no-content',
				'children'  => [],
			];

			$depts = $wpdb->get_results(
				"SELECT DISTINCT dept.id, dept.lead
				FROM {$wpdb->prefix}erp_hr_depts AS dept
				LEFT JOIN {$wpdb->prefix}erp_hr_employees AS emp
				ON dept.id = emp.department
				WHERE dept.status = 1
				AND emp.status = 'active'
				AND emp.deleted_at IS NULL"
			);

			foreach ( $depts as $dept ) {
				$data['children'][] = $this->sort_employees( (int) $dept->lead, (int) $dept->id );
			}

			$data['children'][] = $this->sort_employees( 0 );

			return $data;
		}

		if ( -1 === $dept_id ) {
			return $this->sort_employees( 0 );
		}

		$dept_lead = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT dept.lead FROM {$wpdb->prefix}erp_hr_depts AS dept WHERE dept.id = %d",
				$dept_id
			)
		);

		return $this->sort_employees( (int) $dept_lead, $dept_id );
	}

	/**
	 * Port of `Helpers::sort_employees()` — recursive `reporting_to` walk within a
	 * department.
	 *
	 * @param int $emp_id  Manager user id (0 for the synthetic top of a team).
	 * @param int $dept_id Department id.
	 * @param int $depth   Recursion depth (1 = department top).
	 *
	 * @return array
	 */
	private function sort_employees( int $emp_id, int $dept_id = 0, int $depth = 1 ): array {
		global $wpdb;

		$data = [
			'id'        => 0,
			'name'      => '',
			'title'     => '',
			'lead'      => 0,
			'avatar'    => '',
			'dept_id'   => $dept_id,
			'email'     => '',
			'className' => '',
			'children'  => [],
		];

		$where = [ "department = {$dept_id}" ];

		if ( ! $emp_id ) {
			$data['className'] .= ' no-content';

			if ( 1 === $depth ) {
				$where[] = "(
					reporting_to = 0
					OR reporting_to IS NULL
					OR reporting_to NOT IN (
						SELECT emp.user_id
						FROM {$wpdb->prefix}erp_hr_employees AS emp
						WHERE emp.department = {$dept_id}
						AND emp.status = 'active'
						AND emp.deleted_at IS NULL
					)
				)";

				$data['className'] .= ' no-parent';
			}
		} else {
			if ( 1 === $depth ) {
				$where[] = "( reporting_to = {$emp_id} OR reporting_to = 0 OR reporting_to IS NULL )";
				$where[] = "user_id != {$emp_id}";
			} else {
				$where[] = "reporting_to = {$emp_id}";
			}

			$manager = new Employee( $emp_id );

			if ( (int) $manager->get_user_id() ) {
				$wp_user        = get_userdata( $emp_id );
				$data['id']     = (int) $manager->get_user_id();
				$data['name']   = $manager->get_full_name();
				$data['title']  = $manager->get_job_title();
				$data['lead']   = (int) $manager->get_reporting_to();
				$data['avatar'] = $manager->get_avatar_url( 80 ) ?: '';
				$data['email']  = $wp_user ? $wp_user->user_email : '';
			}
		}

		$where[] = "status = 'active'";
		$where[] = "deleted_at IS NULL";

		$query   = "SELECT `user_id` FROM {$wpdb->prefix}erp_hr_employees WHERE " . implode( ' AND ', $where );
		$emp_ids = $wpdb->get_col( $query );

		foreach ( $emp_ids as $index => $id ) {
			$child = $this->sort_employees( (int) $id, $dept_id, $depth + 1 );

			if ( ! $emp_id ) {
				$child['className'] = 'no-parent';
			}

			$data['children'][ $index ] = $child;
		}

		return $data;
	}

	/**
	 * Port of `Helpers::get_dept_dropdown_raw()` → `[ { value, label } ]` options.
	 *
	 * @return array
	 */
	private function get_dept_dropdown(): array {
		global $wpdb;

		$options = [ [ 'value' => '', 'label' => __( 'All Teams', 'erp' ) ] ];

		$depts = $wpdb->get_results(
			"SELECT DISTINCT dept.id, dept.title
			FROM {$wpdb->prefix}erp_hr_depts AS dept
			LEFT JOIN {$wpdb->prefix}erp_hr_employees AS emp
			ON dept.id = emp.department
			WHERE dept.status = 1
			AND emp.status = 'active'
			AND emp.deleted_at IS NULL"
		);

		foreach ( $depts as $dept ) {
			$options[] = [
				'value' => (string) $dept->id,
				// translators: %s: department title.
				'label' => sprintf( __( '%s Team', 'erp' ), stripslashes( (string) $dept->title ) ),
			];
		}

		$empty_dept = $wpdb->get_row(
			"SELECT id FROM {$wpdb->prefix}erp_hr_employees WHERE department = 0"
		);

		if ( $empty_dept ) {
			$options[] = [ 'value' => '-1', 'label' => __( 'No Team', 'erp' ) ];
		}

		return $options;
	}
}
