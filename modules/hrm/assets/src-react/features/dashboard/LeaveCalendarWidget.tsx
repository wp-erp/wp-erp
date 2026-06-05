/**
 * Dashboard "My Leave Calendar" widget — month grid of leaves + holidays,
 * mirroring the full `/leave/calendar` page (same `erp/v2/leave-calendar` data
 * via `useLeaveCalendar`): weekday header, 6-week grid, holiday + leave name
 * chips per day, weekend tint, today highlight, and month navigation.
 */

import { useSelect } from '@wordpress/data';
import { Button } from '@wedevs/plugin-ui';
import { CalendarDays, ChevronLeft, ChevronRight, Plus } from 'lucide-react';
import { useMemo, useState } from 'react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import { storeName as meStoreName } from '@/stores/me';
import type { MeUser } from '@/stores/me/types';
import { LeaveRequestDialog } from '@/features/employee-profile-v2/leave/LeaveRequestDialog';
import { useEmployeeLeave } from '@/features/employee-profile-v2/leave/useEmployeeLeave';
import type { CalendarEvent } from '@/features/leave-calendar/types';
import { useLeaveCalendar } from '@/features/leave-calendar/useLeaveCalendar';

const WEEKDAYS = [ 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' ];

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

interface DayEvents {
	leaves:   CalendarEvent[];
	holidays: CalendarEvent[];
	weekend:  boolean;
}

export function LeaveCalendarWidget( { className = '' }: { readonly className?: string } = {} ): JSX.Element {
	const [ cursor, setCursor ] = useState( () => {
		const now = new Date();
		return new Date( now.getFullYear(), now.getMonth(), 1 );
	} );

	const gridStart = useMemo( () => addDays( cursor, -cursor.getDay() ), [ cursor ] );
	const { events, loading, error, reload } = useLeaveCalendar( ymd( gridStart ), ymd( addDays( gridStart, 41 ) ) );

	// Current user — for the "Take a Leave" action (own leave request, mirroring
	// the legacy dashboard widget's button).
	const user          = useSelect( ( select ) => ( select( meStoreName ) as { getUser: () => MeUser | null } ).getUser(), [] );
	const currentUserId = user?.id ?? 0;
	const { data: leaveData } = useEmployeeLeave( currentUserId );
	const financialYears = leaveData?.meta.financial_years ?? [];
	const currentYear    = leaveData?.meta.current_year ?? 0;
	const [ leaveOpen, setLeaveOpen ] = useState( false );

	const byDay = useMemo( () => {
		const map = new Map< string, DayEvents >();
		const ensure = ( key: string ): DayEvents => {
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

	const weeks = useMemo( () => {
		const rows: Date[][] = [];
		for ( let i = 0; i < 42; i += 7 ) {
			rows.push( Array.from( { length: 7 }, ( _, j ) => addDays( gridStart, i + j ) ) );
		}
		return rows;
	}, [ gridStart ] );

	const monthLabel = cursor.toLocaleDateString( undefined, { month: 'long', year: 'numeric' } );
	const todayKey   = ymd( new Date() );
	const thisMonth  = cursor.getMonth();

	function shiftMonth( delta: number ): void {
		setCursor( ( p ) => new Date( p.getFullYear(), p.getMonth() + delta, 1 ) );
	}
	function goToday(): void {
		const now = new Date();
		setCursor( new Date( now.getFullYear(), now.getMonth(), 1 ) );
	}

	return (
		<section className={ `flex flex-col rounded-lg border border-border bg-card shadow-sm ${ className }` }>
			{ /* Header: title + month nav + Today */ }
			<div className="flex flex-wrap items-center justify-between gap-3 border-b border-border px-4 py-3">
				<div className="flex items-center gap-2">
					<CalendarDays size={ 18 } className="text-primary" aria-hidden="true" />
					<h2 className="m-0 text-base font-semibold text-foreground">{ __( 'My Leave Calendar', 'erp' ) }</h2>
				</div>
				<div className="flex items-center gap-2">
					{ currentUserId > 0 ? (
						<Button className="h-8 gap-1.5 px-3 text-sm" onClick={ () => setLeaveOpen( true ) }>
							<Plus size={ 15 } aria-hidden="true" /> { __( 'Take a Leave', 'erp' ) }
						</Button>
					) : null }
					<button type="button" onClick={ () => shiftMonth( -1 ) } className="inline-flex size-8 items-center justify-center rounded-md border border-border bg-card text-foreground hover:bg-muted" aria-label={ __( 'Previous month', 'erp' ) }>
						<ChevronLeft size={ 16 } aria-hidden="true" />
					</button>
					<button type="button" onClick={ () => shiftMonth( 1 ) } className="inline-flex size-8 items-center justify-center rounded-md border border-border bg-card text-foreground hover:bg-muted" aria-label={ __( 'Next month', 'erp' ) }>
						<ChevronRight size={ 16 } aria-hidden="true" />
					</button>
					<span className="ml-1 text-sm font-semibold tabular-nums text-foreground">{ monthLabel }</span>
					<Button variant="outline" className="h-8 px-3 text-sm" onClick={ goToday }>{ __( 'Today', 'erp' ) }</Button>
				</div>
			</div>

			{ /* Legend */ }
			<div className="flex flex-wrap items-center gap-4 border-b border-border px-4 py-2 text-xs text-muted-foreground">
				<span className="inline-flex items-center gap-1.5"><span aria-hidden="true" className="inline-block size-2.5 rounded-full" style={ { backgroundColor: '#FF5354' } } /> { __( 'Holiday', 'erp' ) }</span>
				<span className="inline-flex items-center gap-1.5"><span aria-hidden="true" className="inline-block size-2.5 rounded-full bg-muted-foreground/40" /> { __( 'Weekend', 'erp' ) }</span>
				<span className="inline-flex items-center gap-1.5"><span aria-hidden="true" className="inline-block size-2.5 rounded-full bg-primary" /> { __( 'Leave', 'erp' ) }</span>
			</div>

			{ error ? (
				<p className="p-6 text-sm text-destructive">{ error }</p>
			) : (
				<div className="relative">
					{ loading ? (
						<div className="absolute inset-0 z-10 flex items-center justify-center bg-card/60 text-sm text-muted-foreground">{ __( 'Loading…', 'erp' ) }</div>
					) : null }

					<div className="grid grid-cols-7 border-b border-border">
						{ WEEKDAYS.map( ( wd ) => (
							<div key={ wd } className="px-2 py-2 text-center text-xs font-medium uppercase tracking-normal text-muted-foreground">{ wd }</div>
						) ) }
					</div>

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
												<span className={ [ 'inline-flex size-6 items-center justify-center rounded-full text-xs', isToday ? 'bg-primary font-semibold text-primary-foreground' : inMonth ? 'text-foreground' : 'text-muted-foreground' ].join( ' ' ) }>
													{ day.getDate() }
												</span>
											</div>

											<div className="flex flex-col gap-1">
												{ ( bucket?.holidays ?? [] ).map( ( ev, i ) => (
													<span key={ `h-${ ev.id }-${ i }` } className="truncate rounded px-1.5 py-0.5 text-[11px] font-medium text-white" style={ { backgroundColor: ev.color || '#FF5354' } } title={ ev.title }>{ ev.title }</span>
												) ) }
												{ ( bucket?.leaves ?? [] ).map( ( ev, i ) => (
													<span key={ `l-${ ev.id }-${ i }` } className="flex items-center gap-1 truncate rounded px-1.5 py-0.5 text-[11px] text-foreground" style={ { backgroundColor: ev.color ? `${ ev.color }22` : 'var(--muted)' } } title={ ev.reason ? `${ ev.title } — ${ ev.reason }` : ev.title }>
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

			{ currentUserId > 0 ? (
				<LeaveRequestDialog
					open={ leaveOpen }
					userId={ currentUserId }
					financialYears={ financialYears }
					currentYear={ currentYear }
					onClose={ () => setLeaveOpen( false ) }
					onSubmitted={ () => {
						setLeaveOpen( false );
						void reload();
					} }
				/>
			) : null }
		</section>
	);
}
