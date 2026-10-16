/**
 * Debounced global-search hook for the top-bar command palette.
 *
 * Queries `GET /erp/v2/search?q=…`, which fans out across employees,
 * departments, and designations (each capability-gated server-side). Results
 * are server-driven — the cmdk `Command` runs with `shouldFilter={false}` — so
 * this hook owns debouncing, aborting in-flight requests, and shaping the
 * response defensively.
 */

import { useEffect, useState } from 'react';

import { request, restPath } from '@/shared/utils/apiFetch';

export interface SearchHit {
	readonly id:        number;
	readonly label:     string;
	readonly sublabel?: string;
	readonly avatar?:   string;
}

export interface SearchResults {
	readonly employees:    readonly SearchHit[];
	readonly departments:  readonly SearchHit[];
	readonly designations: readonly SearchHit[];
}

interface UseGlobalSearchResult {
	readonly results: SearchResults;
	readonly loading: boolean;
}

const EMPTY: SearchResults = { employees: [], departments: [], designations: [] };
const MIN_CHARS = 2;
const DEBOUNCE_MS = 250;

function toHits( raw: unknown ): SearchHit[] {
	if ( ! Array.isArray( raw ) ) {
		return [];
	}
	return raw
		.map( ( row ): SearchHit | null => {
			if ( ! row || typeof row !== 'object' ) {
				return null;
			}
			const r = row as Record< string, unknown >;
			const id = Number( r.id );
			if ( ! Number.isFinite( id ) || id <= 0 ) {
				return null;
			}
			return {
				id,
				label:    typeof r.label === 'string' ? r.label : '',
				sublabel: typeof r.sublabel === 'string' ? r.sublabel : '',
				avatar:   typeof r.avatar === 'string' ? r.avatar : '',
			};
		} )
		.filter( ( hit ): hit is SearchHit => hit !== null );
}

export function useGlobalSearch( query: string, enabled: boolean ): UseGlobalSearchResult {
	const [ results, setResults ] = useState< SearchResults >( EMPTY );
	const [ loading, setLoading ] = useState( false );

	useEffect( () => {
		const q = query.trim();
		if ( ! enabled || q.length < MIN_CHARS ) {
			setResults( EMPTY );
			setLoading( false );
			return;
		}

		let cancelled = false;
		setLoading( true );
		const controller = new AbortController();

		const timer = window.setTimeout( () => {
			void request< {
				employees?: unknown;
				departments?: unknown;
				designations?: unknown;
			} >( restPath( 'v2', '/search', { q, limit: 5 } ), { signal: controller.signal } )
				.then( ( res ) => {
					if ( cancelled ) {
						return;
					}
					setResults( {
						employees:    toHits( res.employees ),
						departments:  toHits( res.departments ),
						designations: toHits( res.designations ),
					} );
				} )
				.catch( () => {
					// Aborted or failed — leave prior results, fall out of loading.
				} )
				.finally( () => {
					if ( ! cancelled ) {
						setLoading( false );
					}
				} );
		}, DEBOUNCE_MS );

		return () => {
			cancelled = true;
			window.clearTimeout( timer );
			controller.abort();
		};
	}, [ query, enabled ] );

	return { results, loading };
}
