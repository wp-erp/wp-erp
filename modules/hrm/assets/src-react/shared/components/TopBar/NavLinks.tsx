/**
 * Top bar nav links — center cluster.
 *
 * Renders the canonical 11-item HR nav from `nav-items.ts`. Each item gates on
 * the spec's capability list (Pro-only items appear once HR Pro grants their
 * caps). Active state derived from the current hash route, not the link's own
 * `to` value, so a deep route (e.g. `/employees/42`) still highlights its
 * parent ("People").
 *
 * Locked to openspec/changes/redesign-hr-free/figma-reference.md §Layer 2,
 * "Center cluster — horizontal nav".
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
import type { ComponentType, JSX, SVGProps } from 'react';
import { NavLink, useLocation } from 'react-router-dom';

import { useBoot } from '@/shared/hooks/useBoot';
import { storeName as meStoreName } from '@/stores/me';
import type { Capability } from '@/types/global';

import { ProBadge, useProUpsell } from '@/shared/components/pro/ProUpsell';

import { NavDropdown } from './NavDropdown';
import { TOPBAR_NAV_ITEMS } from './nav-items';
import type { NavIconId, NavItem } from './nav-items';

type LucideIcon = ComponentType< SVGProps< SVGSVGElement > & { size?: number; strokeWidth?: number } >;

const ICON_MAP: Readonly< Record< NavIconId, LucideIcon > > = {
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

interface MeStoreSelectors {
	hasCap: ( capability: Capability | readonly Capability[] ) => boolean;
}

export function NavLinks(): JSX.Element {
	const hasCap = useSelect(
		( select ) => ( select( meStoreName ) as unknown as MeStoreSelectors ).hasCap,
		[]
	);
	const location = useLocation();
	const boot = useBoot();
	const activeModules = boot.modules ?? [];
	const isPro = boot.isPro;
	const { openUpsell } = useProUpsell();

	const visible = TOPBAR_NAV_ITEMS.filter( ( item ) => {
		// Capability gate first.
		if ( item.capabilities.length > 0 && ! hasCap( item.capabilities ) ) {
			return false;
		}
		// Pro-module item whose module is inactive: show only as a "Pro" upsell
		// badge when the Pro plugin is absent — otherwise hide (legacy parity:
		// pro installed + module off ⇒ no menu item).
		if ( item.module && ! activeModules.includes( item.module ) ) {
			return Boolean( item.pro && ! isPro );
		}
		return true;
	} );

	return (
		<nav
			aria-label="HR sections"
			className="ml-6 flex h-16 items-stretch flex-nowrap whitespace-nowrap"
		>
			{ visible.map( ( item ) => {
				const Icon = ICON_MAP[ item.icon ];

				// "Pro locked" = a pro item shown only because the Pro plugin is
				// absent. It renders as a badge that opens the upgrade dialog
				// instead of navigating to a page that does not exist.
				const proLocked = Boolean(
					item.pro && ! isPro && ( ! item.module || ! activeModules.includes( item.module ) )
				);
				if ( proLocked ) {
					return (
						<NavProItem
							key={ item.id }
							item={ item }
							Icon={ Icon }
							onClick={ () => openUpsell( item.label ) }
						/>
					);
				}

				const active  = isPathActive( item, location.pathname );
				const hasMenu = ( item.children?.length ?? 0 ) > 0;

				return hasMenu ? (
					<NavDropdown
						key={ item.id }
						item={ item }
						active={ active }
						Icon={ Icon }
						hasCap={ hasCap }
					/>
				) : (
					<NavItemLink
						key={ item.id }
						item={ item }
						currentPath={ location.pathname }
					/>
				);
			} ) }
		</nav>
	);
}

interface NavProItemProps {
	readonly item:    NavItem;
	readonly Icon:    LucideIcon;
	readonly onClick: () => void;
}

/** A pro-only top-level item rendered as a "Pro" badge (Pro plugin absent). */
function NavProItem( { item, Icon, onClick }: NavProItemProps ): JSX.Element {
	return (
		<button
			type="button"
			onClick={ onClick }
			className="group relative inline-flex shrink-0 items-center gap-2 px-5 text-sm font-normal text-foreground transition-colors hover:text-primary"
		>
			<Icon size={ 18 } strokeWidth={ 1.75 } aria-hidden="true" />
			<span>{ item.label }</span>
			<ProBadge />
		</button>
	);
}

interface NavItemLinkProps {
	readonly item:        NavItem;
	readonly currentPath: string;
}

function NavItemLink( { item, currentPath }: NavItemLinkProps ): JSX.Element {
	const active = isPathActive( item, currentPath );
	const Icon   = ICON_MAP[ item.icon ];

	return (
		<NavLink
			to={ item.path }
			viewTransition
			aria-current={ active ? 'page' : undefined }
			className={ [
				'group relative inline-flex shrink-0 items-center gap-2 px-5 text-sm transition-colors',
				active
					? 'font-medium text-primary'
					: 'font-normal text-foreground hover:text-primary',
			].join( ' ' ) }
		>
			<Icon size={ 18 } strokeWidth={ 1.75 } aria-hidden="true" />
			<span>{ item.label }</span>
			<span
				aria-hidden="true"
				className={ [
					'absolute inset-x-0 bottom-0 h-0.5',
					active ? 'bg-primary' : 'bg-transparent',
				].join( ' ' ) }
			/>
		</NavLink>
	);
}

/**
 * Match the current hash route against an item's `activeMatches` prefix list.
 * Longest match wins implicitly — items are mutually exclusive in the spec.
 */
function isPathActive( item: NavItem, currentPath: string ): boolean {
	const normalized = currentPath === '' ? '/' : currentPath;
	return item.activeMatches.some( ( prefix ) => {
		if ( prefix === '/' ) {
			return normalized === '/';
		}
		return normalized === prefix || normalized.startsWith( prefix + '/' );
	} );
}
