/**
 * `/announcements` route — company announcement management.
 *
 * Employees-table conventions: status tabs (Published / Draft / Trash) with
 * counts + inline search, the shared `OrgPagination` footer, per-row actions.
 * Create + edit happen in a dialog; trash / restore / permanent-delete mirror
 * the legacy bulk handlers.
 *
 * Orchestration only — list state, data hook, and mutation handlers. Chrome
 * lives alongside: `AnnouncementsToolbar` (status tabs + search),
 * `AnnouncementsTable` (rows), `announcements-format` (pure consts/formatter).
 */

import { Button, toast } from '@wedevs/plugin-ui';
import { Plus, RotateCcw, Trash2 } from 'lucide-react';
import { useCallback, useEffect, useMemo, useState } from 'react';
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
import { AnnouncementFormDialog } from './AnnouncementFormDialog';
import { AnnouncementsTable } from './AnnouncementsTable';
import { AnnouncementsToolbar } from './AnnouncementsToolbar';
import { SEARCH_DEBOUNCE_MS } from './announcements-format';
import type { Announcement, AnnouncementDetail, AnnouncementFormOptions, AnnouncementInput } from './types';
import { useAnnouncements } from './useAnnouncements';

function AnnouncementsInner(): JSX.Element {
	const canManage = useCan( 'erp_manage_announcement' );

	const [ status, setStatus ]   = useState( 'publish' );
	const [ searchInput, setSearchInput ] = useState( '' );
	const [ search, setSearch ]   = useState( '' );
	const [ page, setPage ]       = useState( 1 );
	const [ perPage, setPerPage ] = useState( 20 );

	// Published-date range filter (F11a) + its collapsible panel toggle.
	const [ showFilters, setShowFilters ] = useState( false );
	const [ startDate, setStartDate ]     = useState( '' );
	const [ endDate, setEndDate ]         = useState( '' );

	// Bulk-selection state (F11b) — client-side loop over the single endpoints.
	const [ selected, setSelected ] = useState< ReadonlySet< number > >( new Set() );

	const { rows, total, counts, loading, error, getOne, save, remove, restore, bulkRemove, bulkRestore, loadOptions } = useAnnouncements( {
		status,
		search,
		page,
		perPage,
		startDate,
		endDate,
	} );

	const activeFilterCount = ( startDate ? 1 : 0 ) + ( endDate ? 1 : 0 );
	const filterButtonActive = showFilters || activeFilterCount > 0;

	const pageIds    = useMemo( () => rows.map( ( r ) => r.id ), [ rows ] );
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

	const [ options, setOptions ]     = useState< AnnouncementFormOptions | null >( null );
	// Create/edit modal open-state lives in the URL (`?form=new` | `?form=<id>`)
	// so a browser refresh re-opens it. The edit dialog needs the full detail
	// (not in the list), so it's fetched and cached in `editing`.
	const [ formParam, setFormParam ] = useModalParam( 'form' );
	const [ editing, setEditing ]     = useState< AnnouncementDetail | null >( null );
	const [ deleting, setDeleting ]   = useState< Announcement | null >( null );
	const [ busy, setBusy ]           = useState( false );
	const [ formError, setFormError ] = useState< string | null >( null );

	const totalPages = Math.max( 1, Math.ceil( total / perPage ) );

	useEffect( () => {
		const id = window.setTimeout( () => setSearch( searchInput ), SEARCH_DEBOUNCE_MS );
		return () => window.clearTimeout( id );
	}, [ searchInput ] );

	useEffect( () => {
		setPage( 1 );
	}, [ status, search, startDate, endDate ] );

	// Drop the selection whenever the visible cohort changes (tab/search/dates/page).
	useEffect( () => {
		setSelected( new Set() );
	}, [ status, search, startDate, endDate, page ] );

	const ensureOptions = useCallback( async (): Promise< AnnouncementFormOptions > => {
		if ( options ) {
			return options;
		}
		const opts = await loadOptions();
		setOptions( opts );
		return opts;
	}, [ options, loadOptions ] );

	async function openCreate(): Promise< void > {
		await ensureOptions();
		setEditing( null );
		setFormError( null );
		setFormParam( 'new' );
	}

	async function openEdit( row: Announcement ): Promise< void > {
		await ensureOptions();
		setFormError( null );
		try {
			const full = await getOne( row.id );
			setEditing( full );
			setFormParam( String( row.id ) );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not load the announcement.', 'erp' ) );
		}
	}

	// Deep-link / refresh: when `?form=<id>` is present but the detail isn't
	// loaded yet (e.g. opened the dialog, then refreshed), fetch it.
	useEffect( () => {
		const id = formParam && formParam !== 'new' ? Number( formParam ) : 0;
		if ( ! id || editing?.id === id ) {
			return;
		}
		let active = true;
		void ensureOptions();
		void getOne( id )
			.then( ( full ) => {
				if ( active ) {
					setEditing( full );
				}
			} )
			.catch( ( raw ) => {
				if ( active ) {
					toast.error( ( raw as ApiError )?.message ?? __( 'Could not load the announcement.', 'erp' ) );
					setFormParam( null );
				}
			} );
		return () => {
			active = false;
		};
	}, [ formParam, editing, ensureOptions, getOne, setFormParam ] );

	async function handleSubmit( payload: AnnouncementInput ): Promise< void > {
		setBusy( true );
		setFormError( null );
		try {
			await save( editing ? editing.id : null, payload );
			toast.success( editing ? __( 'Announcement updated.', 'erp' ) : __( 'Announcement saved.', 'erp' ) );
			setFormParam( null );
			setEditing( null );
		} catch ( raw ) {
			setFormError( ( raw as ApiError )?.message ?? __( 'Could not save the announcement.', 'erp' ) );
		} finally {
			setBusy( false );
		}
	}

	async function handleRestore( row: Announcement ): Promise< void > {
		try {
			await restore( row.id );
			toast.success( __( 'Announcement restored.', 'erp' ) );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not restore the announcement.', 'erp' ) );
		}
	}

	async function handleDelete(): Promise< void > {
		if ( ! deleting ) {
			return;
		}
		const force = deleting.status === 'trash';
		setBusy( true );
		try {
			await remove( deleting.id, force );
			toast.success( force ? __( 'Announcement permanently deleted.', 'erp' ) : __( 'Announcement trashed.', 'erp' ) );
			setDeleting( null );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not delete the announcement.', 'erp' ) );
			setDeleting( null );
		} finally {
			setBusy( false );
		}
	}

	async function handleBulkTrash(): Promise< void > {
		const ids = Array.from( selected );
		if ( ids.length === 0 ) {
			return;
		}
		setBusy( true );
		try {
			const failed = await bulkRemove( ids, false );
			setSelected( new Set() );
			if ( failed > 0 ) {
				toast.error( sprintf( __( '%d announcement(s) could not be trashed.', 'erp' ), failed ) );
			} else {
				toast.success( __( 'Announcements moved to trash.', 'erp' ) );
			}
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not trash the announcements.', 'erp' ) );
		} finally {
			setBusy( false );
		}
	}

	async function handleBulkRestore(): Promise< void > {
		const ids = Array.from( selected );
		if ( ids.length === 0 ) {
			return;
		}
		setBusy( true );
		try {
			const failed = await bulkRestore( ids );
			setSelected( new Set() );
			if ( failed > 0 ) {
				toast.error( sprintf( __( '%d announcement(s) could not be restored.', 'erp' ), failed ) );
			} else {
				toast.success( __( 'Announcements restored.', 'erp' ) );
			}
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not restore the announcements.', 'erp' ) );
		} finally {
			setBusy( false );
		}
	}

	async function handleBulkDelete(): Promise< void > {
		const ids = Array.from( selected );
		if ( ids.length === 0 ) {
			return;
		}
		setBusy( true );
		try {
			const failed = await bulkRemove( ids, true );
			setSelected( new Set() );
			if ( failed > 0 ) {
				toast.error( sprintf( __( '%d announcement(s) could not be deleted.', 'erp' ), failed ) );
			} else {
				toast.success( __( 'Announcements permanently deleted.', 'erp' ) );
			}
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not delete the announcements.', 'erp' ) );
		} finally {
			setBusy( false );
		}
	}

	function countFor( value: string ): number {
		return value === 'draft' ? counts.draft : value === 'trash' ? counts.trash : counts.publish;
	}

	return (
		<section className="mx-auto w-full max-w-full">
			<header className="mb-6 flex items-center justify-between gap-4">
				<h1 className="text-2xl font-bold leading-8 text-foreground">
					{ __( 'Announcements', 'erp' ) }
				</h1>
				{ canManage ? (
					<Button
						onClick={ () => void openCreate() }
						variant="default"
						className="inline-flex h-10 items-center gap-2 rounded-md px-5 text-sm font-medium leading-5 shadow-sm"
					>
						<Plus size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
						{ __( 'New Announcement', 'erp' ) }
					</Button>
				) : null }
			</header>

			<div className="rounded-lg border border-border bg-card shadow-sm">
				<AnnouncementsToolbar
					status={ status }
					onStatus={ setStatus }
					searchInput={ searchInput }
					onSearchInput={ setSearchInput }
					countFor={ countFor }
					onToggleFilters={ () => setShowFilters( ( prev ) => ! prev ) }
					filterButtonActive={ filterButtonActive }
					activeFilterCount={ activeFilterCount }
					startDate={ startDate }
					endDate={ endDate }
					onStartDate={ setStartDate }
					onEndDate={ setEndDate }
				/>

				{ canManage && selected.size > 0 ? (
					<div className="flex flex-wrap items-center justify-between gap-3 border-b border-border bg-muted/30 px-4 py-2.5">
						<span className="text-sm font-medium text-foreground">{ sprintf( __( '%d selected', 'erp' ), selected.size ) }</span>
						<div className="flex items-center gap-2">
							{ status === 'trash' ? (
								<>
									<Button variant="outline" className="h-9 gap-1.5" disabled={ busy } onClick={ () => void handleBulkRestore() }>
										<RotateCcw size={ 14 } aria-hidden="true" /> { __( 'Restore', 'erp' ) }
									</Button>
									<Button variant="outline" className="h-9 gap-1.5 border-destructive text-destructive hover:border-destructive hover:text-destructive" disabled={ busy } onClick={ () => void handleBulkDelete() }>
										<Trash2 size={ 14 } aria-hidden="true" /> { __( 'Delete permanently', 'erp' ) }
									</Button>
								</>
							) : (
								<Button variant="outline" className="h-9 gap-1.5 border-destructive text-destructive hover:border-destructive hover:text-destructive" disabled={ busy } onClick={ () => void handleBulkTrash() }>
									<Trash2 size={ 14 } aria-hidden="true" /> { __( 'Trash', 'erp' ) }
								</Button>
							) }
						</div>
					</div>
				) : null }

				{ error ? (
					<p className="p-6 text-sm text-destructive">{ error }</p>
				) : loading ? (
					<TableSkeleton rows={ 6 } />
				) : rows.length === 0 ? (
					<p className="p-10 text-center text-sm text-muted-foreground">
						{ search ? __( 'No announcements match your search.', 'erp' ) : __( 'No announcements here yet.', 'erp' ) }
					</p>
				) : (
					<AnnouncementsTable
						rows={ rows }
						canManage={ canManage }
						selected={ selected }
						allChecked={ allChecked }
						onToggleAll={ toggleAll }
						onToggleOne={ toggleOne }
						onEdit={ ( row ) => void openEdit( row ) }
						onRestore={ ( row ) => void handleRestore( row ) }
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

			<AnnouncementFormDialog
				open={ formParam !== null }
				editing={ editing }
				options={ options }
				busy={ busy }
				error={ formError }
				onClose={ () => { setFormParam( null ); setEditing( null ); } }
				onSubmit={ ( payload ) => void handleSubmit( payload ) }
			/>

			<OrgDeleteDialog
				open={ deleting !== null }
				title={ deleting?.status === 'trash' ? __( 'Delete permanently?', 'erp' ) : __( 'Move to trash?', 'erp' ) }
				description={
					deleting
						? deleting.status === 'trash'
							? sprintf( __( '“%s” will be permanently deleted. This cannot be undone.', 'erp' ), deleting.title )
							: sprintf( __( '“%s” will be moved to trash.', 'erp' ), deleting.title )
						: ''
				}
				busy={ busy }
				onConfirm={ () => void handleDelete() }
				onCancel={ () => setDeleting( null ) }
			/>
		</section>
	);
}

export function AnnouncementsPage(): JSX.Element {
	return (
		<CapabilityGate caps={ [ 'erp_view_announcement' ] }>
			<ErrorBoundary>
				<AnnouncementsInner />
			</ErrorBoundary>
		</CapabilityGate>
	);
}
