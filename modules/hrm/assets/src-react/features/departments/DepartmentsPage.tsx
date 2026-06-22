/**
 * `/departments` route — department directory with inline CRUD.
 *
 * Card layout mirrors the People table: a page header with the primary CTA, a
 * search field, and a table with per-row Edit / Delete actions. Create + edit
 * happen in a dialog; delete is confirmed and surfaces the server-side
 * "contains employees" guard as a toast.
 *
 * Reads + writes `erp/v2/departments`, which delegates to the unchanged v1
 * model layer so every legacy hook keeps firing.
 */

import { Button, toast } from '@wedevs/plugin-ui';
import { Plus, Trash2 } from 'lucide-react';
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
import { useOrgCrud } from '../org/useOrgCrud';
import { DepartmentFormDialog } from './DepartmentFormDialog';
import { DepartmentsTable, type SortKey } from './DepartmentsTable';
import { DepartmentsToolbar } from './DepartmentsToolbar';
import type { Department, DepartmentInput } from './types';

function DepartmentsInner(): JSX.Element {
	const { rows, loading, error, save, remove, bulkRemove } = useOrgCrud< Department >( 'departments' );
	const canManage = useCan( 'erp_manage_department' );
	const [ selected, setSelected ] = useState< ReadonlySet< number > >( new Set() );

	const [ search, setSearch ]     = useState( '' );
	const [ page, setPage ]         = useState( 1 );
	const [ perPage, setPerPage ]   = useState( 10 );
	// Create/edit modal open-state lives in the URL (`?form=new` | `?form=<id>`)
	// so a browser refresh re-opens it. For edit, resolve the target from the
	// loaded rows (legacy parity: department create/edit is a modal, not a route).
	const [ formParam, setFormParam ] = useModalParam( 'form' );
	const editing: Department | null =
		formParam && formParam !== 'new'
			? ( rows.find( ( d ) => d.id === Number( formParam ) ) ?? null )
			: null;
	const [ deleting, setDeleting ] = useState< Department | null >( null );
	const [ busy, setBusy ]         = useState( false );
	const [ formError, setFormError ] = useState< string | null >( null );

	const [ sort, setSort ] = useState< { key: SortKey; dir: 'asc' | 'desc' } >( { key: 'title', dir: 'asc' } );
	const [ showFilters, setShowFilters ] = useState( false );
	const [ parentFilter, setParentFilter ] = useState( '' );

	// Parent departments present in the data (for the Filter panel select).
	const parentOptions = useMemo( () => {
		const set = new Set< string >();
		rows.forEach( ( d ) => {
			if ( d.parent_title ) {
				set.add( d.parent_title );
			}
		} );
		return Array.from( set ).sort( ( a, b ) => a.localeCompare( b ) );
	}, [ rows ] );

	const activeFilterCount = parentFilter ? 1 : 0;
	const filterButtonActive = showFilters || activeFilterCount > 0;

	const filtered = useMemo( () => {
		const q = search.trim().toLowerCase();
		return rows.filter( ( d ) => {
			if ( q && ! d.title.toLowerCase().includes( q ) ) {
				return false;
			}
			if ( parentFilter && d.parent_title !== parentFilter ) {
				return false;
			}
			return true;
		} );
	}, [ rows, search, parentFilter ] );

	const sorted = useMemo( () => {
		const arr = [ ...filtered ];
		arr.sort( ( a, b ) => {
			const cmp = sort.key === 'total_employees'
				? ( a.total_employees ?? 0 ) - ( b.total_employees ?? 0 )
				: String( ( a as unknown as Record< string, unknown > )[ sort.key ] ?? '' )
					.localeCompare( String( ( b as unknown as Record< string, unknown > )[ sort.key ] ?? '' ) );
			return sort.dir === 'asc' ? cmp : -cmp;
		} );
		return arr;
	}, [ filtered, sort ] );

	function toggleSort( key: SortKey ): void {
		setSort( ( prev ) => ( prev.key === key ? { key, dir: prev.dir === 'asc' ? 'desc' : 'asc' } : { key, dir: 'asc' } ) );
	}

	const totalPages = Math.max( 1, Math.ceil( filtered.length / perPage ) );
	const safePage   = Math.min( page, totalPages );
	const pageRows   = sorted.slice( ( safePage - 1 ) * perPage, safePage * perPage );

	const pageIds    = pageRows.map( ( d ) => d.id );
	const allChecked = pageIds.length > 0 && pageIds.every( ( id ) => selected.has( id ) );

	function toggleAll(): void {
		setSelected( ( prev ) => {
			const next = new Set( prev );
			if ( allChecked ) {
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

	async function handleBulkDelete(): Promise< void > {
		const ids = Array.from( selected );
		if ( ids.length === 0 ) {
			return;
		}
		setBusy( true );
		try {
			const failed = await bulkRemove( ids );
			setSelected( new Set() );
			if ( failed > 0 ) {
				toast.error( sprintf( __( '%d department(s) could not be deleted (they may have employees).', 'erp' ), failed ) );
			} else {
				toast.success( __( 'Departments deleted.', 'erp' ) );
			}
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not delete the departments.', 'erp' ) );
		} finally {
			setBusy( false );
		}
	}

	// Reset to the first page whenever the search query or filter changes.
	useEffect( () => {
		setPage( 1 );
	}, [ search, parentFilter ] );

	function openCreate(): void {
		setFormError( null );
		setFormParam( 'new' );
	}

	function openEdit( department: Department ): void {
		setFormError( null );
		setFormParam( String( department.id ) );
	}

	async function handleSubmit( payload: DepartmentInput ): Promise< void > {
		setBusy( true );
		setFormError( null );
		try {
			await save( editing ? editing.id : null, payload as unknown as Record< string, unknown > );
			toast.success(
				editing
					? __( 'Department updated.', 'erp' )
					: __( 'Department created.', 'erp' )
			);
			setFormParam( null );
		} catch ( raw ) {
			setFormError( ( raw as ApiError )?.message ?? __( 'Could not save the department.', 'erp' ) );
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
			toast.success( __( 'Department deleted.', 'erp' ) );
			setDeleting( null );
		} catch ( raw ) {
			// The "contains employees" guard returns HTTP 409 with the legacy message.
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not delete the department.', 'erp' ) );
			setDeleting( null );
		} finally {
			setBusy( false );
		}
	}

	return (
		<section className="mx-auto w-full max-w-full">
			<header className="mb-6 flex items-center justify-between gap-4">
				<h1 className="text-2xl font-bold leading-8 text-foreground">
					{ __( 'Departments', 'erp' ) }
				</h1>
				{ canManage ? (
					<Button
						onClick={ openCreate }
						variant="default"
						className="inline-flex h-10 items-center gap-2 rounded-md px-5 text-sm font-medium leading-5 shadow-sm"
					>
						<Plus size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
						{ __( 'Add Department', 'erp' ) }
					</Button>
				) : null }
			</header>

			<div className="rounded-lg border border-border bg-card shadow-sm">
				<DepartmentsToolbar
					count={ rows.length }
					search={ search }
					onSearch={ setSearch }
					onToggleFilters={ () => setShowFilters( ( prev ) => ! prev ) }
					filterButtonActive={ filterButtonActive }
					activeFilterCount={ activeFilterCount }
					parentOptions={ parentOptions }
					parentFilter={ parentFilter }
					onParentFilter={ setParentFilter }
				/>

				{ canManage && selected.size > 0 ? (
					<div className="flex items-center justify-between gap-3 border-b border-border bg-muted/30 px-4 py-2.5">
						<span className="text-sm font-medium text-foreground">{ sprintf( __( '%d selected', 'erp' ), selected.size ) }</span>
						<Button variant="outline" className="h-9 gap-1.5 text-destructive hover:text-destructive" disabled={ busy } onClick={ handleBulkDelete }>
							<Trash2 size={ 14 } aria-hidden="true" /> { __( 'Delete', 'erp' ) }
						</Button>
					</div>
				) : null }

				{ error ? (
					<p className="p-6 text-sm text-destructive">{ error }</p>
				) : loading ? (
					<TableSkeleton rows={ 6 } />
				) : filtered.length === 0 ? (
					<p className="p-10 text-center text-sm text-muted-foreground">
						{ search
							? __( 'No departments match your search.', 'erp' )
							: __( 'No departments yet.', 'erp' ) }
					</p>
				) : (
					<DepartmentsTable
						rows={ pageRows }
						canManage={ canManage }
						selected={ selected }
						allChecked={ allChecked }
						sort={ sort }
						onToggleAll={ toggleAll }
						onToggleOne={ toggleOne }
						onToggleSort={ toggleSort }
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

			<DepartmentFormDialog
				open={ formParam !== null }
				editing={ editing }
				departments={ rows }
				busy={ busy }
				error={ formError }
				onClose={ () => setFormParam( null ) }
				onSubmit={ handleSubmit }
			/>

			<OrgDeleteDialog
				open={ deleting !== null }
				title={ __( 'Delete department?', 'erp' ) }
				description={
					deleting
						? sprintf(
								__( '%s will be permanently deleted. Departments with assigned employees cannot be deleted.', 'erp' ),
								deleting.title
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

export function DepartmentsPage(): JSX.Element {
	return (
		<CapabilityGate caps={ [ 'erp_view_list' ] }>
			<ErrorBoundary>
				<DepartmentsInner />
			</ErrorBoundary>
		</CapabilityGate>
	);
}
