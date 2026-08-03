/**
 * Import-holidays dialog.
 *
 * Two-step flow mirroring the legacy importer: (1) pick a `.ics` / `.csv` file
 * → `POST /holidays/parse` returns current-year preview rows (duplicates already
 * skipped server-side); (2) review + uncheck any rows, then `POST
 * /holidays/import` bulk-inserts the selected ones.
 *
 * The parse step also returns a `message` describing what it skipped. Showing it
 * is the difference between "nothing importable here, and here is why" and the
 * bare "No new holidays found" that used to swallow the reason.
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
	Input,
} from '@wedevs/plugin-ui';
import { Download, Upload } from 'lucide-react';
import { useState } from 'react';
import type { JSX } from 'react';

import { __, _n, sprintf } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';

import type { HolidayImportResult, HolidayParseResult, HolidayPreviewRow } from './types';

interface HolidayImportDialogProps {
	readonly open:      boolean;
	readonly onClose:   () => void;
	readonly onParse:   ( file: File ) => Promise< HolidayParseResult >;
	readonly onImport:  ( rows: readonly HolidayPreviewRow[] ) => Promise< HolidayImportResult >;
}

/**
 * Hand the user a correctly-shaped CSV to edit rather than making them infer the
 * columns from a hint line. Built in the browser (no server round-trip, no bundled
 * asset) and dated to the current year, since the importer only accepts this year.
 */
function downloadSampleCsv(): void {
	const year = new Date().getFullYear();
	const csv  = [
		'title,start,end,description',
		`New Year's Day,${ year }-01-01,${ year }-01-01,Single-day holiday`,
		`Winter Break,${ year }-12-24,${ year }-12-26,Multi-day holiday (inclusive)`,
	].join( '\n' );

	const url  = URL.createObjectURL( new Blob( [ csv ], { type: 'text/csv;charset=utf-8' } ) );
	const link = document.createElement( 'a' );
	link.href     = url;
	link.download = `erp-holidays-sample-${ year }.csv`;
	document.body.appendChild( link );
	link.click();
	link.remove();
	URL.revokeObjectURL( url );
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
	const [ notice, setNotice ]   = useState( '' );
	const [ parsed, setParsed ]   = useState( false );

	function reset(): void {
		setRows( [] );
		setChecked( new Set() );
		setParsing( false );
		setImporting( false );
		setError( null );
		setNotice( '' );
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
		setNotice( '' );
		try {
			const preview = await onParse( file );
			setRows( preview.rows );
			setChecked( new Set( preview.rows.map( ( _, i ) => i ) ) );
			setNotice( preview.message );
			setParsed( true );
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? __( 'Could not read the file.', 'erp' ) );
		} finally {
			setParsing( false );
			e.target.value = '';
		}
	}

	/** Edit one preview row in place; the import sends whatever is on screen. */
	function patchRow( index: number, patch: Partial< HolidayPreviewRow > ): void {
		setRows( ( prev ) => prev.map( ( r, i ) => ( i === index ? { ...r, ...patch } : r ) ) );
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
					<DialogTitle className="m-0 mb-4 text-2xl font-bold leading-tight tracking-tight text-foreground">
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
						{ notice || __( 'No new holidays found in that file.', 'erp' ) }
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
										{ /* Legacy rendered these as inputs so a bad title or date
										     could be corrected before importing (leave.js:1150, made
										     editable on double-click). Same capability, without the
										     hidden gesture — the fields are simply editable. */ }
										<td className="px-3 align-middle">
											<Input
												value={ row.title }
												onChange={ ( e ) => patchRow( i, { title: e.target.value } ) }
												aria-label={ sprintf( __( 'Title for row %d', 'erp' ), i + 1 ) }
												className="h-9 text-sm font-medium"
											/>
										</td>
										<td className="px-3 align-middle">
											<Input
												type="date"
												value={ row.start.slice( 0, 10 ) }
												onChange={ ( e ) => patchRow( i, { start: e.target.value } ) }
												aria-label={ sprintf( __( 'Start date for row %d', 'erp' ), i + 1 ) }
												className="h-9 text-sm"
											/>
										</td>
										<td className="px-3 align-middle">
											<Input
												type="date"
												value={ row.end.slice( 0, 10 ) }
												onChange={ ( e ) => patchRow( i, { end: e.target.value } ) }
												aria-label={ sprintf( __( 'End date for row %d', 'erp' ), i + 1 ) }
												className="h-9 text-sm"
											/>
										</td>
									</tr>
								) ) }
							</tbody>
						</table>
					</div>
				) }

				{ ! parsed ? (
					<button
						type="button"
						className="inline-flex items-center gap-1.5 self-start text-sm font-medium text-primary hover:underline"
						onClick={ downloadSampleCsv }
					>
						<Download size={ 14 } aria-hidden="true" />
						{ __( 'Download a sample CSV', 'erp' ) }
					</button>
				) : null }

				{ notice && rows.length > 0 ? (
					<Alert>
						<AlertDescription>{ notice }</AlertDescription>
					</Alert>
				) : null }

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
								: sprintf(
										_n( 'Import %d holiday', 'Import %d holidays', checked.size, 'erp' ),
										checked.size
								  ) }
						</Button>
					) : null }
				</DialogFooter>
			</DialogContent>
		</Dialog>
	);
}
