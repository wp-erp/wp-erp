/**
 * List + CRUD + trash/restore hook for Announcements, plus a status-count
 * loader and a one-shot form-options loader.
 *
 * Reads + writes `erp/v2/announcements`, which delegates (server side) to the
 * `erp_hr_announcement` CPT + `erp_hr_assign_announcements_to_employees()` (the
 * same recipient meta, `erp_hr_announcement` rows, publish-email schedule and
 * hooks as the legacy save_post path) and the legacy trash/restore/count fns.
 */

import { useCallback, useEffect, useState } from 'react';

import type { ApiError } from '@/shared/utils/apiFetch';
import { request, requestWithHeaders, restPath } from '@/shared/utils/apiFetch';
import { toInt } from '@/shared/utils/coerce';

import type {
	Announcement,
	AnnouncementDetail,
	AnnouncementFormOptions,
	AnnouncementInput,
	AnnouncementStatusCounts,
	IdName,
} from './types';

interface RawFormOptions {
	assign_types?: Array< { value: string; label: string } >;
	departments?:  IdName[];
	designations?: IdName[];
	employees?:    IdName[];
}

interface UseAnnouncementsArgs {
	readonly status:  string;
	readonly search:  string;
	readonly page:    number;
	readonly perPage: number;
}

export interface UseAnnouncementsResult {
	readonly rows:        readonly Announcement[];
	readonly total:       number;
	readonly counts:      AnnouncementStatusCounts;
	readonly loading:     boolean;
	readonly error:       string | null;
	readonly reload:      () => Promise< void >;
	readonly getOne:      ( id: number ) => Promise< AnnouncementDetail >;
	readonly save:        ( id: number | null, payload: AnnouncementInput ) => Promise< void >;
	readonly remove:      ( id: number, force: boolean ) => Promise< void >;
	readonly restore:     ( id: number ) => Promise< void >;
	readonly loadOptions: () => Promise< AnnouncementFormOptions >;
}

const EMPTY_COUNTS: AnnouncementStatusCounts = { publish: 0, draft: 0, trash: 0 };

export function useAnnouncements( { status, search, page, perPage }: UseAnnouncementsArgs ): UseAnnouncementsResult {
	const [ rows, setRows ]       = useState< readonly Announcement[] >( [] );
	const [ total, setTotal ]     = useState( 0 );
	const [ counts, setCounts ]   = useState< AnnouncementStatusCounts >( EMPTY_COUNTS );
	const [ loading, setLoading ] = useState( true );
	const [ error, setError ]     = useState< string | null >( null );

	const reload = useCallback( async (): Promise< void > => {
		setLoading( true );
		setError( null );
		try {
			const { body, headers } = await requestWithHeaders< Announcement[] >(
				restPath( 'v2', '/announcements', { status, search, page, per_page: perPage } )
			);
			const list = Array.isArray( body ) ? body : [];
			setRows( list );
			setTotal( toInt( headers.get( 'X-WP-Total' ), list.length ) );

			try {
				const c = await request< AnnouncementStatusCounts >( restPath( 'v2', '/announcements/status-counts' ) );
				setCounts( {
					publish: Number( c?.publish ?? 0 ),
					draft:   Number( c?.draft ?? 0 ),
					trash:   Number( c?.trash ?? 0 ),
				} );
			} catch {
				setCounts( EMPTY_COUNTS );
			}
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? 'Could not load announcements.' );
		} finally {
			setLoading( false );
		}
	}, [ status, search, page, perPage ] );

	useEffect( () => {
		void reload();
	}, [ reload ] );

	const getOne = useCallback( async ( id: number ): Promise< AnnouncementDetail > => {
		return request< AnnouncementDetail >( restPath( 'v2', `/announcements/${ id }` ) );
	}, [] );

	const save = useCallback(
		async ( id: number | null, payload: AnnouncementInput ): Promise< void > => {
			if ( id ) {
				await request( restPath( 'v2', `/announcements/${ id }` ), { method: 'PUT', data: payload } );
			} else {
				await request( restPath( 'v2', '/announcements' ), { method: 'POST', data: payload } );
			}
			await reload();
		},
		[ reload ]
	);

	const remove = useCallback(
		async ( id: number, force: boolean ): Promise< void > => {
			await request( restPath( 'v2', `/announcements/${ id }`, { force } ), { method: 'DELETE' } );
			await reload();
		},
		[ reload ]
	);

	const restore = useCallback(
		async ( id: number ): Promise< void > => {
			await request( restPath( 'v2', `/announcements/${ id }/restore` ), { method: 'POST' } );
			await reload();
		},
		[ reload ]
	);

	const loadOptions = useCallback( async (): Promise< AnnouncementFormOptions > => {
		const raw = await request< RawFormOptions >( restPath( 'v2', '/announcements/form-options' ) );
		return {
			assignTypes:  raw.assign_types ?? [],
			departments:  raw.departments ?? [],
			designations: raw.designations ?? [],
			employees:    raw.employees ?? [],
		};
	}, [] );

	return { rows, total, counts, loading, error, reload, getOne, save, remove, restore, loadOptions };
}
