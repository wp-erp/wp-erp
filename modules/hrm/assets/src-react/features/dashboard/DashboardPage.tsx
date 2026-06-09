/**
 * `/` (Overview) — the HR dashboard landing page.
 *
 * Modern card-based layout over `GET /erp/v2/dashboard`: a greeting, a row of
 * summary stat cards, then a two-column grid of widgets (who's out, birthdays,
 * upcoming holidays, latest announcements) — the same information the legacy
 * `views/dashboard.php` widgets surface, restyled for the React design system.
 */

import { useSelect } from '@wordpress/data';
import {
	Avatar,
	AvatarFallback,
	AvatarImage,
	Button,
	Dialog,
	DialogContent,
	DialogHeader,
	DialogTitle,
	toast,
} from '@wedevs/plugin-ui';
import {
	ArrowRight,
	Banknote,
	Briefcase,
	Building2,
	CalendarCheck,
	CalendarClock,
	Cake,
	Clock4,
	Gift,
	Megaphone,
	Package,
	PalmtreeIcon,
	UserPlus,
	Users,
	Wallet,
} from 'lucide-react';
import { applyFilters } from '@wordpress/hooks';
import { useEffect, useState } from 'react';
import type { ComponentType, JSX, SVGProps } from 'react';
import { Link } from 'react-router-dom';

import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { HOOKS } from '@/shared/filters';
import { useCan } from '@/shared/hooks/useCan';
import { __, sprintf } from '@/shared/i18n';
import { storeName as meStoreName } from '@/stores/me';
import type { MeUser } from '@/stores/me/types';

import { ChartsSection } from './DashboardCharts';
import { LeaveCalendarWidget } from './LeaveCalendarWidget';
import { WeatherWidget } from './WeatherWidget';
import type { BirthdayPerson, DashboardProWidget, OnLeavePerson } from './types';
import {
	fetchAnnouncement,
	markAnnouncementRead,
	sendBirthdayWish,
	useDashboard,
} from './useDashboard';
import type { AnnouncementContent } from './useDashboard';

type LucideIcon = ComponentType< SVGProps< SVGSVGElement > & { size?: number; strokeWidth?: number } >;

/** Live wall-clock that re-renders every second. */
function LiveTime(): JSX.Element {
	const [ now, setNow ] = useState( () => new Date() );
	useEffect( () => {
		const id = window.setInterval( () => setNow( new Date() ), 1000 );
		return () => window.clearInterval( id );
	}, [] );
	return (
		<span className="font-medium tabular-nums text-foreground">
			{ now.toLocaleTimeString( undefined, {
				hour:   '2-digit',
				minute: '2-digit',
				second: '2-digit',
			} ) }
		</span>
	);
}

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
		<div className="group relative flex items-center gap-4 overflow-hidden rounded-[10px] bg-card p-5 shadow-sm ring-1 ring-border/40 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:ring-primary/30">
			<span className={ `inline-flex size-12 shrink-0 items-center justify-center rounded-xl shadow-sm transition-transform duration-200 group-hover:scale-105 ${ tint }` }>
				<Icon size={ 22 } strokeWidth={ 1.9 } aria-hidden="true" />
			</span>
			<div className="min-w-0">
				<p className="text-3xl font-bold leading-8 text-foreground tabular-nums">{ value }</p>
				<p className="truncate text-sm text-muted-foreground">{ label }</p>
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
		<Link to={ to } viewTransition className="group block rounded-[10px] focus:outline-none focus-visible:ring-2 focus-visible:ring-ring">
			{ body }
		</Link>
	) : (
		body
	);
}

interface WidgetCardProps {
	readonly icon:     LucideIcon;
	readonly title:    string;
	readonly count?:   number | undefined;
	readonly action?:  { label: string; to: string } | undefined;
	readonly children: React.ReactNode;
}

function WidgetCard( { icon: Icon, title, count, action, children }: WidgetCardProps ): JSX.Element {
	return (
		<section className="flex flex-col rounded-[10px] bg-card shadow-sm">
			<header className="flex items-center justify-between gap-3 border-b border-border px-6 py-4">
				<h2 className="flex items-center gap-2 text-base font-bold leading-tight tracking-tight text-foreground">
					<span className="inline-flex size-7 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground">
						<Icon size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
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
			<div className="max-h-80 flex-1 overflow-y-auto p-2">{ children }</div>
		</section>
	);
}

