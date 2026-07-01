/**
 * Leave-policies list table — colour swatch + scope columns and a per-row
 * Edit / Duplicate / Delete action menu. Presentational; all row actions are
 * delegated back to the page via callbacks.
 */

import {
	Button,
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuItem,
	DropdownMenuTrigger,
} from '@wedevs/plugin-ui';
import { Copy, MoreVertical, Pencil, Trash2 } from 'lucide-react';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';

import type { LeavePolicyListRow } from './types';

interface LeavePoliciesTableProps {
	readonly rows:        readonly LeavePolicyListRow[];
	readonly canManage:   boolean;
	readonly onEdit:      ( row: LeavePolicyListRow ) => void;
	readonly onDuplicate: ( row: LeavePolicyListRow ) => void;
	readonly onDelete:    ( row: LeavePolicyListRow ) => void;
}

export function LeavePoliciesTable( {
	rows,
	canManage,
	onEdit,
	onDuplicate,
	onDelete,
}: LeavePoliciesTableProps ): JSX.Element {
	return (
		<div className="overflow-x-auto">
			<table className="w-full min-w-[40rem] text-left">
			<thead className="border-b border-border bg-card">
				<tr className="h-10 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">
					<th scope="col" className="px-4">{ __( 'Name', 'erp' ) }</th>
					<th scope="col" className="px-2">{ __( 'Days', 'erp' ) }</th>
					<th scope="col" className="px-2">{ __( 'Department', 'erp' ) }</th>
					<th scope="col" className="px-2">{ __( 'Designation', 'erp' ) }</th>
					<th scope="col" className="px-2">{ __( 'Type', 'erp' ) }</th>
					<th scope="col" className="px-2">{ __( 'Year', 'erp' ) }</th>
					<th scope="col" className="w-20 px-4">
						<span className="sr-only">{ __( 'Actions', 'erp' ) }</span>
					</th>
				</tr>
			</thead>
			<tbody>
				{ rows.map( ( policy ) => (
					<tr key={ policy.id } className="h-18 border-b border-border bg-card last:border-b-0 hover:bg-muted/40">
						<td className="px-4 align-middle text-sm">
							<div className="flex items-center gap-2">
								<span
									aria-hidden="true"
									className="inline-block size-3 shrink-0 rounded-full"
									style={ { backgroundColor: policy.color || 'transparent' } }
								/>
								<span className="font-medium text-foreground">{ policy.name }</span>
							</div>
						</td>
						<td className="px-2 align-middle text-sm text-foreground">{ policy.days }</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">{ policy.department }</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">{ policy.designation }</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">{ policy.employee_type }</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">{ policy.f_year }</td>
						<td className="px-4 align-middle">
							{ canManage ? (
								<div className="flex justify-end">
									<DropdownMenu>
										<DropdownMenuTrigger
											render={
												<Button variant="ghost" size="icon" aria-label={ sprintf( __( 'Actions for %s', 'erp' ), policy.name ) }>
													<MoreVertical size={ 16 } aria-hidden="true" />
												</Button>
											}
										/>
										<DropdownMenuContent align="end" className="min-w-44">
											<DropdownMenuItem className="gap-2" onClick={ () => onEdit( policy ) }>
												<Pencil size={ 14 } aria-hidden="true" />
												{ __( 'Edit', 'erp' ) }
											</DropdownMenuItem>
											<DropdownMenuItem className="gap-2" onClick={ () => onDuplicate( policy ) }>
												<Copy size={ 14 } aria-hidden="true" />
												{ __( 'Duplicate', 'erp' ) }
											</DropdownMenuItem>
											<DropdownMenuItem
												variant="destructive"
												className="gap-2"
												onClick={ () => onDelete( policy ) }
											>
												<Trash2 size={ 14 } aria-hidden="true" />
												{ __( 'Delete', 'erp' ) }
											</DropdownMenuItem>
										</DropdownMenuContent>
									</DropdownMenu>
								</div>
							) : null }
						</td>
					</tr>
				) ) }
			</tbody>
		</table>
		</div>
	);
}
