/**
 * Data-bearing dashboard list rows + the generic pro-module widget renderer.
 * These read from the `GET /erp/v2/dashboard` payload; the layout primitives
 * they sit in (WidgetCard, PersonAvatar, EmptyRow) live in `DashboardCards.tsx`.
 */

import { Button } from '@wedevs/plugin-ui';
import {
	Banknote,
	Briefcase,
	CalendarCheck,
	Gift,
	Package,
	Wallet,
} from 'lucide-react';
import { Fragment } from 'react';
import type { JSX } from 'react';
import { Link } from 'react-router-dom';

import { __ } from '@/shared/i18n';

import { EmptyRow, PersonAvatar, WidgetCard } from './DashboardCards';
import { fmtDate, fmtDayMonth } from './format';
import type { LucideIcon } from './format';
import type {
	AboutToEndPerson,
	BirthdayPerson,
	DashboardProWidget,
	OnLeavePerson,
} from './types';

export function OnLeaveItem( {
	person,
}: {
	person: OnLeavePerson;
} ): JSX.Element {
	return (
		<li className="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-muted/50">
			<PersonAvatar name={ person.name } src={ person.avatar_url } />
			<div className="min-w-0 flex-1">
				<p className="flex items-center gap-1.5 truncate text-sm font-medium text-foreground">
					{ person.name }
					{ /* Half-day indicator (legacy Morning/Afternoon SVGs → pill). */ }
					{ person.day_status_id > 1 && person.day_status ? (
						<span className="inline-flex shrink-0 items-center rounded-full bg-muted px-1.5 py-0.5 text-[10px] font-medium uppercase tracking-wide text-muted-foreground">
							{ person.day_status }
						</span>
					) : null }
				</p>
				{ person.designation ? (
					<p className="truncate text-xs text-muted-foreground">
						{ person.designation }
					</p>
				) : null }
			</div>
			<span className="shrink-0 text-xs text-muted-foreground">
				{ `${ fmtDate( person.start_date ) } – ${ fmtDate(
					person.end_date
				) }` }
			</span>
		</li>
	);
}

export function AboutToEndItem( {
	person,
}: {
	person: AboutToEndPerson;
} ): JSX.Element {
	return (
		<li className="flex items-center justify-between gap-3 rounded-lg px-3 py-2 hover:bg-muted/50">
			<span className="min-w-0 truncate text-sm font-medium text-foreground">
				{ person.name }
			</span>
			<span className="shrink-0 text-xs text-muted-foreground">
				{ fmtDate( person.end_date ) }
			</span>
		</li>
	);
}

export function BirthdayItem( {
	person,
	today,
	canWish,
	wished,
	onWish,
}: {
	person: BirthdayPerson;
	today: boolean;
	canWish: boolean;
	wished: boolean;
	onWish: ( id: number ) => void;
} ): JSX.Element {
	return (
		<li className="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-muted/50">
			<PersonAvatar name={ person.name } src={ person.avatar_url } />
			<div className="min-w-0 flex-1">
				<p className="truncate text-sm font-medium text-foreground">
					{ person.name }
				</p>
				{ person.designation ? (
					<p className="truncate text-xs text-muted-foreground">
						{ person.designation }
					</p>
				) : null }
			</div>
			<span className="shrink-0 text-xs text-muted-foreground">
				{ today
					? __( 'Today 🎉', 'erp' )
					: fmtDayMonth( person.date_of_birth ) }
			</span>
			{ canWish ? (
				<Button
					variant="ghost"
					size="sm"
					className="h-8 gap-1.5 text-primary hover:text-primary"
					disabled={ wished || person.wished }
					onClick={ () => onWish( person.user_id ) }
				>
					<Gift size={ 14 } aria-hidden="true" />
					{ wished || person.wished ? __( 'Sent', 'erp' ) : __( 'Wish', 'erp' ) }
				</Button>
			) : null }
		</li>
	);
}

const PRO_WIDGET_ICONS: Readonly< Record< string, LucideIcon > > = {
	briefcase: Briefcase,
	package: Package,
	wallet: Wallet,
	'calendar-check': CalendarCheck,
	banknote: Banknote,
};

/**
 * Generic renderer for a pro-module dashboard widget (recruitment, assets,
 * reimbursement, attendance, payroll). Pro modules contribute these via the
 * `erp_hr_v2_dashboard` PHP filter; the free dashboard knows nothing about the
 * module — it just paints the stats row and/or item list it was handed.
 */
/** Colored status pill for item rows (Approved / Pending / Rejected …). */
const TONE_PILL: Readonly< Record< string, string > > = {
	success: 'bg-success/10 text-success',
	warning: 'bg-warning-light text-warning-on-light',
	destructive: 'bg-destructive/10 text-destructive',
	muted: 'bg-muted text-muted-foreground',
};

