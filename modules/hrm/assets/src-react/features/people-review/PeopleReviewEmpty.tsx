/**
 * Empty state — dual flavor (no people / filter mismatch) with the same
 * shared illustration from `features/employees/illustrations`.
 */

import { useDispatch } from '@wordpress/data';
import { Plus, SearchX } from 'lucide-react';
import type { JSX } from 'react';

import { useCan } from '@/shared/hooks/useCan';
import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import type {
	EmployeeListQuery,
	EmployeesState,
} from '@/stores/employees';

import EmptyEmployeesIllustration from '../employees/illustrations/empty-employees.svg';

interface EmployeesStoreDispatch {
	setFilters:    ( filters: EmployeeListQuery ) => void;
	setPagination: ( pagination: EmployeesState[ 'pagination' ] ) => void;
}

interface PeopleReviewEmptyProps {
	readonly hasFiltersApplied: boolean;
}

export function PeopleReviewEmpty( {
	hasFiltersApplied,
}: PeopleReviewEmptyProps ): JSX.Element {
	const canCreate = useCan( 'erp_create_employee' );
	const { setFilters, setPagination } = useDispatch(
		employeesStoreName
	) as unknown as EmployeesStoreDispatch;

	const title = hasFiltersApplied
		? __( 'No matches', 'erp' )
		: __( 'Nobody here yet', 'erp' );

	const body = hasFiltersApplied
		? __( 'Loosen the search or clear active filters to see more people.', 'erp' )
		: __( 'Add your first teammate to start managing your workforce.', 'erp' );

	return (
		<div className="relative flex flex-col items-center overflow-hidden rounded-3xl border border-white/40 bg-white/45 px-6 py-16 text-center ring-1 ring-white/40 backdrop-blur-xl backdrop-saturate-150">
			<span
				aria-hidden="true"
				className="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/80 to-transparent"
			/>
			{ hasFiltersApplied ? (
				<span className="inline-flex size-16 items-center justify-center rounded-full border border-white/50 bg-white/60 text-muted-foreground ring-1 ring-white/40 backdrop-blur">
					<SearchX size={ 28 } strokeWidth={ 1.5 } aria-hidden="true" />
				</span>
			) : (
				<EmptyEmployeesIllustration aria-hidden="true" />
			) }
			<h2 className="mt-6 text-xl font-bold leading-7 text-foreground">{ title }</h2>
			<p className="mt-2 max-w-md text-sm leading-6 text-muted-foreground">{ body }</p>
			{ hasFiltersApplied ? (
				<button
					type="button"
					onClick={ () => {
						setFilters( { status: 'all' } );
						setPagination( { page: 1, perPage: 20 } );
					} }
					className="mt-6 inline-flex h-10 items-center gap-2 rounded-full bg-foreground px-5 text-sm font-medium text-background shadow-sm hover:opacity-90"
				>
					{ __( 'Clear filters', 'erp' ) }
				</button>
			) : canCreate ? (
				<button
					type="button"
					onClick={ () => { window.location.hash = '#/employees/new'; } }
					className="mt-6 inline-flex h-10 items-center gap-2 rounded-full bg-primary px-5 text-sm font-medium text-primary-foreground shadow-sm hover:opacity-90"
				>
					<Plus size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
					{ __( 'Add person', 'erp' ) }
				</button>
			) : null }
		</div>
	);
}
