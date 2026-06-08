/**
 * Add dialog for the Performance tab — one component, three types:
 *   reviews  → ratings + reporting-to
 *   comments → reviewer + comment
 *   goals    → goal/assessment + supervisor + completion date
 *
 * Mirrors the legacy performance modals. Submits to the v2 `/performance` POST,
 * which delegates to the unchanged `Employee::add_performance()`.
 */

import {
	Alert,
	AlertDescription,
	Button,
	Dialog,
	DialogContent,
	DialogFooter,
	DialogHeader,
	DialogTitle,
} from '@wedevs/plugin-ui';
import { useEffect, useMemo, useState } from 'react';
import type { FormEvent, JSX } from 'react';

import { __ } from '@/shared/i18n';

import { useEmployeeSearch } from '@/features/employees/hooks/useEmployeeSearch';
import { SelectField, SmartSelectField, TextField, TextareaField } from '../fields';
import { RATING_OPTIONS } from '../options';

export type PerformanceType = 'reviews' | 'comments' | 'goals';

interface PerformanceFormDialogProps {
	readonly type:     PerformanceType | null;
	readonly busy:     boolean;
	readonly error:    string | null;
	readonly onClose:  () => void;
	readonly onSubmit: ( payload: Record< string, unknown > ) => void;
}

interface FormState {
	performance_date:      string;
	reporting_to:          string;
	job_knowledge:         string;
	work_quality:          string;
	attendance:            string;
	communication:         string;
	dependablity:          string;
	reviewer:              string;
	comments:              string;
	goal_description:      string;
	employee_assessment:   string;
	supervisor:            string;
	supervisor_assessment: string;
	completion_date:       string;
}

function todayISO(): string {
	const d = new Date();
	return Number.isNaN( d.getTime() ) ? '' : d.toISOString().slice( 0, 10 );
}

function emptyForm(): FormState {
	return {
		performance_date:      todayISO(),
		reporting_to:          '',
		job_knowledge:         '',
		work_quality:          '',
		attendance:            '',
		communication:         '',
		dependablity:          '',
		reviewer:              '',
		comments:              '',
		goal_description:      '',
		employee_assessment:   '',
		supervisor:            '',
		supervisor_assessment: '',
		completion_date:       todayISO(),
	};
}

const TITLES: Record< PerformanceType, string > = {
	reviews:  __( 'Add Performance Review', 'erp' ),
	comments: __( 'Add Performance Comment', 'erp' ),
	goals:    __( 'Add Performance Goal', 'erp' ),
};

