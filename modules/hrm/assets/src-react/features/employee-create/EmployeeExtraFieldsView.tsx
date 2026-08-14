/**
 * Read-only display of an employee's pro custom fields on the view profile.
 *
 * Reuses the `erp_hr.employee.extra_fields` wp.hooks filter (same source the
 * edit form uses) — it returns each field's definition plus the saved value —
 * and renders one card per section, mirroring the overview DetailCard layout.
 */

import { applyFilters } from '@wordpress/hooks';
import { useEffect, useState } from 'react';
import type { JSX } from 'react';

import { HOOKS } from '@/shared/filters';

import type { ExtraField } from './ExtraFields';

/** Resolve a stored value into its human label(s) for option-bearing fields. */
function displayValue( field: ExtraField ): string {
	const raw = ( field.value ?? '' ).trim();
	if ( raw === '' ) {
		return '';
	}

	const options = field.options ?? [];
	const textFor = ( v: string ): string => options.find( ( o ) => o.value === v )?.text ?? v;

	if ( field.type === 'checkbox' ) {
		return raw.split( ',' ).filter( Boolean ).map( textFor ).join( ', ' );
	}
	if ( field.type === 'select' || field.type === 'radio' ) {
		return textFor( raw );
	}
	return raw;
}

interface EmployeeExtraFieldsViewProps {
	readonly employeeId: number;
	/** Restrict to these legacy section keys (e.g. ['top']). Omit for all. */
	readonly sections?: readonly string[];
}

export function EmployeeExtraFieldsView( { employeeId, sections }: EmployeeExtraFieldsViewProps ): JSX.Element | null {
	const [ fields, setFields ] = useState< ExtraField[] >( [] );

	useEffect( () => {
		let cancelled = false;
		const result = applyFilters( HOOKS.EMPLOYEE_EXTRA_FIELDS, [], { mode: 'edit', employeeId } ) as
			| ExtraField[]
			| Promise< ExtraField[] >;
		void Promise.resolve( result ).then( ( list ) => {
			if ( ! cancelled && Array.isArray( list ) ) {
				setFields( list );
			}
		} );
		return () => {
			cancelled = true;
		};
	}, [ employeeId ] );

	const visible = sections
		? fields.filter( ( f ) => sections.includes( f.sectionKey ?? '' ) )
		: fields;

	if ( visible.length === 0 ) {
		return null;
	}

	// Group by section label, preserving first-seen order.
	const groups: { section: string; items: ExtraField[] }[] = [];
	for ( const f of visible ) {
		let group = groups.find( ( g ) => g.section === f.section );
		if ( ! group ) {
			group = { section: f.section, items: [] };
			groups.push( group );
		}
		group.items.push( f );
	}

	return (
		<>
			{ groups.map( ( group ) => (
				<section key={ group.section } className="rounded-[10px] bg-card p-6 shadow-sm">
					<h2 className="mt-0 text-2xl font-bold leading-tight tracking-tight text-foreground">
						{ group.section }
					</h2>
					<div className="mb-4 mt-4 h-px w-full bg-border" />
					<dl className="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-2 lg:grid-cols-3">
						{ group.items.map( ( field ) => {
							const value = displayValue( field );
							return (
								<div key={ field.key } className="flex flex-col gap-0.5">
									<dt className="text-xs font-medium text-muted-foreground">{ field.label }</dt>
									<dd className="text-sm text-foreground">{ value === '' ? '—' : value }</dd>
								</div>
							);
						} ) }
					</dl>
				</section>
			) ) }
		</>
	);
}
