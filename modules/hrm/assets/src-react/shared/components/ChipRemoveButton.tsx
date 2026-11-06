/**
 * Chip remove button: the small x inside a selected chip or picked item
 * (multi-selects, tag inputs, an uploaded CV, an email recipient). Muted
 * until hovered, then destructive, with a 12px X icon, so removing a value
 * looks the same on every screen.
 *
 * `aria-label` is required: the button has no visible text. `className`
 * is merged with `cn` for spacing only (`ml-0.5`).
 */

import { cn } from '@wedevs/plugin-ui';
import { X } from 'lucide-react';
import type { ComponentProps, JSX } from 'react';

type ChipRemoveButtonProps = Omit< ComponentProps< 'button' >, 'type' | 'children' | 'aria-label' > & {
	readonly 'aria-label': string;
};

export function ChipRemoveButton( { className, ...rest }: ChipRemoveButtonProps ): JSX.Element {
	return (
		<button
			type="button"
			className={ cn( 'inline-flex shrink-0 items-center text-muted-foreground hover:text-destructive', className ) }
			{ ...rest }
		>
			<X size={ 12 } aria-hidden="true" />
		</button>
	);
}
