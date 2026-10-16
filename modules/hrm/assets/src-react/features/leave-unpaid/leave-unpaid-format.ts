/**
 * Pure CSV helpers for the Unpaid Leaves export — quote a cell and trigger a
 * client-side download. No React/state.
 */

import { csvCell as cell } from '@/shared/utils/csv';

/** Quote a CSV cell (formula-safe, see `shared/utils/csv`). */
export function csvCell( value: unknown ): string {
	return cell( value, true );
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
