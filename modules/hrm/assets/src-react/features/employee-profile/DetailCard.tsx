/**
 * Overview card primitives for the Employee Profile layout: a titled card
 * (`DetailCard`) holding a responsive grid of label/value rows (`Item`).
 * Presentational only.
 */

import type { JSX, ReactNode } from 'react';

import type { LucideIcon } from './profile-format';

interface DetailCardProps {
	readonly title:    string;
	readonly children: ReactNode;
}

export function DetailCard( { title, children }: DetailCardProps ): JSX.Element {
	return (
		<section className="rounded-[10px] bg-card p-6 shadow-sm">
			<h2 className="mt-0 text-2xl font-bold leading-tight tracking-tight text-foreground">{ title }</h2>
			<div className="mb-4 mt-4 h-px w-full bg-border" />
			<dl className="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-2 lg:grid-cols-3">
				{ children }
			</dl>
		</section>
	);
}

export function Item( { label, value, icon: Icon }: { readonly label: string; readonly value: string; readonly icon?: LucideIcon } ): JSX.Element {
	return (
		<div className="flex items-start gap-2.5">
			{ Icon ? (
				<span className="mt-0.5 inline-flex size-7 shrink-0 items-center justify-center rounded-md bg-primary/10 text-primary">
					<Icon size={ 14 } aria-hidden="true" />
				</span>
			) : null }
			<div className="flex min-w-0 flex-col gap-0.5">
				<dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">{ label }</dt>
				<dd className="text-sm text-foreground">{ value.trim() === '' ? '—' : value }</dd>
			</div>
		</div>
	);
}
