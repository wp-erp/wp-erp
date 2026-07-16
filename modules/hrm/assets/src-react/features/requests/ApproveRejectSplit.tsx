/**
 * Approve / Reject split button — mirrors the Employees "Export" split control:
 * a primary "Approve" segment, a divider, then a chevron that reveals "Reject"
 * (and any extra items, e.g. Delete) in a dropdown. Shared by every request
 * table (Leave / Resignation / Remote Work) so moderation looks identical.
 */

import {
	Button,
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuItem,
	DropdownMenuTrigger,
} from '@wedevs/plugin-ui';
import { Check, ChevronDown, X } from 'lucide-react';
import type { JSX, ReactNode } from 'react';

import { __ } from '@/shared/i18n';

interface ApproveRejectSplitProps {
	readonly onApprove:   () => void;
	readonly onReject:    () => void;
	readonly disabled?:   boolean;
	/** Extra dropdown items rendered below "Reject" (e.g. a Delete item). */
	readonly extraItems?: ReactNode;
}

export function ApproveRejectSplit( { onApprove, onReject, disabled, extraItems }: ApproveRejectSplitProps ): JSX.Element {
	return (
		<div className="inline-flex h-9 items-center rounded-md border border-border bg-background shadow-sm">
			<Button
				type="button"
				variant="ghost"
				disabled={ disabled }
				onClick={ onApprove }
				className="h-full gap-1.5 rounded-l-md rounded-r-none border-0 px-3 text-sm font-medium leading-5 text-success shadow-none hover:bg-success/10 hover:text-success"
			>
				<Check size={ 15 } strokeWidth={ 2 } aria-hidden="true" />
				{ __( 'Approve', 'erp' ) }
			</Button>
			<span className="h-4 w-px shrink-0 bg-border" aria-hidden="true" />
			<DropdownMenu>
				<DropdownMenuTrigger
					render={
						<Button
							variant="ghost"
							size="icon"
							disabled={ disabled }
							className="h-full w-auto rounded-l-none rounded-r-md border-0 px-2 text-foreground shadow-none"
							aria-label={ __( 'More moderation actions', 'erp' ) }
						>
							<ChevronDown size={ 15 } strokeWidth={ 2 } aria-hidden="true" />
						</Button>
					}
				/>
				<DropdownMenuContent align="end" className="min-w-40">
					<DropdownMenuItem
						variant="destructive"
						className="gap-2"
						onClick={ onReject }
					>
						<X size={ 14 } aria-hidden="true" />
						{ __( 'Reject', 'erp' ) }
					</DropdownMenuItem>
					{ extraItems }
				</DropdownMenuContent>
			</DropdownMenu>
		</div>
	);
}
