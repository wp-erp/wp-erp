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
			role="tab"
			aria-selected={ active }
			className={ [
				'inline-flex max-w-56 shrink-0 flex-none items-center truncate rounded-md px-3 py-1.5 text-sm font-medium ring-1 ring-transparent transition-all',
				active
					? 'bg-card text-primary shadow-sm ring-primary/40'
					: 'text-muted-foreground hover:text-foreground',
			].join( ' ' ) }
		>
			{ label }
		</button>
	);
}
