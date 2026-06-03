/**
 * Canonical HR top-bar navigation items.
 *
 * Locked to openspec/changes/redesign-hr-free/figma-reference.md §Layer 2,
 * "Center cluster — horizontal nav" (table at lines 174–186).
 *
 * Items render in this exact order. Each item carries the capability gate
 * the spec assigns; absent caps hide the link. Pro-only items use the same
 * capability mechanism — they show automatically once HR Pro is installed
 * (which grants those caps).
 */

import { __ } from '@/shared/i18n';
import type { Capability } from '@/types/global';

/**
 * Stable string ID for the leading lucide icon. NavLinks maps the string to
 * a `lucide-react` component at render time; keeping the data layer
 * icon-name-only means this file stays a plain TS module (no JSX) and easier
 * to extend via wp.hooks filters.
 */
export type NavIconId =
	| 'house'
	| 'users-round'
	| 'sparkles'
	| 'layout-list'
	| 'badge-check'
	| 'layout-grid'
	| 'calendar-days'
	| 'calendar-check'
	| 'package'
	| 'file-text'
	| 'graduation-cap'
	| 'briefcase'
	| 'bar-chart-3'
	| 'help-circle';

/** A single entry inside a nav item's dropdown submenu. */
export interface NavSubItem {
	readonly id:           string;
	readonly label:        string;
	/** In-app hash route (e.g. `/employees/new`). */
	readonly to:           string;
	readonly capabilities: readonly Capability[];
	/** Optional one-line description shown under the label. */
	readonly description?: string;
}

export interface NavItem {
	readonly id:           string;
	readonly label:        string;
	readonly path:         string;
	readonly icon:         NavIconId;
	/** True when the item is a dropdown trigger (rendered with ChevronDown). */
	readonly hasDropdown:  boolean;
	readonly capabilities: readonly Capability[];
	/** Hash-path prefixes that count as "active" for the link's underline. */
	readonly activeMatches: readonly string[];
	/**
	 * Submenu entries. Rendered as an inline dropdown when present. Only modules
	 * already migrated to React carry children; the rest stay plain links until
	 * their pages ship.
	 */
	readonly children?: readonly NavSubItem[];
}

