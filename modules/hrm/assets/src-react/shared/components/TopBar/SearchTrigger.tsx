/**
 * Top-bar global search — a command-palette over `GET /erp/v2/search`.
 *
 * Opens on click or ⌘K / Ctrl+K. Results (employees, departments, designations)
 * are fetched server-side and debounced via `useGlobalSearch`; this component
 * owns the palette UI, keyboard navigation (↑/↓/↵), and routing on select.
 *
 * Built from primitives (Dialog + Input) rather than plugin-ui's cmdk `Command`
 * wrapper, which isn't exported from the package entry. Navigations are plain
 * (no `viewTransition`) because the targets are lazy routes that would abort the
 * transition while their chunk loads.
 */

import {
	Avatar,
	AvatarFallback,
	AvatarImage,
	Dialog,
	DialogContent,
	DialogTitle,
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuItem,
	DropdownMenuTrigger,
	Input,
} from '@wedevs/plugin-ui';
import { Briefcase, Building2, Check, ChevronDown, CornerDownLeft, Search, Users } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import type { ComponentType, JSX, SVGProps } from 'react';
import { useNavigate } from 'react-router-dom';

import { __ } from '@/shared/i18n';

import type { SearchHit } from './useGlobalSearch';
import { useGlobalSearch } from './useGlobalSearch';

type LucideIcon = ComponentType< SVGProps< SVGSVGElement > & { size?: number; strokeWidth?: number } >;

type HitKind = 'employee' | 'department' | 'designation';

/** Scope the palette to one result kind (or all). */
type Scope = 'all' | HitKind;

interface FlatItem {
	readonly key:  string;
	readonly kind: HitKind;
	readonly hit:  SearchHit;
	readonly path: string;
}

interface Group {
	readonly kind:  HitKind;
	readonly label: string;
	readonly Icon:  LucideIcon;
	readonly hits:  readonly SearchHit[];
}

function initials( name: string ): string {
	const parts = name.trim().split( /\s+/ ).filter( Boolean );
	const first = parts[ 0 ]?.[ 0 ] ?? '';
	const last  = parts.length > 1 ? parts[ parts.length - 1 ]?.[ 0 ] ?? '' : '';
	return ( first + last ).toUpperCase() || '?';
}

function pathFor( kind: HitKind, id: number ): string {
	if ( kind === 'employee' ) {
		return `/employees/${ id }`;
	}
	if ( kind === 'department' ) {
		return '/departments';
	}
	return '/designations';
}

