/**
 * Overview card primitives for the employee profile: a titled card
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
			<dl className="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2 lg:grid-cols-3">
				{ children }
			</dl>
		</section>
	);
}

export function Item( { label, value, icon: Icon }: { readonly label: string; readonly value: string; readonly icon?: LucideIcon } ): JSX.Element {
	return (
		// `min-w-0` and `wrap-anywhere`: a long value with no spaces (a website,
		// an email) wraps inside its grid cell instead of running off the card.
		// `dl > div > dt + dd` only, so the icon sits inside the `dt`, absolutely
		// placed in the gutter the padding leaves (size-7 + gap-2.5).
		<div className={ `relative flex min-w-0 flex-col gap-0.5 ${ Icon ? 'ps-[2.375rem]' : '' }` }>
			<dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
				{ Icon ? (
					<span className="absolute start-0 top-0 mt-0.5 inline-flex size-7 items-center justify-center rounded-md bg-primary/10 text-primary">
						<Icon size={ 14 } aria-hidden="true" />
					</span>
				) : null }
				{ label }
			</dt>
			<dd className="text-sm text-foreground wrap-anywhere">{ value.trim() === '' ? '—' : value }</dd>
		</div>
	);
}
