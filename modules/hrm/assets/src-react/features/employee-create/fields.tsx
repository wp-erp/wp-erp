/**
 * Page-local form primitives for the employee create page.
 *
 * Thin wrappers over `@wedevs/plugin-ui` `Input` / `Label` / `Textarea` plus a
 * token-styled native `<select>` (same visual treatment as the filters row in
 * `features/employees/filters/LookupFilter.tsx`). Kept local to this feature —
 * they are layout glue, not a shared component library.
 */

import {
	Input,
	Label,
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue,
	SmartSelect,
	Textarea,
} from '@wedevs/plugin-ui';
import type { JSX, ReactNode } from 'react';

import { DateField } from '@/shared/DateField';
import { __ } from '@/shared/i18n';

import type { Option } from './options';

// Sentinel for the "no selection" item so optional selects can be reset back to
// empty (Base UI Select shows the placeholder when the value is `undefined`).
const EMPTY_VALUE = '__erp_empty__';

interface FieldShellProps {
	readonly id:       string;
	readonly label:    string;
	readonly required?: boolean | undefined;
	readonly error?:   string | undefined;
	readonly className?: string | undefined;
	readonly children: ReactNode;
	readonly hint?:    string | undefined;
	/** Optional affordance rendered to the right of the label — e.g. a "+ Add new" quick-create button. */
	readonly labelAction?: ReactNode;
}

function FieldShell( {
	id,
	label,
	required,
	error,
	className,
	children,
	hint,
	labelAction,
}: FieldShellProps ): JSX.Element {
	return (
		<div className={ [ 'flex min-w-0 flex-col gap-2.5', className ?? '' ].join( ' ' ) }>
			<div className="flex min-h-[1.25rem] items-center justify-between gap-2">
				<Label htmlFor={ id } className="text-sm font-medium text-foreground">
					{ label }
					{ required ? <span className="ml-0.5 text-destructive">*</span> : null }
				</Label>
				{ labelAction }
			</div>
			{ children }
			{ hint && ! error ? (
				<p className="text-xs text-muted-foreground">{ hint }</p>
			) : null }
			{ error ? <p className="text-xs text-destructive">{ error }</p> : null }
		</div>
	);
}

interface TextFieldProps {
	readonly id:          string;
	readonly label:       string;
	readonly value:       string;
	readonly onChange:    ( value: string ) => void;
	readonly type?:       'text' | 'email' | 'url' | 'date' | 'number' | 'tel' | 'password' | undefined;
	readonly required?:   boolean | undefined;
	readonly error?:      string | undefined;
	readonly placeholder?: string | undefined;
	readonly maxLength?:  number | undefined;
	readonly disabled?:   boolean | undefined;
	readonly className?:  string | undefined;
}

export function TextField( {
	id,
	label,
	value,
	onChange,
	type = 'text',
	required,
	error,
	placeholder,
	maxLength,
	disabled,
	className,
}: TextFieldProps ): JSX.Element {
	return (
		<FieldShell id={ id } label={ label } required={ required } error={ error } className={ className }>
			{ type === 'date' ? (
				<DateField
					value={ value }
					onChange={ onChange }
					disabled={ disabled }
					placeholder={ placeholder }
					className="h-10 w-full border-border bg-background px-4 text-sm"
				/>
			) : (
				<Input
					id={ id }
					type={ type }
					value={ value }
					required={ required }
					disabled={ disabled }
					placeholder={ placeholder }
					maxLength={ maxLength }
					onChange={ ( e ) => onChange( e.target.value ) }
					className="h-10 w-full border-border bg-background px-4 text-sm"
					aria-invalid={ error ? true : undefined }
				/>
			) }
		</FieldShell>
	);
}

interface SelectFieldProps {
	readonly id:          string;
	readonly label:       string;
	readonly value:       string;
	readonly onChange:    ( value: string ) => void;
	readonly options:     readonly Option[];
	readonly placeholder?: string | undefined;
	readonly required?:   boolean | undefined;
	readonly error?:      string | undefined;
	readonly disabled?:   boolean | undefined;
	readonly className?:  string | undefined;
}

export function SelectField( {
	id,
	label,
	value,
	onChange,
	options,
	placeholder,
	required,
	error,
	disabled,
	className,
}: SelectFieldProps ): JSX.Element {
	return (
		<FieldShell id={ id } label={ label } required={ required } error={ error } className={ className }>
			<Select
				items={ options as { value: string; label: string }[] }
				value={ value === '' ? undefined : value }
				onValueChange={ ( v ) => onChange( v == null || v === EMPTY_VALUE ? '' : String( v ) ) }
				required={ required }
				disabled={ disabled }
			>
				<SelectTrigger id={ id } aria-invalid={ error ? true : undefined } className="h-10 w-full bg-background">
					<SelectValue placeholder={ placeholder ?? '—' } />
				</SelectTrigger>
				<SelectContent align="start" alignItemWithTrigger={ false }>
					{ ! required ? (
						<SelectItem value={ EMPTY_VALUE }>{ placeholder ?? '—' }</SelectItem>
					) : null }
					{ options.map( ( opt ) => (
						<SelectItem key={ opt.value } value={ opt.value }>
							{ opt.label }
						</SelectItem>
					) ) }
				</SelectContent>
			</Select>
		</FieldShell>
	);
}

