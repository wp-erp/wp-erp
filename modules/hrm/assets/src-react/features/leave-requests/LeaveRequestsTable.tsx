/**
 * Leave-requests table — the paginated row grid for `/leave/requests`.
 *
 * Pure presentation: it receives the resolved rows + the current selection and
 * reports user intent (select, moderate, delete, sort) back to the page via
 * callbacks. Row layout mirrors the Employees table conventions (h-18 rows,
 * px-4 ends). Columns mirror the legacy `LeaveRequestsListTable`: employee,
 * policy, request-for (dates + days), requested-on, available balance, status,
 * approved/rejected-by, and the uploaded documents.
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
import {
	ChevronDown,
	ChevronsUpDown,
	ChevronUp,
	MoreVertical,
	Paperclip,
	RotateCcw,
	Trash2,
} from 'lucide-react';
import { applyFilters } from '@wordpress/hooks';
import type { JSX } from 'react';

import { ApproveRejectSplit } from '@/features/requests/ApproveRejectSplit';

import { PersonCell } from '@/shared/components/PersonCell';
import { HOOKS } from '@/shared/filters';
import { __, sprintf } from '@/shared/i18n';
import { formatDisplayDate } from '@/shared/utils/date';

import type { LeaveRequest, LeaveRequestRowAction } from './types';

/**
 * Long "Year Mon D" date label; "—" when empty, raw slice when unparseable.
 * Parses date-only `YYYY-MM-DD` as a local day so it never shifts back one.
 * @param value
 */
