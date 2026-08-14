/**
 * Vertical sidebar nav — the sidebar-layout counterpart of `TopBar/NavLinks`.
 *
 * Consumes the SAME `useNavMenu()` runtime (identical capability + pro-module
 * gating), so it can never show a different set of items than the top bar.
 * Dropdown items render as inline accordions (children indented below the
 * parent) instead of hover flyouts — the active section auto-expands.
 *
 * When `collapsed` is set the rail is icon-only: labels, pro badges, and the
 * accordion chevrons are hidden, sections degrade to a single link to their own
 * route (no inline children), and every row carries a `title` tooltip.
 *
 * Reference: Figma HRM-Redesign-2024 node 1511:29973 (sidebar layout).
 */

import { Popover, PopoverContent, PopoverTrigger } from '@wedevs/plugin-ui';
import { ChevronDown } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import type { JSX } from 'react';
import { NavLink } from 'react-router-dom';

import { ProBadge } from '@/shared/components/pro/ProUpsell';
import { useNavMenu } from '@/shared/components/nav/nav-runtime';
import type { LucideIcon } from '@/shared/components/nav/nav-runtime';
import type { NavItem } from '@/shared/components/TopBar/nav-items';

const ROW_BASE =
	'group flex w-full items-center gap-3 rounded-md px-3 py-2 text-left text-sm transition-colors';

const ROW_BASE_COLLAPSED =
	'group flex w-full items-center justify-center rounded-md p-2 text-sm transition-colors';

function rowClass( active: boolean, collapsed = false ): string {
	return [
		collapsed ? ROW_BASE_COLLAPSED : ROW_BASE,
		active
			? 'bg-primary/10 font-medium text-primary'
			: 'font-normal text-foreground hover:bg-muted',
	].join( ' ' );
}

interface SidebarNavProps {
	readonly collapsed?: boolean;
}

export function SidebarNav( { collapsed = false }: SidebarNavProps ): JSX.Element {
	const { entries, currentPath, openUpsell, isActive } = useNavMenu();

	// Track which dropdown sections are expanded. Seed with the active section
	// and keep it open as the route changes; the user can still toggle others.
	const [ expanded, setExpanded ] = useState< ReadonlySet< string > >( () => {
		const init = new Set< string >();
		entries.forEach( ( e ) => {
			if ( e.hasMenu && isActive( e.item ) ) {
				init.add( e.item.id );
			}
		} );
		return init;
	} );

	useEffect( () => {
		setExpanded( ( prev ) => {
			const next = new Set( prev );
			entries.forEach( ( e ) => {
				if ( e.hasMenu && isActive( e.item ) ) {
					next.add( e.item.id );
				}
			} );
			return next;
		} );
		// Re-evaluate only when the route changes.
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ currentPath ] );

	const toggle = ( id: string ): void =>
		setExpanded( ( prev ) => {
			const next = new Set( prev );
			if ( next.has( id ) ) {
				next.delete( id );
			} else {
				next.add( id );
			}
			return next;
		} );

	return (
		<nav
			aria-label="HR sections"
			className={ `flex flex-col gap-0.5 py-3 ${ collapsed ? 'px-2' : 'px-3' }` }
		>
			{ entries.map( ( { item, Icon, proLocked, hasMenu } ) => {
				if ( proLocked ) {
					return (
						<button
							key={ item.id }
							type="button"
							onClick={ () => openUpsell( item.label ) }
							className={ rowClass( false, collapsed ) }
							title={ collapsed ? item.label : undefined }
						>
							<Icon size={ 18 } strokeWidth={ 1.75 } aria-hidden="true" />
							{ ! collapsed && (
								<>
									<span className="flex-1 truncate">{ item.label }</span>
									<ProBadge />
								</>
							) }
						</button>
					);
				}

				// In the collapsed rail a top-level item with a submenu shows its
				// children in a hover flyout (Popover, portaled so it escapes the
				// rail's overflow); childless items stay a plain icon link.
				if ( collapsed ) {
					return hasMenu
						? <SidebarFlyout key={ item.id } item={ item } Icon={ Icon } active={ isActive( item ) } />
						: <SidebarLeaf key={ item.id } item={ item } Icon={ Icon } collapsed />;
				}
				if ( ! hasMenu ) {
					return <SidebarLeaf key={ item.id } item={ item } Icon={ Icon } />;
				}

				return (
					<SidebarSection
						key={ item.id }
						item={ item }
						Icon={ Icon }
						active={ isActive( item ) }
						open={ expanded.has( item.id ) }
						onToggle={ () => toggle( item.id ) }
					/>
				);
			} ) }
		</nav>
	);
}

interface LeafProps {
	readonly item:       NavItem;
	readonly Icon:       LucideIcon;
	readonly collapsed?: boolean;
}

/** A childless top-level item — a plain route link. */
function SidebarLeaf( { item, Icon, collapsed = false }: LeafProps ): JSX.Element {
	return (
		<NavLink
			to={ item.path }
			end={ item.path === '/' }
			viewTransition
			title={ collapsed ? item.label : undefined }
			className={ ( { isActive } ) => rowClass( isActive, collapsed ) }
		>
			<Icon size={ 18 } strokeWidth={ 1.75 } aria-hidden="true" />
			{ ! collapsed && <span className="flex-1 truncate">{ item.label }</span> }
		</NavLink>
	);
}

