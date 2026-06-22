/**
 * Upload + preview view for the employee CSV import dialog: the drag/drop
 * dropzone (with sample-CSV download), the parse-error alert, the detected
 * rows/columns preview, and the import/cancel footer. Presentational — the
 * dialog owns parsing state and the import action.
 */

import {
	Alert,
	AlertDescription,
	Button,
	DialogFooter,
	cn,
} from '@wedevs/plugin-ui';
import { CloudUpload, Download, Upload } from 'lucide-react';
import type { ChangeEvent, DragEvent, JSX, RefObject } from 'react';

import { __, sprintf } from '@/shared/i18n';

import { downloadCsv, importTemplateCsv } from './useEmployeeImportExport';
import type { Parsed } from './employee-import-helpers';

interface EmployeeImportUploadProps {
	readonly fileRef:     RefObject< HTMLInputElement | null >;
	readonly fileName:    string;
	readonly dragging:    boolean;
	readonly setDragging: ( value: boolean ) => void;
	readonly onDrop:      ( e: DragEvent< HTMLDivElement > ) => void;
	readonly onFile:      ( e: ChangeEvent< HTMLInputElement > ) => void;
	readonly parseError:  string | null;
	readonly parsed:      Parsed | null;
	readonly submitting:  boolean;
	readonly canImport:   boolean;
	readonly onCancel:    () => void;
	readonly onImport:    () => void;
}

export function EmployeeImportUpload( {
	fileRef,
	fileName,
	dragging,
	setDragging,
	onDrop,
	onFile,
	parseError,
	parsed,
	submitting,
	canImport,
	onCancel,
	onImport,
}: EmployeeImportUploadProps ): JSX.Element {
	return (
		<div className="space-y-4">
			<div
				onDragOver={ ( e ) => {
					e.preventDefault();
					setDragging( true );
				} }
				onDragLeave={ () => setDragging( false ) }
				onDrop={ onDrop }
				className={ cn(
					'flex flex-col items-center justify-center gap-3 rounded-2xl border border-dashed px-6 py-12 text-center transition-colors',
					dragging ? 'border-primary bg-primary/5' : 'border-border bg-muted/30'
				) }
			>
				<CloudUpload size={ 36 } strokeWidth={ 1.5 } className="text-muted-foreground" aria-hidden="true" />
				<p className="m-0 text-sm font-semibold text-foreground">
					{ fileName || __( 'Upload a CSV file', 'erp' ) }
				</p>
				<p className="m-0 text-xs text-muted-foreground">
					{ __( 'Drag and drop CSV file here', 'erp' ) }
				</p>
				<Button
					type="button"
					variant="outline"
					className="mt-1 h-9 gap-1.5 text-primary"
					onClick={ () => fileRef.current?.click() }
				>
					<Upload size={ 15 } aria-hidden="true" />
					{ __( 'Choose File', 'erp' ) }
				</Button>
				<input
					ref={ fileRef }
					type="file"
					accept=".csv,text/csv"
					className="sr-only"
					onChange={ onFile }
				/>
				<button
					type="button"
					onClick={ () => downloadCsv( 'employees-template.csv', importTemplateCsv() ) }
					className="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-muted-foreground hover:text-foreground"
				>
					<Download size={ 13 } aria-hidden="true" />
					{ __( 'Download Sample CSV', 'erp' ) }
				</button>
			</div>

			{ parseError ? (
				<Alert variant="destructive">
					<AlertDescription>{ parseError }</AlertDescription>
				</Alert>
			) : null }

			{ parsed ? (
				<div className="space-y-2 rounded-lg border border-border bg-card p-4 text-sm">
					<p className="m-0 font-medium text-foreground">
						{ sprintf(
							/* translators: 1: row count, 2: recognised column count */
							__( '%1$d rows · %2$d recognised columns', 'erp' ),
							parsed.rows.length,
							parsed.headers.length
						) }
					</p>
					{ parsed.headers.length > 0 ? (
						<div className="flex flex-wrap gap-1.5">
							{ parsed.headers.map( ( h ) => (
								<span
									key={ h }
									className="rounded bg-muted px-2 py-0.5 text-xs text-muted-foreground"
								>
									{ h }
								</span>
							) ) }
						</div>
					) : null }
					{ parsed.missing.length > 0 ? (
						<Alert variant="destructive">
							<AlertDescription>
								{ sprintf(
									/* translators: %s: comma-separated column names */
									__( 'Missing required columns: %s', 'erp' ),
									parsed.missing.join( ', ' )
								) }
							</AlertDescription>
						</Alert>
					) : null }
					{ parsed.unknownHeaders.length > 0 ? (
						<p className="m-0 text-xs text-muted-foreground">
							{ sprintf(
								/* translators: %s: comma-separated column names */
								__( 'Ignored columns: %s', 'erp' ),
								parsed.unknownHeaders.join( ', ' )
							) }
						</p>
					) : null }
				</div>
			) : null }

			<DialogFooter className="gap-5 sm:gap-5">
				<Button type="button" variant="outline" className="h-10 px-6" disabled={ submitting } onClick={ onCancel }>
					{ __( 'Cancel', 'erp' ) }
				</Button>
				<Button type="button" className="h-10 gap-1.5 px-6" disabled={ ! canImport } onClick={ onImport }>
					<Upload size={ 15 } aria-hidden="true" />
					{ submitting ? __( 'Importing…', 'erp' ) : __( 'Import', 'erp' ) }
				</Button>
			</DialogFooter>
		</div>
	);
}
