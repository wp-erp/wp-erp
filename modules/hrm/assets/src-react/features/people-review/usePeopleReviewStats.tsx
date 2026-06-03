/**
 * Derive KPI stats for the People Review hero strip.
 *
 * Pulls from `/erp/v2/employees/counts` (filter-aware) via the shared
 * employees store — same source the legacy StatusFilter tabs use.
 */

import { useSelect } from '@wordpress/data';

import { storeName as employeesStoreName, toCountsQuery } from '@/stores/employees';
import type {
	EmployeeCountsQuery,
	EmployeeListQuery,
	EmployeeStatusCounts,
} from '@/stores/employees';

interface EmployeesStoreSelectors {
	getFilters: () => EmployeeListQuery;
	getCounts:  ( query: EmployeeCountsQuery ) => EmployeeStatusCounts | null;
}

export interface PeopleReviewStats {
	readonly total:      number | null;
	readonly active:     number | null;
	readonly inactive:   number | null;
	readonly terminated: number | null;
}

export function usePeopleReviewStats(): PeopleReviewStats {
	return useSelect( ( select ) => {
		const store   = select( employeesStoreName ) as unknown as EmployeesStoreSelectors;
		const filters = store.getFilters();
		const counts  = store.getCounts( toCountsQuery( filters ) );

		if ( ! counts ) {
			return { total: null, active: null, inactive: null, terminated: null };
		}

		return {
			total:      counts.all,
			active:     counts.by_status.active     ?? 0,
			inactive:   counts.by_status.inactive   ?? 0,
			terminated: counts.by_status.terminated ?? 0,
		};
	}, [] );
}
