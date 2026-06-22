/**
 * Sortable department list table: select-all / per-row checkboxes, sortable
 * column headers, an avatar stack for employee counts, and a per-row
 * Edit / Delete action menu. Presentational — all state and handlers come from
 * the page orchestrator.
 */

import {
	Button,
	Checkbox,
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuItem,
	DropdownMenuTrigger,
} from '@wedevs/plugin-ui';
import { ArrowDown, ArrowUp, ArrowUpDown, MoreVertical, Pencil, Trash2 } from 'lucide-react';
import type { JSX } from 'react';

import { EmployeeAvatarStack } from '@/shared/components/EmployeeAvatarStack';
import { __, sprintf } from '@/shared/i18n';

import type { Department } from './types';

export type SortKey = 'title' | 'lead_name' | 'parent_title' | 'total_employees';

interface DepartmentsTableProps {
	readonly rows:         readonly Department[];
	readonly canManage:    boolean;
	readonly selected:     ReadonlySet< number >;
	readonly allChecked:   boolean;
	readonly sort:         { key: SortKey; dir: 'asc' | 'desc' };
	readonly onToggleAll:  () => void;
	readonly onToggleOne:  ( id: number ) => void;
	readonly onToggleSort: ( key: SortKey ) => void;
	readonly onEdit:       ( department: Department ) => void;
	readonly onDelete:     ( department: Department ) => void;
}

export function DepartmentsTable( {
	rows,
	canManage,
	selected,
	allChecked,
	sort,
	onToggleAll,
	onToggleOne,
	onToggleSort,
	onEdit,
	onDelete,
}: DepartmentsTableProps ): JSX.Element {
	function sortIcon( key: SortKey ): JSX.Element {
		if ( sort.key !== key ) {
			return <ArrowUpDown size={ 12 } aria-hidden="true" />;
		}
		return sort.dir === 'asc'
			? <ArrowUp size={ 12 } aria-hidden="true" />
			: <ArrowDown size={ 12 } aria-hidden="true" />;
	}

	return (
		<table className="w-full text-left">
			<thead className="border-b border-border bg-muted/40">
				<tr className="h-10 text-xs font-medium uppercase tracking-normal text-muted-foreground">
					{ canManage ? (
						<th scope="col" className="w-10 px-2">
							<Checkbox checked={ allChecked } onCheckedChange={ onToggleAll } aria-label={ __( 'Select all', 'erp' ) } />
						</th>
					) : null }
					<th scope="col" className="px-2">
						<button type="button" onClick={ () => onToggleSort( 'title' ) } className="inline-flex items-center gap-1 uppercase hover:text-foreground">
							{ __( 'Name', 'erp' ) }{ sortIcon( 'title' ) }
						</button>
					</th>
					<th scope="col" className="px-2">
						<button type="button" onClick={ () => onToggleSort( 'lead_name' ) } className="inline-flex items-center gap-1 uppercase hover:text-foreground">
							{ __( 'Head', 'erp' ) }{ sortIcon( 'lead_name' ) }
						</button>
					</th>
					<th scope="col" className="px-2">
						<button type="button" onClick={ () => onToggleSort( 'parent_title' ) } className="inline-flex items-center gap-1 uppercase hover:text-foreground">
							{ __( 'Parent', 'erp' ) }{ sortIcon( 'parent_title' ) }
						</button>
					</th>
					<th scope="col" className="px-2">
						<button type="button" onClick={ () => onToggleSort( 'total_employees' ) } className="inline-flex items-center gap-1 uppercase hover:text-foreground">
							{ __( 'Employees', 'erp' ) }{ sortIcon( 'total_employees' ) }
						</button>
					</th>
					<th scope="col" className="w-20 px-4">
						<span className="sr-only">{ __( 'Actions', 'erp' ) }</span>
					</th>
				</tr>
			</thead>
			<tbody>
				{ rows.map( ( dept ) => (
					<tr key={ dept.id } className="h-18 border-b border-border last:border-b-0 hover:bg-muted/40">
						{ canManage ? (
							<td className="px-2 align-middle">
								<Checkbox checked={ selected.has( dept.id ) } onCheckedChange={ () => onToggleOne( dept.id ) } aria-label={ sprintf( __( 'Select %s', 'erp' ), dept.title ) } />
							</td>
						) : null }
						<td className="px-2 align-middle">
							<div className="font-medium text-foreground">{ dept.title }</div>
							{ dept.description ? (
								<div className="truncate text-xs text-muted-foreground">{ dept.description }</div>
							) : null }
						</td>
						<td className="px-2 align-middle text-sm text-foreground">
							{ dept.lead_name || <span className="text-muted-foreground">—</span> }
						</td>
						<td className="px-2 align-middle text-sm text-foreground">
							{ dept.parent_title || <span className="text-muted-foreground">—</span> }
						</td>
						<td className="px-2 align-middle text-sm text-foreground">
							<EmployeeAvatarStack people={ dept.employees } total={ dept.total_employees } />
						</td>
						<td className="px-4 align-middle">
							{ canManage ? (
								<div className="flex justify-end">
									<DropdownMenu>
										<DropdownMenuTrigger
											render={
												<Button variant="ghost" size="icon" aria-label={ sprintf( __( 'Actions for %s', 'erp' ), dept.title ) }>
													<MoreVertical size={ 16 } aria-hidden="true" />
												</Button>
											}
										/>
										<DropdownMenuContent align="end" className="min-w-44">
											<DropdownMenuItem className="gap-2" onClick={ () => onEdit( dept ) }>
												<Pencil size={ 14 } aria-hidden="true" />
												{ __( 'Edit', 'erp' ) }
											</DropdownMenuItem>
											<DropdownMenuItem
												variant="destructive"
												className="gap-2"
												onClick={ () => onDelete( dept ) }
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
	);
}
