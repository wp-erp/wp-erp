/**
 * `/` (Overview) — the HR dashboard landing page.
 *
 * Modern card-based layout over `GET /erp/v2/dashboard`: a greeting, a row of
 * summary stat cards, then a two-column grid of widgets (who's out, birthdays,
 * upcoming holidays, latest announcements) — the same information the legacy
 * `views/dashboard.php` widgets surface, restyled for the React design system.
 *
 * This file is the orchestrator: data loading + page composition. The
 * presentational pieces live in `DashboardCards.tsx` (layout primitives),
 * `DashboardWidgets.tsx` (data rows) and `format.ts` (helpers).
 */

import { useSelect } from '@wordpress/data';
import { applyFilters } from '@wordpress/hooks';
import {
	Dialog,
	DialogContent,
	DialogHeader,
	DialogTitle,
	toast,
} from '@wedevs/plugin-ui';
import {
	Briefcase,
	Building2,
	Cake,
	CalendarClock,
	Clock4,
	Hourglass,
	Megaphone,
	PalmtreeIcon,
	UserPlus,
	Users,
} from 'lucide-react';
import { useState } from 'react';
import type { ComponentType, JSX } from 'react';
import { Link } from 'react-router-dom';

import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { HOOKS } from '@/shared/filters';
import { useCan } from '@/shared/hooks/useCan';
import { __, sprintf } from '@/shared/i18n';
import { storeName as meStoreName } from '@/stores/me';
import type { MeUser } from '@/stores/me/types';

import {
	EmptyRow,
	LiveTime,
	SectionLabel,
	StatCard,
	WidgetCard,
} from './DashboardCards';
import { ChartsSection } from './DashboardCharts';
import {
	AboutToEndItem,
	BirthdayItem,
	OnLeaveItem,
	ProWidget,
} from './DashboardWidgets';
import { fmtDate, greeting } from './format';
import { MiniCalendarWidget } from './MiniCalendarWidget';
import { WeatherWidget } from './WeatherWidget';
import {
	fetchAnnouncement,
	markAnnouncementRead,
	sendBirthdayWish,
	useDashboard,
} from './useDashboard';
import type { AnnouncementContent } from './useDashboard';

