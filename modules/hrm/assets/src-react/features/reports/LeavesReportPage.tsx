/**
 * `/reports/leaves` — employee-based leave matrix.
 *
 * Mirrors the legacy `LeaveReportEmployeeBased` WP_List_Table: rows are active
 * employees, columns are leave policies, each cell shows spent / entitled days.
 * Filters (financial year, designation, department, employment type, custom
 * date range) and pagination follow the Employees-table conventions. Data from
 * `GET /reports/leaves` (+ `/reports/leaves/form-options` for the pickers).
 */

import { Input, SmartSelect } from '@wedevs/plugin-ui';
import { Filter } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { OrgPagination } from '../org/OrgPagination';
import { ReportNameCell } from './ReportNameCell';
import { ReportShell, ReportState } from './ReportShell';
import type { LeaveReportFormOptions } from './types';
import { useLeaveReport } from './useReports';

function fmtCell( spent: number, days: number ): string {
	return `${ spent } / ${ days }`;
}

export function LeavesReportPage(): JSX.Element {
	const [ options, setOptions ] = useState< LeaveReportFormOptions | null >( null );

	const [ year, setYear ]                     = useState( '' );
	const [ designation, setDesignation ]       = useState( 0 );
	const [ department, setDepartment ]         = useState( 0 );
	const [ employmentType, setEmploymentType ] = useState( '' );
	const [ start, setStart ]                   = useState( '' );
	const [ end, setEnd ]                       = useState( '' );
	const [ showFilters, setShowFilters ]       = useState( false );
	const [ page, setPage ]                     = useState( 1 );
	const [ perPage, setPerPage ]               = useState( 20 );

	const { data, total, loading, error, loadOptions } = useLeaveReport( {
		filter_year:            year,
		filter_designation:     designation,
		filter_department:      department,
		filter_employment_type: employmentType,
		start,
		end,
		page,
		perPage,
	} );

	// Load filter pickers once, then default the year to the current financial year.
	useEffect( () => {
		let active = true;
		void loadOptions().then( ( opts ) => {
			if ( ! active ) {
				return;
			}
			setOptions( opts );
			if ( opts.current_f_year ) {
				setYear( String( opts.current_f_year ) );
			}
		} );
		return () => {
			active = false;
		};
	}, [ loadOptions ] );

	useEffect( () => {
		setPage( 1 );
	}, [ year, designation, department, employmentType, start, end ] );

	const totalPages = Math.max( 1, Math.ceil( total / perPage ) );

	const yearOptions = useMemo( () => {
		const list: Array< { value: string; label: string } > = [ { value: '', label: __( 'Select year', 'erp' ) } ];
		( options?.financial_years ?? [] ).forEach( ( fy ) => list.push( { value: String( fy.id ), label: fy.label } ) );
		list.push( { value: 'custom', label: __( 'Custom', 'erp' ) } );
		return list;
	}, [ options ] );

	const designationOptions = useMemo(
		() => [
			{ value: '', label: __( 'All Designations', 'erp' ) },
			...( options?.designations ?? [] ).map( ( d ) => ( { value: String( d.id ), label: d.label } ) ),
		],
		[ options ]
	);

	const departmentOptions = useMemo(
		() => [
			{ value: '', label: __( 'All Departments', 'erp' ) },
			...( options?.departments ?? [] ).map( ( d ) => ( { value: String( d.id ), label: d.label } ) ),
		],
		[ options ]
	);

	const typeOptions = useMemo(
		() => [
			{ value: '', label: __( 'All Types', 'erp' ) },
			...( options?.employment_types ?? [] ).map( ( t ) => ( { value: t.value, label: t.label } ) ),
		],
		[ options ]
	);

	const activeFilterCount =
		( designation ? 1 : 0 ) + ( department ? 1 : 0 ) + ( employmentType ? 1 : 0 ) + ( year === 'custom' ? 1 : 0 );
	const filterButtonActive = showFilters || activeFilterCount > 0;

	const columns = data?.columns ?? [];
	const rows = data?.rows ?? [];

	const toolbar = (
		<div className="space-y-3">
			<div className="flex flex-wrap items-center justify-between gap-3">
				<label className="flex items-center gap-2 text-sm text-muted-foreground">
					{ __( 'Financial Year', 'erp' ) }
					<SmartSelect
						options={ yearOptions }
						value={ year }
						onValueChange={ setYear }
						placeholder={ __( 'Select year', 'erp' ) }
						className="h-9 w-44"
						contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
					/>
				</label>
				<button
					type="button"
					aria-label={ __( 'Toggle filters', 'erp' ) }
					aria-pressed={ filterButtonActive }
					onClick={ () => setShowFilters( ( prev ) => ! prev ) }
					className={ [
						'inline-flex h-9 items-center gap-2 rounded-md border bg-card px-3 text-sm font-medium transition-colors',
						filterButtonActive ? 'border-primary text-primary' : 'border-border text-muted-foreground hover:text-foreground',
					].join( ' ' ) }
				>
					<Filter size={ 16 } strokeWidth={ 1.75 } aria-hidden="true" />
					<span>{ __( 'Filter', 'erp' ) }</span>
					{ activeFilterCount > 0 ? (
						<span className="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1.5 text-xs font-medium text-primary-foreground">
							{ activeFilterCount }
						</span>
					) : null }
				</button>
			</div>
			{ filterButtonActive ? (
				<div className="flex flex-wrap items-center gap-2 rounded-md border border-border bg-muted/20 p-3">
					{ year === 'custom' ? (
						<>
							<label className="flex items-center gap-2 text-sm text-muted-foreground">
								{ __( 'From', 'erp' ) }
								<Input
									type="date"
									value={ start }
									onChange={ ( e ) => setStart( e.target.value ) }
									className="h-9 w-40 border-border text-sm"
								/>
							</label>
							<label className="flex items-center gap-2 text-sm text-muted-foreground">
								{ __( 'To', 'erp' ) }
								<Input
									type="date"
									value={ end }
									onChange={ ( e ) => setEnd( e.target.value ) }
									className="h-9 w-40 border-border text-sm"
								/>
							</label>
						</>
					) : null }
					<label className="flex items-center gap-2 text-sm text-muted-foreground">
						{ __( 'Designation', 'erp' ) }
						<SmartSelect
							options={ designationOptions }
							value={ String( designation || '' ) }
							onValueChange={ ( v ) => setDesignation( Number( v || 0 ) ) }
							placeholder={ __( 'All Designations', 'erp' ) }
							showClear
							className="h-9 w-48"
							contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
						/>
					</label>
					<label className="flex items-center gap-2 text-sm text-muted-foreground">
						{ __( 'Department', 'erp' ) }
						<SmartSelect
							options={ departmentOptions }
							value={ String( department || '' ) }
							onValueChange={ ( v ) => setDepartment( Number( v || 0 ) ) }
							placeholder={ __( 'All Departments', 'erp' ) }
							showClear
							className="h-9 w-48"
							contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
						/>
					</label>
					<label className="flex items-center gap-2 text-sm text-muted-foreground">
						{ __( 'Employment Type', 'erp' ) }
						<SmartSelect
							options={ typeOptions }
							value={ employmentType }
							onValueChange={ setEmploymentType }
							placeholder={ __( 'All Types', 'erp' ) }
							showClear
							className="h-9 w-44"
							contentClassName="!w-[var(--popover-anchor-width,var(--anchor-width))]"
						/>
					</label>
				</div>
			) : null }
		</div>
	);

	return (
		<ReportShell title={ __( 'Leave Report', 'erp' ) } toolbar={ toolbar }>
			<ReportState
				loading={ loading }
				error={ error }
				empty={ rows.length === 0 }
				emptyText={ __( 'No record found.', 'erp' ) }
			>
				<div className="rounded-lg border border-border bg-card shadow-sm">
					<div className="overflow-x-auto">
						<table className="w-full text-left">
					<thead className="border-b border-border bg-muted/40">
						<tr className="h-10 text-xs font-medium uppercase tracking-normal text-muted-foreground">
							<th scope="col" className="sticky left-0 z-20 whitespace-nowrap bg-muted/40 px-2">{ __( 'Name', 'erp' ) }</th>
							{ columns.map( ( col ) => (
								<th key={ col.leave_id } scope="col" className="whitespace-nowrap px-2 text-right">{ col.name }</th>
							) ) }
						</tr>
					</thead>
					<tbody>
						{ rows.map( ( row ) => (
							<tr key={ row.user_id } className="group h-18 border-b border-border last:border-b-0 hover:bg-muted/40">
								<td className="sticky left-0 z-10 whitespace-nowrap bg-card px-2 align-middle font-medium text-foreground group-hover:bg-muted/40">
									{ row.name ? (
										<ReportNameCell name={ row.name } avatar={ row.avatar } />
									) : (
										<span className="text-muted-foreground">—</span>
									) }
								</td>
								{ columns.map( ( col ) => {
									const cell = row.cells[ String( col.leave_id ) ];
									return (
										<td key={ col.leave_id } className="whitespace-nowrap px-2 text-right align-middle text-sm tabular-nums text-foreground">
											{ cell ? fmtCell( cell.spent, cell.days ) : '—' }
										</td>
									);
								} ) }
							</tr>
						) ) }
					</tbody>
				</table>
					</div>

					{ total > 0 ? (
						<OrgPagination
							page={ page }
							totalPages={ totalPages }
							total={ total }
							perPage={ perPage }
							onPage={ setPage }
							onPerPage={ ( n ) => { setPerPage( n ); setPage( 1 ); } }
						/>
					) : null }
				</div>
			</ReportState>
		</ReportShell>
	);
}
