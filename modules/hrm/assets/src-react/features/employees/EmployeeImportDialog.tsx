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
	Dialog,
	DialogContent,
	DialogHeader,
	DialogTitle,
	toast,
} from '@wedevs/plugin-ui';
import { useDispatch } from '@wordpress/data';
import { useRef, useState } from 'react';
import type { ChangeEvent, DragEvent, JSX } from 'react';

import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';

import { EmployeeImportResult } from './EmployeeImportResult';
import { EmployeeImportUpload } from './EmployeeImportUpload';
import { REQUIRED } from './employee-import-helpers';
import type { Parsed } from './employee-import-helpers';
import {
	importEmployees,
	parseCsv,
	rowsToEmployees,
} from './useEmployeeImportExport';
import type { ImportResult } from './useEmployeeImportExport';

interface EmployeeImportDialogProps {
	readonly open:    boolean;
	readonly onClose: () => void;
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

				{ result ? (
					<EmployeeImportResult result={ result } onReset={ reset } onClose={ close } />
				) : (
					<EmployeeImportUpload
						fileRef={ fileRef }
						fileName={ fileName }
						dragging={ dragging }
						setDragging={ setDragging }
						onDrop={ onDrop }
						onFile={ onFile }
						parseError={ parseError }
						parsed={ parsed }
						submitting={ submitting }
						canImport={ canImport }
						onCancel={ close }
						onImport={ handleImport }
					/>
				) }
			</DialogContent>
		</Dialog>
	);
}
