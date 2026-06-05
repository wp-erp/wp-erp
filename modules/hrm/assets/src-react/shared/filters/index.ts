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

	// Employee create/edit form — pro injects extra (custom) field definitions.
	EMPLOYEE_EXTRA_FIELDS:   'erp_hr.employee.extra_fields',

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
