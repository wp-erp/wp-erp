/**
 * Top-bar announcement bell for employees (legacy HR Frontend
 * `AnnouncementDropdown`): a dot while anything addressed to them is unread,
 * and a dropdown of the latest five with View all / Show less and a link to
 * the full list. Managers manage announcements instead, so it hides for them.
 *
 * Reads `GET /erp/v2/me/announcements`, the caller's own inbox only.
 */

import { Button, Popover, PopoverContent, PopoverTrigger } from '@wedevs/plugin-ui';
import { Bell } from 'lucide-react';
import { useState } from 'react';
import type { JSX } from 'react';
import { Link } from 'react-router-dom';

import { fmt } from '@/features/announcements/announcements-format';
import { useMyAnnouncements } from '@/features/announcements/useMyAnnouncements';
import { useCan } from '@/shared/hooks/useCan';
import { __, sprintf } from '@/shared/i18n';

/** Rows the closed list shows; legacy showed five before "View all". */
const PREVIEW = 5;

function BellMenu(): JSX.Element {
	const [ open, setOpen ]       = useState( false );
	const [ showAll, setShowAll ] = useState( false );
	const { rows, unread }        = useMyAnnouncements( { page: 1, perPage: 20 } );

	const shown = showAll ? rows : rows.slice( 0, PREVIEW );
	const label = unread > 0
		/* translators: %d: number of unread announcements */
		? sprintf( __( 'Announcements, %d unread', 'erp' ), unread )
		: __( 'Announcements', 'erp' );

	return (
		<Popover open={ open } onOpenChange={ ( next: boolean ) => { setOpen( next ); if ( ! next ) { setShowAll( false ); } } }>
			<PopoverTrigger
				render={
					<Button
						variant="ghost"
						size="icon"
						aria-label={ label }
						title={ label }
						className="relative size-9 rounded-md text-muted-foreground hover:bg-muted hover:text-foreground"
					>
						<Bell size={ 16 } strokeWidth={ 1.75 } aria-hidden="true" />
						{ unread > 0 ? (
							<span aria-hidden="true" className="absolute right-2 top-2 size-2 rounded-full bg-destructive ring-2 ring-card" />
						) : null }
					</Button>
				}
			/>
			<PopoverContent align="end" sideOffset={ 8 } className="w-80 max-w-[calc(100vw-32px)] rounded-xl border border-border bg-popover p-0 text-popover-foreground shadow-lg">
				<div className="flex items-center justify-between gap-2 border-b border-border px-4 py-3">
					<span className="text-sm font-semibold text-foreground">{ __( 'Announcements', 'erp' ) }</span>
					<Link
						to="/announcements"
						onClick={ () => setOpen( false ) }
						className="text-xs font-medium text-primary hover:underline"
					>
						{ __( 'Open list', 'erp' ) }
					</Link>
				</div>

				{ rows.length === 0 ? (
					<p className="m-0 px-4 py-6 text-center text-sm text-muted-foreground">{ __( 'No announcements yet.', 'erp' ) }</p>
				) : (
					<ul className="m-0 max-h-96 list-none overflow-y-auto p-0">
						{ shown.map( ( row ) => (
							<li key={ row.id } className="border-b border-border last:border-b-0">
								<Link
									to={ `/announcements?view=${ row.id }` }
									onClick={ () => setOpen( false ) }
									className="flex items-start gap-2.5 px-4 py-3 text-left no-underline transition-colors hover:bg-muted/60"
								>
									<span
										aria-hidden="true"
										className={ `mt-1.5 size-2 shrink-0 rounded-full ${ row.read ? 'bg-transparent' : 'bg-primary' }` }
									/>
									<span className="min-w-0 flex-1">
										<span className={ `block truncate text-sm text-foreground ${ row.read ? 'font-medium' : 'font-semibold' }` }>
											{ row.title }
											{ row.read ? null : <span className="sr-only">{ __( '(unread)', 'erp' ) }</span> }
										</span>
										{ row.excerpt ? (
											<span className="mt-0.5 line-clamp-2 block text-xs text-muted-foreground">{ row.excerpt }</span>
										) : null }
										<span className="mt-1 block text-[11px] text-muted-foreground">{ fmt( row.date ) }</span>
									</span>
								</Link>
							</li>
						) ) }
					</ul>
				) }

				{ rows.length > PREVIEW ? (
					<div className="border-t border-border px-4 py-2 text-center">
						<button
							type="button"
							onClick={ () => setShowAll( ( prev ) => ! prev ) }
							className="text-xs font-medium text-primary hover:underline"
						>
							{ showAll ? __( 'Show less', 'erp' ) : __( 'View all', 'erp' ) }
						</button>
					</div>
				) : null }
			</PopoverContent>
		</Popover>
	);
}

/** Employees only: managers (`erp_view_announcement`) have the management page. */
export function AnnouncementBell(): JSX.Element | null {
	const isManager  = useCan( 'erp_view_announcement' );
	const isEmployee = useCan( 'erp_list_employee' );

	if ( isManager || ! isEmployee ) {
		return null;
	}

	return <BellMenu />;
}
