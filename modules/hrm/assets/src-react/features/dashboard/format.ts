/**
 * Small presentation helpers shared across the dashboard widgets: the greeting,
 * date formatters and avatar initials. Pure functions — no component state.
 */

import type { ComponentType, SVGProps } from 'react';

import { __ } from '@/shared/i18n';
import { parseServerDate } from '@/shared/utils/date';

export type LucideIcon = ComponentType<
	SVGProps< SVGSVGElement > & { size?: number; strokeWidth?: number }
>;

/** Time-of-day greeting ("Good morning" / "afternoon" / "evening"). */
export function greeting(): string {
	const h = new Date().getHours();
	if ( h < 12 ) {
		return __( 'Good morning', 'erp' );
	}
	if ( h < 18 ) {
		return __( 'Good afternoon', 'erp' );
	}
	return __( 'Good evening', 'erp' );
}

/** Short "Mon D" date label; "—" when empty, raw slice when unparseable. */
export function fmtDate( value: string | null ): string {
	const d = parseServerDate( value );
	if ( ! d ) {
		return value ? value.slice( 0, 10 ) : '—';
	}
	return d.toLocaleDateString( undefined, {
		month: 'short',
		day: 'numeric',
	} );
}

/** Long "Month D" date label (used for birthdays). */
export function fmtDayMonth( value: string | null ): string {
	const d = parseServerDate( value );
	if ( ! d ) {
		return value ? value.slice( 5, 10 ) : '—';
	}
	return d.toLocaleDateString( undefined, { month: 'long', day: 'numeric' } );
}

/** Up-to-two-letter initials for an avatar fallback. */
export function initials( name: string ): string {
	const parts = name.trim().split( /\s+/ ).filter( Boolean );
	if ( parts.length === 0 ) {
		return '?';
	}
	const first = parts[ 0 ]?.[ 0 ] ?? '';
	const last = parts.length > 1 ? parts[ parts.length - 1 ]?.[ 0 ] ?? '' : '';
	return ( first + last ).toUpperCase();
}
