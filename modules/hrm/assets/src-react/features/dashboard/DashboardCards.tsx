/**
 * Presentational building blocks for the dashboard grid: the live clock, the
 * avatar, the stat card, the widget card shell, and the small section/empty
 * helpers. All are layout-only — data-bearing widgets live in
 * `DashboardWidgets.tsx`.
 */

import { Avatar, AvatarFallback, AvatarImage } from '@wedevs/plugin-ui';
import { ArrowRight } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { JSX } from 'react';
import { Link } from 'react-router-dom';

import { initials } from './format';
import type { LucideIcon } from './format';

/** Live wall-clock that re-renders every second. */
export function LiveTime(): JSX.Element {
	const [ now, setNow ] = useState( () => new Date() );
	useEffect( () => {
		const id = window.setInterval( () => setNow( new Date() ), 1000 );
		return () => window.clearInterval( id );
	}, [] );
	return (
		<span className="font-medium tabular-nums text-foreground">
			{ now.toLocaleTimeString( undefined, {
				hour: '2-digit',
				minute: '2-digit',
				second: '2-digit',
			} ) }
		</span>
	);
}

export function PersonAvatar( {
	name,
	src,
	size = 'size-9',
}: {
	name: string;
	src: string;
	size?: string;
} ): JSX.Element {
	return (
		<Avatar className={ `${ size } shrink-0` }>
			{ src ? <AvatarImage src={ src } alt={ name } /> : null }
			<AvatarFallback className="bg-primary/10 text-xs font-medium text-primary">
				{ initials( name ) }
			</AvatarFallback>
		</Avatar>
	);
}

interface StatCardProps {
	readonly icon: LucideIcon;
	readonly label: string;
	readonly value: number;
	readonly tint: string;
	readonly to?: string;
}

export function StatCard( {
	icon: Icon,
	label,
	value,
	tint,
	to,
}: StatCardProps ): JSX.Element {
	const body = (
		<div className="group relative flex items-center gap-4 overflow-hidden rounded-[10px] bg-card p-5 shadow-sm ring-1 ring-border/40 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:ring-primary/30">
			<span
				className={ `inline-flex size-12 shrink-0 items-center justify-center rounded-xl shadow-sm transition-transform duration-200 group-hover:scale-105 ${ tint }` }
			>
				<Icon size={ 22 } strokeWidth={ 1.9 } aria-hidden="true" />
			</span>
			<div className="min-w-0">
				<p className="text-3xl font-bold leading-8 text-foreground tabular-nums">
					{ value }
				</p>
				<p className="truncate text-sm text-muted-foreground">
					{ label }
				</p>
			</div>
			{ to ? (
				<ArrowRight
					size={ 16 }
					className="ml-auto shrink-0 text-muted-foreground/40 transition-all duration-200 group-hover:translate-x-0.5 group-hover:text-primary"
					aria-hidden="true"
				/>
			) : null }
		</div>
	);
	return to ? (
		<Link
			to={ to }
			viewTransition
			className="group block rounded-[10px] focus:outline-none focus-visible:ring-2 focus-visible:ring-ring"
		>
			{ body }
		</Link>
	) : (
		body
	);
}

interface WidgetCardProps {
	readonly icon: LucideIcon;
	readonly title: string;
	readonly count?: number | undefined;
	readonly action?: { label: string; to: string } | undefined;
	readonly children: React.ReactNode;
}

export function WidgetCard( {
	icon: Icon,
	title,
	count,
	action,
	children,
}: WidgetCardProps ): JSX.Element {
	return (
		<section className="flex flex-col rounded-[10px] bg-card shadow-sm">
			<header className="flex items-center justify-between gap-3 border-b border-border px-6 py-4">
				<h2 className="flex items-center gap-2 text-base font-bold leading-tight tracking-tight text-foreground">
					<span className="inline-flex size-7 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground">
						<Icon
							size={ 16 }
							strokeWidth={ 2 }
							aria-hidden="true"
						/>
					</span>
					{ title }
					{ count && count > 0 ? (
						<span className="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-muted px-1.5 text-xs font-medium text-muted-foreground">
							{ count }
						</span>
					) : null }
				</h2>
				{ action ? (
					<Link
						to={ action.to }
						viewTransition
						className="inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline"
					>
						{ action.label }
						<ArrowRight size={ 13 } aria-hidden="true" />
					</Link>
				) : null }
			</header>
			{ /* Scroll long lists inside the card instead of stretching the page. */ }
			<div className="max-h-80 flex-1 overflow-y-auto p-2">
				{ children }
			</div>
		</section>
	);
}

export function EmptyRow( { text }: { text: string } ): JSX.Element {
	return (
		<p className="px-3 py-6 text-center text-sm text-muted-foreground">
			{ text }
		</p>
	);
}

/** Small uppercase divider label that groups the dashboard sections. */
export function SectionLabel( {
	children,
	className,
}: {
	children: React.ReactNode;
	className?: string;
} ): JSX.Element {
	return (
		<h2
			className={ `mb-3 text-xs font-semibold uppercase tracking-wider text-muted-foreground ${
				className ?? ''
			}` }
		>
			{ children }
		</h2>
	);
}
