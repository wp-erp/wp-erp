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
	| 'help-circle'
	| 'wallet'
	| 'banknote';

/** A single entry inside a nav item's dropdown submenu. */
export interface NavSubItem {
	readonly id:           string;
	readonly label:        string;
	/** In-app hash route (e.g. `/employees/new`). */
	readonly to:           string;
	readonly capabilities: readonly Capability[];
	/** Optional one-line description shown under the label. */
	readonly description?: string;
	/**
	 * Optional pro sub-module key — the child only shows when that module is
	 * active (present in `boot.modules`). Used for cross-module report links whose
	 * pages are provided by a pro module's routes.
	 */
	readonly module?: string;
	/**
	 * Marks a pro-only child. When the ERP Pro plugin is absent (`!boot.isPro`),
	 * the child still renders — as a "Pro" badge that opens the upgrade dialog
	 * instead of navigating (legacy `pro_popup` parity).
	 */
	readonly pro?: boolean;
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
	 * Pro sub-module key. When set, the item only shows if that module is active
	 * (present in `boot.modules`) — so it disappears when the pro module is off,
	 * exactly like the legacy admin menu.
	 */
	readonly module?: string;
	/**
	 * Marks a pro-only top-level item. When the ERP Pro plugin is absent
	 * (`!boot.isPro`), the item renders as a "Pro" badge that opens the upgrade
	 * dialog instead of navigating — the React equivalent of the legacy
	 * `AddProMenu` `pro_popup` upsell entries.
	 */
	readonly pro?: boolean;
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
		label:         __( 'Dashboard', 'erp' ),
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
		activeMatches: [ '/employees', '/departments', '/designations', '/org-chart', '/announcements' ],
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
				id:           'people-requests',
				label:        __( 'Requests', 'erp' ),
				to:           '/leave/requests',
				capabilities: [ 'erp_leave_manage' ],
				description:  __( 'Employee requests', 'erp' ),
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
			{
				id:           'people-org-chart',
				label:        __( 'Org Chart', 'erp' ),
				to:           '/org-chart',
				capabilities: [ 'erp_view_list' ],
				description:  __( 'Reporting hierarchy', 'erp' ),
			},
			{
				id:           'people-announcements',
				label:        __( 'Announcements', 'erp' ),
				to:           '/announcements',
				capabilities: [ 'erp_view_announcement' ],
				description:  __( 'Post company-wide notices', 'erp' ),
			},
		],
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
				description:  __( 'Manage company holidays', 'erp' ),
			},
			{
				id:           'leave-calendar',
				label:        __( 'Calendar', 'erp' ),
				to:           '/leave/calendar',
				capabilities: [ 'erp_leave_manage' ],
				description:  __( 'Month view of leave and holidays', 'erp' ),
			},
			{
				id:           'leave-unpaid',
				label:        __( 'Unpaid Leaves', 'erp' ),
				to:           '/leave/unpaid',
				capabilities: [ 'erp_leave_manage' ],
				description:  __( 'Calculate and encash extra unpaid leaves', 'erp' ),
				module:       'advanced_leave',
				pro:          true,
			},
			{
				id:           'leave-forward',
				label:        __( 'Forward Leaves', 'erp' ),
				to:           '/leave/forward',
				capabilities: [ 'erp_leave_manage' ],
				description:  __( 'Carry-forward and encashment requests', 'erp' ),
				module:       'advanced_leave',
				pro:          true,
			},
		],
	},
	{
		id:            'attendance',
		label:         __( 'Attendance', 'erp' ),
		path:          '/attendance',
		icon:          'calendar-check',
		hasDropdown:   true,
		// Manager/admin only — legacy parity (an employee sees no Attendance nav;
		// their self-attendance lives on the dashboard widget). `erp_hr_manager` is a
		// real role cap already in the boot map (same gate the Reports nav uses).
		capabilities:  [ 'erp_hr_manager' ],
		activeMatches: [ '/attendance' ],
		module:        'attendance',
		pro:           true,
		// Sub-pages provided by the pro Attendance module (registered via `erp_hr.routes`).
		children: [
			{
				id:           'attendance-logs',
				label:        __( 'Logs', 'erp' ),
				to:           '/attendance',
				capabilities: [ 'erp_hr_manager' ],
				description:  __( 'Daily attendance of every employee', 'erp' ),
			},
			{
				id:           'attendance-shifts',
				label:        __( 'Shifts', 'erp' ),
				to:           '/attendance/shifts',
				capabilities: [ 'erp_hr_manager' ],
				description:  __( 'Create shifts and assign employees', 'erp' ),
			},
			{
				id:           'attendance-reports',
				label:        __( 'Reports', 'erp' ),
				to:           '/attendance/reports',
				capabilities: [ 'erp_hr_manager' ],
				description:  __( 'Per-employee attendance report', 'erp' ),
			},
		],
	},
	{
		id:            'documents',
		label:         __( 'Documents', 'erp' ),
		path:          '/documents',
		icon:          'file-text',
		hasDropdown:   false,
		// Company documents = manager/admin (legacy parity). Employees use the
		// per-employee Documents tab in their profile, not this top-nav page.
		capabilities:  [ 'erp_hr_manager' ],
		activeMatches: [ '/documents' ],
		module:        'document-manager',
		pro:           true,
	},
	{
		id:            'training',
		label:         __( 'Training', 'erp' ),
		path:          '/training',
		icon:          'graduation-cap',
		hasDropdown:   false,
		// Manager/admin — matches the pro Training submenu (`erp_hr_manager`) and,
		// unlike the pro-only `erp_view_training`, this cap is in the free boot map
		// so the "Pro" upsell badge still resolves when the pro plugin is absent.
		capabilities:  [ 'erp_hr_manager' ],
		activeMatches: [ '/training' ],
		module:        'training',
		pro:           true,
	},
	{
		id:            'recruitment',
		label:         __( 'Recruitment', 'erp' ),
		path:          '/recruitment',
		icon:          'briefcase',
		hasDropdown:   true,
		// Manager/admin (legacy parity). Shows only when the pro Recruitment module
		// is active. Routes provided by the pro module via `erp_hr.routes`.
		capabilities:  [ 'erp_hr_manager' ],
		activeMatches: [ '/recruitment' ],
		module:        'recruitment',
		pro:           true,
		children: [
			{ id: 'recruitment-jobs', label: __( 'Job Openings', 'erp' ), to: '/recruitment', capabilities: [ 'erp_hr_manager' ] },
			{ id: 'recruitment-add-job', label: __( 'Add Opening', 'erp' ), to: '/recruitment/new', capabilities: [ 'erp_hr_manager' ] },
			{ id: 'recruitment-candidates', label: __( 'Candidates', 'erp' ), to: '/recruitment/candidates', capabilities: [ 'erp_hr_manager' ] },
			{ id: 'recruitment-add-candidate', label: __( 'Add candidate', 'erp' ), to: '/recruitment/candidates/new', capabilities: [ 'erp_hr_manager' ] },
			{ id: 'recruitment-question-sets', label: __( 'Question Sets', 'erp' ), to: '/recruitment/question-sets', capabilities: [ 'erp_hr_manager' ] },
			{ id: 'recruitment-stages', label: __( 'Stages', 'erp' ), to: '/recruitment/stages', capabilities: [ 'erp_hr_manager' ] },
			{ id: 'recruitment-calendar', label: __( 'Calendar', 'erp' ), to: '/recruitment/calendar', capabilities: [ 'erp_hr_manager' ] },
			{ id: 'recruitment-reports', label: __( 'Reports', 'erp' ), to: '/recruitment/reports', capabilities: [ 'erp_hr_manager' ] },
			{ id: 'recruitment-ai-settings', label: __( 'AI Settings', 'erp' ), to: '/recruitment/ai-settings', capabilities: [ 'erp_hr_manager' ] },
			{ id: 'recruitment-ai-talent-pool', label: __( 'AI Talent Pool', 'erp' ), to: '/recruitment/ai-talent-pool', capabilities: [ 'erp_hr_manager' ] },
			{ id: 'recruitment-ai-job-settings', label: __( 'AI Job Settings', 'erp' ), to: '/recruitment/ai-job-settings', capabilities: [ 'erp_hr_manager' ] },
		],
	},
	{
		id:            'asset',
		label:         __( 'Assets', 'erp' ),
		path:          '/assets',
		icon:          'briefcase',
		hasDropdown:   true,
		// Manager/admin (legacy parity). Shows only when the pro Asset Management
		// module is active. Routes provided by the pro module via `erp_hr.routes`.
		capabilities:  [ 'erp_hr_manager' ],
		activeMatches: [ '/assets' ],
		module:        'asset',
		pro:           true,
		children: [
			{ id: 'asset-list', label: __( 'Assets', 'erp' ), to: '/assets', capabilities: [ 'erp_hr_manager' ] },
			{ id: 'asset-allotments', label: __( 'Allotments', 'erp' ), to: '/assets/allotments', capabilities: [ 'erp_hr_manager' ] },
			{ id: 'asset-requests', label: __( 'Requests', 'erp' ), to: '/assets/requests', capabilities: [ 'erp_hr_manager' ] },
			{ id: 'asset-reports', label: __( 'Reports', 'erp' ), to: '/assets/reports', capabilities: [ 'erp_hr_manager' ] },
		],
	},
	{
		id:            'reimbursement',
		label:         __( 'Reimbursement', 'erp' ),
		path:          '/reimbursement',
		icon:          'wallet',
		hasDropdown:   false,
		// Shows only when the pro Reimbursement module is active. Route provided by
		// the pro module via `erp_hr.routes`.
		capabilities:  [ 'erp_hr_manager' ],
		activeMatches: [ '/reimbursement' ],
		module:        'reimbursement',
		pro:           true,
	},
	{
		id:            'payroll',
		label:         __( 'Payroll', 'erp' ),
		path:          '/payroll',
		icon:          'banknote',
		hasDropdown:   true,
		// Shows only when the pro Payroll module is active. Routes + submenu provided
		// by the pro module via `erp_hr.routes`. Mirrors the legacy payroll submenu
		// (Dashboard / Pay Run List / Settings).
		capabilities:  [ 'erp_hr_manager' ],
		activeMatches: [ '/payroll' ],
		module:        'payroll',
		pro:           true,
		children: [
			{ id: 'payroll-dashboard', label: __( 'Dashboard', 'erp' ), to: '/payroll', capabilities: [ 'erp_hr_manager' ] },
			{ id: 'payroll-payruns', label: __( 'Payruns', 'erp' ), to: '/payroll/payruns', capabilities: [ 'erp_hr_manager' ] },
			{ id: 'payroll-components', label: __( 'Pay Components', 'erp' ), to: '/payroll/components', capabilities: [ 'erp_hr_manager' ] },
			{ id: 'payroll-settings', label: __( 'Settings', 'erp' ), to: '/payroll/settings', capabilities: [ 'erp_hr_manager' ] },
		],
	},
	{
		id:            'reports',
		label:         __( 'Reports', 'erp' ),
		path:          '/reports/age-profile',
		icon:          'bar-chart-3',
		hasDropdown:   true,
		// Legacy AdminMenu gates the Reports page (and every submenu) on the
		// HR-manager role `erp_hr_manager` — not a per-role cap key. The v2
		// `/me/capabilities` map now exposes it (MeControllerV2::hr_capability_keys).
		capabilities:  [ 'erp_hr_manager' ],
		activeMatches: [ '/reports' ],
		children: [
			{
				id:           'reports-age-profile',
				label:        __( 'Age Profile', 'erp' ),
				to:           '/reports/age-profile',
				capabilities: [ 'erp_hr_manager' ],
				description:  __( 'Age breakdown across departments', 'erp' ),
			},
			{
				id:           'reports-gender-profile',
				label:        __( 'Gender Profile', 'erp' ),
				to:           '/reports/gender-profile',
				capabilities: [ 'erp_hr_manager' ],
				description:  __( 'Workforce differentiation by gender', 'erp' ),
			},
			{
				id:           'reports-headcount',
				label:        __( 'Head Count', 'erp' ),
				to:           '/reports/headcount',
				capabilities: [ 'erp_hr_manager' ],
				description:  __( 'Headcount by month and department', 'erp' ),
			},
			{
				id:           'reports-salary-history',
				label:        __( 'Salary History', 'erp' ),
				to:           '/reports/salary-history',
				capabilities: [ 'erp_hr_manager' ],
				description:  __( 'Compensation history of employees', 'erp' ),
			},
			{
				id:           'reports-years-of-service',
				label:        __( 'Years of Service', 'erp' ),
				to:           '/reports/years-of-service',
				capabilities: [ 'erp_hr_manager' ],
				description:  __( 'Longevity and experience report', 'erp' ),
			},
			{
				id:           'reports-leaves',
				label:        __( 'Leaves', 'erp' ),
				to:           '/reports/leaves',
				capabilities: [ 'erp_hr_manager' ],
				description:  __( 'Employee-based leave summary', 'erp' ),
			},
			{
				id:           'reports-assets',
				label:        __( 'Assets', 'erp' ),
				to:           '/assets/reports',
				capabilities: [ 'erp_hr_manager' ],
				description:  __( 'Asset allocation report', 'erp' ),
				module:       'asset',
				pro:          true,
			},
			{
				id:           'reports-attendance-date',
				label:        __( 'Attendance (Date Based)', 'erp' ),
				to:           '/attendance',
				capabilities: [ 'erp_hr_manager' ],
				description:  __( 'Daily attendance logs', 'erp' ),
				module:       'attendance',
				pro:          true,
			},
			{
				id:           'reports-attendance-employee',
				label:        __( 'Attendance (Employee Based)', 'erp' ),
				to:           '/attendance/reports',
				capabilities: [ 'erp_hr_manager' ],
				description:  __( 'Per-employee attendance report', 'erp' ),
				module:       'attendance',
				pro:          true,
			},
		],
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