export function SearchTrigger(): JSX.Element {
	const navigate = useNavigate();
	const [ open, setOpen ]   = useState( false );
	const [ query, setQuery ] = useState( '' );
	const [ active, setActive ] = useState( 0 );
	const [ scope, setScope ] = useState< Scope >( 'all' );
	const listRef = useRef< HTMLDivElement >( null );

	const scopeOptions: readonly { value: Scope; label: string }[] = useMemo(
		() => [
			{ value: 'all', label: __( 'All', 'erp' ) },
			{ value: 'employee', label: __( 'Employees', 'erp' ) },
			{ value: 'department', label: __( 'Departments', 'erp' ) },
			{ value: 'designation', label: __( 'Designations', 'erp' ) },
		],
		[]
	);
	const scopeLabel = scopeOptions.find( ( s ) => s.value === scope )?.label ?? '';

	const { results, loading } = useGlobalSearch( query, open );

	// Global ⌘K / Ctrl+K toggle.
	useEffect( () => {
		const onKey = ( e: KeyboardEvent ) => {
			if ( ( e.metaKey || e.ctrlKey ) && e.key.toLowerCase() === 'k' ) {
				e.preventDefault();
				setOpen( ( prev ) => ! prev );
			}
		};
		document.addEventListener( 'keydown', onKey );
		return () => document.removeEventListener( 'keydown', onKey );
	}, [] );

	const groups = useMemo< Group[] >(
		() =>
			(
				[
					{ kind: 'employee', label: __( 'Employees', 'erp' ), Icon: Users, hits: results.employees },
					{ kind: 'department', label: __( 'Departments', 'erp' ), Icon: Building2, hits: results.departments },
					{ kind: 'designation', label: __( 'Designations', 'erp' ), Icon: Briefcase, hits: results.designations },
				] as const
			).filter( ( g ) => g.hits.length > 0 && ( scope === 'all' || g.kind === scope ) ),
		[ results, scope ]
	);

	const flat = useMemo< FlatItem[] >(
		() =>
			groups.flatMap( ( g ) =>
				g.hits.map( ( hit ) => ( {
					key:  `${ g.kind }-${ hit.id }`,
					kind: g.kind,
					hit,
					path: pathFor( g.kind, hit.id ),
				} ) )
			),
		[ groups ]
	);

	// Keep the active index in range as results change.
	useEffect( () => {
		setActive( 0 );
	}, [ query, scope ] );

	function close(): void {
		setOpen( false );
		setQuery( '' );
		setActive( 0 );
	}

	function select( item: FlatItem | undefined ): void {
		if ( ! item ) {
			return;
		}
		close();
		navigate( item.path );
	}

	function onInputKeyDown( e: React.KeyboardEvent< HTMLInputElement > ): void {
		if ( flat.length === 0 ) {
			return;
		}
		if ( e.key === 'ArrowDown' ) {
			e.preventDefault();
			setActive( ( i ) => ( i + 1 ) % flat.length );
		} else if ( e.key === 'ArrowUp' ) {
			e.preventDefault();
			setActive( ( i ) => ( i - 1 + flat.length ) % flat.length );
		} else if ( e.key === 'Enter' ) {
			e.preventDefault();
			select( flat[ active ] );
		}
	}

	// Scroll the active row into view on keyboard nav.
	useEffect( () => {
		const el = listRef.current?.querySelector< HTMLElement >( `[data-idx="${ active }"]` );
		el?.scrollIntoView( { block: 'nearest' } );
	}, [ active ] );

	const trimmed   = query.trim();
	const showEmpty = trimmed.length >= 2 && ! loading && flat.length === 0;

	let flatIndex = -1;

	return (
		<>
			<button
				type="button"
				onClick={ () => setOpen( true ) }
				aria-label={ __( 'Search', 'erp' ) }
				className="inline-flex size-9 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
			>
				<Search size={ 16 } strokeWidth={ 1.9 } aria-hidden="true" />
			</button>

			<Dialog open={ open } onOpenChange={ ( next ) => ( next ? setOpen( true ) : close() ) }>
				<DialogContent
					showCloseButton={ false }
					className="gap-0 overflow-hidden rounded-xl p-0 sm:max-w-xl"
				>
					<DialogTitle className="sr-only">{ __( 'Search', 'erp' ) }</DialogTitle>

					<div className="flex items-center gap-2 border-b border-border px-4">
						<Search size={ 18 } strokeWidth={ 1.9 } className="shrink-0 text-muted-foreground" aria-hidden="true" />
						<Input
							autoFocus
							value={ query }
							onChange={ ( e ) => setQuery( e.target.value ) }
							onKeyDown={ onInputKeyDown }
							placeholder={ __( 'Search employees, departments, designations…', 'erp' ) }
							className="h-12 border-0 bg-transparent px-0 shadow-none focus-visible:ring-0"
						/>
					</div>

					<div className="flex items-center gap-1.5 border-b border-border px-4 py-2 text-xs text-muted-foreground">
						<span>{ __( 'Filter:', 'erp' ) }</span>
						<DropdownMenu>
							<DropdownMenuTrigger
								render={
									<button
										type="button"
										className="inline-flex items-center gap-1 rounded px-1 py-0.5 font-semibold text-foreground outline-none transition-colors hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring"
										aria-label={ __( 'Filter search results', 'erp' ) }
									>
										{ scopeLabel }
										<ChevronDown size={ 13 } strokeWidth={ 2 } aria-hidden="true" />
									</button>
								}
							/>
							<DropdownMenuContent align="start" className="min-w-40">
								{ scopeOptions.map( ( s ) => (
									<DropdownMenuItem
										key={ s.value }
										className="justify-between gap-2"
										onClick={ () => setScope( s.value ) }
									>
										{ s.label }
										{ scope === s.value ? (
											<Check size={ 14 } strokeWidth={ 2 } aria-hidden="true" />
										) : null }
									</DropdownMenuItem>
								) ) }
							</DropdownMenuContent>
						</DropdownMenu>
					</div>

					<div ref={ listRef } className="max-h-88 overflow-y-auto p-2">
						{ trimmed.length < 2 ? (
							<p className="px-3 py-6 text-center text-sm text-muted-foreground">
								{ __( 'Type at least 2 characters to search.', 'erp' ) }
							</p>
						) : loading && flat.length === 0 ? (
							<p className="px-3 py-6 text-center text-sm text-muted-foreground">
								{ __( 'Searching…', 'erp' ) }
							</p>
						) : showEmpty ? (
							<p className="px-3 py-6 text-center text-sm text-muted-foreground">
								{ __( 'No matches found.', 'erp' ) }
							</p>
						) : (
							groups.map( ( group ) => (
								<div key={ group.kind } className="mb-1 last:mb-0">
									<p className="flex items-center gap-1.5 px-3 pb-1 pt-2 text-xs font-medium text-muted-foreground">
										<group.Icon size={ 13 } strokeWidth={ 2 } aria-hidden="true" />
										{ group.label }
									</p>
									{ group.hits.map( ( hit ) => {
										flatIndex += 1;
										const idx = flatIndex;
										const isActive = idx === active;
										return (
											<button
												key={ `${ group.kind }-${ hit.id }` }
												type="button"
												data-idx={ idx }
												onMouseMove={ () => setActive( idx ) }
												onClick={ () => select( flat[ idx ] ) }
												className={ [
													'flex w-full items-center gap-3 rounded-md px-3 py-2 text-left transition-colors',
													isActive ? 'bg-accent text-accent-foreground' : 'text-foreground hover:bg-muted',
												].join( ' ' ) }
											>
												{ group.kind === 'employee' ? (
													<Avatar className="size-7 shrink-0">
														{ hit.avatar ? <AvatarImage src={ hit.avatar } alt="" /> : null }
														<AvatarFallback className="bg-primary/10 text-[10px] font-medium text-primary">
															{ initials( hit.label ) }
														</AvatarFallback>
													</Avatar>
												) : (
													<span className="flex size-7 shrink-0 items-center justify-center rounded-md bg-muted text-muted-foreground">
														<group.Icon size={ 14 } strokeWidth={ 2 } aria-hidden="true" />
													</span>
												) }
												<span className="min-w-0 flex-1">
													<span className="block truncate text-sm font-medium">{ hit.label }</span>
													{ hit.sublabel ? (
														<span className="block truncate text-xs text-muted-foreground">{ hit.sublabel }</span>
													) : null }
												</span>
												{ isActive ? (
													<CornerDownLeft size={ 14 } strokeWidth={ 2 } className="shrink-0 text-muted-foreground" aria-hidden="true" />
												) : null }
											</button>
										);
									} ) }
								</div>
							) )
						) }
					</div>
				</DialogContent>
			</Dialog>
		</>
	);
}
