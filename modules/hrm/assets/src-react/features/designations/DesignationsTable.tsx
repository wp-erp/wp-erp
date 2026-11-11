/**
 * Designations list table: sortable Name / Employees columns, per-page select
 * checkboxes and a row actions menu (Edit / Delete). Pure presentation — all
 * state and handlers come from `DesignationsPage`.
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

import type { Designation } from './types';

export type SortKey = 'title' | 'total_employees';

interface DesignationsTableProps {
	readonly rows:         readonly Designation[];
	readonly canManage:    boolean;
	readonly selected:     ReadonlySet< number >;
	readonly allChecked:   boolean;
	readonly sort:         { key: SortKey; dir: 'asc' | 'desc' };
	readonly onToggleAll:  () => void;
	readonly onToggleOne:  ( id: number ) => void;
	readonly onToggleSort: ( key: SortKey ) => void;
	readonly onEdit:       ( designation: Designation ) => void;
	readonly onDelete:     ( designation: Designation ) => void;
}

export function DesignationsTable( {
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
}: DesignationsTableProps ): JSX.Element {
	return (
		<div className="overflow-x-auto">
			<table className="w-full min-w-120 text-left">
				<thead className="border-b border-border bg-card">
					<tr className="h-10">
						{ canManage ? (
							<th scope="col" className="w-10 px-4">
								<Checkbox checked={ allChecked } onCheckedChange={ onToggleAll } aria-label={ __( 'Select all', 'erp' ) } />
							</th>
						) : null }
						<SortHeader label={ __( 'Name', 'erp' ) } sortKey="title" orderBy={ sort.key } order={ sort.dir } onSort={ onToggleSort } first={ false } />
						<SortHeader label={ __( 'Employees', 'erp' ) } sortKey="total_employees" orderBy={ sort.key } order={ sort.dir } onSort={ onToggleSort } first={ false } />
						<th scope="col" className="w-20 px-4">
							<span className="sr-only">{ __( 'Actions', 'erp' ) }</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{ rows.map( ( desig ) => (
						<tr key={ desig.id } className="h-18 border-b border-border bg-card last:border-b-0 hover:bg-muted/40">
							{ canManage ? (
								<td className="px-4 align-middle">
									<Checkbox checked={ selected.has( desig.id ) } onCheckedChange={ () => onToggleOne( desig.id ) } aria-label={ sprintf( __( 'Select %s', 'erp' ), desig.title ) } />
								</td>
							) : null }
							<td className="px-2 align-middle text-sm">
								<Link
									to={ `/employees?designation_id=${ desig.id }` }
									className="font-medium text-foreground hover:text-primary hover:underline"
								>
									{ desig.title }
								</Link>
								{ desig.description ? (
									<div className="truncate text-xs text-muted-foreground">{ desig.description }</div>
								) : null }
							</td>
							<td className="px-2 align-middle text-sm text-foreground">
								<EmployeeAvatarStack people={ desig.employees } total={ desig.total_employees } />
							</td>
							<td className="px-4 align-middle">
								{ canManage ? (
									<div className="flex justify-end">
										<RowActionsMenu
											label={ sprintf( __( 'Actions for %s', 'erp' ), desig.title ) }
											actions={ [
												{ id: 'edit', label: __( 'Edit', 'erp' ), icon: Pencil, onSelect: () => onEdit( desig ) },
												{ id: 'delete', label: __( 'Delete', 'erp' ), icon: Trash2, onSelect: () => onDelete( desig ), variant: 'destructive' },
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
