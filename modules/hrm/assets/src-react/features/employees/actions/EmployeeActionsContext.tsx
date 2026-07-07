/**
 * Imperative controller for the destructive employee row/bulk actions.
 *
 * The kebab row menu and the bulk toolbar only build plain action *descriptors*
 * (id + label + capability). To actually confirm + run a mutation they call into
 * this provider, which owns the confirm dialogs (delete / restore / reactivate)
 * and the terminate form. Keeping the dialogs here — mounted once, high in the
 * tree — means every row reuses the same modal instead of mounting one per row.
 *
 * Each action delegates to a v2 store thunk that wraps the unchanged v1 model
 * layer; on success the store invalidates so the list + counts refetch.
 */

import { useDispatch } from '@wordpress/data';
import {
	AlertDialog,
	AlertDialogAction,
	AlertDialogCancel,
	AlertDialogContent,
	AlertDialogDescription,
	AlertDialogFooter,
	AlertDialogHeader,
	AlertDialogTitle,
	Alert,
	AlertDescription,
	toast,
} from '@wedevs/plugin-ui';
import { createContext, useCallback, useContext, useMemo, useState } from 'react';
import type { JSX, ReactNode } from 'react';

import { __, sprintf } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import type { EmployeeListItem, EmployeeTerminateInput } from '@/stores/employees';

import { TerminateDialog } from './TerminateDialog';

interface EmployeeActionsApi {
	readonly requestDelete:     ( employee: EmployeeListItem, force?: boolean ) => void;
	readonly requestRestore:    ( employee: EmployeeListItem ) => void;
	readonly requestTerminate:  ( employee: EmployeeListItem ) => void;
	readonly requestReactivate: ( employee: EmployeeListItem ) => void;
}

interface ActionsDispatch {
	deleteEmployee:     ( userId: number, force?: boolean ) => Promise< void >;
	restoreEmployee:    ( userId: number ) => Promise< void >;
	terminateEmployee:  ( userId: number, payload: EmployeeTerminateInput ) => Promise< void >;
	reactivateEmployee: ( userId: number ) => Promise< void >;
}

type Pending =
	| { readonly kind: 'delete'; readonly employee: EmployeeListItem; readonly force: boolean }
	| { readonly kind: 'restore'; readonly employee: EmployeeListItem }
	| { readonly kind: 'terminate'; readonly employee: EmployeeListItem }
	| { readonly kind: 'reactivate'; readonly employee: EmployeeListItem };

const EmployeeActionsContext = createContext< EmployeeActionsApi | null >( null );

/**
 * Read the action controller. Throws if used outside the provider so a missing
 * wrapper fails loudly in development rather than silently no-op'ing.
 */
export function useEmployeeActions(): EmployeeActionsApi {
	const ctx = useContext( EmployeeActionsContext );
	if ( ! ctx ) {
		throw new Error( 'useEmployeeActions must be used within <EmployeeActionsProvider>' );
	}
	return ctx;
}

interface ProviderProps {
	readonly children: ReactNode;
}

