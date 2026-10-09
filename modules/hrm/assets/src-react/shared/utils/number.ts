/**
 * Locale-aware number formatting.
 *
 * Mirrors the legacy PHP helper `erp_number_format_i18n()`: a whole number is
 * rendered with no decimals, a fractional number with a single decimal place,
 * both with locale thousands/decimal separators (via `Intl.NumberFormat`).
 */

/**
 * Format a numeric value like `erp_number_format_i18n()`.
 *
 * @param value Number (or numeric string) to format.
 *
 * @return Localised string (e.g. `1,234` or `1,234.5`).
 */
export function formatNumberI18n( value: number | string ): string {
	const num = typeof value === 'number' ? value : Number( value );

	if ( ! Number.isFinite( num ) ) {
		return String( value );
	}

	// Non-zero fractional part → one decimal; otherwise integer formatting.
	const hasFraction = Math.abs( num % 1 ) > 0;
	const fractionDigits = hasFraction ? 1 : 0;

	return new Intl.NumberFormat( undefined, {
		minimumFractionDigits: fractionDigits,
		maximumFractionDigits: fractionDigits,
	} ).format( num );
}
