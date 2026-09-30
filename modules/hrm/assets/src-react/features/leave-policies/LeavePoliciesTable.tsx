/**
 * Leave-policies list table — colour swatch + scope columns and a per-row
 * Edit / Duplicate / Delete action menu. Presentational; all row actions are
 * delegated back to the page via callbacks.
 */

import { Copy, Pencil, Trash2 } from 'lucide-react';
import type { JSX } from 'react';

import { RowActionsMenu } from '@/shared/components/RowActionsMenu';
import { SortHeader } from '@/shared/components/SortHeader';
import { __, sprintf } from '@/shared/i18n';

import type { LeavePolicyListRow } from './types';

/** Columns the v2 endpoint can sort on. */
export type PolicySortKey = 'name' | 'days';

interface LeavePoliciesTableProps {
	readonly rows:        readonly LeavePolicyListRow[];
	readonly canManage:   boolean;
	readonly sort:        { key: PolicySortKey; dir: 'asc' | 'desc' };
	readonly onToggleSort: ( key: PolicySortKey ) => void;
	readonly onEdit:      ( row: LeavePolicyListRow ) => void;
	readonly onDuplicate: ( row: LeavePolicyListRow ) => void;
	readonly onDelete:    ( row: LeavePolicyListRow ) => void;
}

export function LeavePoliciesTable( {
	rows,
	canManage,
	sort,
	onToggleSort,
	onEdit,
	onDuplicate,
	onDelete,
}: LeavePoliciesTableProps ): JSX.Element {
	return (
		<div className="overflow-x-auto">
			<table className="w-full min-w-160 text-left">
			<thead className="border-b border-border bg-card">
				<tr className="h-10">
					<SortHeader label={ __( 'Name', 'erp' ) } sortKey="name" orderBy={ sort.key } order={ sort.dir } onSort={ onToggleSort } first />
					<SortHeader label={ __( 'Days', 'erp' ) } sortKey="days" orderBy={ sort.key } order={ sort.dir } onSort={ onToggleSort } first={ false } />
					<th scope="col" className="whitespace-nowrap px-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">{ __( 'Department', 'erp' ) }</th>
					<th scope="col" className="whitespace-nowrap px-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">{ __( 'Designation', 'erp' ) }</th>
					<th scope="col" className="whitespace-nowrap px-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">{ __( 'Location', 'erp' ) }</th>
					<th scope="col" className="whitespace-nowrap px-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">{ __( 'Type', 'erp' ) }</th>
					<th scope="col" className="whitespace-nowrap px-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">{ __( 'Gender', 'erp' ) }</th>
					<th scope="col" className="whitespace-nowrap px-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">{ __( 'Marital', 'erp' ) }</th>
					<th scope="col" className="whitespace-nowrap px-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">{ __( 'Year', 'erp' ) }</th>
					<th scope="col" className="whitespace-nowrap px-2 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">{ __( 'Description', 'erp' ) }</th>
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
						<td className="px-2 align-middle text-sm text-muted-foreground">{ policy.location }</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">{ policy.employee_type }</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">{ policy.gender }</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">{ policy.marital }</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">{ policy.f_year }</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">
							{ policy.description ? <span className="line-clamp-1">{ policy.description }</span> : <span className="text-muted-foreground">—</span> }
						</td>
						<td className="px-4 align-middle">
							{ canManage ? (
								<div className="flex justify-end">
									<RowActionsMenu
										label={ sprintf( __( 'Actions for %s', 'erp' ), policy.name ) }
										actions={ [
											{ id: 'edit', label: __( 'Edit', 'erp' ), icon: Pencil, onSelect: () => onEdit( policy ) },
											{ id: 'duplicate', label: __( 'Duplicate', 'erp' ), icon: Copy, onSelect: () => onDuplicate( policy ) },
											{ id: 'delete', label: __( 'Delete', 'erp' ), icon: Trash2, onSelect: () => onDelete( policy ), variant: 'destructive' },
										] }
									/>
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
