/**
 * Sortable department list table: select-all / per-row checkboxes, sortable
 * column headers, an avatar stack for employee counts, and a per-row
 * Edit / Delete action menu. Presentational — all state and handlers come from
 * the page orchestrator.
 */

import {
	Checkbox,
} from '@wedevs/plugin-ui';
import { Pencil, Trash2 } from 'lucide-react';
import type { JSX } from 'react';
import { Link } from 'react-router-dom';

import { EmployeeAvatarStack } from '@/shared/components/EmployeeAvatarStack';
import { RowActionsMenu } from '@/shared/components/RowActionsMenu';
import { SortHeader } from '@/shared/components/SortHeader';
import { __, sprintf } from '@/shared/i18n';

import type { Department } from './types';

export type SortKey = 'title' | 'lead_name' | 'parent_title' | 'total_employees';

interface DepartmentsTableProps {
	readonly rows:         readonly Department[];
	readonly canManage:    boolean;
	/** Tree depth per department id — drives the hierarchical name indentation. */
	readonly depthOf?:     ( id: number ) => number;
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
	depthOf,
	selected,
	allChecked,
	sort,
	onToggleAll,
	onToggleOne,
	onToggleSort,
	onEdit,
	onDelete,
}: DepartmentsTableProps ): JSX.Element {
	return (
		<div className="overflow-x-auto">
			<table className="w-full min-w-160 text-left">
				<thead className="border-b border-border bg-card">
					<tr className="h-10">
						{ canManage ? (
							<th scope="col" className="w-10 px-4">
								<Checkbox checked={ allChecked } onCheckedChange={ onToggleAll } aria-label={ __( 'Select all', 'erp' ) } />
							</th>
						) : null }
						<SortHeader label={ __( 'Name', 'erp' ) } sortKey="title" orderBy={ sort.key } order={ sort.dir } onSort={ onToggleSort } first={ false } />
						<SortHeader label={ __( 'Head', 'erp' ) } sortKey="lead_name" orderBy={ sort.key } order={ sort.dir } onSort={ onToggleSort } first={ false } />
						<SortHeader label={ __( 'Parent', 'erp' ) } sortKey="parent_title" orderBy={ sort.key } order={ sort.dir } onSort={ onToggleSort } first={ false } />
						<SortHeader label={ __( 'Employees', 'erp' ) } sortKey="total_employees" orderBy={ sort.key } order={ sort.dir } onSort={ onToggleSort } first={ false } />
						<th scope="col" className="w-20 px-4">
							<span className="sr-only">{ __( 'Actions', 'erp' ) }</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{ rows.map( ( dept ) => (
						<tr key={ dept.id } className="h-18 border-b border-border bg-card last:border-b-0 hover:bg-muted/40">
							{ canManage ? (
								<td className="px-4 align-middle">
									<Checkbox checked={ selected.has( dept.id ) } onCheckedChange={ () => onToggleOne( dept.id ) } aria-label={ sprintf( __( 'Select %s', 'erp' ), dept.title ) } />
								</td>
							) : null }
							<td className="px-2 align-middle text-sm">
								<div style={ { paddingLeft: `${ ( depthOf?.( dept.id ) ?? 0 ) * 20 }px` } }>
									<Link
										to={ `/employees?department_id=${ dept.id }` }
										className="font-medium text-foreground hover:text-primary hover:underline"
									>
										{ ( depthOf?.( dept.id ) ?? 0 ) > 0 ? <span className="mr-1 text-muted-foreground" aria-hidden="true">└</span> : null }
										{ dept.title }
									</Link>
									{ dept.description ? (
										<div className="truncate text-xs text-muted-foreground">{ dept.description }</div>
									) : null }
								</div>
							</td>
							<td className="px-2 align-middle text-sm text-foreground">
								{ dept.lead_name && dept.lead
									? (
										<Link to={ `/employees/${ dept.lead }` } className="text-foreground hover:text-primary hover:underline">
											{ dept.lead_name }
										</Link>
									)
									: ( dept.lead_name || <span className="text-muted-foreground">—</span> ) }
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
										<RowActionsMenu
											label={ sprintf( __( 'Actions for %s', 'erp' ), dept.title ) }
											actions={ [
												{ id: 'edit', label: __( 'Edit', 'erp' ), icon: Pencil, onSelect: () => onEdit( dept ) },
												{ id: 'delete', label: __( 'Delete', 'erp' ), icon: Trash2, onSelect: () => onDelete( dept ), variant: 'destructive' },
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
