/**
 * Nav layout hook + setter.
 *
 * Reads + writes the user's preferred shell nav layout (`'topbar' | 'sidebar'`).
 * Writes go through `erp-hr/me.updatePreferences` which optimistically updates
 * local state and POSTs to `/erp/v2/me/preferences` (stub-tolerant in dev).
 *
 * Both the horizontal TopBar and the vertical Sidebar consume the SAME
 * `nav-items` data + filtering; this hook only decides which chrome renders.
 */

import { useCallback } from 'react';
import { useDispatch, useSelect } from '@wordpress/data';

import { storeName as meStoreName } from '@/stores/me';
import type { NavLayout } from '@/types/global';

interface MeStoreSelectors {
	getNavLayout: () => NavLayout;
}

interface MeStoreDispatch {
	updatePreferences: ( patch: { erp_hr_nav_layout: NavLayout } ) => Promise< void > | void;
}

export interface UseNavLayoutResult {
	readonly layout:    NavLayout;
	readonly setLayout: ( next: NavLayout ) => void;
	readonly toggle:    () => void;
}

export function useNavLayout(): UseNavLayoutResult {
	const layout = useSelect( ( select ) => {
		const store = select( meStoreName ) as unknown as MeStoreSelectors;
		return store.getNavLayout();
	}, [] );

	const { updatePreferences } = useDispatch( meStoreName ) as MeStoreDispatch;

	const setLayout = useCallback(
		( next: NavLayout ) => {
			if ( next === layout ) {
				return;
			}
			void updatePreferences( { erp_hr_nav_layout: next } );
		},
		[ layout, updatePreferences ]
	);

	const toggle = useCallback(
		() => setLayout( layout === 'sidebar' ? 'topbar' : 'sidebar' ),
		[ layout, setLayout ]
	);

	return { layout, setLayout, toggle };
}
