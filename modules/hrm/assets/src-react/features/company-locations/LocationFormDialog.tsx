/**
 * Create-location dialog for the inline "+ Add new" on any Location select.
 *
 * Work locations live on the company record, not in HR, and the only screen that
 * created them was the legacy ERP → Company page — so on a fresh site the
 * employee form's Location dropdown was a dead end. This is the same field set
 * as that page's New Location modal (`erp-address` template), posting to
 * `POST /erp/v2/company-locations` → `Company::create_location()`.
 *
 * Mirrors the Department / Job Title quick-add: the host form stays open and
 * selects whatever comes back.
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
import { useEffect, useMemo, useState } from 'react';
import type { FormEvent, JSX } from 'react';

import { useBoot } from '@/shared/hooks/useBoot';
import { __ } from '@/shared/i18n';
import { dismissGuard } from '@/shared/utils/dialog';
import type { ApiError } from '@/shared/utils/apiFetch';
import { request, restPath } from '@/shared/utils/apiFetch';

import { SelectField, SmartSelectField, TextField } from '../employee-create/fields';
import type { Option } from '../employee-create/options';

/** What the endpoint returns — the shape the Location selects already speak. */
export interface CreatedLocation {
	readonly id:    number;
	readonly title: string;
}

interface LocationFormDialogProps {
	readonly open:      boolean;
	readonly onClose:   () => void;
	readonly onCreated: ( location: CreatedLocation ) => void;
}

interface FormState {
	name:      string;
	address_1: string;
	address_2: string;
	city:      string;
	state:     string;
	country:   string;
	zip:       string;
}

const EMPTY: FormState = {
	name:      '',
	address_1: '',
	address_2: '',
	city:      '',
	state:     '',
	country:   '',
	zip:       '',
};

export function LocationFormDialog( { open, onClose, onCreated }: LocationFormDialogProps ): JSX.Element {
	const boot = useBoot();

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

	const countryOptions = useMemo< Option[] >(
		() => ( boot.countries ?? [] ).map( ( c ) => ( { value: c.value, label: c.label } ) ),
		[ boot.countries ]
	);
	const stateOptions = useMemo< Option[] >(
		() => ( ( boot.states ?? {} )[ form.country ] ?? [] ).map( ( s ) => ( { value: s.value, label: s.label } ) ),
		[ boot.states, form.country ]
	);

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

		// The three the model refuses to save without (`Company::create_location()`).
		const next: Record< string, string > = {};
		if ( ! form.name.trim() ) {
			next.name = __( 'Location name is required.', 'erp' );
		}
		if ( ! form.address_1.trim() ) {
			next.address_1 = __( 'Address is required.', 'erp' );
		}
		if ( ! form.country ) {
			next.country = __( 'Country is required.', 'erp' );
		}
		setErrors( next );
		if ( Object.keys( next ).length > 0 ) {
			return;
		}

		setBusy( true );
		setError( null );
		void request< CreatedLocation >( restPath( 'v2', '/company-locations' ), {
			method: 'POST',
			data:   {
				name:      form.name.trim(),
				address_1: form.address_1.trim(),
				address_2: form.address_2.trim(),
				city:      form.city.trim(),
				state:     form.state,
				country:   form.country,
				zip:       form.zip.trim(),
			},
		} )
			.then( ( created ) => onCreated( created ) )
			.catch( ( raw ) =>
				setError( ( raw as ApiError )?.message || __( 'Could not create the location.', 'erp' ) )
			)
			.finally( () => setBusy( false ) );
	}

	return (
		<Dialog open={ open } onOpenChange={ dismissGuard( onClose, busy ) }>
			<DialogContent className="gap-4 rounded-[10px] p-6 sm:max-w-lg">
				<DialogHeader>
					<DialogTitle className="m-0 mb-4 text-2xl font-bold leading-tight tracking-tight text-foreground">
						{ __( 'New Location', 'erp' ) }
					</DialogTitle>
					<DialogDescription>
						{ __( 'Work locations belong to your company and are shared across HR.', 'erp' ) }
					</DialogDescription>
				</DialogHeader>
				<div className="h-px w-full bg-border" />

				<form onSubmit={ handleSubmit } className="flex min-w-0 flex-col gap-4">
					<TextField
						id="location_name"
						label={ __( 'Location Name', 'erp' ) }
						required
						value={ form.name }
						onChange={ set( 'name' ) }
						error={ errors.name }
						maxLength={ 200 }
					/>
					<TextField
						id="location_address_1"
						label={ __( 'Address 1', 'erp' ) }
						required
						value={ form.address_1 }
						onChange={ set( 'address_1' ) }
						error={ errors.address_1 }
					/>
					<TextField
						id="location_address_2"
						label={ __( 'Address 2', 'erp' ) }
						value={ form.address_2 }
						onChange={ set( 'address_2' ) }
					/>
					<TextField
						id="location_city"
						label={ __( 'City', 'erp' ) }
						value={ form.city }
						onChange={ set( 'city' ) }
					/>
					{ countryOptions.length > 0 ? (
						<SmartSelectField
							id="location_country"
							label={ __( 'Country', 'erp' ) }
							required
							options={ countryOptions }
							value={ form.country }
							onChange={ ( v ) => {
								setForm( ( p ) => ( { ...p, country: v, state: '' } ) );
								setErrors( ( p ) => {
									const nextErrors = { ...p };
									delete nextErrors.country;
									return nextErrors;
								} );
							} }
							error={ errors.country }
							placeholder={ __( '- Select -', 'erp' ) }
							searchPlaceholder={ __( 'Search countries…', 'erp' ) }
						/>
					) : (
						<TextField
							id="location_country"
							label={ __( 'Country', 'erp' ) }
							required
							value={ form.country }
							onChange={ set( 'country' ) }
							error={ errors.country }
						/>
					) }
					{ stateOptions.length > 0 ? (
						<SelectField
							id="location_state"
							label={ __( 'Province / State', 'erp' ) }
							options={ stateOptions }
							value={ form.state }
							onChange={ set( 'state' ) }
							placeholder={ __( '- Select -', 'erp' ) }
						/>
					) : (
						<TextField
							id="location_state"
							label={ __( 'Province / State', 'erp' ) }
							value={ form.state }
							onChange={ set( 'state' ) }
						/>
					) }
					<TextField
						id="location_zip"
						label={ __( 'Post Code / Zip Code', 'erp' ) }
						value={ form.zip }
						onChange={ set( 'zip' ) }
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
							{ busy ? __( 'Saving…', 'erp' ) : __( 'Add Location', 'erp' ) }
						</Button>
					</DialogFooter>
				</form>
			</DialogContent>
		</Dialog>
	);
}
