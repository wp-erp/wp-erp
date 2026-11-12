/**
 * Leave step: financial (leave) years + which predefined leave types to set up,
 * built on the design system.
 *
 * Saving creates the financial years; the leave *policies* for the selected
 * types are created on complete. There is no "enable leave management" toggle
 * here: the wizard never showed one, so it would be a dead control.
 */

import { Alert, AlertDescription, Button, Checkbox, Input, Label } from '@wedevs/plugin-ui';
import { Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent, JSX } from 'react';

import { DateField } from '@/shared/DateField';
import { PlainLabel } from '@/shared/components/FieldLabels';
import { __, sprintf } from '@/shared/i18n';

import { NextButton } from './NextButton';
import { StepShell } from './StepShell';
import type { LeaveYear, StepProps } from '../types';
import { siteToday } from '@/shared/utils/date';
import { MONTHS } from '../seeders';

/** A row in the editor: `key` is a local-only identity for React/removal. */
interface YearRow extends LeaveYear {
	readonly key: string;
}

/**
 * The leave year that holds today, starting on the financial year month picked
 * in the Company step. It always started on January 1, so a company whose
 * year runs July to June was offered a calendar year it then had to retype.
 */
function defaultRow( key: string, startsIn: string ): YearRow {
	const today = siteToday();
	const month = Math.max( 0, MONTHS.indexOf( startsIn ) );
	const year  = today.getMonth() >= month ? today.getFullYear() : today.getFullYear() - 1;
	const pad   = ( n: number ): string => String( n ).padStart( 2, '0' );
	// Day 0 of the start month, a year on, is the day before the next year starts.
	const end   = new Date( year + 1, month, 0 );

	return {
		key,
		fy_name: 0 === month ? String( year ) : `${ year }-${ String( year + 1 ).slice( -2 ) }`,
		start_date: `${ year }-${ pad( month + 1 ) }-01`,
		end_date: `${ end.getFullYear() }-${ pad( end.getMonth() + 1 ) }-${ pad( end.getDate() ) }`,
	};
}

