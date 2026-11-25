<?php
/**
 * WP-ERP HR — the MCP tools WP ERP ships.
 */

namespace WeDevs\ERP\HRM\MCP;

defined( 'ABSPATH' ) || exit;

/**
 * The tools the free plugin exposes, one per `erp/v2` route worth offering.
 *
 * Titles are shown to administrators and are translated. Descriptions are
 * read by the model, which is why they are plain English: they say what the
 * tool returns and when to reach for it.
 *
 * Deliberately left out: file uploads and imports (not expressible as tool
 * arguments), onboarding and UI preferences (no use to an assistant), and
 * changing a user's ERP roles (an assistant should not be able to hand out
 * permissions).
 */
class Catalog {

	/**
	 * Every tool definition. See RouteTool for the shape.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function definitions(): array {
		return array_merge(
			self::self_service(),
			self::employees(),
			self::organization(),
			self::leave(),
			self::announcements(),
			self::reports()
		);
	}

	/**
	 * Shorthand for one definition.
	 *
	 * @param string               $name        Tool name.
	 * @param string               $title       Display title.
	 * @param string               $description What it does, for the model.
	 * @param string               $group       Feature group.
	 * @param string               $mode        read | write | delete.
	 * @param string               $method      HTTP method.
	 * @param string               $route       Route template.
	 * @param array<string, mixed> $extra       Any other definition keys.
	 *
	 * @return array<string, mixed>
	 */
	public static function tool( string $name, string $title, string $description, string $group, string $mode, string $method, string $route, array $extra = [] ): array {
		return array_merge( compact( 'name', 'title', 'description', 'group', 'mode', 'method', 'route' ), $extra );
	}

	/**
	 * The arguments of a leave request, which the route does not declare.
	 *
	 * @return array<string, array<string, string>>
	 */
	private static function leave_request_params(): array {
		return [
			'leave_policy' => [
				'type'        => 'integer',
				'description' => 'The leave policy id to take the leave from. Get the ids this employee can use from the assignable-policies tool.',
			],
			'leave_from'   => [
				'type'        => 'string',
				'description' => 'First day of leave, YYYY-MM-DD.',
			],
			'leave_to'     => [
				'type'        => 'string',
				'description' => 'Last day of leave, YYYY-MM-DD. Same as leave_from for a single day.',
			],
			'leave_reason' => [
				'type'        => 'string',
				'description' => 'Why the leave is being taken.',
			],
		];
	}

