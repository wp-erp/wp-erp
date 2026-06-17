/**
 * `/designations` route — designation (job title) directory with inline CRUD.
 *
 * Same card + dialog pattern as Departments. Reads + writes `erp/v2/designations`,
 * which delegates to the unchanged v1 model layer. Delete surfaces the
 * server-side "contains employees" guard as a toast.
 */

import {
	Button,
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuItem,
	DropdownMenuTrigger,
	Input,
	SmartSelect,
	toast,
} from '@wedevs/plugin-ui';
import { ArrowDown, ArrowUp, ArrowUpDown, Filter, MoreVertical, Pencil, Plus, Search, Trash2 } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { TableSkeleton } from '@/shared/components/TableSkeleton';
import type { JSX } from 'react';

import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { EmployeeAvatarStack } from '@/shared/components/EmployeeAvatarStack';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { useCan } from '@/shared/hooks/useCan';
import { __, sprintf } from '@/shared/i18n';
import { useModalParam } from '@/shared/useModalParam';
import type { ApiError } from '@/shared/utils/apiFetch';

import { OrgDeleteDialog } from '../org/OrgDeleteDialog';
import { OrgPagination } from '../org/OrgPagination';
import { useOrgCrud } from '../org/useOrgCrud';
import { DesignationFormDialog } from './DesignationFormDialog';
import type { Designation, DesignationInput } from './types';

type SortKey = 'title' | 'total_employees';

