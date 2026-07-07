/**
 * Canonical `wp.hooks` filter + action names exposed by the free HR shell.
 *
 * Pro consumers import from this module (build-time) or read from
 * `window.__ERP_HR_BOOT__.filters` (runtime) — both surfaces stay in sync.
 *
 * Contract locked at openspec/changes/redesign-hr-free/playbooks/_first-deliverable.md
 * §Pro extension contract.
 */

export const HOOKS = {
	// Shell-wide filters (top bar, routes, user menu)
	TOPBAR_RIGHT_ITEMS: 'erp_hr.topbar.right_items',
	USER_MENU_ITEMS:    'erp_hr.user_menu.items',
	ROUTES:             'erp_hr.routes',
	NAV_GROUPS:         'erp_hr.nav.groups',
	// Dashboard widgets — pro modules append a React component (e.g. the
	// Attendance self-service widget). Applied lazily at render.
	DASHBOARD_WIDGETS:  'erp_hr.dashboard.widgets',

	// People → Requests tabs. Free seeds the Leave tab; pro modules append their
	// own request type (Asset, Reimbursement, …) — mirrors the legacy
	// `erp_hr_employee_request_types` filter. Each tab: { id, label, element }.
	REQUEST_TABS:       'erp_hr.request_tabs',

	// Employee create/edit form — pro injects extra (custom) field definitions.
	EMPLOYEE_EXTRA_FIELDS:   'erp_hr.employee.extra_fields',

	// Leave policy create/edit form — pro injects extra field definitions
	// (Advanced Leave: half-day, accrual, carry-forward, segregation). Applied
	// with `(fields, ctx)` where ctx = { mode, saved } (saved = the policy
	// response incl. pro columns, for edit prefill). Returns LeaveExtraField[].
	LEAVE_POLICY_FIELDS:     'erp_hr.leave.policy_fields',

	// Leave request (apply-for-leave) form — pro injects extra field definitions
	// (Advanced Leave: half-day request). Applied with `(fields, ctx)` where
	// ctx = { userId, leavePolicyId, halfdayEnabled? } (halfdayEnabled = the
	// selected policy's per-policy halfday flag, once one is chosen). Returns
	// LeaveExtraField[].
	LEAVE_REQUEST_FIELDS:    'erp_hr.leave.request_fields',

	// Leave-request row actions — pro appends dropdown actions to a request row
	// (Advanced Leave multilevel: "Forward"). Applied with `(actions, ctx)`
	// where ctx = { request }. Returns LeaveRequestRowAction[].
	LEAVE_REQUEST_ROW_ACTIONS:     'erp_hr.leave.request_row_actions',

	// Leave-request moderate dialog — pro injects extra nodes shown alongside the
	// approve/reject form (Advanced Leave multilevel: the approval chain).
	// Applied with `(nodes, ctx)` where ctx = { request }. Returns ReactNode[].
	LEAVE_REQUEST_MODERATE_EXTRAS: 'erp_hr.leave.moderate_extras',

	// Employee profile — pro injects / replaces profile tabs (e.g. Documents).
	// Applied lazily at render with `(tabs, ctx)` where ctx = { userId, canEdit }.
	// Free seeds a `documents` preview tab; the Document Manager pro module swaps
	// its `render` with the real file manager when active.
	EMPLOYEE_PROFILE_TABS:   'erp_hr.employee.profile.tabs',

	// Employees feature filters
	EMPLOYEES_COLUMNS:       'erp_hr.employees.columns',
	EMPLOYEES_FILTERS:       'erp_hr.employees.filters',
	EMPLOYEES_ROW_ACTIONS:   'erp_hr.employees.row_actions',
	EMPLOYEES_BULK_ACTIONS:  'erp_hr.employees.bulk_actions',
	EMPLOYEES_TOOLBAR_ITEMS: 'erp_hr.employees.toolbar_items',
} as const;

export const ACTIONS = {
	CAPS_CHANGED:                    'erp_hr.caps.changed',
	THEME_CHANGED:                   'erp_hr.theme.changed',
	SHELL_READY:                     'erp_hr.shell.ready',
	EMPLOYEES_ROW_SELECTED:          'erp_hr.employees.row_selected',
	EMPLOYEES_BULK_SELECTION:        'erp_hr.employees.bulk_selection_changed',
	EMPLOYEES_REFRESH_REQUESTED:     'erp_hr.employees.refresh_requested',
} as const;

export type HookName   = ( typeof HOOKS )[ keyof typeof HOOKS ];
export type ActionName = ( typeof ACTIONS )[ keyof typeof ACTIONS ];

/**
 * Shared text domain for every `__()` / `_x()` / `_n()` call inside this bundle.
 *
 * Kept here (not in i18n module) so pro consumers can import the constant
 * without pulling in the i18n facade.
 */
export const I18N_DOMAIN = 'erp' as const;
