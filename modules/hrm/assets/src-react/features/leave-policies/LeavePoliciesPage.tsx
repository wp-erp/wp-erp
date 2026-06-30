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
	toast,
} from '@wedevs/plugin-ui';
import { Filter, Plus } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
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
import { LeavePoliciesFilters } from './LeavePoliciesFilters';
import { LeavePoliciesTable } from './LeavePoliciesTable';
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
	// Create/edit/duplicate modal open-state lives in the URL so a browser
	// refresh re-opens it: `?form=new` (create), `?form=<id>` (edit), or
	// `?form=copy-<id>` (duplicate). The dialog needs the full policy detail
	// (not in the list rows), so it's fetched and cached in `editing` / `seed`.
	const [ formParam, setFormParam ] = useModalParam( 'form' );
	const [ editing, setEditing ]     = useState< LeavePolicy | null >( null );
	const [ seed, setSeed ]           = useState< LeavePolicy | null >( null );
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
		setSeed( null );
		setFormError( null );
		setFormParam( 'new' );
	}

	async function openEdit( row: LeavePolicyListRow ): Promise< void > {
		await ensureOptions();
		setFormError( null );
		try {
			const full = await getOne( row.id );
			setEditing( full );
			setSeed( null );
			setFormParam( String( row.id ) );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not load the policy.', 'erp' ) );
		}
	}

	// Duplicate — load the policy and open the create dialog prefilled from it
	// (legacy list-table "Copy" row action). Saves as a brand-new policy.
	async function openDuplicate( row: LeavePolicyListRow ): Promise< void > {
		await ensureOptions();
		setFormError( null );
		try {
			const full = await getOne( row.id );
			setEditing( null );
			setSeed( full );
			setFormParam( `copy-${ row.id }` );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not load the policy.', 'erp' ) );
		}
	}

	// Deep-link / refresh: when `?form=<id>` (edit) or `?form=copy-<id>`
	// (duplicate) is present but the detail isn't loaded yet, fetch it.
	useEffect( () => {
		if ( ! formParam || formParam === 'new' ) {
			return;
		}
		const isCopy = formParam.startsWith( 'copy-' );
		const id     = Number( isCopy ? formParam.slice( 5 ) : formParam );
		if ( ! id || ( isCopy ? seed?.id === id : editing?.id === id ) ) {
			return;
		}
		let active = true;
		void ensureOptions();
		void getOne( id )
			.then( ( full ) => {
				if ( ! active ) {
					return;
				}
				if ( isCopy ) {
					setEditing( null );
					setSeed( full );
				} else {
					setSeed( null );
					setEditing( full );
				}
			} )
			.catch( ( raw ) => {
				if ( active ) {
					toast.error( ( raw as ApiError )?.message ?? __( 'Could not load the policy.', 'erp' ) );
					setFormParam( null );
				}
			} );
		return () => {
			active = false;
		};
	}, [ formParam, editing, seed, ensureOptions, getOne, setFormParam ] );

	async function handleSubmit( payload: LeavePolicyInput ): Promise< void > {
		setBusy( true );
		setFormError( null );
		try {
			await save( editing ? editing.id : null, payload );
			toast.success( editing ? __( 'Policy updated.', 'erp' ) : __( 'Policy created.', 'erp' ) );
			setFormParam( null );
			setEditing( null );
			setSeed( null );
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

	const activeFilterCount  = ( fYear ? 1 : 0 ) + ( departmentId ? 1 : 0 ) + ( employeeType ? 1 : 0 );
	const filterButtonActive = showFilters || activeFilterCount > 0;

	return (
		<section className="mx-auto w-full max-w-full">
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
							<span className="font-normal text-[#a5a5aa]">({ total })</span>
							<span aria-hidden="true" className="absolute inset-x-0 -bottom-2 h-0.5 bg-primary" />
						</span>
					</div>
					<button
						type="button"
						aria-label={ __( 'Toggle filters', 'erp' ) }
						aria-pressed={ filterButtonActive }
						onClick={ () => setShowFilters( ( prev ) => ! prev ) }
						className={ [
							'relative inline-flex items-center justify-center gap-1 transition-colors',
							filterButtonActive ? 'text-muted-foreground hover:text-foreground' : 'text-muted-foreground hover:text-foreground',
						].join( ' ' ) }
					>
						<Filter size={ 20 } strokeWidth={ 1.75 } aria-hidden="true" />
						{ activeFilterCount > 0 ? (
							<span className="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1.5 text-xs font-medium text-primary-foreground">
								{ activeFilterCount }
							</span>
						) : null }
					</button>
				</div>

				{ filterButtonActive ? (
					<LeavePoliciesFilters
						options={ options }
						fYear={ fYear }
						departmentId={ departmentId }
						employeeType={ employeeType }
						onFYear={ setFYear }
						onDepartment={ setDepartmentId }
						onEmployeeType={ setEmployeeType }
					/>
				) : null }

				{ error ? (
					<p className="p-6 text-sm text-destructive">{ error }</p>
				) : loading ? (
					<TableSkeleton rows={ 6 } />
				) : rows.length === 0 ? (
					<p className="p-10 text-center text-sm text-muted-foreground">
						{ __( 'No leave policies match these filters.', 'erp' ) }
					</p>
				) : (
					<LeavePoliciesTable
						rows={ rows }
						canManage={ canManage }
						onEdit={ ( policy ) => void openEdit( policy ) }
						onDuplicate={ ( policy ) => void openDuplicate( policy ) }
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

			<LeavePolicyFormDialog
				open={ formParam !== null }
				editing={ editing }
				seed={ seed }
				options={ options }
				busy={ busy }
				error={ formError }
				onClose={ () => {
					setFormParam( null );
					setEditing( null );
					setSeed( null );
				} }
				onSubmit={ handleSubmit }
				onOptionsStale={ () => { void loadOptions().then( setOptions ); } }
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
