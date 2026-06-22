/**
 * `/leave/entitlements` route — leave-entitlement management.
 *
 * Lists assigned entitlements (employee × policy × year) with year / policy /
 * search filters, an Assign action, and per-row delete. Assign + delete
 * delegate (server-side) to the unchanged v1 model layer, so the
 * already-assigned / employee-active guards and the cascade delete all stay.
 *
 * Orchestration only: filters live in `LeaveEntitlementsFilters`, the list in
 * `LeaveEntitlementsTable`, data in `useEntitlements`.
 */

import {
	Button,
	toast,
} from '@wedevs/plugin-ui';
import { Plus, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import { TableSkeleton } from '@/shared/components/TableSkeleton';
import type { JSX } from 'react';

import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { useCan } from '@/shared/hooks/useCan';
import { __, sprintf } from '@/shared/i18n';
import { useModalParam } from '@/shared/useModalParam';
import type { ApiError } from '@/shared/utils/apiFetch';

import { OrgDeleteDialog } from '../org/OrgDeleteDialog';
import { OrgPagination } from '../org/OrgPagination';
import { EntitlementAssignDialog } from './EntitlementAssignDialog';
import { LeaveEntitlementsFilters } from './LeaveEntitlementsFilters';
import { LeaveEntitlementsTable } from './LeaveEntitlementsTable';
import type { Entitlement, EntitlementAssignInput, IdOption } from './types';
import { useEntitlements } from './useEntitlements';

function LeaveEntitlementsInner(): JSX.Element {
	const canManage = useCan( 'erp_leave_manage' );

	// Year filter defaults to all years (a FY dropdown can be layered on later).
	const year = 0;
	const [ policyId, setPolicyId ]   = useState( 0 );
	const [ search, setSearch ]       = useState( '' );
	const [ showFilters, setShowFilters ] = useState( false );
	const [ page, setPage ]           = useState( 1 );
	const [ perPage, setPerPage ]     = useState( 20 );

	const { rows, total, loading, error, assign, remove, bulkRemove, loadPolicies, loadEmployees } = useEntitlements( {
		year,
		policyId,
		employeeType: '',
		search,
		page,
		perPage,
	} );

	const [ policies, setPolicies ]   = useState< readonly IdOption[] >( [] );
	// Assign modal open-state lives in the URL (`?assign=open`) so a browser
	// refresh re-opens it.
	const [ assignParam, setAssignParam ] = useModalParam( 'assign' );
	const [ deleting, setDeleting ]   = useState< Entitlement | null >( null );
	const [ busy, setBusy ]           = useState( false );
	const [ formError, setFormError ] = useState< string | null >( null );
	const [ selected, setSelected ]   = useState< ReadonlySet< number > >( new Set() );
	const [ bulkDeleting, setBulkDeleting ] = useState( false );

	const totalPages = Math.max( 1, Math.ceil( total / perPage ) );

	useEffect( () => {
		setPage( 1 );
	}, [ year, policyId, search ] );

	// Prime the policy list (filter dropdown + assign dialog).
	useEffect( () => {
		let active = true;
		void loadPolicies().then( ( list ) => {
			if ( active ) {
				setPolicies( list );
			}
		} );
		return () => {
			active = false;
		};
	}, [ loadPolicies ] );

	async function handleAssign( payload: EntitlementAssignInput ): Promise< void > {
		setBusy( true );
		setFormError( null );
		try {
			const res = await assign( payload );
			if ( res.affected > 0 ) {
				toast.success( sprintf( __( '%d entitlement(s) assigned.', 'erp' ), res.affected ) );
			}
			if ( res.errors.length > 0 ) {
				toast.error( res.errors.join( ' ' ) );
			}
			setAssignParam( null );
		} catch ( raw ) {
			setFormError( ( raw as ApiError )?.message ?? __( 'Could not assign the policy.', 'erp' ) );
		} finally {
			setBusy( false );
		}
	}

	async function handleDelete(): Promise< void > {
		if ( ! deleting || ! deleting.user_id ) {
			return;
		}
		setBusy( true );
		try {
			await remove( deleting.id, deleting.user_id );
			toast.success( __( 'Entitlement deleted.', 'erp' ) );
			setDeleting( null );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not delete the entitlement.', 'erp' ) );
			setDeleting( null );
		} finally {
			setBusy( false );
		}
	}

	// Clear the selection whenever the visible rows change (filter / page / reload).
	useEffect( () => {
		setSelected( new Set() );
	}, [ rows ] );

	const allOnPageSelected = rows.length > 0 && rows.every( ( r ) => selected.has( r.id ) );

	function toggleAll(): void {
		setSelected( allOnPageSelected ? new Set() : new Set( rows.map( ( r ) => r.id ) ) );
	}

	function toggleOne( id: number ): void {
		setSelected( ( prev ) => {
			const next = new Set( prev );
			if ( next.has( id ) ) {
				next.delete( id );
			} else {
				next.add( id );
			}
			return next;
		} );
	}

	async function runBulkDelete(): Promise< void > {
		const ids = [ ...selected ];
		if ( ids.length === 0 ) {
			return;
		}
		setBusy( true );
		try {
			await bulkRemove( ids );
			toast.success( __( 'Selected entitlements deleted.', 'erp' ) );
			setSelected( new Set() );
			setBulkDeleting( false );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Bulk delete failed.', 'erp' ) );
		} finally {
			setBusy( false );
		}
	}

	return (
		<section className="mx-auto w-full max-w-full">
			<header className="mb-6 flex items-center justify-between gap-4">
				<h1 className="text-2xl font-bold leading-8 text-foreground">
					{ __( 'Leave Entitlements', 'erp' ) }
				</h1>
				{ canManage ? (
					<Button
						onClick={ () => { setFormError( null ); setAssignParam( 'open' ); } }
						variant="default"
						className="inline-flex h-10 items-center gap-2 rounded-md px-5 text-sm font-medium leading-5 shadow-sm"
					>
						<Plus size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
						{ __( 'Assign Policy', 'erp' ) }
					</Button>
				) : null }
			</header>

			<div className="rounded-lg border border-border bg-card shadow-sm">
				<LeaveEntitlementsFilters
					total={ total }
					search={ search }
					onSearch={ setSearch }
					policyId={ policyId }
					onPolicyId={ setPolicyId }
					showFilters={ showFilters }
					onToggleFilters={ () => setShowFilters( ( prev ) => ! prev ) }
					policies={ policies }
				/>

				{ canManage && selected.size > 0 ? (
					<div className="flex flex-wrap items-center gap-3 border-b border-border bg-primary/5 px-4 py-2.5">
						<span className="text-sm font-medium text-foreground">
							{ sprintf( __( '%d selected', 'erp' ), selected.size ) }
						</span>
						<Button size="sm" variant="outline" disabled={ busy } onClick={ () => setBulkDeleting( true ) } className="h-8 gap-1.5 text-destructive hover:text-destructive">
							<Trash2 size={ 14 } aria-hidden="true" /> { __( 'Delete', 'erp' ) }
						</Button>
						<button type="button" className="text-sm text-muted-foreground hover:text-foreground" onClick={ () => setSelected( new Set() ) }>
							{ __( 'Clear', 'erp' ) }
						</button>
					</div>
				) : null }

				{ error ? (
					<p className="p-6 text-sm text-destructive">{ error }</p>
				) : loading ? (
					<TableSkeleton rows={ 6 } />
				) : rows.length === 0 ? (
					<p className="p-10 text-center text-sm text-muted-foreground">
						{ search || policyId
							? __( 'No entitlements match these filters.', 'erp' )
							: __( 'No entitlements assigned yet.', 'erp' ) }
					</p>
				) : (
					<LeaveEntitlementsTable
						rows={ rows }
						canManage={ canManage }
						selected={ selected }
						allOnPageSelected={ allOnPageSelected }
						onToggleAll={ toggleAll }
						onToggleOne={ toggleOne }
						onDelete={ setDeleting }
					/>
				) }

				{ ! error && ! loading && total > 0 ? (
					<OrgPagination
						page={ page }
						totalPages={ totalPages }
						total={ total }
						perPage={ perPage }
						onPage={ setPage }
						onPerPage={ ( n ) => { setPerPage( n ); setPage( 1 ); } }
					/>
				) : null }
			</div>

			<EntitlementAssignDialog
				open={ assignParam !== null }
				policies={ policies }
				busy={ busy }
				error={ formError }
				onClose={ () => setAssignParam( null ) }
				onSubmit={ ( payload ) => void handleAssign( payload ) }
				loadEmployees={ loadEmployees }
			/>

			<OrgDeleteDialog
				open={ deleting !== null }
				title={ __( 'Delete entitlement?', 'erp' ) }
				description={
					deleting
						? sprintf(
								__( 'The %1$s entitlement for %2$s will be deleted along with any dependent leave requests. This cannot be undone.', 'erp' ),
								deleting.policy_name,
								deleting.employee_name
						  )
						: ''
				}
				busy={ busy }
				onConfirm={ () => void handleDelete() }
				onCancel={ () => setDeleting( null ) }
			/>

			<OrgDeleteDialog
				open={ bulkDeleting }
				title={ __( 'Delete selected entitlements?', 'erp' ) }
				description={ sprintf(
					__( '%d entitlement(s) will be deleted along with any dependent leave requests. This cannot be undone.', 'erp' ),
					selected.size
				) }
				busy={ busy }
				onConfirm={ () => void runBulkDelete() }
				onCancel={ () => setBulkDeleting( false ) }
			/>
		</section>
	);
}

export function LeaveEntitlementsPage(): JSX.Element {
	return (
		<CapabilityGate caps={ [ 'erp_leave_manage' ] }>
			<ErrorBoundary>
				<LeaveEntitlementsInner />
			</ErrorBoundary>
		</CapabilityGate>
	);
}
