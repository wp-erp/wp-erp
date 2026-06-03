/**
 * Fetch merged calendar events for a date range.
 *
 * Reads `erp/v2/leave-calendar`, which mirrors the legacy
 * `get_leave_holiday_by_date()` AJAX handler — leave requests (status ≠
 * rejected) + overlapping holidays + weekend background blocks, all merged.
 */

import { useCallback, useEffect, useState } from 'react';

import type { ApiError } from '@/shared/utils/apiFetch';
import { request, restPath } from '@/shared/utils/apiFetch';

import type { CalendarEvent } from './types';

export interface UseLeaveCalendarResult {
	readonly events:  readonly CalendarEvent[];
	readonly loading: boolean;
	readonly error:   string | null;
	readonly reload:  () => Promise< void >;
}

/**
 * @param start Visible range start (Y-m-d).
 * @param end   Visible range end (Y-m-d).
 */
export function useLeaveCalendar( start: string, end: string ): UseLeaveCalendarResult {
	const [ events, setEvents ]   = useState< readonly CalendarEvent[] >( [] );
	const [ loading, setLoading ] = useState( true );
	const [ error, setError ]     = useState< string | null >( null );

	const reload = useCallback( async (): Promise< void > => {
		setLoading( true );
		setError( null );
		try {
			const res = await request< CalendarEvent[] >( restPath( 'v2', '/leave-calendar', { start, end } ) );
			setEvents( Array.isArray( res ) ? res : [] );
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? 'Could not load the calendar.' );
		} finally {
			setLoading( false );
		}
	}, [ start, end ] );

	useEffect( () => {
		void reload();
	}, [ reload ] );

	return { events, loading, error, reload };
}
