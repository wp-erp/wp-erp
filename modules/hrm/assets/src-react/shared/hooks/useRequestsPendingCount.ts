/**
 * Total pending requests across every type (leave / asset / reimbursement / …),
 * for the People → Requests nav badge — mirrors the legacy Requests count badge.
 * Sourced from `GET /erp/v2/requests/counts` (`pending_total`). Cached
 * module-level so the topbar fetches it once regardless of how many dropdowns
 * mount.
 */

import { useEffect, useState } from 'react';

import { request, restPath } from '@/shared/utils/apiFetch';

let cached: number | null = null;

export function useRequestsPendingCount(): number {
	const [ count, setCount ] = useState< number >( cached ?? 0 );

	useEffect( () => {
		if ( cached !== null ) {
			setCount( cached );
			return;
		}
		const ctrl = new AbortController();
		request< { pending_total?: number } >( restPath( 'v2', '/requests/counts' ), { signal: ctrl.signal } )
			.then( ( res ) => {
				cached = res.pending_total ?? 0;
				setCount( cached );
			} )
			.catch( () => undefined );
		return () => ctrl.abort();
	}, [] );

	return count;
}
