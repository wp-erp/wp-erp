/**
 * Composition root for the `/people-saas` route.
 *
 * Third presentation pass on the People page — dense, monochrome,
 * Linear/Vercel/Stripe-style. Same `erp-hr/employees` store reused so the
 * three views (legacy table, glass People Review, SaaS) stay in sync.
 */

import type { JSX } from 'react';

import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';

import { EmployeesError } from '../employees/EmployeesError';
import { EmployeesLiveRegion } from '../employees/EmployeesLiveRegion';
import { useEmployeesQuery } from '../employees/useEmployeesQuery';
import { useEmployeesUrlSync } from '../employees/useEmployeesUrlSync';
import { PeopleSaasBulkBar } from './PeopleSaasBulkBar';
import { PeopleSaasEmpty, PeopleSaasSkeleton } from './PeopleSaasEmpty';
import { PeopleSaasFooter } from './PeopleSaasFooter';
import { PeopleSaasTable } from './PeopleSaasTable';
import { PeopleSaasToolbar } from './PeopleSaasToolbar';

function PeopleSaasInner(): JSX.Element {
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
		<div className="min-h-[calc(100vh-32px-64px)] bg-slate-50/60">
			<PeopleSaasToolbar />

			<div className="mx-auto w-full max-w-7xl px-6 py-4">
				<div className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
					{ error ? (
						<div className="p-6">
							<EmployeesError error={ error } />
						</div>
					) : isLoading && rows.length === 0 ? (
						<PeopleSaasSkeleton />
					) : rows.length === 0 ? (
						<PeopleSaasEmpty hasFiltersApplied={ hasFiltersApplied } />
					) : (
						<>
							<PeopleSaasTable />
							<PeopleSaasFooter />
						</>
					) }
				</div>
			</div>

			<PeopleSaasBulkBar />
			<EmployeesLiveRegion total={ total } isLoading={ isLoading } />
		</div>
	);
}

export function PeopleSaasPage(): JSX.Element {
	return (
		<CapabilityGate caps={ [ 'erp_list_employee' ] }>
			<ErrorBoundary>
				<PeopleSaasInner />
			</ErrorBoundary>
		</CapabilityGate>
	);
}
