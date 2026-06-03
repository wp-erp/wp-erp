/**
 * Composition root for the `/people-pro` route.
 *
 * Enterprise-grade fourth presentation pass on the People page. Tokens-only
 * styling, generous spacing, clear hierarchy, sub-nav for siblings. Same
 * `erp-hr/employees` store powers the data layer.
 */

import type { JSX } from 'react';

import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';

import { EmployeesError } from '../employees/EmployeesError';
import { EmployeesLiveRegion } from '../employees/EmployeesLiveRegion';
import { useEmployeesQuery } from '../employees/useEmployeesQuery';
import { useEmployeesUrlSync } from '../employees/useEmployeesUrlSync';
import { PeopleProEmpty, PeopleProSkeleton } from './PeopleProEmpty';
import { PeopleProFilters } from './PeopleProFilters';
import { PeopleProFooter } from './PeopleProFooter';
import { PeopleProHeader } from './PeopleProHeader';
import { PeopleProStats } from './PeopleProStats';
import { PeopleProTable } from './PeopleProTable';

function PeopleProInner(): JSX.Element {
	useEmployeesUrlSync();
	const { rows, total, isLoading, error, query } = useEmployeesQuery();

	const hasFiltersApplied = Boolean(
		query.search ||
			query.department_id ||
			query.designation_id ||
			query.location_id ||
			( query.status && query.status !== 'all' )
	);

	return (
		<div className="min-h-[calc(100vh-32px-64px)] bg-background">
			<PeopleProHeader />

			<div className="mx-auto w-full max-w-7xl space-y-6 px-6 py-6">
				<PeopleProStats />

				<div className="overflow-hidden rounded-lg border border-border bg-card shadow-sm">
					<PeopleProFilters />

					{ error ? (
						<div className="p-6">
							<EmployeesError error={ error } />
						</div>
					) : isLoading && rows.length === 0 ? (
						<PeopleProSkeleton />
					) : rows.length === 0 ? (
						<PeopleProEmpty hasFiltersApplied={ hasFiltersApplied } />
					) : (
						<>
							<PeopleProTable />
							<PeopleProFooter />
						</>
					) }
				</div>
			</div>

			<EmployeesLiveRegion total={ total } isLoading={ isLoading } />
		</div>
	);
}

export function PeopleProPage(): JSX.Element {
	return (
		<CapabilityGate caps={ [ 'erp_list_employee' ] }>
			<ErrorBoundary>
				<PeopleProInner />
			</ErrorBoundary>
		</CapabilityGate>
	);
}
