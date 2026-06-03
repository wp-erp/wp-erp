/**
 * `/` (Overview) — the HR dashboard landing page.
 *
 * Modern card-based layout over `GET /erp/v2/dashboard`: a greeting, a row of
 * summary stat cards, then a two-column grid of widgets (who's out, birthdays,
 * upcoming holidays, latest announcements) — the same information the legacy
 * `views/dashboard.php` widgets surface, restyled for the React design system.
 */

import { useSelect } from '@wordpress/data';
import { Avatar, AvatarFallback, AvatarImage } from '@wedevs/plugin-ui';
import {
	ArrowRight,
	Briefcase,
	Building2,
	CalendarClock,
	Cake,
	Clock4,
	Megaphone,
	PalmtreeIcon,
	UserPlus,
	Users,
} from 'lucide-react';
import type { ComponentType, JSX, SVGProps } from 'react';
import { Link } from 'react-router-dom';

import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { useCan } from '@/shared/hooks/useCan';
import { __, sprintf } from '@/shared/i18n';
import { storeName as meStoreName } from '@/stores/me';
import type { MeUser } from '@/stores/me/types';

import { ChartsSection } from './DashboardCharts';
import type { BirthdayPerson, OnLeavePerson } from './types';
import { useDashboard } from './useDashboard';

type LucideIcon = ComponentType< SVGProps< SVGSVGElement > & { size?: number; strokeWidth?: number } >;

function greeting(): string {
	const h = new Date().getHours();
	if ( h < 12 ) {
		return __( 'Good morning', 'erp' );
	}
	if ( h < 18 ) {
		return __( 'Good afternoon', 'erp' );
	}
	return __( 'Good evening', 'erp' );
}

function fmtDate( value: string | null ): string {
	if ( ! value ) {
		return '—';
	}
	const d = new Date( value );
	if ( Number.isNaN( d.getTime() ) ) {
		return value.slice( 0, 10 );
	}
	return d.toLocaleDateString( undefined, { month: 'short', day: 'numeric' } );
}

function fmtDayMonth( value: string | null ): string {
	if ( ! value ) {
		return '—';
	}
	const d = new Date( value );
	if ( Number.isNaN( d.getTime() ) ) {
		return value.slice( 5, 10 );
	}
	return d.toLocaleDateString( undefined, { month: 'long', day: 'numeric' } );
}

function initials( name: string ): string {
	const parts = name.trim().split( /\s+/ ).filter( Boolean );
	if ( parts.length === 0 ) {
		return '?';
	}
	const first = parts[ 0 ]?.[ 0 ] ?? '';
	const last = parts.length > 1 ? parts[ parts.length - 1 ]?.[ 0 ] ?? '' : '';
	return ( first + last ).toUpperCase();
}

function PersonAvatar( { name, src, size = 'size-9' }: { name: string; src: string; size?: string } ): JSX.Element {
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
	readonly icon:    LucideIcon;
	readonly label:   string;
	readonly value:   number;
	readonly tint:    string;
	readonly to?:     string;
}

function StatCard( { icon: Icon, label, value, tint, to }: StatCardProps ): JSX.Element {
	const body = (
		<div className="flex items-center gap-4 rounded-lg border border-border bg-card p-5 shadow-sm transition-shadow hover:shadow-md">
			<span className={ `inline-flex size-12 shrink-0 items-center justify-center rounded-lg ${ tint }` }>
				<Icon size={ 22 } strokeWidth={ 1.75 } aria-hidden="true" />
			</span>
			<div className="min-w-0">
				<p className="text-2xl font-bold leading-7 text-foreground tabular-nums">{ value }</p>
				<p className="truncate text-sm text-muted-foreground">{ label }</p>
			</div>
		</div>
	);
	return to ? (
		<Link to={ to } className="block focus:outline-none focus-visible:ring-2 focus-visible:ring-ring rounded-lg">
			{ body }
		</Link>
	) : (
		body
	);
}

interface WidgetCardProps {
	readonly icon:     LucideIcon;
	readonly title:    string;
	readonly action?:  { label: string; to: string } | undefined;
	readonly children: React.ReactNode;
}

function WidgetCard( { icon: Icon, title, action, children }: WidgetCardProps ): JSX.Element {
	return (
		<section className="flex flex-col rounded-lg border border-border bg-card shadow-sm">
			<header className="flex items-center justify-between gap-3 border-b border-border px-5 py-3.5">
				<h2 className="flex items-center gap-2 text-sm font-semibold text-foreground">
					<Icon size={ 17 } strokeWidth={ 1.9 } className="text-muted-foreground" aria-hidden="true" />
					{ title }
				</h2>
				{ action ? (
					<Link
						to={ action.to }
						className="inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline"
					>
						{ action.label }
						<ArrowRight size={ 13 } aria-hidden="true" />
					</Link>
				) : null }
			</header>
			<div className="flex-1 p-2">{ children }</div>
		</section>
	);
}

