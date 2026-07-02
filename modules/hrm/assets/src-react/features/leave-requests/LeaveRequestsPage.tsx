/**
 * `/leave/requests` route — central leave-request queue.
 *
 * Layout follows the Employees table conventions exactly: status tabs on the
 * left of the toolbar, a search box + Filter funnel on the right, a collapsible
 * secondary filter row (`LeaveRequestsFilters`), and the shared paginated table
 * (`LeaveRequestsTable` + `OrgPagination`, identical to `EmployeesTable`'s).
 *
 * This file owns the query/selection state and the moderate/delete/bulk flows;
 * the filter row and the table are presentational siblings. Approve / reject /
 * delete delegate (server-side) to the unchanged v1 model layer, so balance
 * adjustments, status-history rows, e-mail notifications and cascade delete all
 * stay.
 */

import { Button, Input, toast } from '@wedevs/plugin-ui';
import { Check, Filter, Plus, Search, Trash2, X } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import type { JSX } from 'react';

import { TYPE_OPTIONS } from '@/features/employee-create/options';
import { loadLookup } from '@/features/employees/filters/lookups';
import type { LookupOption } from '@/features/employees/filters/lookups';
import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { TableSkeleton } from '@/shared/components/TableSkeleton';
import { useCan } from '@/shared/hooks/useCan';
import { __, sprintf } from '@/shared/i18n';
import { useModalParam } from '@/shared/useModalParam';
import type { ApiError } from '@/shared/utils/apiFetch';

import { OrgDeleteDialog } from '../org/OrgDeleteDialog';
import { OrgPagination } from '../org/OrgPagination';
import { LeaveRequestModerateDialog } from './LeaveRequestModerateDialog';
import { LeaveRequestsFilters } from './LeaveRequestsFilters';
import { LeaveRequestsTable } from './LeaveRequestsTable';
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

/** Local `YYYY-MM-DD` for a Date. */
function iso( d: Date ): string {
	const pad = ( n: number ): string => String( n ).padStart( 2, '0' );
	return `${ d.getFullYear() }-${ pad( d.getMonth() + 1 ) }-${ pad(
		d.getDate()
	) }`;
}

/**
 * Resolve the active date-range filter to concrete `{ start, end }` ISO bounds,
 * mirroring the legacy `filter_leave_year` presets: last week, last month, last
 * 3 months, or a custom From/To range. Empty strings = no range.
 * @param preset Relative preset id or 'custom'.
 * @param customStart Custom start (only used when preset === 'custom').
 * @param customEnd   Custom end (only used when preset === 'custom').
 */
function resolveDateRange(
	preset: string,
	customStart: string,
	customEnd: string
): { start: string; end: string } {
	const today = new Date();

	if ( preset === '1' ) {
		const start = new Date( today );
		start.setDate( start.getDate() - 7 );
		return { start: iso( start ), end: iso( today ) };
	}
	if ( preset === '2' ) {
		const start = new Date(
			today.getFullYear(),
			today.getMonth() - 1,
			1
		);
		const end = new Date( today.getFullYear(), today.getMonth(), 0 );
		return { start: iso( start ), end: iso( end ) };
	}
	if ( preset === '3' ) {
		const start = new Date( today );
		start.setMonth( start.getMonth() - 3 );
		return { start: iso( start ), end: iso( today ) };
	}
	if ( preset === 'custom' ) {
		return { start: customStart, end: customEnd };
	}
	return { start: '', end: '' };
}

