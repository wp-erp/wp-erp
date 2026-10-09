/**
 * Capability gate hook.
 *
 * Reads the `erp-hr/me` store. Returns true when the current user has every
 * requested capability. While the store is still resolving, returns
 * permissively per critical-parts.md §24 — the server always re-checks
 * destructive actions.
 */

import { useSelect } from '@wordpress/data';

import type { Capability } from '@/types/global';
import { storeName } from '@/stores/me';

interface MeStoreSelectors {
	hasCap: ( capability: Capability | readonly Capability[] ) => boolean;
}

export function useCan(
	capability: Capability | readonly Capability[]
): boolean {
	return useSelect(
		( select ) => {
			const store = select( storeName ) as unknown as MeStoreSelectors;
			return store.hasCap( capability );
		},
		[ Array.isArray( capability ) ? capability.join( '|' ) : capability ]
	);
}
