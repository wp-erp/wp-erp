/**
 * Shared multiselect step for Departments / Designations: pick from seeded
 * suggestions or add free-text values, built on the design system's
 * SmartMultiSelect.
 */

import { Label, SmartMultiSelect } from '@wedevs/plugin-ui';
import type { SmartMultiSelectOption } from '@wedevs/plugin-ui';
import { useMemo, useState } from 'react';
import type { FormEvent, JSX } from 'react';

import { __ } from '@/shared/i18n';

import { NextButton } from './NextButton';
import { StepShell } from './StepShell';

interface OrgSelectStepProps {
	readonly title: string;
	readonly subtitle: string;
	readonly label: string;
	readonly placeholder: string;
	readonly suggestions: readonly string[];
	readonly initial: readonly string[];
	readonly saving: boolean;
	readonly onSubmit: ( selected: string[] ) => void;
}

/** Merge suggestion + selected + any custom values into a de-duped option list. */
function toOptions( names: readonly string[] ): SmartMultiSelectOption[] {
	const seen = new Set< string >();
	const options: SmartMultiSelectOption[] = [];

	names.forEach( ( name ) => {
		const key = name.toLowerCase();
		if ( name && ! seen.has( key ) ) {
			seen.add( key );
			options.push( { label: name, value: name } );
		}
	} );

	return options;
}

export function OrgSelectStep( {
	title,
	subtitle,
	label,
	placeholder,
	suggestions,
	initial,
	saving,
	onSubmit,
}: OrgSelectStepProps ): JSX.Element {
	const [ selected, setSelected ] = useState< string[] >( () => [ ...initial ] );
	const [ custom, setCustom ] = useState< string[] >( () =>
		initial.filter( ( n ) => ! suggestions.some( ( s ) => s.toLowerCase() === n.toLowerCase() ) )
	);

	const options = useMemo(
		() => toOptions( [ ...suggestions, ...custom, ...selected ] ),
		[ suggestions, custom, selected ]
	);

	function handleSubmit( event: FormEvent ): void {
		event.preventDefault();
		onSubmit( selected );
	}

	return (
		<StepShell title={ title } subtitle={ subtitle }>
			{ /* Select field: wrapper `mb-16`, label `block text-sm font-medium mb-2`,
			   and the multiselect container is `w-full`. */ }
			<form onSubmit={ handleSubmit } className="text-left">
				<div className="mb-16 w-full">
					<Label className="mb-2 block text-sm font-medium text-foreground">{ label }</Label>
					<SmartMultiSelect
						options={ options }
						value={ selected }
						onValueChange={ setSelected }
						placeholder={ placeholder }
						className="w-full"
						// Every HR utility is `!important`, so plugin-ui's own `w-72` beats its
						// `!w-(--anchor-width)` on source order; the `[data-slot]` variant
						// outranks it and keeps the list as wide as the field.
						contentClassName="[&[data-slot]]:w-(--anchor-width)"
						hideSelectAll
						selectOnCreate
						onCreate={ ( name, done ) => {
							const clean = name.trim();
							if ( clean ) {
								setCustom( ( prev ) =>
									prev.some( ( n ) => n.toLowerCase() === clean.toLowerCase() )
										? prev
										: [ ...prev, clean ]
								);
							}
							done( clean || undefined );
						} }
					/>
					{ /* The create control is the small + beside Search, easy to miss. */ }
					<p className="mt-2 text-xs text-muted-foreground">
						{ __( 'Not in the list? Open it and use + beside Search to add your own.', 'erp' ) }
					</p>
				</div>

				<NextButton saving={ saving } />
			</form>
		</StepShell>
	);
}
