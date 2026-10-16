/**
 * Small "ⓘ" info icon that reveals a short explanation on hover/focus. Use next
 * to a control whose state needs explaining (a disabled button, a prerequisite
 * field) so the user understands what to do without cluttering the layout.
 *
 * Self-contained: bundles its own `TooltipProvider` (the app has no global one).
 * Renders the trigger as a focusable <span> — never a <button> — so it is safe
 * to drop inside a <form> without accidentally submitting it.
 */

import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@wedevs/plugin-ui';
import { Info } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

interface InfoTooltipProps {
	readonly text:   string;
	readonly label?: string;
	readonly size?:  number;
}

export function InfoTooltip( { text, label, size = 14 }: InfoTooltipProps ): JSX.Element {
	return (
		<TooltipProvider delay={ 150 }>
			<Tooltip>
				<TooltipTrigger
					render={
						<span
							tabIndex={ 0 }
							role="img"
							aria-label={ label ?? __( 'More information', 'erp' ) }
							className="inline-flex cursor-help items-center text-muted-foreground hover:text-foreground"
						/>
					}
				>
					<Info size={ size } aria-hidden="true" />
				</TooltipTrigger>
				<TooltipContent className="max-w-xs text-sm">{ text }</TooltipContent>
			</Tooltip>
		</TooltipProvider>
	);
}
