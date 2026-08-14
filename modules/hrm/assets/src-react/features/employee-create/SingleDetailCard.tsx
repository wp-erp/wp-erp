/**
 * Overview card primitives for the v4 single-employee profile view: a titled
 * field group (`FieldGrid`) holding a responsive grid of label/value rows
 * (`Field`). Presentational only.
 */

import type { JSX, ReactNode } from 'react';

interface DetailCardProps {
	readonly title:    string;
	readonly children: ReactNode;
}

export function FieldGrid( { title, children }: DetailCardProps ): JSX.Element {
	return (
		<section>
			<h3 className="m-0 mb-4 text-sm font-semibold uppercase tracking-wide text-muted-foreground">{ title }</h3>
			<dl className="mt-4 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">{ children }</dl>
		</section>
	);
}

export function Field( { label, value }: { readonly label: string; readonly value: string } ): JSX.Element {
	return (
		<div className="flex flex-col gap-0.5">
			<dt className="text-xs text-muted-foreground">{ label }</dt>
			<dd className="text-sm font-medium text-foreground">{ value.trim() === '' ? '—' : value }</dd>
		</div>
	);
}
