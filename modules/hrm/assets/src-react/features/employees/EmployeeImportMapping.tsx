/**
 * Column-matching step of the employee CSV import ("Map Properties").
 *
 * After a file is parsed, each detected CSV column is shown with a select to map
 * it onto an ERP employee field (auto-matched by header name, editable). Columns
 * left as "Don't import" are skipped. Required fields that aren't mapped block
 * the import — mirroring the legacy import tool's field-mapping table. Visual
 * design follows the HRM-Redesign-2024 "Map Properties" card.
 */

import { Alert, AlertDescription } from '@wedevs/plugin-ui';
import { ChevronDown } from 'lucide-react';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';

import { REQUIRED } from './employee-import-helpers';
import { IMPORT_FIELD_OPTIONS } from './useEmployeeImportExport';

interface EmployeeImportMappingProps {
	readonly headers:         string[];
	/** `mapping[i]` = ERP field key for column `i`, or `''` to skip. */
	readonly mapping:         string[];
	readonly onChange:        ( index: number, value: string ) => void;
	/** ERP field keys that are required but not yet mapped. */
	readonly requiredMissing: { key: string; label: string }[];
}

const REQUIRED_SET = new Set( REQUIRED );

export function EmployeeImportMapping( {
	headers,
	mapping,
	onChange,
	requiredMissing,
}: EmployeeImportMappingProps ): JSX.Element {
	// A field already mapped to another column shouldn't be selectable twice.
	const usedElsewhere = ( index: number ): Set< string > =>
		new Set( mapping.filter( ( f, i ) => f !== '' && i !== index ) );

	return (
		<div className="overflow-hidden rounded-[10px] border border-border bg-card">
			<h3 className="m-0 border-b border-border py-4 text-center text-lg font-bold text-foreground">
				{ __( 'Map Properties', 'erp' ) }
			</h3>

			{ /* Column headers */ }
			<div className="grid grid-cols-[1fr_1.3fr] gap-4 border-b border-border bg-muted/40 px-6 py-3 text-sm font-medium text-muted-foreground">
				<span>{ sprintf( /* translators: %d: column count */ __( 'Columns (%d)', 'erp' ), headers.length ) }</span>
				<span>{ __( 'Profile Field', 'erp' ) }</span>
			</div>

			{ requiredMissing.length > 0 ? (
				<div className="px-6 pt-3">
					<Alert variant="destructive">
						<AlertDescription>
							{ sprintf(
								/* translators: %s: comma-separated field names */
								__( 'Map these required fields to continue: %s', 'erp' ),
								requiredMissing.map( ( f ) => f.label ).join( ', ' )
							) }
						</AlertDescription>
					</Alert>
				</div>
			) : null }

			<div className="max-h-72 divide-y divide-border overflow-y-auto">
				{ headers.map( ( header, idx ) => {
					const taken    = usedElsewhere( idx );
					const selected = mapping[ idx ] ?? '';
					const required = selected !== '' && REQUIRED_SET.has( selected );
					return (
						<div key={ `${ header }-${ idx }` } className="grid grid-cols-[1fr_1.3fr] items-center gap-4 px-6 py-3">
							<span className="truncate text-sm text-foreground">
								{ header || sprintf( __( 'Column %d', 'erp' ), idx + 1 ) }
								{ required ? <span className="text-destructive">*</span> : null }
							</span>
							<div className="relative">
								<select
									value={ selected }
									onChange={ ( e ) => onChange( idx, e.target.value ) }
									aria-label={ sprintf( __( 'Map column %s', 'erp' ), header || String( idx + 1 ) ) }
									className="h-10 w-full appearance-none rounded-md border border-border bg-card px-3 pr-9 text-sm text-foreground"
								>
									<option value="">{ __( '— Don’t import —', 'erp' ) }</option>
									{ IMPORT_FIELD_OPTIONS.map( ( o ) => (
										<option key={ o.value } value={ o.value } disabled={ taken.has( o.value ) }>
											{ o.label }
										</option>
									) ) }
								</select>
								<ChevronDown
									size={ 16 }
									aria-hidden="true"
									className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground"
								/>
							</div>
						</div>
					);
				} ) }
			</div>
		</div>
	);
}
