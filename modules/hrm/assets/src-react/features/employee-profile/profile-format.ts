/**
 * Pure presentation helpers shared across the Employee Profile chrome (header
 * card, overview cards, tabs). No component state — plain functions and the two
 * record/icon types the pieces pass around.
 */

import type { ComponentType, SVGProps } from 'react';

import type { Option } from '../employee-create/options';

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
