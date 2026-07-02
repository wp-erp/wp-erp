/**
 * Termination-details card for the Employee Profile v4 overview.
 *
 * Rendered only for terminated employees (self / managers). Mirrors the legacy
 * `tab-general.php` "Termination" postbox: Termination Date, Type, Reason and
 * Eligible for Hire. The backend resolves the slug → label maps, so this only
 * displays; falls back to the raw slug when a label is absent.
 */

import { Calendar, RefreshCcw, ShieldCheck, UserX } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import type { LucideIcon } from './profile-format';

/** Format an ISO date to a locale-friendly display; falls back to the raw string. */
function formatDate( iso: string ): string {
	const date = new Date( iso );
	if ( Number.isNaN( date.getTime() ) ) {
		return iso;
	}
	return date.toLocaleDateString( undefined, { year: 'numeric', month: 'short', day: 'numeric' } );
}

interface TerminationShape {
	readonly terminate_date?:            string;
	readonly termination_type?:          string;
	readonly termination_type_label?:    string;
	readonly termination_reason?:        string;
	readonly termination_reason_label?:  string;
	readonly eligible_for_rehire?:       string;
	readonly eligible_for_rehire_label?: string;
}

function asTermination( value: unknown ): TerminationShape | null {
	if ( value === null || typeof value !== 'object' ) {
		return null;
	}
	return value as TerminationShape;
}

function Row( { icon: Icon, label, value }: { readonly icon: LucideIcon; readonly label: string; readonly value: string } ): JSX.Element {
	return (
		<div className="flex items-start gap-2.5">
			<span className="mt-0.5 inline-flex size-7 shrink-0 items-center justify-center rounded-md bg-destructive/10 text-destructive">
				<Icon size={ 14 } aria-hidden="true" />
			</span>
			<div className="flex min-w-0 flex-col gap-0.5">
				<dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">{ label }</dt>
				<dd className="text-sm text-foreground">{ value.trim() === '' ? '—' : value }</dd>
			</div>
		</div>
	);
}

export function TerminationCard( { termination }: { readonly termination: unknown } ): JSX.Element | null {
	const data = asTermination( termination );
	if ( ! data ) {
		return null;
	}

	const dateRaw = ( data.terminate_date ?? '' ).trim();
	const date    = dateRaw ? formatDate( dateRaw ) : '';
	const type    = ( data.termination_type_label ?? '' ).trim() || ( data.termination_type ?? '' );
	const reason  = ( data.termination_reason_label ?? '' ).trim() || ( data.termination_reason ?? '' );
	const rehire  = ( data.eligible_for_rehire_label ?? '' ).trim() || ( data.eligible_for_rehire ?? '' );

	return (
		<section className="rounded-[10px] border border-destructive/30 bg-card p-6 shadow-sm">
			<h2 className="mt-0 flex items-center gap-2 text-2xl font-bold leading-tight tracking-tight text-foreground">
				<UserX size={ 20 } className="text-destructive" aria-hidden="true" />
				{ __( 'Termination', 'erp' ) }
			</h2>
			<div className="mb-4 mt-4 h-px w-full bg-border" />
			<dl className="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2 lg:grid-cols-3">
				<Row icon={ Calendar } label={ __( 'Termination Date', 'erp' ) } value={ date } />
				<Row icon={ UserX } label={ __( 'Termination Type', 'erp' ) } value={ type } />
				<Row icon={ RefreshCcw } label={ __( 'Termination Reason', 'erp' ) } value={ reason } />
				<Row icon={ ShieldCheck } label={ __( 'Eligible for Hire', 'erp' ) } value={ rehire } />
			</dl>
		</section>
	);
}
