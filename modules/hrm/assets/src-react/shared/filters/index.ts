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
