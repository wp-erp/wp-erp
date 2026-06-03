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
import { X } from 'lucide-react';
import { useState } from 'react';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import type { EmployeeBulkAction } from '@/stores/employees';

import { useEmployeeBulkActions } from './useEmployeeBulkActions';

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
			<button
				type="button"
				onClick={ () => setSelectedIds( [] ) }
				aria-label={ __( 'Clear selection', 'erp' ) }
				className="inline-flex size-6 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
			>
				<X size={ 14 } aria-hidden="true" />
			</button>
			<span className="text-sm font-medium text-foreground">
				{ sprintf(
					/* translators: %d: number of selected employees. */
					__( '%d selected', 'erp' ),
					selectedIds.length
				) }
			</span>

			<div className="ms-auto flex items-center gap-2">
				{ actions.map( ( action ) => (
					<Button
						key={ action.id }
						size="sm"
						variant={ action.variant === 'destructive' ? 'destructive' : 'outline' }
						disabled={ busy }
						onClick={ () => trigger( action ) }
					>
						{ action.label }
					</Button>
				) ) }
			</div>

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
