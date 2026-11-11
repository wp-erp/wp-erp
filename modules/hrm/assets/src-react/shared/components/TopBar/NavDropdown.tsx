/**
 * Inline nav dropdown for top-bar items that carry `children`.
 *
 * Rendered INSIDE the React tree (inside `#erp-hr-app`) so it inherits the
 * ThemeProvider tokens — dark mode "just works", unlike plugin-ui's portaled
 * menus. The panel uses `position: fixed` (coordinates derived from the trigger)
 * so it escapes the nav's overflow clip without a portal.
 *
 * Opens on hover (pointer) and click; closes on outside-click, Escape, blur,
 * route change, or selecting an item. Keyboard + ARIA friendly.
 *
 * The popover mechanics (`useNavPopover`, `NavPanel`) and the item list
 * (`NavPanelLinks`) are shared with the overflow "More" menu.
 */

import { ChevronDown } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import type { ComponentType, JSX, ReactNode, SVGProps } from 'react';
import { NavLink, useLocation } from 'react-router-dom';

import { ProBadge, useProUpsell } from '@/shared/components/pro/ProUpsell';
import type { NavMenuChild } from '@/shared/components/nav/nav-runtime';
import { useDir } from '@/shared/hooks/useDir';
import { useRequestsPendingCount } from '@/shared/hooks/useRequestsPendingCount';

import type { NavItem } from './nav-items';

type LucideIcon = ComponentType< SVGProps< SVGSVGElement > & { size?: number; strokeWidth?: number } >;

interface Coords {
	readonly top:   number;
	readonly left:  number;
	readonly right: number;
}

/** Open/close + placement state for a top-bar popover anchored to a trigger. */
export function useNavPopover() {
	const [ open, setOpen ]     = useState( false );
	const [ coords, setCoords ] = useState< Coords >( { top: 0, left: 0, right: 0 } );
	const wrapRef    = useRef< HTMLDivElement >( null );
	const triggerRef = useRef< HTMLButtonElement >( null );
	const closeTimer = useRef< number | undefined >( undefined );
	const location   = useLocation();

	const place = useCallback( () => {
		const el = triggerRef.current;
		if ( ! el ) {
			return;
		}
		const rect = el.getBoundingClientRect();
		setCoords( {
			top:   Math.round( rect.bottom ),
			left:  Math.round( rect.left ),
			right: Math.round( window.innerWidth - rect.right ),
		} );
	}, [] );

	const openMenu = useCallback( () => {
		if ( closeTimer.current ) {
			window.clearTimeout( closeTimer.current );
			closeTimer.current = undefined;
		}
		place();
		setOpen( true );
	}, [ place ] );

	const closeSoon = useCallback( () => {
		closeTimer.current = window.setTimeout( () => setOpen( false ), 120 );
	}, [] );

	// Close on route change.
	useEffect( () => {
		setOpen( false );
	}, [ location.pathname ] );

	// While open: outside-click + Escape + keep aligned on scroll/resize.
	useEffect( () => {
		if ( ! open ) {
			return;
		}
		const onDocPointer = ( e: MouseEvent ) => {
			if ( wrapRef.current && ! wrapRef.current.contains( e.target as Node ) ) {
				setOpen( false );
			}
		};
		const onKey = ( e: KeyboardEvent ) => {
			if ( e.key === 'Escape' ) {
				setOpen( false );
				triggerRef.current?.focus();
			}
		};
		const onReflow = () => place();

		document.addEventListener( 'mousedown', onDocPointer );
		document.addEventListener( 'keydown', onKey );
		window.addEventListener( 'scroll', onReflow, true );
		window.addEventListener( 'resize', onReflow );
		return () => {
			document.removeEventListener( 'mousedown', onDocPointer );
			document.removeEventListener( 'keydown', onKey );
			window.removeEventListener( 'scroll', onReflow, true );
			window.removeEventListener( 'resize', onReflow );
		};
	}, [ open, place ] );

	return { open, setOpen, coords, wrapRef, triggerRef, openMenu, closeSoon };
}

interface NavPanelProps {
	readonly label:        string;
	readonly coords:       Coords;
	readonly onMouseEnter: () => void;
	readonly onMouseLeave: () => void;
	readonly children:     ReactNode;
}

