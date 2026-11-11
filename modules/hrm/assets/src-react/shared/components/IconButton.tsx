/**
 * Square bordered icon button: the 32px prev / next control of the table pager
 * and the calendar date nav.
 *
 * It is deliberately not the design-system `Button`: that one brings its own
 * height, radius, focus ring and primary border, which is a different look.
 *
 * Classes passed in `className` are merged with `cn`, so a caller can resize or
 * recolour it (e.g. the 36px muted refresh button on the dashboard) without
 * fighting the defaults. Always pass an `aria-label`.
 */

import { cn } from '@wedevs/plugin-ui';
import type { ComponentProps, JSX } from 'react';

type IconButtonProps = Omit< ComponentProps< 'button' >, 'type' >;

export function IconButton( { className, children, ...rest }: IconButtonProps ): JSX.Element {
	return (
		<button
			type="button"
			className={ cn(
				'inline-flex size-8 items-center justify-center rounded-md border border-border bg-card text-foreground hover:bg-muted disabled:opacity-40',
				className
			) }
			{ ...rest }
		>
			{ children }
		</button>
	);
}
