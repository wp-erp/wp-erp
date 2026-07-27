/**
 * Create-financial-year dialog for the inline "+ Add New" on a Financial Year
 * select.
 *
 * On a site with no financial year the New Leave Request dialog is unusable —
 * the year dropdown is empty and Leave Policy stays disabled behind it, so the
 * form can be filled but never submitted. This lets the year be created without
 * leaving the request.
 *
 * `POST /erp/v2/financial-years` is a whole-set, ID-stable upsert (existing rows
 * keep their ids so `f_year` links survive), so the dialog reads the current set
 * first and posts it back with the new row appended — never just the new one.
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
} from '@wedevs/plugin-ui';
import { useEffect, useState } from 'react';
import type { FormEvent, JSX } from 'react';

import { DateField } from '@/shared/DateField';
import { __ } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';
import { request, restPath } from '@/shared/utils/apiFetch';

import { TextField } from '../employee-create/fields';
import type { FinancialYear } from './types';

interface FinancialYearQuickAddDialogProps {
	readonly open:      boolean;
	readonly onClose:   () => void;
	readonly onCreated: ( year: FinancialYear ) => void;
}

interface FormState {
	fy_name:     string;
	start_date:  string;
	end_date:    string;
	description: string;
}

const EMPTY: FormState = { fy_name: '', start_date: '', end_date: '', description: '' };

export function FinancialYearQuickAddDialog( {
	open,
	onClose,
	onCreated,
}: FinancialYearQuickAddDialogProps ): JSX.Element {
	const [ form, setForm ]     = useState< FormState >( EMPTY );
	const [ errors, setErrors ] = useState< Record< string, string > >( {} );
	const [ busy, setBusy ]     = useState( false );
	const [ error, setError ]   = useState< string | null >( null );

	useEffect( () => {
		if ( ! open ) {
			return;
		}
		setForm( EMPTY );
		setErrors( {} );
		setError( null );
		setBusy( false );
	}, [ open ] );

	const set = ( key: keyof FormState ) => ( value: string ) => {
		setForm( ( p ) => ( { ...p, [ key ]: value } ) );
		setErrors( ( p ) => {
			if ( ! p[ key ] ) {
				return p;
			}
			const next = { ...p };
			delete next[ key ];
			return next;
		} );
	};

	function handleSubmit( e: FormEvent ): void {
		e.preventDefault();

		const next: Record< string, string > = {};
		if ( ! form.fy_name.trim() ) {
			next.fy_name = __( 'Name is required.', 'erp' );
		}
		if ( ! form.start_date ) {
			next.start_date = __( 'Start date is required.', 'erp' );
		}
		if ( ! form.end_date ) {
			next.end_date = __( 'End date is required.', 'erp' );
		} else if ( form.start_date && form.end_date <= form.start_date ) {
			next.end_date = __( 'End date must be after the start date.', 'erp' );
		}
		setErrors( next );
		if ( Object.keys( next ).length > 0 ) {
			return;
		}

		const row: FinancialYear = {
			id:          null,
			fy_name:     form.fy_name.trim(),
			start_date:  form.start_date,
			end_date:    form.end_date,
			description: form.description.trim(),
		};

		setBusy( true );
		setError( null );
		void request< FinancialYear[] >( restPath( 'v2', '/financial-years' ) )
			.then( ( existing ) =>
				request< FinancialYear[] >( restPath( 'v2', '/financial-years' ), {
					method: 'POST',
					data:   { years: [ ...( Array.isArray( existing ) ? existing : [] ), row ] },
				} )
			)
			.then( ( saved ) => {
				const created = ( Array.isArray( saved ) ? saved : [] ).find(
					( y ) => y.fy_name === row.fy_name
				);
				if ( ! created || ! created.id ) {
					setError( __( 'The financial year was saved but could not be selected. Reopen the form.', 'erp' ) );
					return;
				}
				onCreated( created );
			} )
			.catch( ( raw ) =>
				setError( ( raw as ApiError )?.message || __( 'Could not create the financial year.', 'erp' ) )
			)
			.finally( () => setBusy( false ) );
	}

	return (
		<Dialog open={ open } onOpenChange={ ( next ) => ( next || busy ? undefined : onClose() ) }>
			<DialogContent className="gap-4 rounded-[10px] p-6 sm:max-w-lg">
				<DialogHeader>
					<DialogTitle className="m-0 mb-4 text-2xl font-bold leading-tight tracking-tight text-foreground">
						{ __( 'New Financial Year', 'erp' ) }
					</DialogTitle>
					<DialogDescription>
						{ __( 'Leave entitlements and policies are scoped to a financial year.', 'erp' ) }
					</DialogDescription>
				</DialogHeader>
				<div className="h-px w-full bg-border" />

				<form onSubmit={ handleSubmit } className="flex min-w-0 flex-col gap-4">
					<TextField
						id="fy_quick_name"
						label={ __( 'Name', 'erp' ) }
						required
						value={ form.fy_name }
						onChange={ set( 'fy_name' ) }
						error={ errors.fy_name }
						placeholder={ __( 'e.g. 2026-2027', 'erp' ) }
						maxLength={ 200 }
					/>
					<div className="flex min-w-0 flex-col gap-2.5">
						<span className="text-sm font-medium text-foreground">
							{ __( 'Start date', 'erp' ) }
							<span className="ml-0.5 text-destructive">*</span>
						</span>
						<DateField
							value={ form.start_date }
							onChange={ set( 'start_date' ) }
							className="h-10 w-full bg-background"
						/>
						{ errors.start_date ? (
							<p className="text-xs text-destructive">{ errors.start_date }</p>
						) : null }
					</div>
					<div className="flex min-w-0 flex-col gap-2.5">
						<span className="text-sm font-medium text-foreground">
							{ __( 'End date', 'erp' ) }
							<span className="ml-0.5 text-destructive">*</span>
						</span>
						<DateField
							value={ form.end_date }
							onChange={ set( 'end_date' ) }
							min={ form.start_date || undefined }
							className="h-10 w-full bg-background"
						/>
						{ errors.end_date ? (
							<p className="text-xs text-destructive">{ errors.end_date }</p>
						) : null }
					</div>
					<TextField
						id="fy_quick_description"
						label={ __( 'Description', 'erp' ) }
						value={ form.description }
						onChange={ set( 'description' ) }
					/>

					{ error ? (
						<Alert variant="destructive">
							<AlertDescription>{ error }</AlertDescription>
						</Alert>
					) : null }

					<DialogFooter className="gap-5 sm:gap-5">
						<Button type="button" variant="outline" className="h-10 px-4" disabled={ busy } onClick={ onClose }>
							{ __( 'Cancel', 'erp' ) }
						</Button>
						<Button type="submit" className="h-10 px-4" disabled={ busy }>
							{ busy ? __( 'Saving…', 'erp' ) : __( 'Add Financial Year', 'erp' ) }
						</Button>
					</DialogFooter>
				</form>
			</DialogContent>
		</Dialog>
	);
}
