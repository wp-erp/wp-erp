/**
 * Loading state — N skeleton rows matching the table layout.
 */

import { Skeleton } from '@wedevs/plugin-ui';
import type { JSX } from 'react';

interface EmployeesSkeletonProps {
	readonly rows?: number;
}

export function EmployeesSkeleton( { rows = 8 }: EmployeesSkeletonProps ): JSX.Element {
	return (
		<div role="status" aria-busy="true" aria-live="polite" className="divide-y divide-border">
			{ Array.from( { length: rows } ).map( ( _, idx ) => (
				<div key={ idx } className="flex items-center gap-3 px-4 py-4">
					<Skeleton className="size-8 rounded-full" />
					<div className="flex-1 space-y-2">
						<Skeleton className="h-3 w-40" />
						<Skeleton className="h-2.5 w-28" />
					</div>
					<Skeleton className="h-3 w-48" />
					<Skeleton className="h-3 w-28" />
					<Skeleton className="h-5 w-16" />
					<Skeleton className="h-3 w-20" />
				</div>
			) ) }
		</div>
	);
}
