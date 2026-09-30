/**
 * An unstyled `type="button"` button for the one-off clickable surfaces that
 * are not a design-system `Button` and do not repeat often enough to be their
 * own component: a clickable list row, an avatar that opens the file picker, a
 * breadcrumb crumb, a fact pill.
 *
 * It adds no classes of its own; the caller's `className` is the whole look.
 * Reach for it last. A pattern that shows up on a second screen belongs in a
 * named component next to this one (`TextButton`, `IconButton`, `SortButton`,
 * `SegmentedTab`, `NavItemButton` ...), and an ordinary action belongs in the
 * design-system `Button`.
 */

import type { ComponentProps, JSX } from 'react';

type PlainButtonProps = Omit< ComponentProps< 'button' >, 'type' >;

export function PlainButton( { children, ...rest }: PlainButtonProps ): JSX.Element {
	return (
		<button type="button" { ...rest }>
			{ children }
		</button>
	);
}
