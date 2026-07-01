/**
 * Compact dashboard "Calendar" card — a small month grid with a colored event
 * dot under each day that has a leave / holiday (Figma HRM-Redesign-2024
 * dashboard calendar). It reads the same `erp/v2/leave-calendar` data as the
 * full `/leave/calendar` page (own scope) but stays glanceable: just the day
 * number + up to three dots, no name chips. "View" opens the full calendar.
 */

import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@wedevs/plugin-ui';
import { ChevronLeft, ChevronRight, ArrowUpRight } from 'lucide-react';
import { useMemo, useState } from 'react';
import type { JSX } from 'react';
import { Link } from 'react-router-dom';

import { useCan } from '@/shared/hooks/useCan';
import { __ } from '@/shared/i18n';
import type { CalendarEvent } from '@/features/leave-calendar/types';
import { useLeaveCalendar } from '@/features/leave-calendar/useLeaveCalendar';

const WEEKDAYS = [ 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' ];

const HOLIDAY_COLOR = '#FF5354';

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

	// Monday-first 6-week window covering the visible month.
	const gridStart = useMemo( () => addDays( cursor, -mondayOffset( cursor.getDay() ) ), [ cursor ] );
	const { events, loading, error } = useLeaveCalendar( ymd( gridStart ), ymd( addDays( gridStart, 41 ) ), { scope } );

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
		() => Array.from( { length: 42 }, ( _, i ) => addDays( gridStart, i ) ),
		[ gridStart ],
	);

	const monthLabel = cursor.toLocaleDateString( undefined, { month: 'long', year: 'numeric' } );
	const todayKey   = ymd( new Date() );
	const thisMonth  = cursor.getMonth();

	function shiftMonth( delta: number ): void {
		setCursor( ( p ) => new Date( p.getFullYear(), p.getMonth() + delta, 1 ) );
	}

	return (
		<section className="flex flex-col rounded-[10px] bg-card shadow-sm">
			{ /* Header: title + "View" link */ }
			<header className="flex items-center justify-between gap-3 border-b border-border px-6 py-4">
				<h2 className="m-0 text-base font-bold leading-tight tracking-tight text-foreground">
					{ canManageLeave ? __( 'Team Calendar', 'erp' ) : __( 'My Calendar', 'erp' ) }
				</h2>
				<Link
					to="/leave/calendar"
					viewTransition
					className="inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline"
				>
					{ __( 'View', 'erp' ) }
					<ArrowUpRight size={ 13 } aria-hidden="true" />
				</Link>
			</header>

			<div className="relative px-4 py-3">
				{ loading ? (
					<div className="absolute inset-0 z-10 flex items-center justify-center bg-card/60 text-xs text-muted-foreground">
						{ __( 'Loading…', 'erp' ) }
					</div>
				) : null }

				{ error ? (
					<p className="py-6 text-center text-sm text-destructive">{ error }</p>
				) : (
					<>
						{ /* Month nav */ }
						<div className="mb-2 flex items-center justify-between px-1">
							<button
								type="button"
								onClick={ () => shiftMonth( -1 ) }
								className="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground hover:bg-muted hover:text-foreground"
								aria-label={ __( 'Previous month', 'erp' ) }
							>
								<ChevronLeft size={ 16 } aria-hidden="true" />
							</button>
							<span className="text-sm font-semibold text-foreground">{ monthLabel }</span>
							<button
								type="button"
								onClick={ () => shiftMonth( 1 ) }
								className="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground hover:bg-muted hover:text-foreground"
								aria-label={ __( 'Next month', 'erp' ) }
							>
								<ChevronRight size={ 16 } aria-hidden="true" />
							</button>
						</div>

						{ /* Weekday header */ }
						<div className="grid grid-cols-7">
							{ WEEKDAYS.map( ( wd ) => (
								<div key={ wd } className="py-1 text-center text-[11px] font-medium uppercase tracking-wide text-muted-foreground">
									{ wd }
								</div>
							) ) }
						</div>

						{ /* Day grid: number + up to 3 event dots beneath. Days with
						   leaves/holidays get a hover tooltip listing them. */ }
						<TooltipProvider>
							<div className="grid grid-cols-7">
								{ days.map( ( day ) => {
									const key      = ymd( day );
									const bucket   = byDay.get( key );
									const inMonth  = day.getMonth() === thisMonth;
									const isToday  = key === todayKey;
									const leaves   = bucket?.leaves ?? [];
									const holidays = bucket?.holidays ?? [];
									const hasEvents = leaves.length > 0 || holidays.length > 0;
									const dots: string[] = [];
									if ( leaves.length ) {
										dots.push( 'var(--primary)' );
									}
									if ( holidays.length ) {
										dots.push( HOLIDAY_COLOR );
									}

									const cell = (
										<div className="flex flex-col items-center justify-start py-1">
											<span
												className={ [
													'inline-flex size-8 items-center justify-center rounded-full text-sm',
													isToday
														? 'bg-primary font-semibold text-primary-foreground'
														: inMonth
															? 'text-foreground'
															: 'text-muted-foreground/50',
													hasEvents && ! isToday ? 'cursor-default font-medium' : '',
												].join( ' ' ) }
											>
												{ day.getDate() }
											</span>
											<span className="mt-0.5 flex h-1.5 items-center gap-0.5">
												{ dots.map( ( color, i ) => (
													<span
														key={ i }
														aria-hidden="true"
														className="inline-block size-1.5 rounded-full"
														style={ { backgroundColor: color } }
													/>
												) ) }
											</span>
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

						{ /* Legend */ }
						<div className="mt-2 flex items-center justify-center gap-4 border-t border-border pt-2 text-[11px] text-muted-foreground">
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
	);
}
