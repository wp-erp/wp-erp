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
 */

import { useEffect, useState } from 'react';

import { request, restPath } from '@/shared/utils/apiFetch';

let cached: number | null = null;
let inflight: Promise< number > | null = null;

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

export function useRequestsPendingCount(): number {
	const [ count, setCount ] = useState< number >( cached ?? 0 );

	useEffect( () => {
		if ( cached !== null ) {
			setCount( cached );

			return;
		}

		// No AbortController: the request is shared, so one dropdown unmounting
		// must not cancel it for the others. The flag drops the late setState.
		let alive = true;
		void fetchPendingCount().then( ( total ) => {
			if ( alive ) {
				setCount( total );
			}
		} );

		return () => {
			alive = false;
		};
	}, [] );

	return count;
}
