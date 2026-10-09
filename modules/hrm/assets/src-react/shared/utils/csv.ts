/**
 * One CSV cell for the HR exports.
 *
 * Quoted when it holds a comma, quote or line break, and a text value that
 * starts like a spreadsheet formula (`= + - @`, tab, CR) gets a leading `'`
 * so Excel shows it as text instead of running it. Names come from the
 * user's own profile, so an export must not execute what they typed.
 *
 * Numbers and phone numbers (`-12.5`, `+880 1711-000000`) are left alone:
 * they cannot run as formulas, and the employee export is also the import
 * format, so a prefix would change them on the way back in.
 *
 * @param value  Cell value.
 * @param always Quote even when nothing needs quoting.
 */
export function csvCell( value: unknown, always = false ): string {
	let text = null === value || undefined === value ? '' : String( value );

	if ( /^[=+\-@\t\r]/.test( text ) && ! /^[+-]?[\d\s().-]+$/.test( text ) ) {
		text = `'${ text }`;
	}

	if ( always || /[",\n\r]/.test( text ) ) {
		return `"${ text.replace( /"/g, '""' ) }"`;
	}

	return text;
}
