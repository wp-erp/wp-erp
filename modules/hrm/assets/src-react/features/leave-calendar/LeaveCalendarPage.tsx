/**
 * `/leave/calendar` route — leave + holiday calendar with Month / Week / Day
 * views (legacy parity: the FullCalendar admin had all three).
 *
 * Plain Tailwind grids (no calendar dependency). Month = 6-week grid, Week = a
 * single 7-day row, Day = a detailed single-day list. Each day shows holiday
 * chips and leave chips (leave chips carry an avatar + link through to the
 * employee profile); weekend / holiday cells get a subtle tint. Data comes from
 * `erp/v2/leave-calendar`, which accepts any start/end window.
 *
 * Chrome lives alongside: `CalendarToolbar` (nav + view switch + filters +
 * legend), `CalendarGrid` (month/week grid + `LeaveChip`), `leave-calendar-format`
 * (pure date helpers + the per-day bucket type).
 */

import { useEffect, useMemo, useState } from 'react';
import type { JSX } from 'react';

import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { useCan } from '@/shared/hooks/useCan';
import { __ } from '@/shared/i18n';

import { loadLookup } from '../employees/filters/lookups';
import type { LookupOption } from '../employees/filters/lookups';

import { CalendarGrid, LeaveChip } from './CalendarGrid';
import { CalendarToolbar, type CalendarView } from './CalendarToolbar';
import { addDays, mondayOffset, parseYmd, ymd, type DayEvents } from './leave-calendar-format';
import { useLeaveCalendar } from './useLeaveCalendar';

