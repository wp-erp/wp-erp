/**
 * Dashboard "Calendar" card — a medium month grid with named leave / holiday
 * chips inside each day cell (Figma HRM-Redesign-2024 dashboard calendar). It
 * reads the same `erp/v2/leave-calendar` data as the full `/leave/calendar`
 * page (own scope); each day shows up to two event chips + a "+N more" overflow
 * that the hover tooltip expands. "View" opens the full calendar.
 */

import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@wedevs/plugin-ui';
import { ChevronLeft, ChevronRight, ArrowUpRight, Plus } from 'lucide-react';
import { useSelect } from '@wordpress/data';
import { useMemo, useState } from 'react';
import type { JSX } from 'react';
import { Link } from 'react-router-dom';

import { useCan } from '@/shared/hooks/useCan';
import { useModalParam } from '@/shared/useModalParam';
import { __, sprintf } from '@/shared/i18n';
import { storeName as meStoreName } from '@/stores/me';
import type { MeUser } from '@/stores/me/types';
import type { CalendarEvent } from '@/features/leave-calendar/types';
import { LeaveChip } from '@/features/leave-calendar/CalendarGrid';
import { useLeaveCalendar } from '@/features/leave-calendar/useLeaveCalendar';
import { NewLeaveRequestDialog } from '@/features/leave-requests/NewLeaveRequestDialog';

