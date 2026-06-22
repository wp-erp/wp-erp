/**
 * Job action fields for the Job update dialog: Department + Job Title (each with
 * an inline "+ Add new" quick-add), Location, and Reporting To. Presentational;
 * the dialog owns the form state, the lookups, and the quick-add handlers.
 */

import type { JSX } from 'react';

import { QuickAddButton } from '@/shared/components/QuickAddButton';
import { __ } from '@/shared/i18n';

import { useEmployeeSearch } from '@/features/employees/hooks/useEmployeeSearch';

import { SmartSelectField } from '../fields';
import type { Option } from '../options';
import type { FormState } from './job-update-helpers';

interface JobInfoFieldsProps {
	readonly form:         FormState;
	readonly set:          ( key: keyof FormState ) => ( value: string ) => void;
	readonly busy:         boolean;
	readonly departments:  Option[];
	readonly designations: Option[];
	readonly locations:    Option[];
	readonly reporting:    ReturnType< typeof useEmployeeSearch >;
	readonly onAddDept:    () => void;
	readonly onAddDesig:   () => void;
}

export function JobInfoFields( {
	form,
	set,
	busy,
	departments,
	designations,
	locations,
	reporting,
	onAddDept,
	onAddDesig,
}: JobInfoFieldsProps ): JSX.Element {
	return (
		<>
			<SmartSelectField id="job_department" label={ __( 'Department', 'erp' ) } required options={ departments } value={ form.department } onChange={ set( 'department' ) } placeholder={ __( '- Select -', 'erp' ) } searchPlaceholder={ __( 'Search departments…', 'erp' ) } labelAction={ <QuickAddButton label={ __( 'Add new', 'erp' ) } onClick={ onAddDept } disabled={ busy } /> } />
			<SmartSelectField id="job_designation" label={ __( 'Job Title', 'erp' ) } required options={ designations } value={ form.designation } onChange={ set( 'designation' ) } placeholder={ __( '- Select -', 'erp' ) } searchPlaceholder={ __( 'Search job titles…', 'erp' ) } labelAction={ <QuickAddButton label={ __( 'Add new', 'erp' ) } onClick={ onAddDesig } disabled={ busy } /> } />
			<SmartSelectField id="job_location" label={ __( 'Location', 'erp' ) } options={ locations } value={ form.location } onChange={ set( 'location' ) } placeholder={ __( '- Select -', 'erp' ) } searchPlaceholder={ __( 'Search locations…', 'erp' ) } />
			<SmartSelectField id="job_reporting" label={ __( 'Reporting To', 'erp' ) } required options={ reporting.options } value={ form.reporting_to } onChange={ set( 'reporting_to' ) } onSearch={ reporting.onSearch } loading={ reporting.loading } placeholder={ __( '- Select -', 'erp' ) } searchPlaceholder={ __( 'Search employees…', 'erp' ) } />
		</>
	);
}
