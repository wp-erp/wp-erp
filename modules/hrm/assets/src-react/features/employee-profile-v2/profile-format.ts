/**
 * Pure presentation helpers shared across the Employee Profile v2 chrome (left
 * profile card, detail cards, tabs). No component state — plain functions and
 * the two record/icon types the pieces pass around.
 */

import type { ComponentType, SVGProps } from 'react';

import type { Option } from './options';

export type LucideIcon = ComponentType<
	SVGProps< SVGSVGElement > & { size?: number; strokeWidth?: number }
>;

export type Record_ = Record< string, unknown >;

/** Stringify a record field, treating null/undefined as empty. */
export function str( record: Record_, key: string ): string {
	const value = record[ key ];
	return value === null || value === undefined ? '' : String( value );
}

/** Resolve an option value to its display label (falls back to the raw value). */
export function labelOf( options: readonly Option[], value: string ): string {
	return options.find( ( o ) => o.value === value )?.label ?? value;
}

/** First + last initials from a full name. */
export function initials( name: string ): string {
	const parts = name.trim().split( /\s+/ ).filter( Boolean );
	if ( parts.length === 0 ) {
		return '?';
	}
	const first = parts[ 0 ]?.[ 0 ] ?? '';
	const last  = parts.length > 1 ? parts[ parts.length - 1 ]?.[ 0 ] ?? '' : '';
	return ( first + last ).toUpperCase();
}

/** Status → Badge tone. */
export function statusVariant( status: string ): 'success' | 'secondary' | 'destructive' {
	switch ( status ) {
		case 'active':
			return 'success';
		case 'terminated':
		case 'deceased':
			return 'destructive';
		default:
			return 'secondary';
	}
}

/** Whole-year age from a YYYY-MM-DD birth date, or '' when unknown. */
export function ageFrom( dob: string ): string {
	const v = ( dob ?? '' ).trim();
	if ( ! v || v.startsWith( '0000' ) ) {
		return '';
	}
	const d = new Date( v );
	if ( Number.isNaN( d.getTime() ) ) {
		return '';
	}
	const now = new Date();
	let years = now.getFullYear() - d.getFullYear();
	const m = now.getMonth() - d.getMonth();
	if ( m < 0 || ( m === 0 && now.getDate() < d.getDate() ) ) {
		years -= 1;
	}
	return years >= 0 ? String( years ) : '';
}
