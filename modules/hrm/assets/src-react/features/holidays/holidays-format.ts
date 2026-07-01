/**
 * Pure presentation helpers for the Holidays feature. No component state.
 */

/** Format a `YYYY-MM-DD` value for display (locale short date). */
export function fmt( value: string | null ): string {
	if ( ! value ) {
		return '—';
	}
	const d = new Date( value );
	if ( Number.isNaN( d.getTime() ) ) {
		return value.slice( 0, 10 );
	}
	return d.toLocaleDateString( undefined, { year: 'numeric', month: 'short', day: 'numeric' } );
}
