/**
 * Calendar chrome above the day grid: month navigation (prev/next/today + label),
 * department / designation filters (auto-apply), and the holiday/weekend/leave
 * legend. Presentational — all state lives in the page.
 */

import { Button } from '@wedevs/plugin-ui';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import type { LookupOption } from '../employees/filters/lookups';

interface CalendarToolbarProps {
	readonly monthLabel:           string;
	readonly departmentId:         number;
	readonly designationId:        number;
	readonly departments:          LookupOption[];
	readonly designations:         LookupOption[];
	readonly onPrev:               () => void;
	readonly onNext:               () => void;
	readonly onToday:              () => void;
	readonly onDepartmentChange:   ( id: number ) => void;
	readonly onDesignationChange:  ( id: number ) => void;
}

export function CalendarToolbar( {
	monthLabel,
	departmentId,
	designationId,
	departments,
	designations,
	onPrev,
	onNext,
	onToday,
	onDepartmentChange,
	onDesignationChange,
}: CalendarToolbarProps ): JSX.Element {
	return (
		<>
			{ /* Month nav */ }
			<div className="flex items-center justify-between gap-3 border-b border-border px-4 py-3">
				<div className="flex items-center gap-2">
					<button
						type="button"
						onClick={ onPrev }
						className="inline-flex size-8 items-center justify-center rounded-md border border-border bg-card text-foreground hover:bg-muted"
						aria-label={ __( 'Previous month', 'erp' ) }
					>
						<ChevronLeft size={ 16 } aria-hidden="true" />
					</button>
					<button
						type="button"
						onClick={ onNext }
						className="inline-flex size-8 items-center justify-center rounded-md border border-border bg-card text-foreground hover:bg-muted"
						aria-label={ __( 'Next month', 'erp' ) }
					>
						<ChevronRight size={ 16 } aria-hidden="true" />
					</button>
					<span className="ml-2 text-base font-semibold text-foreground">{ monthLabel }</span>
				</div>
				<div className="flex items-center gap-3">
					<Button variant="outline" className="h-9 px-4 text-sm" onClick={ onToday }>
						{ __( 'Today', 'erp' ) }
					</Button>
				</div>
			</div>

			{ /* Department / Designation filters (auto-apply) */ }
			<div className="flex flex-wrap items-center gap-3 border-b border-border px-4 py-3">
				<select
					value={ departmentId }
					onChange={ ( e ) => onDepartmentChange( Number( e.target.value ) ) }
					aria-label={ __( 'Filter by department', 'erp' ) }
					className="h-9 rounded-md border border-border bg-card px-3 text-sm text-foreground"
				>
					<option value={ 0 }>{ __( 'All Departments', 'erp' ) }</option>
					{ departments.map( ( d ) => (
						<option key={ d.id } value={ d.id }>{ d.title }</option>
					) ) }
				</select>
				<select
					value={ designationId }
					onChange={ ( e ) => onDesignationChange( Number( e.target.value ) ) }
					aria-label={ __( 'Filter by designation', 'erp' ) }
					className="h-9 rounded-md border border-border bg-card px-3 text-sm text-foreground"
				>
					<option value={ 0 }>{ __( 'All Designations', 'erp' ) }</option>
					{ designations.map( ( d ) => (
						<option key={ d.id } value={ d.id }>{ d.title }</option>
					) ) }
				</select>
			</div>

			{ /* Legend */ }
			<div className="flex flex-wrap items-center gap-4 border-b border-border px-4 py-2 text-xs text-muted-foreground">
				<span className="inline-flex items-center gap-1.5">
					<span aria-hidden="true" className="inline-block size-2.5 rounded-full" style={ { backgroundColor: '#FF5354' } } />
					{ __( 'Holiday', 'erp' ) }
				</span>
				<span className="inline-flex items-center gap-1.5">
					<span aria-hidden="true" className="inline-block size-2.5 rounded-full bg-muted-foreground/40" />
					{ __( 'Weekend', 'erp' ) }
				</span>
				<span className="inline-flex items-center gap-1.5">
					<span aria-hidden="true" className="inline-block size-2.5 rounded-full bg-primary" />
					{ __( 'Leave', 'erp' ) }
				</span>
			</div>
		</>
	);
}
