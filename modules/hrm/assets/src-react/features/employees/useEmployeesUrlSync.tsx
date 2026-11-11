/**
 * Two-way URL sync for the Employees page.
 *
 * On mount: read `?search`, `?status`, `?department_id`, `?designation_id`,
 * `?location_id`, `?orderby`, `?order`, `?page`, `?per_page` from the hash
 * route's query string and dispatch matching setters.
 *
 * On store change: write the canonical state back to the URL via React Router's
 * `useSearchParams`. Identical values are skipped to avoid history churn.
 */

import { useDispatch } from '@wordpress/data';
import { useEffect, useRef } from 'react';
import { useSearchParams } from 'react-router-dom';

import { toEnumOrNull, toIntOrNull, toStr } from '@/shared/utils/coerce';
import { storeName as employeesStoreName } from '@/stores/employees';
import type {
	EmployeeListQuery,
	EmployeesState,
} from '@/stores/employees';

import { DEFAULT_PER_PAGE } from './constants';
import { useEmployeesQuery } from './useEmployeesQuery';

interface EmployeesStoreDispatch {
	setFilters:    ( filters: EmployeeListQuery ) => void;
	setSort:       ( sort: EmployeesState[ 'sort' ] ) => void;
	setPagination: ( pagination: EmployeesState[ 'pagination' ] ) => void;
}

const STATUS_VALUES = [ 'active', 'inactive', 'terminated', 'resigned', 'deceased', 'all', 'trash' ] as const;
const ORDERBY_VALUES = [ 'full_name', 'email', 'hire_date', 'status' ] as const;
const ORDER_VALUES = [ 'asc', 'desc' ] as const;

export function useEmployeesUrlSync(): void {
	const [ params, setParams ] = useSearchParams();
	const { setFilters, setSort, setPagination } = useDispatch(
		employeesStoreName
	) as unknown as EmployeesStoreDispatch;
	const { query } = useEmployeesQuery();
	const hasReadInitial = useRef( false );

	// On mount → read URL into store.
	useEffect( () => {
		if ( hasReadInitial.current ) {
			return;
		}
		hasReadInitial.current = true;

		const builder: Record< string, unknown > = {};

		const search = toStr( params.get( 'search' ) );
		if ( search ) {
			builder.search = search;
		}

		const status = toEnumOrNull( params.get( 'status' ), STATUS_VALUES );
		if ( status ) {
			builder.status = status;
		}

		const department = toIntOrNull( params.get( 'department_id' ) );
		if ( department !== null ) {
			builder.department_id = department;
		}

		const designation = toIntOrNull( params.get( 'designation_id' ) );
		if ( designation !== null ) {
			builder.designation_id = designation;
		}

		const location = toIntOrNull( params.get( 'location_id' ) );
		if ( location !== null ) {
			builder.location_id = location;
		}

		if ( Object.keys( builder ).length > 0 ) {
			setFilters( builder as EmployeeListQuery );
		}

		const orderby = toEnumOrNull( params.get( 'orderby' ), ORDERBY_VALUES );
		const order   = toEnumOrNull( params.get( 'order' ),   ORDER_VALUES );
		if ( orderby && order ) {
			setSort( { orderby, order } );
		}

		const page    = toIntOrNull( params.get( 'page' ) );
		const perPage = toIntOrNull( params.get( 'per_page' ) );
		if ( page !== null || perPage !== null ) {
			setPagination( {
				page:    page ?? 1,
				perPage: perPage ?? DEFAULT_PER_PAGE,
			} );
		}
	}, [ params, setFilters, setSort, setPagination ] );

	// On store change → write back to URL.
	useEffect( () => {
		if ( ! hasReadInitial.current ) {
			return;
		}
		const next = new URLSearchParams( params );

		setOrUnset( next, 'search',         query.search );
		setOrUnset( next, 'status',         query.status );
		setOrUnset( next, 'department_id',  query.department_id );
		setOrUnset( next, 'designation_id', query.designation_id );
		setOrUnset( next, 'location_id',    query.location_id );
		setOrUnset( next, 'orderby',        query.orderby );
		setOrUnset( next, 'order',          query.order );
		setOrUnset( next, 'page',           query.page );
		setOrUnset( next, 'per_page',       query.per_page );

		if ( next.toString() !== params.toString() ) {
			setParams( next, { replace: true } );
		}
	}, [ query, params, setParams ] );
}

function setOrUnset(
	params: URLSearchParams,
	key: string,
	value: unknown
): void {
	if ( value === undefined || value === null || value === '' ) {
		params.delete( key );
		return;
	}
	params.set( key, String( value ) );
}