const WEEKDAYS = [ 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' ];

const HOLIDAY_COLOR = '#FF5354';

/** Diagonal hatch for weekend columns (Figma) — drawn off the border token so
 * it adapts to light/dark instead of a hardcoded gray. */
const WEEKEND_HATCH = {
	backgroundImage:
		'repeating-linear-gradient(-45deg, color-mix(in srgb, var(--border) 65%, transparent) 0, color-mix(in srgb, var(--border) 65%, transparent) 1px, transparent 1px, transparent 7px)',
} as const;

function ymd( date: Date ): string {
	const y = date.getFullYear();
	const m = String( date.getMonth() + 1 ).padStart( 2, '0' );
	const d = String( date.getDate() ).padStart( 2, '0' );
	return `${ y }-${ m }-${ d }`;
}
function parseYmd( value: string ): Date {
	const [ y = 1970, m = 1, d = 1 ] = value.split( '-' ).map( ( n ) => parseInt( n, 10 ) );
	return new Date( y, m - 1, d );
}
function addDays( date: Date, days: number ): Date {
	const next = new Date( date );
	next.setDate( next.getDate() + days );
	return next;
}

interface DayBucket {
	leaves:   CalendarEvent[];
	holidays: CalendarEvent[];
	weekend:  boolean;
}

/**
 * Human label for a single event row in the day tooltip.
 *
 * Manager (team) scope leads with WHO is out ("Jane Doe — Casual Leave"); the
 * own ("me") scope leads with the policy label only, since it is always you.
 */
function eventLabel( ev: CalendarEvent, teamScope: boolean ): string {
	if ( ev.type !== 'leave' ) {
		return ev.title || __( 'Holiday', 'erp' );
	}
	const policy = ev.title || __( 'Leave', 'erp' );
	const name   = teamScope && ev.employee_name ? `${ ev.employee_name } — ${ policy }` : policy;
	return ev.reason ? `${ name } — ${ ev.reason }` : name;
}

/** A Monday-first grid offset for a given weekday index (0 = Sun). */
function mondayOffset( day: number ): number {
	return ( day + 6 ) % 7;
}

export function MiniCalendarWidget(): JSX.Element {
	const [ cursor, setCursor ] = useState( () => {
		const now = new Date();
		return new Date( now.getFullYear(), now.getMonth(), 1 );
	} );

	// Match the full /leave/calendar page: HR managers see EVERY employee's
	// leave (team scope), regular employees only their own.
	const canManageLeave = useCan( 'erp_leave_manage' );
	const scope: 'me' | 'all' = canManageLeave ? 'all' : 'me';

	// Self-service "Take a Leave" (mirrors the legacy dashboard button): shown to
	// everyone — the form is locked to the current user and the create endpoint
	// enforces the self-only `erp_leave_create_request` meta-cap (employees can
	// request their own leave even though they lack the primitive manager cap).
	const [ takeLeave, setTakeLeave ] = useModalParam( 'take-leave' );
	const currentUserId = useSelect(
		( select ) => ( select( meStoreName ) as { getUser: () => MeUser | null } ).getUser()?.id ?? 0,
		[],
	);

	// Monday-first window covering the visible month. The row count is dynamic
	// (5 or 6 weeks) so a month that fits in 5 rows shows no empty trailing week
	// — matching the Figma calendar.
	const gridStart = useMemo( () => addDays( cursor, -mondayOffset( cursor.getDay() ) ), [ cursor ] );
	const daysInMonth = useMemo( () => new Date( cursor.getFullYear(), cursor.getMonth() + 1, 0 ).getDate(), [ cursor ] );
	const weekCount = useMemo( () => Math.ceil( ( mondayOffset( cursor.getDay() ) + daysInMonth ) / 7 ), [ cursor, daysInMonth ] );
	const { events, loading, error } = useLeaveCalendar( ymd( gridStart ), ymd( addDays( gridStart, weekCount * 7 - 1 ) ), { scope } );

	const byDay = useMemo( () => {
		const map = new Map< string, DayBucket >();
		const ensure = ( key: string ): DayBucket => {
			let b = map.get( key );
			if ( ! b ) {
				b = { leaves: [], holidays: [], weekend: false };
				map.set( key, b );
			}
			return b;
		};
		events.forEach( ( ev: CalendarEvent ) => {
			if ( ! ev.start ) {
				return;
			}
			const from = parseYmd( ev.start );
			const to   = ev.end ? parseYmd( ev.end ) : from;
			for ( let d = from; d <= to; d = addDays( d, 1 ) ) {
				const b = ensure( ymd( d ) );
				if ( ev.type === 'weekend' ) {
					b.weekend = true;
				} else if ( ev.type === 'holiday' ) {
					b.holidays.push( ev );
				} else {
					b.leaves.push( ev );
				}
			}
		} );
		return map;
	}, [ events ] );

	const days = useMemo(
		() => Array.from( { length: weekCount * 7 }, ( _, i ) => addDays( gridStart, i ) ),
		[ gridStart, weekCount ],
	);

	const monthLabel = cursor.toLocaleDateString( undefined, { month: 'long', year: 'numeric' } );
	const todayKey   = ymd( new Date() );
	const thisMonth  = cursor.getMonth();

	function shiftMonth( delta: number ): void {
		setCursor( ( p ) => new Date( p.getFullYear(), p.getMonth() + delta, 1 ) );
	}

	return (
		<>
		<section className="flex flex-col rounded-lg bg-card shadow-sm ring-1 ring-border/40">
			{ /* Header: title + Today / month nav (Figma) + View shortcut. */ }
			<header className="flex flex-wrap items-center justify-between gap-3 px-6 pt-6 pb-4">
				<h2 className="m-0 text-base font-semibold leading-none tracking-normal text-foreground">
					{ canManageLeave ? __( 'Team Calendar', 'erp' ) : __( 'My Calendar', 'erp' ) }
				</h2>
				<div className="flex items-center gap-2">
					{ /* Take a Leave — self-service leave request (legacy dashboard
					   leave-calendar had this button); opens the form locked to the
					   current user. */ }
					<button
						type="button"
						onClick={ () => setTakeLeave( 'new' ) }
						className="inline-flex h-8 items-center gap-1.5 rounded-md bg-primary px-3 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
					>
						<Plus size={ 15 } aria-hidden="true" />
						{ __( 'Take a Leave', 'erp' ) }
					</button>
					<button
						type="button"
						onClick={ () =>
							setCursor( () => {
								const n = new Date();
								return new Date( n.getFullYear(), n.getMonth(), 1 );
							} )
						}
						className="inline-flex h-8 items-center rounded-md border border-border bg-card px-3 text-sm font-medium text-foreground transition-colors hover:bg-muted"
					>
						{ __( 'Today', 'erp' ) }
					</button>
					<div className="inline-flex items-center rounded-md border border-border">
						<button
							type="button"
							onClick={ () => shiftMonth( -1 ) }
							className="inline-flex size-8 items-center justify-center rounded-l-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
							aria-label={ __( 'Previous month', 'erp' ) }
						>
							<ChevronLeft size={ 16 } aria-hidden="true" />
						</button>
						<span className="px-2 text-center text-sm font-medium whitespace-nowrap text-foreground">{ monthLabel }</span>
						<button
							type="button"
							onClick={ () => shiftMonth( 1 ) }
							className="inline-flex size-8 items-center justify-center rounded-r-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
							aria-label={ __( 'Next month', 'erp' ) }
						>
							<ChevronRight size={ 16 } aria-hidden="true" />
						</button>
					</div>
					{ /* View shortcut — pushed to the far right end of the header. */ }
					<Link
						to="/leave/calendar"
						viewTransition
						className="ml-1 inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline"
					>
						{ __( 'View', 'erp' ) }
						<ArrowUpRight size={ 13 } aria-hidden="true" />
					</Link>
				</div>
			</header>

			<div className="relative px-6 pb-6">
				{ loading ? (
					<div className="absolute inset-0 z-10 flex items-center justify-center bg-card/60 text-xs text-muted-foreground">
						{ __( 'Loading…', 'erp' ) }
					</div>
				) : null }

				{ error ? (
					<p className="py-6 text-center text-sm text-destructive">{ error }</p>
				) : (
					<>
						{ /* Bordered month grid (Figma): weekday header row + tall day
						   cells, weekend columns hatched, today a filled blue circle.
						   Days with leaves/holidays keep the event dots + hover tooltip. */ }
						<TooltipProvider>
							<div className="grid grid-cols-7 overflow-hidden rounded-lg border-l border-t border-border">
								{ WEEKDAYS.map( ( wd, i ) => (
									<div
										key={ wd }
										className={ [
											'border-b border-r border-border py-2 text-center text-xs font-medium text-muted-foreground',
											i >= 5 ? 'bg-muted/40' : '',
										].join( ' ' ) }
									>
										{ wd }
									</div>
								) ) }
								{ days.map( ( day ) => {
									const key      = ymd( day );
									const bucket   = byDay.get( key );
									const inMonth  = day.getMonth() === thisMonth;
									const isToday  = key === todayKey;
									const dow      = day.getDay();
									const weekend  = dow === 0 || dow === 6;
									const leaves   = bucket?.leaves ?? [];
									const holidays = bucket?.holidays ?? [];
									const hasEvents = leaves.length > 0 || holidays.length > 0;
									// Named events as chips inside the cell (Figma medium
									// calendar): holidays first, then leaves; up to two, with
									// a "+N more" overflow that the hover tooltip expands.
									const chips = [
										...holidays.map( ( ev ) => ( { ev, holiday: true } ) ),
										...leaves.map( ( ev ) => ( { ev, holiday: false } ) ),
									];
									const extra = chips.length - 2;

									const cell = (
										<div
											className="flex min-h-24 flex-col items-center gap-1 border-b border-r border-border p-2"
											style={ weekend ? WEEKEND_HATCH : undefined }
										>
											<span
												className={ [
													'inline-flex size-8 items-center justify-center rounded-full text-sm',
													isToday
														? 'bg-primary font-semibold text-primary-foreground'
														: inMonth
															? 'text-foreground'
															: 'text-muted-foreground/60',
													hasEvents && ! isToday ? 'cursor-default font-medium' : '',
												].join( ' ' ) }
											>
												{ day.getDate() }
											</span>
											{ chips.length ? (
												<span className="flex w-full flex-col gap-0.5 text-left">
													{ chips.slice( 0, 2 ).map( ( { ev, holiday }, i ) =>
														holiday ? (
															<span
																key={ i }
																className="truncate rounded px-1.5 py-0.5 text-[11px] font-medium text-white"
																style={ { backgroundColor: ev.color || HOLIDAY_COLOR } }
																title={ ev.title }
															>
																{ ev.title }
															</span>
														) : (
															<LeaveChip key={ i } ev={ ev } />
														)
													) }
													{ extra > 0 ? (
														<span className="px-1 text-[11px] font-medium text-muted-foreground">
															{ sprintf( __( '+%d more', 'erp' ), extra ) }
														</span>
													) : null }
												</span>
											) : null }
										</div>
									);

									if ( ! hasEvents ) {
										return <div key={ key }>{ cell }</div>;
									}

									return (
										<Tooltip key={ key }>
											<TooltipTrigger render={ cell } />
											<TooltipContent className="max-w-64">
												<div className="space-y-1.5">
													<p className="text-xs font-semibold">
														{ day.toLocaleDateString( undefined, { weekday: 'short', month: 'short', day: 'numeric' } ) }
													</p>
													<ul className="space-y-1">
														{ holidays.map( ( ev, i ) => (
															<li key={ `h-${ i }` } className="flex items-center gap-1.5 text-xs">
																<span aria-hidden="true" className="inline-block size-2 shrink-0 rounded-full" style={ { backgroundColor: HOLIDAY_COLOR } } />
																<span className="truncate">{ eventLabel( ev, scope === 'all' ) }</span>
															</li>
														) ) }
														{ leaves.map( ( ev, i ) => (
															<li key={ `l-${ i }` } className="flex items-center gap-1.5 text-xs">
																<span aria-hidden="true" className="inline-block size-2 shrink-0 rounded-full bg-primary" />
																<span className="truncate">{ eventLabel( ev, scope === 'all' ) }</span>
															</li>
														) ) }
													</ul>
												</div>
											</TooltipContent>
										</Tooltip>
									);
								} ) }
							</div>
						</TooltipProvider>

						{ /* Legend for the event dots. */ }
						<div className="mt-3 flex items-center justify-center gap-4 text-[11px] text-muted-foreground">
							<span className="inline-flex items-center gap-1.5">
								<span aria-hidden="true" className="inline-block size-2 rounded-full bg-primary" />
								{ __( 'Leave', 'erp' ) }
							</span>
							<span className="inline-flex items-center gap-1.5">
								<span aria-hidden="true" className="inline-block size-2 rounded-full" style={ { backgroundColor: HOLIDAY_COLOR } } />
								{ __( 'Holiday', 'erp' ) }
							</span>
						</div>
					</>
				) }
			</div>
		</section>
		<NewLeaveRequestDialog
			open={ takeLeave !== null }
			onClose={ () => setTakeLeave( null ) }
			onSubmitted={ () => setTakeLeave( null ) }
			lockEmployeeId={ currentUserId }
		/>
		</>
	);
}