export function LeaveStep( { values, meta, saving, onNext }: StepProps ): JSX.Element {
	const [ nextKey, setNextKey ] = useState( 1 );
	const [ rows, setRows ] = useState< YearRow[] >( () =>
		values.leaveYears.length > 0
			? values.leaveYears.map( ( y, i ) => ( { ...y, key: `db-${ i }` } ) )
			: [ defaultRow( 'row-0', values.financialYearStarts ) ]
	);
	// Pre-select every predefined type when nothing is stored yet, matching the
	// wizard this is ported from. Seeding straight from values would leave the
	// grid empty whenever hydration fails, and saving that empty set is sticky:
	// the server treats [] as a deliberate "none", which then suppresses leave
	// policy creation on complete.
	const [ selected, setSelected ] = useState< string[] >( () =>
		values.selectedLeaveTypes.length > 0
			? [ ...values.selectedLeaveTypes ]
			: meta.leaveTypes.map( ( t ) => t.id )
	);
	const [ errors, setErrors ] = useState< string[] >( [] );

	const leaveTypes = meta.leaveTypes;

	function setRow( key: string, field: keyof LeaveYear, value: string ): void {
		setRows( ( prev ) => prev.map( ( r ) => ( r.key === key ? { ...r, [ field ]: value } : r ) ) );
	}

	function addRow(): void {
		setRows( ( prev ) => [ ...prev, { key: `row-${ nextKey }`, fy_name: '', start_date: '', end_date: '' } ] );
		setNextKey( ( n ) => n + 1 );
	}

	function removeRow( key: string ): void {
		setRows( ( prev ) => ( prev.length > 1 ? prev.filter( ( r ) => r.key !== key ) : prev ) );
	}

	function toggleType( id: string ): void {
		setSelected( ( prev ) =>
			prev.includes( id ) ? prev.filter( ( t ) => t !== id ) : [ ...prev, id ]
		);
	}

	function validate(): string[] {
		const found: string[] = [];
		const names: string[] = [];

		rows.forEach( ( row, index ) => {
			const n = index + 1;

			if ( ! row.fy_name.trim() ) {
				/* translators: %d: row number. */
				found.push( sprintf( __( 'Please give a leave year name on row #%d.', 'erp' ), n ) );
			}
			if ( ! row.start_date ) {
				/* translators: %d: row number. */
				found.push( sprintf( __( 'Please give a start date on row #%d.', 'erp' ), n ) );
			}
			if ( ! row.end_date ) {
				/* translators: %d: row number. */
				found.push( sprintf( __( 'Please give an end date on row #%d.', 'erp' ), n ) );
			}
			if ( row.start_date && row.end_date && new Date( row.end_date ) <= new Date( row.start_date ) ) {
				/* translators: %d: row number. */
				found.push( sprintf( __( 'End date must be after the start date on row #%d.', 'erp' ), n ) );
			}

			const key = row.fy_name.trim();
			if ( key ) {
				if ( names.includes( key ) ) {
					/* translators: 1: leave year name, 2: row number. */
					found.push(
						sprintf( __( 'Duplicate leave year name "%1$s" on row #%2$d.', 'erp' ), key, n )
					);
				} else {
					names.push( key );
				}
			}
		} );

		// Two leave years may not share a day: balances are counted per year.
		rows.forEach( ( a, i ) => {
			rows.slice( i + 1 ).forEach( ( b, j ) => {
				if ( a.start_date && a.end_date && b.start_date && b.end_date && a.start_date <= b.end_date && b.start_date <= a.end_date ) {
					found.push(
						/* translators: 1: row number, 2: row number. */
						sprintf( __( 'The leave years on rows #%1$d and #%2$d overlap.', 'erp' ), i + 1, i + j + 2 )
					);
				}
			} );
		} );

		return found;
	}

	function handleSubmit( event: FormEvent ): void {
		event.preventDefault();

		const found = validate();
		setErrors( found );

		if ( found.length > 0 ) {
			return;
		}

		onNext( {
			// Saved rows keep their id: leave years are updated in place, so the
			// leave policies and entitlements that point at them stay attached.
			leaveYears: rows.map( ( { id, fy_name, start_date, end_date } ) => ( {
				...( id ? { id } : {} ),
				fy_name: fy_name.trim(),
				start_date,
				end_date,
			} ) ),
			selectedLeaveTypes: selected,
		} );
	}

	return (
		<StepShell
			title={ __( 'Leave Setup', 'erp' ) }
			subtitle={ __( 'Set your leave year and pick the leave types to start with.', 'erp' ) }
		>
			{ /* The leave-years block is `mb-16`, each row `mb-4` with
			   `flex gap-3 items-end`, and row labels are `block text-xs font-medium mb-1`. */ }
			<form onSubmit={ handleSubmit } className="text-left">
				{ errors.length > 0 ? (
					<Alert variant="destructive" className="mb-6">
						<AlertDescription>
							<ul className="list-inside list-disc space-y-1">
								{ errors.map( ( error ) => (
									<li key={ error }>{ error }</li>
								) ) }
							</ul>
						</AlertDescription>
					</Alert>
				) : null }

				<div className="mb-16">
					<Label className="mb-2 block text-sm font-medium text-foreground">
						{ __( 'Leave Years', 'erp' ) }
					</Label>

					{ rows.map( ( row ) => (
						<div key={ row.key } className="mb-4 flex flex-wrap items-end gap-3">
							<div className="min-w-32 flex-1">
								<Label className="mb-1 block text-xs font-medium text-muted-foreground">
									{ __( 'Leave Year', 'erp' ) }
								</Label>
								<Input
									value={ row.fy_name }
									onChange={ ( e ) => setRow( row.key, 'fy_name', e.target.value ) }
									placeholder={ __( 'e.g. 2026', 'erp' ) }
									className="h-10 w-full px-3 py-2 text-sm"
								/>
							</div>

							<div className="min-w-36 flex-1 [&>button]:w-full">
								<Label className="mb-1 block text-xs font-medium text-muted-foreground">
									{ __( 'Start Date', 'erp' ) }
								</Label>
								<DateField
									value={ row.start_date }
									onChange={ ( v ) => setRow( row.key, 'start_date', v ) }
									className="h-10 w-full px-3 py-2 text-sm"
								/>
							</div>

							<div className="min-w-36 flex-1 [&>button]:w-full">
								<Label className="mb-1 block text-xs font-medium text-muted-foreground">
									{ __( 'End Date', 'erp' ) }
								</Label>
								<DateField
									value={ row.end_date }
									onChange={ ( v ) => setRow( row.key, 'end_date', v ) }
									className="h-10 w-full px-3 py-2 text-sm"
								/>
							</div>

							{ /* Remove control: 36x40, rounded destructive outline that tints
							   on hover. */ }
							{ rows.length > 1 ? (
								<Button
									type="button"
									variant="outline"
									onClick={ () => removeRow( row.key ) }
									aria-label={ __( 'Remove leave year', 'erp' ) }
									className="h-10 w-9 shrink-0 border-destructive/50 p-0 text-destructive hover:bg-destructive/10 hover:text-destructive"
								>
									<Trash2 size={ 16 } aria-hidden="true" />
								</Button>
							) : null }
						</div>
					) ) }

					{ /* Add control: `px-4 py-2 text-sm font-medium` filled primary. */ }
					<Button
						type="button"
						onClick={ addRow }
						className="inline-flex h-auto items-center gap-2 px-4 py-2 text-sm font-medium"
					>
						<Plus size={ 16 } aria-hidden="true" />
						{ __( 'Add New', 'erp' ) }
					</Button>
				</div>

				{ /* "Predefined Leave" card: `rounded-xl border overflow-hidden mb-8`,
				   header `px-6 pt-6 pb-4` (centered title + subtitle + `border-t mt-4`),
				   body `p-6` with a `grid-cols-3 gap-y-6 gap-x-2` of round checkboxes. */ }
				{ leaveTypes.length > 0 ? (
					<div className="mb-8 overflow-hidden rounded-xl border border-border">
						<div className="px-6 pb-4 pt-6">
							<h3 className="m-0 text-center text-xl font-semibold text-foreground">
								{ __( 'Predefined Leave', 'erp' ) }
							</h3>
							<p className="m-0 mt-1 text-center text-sm text-muted-foreground">
								{ __( 'Select the leave types to enable for your organization.', 'erp' ) }
							</p>
							<div className="mt-4 border-t border-border" />
						</div>

						<div className="p-6">
							<div className="grid grid-cols-2 gap-x-2 gap-y-6 sm:grid-cols-3">
								{ leaveTypes.map( ( type ) => (
									<PlainLabel
										key={ type.id }
										className="flex cursor-pointer select-none items-center gap-2"
									>
										<Checkbox
											checked={ selected.includes( type.id ) }
											onCheckedChange={ () => toggleType( type.id ) }
											className="size-5 shrink-0 rounded-full"
										/>
										<span className="text-sm text-foreground">{ type.name }</span>
									</PlainLabel>
								) ) }
							</div>
						</div>
					</div>
				) : null }

				<NextButton saving={ saving } />
			</form>
		</StepShell>
	);
}