export function PerformanceFormDialog( {
	type,
	busy,
	error,
	onClose,
	onSubmit,
}: PerformanceFormDialogProps ): JSX.Element {
	const [ form, setForm ]         = useState< FormState >( emptyForm );
	const reporting  = useEmployeeSearch( type !== null, undefined, form.reporting_to );
	const reviewer   = useEmployeeSearch( type !== null, undefined, form.reviewer );
	const supervisor = useEmployeeSearch( type !== null, undefined, form.supervisor );

	useEffect( () => {
		if ( type ) {
			setForm( emptyForm() );
		}
	}, [ type ] );

	const set = ( key: keyof FormState ) => ( value: string ): void =>
		setForm( ( prev ) => ( { ...prev, [ key ]: value } ) );

	const payload = useMemo< Record< string, unknown > >( () => {
		switch ( type ) {
			case 'reviews':
				return {
					type:             'reviews',
					performance_date: form.performance_date,
					reporting_to:     form.reporting_to ? Number( form.reporting_to ) : 0,
					job_knowledge:    form.job_knowledge,
					work_quality:     form.work_quality,
					attendance:       form.attendance,
					communication:    form.communication,
					dependablity:     form.dependablity,
				};
			case 'comments':
				return {
					type:             'comments',
					performance_date: form.performance_date,
					reviewer:         form.reviewer ? Number( form.reviewer ) : 0,
					comments:         form.comments,
				};
			case 'goals':
				return {
					type:                  'goals',
					performance_date:      form.performance_date,
					completion_date:       form.completion_date,
					goal_description:      form.goal_description,
					employee_assessment:   form.employee_assessment,
					supervisor:            form.supervisor ? Number( form.supervisor ) : 0,
					supervisor_assessment: form.supervisor_assessment,
				};
			default:
				return {};
		}
	}, [ type, form ] );

	function handleSubmit( e: FormEvent ): void {
		e.preventDefault();
		onSubmit( payload );
	}

	return (
		<Dialog open={ type !== null } onOpenChange={ ( next ) => ( next || busy ? undefined : onClose() ) }>
			<DialogContent className="gap-4 rounded-[10px] p-6 sm:max-w-lg">
				<DialogHeader>
					<DialogTitle className="m-0 text-2xl font-bold leading-tight tracking-tight text-foreground">{ type ? TITLES[ type ] : '' }</DialogTitle>
				</DialogHeader>
				<div className="h-px w-full bg-border" />

				<form onSubmit={ handleSubmit } className="flex min-w-0 flex-col gap-4">
					{ error ? (
						<Alert variant="destructive">
							<AlertDescription>{ error }</AlertDescription>
						</Alert>
					) : null }

					<TextField id="perf_date" label={ __( 'Date', 'erp' ) } type="date" required value={ form.performance_date } onChange={ set( 'performance_date' ) } />

					{ type === 'reviews' ? (
						<>
							<SmartSelectField id="perf_reporting" label={ __( 'Reporting To', 'erp' ) } required options={ reporting.options } value={ form.reporting_to } onChange={ set( 'reporting_to' ) } onSearch={ reporting.onSearch } loading={ reporting.loading } placeholder={ __( '- Select -', 'erp' ) } searchPlaceholder={ __( 'Search employees…', 'erp' ) } />
							<SelectField id="perf_jk" label={ __( 'Job Knowledge', 'erp' ) } options={ RATING_OPTIONS } value={ form.job_knowledge } onChange={ set( 'job_knowledge' ) } placeholder={ __( '- Select -', 'erp' ) } />
							<SelectField id="perf_wq" label={ __( 'Work Quality', 'erp' ) } options={ RATING_OPTIONS } value={ form.work_quality } onChange={ set( 'work_quality' ) } placeholder={ __( '- Select -', 'erp' ) } />
							<SelectField id="perf_at" label={ __( 'Attendance', 'erp' ) } options={ RATING_OPTIONS } value={ form.attendance } onChange={ set( 'attendance' ) } placeholder={ __( '- Select -', 'erp' ) } />
							<SelectField id="perf_co" label={ __( 'Communication', 'erp' ) } options={ RATING_OPTIONS } value={ form.communication } onChange={ set( 'communication' ) } placeholder={ __( '- Select -', 'erp' ) } />
							<SelectField id="perf_de" label={ __( 'Dependability', 'erp' ) } options={ RATING_OPTIONS } value={ form.dependablity } onChange={ set( 'dependablity' ) } placeholder={ __( '- Select -', 'erp' ) } />
						</>
					) : null }

					{ type === 'comments' ? (
						<>
							<SmartSelectField id="perf_reviewer" label={ __( 'Reviewer', 'erp' ) } required options={ reviewer.options } value={ form.reviewer } onChange={ set( 'reviewer' ) } onSearch={ reviewer.onSearch } loading={ reviewer.loading } placeholder={ __( '- Select -', 'erp' ) } searchPlaceholder={ __( 'Search employees…', 'erp' ) } />
							<TextareaField id="perf_comment" label={ __( 'Comment', 'erp' ) } value={ form.comments } onChange={ set( 'comments' ) } />
						</>
					) : null }

					{ type === 'goals' ? (
						<>
							<TextField id="perf_completion" label={ __( 'Completion Date', 'erp' ) } type="date" required value={ form.completion_date } onChange={ set( 'completion_date' ) } />
							<TextareaField id="perf_goal" label={ __( 'Goal Description', 'erp' ) } value={ form.goal_description } onChange={ set( 'goal_description' ) } />
							<TextareaField id="perf_emp_assess" label={ __( 'Employee Assessment', 'erp' ) } value={ form.employee_assessment } onChange={ set( 'employee_assessment' ) } />
							<SmartSelectField id="perf_supervisor" label={ __( 'Supervisor', 'erp' ) } required options={ supervisor.options } value={ form.supervisor } onChange={ set( 'supervisor' ) } onSearch={ supervisor.onSearch } loading={ supervisor.loading } placeholder={ __( '- Select -', 'erp' ) } searchPlaceholder={ __( 'Search employees…', 'erp' ) } />
							<TextareaField id="perf_sup_assess" label={ __( 'Supervisor Assessment', 'erp' ) } value={ form.supervisor_assessment } onChange={ set( 'supervisor_assessment' ) } />
						</>
					) : null }

					<DialogFooter className="gap-5 sm:gap-5">
						<Button type="button" variant="outline" className="h-10 px-6" disabled={ busy } onClick={ onClose }>
							{ __( 'Cancel', 'erp' ) }
						</Button>
						<Button type="submit" className="h-10 px-6" disabled={ busy }>
							{ busy ? __( 'Saving…', 'erp' ) : __( 'Save', 'erp' ) }
						</Button>
					</DialogFooter>
				</form>
			</DialogContent>
		</Dialog>
	);
}
