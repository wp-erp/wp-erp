/**
 * One row of a vertical section menu: the left-hand nav of the employee
 * profile. Full width, icon then label; the current row is filled with the
 * primary colour and carries `aria-current="page"`.
 *
 * Children are the icon and the label. `className` is merged with `cn`, so a
 * layout that needs a different corner radius can pass e.g. `rounded-lg`.
 */

import { cn } from '@wedevs/plugin-ui';
import type { ComponentProps, JSX } from 'react';

interface NavItemButtonProps extends Omit< ComponentProps< 'button' >, 'type' | 'aria-current' > {
	/** True for the section being shown. */
	readonly active: boolean;
}

export function NavItemButton( { active, className, children, ...rest }: NavItemButtonProps ): JSX.Element {
	return (
		<button
			type="button"
			aria-current={ active ? 'page' : undefined }
			className={ cn(
				'flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-left text-sm font-medium transition-colors',
				active ? 'bg-primary text-primary-foreground' : 'text-foreground hover:bg-muted',
				className
			) }
			{ ...rest }
		>
			{ children }
		</button>
	);
}
