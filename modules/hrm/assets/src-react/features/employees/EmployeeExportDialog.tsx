/**
 * Employee CSV export dialog.
 *
 * Pick which columns to include → stream the current employees to a CSV
 * download. Header keys stay identical to the import columns so an exported
 * file round-trips back through import. Visual language follows the HRM 2024
 * redesign (CSV-file card, "select all", field-picker grid).
 */

import {
	Button,
	Checkbox,
	Dialog,
	DialogContent,
	DialogFooter,
	DialogHeader,
	DialogTitle,
	toast,
} from '@wedevs/plugin-ui';
import { ArrowRight, FileSpreadsheet, Info } from 'lucide-react';
import { useState } from 'react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import {
	EXPORT_FIELDS,
	EXPORT_FIELD_KEYS,
	buildEmployeesCsv,
	downloadCsv,
} from './useEmployeeImportExport';

interface EmployeeExportDialogProps {
	readonly open:    boolean;
	readonly onClose: () => void;
}

export function EmployeeExportDialog( { open, onClose }: EmployeeExportDialogProps ): JSX.Element {
	const [ selected, setSelected ] = useState< Set< string > >( () => new Set( EXPORT_FIELD_KEYS ) );
	const [ exporting, setExporting ] = useState( false );

	function close(): void {
		setSelected( new Set( EXPORT_FIELD_KEYS ) );
		setExporting( false );
		onClose();
	}

	function toggleField( key: string ): void {
		setSelected( ( prev ) => {
			const next = new Set( prev );
			if ( next.has( key ) ) {
				next.delete( key );
			} else {
				next.add( key );
			}
			return next;
		} );
	}

	function toggleAll(): void {
		setSelected( ( prev ) => ( prev.size === EXPORT_FIELDS.length ? new Set() : new Set( EXPORT_FIELD_KEYS ) ) );
	}

	function handleExport(): void {
		const keys = EXPORT_FIELD_KEYS.filter( ( k ) => selected.has( k ) );
		if ( keys.length === 0 ) {
			return;
		}
		setExporting( true );
		buildEmployeesCsv( 'active', keys )
			.then( ( csv ) => {
				downloadCsv( 'employees.csv', csv );
				toast.success( __( 'Export ready — check your downloads.', 'erp' ) );
				close();
			} )
			.catch( () => toast.error( __( 'Export failed. Please try again.', 'erp' ) ) )
			.finally( () => setExporting( false ) );
	}

	const allSelected = selected.size === EXPORT_FIELDS.length;
	const someSelected = selected.size > 0 && ! allSelected;

	return (
		<Dialog open={ open } onOpenChange={ ( next ) => ( next ? undefined : close() ) }>
			<DialogContent className="max-h-[90vh] gap-4 overflow-y-auto rounded-[10px] p-6 sm:max-w-2xl">
				<DialogHeader className="space-y-1.5 text-center sm:text-center">
					<DialogTitle className="m-0 mb-4 text-center text-2xl font-bold leading-tight tracking-tight text-foreground">
						{ __( 'Export Employee', 'erp' ) }
					</DialogTitle>
					<p className="mx-auto max-w-md text-sm text-muted-foreground">
						{ __( 'Export employee data from your WP ERP by downloading a CSV file.', 'erp' ) }
					</p>
				</DialogHeader>

				<div className="space-y-5 rounded-xl border border-border bg-card px-6 pb-6 pt-5">
					<div className="space-y-1.5">
						<p className="m-0 mb-4 text-xs font-medium uppercase tracking-wide text-muted-foreground">
							{ __( 'Export as', 'erp' ) }
						</p>
						<div className="inline-flex items-center gap-3 rounded-lg border border-primary/40 bg-primary/5 px-4 py-3">
							<span className="flex size-8 items-center justify-center rounded-md bg-emerald-500/15 text-emerald-600">
								<FileSpreadsheet size={ 18 } aria-hidden="true" />
							</span>
							<span className="text-sm font-medium text-foreground">{ __( 'CSV File', 'erp' ) }</span>
						</div>
					</div>

					<div className="h-px w-full bg-border" />

					<label className="flex cursor-pointer items-center gap-2 text-sm font-medium text-foreground">
						<Checkbox
							checked={ allSelected }
							indeterminate={ someSelected }
							onCheckedChange={ toggleAll }
						/>
						{ __( 'Select All Fields', 'erp' ) }
					</label>

					<div className="flex items-center gap-2.5 rounded-md bg-primary/5 p-4 text-sm text-primary">
						<Info size={ 16 } aria-hidden="true" />
						{ __( 'Only selected fields will be on the CSV file.', 'erp' ) }
					</div>

					<div className="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
						{ EXPORT_FIELDS.map( ( field ) => (
							<label
								key={ field.key }
								className="flex cursor-pointer items-center gap-2 text-sm text-foreground"
							>
								<Checkbox
									checked={ selected.has( field.key ) }
									onCheckedChange={ () => toggleField( field.key ) }
								/>
								{ field.label }
							</label>
						) ) }
					</div>
				</div>

				<DialogFooter className="gap-5 sm:gap-5">
					<Button type="button" variant="outline" className="h-10 px-6" disabled={ exporting } onClick={ close }>
						{ __( 'Cancel', 'erp' ) }
					</Button>
					<Button
						type="button"
						className="h-10 gap-1.5 px-6"
						disabled={ exporting || selected.size === 0 }
						onClick={ handleExport }
					>
						{ exporting ? __( 'Exporting…', 'erp' ) : __( 'Continue', 'erp' ) }
						<ArrowRight size={ 15 } aria-hidden="true" />
					</Button>
				</DialogFooter>
			</DialogContent>
		</Dialog>
	);
}
