/**
 * Theme mode hook + setter.
 *
 * Reads + writes the user's preferred theme mode (`'light' | 'dark' | 'auto'`).
 * Writes go through `erp-hr/me.updatePreferences` which optimistically updates
 * local state and POSTs to `/erp/v2/me/preferences`. The POST endpoint is
 * stub-tolerant (404 swallowed in dev) until it ships.
 *
 * Fires `wp.hooks.doAction('erp_hr.theme.changed', mode, resolved)` after every
 * successful change so pro modules can re-theme their own DOM.
 */

import { useCallback } from 'react';
import { useDispatch, useSelect } from '@wordpress/data';
import { doAction } from '@wordpress/hooks';

import { ACTIONS } from '@/shared/filters';
import { storeName as meStoreName } from '@/stores/me';
import type { ColorScheme, ThemeMode } from '@/types/global';

import { useColorScheme } from './useColorScheme';

interface MeStoreSelectors {
	getResolvedThemeMode: () => ThemeMode;
}

interface MeStoreDispatch {
	updatePreferences: ( patch: { erp_hr_color_scheme: ThemeMode } ) => Promise< void > | void;
}

export interface UseThemeResult {
	readonly mode:     ThemeMode;
	readonly resolved: ColorScheme;
	readonly setMode:  ( next: ThemeMode ) => void;
}

export function useTheme(): UseThemeResult {
	const mode = useSelect( ( select ) => {
		const store = select( meStoreName ) as unknown as MeStoreSelectors;
		return store.getResolvedThemeMode();
	}, [] );

	const resolved = useColorScheme();

	const { updatePreferences } = useDispatch( meStoreName ) as MeStoreDispatch;

	const setMode = useCallback(
		( next: ThemeMode ) => {
			if ( next === mode ) {
				return;
			}
			void updatePreferences( { erp_hr_color_scheme: next } );
			doAction( ACTIONS.THEME_CHANGED, next, resolved );
		},
		[ mode, resolved, updatePreferences ]
	);

	return { mode, resolved, setMode };
}
