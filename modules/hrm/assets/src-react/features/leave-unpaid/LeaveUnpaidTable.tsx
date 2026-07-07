/**
 * Unpaid Leaves data table — one row per unpaid leave with an inline,
 * manager-editable per-day amount and a computed total. Presentational; the
 * page owns loading/filter state and supplies the rows + amount handler.
 */

import { Input } from '@wedevs/plugin-ui';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

export interface UnpaidRow {
	readonly id:            number;
	readonly user_id:       number;
	readonly employee_name: string;
	readonly policy_name:   string;
	readonly days:          number;
	readonly f_year:        string;
	readonly start_date:    string;
	readonly end_date:      string;
	readonly amount:        number;
	readonly total:         number;
}

interface LeaveUnpaidTableProps {
	readonly rows:           readonly UnpaidRow[];
	readonly canManage:      boolean;
	readonly onAmountChange: ( id: number, value: string ) => void;
}

export function LeaveUnpaidTable( { rows, canManage, onAmountChange }: LeaveUnpaidTableProps ): JSX.Element {
	return (
		<div className="overflow-x-auto">
			<table className="w-full min-w-3xl text-left">
				<thead className="border-b border-border bg-card">
					<tr className="h-10 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">
						<th scope="col" className="px-4">{ __( 'Employee', 'erp' ) }</th>
						<th scope="col" className="px-2">{ __( 'Policy', 'erp' ) }</th>
						<th scope="col" className="px-2">{ __( 'Days', 'erp' ) }</th>
						<th scope="col" className="px-2">{ __( 'Year', 'erp' ) }</th>
						<th scope="col" className="px-2">{ __( 'Start', 'erp' ) }</th>
						<th scope="col" className="px-2">{ __( 'End', 'erp' ) }</th>
						<th scope="col" className="px-2">{ __( 'Amount/day', 'erp' ) }</th>
						<th scope="col" className="px-2">{ __( 'Total', 'erp' ) }</th>
					</tr>
				</thead>
				<tbody>
					{ rows.map( ( r ) => (
						<tr key={ r.id } className="h-18 border-b border-border bg-card last:border-b-0 hover:bg-muted/40">
							<td className="px-4 align-middle text-sm font-medium text-foreground">{ r.employee_name }</td>
							<td className="px-2 align-middle text-sm text-muted-foreground">{ r.policy_name }</td>
							<td className="px-2 align-middle text-sm text-foreground">{ r.days }</td>
							<td className="px-2 align-middle text-sm text-muted-foreground">{ r.f_year }</td>
							<td className="px-2 align-middle text-sm text-muted-foreground">{ r.start_date }</td>
							<td className="px-2 align-middle text-sm text-muted-foreground">{ r.end_date }</td>
							<td className="px-2 align-middle">
								<Input
									type="number"
									min="0"
									step="0.01"
									defaultValue={ String( r.amount ) }
									disabled={ ! canManage }
									onBlur={ ( e ) => onAmountChange( r.id, e.target.value ) }
									aria-label={ __( 'Amount per day', 'erp' ) }
									className="h-10 w-28 rounded-md border border-border bg-background px-4 text-sm focus:border-primary focus:outline-none"
								/>
							</td>
							<td className="px-2 align-middle text-sm font-medium text-foreground">{ r.total }</td>
						</tr>
					) ) }
				</tbody>
			</table>
		</div>
	);
}
