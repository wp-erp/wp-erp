/**
 * Workday step: per-weekday Full/Half/Non-working + office hours. Built on the design system (Button + Input).
 */

import { Button, cn, Input, Label } from '@wedevs/plugin-ui';
import { useState } from 'react';
import type { FormEvent, JSX } from 'react';

import { __ } from '@/shared/i18n';

import { NextButton } from './NextButton';
import { StepShell } from './StepShell';
import type { StepProps, Weekday, WorkingHours } from '../types';

const DAYS: readonly { key: Weekday; label: string }[] = [
	{ key: 'mon', label: __( 'Monday', 'erp' ) },
	{ key: 'tue', label: __( 'Tuesday', 'erp' ) },
	{ key: 'wed', label: __( 'Wednesday', 'erp' ) },
	{ key: 'thu', label: __( 'Thursday', 'erp' ) },
	{ key: 'fri', label: __( 'Friday', 'erp' ) },
	{ key: 'sat', label: __( 'Saturday', 'erp' ) },
	{ key: 'sun', label: __( 'Sunday', 'erp' ) },
];

const OPTIONS: readonly { value: string; label: string }[] = [
	{ value: '8', label: __( 'Full Day', 'erp' ) },
	{ value: '4', label: __( 'Half Day', 'erp' ) },
	{ value: '0', label: __( 'Non-working', 'erp' ) },
];

export function WorkdayStep( { values, saving, onNext }: StepProps ): JSX.Element {
	const [ workingDays, setWorkingDays ] = useState< Record< Weekday, string > >( () => ( {
		...values.workingDays,
	} ) );
	const [ hours, setHours ] = useState< WorkingHours >( () => ( { ...values.workingHours } ) );

	function setDay( day: Weekday, value: string ): void {
		setWorkingDays( ( prev ) => ( { ...prev, [ day ]: value } ) );
	}

	function handleSubmit( event: FormEvent ): void {
		event.preventDefault();
		onNext( { workingDays, workingHours: hours } );
	}

	return (
		<StepShell
			title={ __( 'Workday Setup', 'erp' ) }
			subtitle={ __( 'Configure your weekly schedule and office hours.', 'erp' ) }
			subtitleSpacing="mb-20"
		>
			{ /* Day list `flex flex-col gap-1`, each row
			   `flex items-center justify-between py-2`, label `min-w-[100px] text-sm
			   font-medium`, options `flex gap-2`, pill `px-5 py-3 rounded-full text-sm
			   leading-4 border shadow-sm`. */ }
			<form onSubmit={ handleSubmit }>
				<div className="flex flex-col gap-1">
					{ DAYS.map( ( day ) => (
						<div key={ day.key } className="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 py-2">
							<span className="min-w-[100px] text-sm font-medium text-foreground">{ day.label }</span>
							<div className="flex flex-wrap gap-2">
								{ OPTIONS.map( ( opt ) => {
									const selected = workingDays[ day.key ] === opt.value;
									const nonWorking = '0' === opt.value;

									return (
										<Button
											key={ opt.value }
											type="button"
											variant={ selected ? 'default' : 'outline' }
											onClick={ () => setDay( day.key, opt.value ) }
											className={ cn(
												'h-auto rounded-full px-3.5 py-3 text-sm font-normal leading-4 shadow-sm sm:px-5',
												selected && nonWorking && 'bg-destructive text-destructive-foreground hover:bg-destructive/90'
											) }
										>
											{ opt.label }
										</Button>
									);
								} ) }
							</div>
						</div>
					) ) }
				</div>

				<div className="mt-8 grid grid-cols-1 gap-5 text-left sm:grid-cols-2">
					<div>
						<Label
							htmlFor="erp-onb-hours-start"
							className="mb-2 block text-sm font-medium text-foreground"
						>
							{ __( 'Office Start Time', 'erp' ) }
						</Label>
						<Input
							id="erp-onb-hours-start"
							type="time"
							value={ hours.start }
							onChange={ ( e ) => setHours( ( prev ) => ( { ...prev, start: e.target.value } ) ) }
							className="w-full"
						/>
					</div>
					<div>
						<Label htmlFor="erp-onb-hours-end" className="mb-2 block text-sm font-medium text-foreground">
							{ __( 'Office End Time', 'erp' ) }
						</Label>
						<Input
							id="erp-onb-hours-end"
							type="time"
							value={ hours.end }
							onChange={ ( e ) => setHours( ( prev ) => ( { ...prev, end: e.target.value } ) ) }
							className="w-full"
						/>
					</div>
				</div>

				<NextButton saving={ saving } />
			</form>
		</StepShell>
	);
}
