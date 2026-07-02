/**
 * Bulk-action bar.
 *
 * Appears above the table once one or more rows are selected. Renders the
 * status-aware bulk actions from `useEmployeeBulkActions` and gates any action
 * carrying `confirm` metadata behind a shared AlertDialog before running it.
 */

import {
	AlertDialog,
	AlertDialogAction,
	AlertDialogCancel,
	AlertDialogContent,
	AlertDialogDescription,
	AlertDialogFooter,
	AlertDialogHeader,
	AlertDialogTitle,
	Button,
} from '@wedevs/plugin-ui';
import { useDispatch, useSelect } from '@wordpress/data';
import { ArchiveRestore, Trash2 } from 'lucide-react';
import { useState } from 'react';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import type { EmployeeBulkAction } from '@/stores/employees';

import { useEmployeeBulkActions } from './useEmployeeBulkActions';

const ICON_MAP: Record< string, typeof Trash2 > = {
	ArchiveRestore,
	Trash2,
};

interface BulkBarSelectors {
	getSelectedIds: () => readonly number[];
}

interface BulkBarDispatch {
	setSelectedIds: ( ids: readonly number[] ) => void;
}

export function EmployeesBulkBar(): JSX.Element | null {
	const selectedIds = useSelect(
		( select ) => ( select( employeesStoreName ) as unknown as BulkBarSelectors ).getSelectedIds(),
		[]
	);
	const { setSelectedIds } = useDispatch( employeesStoreName ) as unknown as BulkBarDispatch;
	const actions = useEmployeeBulkActions( selectedIds );

	const [ pending, setPending ] = useState< EmployeeBulkAction | null >( null );
	const [ busy, setBusy ]       = useState( false );

	if ( selectedIds.length === 0 || actions.length === 0 ) {
		return null;
	}

	const trigger = ( action: EmployeeBulkAction ): void => {
		if ( action.confirm ) {
			setPending( action );
			return;
		}
		void run( action );
	};

	async function run( action: EmployeeBulkAction ): Promise< void > {
		setBusy( true );
		try {
			await action.onSelect( selectedIds );
			setPending( null );
		} finally {
			setBusy( false );
		}
	}

	return (
		<div className="flex flex-wrap items-center gap-3 border-b border-border bg-primary/5 px-4 py-2.5">
			<span className="text-sm font-medium text-foreground">
				{ sprintf(
					/* translators: %d: number of selected employees. */
					__( '%d selected', 'erp' ),
					selectedIds.length
				) }
			</span>

			<div className="flex items-center gap-2">
				{ actions.map( ( action ) => {
					const Icon = action.icon ? ICON_MAP[ action.icon ] : undefined;
					const isDestructive = action.variant === 'destructive';
					return (
						<Button
							key={ action.id }
							size="sm"
							variant="outline"
							disabled={ busy }
							onClick={ () => trigger( action ) }
							className={ `h-8 gap-1.5${
								isDestructive
									? ' border-destructive text-destructive hover:border-destructive hover:text-destructive'
									: ''
							}` }
						>
							{ Icon ? <Icon size={ 14 } aria-hidden="true" /> : null }
							{ action.label }
						</Button>
					);
				} ) }
			</div>

			<button
				type="button"
				className="text-sm text-muted-foreground hover:text-foreground"
				onClick={ () => setSelectedIds( [] ) }
			>
				{ __( 'Clear', 'erp' ) }
			</button>

			<AlertDialog
				open={ pending !== null }
				onOpenChange={ ( open ) => ( open || busy ? undefined : setPending( null ) ) }
			>
				<AlertDialogContent>
					<AlertDialogHeader>
						<AlertDialogTitle>
							{ pending?.confirm?.title ?? __( 'Are you sure?', 'erp' ) }
						</AlertDialogTitle>
						<AlertDialogDescription>
							{ pending?.confirm?.description ?? '' }
						</AlertDialogDescription>
					</AlertDialogHeader>
					<AlertDialogFooter>
						<AlertDialogCancel disabled={ busy } onClick={ () => setPending( null ) }>
							{ __( 'Cancel', 'erp' ) }
						</AlertDialogCancel>
						<AlertDialogAction
							variant={ pending?.variant === 'destructive' ? 'destructive' : 'default' }
							disabled={ busy }
							onClick={ () => pending && void run( pending ) }
						>
							{ busy
								? __( 'Working…', 'erp' )
								: pending?.confirm?.confirmLabel ?? __( 'Confirm', 'erp' ) }
						</AlertDialogAction>
					</AlertDialogFooter>
				</AlertDialogContent>
			</AlertDialog>
		</div>
	);
}
