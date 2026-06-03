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

const EmployeeSinglePage = lazy( () =>
	import( '@/features/employee-create' ).then( ( m ) => ( { default: m.EmployeeSinglePage } ) )
);

const DepartmentsPage = lazy( () =>
	import( '@/features/departments' ).then( ( m ) => ( { default: m.DepartmentsPage } ) )
);

const DesignationsPage = lazy( () =>
	import( '@/features/designations' ).then( ( m ) => ( { default: m.DesignationsPage } ) )
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

const AnnouncementsPage = lazy( () =>
	import( '@/features/announcements' ).then( ( m ) => ( { default: m.AnnouncementsPage } ) )
);

const DashboardPage = lazy( () =>
	import( '@/features/dashboard' ).then( ( m ) => ( { default: m.DashboardPage } ) )
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
		element:      EmployeeSinglePage,
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
		id:           'employee-edit',
		path:         '/employees/:id/edit',
		element:      EmployeeEditPage,
		capabilities: [ 'erp_edit_employee' ],
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

const filteredRoutes = applyFilters( HOOKS.ROUTES, baseRoutes ) as AppRoute[];

const wrappedRoutes: RouteObject[] = filteredRoutes.map( ( route ) => ( {
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

export const hashRouter = createHashRouter( [
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
			...wrappedRoutes,
			{ path: '*', element: <NotFound /> },
		],
	},
] );

export { baseRoutes };
