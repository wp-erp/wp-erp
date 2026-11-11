/**
 * Top bar nav links — center cluster.
 *
 * Renders the canonical HR nav from `nav-items.ts`, resolved + capability/pro
 * gated by the shared `useNavMenu()` runtime (the same hook the vertical
 * Sidebar uses, so the two layouts never drift). Active state derives from the
 * current hash route, so a deep route (e.g. `/employees/42`) still highlights
 * its parent ("People").
 *
 * Every item stays in the bar. When they do not fit, the list scrolls sideways:
 * an arrow button and an edge fade appear on each side that has hidden items,
 * a vertical mouse wheel scrolls the bar, and the active item is kept in view
 * (same pattern as the FlyCRM top nav). Logical start/end, so RTL works too.
 *
 * Locked to openspec/changes/redesign-hr-free/figma-reference.md §Layer 2,
 * "Center cluster — horizontal nav".
 */

import { __ } from '@wordpress/i18n';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import type { JSX } from 'react';
import { NavLink } from 'react-router-dom';

import { ProBadge } from '@/shared/components/pro/ProUpsell';
import { ICON_MAP, isPathActive, useNavMenu } from '@/shared/components/nav/nav-runtime';
import type { LucideIcon, NavMenu, NavMenuEntry } from '@/shared/components/nav/nav-runtime';

import { NavDropdown } from './NavDropdown';
import type { NavItem } from './nav-items';

// Hidden on phones: the bar is too narrow to spare them, and touch swipes it anyway.
const EDGE_BUTTON = 'max-md:hidden inline-flex w-8 shrink-0 items-center justify-center text-muted-foreground transition-colors enabled:hover:text-primary disabled:opacity-30';

interface Edges {
	readonly start: boolean;
	readonly end:   boolean;
	readonly rtl:   boolean;
	/** The items do not fit: both arrows show, the side with nothing hidden disabled. */
	readonly overflow: boolean;
}

export function NavLinks(): JSX.Element {
	const menu = useNavMenu();

	const navRef  = useRef< HTMLElement >( null );
	const listRef = useRef< HTMLDivElement >( null );
	const [ edges, setEdges ] = useState< Edges >( { start: false, end: false, rtl: false, overflow: false } );

	const measure = useCallback( () => {
		const list = listRef.current;
		if ( ! list ) {
			return;
		}
		// In RTL, scrollLeft runs from 0 down to a negative number.
		const rtl      = getComputedStyle( list ).direction === 'rtl';
		const scrolled = Math.abs( list.scrollLeft );
		setEdges( {
			start: scrolled > 1,
			end:   scrolled + list.clientWidth < list.scrollWidth - 1,
			rtl,
			// Both arrows stay while it overflows, so the list width never jumps mid-scroll.
			overflow: list.scrollWidth > list.clientWidth + 1,
		} );
	}, [] );

	// Keep the active item in view on load, on navigation and when the bar resizes.
	const reveal = useCallback( () => {
		const active = listRef.current?.querySelector< HTMLElement >( '[aria-current="page"], [data-active="true"]' );
		active?.scrollIntoView( { inline: 'nearest', block: 'nearest' } );
		measure();
	}, [ measure ] );

	useEffect( () => {
		const list = listRef.current;
		if ( ! list ) {
			return;
		}

		// A vertical wheel scrolls the bar sideways, but only while it has somewhere to go.
		const onWheel = ( event: WheelEvent ) => {
			if ( Math.abs( event.deltaY ) <= Math.abs( event.deltaX ) || list.scrollWidth <= list.clientWidth ) {
				return;
			}
			event.preventDefault();
			list.scrollLeft += event.deltaY;
		};
		// Watches the nav, not the list: the edge buttons resize the list, and
		// revealing then would undo their scroll.
		const observer = new ResizeObserver( reveal );
		observer.observe( navRef.current ?? list );
		list.addEventListener( 'wheel', onWheel, { passive: false } );

		return () => {
			observer.disconnect();
			list.removeEventListener( 'wheel', onWheel );
		};
	}, [ reveal ] );

	useEffect( reveal, [ menu.currentPath, reveal ] );

	// Scroll by most of the visible width, keeping a sliver of the last view for
	// context. Stepping one item at a time barely moved: the first cut-off item
	// is usually almost in view, and the other arrow appearing narrows the list.
	const step = ( side: 'start' | 'end' ) => {
		const list = listRef.current;
		if ( ! list ) {
			return;
		}
		const distance = Math.max( list.clientWidth - 64, 64 );
		const forward  = side === 'end' ? 1 : -1;
		list.scrollBy( { left: distance * forward * ( edges.rtl ? -1 : 1 ), behavior: 'smooth' } );
	};

	const fade = [
		edges.start ? 'transparent, black 1.5rem' : 'black',
		edges.end ? 'black calc(100% - 1.5rem), transparent' : 'black',
	].join( ', ' );

	return (
		<nav
			ref={ navRef }
			aria-label={ __( 'HR sections', 'erp' ) }
			className="ml-4 flex h-12 min-w-0 flex-1 items-stretch"
		>
			{ edges.overflow ? (
				<button
					type="button"
					tabIndex={ -1 }
					disabled={ ! edges.start }
					aria-label={ __( 'Scroll menu back', 'erp' ) }
					className={ EDGE_BUTTON }
					onClick={ () => step( 'start' ) }
				>
					<ChevronLeft size={ 16 } strokeWidth={ 1.75 } aria-hidden="true" />
				</button>
			) : null }

			<div
				ref={ listRef }
				onScroll={ measure }
				style={ {
					maskImage:       `linear-gradient(to ${ edges.rtl ? 'left' : 'right' }, ${ fade })`,
					WebkitMaskImage: `linear-gradient(to ${ edges.rtl ? 'left' : 'right' }, ${ fade })`,
				} }
				className="erp-hr-topbar-nav-scroll flex min-w-0 flex-1 flex-nowrap items-stretch overflow-x-auto whitespace-nowrap"
			>
				{ menu.entries.map( ( entry ) => (
					<NavEntry key={ entry.item.id } entry={ entry } menu={ menu } />
				) ) }
			</div>

			{ edges.overflow ? (
				<button
					type="button"
					tabIndex={ -1 }
					disabled={ ! edges.end }
					aria-label={ __( 'Scroll menu forward', 'erp' ) }
					className={ EDGE_BUTTON }
					onClick={ () => step( 'end' ) }
				>
					<ChevronRight size={ 16 } strokeWidth={ 1.75 } aria-hidden="true" />
				</button>
			) : null }
		</nav>
	);
}

interface NavEntryProps {
	readonly entry: NavMenuEntry;
	readonly menu:  NavMenu;
}

function NavEntry( { entry, menu }: NavEntryProps ): JSX.Element {
	const { item, Icon, proLocked, hasMenu } = entry;

	if ( proLocked ) {
		return <NavProItem item={ item } Icon={ Icon } onClick={ () => menu.openUpsell( item.label ) } />;
	}

	return hasMenu ? (
		<NavDropdown
			item={ item }
			active={ menu.isActive( item ) }
			Icon={ Icon }
			items={ menu.childrenOf( item ) }
		/>
	) : (
		<NavItemLink item={ item } currentPath={ menu.currentPath } />
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
	const Icon   = ICON_MAP[ item.icon ] ?? ICON_MAP[ 'layout-grid' ];

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
