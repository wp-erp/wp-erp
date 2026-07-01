/**
 * Leave-requests table — the paginated row grid for `/leave/requests`.
 *
 * Pure presentation: it receives the resolved rows + the current selection and
 * reports user intent (select, moderate, delete) back to the page via callbacks.
 * Row layout mirrors the Employees table conventions (h-18 rows, px-4 ends).
 */

import {
	Badge,
	Button,
	Checkbox,
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuItem,
	DropdownMenuTrigger,
} from '@wedevs/plugin-ui';
import { Check, MoreVertical, RotateCcw, Trash2, X } from 'lucide-react';
import type { JSX } from 'react';

import { PersonCell } from '@/shared/components/PersonCell';
import { __, sprintf } from '@/shared/i18n';

import type { LeaveRequest } from './types';

/**
 * Long "Year Mon D" date label; "—" when empty, raw slice when unparseable.
 * @param value
 */
function fmt( value: string | null ): string {
	if ( ! value ) {
		return '—';
	}
	const d = new Date( value );
	if ( Number.isNaN( d.getTime() ) ) {
		return value.slice( 0, 10 );
	}
	return d.toLocaleDateString( undefined, {
		year: 'numeric',
		month: 'short',
		day: 'numeric',
	} );
}

/**
 * Status pill — same plugin-ui `Badge` + semantic-token treatment as the
 * Employees `StatusCell` (no bespoke component). Approved → success, rejected →
 * destructive, pending → warning.
 * @param root0
 * @param root0.status
 * @param root0.label
 */
function StatusPill( {
	status,
	label,
}: {
	status: number;
	label: string;
} ): JSX.Element {
	const className =
		status === 1
			? 'bg-success-light text-success-on-light'
			: status === 3
			? 'bg-destructive-light text-destructive-on-light'
			: 'bg-warning-light text-warning-on-light';
	return <Badge className={ `${ className } rounded-md` }>{ label }</Badge>;
}

interface LeaveRequestsTableProps {
	readonly rows: readonly LeaveRequest[];
	readonly canManage: boolean;
	readonly selected: ReadonlySet< number >;
	readonly allOnPageSelected: boolean;
	readonly onToggleAll: () => void;
	readonly onToggleOne: ( id: number ) => void;
	readonly onModerate: (
		action: 'approve' | 'reject',
		request: LeaveRequest
	) => void;
	readonly onDelete: ( request: LeaveRequest ) => void;
}

