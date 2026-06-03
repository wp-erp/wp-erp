/**
 * Create / edit holiday dialog.
 *
 * Mirrors the legacy holiday form: title (required), start date (required), a
 * "date range" switch that reveals the end date for multi-day holidays, and a
 * description. The model enforces non-empty title / start / end; the client
 * mirrors those plus an end-after-start check. The server re-validates.
 */

import {
	Alert,
	AlertDescription,
	Button,
	Dialog,
	DialogContent,
	DialogDescription,
	DialogFooter,
	DialogHeader,
	DialogTitle,
	Label,
	Switch,
} from '@wedevs/plugin-ui';
import { useEffect, useState } from 'react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { TextField, TextareaField } from '../employee-create/fields';
import type { Holiday, HolidayInput } from './types';

interface HolidayFormDialogProps {
	readonly open:     boolean;
	readonly editing:  Holiday | null;
	readonly busy:     boolean;
	readonly error:    string | null;
	readonly onClose:  () => void;
	readonly onSubmit: ( payload: HolidayInput ) => void;
}

interface FormState {
	title:       string;
	start:       string;
	end:         string;
	range:       boolean;
	description: string;
}

const EMPTY: FormState = { title: '', start: '', end: '', range: false, description: '' };

/** Normalise an ISO / datetime string down to a `YYYY-MM-DD` value for `<input type=date>`. */
function toDateInput( value: string | null ): string {
	if ( ! value ) {
		return '';
	}
	return value.slice( 0, 10 );
}

export function HolidayFormDialog( {
	open,
	editing,
	busy,
	error,
	onClose,
	onSubmit,
}: HolidayFormDialogProps ): JSX.Element {
	const [ form, setForm ] = useState< FormState >( EMPTY );
	const [ errors, setErrors ] = useState< {
		title?: string | undefined;
		start?: string | undefined;
		end?:   string | undefined;
	} >( {} );

	useEffect( () => {
		if ( ! open ) {
			return;
		}
		setErrors( {} );
		setForm(
			editing
				? {
						title:       editing.title,
						start:       toDateInput( editing.start ),
						end:         toDateInput( editing.end ),
						range:       editing.range,
						description: editing.description,
				  }
				: EMPTY
		);
	}, [ open, editing ] );

	function handleSubmit( e: React.FormEvent ): void {
		e.preventDefault();

		const next: { title?: string; start?: string; end?: string } = {};
		const title = form.title.trim();
		const start = form.start.trim();
		const end   = form.end.trim();

		if ( ! title ) {
			next.title = __( 'Title is required.', 'erp' );
		}
		if ( ! start ) {
			next.start = __( 'Start date is required.', 'erp' );
		}
		if ( form.range ) {
			if ( ! end ) {
				next.end = __( 'End date is required for a date range.', 'erp' );
			} else if ( start && end < start ) {
				next.end = __( 'End date must be on or after the start date.', 'erp' );
			}
		}

		if ( Object.keys( next ).length > 0 ) {
			setErrors( next );
			return;
		}

		onSubmit( {
			title,
			start,
			end:         form.range ? end : start,
			range:       form.range,
			description: form.description.trim(),
		} );
	}

	return (
		<Dialog open={ open } onOpenChange={ ( nextOpen ) => ( nextOpen || busy ? undefined : onClose() ) }>
			<DialogContent className="gap-4 rounded-[10px] p-6 sm:max-w-lg">
				<DialogHeader>
					<DialogTitle className="m-0 text-2xl font-bold leading-tight tracking-tight text-foreground">
						{ editing ? __( 'Edit Holiday', 'erp' ) : __( 'Add Holiday', 'erp' ) }
					</DialogTitle>
					<DialogDescription>
						{ __( 'Holidays appear on the leave calendar and are excluded from leave-day counts.', 'erp' ) }
					</DialogDescription>
				</DialogHeader>
				<div className="h-px w-full bg-border" />

				<form onSubmit={ handleSubmit } className="flex min-w-0 flex-col gap-4">
					<TextField
						id="holiday_title"
						label={ __( 'Title', 'erp' ) }
						required
						value={ form.title }
						onChange={ ( v ) => {
							setForm( ( p ) => ( { ...p, title: v } ) );
							setErrors( ( p ) => ( { ...p, title: undefined } ) );
						} }
						error={ errors.title }
						maxLength={ 200 }
					/>

					<div className="flex items-center justify-between rounded-md border border-border bg-muted/20 px-4 py-3">
						<Label htmlFor="holiday_range" className="text-sm font-medium text-foreground">
							{ __( 'Date range (multi-day)', 'erp' ) }
						</Label>
						<Switch
							id="holiday_range"
							checked={ form.range }
							onCheckedChange={ ( checked ) =>
								setForm( ( p ) => ( {
									...p,
									range: checked,
									end:   checked ? p.end : '',
								} ) )
							}
						/>
					</div>

					<div className={ form.range ? 'grid grid-cols-2 gap-4' : '' }>
						<TextField
							id="holiday_start"
							label={ form.range ? __( 'Start Date', 'erp' ) : __( 'Date', 'erp' ) }
							type="date"
							required
							value={ form.start }
							onChange={ ( v ) => {
								setForm( ( p ) => ( { ...p, start: v } ) );
								setErrors( ( p ) => ( { ...p, start: undefined } ) );
							} }
							error={ errors.start }
						/>
						{ form.range ? (
							<TextField
								id="holiday_end"
								label={ __( 'End Date', 'erp' ) }
								type="date"
								required
								value={ form.end }
								onChange={ ( v ) => {
									setForm( ( p ) => ( { ...p, end: v } ) );
									setErrors( ( p ) => ( { ...p, end: undefined } ) );
								} }
								error={ errors.end }
							/>
						) : null }
					</div>

					<TextareaField
						id="holiday_description"
						label={ __( 'Description', 'erp' ) }
						value={ form.description }
						onChange={ ( v ) => setForm( ( p ) => ( { ...p, description: v } ) ) }
						rows={ 3 }
					/>

					{ error ? (
						<Alert variant="destructive">
							<AlertDescription>{ error }</AlertDescription>
						</Alert>
					) : null }

					<DialogFooter className="gap-5 sm:gap-5">
						<Button type="button" variant="outline" className="h-10 px-6" disabled={ busy } onClick={ onClose }>
							{ __( 'Cancel', 'erp' ) }
						</Button>
						<Button type="submit" className="h-10 px-6" disabled={ busy }>
							{ busy
								? __( 'Saving…', 'erp' )
								: editing
								? __( 'Update Holiday', 'erp' )
								: __( 'Create Holiday', 'erp' ) }
						</Button>
					</DialogFooter>
				</form>
			</DialogContent>
		</Dialog>
	);
}
