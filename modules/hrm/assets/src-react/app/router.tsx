/**
 * React Router v7 route table for the HR admin shell.
 *
 * Single shipped route in this deliverable: `/employees`. Future routes plug
 * in either here directly or via `wp.hooks.applyFilters('erp_hr.routes', baseRoutes)`
 * which pro consumers use.
 */

import { applyFilters } from '@wordpress/hooks';
import { Suspense, lazy } from 'react';
import { Navigate, createHashRouter } from 'react-router-dom';
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

const PeopleReviewPage = lazy( () =>
	import( '@/features/people-review' ).then( ( m ) => ( { default: m.PeopleReviewPage } ) )
);

const PeopleSaasPage = lazy( () =>
	import( '@/features/people-saas' ).then( ( m ) => ( { default: m.PeopleSaasPage } ) )
);

const PeopleProPage = lazy( () =>
	import( '@/features/people-pro' ).then( ( m ) => ( { default: m.PeopleProPage } ) )
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
		id:           'people-review',
		path:         '/people-review',
		element:      PeopleReviewPage,
		capabilities: [ 'erp_list_employee' ],
		handle: {
			id:        'people-review',
			title:     __( 'People Review', 'erp' ),
			navLabel:  __( 'People Review', 'erp' ),
			group:     'people',
			showInNav: true,
		},
	},
	{
		id:           'people-saas',
		path:         '/people-saas',
		element:      PeopleSaasPage,
		capabilities: [ 'erp_list_employee' ],
		handle: {
			id:        'people-saas',
			title:     __( 'People SaaS', 'erp' ),
			navLabel:  __( 'People SaaS', 'erp' ),
			group:     'people',
			showInNav: true,
		},
	},
	{
		id:           'people-pro',
		path:         '/people-pro',
		element:      PeopleProPage,
		capabilities: [ 'erp_list_employee' ],
		handle: {
			id:        'people-pro',
			title:     __( 'People Pro', 'erp' ),
			navLabel:  __( 'People Pro', 'erp' ),
			group:     'people',
			showInNav: true,
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
			{ index: true, element: <Navigate to="/employees" replace /> },
			...wrappedRoutes,
			{ path: '*', element: <NotFound /> },
		],
	},
] );

export { baseRoutes };
