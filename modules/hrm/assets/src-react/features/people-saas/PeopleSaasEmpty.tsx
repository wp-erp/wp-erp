/**
 * Empty + skeleton states — neutral, dense, no chrome.
 */

import { Skeleton } from '@wedevs/plugin-ui';
import { useDispatch } from '@wordpress/data';
import { Plus, SearchX, Users } from 'lucide-react';
import type { JSX } from 'react';

import { useCan } from '@/shared/hooks/useCan';
import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import type {
	EmployeeListQuery,
	EmployeesState,
} from '@/stores/employees';

interface EmployeesStoreDispatch {
	setFilters:    ( filters: EmployeeListQuery ) => void;
	setPagination: ( pagination: EmployeesState[ 'pagination' ] ) => void;
}

interface PeopleSaasEmptyProps {
	readonly hasFiltersApplied: boolean;
}

export function PeopleSaasEmpty( { hasFiltersApplied }: PeopleSaasEmptyProps ): JSX.Element {
	const canCreate = useCan( 'erp_create_employee' );
	const { setFilters, setPagination } = useDispatch(
		employeesStoreName
	) as unknown as EmployeesStoreDispatch;

	const Icon  = hasFiltersApplied ? SearchX : Users;
	const title = hasFiltersApplied
		? __( 'No people match these filters', 'erp' )
		: __( 'Your directory is empty', 'erp' );
	const body = hasFiltersApplied
		? __( 'Try widening your search or clearing active filters.', 'erp' )
		: __( 'Add your first teammate to start managing your workforce.', 'erp' );

	return (
		<div className="flex flex-col items-center px-6 py-20 text-center">
			<span className="inline-flex size-12 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-slate-500">
				<Icon size={ 22 } strokeWidth={ 1.5 } aria-hidden="true" />
			</span>
			<h2 className="mt-4 text-sm font-semibold text-slate-900">{ title }</h2>
			<p className="mt-1 max-w-sm text-xs text-slate-500">{ body }</p>
			{ hasFiltersApplied ? (
				<button
					type="button"
					onClick={ () => {
						setFilters( { status: 'all' } );
						setPagination( { page: 1, perPage: 20 } );
					} }
					className="mt-5 inline-flex h-8 items-center gap-1.5 rounded-md border border-slate-200 bg-white px-3 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50"
				>
					{ __( 'Clear filters', 'erp' ) }
				</button>
			) : canCreate ? (
				<button
					type="button"
					onClick={ () => { window.location.hash = '#/employees/new'; } }
					className="mt-5 inline-flex h-8 items-center gap-1.5 rounded-md bg-slate-900 px-3 text-xs font-medium text-white shadow-sm hover:bg-slate-800"
				>
					<Plus size={ 12 } strokeWidth={ 2 } aria-hidden="true" />
					{ __( 'Add person', 'erp' ) }
				</button>
			) : null }
		</div>
	);
}

interface PeopleSaasSkeletonProps {
	readonly rows?: number;
}

export function PeopleSaasSkeleton( { rows = 8 }: PeopleSaasSkeletonProps ): JSX.Element {
	return (
		<div role="status" aria-busy="true" aria-live="polite" className="divide-y divide-slate-100">
			{ Array.from( { length: rows } ).map( ( _, idx ) => (
				<div key={ idx } className="grid grid-cols-[auto_2fr_2fr_1.5fr_1.5fr_1fr_1fr] items-center gap-4 px-4 py-2.5">
					<Skeleton className="size-3.5 rounded" />
					<div className="flex items-center gap-2.5">
						<Skeleton className="size-7 rounded-full" />
						<div className="space-y-1.5">
							<Skeleton className="h-2.5 w-28" />
							<Skeleton className="h-2 w-20" />
						</div>
					</div>
					<Skeleton className="h-2.5 w-40" />
					<Skeleton className="h-2.5 w-24" />
					<Skeleton className="h-2.5 w-24" />
					<Skeleton className="h-2.5 w-16" />
					<Skeleton className="h-5 w-16 rounded" />
				</div>
			) ) }
		</div>
	);
}
