/**
 * Resolvers for the `erp-hr/employees` store.
 *
 * Auto-fired the first time the matching selector is accessed for a given
 * query-key (resolver tracking is keyed on serialized args by @wordpress/data).
 *
 * Written as thunks (async functions returning a thunk) — the same pattern as
 * `fetchEmployees` in actions.ts. The store-bound `dispatch` resolves the
 * registered action creator so we get fully-typed cross-action calls.
 */

import type { EmployeeCountsQuery, EmployeeListQuery } from './types';

interface ResolverThunkContext {
	readonly dispatch: {
		fetchEmployees: ( query: EmployeeListQuery )  => Promise< void >;
		fetchCounts:    ( query: EmployeeCountsQuery ) => Promise< void >;
	};
}

export const getEmployees =
	( query: EmployeeListQuery ) =>
	async ( { dispatch }: ResolverThunkContext ): Promise< void > => {
		await dispatch.fetchEmployees( query );
	};

export const getCounts =
	( query: EmployeeCountsQuery ) =>
	async ( { dispatch }: ResolverThunkContext ): Promise< void > => {
		await dispatch.fetchCounts( query );
	};
