/**
 * Right-pane detail card primitives for the Employee Profile v2 layout: a card
 * with an icon chip, title and optional edit action (`InfoCard`) holding
 * label→value split rows (`SplitRow`). Presentational only.
 */

import { Button } from '@wedevs/plugin-ui';
import { Pencil } from 'lucide-react';
import type { JSX, ReactNode } from 'react';

import { __ } from '@/shared/i18n';

import type { LucideIcon } from './profile-format';

/** Detail card with an icon chip, title, optional edit action, and split rows. */
export function InfoCard( {
	icon: Icon,
	tone,
	title,
	onEdit,
	children,
}: {
	readonly icon:     LucideIcon;
	readonly tone:     string;
	readonly title:    string;
	readonly onEdit?:  ( () => void ) | undefined;
	readonly children: ReactNode;
} ): JSX.Element {
	return (
		<section className="rounded-[10px] bg-card p-6 shadow-sm">
			<div className="flex items-center gap-3">
				<span className={ `inline-flex size-9 items-center justify-center rounded-lg ${ tone }` }>
					<Icon size={ 18 } strokeWidth={ 2 } aria-hidden="true" />
				</span>
				<h2 className="m-0 mb-4 flex-1 text-2xl font-bold leading-tight tracking-tight text-foreground">{ title }</h2>
				{ onEdit ? (
					<Button
						type="button"
						variant="ghost"
						size="icon"
						onClick={ onEdit }
						className="inline-flex size-8 items-center justify-center rounded-full text-muted-foreground ring-1 ring-border transition-colors hover:bg-muted hover:text-foreground"
						aria-label={ __( 'Edit', 'erp' ) }
						title={ __( 'Edit', 'erp' ) }
					>
						<Pencil size={ 14 } aria-hidden="true" />
					</Button>
				) : null }
			</div>
			<div className="mb-4 mt-4 h-px w-full bg-border" />
			<dl className="divide-y divide-border">{ children }</dl>
		</section>
	);
}

/** Label (left) → value (right) split row. */
export function SplitRow( { label, value, icon: Icon }: { readonly label: string; readonly value: string; readonly icon?: LucideIcon } ): JSX.Element {
	return (
		<div className="flex items-start gap-3 py-3.5">
			{ Icon ? (
				<span className="mt-0.5 inline-flex size-7 shrink-0 items-center justify-center rounded-md bg-primary/10 text-primary">
					<Icon size={ 14 } aria-hidden="true" />
				</span>
			) : null }
			<div className="flex min-w-0 flex-1 items-start justify-between gap-6">
				<dt className="text-sm text-muted-foreground">{ label }</dt>
				<dd className="max-w-[60%] text-right text-sm font-medium text-foreground">
					{ value.trim() === '' ? '—' : value }
				</dd>
			</div>
		</div>
	);
}