function DesignationsInner(): JSX.Element {
	const { rows, loading, error, save, remove } = useOrgCrud< Designation >( 'designations' );
	const canManage = useCan( 'erp_manage_designation' );

	const [ search, setSearch ]       = useState( '' );
	const [ page, setPage ]           = useState( 1 );
	const [ perPage, setPerPage ]     = useState( 10 );
	// Create/edit modal open-state lives in the URL (`?form=new` | `?form=<id>`)
	// so a browser refresh re-opens it. For edit, resolve the target from the
	// loaded rows (legacy parity: designation create/edit is a modal, not a route).
	const [ formParam, setFormParam ] = useModalParam( 'form' );
	const editing: Designation | null =
		formParam && formParam !== 'new'
			? ( rows.find( ( d ) => d.id === Number( formParam ) ) ?? null )
			: null;
	const [ deleting, setDeleting ]   = useState< Designation | null >( null );
	const [ busy, setBusy ]           = useState( false );
	const [ formError, setFormError ] = useState< string | null >( null );

	const [ sort, setSort ] = useState< { key: SortKey; dir: 'asc' | 'desc' } >( { key: 'title', dir: 'asc' } );
	const [ showFilters, setShowFilters ] = useState( false );
	const [ employeesFilter, setEmployeesFilter ] = useState< '' | 'with' | 'without' >( '' );

	const activeFilterCount = employeesFilter ? 1 : 0;
	const filterButtonActive = showFilters || activeFilterCount > 0;

	const filtered = useMemo( () => {
		const q = search.trim().toLowerCase();
		return rows.filter( ( d ) => {
			if ( q && ! d.title.toLowerCase().includes( q ) ) {
				return false;
			}
			if ( employeesFilter === 'with' && ( d.total_employees ?? 0 ) === 0 ) {
				return false;
			}
			if ( employeesFilter === 'without' && ( d.total_employees ?? 0 ) > 0 ) {
				return false;
			}
			return true;
		} );
	}, [ rows, search, employeesFilter ] );

	const sorted = useMemo( () => {
		const arr = [ ...filtered ];
		arr.sort( ( a, b ) => {
			const cmp = sort.key === 'total_employees'
				? ( a.total_employees ?? 0 ) - ( b.total_employees ?? 0 )
				: String( a.title ?? '' ).localeCompare( String( b.title ?? '' ) );
			return sort.dir === 'asc' ? cmp : -cmp;
		} );
		return arr;
	}, [ filtered, sort ] );

	function toggleSort( key: SortKey ): void {
		setSort( ( prev ) => ( prev.key === key ? { key, dir: prev.dir === 'asc' ? 'desc' : 'asc' } : { key, dir: 'asc' } ) );
	}

	function sortIcon( key: SortKey ): JSX.Element {
		if ( sort.key !== key ) {
			return <ArrowUpDown size={ 12 } aria-hidden="true" />;
		}
		return sort.dir === 'asc'
			? <ArrowUp size={ 12 } aria-hidden="true" />
			: <ArrowDown size={ 12 } aria-hidden="true" />;
	}

	const totalPages = Math.max( 1, Math.ceil( filtered.length / perPage ) );
	const safePage   = Math.min( page, totalPages );
	const pageRows   = sorted.slice( ( safePage - 1 ) * perPage, safePage * perPage );

	// Reset to the first page whenever the search query or filter changes.
	useEffect( () => {
		setPage( 1 );
	}, [ search, employeesFilter ] );

	function openCreate(): void {
		setFormError( null );
		setFormParam( 'new' );
	}

	function openEdit( designation: Designation ): void {
		setFormError( null );
		setFormParam( String( designation.id ) );
	}

	async function handleSubmit( payload: DesignationInput ): Promise< void > {
		setBusy( true );
		setFormError( null );
		try {
			await save( editing ? editing.id : null, payload as unknown as Record< string, unknown > );
			toast.success(
				editing
					? __( 'Designation updated.', 'erp' )
					: __( 'Designation created.', 'erp' )
			);
			setFormParam( null );
		} catch ( raw ) {
			setFormError( ( raw as ApiError )?.message ?? __( 'Could not save the designation.', 'erp' ) );
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
			toast.success( __( 'Designation deleted.', 'erp' ) );
			setDeleting( null );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not delete the designation.', 'erp' ) );
			setDeleting( null );
		} finally {
			setBusy( false );
		}
	}

	return (
		<section className="mx-auto w-full max-w-7xl">
			<header className="mb-6 flex items-center justify-between gap-4">
				<h1 className="text-2xl font-bold leading-8 text-foreground">
					{ __( 'Designations', 'erp' ) }
				</h1>
				{ canManage ? (
					<Button
						onClick={ openCreate }
						variant="default"
						className="inline-flex h-10 items-center gap-2 rounded-md px-5 text-sm font-medium leading-5 shadow-sm"
					>
						<Plus size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
						{ __( 'Add Designation', 'erp' ) }
					</Button>
				) : null }
			</header>

			<div className="rounded-lg border border-border bg-card shadow-sm">
				<div className="flex flex-wrap items-center justify-between gap-3 border-b border-border px-4 pt-3 pb-2">
					<div role="tablist" aria-label={ __( 'Designations', 'erp' ) } className="flex items-stretch">
						<span role="tab" aria-selected="true" className="relative inline-flex h-11 items-center gap-1.5 px-4 text-sm font-medium text-primary">
							<span>{ __( 'All', 'erp' ) }</span>
							<span className="font-normal text-muted-foreground">({ rows.length })</span>
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
								placeholder={ __( 'Search designations…', 'erp' ) }
								className="h-9 w-60 rounded-md border-border pl-9 text-sm"
								aria-label={ __( 'Search designations', 'erp' ) }
							/>
						</div>
						<button
							type="button"
							aria-label={ __( 'Toggle filters', 'erp' ) }
							aria-pressed={ filterButtonActive }
							onClick={ () => setShowFilters( ( prev ) => ! prev ) }
							className={ [
								'inline-flex h-9 items-center gap-2 rounded-md border bg-card px-3 text-sm font-medium transition-colors',
								filterButtonActive
									? 'border-primary text-primary'
									: 'border-border text-muted-foreground hover:text-foreground',
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
							{ __( 'Employees', 'erp' ) }
							<SmartSelect
								options={ [
									{ value: 'with', label: __( 'With employees', 'erp' ) },
									{ value: 'without', label: __( 'Without employees', 'erp' ) },
								] }
								value={ employeesFilter }
								onValueChange={ ( v ) => setEmployeesFilter( ( v ?? '' ) as '' | 'with' | 'without' ) }
								placeholder={ __( 'All', 'erp' ) }
								searchPlaceholder={ __( 'Search…', 'erp' ) }
								emptyMessage={ __( 'No matches found.', 'erp' ) }
								showClear
								className="h-9 w-52 bg-background"
								contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
							/>
						</label>
					</div>
				) : null }

				{ error ? (
					<p className="p-6 text-sm text-destructive">{ error }</p>
				) : loading ? (
					<TableSkeleton rows={ 6 } />
				) : filtered.length === 0 ? (
					<p className="p-10 text-center text-sm text-muted-foreground">
						{ search
							? __( 'No designations match your search.', 'erp' )
							: __( 'No designations yet.', 'erp' ) }
					</p>
				) : (
					<table className="w-full text-left">
						<thead className="border-b border-border bg-muted/40">
							<tr className="h-10 text-xs font-medium uppercase tracking-normal text-muted-foreground">
								<th scope="col" className="px-2">
									<button type="button" onClick={ () => toggleSort( 'title' ) } className="inline-flex items-center gap-1 uppercase hover:text-foreground">
										{ __( 'Name', 'erp' ) }{ sortIcon( 'title' ) }
									</button>
								</th>
								<th scope="col" className="px-2">
									<button type="button" onClick={ () => toggleSort( 'total_employees' ) } className="inline-flex items-center gap-1 uppercase hover:text-foreground">
										{ __( 'Employees', 'erp' ) }{ sortIcon( 'total_employees' ) }
									</button>
								</th>
								<th scope="col" className="w-20 px-4">
									<span className="sr-only">{ __( 'Actions', 'erp' ) }</span>
								</th>
							</tr>
						</thead>
						<tbody>
							{ pageRows.map( ( desig ) => (
								<tr key={ desig.id } className="h-18 border-b border-border last:border-b-0 hover:bg-muted/40">
									<td className="px-2 align-middle">
										<div className="font-medium text-foreground">{ desig.title }</div>
										{ desig.description ? (
											<div className="truncate text-xs text-muted-foreground">{ desig.description }</div>
										) : null }
									</td>
									<td className="px-2 align-middle text-sm text-foreground">
										<EmployeeAvatarStack people={ desig.employees } total={ desig.total_employees } />
									</td>
									<td className="px-4 align-middle">
										{ canManage ? (
											<div className="flex justify-end">
												<DropdownMenu>
													<DropdownMenuTrigger
														render={
															<Button variant="ghost" size="icon" aria-label={ sprintf( __( 'Actions for %s', 'erp' ), desig.title ) }>
																<MoreVertical size={ 16 } aria-hidden="true" />
															</Button>
														}
													/>
													<DropdownMenuContent align="end" className="min-w-44">
														<DropdownMenuItem className="gap-2" onClick={ () => openEdit( desig ) }>
															<Pencil size={ 14 } aria-hidden="true" />
															{ __( 'Edit', 'erp' ) }
														</DropdownMenuItem>
														<DropdownMenuItem
															variant="destructive"
															className="gap-2"
															onClick={ () => setDeleting( desig ) }
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

			<DesignationFormDialog
				open={ formParam !== null }
				editing={ editing }
				busy={ busy }
				error={ formError }
				onClose={ () => setFormParam( null ) }
				onSubmit={ handleSubmit }
			/>

			<OrgDeleteDialog
				open={ deleting !== null }
				title={ __( 'Delete designation?', 'erp' ) }
				description={
					deleting
						? sprintf(
								__( '%s will be permanently deleted. Designations with assigned employees cannot be deleted.', 'erp' ),
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

export function DesignationsPage(): JSX.Element {
	return (
		<CapabilityGate caps={ [ 'erp_view_list' ] }>
			<ErrorBoundary>
				<DesignationsInner />
			</ErrorBoundary>
		</CapabilityGate>
	);
}
