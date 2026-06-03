/**
 * List + CRUD + import hook for Holidays.
 *
 * Reads + writes `erp/v2/holidays`, which delegates (server side) to the
 * unchanged v1 model layer (`erp_hr_leave_insert_holiday()`,
 * `erp_hr_get_holidays()`, `erp_hr_delete_holidays()`) so every legacy hook
 * keeps firing.
 *
 * Holidays are date-scoped, so unlike the taxonomy entities this list is
 * fetched server-paginated with a `year` filter rather than loaded whole and
 * paged in the browser.
 */

import apiFetch from '@wordpress/api-fetch';
import { useCallback, useEffect, useState } from 'react';

import type { ApiError } from '@/shared/utils/apiFetch';
import { request, requestWithHeaders, restPath } from '@/shared/utils/apiFetch';
import { toInt } from '@/shared/utils/coerce';

import type {
	Holiday,
	HolidayImportResult,
	HolidayInput,
	HolidayPreviewRow,
} from './types';

interface UseHolidaysArgs {
	readonly year:    number;
	readonly search:  string;
	readonly page:    number;
	readonly perPage: number;
}

export interface UseHolidaysResult {
	readonly rows:    readonly Holiday[];
	readonly total:   number;
	readonly loading: boolean;
	readonly error:   string | null;
	readonly reload:  () => Promise< void >;
	readonly save:    ( id: number | null, payload: HolidayInput ) => Promise< void >;
	readonly remove:  ( id: number ) => Promise< void >;
	readonly parseFile: ( file: File ) => Promise< readonly HolidayPreviewRow[] >;
	readonly importRows: ( rows: readonly HolidayPreviewRow[] ) => Promise< HolidayImportResult >;
}

export function useHolidays( { year, search, page, perPage }: UseHolidaysArgs ): UseHolidaysResult {
	const [ rows, setRows ]       = useState< readonly Holiday[] >( [] );
	const [ total, setTotal ]     = useState( 0 );
	const [ loading, setLoading ] = useState( true );
	const [ error, setError ]     = useState< string | null >( null );

	const reload = useCallback( async (): Promise< void > => {
		setLoading( true );
		setError( null );
		try {
			const { body, headers } = await requestWithHeaders< Holiday[] >(
				restPath( 'v2', '/holidays', {
					year,
					search,
					page,
					per_page: perPage,
					orderby:  'start',
					order:    'asc',
				} )
			);
			const list = Array.isArray( body ) ? body : [];
			setRows( list );
			setTotal( toInt( headers.get( 'X-WP-Total' ), list.length ) );
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? 'Could not load holidays.' );
		} finally {
			setLoading( false );
		}
	}, [ year, search, page, perPage ] );

	useEffect( () => {
		void reload();
	}, [ reload ] );

	const save = useCallback(
		async ( id: number | null, payload: HolidayInput ): Promise< void > => {
			if ( id ) {
				await request( restPath( 'v2', `/holidays/${ id }` ), { method: 'PUT', data: payload } );
			} else {
				await request( restPath( 'v2', '/holidays' ), { method: 'POST', data: payload } );
			}
			await reload();
		},
		[ reload ]
	);

	const remove = useCallback(
		async ( id: number ): Promise< void > => {
			await request( restPath( 'v2', `/holidays/${ id }` ), { method: 'DELETE' } );
			await reload();
		},
		[ reload ]
	);

	// File upload needs a raw multipart body — apiFetch JSON-stringifies the
	// `data` option, so the FormData goes through `body` directly (same pattern
	// as AvatarUpload). The shared root-URL + nonce middlewares still apply.
	const parseFile = useCallback( async ( file: File ): Promise< readonly HolidayPreviewRow[] > => {
		const fd = new FormData();
		fd.append( 'file', file );
		const res = await apiFetch< { rows: HolidayPreviewRow[] } >( {
			path:   restPath( 'v2', '/holidays/parse' ),
			method: 'POST',
			body:   fd,
		} );
		return Array.isArray( res?.rows ) ? res.rows : [];
	}, [] );

	const importRows = useCallback(
		async ( previewRows: readonly HolidayPreviewRow[] ): Promise< HolidayImportResult > => {
			const res = await request< HolidayImportResult >(
				restPath( 'v2', '/holidays/import' ),
				{ method: 'POST', data: { holidays: previewRows } }
			);
			await reload();
			return res;
		},
		[ reload ]
	);

	return { rows, total, loading, error, reload, save, remove, parseFile, importRows };
}
