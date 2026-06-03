/**
 * Composition root for the `/people-review` route.
 *
 * Modern design exploration sibling to `features/employees/EmployeesPage`.
 * Same underlying `erp-hr/employees` store and `useEmployeesQuery` hook —
 * different presentation layer (hero KPIs, segmented pills, card-rows).
 */

import type { JSX } from 'react';

import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';

import { EmployeesError } from '../employees/EmployeesError';
import { EmployeesLiveRegion } from '../employees/EmployeesLiveRegion';
import { useEmployeesQuery } from '../employees/useEmployeesQuery';
import { useEmployeesUrlSync } from '../employees/useEmployeesUrlSync';
import { PeopleReviewEmpty } from './PeopleReviewEmpty';
import { PeopleReviewFilters } from './PeopleReviewFilters';
import { PeopleReviewList } from './PeopleReviewList';
import { PeopleReviewSkeleton } from './PeopleReviewSkeleton';

function PeopleReviewPageInner(): JSX.Element {
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
		<div className="relative isolate min-h-screen overflow-hidden">
			<span
				aria-hidden="true"
				className="pointer-events-none absolute inset-0 -z-20 bg-gradient-to-br from-sky-100 via-blue-50 to-indigo-100"
			/>
			<span
				aria-hidden="true"
				className="pointer-events-none absolute -left-24 -top-24 -z-10 size-[560px] rounded-full bg-gradient-to-br from-sky-400/50 via-blue-400/35 to-transparent blur-3xl"
			/>
			<span
				aria-hidden="true"
				className="pointer-events-none absolute right-[-10rem] top-[15%] -z-10 size-[620px] rounded-full bg-gradient-to-br from-blue-500/45 via-indigo-400/30 to-transparent blur-3xl"
			/>
			<span
				aria-hidden="true"
				className="pointer-events-none absolute left-[15%] top-[50%] -z-10 size-[540px] rounded-full bg-gradient-to-br from-cyan-400/40 via-sky-300/30 to-transparent blur-3xl"
			/>
			<span
				aria-hidden="true"
				className="pointer-events-none absolute bottom-[-8rem] right-[15%] -z-10 size-[520px] rounded-full bg-gradient-to-br from-indigo-500/40 via-blue-400/30 to-transparent blur-3xl"
			/>
			<span
				aria-hidden="true"
				className="pointer-events-none absolute bottom-0 left-[-6rem] -z-10 size-[460px] rounded-full bg-gradient-to-br from-blue-400/35 via-sky-300/25 to-transparent blur-3xl"
			/>

			<section className="mx-auto w-full max-w-7xl px-2 py-6 sm:px-4">
				<div className="rounded-3xl border border-white/40 bg-white/55 p-4 shadow-[0_8px_32px_-12px_rgba(15,23,42,0.18)] ring-1 ring-white/60 backdrop-blur-xl backdrop-saturate-150 sm:p-6">
					<PeopleReviewFilters />

					<div className="mt-4">
						{ error ? (
							<EmployeesError error={ error } />
						) : isLoading && rows.length === 0 ? (
							<PeopleReviewSkeleton />
						) : rows.length === 0 ? (
							<PeopleReviewEmpty hasFiltersApplied={ hasFiltersApplied } />
						) : (
							<PeopleReviewList />
						) }
					</div>
				</div>

				<EmployeesLiveRegion total={ total } isLoading={ isLoading } />
			</section>
		</div>
	);
}

export function PeopleReviewPage(): JSX.Element {
	return (
		<CapabilityGate caps={ [ 'erp_list_employee' ] }>
			<ErrorBoundary>
				<PeopleReviewPageInner />
			</ErrorBoundary>
		</CapabilityGate>
	);
}
