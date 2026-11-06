/**
 * Status pill: the one way a status is shown in a table, card or header
 * (Active, Pending, Approved, Rejected, Draft...).
 *
 * Same look as the Employees table status cell: a soft tinted background with
 * darker text of the same hue, 6px corners. Pick the tone by meaning, not by
 * colour, so the same status reads the same on every screen:
 *
 *   success  done or good      Active, Approved, Present, Paid, Completed
 *   warning  waiting on someone  Pending, Draft, Awaiting
 *   danger   stopped or refused  Rejected, Terminated, Absent, Expired
 *   info     in progress         Forwarded, Scheduled, In review
 *   neutral  off or unknown      Inactive, Not approved, Closed, none
 */

import { Badge, cn } from '@wedevs/plugin-ui';
import type { JSX, ReactNode } from 'react';

export type StatusTone = 'success' | 'warning' | 'danger' | 'info' | 'neutral';

const TONE_CLASS: Record< StatusTone, string > = {
	success: 'bg-success-light text-success-on-light',
	warning: 'bg-warning-light text-warning-on-light',
	danger:  'bg-destructive-light text-destructive-on-light',
	info:    'bg-info-light text-info-on-light',
	neutral: 'bg-neutral-light text-neutral-on-light',
};

interface StatusPillProps {
	readonly tone:       StatusTone;
	readonly children:   ReactNode;
	readonly className?: string;
}

export function StatusPill( { tone, children, className }: StatusPillProps ): JSX.Element {
	return <Badge className={ cn( TONE_CLASS[ tone ], 'rounded-md', className ) }>{ children }</Badge>;
}
