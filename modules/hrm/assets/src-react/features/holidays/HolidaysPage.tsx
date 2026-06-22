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
	toast,
} from '@wedevs/plugin-ui';
import { Plus, Trash2, Upload } from 'lucide-react';
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
import { HolidayFormDialog } from './HolidayFormDialog';
import { HolidayImportDialog } from './HolidayImportDialog';
import { HolidaysTable } from './HolidaysTable';
import { HolidaysToolbar } from './HolidaysToolbar';
import type { Holiday, HolidayInput } from './types';
import { useHolidays } from './useHolidays';

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
		<section className="mx-auto w-full max-w-full">
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
				<HolidaysToolbar
					total={ total }
					search={ search }
					onSearch={ setSearch }
					onToggleFilters={ () => setShowFilters( ( prev ) => ! prev ) }
					filterButtonActive={ filterButtonActive }
					activeFilterCount={ activeFilterCount }
					year={ year }
					onYear={ ( v ) => setYear( v ) }
					yearOptions={ yearOptions }
				/>

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
					<TableSkeleton rows={ 6 } />
				) : rows.length === 0 ? (
					<p className="p-10 text-center text-sm text-muted-foreground">
						{ search
							? __( 'No holidays match your search.', 'erp' )
							: __( 'No holidays for this year yet.', 'erp' ) }
					</p>
				) : (
					<HolidaysTable
						rows={ rows }
						canManage={ canManage }
						selectedIds={ selectedIds }
						allSelected={ allSelected }
						onToggleAll={ toggleAll }
						onToggleRow={ toggleRow }
						onEdit={ openEdit }
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
