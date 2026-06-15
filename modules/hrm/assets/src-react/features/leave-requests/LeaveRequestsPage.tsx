/**
 * `/leave/requests` route — central leave-request queue.
 *
 * Layout follows the Employees table conventions exactly: status tabs on the
 * left of the toolbar, a search box + Filter funnel on the right, a collapsible
 * secondary filter row (leave type + year), and the shared paginated table
 * footer (`OrgPagination`, identical to `EmployeesTable`'s footer).
 *
 * Approve / reject / delete delegate (server-side) to the unchanged v1 model
 * layer, so balance adjustments, status-history rows, e-mail notifications and
 * cascade delete all stay.
 */

import {
	Badge,
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
import { Check, Filter, MoreVertical, Plus, RotateCcw, Search, Trash2, X } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import type { JSX } from 'react';

import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { PersonCell } from '@/shared/components/PersonCell';
import { useCan } from '@/shared/hooks/useCan';
import { __, sprintf } from '@/shared/i18n';
import { useModalParam } from '@/shared/useModalParam';
import type { ApiError } from '@/shared/utils/apiFetch';

import { OrgDeleteDialog } from '../org/OrgDeleteDialog';
import { OrgPagination } from '../org/OrgPagination';
import { LeaveRequestModerateDialog } from './LeaveRequestModerateDialog';
import { NewLeaveRequestDialog } from './NewLeaveRequestDialog';
import type { LeaveRequest } from './types';
import type { LeaveTypeOption } from './useLeaveRequests';
import { useLeaveRequests } from './useLeaveRequests';

const STATUS_TABS: ReadonlyArray< { value: number; label: string } > = [
	{ value: 0, label: __( 'All', 'erp' ) },
	{ value: 2, label: __( 'Pending', 'erp' ) },
	{ value: 1, label: __( 'Approved', 'erp' ) },
	{ value: 3, label: __( 'Rejected', 'erp' ) },
];

const SEARCH_DEBOUNCE_MS = 350;

function fmt( value: string | null ): string {
	if ( ! value ) {
		return '—';
	}
	const d = new Date( value );
	if ( Number.isNaN( d.getTime() ) ) {
		return value.slice( 0, 10 );
	}
	return d.toLocaleDateString( undefined, { year: 'numeric', month: 'short', day: 'numeric' } );
}

/**
 * Status pill — same plugin-ui `Badge` + semantic-token treatment as the
 * Employees `StatusCell` (no bespoke component). Approved → success, rejected →
 * destructive, pending → warning.
 */
function StatusPill( { status, label }: { status: number; label: string } ): JSX.Element {
	const className =
		status === 1
			? 'bg-success-light text-success-on-light'
			: status === 3
			? 'bg-destructive-light text-destructive-on-light'
			: 'bg-warning-light text-warning-on-light';
	return <Badge className={ `${ className } rounded-full` }>{ label }</Badge>;
}

function LeaveRequestsInner(): JSX.Element {
	const canManage = useCan( 'erp_leave_manage' );

	const [ status, setStatus ]       = useState( 0 );
	const [ leaveId, setLeaveId ]     = useState( 0 );
	const [ year, setYear ]           = useState( 0 );
	const [ searchInput, setSearchInput ] = useState( '' );
	const [ search, setSearch ]       = useState( '' );
	const [ showFilters, setShowFilters ] = useState( false );
	const [ page, setPage ]           = useState( 1 );
	const [ perPage, setPerPage ]     = useState( 20 );

	const { rows, total, counts, loading, error, reload, approve, reject, remove, bulk, loadLeaveTypes } = useLeaveRequests( {
		status,
		leaveId,
		year,
		search,
		page,
		perPage,
	} );

	const [ leaveTypes, setLeaveTypes ] = useState< readonly LeaveTypeOption[] >( [] );
	const [ moderate, setModerate ]   = useState< { action: 'approve' | 'reject'; request: LeaveRequest } | null >( null );
	const [ deleting, setDeleting ]   = useState< LeaveRequest | null >( null );
	// Create modal open-state lives in the URL (`?form=new`) so a browser
	// refresh re-opens it.
	const [ formParam, setFormParam ] = useModalParam( 'form' );
	const [ busy, setBusy ]           = useState( false );
	const [ moderateError, setModerateError ] = useState< string | null >( null );
	const [ selected, setSelected ]   = useState< ReadonlySet< number > >( new Set() );
	const [ bulkDeleting, setBulkDeleting ] = useState( false );

	const totalPages = Math.max( 1, Math.ceil( total / perPage ) );

	// Debounced search → committed query.
	useEffect( () => {
		const id = window.setTimeout( () => setSearch( searchInput ), SEARCH_DEBOUNCE_MS );
		return () => window.clearTimeout( id );
	}, [ searchInput ] );

	useEffect( () => {
		setPage( 1 );
	}, [ status, leaveId, year, search ] );

	useEffect( () => {
		let active = true;
		void loadLeaveTypes().then( ( list ) => {
			if ( active ) {
				setLeaveTypes( list );
			}
		} );
		return () => {
			active = false;
		};
	}, [ loadLeaveTypes ] );

	const yearOptions = useMemo( () => {
		const now = new Date().getFullYear();
		const years: Array< { value: string; label: string } > = [ { value: '', label: __( 'All Years', 'erp' ) } ];
		for ( let y = now + 1; y >= now - 5; y-- ) {
			years.push( { value: String( y ), label: String( y ) } );
		}
		return years;
	}, [] );

	const leaveTypeFilterOpts = useMemo(
		() => [ { value: '', label: __( 'All Types', 'erp' ) }, ...leaveTypes.map( ( t ) => ( { value: String( t.value ), label: t.label } ) ) ],
		[ leaveTypes ]
	);

	const activeFilterCount = ( leaveId ? 1 : 0 ) + ( year ? 1 : 0 );
	const filterButtonActive = showFilters || activeFilterCount > 0;

	async function handleModerate( reason: string ): Promise< void > {
		if ( ! moderate ) {
			return;
		}
		setBusy( true );
		setModerateError( null );
		try {
			if ( moderate.action === 'approve' ) {
				await approve( moderate.request.id, reason );
				toast.success( __( 'Leave request approved.', 'erp' ) );
			} else {
				await reject( moderate.request.id, reason );
				toast.success( __( 'Leave request rejected.', 'erp' ) );
			}
			setModerate( null );
		} catch ( raw ) {
			setModerateError( ( raw as ApiError )?.message ?? __( 'Could not update the request.', 'erp' ) );
		} finally {
			setBusy( false );
		}
	}

	async function handleDelete(): Promise< void > {
		if ( ! deleting ) {
			return;
		}
		setBusy( true );
		try {
			await remove( deleting.id );
			toast.success( __( 'Leave request deleted.', 'erp' ) );
			setDeleting( null );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not delete the request.', 'erp' ) );
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

	async function runBulk( action: 'approve' | 'reject' | 'delete' ): Promise< void > {
		const ids = [ ...selected ];
		if ( ids.length === 0 ) {
			return;
		}
		setBusy( true );
		try {
			await bulk( action, ids );
			toast.success(
				action === 'approve'
					? __( 'Selected requests approved.', 'erp' )
					: action === 'reject'
					? __( 'Selected requests rejected.', 'erp' )
					: __( 'Selected requests deleted.', 'erp' )
			);
			setSelected( new Set() );
			setBulkDeleting( false );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Bulk action failed.', 'erp' ) );
		} finally {
			setBusy( false );
		}
	}

	return (
		<section className="mx-auto w-full max-w-7xl">
			<header className="mb-6 flex items-center justify-between gap-4">
				<h1 className="text-2xl font-bold leading-8 text-foreground">
					{ __( 'Leave Requests', 'erp' ) }
				</h1>
				{ canManage ? (
					<Button className="h-10 gap-1.5 px-4" onClick={ () => setFormParam( 'new' ) }>
						<Plus size={ 16 } aria-hidden="true" />
						{ __( 'New Request', 'erp' ) }
					</Button>
				) : null }
			</header>

			<div className="rounded-lg border border-border bg-card shadow-sm">
				{ /* Toolbar — status tabs (left) + search + filter funnel (right). */ }
				<div className="flex flex-wrap items-center justify-between gap-3 border-b border-border px-4 pt-3 pb-2">
					<div role="tablist" aria-label={ __( 'Leave request status', 'erp' ) } className="flex items-stretch">
						{ STATUS_TABS.map( ( tab ) => {
							const selected = status === tab.value;
							const count =
								tab.value === 0 ? counts.all
									: tab.value === 1 ? counts.approved
									: tab.value === 2 ? counts.pending
									: counts.rejected;
							return (
								<button
									key={ tab.value }
									role="tab"
									type="button"
									aria-selected={ selected }
									onClick={ () => setStatus( tab.value ) }
									className={ [
										'relative inline-flex h-11 items-center gap-1.5 px-4 text-sm font-medium',
										selected ? 'text-primary' : 'text-muted-foreground hover:text-foreground',
									].join( ' ' ) }
								>
									<span>{ tab.label }</span>
									<span className="font-normal text-muted-foreground">({ count })</span>
									{ selected ? (
										<span aria-hidden="true" className="absolute inset-x-0 -bottom-2 h-0.5 bg-primary" />
									) : null }
								</button>
							);
						} ) }
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
								value={ searchInput }
								onChange={ ( e ) => setSearchInput( e.target.value ) }
								placeholder={ __( 'Search', 'erp' ) }
								className="h-9 w-60 rounded-md border-border pl-9 text-sm"
								aria-label={ __( 'Search leave requests by employee', 'erp' ) }
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
							{ __( 'Leave Type', 'erp' ) }
							<SmartSelect
								options={ leaveTypeFilterOpts }
								value={ String( leaveId || '' ) }
								onValueChange={ ( v ) => setLeaveId( Number( v || 0 ) ) }
								placeholder={ __( 'All Types', 'erp' ) }
								showClear
								className="h-9 w-48 bg-background"
								contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
							/>
						</label>
						<label className="flex items-center gap-2 text-sm text-muted-foreground">
							{ __( 'Year', 'erp' ) }
							<SmartSelect
								options={ yearOptions }
								value={ String( year || '' ) }
								onValueChange={ ( v ) => setYear( Number( v || 0 ) ) }
								placeholder={ __( 'All Years', 'erp' ) }
								showClear
								className="h-9 w-36 bg-background"
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
						<div className="flex items-center gap-2">
							<Button size="sm" variant="outline" disabled={ busy } onClick={ () => void runBulk( 'approve' ) } className="h-8 gap-1.5">
								<Check size={ 14 } aria-hidden="true" /> { __( 'Approve', 'erp' ) }
							</Button>
							<Button size="sm" variant="outline" disabled={ busy } onClick={ () => void runBulk( 'reject' ) } className="h-8 gap-1.5">
								<X size={ 14 } aria-hidden="true" /> { __( 'Reject', 'erp' ) }
							</Button>
							<Button size="sm" variant="outline" disabled={ busy } onClick={ () => setBulkDeleting( true ) } className="h-8 gap-1.5 text-destructive hover:text-destructive">
								<Trash2 size={ 14 } aria-hidden="true" /> { __( 'Delete', 'erp' ) }
							</Button>
						</div>
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
						{ __( 'No leave requests match these filters.', 'erp' ) }
					</p>
				) : (
					<div className="overflow-x-auto">
						<table className="w-full min-w-[40rem] text-left">
						<thead className="border-b border-border bg-muted/40">
							<tr className="h-10 text-xs font-medium uppercase tracking-normal text-muted-foreground">
								{ canManage ? (
									<th scope="col" className="w-10 px-4">
										<Checkbox
											checked={ allOnPageSelected }
											onCheckedChange={ toggleAll }
											aria-label={ __( 'Select all', 'erp' ) }
										/>
									</th>
								) : null }
								<th scope="col" className="px-2">{ __( 'Employee', 'erp' ) }</th>
								<th scope="col" className="px-2">{ __( 'Leave Type', 'erp' ) }</th>
								<th scope="col" className="px-2">{ __( 'Duration', 'erp' ) }</th>
								<th scope="col" className="px-2">{ __( 'Days', 'erp' ) }</th>
								<th scope="col" className="px-2">{ __( 'Status', 'erp' ) }</th>
								<th scope="col" className="w-24 px-4">
									<span className="sr-only">{ __( 'Actions', 'erp' ) }</span>
								</th>
							</tr>
						</thead>
						<tbody>
							{ rows.map( ( req ) => (
								<tr key={ req.id } className="h-18 border-b border-border last:border-b-0 hover:bg-muted/40">
									{ canManage ? (
										<td className="w-10 px-4 align-middle">
											<Checkbox
												checked={ selected.has( req.id ) }
												onCheckedChange={ () => toggleOne( req.id ) }
												aria-label={ sprintf( __( 'Select %s', 'erp' ), req.name ) }
											/>
										</td>
									) : null }
									<td className="px-2 align-middle font-medium text-foreground">
										{ req.name ? (
											<PersonCell name={ req.name } avatar={ req.avatar } />
										) : (
											<span className="text-muted-foreground">—</span>
										) }
									</td>
									<td className="px-2 align-middle text-sm text-foreground">
										<span className="inline-flex items-center gap-2">
											<span aria-hidden="true" className="inline-block size-2.5 shrink-0 rounded-full" style={ { backgroundColor: req.color || 'transparent' } } />
											{ req.policy_name }
										</span>
									</td>
									<td className="whitespace-nowrap px-2 align-middle text-sm text-muted-foreground">
										{ `${ fmt( req.start_date ) } – ${ fmt( req.end_date ) }` }
									</td>
									<td className="px-2 align-middle text-sm text-foreground">{ req.days }</td>
									<td className="px-2 align-middle">
										<StatusPill status={ req.status } label={ req.status_label } />
									</td>
									<td className="px-4 align-middle">
										{ canManage ? (
											<div className="flex items-center justify-end gap-1">
												{ req.status === 2 ? (
													<>
														<Button
															variant="ghost"
															size="icon"
															aria-label={ __( 'Approve', 'erp' ) }
															className="text-green-600 hover:bg-green-50 hover:text-green-700 dark:hover:bg-green-900/20"
															onClick={ () => { setModerateError( null ); setModerate( { action: 'approve', request: req } ); } }
														>
															<Check size={ 16 } aria-hidden="true" />
														</Button>
														<Button
															variant="ghost"
															size="icon"
															aria-label={ __( 'Reject', 'erp' ) }
															className="text-red-600 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-900/20"
															onClick={ () => { setModerateError( null ); setModerate( { action: 'reject', request: req } ); } }
														>
															<X size={ 16 } aria-hidden="true" />
														</Button>
													</>
												) : null }
												<DropdownMenu>
													<DropdownMenuTrigger
														render={
															<Button variant="ghost" size="icon" aria-label={ sprintf( __( 'Actions for %s', 'erp' ), req.name ) }>
																<MoreVertical size={ 16 } aria-hidden="true" />
															</Button>
														}
													/>
													<DropdownMenuContent align="end" className="min-w-44">
														{ /* Reverse moderation — reject an approved request, or approve a rejected one (legacy list-table behaviour). */ }
														{ req.status === 1 ? (
															<DropdownMenuItem
																className="gap-2"
																onClick={ () => { setModerateError( null ); setModerate( { action: 'reject', request: req } ); } }
															>
																<RotateCcw size={ 14 } aria-hidden="true" />
																{ __( 'Reject', 'erp' ) }
															</DropdownMenuItem>
														) : null }
														{ req.status === 3 ? (
															<DropdownMenuItem
																className="gap-2"
																onClick={ () => { setModerateError( null ); setModerate( { action: 'approve', request: req } ); } }
															>
																<RotateCcw size={ 14 } aria-hidden="true" />
																{ __( 'Approve', 'erp' ) }
															</DropdownMenuItem>
														) : null }
														<DropdownMenuItem
															variant="destructive"
															className="gap-2"
															onClick={ () => setDeleting( req ) }
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

			<NewLeaveRequestDialog
				open={ formParam !== null }
				onClose={ () => setFormParam( null ) }
				onSubmitted={ () => {
					setFormParam( null );
					toast.success( __( 'Leave request submitted.', 'erp' ) );
					void reload();
				} }
			/>

			<LeaveRequestModerateDialog
				open={ moderate !== null }
				action={ moderate?.action ?? 'approve' }
				request={ moderate?.request ?? null }
				busy={ busy }
				error={ moderateError }
				onConfirm={ ( reason ) => void handleModerate( reason ) }
				onCancel={ () => setModerate( null ) }
			/>

			<OrgDeleteDialog
				open={ deleting !== null }
				title={ __( 'Delete leave request?', 'erp' ) }
				description={
					deleting
						? sprintf(
								__( '%1$s’s %2$s request will be permanently deleted and any used balance restored. This cannot be undone.', 'erp' ),
								deleting.name,
								deleting.policy_name
						  )
						: ''
				}
				busy={ busy }
				onConfirm={ () => void handleDelete() }
				onCancel={ () => setDeleting( null ) }
			/>

			<OrgDeleteDialog
				open={ bulkDeleting }
				title={ __( 'Delete selected requests?', 'erp' ) }
				description={ sprintf(
					__( '%d leave request(s) will be permanently deleted and any used balance restored. This cannot be undone.', 'erp' ),
					selected.size
				) }
				busy={ busy }
				onConfirm={ () => void runBulk( 'delete' ) }
				onCancel={ () => setBulkDeleting( false ) }
			/>
		</section>
	);
}

export function LeaveRequestsPage(): JSX.Element {
	return (
		<CapabilityGate caps={ [ 'erp_leave_manage' ] }>
			<ErrorBoundary>
				<LeaveRequestsInner />
			</ErrorBoundary>
		</CapabilityGate>
	);
}
