/**
 * Offboarding check: warns (never blocks) when the employee still holds company
 * assets. Reads the same `erp/v2/hrm/assets/employee/{id}` route the profile
 * Assets tab uses, and only when the pro Asset Management module is active.
 */

import { Alert, AlertDescription } from '@wedevs/plugin-ui';
import { useEffect, useState } from 'react';
import type { JSX } from 'react';

import { useBoot } from '@/shared/hooks/useBoot';
import { _n, sprintf } from '@/shared/i18n';
import { request, restPath } from '@/shared/utils/apiFetch';

interface EmployeeAssetRow {
	readonly itemCode:  string;
	readonly itemName:  string;
	readonly itemGroup: string;
	readonly status:    string;
}

/** Names of the items still with the employee (allotted or return requested). */
export function useAllottedAssets( userId: number, enabled: boolean ): string[] {
	const { modules } = useBoot();
	const active = enabled && userId > 0 && ( modules ?? [] ).includes( 'asset' );
	const [ names, setNames ] = useState< string[] >( [] );

	useEffect( () => {
		setNames( [] );
		if ( ! active ) {
			return;
		}
		const ctrl = new AbortController();
		request< { assets: EmployeeAssetRow[] } >( restPath( 'v2', `/hrm/assets/employee/${ userId }` ), { signal: ctrl.signal } )
			.then( ( r ) => setNames(
				r.assets
					.filter( ( a ) => 'allotted' === a.status || 'requested_return' === a.status )
					.map( ( a ) => a.itemGroup || a.itemName || a.itemCode )
			) )
			.catch( () => undefined );
		return () => ctrl.abort();
	}, [ active, userId ] );

	return names;
}

export function AllottedAssetsWarning( { names }: { readonly names: readonly string[] } ): JSX.Element | null {
	if ( names.length === 0 ) {
		return null;
	}
	return (
		<Alert>
			<AlertDescription>
				{ sprintf(
					/* translators: 1: number of assets, 2: comma-separated item names */
					_n( '%1$d asset is still allotted: %2$s.', '%1$d assets are still allotted: %2$s.', names.length, 'erp' ),
					names.length,
					names.join( ', ' )
				) }
			</AlertDescription>
		</Alert>
	);
}
