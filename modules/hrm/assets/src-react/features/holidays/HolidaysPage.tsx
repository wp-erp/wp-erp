/**
 * `/leave/holidays` route — holiday calendar management.
 *
 * Page header with Add + Import CTAs, a year filter and search, and a table
 * with per-row Edit / Delete. Create + edit happen in a dialog; delete is
 * confirmed. Import is a separate two-step dialog (parse → review → insert).
 *
 * Reads + writes `erp/v2/holidays`, which delegates to the unchanged v1 model
 * layer so every legacy hook keeps firing.
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
import { Filter, MoreVertical, Pencil, Plus, Search, Trash2, Upload } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import type { JSX } from 'react';

import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { useCan } from '@/shared/hooks/useCan';
import { __, sprintf } from '@/shared/i18n';
import { useModalParam } from '@/shared/useModalParam';
import type { ApiError } from '@/shared/utils/apiFetch';

import { OrgDeleteDialog } from '../org/OrgDeleteDialog';
import { OrgPagination } from '../org/OrgPagination';
import { HolidayFormDialog } from './HolidayFormDialog';
import { HolidayImportDialog } from './HolidayImportDialog';
import type { Holiday, HolidayInput } from './types';
import { useHolidays } from './useHolidays';

/** Format a `YYYY-MM-DD` value for display (locale short date). */
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