export function ProWidget( {
	widget,
}: {
	widget: DashboardProWidget;
} ): JSX.Element {
	const Icon =
		( widget.icon && PRO_WIDGET_ICONS[ widget.icon ] ) || Briefcase;
	const featured = widget.stats?.filter( ( s ) => s.featured ) ?? [];
	const stats = widget.stats?.filter( ( s ) => ! s.featured ) ?? [];
	const hasStats = ( widget.stats?.length ?? 0 ) > 0;
	const hasItems = ( widget.items?.length ?? 0 ) > 0;

	return (
		<WidgetCard
			icon={ Icon }
			title={ widget.title }
			action={
				widget.to
					? { label: __( 'View', 'erp' ), to: widget.to }
					: undefined
			}
		>
			{ /* Featured stat — a full-width highlighted box (e.g. Open Openings). */ }
			{ featured.map( ( s, i ) => (
				<div
					key={ `f-${ i }` }
					className="mx-2 mb-3 flex items-baseline gap-3 rounded-lg border border-border bg-card px-4 py-3.5"
				>
					<span className="text-2xl font-bold leading-7 text-primary">
						{ s.value }
					</span>
					<span className="text-sm text-muted-foreground">
						{ s.label }
					</span>
				</div>
			) ) }

			{ /* Regular stats. A small set (≤4) is ONE bordered box with an inset
			   divider between cells (Figma); a funnel (≥5) is separate tiles. */ }
			{ stats.length >= 5 ? (
				<div className="grid grid-cols-5 gap-3 p-2">
					{ stats.map( ( s, i ) => (
						<div
							key={ i }
							className="rounded-lg border border-border bg-card px-4 py-3.5 text-center"
						>
							<p className="text-2xl font-bold leading-7 text-primary">
								{ s.value }
							</p>
							<p className="truncate text-xs text-muted-foreground">
								{ s.label }
							</p>
						</div>
					) ) }
				</div>
			) : stats.length ? (
				<div className="mx-2 mb-2 flex items-stretch rounded-lg border border-border bg-card">
					{ stats.map( ( s, i ) => (
						<Fragment key={ i }>
							{ i > 0 ? (
								<div
									aria-hidden="true"
									className="my-3 w-px shrink-0 bg-border"
								/>
							) : null }
							<div className="min-w-0 flex-1 px-4 py-3.5">
								<p className="text-2xl font-bold leading-7 text-primary">
									{ s.value }
								</p>
								<p className="truncate text-xs text-muted-foreground">
									{ s.label }
								</p>
							</div>
						</Fragment>
					) ) }
				</div>
			) : null }

			{ /* Optional sub-heading above the list (e.g. "Recent Requests"). */ }
			{ hasItems && widget.itemsTitle ? (
				<p className="px-3 pt-2 pb-1 text-sm font-bold text-foreground">
					{ widget.itemsTitle }
				</p>
			) : null }

			{ hasItems ? (
				<ul>
					{ widget.items?.map( ( it, i ) => {
						const pillCls =
							( it.tone ? TONE_PILL[ it.tone ] : undefined ) ??
							'bg-muted text-muted-foreground';
						const row = (
							<>
								{ it.avatar_url !== undefined ? (
									<PersonAvatar
										name={ it.label }
										src={ it.avatar_url }
									/>
								) : null }
								<div className="min-w-0 flex-1">
									<p className="truncate text-sm font-medium text-foreground">
										{ it.label }
									</p>
									{ it.sub ? (
										<p className="truncate text-xs text-muted-foreground">
											{ it.sub }
										</p>
									) : null }
								</div>
								{ it.status ? (
									<span
										className={ `shrink-0 rounded-full px-2 py-0.5 text-xs font-medium ${ pillCls }` }
									>
										{ it.status }
									</span>
								) : null }
								{ it.meta ? (
									<span className="shrink-0 text-xs text-muted-foreground">
										{ it.meta }
									</span>
								) : null }
							</>
						);
						return (
							<li key={ i }>
								{ it.to ? (
									<Link
										to={ it.to }
										viewTransition
										className="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-muted/50"
									>
										{ row }
									</Link>
								) : (
									<div className="flex items-center gap-3 rounded-lg px-3 py-2">
										{ row }
									</div>
								) }
							</li>
						);
					} ) }
				</ul>
			) : null }

			{ ! hasStats && ! hasItems ? (
				<EmptyRow
					text={ widget.empty ?? __( 'Nothing to show.', 'erp' ) }
				/>
			) : null }
		</WidgetCard>
	);
}
