/**
 * Departments step: create org departments.
 */

import { useMemo } from 'react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { SEEDER_DEPARTMENTS } from '../seeders';
import { OrgSelectStep } from './OrgSelectStep';
import type { StepProps } from '../types';

export function DepartmentsStep( { values, saving, onNext }: StepProps ): JSX.Element {
	const suggestions = useMemo( () => {
		const extra = values.departments.filter(
			( d ) => ! SEEDER_DEPARTMENTS.some( ( s ) => s.toLowerCase() === d.toLowerCase() )
		);
		return [ ...SEEDER_DEPARTMENTS, ...extra ];
	}, [ values.departments ] );

	return (
		<OrgSelectStep
			title={ __( 'Set Up Departments', 'erp' ) }
			subtitle={ __( 'Add the departments that make up your organization.', 'erp' ) }
			label={ __( 'Select Department', 'erp' ) }
			placeholder={ __( 'Add or select a department', 'erp' ) }
			suggestions={ suggestions }
			initial={ values.departments }
			saving={ saving }
			onSubmit={ ( departments ) => onNext( { departments } ) }
		/>
	);
}
