/**
 * The current employee's own announcements (`GET /erp/v2/me/announcements`):
 * only what was addressed to them, newest first, with a per-user read flag.
 *
 * Used by the employee Announcements page and the top-bar bell. Reading an
 * announcement anywhere fires `ANNOUNCEMENT_READ_EVENT`, so every open copy of
 * the list (page, bell, dashboard) drops its unread marker without a refetch.
 */

import { useCallback, useEffect, useRef, useState } from 'react';

import { __ } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';
import { request, requestWithHeaders, restPath } from '@/shared/utils/apiFetch';
import { toInt } from '@/shared/utils/coerce';

import type { MyAnnouncement } from './types';

/** Window event fired with `{ id }` once an announcement was marked read. */
export const ANNOUNCEMENT_READ_EVENT = 'erp-hr:announcement-read';

/**
 * Mark one announcement read for the current user and tell every listener.
 * The server only touches the caller's own recipient row.
 */
export async function markMyAnnouncementRead( id: number ): Promise< void > {
	try {
		await request( restPath( 'v2', `/announcements/${ id }/mark-read` ), { method: 'POST' } );
	} finally {
		// After the write, so a listener that refetches reads the new state.
		window.dispatchEvent( new CustomEvent( ANNOUNCEMENT_READ_EVENT, { detail: { id } } ) );
	}
}

interface UseMyAnnouncementsArgs {
	readonly page:     number;
	readonly perPage:  number;
	/** Skip the request entirely (e.g. a manager, who has no inbox). */
	readonly enabled?: boolean;
}

export interface UseMyAnnouncementsResult {
	readonly rows:    readonly MyAnnouncement[];
	readonly total:   number;
	readonly unread:  number;
	readonly loading: boolean;
	readonly error:   string | null;
	readonly reload:  () => Promise< void >;
}

export function useMyAnnouncements( { page, perPage, enabled = true }: UseMyAnnouncementsArgs ): UseMyAnnouncementsResult {
	const [ rows, setRows ]       = useState< readonly MyAnnouncement[] >( [] );
	const [ total, setTotal ]     = useState( 0 );
	const [ unread, setUnread ]   = useState( 0 );
	const [ loading, setLoading ] = useState( enabled );
	const [ error, setError ]     = useState< string | null >( null );

	const reload = useCallback( async (): Promise< void > => {
		if ( ! enabled ) {
			return;
		}
		setLoading( true );
		setError( null );
		try {
			const { body, headers } = await requestWithHeaders< MyAnnouncement[] >(
				restPath( 'v2', '/me/announcements', { page, per_page: perPage } )
			);
			const list = Array.isArray( body ) ? body : [];
			setRows( list );
			setTotal( toInt( headers.get( 'X-WP-Total' ), list.length ) );
			setUnread( toInt( headers.get( 'X-ERP-Unread' ), list.filter( ( r ) => ! r.read ).length ) );
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? __( 'Could not load announcements.', 'erp' ) );
		} finally {
			setLoading( false );
		}
	}, [ page, perPage, enabled ] );

	useEffect( () => {
		void reload();
	}, [ reload ] );

	// Another view marked one read: flip it here too and lower the count. One
	// that is not on this page still counted as unread, so ask again.
	const rowsRef   = useRef( rows );
	const reloadRef = useRef( reload );
	rowsRef.current   = rows;
	reloadRef.current = reload;

	useEffect( () => {
		function onRead( event: Event ): void {
			const id = Number( ( event as CustomEvent< { id?: number } > ).detail?.id ?? 0 );
			if ( ! id ) {
				return;
			}
			const row = rowsRef.current.find( ( r ) => r.id === id );
			if ( ! row ) {
				void reloadRef.current();
				return;
			}
			if ( row.read ) {
				return;
			}
			setRows( ( prev ) => prev.map( ( r ) => ( r.id === id ? { ...r, read: true } : r ) ) );
			setUnread( ( n ) => Math.max( 0, n - 1 ) );
		}
		window.addEventListener( ANNOUNCEMENT_READ_EVENT, onRead );
		return () => window.removeEventListener( ANNOUNCEMENT_READ_EVENT, onRead );
	}, [] );

	return { rows, total, unread, loading, error, reload };
}
