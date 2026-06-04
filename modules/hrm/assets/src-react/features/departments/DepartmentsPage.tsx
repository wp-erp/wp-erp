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
import type { JSX } from 'react';

import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { EmployeeAvatarStack } from '@/shared/components/EmployeeAvatarStack';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { useCan } from '@/shared/hooks/useCan';
import { __, sprintf } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';

import { OrgDeleteDialog } from '../org/OrgDeleteDialog';
import { OrgPagination } from '../org/OrgPagination';
import { useOrgCrud } from '../org/useOrgCrud';
import { DepartmentFormDialog } from './DepartmentFormDialog';
import type { Department, DepartmentInput } from './types';

type SortKey = 'title' | 'lead_name' | 'parent_title' | 'total_employees';

function DepartmentsInner(): JSX.Element {
	const { rows, loading, error, save, remove } = useOrgCrud< Department >( 'departments' );
	const canManage = useCan( 'erp_manage_department' );

	const [ search, setSearch ]     = useState( '' );
	const [ page, setPage ]         = useState( 1 );
	const [ perPage, setPerPage ]   = useState( 10 );
	const [ formOpen, setFormOpen ] = useState( false );
	const [ editing, setEditing ]   = useState< Department | null >( null );
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
	}, [ search, parentFilter ] );

	function openCreate(): void {
		setEditing( null );
		setFormError( null );
		setFormOpen( true );
	}

	function openEdit( department: Department ): void {
		setEditing( department );
		setFormError( null );
		setFormOpen( true );
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
			setFormOpen( false );
			setEditing( null );
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
		<section className="mx-auto w-full max-w-7xl">
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
				<div className="flex flex-wrap items-center justify-between gap-3 border-b border-border px-4 pt-3 pb-2">
					<div role="tablist" aria-label={ __( 'Departments', 'erp' ) } className="flex items-stretch">
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
								placeholder={ __( 'Search departments…', 'erp' ) }
								className="h-9 w-60 rounded-md border-border pl-9 text-sm"
								aria-label={ __( 'Search departments', 'erp' ) }
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
							{ __( 'Parent', 'erp' ) }
							<SmartSelect
								options={ parentOptions.map( ( p ) => ( { value: p, label: p } ) ) }
								value={ parentFilter }
								onValueChange={ ( v ) => setParentFilter( v ?? '' ) }
								placeholder={ __( 'All departments', 'erp' ) }
								searchPlaceholder={ __( 'Search…', 'erp' ) }
								emptyMessage={ __( 'No matches found.', 'erp' ) }
								showClear
								className="h-9 w-52"
								contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
							/>
						</label>
					</div>
				) : null }

				{ error ? (
					<p className="p-6 text-sm text-destructive">{ error }</p>
				) : loading ? (
					<p className="p-6 text-sm text-muted-foreground">{ __( 'Loading…', 'erp' ) }</p>
				) : filtered.length === 0 ? (
					<p className="p-10 text-center text-sm text-muted-foreground">
						{ search
							? __( 'No departments match your search.', 'erp' )
							: __( 'No departments yet.', 'erp' ) }
					</p>
				) : (
					<table className="w-full text-left">
						<thead className="border-b border-border bg-muted/40">
							<tr className="h-10 text-xs font-medium uppercase tracking-normal text-muted-foreground">
								<th scope="col" className="px-4">
									<button type="button" onClick={ () => toggleSort( 'title' ) } className="inline-flex items-center gap-1 uppercase hover:text-foreground">
										{ __( 'Name', 'erp' ) }{ sortIcon( 'title' ) }
									</button>
								</th>
								<th scope="col" className="px-4">
									<button type="button" onClick={ () => toggleSort( 'lead_name' ) } className="inline-flex items-center gap-1 uppercase hover:text-foreground">
										{ __( 'Head', 'erp' ) }{ sortIcon( 'lead_name' ) }
									</button>
								</th>
								<th scope="col" className="px-4">
									<button type="button" onClick={ () => toggleSort( 'parent_title' ) } className="inline-flex items-center gap-1 uppercase hover:text-foreground">
										{ __( 'Parent', 'erp' ) }{ sortIcon( 'parent_title' ) }
									</button>
								</th>
								<th scope="col" className="px-4">
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
							{ pageRows.map( ( dept ) => (
								<tr key={ dept.id } className="h-14 border-b border-border last:border-b-0 hover:bg-muted/40">
									<td className="px-4 align-middle">
										<div className="font-medium text-foreground">{ dept.title }</div>
										{ dept.description ? (
											<div className="truncate text-xs text-muted-foreground">{ dept.description }</div>
										) : null }
									</td>
									<td className="px-4 align-middle text-sm text-foreground">
										{ dept.lead_name || <span className="text-muted-foreground">—</span> }
									</td>
									<td className="px-4 align-middle text-sm text-foreground">
										{ dept.parent_title || <span className="text-muted-foreground">—</span> }
									</td>
									<td className="px-4 align-middle text-sm text-foreground">
										<EmployeeAvatarStack people={ dept.employees } total={ dept.total_employees } />
									</td>
									<td className="px-4 align-middle">
										{ canManage ? (
											<div className="flex justify-end">
												<DropdownMenu>
													<DropdownMenuTrigger
														render={
															<Button variant="ghost" size="icon" aria-label={ sprintf( __( 'Actions for %s', 'erp' ), dept.title ) }>
																<MoreVertical size={ 16 } aria-hidden="true" />
															</Button>
														}
													/>
													<DropdownMenuContent align="end" className="min-w-44">
														<DropdownMenuItem className="gap-2" onClick={ () => openEdit( dept ) }>
															<Pencil size={ 14 } aria-hidden="true" />
															{ __( 'Edit', 'erp' ) }
														</DropdownMenuItem>
														<DropdownMenuItem
															variant="destructive"
															className="gap-2"
															onClick={ () => setDeleting( dept ) }
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

			<DepartmentFormDialog
				open={ formOpen }
				editing={ editing }
				departments={ rows }
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
