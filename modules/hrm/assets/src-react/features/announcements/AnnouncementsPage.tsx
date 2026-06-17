/**
 * `/announcements` route — company announcement management.
 *
 * Employees-table conventions: status tabs (Published / Draft / Trash) with
 * counts + inline search, the shared `OrgPagination` footer, per-row actions.
 * Create + edit happen in a dialog; trash / restore / permanent-delete mirror
 * the legacy bulk handlers.
 */

import {
	Button,
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuItem,
	DropdownMenuTrigger,
	Input,
	toast,
} from '@wedevs/plugin-ui';
import { MoreVertical, Pencil, Plus, RotateCcw, Search, Trash2 } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';
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
import { AnnouncementFormDialog } from './AnnouncementFormDialog';
import type { Announcement, AnnouncementDetail, AnnouncementFormOptions, AnnouncementInput } from './types';
import { useAnnouncements } from './useAnnouncements';

const STATUS_TABS: ReadonlyArray< { value: string; label: string } > = [
	{ value: 'publish', label: __( 'Published', 'erp' ) },
	{ value: 'draft', label: __( 'Draft', 'erp' ) },
	{ value: 'trash', label: __( 'Trash', 'erp' ) },
];

const SEARCH_DEBOUNCE_MS = 350;

function fmt( value: string | null ): string {
	if ( ! value ) {
		return '—';
	}
	const d = new Date( value );
	return Number.isNaN( d.getTime() ) ? value.slice( 0, 10 ) : d.toLocaleDateString( undefined, { year: 'numeric', month: 'short', day: 'numeric' } );
}

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
		<section className="mx-auto w-full max-w-7xl">
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
				<div className="flex flex-wrap items-center justify-between gap-3 border-b border-border px-4 pt-3 pb-2">
					<div role="tablist" aria-label={ __( 'Announcement status', 'erp' ) } className="flex items-stretch">
						{ STATUS_TABS.map( ( tab ) => {
							const selected = status === tab.value;
							return (
								<button
									key={ tab.value }
									role="tab"
									type="button"
									aria-selected={ selected }
									onClick={ () => setStatus( tab.value ) }
									className={ [
										'relative inline-flex h-11 items-center gap-1.5 px-4 text-sm font-medium',
										selected ? 'text-primary' : 'text-muted-foreground hover:text-foreground',
									].join( ' ' ) }
								>
									<span>{ tab.label }</span>
									<span className="font-normal text-muted-foreground">({ countFor( tab.value ) })</span>
									{ selected ? (
										<span aria-hidden="true" className="absolute inset-x-0 -bottom-2 h-0.5 bg-primary" />
									) : null }
								</button>
							);
						} ) }
					</div>
					<div className="relative">
						<Search
							size={ 16 }
							aria-hidden="true"
							className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"
						/>
						<Input
							type="search"
							value={ searchInput }
							onChange={ ( e ) => setSearchInput( e.target.value ) }
							placeholder={ __( 'Search', 'erp' ) }
							className="h-9 w-60 rounded-md border-border pl-9 text-sm"
							aria-label={ __( 'Search announcements', 'erp' ) }
						/>
					</div>
				</div>

				{ error ? (
					<p className="p-6 text-sm text-destructive">{ error }</p>
				) : loading ? (
					<TableSkeleton rows={ 6 } />
				) : rows.length === 0 ? (
					<p className="p-10 text-center text-sm text-muted-foreground">
						{ search ? __( 'No announcements match your search.', 'erp' ) : __( 'No announcements here yet.', 'erp' ) }
					</p>
				) : (
					<div className="overflow-x-auto">
						<table className="w-full min-w-[40rem] text-left">
						<thead className="border-b border-border bg-muted/40">
							<tr className="h-10 text-xs font-medium uppercase tracking-normal text-muted-foreground">
								<th scope="col" className="px-4">{ __( 'Title', 'erp' ) }</th>
								<th scope="col" className="whitespace-nowrap px-2">{ __( 'Recipients', 'erp' ) }</th>
								<th scope="col" className="whitespace-nowrap px-2">{ __( 'Author', 'erp' ) }</th>
								<th scope="col" className="whitespace-nowrap px-2">{ __( 'Date', 'erp' ) }</th>
								<th scope="col" className="w-20 px-4">
									<span className="sr-only">{ __( 'Actions', 'erp' ) }</span>
								</th>
							</tr>
						</thead>
						<tbody>
							{ rows.map( ( row ) => (
								<tr key={ row.id } className="h-18 border-b border-border last:border-b-0 hover:bg-muted/40">
									<td className="max-w-md px-4 align-middle">
										<div className="truncate font-medium text-foreground">{ row.title || __( '(no title)', 'erp' ) }</div>
										{ row.excerpt ? (
											<div className="truncate text-xs text-muted-foreground">{ row.excerpt }</div>
										) : null }
									</td>
									<td className="px-2 align-middle text-sm text-muted-foreground">
										<EmployeeAvatarStack people={ row.recipients_preview } total={ row.recipient_count } />
									</td>
									<td className="whitespace-nowrap px-2 align-middle text-sm text-muted-foreground">{ row.author || '—' }</td>
									<td className="whitespace-nowrap px-2 align-middle text-sm text-muted-foreground">{ fmt( row.date ) }</td>
									<td className="px-4 align-middle">
										{ canManage ? (
											<div className="flex justify-end">
												<DropdownMenu>
													<DropdownMenuTrigger
														render={
															<Button variant="ghost" size="icon" aria-label={ sprintf( __( 'Actions for %s', 'erp' ), row.title ) }>
																<MoreVertical size={ 16 } aria-hidden="true" />
															</Button>
														}
													/>
													<DropdownMenuContent align="end" className="min-w-44">
														{ row.status === 'trash' ? (
															<DropdownMenuItem className="gap-2" onClick={ () => void handleRestore( row ) }>
																<RotateCcw size={ 14 } aria-hidden="true" />
																{ __( 'Restore', 'erp' ) }
															</DropdownMenuItem>
														) : (
															<DropdownMenuItem className="gap-2" onClick={ () => void openEdit( row ) }>
																<Pencil size={ 14 } aria-hidden="true" />
																{ __( 'Edit', 'erp' ) }
															</DropdownMenuItem>
														) }
														<DropdownMenuItem
															variant="destructive"
															className="gap-2"
															onClick={ () => setDeleting( row ) }
														>
															<Trash2 size={ 14 } aria-hidden="true" />
															{ row.status === 'trash' ? __( 'Delete permanently', 'erp' ) : __( 'Trash', 'erp' ) }
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
