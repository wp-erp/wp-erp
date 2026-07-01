/**
 * Pure CSV helpers for the Unpaid Leaves export — quote a cell and trigger a
 * client-side download. No React/state.
 */

/** Quote a CSV cell. */
export function csvCell( value: unknown ): string {
	return `"${ String( value ).replace( /"/g, '""' ) }"`;
}

/** Trigger a client-side CSV download. */
export function downloadCsv( content: string, filename: string ): void {
	const blob = new Blob( [ content ], { type: 'text/csv;charset=utf-8;' } );
	const url  = URL.createObjectURL( blob );
	const a    = document.createElement( 'a' );
	a.href     = url;
	a.download = filename;
	document.body.appendChild( a );
	a.click();
	document.body.removeChild( a );
	URL.revokeObjectURL( url );
}
