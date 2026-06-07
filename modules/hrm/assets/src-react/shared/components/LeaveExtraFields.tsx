/**
 * Renders pro-injected leave fields inside the leave-policy and leave-request
 * dialogs.
 *
 * Definitions arrive via the `erp_hr.leave.policy_fields` /
 * `erp_hr.leave.request_fields` wp.hooks filters (filled by ERP Pro's Advanced
 * Leave module — half-day, accrual, carry-forward, segregation). This mirrors
 * the employee form's `ExtraFields`: pro returns field *data* (not components),
 * and the free app renders it, so there is no cross-bundle React.
 *
 * Values live in the parent dialog's `extra` state and are submitted under the
 * `extra` payload bucket, which the v2 controllers bridge onto `$_POST` for the
 * legacy `erp_hr_leave_insert_policy_extra` / `erp_hr_leave_new_args` filters.
 */

import { Label, LabeledRadio, RadioGroup, Switch } from '@wedevs/plugin-ui';
import type { JSX } from 'react';

import { SelectField, TextField } from '@/features/employee-create/fields';

/** A choice for a `select` / `radio` leave field. */
export interface LeaveExtraFieldOption {
	readonly value: string;
	readonly label: string;
}

/** A single pro-injected leave field definition. */
export interface LeaveExtraField {
	/** Payload key (read off `$_POST` by the legacy save filters). */
	readonly key:      string;
	readonly type:     'checkbox' | 'number' | 'select' | 'radio' | 'text';
	readonly label:    string;
	/** Section heading — consecutive fields sharing a section group together. */
	readonly section?: string;
	/** When set, the value nests under `extra[group][key]` (segregation → `segre`). */
	readonly group?:   string;
	readonly options?: readonly LeaveExtraFieldOption[];
	readonly help?:    string;
	/** Initial / edit-prefill value. */
	readonly default?: string | boolean;
	/** Grid span inside the section (1–2); defaults to 1. */
	readonly colSpan?: 1 | 2;
}

/** The nested values bucket sent as the `extra` payload key. */
export type LeaveExtraValues = Record< string, unknown >;

/** Read a field's current value out of the nested `extra` bucket. */
export function getLeaveFieldValue( values: LeaveExtraValues, field: LeaveExtraField ): unknown {
	if ( field.group ) {
		const grp = values[ field.group ];
		return grp && typeof grp === 'object' ? ( grp as Record< string, unknown > )[ field.key ] : undefined;
	}
	return values[ field.key ];
}

/** Return a new `extra` bucket with one field's value set (respecting `group`). */
export function setLeaveFieldValue(
	values: LeaveExtraValues,
	field: LeaveExtraField,
	value: unknown
): LeaveExtraValues {
	if ( field.group ) {
		const grp = ( values[ field.group ] as Record< string, unknown > ) ?? {};
		return { ...values, [ field.group ]: { ...grp, [ field.key ]: value } };
	}
	return { ...values, [ field.key ]: value };
}

/** Build the initial `extra` bucket from each field's `default`. */
export function initLeaveFieldValues( fields: readonly LeaveExtraField[] ): LeaveExtraValues {
	let values: LeaveExtraValues = {};
	for ( const f of fields ) {
		values = setLeaveFieldValue( values, f, f.default ?? ( f.type === 'checkbox' ? false : '' ) );
	}
	return values;
}

interface LeaveExtraFieldsProps {
	readonly fields:   readonly LeaveExtraField[];
	readonly values:   LeaveExtraValues;
	readonly onChange: ( field: LeaveExtraField, value: unknown ) => void;
}

function renderField(
	field: LeaveExtraField,
	value: unknown,
	onChange: ( value: unknown ) => void
): JSX.Element {
	const id = `al-${ field.group ? `${ field.group }-` : '' }${ field.key }`;

	if ( field.type === 'checkbox' ) {
		return (
			<div
				key={ id }
				className="flex items-center justify-between rounded-md border border-border bg-muted/20 px-4 py-3 sm:col-span-2"
			>
				<Label htmlFor={ id } className="text-sm font-medium text-foreground">
					{ field.label }
					{ field.help ? (
						<span className="mt-0.5 block text-xs font-normal text-muted-foreground">{ field.help }</span>
					) : null }
				</Label>
				<Switch
					id={ id }
					checked={ value === true || value === 'on' || value === '1' }
					onCheckedChange={ ( checked ) => onChange( checked ) }
				/>
			</div>
		);
	}

	if ( field.type === 'radio' ) {
		return (
			<div key={ id } className="flex min-w-0 flex-col gap-2.5 sm:col-span-2">
				<span className="text-sm font-medium text-foreground">{ field.label }</span>
				<RadioGroup
					value={ value == null ? '' : String( value ) }
					onValueChange={ ( v ) => onChange( v == null ? '' : String( v ) ) }
					className="flex flex-row flex-wrap gap-x-6 gap-y-2"
				>
					{ ( field.options ?? [] ).map( ( opt ) => (
						<LabeledRadio key={ opt.value } value={ opt.value } label={ opt.label } />
					) ) }
				</RadioGroup>
				{ field.help ? <p className="text-xs text-muted-foreground">{ field.help }</p> : null }
			</div>
		);
	}

	if ( field.type === 'select' ) {
		return (
			<SelectField
				key={ id }
				id={ id }
				label={ field.label }
				options={ ( field.options ?? [] ).map( ( o ) => ( { value: o.value, label: o.label } ) ) }
				value={ value == null ? '' : String( value ) }
				onChange={ onChange }
				className={ field.colSpan === 2 ? 'sm:col-span-2' : undefined }
			/>
		);
	}

	return (
		<TextField
			key={ id }
			id={ id }
			label={ field.help ? `${ field.label } (${ field.help })` : field.label }
			type={ field.type === 'number' ? 'number' : 'text' }
			value={ value == null ? '' : String( value ) }
			onChange={ onChange }
			className={ field.colSpan === 2 ? 'sm:col-span-2' : undefined }
		/>
	);
}

/**
 * Render the injected fields, grouped into titled sections. Returns `null` when
 * no pro fields are present so the host dialog stays unchanged for free users.
 */
export function LeaveExtraFields( { fields, values, onChange }: LeaveExtraFieldsProps ): JSX.Element | null {
	if ( fields.length === 0 ) {
		return null;
	}

	// Group fields by their section label, preserving first-seen order.
	const groups: { section: string; items: LeaveExtraField[] }[] = [];
	for ( const f of fields ) {
		const section = f.section ?? '';
		let group = groups.find( ( g ) => g.section === section );
		if ( ! group ) {
			group = { section, items: [] };
			groups.push( group );
		}
		group.items.push( f );
	}

	return (
		<>
			{ groups.map( ( group ) => (
				<div key={ group.section || 'al-default' } className="flex min-w-0 flex-col gap-4">
					{ group.section ? (
						<div className="flex flex-col gap-3">
							<div className="h-px w-full bg-border" />
							<h3 className="m-0 text-sm font-semibold text-foreground">{ group.section }</h3>
						</div>
					) : null }
					<div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
						{ group.items.map( ( f ) =>
							renderField( f, getLeaveFieldValue( values, f ), ( v ) => onChange( f, v ) )
						) }
					</div>
				</div>
			) ) }
		</>
	);
}
