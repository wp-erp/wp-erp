/**
 * React Router v7 route table for the HR admin shell.
 *
 * Single shipped route in this deliverable: `/employees`. Future routes plug
 * in either here directly or via `wp.hooks.applyFilters('erp_hr.routes', baseRoutes)`
 * which pro consumers use.
 */

import { applyFilters } from '@wordpress/hooks';
import { Suspense, lazy } from 'react';
import { createHashRouter } from 'react-router-dom';
import type { RouteObject } from 'react-router-dom';

import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { HOOKS } from '@/shared/filters';
import { __ } from '@/shared/i18n';
import type { Capability, RouteHandle } from '@/types/global';

import { AppShell } from './AppShell';

const EmployeesPage = lazy( () =>
	import( '@/features/employees' ).then( ( m ) => ( { default: m.EmployeesPage } ) )
);

const EmployeeCreatePage = lazy( () =>
	import( '@/features/employee-create' ).then( ( m ) => ( { default: m.EmployeeCreatePage } ) )
);

const EmployeeEditPage = lazy( () =>
	import( '@/features/employee-create' ).then( ( m ) => ( { default: m.EmployeeEditPage } ) )
);

// [NEW-PROFILE] Modern profile page. Self-contained — to remove it, delete the
// `features/employee-profile/` folder and every block tagged `[NEW-PROFILE]`
// (this lazy import, the route entry below, and the "New design" button in
// EmployeeSinglePage.tsx). Nothing else depends on it.
const EmployeeProfilePage = lazy( () =>
	import( '@/features/employee-profile' ).then( ( m ) => ( { default: m.EmployeeProfilePage } ) )
);

// [NEW-PROFILE-V2] Profile-card + dark-pill-tabs layout. Self-contained — to
// remove it, delete the `features/employee-profile-v2/` folder and every block
// tagged `[NEW-PROFILE-V2]` (this lazy import, the route entry below, and the
// "New design v2" button in EmployeeSinglePage.tsx). Nothing else depends on it.
const EmployeeProfileV2Page = lazy( () =>
	import( '@/features/employee-profile-v2' ).then( ( m ) => ( { default: m.EmployeeProfileV2Page } ) )
);

// [NEW-PROFILE-V3] Big-square-portrait + dashboard-card layout. Self-contained —
// to remove it, delete the `features/employee-profile-v3/` folder and every
// block tagged `[NEW-PROFILE-V3]` (this lazy import, the route entry below, and
// the "New design v3" button in EmployeeSinglePage.tsx). Nothing depends on it.
const EmployeeProfileV3Page = lazy( () =>
	import( '@/features/employee-profile-v3' ).then( ( m ) => ( { default: m.EmployeeProfileV3Page } ) )
);

// The v4 design is the main employee profile page (`/employees/:id`); it is also
// still reachable at `/employees/:id/profile-v4` while the older previews stay.
const EmployeeProfileV4Page = lazy( () =>
	import( '@/features/employee-profile-v4' ).then( ( m ) => ( { default: m.EmployeeProfileV4Page } ) )
);

const DepartmentsPage = lazy( () =>
	import( '@/features/departments' ).then( ( m ) => ( { default: m.DepartmentsPage } ) )
);

const DesignationsPage = lazy( () =>
	import( '@/features/designations' ).then( ( m ) => ( { default: m.DesignationsPage } ) )
);

const OrgChartPage = lazy( () =>
	import( '@/features/org-chart' ).then( ( m ) => ( { default: m.OrgChartPage } ) )
);

const HolidaysPage = lazy( () =>
	import( '@/features/holidays' ).then( ( m ) => ( { default: m.HolidaysPage } ) )
);

const LeaveTypesPage = lazy( () =>
	import( '@/features/leave-types' ).then( ( m ) => ( { default: m.LeaveTypesPage } ) )
);

const LeavePoliciesPage = lazy( () =>
	import( '@/features/leave-policies' ).then( ( m ) => ( { default: m.LeavePoliciesPage } ) )
);