export function LeaveRequestsTable( {
	rows,
	canManage,
	selected,
	allOnPageSelected,
	onToggleAll,
	onToggleOne,
	onModerate,
	onDelete,
}: LeaveRequestsTableProps ): JSX.Element {
	return (
		<div className="overflow-x-auto">
			<table className="w-full min-w-[40rem] text-left">
				<thead className="border-b border-border bg-card">
					<tr className="h-10 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">
						{ canManage ? (
							<th scope="col" className="w-10 px-4">
								<Checkbox
									checked={ allOnPageSelected }
									onCheckedChange={ onToggleAll }
									aria-label={ __( 'Select all', 'erp' ) }
								/>
							</th>
						) : null }
						<th scope="col" className="whitespace-nowrap px-4">
							{ __( 'Employee', 'erp' ) }
						</th>
						<th scope="col" className="whitespace-nowrap px-2">
							{ __( 'Leave Type', 'erp' ) }
						</th>
						<th scope="col" className="whitespace-nowrap px-2">
							{ __( 'Duration', 'erp' ) }
						</th>
						<th scope="col" className="whitespace-nowrap px-2">
							{ __( 'Days', 'erp' ) }
						</th>
						<th scope="col" className="whitespace-nowrap px-2">
							{ __( 'Status', 'erp' ) }
						</th>
						<th scope="col" className="w-24 px-4">
							<span className="sr-only">
								{ __( 'Actions', 'erp' ) }
							</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{ rows.map( ( req ) => (
						<tr
							key={ req.id }
							className="h-18 border-b border-border bg-card last:border-b-0 hover:bg-muted/40"
						>
							{ canManage ? (
								<td className="w-10 px-4 align-middle">
									<Checkbox
										checked={ selected.has( req.id ) }
										onCheckedChange={ () =>
											onToggleOne( req.id )
										}
										aria-label={ sprintf(
											__( 'Select %s', 'erp' ),
											req.name
										) }
									/>
								</td>
							) : null }
							<td className="px-4 align-middle text-sm font-medium text-foreground">
								{ req.name ? (
									<PersonCell
										name={ req.name }
										avatar={ req.avatar }
									/>
								) : (
									<span className="text-muted-foreground">
										—
									</span>
								) }
							</td>
							<td className="whitespace-nowrap px-2 align-middle text-sm text-foreground">
								<span className="inline-flex items-center gap-2">
									<span
										aria-hidden="true"
										className="inline-block size-2.5 shrink-0 rounded-full"
										style={ {
											backgroundColor:
												req.color || 'transparent',
										} }
									/>
									{ req.policy_name }
								</span>
							</td>
							<td className="whitespace-nowrap px-2 align-middle text-sm text-muted-foreground">
								{ `${ fmt( req.start_date ) } – ${ fmt(
									req.end_date
								) }` }
							</td>
							<td className="px-2 align-middle text-sm text-foreground">
								{ req.days }
							</td>
							<td className="whitespace-nowrap px-2 align-middle">
								<StatusPill
									status={ req.status }
									label={ req.status_label }
								/>
							</td>
							<td className="px-4 align-middle">
								{ canManage ? (
									<div className="flex items-center justify-end gap-1">
										{ req.status === 2 ? (
											<>
												<Button
													variant="ghost"
													size="icon"
													aria-label={ __(
														'Approve',
														'erp'
													) }
													className="text-green-600 hover:bg-green-50 hover:text-green-700 dark:hover:bg-green-900/20"
													onClick={ () =>
														onModerate(
															'approve',
															req
														)
													}
												>
													<Check
														size={ 16 }
														aria-hidden="true"
													/>
												</Button>
												<Button
													variant="ghost"
													size="icon"
													aria-label={ __(
														'Reject',
														'erp'
													) }
													className="text-red-600 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-900/20"
													onClick={ () =>
														onModerate(
															'reject',
															req
														)
													}
												>
													<X
														size={ 16 }
														aria-hidden="true"
													/>
												</Button>
											</>
										) : null }
										<DropdownMenu>
											<DropdownMenuTrigger
												render={
													<Button
														variant="ghost"
														size="icon"
														aria-label={ sprintf(
															__(
																'Actions for %s',
																'erp'
															),
															req.name
														) }
													>
														<MoreVertical
															size={ 16 }
															aria-hidden="true"
														/>
													</Button>
												}
											/>
											<DropdownMenuContent
												align="end"
												className="min-w-44"
											>
												{ /* Reverse moderation — reject an approved request, or approve a rejected one (legacy list-table behaviour). */ }
												{ req.status === 1 ? (
													<DropdownMenuItem
														className="gap-2"
														onClick={ () =>
															onModerate(
																'reject',
																req
															)
														}
													>
														<RotateCcw
															size={ 14 }
															aria-hidden="true"
														/>
														{ __(
															'Reject',
															'erp'
														) }
													</DropdownMenuItem>
												) : null }
												{ req.status === 3 ? (
													<DropdownMenuItem
														className="gap-2"
														onClick={ () =>
															onModerate(
																'approve',
																req
															)
														}
													>
														<RotateCcw
															size={ 14 }
															aria-hidden="true"
														/>
														{ __(
															'Approve',
															'erp'
														) }
													</DropdownMenuItem>
												) : null }
												<DropdownMenuItem
													variant="destructive"
													className="gap-2"
													onClick={ () =>
														onDelete( req )
													}
												>
													<Trash2
														size={ 14 }
														aria-hidden="true"
													/>
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
