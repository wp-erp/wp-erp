/**
 * Shared list + CRUD hook for the small HR taxonomy entities (departments and
 * designations). Both are low-cardinality, so the list is fetched in one page
 * (`per_page=100`) and searched client-side — no server pagination needed,
 * matching the existing lookup-dropdown pattern.
 *
 * Reads + writes the matching `erp/v2` resource. Mutations delegate (server
 * side) to the unchanged v1 model layer, so every legacy hook keeps firing.
 */

import { useCallback, useEffect, useState } from 'react';

import type { ApiError } from '@/shared/utils/apiFetch';
import { request, requestWithHeaders, restPath } from '@/shared/utils/apiFetch';
import { toInt } from '@/shared/utils/coerce';

/** Minimal shape every taxonomy entity shares. */
export interface OrgEntity {
	readonly id:    number;
	readonly title: string;
}

export interface UseOrgCrudResult< T extends OrgEntity > {
	readonly rows:    readonly T[];
	readonly total:   number;
	readonly loading: boolean;
	readonly error:   string | null;
	readonly reload:  () => Promise< void >;
	/** Create (when `id` is null) or update an entity. Rejects with ApiError. */
	readonly save:    ( id: number | null, payload: Record< string, unknown > ) => Promise< void >;
	/** Delete an entity. Rejects with ApiError (e.g. the "not empty" guard). */
	readonly remove:  ( id: number ) => Promise< void >;
}

export function useOrgCrud< T extends OrgEntity >(
	resource: 'departments' | 'designations'
): UseOrgCrudResult< T > {
	const [ rows, setRows ]       = useState< readonly T[] >( [] );
	const [ total, setTotal ]     = useState( 0 );
	const [ loading, setLoading ] = useState( true );
	const [ error, setError ]     = useState< string | null >( null );

	const reload = useCallback( async (): Promise< void > => {
		setLoading( true );
		setError( null );
		try {
			const { body, headers } = await requestWithHeaders< T[] >(
				restPath( 'v2', `/${ resource }`, { per_page: 100, orderby: 'title', order: 'asc' } )
			);
			const list = Array.isArray( body ) ? body : [];
			setRows( list );
			setTotal( toInt( headers.get( 'X-WP-Total' ), list.length ) );
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? 'Could not load the list.' );
		} finally {
			setLoading( false );
		}
	}, [ resource ] );

	useEffect( () => {
		void reload();
	}, [ reload ] );

	const save = useCallback(
		async ( id: number | null, payload: Record< string, unknown > ): Promise< void > => {
			if ( id ) {
				await request( restPath( 'v2', `/${ resource }/${ id }` ), { method: 'PUT', data: payload } );
			} else {
				await request( restPath( 'v2', `/${ resource }` ), { method: 'POST', data: payload } );
			}
			await reload();
		},
		[ resource, reload ]
	);

	const remove = useCallback(
		async ( id: number ): Promise< void > => {
			await request( restPath( 'v2', `/${ resource }/${ id }` ), { method: 'DELETE' } );
			await reload();
		},
		[ resource, reload ]
	);

	return { rows, total, loading, error, reload, save, remove };
}