function LeaveRequestsInner(): JSX.Element {
	const canManage = useCan( 'erp_leave_manage' );

	const [ status, setStatus ] = useState( 0 );
	const [ leaveId, setLeaveId ] = useState( 0 );
	const [ year, setYear ] = useState( 0 );
	const [ departmentId, setDepartmentId ] = useState( 0 );
	const [ designationId, setDesignationId ] = useState( 0 );
	const [ employmentType, setEmploymentType ] = useState( '' );
	const [ datePreset, setDatePreset ] = useState( '' );
	const [ startDate, setStartDate ] = useState( '' );
	const [ endDate, setEndDate ] = useState( '' );
	const [ orderby, setOrderby ] = useState( 'created_at' );
	const [ order, setOrder ] = useState< 'asc' | 'desc' >( 'desc' );
	const [ searchInput, setSearchInput ] = useState( '' );
	const [ search, setSearch ] = useState( '' );
	const [ showFilters, setShowFilters ] = useState( false );
	const [ page, setPage ] = useState( 1 );
	const [ perPage, setPerPage ] = useState( 20 );

	const { start: rangeStart, end: rangeEnd } = useMemo(
		() => resolveDateRange( datePreset, startDate, endDate ),
		[ datePreset, startDate, endDate ]
	);

	const {
		rows,
		total,
		counts,
		loading,
		error,
		reload,
		approve,
		reject,
		remove,
		bulk,
		loadLeaveTypes,
	} = useLeaveRequests( {
		status,
		leaveId,
		year,
		departmentId,
		designationId,
		type: employmentType,
		search,
		startDate: rangeStart,
		endDate: rangeEnd,
		orderby,
		order,
		page,
		perPage,
	} );

	const [ leaveTypes, setLeaveTypes ] = useState<
		readonly LeaveTypeOption[]
	>( [] );
	const [ departments, setDepartments ] = useState< readonly LookupOption[] >(
		[]
	);
	const [ designations, setDesignations ] = useState<
		readonly LookupOption[]
	>( [] );
	const [ moderate, setModerate ] = useState< {
		action: 'approve' | 'reject';
		request: LeaveRequest;
	} | null >( null );
	const [ deleting, setDeleting ] = useState< LeaveRequest | null >( null );
	// Create modal open-state lives in the URL (`?form=new`) so a browser
	// refresh re-opens it.
	const [ formParam, setFormParam ] = useModalParam( 'form' );
	const [ busy, setBusy ] = useState( false );
	const [ moderateError, setModerateError ] = useState< string | null >(
		null
	);
	const [ selected, setSelected ] = useState< ReadonlySet< number > >(
		new Set()
	);
	const [ bulkDeleting, setBulkDeleting ] = useState( false );

	const totalPages = Math.max( 1, Math.ceil( total / perPage ) );

	// Debounced search → committed query.
	useEffect( () => {
		const id = window.setTimeout(
			() => setSearch( searchInput ),
			SEARCH_DEBOUNCE_MS
		);
		return () => window.clearTimeout( id );
	}, [ searchInput ] );

	useEffect( () => {
		setPage( 1 );
	}, [
		status,
		leaveId,
		year,
		departmentId,
		designationId,
		employmentType,
		rangeStart,
		rangeEnd,
		orderby,
		order,
		search,
	] );

	// Toggle sort: same column flips direction, a new column starts ascending.
	function handleSort( column: string ): void {
		if ( orderby === column ) {
			setOrder( ( prev ) => ( prev === 'asc' ? 'desc' : 'asc' ) );
		} else {
			setOrderby( column );
			setOrder( 'asc' );
		}
	}

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

	// Department + Designation options reuse the shared lookup cache (same
	// source as the Employees-table filters).
	useEffect( () => {
		let active = true;
		void loadLookup( 'departments' ).then( ( list ) => {
			if ( active ) {
				setDepartments( list );
			}
		} );
		void loadLookup( 'designations' ).then( ( list ) => {
			if ( active ) {
				setDesignations( list );
			}
		} );
		return () => {
			active = false;
		};
	}, [] );

	const yearOptions = useMemo( () => {
		const now = new Date().getFullYear();
		const years: Array< { value: string; label: string } > = [
			{ value: '', label: __( 'All Years', 'erp' ) },
		];
		for ( let y = now + 1; y >= now - 5; y-- ) {
			years.push( { value: String( y ), label: String( y ) } );
		}
		return years;
	}, [] );

	const leaveTypeFilterOpts = useMemo(
		() => [
			{ value: '', label: __( 'All Types', 'erp' ) },
			...leaveTypes.map( ( t ) => ( {
				value: String( t.value ),
				label: t.label,
			} ) ),
		],
		[ leaveTypes ]
	);

	const departmentOpts = useMemo(
		() => [
			{ value: '', label: __( 'All Departments', 'erp' ) },
			...departments.map( ( d ) => ( {
				value: String( d.id ),
				label: d.title,
			} ) ),
		],
		[ departments ]
	);

	const designationOpts = useMemo(
		() => [
			{ value: '', label: __( 'All Designations', 'erp' ) },
			...designations.map( ( d ) => ( {
				value: String( d.id ),
				label: d.title,
			} ) ),
		],
		[ designations ]
	);

	const employmentTypeOpts = useMemo(
		() => [
			{ value: '', label: __( 'All Employment Types', 'erp' ) },
			...TYPE_OPTIONS.map( ( t ) => ( {
				value: t.value,
				label: t.label,
			} ) ),
		],
		[]
	);

	const dateFilterActive = Boolean( rangeStart && rangeEnd );
	const activeFilterCount =
		( leaveId ? 1 : 0 ) +
		( year ? 1 : 0 ) +
		( departmentId ? 1 : 0 ) +
		( designationId ? 1 : 0 ) +
		( employmentType ? 1 : 0 ) +
		( dateFilterActive ? 1 : 0 );
	const filterButtonActive = showFilters || activeFilterCount > 0;

	function openModerate(
		action: 'approve' | 'reject',
		request: LeaveRequest
	): void {
		setModerateError( null );
		setModerate( { action, request } );
	}

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
			setModerateError(
				( raw as ApiError )?.message ??
					__( 'Could not update the request.', 'erp' )
			);
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
			toast.error(
				( raw as ApiError )?.message ??
					__( 'Could not delete the request.', 'erp' )
			);
			setDeleting( null );
		} finally {
			setBusy( false );
		}
	}

	// Clear the selection whenever the visible rows change (filter / page / reload).
	useEffect( () => {
		setSelected( new Set() );
	}, [ rows ] );

	const allOnPageSelected =
		rows.length > 0 && rows.every( ( r ) => selected.has( r.id ) );

	function toggleAll(): void {
		setSelected(
			allOnPageSelected ? new Set() : new Set( rows.map( ( r ) => r.id ) )
		);
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

	async function runBulk(
		action: 'approve' | 'reject' | 'delete'
	): Promise< void > {
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
			toast.error(
				( raw as ApiError )?.message ??
					__( 'Bulk action failed.', 'erp' )
			);
		} finally {
			setBusy( false );
		}
	}

	return (
		<section className="mx-auto w-full max-w-full">
			<header className="mb-6 flex items-center justify-between gap-4">
				<h1 className="text-2xl font-bold leading-8 text-foreground">
					{ __( 'Leave Requests', 'erp' ) }
				</h1>
				{ canManage ? (
					<Button
						className="h-10 gap-1.5 px-4"
						onClick={ () => setFormParam( 'new' ) }
					>
						<Plus size={ 16 } aria-hidden="true" />
						{ __( 'New Request', 'erp' ) }
					</Button>
				) : null }
			</header>

			<div className="rounded-lg border border-border bg-card shadow-sm">
				{ /* Toolbar — status tabs (left) + search + filter funnel (right). */ }
				<div className="flex flex-wrap items-center justify-between gap-3 border-b border-border px-4 pt-3 pb-2">
					<div
						role="tablist"
						aria-label={ __( 'Leave request status', 'erp' ) }
						className="flex items-stretch"
					>
						{ STATUS_TABS.map( ( tab ) => {
							const isSelected = status === tab.value;
							const count =
								tab.value === 0
									? counts.all
									: tab.value === 1
									? counts.approved
									: tab.value === 2
									? counts.pending
									: counts.rejected;
							return (
								<button
									key={ tab.value }
									role="tab"
									type="button"
									aria-selected={ isSelected }
									onClick={ () => setStatus( tab.value ) }
									className={ [
										'relative inline-flex h-11 items-center gap-1.5 px-4 text-sm font-medium',
										isSelected
											? 'text-primary'
											: 'text-muted-foreground hover:text-foreground',
									].join( ' ' ) }
								>
									<span>{ tab.label }</span>
									<span className="font-normal text-[#a5a5aa]">
										({ count })
									</span>
									{ isSelected ? (
										<span
											aria-hidden="true"
											className="absolute inset-x-0 -bottom-2 h-0.5 bg-primary"
										/>
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
								onChange={ ( e ) =>
									setSearchInput( e.target.value )
								}
								placeholder={ __( 'Search', 'erp' ) }
								className="h-9 w-60 rounded-md border-border pl-9 text-sm"
								aria-label={ __(
									'Search leave requests by employee',
									'erp'
								) }
							/>
						</div>
						<button
							type="button"
							aria-label={ __( 'Toggle filters', 'erp' ) }
							aria-pressed={ filterButtonActive }
							onClick={ () =>
								setShowFilters( ( prev ) => ! prev )
							}
							className={ [
								'relative inline-flex size-5 items-center justify-center transition-colors',
								filterButtonActive
									? 'text-primary'
									: 'text-muted-foreground hover:text-foreground',
							].join( ' ' ) }
						>
							<Filter
								size={ 20 }
								strokeWidth={ 1.75 }
								aria-hidden="true"
							/>
							{ activeFilterCount > 0 ? (
								<span className="absolute -right-1.5 -top-1.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-medium text-primary-foreground">
									{ activeFilterCount }
								</span>
							) : null }
						</button>
					</div>
				</div>

				{ filterButtonActive ? (
					<LeaveRequestsFilters
						leaveId={ leaveId }
						onLeaveId={ setLeaveId }
						year={ year }
						onYear={ setYear }
						departmentId={ departmentId }
						onDepartmentId={ setDepartmentId }
						designationId={ designationId }
						onDesignationId={ setDesignationId }
						employmentType={ employmentType }
						onEmploymentType={ setEmploymentType }
						datePreset={ datePreset }
						onDatePreset={ setDatePreset }
						startDate={ startDate }
						onStartDate={ setStartDate }
						endDate={ endDate }
						onEndDate={ setEndDate }
						leaveTypeOptions={ leaveTypeFilterOpts }
						yearOptions={ yearOptions }
						departmentOptions={ departmentOpts }
						designationOptions={ designationOpts }
						employmentTypeOptions={ employmentTypeOpts }
					/>
				) : null }

				{ canManage && selected.size > 0 ? (
					<div className="flex flex-wrap items-center gap-3 border-b border-border bg-primary/5 px-4 py-2.5">
						<span className="text-sm font-medium text-foreground">
							{ sprintf(
								__( '%d selected', 'erp' ),
								selected.size
							) }
						</span>
						<div className="flex items-center gap-2">
							<Button
								size="sm"
								variant="outline"
								disabled={ busy }
								onClick={ () => void runBulk( 'approve' ) }
								className="h-8 gap-1.5"
							>
								<Check size={ 14 } aria-hidden="true" />{ ' ' }
								{ __( 'Approve', 'erp' ) }
							</Button>
							<Button
								size="sm"
								variant="outline"
								disabled={ busy }
								onClick={ () => void runBulk( 'reject' ) }
								className="h-8 gap-1.5"
							>
								<X size={ 14 } aria-hidden="true" />{ ' ' }
								{ __( 'Reject', 'erp' ) }
							</Button>
							<Button
								size="sm"
								variant="outline"
								disabled={ busy }
								onClick={ () => setBulkDeleting( true ) }
								className="h-8 gap-1.5 border-destructive text-destructive hover:border-destructive hover:text-destructive"
							>
								<Trash2 size={ 14 } aria-hidden="true" />{ ' ' }
								{ __( 'Delete', 'erp' ) }
							</Button>
						</div>
						<button
							type="button"
							className="text-sm text-muted-foreground hover:text-foreground"
							onClick={ () => setSelected( new Set() ) }
						>
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
						{ __(
							'No leave requests match these filters.',
							'erp'
						) }
					</p>
				) : (
					<LeaveRequestsTable
						rows={ rows }
						canManage={ canManage }
						selected={ selected }
						allOnPageSelected={ allOnPageSelected }
						statusFilter={ status }
						orderby={ orderby }
						order={ order }
						onSort={ handleSort }
						onToggleAll={ toggleAll }
						onToggleOne={ toggleOne }
						onModerate={ openModerate }
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
						onPerPage={ ( n ) => {
							setPerPage( n );
							setPage( 1 );
						} }
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
								__(
									'%1$s’s %2$s request will be permanently deleted and any used balance restored. This cannot be undone.',
									'erp'
								),
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
					__(
						'%d leave request(s) will be permanently deleted and any used balance restored. This cannot be undone.',
						'erp'
					),
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
