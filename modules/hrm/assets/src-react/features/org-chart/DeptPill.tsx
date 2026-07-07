/**
 * A team pill in the org-chart top filter bar. Presentational toggle button.
 */

import type { JSX } from 'react';

/** A team pill in the top filter bar. */
export function DeptPill( { label, active, onClick }: { readonly label: string; readonly active: boolean; readonly onClick: () => void } ): JSX.Element {
	return (
		<button
			type="button"
			onClick={ onClick }
			aria-pressed={ active }
			className={ [
				'max-w-[14rem] shrink-0 truncate rounded-md border px-3 py-1.5 text-left text-xs font-medium transition-colors',
				active
					? 'border-primary/30 bg-primary/10 text-primary'
					: 'border-border bg-card text-foreground hover:bg-muted',
			].join( ' ' ) }
		>
			{ label }
		</button>
	);
}
