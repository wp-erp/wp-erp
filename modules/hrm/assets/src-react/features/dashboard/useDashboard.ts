/**
 * Loads the aggregate `GET /erp/v2/dashboard` payload once on mount.
 */

import { useCallback, useEffect, useState } from 'react';

import type { ApiError } from '@/shared/utils/apiFetch';
import { request, restPath } from '@/shared/utils/apiFetch';

import type { DashboardData } from './types';

export interface UseDashboardResult {
	readonly data:    DashboardData | null;
	readonly loading: boolean;
	readonly error:   string | null;
	readonly reload:  () => Promise< void >;
}

export interface AnnouncementContent {
	readonly id:      number;
	readonly title:   string;
	readonly content: string;
	readonly date:    string | null;
}

/** Fetch a single announcement's full content (mirrors the legacy view modal). */
export async function fetchAnnouncement( id: number ): Promise< AnnouncementContent > {
	const body = await request< { id: number; title: string; content?: string; html_content?: string; date?: string | null } >(
		restPath( 'v2', `/announcements/${ id }` )
	);
	return {
		id:      body.id,
		title:   body.title,
		content: body.html_content ?? body.content ?? '',
		date:    body.date ?? null,
	};
}

/** Mark an announcement read for the current user. */
export async function markAnnouncementRead( id: number ): Promise< void > {
	await request( restPath( 'v2', `/announcements/${ id }/mark-read` ), { method: 'POST' } );
}

/** Send a birthday wish e-mail to a coworker (mirrors the legacy birthday_wish). */
export async function sendBirthdayWish( employeeUserId: number ): Promise< void > {
	await request( restPath( 'v2', '/dashboard/birthday-wish' ), {
		method: 'POST',
		data:   { employee_user_id: employeeUserId },
	} );
}

export function useDashboard(): UseDashboardResult {
	const [ data, setData ]       = useState< DashboardData | null >( null );
	const [ loading, setLoading ] = useState( true );
	const [ error, setError ]     = useState< string | null >( null );

	const reload = useCallback( async (): Promise< void > => {
		setLoading( true );
		setError( null );
		try {
			const body = await request< DashboardData >( restPath( 'v2', '/dashboard' ) );
			setData( body );
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? 'Could not load the dashboard.' );
		} finally {
			setLoading( false );
		}
	}, [] );

	useEffect( () => {
		void reload();
	}, [ reload ] );

	return { data, loading, error, reload };
}
