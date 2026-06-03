/**
 * Actions for the `erp-hr/me` store.
 *
 * Sync setters + async generators. Async generators use the
 * `@wordpress/data` controls pattern (`yield apiFetch(...)`).
 */

import type { Capability } from '@/types/global';
import { request, restPath } from '@/shared/utils/apiFetch';
import { toBool, toEnumOrNull, toInt, toStr } from '@/shared/utils/coerce';

import type { MeAction } from './reducer';
import type {
	MeError,
	MePreferences,
	MeUser,
	RawMeResponse,
} from './types';

// ─────────────────────────────────────────────────────────────────────────
// Sync action creators
// ─────────────────────────────────────────────────────────────────────────

export function setMe( user: MeUser ): MeAction {
	return { type: 'SET_ME', payload: user };
}

export function setCapabilities(
	capabilities: Record< Capability, boolean >
): MeAction {
	return { type: 'SET_CAPABILITIES', payload: capabilities };
}

export function setPreferences( patch: Partial< MePreferences > ): MeAction {
	return { type: 'SET_PREFERENCES', payload: patch };
}

export function setLoading( isLoading: boolean ): MeAction {
	return { type: 'SET_LOADING', payload: isLoading };
}

export function setError( error: MeError | null ): MeAction {
	return { type: 'SET_ERROR', payload: error };
}

export function setReady( isReady: boolean ): MeAction {
	return { type: 'SET_READY', payload: isReady };
}

export function invalidate(): MeAction {
	return { type: 'INVALIDATE' };
}

// ─────────────────────────────────────────────────────────────────────────
// Async generators — `@wordpress/data` resolves yielded promises through its
// built-in controls.
// ─────────────────────────────────────────────────────────────────────────

/**
 * Fetch the current user's identity + capabilities from `/erp/v2/me/capabilities`.
 *
 * Dispatched directly (after boot-payload hydration) or auto-fired by the
 * `getUser` resolver.
 */
export function* fetchMe(): Generator< unknown, void, unknown > {
	yield setLoading( true );
	yield setError( null );

	try {
		const response = ( yield request< RawMeResponse >(
			restPath( 'v2', '/me/capabilities' )
		) ) as RawMeResponse;

		const { user, capabilities, preferences } = normalizeMeResponse( response );

		// Only apply a *valid* response. A 401/empty body (the error-normalizer
		// returns the body without throwing) would otherwise wipe the caps the
		// boot payload already seeded → false "No access". When the fetch can't be
		// trusted, keep the boot-seeded state.
		const isValid =
			user.id > 0 &&
			Object.keys( capabilities ).length > 0 &&
			! ( response as unknown as { code?: string } )?.code;

		if ( isValid ) {
			yield setMe( user );
			yield setCapabilities( capabilities );
			yield setPreferences( preferences );
		}
		yield setReady( true );
	} catch ( raw ) {
		const error = raw as { code?: string; message?: string };
		yield setError( {
			code:    toStr( error.code, 'erp_hr_me_failed' ),
			message: toStr( error.message, 'Could not load current user' ),
		} );
		// Permissive fallback per critical-parts.md §24 — leave user gated by
		// server-side caps; mark store ready so the UI can render skeleton →
		// shell rather than hang on isLoading forever.
		yield setReady( true );
	} finally {
		yield setLoading( false );
	}
}

/**
 * Push a preference change to the server (POST /erp/v2/me/preferences).
 *
 * The endpoint is not in this deliverable. The dispatch optimistically updates
 * local state and swallows 404 errors with a dev-mode console warning so
 * ThemeToggle persistence works as soon as the endpoint ships.
 */
export function* updatePreferences(
	patch: Partial< MePreferences >
): Generator< unknown, void, unknown > {
	yield setPreferences( patch );

	try {
		yield request( restPath( 'v2', '/me/preferences' ), {
			method: 'POST',
			data:   patch,
		} );
	} catch ( raw ) {
		const error = raw as { status?: number; code?: string; message?: string };
		// 404 — endpoint not yet implemented. Silent in production; warn in dev.
		const isMissingEndpoint = error.status === 404 || error.code === 'rest_no_route';
		if ( isMissingEndpoint ) {
			if ( typeof window !== 'undefined' && ( window as { wp?: { debug?: boolean } } ).wp?.debug ) {
				// eslint-disable-next-line no-console
				console.warn( '[erp-hr/me] /erp/v2/me/preferences endpoint not ready — preference stayed local.' );
			}
			return;
		}
		yield setError( {
			code:    toStr( error.code, 'erp_hr_me_pref_failed' ),
			message: toStr( error.message, 'Could not save preferences' ),
		} );
	}
}

// ─────────────────────────────────────────────────────────────────────────
// Normalizer — defensive coercion against loose WP REST responses.
// ─────────────────────────────────────────────────────────────────────────

function normalizeMeResponse( raw: RawMeResponse ): {
	user:         MeUser;
	capabilities: Record< Capability, boolean >;
	preferences:  Partial< MePreferences >;
} {
	const rawCaps = raw.capabilities ?? {};
	const capabilities: Record< Capability, boolean > = {};
	for ( const key of Object.keys( rawCaps ) ) {
		capabilities[ key ] = toBool( rawCaps[ key ] );
	}

	const user: MeUser = {
		id:          toInt( raw.user_id, 0 ),
		displayName: toStr( raw.display_name ),
		email:       toStr( raw.email ),
		avatarUrl:   toStr( raw.avatar_url ),
		isPro:       toBool( raw.is_pro ),
		isHrManager: toBool( raw.is_hr_manager ),
		roles:       Array.isArray( raw.roles )
			? raw.roles.filter( ( r ): r is string => typeof r === 'string' )
			: [],
	};

	const colorScheme = raw.preferences?.erp_hr_color_scheme;
	const mode = toEnumOrNull( colorScheme, [ 'light', 'dark', 'auto' ] as const );
	const preferences: Partial< MePreferences > = mode ? { erp_hr_color_scheme: mode } : {};

	return { user, capabilities, preferences };
}
