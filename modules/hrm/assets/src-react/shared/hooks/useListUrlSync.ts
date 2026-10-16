/**
 * Two-way URL sync for simple list screens (Departments, Designations).
 *
 * The Employees list publishes its whole state into the hash query string, so a
 * link reproduces the exact view. The smaller list screens kept search, sort and
 * page in component state only — refreshing, or sharing the URL, silently reset
 * them to the unfiltered first page.
 *
 * This is the modest version of `useEmployeesUrlSync`: those pages hold their
 * state in `useState` rather than a data store, so each field is described by
 * its current value, the setter to feed it back, and the default that keeps the
 * URL clean when nothing is filtered.
 */

import { useEffect, useRef } from 'react';
import { useSearchParams } from 'react-router-dom';

export interface UrlSyncedField {
	/** Query-string key. */
	readonly key:     string;
	/** Current value; `''`/default is omitted from the URL. */
	readonly value:   string | number;
	/** Applies a value read back from the URL on first load. */
	readonly apply:   ( value: string ) => void;
	/** Value considered "unset" — never written to the URL. */
	readonly initial: string | number;
}

export function useListUrlSync( fields: readonly UrlSyncedField[] ): void {
	const [ params, setParams ] = useSearchParams();
	const hasReadInitial = useRef( false );

	// Latest fields, so the write effect never runs against a stale closure
	// without having to list every value in its dependency array.
	const latest = useRef( fields );
	latest.current = fields;

	// URL → state, once.
	useEffect( () => {
		if ( hasReadInitial.current ) {
			return;
		}
		hasReadInitial.current = true;

		for ( const field of latest.current ) {
			const raw = params.get( field.key );
			if ( raw !== null && raw !== '' ) {
				field.apply( raw );
			}
		}
	}, [ params ] );

	// state → URL, skipping defaults and no-op writes.
	useEffect( () => {
		if ( ! hasReadInitial.current ) {
			return;
		}

		const next = new URLSearchParams( params );

		for ( const field of fields ) {
			const value = String( field.value ?? '' );
			if ( value === '' || value === String( field.initial ) ) {
				next.delete( field.key );
			} else {
				next.set( field.key, value );
			}
		}

		if ( next.toString() !== params.toString() ) {
			setParams( next, { replace: true } );
		}
	} );
}
