/**
 * The native inputs the design system has no equal for, one component each, so
 * feature code never writes a raw `<input>`.
 *
 *  - `FileInput`     : a visible file picker (the browser's "Choose files" row).
 *  - `DateTimeInput` : a `datetime-local` field.
 *  - `ColorInput`    : a colour swatch that opens the browser's colour picker.
 *
 * The design-system `Input` is not used for these: it adds a shadow, a focus
 * ring and its own file-button styling, which is a different look. A visually
 * hidden `type="file"` input driven by a button or dropzone is not covered
 * here; that one stays a raw `<input>` next to its trigger.
 *
 * Every one forwards `id`, `value`, `onChange`, `disabled` and the rest of the
 * input props, and merges `className` with `cn`.
 */

import { cn } from '@wedevs/plugin-ui';
import type { ComponentProps, JSX } from 'react';

type InputProps = Omit< ComponentProps< 'input' >, 'type' >;

export function FileInput( { className, ...rest }: InputProps ): JSX.Element {
	return (
		<input
			type="file"
			className={ cn(
				'block w-full text-sm text-muted-foreground file:mr-3 file:rounded-md file:border file:border-border file:bg-muted file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-foreground hover:file:bg-muted/70 disabled:opacity-50',
				className
			) }
			{ ...rest }
		/>
	);
}

export function DateTimeInput( { className, ...rest }: InputProps ): JSX.Element {
	return (
		<input
			type="datetime-local"
			className={ cn( 'h-10 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground', className ) }
			{ ...rest }
		/>
	);
}

export function ColorInput( { className, ...rest }: InputProps ): JSX.Element {
	return (
		<input
			type="color"
			className={ cn( 'h-10 w-16 cursor-pointer rounded-md border border-border bg-background p-1', className ) }
			{ ...rest }
		/>
	);
}
