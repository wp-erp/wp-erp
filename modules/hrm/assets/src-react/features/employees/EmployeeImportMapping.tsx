/**
 * Column-matching step of the employee CSV import.
 *
 * After a file is parsed, each detected CSV column is shown with a select to map
 * it onto an ERP employee field (auto-matched by header name, editable). Columns
 * left as "Don't import" are skipped. Required fields that aren't mapped block
 * the import — mirroring the legacy import tool's field-mapping table.
 */

import { Alert, AlertDescription } from '@wedevs/plugin-ui';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';

import { IMPORT_FIELD_OPTIONS } from './useEmployeeImportExport';

interface EmployeeImportMappingProps {
	readonly headers:         string[];
	/** First data row, shown as a sample value per column. */
	readonly sampleRow:       string[];
	/** `mapping[i]` = ERP field key for column `i`, or `''` to skip. */
	readonly mapping:         string[];
	readonly onChange:        ( index: number, value: string ) => void;
	/** ERP field keys that are required but not yet mapped. */
	readonly requiredMissing: { key: string; label: string }[];
}

export function EmployeeImportMapping( {
	headers,
	sampleRow,
	mapping,
	onChange,
	requiredMissing,
}: EmployeeImportMappingProps ): JSX.Element {
	// A field already mapped to another column shouldn't be selectable twice.
	const usedElsewhere = ( index: number ): Set< string > =>
		new Set( mapping.filter( ( f, i ) => f !== '' && i !== index ) );

	return (
		<div className="space-y-3 rounded-lg border border-border bg-card p-4">
			<div className="flex items-center justify-between">
				<p className="m-0 text-sm font-semibold text-foreground">{ __( 'Match columns to fields', 'erp' ) }</p>
				<span className="text-xs text-muted-foreground">
					{ sprintf( /* translators: %d: column count */ __( '%d columns', 'erp' ), headers.length ) }
				</span>
			</div>

			{ requiredMissing.length > 0 ? (
				<Alert variant="destructive">
					<AlertDescription>
						{ sprintf(
							/* translators: %s: comma-separated field names */
							__( 'Map these required fields to continue: %s', 'erp' ),
							requiredMissing.map( ( f ) => f.label ).join( ', ' )
						) }
					</AlertDescription>
				</Alert>
			) : null }

			<div className="max-h-64 space-y-2 overflow-y-auto">
				{ headers.map( ( header, idx ) => {
					const taken = usedElsewhere( idx );
					const sample = ( sampleRow[ idx ] ?? '' ).trim();
					return (
						<div key={ `${ header }-${ idx }` } className="flex items-center gap-3">
							<div className="min-w-0 flex-1">
								<p className="m-0 truncate text-sm font-medium text-foreground">
									{ header || sprintf( __( 'Column %d', 'erp' ), idx + 1 ) }
								</p>
								{ sample ? (
									<p className="m-0 truncate text-xs text-muted-foreground">{ sample }</p>
								) : null }
							</div>
							<span aria-hidden="true" className="text-muted-foreground">→</span>
							<select
								value={ mapping[ idx ] ?? '' }
								onChange={ ( e ) => onChange( idx, e.target.value ) }
								aria-label={ sprintf( __( 'Map column %s', 'erp' ), header || String( idx + 1 ) ) }
								className="h-9 w-44 shrink-0 rounded-md border border-border bg-card px-2 text-sm text-foreground"
							>
								<option value="">{ __( '— Don’t import —', 'erp' ) }</option>
								{ IMPORT_FIELD_OPTIONS.map( ( o ) => (
									<option key={ o.value } value={ o.value } disabled={ taken.has( o.value ) }>
										{ o.label }
									</option>
								) ) }
							</select>
						</div>
					);
				} ) }
			</div>
		</div>
	);
}