const LeaveEntitlementsPage = lazy( () =>
	import( '@/features/leave-entitlements' ).then( ( m ) => ( { default: m.LeaveEntitlementsPage } ) )
);

const LeaveRequestsPage = lazy( () =>
	import( '@/features/leave-requests' ).then( ( m ) => ( { default: m.LeaveRequestsPage } ) )
);

const LeaveCalendarPage = lazy( () =>
	import( '@/features/leave-calendar' ).then( ( m ) => ( { default: m.LeaveCalendarPage } ) )
);

// Advanced Leave (pro) pages — mounted in the free router but only reachable via
// the Leave dropdown when the `advanced_leave` module is active. They consume
// the pro `erp/v2/hrm/advance-leave/*` endpoints.
const LeaveUnpaidPage = lazy( () =>
	import( '@/features/leave-unpaid' ).then( ( m ) => ( { default: m.LeaveUnpaidPage } ) )
);

const LeaveForwardPage = lazy( () =>
	import( '@/features/leave-forward' ).then( ( m ) => ( { default: m.LeaveForwardPage } ) )
);

const AnnouncementsPage = lazy( () =>
	import( '@/features/announcements' ).then( ( m ) => ( { default: m.AnnouncementsPage } ) )
);

const DashboardPage = lazy( () =>
	import( '@/features/dashboard' ).then( ( m ) => ( { default: m.DashboardPage } ) )
);

const MyProfilePage = lazy( () =>
	import( '@/features/my-profile' ).then( ( m ) => ( { default: m.MyProfilePage } ) )
);

const HelpPage = lazy( () =>
	import( '@/features/help' ).then( ( m ) => ( { default: m.HelpPage } ) )
);

const AgeProfilePage = lazy( () =>
	import( '@/features/reports' ).then( ( m ) => ( { default: m.AgeProfilePage } ) )
);

const GenderProfilePage = lazy( () =>
	import( '@/features/reports' ).then( ( m ) => ( { default: m.GenderProfilePage } ) )
);

const HeadcountReportPage = lazy( () =>
	import( '@/features/reports' ).then( ( m ) => ( { default: m.HeadcountPage } ) )
);

const SalaryHistoryPage = lazy( () =>
	import( '@/features/reports' ).then( ( m ) => ( { default: m.SalaryHistoryPage } ) )
);

const YearsOfServicePage = lazy( () =>
	import( '@/features/reports' ).then( ( m ) => ( { default: m.YearsOfServicePage } ) )
);

const LeavesReportPage = lazy( () =>
	import( '@/features/reports' ).then( ( m ) => ( { default: m.LeavesReportPage } ) )
);

export interface AppRoute {
	readonly id:            string;
	readonly path:          string;
	readonly element:       React.ComponentType;
	readonly capabilities?: readonly Capability[];
	readonly handle:        RouteHandle;
}

function NotFound(): React.ReactElement {
	return (
		<div className="mx-auto my-12 max-w-md text-center">
			<h2 className="text-base font-semibold text-foreground">
				{ __( 'Page not found', 'erp' ) }
			</h2>
			<p className="mt-2 text-sm text-muted-foreground">
				{ __( 'The page you tried to open does not exist in the new HR admin yet.', 'erp' ) }
			</p>
		</div>
	);
}

function RouteSkeleton(): React.ReactElement {
	return (
		<div className="mx-auto my-12 max-w-md text-center text-sm text-muted-foreground">
			{ __( 'Loading…', 'erp' ) }
		</div>
	);
}

