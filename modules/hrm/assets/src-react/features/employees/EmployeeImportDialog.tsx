/**
 * Employee CSV import dialog.
 *
 * Pick/drop a CSV → parse client-side → MATCH each CSV column to an ERP field
 * (auto-detected, editable) → POST to the bulk-import endpoint → show a success
 * screen (with a per-row failure table when needed). On any success the employee
 * list + counts are invalidated so the table refreshes.
 *
 * The visual language follows the HRM 2024 redesign (upload dropzone,
 * column-matching step, celebratory success state).
 */

import {
	Dialog,
	DialogContent,
	DialogHeader,
	DialogTitle,
	toast,
} from '@wedevs/plugin-ui';
import { useDispatch } from '@wordpress/data';
import { useMemo, useRef, useState } from 'react';
import type { ChangeEvent, DragEvent, JSX } from 'react';

import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';

import { EmployeeImportMapping } from './EmployeeImportMapping';
import { EmployeeImportResult } from './EmployeeImportResult';
import { EmployeeImportUpload } from './EmployeeImportUpload';
import { REQUIRED } from './employee-import-helpers';
import {
	autoMatchField,
	buildRowsFromMapping,
	importEmployees,
	IMPORT_FIELD_OPTIONS,
	parseCsv,
} from './useEmployeeImportExport';
import type { ImportResult } from './useEmployeeImportExport';

interface EmployeeImportDialogProps {
	readonly open:    boolean;
	readonly onClose: () => void;
}

/** Human label for an ERP field key (for the required-field notice). */
function fieldLabel( key: string ): string {
	return IMPORT_FIELD_OPTIONS.find( ( o ) => o.value === key )?.label ?? key;
}

export function EmployeeImportDialog( { open, onClose }: EmployeeImportDialogProps ): JSX.Element {
	const { invalidate } = useDispatch( employeesStoreName ) as unknown as { invalidate: () => void };
	const fileRef = useRef< HTMLInputElement >( null );

	const [ fileName, setFileName ]     = useState( '' );
	const [ dragging, setDragging ]     = useState( false );
	const [ matrix, setMatrix ]         = useState< string[][] | null >( null );
	const [ headers, setHeaders ]       = useState< string[] >( [] );
	const [ mapping, setMapping ]       = useState< string[] >( [] );
	const [ parseError, setParseError ] = useState< string | null >( null );
	const [ submitting, setSubmitting ] = useState( false );
	const [ result, setResult ]         = useState< ImportResult | null >( null );

	function reset(): void {
		setFileName( '' );
		setDragging( false );
		setMatrix( null );
		setHeaders( [] );
		setMapping( [] );
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
		setMatrix( null );
		setHeaders( [] );
		setMapping( [] );
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
				const parsed = parseCsv( String( reader.result ?? '' ) );
				const head   = ( parsed[ 0 ] ?? [] ).map( ( h ) => h.trim() );
				const hasData = parsed.slice( 1 ).some( ( row ) => row.some( ( c ) => c.trim() !== '' ) );
				if ( head.length === 0 || ! hasData ) {
					setParseError( __( 'No data rows found in the file.', 'erp' ) );
					return;
				}
				setMatrix( parsed );
				setHeaders( head );
				// Pre-fill the mapping by auto-matching each header to a field.
				setMapping( head.map( ( h ) => autoMatchField( h ) ) );
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

	function setColumn( index: number, value: string ): void {
		setMapping( ( prev ) => prev.map( ( f, i ) => ( i === index ? value : f ) ) );
	}

	// Required ERP fields not yet mapped to any column → block import.
	const requiredMissing = useMemo(
		() => REQUIRED.filter( ( key ) => ! mapping.includes( key ) ).map( ( key ) => ( { key, label: fieldLabel( key ) } ) ),
		[ mapping ]
	);

	const canImport = !! matrix && requiredMissing.length === 0 && ! submitting;

	function handleImport(): void {
		if ( ! matrix || requiredMissing.length > 0 ) {
			return;
		}
		const rows = buildRowsFromMapping( matrix, mapping );
		if ( rows.length === 0 ) {
			setParseError( __( 'No data rows found in the file.', 'erp' ) );
			return;
		}
		setSubmitting( true );
		importEmployees( rows )
			.then( ( res ) => {
				setResult( res );
				if ( res.created > 0 ) {
					invalidate();
				}
			} )
			.catch( () => toast.error( __( 'Import failed. Please try again.', 'erp' ) ) )
			.finally( () => setSubmitting( false ) );
	}

	return (
		<Dialog open={ open } onOpenChange={ ( next ) => ( next ? undefined : close() ) }>
			<DialogContent className="max-h-[90vh] gap-4 overflow-y-auto rounded-[10px] p-6 sm:max-w-lg">
				<DialogHeader className="space-y-1.5 text-center sm:text-center">
					<DialogTitle className="m-0 mb-4 text-center text-2xl font-bold leading-tight tracking-tight text-foreground">
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
						submitting={ submitting }
						canImport={ canImport }
						onCancel={ close }
						onImport={ handleImport }
					>
						{ matrix && headers.length > 0 ? (
							<EmployeeImportMapping
								headers={ headers }
								mapping={ mapping }
								onChange={ setColumn }
								requiredMissing={ requiredMissing }
							/>
						) : null }
					</EmployeeImportUpload>
				) }
			</DialogContent>
		</Dialog>
	);
}
