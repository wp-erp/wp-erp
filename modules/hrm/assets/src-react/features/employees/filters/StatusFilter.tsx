/**
 * Employee status tabs — All | Active | Inactive | Terminated | Trash.
 *
 * Each tab renders its bucket count from `/erp/v2/employees/counts`. Counts
 * are filter-aware (search + department + designation + location) so the
 * displayed totals always match the visible cohort.
 *
 * Per figma-reference.md "Tabs row" (also fixes Figma typos
 * `Teminated`/`Trush`).
 */

import { useDispatch, useSelect } from '@wordpress/data';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName, toCountsQuery } from '@/stores/employees';
import type {
	EmployeeCountsQuery,
	EmployeeListQuery,
	EmployeeStatusCounts,
	EmployeesState,
} from '@/stores/employees';

type StatusTab = NonNullable< EmployeeListQuery[ 'status' ] >;

interface EmployeesStoreSelectors {
	getFilters: () => EmployeeListQuery;
	getCounts:  ( query: EmployeeCountsQuery ) => EmployeeStatusCounts | null;
}

interface EmployeesStoreDispatch {
	setFilters: ( filters: EmployeeListQuery ) => void;
	setPagination: ( pagination: EmployeesState[ 'pagination' ] ) => void;
}

const TABS: ReadonlyArray< { readonly value: StatusTab; readonly label: string } > = [
	{ value: 'all',        label: __( 'All', 'erp' ) },
	{ value: 'active',     label: __( 'Active', 'erp' ) },
	{ value: 'inactive',   label: __( 'Inactive', 'erp' ) },
	{ value: 'terminated', label: __( 'Terminated', 'erp' ) },
	{ value: 'deceased',   label: __( 'Deceased', 'erp' ) },
	{ value: 'resigned',   label: __( 'Resigned', 'erp' ) },
	{ value: 'trash',      label: __( 'Trash', 'erp' ) },
];

export function StatusFilter(): JSX.Element {
	const filters = useSelect(
		( select ) => ( select( employeesStoreName ) as unknown as EmployeesStoreSelectors ).getFilters(),
		[]
	);
	const counts = useSelect(
		( select ) => ( select( employeesStoreName ) as unknown as EmployeesStoreSelectors ).getCounts(
			toCountsQuery( filters )
		),
		[ filters.search, filters.department_id, filters.designation_id, filters.location_id ]
	);
	const { setFilters, setPagination } = useDispatch(
		employeesStoreName
	) as unknown as EmployeesStoreDispatch;

	const current: StatusTab = filters.status ?? 'all';

	return (
		<div
			role="tablist"
			aria-label={ __( 'Employee status', 'erp' ) }
			className="-mb-2 flex min-w-0 max-w-full items-stretch overflow-x-auto pb-2 scrollbar-none"
		>
			{ TABS.map( ( tab ) => {
				const active = tab.value === current;
				const count  = countFor( counts, tab.value );
				return (
					<button
						key={ tab.value }
						type="button"
						role="tab"
						aria-selected={ active }
						onClick={ () => {
							setFilters( { ...filters, status: tab.value } );
							setPagination( { page: 1, perPage: 20 } );
						} }
						className={ [
							'relative inline-flex h-11 shrink-0 items-center gap-1.5 whitespace-nowrap px-4 text-sm font-medium transition-colors',
							active
								? 'text-primary'
								: 'text-foreground hover:text-primary',
						].join( ' ' ) }
					>
						<span>{ tab.label }</span>
						{ count !== null ? (
							<span className="font-normal text-[#a5a5aa]">
								({ count })
							</span>
						) : null }
						<span
							aria-hidden="true"
							className={ [
								'absolute inset-x-0 -bottom-2 h-0.5',
								active ? 'bg-primary' : 'bg-transparent',
							].join( ' ' ) }
						/>
					</button>
				);
			} ) }
		</div>
	);
}

function countFor( counts: EmployeeStatusCounts | null, tab: StatusTab ): number | null {
	if ( ! counts ) {
		return null;
	}
	if ( tab === 'all' ) {
		return counts.all;
	}
	return counts.by_status[ tab ] ?? 0;
}
