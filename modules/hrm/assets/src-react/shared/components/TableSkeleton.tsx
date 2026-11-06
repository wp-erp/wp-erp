/**
 * Generic list/table loading state — N rows of skeleton bars. Drop-in
 * replacement for the old "Loading…" text in list/report cards.
 */

import { Skeleton } from '@wedevs/plugin-ui';
import type { JSX } from 'react';

interface TableSkeletonProps {
	readonly rows?: number;
}

export function TableSkeleton( { rows = 8 }: TableSkeletonProps ): JSX.Element {
	return (
		<div role="status" aria-busy="true" aria-live="polite" className="divide-y divide-border">
			{ Array.from( { length: rows } ).map( ( _, idx ) => (
				<div key={ idx } className="flex items-center gap-4 px-6 py-4">
					<Skeleton className="h-3.5 flex-1" />
					<Skeleton className="hidden h-3.5 w-32 sm:block" />
					<Skeleton className="hidden h-3.5 w-24 md:block" />
					<Skeleton className="h-5 w-16 rounded-full" />
				</div>
			) ) }
		</div>
	);
}
