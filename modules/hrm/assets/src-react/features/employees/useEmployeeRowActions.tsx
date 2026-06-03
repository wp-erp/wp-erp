/**
 * Build the per-row action list.
 *
 * Free defaults adapt to the current status tab + the row's own status:
 *   - Normal tabs: View / Edit / Terminate (active rows) / Reactivate
 *     (terminated rows) / Move to trash.
 *   - Trash tab:   Restore / Delete permanently.
 *
 * The destructive actions delegate to <EmployeeActionsProvider> (confirm dialogs
 * + terminate form). Pro injects extra actions via
 * `wp.hooks.applyFilters('erp_hr.employees.row_actions', ...)`.
 */

import { useSelect } from '@wordpress/data';
import { applyFilters, didFilter } from '@wordpress/hooks';
import { useMemo } from 'react';

import { __ } from '@/shared/i18n';
import {
	storeName as employeesStoreName,
	getFilters,
} from '@/stores/employees';
import type {
	ColumnContext,
	EmployeeListItem,
	EmployeeRowAction,
	RowActionsFilter,
} from '@/stores/employees';

import { useEmployeeActions } from './actions/EmployeeActionsContext';
import { EMPLOYEES_HOOKS } from './constants';
import { useColumnContext } from './useColumnContext';

interface EmployeesStoreSelect {
	getFilters: () => ReturnType< typeof getFilters >;
}

export function useEmployeeRowActions(
	employee: EmployeeListItem | null
): readonly EmployeeRowAction[] {
	const { ctx, can } = useColumnContext();
	const actions      = useEmployeeActions();
	const filterVersion = didFilter( EMPLOYEES_HOOKS.ROW_ACTIONS );

	const statusFilter = useSelect(
		( select ) =>
			( select( employeesStoreName ) as unknown as EmployeesStoreSelect ).getFilters().status,
		[]
	);

	const baseActions: EmployeeRowAction[] = useMemo( () => {
		const isTrash = statusFilter === 'trash';

		if ( isTrash ) {
			return [
				{
					id:         'restore',
					label:      __( 'Restore', 'erp' ),
					priority:   10,
					icon:       'ArchiveRestore',
					onSelect:   ( emp: EmployeeListItem ) => actions.requestRestore( emp ),
					capability: 'erp_delete_employee',
				},
				{
					id:         'delete-permanently',
					label:      __( 'Delete permanently', 'erp' ),
					priority:   90,
					icon:       'Trash2',
					variant:    'destructive',
					onSelect:   ( emp: EmployeeListItem ) => actions.requestDelete( emp, true ),
					capability: 'erp_delete_employee',
				},
			];
		}

		return [
			{
				id:       'view',
				label:    __( 'View profile', 'erp' ),
				priority: 10,
				icon:     'Eye',
				onSelect: ( emp: EmployeeListItem ) => {
					window.location.hash = `#/employees/${ emp.user_id }`;
				},
				capability: 'erp_view_employee',
			},
			{
				id:       'edit',
				label:    __( 'Edit', 'erp' ),
				priority: 20,
				icon:     'Pencil',
				onSelect: ( emp: EmployeeListItem ) => {
					window.location.hash = `#/employees/${ emp.user_id }/edit`;
				},
				capability: 'erp_edit_employee',
			},
			{
				id:         'reactivate',
				label:      __( 'Reactivate', 'erp' ),
				priority:   30,
				icon:       'UserCheck',
				onSelect:   ( emp: EmployeeListItem ) => actions.requestReactivate( emp ),
				capability: 'erp_edit_employee',
				isVisible:  ( emp: EmployeeListItem ) => emp.status === 'terminated',
			},
			{
				id:         'terminate',
				label:      __( 'Terminate', 'erp' ),
				priority:   40,
				icon:       'UserX',
				variant:    'destructive',
				onSelect:   ( emp: EmployeeListItem ) => actions.requestTerminate( emp ),
				capability: 'erp_can_terminate',
				isVisible:  ( emp: EmployeeListItem ) => emp.status !== 'terminated',
			},
			{
				id:         'trash',
				label:      __( 'Move to trash', 'erp' ),
				priority:   90,
				icon:       'Trash2',
				variant:    'destructive',
				onSelect:   ( emp: EmployeeListItem ) => actions.requestDelete( emp, false ),
				capability: 'erp_delete_employee',
			},
		];
	}, [ actions, statusFilter ] );

	return useMemo( () => {
		if ( ! employee ) {
			return [];
		}
		const filter = applyFilters as unknown as (
			name: string,
			value: EmployeeRowAction[],
			employee: EmployeeListItem,
			context: ColumnContext
		) => ReturnType< RowActionsFilter >;
		const merged = filter( EMPLOYEES_HOOKS.ROW_ACTIONS, [ ...baseActions ], employee, ctx );
		return [ ...merged ]
			.filter( ( action ) => {
				if ( action.capability && ! can( action.capability ) ) {
					return false;
				}
				if ( action.isVisible && ! action.isVisible( employee, ctx ) ) {
					return false;
				}
				return true;
			} )
			.sort( ( a, b ) => a.priority - b.priority );
	}, [ baseActions, employee, ctx, filterVersion, can ] );
}
