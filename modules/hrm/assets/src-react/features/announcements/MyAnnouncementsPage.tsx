/**
 * `/announcements` for an employee: a read-only list of the announcements
 * addressed to them (legacy HR Frontend Announcements page, employee view).
 *
 * Each row shows the title, a two-line excerpt, the author, the date and an
 * unread marker. Opening one shows the shared read-only view dialog and marks
 * it read. Managers get the management page instead (`AnnouncementsPage`).
 */

import { toast } from '@wedevs/plugin-ui';
import { CalendarDays, Megaphone, UserRound } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { JSX } from 'react';

import { EmptyState } from '@/shared/components/EmptyState';
import { PlainButton } from '@/shared/components/PlainButton';
import { TableSkeleton } from '@/shared/components/TableSkeleton';
import { __ } from '@/shared/i18n';
import { useModalParam } from '@/shared/useModalParam';
import type { ApiError } from '@/shared/utils/apiFetch';
import { request, restPath } from '@/shared/utils/apiFetch';

import { OrgPagination } from '../org/OrgPagination';
import { AnnouncementViewDialog } from './AnnouncementViewDialog';
import { fmt } from './announcements-format';
import type { AnnouncementDetail } from './types';
import { markMyAnnouncementRead, useMyAnnouncements } from './useMyAnnouncements';

export function MyAnnouncementsPage(): JSX.Element {
	const [ page, setPage ]       = useState( 1 );
	const [ perPage, setPerPage ] = useState( 20 );

	const { rows, total, loading, error } = useMyAnnouncements( { page, perPage } );

	// `?view=<id>` keeps the open announcement across a refresh, and lets the
	// top-bar bell deep-link to one.
	const [ viewParam, setViewParam ] = useModalParam( 'view' );
	const [ viewing, setViewing ]     = useState< AnnouncementDetail | null >( null );

	useEffect( () => {
		const id = Number( viewParam );
		if ( ! id || viewing?.id === id ) {
			return;
		}
		let active = true;
		void markMyAnnouncementRead( id ).catch( () => undefined );
		void request< AnnouncementDetail >( restPath( 'v2', `/announcements/${ id }` ) )
			.then( ( full ) => { if ( active ) { setViewing( full ); } } )
			.catch( ( raw ) => {
				if ( active ) {
					toast.error( ( raw as ApiError )?.message ?? __( 'Could not load the announcement.', 'erp' ) );
					setViewParam( null );
				}
			} );
		return () => { active = false; };
	}, [ viewParam, viewing, setViewParam ] );

	const totalPages = Math.max( 1, Math.ceil( total / perPage ) );

	return (
		<section className="mx-auto w-full max-w-full">
			<header className="mb-6 flex flex-wrap items-center justify-between gap-4">
				<h1 className="text-2xl font-bold leading-8 text-foreground">
					{ __( 'Announcements', 'erp' ) }
				</h1>
			</header>

			{ loading && rows.length === 0 ? (
				<TableSkeleton />
			) : error ? (
				<p role="alert" className="rounded-[10px] bg-card p-6 text-sm text-destructive shadow-sm">{ error }</p>
			) : rows.length === 0 ? (
				<div className="rounded-[10px] bg-card shadow-sm">
					<EmptyState
						icon={ Megaphone }
						title={ __( 'No announcements yet.', 'erp' ) }
						description={ __( 'Announcements sent to you will show up here.', 'erp' ) }
					/>
				</div>
			) : (
				<div className="overflow-hidden rounded-[10px] bg-card shadow-sm">
					<ul className="m-0 list-none p-0">
						{ rows.map( ( row ) => (
							<li key={ row.id } className="border-b border-border last:border-b-0">
								<PlainButton
									onClick={ () => { setViewing( null ); setViewParam( String( row.id ) ); } }
									className="flex w-full items-start gap-3 px-5 py-4 text-left transition-colors hover:bg-muted/50"
								>
									<span
										aria-hidden="true"
										className={ `mt-2 size-2 shrink-0 rounded-full ${ row.read ? 'bg-transparent' : 'bg-primary' }` }
									/>
									<span className="min-w-0 flex-1">
										<span className={ `block truncate text-base text-foreground ${ row.read ? 'font-medium' : 'font-semibold' }` }>
											{ row.title }
											{ row.read ? null : <span className="sr-only">{ __( '(unread)', 'erp' ) }</span> }
										</span>
										{ row.excerpt ? (
											<span className="mt-1 line-clamp-2 block text-sm text-muted-foreground">{ row.excerpt }</span>
										) : null }
										<span className="mt-2 flex flex-wrap items-center gap-4 text-xs text-muted-foreground">
											{ row.author ? (
												<span className="inline-flex items-center gap-1">
													<UserRound size={ 12 } aria-hidden="true" />
													{ row.author }
												</span>
											) : null }
											<span className="inline-flex items-center gap-1">
												<CalendarDays size={ 12 } aria-hidden="true" />
												{ fmt( row.date ) }
											</span>
										</span>
									</span>
								</PlainButton>
							</li>
						) ) }
					</ul>
					<OrgPagination
						page={ page }
						totalPages={ totalPages }
						total={ total }
						perPage={ perPage }
						onPage={ setPage }
						onPerPage={ ( next ) => { setPerPage( next ); setPage( 1 ); } }
					/>
				</div>
			) }

			<AnnouncementViewDialog
				open={ viewParam !== null }
				loading={ viewing === null }
				item={ viewing }
				canManage={ false }
				showAudience={ false }
				onEdit={ () => undefined }
				onClose={ () => { setViewParam( null ); setViewing( null ); } }
			/>
		</section>
	);
}