const baseRoutes: AppRoute[] = [
	{
		id:           'employees',
		path:         '/employees',
		element:      EmployeesPage,
		capabilities: [ 'erp_list_employee' ],
		handle: {
			id:        'employees',
			title:     __( 'Employees', 'erp' ),
			navLabel:  __( 'People', 'erp' ),
			group:     'people',
			showInNav: true,
		},
	},
	{
		id:           'employee-create',
		path:         '/employees/new',
		element:      EmployeeCreatePage,
		capabilities: [ 'erp_create_employee' ],
		handle: {
			id:        'employee-create',
			title:     __( 'Add New Employee', 'erp' ),
			group:     'people',
			showInNav: false,
		},
	},
	{
		id:           'employee-single',
		path:         '/employees/:id',
		// The main employee page = `EmployeeProfileV4Page` (the sidebar/modern design,
		// shown in the menu as "View profile v4"). This is the canonical main view.
		element:      EmployeeProfileV4Page,
		// Legacy parity: viewing a profile needs only the People-list cap (the menu
		// gate); `erp_view_employee` is a meta cap that would block non-managers.
		capabilities: [ 'erp_list_employee' ],
		handle: {
			id:        'employee-single',
			title:     __( 'Employee', 'erp' ),
			group:     'people',
			showInNav: false,
		},
	},
	{
		// [NEW-PROFILE] Route for the modern profile page (see note at the lazy import).
		id:           'employee-profile',
		path:         '/employees/:id/profile',
		// Same gate as the classic single page — view access is the People-list cap.
		capabilities: [ 'erp_list_employee' ],
		element:      EmployeeProfilePage,
		handle: {
			id:        'employee-profile',
			title:     __( 'Employee', 'erp' ),
			group:     'people',
			showInNav: false,
		},
	},
	{
		// [NEW-PROFILE-V2] Route for the profile-card layout (see note at the lazy import).
		id:           'employee-profile-v2',
		path:         '/employees/:id/profile-v2',
		capabilities: [ 'erp_list_employee' ],
		element:      EmployeeProfileV2Page,
		handle: {
			id:        'employee-profile-v2',
			title:     __( 'Employee', 'erp' ),
			group:     'people',
			showInNav: false,
		},
	},
	{
		// [NEW-PROFILE-V3] Route for the big-square-portrait layout (see note at the lazy import).
		id:           'employee-profile-v3',
		path:         '/employees/:id/profile-v3',
		capabilities: [ 'erp_list_employee' ],
		element:      EmployeeProfileV3Page,
		handle: {
			id:        'employee-profile-v3',
			title:     __( 'Employee', 'erp' ),
			group:     'people',
			showInNav: false,
		},
	},
	{
		// [NEW-PROFILE-V4] Route for the header-card + left-nav layout (also the main page above).
		id:           'employee-profile-v4',
		path:         '/employees/:id/profile-v4',
		capabilities: [ 'erp_list_employee' ],
		element:      EmployeeProfileV4Page,
		handle: {
			id:        'employee-profile-v4',
			title:     __( 'Employee', 'erp' ),
			group:     'people',
			showInNav: false,
		},
	},
	{
		id:           'employee-edit',
		path:         '/employees/:id/edit',
		element:      EmployeeEditPage,
		// No route-level cap: the page itself allows `erp_edit_employee` OR the
		// employee editing their OWN profile (the route guard can't see the :id).
		handle: {
			id:        'employee-edit',
			title:     __( 'Edit Employee', 'erp' ),
			group:     'people',
			showInNav: false,
		},
	},
	{
		id:           'departments',
		path:         '/departments',
		element:      DepartmentsPage,
		capabilities: [ 'erp_view_list' ],
		handle: {
			id:        'departments',
			title:     __( 'Departments', 'erp' ),
			group:     'people',
			showInNav: false,
		},
	},
	{
		id:           'designations',
		path:         '/designations',
		element:      DesignationsPage,
		capabilities: [ 'erp_view_list' ],
		handle: {
			id:        'designations',
			title:     __( 'Designations', 'erp' ),
			group:     'people',
			showInNav: false,
		},
	},
	{
		id:           'org-chart',
		path:         '/org-chart',
		element:      OrgChartPage,
		capabilities: [ 'erp_view_list' ],
		handle: {
			id:        'org-chart',
			title:     __( 'Org Chart', 'erp' ),
			group:     'people',
			showInNav: false,
		},
	},
	{
		id:           'announcements',
		path:         '/announcements',
		element:      AnnouncementsPage,
		capabilities: [ 'erp_view_announcement' ],
		handle: {
			id:        'announcements',
			title:     __( 'Announcements', 'erp' ),
			group:     'people',
			showInNav: false,
		},
	},
	{
		id:           'leave-requests',
		path:         '/leave/requests',
		element:      LeaveRequestsPage,
		capabilities: [ 'erp_leave_manage' ],
		handle: {
			id:        'leave-requests',
			title:     __( 'Leave Requests', 'erp' ),
			group:     'leave',
			showInNav: false,
		},
	},
	{
		id:           'leave-types',
		path:         '/leave/types',
		element:      LeaveTypesPage,
		capabilities: [ 'erp_leave_manage' ],
		handle: {
			id:        'leave-types',
			title:     __( 'Leave Types', 'erp' ),
			group:     'leave',
			showInNav: false,
		},
	},
	{
		id:           'leave-policies',
		path:         '/leave/policies',
		element:      LeavePoliciesPage,
		capabilities: [ 'erp_leave_manage' ],
		handle: {
			id:        'leave-policies',
			title:     __( 'Leave Policies', 'erp' ),
			group:     'leave',
			showInNav: false,
		},
	},
	{
		id:           'leave-entitlements',
		path:         '/leave/entitlements',
		element:      LeaveEntitlementsPage,
		capabilities: [ 'erp_leave_manage' ],
		handle: {
			id:        'leave-entitlements',
			title:     __( 'Leave Entitlements', 'erp' ),
			group:     'leave',
			showInNav: false,
		},
	},
	{
		id:           'leave-calendar',
		path:         '/leave/calendar',
		element:      LeaveCalendarPage,
		capabilities: [ 'erp_leave_manage' ],
		handle: {
			id:        'leave-calendar',
			title:     __( 'Leave Calendar', 'erp' ),
			group:     'leave',
			showInNav: false,
		},
	},
	{
		id:           'holidays',
		path:         '/leave/holidays',
		element:      HolidaysPage,
		capabilities: [ 'erp_leave_manage' ],
		handle: {
			id:        'holidays',
			title:     __( 'Holidays', 'erp' ),
			group:     'leave',
			showInNav: false,
		},
	},
	{
		id:           'leave-unpaid',
		path:         '/leave/unpaid',
		element:      LeaveUnpaidPage,
		capabilities: [ 'erp_leave_manage' ],
		handle: {
			id:        'leave-unpaid',
			title:     __( 'Unpaid Leaves', 'erp' ),
			group:     'leave',
			showInNav: false,
		},
	},
	{
		id:           'leave-forward',
		path:         '/leave/forward',
		element:      LeaveForwardPage,
		capabilities: [ 'erp_leave_manage' ],
		handle: {
			id:        'leave-forward',
			title:     __( 'Forward Leaves', 'erp' ),
			group:     'leave',
			showInNav: false,
		},
	},
	{
		id:           'reports-age-profile',
		path:         '/reports/age-profile',
		element:      AgeProfilePage,
		capabilities: [ 'erp_hr_manager' ],
		handle: {
			id:        'reports-age-profile',
			title:     __( 'Age Profile', 'erp' ),
			group:     'reports',
			showInNav: false,
		},
	},
	{
		id:           'reports-gender-profile',
		path:         '/reports/gender-profile',
		element:      GenderProfilePage,
		capabilities: [ 'erp_hr_manager' ],
		handle: {
			id:        'reports-gender-profile',
			title:     __( 'Gender Profile', 'erp' ),
			group:     'reports',
			showInNav: false,
		},
	},
	{
		id:           'reports-headcount',
		path:         '/reports/headcount',
		element:      HeadcountReportPage,
		capabilities: [ 'erp_hr_manager' ],
		handle: {
			id:        'reports-headcount',
			title:     __( 'Head Count', 'erp' ),
			group:     'reports',
			showInNav: false,
		},
	},
	{
		id:           'reports-salary-history',
		path:         '/reports/salary-history',
		element:      SalaryHistoryPage,
		capabilities: [ 'erp_hr_manager' ],
		handle: {
			id:        'reports-salary-history',
			title:     __( 'Salary History', 'erp' ),
			group:     'reports',
			showInNav: false,
		},
	},
	{
		id:           'reports-years-of-service',
		path:         '/reports/years-of-service',
		element:      YearsOfServicePage,
		capabilities: [ 'erp_hr_manager' ],
		handle: {
			id:        'reports-years-of-service',
			title:     __( 'Years of Service', 'erp' ),
			group:     'reports',
			showInNav: false,
		},
	},
	{
		id:           'reports-leaves',
		path:         '/reports/leaves',
		element:      LeavesReportPage,
		capabilities: [ 'erp_hr_manager' ],
		handle: {
			id:        'reports-leaves',
			title:     __( 'Leaves', 'erp' ),
			group:     'reports',
			showInNav: false,
		},
	},
	{
		id:           'my-profile',
		path:         '/my-profile',
		element:      MyProfilePage,
		capabilities: [ 'read' ],
		handle: {
			id:        'my-profile',
			title:     __( 'My Profile', 'erp' ),
			group:     'people',
			showInNav: false,
		},
	},
	{
		id:           'help',
		path:         '/help',
		element:      HelpPage,
		capabilities: [],
		handle: {
			id:        'help',
			title:     __( 'Help', 'erp' ),
			group:     'help',
			showInNav: false,
		},
	},
];

