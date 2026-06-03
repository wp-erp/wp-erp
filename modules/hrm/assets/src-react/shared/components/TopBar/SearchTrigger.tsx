/**
 * Right-cluster search trigger. Modal search is post-MVP — this is the slot.
 */

import { Button, Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@wedevs/plugin-ui';
import { Search } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

export function SearchTrigger(): JSX.Element {
	return (
		<TooltipProvider>
			<Tooltip>
				<TooltipTrigger
					render={
						<Button
							variant="ghost"
							size="icon"
							disabled
							aria-label={ __( 'Search (coming soon)', 'erp' ) }
						>
							<Search size={ 16 } strokeWidth={ 1.75 } aria-hidden="true" />
						</Button>
					}
				/>
				<TooltipContent>{ __( 'Search (coming soon)', 'erp' ) }</TooltipContent>
			</Tooltip>
		</TooltipProvider>
	);
}
