/**
 * Vertical sidebar nav — the sidebar-layout counterpart of `TopBar/NavLinks`.
 *
 * Consumes the SAME `useNavMenu()` runtime (identical capability + pro-module
 * gating), so it can never show a different set of items than the top bar.
 * Dropdown items render as inline accordions (children indented below the
 * parent) instead of hover flyouts — the active section auto-expands.
 *
 * Reference: Figma HRM-Redesign-2024 node 1511:29973 (sidebar layout).
 */

import { ChevronDown } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { JSX } from 'react';
import { NavLink } from 'react-router-dom';

import { ProBadge } from '@/shared/components/pro/ProUpsell';
import { useNavMenu } from '@/shared/components/nav/nav-runtime';
import type { LucideIcon } from '@/shared/components/nav/nav-runtime';
import type { NavItem } from '@/shared/components/TopBar/nav-items';

const ROW_BASE =
	'group flex w-full items-center gap-3 rounded-md px-3 py-2 text-left text-sm transition-colors';

function rowClass( active: boolean ): string {
	return [
		ROW_BASE,
		active
			? 'bg-primary/10 font-medium text-primary'
			: 'font-normal text-foreground hover:bg-muted',
	].join( ' ' );
}

export function SidebarNav(): JSX.Element {
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
		<nav aria-label="HR sections" className="flex flex-col gap-0.5 px-3 py-3">
			{ entries.map( ( { item, Icon, proLocked, hasMenu } ) => {
				if ( proLocked ) {
					return (
						<button
							key={ item.id }
							type="button"
							onClick={ () => openUpsell( item.label ) }
							className={ rowClass( false ) }
						>
							<Icon size={ 18 } strokeWidth={ 1.75 } aria-hidden="true" />
							<span className="flex-1 truncate">{ item.label }</span>
							<ProBadge />
						</button>
					);
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
	readonly item: NavItem;
	readonly Icon: LucideIcon;
}

/** A childless top-level item — a plain route link. */
function SidebarLeaf( { item, Icon }: LeafProps ): JSX.Element {
	return (
		<NavLink
			to={ item.path }
			end={ item.path === '/' }
			viewTransition
			className={ ( { isActive } ) => rowClass( isActive ) }
		>
			<Icon size={ 18 } strokeWidth={ 1.75 } aria-hidden="true" />
			<span className="flex-1 truncate">{ item.label }</span>
		</NavLink>
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
