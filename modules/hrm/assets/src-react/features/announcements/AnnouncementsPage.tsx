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
import { Plus } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';
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

	const { rows, total, counts, loading, error, getOne, save, remove, restore, loadOptions } = useAnnouncements( {
		status,
		search,
		page,
		perPage,
	} );

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
	}, [ status, search ] );

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
				/>

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
