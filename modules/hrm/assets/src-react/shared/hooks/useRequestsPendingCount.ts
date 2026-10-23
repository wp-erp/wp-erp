/**
 * Total pending requests across every type (leave / asset / reimbursement / …),
 * for the People → Requests nav badge — mirrors the legacy Requests count badge.
 * Sourced from `GET /erp/v2/requests/counts` (`pending_total`). Cached
 * module-level so the topbar fetches it once regardless of how many dropdowns
 * mount.
 *
 * The cache is on the *promise*, not just the resolved value: every `NavDropdown`
 * mounts in the same tick, so a value-only cache is still empty when each of them
 * runs its effect and they all fire their own request — 7 identical calls per page
 * load, each fanning out to three table aggregations server-side.
 *
 * Anything that changes a request's status (approve, reject, delete, file a new
 * one) calls `refreshRequestsPendingCount()`, which drops the cache and pushes
 * the fresh total to every mounted badge.
 */

import { useEffect, useState } from 'react';

import { request, restPath } from '@/shared/utils/apiFetch';

let cached: number | null = null;
let inflight: Promise< number > | null = null;
const listeners = new Set< ( total: number ) => void >();

function fetchPendingCount(): Promise< number > {
	if ( inflight === null ) {
		inflight = request< { pending_total?: number } >( restPath( 'v2', '/requests/counts' ) )
			.then( ( res ) => {
				cached = res.pending_total ?? 0;

				return cached;
			} )
			.catch( () => {
				// Clear it so a later mount can retry rather than being stuck on
				// one failed response for the life of the page.
				inflight = null;

				return 0;
			} );
	}

	return inflight;
}

/**
 * Drop the cached total and refetch it for every mounted badge. Call after any
 * change to a request's status; safe to call when no badge is mounted (the next
 * mount then fetches).
 */
export function refreshRequestsPendingCount(): Promise< number > {
	cached   = null;
	inflight = null;

	if ( listeners.size === 0 ) {
		return Promise.resolve( 0 );
	}

	return fetchPendingCount().then( ( total ) => {
		listeners.forEach( ( listener ) => listener( total ) );

		return total;
	} );
}

export function useRequestsPendingCount(): number {
	const [ count, setCount ] = useState< number >( cached ?? 0 );

	useEffect( () => {
		// No AbortController: the request is shared, so one dropdown unmounting
		// must not cancel it for the others. The flag drops the late setState.
		let alive = true;
		const listener = ( total: number ): void => {
			if ( alive ) {
				setCount( total );
			}
		};
		listeners.add( listener );

		if ( cached !== null ) {
			setCount( cached );
		} else {
			void fetchPendingCount().then( listener );
		}

		return () => {
			alive = false;
			listeners.delete( listener );
		};
	}, [] );

	return count;
}
