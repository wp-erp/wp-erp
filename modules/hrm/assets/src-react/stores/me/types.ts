/**
 * Types for the `erp-hr/me` @wordpress/data store.
 *
 * Mirrors the `/erp/v2/me/capabilities` response shape (backend-owned).
 */

import type { Capability, ThemeMode } from '@/types/global';

export interface MeUser {
	readonly id:           number;
	readonly displayName:  string;
	readonly email:        string;
	readonly avatarUrl:    string;
	readonly isPro:        boolean;
	readonly isHrManager:  boolean;
	readonly roles:        readonly string[];
}

export interface MePreferences {
	readonly erp_hr_color_scheme: ThemeMode;
}

export interface MeError {
	readonly code:    string;
	readonly message: string;
}

export interface MeState {
	readonly user:         MeUser | null;
	readonly capabilities: Record< Capability, boolean >;
	readonly preferences:  MePreferences;
	readonly isLoading:    boolean;
	readonly error:        MeError | null;
	readonly isReady:      boolean;
}

/**
 * Raw response from `/erp/v2/me/capabilities`. WP-loose typing — fields may
 * arrive as `'0' | '1'` or other stringly variants depending on filters.
 */
export interface RawMeResponse {
	readonly user_id?:       number | string;
	readonly display_name?:  string;
	readonly email?:         string;
	readonly avatar_url?:    string;
	readonly is_pro?:        boolean | number | string;
	readonly is_hr_manager?: boolean | number | string;
	readonly roles?:         readonly string[];
	readonly capabilities?:  Record< string, unknown >;
	readonly preferences?:   Partial< { erp_hr_color_scheme: string } >;
}

export const STORE_NAME = 'erp-hr/me' as const;
export type  StoreName  = typeof STORE_NAME;
