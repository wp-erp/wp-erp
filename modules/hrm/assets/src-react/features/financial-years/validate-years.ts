/**
 * Client mirror of the financial-year save rules.
 *
 * The first four messages are the legacy `erp_settings_save_leave_years()`
 * strings, word for word; the overlap rule is enforced by
 * `FinancialYearsController::validate_years()` with the same wording. Dates are
 * `Y-m-d` strings, so string comparison is date comparison.
 */

import { __, sprintf } from '@/shared/i18n';

interface YearRow {
	readonly id?:        number | null;
	readonly fy_name:    string;
	readonly start_date: string;
	readonly end_date:   string;
}

/**
 * The first rule the set breaks, as a message, or null when it is valid.
 *
 * The overlap rule checks only new rows and rows whose dates were changed:
 * two saved years that overlap and that nobody touched do not block the save.
 *
 * @param rows  Every financial year that will be saved (the whole set).
 * @param saved The years as they are saved now (to tell untouched rows apart).
 */
export function findYearsError( rows: readonly YearRow[], saved: readonly YearRow[] = [] ): string | null {
	const names: string[] = [];

	for ( const [ i, r ] of rows.entries() ) {
		const name = r.fy_name.trim();
		if ( ! name ) {
			return __( 'Please give a financial year name on row #', 'erp' ) + ( i + 1 );
		}
		if ( ! r.start_date || ! r.end_date ) {
			return __( 'Start and end date are required on row #', 'erp' ) + ( i + 1 );
		}
		if ( r.end_date <= r.start_date ) {
			return __( 'End date must be greater than start date on row #', 'erp' ) + ( i + 1 );
		}
		if ( names.includes( name ) ) {
			return __( 'Duplicate financial year name', 'erp' ) + ' ' + name;
		}
		names.push( name );
	}

	const touched = ( r: YearRow ): boolean => {
		const was = r.id ? saved.find( ( s ) => s.id === r.id ) : undefined;
		return ! was || was.start_date !== r.start_date || was.end_date !== r.end_date;
	};

	for ( let i = 0; i < rows.length; i++ ) {
		for ( let j = i + 1; j < rows.length; j++ ) {
			const a = rows[ i ];
			const b = rows[ j ];
			if ( a && b && ( touched( a ) || touched( b ) ) && a.start_date <= b.end_date && b.start_date <= a.end_date ) {
				return sprintf(
					/* translators: 1: a financial year name, 2: another financial year name. */
					__( 'Financial years %1$s and %2$s overlap. Each date can belong to one financial year only.', 'erp' ),
					a.fy_name.trim(),
					b.fy_name.trim()
				);
			}
		}
	}

	return null;
}
