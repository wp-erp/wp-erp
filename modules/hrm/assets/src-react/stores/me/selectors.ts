/**
 * Selectors for the `erp-hr/me` store.
 *
 * Pure read functions. Each takes the canonical state as the first arg per
 * `@wordpress/data` contract.
 */

import type { Capability } from '@/types/global';

import type { MeError, MePreferences, MeState, MeUser } from './types';

export function getUser( state: MeState ): MeUser | null {
	return state.user;
}

export function getCapabilities( state: MeState ): Record< Capability, boolean > {
	return state.capabilities;
}

/**
 * Test whether the current user has every capability in the given list.
 *
 * Single capability: pass a string.
 * AND-of-capabilities: pass an array.
 *
 * Returns true permissively if the store has never resolved (avoids flicker
 * before boot payload lands). Per critical-parts.md §24, the UI never hides a
 * destructive action solely on the client — the server always re-checks.
 */
export function hasCap(
	state: MeState,
	capability: Capability | readonly Capability[]
): boolean {
	if ( ! state.isReady ) {
		return true;
	}
	const caps = Array.isArray( capability ) ? capability : [ capability ];
	return caps.every( ( cap ) => state.capabilities[ cap ] === true );
}

export function getPreferences( state: MeState ): MePreferences {
	return state.preferences;
}

export function getResolvedThemeMode( state: MeState ): MePreferences[ 'erp_hr_color_scheme' ] {
	return state.preferences.erp_hr_color_scheme;
}

export function isLoading( state: MeState ): boolean {
	return state.isLoading;
}

export function getError( state: MeState ): MeError | null {
	return state.error;
}

export function isReady( state: MeState ): boolean {
	return state.isReady;
}
