/**
 * Resolved color scheme hook.
 *
 * Resolution chain (highest priority first):
 *   1. User preference (`themeMode` from boot payload + me store).
 *   2. WordPress admin color scheme (`colorScheme` from boot payload).
 *   3. OS preference (`prefers-color-scheme`).
 *   4. 'light' default.
 *
 * Returns the resolved scheme (`'light' | 'dark'`) — never `'auto'`.
 */

import { useEffect, useState } from 'react';
import { useSelect } from '@wordpress/data';

import { storeName as meStoreName } from '@/stores/me';
import type { ColorScheme, ThemeMode } from '@/types/global';

import { useBoot } from './useBoot';

interface MeStoreSelectors {
	getResolvedThemeMode: () => ThemeMode;
}

function readSystemScheme(): ColorScheme {
	if ( typeof window === 'undefined' || typeof window.matchMedia !== 'function' ) {
		return 'light';
	}
	return window.matchMedia( '(prefers-color-scheme: dark)' ).matches ? 'dark' : 'light';
}

export function useColorScheme(): ColorScheme {
	const boot = useBoot();

	const storedMode = useSelect( ( select ) => {
		const store = select( meStoreName ) as unknown as MeStoreSelectors;
		return store.getResolvedThemeMode();
	}, [] );

	const [ system, setSystem ] = useState< ColorScheme >( readSystemScheme );

	useEffect( () => {
		if ( typeof window === 'undefined' || typeof window.matchMedia !== 'function' ) {
			return;
		}
		const mql = window.matchMedia( '(prefers-color-scheme: dark)' );
		const onChange = ( e: MediaQueryListEvent ) => {
			setSystem( e.matches ? 'dark' : 'light' );
		};
		mql.addEventListener( 'change', onChange );
		return () => mql.removeEventListener( 'change', onChange );
	}, [] );

	const effectiveMode: ThemeMode = storedMode ?? boot.themeMode;

	if ( effectiveMode === 'light' || effectiveMode === 'dark' ) {
		return effectiveMode;
	}

	// 'auto' — fall through the rest of the chain.
	if ( boot.colorScheme === 'light' || boot.colorScheme === 'dark' ) {
		return boot.colorScheme;
	}

	return system;
}
