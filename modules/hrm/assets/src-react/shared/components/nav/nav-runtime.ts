/**
 * Shared nav runtime — the single source of truth for turning
 * `TOPBAR_NAV_ITEMS` into the visible, capability/pro-gated menu both the
 * horizontal `TopBar` and the vertical `Sidebar` render.
 *
 * Keeping the icon map, the path-active matcher, and the visibility filters
 * here means the two layouts can NEVER drift in what they show or how they
 * gate — they only differ in chrome.
 */

import { useSelect } from '@wordpress/data';
import {
	BadgeCheck,
	Banknote,
	BarChart3,
	Briefcase,
	CalendarCheck,
	CalendarDays,
	FileText,
	GraduationCap,
	HelpCircle,
	House,
	LayoutGrid,
	LayoutList,
	Package,
	Sparkles,
	UsersRound,
	Wallet,
} from 'lucide-react';
import type { ComponentType, SVGProps } from 'react';
import { useLocation } from 'react-router-dom';

import { useProUpsell } from '@/shared/components/pro/ProUpsell';
import { useBoot } from '@/shared/hooks/useBoot';
import { storeName as meStoreName } from '@/stores/me';
import type { Capability } from '@/types/global';

import { TOPBAR_NAV_ITEMS } from '@/shared/components/TopBar/nav-items';
import type { NavIconId, NavItem, NavSubItem } from '@/shared/components/TopBar/nav-items';

export type LucideIcon = ComponentType< SVGProps< SVGSVGElement > & { size?: number; strokeWidth?: number } >;

export type HasCap = ( capability: Capability | readonly Capability[] ) => boolean;

export const ICON_MAP: Readonly< Record< NavIconId, LucideIcon > > = {
	'house':           House,
	'users-round':     UsersRound,
	'sparkles':        Sparkles,
	'layout-list':     LayoutList,
	'badge-check':     BadgeCheck,
	'layout-grid':     LayoutGrid,
	'calendar-days':   CalendarDays,
	'calendar-check':  CalendarCheck,
	'package':         Package,
	'file-text':       FileText,
	'graduation-cap': GraduationCap,
	'briefcase':       Briefcase,
	'bar-chart-3':     BarChart3,
	'help-circle':     HelpCircle,
	'wallet':          Wallet,
	'banknote':        Banknote,
};

/**
 * Match the current hash route against an item's `activeMatches` prefix list.
 * Longest match wins implicitly — items are mutually exclusive in the spec.
 */
export function isPathActive( item: NavItem, currentPath: string ): boolean {
	const normalized = currentPath === '' ? '/' : currentPath;
	return item.activeMatches.some( ( prefix ) => {
		if ( prefix === '/' ) {
			return normalized === '/';
		}
		return normalized === prefix || normalized.startsWith( prefix + '/' );
	} );
}

interface MeStoreSelectors {
	hasCap: HasCap;
}

/** A visible top-level nav entry with its resolved icon + gate flags. */
export interface NavMenuEntry {
	readonly item:      NavItem;
	readonly Icon:      LucideIcon;
	/** Pro item shown only because the Pro plugin is absent — opens the upsell. */
	readonly proLocked: boolean;
	readonly hasMenu:   boolean;
}

/** A visible submenu child with its pro-locked flag. */
export interface NavMenuChild {
	readonly sub:       NavSubItem;
	readonly proLocked: boolean;
}

export interface NavMenu {
	readonly entries:     readonly NavMenuEntry[];
	readonly hasCap:      HasCap;
	readonly currentPath: string;
	readonly openUpsell:  ( label: string ) => void;
	readonly isActive:    ( item: NavItem ) => boolean;
	/** Visible, gated children for a dropdown/accordion item. */
	readonly childrenOf:  ( item: NavItem ) => readonly NavMenuChild[];
}

/**
 * Resolve the visible nav menu for the current user. Encapsulates the exact
 * capability + pro-module gating both layouts share.
 */
export function useNavMenu(): NavMenu {
	const hasCap = useSelect(
		( select ) => ( select( meStoreName ) as unknown as MeStoreSelectors ).hasCap,
		[]
	);
	const { pathname } = useLocation();
	const boot = useBoot();
	const activeModules = boot.modules ?? [];
	const isPro = boot.isPro;
	const { openUpsell } = useProUpsell();

	const entries: NavMenuEntry[] = TOPBAR_NAV_ITEMS
		.filter( ( item ) => {
			if ( item.capabilities.length > 0 && ! hasCap( item.capabilities ) ) {
				return false;
			}
			// Pro-module item whose module is inactive: show only as a "Pro" upsell
			// badge when the Pro plugin is absent (legacy parity).
			if ( item.module && ! activeModules.includes( item.module ) ) {
				return Boolean( item.pro && ! isPro );
			}
			return true;
		} )
		.map( ( item ) => ( {
			item,
			Icon:      ICON_MAP[ item.icon ],
			proLocked: Boolean(
				item.pro && ! isPro && ( ! item.module || ! activeModules.includes( item.module ) )
			),
			hasMenu: ( item.children?.length ?? 0 ) > 0,
		} ) );

	const childrenOf = ( item: NavItem ): NavMenuChild[] =>
		( item.children ?? [] )
			.filter(
				( sub ) =>
					( sub.capabilities.length === 0 || hasCap( sub.capabilities ) ) &&
					( ! sub.module || activeModules.includes( sub.module ) || Boolean( sub.pro && ! isPro ) )
			)
			.map( ( sub ) => ( {
				sub,
				proLocked: Boolean(
					sub.pro && ! isPro && ( ! sub.module || ! activeModules.includes( sub.module ) )
				),
			} ) );

	return {
		entries,
		hasCap,
		currentPath: pathname,
		openUpsell,
		isActive: ( item: NavItem ) => isPathActive( item, pathname ),
		childrenOf,
	};
}
