/**
 * Single employee node card for the org chart: avatar · name · designation, with
 * a hover mail action. Presentational only.
 */

import { Avatar, AvatarFallback, AvatarImage } from '@wedevs/plugin-ui';
import { Mail } from 'lucide-react';
import type { JSX } from 'react';

import { makeInitials } from '@/shared/components/PersonCell';
import { __ } from '@/shared/i18n';

import type { ServerNode } from './org-chart-format';

/** Single employee card: avatar · name · designation, with a mail action. */
export function OrgCard( { node }: { node: ServerNode } ): JSX.Element {
	return (
		<div className="group relative inline-flex w-60 items-center gap-3 rounded-xl border border-border bg-card py-2.5 pl-3 pr-8 text-left shadow-sm transition-shadow hover:shadow-md">
			<Avatar className="size-10 shrink-0">
				{ node.avatar ? <AvatarImage src={ node.avatar } alt="" /> : null }
				<AvatarFallback>{ makeInitials( node.name ) }</AvatarFallback>
			</Avatar>
			<div className="min-w-0 flex-1">
				<div className="truncate text-sm font-semibold text-foreground">{ node.name }</div>
				{ node.title ? <div className="truncate text-xs text-muted-foreground">{ node.title }</div> : null }
			</div>
			{ node.email ? (
				<a
					href={ `mailto:${ node.email }` }
					aria-label={ __( 'Email', 'erp' ) }
					title={ node.email }
					className="absolute right-1.5 top-1.5 inline-flex size-6 items-center justify-center rounded-md text-muted-foreground opacity-0 transition-opacity hover:bg-muted hover:text-foreground focus:opacity-100 group-hover:opacity-100"
				>
					<Mail size={ 13 } aria-hidden="true" />
				</a>
			) : null }
		</div>
	);
}