export const TOPBAR_NAV_ITEMS: ReadonlyArray< NavItem > = [
	{
		id:            'overview',
		label:         __( 'Overview', 'erp' ),
		path:          '/',
		icon:          'house',
		hasDropdown:   false,
		capabilities:  [ 'read' ],
		activeMatches: [ '/', '/overview', '/dashboard' ],
	},
	{
		id:            'people',
		label:         __( 'People', 'erp' ),
		path:          '/employees',
		icon:          'users-round',
		hasDropdown:   true,
		capabilities:  [ 'erp_list_employee' ],
		activeMatches: [ '/employees', '/departments', '/designations', '/org-chart' ],
		children: [
			{
				id:           'people-all',
				label:        __( 'All Employees', 'erp' ),
				to:           '/employees',
				capabilities: [ 'erp_list_employee' ],
				description:  __( 'Browse and manage the directory', 'erp' ),
			},
			{
				id:           'people-add',
				label:        __( 'Add New Employee', 'erp' ),
				to:           '/employees/new',
				capabilities: [ 'erp_create_employee' ],
				description:  __( 'Create a new team member', 'erp' ),
			},
			{
				id:           'people-departments',
				label:        __( 'Departments', 'erp' ),
				to:           '/departments',
				capabilities: [ 'erp_view_list' ],
				description:  __( 'Organize teams into departments', 'erp' ),
			},
			{
				id:           'people-designations',
				label:        __( 'Designations', 'erp' ),
				to:           '/designations',
				capabilities: [ 'erp_view_list' ],
				description:  __( 'Manage job titles', 'erp' ),
			},
		],
	},
	{
		id:            'payroll',
		label:         __( 'Payroll', 'erp' ),
		path:          '/payroll',
		icon:          'layout-grid',
		hasDropdown:   true,
		capabilities:  [ 'erp_view_payment' ],
		activeMatches: [ '/payroll' ],
	},
	{
		id:            'leave',
		label:         __( 'Leave', 'erp' ),
		path:          '/leave/requests',
		icon:          'calendar-days',
		hasDropdown:   true,
		// Free leave caps are `erp_leave_manage` (manager) + `erp_leave_create_request`.
		// There is NO `erp_leave_list_request` cap, so the old gate never passed and
		// the Leave menu never rendered. Gate on the real manage cap.
		capabilities:  [ 'erp_leave_manage' ],
		activeMatches: [ '/leave' ],
		children: [
			{
				id:           'leave-requests',
				label:        __( 'Requests', 'erp' ),
				to:           '/leave/requests',
				capabilities: [ 'erp_leave_manage' ],
				description:  __( 'Approve, reject and manage leave requests', 'erp' ),
			},
			{
				id:           'leave-types',
				label:        __( 'Leave Types', 'erp' ),
				to:           '/leave/types',
				capabilities: [ 'erp_leave_manage' ],
				description:  __( 'Categories employees request leave against', 'erp' ),
			},
			{
				id:           'leave-policies',
				label:        __( 'Leave Policies', 'erp' ),
				to:           '/leave/policies',
				capabilities: [ 'erp_leave_manage' ],
				description:  __( 'Grant leave days by year, scoped to teams or types', 'erp' ),
			},
			{
				id:           'leave-entitlements',
				label:        __( 'Leave Entitlements', 'erp' ),
				to:           '/leave/entitlements',
				capabilities: [ 'erp_leave_manage' ],
				description:  __( 'Assign policies to employees', 'erp' ),
			},
			{
				id:           'leave-holidays',
				label:        __( 'Holidays', 'erp' ),
				to:           '/leave/holidays',
				// Legacy AdminMenu gates the Holidays page on `erp_leave_manage`.
				capabilities: [ 'erp_leave_manage' ],
				description:  __( 'Manage company holidays and the leave calendar', 'erp' ),
			},
		],
	},
	{
		id:            'attendance',
		label:         __( 'Attendance', 'erp' ),
		path:          '/attendance',
		icon:          'calendar-check',
		hasDropdown:   true,
		capabilities:  [ 'erp_attendance_list' ],
		activeMatches: [ '/attendance' ],
	},
	{
		id:            'assets',
		label:         __( 'Assets', 'erp' ),
		path:          '/assets',
		icon:          'package',
		hasDropdown:   true,
		capabilities:  [ 'erp_view_asset' ],
		activeMatches: [ '/assets' ],
	},
	{
		id:            'documents',
		label:         __( 'Documents', 'erp' ),
		path:          '/documents',
		icon:          'file-text',
		hasDropdown:   false,
		capabilities:  [ 'erp_view_doc' ],
		activeMatches: [ '/documents' ],
	},
	{
		id:            'training',
		label:         __( 'Training', 'erp' ),
		path:          '/training',
		icon:          'graduation-cap',
		hasDropdown:   false,
		capabilities:  [ 'erp_view_training' ],
		activeMatches: [ '/training' ],
	},
	{
		id:            'recruitment',
		label:         __( 'Recruitment', 'erp' ),
		path:          '/recruitment',
		icon:          'briefcase',
		hasDropdown:   true,
		capabilities:  [ 'erp_view_jobs' ],
		activeMatches: [ '/recruitment' ],
	},
	{
		id:            'reports',
		label:         __( 'Reports', 'erp' ),
		path:          '/reports',
		icon:          'bar-chart-3',
		hasDropdown:   true,
		capabilities:  [ 'erp_hr_reports' ],
		activeMatches: [ '/reports' ],
	},
	{
		id:            'help',
		label:         __( 'Help', 'erp' ),
		path:          '/help',
		icon:          'help-circle',
		hasDropdown:   false,
		capabilities:  [],
		activeMatches: [ '/help' ],
	},
];
