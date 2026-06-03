/**
 * `/leave/policies` route — leave-policy management.
 *
 * List with financial-year / department / employee-type filters, a colour
 * swatch + scope columns, per-row Edit / Delete, and a create/edit dialog.
 * Create + edit + delete all delegate (server-side) to the unchanged v1 model
 * layer, so creating a policy still auto-creates entitlements for matching
 * employees and delete still cascades.
 */

import {
	Button,
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuItem,
	DropdownMenuTrigger,
	SmartSelect,
	toast,
} from '@wedevs/plugin-ui';
import { Filter, MoreVertical, Pencil, Plus, Trash2 } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import type { JSX } from 'react';

import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { useCan } from '@/shared/hooks/useCan';
import { __, sprintf } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';

import { OrgDeleteDialog } from '../org/OrgDeleteDialog';
import { OrgPagination } from '../org/OrgPagination';
import { LeavePolicyFormDialog } from './LeavePolicyFormDialog';
import type { LeavePolicy, LeavePolicyInput, LeavePolicyListRow, PolicyFormOptions } from './types';
import { useLeavePolicies } from './useLeavePolicies';

function LeavePoliciesInner(): JSX.Element {
	const canManage = useCan( 'erp_leave_manage' );

	const [ fYear, setFYear ]               = useState( 0 );
	const [ departmentId, setDepartmentId ] = useState( 0 );
	const [ employeeType, setEmployeeType ] = useState( '' );
	const [ showFilters, setShowFilters ]   = useState( false );
	const [ page, setPage ]                 = useState( 1 );
	const [ perPage, setPerPage ]           = useState( 20 );

	const { rows, total, loading, error, save, remove, getOne, loadOptions } = useLeavePolicies( {
		fYear,
		departmentId,
		employeeType,
		page,
		perPage,
	} );

	const [ options, setOptions ]     = useState< PolicyFormOptions | null >( null );
	const [ formOpen, setFormOpen ]   = useState( false );
	const [ editing, setEditing ]     = useState< LeavePolicy | null >( null );
	const [ deleting, setDeleting ]   = useState< LeavePolicyListRow | null >( null );
	const [ busy, setBusy ]           = useState( false );
	const [ formError, setFormError ] = useState< string | null >( null );

	const totalPages = Math.max( 1, Math.ceil( total / perPage ) );

	useEffect( () => {
		setPage( 1 );
	}, [ fYear, departmentId, employeeType ] );

	// Default the year filter to the current financial year (like Holidays
	// defaults to the current calendar year). One-shot, so clearing back to
	// "All Years" sticks.
	const didDefaultYear = useRef( false );

	// Load the form-options once (lazily, on first need) and cache them.
	const ensureOptions = useCallback( async (): Promise< PolicyFormOptions > => {
		if ( options ) {
			return options;
		}
		const opts = await loadOptions();
		setOptions( opts );
		if ( ! didDefaultYear.current && opts.currentFYear ) {
			didDefaultYear.current = true;
			setFYear( opts.currentFYear );
		}
		return opts;
	}, [ options, loadOptions ] );

	// Prime options on mount so the filter selects have labels.
	useEffect( () => {
		void ensureOptions();
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [] );

	async function openCreate(): Promise< void > {
		await ensureOptions();
		setEditing( null );
		setFormError( null );
		setFormOpen( true );
	}

	async function openEdit( row: LeavePolicyListRow ): Promise< void > {
		await ensureOptions();
		setFormError( null );
		try {
			const full = await getOne( row.id );
			setEditing( full );
			setFormOpen( true );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not load the policy.', 'erp' ) );
		}
	}

	async function handleSubmit( payload: LeavePolicyInput ): Promise< void > {
		setBusy( true );
		setFormError( null );
		try {
			await save( editing ? editing.id : null, payload );
			toast.success( editing ? __( 'Policy updated.', 'erp' ) : __( 'Policy created.', 'erp' ) );
			setFormOpen( false );
			setEditing( null );
		} catch ( raw ) {
			setFormError( ( raw as ApiError )?.message ?? __( 'Could not save the policy.', 'erp' ) );
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
			toast.success( __( 'Policy deleted.', 'erp' ) );
			setDeleting( null );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not delete the policy.', 'erp' ) );
			setDeleting( null );
		} finally {
			setBusy( false );
		}
	}

	const fYearFilterOpts = [
		{ value: '', label: __( 'All Years', 'erp' ) },
		...( options?.financialYears ?? [] ).map( ( y ) => ( { value: String( y.id ), label: y.label } ) ),
	];
	const deptFilterOpts = [
		{ value: '', label: __( 'All Departments', 'erp' ) },
		...( options?.departments ?? [] ).map( ( d ) => ( { value: String( d.id ), label: d.label } ) ),
	];
	const empTypeFilterOpts = [
		{ value: '', label: __( 'All Types', 'erp' ) },
		...( options?.employeeTypes ?? [] ).map( ( o ) => ( { value: o.value, label: o.label } ) ),
	];

	const activeFilterCount  = ( fYear ? 1 : 0 ) + ( departmentId ? 1 : 0 ) + ( employeeType ? 1 : 0 );
	const filterButtonActive = showFilters || activeFilterCount > 0;

	return (
		<section className="mx-auto w-full max-w-7xl">
			<header className="mb-6 flex items-center justify-between gap-4">
				<h1 className="text-2xl font-bold leading-8 text-foreground">
					{ __( 'Leave Policies', 'erp' ) }
				</h1>
				{ canManage ? (
					<Button
						onClick={ () => void openCreate() }
						variant="default"
						className="inline-flex h-10 items-center gap-2 rounded-md px-5 text-sm font-medium leading-5 shadow-sm"
					>
						<Plus size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
						{ __( 'Add Policy', 'erp' ) }
					</Button>
				) : null }
			</header>

			<div className="rounded-lg border border-border bg-card shadow-sm">
				<div className="flex flex-wrap items-center justify-between gap-3 border-b border-border px-4 pt-3 pb-2">
					<div role="tablist" aria-label={ __( 'Leave Policies', 'erp' ) } className="flex items-stretch">
						<span role="tab" aria-selected="true" className="relative inline-flex h-11 items-center gap-1.5 px-4 text-sm font-medium text-primary">
							<span>{ __( 'All', 'erp' ) }</span>
							<span className="font-normal text-muted-foreground">({ total })</span>
							<span aria-hidden="true" className="absolute inset-x-0 -bottom-2 h-0.5 bg-primary" />
						</span>
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

				{ filterButtonActive ? (
					<div className="flex flex-wrap items-center gap-2 border-b border-border bg-muted/20 px-4 py-3">
						<label className="flex items-center gap-2 text-sm text-muted-foreground">
							{ __( 'Year', 'erp' ) }
							<SmartSelect
								options={ fYearFilterOpts }
								value={ String( fYear || '' ) }
								onValueChange={ ( v ) => setFYear( Number( v || 0 ) ) }
								placeholder={ __( 'All Years', 'erp' ) }
								showClear
								className="h-9 w-40"
								contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
							/>
						</label>
						<label className="flex items-center gap-2 text-sm text-muted-foreground">
							{ __( 'Department', 'erp' ) }
							<SmartSelect
								options={ deptFilterOpts }
								value={ String( departmentId || '' ) }
								onValueChange={ ( v ) => setDepartmentId( Number( v || 0 ) ) }
								placeholder={ __( 'All Departments', 'erp' ) }
								showClear
								className="h-9 w-48"
								contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
							/>
						</label>
						<label className="flex items-center gap-2 text-sm text-muted-foreground">
							{ __( 'Employee Type', 'erp' ) }
							<SmartSelect
								options={ empTypeFilterOpts }
								value={ employeeType }
								onValueChange={ ( v ) => setEmployeeType( v ?? '' ) }
								placeholder={ __( 'All Types', 'erp' ) }
								showClear
								className="h-9 w-40"
								contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
							/>
						</label>
					</div>
				) : null }

				{ error ? (
					<p className="p-6 text-sm text-destructive">{ error }</p>
				) : loading ? (
					<p className="p-6 text-sm text-muted-foreground">{ __( 'Loading…', 'erp' ) }</p>
				) : rows.length === 0 ? (
					<p className="p-10 text-center text-sm text-muted-foreground">
						{ __( 'No leave policies match these filters.', 'erp' ) }
					</p>
				) : (
					<div className="overflow-x-auto">
						<table className="w-full min-w-[40rem] text-left">
						<thead className="border-b border-border bg-muted/40">
							<tr className="h-10 text-xs font-medium uppercase tracking-normal text-muted-foreground">
								<th scope="col" className="px-4">{ __( 'Name', 'erp' ) }</th>
								<th scope="col" className="px-4">{ __( 'Days', 'erp' ) }</th>
								<th scope="col" className="px-4">{ __( 'Department', 'erp' ) }</th>
								<th scope="col" className="px-4">{ __( 'Designation', 'erp' ) }</th>
								<th scope="col" className="px-4">{ __( 'Type', 'erp' ) }</th>
								<th scope="col" className="px-4">{ __( 'Year', 'erp' ) }</th>
								<th scope="col" className="w-20 px-4">
									<span className="sr-only">{ __( 'Actions', 'erp' ) }</span>
								</th>
							</tr>
						</thead>
						<tbody>
							{ rows.map( ( policy ) => (
								<tr key={ policy.id } className="h-14 border-b border-border last:border-b-0 hover:bg-muted/40">
									<td className="px-4 align-middle">
										<div className="flex items-center gap-2">
											<span
												aria-hidden="true"
												className="inline-block size-3 shrink-0 rounded-full"
												style={ { backgroundColor: policy.color || 'transparent' } }
											/>
											<span className="font-medium text-foreground">{ policy.name }</span>
										</div>
									</td>
									<td className="px-4 align-middle text-sm text-foreground">{ policy.days }</td>
									<td className="px-4 align-middle text-sm text-muted-foreground">{ policy.department }</td>
									<td className="px-4 align-middle text-sm text-muted-foreground">{ policy.designation }</td>
									<td className="px-4 align-middle text-sm text-muted-foreground">{ policy.employee_type }</td>
									<td className="px-4 align-middle text-sm text-muted-foreground">{ policy.f_year }</td>
									<td className="px-4 align-middle">
										{ canManage ? (
											<div className="flex justify-end">
												<DropdownMenu>
													<DropdownMenuTrigger
														render={
															<Button variant="ghost" size="icon" aria-label={ sprintf( __( 'Actions for %s', 'erp' ), policy.name ) }>
																<MoreVertical size={ 16 } aria-hidden="true" />
															</Button>
														}
													/>
													<DropdownMenuContent align="end" className="min-w-44">
														<DropdownMenuItem className="gap-2" onClick={ () => void openEdit( policy ) }>
															<Pencil size={ 14 } aria-hidden="true" />
															{ __( 'Edit', 'erp' ) }
														</DropdownMenuItem>
														<DropdownMenuItem
															variant="destructive"
															className="gap-2"
															onClick={ () => setDeleting( policy ) }
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

			<LeavePolicyFormDialog
				open={ formOpen }
				editing={ editing }
				options={ options }
				busy={ busy }
				error={ formError }
				onClose={ () => {
					setFormOpen( false );
					setEditing( null );
				} }
				onSubmit={ handleSubmit }
			/>

			<OrgDeleteDialog
				open={ deleting !== null }
				title={ __( 'Delete leave policy?', 'erp' ) }
				description={
					deleting
						? sprintf(
								__( '%s will be deleted along with its entitlements and any dependent leave requests. This cannot be undone.', 'erp' ),
								deleting.name
						  )
						: ''
				}
				busy={ busy }
				onConfirm={ () => void handleDelete() }
				onCancel={ () => setDeleting( null ) }
			/>
		</section>
	);
}

export function LeavePoliciesPage(): JSX.Element {
	return (
		<CapabilityGate caps={ [ 'erp_leave_manage' ] }>
			<ErrorBoundary>
				<LeavePoliciesInner />
			</ErrorBoundary>
		</CapabilityGate>
	);
}
