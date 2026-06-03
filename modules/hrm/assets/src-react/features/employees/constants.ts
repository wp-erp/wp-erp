/**
 * Canonical hook + action names for the Employees feature. Pro consumers
 * import these literals to avoid string-drift.
 */

import { HOOKS, ACTIONS } from '@/shared/filters';

export const EMPLOYEES_HOOKS = {
	COLUMNS:       HOOKS.EMPLOYEES_COLUMNS,
	FILTERS:       HOOKS.EMPLOYEES_FILTERS,
	ROW_ACTIONS:   HOOKS.EMPLOYEES_ROW_ACTIONS,
	BULK_ACTIONS:  HOOKS.EMPLOYEES_BULK_ACTIONS,
	TOOLBAR_ITEMS: HOOKS.EMPLOYEES_TOOLBAR_ITEMS,
} as const;

export const EMPLOYEES_ACTIONS = {
	ROW_SELECTED:           ACTIONS.EMPLOYEES_ROW_SELECTED,
	BULK_SELECTION_CHANGED: ACTIONS.EMPLOYEES_BULK_SELECTION,
	REFRESH_REQUESTED:      ACTIONS.EMPLOYEES_REFRESH_REQUESTED,
} as const;

export const DEFAULT_PER_PAGE = 20;
export const SEARCH_DEBOUNCE_MS = 250;

/** Stable column ids — pro can target these to override visibility/priority. */
export const COLUMN_IDS = {
	NAME:        'name',
	EMAIL:       'email',
	DEPARTMENT:  'department',
	DESIGNATION: 'designation',
	STATUS:      'status',
	HIRE_DATE:   'hire_date',
} as const;
