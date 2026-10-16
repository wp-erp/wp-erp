/**
 * Quiet text button: muted text that darkens on hover, with no box, border or
 * padding. The bulk bar's "Clear", the profile "Back" link, the "copy ID" and
 * "download sample" links.
 *
 * It is deliberately not the design-system `Button`: `variant="link"` is
 * primary-coloured, underlined on hover and 36px tall, which is a different
 * look.
 *
 * `className` is merged with `cn`, so a caller adds layout (`inline-flex
 * items-center gap-1.5`), weight or a smaller size (`text-xs`) on top.
 */

import { cn } from '@wedevs/plugin-ui';
import type { ComponentProps, JSX } from 'react';

type TextButtonProps = Omit< ComponentProps< 'button' >, 'type' >;

export function TextButton( { className, children, ...rest }: TextButtonProps ): JSX.Element {
	return (
		<button
			type="button"
			className={ cn( 'text-sm text-muted-foreground hover:text-foreground', className ) }
			{ ...rest }
		>
			{ children }
		</button>
	);
}
