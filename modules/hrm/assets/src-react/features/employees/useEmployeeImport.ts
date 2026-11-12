/**
 * State and actions of the employee CSV import: pick or drop a CSV, parse it in
 * the browser, match each column to an ERP field (auto-detected, editable),
 * POST the rows to `erp/v2/employees/import`, keep the result.
 *
 * Shared by the Employees page's import dialog and the setup wizard's Add Team
 * step, so both import exactly the same way. On any created row the employee
 * list + counts are invalidated so the table refreshes.
 */

import { toast } from '@wedevs/plugin-ui';
import { useDispatch } from '@wordpress/data';
import { useMemo, useRef, useState } from 'react';
import type { ChangeEvent, DragEvent, RefObject } from 'react';

import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';

import { REQUIRED } from './employee-import-helpers';
import {
	autoMatchField,
	buildRowsFromMapping,
	importEmployees,
	IMPORT_FIELD_OPTIONS,
	parseCsv,
} from './useEmployeeImportExport';
import type { ImportResult } from './useEmployeeImportExport';

/** Human label for an ERP field key (for the required-field notice). */
function fieldLabel( key: string ): string {
	return IMPORT_FIELD_OPTIONS.find( ( o ) => o.value === key )?.label ?? key;
}

export interface EmployeeImportFlow {
	readonly fileRef:         RefObject< HTMLInputElement | null >;
	readonly fileName:        string;
	readonly dragging:        boolean;
	readonly setDragging:     ( value: boolean ) => void;
	readonly headers:         string[];
	readonly mapping:         string[];
	readonly hasMatrix:       boolean;
	readonly parseError:      string | null;
	readonly submitting:      boolean;
	readonly result:          ImportResult | null;
	readonly requiredMissing: { key: string; label: string }[];
	readonly canImport:       boolean;
	readonly reset:           () => void;
	readonly onFile:          ( e: ChangeEvent< HTMLInputElement > ) => void;
	readonly onDrop:          ( e: DragEvent< HTMLDivElement > ) => void;
	readonly setColumn:       ( index: number, value: string ) => void;
	readonly runImport:       () => void;
}

export function useEmployeeImport(): EmployeeImportFlow {
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

	function runImport(): void {
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

	return {
		fileRef,
		fileName,
		dragging,
		setDragging,
		headers,
		mapping,
		hasMatrix: !! matrix,
		parseError,
		submitting,
		result,
		requiredMissing,
		canImport,
		reset,
		onFile,
		onDrop,
		setColumn,
		runImport,
	};
}
