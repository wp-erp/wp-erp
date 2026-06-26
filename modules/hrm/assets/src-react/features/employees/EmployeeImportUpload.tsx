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
import type { ChangeEvent, DragEvent, JSX, ReactNode, RefObject } from 'react';

import { __ } from '@/shared/i18n';

import { downloadCsv, importTemplateCsv } from './useEmployeeImportExport';

interface EmployeeImportUploadProps {
	readonly fileRef:     RefObject< HTMLInputElement | null >;
	readonly fileName:    string;
	readonly dragging:    boolean;
	readonly setDragging: ( value: boolean ) => void;
	readonly onDrop:      ( e: DragEvent< HTMLDivElement > ) => void;
	readonly onFile:      ( e: ChangeEvent< HTMLInputElement > ) => void;
	readonly parseError:  string | null;
	readonly submitting:  boolean;
	readonly canImport:   boolean;
	readonly onCancel:    () => void;
	readonly onImport:    () => void;
	/** Column-matching step, rendered below the dropzone once a file is parsed. */
	readonly children?:   ReactNode;
}

export function EmployeeImportUpload( {
	fileRef,
	fileName,
	dragging,
	setDragging,
	onDrop,
	onFile,
	parseError,
	submitting,
	canImport,
	onCancel,
	onImport,
	children,
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

			{ children }

			<DialogFooter className="gap-5 sm:gap-5">
				<Button type="button" variant="outline" className="h-10 px-6" disabled={ submitting } onClick={ onCancel }>
					{ __( 'Cancel', 'erp' ) }
				</Button>
				<Button type="button" className="h-10 gap-1.5 px-6" disabled={ ! canImport } onClick={ onImport }>
					<Upload size={ 15 } aria-hidden="true" />
					{ submitting ? __( 'Importing…', 'erp' ) : __( 'Import Employee', 'erp' ) }
				</Button>
			</DialogFooter>
		</div>
	);
}
