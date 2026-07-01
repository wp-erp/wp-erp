/**
 * `/leave/calendar` route — month-view leave + holiday calendar.
 *
 * Plain Tailwind month grid (no new calendar dependency): weekday header + a
 * 6-week grid. Each day shows holiday chips and leave chips; weekend / holiday
 * cells get a subtle background tint. Data comes from `erp/v2/leave-calendar`,
 * which mirrors the legacy `get_leave_holiday_by_date()` merge.
 *
 * Chrome lives alongside: `CalendarToolbar` (nav + filters + legend),
 * `CalendarGrid` (weekday header + day grid), `leave-calendar-format` (pure
 * date helpers + the per-day bucket type).
 */

import { useEffect, useMemo, useState } from 'react';
import type { JSX } from 'react';

import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { useCan } from '@/shared/hooks/useCan';
import { __ } from '@/shared/i18n';

import { loadLookup } from '../employees/filters/lookups';
import type { LookupOption } from '../employees/filters/lookups';

import { CalendarGrid } from './CalendarGrid';
import { CalendarToolbar } from './CalendarToolbar';
import { addDays, parseYmd, ymd, type DayEvents } from './leave-calendar-format';
import { useLeaveCalendar } from './useLeaveCalendar';

function LeaveCalendarInner(): JSX.Element {
	// Managers see the whole-company calendar ('all' scope) with dept/designation
	// filters; an employee without `erp_leave_manage` sees only their OWN leave
	// ('me' scope — the v2 controller clamps non-managers to self regardless, this
	// just hides the manager-only affordances).
	const canManage = useCan( 'erp_leave_manage' );

	// `cursor` is the first day of the displayed month.
	const [ cursor, setCursor ] = useState( () => {
		const now = new Date();
		return new Date( now.getFullYear(), now.getMonth(), 1 );
	} );

	// 6-week visible grid (week starts Sunday).
	const gridStart = useMemo( () => addDays( cursor, -cursor.getDay() ), [ cursor ] );
	const gridEnd   = useMemo( () => addDays( gridStart, 41 ), [ gridStart ] );

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

	const { events, loading, error } = useLeaveCalendar( ymd( gridStart ), ymd( gridEnd ), {
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
		<section className="mx-auto w-full max-w-full">
			<header className="mb-6 flex items-center justify-between gap-4">
				<h1 className="text-2xl font-bold leading-8 text-foreground">
					{ canManage ? __( 'Leave Calendar', 'erp' ) : __( 'My Leave Calendar', 'erp' ) }
				</h1>
			</header>

			<div className="rounded-lg border border-border bg-card shadow-sm">
				<CalendarToolbar
					monthLabel={ monthLabel }
					departmentId={ departmentId }
					designationId={ designationId }
					departments={ departments }
					designations={ designations }
					onPrev={ () => shiftMonth( -1 ) }
					onNext={ () => shiftMonth( 1 ) }
					onToday={ goToday }
					onDepartmentChange={ setDepartmentId }
					onDesignationChange={ setDesignationId }
					showFilters={ canManage }
				/>

				{ error ? (
					<p className="p-6 text-sm text-destructive">{ error }</p>
				) : (
					<CalendarGrid
						weeks={ weeks }
						byDay={ byDay }
						thisMonth={ thisMonth }
						todayKey={ todayKey }
						loading={ loading }
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
