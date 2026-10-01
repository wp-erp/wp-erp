/**
 * Link button: primary-coloured text that underlines on hover, with no box,
 * border or padding. The "Add new" and "Download a sample CSV" links, a
 * clickable date or count in a table cell, an asset name that opens its
 * detail.
 *
 * It is a `type="button"` for in-page actions; a route or URL stays a
 * `Link` / `a`. It is deliberately not the design-system `Button`:
 * `variant="link"` is 36px tall with padding, which is a different look.
 *
 * Weight is medium by default. `className` is merged with `cn`, so a caller
 * adds layout (`inline-flex items-center gap-1`) or a size (`text-xs`) on
 * top.
 */

import { cn } from '@wedevs/plugin-ui';
import type { ComponentProps, JSX } from 'react';

type LinkButtonProps = Omit< ComponentProps< 'button' >, 'type' >;

export function LinkButton( { className, children, ...rest }: LinkButtonProps ): JSX.Element {
	return (
		<button
			type="button"
			className={ cn( 'font-medium text-primary hover:underline', className ) }
			{ ...rest }
		>
			{ children }
		</button>
	);
}
