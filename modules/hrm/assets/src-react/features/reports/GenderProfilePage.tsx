/**
 * `/reports/gender-profile` — workforce gender ratio.
 *
 * Mirrors views/reporting/gender-profile.php: male / female / unspecified rows
 * plus a Total row, each with a count and percentage. Data from
 * `GET /reports/gender-profile`.
 */

import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { ReportShell, ReportState } from './ReportShell';
import type { GenderProfileResponse } from './types';
import { useReport } from './useReports';

export function GenderProfilePage(): JSX.Element {
	const { data, loading, error } = useReport< GenderProfileResponse >( '/reports/gender-profile' );
	const rows = data?.rows ?? [];

	return (
		<ReportShell title={ __( 'Gender Profile', 'erp' ) }>
			<ReportState
				loading={ loading }
				error={ error }
				empty={ rows.length === 0 }
				emptyText={ __( 'No employee data available.', 'erp' ) }
			>
				<div className="overflow-x-auto">
						<table className="w-full min-w-[40rem] text-left">
					<thead className="border-b border-border bg-muted/40">
						<tr className="h-10 text-xs font-medium uppercase tracking-normal text-muted-foreground">
							<th scope="col" className="px-4">{ __( 'Gender', 'erp' ) }</th>
							<th scope="col" className="px-4 text-right">{ __( 'Count', 'erp' ) }</th>
							<th scope="col" className="px-4 text-right">{ __( 'Percentage', 'erp' ) }</th>
						</tr>
					</thead>
					<tbody>
						{ rows.map( ( row, idx ) => {
							const isTotal = idx === rows.length - 1;
							return (
								<tr
									key={ `${ row.gender }-${ idx }` }
									className={ [
										'h-12 border-b border-border last:border-b-0',
										isTotal ? 'bg-muted/30 font-semibold text-foreground' : 'hover:bg-muted/40',
									].join( ' ' ) }
								>
									<td className="px-4 align-middle font-medium text-foreground">{ row.gender }</td>
									<td className="px-4 text-right align-middle text-sm text-foreground">{ row.count }</td>
									<td className="px-4 text-right align-middle text-sm text-foreground">{ row.percentage }</td>
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
