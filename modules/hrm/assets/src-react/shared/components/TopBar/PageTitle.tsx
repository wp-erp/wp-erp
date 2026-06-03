/**
 * Active page title driven by the matched route's `handle.title`.
 */

import { useMatches } from 'react-router-dom';
import type { JSX } from 'react';

import type { RouteHandle } from '@/types/global';

export function PageTitle(): JSX.Element | null {
	const matches = useMatches();
	const handle  = matches
		.map( ( m ) => m.handle as RouteHandle | undefined )
		.reverse()
		.find( ( h ) => Boolean( h?.title ) );

	if ( ! handle ) {
		return null;
	}

	return (
		<span className="ml-6 text-sm font-medium text-muted-foreground">
			{ handle.title }
		</span>
	);
}