export function EmployeeActionsProvider( { children }: ProviderProps ): JSX.Element {
	const dispatch = useDispatch( employeesStoreName ) as unknown as ActionsDispatch;

	const [ pending, setPending ] = useState< Pending | null >( null );
	const [ busy, setBusy ]       = useState( false );
	const [ error, setError ]     = useState< string | null >( null );

	const api = useMemo< EmployeeActionsApi >(
		() => ( {
			requestDelete: ( employee, force = false ) => {
				setError( null );
				setPending( { kind: 'delete', employee, force } );
			},
			requestRestore: ( employee ) => {
				setError( null );
				setPending( { kind: 'restore', employee } );
			},
			requestTerminate: ( employee ) => {
				setError( null );
				setPending( { kind: 'terminate', employee } );
			},
			requestReactivate: ( employee ) => {
				setError( null );
				setPending( { kind: 'reactivate', employee } );
			},
		} ),
		[]
	);

	// Closing is blocked while a mutation is in flight so a half-finished request
	// can't be orphaned by an accidental outside-click.
	const close = useCallback( () => {
		if ( busy ) {
			return;
		}
		setPending( null );
		setError( null );
	}, [ busy ] );

	const run = useCallback(
		async ( fn: () => Promise< void >, successMsg: string, fallbackErr: string ): Promise< void > => {
			setBusy( true );
			setError( null );
			try {
				await fn();
				toast.success( successMsg );
				setPending( null );
			} catch ( raw ) {
				const message = ( raw as { message?: string } )?.message || fallbackErr;
				setError( message );
			} finally {
				setBusy( false );
			}
		},
		[]
	);

	const confirmDelete = useCallback( () => {
		if ( ! pending || pending.kind !== 'delete' ) {
			return;
		}
		const { employee, force } = pending;
		void run(
			() => dispatch.deleteEmployee( employee.user_id, force ),
			force
				? sprintf( __( '%s was permanently deleted.', 'erp' ), employee.full_name )
				: sprintf( __( '%s was moved to trash.', 'erp' ), employee.full_name ),
			__( 'Could not delete the employee. Please try again.', 'erp' )
		);
	}, [ pending, run, dispatch ] );

	const confirmRestore = useCallback( () => {
		if ( ! pending || pending.kind !== 'restore' ) {
			return;
		}
		const { employee } = pending;
		void run(
			() => dispatch.restoreEmployee( employee.user_id ),
			sprintf( __( '%s was restored.', 'erp' ), employee.full_name ),
			__( 'Could not restore the employee. Please try again.', 'erp' )
		);
	}, [ pending, run, dispatch ] );

	const confirmReactivate = useCallback( () => {
		if ( ! pending || pending.kind !== 'reactivate' ) {
			return;
		}
		const { employee } = pending;
		void run(
			() => dispatch.reactivateEmployee( employee.user_id ),
			sprintf( __( '%s was reactivated.', 'erp' ), employee.full_name ),
			__( 'Could not reactivate the employee. Please try again.', 'erp' )
		);
	}, [ pending, run, dispatch ] );

	const submitTerminate = useCallback(
		( payload: EmployeeTerminateInput ) => {
			if ( ! pending || pending.kind !== 'terminate' ) {
				return;
			}
			const { employee } = pending;
			void run(
				() => dispatch.terminateEmployee( employee.user_id, payload ),
				sprintf( __( '%s was terminated.', 'erp' ), employee.full_name ),
				__( 'Could not terminate the employee. Please try again.', 'erp' )
			);
		},
		[ pending, run, dispatch ]
	);

	const isDelete     = pending?.kind === 'delete';
	const isRestore    = pending?.kind === 'restore';
	const isReactivate = pending?.kind === 'reactivate';
	const isTerminate  = pending?.kind === 'terminate';
	const name         = pending?.employee.full_name ?? '';

	return (
		<EmployeeActionsContext.Provider value={ api }>
			{ children }

			{ /* Delete / move-to-trash confirmation. */ }
			<AlertDialog open={ isDelete } onOpenChange={ ( open ) => ( open ? undefined : close() ) }>
				<AlertDialogContent>
					<AlertDialogHeader>
						<AlertDialogTitle>
							{ isDelete && pending.force
								? __( 'Delete permanently?', 'erp' )
								: __( 'Move to trash?', 'erp' ) }
						</AlertDialogTitle>
						<AlertDialogDescription>
							{ isDelete && pending.force
								? sprintf(
										__( '%s will be permanently deleted. This action cannot be undone.', 'erp' ),
										name
								  )
								: sprintf(
										__( '%s will be moved to trash. You can restore them later.', 'erp' ),
										name
								  ) }
						</AlertDialogDescription>
					</AlertDialogHeader>
					{ error && isDelete ? (
						<Alert variant="destructive">
							<AlertDescription>{ error }</AlertDescription>
						</Alert>
					) : null }
					<AlertDialogFooter>
						<AlertDialogCancel disabled={ busy } onClick={ close }>
							{ __( 'Cancel', 'erp' ) }
						</AlertDialogCancel>
						<AlertDialogAction variant="destructive" disabled={ busy } onClick={ confirmDelete }>
							{ busy
								? __( 'Deleting…', 'erp' )
								: isDelete && pending.force
								? __( 'Delete permanently', 'erp' )
								: __( 'Move to trash', 'erp' ) }
						</AlertDialogAction>
					</AlertDialogFooter>
				</AlertDialogContent>
			</AlertDialog>

			{ /* Restore-from-trash confirmation. */ }
			<AlertDialog open={ isRestore } onOpenChange={ ( open ) => ( open ? undefined : close() ) }>
				<AlertDialogContent>
					<AlertDialogHeader>
						<AlertDialogTitle>{ __( 'Restore employee?', 'erp' ) }</AlertDialogTitle>
						<AlertDialogDescription>
							{ sprintf( __( '%s will be restored to the directory.', 'erp' ), name ) }
						</AlertDialogDescription>
					</AlertDialogHeader>
					{ error && isRestore ? (
						<Alert variant="destructive">
							<AlertDescription>{ error }</AlertDescription>
						</Alert>
					) : null }
					<AlertDialogFooter>
						<AlertDialogCancel disabled={ busy } onClick={ close }>
							{ __( 'Cancel', 'erp' ) }
						</AlertDialogCancel>
						<AlertDialogAction disabled={ busy } onClick={ confirmRestore }>
							{ busy ? __( 'Restoring…', 'erp' ) : __( 'Restore', 'erp' ) }
						</AlertDialogAction>
					</AlertDialogFooter>
				</AlertDialogContent>
			</AlertDialog>

			{ /* Reactivate (reverse termination) confirmation. */ }
			<AlertDialog open={ isReactivate } onOpenChange={ ( open ) => ( open ? undefined : close() ) }>
				<AlertDialogContent>
					<AlertDialogHeader>
						<AlertDialogTitle>{ __( 'Reactivate employee?', 'erp' ) }</AlertDialogTitle>
						<AlertDialogDescription>
							{ sprintf(
								__( "%s's termination will be reversed and their status set to active.", 'erp' ),
								name
							) }
						</AlertDialogDescription>
					</AlertDialogHeader>
					{ error && isReactivate ? (
						<Alert variant="destructive">
							<AlertDescription>{ error }</AlertDescription>
						</Alert>
					) : null }
					<AlertDialogFooter>
						<AlertDialogCancel disabled={ busy } onClick={ close }>
							{ __( 'Cancel', 'erp' ) }
						</AlertDialogCancel>
						<AlertDialogAction disabled={ busy } onClick={ confirmReactivate }>
							{ busy ? __( 'Reactivating…', 'erp' ) : __( 'Reactivate', 'erp' ) }
						</AlertDialogAction>
					</AlertDialogFooter>
				</AlertDialogContent>
			</AlertDialog>

			{ /* Terminate form. */ }
			<TerminateDialog
				open={ isTerminate }
				employeeName={ name }
				busy={ busy }
				error={ isTerminate ? error : null }
				onClose={ close }
				onSubmit={ submitTerminate }
			/>
		</EmployeeActionsContext.Provider>
	);
}