interface SmartSelectFieldProps {
	readonly id:                string;
	readonly label:             string;
	readonly value:             string;
	readonly onChange:          ( value: string ) => void;
	readonly options:           readonly Option[];
	readonly placeholder?:      string | undefined;
	readonly searchPlaceholder?: string | undefined;
	readonly emptyMessage?:     string | undefined;
	readonly required?:         boolean | undefined;
	readonly error?:            string | undefined;
	readonly className?:        string | undefined;
	/**
	 * Optional server-side search. When provided, `SmartSelect` calls this
	 * (debounced) on every keystroke instead of filtering `options` client-side
	 * — so a picker backed by a paged endpoint can reach every row by typing.
	 */
	readonly onSearch?:         ( ( query: string ) => void | Promise< void > ) | undefined;
	readonly loading?:          boolean | undefined;
	readonly disabled?:         boolean | undefined;
	/** Optional affordance rendered to the right of the label — e.g. a "+ Add new" quick-create button. */
	readonly labelAction?:      ReactNode;
}

/**
 * Searchable single-select built on plugin-ui's `SmartSelect` combobox. Use for
 * long option lists (employees, departments) where a native `<select>` is
 * unwieldy; `SelectField` (native) stays the right choice for short enums.
 */
export function SmartSelectField( {
	id,
	label,
	value,
	onChange,
	options,
	placeholder,
	searchPlaceholder,
	emptyMessage,
	required,
	error,
	className,
	onSearch,
	loading,
	disabled,
	labelAction,
}: SmartSelectFieldProps ): JSX.Element {
	return (
		<FieldShell id={ id } label={ label } required={ required } error={ error } className={ className } labelAction={ labelAction }>
			<SmartSelect
				options={ options as { value: string; label: string }[] }
				value={ value }
				onValueChange={ onChange }
				placeholder={ placeholder ?? __( '- Select -', 'erp' ) }
				searchPlaceholder={ searchPlaceholder ?? __( 'Search…', 'erp' ) }
				emptyMessage={ emptyMessage ?? __( 'No matches found.', 'erp' ) }
				invalid={ error ? true : false }
				{ ...( disabled !== undefined ? { disabled } : {} ) }
				{ ...( onSearch ? { onSearch } : {} ) }
				{ ...( loading !== undefined ? { loading } : {} ) }
				showClear
				className="h-10 w-full"
				// plugin-ui's SmartSelect content reads `--anchor-width`, but its
				// popover positioner sets `--popover-anchor-width` — so the popup
				// collapses to its intrinsic width. Force it to the trigger width.
				// Cap the height so the list fits below the trigger and Base UI's
				// collision-flip keeps it under the field instead of flipping above.
				contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))] max-h-[min(16rem,var(--available-height))] overflow-y-auto"
			/>
		</FieldShell>
	);
}

interface TextareaFieldProps {
	readonly id:        string;
	readonly label:     string;
	readonly value:     string;
	readonly onChange:  ( value: string ) => void;
	readonly rows?:     number | undefined;
	readonly required?: boolean | undefined;
	readonly error?:    string | undefined;
	readonly disabled?: boolean | undefined;
	readonly className?: string | undefined;
}

export function TextareaField( {
	id,
	label,
	value,
	onChange,
	rows = 4,
	required,
	error,
	disabled,
	className,
}: TextareaFieldProps ): JSX.Element {
	return (
		<FieldShell id={ id } label={ label } required={ required } error={ error } className={ className }>
			<Textarea
				id={ id }
				value={ value }
				rows={ rows }
				required={ required }
				disabled={ disabled }
				onChange={ ( e ) => onChange( e.target.value ) }
				className="w-full border-border bg-background text-sm"
				aria-invalid={ error ? true : undefined }
			/>
		</FieldShell>
	);
}

interface FormSectionProps {
	readonly title:       string;
	readonly description?: string;
	readonly children:    ReactNode;
}

export function FormSection( { title, description, children }: FormSectionProps ): JSX.Element {
	return (
		<section className="rounded-[10px] bg-card p-6 shadow-sm">
			<h2 className="mt-0 text-2xl font-bold leading-tight tracking-tight text-foreground">{ title }</h2>
			{ description ? (
				<p className="mt-1.5 text-sm text-muted-foreground">{ description }</p>
			) : null }
			<div className="mb-4 mt-4 h-px w-full bg-border" />
			<div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
				{ children }
			</div>
		</section>
	);
}
