/**
 * `/reports/salary-history` — compensation history of active employees.
 *
 * Mirrors views/reporting/salary-history.php: one row per compensation entry,
 * the employee name shown only on the first row of each employee's block. Data
 * from `GET /reports/salary-history`.
 */

import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import { formatDisplayDate } from '@/shared/utils/date';

import { ReportNameCell } from './ReportNameCell';
import { ReportShell, ReportState } from './ReportShell';
import type { SalaryHistoryResponse } from './types';
import { useReport } from './useReports';

function fmtDate( value: string | null ): string {
	return formatDisplayDate( value, ( value ?? '' ).slice( 0, 10 ) || '—' );
}

export function SalaryHistoryPage(): JSX.Element {
	const { data, loading, error } = useReport< SalaryHistoryResponse >( '/reports/salary-history' );
	const rows = data?.rows ?? [];

	return (
		<ReportShell title={ __( 'Salary History', 'erp' ) }>
			<ReportState
				loading={ loading }
				error={ error }
				empty={ rows.length === 0 }
				emptyText={ __( 'No employee found.', 'erp' ) }
			>
				<div className="overflow-hidden rounded-lg border border-border bg-card shadow-sm">
					<div className="overflow-x-auto">
						<table className="w-full min-w-160 text-left">
					<thead className="border-b border-border bg-card">
						<tr className="h-10 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">
							<th scope="col" className="px-4">{ __( 'Employee', 'erp' ) }</th>
							<th scope="col" className="px-2">{ __( 'Date', 'erp' ) }</th>
							<th scope="col" className="px-2">{ __( 'Pay Rate', 'erp' ) }</th>
							<th scope="col" className="px-2">{ __( 'Pay Type', 'erp' ) }</th>
							<th scope="col" className="px-2">{ __( 'Employee ID', 'erp' ) }</th>
						</tr>
					</thead>
					<tbody>
						{ rows.map( ( row, idx ) => (
							<tr key={ `${ row.user_id }-${ idx }` } className="h-18 border-b border-border bg-card last:border-b-0 hover:bg-muted/40">
								<td className="px-4 align-middle font-medium text-foreground">
									{ row.name ? <ReportNameCell name={ row.name } avatar={ row.avatar } /> : <span aria-hidden="true">&nbsp;</span> }
								</td>
								<td className="whitespace-nowrap px-2 align-middle text-sm text-muted-foreground">{ fmtDate( row.date ) }</td>
								<td className="px-2 align-middle text-sm text-foreground">{ row.pay_rate ?? '—' }</td>
								<td className="px-2 align-middle text-sm text-foreground">{ row.pay_type ?? '—' }</td>
								<td className="px-2 align-middle text-sm text-muted-foreground">{ row.employee_id ?? '—' }</td>
							</tr>
						) ) }
					</tbody>
				</table>
					</div>
				</div>
			</ReportState>
		</ReportShell>
	);
}
