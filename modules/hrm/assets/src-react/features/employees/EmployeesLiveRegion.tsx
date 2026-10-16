/**
 * aria-live announcer for row-count + sort changes. Debounces 500 ms so
 * keystroke-driven filter changes don't flood the screen reader.
 */

import { useEffect, useState } from 'react';
import type { JSX } from 'react';

import { _n, sprintf } from '@/shared/i18n';

interface EmployeesLiveRegionProps {
	readonly total:     number;
	readonly isLoading: boolean;
}

export function EmployeesLiveRegion( {
	total,
	isLoading,
}: EmployeesLiveRegionProps ): JSX.Element {
	const [ message, setMessage ] = useState( '' );

	useEffect( () => {
		if ( isLoading ) {
			return;
		}
		const id = window.setTimeout( () => {
			setMessage(
				sprintf(
					_n( '%d employee shown', '%d employees shown', total, 'erp' ),
					total
				)
			);
		}, 500 );
		return () => window.clearTimeout( id );
	}, [ total, isLoading ] );

	return (
		<div role="status" aria-live="polite" className="sr-only">
			{ message }
		</div>
	);
}
