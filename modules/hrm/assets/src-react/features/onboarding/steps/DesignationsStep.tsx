/**
 * Designations step: create job designations.
 */

import { useMemo } from 'react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { SEEDER_DESIGNATIONS } from '../seeders';
import { OrgSelectStep } from './OrgSelectStep';
import type { StepProps } from '../types';

export function DesignationsStep( { values, saving, onNext }: StepProps ): JSX.Element {
	const suggestions = useMemo( () => {
		const extra = values.designations.filter(
			( d ) => ! SEEDER_DESIGNATIONS.some( ( s ) => s.toLowerCase() === d.toLowerCase() )
		);
		return [ ...SEEDER_DESIGNATIONS, ...extra ];
	}, [ values.designations ] );

	return (
		<OrgSelectStep
			title={ __( 'Set Up Designations', 'erp' ) }
			subtitle={ __( 'Add the job titles used across your teams.', 'erp' ) }
			label={ __( 'Select Designation', 'erp' ) }
			placeholder={ __( 'Add or select a designation', 'erp' ) }
			suggestions={ suggestions }
			initial={ values.designations }
			saving={ saving }
			onSubmit={ ( designations ) => onNext( { designations } ) }
		/>
	);
}
