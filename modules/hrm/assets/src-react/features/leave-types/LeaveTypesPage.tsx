/**
 * `/leave/types` route — leave-type management with inline CRUD + bulk delete.
 *
 * Mirrors the legacy Leave Types list table: name + description columns,
 * per-row Edit / Delete, a header checkbox for bulk selection and a bulk-delete
 * action. Create + edit happen in a dialog; deletes surface the server-side
 * "associated with a policy" guard as a toast.
 *
 * Reads + writes `erp/v2/leave-types`, which delegates to the unchanged v1
 * model layer so every legacy hook + guard keeps firing.
 */

import {
	Button,
	Input,
	toast,
} from '@wedevs/plugin-ui';
import { Plus, Search, Trash2 } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
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
import { LeaveTypeFormDialog } from './LeaveTypeFormDialog';
import { LeaveTypesTable } from './LeaveTypesTable';
import type { LeaveType, LeaveTypeInput } from './types';
import { useLeaveTypes } from './useLeaveTypes';

function LeaveTypesInner(): JSX.Element {
	const { rows, loading, error, save, remove, bulkRemove } = useLeaveTypes();
	const canManage = useCan( 'erp_leave_manage' );

	const [ search, setSearch ]   = useState( '' );
	const [ page, setPage ]       = useState( 1 );
	const [ perPage, setPerPage ] = useState( 10 );
	const [ selected, setSelected ] = useState< Set< number > >( new Set() );

	// Create/edit modal open-state lives in the URL (`?form=new` | `?form=<id>`)
	// so a browser refresh re-opens it. For edit, resolve the target from the
	// loaded rows (legacy parity: leave-type create/edit is a modal, not a route).
	const [ formParam, setFormParam ] = useModalParam( 'form' );
	const editing: LeaveType | null =
		formParam && formParam !== 'new'
			? ( rows.find( ( t ) => t.id === Number( formParam ) ) ?? null )
			: null;
	const [ deleting, setDeleting ]   = useState< LeaveType | null >( null );
	const [ bulkOpen, setBulkOpen ]   = useState( false );
	const [ busy, setBusy ]           = useState( false );
	const [ formError, setFormError ] = useState< string | null >( null );

	const filtered = useMemo( () => {
		const q = search.trim().toLowerCase();
		if ( ! q ) {
			return rows;
		}
		return rows.filter(
			( t ) =>
				t.name.toLowerCase().includes( q ) ||
				t.description.toLowerCase().includes( q )
		);
	}, [ rows, search ] );

	const totalPages = Math.max( 1, Math.ceil( filtered.length / perPage ) );
	const safePage   = Math.min( page, totalPages );
	const pageRows   = filtered.slice( ( safePage - 1 ) * perPage, safePage * perPage );

	useEffect( () => {
		setPage( 1 );
	}, [ search ] );

	// Drop selections that are no longer present (e.g. after a reload).
	useEffect( () => {
		setSelected( ( prev ) => {
			const ids = new Set( rows.map( ( r ) => r.id ) );
			const next = new Set( [ ...prev ].filter( ( id ) => ids.has( id ) ) );
			return next.size === prev.size ? prev : next;
		} );
	}, [ rows ] );

	const pageIds         = pageRows.map( ( r ) => r.id );
	const allPageSelected = pageIds.length > 0 && pageIds.every( ( id ) => selected.has( id ) );

	function toggleAll(): void {
		setSelected( ( prev ) => {
			const next = new Set( prev );
			if ( allPageSelected ) {
				pageIds.forEach( ( id ) => next.delete( id ) );
			} else {
				pageIds.forEach( ( id ) => next.add( id ) );
			}
			return next;
		} );
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

	function openCreate(): void {
		setFormError( null );
		setFormParam( 'new' );
	}

	function openEdit( type: LeaveType ): void {
		setFormError( null );
		setFormParam( String( type.id ) );
	}

	async function handleSubmit( payload: LeaveTypeInput ): Promise< void > {
		setBusy( true );
		setFormError( null );
		try {
			await save( editing ? editing.id : null, payload );
			toast.success( editing ? __( 'Leave type updated.', 'erp' ) : __( 'Leave type created.', 'erp' ) );
			setFormParam( null );
		} catch ( raw ) {
			setFormError( ( raw as ApiError )?.message ?? __( 'Could not save the leave type.', 'erp' ) );
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
			toast.success( __( 'Leave type deleted.', 'erp' ) );
			setDeleting( null );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not delete the leave type.', 'erp' ) );
			setDeleting( null );
		} finally {
			setBusy( false );
		}
	}

	async function handleBulkDelete(): Promise< void > {
		setBusy( true );
		try {
			await bulkRemove( [ ...selected ] );
			toast.success( __( 'Selected leave types deleted.', 'erp' ) );
			setSelected( new Set() );
			setBulkOpen( false );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not delete the selected leave types.', 'erp' ) );
			setBulkOpen( false );
		} finally {
			setBusy( false );
		}
	}

	return (
		<section className="mx-auto w-full max-w-full">
			<header className="mb-6 flex items-center justify-between gap-4">
				<h1 className="text-2xl font-bold leading-8 text-foreground">
					{ __( 'Leave Types', 'erp' ) }
				</h1>
				{ canManage ? (
					<Button
						onClick={ openCreate }
						variant="default"
						className="inline-flex h-10 items-center gap-2 rounded-md px-5 text-sm font-medium leading-5 shadow-sm"
					>
						<Plus size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
						{ __( 'Add Leave Type', 'erp' ) }
					</Button>
				) : null }
			</header>

			<div className="rounded-lg border border-border bg-card shadow-sm">
				<div className="flex flex-wrap items-center justify-between gap-3 border-b border-border px-4 pt-3 pb-2">
					<div role="tablist" aria-label={ __( 'Leave Types', 'erp' ) } className="flex items-stretch">
						<span role="tab" aria-selected="true" className="relative inline-flex h-11 items-center gap-1.5 px-4 text-sm font-medium text-primary">
							<span>{ __( 'All', 'erp' ) }</span>
							<span className="font-normal text-muted-foreground">({ rows.length })</span>
							<span aria-hidden="true" className="absolute inset-x-0 -bottom-2 h-0.5 bg-primary" />
						</span>
					</div>
					<div className="flex items-center gap-3">
						{ canManage && selected.size > 0 ? (
							<Button
								variant="outline"
								className="inline-flex h-9 items-center gap-2 rounded-md border-destructive px-3 text-sm font-medium text-destructive hover:bg-destructive/10"
								onClick={ () => setBulkOpen( true ) }
							>
								<Trash2 size={ 14 } aria-hidden="true" />
								{ sprintf( __( 'Delete (%d)', 'erp' ), selected.size ) }
							</Button>
						) : null }
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
								placeholder={ __( 'Search leave types…', 'erp' ) }
								className="h-9 w-60 rounded-md border-border pl-9 text-sm"
								aria-label={ __( 'Search leave types', 'erp' ) }
							/>
						</div>
					</div>
				</div>

				{ error ? (
					<p className="p-6 text-sm text-destructive">{ error }</p>
				) : loading ? (
					<TableSkeleton rows={ 6 } />
				) : filtered.length === 0 ? (
					<p className="p-10 text-center text-sm text-muted-foreground">
						{ search
							? __( 'No leave types match your search.', 'erp' )
							: __( 'No leave types yet.', 'erp' ) }
					</p>
				) : (
					<LeaveTypesTable
						rows={ pageRows }
						canManage={ canManage }
						selected={ selected }
						allPageSelected={ allPageSelected }
						onToggleAll={ toggleAll }
						onToggleOne={ toggleOne }
						onEdit={ openEdit }
						onDelete={ setDeleting }
					/>
				) }

				{ ! error && ! loading && filtered.length > 0 ? (
					<OrgPagination
						page={ safePage }
						totalPages={ totalPages }
						total={ filtered.length }
						perPage={ perPage }
						onPage={ setPage }
						onPerPage={ ( n ) => { setPerPage( n ); setPage( 1 ); } }
					/>
				) : null }
			</div>

			<LeaveTypeFormDialog
				open={ formParam !== null }
				editing={ editing }
				busy={ busy }
				error={ formError }
				onClose={ () => setFormParam( null ) }
				onSubmit={ handleSubmit }
			/>

			<OrgDeleteDialog
				open={ deleting !== null }
				title={ __( 'Delete leave type?', 'erp' ) }
				description={
					deleting
						? sprintf(
								__( '%s will be permanently deleted. Leave types attached to a policy cannot be deleted.', 'erp' ),
								deleting.name
						  )
						: ''
				}
				busy={ busy }
				onConfirm={ () => void handleDelete() }
				onCancel={ () => setDeleting( null ) }
			/>

			<OrgDeleteDialog
				open={ bulkOpen }
				title={ __( 'Delete selected leave types?', 'erp' ) }
				description={ sprintf(
					__( '%d leave types will be deleted. Any attached to a policy are skipped.', 'erp' ),
					selected.size
				) }
				busy={ busy }
				onConfirm={ () => void handleBulkDelete() }
				onCancel={ () => setBulkOpen( false ) }
			/>
		</section>
	);
}

export function LeaveTypesPage(): JSX.Element {
	return (
		<CapabilityGate caps={ [ 'erp_leave_manage' ] }>
			<ErrorBoundary>
				<LeaveTypesInner />
			</ErrorBoundary>
		</CapabilityGate>
	);
}
