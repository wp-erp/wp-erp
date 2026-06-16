/**
 * `/leave/entitlements` route — leave-entitlement management.
 *
 * Lists assigned entitlements (employee × policy × year) with year / policy /
 * search filters, an Assign action, and per-row delete. Assign + delete
 * delegate (server-side) to the unchanged v1 model layer, so the
 * already-assigned / employee-active guards and the cascade delete all stay.
 */

import {
	Button,
	Checkbox,
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuItem,
	DropdownMenuTrigger,
	Input,
	SmartSelect,
	toast,
} from '@wedevs/plugin-ui';
import { Filter, MoreVertical, Plus, Search, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
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

	const policyFilterOpts = [
		{ value: '', label: __( 'All Policies', 'erp' ) },
		...policies.map( ( p ) => ( { value: String( p.value ), label: p.label } ) ),
	];

	const activeFilterCount  = policyId ? 1 : 0;
	const filterButtonActive = showFilters || activeFilterCount > 0;

	return (
		<section className="mx-auto w-full max-w-7xl">
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
				<div className="flex flex-wrap items-center justify-between gap-3 border-b border-border px-4 pt-3 pb-2">
					<div role="tablist" aria-label={ __( 'Leave Entitlements', 'erp' ) } className="flex items-stretch">
						<span role="tab" aria-selected="true" className="relative inline-flex h-11 items-center gap-1.5 px-4 text-sm font-medium text-primary">
							<span>{ __( 'All', 'erp' ) }</span>
							<span className="font-normal text-muted-foreground">({ total })</span>
							<span aria-hidden="true" className="absolute inset-x-0 -bottom-2 h-0.5 bg-primary" />
						</span>
					</div>
					<div className="flex items-center gap-3">
						<div className="relative">
							<Search
								size={ 16 }
								aria-hidden="true"
								className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"
							/>
							<Input
								type="search"
								value={ search }
								onChange={ ( e ) => setSearch( e.target.value ) }
								placeholder={ __( 'Search employees…', 'erp' ) }
								className="h-9 w-56 rounded-md border-border pl-9 text-sm"
								aria-label={ __( 'Search entitlements by employee', 'erp' ) }
							/>
						</div>
						<button
							type="button"
							aria-label={ __( 'Toggle filters', 'erp' ) }
							aria-pressed={ filterButtonActive }
							onClick={ () => setShowFilters( ( prev ) => ! prev ) }
							className={ [
								'inline-flex h-9 items-center gap-2 rounded-md border bg-card px-3 text-sm font-medium transition-colors',
								filterButtonActive ? 'border-primary text-primary' : 'border-border text-muted-foreground hover:text-foreground',
							].join( ' ' ) }
						>
							<Filter size={ 16 } strokeWidth={ 1.75 } aria-hidden="true" />
							<span>{ __( 'Filter', 'erp' ) }</span>
							{ activeFilterCount > 0 ? (
								<span className="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1.5 text-xs font-medium text-primary-foreground">
									{ activeFilterCount }
								</span>
							) : null }
						</button>
					</div>
				</div>

				{ filterButtonActive ? (
					<div className="flex flex-wrap items-center gap-2 border-b border-border bg-muted/20 px-4 py-3">
						<label className="flex items-center gap-2 text-sm text-muted-foreground">
							{ __( 'Policy', 'erp' ) }
							<SmartSelect
								options={ policyFilterOpts }
								value={ String( policyId || '' ) }
								onValueChange={ ( v ) => setPolicyId( Number( v || 0 ) ) }
								placeholder={ __( 'All Policies', 'erp' ) }
								showClear
								className="h-9 w-52 bg-background"
								contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
							/>
						</label>
					</div>
				) : null }

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
					<p className="p-6 text-sm text-muted-foreground">{ __( 'Loading…', 'erp' ) }</p>
				) : rows.length === 0 ? (
					<p className="p-10 text-center text-sm text-muted-foreground">
						{ search || policyId
							? __( 'No entitlements match these filters.', 'erp' )
							: __( 'No entitlements assigned yet.', 'erp' ) }
					</p>
				) : (
					<div className="overflow-x-auto">
						<table className="w-full min-w-[40rem] text-left">
						<thead className="border-b border-border bg-muted/40">
							<tr className="h-10 text-xs font-medium uppercase tracking-normal text-muted-foreground">
								{ canManage ? (
									<th scope="col" className="w-10 px-4">
										<Checkbox checked={ allOnPageSelected } onCheckedChange={ toggleAll } aria-label={ __( 'Select all', 'erp' ) } />
									</th>
								) : null }
								<th scope="col" className="px-2">{ __( 'Employee', 'erp' ) }</th>
								<th scope="col" className="px-2">{ __( 'Policy', 'erp' ) }</th>
								<th scope="col" className="px-2">{ __( 'Days', 'erp' ) }</th>
								<th scope="col" className="px-2">{ __( 'Available', 'erp' ) }</th>
								<th scope="col" className="px-2">{ __( 'Spent', 'erp' ) }</th>
								<th scope="col" className="w-20 px-4">
									<span className="sr-only">{ __( 'Actions', 'erp' ) }</span>
								</th>
							</tr>
						</thead>
						<tbody>
							{ rows.map( ( ent ) => (
								<tr key={ ent.id } className="h-18 border-b border-border last:border-b-0 hover:bg-muted/40">
									{ canManage ? (
										<td className="w-10 px-4 align-middle">
											<Checkbox checked={ selected.has( ent.id ) } onCheckedChange={ () => toggleOne( ent.id ) } aria-label={ sprintf( __( 'Select %s', 'erp' ), ent.employee_name ) } />
										</td>
									) : null }
									<td className="px-2 align-middle font-medium text-foreground">
										{ ent.employee_name || <span className="text-muted-foreground">—</span> }
									</td>
									<td className="px-2 align-middle text-sm text-foreground">{ ent.policy_name }</td>
									<td className="px-2 align-middle text-sm text-foreground">{ ent.days }</td>
									<td className="px-2 align-middle text-sm text-foreground">{ ent.available }</td>
									<td className="px-2 align-middle text-sm text-muted-foreground">{ ent.spent }</td>
									<td className="px-4 align-middle">
										{ canManage ? (
											<div className="flex justify-end">
												<DropdownMenu>
													<DropdownMenuTrigger
														render={
															<Button variant="ghost" size="icon" aria-label={ sprintf( __( 'Actions for %s', 'erp' ), ent.employee_name ) }>
																<MoreVertical size={ 16 } aria-hidden="true" />
															</Button>
														}
													/>
													<DropdownMenuContent align="end" className="min-w-44">
														<DropdownMenuItem
															variant="destructive"
															className="gap-2"
															onClick={ () => setDeleting( ent ) }
														>
															<Trash2 size={ 14 } aria-hidden="true" />
															{ __( 'Delete', 'erp' ) }
														</DropdownMenuItem>
													</DropdownMenuContent>
												</DropdownMenu>
											</div>
										) : null }
									</td>
								</tr>
							) ) }
						</tbody>
					</table>
					</div>
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
