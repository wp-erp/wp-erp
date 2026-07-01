/**
 * Shared async employee picker state for searchable single-selects.
 *
 * The `/erp/v2/employees` list endpoint caps `per_page` at 100, so a dropdown
 * that loads once and filters client-side can never reach employees past the
 * first page. This hook drives a server-side search instead: it loads the first
 * 20 active employees on open, and re-queries (capped at 20 rows) on every typed
 * keystroke via `SmartSelect`'s debounced `onSearch`.
 *
 * `SmartSelect` resolves the trigger label by finding the selected value inside
 * `options` (and, with `onSearch`, it does NOT keep its own copy) — so the
 * currently-selected employee and any `keep` option are always merged into
 * `options`, otherwise the trigger would fall back to showing the raw id once a
 * new search replaces the result page.
 */

import { useCallback, useEffect, useMemo, useRef, useState } from 'react';

import { searchEmployees, type LookupOption } from '@/features/employees/filters/lookups';
import type { Option } from '@/features/employee-create/options';

// `o.title` already carries "<employee_id> - <name>" (the HR Employee ID, not
// the DB user id); the value stays the user id the API expects.
function toOption( o: LookupOption ): Option {
	return { value: String( o.id ), label: o.title };
}

function normalizeKeep( keep?: Option | readonly Option[] | null ): Option[] {
	if ( ! keep ) {
		return [];
	}
	return ( Array.isArray( keep ) ? keep : [ keep as Option ] ).filter( ( o ) => o && o.value );
}

export interface UseEmployeeSearch {
	readonly options:  Option[];
	readonly loading:  boolean;
	readonly onSearch: ( query: string ) => Promise< void >;
}

/**
 * @param enabled       Skip fetching until the picker is shown (e.g. dialog open).
 * @param keep          Already-selected option(s) to always keep present.
 * @param selectedValue The current single-select value, kept present so its label resolves.
 */
export function useEmployeeSearch(
	enabled = true,
	keep?: Option | readonly Option[] | null,
	selectedValue?: string,
): UseEmployeeSearch {
	const [ results, setResults ] = useState< Option[] >( [] );
	const [ loading, setLoading ] = useState( false );

	// Every value→label pair we've ever seen, so a selected employee keeps its
	// label even after a later search drops it from the current result page.
	const labelsRef = useRef< Map< string, string > >( new Map() );

	const keeps = normalizeKeep( keep );
	keeps.forEach( ( k ) => labelsRef.current.set( k.value, k.label ) );

	const onSearch = useCallback( async ( query: string ): Promise< void > => {
		setLoading( true );
		try {
			const rows = ( await searchEmployees( query, 20 ) ).map( toOption );
			rows.forEach( ( r ) => labelsRef.current.set( r.value, r.label ) );
			setResults( rows );
		} finally {
			setLoading( false );
		}
	}, [] );

	// Load the first page when the picker becomes visible.
	useEffect( () => {
		if ( ! enabled ) {
			return;
		}
		void onSearch( '' );
	}, [ enabled, onSearch ] );

	const keepKey = keeps.map( ( o ) => o.value ).join( ',' );
	const options = useMemo( () => {
		const have  = new Set( results.map( ( r ) => r.value ) );
		const extra: Option[] = [];
		// Always surface the selected value so the trigger shows its label.
		if ( selectedValue && ! have.has( selectedValue ) ) {
			const label = labelsRef.current.get( selectedValue );
			if ( label ) {
				extra.push( { value: selectedValue, label } );
				have.add( selectedValue );
			}
		}
		// Surface kept options not already present.
		keeps.forEach( ( k ) => {
			if ( ! have.has( k.value ) ) {
				extra.push( k );
				have.add( k.value );
			}
		} );
		return [ ...extra, ...results ];
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ results, selectedValue, keepKey ] );

	return { options, loading, onSearch };
}
