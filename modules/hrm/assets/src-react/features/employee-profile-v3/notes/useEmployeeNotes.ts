/**
 * Data hook for a single employee's notes (`erp/v2/employees/{id}/notes`).
 *
 * Mirrors the direct-fetch pattern the single-employee detail page already uses
 * (it bypasses the @wordpress/data store, since notes are scoped to one
 * employee and don't need cross-view caching). Reads + writes the v2 endpoint,
 * which delegates to the unchanged v1 `Employee` model.
 */

import { useCallback, useEffect, useState } from 'react';

import { __ } from '@/shared/i18n';
import { request, requestWithHeaders, restPath } from '@/shared/utils/apiFetch';
import type { ApiError } from '@/shared/utils/apiFetch';

export interface EmployeeNote {
	readonly id:                number;
	readonly user_id:           number | null;
	readonly comment:           string;
	readonly comment_by:        number | null;
	readonly author_name:       string;
	readonly author_avatar_url: string;
	readonly created_at:        string | null;
}

export interface UseEmployeeNotes {
	readonly notes:      readonly EmployeeNote[];
	readonly total:      number;
	readonly loading:    boolean;
	readonly error:      string | null;
	readonly addNote:    ( comment: string ) => Promise< void >;
	readonly removeNote: ( id: number ) => Promise< void >;
	readonly reload:     () => void;
}

// Notes are low-cardinality per employee; fetch the whole set once.
const PER_PAGE = 100;

export function useEmployeeNotes( userId: number ): UseEmployeeNotes {
	const [ notes, setNotes ]     = useState< readonly EmployeeNote[] >( [] );
	const [ total, setTotal ]     = useState( 0 );
	const [ loading, setLoading ] = useState( true );
	const [ error, setError ]     = useState< string | null >( null );
	const [ nonce, setNonce ]     = useState( 0 );

	const reload = useCallback( () => setNonce( ( n ) => n + 1 ), [] );

	useEffect( () => {
		let cancelled = false;
		setLoading( true );
		setError( null );

		const path = restPath( 'v2', `/employees/${ userId }/notes`, { per_page: PER_PAGE } );
		void requestWithHeaders< EmployeeNote[] >( path )
			.then( ( { body, headers } ) => {
				if ( cancelled ) {
					return;
				}
				setNotes( body );
				const totalHeader = headers.get( 'X-WP-Total' );
				setTotal( totalHeader ? parseInt( totalHeader, 10 ) : body.length );
			} )
			.catch( ( raw ) => {
				if ( cancelled ) {
					return;
				}
				setError( ( raw as ApiError )?.message ?? __( 'Could not load notes.', 'erp' ) );
			} )
			.finally( () => {
				if ( ! cancelled ) {
					setLoading( false );
				}
			} );

		return () => {
			cancelled = true;
		};
	}, [ userId, nonce ] );

	const addNote = useCallback(
		async ( comment: string ): Promise< void > => {
			const path = restPath( 'v2', `/employees/${ userId }/notes` );
			await request( path, { method: 'POST', data: { comment } } );
			reload();
		},
		[ userId, reload ]
	);

	const removeNote = useCallback(
		async ( id: number ): Promise< void > => {
			const path = restPath( 'v2', `/employees/${ userId }/notes/${ id }` );
			await request( path, { method: 'DELETE' } );
			reload();
		},
		[ userId, reload ]
	);

	return { notes, total, loading, error, addNote, removeNote, reload };
}