function EmptyRow( { text }: { text: string } ): JSX.Element {
	return <p className="px-3 py-6 text-center text-sm text-muted-foreground">{ text }</p>;
}

function OnLeaveItem( { person }: { person: OnLeavePerson } ): JSX.Element {
	return (
		<li className="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-muted/50">
			<PersonAvatar name={ person.name } src={ person.avatar_url } />
			<div className="min-w-0 flex-1">
				<p className="truncate text-sm font-medium text-foreground">{ person.name }</p>
				<p className="text-xs text-muted-foreground">
					{ `${ fmtDate( person.start_date ) } – ${ fmtDate( person.end_date ) }` }
				</p>
			</div>
		</li>
	);
}

function BirthdayItem( { person, today }: { person: BirthdayPerson; today: boolean } ): JSX.Element {
	return (
		<li className="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-muted/50">
			<PersonAvatar name={ person.name } src={ person.avatar_url } />
			<div className="min-w-0 flex-1">
				<p className="truncate text-sm font-medium text-foreground">{ person.name }</p>
				<p className="text-xs text-muted-foreground">
					{ today ? __( 'Today 🎉', 'erp' ) : fmtDayMonth( person.date_of_birth ) }
				</p>
			</div>
		</li>
	);
}

function DashboardInner(): JSX.Element {
	const { data, loading, error } = useDashboard();
	const user = useSelect( ( select ) => ( select( meStoreName ) as { getUser: () => MeUser | null } ).getUser(), [] );
	const canCreateEmployee = useCan( 'erp_create_employee' );
	const canManageLeave    = useCan( 'erp_leave_manage' );

	const name = user?.displayName ? user.displayName.split( ' ' )[ 0 ] : '';

	if ( error ) {
		return <p className="mx-auto my-12 max-w-md text-center text-sm text-destructive">{ error }</p>;
	}

	const summary = data?.summary;
	const isManager = data?.is_hr_manager ?? false;

	return (
		<section className="mx-auto w-full max-w-7xl">
			{ /* Greeting + quick actions */ }
			<header className="mb-6 flex flex-wrap items-end justify-between gap-4">
				<div>
					<h1 className="text-2xl font-bold leading-8 text-foreground">
						{ name ? sprintf( __( '%1$s, %2$s', 'erp' ), greeting(), name ) : greeting() }
					</h1>
					<p className="mt-1 text-sm text-muted-foreground">
						{ new Date().toLocaleDateString( undefined, {
							weekday: 'long',
							year:    'numeric',
							month:   'long',
							day:     'numeric',
						} ) }
					</p>
				</div>
				<div className="flex items-center gap-2">
					{ canManageLeave ? (
						<Link
							to="/leave/requests"
							className="inline-flex h-9 items-center gap-2 rounded-md border border-border bg-card px-3 text-sm font-medium text-foreground hover:bg-muted"
						>
							<CalendarClock size={ 16 } aria-hidden="true" />
							{ __( 'Leave Requests', 'erp' ) }
						</Link>
					) : null }
					{ canCreateEmployee ? (
						<Link
							to="/employees/new"
							className="inline-flex h-9 items-center gap-2 rounded-md bg-primary px-3 text-sm font-medium text-primary-foreground hover:bg-primary/90"
						>
							<UserPlus size={ 16 } aria-hidden="true" />
							{ __( 'Add Employee', 'erp' ) }
						</Link>
					) : null }
				</div>
			</header>

			{ loading && ! data ? (
				<div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
					{ [ 0, 1, 2, 3 ].map( ( i ) => (
						<div key={ i } className="h-24 animate-pulse rounded-lg border border-border bg-muted/40" />
					) ) }
				</div>
			) : (
				<>
					{ /* Summary stat cards */ }
					<div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
						<StatCard
							icon={ Users }
							label={ __( 'Active Employees', 'erp' ) }
							value={ summary?.total_employees ?? 0 }
							tint="bg-primary/10 text-primary"
							to="/employees"
						/>
						<StatCard
							icon={ Building2 }
							label={ __( 'Departments', 'erp' ) }
							value={ summary?.total_departments ?? 0 }
							tint="bg-sky-500/10 text-sky-600 dark:text-sky-400"
							to="/departments"
						/>
						<StatCard
							icon={ Briefcase }
							label={ __( 'Designations', 'erp' ) }
							value={ summary?.total_designations ?? 0 }
							tint="bg-violet-500/10 text-violet-600 dark:text-violet-400"
							to="/designations"
						/>
						{ isManager ? (
							<StatCard
								icon={ Clock4 }
								label={ __( 'Pending Approvals', 'erp' ) }
								value={ summary?.pending_requests ?? 0 }
								tint="bg-warning-light text-warning-on-light"
								to="/leave/requests"
							/>
						) : (
							<StatCard
								icon={ Cake }
								label={ __( 'Birthdays This Week', 'erp' ) }
								value={ ( data?.birthdays_today.length ?? 0 ) + ( data?.birthdays_upcoming.length ?? 0 ) }
								tint="bg-pink-500/10 text-pink-600 dark:text-pink-400"
							/>
						) }
					</div>

					{ /* Analytics charts */ }
					{ data ? <ChartsSection charts={ data.charts } isManager={ isManager } /> : null }

					{ /* Widget grid */ }
					<div className="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
						{ /* Who's out */ }
						<WidgetCard icon={ CalendarClock } title={ __( 'Who’s Out', 'erp' ) }>
							{ ( data?.on_leave.length ?? 0 ) === 0 ? (
								<EmptyRow text={ __( 'Nobody is on leave this month.', 'erp' ) } />
							) : (
								<ul>
									{ data?.on_leave.map( ( p ) => <OnLeaveItem key={ `${ p.user_id }-${ p.start_date }` } person={ p } /> ) }
								</ul>
							) }
						</WidgetCard>

						{ /* Birthdays */ }
						<WidgetCard icon={ Cake } title={ __( 'Birthdays', 'erp' ) }>
							{ ( data?.birthdays_today.length ?? 0 ) === 0 && ( data?.birthdays_upcoming.length ?? 0 ) === 0 ? (
								<EmptyRow text={ __( 'No birthdays in the next 7 days.', 'erp' ) } />
							) : (
								<ul>
									{ data?.birthdays_today.map( ( p ) => <BirthdayItem key={ `t-${ p.user_id }` } person={ p } today /> ) }
									{ data?.birthdays_upcoming.map( ( p ) => <BirthdayItem key={ `u-${ p.user_id }` } person={ p } today={ false } /> ) }
								</ul>
							) }
						</WidgetCard>

						{ /* Upcoming holidays */ }
						<WidgetCard
							icon={ PalmtreeIcon }
							title={ __( 'Upcoming Holidays', 'erp' ) }
							action={ canManageLeave ? { label: __( 'All', 'erp' ), to: '/leave/holidays' } : undefined }
						>
							{ ( data?.holidays_upcoming.length ?? 0 ) === 0 ? (
								<EmptyRow text={ __( 'No holidays in the next 30 days.', 'erp' ) } />
							) : (
								<ul>
									{ data?.holidays_upcoming.map( ( h ) => (
										<li key={ h.id } className="flex items-center justify-between gap-3 rounded-lg px-3 py-2 hover:bg-muted/50">
											<span className="min-w-0 truncate text-sm font-medium text-foreground">{ h.title }</span>
											<span className="shrink-0 text-xs text-muted-foreground">
												{ h.start === h.end || ! h.end ? fmtDate( h.start ) : `${ fmtDate( h.start ) } – ${ fmtDate( h.end ) }` }
											</span>
										</li>
									) ) }
								</ul>
							) }
						</WidgetCard>

						{ /* Latest announcements */ }
						<WidgetCard
							icon={ Megaphone }
							title={ __( 'Latest Announcements', 'erp' ) }
							action={ { label: __( 'All', 'erp' ), to: '/announcements' } }
						>
							{ ( data?.announcements.length ?? 0 ) === 0 ? (
								<EmptyRow text={ __( 'No announcements yet.', 'erp' ) } />
							) : (
								<ul>
									{ data?.announcements.map( ( a ) => (
										<li key={ a.id } className="flex items-center justify-between gap-3 rounded-lg px-3 py-2 hover:bg-muted/50">
											<span className="min-w-0 truncate text-sm font-medium text-foreground">{ a.title }</span>
											<span className="shrink-0 text-xs text-muted-foreground">{ fmtDate( a.date ) }</span>
										</li>
									) ) }
								</ul>
							) }
						</WidgetCard>
					</div>
				</>
			) }
		</section>
	);
}

export function DashboardPage(): JSX.Element {
	return (
		<ErrorBoundary>
			<DashboardInner />
		</ErrorBoundary>
	);
}
