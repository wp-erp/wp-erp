/**
 * Build the toolbar item list. Free ships "Add new employee" CTA (stub —
 * routes to a page that does not exist yet). Pro adds via
 * `wp.hooks.applyFilters('erp_hr.employees.toolbar_items', ...)`.
 */

import { applyFilters, didFilter } from '@wordpress/hooks';
import { useMemo } from 'react';

import { __ } from '@/shared/i18n';
import type {
	ColumnContext,
	EmployeeToolbarItem,
	ToolbarItemsFilter,
} from '@/stores/employees';

import { EMPLOYEES_HOOKS } from './constants';
import { useColumnContext } from './useColumnContext';

export function useEmployeeToolbarItems(): readonly EmployeeToolbarItem[] {
	const { ctx, can } = useColumnContext();
	const filterVersion = didFilter( EMPLOYEES_HOOKS.TOOLBAR_ITEMS );

	const baseItems: EmployeeToolbarItem[] = useMemo(
		() => [
			{
				id:       'add-employee',
				label:    __( 'Add new employee', 'erp' ),
				priority: 10,
				variant:  'primary',
				icon:     'Plus',
				onSelect: () => {
					window.location.hash = '#/employees/new';
				},
				capability: 'erp_create_employee',
			},
		],
		[]
	);

	return useMemo( () => {
		const filter = applyFilters as unknown as (
			name: string,
			value: EmployeeToolbarItem[],
			context: ColumnContext
		) => ReturnType< ToolbarItemsFilter >;
		const merged = filter( EMPLOYEES_HOOKS.TOOLBAR_ITEMS, [ ...baseItems ], ctx );
		return [ ...merged ]
			.filter( ( item ) => ! item.capability || can( item.capability ) )
			.sort( ( a, b ) => a.priority - b.priority );
	}, [ baseItems, ctx, filterVersion, can ] );
}
