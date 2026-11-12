/**
 * Basic step: company name, start date, financial-year-start month, currency,
 * date format, business type and the Appsero "Share Essentials" consent, the
 * fields of WP ERP's old setup wizard. The server checks the same rules; the
 * lists come from it (`meta`).
 */

import {
	Input,
	Label,
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue,
	Switch,
} from '@wedevs/plugin-ui';
import { createInterpolateElement } from '@wordpress/element';
import { useState } from 'react';
import type { FormEvent, JSX } from 'react';

import { DateField } from '@/shared/DateField';
import { TextButton } from '@/shared/components/TextButton';
import { __ } from '@/shared/i18n';

import { MONTHS } from '../seeders';
import { NextButton } from './NextButton';
import { StepShell } from './StepShell';
import type { ChoiceOption, StepProps } from '../types';
import { siteToday } from '@/shared/utils/date';

/**
 * A month key (`july`) as the site's month name. The keys were shown as typed,
 * capitalised only by CSS, so the closed select read "july" and a screen
 * reader heard the key.
 */
function monthLabel( month: string ): string {
	const index = MONTHS.indexOf( month );

	if ( index < 0 ) {
		return month;
	}

	return new Date( 2000, index, 1 ).toLocaleString( document.documentElement.lang || undefined, { month: 'long' } );
}

/** One labelled select over server-sent choices. */
function ChoiceSelect( { id, label, value, options, onChange, placeholder }: {
	readonly id:           string;
	readonly label:        string;
	readonly value:        string;
	readonly options:      readonly ChoiceOption[];
	readonly onChange:     ( value: string ) => void;
	readonly placeholder?: string;
} ): JSX.Element {
	return (
		<div className="mb-6">
			<Label htmlFor={ id } className="mb-2 block text-sm font-medium text-foreground">
				{ label }
			</Label>
			<Select value={ value || undefined } onValueChange={ ( next ) => onChange( next ?? '' ) }>
				<SelectTrigger id={ id } className="w-full">
					{ /* Without children the trigger showed the raw value: `BDT`, `d-m-Y`, `SmallBWeb`. */ }
					<SelectValue placeholder={ placeholder }>
						{ options.find( ( option ) => option.value === value )?.label ?? placeholder }
					</SelectValue>
				</SelectTrigger>
				<SelectContent>
					{ options.map( ( option ) => (
						<SelectItem key={ option.value } value={ option.value }>
							{ option.label }
						</SelectItem>
					) ) }
				</SelectContent>
			</Select>
		</div>
	);
}

