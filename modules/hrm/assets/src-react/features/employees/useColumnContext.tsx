/**
 * Shared `ColumnContext` builder used by every Employees extension hook.
 *
 * Wraps `erp-hr/me.hasCap` in a stable function reference so memoization in
 * downstream hooks stays cheap.
 */

import { useSelect } from '@wordpress/data';
import { useCallback, useMemo } from 'react';

import { useBoot } from '@/shared/hooks/useBoot';
import { useDir } from '@/shared/hooks/useDir';
import { storeName as meStoreName } from '@/stores/me';
import type { ColumnContext } from '@/stores/employees';
import type { Capability } from '@/types/global';

interface MeStoreSelectors {
	hasCap: ( capability: Capability | readonly Capability[] ) => boolean;
}

export interface UseColumnContextResult {
	readonly ctx: ColumnContext;
	readonly can: ( capability: Capability ) => boolean;
}

export function useColumnContext(): UseColumnContextResult {
	const boot = useBoot();
	const dir  = useDir();

	const hasCap = useSelect(
		( select ) => ( select( meStoreName ) as unknown as MeStoreSelectors ).hasCap,
		[]
	);

	const can = useCallback(
		( capability: Capability ) => hasCap( capability ),
		[ hasCap ]
	);

	const ctx = useMemo< ColumnContext >(
		() => ( {
			can,
			hasPro: boot.isPro,
			locale: boot.locale,
			dir,
		} ),
		[ can, boot.isPro, boot.locale, dir ]
	);

	return { ctx, can };
}