function DashboardInner(): JSX.Element {
	const { data, loading, error } = useDashboard();
	const user = useSelect(
		( select ) =>
			(
				select( meStoreName ) as { getUser: () => MeUser | null }
			 ).getUser(),
		[]
	);
	const canCreateEmployee = useCan( 'erp_create_employee' );
	const canManageLeave = useCan( 'erp_leave_manage' );

	const currentUserId = user?.id ?? 0;
	const name = user?.displayName ? user.displayName.split( ' ' )[ 0 ] : '';

	const [ viewing, setViewing ] = useState< AnnouncementContent | null >(
		null
	);
	const [ viewLoading, setViewLoading ] = useState( false );
	const [ wished, setWished ] = useState< ReadonlySet< number > >(
		new Set()
	);
	// Locally-marked-read announcements (optimistic; the dashboard payload
	// carries the persisted per-user read state on load).
	const [ readAnnouncements, setReadAnnouncements ] = useState<
		ReadonlySet< number >
	>( new Set() );

	function openAnnouncement( id: number ): void {
		setViewLoading( true );
		setViewing( null );
		setReadAnnouncements( ( prev ) => new Set( prev ).add( id ) );
		void markAnnouncementRead( id ).catch( () => undefined );
		void fetchAnnouncement( id )
			.then( ( content ) => setViewing( content ) )
			.catch( () =>
				toast.error( __( 'Could not open the announcement.', 'erp' ) )
			)
			.finally( () => setViewLoading( false ) );
	}

	function handleWish( employeeUserId: number ): void {
		void sendBirthdayWish( employeeUserId )
			.then( () => {
				setWished( ( prev ) => new Set( prev ).add( employeeUserId ) );
				toast.success( __( 'Birthday wish sent!', 'erp' ) );
			} )
			.catch( () =>
				toast.error( __( 'Could not send the birthday wish.', 'erp' ) )
			);
	}

	if ( error ) {
		return (
			<p className="mx-auto my-12 max-w-md text-center text-sm text-destructive">
				{ error }
			</p>
		);
	}

	const summary = data?.summary;
	const isManager = data?.is_hr_manager ?? false;

	// Pro self-service widgets (e.g. Attendance punch card) appended via the
	// `erp_hr.dashboard.widgets` filter; applied lazily so pro bundles that load
	// after the free app are included. They drop straight into the card grid.
	const proSelfWidgets = applyFilters(
		HOOKS.DASHBOARD_WIDGETS,
		[]
	) as ComponentType[];

	return (
		<section className="mx-auto w-full max-w-full">
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
			<header className="relative mb-6 flex flex-wrap items-center justify-between gap-x-5 gap-y-3 overflow-hidden rounded-lg bg-card p-6 shadow-sm ring-1 ring-border/50">
				<div className="relative">
					<h1 className="text-3xl font-bold leading-9 tracking-tight text-foreground">
						{ name
							? sprintf(
									__( '%1$s, %2$s', 'erp' ),
									greeting(),
									name
							  )
							: greeting() }
						<span
							className="ml-1.5 inline-block"
							aria-hidden="true"
						>
							👋
						</span>
					</h1>
					<p className="mt-1.5 flex flex-wrap items-center gap-x-1.5 text-sm text-muted-foreground">
						{ new Date().toLocaleDateString( undefined, {
							weekday: 'long',
							year: 'numeric',
							month: 'long',
							day: 'numeric',
						} ) }
						<span aria-hidden="true">·</span>
						<LiveTime />
					</p>
				</div>
				<div className="relative">
					<WeatherWidget embedded />
				</div>
			</header>

			{ loading && ! data ? (
				<div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
					{ [ 0, 1, 2, 3 ].map( ( i ) => (
						<div
							key={ i }
							className="h-24 animate-pulse rounded-[10px] bg-card shadow-sm"
						/>
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
								value={
									( data?.birthdays_today.length ?? 0 ) +
									( data?.birthdays_upcoming.length ?? 0 )
								}
								tint="bg-pink-500/10 text-pink-600 dark:text-pink-400"
							/>
						) }
					</div>

					{ /* Widget grid — a robust multi-card layout (Figma dashboard):
					   compact calendar leads, then the activity cards + any pro
					   module / self-service widgets, all on one responsive grid. */ }
					<SectionLabel className="mt-8">
						{ __( 'Activity', 'erp' ) }
					</SectionLabel>
					<div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
						{ /* Compact calendar */ }
						<MiniCalendarWidget />

						{ /* Who's out */ }
						<WidgetCard
							icon={ CalendarClock }
							title={ __( 'Who’s Out', 'erp' ) }
							count={ data?.on_leave.length }
							action={
								canManageLeave
									? {
											label: __( 'Calendar', 'erp' ),
											to: '/leave/calendar',
									  }
									: undefined
							}
						>
							{ ( data?.on_leave.length ?? 0 ) === 0 ? (
								<EmptyRow
									text={ __(
										'Nobody is on leave this or next month.',
										'erp'
									) }
								/>
							) : (
								( () => {
									const thisMonth =
										data?.on_leave.filter(
											( p ) => p.period !== 'next_month'
										) ?? [];
									const nextMonth =
										data?.on_leave.filter(
											( p ) => p.period === 'next_month'
										) ?? [];
									return (
										<>
											{ thisMonth.length > 0 ? (
												<>
													<p className="px-3 pt-1 pb-1 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
														{ __(
															'This Month',
															'erp'
														) }
													</p>
													<ul>
														{ thisMonth.map(
															( p ) => (
																<OnLeaveItem
																	key={ `t-${ p.user_id }-${ p.start_date }` }
																	person={ p }
																/>
															)
														) }
													</ul>
												</>
											) : null }
											{ nextMonth.length > 0 ? (
												<>
													<p className="px-3 pt-2 pb-1 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
														{ __(
															'Next Month',
															'erp'
														) }
													</p>
													<ul>
														{ nextMonth.map(
															( p ) => (
																<OnLeaveItem
																	key={ `n-${ p.user_id }-${ p.start_date }` }
																	person={ p }
																/>
															)
														) }
													</ul>
												</>
											) : null }
										</>
									);
								} )()
							) }
						</WidgetCard>

						{ /* About to End — contractual & trainee employees whose job
						   period ends within 21 days. Manager-only (legacy gate). */ }
						{ isManager &&
						( data?.about_to_end?.contract.length ?? 0 ) +
							( data?.about_to_end?.trainee.length ?? 0 ) >
							0 ? (
							<WidgetCard
								icon={ Hourglass }
								title={ __( 'About to End', 'erp' ) }
								count={
									( data?.about_to_end?.contract.length ??
										0 ) +
									( data?.about_to_end?.trainee.length ?? 0 )
								}
							>
								{ ( data?.about_to_end?.contract.length ?? 0 ) >
								0 ? (
									<>
										<p className="px-3 pt-1 pb-1 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
											{ __(
												'Contractual Employees',
												'erp'
											) }
										</p>
										<ul>
											{ data?.about_to_end?.contract.map(
												( p ) => (
													<AboutToEndItem
														key={ `c-${ p.user_id }` }
														person={ p }
													/>
												)
											) }
										</ul>
									</>
								) : null }
								{ ( data?.about_to_end?.trainee.length ?? 0 ) >
								0 ? (
									<>
										<p className="px-3 pt-2 pb-1 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
											{ __( 'Trainee Employees', 'erp' ) }
										</p>
										<ul>
											{ data?.about_to_end?.trainee.map(
												( p ) => (
													<AboutToEndItem
														key={ `tr-${ p.user_id }` }
														person={ p }
													/>
												)
											) }
										</ul>
									</>
								) : null }
							</WidgetCard>
						) : null }

						{ /* Pro self-service widgets (e.g. Attendance punch card) first,
						   so the Attendance card lands top-right (row 1, col 3) for every
						   role — admins get extra `pro_widgets` below it that would
						   otherwise push the punch card out of that slot. Swapped with
						   Birthdays per the dashboard layout request. */ }
						{ proSelfWidgets.map( ( Widget, i ) => (
							<Widget key={ `self-${ i }` } />
						) ) }

						{ /* Pro-module widgets — appended via the `erp_hr_v2_dashboard`
						   PHP filter; render only the ones active modules contributed. */ }
						{ data?.pro_widgets?.map( ( w ) => (
							<ProWidget key={ w.id } widget={ w } />
						) ) }

						{ /* Upcoming holidays */ }
						<WidgetCard
							icon={ PalmtreeIcon }
							title={ __( 'Upcoming Holidays', 'erp' ) }
							action={
								canManageLeave
									? {
											label: __( 'All', 'erp' ),
											to: '/leave/holidays',
									  }
									: undefined
							}
						>
							{ ( data?.holidays_upcoming.length ?? 0 ) === 0 ? (
								<EmptyRow
									text={ __(
										'No holidays in the next 30 days.',
										'erp'
									) }
								/>
							) : (
								<ul>
									{ data?.holidays_upcoming.map( ( h ) => (
										<li
											key={ h.id }
											className="flex items-center justify-between gap-3 rounded-lg px-3 py-2 hover:bg-muted/50"
										>
											<span className="min-w-0 truncate text-sm font-medium text-foreground">
												{ h.title }
											</span>
											<span className="shrink-0 text-xs text-muted-foreground">
												{ h.start === h.end || ! h.end
													? fmtDate( h.start )
													: `${ fmtDate(
															h.start
													  ) } – ${ fmtDate(
															h.end
													  ) }` }
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
							action={ {
								label: __( 'All', 'erp' ),
								to: '/announcements',
							} }
						>
							{ ( data?.announcements.length ?? 0 ) === 0 ? (
								<EmptyRow
									text={ __(
										'No announcements yet.',
										'erp'
									) }
								/>
							) : (
								<ul>
									{ data?.announcements.map( ( a ) => {
										const unread =
											! a.read &&
											! readAnnouncements.has( a.id );
										return (
											<li key={ a.id }>
												<button
													type="button"
													onClick={ () =>
														openAnnouncement( a.id )
													}
													className="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-left hover:bg-muted/50"
												>
													<span className="flex min-w-0 items-center gap-2">
														{ unread ? (
															<span
																aria-hidden="true"
																className="size-2 shrink-0 rounded-full bg-primary"
															/>
														) : null }
														<span
															className={ `min-w-0 truncate text-sm ${
																unread
																	? 'font-semibold text-foreground'
																	: 'font-medium text-muted-foreground'
															}` }
														>
															{ a.title }
														</span>
														{ unread ? (
															<span className="sr-only">
																{ __(
																	'(unread)',
																	'erp'
																) }
															</span>
														) : null }
													</span>
													<span className="shrink-0 text-xs text-muted-foreground">
														{ fmtDate( a.date ) }
													</span>
												</button>
											</li>
										);
									} ) }
								</ul>
							) }
						</WidgetCard>

						{ /* Birthdays — moved to the end (slot after announcements) so it
						   sits below the Attendance card; swapped with the pro widgets
						   above per the dashboard layout request. */ }
						<WidgetCard
							icon={ Cake }
							title={ __( 'Birthdays', 'erp' ) }
							count={
								( data?.birthdays_today.length ?? 0 ) +
								( data?.birthdays_upcoming.length ?? 0 )
							}
						>
							{ ( data?.birthdays_today.length ?? 0 ) === 0 &&
							( data?.birthdays_upcoming.length ?? 0 ) === 0 ? (
								<EmptyRow
									text={ __(
										'No birthdays in the next 7 days.',
										'erp'
									) }
								/>
							) : (
								<ul>
									{ data?.birthdays_today.map( ( p ) => (
										<BirthdayItem
											key={ `t-${ p.user_id }` }
											person={ p }
											today
											canWish={
												p.user_id !== currentUserId
											}
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
					</div>

					{ /* Analytics charts — sit below the activity cards. */ }
					{ data ? (
						<div className="mt-8">
							<SectionLabel>
								{ __( 'Analytics', 'erp' ) }
							</SectionLabel>
							<ChartsSection
								charts={ data.charts }
								isManager={ isManager }
							/>
						</div>
					) : null }
				</>
			) }

			{ /* Announcement view modal (marks read on open). */ }
			<Dialog
				open={ viewing !== null || viewLoading }
				onOpenChange={ ( next ) =>
					next ? undefined : setViewing( null )
				}
			>
				<DialogContent className="gap-4 rounded-[10px] p-6 sm:max-w-lg">
					<DialogHeader>
						<DialogTitle className="m-0 mb-4 text-xl font-bold leading-tight tracking-tight text-foreground">
							{ viewing?.title ?? __( 'Announcement', 'erp' ) }
						</DialogTitle>
					</DialogHeader>
					<div className="h-px w-full bg-border" />
					{ viewLoading && ! viewing ? (
						<p className="text-sm text-muted-foreground">
							{ __( 'Loading…', 'erp' ) }
						</p>
					) : viewing ? (
						<>
							{ viewing.date ? (
								<p className="text-xs text-muted-foreground">
									{ fmtDate( viewing.date ) }
								</p>
							) : null }
							{ /* eslint-disable-next-line react/no-danger -- admin-authored, manage-gated content */ }
							<div
								className="prose prose-sm max-w-none text-sm text-foreground [&_a]:text-primary"
								dangerouslySetInnerHTML={ {
									__html: viewing.content,
								} }
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
