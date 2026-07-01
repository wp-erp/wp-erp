/**
 * Top bar nav links — center cluster.
 *
 * Renders the canonical HR nav from `nav-items.ts`, resolved + capability/pro
 * gated by the shared `useNavMenu()` runtime (the same hook the vertical
 * Sidebar uses, so the two layouts never drift). Active state derives from the
 * current hash route, so a deep route (e.g. `/employees/42`) still highlights
 * its parent ("People").
 *
 * Locked to openspec/changes/redesign-hr-free/figma-reference.md §Layer 2,
 * "Center cluster — horizontal nav".
 */

import type { JSX } from 'react';
import { NavLink } from 'react-router-dom';

import { ProBadge } from '@/shared/components/pro/ProUpsell';
import { ICON_MAP, isPathActive, useNavMenu } from '@/shared/components/nav/nav-runtime';
import type { LucideIcon } from '@/shared/components/nav/nav-runtime';

import { NavDropdown } from './NavDropdown';
import type { NavItem } from './nav-items';

export function NavLinks(): JSX.Element {
	const { entries, hasCap, currentPath, openUpsell, isActive } = useNavMenu();

	return (
		<nav
			aria-label="HR sections"
			className="ml-4 flex h-12 items-stretch flex-nowrap whitespace-nowrap"
		>
			{ entries.map( ( { item, Icon, proLocked, hasMenu } ) => {
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

				const active = isActive( item );

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
						currentPath={ currentPath }
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
			className="group relative inline-flex shrink-0 items-center gap-1.5 px-3 text-sm font-normal text-foreground transition-colors hover:text-primary"
		>
			<Icon size={ 16 } strokeWidth={ 1.75 } aria-hidden="true" />
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
				'group relative inline-flex shrink-0 items-center gap-1.5 px-3 text-sm transition-colors',
				active
					? 'font-medium text-primary'
					: 'font-normal text-foreground hover:text-primary',
			].join( ' ' ) }
		>
			<Icon size={ 16 } strokeWidth={ 1.75 } aria-hidden="true" />
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