function LeaveCalendarInner(): JSX.Element {
	// Managers see the whole-company calendar ('all' scope) with dept/designation
	// filters; an employee without `erp_leave_manage` sees only their OWN leave
	// ('me' scope — the v2 controller clamps non-managers to self regardless, this
	// just hides the manager-only affordances).
	const canManage = useCan( 'erp_leave_manage' );

	const [ view, setView ]     = useState< CalendarView >( 'month' );
	// `cursor` is the focus day (any day within the displayed month / week, or the
	// exact day in Day view).
	const [ cursor, setCursor ] = useState( () => {
		const now = new Date();
		return new Date( now.getFullYear(), now.getMonth(), now.getDate() );
	} );

	// Derive the visible window + grid rows from the active view.
	const { rangeStart, rangeEnd, weeks, thisMonth } = useMemo( () => {
		if ( view === 'day' ) {
			return { rangeStart: cursor, rangeEnd: cursor, weeks: [ [ cursor ] ], thisMonth: cursor.getMonth() };
		}
		if ( view === 'week' ) {
			const weekStart = addDays( cursor, -mondayOffset( cursor.getDay() ) );
			const days      = Array.from( { length: 7 }, ( _v, i ) => addDays( weekStart, i ) );
			return { rangeStart: weekStart, rangeEnd: addDays( weekStart, 6 ), weeks: [ days ], thisMonth: cursor.getMonth() };
		}
		// Month: a 6-week grid starting on the Monday on/before the 1st.
		const monthStart = new Date( cursor.getFullYear(), cursor.getMonth(), 1 );
		const gridStart  = addDays( monthStart, -mondayOffset( monthStart.getDay() ) );
		const cells      = Array.from( { length: 42 }, ( _v, i ) => addDays( gridStart, i ) );
		const rows: Date[][] = [];
		for ( let i = 0; i < 42; i += 7 ) {
			rows.push( cells.slice( i, i + 7 ) );
		}
		return { rangeStart: gridStart, rangeEnd: addDays( gridStart, 41 ), weeks: rows, thisMonth: cursor.getMonth() };
	}, [ view, cursor ] );

	// Department / Designation filters — this is the whole-company admin calendar,
	// so it queries every employee's leave ('all' scope), optionally narrowed.
	const [ departmentId, setDepartmentId ]   = useState( 0 );
	const [ designationId, setDesignationId ] = useState( 0 );
	const [ departments, setDepartments ]     = useState< LookupOption[] >( [] );
	const [ designations, setDesignations ]   = useState< LookupOption[] >( [] );

	useEffect( () => {
		let cancelled = false;
		void loadLookup( 'departments' ).then( ( l ) => ! cancelled && setDepartments( l ) );
		void loadLookup( 'designations' ).then( ( l ) => ! cancelled && setDesignations( l ) );
		return () => {
			cancelled = true;
		};
	}, [] );

	const { events, loading, error } = useLeaveCalendar( ymd( rangeStart ), ymd( rangeEnd ), {
		scope: canManage ? 'all' : 'me',
		departmentId,
		designationId,
	} );

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

	const todayKey = ymd( new Date() );

	// View-dependent header label.
	const label = useMemo( () => {
		if ( view === 'day' ) {
			return cursor.toLocaleDateString( undefined, { weekday: 'long', month: 'short', day: 'numeric', year: 'numeric' } );
		}
		if ( view === 'week' ) {
			const start = addDays( cursor, -mondayOffset( cursor.getDay() ) );
			const end   = addDays( start, 6 );
			const a = start.toLocaleDateString( undefined, { month: 'short', day: 'numeric' } );
			const b = end.toLocaleDateString( undefined, { month: 'short', day: 'numeric', year: 'numeric' } );
			return `${ a } – ${ b }`;
		}
		return cursor.toLocaleDateString( undefined, { month: 'long', year: 'numeric' } );
	}, [ view, cursor ] );

	function shift( delta: number ): void {
		setCursor( ( prev ) => {
			if ( view === 'day' ) {
				return addDays( prev, delta );
			}
			if ( view === 'week' ) {
				return addDays( prev, delta * 7 );
			}
			return new Date( prev.getFullYear(), prev.getMonth() + delta, 1 );
		} );
	}

	function goToday(): void {
		const now = new Date();
		setCursor( new Date( now.getFullYear(), now.getMonth(), now.getDate() ) );
	}

	const dayBucket = byDay.get( ymd( cursor ) );

	return (
		<section className="mx-auto w-full max-w-full">
			<header className="mb-6 flex items-center justify-between gap-4">
				<h1 className="text-2xl font-bold leading-8 text-foreground">
					{ canManage ? __( 'Leave Calendar', 'erp' ) : __( 'My Leave Calendar', 'erp' ) }
				</h1>
			</header>

			<div className="rounded-lg border border-border bg-card shadow-sm">
				<CalendarToolbar
					label={ label }
					view={ view }
					onView={ setView }
					departmentId={ departmentId }
					designationId={ designationId }
					departments={ departments }
					designations={ designations }
					onPrev={ () => shift( -1 ) }
					onNext={ () => shift( 1 ) }
					onToday={ goToday }
					onDepartmentChange={ setDepartmentId }
					onDesignationChange={ setDesignationId }
					showFilters={ canManage }
				/>

				{ error ? (
					<p className="p-6 text-sm text-destructive">{ error }</p>
				) : view === 'day' ? (
					<div className="relative p-4">
						{ loading ? (
							<div className="absolute inset-0 z-10 flex items-center justify-center bg-card/60 text-sm text-muted-foreground">
								{ __( 'Loading…', 'erp' ) }
							</div>
						) : null }
						<div className="flex flex-col gap-2">
							{ ( dayBucket?.holidays ?? [] ).map( ( ev, i ) => (
								<span
									key={ `dh-${ ev.id }-${ i }` }
									className="w-fit rounded px-2 py-1 text-sm font-medium text-white"
									style={ { backgroundColor: ev.color || '#FF5354' } }
								>
									{ ev.title }
								</span>
							) ) }
							{ ( dayBucket?.leaves ?? [] ).map( ( ev, i ) => (
								<div key={ `dl-${ ev.id }-${ i }` } className="w-fit max-w-full text-sm">
									<LeaveChip ev={ ev } />
								</div>
							) ) }
							{ ( dayBucket?.holidays?.length ?? 0 ) === 0 && ( dayBucket?.leaves?.length ?? 0 ) === 0 && ! loading ? (
								<p className="py-8 text-center text-sm text-muted-foreground">
									{ __( 'No leave or holidays on this day.', 'erp' ) }
								</p>
							) : null }
						</div>
					</div>
				) : (
					<CalendarGrid
						weeks={ weeks }
						byDay={ byDay }
						thisMonth={ thisMonth }
						todayKey={ todayKey }
						loading={ loading }
						dimOutOfMonth={ view === 'month' }
					/>
				) }
			</div>
		</section>
	);
}

export function LeaveCalendarPage(): JSX.Element {
	// No capability gate here: the route is reachable by any HR-app user and the
	// inner view + the v2 controller scope a non-manager to their OWN leave.
	return (
		<ErrorBoundary>
			<LeaveCalendarInner />
		</ErrorBoundary>
	);
}