interface FlyoutProps {
	readonly item:   NavItem;
	readonly Icon:   LucideIcon;
	readonly active: boolean;
}

/** Collapsed-rail item with children — icon link + a hover flyout of sub-links. */
function SidebarFlyout( { item, Icon, active }: FlyoutProps ): JSX.Element {
	const { childrenOf, openUpsell } = useNavMenu();
	const kids = childrenOf( item );

	// Hover-driven flyout: open on enter, close on leave with a small delay so the
	// pointer can cross the gap from the icon to the portaled panel.
	const [ open, setOpen ] = useState( false );
	const closeTimer = useRef< number | undefined >( undefined );
	const show = (): void => { window.clearTimeout( closeTimer.current ); setOpen( true ); };
	const hide = (): void => { closeTimer.current = window.setTimeout( () => setOpen( false ), 120 ); };

	return (
		<Popover open={ open } onOpenChange={ setOpen }>
			<PopoverTrigger
				render={
					<NavLink
						to={ item.path }
						end={ item.path === '/' }
						viewTransition
						aria-label={ item.label }
						className={ rowClass( active, true ) }
						onMouseEnter={ show }
						onMouseLeave={ hide }
					>
						<Icon size={ 18 } strokeWidth={ 1.75 } aria-hidden="true" />
					</NavLink>
				}
			/>
			<PopoverContent side="right" align="start" sideOffset={ 10 } className="w-56 rounded-xl border border-border bg-popover p-1.5 text-popover-foreground shadow-lg" onMouseEnter={ show } onMouseLeave={ hide }>
				<div className="px-2.5 pb-1 pt-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">{ item.label }</div>
				<div className="flex flex-col gap-0.5">
					{ kids.map( ( { sub, proLocked } ) =>
						proLocked ? (
							<button
								key={ sub.id }
								type="button"
								onClick={ () => openUpsell( sub.label ) }
								className="flex w-full items-center justify-between gap-2 rounded-lg px-2.5 py-2 text-left text-sm font-normal text-foreground outline-none transition-colors hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring"
							>
								<span className="truncate">{ sub.label }</span>
								<ProBadge />
							</button>
						) : (
							<NavLink
								key={ sub.id }
								to={ sub.to }
								end
								viewTransition
								className={ ( { isActive } ) =>
									[
										'block rounded-lg px-2.5 py-2 text-sm outline-none transition-colors focus-visible:ring-2 focus-visible:ring-ring',
										isActive
											? 'bg-primary/10 font-medium text-primary'
											: 'font-normal text-muted-foreground hover:bg-muted hover:text-foreground',
									].join( ' ' )
								}
							>
								{ sub.label }
							</NavLink>
						)
					) }
				</div>
			</PopoverContent>
		</Popover>
	);
}

interface SectionProps {
	readonly item:     NavItem;
	readonly Icon:     LucideIcon;
	readonly active:   boolean;
	readonly open:     boolean;
	readonly onToggle: () => void;
}

/** A top-level item with children — accordion: header link + indented children. */
function SidebarSection( { item, Icon, active, open, onToggle }: SectionProps ): JSX.Element {
	const { childrenOf, openUpsell } = useNavMenu();
	const kids = childrenOf( item );

	return (
		<div>
			<div className={ [ rowClass( active ), 'pr-1.5' ].join( ' ' ) }>
				<NavLink
					to={ item.path }
					viewTransition
					className="flex min-w-0 flex-1 items-center gap-3 text-inherit no-underline"
				>
					<Icon size={ 18 } strokeWidth={ 1.75 } aria-hidden="true" />
					<span className="flex-1 truncate">{ item.label }</span>
				</NavLink>
				<button
					type="button"
					onClick={ onToggle }
					aria-expanded={ open }
					aria-label={ open ? 'Collapse' : 'Expand' }
					className="-mr-1 rounded p-1 text-muted-foreground transition-colors hover:text-foreground"
				>
					<ChevronDown
						size={ 16 }
						strokeWidth={ 1.75 }
						aria-hidden="true"
						className={ open ? 'rotate-180 transition-transform' : 'transition-transform' }
					/>
				</button>
			</div>

			{ open ? (
				<div className="mt-0.5 flex flex-col gap-0.5">
					{ kids.map( ( { sub, proLocked } ) =>
						proLocked ? (
							<button
								key={ sub.id }
								type="button"
								onClick={ () => openUpsell( sub.label ) }
								className="flex w-full items-center justify-between gap-2 rounded-md py-1.5 pl-11 pr-3 text-left text-sm font-normal text-foreground transition-colors hover:bg-muted"
							>
								<span className="truncate">{ sub.label }</span>
								<ProBadge />
							</button>
						) : (
							<NavLink
								key={ sub.id }
								to={ sub.to }
								end
								viewTransition
								className={ ( { isActive } ) =>
									[
										'block rounded-md py-1.5 pl-11 pr-3 text-sm transition-colors',
										isActive
											? 'font-medium text-primary'
											: 'font-normal text-muted-foreground hover:bg-muted hover:text-foreground',
									].join( ' ' )
								}
							>
								{ sub.label }
							</NavLink>
						)
					) }
				</div>
			) : null }
		</div>
	);
}
