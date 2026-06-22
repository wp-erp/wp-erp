/**
 * Overview card primitives for the Employee Profile v3 layout: a titled card
 * with an icon chip + optional edit action (`InfoCard`) holding a 2-col grid of
 * label/value rows (`Field`). Presentational only.
 */

import { Pencil } from 'lucide-react';
import type { JSX, ReactNode } from 'react';

import { __ } from '@/shared/i18n';

import type { LucideIcon } from './profile-format';

/** Detail card — icon chip + title + optional edit, then a 2-col label/value grid. */
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
				<span className={ `inline-flex size-9 items-center justify-center rounded-xl ${ tone }` }>
					<Icon size={ 18 } strokeWidth={ 2 } aria-hidden="true" />
				</span>
				<h2 className="m-0 flex-1 text-2xl font-bold leading-tight tracking-tight text-foreground">{ title }</h2>
				{ onEdit ? (
					<button
						type="button"
						onClick={ onEdit }
						className="inline-flex size-8 items-center justify-center rounded-full text-muted-foreground ring-1 ring-border transition-colors hover:bg-muted hover:text-foreground"
						aria-label={ __( 'Edit', 'erp' ) }
						title={ __( 'Edit', 'erp' ) }
					>
						<Pencil size={ 14 } aria-hidden="true" />
					</button>
				) : null }
			</div>
			<div className="mb-4 mt-4 h-px w-full bg-border" />
			<dl className="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">{ children }</dl>
		</section>
	);
}

export function Field( { label, value, icon: Icon }: { readonly label: string; readonly value: string; readonly icon?: LucideIcon } ): JSX.Element {
	return (
		<div className="flex items-start gap-2.5">
			{ Icon ? (
				<span className="mt-0.5 inline-flex size-7 shrink-0 items-center justify-center rounded-md bg-primary/10 text-primary">
					<Icon size={ 14 } aria-hidden="true" />
				</span>
			) : null }
			<div className="flex min-w-0 flex-col gap-0.5">
				<dt className="text-xs text-muted-foreground">{ label }</dt>
				<dd className="text-sm font-medium text-foreground">{ value.trim() === '' ? '—' : value }</dd>
			</div>
		</div>
	);
}
