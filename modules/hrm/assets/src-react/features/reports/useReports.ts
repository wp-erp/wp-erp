/**
 * Data hooks for the read-only `erp/v2/reports/*` endpoints.
 *
 * `useReport` is the generic GET-once hook used by the four parameterless
 * reports (age / gender / salary / years-of-service). Headcount and leaves take
 * filters, so they get dedicated hooks that re-fetch when the filters change.
 */

import { useCallback, useEffect, useState } from 'react';

import type { ApiError } from '@/shared/utils/apiFetch';
import { request, requestWithHeaders, restPath } from '@/shared/utils/apiFetch';
import { toInt } from '@/shared/utils/coerce';

import type {
	HeadcountResponse,
	LeaveReportFilters,
	LeaveReportFormOptions,
	LeaveReportResponse,
} from './types';

export interface UseReportResult< T > {
	readonly data:    T | null;
	readonly loading: boolean;
	readonly error:   string | null;
	readonly reload:  () => Promise< void >;
}

/** Generic GET-once report hook (no filters). */
export function useReport< T >( path: string ): UseReportResult< T > {
	const [ data, setData ]       = useState< T | null >( null );
	const [ loading, setLoading ] = useState( true );
	const [ error, setError ]     = useState< string | null >( null );

	const reload = useCallback( async (): Promise< void > => {
		setLoading( true );
		setError( null );
		try {
			const body = await request< T >( restPath( 'v2', path ) );
			setData( body );
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? 'Could not load the report.' );
		} finally {
			setLoading( false );
		}
	}, [ path ] );

	useEffect( () => {
		void reload();
	}, [ reload ] );

	return { data, loading, error, reload };
}

/** Headcount report — re-fetches on year / department change. */
export function useHeadcount( year: string, department: number ): UseReportResult< HeadcountResponse > {
	const [ data, setData ]       = useState< HeadcountResponse | null >( null );
	const [ loading, setLoading ] = useState( true );
	const [ error, setError ]     = useState< string | null >( null );

	const reload = useCallback( async (): Promise< void > => {
		setLoading( true );
		setError( null );
		try {
			const query: Record< string, string | number > = {};
			if ( year ) {
				query.year = year;
			}
			if ( department ) {
				query.department = department;
			}
			const body = await request< HeadcountResponse >( restPath( 'v2', '/reports/headcount', query ) );
			setData( body );
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? 'Could not load the headcount report.' );
		} finally {
			setLoading( false );
		}
	}, [ year, department ] );

	useEffect( () => {
		void reload();
	}, [ reload ] );

	return { data, loading, error, reload };
}

export interface UseLeaveReportResult {
	readonly data:    LeaveReportResponse | null;
	readonly total:   number;
	readonly loading: boolean;
	readonly error:   string | null;
	readonly reload:  () => Promise< void >;
	readonly loadOptions: () => Promise< LeaveReportFormOptions >;
}

/** Leaves report — paginated matrix with designation / department / type / year filters. */
export function useLeaveReport( filters: LeaveReportFilters ): UseLeaveReportResult {
	const [ data, setData ]       = useState< LeaveReportResponse | null >( null );
	const [ total, setTotal ]     = useState( 0 );
	const [ loading, setLoading ] = useState( true );
	const [ error, setError ]     = useState< string | null >( null );

	const { filter_year, filter_designation, filter_department, filter_employment_type, start, end, page, perPage } = filters;

	const reload = useCallback( async (): Promise< void > => {
		setLoading( true );
		setError( null );
		try {
			const query: Record< string, string | number > = { page, per_page: perPage };
			if ( filter_year ) {
				query.filter_year = filter_year;
			}
			if ( filter_designation ) {
				query.filter_designation = filter_designation;
			}
			if ( filter_department ) {
				query.filter_department = filter_department;
			}
			if ( filter_employment_type ) {
				query.filter_employment_type = filter_employment_type;
			}
			if ( filter_year === 'custom' && start ) {
				query.start = start;
			}
			if ( filter_year === 'custom' && end ) {
				query.end = end;
			}

			const { body, headers } = await requestWithHeaders< LeaveReportResponse >(
				restPath( 'v2', '/reports/leaves', query )
			);
			setData( body );
			setTotal( toInt( headers.get( 'X-WP-Total' ), body?.rows?.length ?? 0 ) );
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? 'Could not load the leave report.' );
		} finally {
			setLoading( false );
		}
	}, [ filter_year, filter_designation, filter_department, filter_employment_type, start, end, page, perPage ] );

	useEffect( () => {
		void reload();
	}, [ reload ] );

	const loadOptions = useCallback( async (): Promise< LeaveReportFormOptions > => {
		return request< LeaveReportFormOptions >( restPath( 'v2', '/reports/leaves/form-options' ) );
	}, [] );

	return { data, total, loading, error, reload, loadOptions };
}
