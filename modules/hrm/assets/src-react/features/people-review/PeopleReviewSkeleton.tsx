/**
 * Loading skeleton — N shimmer rows matching the modern card-row layout.
 */

import { Skeleton } from '@wedevs/plugin-ui';
import type { JSX } from 'react';

interface PeopleReviewSkeletonProps {
	readonly rows?: number;
}

export function PeopleReviewSkeleton( { rows = 6 }: PeopleReviewSkeletonProps ): JSX.Element {
	return (
		<div role="status" aria-busy="true" aria-live="polite" className="space-y-2">
			{ Array.from( { length: rows } ).map( ( _, idx ) => (
				<div
					key={ idx }
					className="grid grid-cols-[auto_minmax(0,2fr)_minmax(0,1.5fr)_minmax(0,1fr)_minmax(0,1fr)_auto] items-center gap-4 rounded-2xl border border-white/40 bg-white/50 px-4 py-3 ring-1 ring-white/40 backdrop-blur-xl"
				>
					<Skeleton className="size-4 rounded" />
					<div className="flex items-center gap-3">
						<Skeleton className="size-10 rounded-full" />
						<div className="space-y-2">
							<Skeleton className="h-3 w-32" />
							<Skeleton className="h-2.5 w-20" />
						</div>
					</div>
					<Skeleton className="h-3 w-40" />
					<Skeleton className="h-3 w-24" />
					<Skeleton className="h-3 w-20" />
					<Skeleton className="h-6 w-20 rounded-full" />
				</div>
			) ) }
		</div>
	);
}