function EmptyRow( { text }: { text: string } ): JSX.Element {
	return <p className="px-3 py-6 text-center text-sm text-muted-foreground">{ text }</p>;
}

/** Small uppercase divider label that groups the dashboard sections. */
function SectionLabel( { children, className }: { children: React.ReactNode; className?: string } ): JSX.Element {
	return (
		<h2 className={ `mb-3 text-xs font-semibold uppercase tracking-wider text-muted-foreground ${ className ?? '' }` }>
			{ children }
		</h2>
	);
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

function BirthdayItem( {
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
				<p className="truncate text-sm font-medium text-foreground">{ person.name }</p>
				<p className="text-xs text-muted-foreground">
					{ today ? __( 'Today 🎉', 'erp' ) : fmtDayMonth( person.date_of_birth ) }
				</p>
			</div>
			{ canWish ? (
				<Button
					variant="ghost"
					size="sm"
					className="h-8 gap-1.5 text-primary hover:text-primary"
					disabled={ wished }
					onClick={ () => onWish( person.user_id ) }
				>
					<Gift size={ 14 } aria-hidden="true" />
					{ wished ? __( 'Sent', 'erp' ) : __( 'Wish', 'erp' ) }
				</Button>
			) : null }
		</li>
	);
}

const PRO_WIDGET_ICONS: Readonly< Record< string, LucideIcon > > = {
	'briefcase':      Briefcase,
	'package':        Package,
	'wallet':         Wallet,
	'calendar-check': CalendarCheck,
	'banknote':       Banknote,
};

/**
 * Generic renderer for a pro-module dashboard widget (recruitment, assets,
 * reimbursement, attendance, payroll). Pro modules contribute these via the
 * `erp_hr_v2_dashboard` PHP filter; the free dashboard knows nothing about the
 * module — it just paints the stats row and/or item list it was handed.
 */
function ProWidget( { widget }: { widget: DashboardProWidget } ): JSX.Element {
	const Icon = ( widget.icon && PRO_WIDGET_ICONS[ widget.icon ] ) || Briefcase;
	const hasStats = ( widget.stats?.length ?? 0 ) > 0;
	const hasItems = ( widget.items?.length ?? 0 ) > 0;

	return (
		<WidgetCard
			icon={ Icon }
			title={ widget.title }
			action={ widget.to ? { label: __( 'View', 'erp' ), to: widget.to } : undefined }
		>
			{ hasStats ? (
				<div className="grid grid-cols-2 gap-2 p-2">
					{ widget.stats?.map( ( s, i ) => (
						<div key={ i } className="rounded-lg bg-muted/40 px-3 py-2.5">
							<p className="text-2xl font-bold leading-7 tabular-nums text-foreground">{ s.value }</p>
							<p className="truncate text-xs text-muted-foreground">{ s.label }</p>
						</div>
					) ) }
				</div>
			) : null }

			{ hasItems ? (
				<ul>
					{ widget.items?.map( ( it, i ) => {
						const row = (
							<>
								<span className="min-w-0 truncate text-sm font-medium text-foreground">{ it.label }</span>
								{ it.meta ? <span className="shrink-0 text-xs text-muted-foreground">{ it.meta }</span> : null }
							</>
						);
						return (
							<li key={ i }>
								{ it.to ? (
									<Link to={ it.to } viewTransition className="flex items-center justify-between gap-3 rounded-lg px-3 py-2 hover:bg-muted/50">
										{ row }
									</Link>
								) : (
									<div className="flex items-center justify-between gap-3 rounded-lg px-3 py-2">{ row }</div>
								) }
							</li>
						);
					} ) }
				</ul>
			) : null }

			{ ! hasStats && ! hasItems ? (
				<EmptyRow text={ widget.empty ?? __( 'Nothing to show.', 'erp' ) } />
			) : null }
		</WidgetCard>
	);
}

function DashboardInner(): JSX.Element {
	const { data, loading, error } = useDashboard();
	const user = useSelect( ( select ) => ( select( meStoreName ) as { getUser: () => MeUser | null } ).getUser(), [] );
	const canCreateEmployee = useCan( 'erp_create_employee' );
	const canManageLeave    = useCan( 'erp_leave_manage' );

	const currentUserId = user?.id ?? 0;
	const name = user?.displayName ? user.displayName.split( ' ' )[ 0 ] : '';

	const [ viewing, setViewing ] = useState< AnnouncementContent | null >( null );
	const [ viewLoading, setViewLoading ] = useState( false );
	const [ wished, setWished ] = useState< ReadonlySet< number > >( new Set() );

	function openAnnouncement( id: number ): void {
		setViewLoading( true );
		setViewing( null );
		void markAnnouncementRead( id ).catch( () => undefined );
		void fetchAnnouncement( id )
			.then( ( content ) => setViewing( content ) )
			.catch( () => toast.error( __( 'Could not open the announcement.', 'erp' ) ) )
			.finally( () => setViewLoading( false ) );
	}

	function handleWish( employeeUserId: number ): void {
		void sendBirthdayWish( employeeUserId )
			.then( () => {
				setWished( ( prev ) => new Set( prev ).add( employeeUserId ) );
				toast.success( __( 'Birthday wish sent!', 'erp' ) );
			} )
			.catch( () => toast.error( __( 'Could not send the birthday wish.', 'erp' ) ) );
	}

	if ( error ) {
		return <p className="mx-auto my-12 max-w-md text-center text-sm text-destructive">{ error }</p>;
	}

	const summary = data?.summary;
	const isManager = data?.is_hr_manager ?? false;

	return (
		<section className="mx-auto w-full max-w-7xl">
			{ /* Quick actions — sit above the greeting card, outside it */ }
			{ canManageLeave || canCreateEmployee ? (
				<div className="mb-4 flex flex-wrap items-center justify-end gap-2">
					{ canManageLeave ? (
						<Link
							to="/leave/requests"
							viewTransition
							className="inline-flex h-9 items-center gap-2 rounded-md border border-border bg-card px-3 text-sm font-medium text-foreground hover:bg-muted"
						>
							<CalendarClock size={ 16 } aria-hidden="true" />
							{ __( 'Leave Requests', 'erp' ) }
						</Link>
					) : null }
					{ canCreateEmployee ? (
						<Link
							to="/employees/new"
							viewTransition
							className="inline-flex h-9 items-center gap-2 rounded-md bg-gradient-to-r from-primary to-primary/85 px-3.5 text-sm font-medium text-primary-foreground shadow-sm transition-all hover:shadow-md hover:brightness-105"
						>
							<UserPlus size={ 16 } aria-hidden="true" />
							{ __( 'Add Employee', 'erp' ) }
						</Link>
					) : null }
				</div>
			) : null }

			{ /* Greeting hero */ }
			<header className="relative mb-6 flex flex-wrap items-center justify-between gap-x-5 gap-y-3 overflow-hidden rounded-2xl bg-card p-6 shadow-sm ring-1 ring-border/50">
				<div className="relative">
					<h1 className="text-3xl font-bold leading-9 tracking-tight text-foreground">
						{ name ? sprintf( __( '%1$s, %2$s', 'erp' ), greeting(), name ) : greeting() }
						<span className="ml-1.5 inline-block" aria-hidden="true">👋</span>
					</h1>
					<p className="mt-1.5 flex flex-wrap items-center gap-x-1.5 text-sm text-muted-foreground">
						{ new Date().toLocaleDateString( undefined, {
							weekday: 'long',
							year:    'numeric',
							month:   'long',
							day:     'numeric',
						} ) }
						<span aria-hidden="true">·</span>
						<LiveTime />
					</p>
				</div>
				<div className="relative">
					<WeatherWidget embedded />
				</div>
			</header>

			{ /* Self-service row: pro widgets (e.g. Attendance self-service, appended via
			   the `erp_hr.dashboard.widgets` filter and applied lazily so pro bundles that
			   load after the free app are included) on the left, the leave calendar filling
			   the rest — mirroring the legacy dashboard's calendar beside the punch card. */ }
			{ ( () => {
				const widgets = applyFilters( HOOKS.DASHBOARD_WIDGETS, [] ) as ComponentType[];
				return (
					widgets.length > 0 ? (
						<div className="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-[1fr_22rem]">
							<LeaveCalendarWidget />
							<div className="flex flex-col gap-4">
								{ widgets.map( ( Widget, i ) => <Widget key={ i } /> ) }
							</div>
						</div>
					) : (
						<div className="mb-6">
							<LeaveCalendarWidget className="w-full" />
						</div>
					)
				);
			} )() }

			{ loading && ! data ? (
				<div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
					{ [ 0, 1, 2, 3 ].map( ( i ) => (
						<div key={ i } className="h-24 animate-pulse rounded-[10px] bg-card shadow-sm" />
					) ) }
				</div>
			) : (
				<>
					{ /* Summary stat cards */ }
					<SectionLabel>{ __( 'Overview', 'erp' ) }</SectionLabel>
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
					{ data ? (
						<div className="mt-8">
							<SectionLabel>{ __( 'Analytics', 'erp' ) }</SectionLabel>
							<ChartsSection charts={ data.charts } isManager={ isManager } />
						</div>
					) : null }

					{ /* Widget grid */ }
					<SectionLabel className="mt-8">{ __( 'Activity', 'erp' ) }</SectionLabel>
					<div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
						{ /* Who's out */ }
						<WidgetCard
							icon={ CalendarClock }
							title={ __( 'Who’s Out', 'erp' ) }
							count={ data?.on_leave.length }
							action={ canManageLeave ? { label: __( 'Calendar', 'erp' ), to: '/leave/calendar' } : undefined }
						>
							{ ( data?.on_leave.length ?? 0 ) === 0 ? (
								<EmptyRow text={ __( 'Nobody is on leave this month.', 'erp' ) } />
							) : (
								<ul>
									{ data?.on_leave.map( ( p ) => <OnLeaveItem key={ `${ p.user_id }-${ p.start_date }` } person={ p } /> ) }
								</ul>
							) }
						</WidgetCard>

						{ /* Birthdays */ }
						<WidgetCard
							icon={ Cake }
							title={ __( 'Birthdays', 'erp' ) }
							count={ ( data?.birthdays_today.length ?? 0 ) + ( data?.birthdays_upcoming.length ?? 0 ) }
						>
							{ ( data?.birthdays_today.length ?? 0 ) === 0 && ( data?.birthdays_upcoming.length ?? 0 ) === 0 ? (
								<EmptyRow text={ __( 'No birthdays in the next 7 days.', 'erp' ) } />
							) : (
								<ul>
									{ data?.birthdays_today.map( ( p ) => (
										<BirthdayItem
											key={ `t-${ p.user_id }` }
											person={ p }
											today
											canWish={ p.user_id !== currentUserId }
											wished={ wished.has( p.user_id ) }
											onWish={ handleWish }
										/>
									) ) }
									{ data?.birthdays_upcoming.map( ( p ) => (
										<BirthdayItem
											key={ `u-${ p.user_id }` }
											person={ p }
											today={ false }
											canWish={ false }
											wished={ wished.has( p.user_id ) }
											onWish={ handleWish }
										/>
									) ) }
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
										<li key={ a.id }>
											<button
												type="button"
												onClick={ () => openAnnouncement( a.id ) }
												className="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-left hover:bg-muted/50"
											>
												<span className="min-w-0 truncate text-sm font-medium text-foreground">{ a.title }</span>
												<span className="shrink-0 text-xs text-muted-foreground">{ fmtDate( a.date ) }</span>
											</button>
										</li>
									) ) }
								</ul>
							) }
						</WidgetCard>

						{ /* Pro-module widgets — appended via the `erp_hr_v2_dashboard`
						   PHP filter; render only the ones active modules contributed. */ }
						{ data?.pro_widgets?.map( ( w ) => <ProWidget key={ w.id } widget={ w } /> ) }
					</div>
				</>
			) }

			{ /* Announcement view modal (marks read on open). */ }
			<Dialog open={ viewing !== null || viewLoading } onOpenChange={ ( next ) => ( next ? undefined : setViewing( null ) ) }>
				<DialogContent className="gap-4 rounded-[10px] p-6 sm:max-w-lg">
					<DialogHeader>
						<DialogTitle className="m-0 text-xl font-bold leading-tight tracking-tight text-foreground">
							{ viewing?.title ?? __( 'Announcement', 'erp' ) }
						</DialogTitle>
					</DialogHeader>
					<div className="h-px w-full bg-border" />
					{ viewLoading && ! viewing ? (
						<p className="text-sm text-muted-foreground">{ __( 'Loading…', 'erp' ) }</p>
					) : viewing ? (
						<>
							{ viewing.date ? (
								<p className="text-xs text-muted-foreground">{ fmtDate( viewing.date ) }</p>
							) : null }
							{ /* eslint-disable-next-line react/no-danger -- admin-authored, manage-gated content */ }
							<div
								className="prose prose-sm max-w-none text-sm text-foreground [&_a]:text-primary"
								dangerouslySetInnerHTML={ { __html: viewing.content } }
							/>
						</>
					) : null }
				</DialogContent>
			</Dialog>
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