/** The fixed-position menu panel under a top-bar trigger. */
export function NavPanel( { label, coords, onMouseEnter, onMouseLeave, children }: NavPanelProps ): JSX.Element {
	const dir = useDir();

	return (
		<div
			role="menu"
			aria-label={ label }
			onMouseEnter={ onMouseEnter }
			onMouseLeave={ onMouseLeave }
			style={ {
				position: 'fixed',
				top: coords.top,
				// RTL: anchor the panel to the trigger's right edge so the
				// min-w-64 menu grows leftward (toward the page interior)
				// instead of overflowing the left viewport edge.
				...( dir === 'rtl' ? { right: coords.right } : { left: coords.left } ),
				maxHeight: `calc(100vh - ${ coords.top + 16 }px)`,
			} }
			className="z-50 mt-1 min-w-64 overflow-y-auto rounded-lg border border-border bg-popover p-1.5 text-popover-foreground shadow-lg"
		>
			{ children }
		</div>
	);
}

interface NavPanelLinksProps {
	readonly items:  readonly NavMenuChild[];
	readonly onPick: () => void;
}

/** Menu entries of a dropdown: links, or a "Pro" upsell when Pro is absent. */
export function NavPanelLinks( { items, onPick }: NavPanelLinksProps ): JSX.Element {
	const { openUpsell } = useProUpsell();
	const pendingRequests = useRequestsPendingCount();

	return (
		<>
			{ items.map( ( { sub, proLocked } ) => {
				if ( proLocked ) {
					return (
						<button
							key={ sub.id }
							type="button"
							role="menuitem"
							onClick={ () => {
								onPick();
								openUpsell( sub.label );
							} }
							className="flex w-full items-center justify-between gap-2 rounded-md px-3 py-2 text-left text-sm font-medium text-popover-foreground transition-colors hover:bg-muted"
						>
							{ sub.label }
							<ProBadge />
						</button>
					);
				}

				return (
					<NavLink
						key={ sub.id }
						to={ sub.to }
						end
						viewTransition
						role="menuitem"
						onClick={ onPick }
						className={ ( { isActive } ) =>
							[
								'block rounded-md px-3 py-2 text-sm font-medium transition-colors',
								isActive
									? 'bg-accent text-accent-foreground'
									: 'text-popover-foreground hover:bg-muted',
							].join( ' ' )
						}
					>
						{ sub.label }
						{ sub.id === 'people-requests' && pendingRequests > 0 ? (
							<span className="ml-2 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-destructive px-1.5 text-xs font-semibold text-destructive-foreground">
								{ pendingRequests }
							</span>
						) : null }
					</NavLink>
				);
			} ) }
		</>
	);
}

interface NavDropdownProps {
	readonly item:   NavItem;
	readonly active: boolean;
	readonly Icon:   LucideIcon;
	/** Visible, gated children (`useNavMenu().childrenOf( item )`). */
	readonly items:  readonly NavMenuChild[];
}

export function NavDropdown( { item, active, Icon, items }: NavDropdownProps ): JSX.Element {
	const { open, setOpen, coords, wrapRef, triggerRef, openMenu, closeSoon } = useNavPopover();

	return (
		<div
			ref={ wrapRef }
			className="relative flex items-stretch"
			onMouseEnter={ openMenu }
			onMouseLeave={ closeSoon }
		>
			<button
				ref={ triggerRef }
				type="button"
				aria-haspopup="menu"
				aria-expanded={ open }
				// Lets the scrolling top bar find and reveal the active section.
				data-active={ active ? 'true' : undefined }
				onClick={ () => ( open ? setOpen( false ) : openMenu() ) }
				className={ [
					'group relative inline-flex shrink-0 items-center gap-1.5 px-3 text-sm transition-colors',
					active ? 'font-medium text-primary' : 'font-normal text-foreground hover:text-primary',
				].join( ' ' ) }
			>
				<Icon size={ 16 } strokeWidth={ 1.75 } aria-hidden="true" />
				<span>{ item.label }</span>
				<ChevronDown
					size={ 14 }
					strokeWidth={ 1.75 }
					aria-hidden="true"
					className={ [
						'opacity-70 transition-transform group-hover:opacity-100',
						open ? 'rotate-180' : '',
					].join( ' ' ) }
				/>
				<span
					aria-hidden="true"
					className={ [
						'absolute inset-x-0 bottom-0 h-0.5',
						active ? 'bg-primary' : 'bg-transparent',
					].join( ' ' ) }
				/>
			</button>

			{ open ? (
				<NavPanel label={ item.label } coords={ coords } onMouseEnter={ openMenu } onMouseLeave={ closeSoon }>
					<NavPanelLinks items={ items } onPick={ () => setOpen( false ) } />
				</NavPanel>
			) : null }
		</div>
	);
}
