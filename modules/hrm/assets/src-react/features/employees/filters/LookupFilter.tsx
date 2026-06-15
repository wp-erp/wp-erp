/**
 * Generic single-value lookup dropdown used by Department / Designation /
 * Location filters. Renders nothing while the lookup is still loading or
 * empty so the filter row collapses cleanly when no options exist.
 *
 * Uses the same searchable `SmartSelect` combobox as the employee create/edit
 * form, so the Department / Designation dropdowns look + behave identically in
 * the filter row and in the forms.
 */

import { SmartSelect } from '@wedevs/plugin-ui';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect, useMemo, useState } from 'react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import type {
	EmployeeListQuery,
	EmployeesState,
} from '@/stores/employees';

import { loadLookup, readLookup } from './lookups';
import type { LookupOption } from './lookups';

interface EmployeesStoreSelectors {
	getFilters: () => EmployeeListQuery;
}

interface EmployeesStoreDispatch {
	setFilters:    ( filters: EmployeeListQuery ) => void;
	setPagination: ( pagination: EmployeesState[ 'pagination' ] ) => void;
}

type LookupKey = 'departments' | 'designations' | 'locations';
type FilterField = 'department_id' | 'designation_id' | 'location_id';

interface LookupFilterProps {
	readonly label:       string;
	readonly placeholder: string;
	readonly lookupKey:   LookupKey;
	readonly field:       FilterField;
}

export function LookupFilter( {
	label,
	placeholder,
	lookupKey,
	field,
}: LookupFilterProps ): JSX.Element | null {
	const [ options, setOptions ] = useState< LookupOption[] | null >( () => readLookup( lookupKey ) );
	const filters = useSelect(
		( select ) => ( select( employeesStoreName ) as unknown as EmployeesStoreSelectors ).getFilters(),
		[]
	);
	const { setFilters, setPagination } = useDispatch(
		employeesStoreName
	) as unknown as EmployeesStoreDispatch;

	useEffect( () => {
		if ( options ) {
			return;
		}
		let cancelled = false;
		void loadLookup( lookupKey ).then( ( list ) => {
			if ( ! cancelled ) {
				setOptions( list );
			}
		} );
		return () => {
			cancelled = true;
		};
	}, [ lookupKey, options ] );

	const selectOptions = useMemo(
		() => ( options ?? [] ).map( ( opt ) => ( { value: String( opt.id ), label: opt.title } ) ),
		[ options ]
	);

	if ( ! options || options.length === 0 ) {
		return null;
	}

	const value = filters[ field ] ? String( filters[ field ] ) : '';

	return (
		<div className="flex items-center gap-2">
			<label className="text-xs font-medium text-muted-foreground">
				{ label }
			</label>
			<SmartSelect
				options={ selectOptions }
				value={ value }
				onValueChange={ ( raw ) => {
					const next: EmployeeListQuery = { ...filters };
					if ( ! raw ) {
						delete ( next as Record< string, unknown > )[ field ];
					} else {
						( next as Record< string, unknown > )[ field ] = parseInt( raw, 10 );
					}
					setFilters( next );
					setPagination( { page: 1, perPage: 20 } );
				} }
				placeholder={ placeholder }
				searchPlaceholder={ __( 'Search…', 'erp' ) }
				emptyMessage={ __( 'No matches found.', 'erp' ) }
				showClear
				className="h-9 w-52 bg-background"
				contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
			/>
		</div>
	);
}

