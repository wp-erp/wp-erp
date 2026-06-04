/**
 * Employee CSV import dialog.
 *
 * Pick/drop a CSV → parse client-side → preview the detected rows/columns →
 * POST to the bulk-import endpoint → show a success screen (with a per-row
 * failure table when needed). On any success the employee list + counts are
 * invalidated so the table refreshes.
 *
 * The visual language follows the HRM 2024 redesign (upload dropzone,
 * celebratory success state); the parsing/import logic is unchanged.
 */

import {
	Alert,
	AlertDescription,
	Button,
	Dialog,
	DialogContent,
	DialogFooter,
	DialogHeader,
	DialogTitle,
	cn,
	toast,
} from '@wedevs/plugin-ui';
import { useDispatch } from '@wordpress/data';
import { CloudUpload, Download, Upload } from 'lucide-react';
import { useRef, useState } from 'react';
import type { ChangeEvent, DragEvent, JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';

import {
	downloadCsv,
	importEmployees,
	importTemplateCsv,
	parseCsv,
	rowsToEmployees,
} from './useEmployeeImportExport';
import type { ImportResult, ImportRow } from './useEmployeeImportExport';

interface EmployeeImportDialogProps {
	readonly open:    boolean;
	readonly onClose: () => void;
}

const REQUIRED = [ 'first_name', 'last_name', 'email' ];

interface Parsed {
	readonly rows:           ImportRow[];
	readonly headers:        string[];
	readonly unknownHeaders: string[];
	readonly missing:        string[];
}

export function EmployeeImportDialog( { open, onClose }: EmployeeImportDialogProps ): JSX.Element {
	const { invalidate } = useDispatch( employeesStoreName ) as unknown as { invalidate: () => void };
	const fileRef = useRef< HTMLInputElement >( null );

	const [ fileName, setFileName ]   = useState( '' );
	const [ dragging, setDragging ]   = useState( false );
	const [ parsed, setParsed ]       = useState< Parsed | null >( null );
	const [ parseError, setParseError ] = useState< string | null >( null );
	const [ submitting, setSubmitting ] = useState( false );
	const [ result, setResult ]       = useState< ImportResult | null >( null );

	function reset(): void {
		setFileName( '' );
		setDragging( false );
		setParsed( null );
		setParseError( null );
		setSubmitting( false );
		setResult( null );
		if ( fileRef.current ) {
			fileRef.current.value = '';
		}
	}

	function close(): void {
		reset();
		onClose();
	}

	function handleFile( file: File | undefined ): void {
		setParsed( null );
		setParseError( null );
		setResult( null );
		if ( ! file ) {
			setFileName( '' );
			return;
		}
		setFileName( file.name );

		const reader = new FileReader();
		reader.onload = () => {
			try {
				const text = String( reader.result ?? '' );
				const { rows, headers, unknownHeaders } = rowsToEmployees( parseCsv( text ) );
				const missing = REQUIRED.filter( ( c ) => ! headers.includes( c ) );
				if ( rows.length === 0 ) {
					setParseError( __( 'No data rows found in the file.', 'erp' ) );
					return;
				}
				setParsed( { rows, headers, unknownHeaders, missing } );
			} catch {
				setParseError( __( 'Could not read this file. Make sure it is a valid CSV.', 'erp' ) );
			}
		};
		reader.onerror = () => setParseError( __( 'Could not read this file.', 'erp' ) );
		reader.readAsText( file );
	}

	function onFile( e: ChangeEvent< HTMLInputElement > ): void {
		handleFile( e.target.files?.[ 0 ] );
	}

	function onDrop( e: DragEvent< HTMLDivElement > ): void {
		e.preventDefault();
		setDragging( false );
		handleFile( e.dataTransfer.files?.[ 0 ] );
	}

	function handleImport(): void {
		if ( ! parsed || parsed.missing.length > 0 ) {
			return;
		}
		setSubmitting( true );
		importEmployees( parsed.rows )
			.then( ( res ) => {
				setResult( res );
				if ( res.created > 0 ) {
					invalidate();
				}
			} )
			.catch( () => toast.error( __( 'Import failed. Please try again.', 'erp' ) ) )
			.finally( () => setSubmitting( false ) );
	}

	const canImport = !! parsed && parsed.missing.length === 0 && ! submitting;

	return (
		<Dialog open={ open } onOpenChange={ ( next ) => ( next ? undefined : close() ) }>
			<DialogContent className="max-h-[90vh] gap-4 overflow-y-auto rounded-[10px] p-6 sm:max-w-lg">
				<DialogHeader className="space-y-1.5 text-center sm:text-center">
					<DialogTitle className="m-0 text-center text-2xl font-bold leading-tight tracking-tight text-foreground">
						{ __( 'Import Employee', 'erp' ) }
					</DialogTitle>
					<p className="mx-auto max-w-md text-sm text-muted-foreground">
						{ __( 'Add employees to your WP ERP by uploading a CSV file.', 'erp' ) }
					</p>
				</DialogHeader>

				{ renderImport() }
			</DialogContent>
		</Dialog>
	);

	// ── Import ────────────────────────────────────────────────────────────────
	function renderImport(): JSX.Element {
		if ( result ) {
			return (
				<div className="space-y-4">
					<div className="flex flex-col items-center justify-center gap-3 rounded-xl border border-border bg-card px-6 py-10 text-center">
						<span className="text-4xl" aria-hidden="true">🎉</span>
						<div className="space-y-1">
							<h3 className="m-0 text-lg font-semibold text-foreground">
								{ result.failed.length > 0
									? __( 'Import finished', 'erp' )
									: __( 'Successfully Imported', 'erp' ) }
							</h3>
							<p className="text-sm text-muted-foreground">
								{ sprintf(
									/* translators: 1: created count, 2: total count */
									__( '%1$d of %2$d employees have been imported', 'erp' ),
									result.created,
									result.total
								) }
							</p>
						</div>
					</div>

					{ result.failed.length > 0 ? (
						<div className="max-h-56 overflow-auto rounded-md border border-border">
							<table className="w-full min-w-[22rem] text-left text-sm">
								<thead className="bg-muted/50 text-xs text-muted-foreground">
									<tr>
										<th className="px-3 py-2">{ __( 'Row', 'erp' ) }</th>
										<th className="px-3 py-2">{ __( 'Email', 'erp' ) }</th>
										<th className="px-3 py-2">{ __( 'Error', 'erp' ) }</th>
									</tr>
								</thead>
								<tbody>
									{ result.failed.map( ( f ) => (
										<tr key={ `${ f.row }-${ f.email }` } className="border-t border-border">
											<td className="px-3 py-2 tabular-nums">{ f.row }</td>
											<td className="px-3 py-2">{ f.email }</td>
											<td className="px-3 py-2 text-destructive">{ f.message }</td>
										</tr>
									) ) }
								</tbody>
							</table>
						</div>
					) : null }

					<DialogFooter className="gap-5 sm:gap-5">
						<Button type="button" variant="outline" className="h-10 px-6" onClick={ reset }>
							{ __( 'Import another file', 'erp' ) }
						</Button>
						<Button type="button" className="h-10 px-6" onClick={ close }>
							{ __( 'Done', 'erp' ) }
						</Button>
					</DialogFooter>
				</div>
			);
		}

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
						'flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed px-4 py-10 text-center transition-colors',
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
					<Button type="button" variant="outline" className="h-10 px-6" disabled={ submitting } onClick={ close }>
						{ __( 'Cancel', 'erp' ) }
					</Button>
					<Button type="button" className="h-10 gap-1.5 px-6" disabled={ ! canImport } onClick={ handleImport }>
						<Upload size={ 15 } aria-hidden="true" />
						{ submitting ? __( 'Importing…', 'erp' ) : __( 'Import', 'erp' ) }
					</Button>
				</DialogFooter>
			</div>
		);
	}
}
