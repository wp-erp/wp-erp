/**
 * Renders pro-injected custom fields inside the employee create/edit form.
 *
 * Definitions arrive via the `erp_hr.employee.extra_fields` wp.hooks filter
 * (filled by ERP Pro's Custom Field Builder). Values live in the parent form
 * state keyed by each field's machine name and are submitted under the
 * `additional` payload bucket, matching the legacy `erp-{people}-fields` model.
 */

import { LabeledCheckbox, LabeledRadio, RadioGroup } from '@wedevs/plugin-ui';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import { FormSection, SelectField, TextField, TextareaField } from './fields';

export interface ExtraFieldOption {
	readonly value: string;
	readonly text:  string;
}

export interface ExtraField {
	readonly key:          string;
	/** Raw section key (top/basic/work/personal/bottom) — drives form placement. */
	readonly sectionKey?:  string;
	readonly section:      string;
	readonly label:        string;
	readonly type:         string;
	readonly required?:    boolean;
	readonly placeholder?: string;
	readonly helptext?:    string;
	readonly options?:     readonly ExtraFieldOption[];
	/** Saved value (edit mode prefill). */
	readonly value?:       string;
}

const TEXT_TYPES: Record< string, 'text' | 'email' | 'url' | 'date' | 'number' | 'password' > = {
	text:     'text',
	email:    'email',
	url:      'url',
	date:     'date',
	number:   'number',
	password: 'password',
};

function toOptions( opts?: readonly ExtraFieldOption[] ): { value: string; label: string }[] {
	return ( opts ?? [] ).map( ( o ) => ( { value: o.value, label: o.text } ) );
}

interface GroupRadioProps {
	readonly field:    ExtraField;
	readonly value:    string;
	readonly onChange: ( value: string ) => void;
}

/** Title + helptext wrapper shared by the radio/checkbox groups. */
function GroupShell( {
	label,
	required,
	helptext,
	children,
}: {
	label: string;
	required?: boolean | undefined;
	helptext?: string | undefined;
	children: JSX.Element;
} ): JSX.Element {
	return (
		<div className="flex min-w-0 flex-col gap-2.5">
			<span className="text-sm font-medium text-foreground">
				{ label }
				{ required ? <span className="text-destructive"> *</span> : null }
			</span>
			{ children }
			{ helptext ? <p className="text-xs text-muted-foreground">{ helptext }</p> : null }
		</div>
	);
}

function RadioGroupField( { field, value, onChange }: GroupRadioProps ): JSX.Element {
	return (
		<GroupShell label={ field.label } required={ field.required } helptext={ field.helptext }>
			<RadioGroup
				value={ value }
				onValueChange={ ( v ) => onChange( v == null ? '' : String( v ) ) }
				className="flex flex-row flex-wrap gap-x-6 gap-y-2"
			>
				{ ( field.options ?? [] ).map( ( opt ) => (
					<LabeledRadio key={ opt.value } value={ opt.value } label={ opt.text } />
				) ) }
			</RadioGroup>
		</GroupShell>
	);
}

function CheckboxGroupField( { field, value, onChange }: GroupRadioProps ): JSX.Element {
	const selected = value ? value.split( ',' ).filter( Boolean ) : [];

	function toggle( optValue: string, checked: boolean ): void {
		const next = checked
			? [ ...selected, optValue ]
			: selected.filter( ( v ) => v !== optValue );
		onChange( next.join( ',' ) );
	}

	return (
		<GroupShell label={ field.label } required={ field.required } helptext={ field.helptext }>
			<div className="flex flex-row flex-wrap gap-x-6 gap-y-2">
				{ ( field.options ?? [] ).map( ( opt ) => (
					<LabeledCheckbox
						key={ opt.value }
						label={ opt.text }
						checked={ selected.includes( opt.value ) }
						onCheckedChange={ ( c ) => toggle( opt.value, c === true ) }
					/>
				) ) }
			</div>
		</GroupShell>
	);
}

function renderField(
	field: ExtraField,
	value: string,
	onChange: ( value: string ) => void,
	error?: string
): JSX.Element {
	const id = `cfb-${ field.key }`;

	if ( field.type === 'textarea' ) {
		return (
			<TextareaField
				key={ field.key }
				id={ id }
				label={ field.label }
				value={ value }
				onChange={ onChange }
				required={ field.required }
				error={ error }
				className="sm:col-span-2 lg:col-span-3"
			/>
		);
	}

	if ( field.type === 'select' ) {
		return (
			<SelectField
				key={ field.key }
				id={ id }
				label={ field.label }
				options={ toOptions( field.options ) }
				value={ value }
				onChange={ onChange }
				placeholder={ __( '- Select -', 'erp' ) }
				required={ field.required }
				error={ error }
			/>
		);
	}

	if ( field.type === 'radio' ) {
		return <RadioGroupField key={ field.key } field={ field } value={ value } onChange={ onChange } />;
	}

	if ( field.type === 'checkbox' ) {
		return <CheckboxGroupField key={ field.key } field={ field } value={ value } onChange={ onChange } />;
	}

	return (
		<TextField
			key={ field.key }
			id={ id }
			label={ field.label }
			type={ TEXT_TYPES[ field.type ] ?? 'text' }
			value={ value }
			onChange={ onChange }
			placeholder={ field.placeholder }
			required={ field.required }
			error={ error }
		/>
	);
}

interface ExtraFieldsProps {
	readonly fields:   readonly ExtraField[];
	readonly values:   Record< string, string >;
	readonly onChange: ( key: string ) => ( value: string ) => void;
	/**
	 * Render the raw fields only, for hosting inside an existing section card
	 * (Basic Information / Work / Personal Details) — the legacy form fired its
	 * `erp-hr-employee-form-*` hooks inside those grids, not in a card of their
	 * own. Standalone (`top`/`bottom`) groups keep the default card.
	 */
	readonly inline?:  boolean;
	/** field key → message, so a required custom field can report itself. */
	readonly errors?:  Record< string, string >;
}

export function ExtraFields( { fields, values, onChange, inline, errors }: ExtraFieldsProps ): JSX.Element | null {
	if ( fields.length === 0 ) {
		return null;
	}

	if ( inline ) {
		return (
			<>
				<div className="mt-2 flex items-center gap-3 sm:col-span-2 lg:col-span-3">
					<span className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
						{ __( 'Custom Fields', 'erp' ) }
					</span>
					<span className="h-px flex-1 bg-border" />
				</div>
				{ fields.map( ( f ) => renderField( f, values[ f.key ] ?? '', onChange( f.key ), errors?.[ f.key ] ) ) }
			</>
		);
	}

	// Group fields by their section label, preserving first-seen order.
	const groups: { section: string; items: ExtraField[] }[] = [];
	for ( const f of fields ) {
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
				<FormSection key={ group.section } title={ group.section }>
					{ group.items.map( ( f ) => renderField( f, values[ f.key ] ?? '', onChange( f.key ), errors?.[ f.key ] ) ) }
				</FormSection>
			) ) }
		</>
	);
}
