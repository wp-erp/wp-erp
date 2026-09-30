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

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

\defined( 'ABSPATH' ) || exit;

class OrgChartController extends RestController {

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
	 * Active employees as `user_id => [ department, reporting_to ]`, in table
	 * order. Loaded once per request.
	 *
	 * @var array|null
	 */
	private $employees = null;

	/**
	 * Node display data (name, title, avatar, email) keyed by user id.
	 *
	 * @var array
	 */
	private $people = [];

	/**
	 * Load every active employee and the display data of each possible node in
	 * a fixed number of queries, instead of one `Employee` object and one
	 * children query per node.
	 *
	 * @return void
	 */
	private function load_employees(): void {
		global $wpdb;

		if ( null !== $this->employees ) {
			return;
		}

		$this->employees = [];

		$rows = $wpdb->get_results(
			"SELECT user_id, department, reporting_to
			FROM {$wpdb->prefix}erp_hr_employees
			WHERE status = 'active'
			AND deleted_at IS NULL
			ORDER BY id ASC"
		);

		foreach ( (array) $rows as $row ) {
			if ( ! (int) $row->user_id ) {
				continue;
			}

			$this->employees[ (int) $row->user_id ] = [
				'department'   => (int) $row->department,
				'reporting_to' => (int) $row->reporting_to,
			];
		}

		// Department leads are nodes too, and need not be active employees.
		$leads = $wpdb->get_col( "SELECT `lead` FROM {$wpdb->prefix}erp_hr_depts" );

		$this->people = erp_hr_get_employee_display_data( array_merge( array_keys( $this->employees ), (array) $leads ), 80 );
	}

	/**
	 * Port of `Helpers::sort_employees()` — recursive `reporting_to` walk within a
	 * department.
	 *
	 * @param int   $emp_id  Manager user id (0 for the synthetic top of a team).
	 * @param int   $dept_id Department id.
	 * @param int   $depth   Recursion depth (1 = department top).
	 * @param array $path    User ids already on this branch, as keys.
	 *
	 * @return array
	 */
	private function sort_employees( int $emp_id, int $dept_id = 0, int $depth = 1, array $path = [] ): array {
		$this->load_employees();

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

		if ( ! $emp_id ) {
			$data['className'] .= ' no-content';

			if ( 1 === $depth ) {
				$data['className'] .= ' no-parent';
			}
		} elseif ( ! empty( $this->people[ $emp_id ]['user_id'] ) ) {
			$manager        = $this->people[ $emp_id ];
			$data['id']     = (int) $manager['user_id'];
			$data['name']   = $manager['full_name'];
			$data['title']  = $manager['designation_title'];
			$data['lead']   = (int) $manager['reporting_to'];
			$data['avatar'] = $manager['avatar'] ?: '';
			// Every employee can open the chart, only managers get the addresses.
			$data['email']  = current_user_can( erp_hr_get_manager_role() ) ? $manager['email'] : '';
		}

		$path[ $emp_id ] = true;

		foreach ( $this->employees as $id => $employee ) {
			if ( $employee['department'] !== $dept_id ) {
				continue;
			}

			$reporting_to = $employee['reporting_to'];

			if ( ! $emp_id ) {
				// Top of a team without a lead: everyone who reports to nobody
				// inside the department.
				if ( 1 === $depth && $reporting_to && isset( $this->employees[ $reporting_to ] ) && $this->employees[ $reporting_to ]['department'] === $dept_id ) {
					continue;
				}
			} elseif ( 1 === $depth ) {
				if ( $id === $emp_id || ( $reporting_to && $reporting_to !== $emp_id ) ) {
					continue;
				}
			} elseif ( $reporting_to !== $emp_id ) {
				continue;
			}

			// A reporting cycle (A reports to B, B reports to A) would recurse
			// forever: never place someone under themselves.
			if ( isset( $path[ $id ] ) ) {
				continue;
			}

			$child = $this->sort_employees( $id, $dept_id, $depth + 1, $path );

			if ( ! $emp_id ) {
				$child['className'] = 'no-parent';
			}

			$data['children'][] = $child;
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