export function BasicStep( { values, meta, saving, onNext }: StepProps ): JSX.Element {
	const [ companyName, setCompanyName ] = useState( values.companyName );
	// Fall back to January 1st of the current year when nothing is stored. The
	// field is required, so an empty date is a dead end the user has to fill in
	// by hand; a company's records almost always start at a year boundary, and
	// it stays editable. Built from local calendar fields, never toISOString(),
	// which would render Dec 31 of the previous year east of UTC.
	const [ companyStartDate, setCompanyStartDate ] = useState(
		values.companyStartDate || `${ siteToday().getFullYear() }-01-01`
	);
	const [ financialYearStarts, setFinancialYearStarts ] = useState(
		values.financialYearStarts || 'january'
	);
	const [ currency, setCurrency ]         = useState( values.currency || '148' );
	const [ dateFormat, setDateFormat ]     = useState( values.dateFormat || 'd-m-Y' );
	const [ businessType, setBusinessType ] = useState( values.businessType );
	const [ shareEssentials, setShareEssentials ] = useState( values.shareEssentials );
	const [ showCollected, setShowCollected ] = useState( false );
	const [ errors, setErrors ] = useState< { name?: string; date?: string } >( {} );

	function handleSubmit( event: FormEvent ): void {
		event.preventDefault();

		const next: { name?: string; date?: string } = {};
		if ( ! companyName.trim() ) {
			next.name = __( 'Company name is required.', 'erp' );
		}
		if ( ! companyStartDate ) {
			next.date = __( 'Company start date is required.', 'erp' );
		}
		setErrors( next );

		if ( Object.keys( next ).length > 0 ) {
			return;
		}

		onNext( { companyName: companyName.trim(), companyStartDate, financialYearStarts, currency, dateFormat, businessType, shareEssentials } );
	}

	return (
		<StepShell
			title={ __( 'Company Profile', 'erp' ) }
			subtitle={ __( 'Tell us about your company and financial year.', 'erp' ) }
		>
			{ /* Setup form rhythm: each field group `mb-6`, labels are
			   `block text-sm font-medium mb-2`, the two-up row is `grid-cols-2 gap-5`. */ }
			<form onSubmit={ handleSubmit } className="text-left">
				<div className="mb-6">
					<Label htmlFor="erp-onb-company-name" className="mb-2 block text-sm font-medium text-foreground">
						{ __( 'Company Name', 'erp' ) } <span className="text-destructive">*</span>
					</Label>
					<Input
						id="erp-onb-company-name"
						value={ companyName }
						onChange={ ( e ) => setCompanyName( e.target.value ) }
						placeholder={ __( 'Company Name', 'erp' ) }
						aria-invalid={ !! errors.name }
						className="w-full"
					/>
					{ errors.name ? <p className="mt-1 text-sm text-destructive">{ errors.name }</p> : null }
				</div>

				<div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
					{ /* The date picker's trigger is an inline button: stretch it to the column. */ }
					<div className="mb-6 [&>button]:w-full">
						<Label className="mb-2 block text-sm font-medium text-foreground">
							{ __( 'Company Start Date', 'erp' ) } <span className="text-destructive">*</span>
						</Label>
						<DateField value={ companyStartDate } onChange={ setCompanyStartDate } className="w-full" />
						{ errors.date ? <p className="mt-1 text-sm text-destructive">{ errors.date }</p> : null }
					</div>

					<div className="mb-6">
						<Label htmlFor="erp-onb-fy-start" className="mb-2 block text-sm font-medium text-foreground">
							{ __( 'Financial Year Starts', 'erp' ) } <span className="text-destructive">*</span>
						</Label>
						<Select
							value={ financialYearStarts }
							onValueChange={ ( value ) => setFinancialYearStarts( value ?? 'january' ) }
						>
							<SelectTrigger id="erp-onb-fy-start" className="w-full">
								<SelectValue>{ monthLabel( financialYearStarts ) }</SelectValue>
							</SelectTrigger>
							<SelectContent>
								{ MONTHS.map( ( month ) => (
									<SelectItem key={ month } value={ month }>
										{ monthLabel( month ) }
									</SelectItem>
								) ) }
							</SelectContent>
						</Select>
					</div>
				</div>

				<div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
					<ChoiceSelect
						id="erp-onb-currency"
						label={ __( 'Currency', 'erp' ) }
						value={ currency }
						options={ meta.currencies }
						onChange={ setCurrency }
					/>
					<ChoiceSelect
						id="erp-onb-date-format"
						label={ __( 'Date Format', 'erp' ) }
						value={ dateFormat }
						options={ meta.dateFormats }
						onChange={ setDateFormat }
					/>
				</div>

				<ChoiceSelect
					id="erp-onb-business-type"
					label={ __( 'What sort of business do you do?', 'erp' ) }
					value={ businessType }
					options={ meta.businessTypes }
					onChange={ setBusinessType }
					placeholder={ __( '- Select -', 'erp' ) }
				/>

				{ /* Appsero consent, as in the old setup wizard: on until the admin
				   answers, then whatever they chose; running setup again can change it. */ }
				<div className="mb-6 rounded-[10px] border border-border p-4">
					<div className="flex items-start justify-between gap-4">
						<div className="min-w-0">
							<Label htmlFor="erp-onb-share-essentials" className="block text-sm font-medium text-foreground">
								{ __( 'Share Essentials', 'erp' ) }
							</Label>
							<p className="m-0 mt-1 text-sm leading-5 text-muted-foreground">
								{ __( 'Want to help make WP ERP even more awesome? Allow weDevs to collect non-sensitive diagnostic data and usage information.', 'erp' ) }{ ' ' }
								<TextButton
									onClick={ () => setShowCollected( ( open ) => ! open ) }
									aria-expanded={ showCollected }
									className="font-medium text-primary hover:text-primary hover:underline"
								>
									{ __( 'What we collect', 'erp' ) }
								</TextButton>
							</p>
						</div>
						<Switch
							id="erp-onb-share-essentials"
							checked={ shareEssentials }
							onCheckedChange={ ( checked ) => setShareEssentials( !! checked ) }
							className="mt-0.5 shrink-0"
						/>
					</div>

					{ showCollected ? (
						<p className="m-0 mt-3 text-sm leading-5 text-muted-foreground">
							{ createInterpolateElement(
								__( 'Server environment details (php, mysql, server, WordPress versions), number of users in your site, site language, number of active and inactive plugins, site name and URL, your name and email address. No sensitive data is tracked. We use Appsero to collect your data. <link>Learn more</link> about how Appsero collects and handles your data.', 'erp' ),
								{
									link: (
										// eslint-disable-next-line jsx-a11y/anchor-has-content -- the text comes from the translated string.
										<a href="https://appsero.com/privacy-policy/" target="_blank" rel="noreferrer" className="font-medium text-primary underline underline-offset-2" />
									),
								}
							) }
						</p>
					) : null }
				</div>

				<NextButton saving={ saving } />
			</form>
		</StepShell>
	);
}
