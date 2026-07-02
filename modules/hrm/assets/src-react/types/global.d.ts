/**
 * Ambient types for the WP-ERP HR React shell.
 *
 * Declares the PHP→JS boot bridge (`window.__ERP_HR_BOOT__`), the WP global
 * surfaces consumed by the shell, and the route handle attached to every
 * React Router v7 route.
 *
 * Contract locked at:
 *   openspec/changes/redesign-hr-free/playbooks/_first-deliverable.md
 *   (§Boot payload contract, §Critical TypeScript interfaces)
 */

export type ColorScheme = 'light' | 'dark';
export type ThemeMode   = 'light' | 'dark' | 'auto';
export type NavLayout   = 'topbar' | 'sidebar';
export type Direction   = 'ltr' | 'rtl';
export type Capability  = string;

export interface BootPayloadApi {
	readonly nsV1: 'erp/v1';
	readonly nsV2: 'erp/v2';
	readonly root: string;
}

export interface BootPayloadAssets {
	readonly logoUrl: string;
	/** Base URL of the shared pro-popup illustration set (no trailing slash). */
	readonly proPopupUrl?: string;
}

/** Global HR settings surfaced to the React admin. */
export interface BootPayloadSettings {
	/** `erp_hrm_hide_pay_rate` — blur pay rate on the employee profile. */
	readonly hidePayRate: boolean;
}

export interface BootSelectOption {
	readonly value: string;
	readonly label: string;
}

export interface BootPayloadFilters {
	readonly topbarRightItems: 'erp_hr.topbar.right_items';
	readonly userMenuItems:    'erp_hr.user_menu.items';
	readonly routes:           'erp_hr.routes';
	readonly capsChanged:      'erp_hr.caps.changed';
	readonly themeChanged:     'erp_hr.theme.changed';
	readonly shellReady:       'erp_hr.shell.ready';
}

export interface LegacySwitchTarget {
	readonly section:      string;
	readonly subSection?:  string;
	readonly action?:      'view' | 'edit' | 'add' | null;
	readonly id?:          number | null;
	readonly type?:        string | null;
}

export interface BootPayload {
	readonly currentUserId: number;
	readonly displayName:   string;
	readonly email:         string;
	readonly avatarUrl:     string;
	readonly isPro:         boolean;
	readonly isHrManager:   boolean;
	readonly api:           BootPayloadApi;
	readonly nonce:         string;
	readonly locale:        string;
	readonly isRTL:         boolean;
	readonly colorScheme:   ColorScheme;
	readonly themeMode:     ThemeMode;
	readonly navLayout:     NavLayout;
	readonly switchUrl:     string;
	readonly pageSlug:      string;
	readonly assets:        BootPayloadAssets;
	readonly settings?:     BootPayloadSettings;
	readonly capabilities:  Record<Capability, boolean>;
	readonly hrmVersion:    string;
	readonly legacyTarget?: LegacySwitchTarget;
	readonly filters:       BootPayloadFilters;
	/** Active pro HR sub-modules (each self-registers via `erp_hr_v2_boot_payload`). */
	readonly modules?:      readonly string[];
	/** Country options (value=code, label=name) for the employee-form address selects. */
	readonly countries?:    readonly BootSelectOption[];
	/** States keyed by country code, for the country-dependent state select. */
	readonly states?:       Readonly<Record<string, readonly BootSelectOption[]>>;
}

export interface RouteHandle {
	readonly id:           string;
	readonly title:        string;
	readonly navLabel?:    string;
	readonly group?:       'people' | 'leave' | 'reports' | 'attendance' | 'help';
	readonly showInNav?:   boolean;
	readonly capabilities?: readonly Capability[];
}

declare global {
	interface Window {
		__ERP_HR_BOOT__?:        BootPayload;
		__ERP_HR_LEGACY_URL__?:  LegacySwitchTarget;
		wpApiSettings?:          { root: string; nonce: string };
		wp?: {
			hooks?: typeof import( '@wordpress/hooks' );
			data?:  typeof import( '@wordpress/data' );
			i18n?:  typeof import( '@wordpress/i18n' );
		};
	}
}

// Side-effect CSS imports (wp-scripts extracts these via mini-css-extract).
declare module '*.css';
declare module '*.scss';

export {};
