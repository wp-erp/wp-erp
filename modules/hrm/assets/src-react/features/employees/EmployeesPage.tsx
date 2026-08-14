/**
 * Composition root for the `/employees` route.
 *
 * Layout follows the Figma "Job Openings" canonical card pattern:
 *   [Page header: title + primary CTA]
 *   [Card  ──────────────────────────────]
 *     [Status tabs | Search | Filter funnel]
 *     [Secondary filters (collapsible)     ]
 *     [Table                                ]
 *     [Pagination footer                    ]
 *   [────────────────────────────────────]
 *
 * Empty / loading / error states swap the table content but keep the card
 * chrome (tabs + search) visible so the user can keep tweaking the filter
 * without the page reflowing.
 */

import { useState } from 'react';
import type { JSX } from 'react';

import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';

import { EmployeesBulkBar } from './EmployeesBulkBar';
import { EmployeesEmpty } from './EmployeesEmpty';
import { EmployeesError } from './EmployeesError';
import { EmployeesFilters } from './EmployeesFilters';
import { EmployeesGrid } from './EmployeesGrid';
import { EmployeesLiveRegion } from './EmployeesLiveRegion';
import { EmployeesSkeleton } from './EmployeesSkeleton';
import { EmployeesTable } from './EmployeesTable';
import { EmployeesToolbar } from './EmployeesToolbar';
import type { EmployeesView } from './EmployeesViewToggle';
import { useEmployeesQuery } from './useEmployeesQuery';
import { useEmployeesUrlSync } from './useEmployeesUrlSync';

function EmployeesPageInner(): JSX.Element {
	useEmployeesUrlSync();
	const { rows, total, isLoading, error, query } = useEmployeesQuery();
	const [ view, setView ] = useState< EmployeesView >( 'list' );

	const hasFiltersApplied = Boolean(
		query.search ||
			query.department_id ||
			query.designation_id ||
			query.location_id ||
			( query.status && query.status !== 'active' )
	);

	return (
		<section className="mx-auto w-full max-w-full">
			<EmployeesToolbar view={ view } onViewChange={ setView } />

			<div className="rounded-lg border border-border bg-card shadow-sm">
				<EmployeesFilters />

				<EmployeesBulkBar />

				{ error ? (
					<div className="p-6">
						<EmployeesError error={ error } />
					</div>
				) : isLoading && rows.length === 0 ? (
					<EmployeesSkeleton />
				) : rows.length === 0 ? (
					<EmployeesEmpty hasFiltersApplied={ hasFiltersApplied } />
				) : view === 'grid' ? (
					<EmployeesGrid />
				) : (
					<EmployeesTable />
				) }
			</div>

			<EmployeesLiveRegion total={ total } isLoading={ isLoading } />
		</section>
	);
}

export function EmployeesPage(): JSX.Element {
	return (
		<CapabilityGate caps={ [ 'erp_list_employee' ] }>
			<ErrorBoundary>
				<EmployeesPageInner />
			</ErrorBoundary>
		</CapabilityGate>
	);
}
