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

import { StatusTabs } from '@/shared/components/StatusTabs';
import { useCan } from '@/shared/hooks/useCan';
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

/**
 * Tabs a non-HR viewer may use: the legacy HR Frontend directory's Active,
 * Terminated, Deceased and Resigned, plus All (those four). The server lists
 * exactly these to someone without `erp_view_employee`; Inactive and Trash
 * stay HR only.
 */
const LIMITED_TABS: ReadonlySet< StatusTab > = new Set< StatusTab >( [ 'all', 'active', 'terminated', 'deceased', 'resigned' ] );

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

	const isHr    = useCan( 'erp_view_employee' );
	const visible = isHr ? TABS : TABS.filter( ( tab ) => LIMITED_TABS.has( tab.value ) );

	const current: StatusTab = filters.status ?? 'all';

	return (
		<StatusTabs
			tabs={ visible.map( ( tab ) => ( {
				value: tab.value,
				label: tab.label,
				count: countFor( counts, tab.value ),
			} ) ) }
			value={ current }
			onChange={ ( value ) => {
				setFilters( { ...filters, status: value } );
				setPagination( { page: 1, perPage: 20 } );
			} }
			ariaLabel={ __( 'Employee status', 'erp' ) }
		/>
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
