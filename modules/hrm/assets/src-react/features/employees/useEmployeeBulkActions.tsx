/**
 * Build the bulk-action list.
 *
 * Free defaults adapt to the current status tab:
 *   - Normal tabs: Move to trash.
 *   - Trash tab:   Restore / Delete permanently.
 *
 * Each action carries `confirm` metadata so the bulk bar can gate it behind an
 * AlertDialog, and an `onSelect` that fires the matching bulk store thunk (a
 * single invalidate after all rows settle), toasts the outcome, then clears the
 * selection. Pro adds via `wp.hooks.applyFilters('erp_hr.employees.bulk_actions', ...)`.
 */

import { toast } from '@wedevs/plugin-ui';
import { useDispatch, useSelect } from '@wordpress/data';
import { applyFilters, didFilter } from '@wordpress/hooks';
import { useMemo } from 'react';

import { __, sprintf } from '@/shared/i18n';
import { storeName as employeesStoreName, getFilters } from '@/stores/employees';
import type {
	BulkActionsFilter,
	ColumnContext,
	EmployeeBulkAction,
} from '@/stores/employees';

import { EMPLOYEES_HOOKS } from './constants';
import { useColumnContext } from './useColumnContext';

interface BulkDispatch {
	bulkDeleteEmployees:  ( ids: readonly number[], force?: boolean ) => Promise< number >;
	bulkRestoreEmployees: ( ids: readonly number[] ) => Promise< number >;
	setSelectedIds:       ( ids: readonly number[] ) => void;
}

interface BulkSelect {
	getFilters: () => ReturnType< typeof getFilters >;
}

export function useEmployeeBulkActions(
	selectedIds: readonly number[]
): readonly EmployeeBulkAction[] {
	const { ctx, can } = useColumnContext();
	const filterVersion = didFilter( EMPLOYEES_HOOKS.BULK_ACTIONS );

	const { bulkDeleteEmployees, bulkRestoreEmployees, setSelectedIds } = useDispatch(
		employeesStoreName
	) as unknown as BulkDispatch;

	const statusFilter = useSelect(
		( select ) => ( select( employeesStoreName ) as unknown as BulkSelect ).getFilters().status,
		[]
	);

	const baseActions = useMemo< EmployeeBulkAction[] >( () => {
		const isTrash = statusFilter === 'trash';

		const notify = ( failed: number, total: number, successMsg: string ): void => {
			setSelectedIds( [] );
			if ( failed > 0 ) {
				toast.error(
					sprintf(
						/* translators: 1: failed count, 2: total count. */
						__( '%1$d of %2$d could not be processed.', 'erp' ),
						failed,
						total
					)
				);
			} else {
				toast.success( successMsg );
			}
		};

		if ( isTrash ) {
			return [
				{
					id:       'bulk-restore',
					label:    __( 'Restore', 'erp' ),
					priority: 10,
					icon:     'ArchiveRestore',
					confirm:  {
						title:        __( 'Restore selected employees?', 'erp' ),
						description:  __( 'The selected employees will be restored to the directory.', 'erp' ),
						confirmLabel: __( 'Restore', 'erp' ),
					},
					onSelect: async ( ids ) => {
						const failed = await bulkRestoreEmployees( ids );
						notify( failed, ids.length, __( 'Selected employees were restored.', 'erp' ) );
					},
					capability: 'erp_delete_employee',
				},
				{
					id:       'bulk-delete-permanently',
					label:    __( 'Delete permanently', 'erp' ),
					priority: 90,
					icon:     'Trash2',
					variant:  'destructive',
					confirm:  {
						title:        __( 'Delete selected permanently?', 'erp' ),
						description:  __( 'The selected employees will be permanently deleted. This cannot be undone.', 'erp' ),
						confirmLabel: __( 'Delete permanently', 'erp' ),
					},
					onSelect: async ( ids ) => {
						const failed = await bulkDeleteEmployees( ids, true );
						notify( failed, ids.length, __( 'Selected employees were permanently deleted.', 'erp' ) );
					},
					capability: 'erp_delete_employee',
				},
			];
		}

		return [
			{
				id:       'bulk-trash',
				label:    __( 'Move to trash', 'erp' ),
				priority: 90,
				icon:     'Trash2',
				variant:  'destructive',
				confirm:  {
					title:        __( 'Move selected to trash?', 'erp' ),
					description:  __( 'The selected employees will be moved to trash. You can restore them later.', 'erp' ),
					confirmLabel: __( 'Move to trash', 'erp' ),
				},
				onSelect: async ( ids ) => {
					const failed = await bulkDeleteEmployees( ids, false );
					notify( failed, ids.length, __( 'Selected employees were moved to trash.', 'erp' ) );
				},
				capability: 'erp_delete_employee',
			},
		];
	}, [ statusFilter, bulkDeleteEmployees, bulkRestoreEmployees, setSelectedIds ] );

	return useMemo( () => {
		const filter = applyFilters as unknown as (
			name: string,
			value: EmployeeBulkAction[],
			selectedIds: readonly number[],
			context: ColumnContext
		) => ReturnType< BulkActionsFilter >;
		const merged = filter( EMPLOYEES_HOOKS.BULK_ACTIONS, [ ...baseActions ], selectedIds, ctx );
		return [ ...merged ]
			.filter( ( action ) => ! action.capability || can( action.capability ) )
			.sort( ( a, b ) => a.priority - b.priority );
	}, [ baseActions, selectedIds, ctx, filterVersion, can ] );
}
