/**
 * Import-holidays dialog.
 *
 * Two-step flow mirroring the legacy importer: (1) pick a `.ics` / `.csv` file
 * → `POST /holidays/parse` returns current-year preview rows (duplicates already
 * skipped server-side); (2) review + uncheck any rows, then `POST
 * /holidays/import` bulk-inserts the selected ones.
 */

import {
	Alert,
	AlertDescription,
	Button,
	Checkbox,
	Dialog,
	DialogContent,
	DialogDescription,
	DialogFooter,
	DialogHeader,
	DialogTitle,
} from '@wedevs/plugin-ui';
import { Upload } from 'lucide-react';
import { useState } from 'react';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';

import type { HolidayImportResult, HolidayPreviewRow } from './types';

interface HolidayImportDialogProps {
	readonly open:      boolean;
	readonly onClose:   () => void;
	readonly onParse:   ( file: File ) => Promise< readonly HolidayPreviewRow[] >;
	readonly onImport:  ( rows: readonly HolidayPreviewRow[] ) => Promise< HolidayImportResult >;
}

export function HolidayImportDialog( {
	open,
	onClose,
	onParse,
	onImport,
}: HolidayImportDialogProps ): JSX.Element {
	const [ rows, setRows ]       = useState< readonly HolidayPreviewRow[] >( [] );
	const [ checked, setChecked ] = useState< Set< number > >( new Set() );
	const [ parsing, setParsing ] = useState( false );
	const [ importing, setImporting ] = useState( false );
	const [ error, setError ]     = useState< string | null >( null );
	const [ parsed, setParsed ]   = useState( false );

	function reset(): void {
		setRows( [] );
		setChecked( new Set() );
		setParsing( false );
		setImporting( false );
		setError( null );
		setParsed( false );
	}

	function handleClose(): void {
		reset();
		onClose();
	}

	async function handleFile( e: React.ChangeEvent< HTMLInputElement > ): Promise< void > {
		const file = e.target.files?.[ 0 ];
		if ( ! file ) {
			return;
		}
		setParsing( true );
		setError( null );
		try {
			const preview = await onParse( file );
			setRows( preview );
			setChecked( new Set( preview.map( ( _, i ) => i ) ) );
			setParsed( true );
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? __( 'Could not read the file.', 'erp' ) );
		} finally {
			setParsing( false );
			e.target.value = '';
		}
	}

	function toggle( index: number ): void {
		setChecked( ( prev ) => {
			const next = new Set( prev );
			if ( next.has( index ) ) {
				next.delete( index );
			} else {
				next.add( index );
			}
			return next;
		} );
	}

	async function handleImport(): Promise< void > {
		const selected = rows.filter( ( _, i ) => checked.has( i ) );
		if ( selected.length === 0 ) {
			return;
		}
		setImporting( true );
		setError( null );
		try {
			await onImport( selected );
			handleClose();
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? __( 'Import failed.', 'erp' ) );
		} finally {
			setImporting( false );
		}
	}

	const busy = parsing || importing;

	return (
		<Dialog open={ open } onOpenChange={ ( next ) => ( next || busy ? undefined : handleClose() ) }>
			<DialogContent className="gap-4 rounded-[10px] p-6 sm:max-w-2xl">
				<DialogHeader>
					<DialogTitle className="m-0 text-2xl font-bold leading-tight tracking-tight text-foreground">
						{ __( 'Import Holidays', 'erp' ) }
					</DialogTitle>
					<DialogDescription>
						{ __( 'Upload an iCal (.ics) or CSV file. Only this year’s entries are imported; duplicates are skipped.', 'erp' ) }
					</DialogDescription>
				</DialogHeader>
				<div className="h-px w-full bg-border" />

				{ ! parsed ? (
					<label className="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-border bg-muted/20 px-6 py-10 text-center hover:border-primary">
						<Upload size={ 24 } className="text-muted-foreground" aria-hidden="true" />
						<span className="text-sm font-medium text-foreground">
							{ parsing ? __( 'Reading file…', 'erp' ) : __( 'Choose a .ics or .csv file', 'erp' ) }
						</span>
						<span className="text-xs text-muted-foreground">
							{ __( 'CSV columns: title, start, end, description', 'erp' ) }
						</span>
						<input
							type="file"
							accept=".ics,.csv,text/calendar,text/csv,application/vnd.ms-excel"
							className="sr-only"
							disabled={ parsing }
							onChange={ ( e ) => void handleFile( e ) }
						/>
					</label>
				) : rows.length === 0 ? (
					<p className="p-6 text-center text-sm text-muted-foreground">
						{ __( 'No new holidays found in that file.', 'erp' ) }
					</p>
				) : (
					<div className="max-h-80 overflow-auto rounded-lg border border-border">
						<table className="w-full text-left text-sm">
							<thead className="sticky top-0 border-b border-border bg-card">
								<tr className="h-10 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">
									<th scope="col" className="w-10 px-3" />
									<th scope="col" className="px-3">{ __( 'Title', 'erp' ) }</th>
									<th scope="col" className="px-3">{ __( 'Start', 'erp' ) }</th>
									<th scope="col" className="px-3">{ __( 'End', 'erp' ) }</th>
								</tr>
							</thead>
							<tbody>
								{ rows.map( ( row, i ) => (
									<tr key={ `${ row.title }-${ row.start }-${ i }` } className="h-11 border-b border-border last:border-b-0">
										<td className="px-3 align-middle">
											<Checkbox checked={ checked.has( i ) } onCheckedChange={ () => toggle( i ) } />
										</td>
										<td className="px-3 align-middle font-medium text-foreground">{ row.title }</td>
										<td className="px-3 align-middle text-muted-foreground">{ row.start.slice( 0, 10 ) }</td>
										<td className="px-3 align-middle text-muted-foreground">{ row.end.slice( 0, 10 ) }</td>
									</tr>
								) ) }
							</tbody>
						</table>
					</div>
				) }

				{ error ? (
					<Alert variant="destructive">
						<AlertDescription>{ error }</AlertDescription>
					</Alert>
				) : null }

				<DialogFooter className="gap-5 sm:gap-5">
					<Button type="button" variant="outline" className="h-10 px-6" disabled={ busy } onClick={ handleClose }>
						{ __( 'Cancel', 'erp' ) }
					</Button>
					{ parsed && rows.length > 0 ? (
						<Button type="button" className="h-10 px-6" disabled={ busy || checked.size === 0 } onClick={ () => void handleImport() }>
							{ importing
								? __( 'Importing…', 'erp' )
								: sprintf( __( 'Import %d holidays', 'erp' ), checked.size ) }
						</Button>
					) : null }
				</DialogFooter>
			</DialogContent>
		</Dialog>
	);
}