	/**
	 * Tools that only ever reach the calling user's own records.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private static function self_service(): array {
		$me = [ 'bind' => [ 'user_id' => '@me' ] ];

		return [
			self::tool( 'erp_hr_whoami', __( 'Who am I', 'erp' ), 'Identify the user this connection acts as: their WordPress user id, name, and which HR capabilities they hold. Call this first when you need the current user\'s id.', 'self', 'read', 'GET', '/erp/v2/me/capabilities' ),
			self::tool( 'erp_hr_get_my_profile', __( 'Get my profile', 'erp' ), 'Get the current user\'s own employee profile: job title, department, reporting manager, hire date and personal details.', 'self', 'read', 'GET', '/erp/v2/employees/{user_id}', $me ),
			self::tool( 'erp_hr_get_my_leave', __( 'Get my leave', 'erp' ), 'Get the current user\'s own leave balances per policy and their leave request history.', 'self', 'read', 'GET', '/erp/v2/employees/{user_id}/leave', $me ),
			self::tool( 'erp_hr_get_my_leave_policies', __( 'Get my leave policies', 'erp' ), 'List the leave policies the current user can request leave from, with their ids. Call before requesting leave.', 'self', 'read', 'GET', '/erp/v2/employees/{user_id}/leave/assignable', $me ),
			self::tool(
				'erp_hr_check_my_leave_dates',
				__( 'Check my leave dates', 'erp' ),
				'Check whether the current user can take leave on the given dates under a policy, and how many days it would use, without creating a request.',
				'self',
				'read',
				'POST',
				'/erp/v2/employees/{user_id}/leave/validate-dates',
				$me + [
					'params'   => array_diff_key( self::leave_request_params(), [ 'leave_reason' => 1 ] ),
					'required' => [ 'leave_policy', 'leave_from', 'leave_to' ],
				]
			),
			self::tool(
				'erp_hr_request_my_leave',
				__( 'Request leave for myself', 'erp' ),
				'Submit a leave request for the current user. It goes to their manager for approval like one made in the HR screens.',
				'self',
				'write',
				'POST',
				'/erp/v2/employees/{user_id}/leave/requests',
				$me + [
					'params'   => self::leave_request_params(),
					'required' => [ 'leave_policy', 'leave_from', 'leave_to', 'leave_reason' ],
				]
			),
			self::tool( 'erp_hr_list_my_announcements', __( 'List my announcements', 'erp' ), 'List the company announcements addressed to the current user, newest first.', 'self', 'read', 'GET', '/erp/v2/me/announcements' ),
			self::tool(
				'erp_hr_get_my_leave_calendar',
				__( 'Get my leave calendar', 'erp' ),
				'Get the current user\'s own leave and the company holidays between two dates.',
				'self',
				'read',
				'GET',
				'/erp/v2/leave-calendar',
				[
					'bind' => [ 'scope' => 'me' ],
					'omit' => [ 'user_id', 'department_id', 'designation_id' ],
				]
			),
		];
	}

	/**
	 * Employee directory and records.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private static function employees(): array {
		$list   = [ 'cap' => 'erp_list_employee' ];
		$create = [ 'cap' => 'erp_create_employee' ];

		return [
			self::tool( 'erp_hr_search', __( 'Search HR', 'erp' ), 'Quick search across employees, departments and designations by name. Use to resolve a name to an id.', 'employees', 'read', 'GET', '/erp/v2/search', $list ),
			self::tool( 'erp_hr_list_employees', __( 'List employees', 'erp' ), 'Search and list employees, filtered by status, department, designation or location. Returns each employee\'s user_id, which the other employee tools take.', 'employees', 'read', 'GET', '/erp/v2/employees', $list ),
			self::tool( 'erp_hr_count_employees', __( 'Count employees', 'erp' ), 'Count employees by status (active, terminated, resigned…), optionally within a department, designation or location.', 'employees', 'read', 'GET', '/erp/v2/employees/counts', $list ),
			self::tool( 'erp_hr_get_employee', __( 'Get employee', 'erp' ), 'Get one employee\'s full profile by user_id.', 'employees', 'read', 'GET', '/erp/v2/employees/{user_id}', $list ),
			self::tool( 'erp_hr_check_employee_email', __( 'Check employee email', 'erp' ), 'Check whether an email address already belongs to a WordPress user or employee, before creating an employee.', 'employees', 'read', 'GET', '/erp/v2/employees/check-user', $create ),
			self::tool( 'erp_hr_create_employee', __( 'Create employee', 'erp' ), 'Create a new employee. Check the email is free first. Department, designation, location and reporting_to take ids from the organization tools.', 'employees', 'write', 'POST', '/erp/v2/employees', $create + [ 'omit' => [ 'photo_id' ] ] ),
			self::tool( 'erp_hr_update_employee', __( 'Update employee', 'erp' ), 'Update fields on an existing employee. Send only the fields that change.', 'employees', 'write', 'POST', '/erp/v2/employees/{user_id}', [ 'omit' => [ 'photo_id' ] ] ),
			self::tool( 'erp_hr_terminate_employee', __( 'Terminate employee', 'erp' ), 'Terminate an employee\'s employment, recording the type, reason and rehire eligibility. Confirm with the user before calling.', 'employees', 'write', 'POST', '/erp/v2/employees/{user_id}/terminate', $create ),
			self::tool( 'erp_hr_reactivate_employee', __( 'Reactivate employee', 'erp' ), 'Return a terminated or resigned employee to active status.', 'employees', 'write', 'POST', '/erp/v2/employees/{user_id}/reactivate', $create ),
			self::tool( 'erp_hr_delete_employee', __( 'Delete employee', 'erp' ), 'Move an employee to the trash, or permanently delete them with force. Permanent deletion cannot be undone; confirm with the user before calling.', 'employees', 'delete', 'DELETE', '/erp/v2/employees/{user_id}', $create ),
			self::tool( 'erp_hr_restore_employee', __( 'Restore employee', 'erp' ), 'Restore an employee from the trash.', 'employees', 'write', 'POST', '/erp/v2/employees/{user_id}/restore', $create ),

			self::tool( 'erp_hr_list_employee_job_history', __( 'List job history', 'erp' ), 'Get an employee\'s job history: employment status, compensation and job (department, designation, manager) changes over time.', 'employees', 'read', 'GET', '/erp/v2/employees/{user_id}/job-histories' ),
			self::tool( 'erp_hr_add_employee_job_history', __( 'Add job history entry', 'erp' ), 'Record a change to an employee\'s employment status (module "employment"), compensation ("compensation") or job information ("job"). This is how a promotion, transfer or pay change is made.', 'employees', 'write', 'POST', '/erp/v2/employees/{user_id}/job-histories' ),
			self::tool( 'erp_hr_delete_employee_job_history', __( 'Delete job history entry', 'erp' ), 'Delete one entry from an employee\'s job history.', 'employees', 'delete', 'DELETE', '/erp/v2/employees/{user_id}/job-histories/{history_id}' ),

			self::tool( 'erp_hr_list_employee_notes', __( 'List employee notes', 'erp' ), 'List the HR notes written about an employee.', 'employees', 'read', 'GET', '/erp/v2/employees/{user_id}/notes' ),
			self::tool( 'erp_hr_add_employee_note', __( 'Add employee note', 'erp' ), 'Add an HR note to an employee\'s record.', 'employees', 'write', 'POST', '/erp/v2/employees/{user_id}/notes' ),
			self::tool( 'erp_hr_delete_employee_note', __( 'Delete employee note', 'erp' ), 'Delete an HR note from an employee\'s record.', 'employees', 'delete', 'DELETE', '/erp/v2/employees/{user_id}/notes/{note_id}' ),

			self::tool( 'erp_hr_list_employee_performance', __( 'List performance records', 'erp' ), 'Get an employee\'s performance reviews, comments and goals.', 'employees', 'read', 'GET', '/erp/v2/employees/{user_id}/performance' ),
			self::tool( 'erp_hr_add_employee_performance', __( 'Add performance record', 'erp' ), 'Add a performance review, comment or goal for an employee. The type decides which of the other fields apply.', 'employees', 'write', 'POST', '/erp/v2/employees/{user_id}/performance' ),
			self::tool( 'erp_hr_delete_employee_performance', __( 'Delete performance record', 'erp' ), 'Delete one performance review, comment or goal.', 'employees', 'delete', 'DELETE', '/erp/v2/employees/{user_id}/performance/{id}' ),

			self::tool( 'erp_hr_list_employee_experiences', __( 'List work experience', 'erp' ), 'Get an employee\'s previous work experience.', 'employees', 'read', 'GET', '/erp/v2/employees/{user_id}/experiences' ),
			self::tool( 'erp_hr_list_employee_educations', __( 'List education', 'erp' ), 'Get an employee\'s education history.', 'employees', 'read', 'GET', '/erp/v2/employees/{user_id}/educations' ),
			self::tool( 'erp_hr_list_employee_dependents', __( 'List dependents', 'erp' ), 'Get an employee\'s dependents.', 'employees', 'read', 'GET', '/erp/v2/employees/{user_id}/dependents' ),
			self::tool( 'erp_hr_get_employee_permissions', __( 'Get employee permissions', 'erp' ), 'See which ERP roles an employee\'s user account holds.', 'employees', 'read', 'GET', '/erp/v2/employees/{user_id}/permission', [ 'cap' => 'manage_options' ] ),
		];
	}

	/**
	 * Company structure.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private static function organization(): array {
		$view        = [ 'cap' => 'erp_view_list' ];
		$department  = [ 'cap' => 'erp_manage_department' ];
		$designation = [ 'cap' => 'erp_manage_designation' ];

		return [
			self::tool( 'erp_hr_list_departments', __( 'List departments', 'erp' ), 'List departments with their lead, parent department and headcount.', 'organization', 'read', 'GET', '/erp/v2/departments', $view ),
			self::tool( 'erp_hr_get_department', __( 'Get department', 'erp' ), 'Get one department by id.', 'organization', 'read', 'GET', '/erp/v2/departments/{id}', $view ),
			self::tool( 'erp_hr_create_department', __( 'Create department', 'erp' ), 'Create a department. lead is an employee user_id; parent is another department\'s id.', 'organization', 'write', 'POST', '/erp/v2/departments', $department ),
			self::tool( 'erp_hr_update_department', __( 'Update department', 'erp' ), 'Rename a department or change its description, lead or parent.', 'organization', 'write', 'POST', '/erp/v2/departments/{id}', $department ),
			self::tool( 'erp_hr_delete_department', __( 'Delete department', 'erp' ), 'Delete a department. Refused while employees are still assigned to it.', 'organization', 'delete', 'DELETE', '/erp/v2/departments/{id}', $department ),

			self::tool( 'erp_hr_list_designations', __( 'List designations', 'erp' ), 'List designations (job titles) with how many employees hold each.', 'organization', 'read', 'GET', '/erp/v2/designations', $view ),
			self::tool( 'erp_hr_get_designation', __( 'Get designation', 'erp' ), 'Get one designation by id.', 'organization', 'read', 'GET', '/erp/v2/designations/{id}', $view ),
			self::tool( 'erp_hr_create_designation', __( 'Create designation', 'erp' ), 'Create a designation (job title).', 'organization', 'write', 'POST', '/erp/v2/designations', $designation ),
			self::tool( 'erp_hr_update_designation', __( 'Update designation', 'erp' ), 'Rename a designation or change its description.', 'organization', 'write', 'POST', '/erp/v2/designations/{id}', $designation ),
			self::tool( 'erp_hr_delete_designation', __( 'Delete designation', 'erp' ), 'Delete a designation. Refused while employees still hold it.', 'organization', 'delete', 'DELETE', '/erp/v2/designations/{id}', $designation ),

			self::tool( 'erp_hr_list_locations', __( 'List company locations', 'erp' ), 'List the company\'s office locations with their ids.', 'organization', 'read', 'GET', '/erp/v2/company-locations', $view ),
			self::tool( 'erp_hr_create_location', __( 'Create company location', 'erp' ), 'Add an office location. country is a two-letter ISO code.', 'organization', 'write', 'POST', '/erp/v2/company-locations', [ 'cap' => 'manage_options' ] ),
			self::tool( 'erp_hr_get_org_chart', __( 'Get org chart', 'erp' ), 'Get the reporting hierarchy — who reports to whom — for the whole company or one department.', 'organization', 'read', 'GET', '/erp/v2/org-chart', [ 'cap' => 'erp_list_employee' ] ),
		];
	}

	/**
	 * Leave management.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private static function leave(): array {
		$manage = [ 'cap' => 'erp_leave_manage' ];

		return [
			self::tool(
				'erp_hr_list_leave_requests',
				__( 'List leave requests', 'erp' ),
				'List leave requests across the company, filtered by status, dates, policy or department. Status is 1 for approved, 2 for pending, 3 for rejected.',
				'leave',
				'read',
				'GET',
				'/erp/v2/leave-requests'
			),
			self::tool( 'erp_hr_count_leave_requests', __( 'Count leave requests', 'erp' ), 'Count leave requests by status for a year.', 'leave', 'read', 'GET', '/erp/v2/leave-requests/counts' ),
			self::tool( 'erp_hr_approve_leave_request', __( 'Approve leave request', 'erp' ), 'Approve a pending leave request, optionally with a comment.', 'leave', 'write', 'POST', '/erp/v2/leave-requests/{id}/approve' ),
			self::tool( 'erp_hr_reject_leave_request', __( 'Reject leave request', 'erp' ), 'Reject a pending leave request. Give the reason; the employee sees it.', 'leave', 'write', 'POST', '/erp/v2/leave-requests/{id}/reject' ),
			self::tool( 'erp_hr_delete_leave_request', __( 'Delete leave request', 'erp' ), 'Delete a leave request and give back any days it used.', 'leave', 'delete', 'DELETE', '/erp/v2/leave-requests/{id}', $manage ),

			self::tool( 'erp_hr_get_employee_leave', __( 'Get employee leave', 'erp' ), 'Get one employee\'s leave balances per policy and their leave request history.', 'leave', 'read', 'GET', '/erp/v2/employees/{user_id}/leave' ),
			self::tool( 'erp_hr_get_employee_leave_policies', __( 'Get employee leave policies', 'erp' ), 'List the leave policies an employee can request leave from, with their ids.', 'leave', 'read', 'GET', '/erp/v2/employees/{user_id}/leave/assignable' ),
			self::tool(
				'erp_hr_check_employee_leave_dates',
				__( 'Check employee leave dates', 'erp' ),
				'Check whether an employee can take leave on the given dates under a policy, and how many days it would use, without creating a request.',
				'leave',
				'read',
				'POST',
				'/erp/v2/employees/{user_id}/leave/validate-dates',
				[
					'params'   => array_diff_key( self::leave_request_params(), [ 'leave_reason' => 1 ] ),
					'required' => [ 'leave_policy', 'leave_from', 'leave_to' ],
				]
			),
			self::tool(
				'erp_hr_create_leave_request',
				__( 'Create leave request', 'erp' ),
				'Submit a leave request on behalf of an employee.',
				'leave',
				'write',
				'POST',
				'/erp/v2/employees/{user_id}/leave/requests',
				[
					'params'   => self::leave_request_params(),
					'required' => [ 'leave_policy', 'leave_from', 'leave_to', 'leave_reason' ],
				]
			),
			self::tool( 'erp_hr_get_leave_calendar', __( 'Get leave calendar', 'erp' ), 'See who is on leave between two dates, along with company holidays. Filter by department, designation or one employee.', 'leave', 'read', 'GET', '/erp/v2/leave-calendar' ),

			self::tool( 'erp_hr_list_leave_types', __( 'List leave types', 'erp' ), 'List the kinds of leave the company offers (annual, sick…).', 'leave', 'read', 'GET', '/erp/v2/leave-types', $manage ),
			self::tool( 'erp_hr_create_leave_type', __( 'Create leave type', 'erp' ), 'Create a kind of leave. A policy must then be created for it before anyone can take it.', 'leave', 'write', 'POST', '/erp/v2/leave-types', $manage ),
			self::tool( 'erp_hr_update_leave_type', __( 'Update leave type', 'erp' ), 'Rename a leave type or change its description.', 'leave', 'write', 'POST', '/erp/v2/leave-types/{id}', $manage ),
			self::tool( 'erp_hr_delete_leave_type', __( 'Delete leave type', 'erp' ), 'Delete a leave type. Refused while a policy still uses it.', 'leave', 'delete', 'DELETE', '/erp/v2/leave-types/{id}', $manage ),

			self::tool( 'erp_hr_list_leave_policies', __( 'List leave policies', 'erp' ), 'List leave policies: how many days of which leave type which employees get in a financial year.', 'leave', 'read', 'GET', '/erp/v2/leave-policies', $manage ),
			self::tool( 'erp_hr_get_leave_policy', __( 'Get leave policy', 'erp' ), 'Get one leave policy by id.', 'leave', 'read', 'GET', '/erp/v2/leave-policies/{id}', $manage ),
			self::tool( 'erp_hr_get_leave_policy_options', __( 'Get leave policy options', 'erp' ), 'Get the values a leave policy can be built from: leave types, financial years, departments, designations, locations and the like.', 'leave', 'read', 'GET', '/erp/v2/leave-policies/form-options', $manage ),
			self::tool( 'erp_hr_create_leave_policy', __( 'Create leave policy', 'erp' ), 'Create a leave policy for a leave type and financial year. Get valid ids from the leave policy options tool. Use "-1" for department_id, designation_id, location_id, gender or marital to mean all.', 'leave', 'write', 'POST', '/erp/v2/leave-policies', $manage + [ 'required' => [ 'leave_id', 'days', 'f_year' ] ] ),
			self::tool( 'erp_hr_update_leave_policy', __( 'Update leave policy', 'erp' ), 'Change a leave policy.', 'leave', 'write', 'POST', '/erp/v2/leave-policies/{id}', $manage ),
			self::tool( 'erp_hr_delete_leave_policy', __( 'Delete leave policy', 'erp' ), 'Delete a leave policy.', 'leave', 'delete', 'DELETE', '/erp/v2/leave-policies/{id}', $manage ),

			self::tool( 'erp_hr_list_leave_entitlements', __( 'List leave entitlements', 'erp' ), 'List which employees have been granted which leave policies, with the days granted, used and remaining.', 'leave', 'read', 'GET', '/erp/v2/leave-entitlements', $manage ),
			self::tool( 'erp_hr_create_leave_entitlement', __( 'Grant leave entitlement', 'erp' ), 'Grant a leave policy to one employee (assignment_to "single" with single_employee) or to every employee it applies to ("all").', 'leave', 'write', 'POST', '/erp/v2/leave-entitlements', $manage ),
			self::tool( 'erp_hr_delete_leave_entitlement', __( 'Delete leave entitlement', 'erp' ), 'Take a leave entitlement away from an employee.', 'leave', 'delete', 'DELETE', '/erp/v2/leave-entitlements/{id}', $manage ),

			self::tool( 'erp_hr_list_holidays', __( 'List holidays', 'erp' ), 'List company holidays, optionally for a year or between two dates.', 'leave', 'read', 'GET', '/erp/v2/holidays', $manage ),
			self::tool( 'erp_hr_create_holiday', __( 'Create holiday', 'erp' ), 'Add a company holiday. Set range to true and give an end date for one that spans several days.', 'leave', 'write', 'POST', '/erp/v2/holidays', $manage ),
			self::tool( 'erp_hr_update_holiday', __( 'Update holiday', 'erp' ), 'Change a company holiday.', 'leave', 'write', 'POST', '/erp/v2/holidays/{id}', $manage ),
			self::tool( 'erp_hr_delete_holiday', __( 'Delete holiday', 'erp' ), 'Delete a company holiday.', 'leave', 'delete', 'DELETE', '/erp/v2/holidays/{id}', $manage ),
			self::tool( 'erp_hr_list_financial_years', __( 'List financial years', 'erp' ), 'List the financial years leave is accounted in, with their ids and dates.', 'leave', 'read', 'GET', '/erp/v2/financial-years' ),
		];
	}

	/**
	 * Company announcements.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private static function announcements(): array {
		$manage = [ 'cap' => 'erp_manage_announcement' ];

		return [
			self::tool( 'erp_hr_list_announcements', __( 'List announcements', 'erp' ), 'List company announcements by status, date or search term.', 'announcements', 'read', 'GET', '/erp/v2/announcements', $manage ),
			self::tool( 'erp_hr_get_announcement', __( 'Get announcement', 'erp' ), 'Get one announcement with its full content and audience.', 'announcements', 'read', 'GET', '/erp/v2/announcements/{id}' ),
			self::tool( 'erp_hr_create_announcement', __( 'Create announcement', 'erp' ), 'Write an announcement and publish it, or save it as a draft. assign_type chooses the audience: everyone, or the listed departments, designations or employees. Publishing notifies the audience, so confirm the wording first.', 'announcements', 'write', 'POST', '/erp/v2/announcements', $manage + [ 'required' => [ 'title', 'content' ] ] ),
			self::tool( 'erp_hr_update_announcement', __( 'Update announcement', 'erp' ), 'Edit an announcement\'s title, content, status or audience.', 'announcements', 'write', 'POST', '/erp/v2/announcements/{id}', $manage ),
			self::tool( 'erp_hr_delete_announcement', __( 'Delete announcement', 'erp' ), 'Move an announcement to the trash, or permanently delete it with force.', 'announcements', 'delete', 'DELETE', '/erp/v2/announcements/{id}', $manage ),
			self::tool( 'erp_hr_restore_announcement', __( 'Restore announcement', 'erp' ), 'Restore an announcement from the trash.', 'announcements', 'write', 'POST', '/erp/v2/announcements/{id}/restore', $manage ),
		];
	}

	/**
	 * Dashboard and reports.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private static function reports(): array {
		$manager = [ 'cap' => erp_hr_get_manager_role() ];

		return [
			self::tool( 'erp_hr_get_dashboard', __( 'Get HR dashboard', 'erp' ), 'Get the HR overview: headcount, who is out today, upcoming birthdays and leave, and recent announcements.', 'reports', 'read', 'GET', '/erp/v2/dashboard', [ 'cap' => 'erp_list_employee' ] ),
			self::tool( 'erp_hr_get_pending_request_counts', __( 'Get pending request counts', 'erp' ), 'Count the requests waiting for a decision, by kind: leave and, with WP ERP Pro, remote work, resignation, reimbursement and assets.', 'reports', 'read', 'GET', '/erp/v2/requests/counts' ),
			self::tool( 'erp_hr_report_headcount', __( 'Headcount report', 'erp' ), 'Headcount by month for a year, optionally for one department.', 'reports', 'read', 'GET', '/erp/v2/reports/headcount', $manager ),
			self::tool( 'erp_hr_report_age_profile', __( 'Age profile report', 'erp' ), 'How many employees fall in each age band.', 'reports', 'read', 'GET', '/erp/v2/reports/age-profile', $manager ),
			self::tool( 'erp_hr_report_gender_profile', __( 'Gender profile report', 'erp' ), 'Employee gender split per department.', 'reports', 'read', 'GET', '/erp/v2/reports/gender-profile', $manager ),
			self::tool( 'erp_hr_report_salary_history', __( 'Salary history report', 'erp' ), 'Every employee\'s compensation changes over time.', 'reports', 'read', 'GET', '/erp/v2/reports/salary-history', $manager ),
			self::tool( 'erp_hr_report_years_of_service', __( 'Years of service report', 'erp' ), 'Employees with their hire date and length of service.', 'reports', 'read', 'GET', '/erp/v2/reports/years-of-service', $manager ),
			self::tool( 'erp_hr_report_leaves', __( 'Leave report', 'erp' ), 'Leave taken per employee per policy, for a financial year or between two dates, filterable by department, designation or employment type.', 'reports', 'read', 'GET', '/erp/v2/reports/leaves', $manager ),
		];
	}
}
