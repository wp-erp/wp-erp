/**
 * Empty state — two flavors:
 *   1. Truly empty (no employees in DB) → illustration + Add CTA.
 *   2. Filters applied + 0 results       → same illustration + Clear filters CTA.
 *
 * Illustration matches the Figma "No Employees Added Yet" frame: light-blue
 * circular badge with a User glyph and a small Plus pill in the lower-right.
 *
 * CTA gated on `useCan('erp_create_employee')` for the create path.
 */

import { Button } from '@wedevs/plugin-ui';
import { useDispatch } from '@wordpress/data';
import { Plus } from 'lucide-react';
import type { JSX } from 'react';

import { useCan } from '@/shared/hooks/useCan';
import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import type {
	EmployeeListQuery,
	EmployeesState,
} from '@/stores/employees';

import EmptyEmployeesIllustration from './illustrations/empty-employees.svg';

interface EmployeesStoreDispatch {
	setFilters:    ( filters: EmployeeListQuery ) => void;
	setPagination: ( pagination: EmployeesState[ 'pagination' ] ) => void;
}

interface EmployeesEmptyProps {
	readonly hasFiltersApplied?: boolean;
}

export function EmployeesEmpty( {
	hasFiltersApplied = false,
}: EmployeesEmptyProps ): JSX.Element {
	const canCreate = useCan( 'erp_create_employee' );
	const { setFilters, setPagination } = useDispatch(
		employeesStoreName
	) as unknown as EmployeesStoreDispatch;

	const title = hasFiltersApplied
		? __( 'No employees match your filters', 'erp' )
		: __( 'No Employees Added Yet', 'erp' );

	const body = hasFiltersApplied
		? __( 'Try adjusting your search or clearing the active filters.', 'erp' )
		: __( 'Start adding employees to manage your workforce effectively.', 'erp' );

	return (
		<div className="flex flex-col items-center px-6 py-16 text-center">
			<EmptyEmployeesIllustration aria-hidden="true" />

			<h2 className="mt-10 text-2xl font-bold leading-8 text-foreground">
				{ title }
			</h2>
			<p className="mt-3 max-w-md text-sm leading-6 text-muted-foreground">
				{ body }
			</p>

			{ hasFiltersApplied ? (
				<Button
					variant="default"
					className="mt-8 inline-flex h-10 items-center gap-2 rounded-md px-5 text-sm font-medium shadow-sm"
					onClick={ () => {
						setFilters( { status: 'all' } );
						setPagination( { page: 1, perPage: 20 } );
					} }
				>
					{ __( 'Clear filters', 'erp' ) }
				</Button>
			) : canCreate ? (
				<Button
					variant="default"
					className="mt-8 inline-flex h-10 items-center gap-2 rounded-md px-5 text-sm font-medium shadow-sm"
					onClick={ () => {
						window.location.hash = '#/employees/new';
					} }
				>
					<Plus size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
					{ __( 'Add New Employee', 'erp' ) }
				</Button>
			) : null }
		</div>
	);
}

