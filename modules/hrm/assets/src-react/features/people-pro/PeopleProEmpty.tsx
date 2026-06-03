/**
 * Empty + skeleton states — neutral, token-driven, professional.
 */

import { Button, Skeleton } from '@wedevs/plugin-ui';
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

interface PeopleProEmptyProps {
	readonly hasFiltersApplied: boolean;
}

export function PeopleProEmpty( { hasFiltersApplied }: PeopleProEmptyProps ): JSX.Element {
	const canCreate = useCan( 'erp_create_employee' );
	const { setFilters, setPagination } = useDispatch(
		employeesStoreName
	) as unknown as EmployeesStoreDispatch;

	const Icon  = hasFiltersApplied ? SearchX : Users;
	const title = hasFiltersApplied
		? __( 'No members match these filters', 'erp' )
		: __( 'Build your directory', 'erp' );
	const body = hasFiltersApplied
		? __( 'Adjust the filters above or clear them to see your full directory.', 'erp' )
		: __( 'Invite your first member to get started. Your directory keeps every record on hand — emails, roles, departments, and more.', 'erp' );

	return (
		<div className="flex flex-col items-center px-6 py-16 text-center">
			<span className="inline-flex size-14 items-center justify-center rounded-full border border-border bg-muted text-muted-foreground">
				<Icon size={ 24 } strokeWidth={ 1.5 } aria-hidden="true" />
			</span>
			<h2 className="mt-5 text-lg font-semibold leading-7 text-foreground">{ title }</h2>
			<p className="mt-1 max-w-md text-sm text-muted-foreground">{ body }</p>
			{ hasFiltersApplied ? (
				<Button
					variant="outline"
					size="sm"
					className="mt-6 h-9 gap-1.5"
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
					size="sm"
					className="mt-6 h-9 gap-1.5"
					onClick={ () => { window.location.hash = '#/employees/new'; } }
				>
					<Plus size={ 14 } strokeWidth={ 2 } aria-hidden="true" />
					{ __( 'Invite member', 'erp' ) }
				</Button>
			) : null }
		</div>
	);
}

interface PeopleProSkeletonProps {
	readonly rows?: number;
}

export function PeopleProSkeleton( { rows = 6 }: PeopleProSkeletonProps ): JSX.Element {
	return (
		<div role="status" aria-busy="true" aria-live="polite" className="divide-y divide-border">
			{ Array.from( { length: rows } ).map( ( _, idx ) => (
				<div key={ idx } className="grid grid-cols-[auto_2fr_2fr_1.5fr_1.5fr_1fr_1fr] items-center gap-4 px-4 py-4">
					<Skeleton className="size-4 rounded" />
					<div className="flex items-center gap-3">
						<Skeleton className="size-9 rounded-full" />
						<div className="space-y-2">
							<Skeleton className="h-3 w-32" />
							<Skeleton className="h-2.5 w-20" />
						</div>
					</div>
					<Skeleton className="h-3 w-44" />
					<Skeleton className="h-3 w-28" />
					<Skeleton className="h-3 w-24" />
					<Skeleton className="h-3 w-20" />
					<Skeleton className="h-6 w-20 rounded-full" />
				</div>
			) ) }
		</div>
	);
}
