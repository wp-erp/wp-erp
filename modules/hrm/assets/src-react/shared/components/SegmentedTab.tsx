/**
 * One segment of a segmented tab bar: the boxed switcher used for the request
 * types, the org-chart team filter and the calendar Month / Week / Day views.
 * The selected segment is a raised card with primary text and a primary ring.
 *
 * The caller owns the `role="tablist"` container
 * (`inline-flex w-fit items-center gap-1 rounded-lg border border-border bg-muted/60 p-1`)
 * and renders one `SegmentedTab` per option. Extra classes (a max width and
 * truncation for long labels) go in `className`; they are appended as they
 * are, not merged, so pass additions only.
 */

import type { ComponentProps, JSX } from 'react';

interface SegmentedTabProps extends Omit< ComponentProps< 'button' >, 'type' | 'role' | 'aria-selected' > {
	/** True for the selected segment. */
	readonly active: boolean;
}

export function SegmentedTab( { active, className, children, ...rest }: SegmentedTabProps ): JSX.Element {
	return (
		<button
			type="button"
			role="tab"
			aria-selected={ active }
			className={ [
				'inline-flex shrink-0 flex-none items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium ring-1 ring-transparent transition-all',
				active ? 'bg-card text-primary shadow-sm ring-primary/40' : 'text-muted-foreground hover:text-foreground',
				className ?? '',
			].join( ' ' ).trim() }
			{ ...rest }
		>
			{ children }
		</button>
	);
}