function HolidaysInner(): JSX.Element {
	const thisYear = new Date().getFullYear();
	const canManage = useCan( 'erp_leave_manage' );

	const [ year, setYear ]       = useState( thisYear );
	const [ search, setSearch ]   = useState( '' );
	const [ showFilters, setShowFilters ] = useState( false );
	const [ page, setPage ]       = useState( 1 );
	const [ perPage, setPerPage ] = useState( 20 );

	const { rows, total, loading, error, save, remove, removeMany, parseFile, importRows } = useHolidays( {
		year,
		search,
		page,
		perPage,
	} );

	// Create/edit + import modal open-state lives in the URL (`?form=new` |
	// `?form=<id>` and `?import=open`) so a browser refresh re-opens them. For
	// edit, resolve the target from the loaded rows (legacy parity: holiday
	// create/edit is a modal, not a route).
	const [ formParam, setFormParam ]     = useModalParam( 'form' );
	const [ importParam, setImportParam ] = useModalParam( 'import' );
	const editing: Holiday | null =
		formParam && formParam !== 'new'
			? ( rows.find( ( h ) => h.id === Number( formParam ) ) ?? null )
			: null;
	const [ deleting, setDeleting ]   = useState< Holiday | null >( null );
	const [ selectedIds, setSelectedIds ] = useState< readonly number[] >( [] );
	const [ bulkConfirm, setBulkConfirm ] = useState( false );
	const [ busy, setBusy ]           = useState( false );
	const [ formError, setFormError ] = useState< string | null >( null );

	const totalPages = Math.max( 1, Math.ceil( total / perPage ) );

	// Reset to the first page whenever the filters change.
	useEffect( () => {
		setPage( 1 );
	}, [ year, search ] );

	// Drop selections that are no longer visible after a list refresh / page change.
	useEffect( () => {
		const visible = new Set( rows.map( ( h ) => h.id ) );
		setSelectedIds( ( prev ) => prev.filter( ( id ) => visible.has( id ) ) );
	}, [ rows ] );

	const allSelected = rows.length > 0 && selectedIds.length === rows.length;

	function toggleAll(): void {
		setSelectedIds( allSelected ? [] : rows.map( ( h ) => h.id ) );
	}

	function toggleRow( id: number ): void {
		setSelectedIds( ( prev ) =>
			prev.includes( id ) ? prev.filter( ( x ) => x !== id ) : [ ...prev, id ]
		);
	}

	async function handleBulkDelete(): Promise< void > {
		setBusy( true );
		try {
			await removeMany( selectedIds );
			toast.success(
				sprintf(
					/* translators: %d: number of holidays deleted */
					selectedIds.length === 1 ? __( '%d holiday deleted.', 'erp' ) : __( '%d holidays deleted.', 'erp' ),
					selectedIds.length
				)
			);
			setSelectedIds( [] );
			setBulkConfirm( false );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not delete the holidays.', 'erp' ) );
			setBulkConfirm( false );
		} finally {
			setBusy( false );
		}
	}

	const yearOptions = useMemo( () => {
		const years: Array< { value: string; label: string } > = [ { value: '0', label: __( 'All Years', 'erp' ) } ];
		for ( let y = thisYear + 1; y >= thisYear - 5; y-- ) {
			years.push( { value: String( y ), label: String( y ) } );
		}
		return years;
	}, [ thisYear ] );

	const activeFilterCount  = year ? 1 : 0;
	const filterButtonActive = showFilters || activeFilterCount > 0;

	function openCreate(): void {
		setFormError( null );
		setFormParam( 'new' );
	}

	function openEdit( holiday: Holiday ): void {
		setFormError( null );
		setFormParam( String( holiday.id ) );
	}

	async function handleSubmit( payload: HolidayInput ): Promise< void > {
		setBusy( true );
		setFormError( null );
		try {
			await save( editing ? editing.id : null, payload );
			toast.success( editing ? __( 'Holiday updated.', 'erp' ) : __( 'Holiday created.', 'erp' ) );
			setFormParam( null );
		} catch ( raw ) {
			setFormError( ( raw as ApiError )?.message ?? __( 'Could not save the holiday.', 'erp' ) );
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
			toast.success( __( 'Holiday deleted.', 'erp' ) );
			setDeleting( null );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not delete the holiday.', 'erp' ) );
			setDeleting( null );
		} finally {
			setBusy( false );
		}
	}

	return (
		<section className="mx-auto w-full max-w-7xl">
			<header className="mb-6 flex items-center justify-between gap-4">
				<h1 className="text-2xl font-bold leading-8 text-foreground">
					{ __( 'Holidays', 'erp' ) }
				</h1>
				{ canManage ? (
					<div className="flex items-center gap-2">
						<Button
							onClick={ () => setImportParam( 'open' ) }
							variant="outline"
							className="inline-flex h-10 items-center gap-2 rounded-md px-4 text-sm font-medium"
						>
							<Upload size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
							{ __( 'Import', 'erp' ) }
						</Button>
						<Button
							onClick={ openCreate }
							variant="default"
							className="inline-flex h-10 items-center gap-2 rounded-md px-5 text-sm font-medium leading-5 shadow-sm"
						>
							<Plus size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
							{ __( 'Add Holiday', 'erp' ) }
						</Button>
					</div>
				) : null }
			</header>

			<div className="rounded-lg border border-border bg-card shadow-sm">
				<div className="flex flex-wrap items-center justify-between gap-3 border-b border-border px-4 pt-3 pb-2">
					<div role="tablist" aria-label={ __( 'Holidays', 'erp' ) } className="flex items-stretch">
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
								placeholder={ __( 'Search holidays…', 'erp' ) }
								className="h-9 w-60 rounded-md border-border pl-9 text-sm"
								aria-label={ __( 'Search holidays', 'erp' ) }
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
							{ __( 'Year', 'erp' ) }
							<SmartSelect
								options={ yearOptions }
								value={ String( year || '0' ) }
								onValueChange={ ( v ) => setYear( Number( v || 0 ) ) }
								placeholder={ __( 'All Years', 'erp' ) }
								showClear
								className="h-9 w-36"
								contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
							/>
						</label>
					</div>
				) : null }

				{ canManage && selectedIds.length > 0 ? (
					<div className="flex items-center justify-between gap-3 border-b border-border bg-muted/30 px-4 py-2.5">
						<span className="text-sm font-medium text-foreground">
							{ sprintf(
								/* translators: %d: number of selected holidays */
								selectedIds.length === 1 ? __( '%d selected', 'erp' ) : __( '%d selected', 'erp' ),
								selectedIds.length
							) }
						</span>
						<div className="flex items-center gap-2">
							<Button
								variant="ghost"
								size="sm"
								className="h-8 px-3 text-sm"
								onClick={ () => setSelectedIds( [] ) }
							>
								{ __( 'Clear', 'erp' ) }
							</Button>
							<Button
								variant="destructive"
								size="sm"
								className="h-8 gap-1.5 px-3 text-sm"
								onClick={ () => setBulkConfirm( true ) }
							>
								<Trash2 size={ 14 } aria-hidden="true" />
								{ __( 'Delete', 'erp' ) }
							</Button>
						</div>
					</div>
				) : null }

				{ error ? (
					<p className="p-6 text-sm text-destructive">{ error }</p>
				) : loading ? (
					<p className="p-6 text-sm text-muted-foreground">{ __( 'Loading…', 'erp' ) }</p>
				) : rows.length === 0 ? (
					<p className="p-10 text-center text-sm text-muted-foreground">
						{ search
							? __( 'No holidays match your search.', 'erp' )
							: __( 'No holidays for this year yet.', 'erp' ) }
					</p>
				) : (
					<div className="overflow-x-auto">
						<table className="w-full min-w-[40rem] text-left">
						<thead className="border-b border-border bg-muted/40">
							<tr className="h-10 text-xs font-medium uppercase tracking-normal text-muted-foreground">
								{ canManage ? (
									<th scope="col" className="w-10 px-4">
										<Checkbox
											checked={ allSelected }
											onCheckedChange={ toggleAll }
											aria-label={ __( 'Select all holidays', 'erp' ) }
										/>
									</th>
								) : null }
								<th scope="col" className="px-2">{ __( 'Title', 'erp' ) }</th>
								<th scope="col" className="px-2">{ __( 'Date', 'erp' ) }</th>
								<th scope="col" className="px-2">{ __( 'Duration', 'erp' ) }</th>
								<th scope="col" className="px-2">{ __( 'Description', 'erp' ) }</th>
								<th scope="col" className="w-20 px-4">
									<span className="sr-only">{ __( 'Actions', 'erp' ) }</span>
								</th>
							</tr>
						</thead>
						<tbody>
							{ rows.map( ( holiday ) => (
								<tr key={ holiday.id } className="h-18 border-b border-border last:border-b-0 hover:bg-muted/40">
									{ canManage ? (
										<td className="w-10 px-4 align-middle">
											<Checkbox
												checked={ selectedIds.includes( holiday.id ) }
												onCheckedChange={ () => toggleRow( holiday.id ) }
												aria-label={ sprintf( __( 'Select %s', 'erp' ), holiday.title ) }
											/>
										</td>
									) : null }
									<td className="px-2 align-middle font-medium text-foreground">{ holiday.title }</td>
									<td className="whitespace-nowrap px-2 align-middle text-sm text-foreground">
										{ holiday.range
											? `${ fmt( holiday.start ) } – ${ fmt( holiday.end ) }`
											: fmt( holiday.start ) }
									</td>
									<td className="px-2 align-middle text-sm text-muted-foreground">
										{ sprintf(
											/* translators: %d: number of days */
											holiday.duration === 1 ? __( '%d day', 'erp' ) : __( '%d days', 'erp' ),
											holiday.duration
										) }
									</td>
									<td className="px-2 align-middle text-sm text-muted-foreground">
										{ holiday.description ? (
											<span className="line-clamp-1">{ holiday.description }</span>
										) : (
											<span className="text-muted-foreground">—</span>
										) }
									</td>
									<td className="px-4 align-middle">
										{ canManage ? (
											<div className="flex justify-end">
												<DropdownMenu>
													<DropdownMenuTrigger
														render={
															<Button variant="ghost" size="icon" aria-label={ sprintf( __( 'Actions for %s', 'erp' ), holiday.title ) }>
																<MoreVertical size={ 16 } aria-hidden="true" />
															</Button>
														}
													/>
													<DropdownMenuContent align="end" className="min-w-44">
														<DropdownMenuItem className="gap-2" onClick={ () => openEdit( holiday ) }>
															<Pencil size={ 14 } aria-hidden="true" />
															{ __( 'Edit', 'erp' ) }
														</DropdownMenuItem>
														<DropdownMenuItem
															variant="destructive"
															className="gap-2"
															onClick={ () => setDeleting( holiday ) }
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

			<HolidayFormDialog
				open={ formParam !== null }
				editing={ editing }
				busy={ busy }
				error={ formError }
				onClose={ () => setFormParam( null ) }
				onSubmit={ handleSubmit }
			/>

			<HolidayImportDialog
				open={ importParam !== null }
				onClose={ () => setImportParam( null ) }
				onParse={ parseFile }
				onImport={ importRows }
			/>

			<OrgDeleteDialog
				open={ deleting !== null }
				title={ __( 'Delete holiday?', 'erp' ) }
				description={
					deleting
						? sprintf( __( '%s will be permanently deleted.', 'erp' ), deleting.title )
						: ''
				}
				busy={ busy }
				onConfirm={ () => void handleDelete() }
				onCancel={ () => setDeleting( null ) }
			/>

			<OrgDeleteDialog
				open={ bulkConfirm }
				title={ __( 'Delete holidays?', 'erp' ) }
				description={ sprintf(
					/* translators: %d: number of holidays */
					selectedIds.length === 1
						? __( '%d holiday will be permanently deleted.', 'erp' )
						: __( '%d holidays will be permanently deleted.', 'erp' ),
					selectedIds.length
				) }
				busy={ busy }
				onConfirm={ () => void handleBulkDelete() }
				onCancel={ () => setBulkConfirm( false ) }
			/>
		</section>
	);
}

export function HolidaysPage(): JSX.Element {
	return (
		<CapabilityGate caps={ [ 'erp_leave_manage' ] }>
			<ErrorBoundary>
				<HolidaysInner />
			</ErrorBoundary>
		</CapabilityGate>
	);
}
