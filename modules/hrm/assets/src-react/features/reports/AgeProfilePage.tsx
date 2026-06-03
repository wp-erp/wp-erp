/**
 * `/reports/age-profile` — employee age breakdown by department.
 *
 * Mirrors views/reporting/age-profile.php: one row per department plus a Total
 * row, columns for each age band. Data from `GET /reports/age-profile`.
 */

import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { ReportShell, ReportState } from './ReportShell';
import type { AgeProfileResponse } from './types';
import { useReport } from './useReports';

const BANDS: ReadonlyArray< { key: keyof Omit< AgeProfileResponse['rows'][number], 'department' >; label: string } > = [
	{ key: 'under_18', label: __( 'Under 18', 'erp' ) },
	{ key: 'age_18_25', label: '18–25' },
	{ key: 'age_26_35', label: '26–35' },
	{ key: 'age_36_45', label: '36–45' },
	{ key: 'age_46_55', label: '46–55' },
	{ key: 'age_56_65', label: '56–65' },
	{ key: 'age_65_plus', label: __( '65+', 'erp' ) },
];

export function AgeProfilePage(): JSX.Element {
	const { data, loading, error } = useReport< AgeProfileResponse >( '/reports/age-profile' );
	const rows = data?.rows ?? [];

	return (
		<ReportShell title={ __( 'Age Profile', 'erp' ) }>
			<ReportState loading={ loading } error={ error } empty={ rows.length === 0 }>
				<div className="overflow-x-auto">
						<table className="w-full min-w-[40rem] text-left">
					<thead className="border-b border-border bg-muted/40">
						<tr className="h-10 text-xs font-medium uppercase tracking-normal text-muted-foreground">
							<th scope="col" className="px-4">{ __( 'Department', 'erp' ) }</th>
							{ BANDS.map( ( b ) => (
								<th key={ b.key } scope="col" className="px-4 text-right">{ b.label }</th>
							) ) }
						</tr>
					</thead>
					<tbody>
						{ rows.map( ( row, idx ) => {
							const isTotal = idx === rows.length - 1;
							return (
								<tr
									key={ `${ row.department }-${ idx }` }
									className={ [
										'h-12 border-b border-border last:border-b-0',
										isTotal ? 'bg-muted/30 font-semibold text-foreground' : 'hover:bg-muted/40',
									].join( ' ' ) }
								>
									<td className="px-4 align-middle font-medium text-foreground">{ row.department }</td>
									{ BANDS.map( ( b ) => (
										<td key={ b.key } className="px-4 text-right align-middle text-sm text-foreground">
											{ row[ b.key ] }
										</td>
									) ) }
								</tr>
							);
						} ) }
					</tbody>
				</table>
					</div>
			</ReportState>
		</ReportShell>
	);
}
