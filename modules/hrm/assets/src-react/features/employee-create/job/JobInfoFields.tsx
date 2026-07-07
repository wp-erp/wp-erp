/**
 * Job-info action fields for the Job-tab update dialog: department, job title,
 * location and reporting-to (server-searched) selects.
 */

import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { useEmployeeSearch } from '@/features/employees/hooks/useEmployeeSearch';

import { SmartSelectField } from '../fields';
import type { Option } from '../options';
import type { FormState } from './job-update-helpers';

interface JobInfoFieldsProps {
	readonly form:         FormState;
	readonly set:          ( key: keyof FormState ) => ( value: string ) => void;
	readonly departments:  Option[];
	readonly designations: Option[];
	readonly locations:    Option[];
	readonly reporting:    ReturnType< typeof useEmployeeSearch >;
}

export function JobInfoFields( {
	form,
	set,
	departments,
	designations,
	locations,
	reporting,
}: JobInfoFieldsProps ): JSX.Element {
	return (
		<>
			<SmartSelectField id="job_department" label={ __( 'Department', 'erp' ) } required options={ departments } value={ form.department } onChange={ set( 'department' ) } placeholder={ __( '- Select -', 'erp' ) } searchPlaceholder={ __( 'Search departments…', 'erp' ) } />
			<SmartSelectField id="job_designation" label={ __( 'Job Title', 'erp' ) } required options={ designations } value={ form.designation } onChange={ set( 'designation' ) } placeholder={ __( '- Select -', 'erp' ) } searchPlaceholder={ __( 'Search job titles…', 'erp' ) } />
			<SmartSelectField id="job_location" label={ __( 'Location', 'erp' ) } options={ locations } value={ form.location } onChange={ set( 'location' ) } placeholder={ __( '- Select -', 'erp' ) } searchPlaceholder={ __( 'Search locations…', 'erp' ) } />
			<SmartSelectField id="job_reporting" label={ __( 'Reporting To', 'erp' ) } required options={ reporting.options } value={ form.reporting_to } onChange={ set( 'reporting_to' ) } onSearch={ reporting.onSearch } loading={ reporting.loading } placeholder={ __( '- Select -', 'erp' ) } searchPlaceholder={ __( 'Search employees…', 'erp' ) } />
		</>
	);
}
