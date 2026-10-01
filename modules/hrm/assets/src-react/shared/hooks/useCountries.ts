/**
 * Country and state options for the address selects (employee form, work
 * location form), read from `erp/v2/countries` when a form needs them instead
 * of riding every page's boot payload. Cached at module scope, so the lists are
 * fetched once per page load.
 */

import { useEffect, useState } from 'react';

import { request } from '@/shared/utils/apiFetch';

export interface CountryOption {
	readonly value: string;
	readonly label: string;
}

const cache = new Map< string, Promise< CountryOption[] > >();

function load( path: string ): Promise< CountryOption[] > {
	let pending = cache.get( path );
	if ( ! pending ) {
		pending = request< CountryOption[] >( path )
			.then( ( list ) => ( Array.isArray( list ) ? list : [] ) )
			.catch( () => {
				// Let the next form open retry instead of caching the failure.
				cache.delete( path );
				return [];
			} );
		cache.set( path, pending );
	}
	return pending;
}

function useOptions( path: string | null ): CountryOption[] {
	const [ options, setOptions ] = useState< CountryOption[] >( [] );

	useEffect( () => {
		if ( ! path ) {
			setOptions( [] );
			return undefined;
		}
		let active = true;
		void load( path ).then( ( list ) => {
			if ( active ) {
				setOptions( list );
			}
		} );
		return () => {
			active = false;
		};
	}, [ path ] );

	return options;
}

/** All countries. */
export function useCountries(): CountryOption[] {
	return useOptions( '/erp/v2/countries' );
}

/** States of one country; empty when none is chosen or it defines no states. */
export function useStates( country: string | undefined ): CountryOption[] {
	return useOptions( country ? `/erp/v2/countries/${ encodeURIComponent( country ) }/states` : null );
}