// FUTURE ROUTES (each one wired in its own deliverable):
//   /                       → Overview / Dashboard
//   /leave                  → Leave requests
//   /leave/calendar         → Leave calendar
//   /leave/policies         → Leave policies
//   /leave/entitlements     → Leave entitlements
//   /leave/holidays         → Holidays
//   /announcements          → Announcements
//   /reports/:type?         → Reports
//   /employees/:id/:tab?    → Employee single

function wrapRoutes(): RouteObject[] {
	// Apply the `erp_hr.routes` filter HERE (at build time), not at module eval —
	// pro bundles (attendance, documents, …) load AFTER the free app and register
	// their routes via `addFilter`. Building the router lazily at mount (see
	// `buildHashRouter`) means those late registrations are included. Mirrors
	// Dokan's `getRoutes()` called inside the dashboard component at render.
	const filteredRoutes = applyFilters( HOOKS.ROUTES, baseRoutes ) as AppRoute[];

	return filteredRoutes.map( ( route ) => ( {
		path:   route.path,
		handle: route.handle,
		element: (
			<CapabilityGate caps={ route.capabilities ?? [] }>
				<ErrorBoundary>
					<Suspense fallback={ <RouteSkeleton /> }>
						<route.element />
					</Suspense>
				</ErrorBoundary>
			</CapabilityGate>
		),
	} ) );
}

let cachedRouter: ReturnType< typeof createHashRouter > | null = null;

/**
 * Build (once) the hash router, applying `erp_hr.routes` at call time. Call this
 * from the mount path (on DOMContentLoaded) so every pro bundle's `addFilter`
 * registration has already run.
 */
export function buildHashRouter(): ReturnType< typeof createHashRouter > {
	if ( cachedRouter ) {
		return cachedRouter;
	}

	cachedRouter = createHashRouter( [
		{
			element: <AppShell />,
			children: [
				{
					index: true,
					handle: { id: 'overview', title: __( 'Overview', 'erp' ), group: 'people', showInNav: false },
					element: (
						<CapabilityGate caps={ [ 'read' ] }>
							<ErrorBoundary>
								<Suspense fallback={ <RouteSkeleton /> }>
									<DashboardPage />
								</Suspense>
							</ErrorBoundary>
						</CapabilityGate>
					),
				},
				...wrapRoutes(),
				{ path: '*', element: <NotFound /> },
			],
		},
	] );

	return cachedRouter;
}

export { baseRoutes };
