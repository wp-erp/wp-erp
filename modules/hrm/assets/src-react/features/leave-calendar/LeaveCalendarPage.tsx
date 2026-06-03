/**
 * `/leave/calendar` route — month-view leave + holiday calendar.
 *
 * Plain Tailwind month grid (no new calendar dependency): weekday header + a
 * 6-week grid. Each day shows holiday chips and leave chips; weekend / holiday
 * cells get a subtle background tint. Data comes from `erp/v2/leave-calendar`,
 * which mirrors the legacy `get_leave_holiday_by_date()` merge.
 */

import { Button } from '@wedevs/plugin-ui';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useMemo, useState } from 'react';
import type { JSX } from 'react';

import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { __ } from '@/shared/i18n';

import type { CalendarEvent } from './types';
import { useLeaveCalendar } from './useLeaveCalendar';

const WEEKDAYS = [ 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' ];

/** Local-midnight Date from a `Y-m-d` string. */
function parseYmd( value: string ): Date {
	const [ y = 1970, m = 1, d = 1 ] = value.split( '-' ).map( ( n ) => parseInt( n, 10 ) );
	return new Date( y, m - 1, d );
}

function ymd( date: Date ): string {
	const y = date.getFullYear();
	const m = String( date.getMonth() + 1 ).padStart( 2, '0' );
	const d = String( date.getDate() ).padStart( 2, '0' );
	return `${ y }-${ m }-${ d }`;
}

function addDays( date: Date, days: number ): Date {
	const next = new Date( date );
	next.setDate( next.getDate() + days );
	return next;
}

interface DayEvents {
	leaves:   CalendarEvent[];
	holidays: CalendarEvent[];
	weekend:  boolean;
}

function LeaveCalendarInner(): JSX.Element {
	// `cursor` is the first day of the displayed month.
	const [ cursor, setCursor ] = useState( () => {
		const now = new Date();
		return new Date( now.getFullYear(), now.getMonth(), 1 );
	} );

	// 6-week visible grid (week starts Sunday).
	const gridStart = useMemo( () => addDays( cursor, -cursor.getDay() ), [ cursor ] );
	const gridEnd   = useMemo( () => addDays( gridStart, 41 ), [ gridStart ] );

	const { events, loading, error } = useLeaveCalendar( ymd( gridStart ), ymd( gridEnd ) );

	// Expand each event across the days it covers → per-day buckets.
	const byDay = useMemo( () => {
		const map = new Map< string, DayEvents >();
		const ensure = ( key: string ): DayEvents => {
			let bucket = map.get( key );
			if ( ! bucket ) {
				bucket = { leaves: [], holidays: [], weekend: false };
				map.set( key, bucket );
			}
			return bucket;
		};

		events.forEach( ( ev ) => {
			if ( ! ev.start ) {
				return;
			}
			const from = parseYmd( ev.start );
			const to   = ev.end ? parseYmd( ev.end ) : from;
			for ( let d = from; d <= to; d = addDays( d, 1 ) ) {
				const bucket = ensure( ymd( d ) );
				if ( ev.type === 'weekend' ) {
					bucket.weekend = true;
				} else if ( ev.type === 'holiday' ) {
					bucket.holidays.push( ev );
				} else {
					bucket.leaves.push( ev );
				}
			}
		} );

		return map;
	}, [ events ] );

	const weeks = useMemo( () => {
		const cells: Date[] = [];
		for ( let i = 0; i < 42; i++ ) {
			cells.push( addDays( gridStart, i ) );
		}
		const rows: Date[][] = [];
		for ( let i = 0; i < 42; i += 7 ) {
			rows.push( cells.slice( i, i + 7 ) );
		}
		return rows;
	}, [ gridStart ] );

	const monthLabel = cursor.toLocaleDateString( undefined, { month: 'long', year: 'numeric' } );
	const todayKey   = ymd( new Date() );
	const thisMonth  = cursor.getMonth();

	function shiftMonth( delta: number ): void {
		setCursor( ( prev ) => new Date( prev.getFullYear(), prev.getMonth() + delta, 1 ) );
	}

	function goToday(): void {
		const now = new Date();
		setCursor( new Date( now.getFullYear(), now.getMonth(), 1 ) );
	}

	return (
		<section className="mx-auto w-full max-w-7xl">
			<header className="mb-6 flex items-center justify-between gap-4">
				<h1 className="text-2xl font-bold leading-8 text-foreground">
					{ __( 'Leave Calendar', 'erp' ) }
				</h1>
			</header>

			<div className="rounded-lg border border-border bg-card shadow-sm">
				{ /* Month nav */ }
				<div className="flex items-center justify-between gap-3 border-b border-border px-4 py-3">
					<div className="flex items-center gap-2">
						<button
							type="button"
							onClick={ () => shiftMonth( -1 ) }
							className="inline-flex size-8 items-center justify-center rounded-md border border-border bg-card text-foreground hover:bg-muted"
							aria-label={ __( 'Previous month', 'erp' ) }
						>
							<ChevronLeft size={ 16 } aria-hidden="true" />
						</button>
						<button
							type="button"
							onClick={ () => shiftMonth( 1 ) }
							className="inline-flex size-8 items-center justify-center rounded-md border border-border bg-card text-foreground hover:bg-muted"
							aria-label={ __( 'Next month', 'erp' ) }
						>
							<ChevronRight size={ 16 } aria-hidden="true" />
						</button>
						<span className="ml-2 text-base font-semibold text-foreground">{ monthLabel }</span>
					</div>
					<div className="flex items-center gap-3">
						<Button variant="outline" className="h-9 px-4 text-sm" onClick={ goToday }>
							{ __( 'Today', 'erp' ) }
						</Button>
					</div>
				</div>

				{ /* Legend */ }
				<div className="flex flex-wrap items-center gap-4 border-b border-border px-4 py-2 text-xs text-muted-foreground">
					<span className="inline-flex items-center gap-1.5">
						<span aria-hidden="true" className="inline-block size-2.5 rounded-full" style={ { backgroundColor: '#FF5354' } } />
						{ __( 'Holiday', 'erp' ) }
					</span>
					<span className="inline-flex items-center gap-1.5">
						<span aria-hidden="true" className="inline-block size-2.5 rounded-full bg-muted-foreground/40" />
						{ __( 'Weekend', 'erp' ) }
					</span>
					<span className="inline-flex items-center gap-1.5">
						<span aria-hidden="true" className="inline-block size-2.5 rounded-full bg-primary" />
						{ __( 'Leave', 'erp' ) }
					</span>
				</div>

				{ error ? (
					<p className="p-6 text-sm text-destructive">{ error }</p>
				) : (
					<div className="relative">
						{ loading ? (
							<div className="absolute inset-0 z-10 flex items-center justify-center bg-card/60 text-sm text-muted-foreground">
								{ __( 'Loading…', 'erp' ) }
							</div>
						) : null }

						{ /* Weekday header */ }
						<div className="grid grid-cols-7 border-b border-border">
							{ WEEKDAYS.map( ( wd ) => (
								<div key={ wd } className="px-2 py-2 text-center text-xs font-medium uppercase tracking-normal text-muted-foreground">
									{ wd }
								</div>
							) ) }
						</div>

						{ /* Day grid */ }
						<div>
							{ weeks.map( ( week, wi ) => (
								<div key={ wi } className="grid grid-cols-7">
									{ week.map( ( day ) => {
										const key       = ymd( day );
										const bucket    = byDay.get( key );
										const inMonth   = day.getMonth() === thisMonth;
										const isToday   = key === todayKey;
										const isWeekend = bucket?.weekend ?? false;

										return (
											<div
												key={ key }
												className={ [
													'min-h-24 border-b border-r border-border p-1.5 last:border-r-0',
													inMonth ? '' : 'bg-muted/20',
													isWeekend ? 'bg-muted/30' : '',
												].join( ' ' ) }
											>
												<div className="mb-1 flex items-center justify-between">
													<span
														className={ [
															'inline-flex size-6 items-center justify-center rounded-full text-xs',
															isToday ? 'bg-primary font-semibold text-primary-foreground' : inMonth ? 'text-foreground' : 'text-muted-foreground',
														].join( ' ' ) }
													>
														{ day.getDate() }
													</span>
												</div>

												<div className="flex flex-col gap-1">
													{ ( bucket?.holidays ?? [] ).map( ( ev, i ) => (
														<span
															key={ `h-${ ev.id }-${ i }` }
															className="truncate rounded px-1.5 py-0.5 text-[11px] font-medium text-white"
															style={ { backgroundColor: ev.color || '#FF5354' } }
															title={ ev.title }
														>
															{ ev.title }
														</span>
													) ) }
													{ ( bucket?.leaves ?? [] ).map( ( ev, i ) => (
														<span
															key={ `l-${ ev.id }-${ i }` }
															className="flex items-center gap-1 truncate rounded px-1.5 py-0.5 text-[11px] text-foreground"
															style={ { backgroundColor: ev.color ? `${ ev.color }22` : 'var(--muted)' } }
															title={ ev.reason ? `${ ev.title } — ${ ev.reason }` : ev.title }
														>
															<span aria-hidden="true" className="inline-block size-2 shrink-0 rounded-full" style={ { backgroundColor: ev.color || 'var(--primary)' } } />
															<span className="truncate">{ ev.title }</span>
														</span>
													) ) }
												</div>
											</div>
										);
									} ) }
								</div>
							) ) }
						</div>
					</div>
				) }
			</div>
		</section>
	);
}

export function LeaveCalendarPage(): JSX.Element {
	return (
		<CapabilityGate caps={ [ 'erp_leave_manage' ] }>
			<ErrorBoundary>
				<LeaveCalendarInner />
			</ErrorBoundary>
		</CapabilityGate>
	);
}
