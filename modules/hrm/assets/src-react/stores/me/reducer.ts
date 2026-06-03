/**
 * Reducer for the `erp-hr/me` store. Pure — no side effects.
 */

import type { Capability } from '@/types/global';

import type { MeError, MePreferences, MeState, MeUser } from './types';

export type MeAction =
	| { readonly type: 'SET_ME'; readonly payload: MeUser }
	| { readonly type: 'SET_CAPABILITIES'; readonly payload: Record< Capability, boolean > }
	| { readonly type: 'SET_PREFERENCES'; readonly payload: Partial< MePreferences > }
	| { readonly type: 'SET_LOADING'; readonly payload: boolean }
	| { readonly type: 'SET_ERROR'; readonly payload: MeError | null }
	| { readonly type: 'SET_READY'; readonly payload: boolean }
	| { readonly type: 'INVALIDATE' };

/**
 * Seed the store from the boot payload (`window.__ERP_HR_BOOT__`), which the PHP
 * enqueue already localizes with the server-computed capabilities + identity for
 * the logged-in user. This makes access gating work synchronously and without a
 * REST roundtrip — the `/me/capabilities` fetch only *refreshes* it. Falls back
 * to an empty, not-ready state when the payload is absent (e.g. tests).
 */
function bootInitialState(): MeState {
	const boot = typeof window !== 'undefined' ? window.__ERP_HR_BOOT__ : undefined;

	if ( ! boot ) {
		return {
			user:         null,
			capabilities: {},
			preferences:  { erp_hr_color_scheme: 'auto' },
			isLoading:    false,
			error:        null,
			isReady:      false,
		};
	}

	return {
		user: {
			id:          boot.currentUserId,
			displayName: boot.displayName,
			email:       boot.email,
			avatarUrl:   boot.avatarUrl,
			isPro:       boot.isPro,
			isHrManager: boot.isHrManager,
			roles:       [],
		},
		capabilities: { ...boot.capabilities },
		preferences:  { erp_hr_color_scheme: boot.themeMode },
		isLoading:    false,
		error:        null,
		isReady:      true,
	};
}

export const INITIAL_STATE: MeState = bootInitialState();

export default function reducer(
	state: MeState = INITIAL_STATE,
	action: MeAction
): MeState {
	switch ( action.type ) {
		case 'SET_ME':
			return { ...state, user: action.payload };

		case 'SET_CAPABILITIES':
			return { ...state, capabilities: action.payload };

		case 'SET_PREFERENCES':
			return {
				...state,
				preferences: { ...state.preferences, ...action.payload },
			};

		case 'SET_LOADING':
			return { ...state, isLoading: action.payload };

		case 'SET_ERROR':
			return { ...state, error: action.payload };

		case 'SET_READY':
			return { ...state, isReady: action.payload };

		case 'INVALIDATE':
			return { ...INITIAL_STATE };

		default:
			return state;
	}
}
