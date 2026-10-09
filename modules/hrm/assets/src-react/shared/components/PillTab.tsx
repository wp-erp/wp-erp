/**
 * One chip of a pill tab bar: the rounded profile-section switcher, where the
 * selected chip is a solid primary pill.
 *
 * The caller owns the `role="tablist"` container
 * (`flex flex-wrap items-center gap-1 rounded-full bg-muted/60 p-1`) and renders
 * one `PillTab` per section.
 */

import type { ComponentProps, JSX } from 'react';

interface PillTabProps extends Omit< ComponentProps< 'button' >, 'type' | 'role' | 'aria-selected' > {
	/** True for the selected chip. */
	readonly active: boolean;
}

export function PillTab( { active, className, children, ...rest }: PillTabProps ): JSX.Element {
	return (
		<button
			type="button"
			role="tab"
			aria-selected={ active }
			className={ [
				'rounded-full px-4 py-2 text-sm font-medium transition-colors',
				active ? 'bg-primary text-primary-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground',
				className ?? '',
			].join( ' ' ).trim() }
			{ ...rest }
		>
			{ children }
		</button>
	);
}
