/**
 * The `<label>` patterns of the HR admin, one component each, so feature code
 * never writes a raw label.
 *
 *  - `FormLabel`   : the caption above a form control in a dialog or page form.
 *  - `FilterLabel` : a filter-row item, caption text followed by its control.
 *  - `CheckLabel`  : a checkbox or radio with its text on one line.
 *  - `PlainLabel`  : no classes; for a one-off (a dropzone, a stacked cell).
 *
 * None of them is the design-system `Label`: that one adds `leading-none`,
 * which pulls the text 6px tighter than these render today. `FieldShell` in
 * `features/employee-create/fields.tsx` does use the design-system `Label` and
 * stays as it is.
 *
 * Every one forwards `htmlFor` and the rest of the label props, and merges
 * `className` with `cn`.
 */

import { cn } from '@wedevs/plugin-ui';
import type { ComponentProps, JSX } from 'react';

type LabelProps = ComponentProps< 'label' >;

/** Caption above a form control. Pair it with the control through `htmlFor`. */
export function FormLabel( { className, children, ...rest }: LabelProps ): JSX.Element {
	return (
		<label className={ cn( 'text-sm font-medium text-foreground', className ) } { ...rest }>
			{ children }
		</label>
	);
}

/** Filter-row item: muted caption text, then the control it wraps. */
export function FilterLabel( { className, children, ...rest }: LabelProps ): JSX.Element {
	return (
		<label className={ cn( 'flex items-center gap-2 text-sm text-muted-foreground', className ) } { ...rest }>
			{ children }
		</label>
	);
}

/** A checkbox or radio followed by its text; clicking the text toggles it. */
export function CheckLabel( { className, children, ...rest }: LabelProps ): JSX.Element {
	return (
		<label className={ cn( 'flex items-center gap-2 text-sm text-foreground', className ) } { ...rest }>
			{ children }
		</label>
	);
}

/** A label with no classes of its own. The caller's `className` is the look. */
export function PlainLabel( { children, ...rest }: LabelProps ): JSX.Element {
	return <label { ...rest }>{ children }</label>;
}