function fmt( value: string | null ): string {
	return formatDisplayDate( value, ( value ?? '' ).slice( 0, 10 ) || '—' );
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

/**
 * Available-balance cell — mirrors the legacy `available` column: a green
 * remaining-days chip, or a red over-drawn "Extra Leave" chip, or an em-dash.
 * @param root0
 * @param root0.available
 * @param root0.extra
 */
function AvailableCell( {
	available,
	extra,
}: {
	available: number;
	extra: number;
} ): JSX.Element {
	if ( extra > 0 ) {
		return (
			<span
				className="text-destructive"
				title={ __( 'Extra Leave', 'erp' ) }
			>
				{ sprintf(
					extra === 1
						? __( '-%s day', 'erp' )
						: __( '-%s days', 'erp' ),
					String( extra )
				) }
			</span>
		);
	}
	if ( available > 0 ) {
		return (
			<span
				className="text-success-on-light"
				title={ __( 'Available Leave', 'erp' ) }
			>
				{ sprintf(
					available === 1
						? __( '%s day', 'erp' )
						: __( '%s days', 'erp' ),
					String( available )
				) }
			</span>
		);
	}
	return <span className="text-muted-foreground">—</span>;
}

interface SortThProps {
	readonly label: string;
	readonly column: string;
	readonly orderby: string;
	readonly order: 'asc' | 'desc';
	readonly onSort: ( column: string ) => void;
	readonly className?: string;
}

/**
 * Sortable column header — a raw button (per design canon: table sort-headers
 * stay native, not DS `Button`) with an ascending / descending / neutral chevron.
 * @param root0
 */
function SortTh( {
	label,
	column,
	orderby,
	order,
	onSort,
	className,
}: SortThProps ): JSX.Element {
	const active = orderby === column;
	const Icon = ! active ? ChevronsUpDown : order === 'asc' ? ChevronUp : ChevronDown;
	return (
		<th scope="col" className={ `whitespace-nowrap ${ className ?? 'px-2' }` }>
			<button
				type="button"
				onClick={ () => onSort( column ) }
				className={ [
					'inline-flex items-center gap-1 uppercase tracking-normal transition-colors',
					active ? 'text-foreground' : 'hover:text-foreground',
				].join( ' ' ) }
				aria-label={ sprintf( __( 'Sort by %s', 'erp' ), label ) }
			>
				{ label }
				<Icon size={ 12 } aria-hidden="true" className="shrink-0" />
			</button>
		</th>
	);
}

interface LeaveRequestsTableProps {
	readonly rows: readonly LeaveRequest[];
	readonly canManage: boolean;
	readonly selected: ReadonlySet< number >;
	readonly allOnPageSelected: boolean;
	/** Active status tab — decides the "Approved By" vs "Rejected By" header. */
	readonly statusFilter: number;
	readonly orderby: string;
	readonly order: 'asc' | 'desc';
	readonly onSort: ( column: string ) => void;
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
	statusFilter,
	orderby,
	order,
	onSort,
	onToggleAll,
	onToggleOne,
	onModerate,
	onDelete,
}: LeaveRequestsTableProps ): JSX.Element {
	const approverHeader =
		statusFilter === 3
			? __( 'Rejected By', 'erp' )
			: __( 'Approved By', 'erp' );

	return (
		<div className="overflow-x-auto">
			<table className="w-full min-w-240 text-left">
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
						<SortTh
							label={ __( 'Employee', 'erp' ) }
							column="name"
							orderby={ orderby }
							order={ order }
							onSort={ onSort }
							className="px-4"
						/>
						<th scope="col" className="whitespace-nowrap px-2">
							{ __( 'Leave Type', 'erp' ) }
						</th>
						<SortTh
							label={ __( 'Duration', 'erp' ) }
							column="start_date"
							orderby={ orderby }
							order={ order }
							onSort={ onSort }
						/>
						<SortTh
							label={ __( 'Days', 'erp' ) }
							column="days"
							orderby={ orderby }
							order={ order }
							onSort={ onSort }
						/>
						<SortTh
							label={ __( 'Requested On', 'erp' ) }
							column="created_at"
							orderby={ orderby }
							order={ order }
							onSort={ onSort }
						/>
						<SortTh
							label={ __( 'Available', 'erp' ) }
							column="available"
							orderby={ orderby }
							order={ order }
							onSort={ onSort }
						/>
						<SortTh
							label={ __( 'Status', 'erp' ) }
							column="last_status"
							orderby={ orderby }
							order={ order }
							onSort={ onSort }
						/>
						<th scope="col" className="whitespace-nowrap px-2">
							{ __( 'Reason', 'erp' ) }
						</th>
						<th scope="col" className="whitespace-nowrap px-2">
							{ approverHeader }
						</th>
						<th scope="col" className="whitespace-nowrap px-2">
							{ __( 'Docs', 'erp' ) }
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
							<td className="whitespace-nowrap px-2 align-middle text-sm text-muted-foreground">
								{ fmt( req.created_at ) }
							</td>
							<td className="whitespace-nowrap px-2 align-middle text-sm">
								<AvailableCell
									available={ req.available }
									extra={ req.extra_leaves }
								/>
							</td>
							<td className="whitespace-nowrap px-2 align-middle">
								<StatusPill
									status={ req.status }
									label={ req.status_label }
								/>
							</td>
							<td className="max-w-48 px-2 align-middle text-sm text-muted-foreground">
								{ req.reason ? (
									<span
										className="block truncate"
										title={ req.reason }
									>
										{ req.reason }
									</span>
								) : (
									<span className="text-muted-foreground">
										—
									</span>
								) }
							</td>
							<td className="px-2 align-middle text-sm text-muted-foreground">
								{ req.approved_by ? (
									<div className="flex flex-col leading-tight">
										<span className="font-medium text-foreground">
											{ req.approved_by }
										</span>
										{ req.approved_at ? (
											<span className="text-xs">
												{ fmt( req.approved_at ) }
											</span>
										) : null }
										{ req.approver_note ? (
											<span
												className="max-w-48 truncate text-xs italic"
												title={ req.approver_note }
											>
												{ req.approver_note }
											</span>
										) : null }
									</div>
								) : (
									<span className="text-muted-foreground">
										—
									</span>
								) }
							</td>
							<td className="px-2 align-middle text-sm">
								{ req.attachments.length > 0 ? (
									<div className="flex flex-col gap-1">
										{ req.attachments.map( ( file ) => (
											<a
												key={ file.id }
												href={ file.url }
												target="_blank"
												rel="noreferrer"
												className="inline-flex max-w-48 items-center gap-1 truncate text-primary hover:underline"
												title={ file.filename }
											>
												<Paperclip
													size={ 13 }
													aria-hidden="true"
													className="shrink-0"
												/>
												<span className="truncate">
													{ file.filename }
												</span>
											</a>
										) ) }
									</div>
								) : (
									<span className="text-muted-foreground">
										—
									</span>
								) }
							</td>
							<td className="px-4 align-middle">
								{ canManage ? (
									<div className="flex items-center justify-end gap-1">
										{ req.status === 2 ? (
											<ApproveRejectSplit
												onApprove={ () => onModerate( 'approve', req ) }
												onReject={ () => onModerate( 'reject', req ) }
											/>
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
												{ /* Pro-appended row actions (Advanced Leave multilevel: Forward). */ }
												{ (
													applyFilters(
														HOOKS.LEAVE_REQUEST_ROW_ACTIONS,
														[],
														{ request: req }
													) as LeaveRequestRowAction[]
												).map( ( action ) => (
													<DropdownMenuItem
														key={ action.id }
														variant={
															action.variant ===
															'destructive'
																? 'destructive'
																: 'default'
														}
														className="gap-2"
														onClick={ () =>
															action.onSelect(
																req
															)
														}
													>
														{ action.label }
													</DropdownMenuItem>
												) ) }
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
