/**
 * Job tab for the single-employee profile (read-only).
 *
 * Renders the four legacy "Job" history buckets — Employment Status,
 * Employment Type, Compensation, Job Information — each as a simple table with
 * the change date. Inline update actions (status / type / compensation /
 * job-info) are a follow-up; this tab surfaces the history the v1 model already
 * tracks.
 */

import { Button, Spinner, toast } from '@wedevs/plugin-ui';
import { useDispatch } from '@wordpress/data';
import { Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import type { JSX, ReactNode } from 'react';

import { useCan } from '@/shared/hooks/useCan';
import { __ } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';
import { storeName as employeesStoreName } from '@/stores/employees';
import type { EmployeeTerminateInput } from '@/stores/employees';

import { OrgDeleteDialog } from '../../org/OrgDeleteDialog';
import { PayRateReveal } from '../PayRateReveal';
import { JobUpdateDialog } from './JobUpdateDialog';
import type { JobAction } from './JobUpdateDialog';
import {
	compensationInitial,
	jobInitial,
	statusInitial,
	typeInitial,
} from './job-update-helpers';
import type { FormState } from './job-update-helpers';
import { useEmployeeJobHistories } from './useEmployeeJobHistories';

interface JobDispatch {
	terminateEmployee: ( userId: number, payload: EmployeeTerminateInput ) => Promise< unknown >;
}

function formatDate( iso: string | null ): string {
	if ( ! iso ) {
		return '—';
	}
	const date = new Date( iso );
	if ( Number.isNaN( date.getTime() ) ) {
		return '—';
	}
	return date.toLocaleDateString( undefined, { year: 'numeric', month: 'short', day: 'numeric' } );
}

function cell( value: string ): string {
	return value.trim() === '' ? '—' : value;
}

interface HistoryCardProps {
	readonly title:        string;
	readonly columns:      readonly string[];
	readonly empty:        string;
	readonly rowCount:     number;
	readonly canDelete?:   boolean;
	readonly headerAction?: ReactNode;
	readonly children:     ReactNode;
}

function HistoryCard( { title, columns, empty, rowCount, canDelete, headerAction, children }: HistoryCardProps ): JSX.Element {
	return (
		<section className="overflow-hidden rounded-lg border border-border bg-card shadow-sm">
			<header className="flex items-center justify-between gap-4 px-6 py-4">
				<h2 className="m-0 mb-4 text-2xl font-bold leading-tight tracking-tight text-foreground">{ title }</h2>
				{ headerAction }
			</header>
			<div className="mx-6 mb-4 h-px bg-border" />
			{ rowCount === 0 ? (
				<p className="p-6 text-sm text-muted-foreground">{ empty }</p>
			) : (
				<div className="overflow-x-auto">
					<table className="w-full text-left">
						<thead className="border-b border-border bg-card">
							<tr className="h-10 text-[12px] font-normal uppercase leading-[1.4] tracking-normal text-[#828282]">
								{ columns.map( ( col ) => (
									<th key={ col } scope="col" className="px-4">{ col }</th>
								) ) }
								{ canDelete ? (
									<th scope="col" className="w-16 px-4"><span className="sr-only">{ __( 'Actions', 'erp' ) }</span></th>
								) : null }
							</tr>
						</thead>
						<tbody>{ children }</tbody>
					</table>
				</div>
			) }
		</section>
	);
}

interface RowActionCellProps {
	readonly isActive: boolean;
	readonly onEdit:   () => void;
	readonly onDelete: () => void;
}

/**
 * Trailing action cell for a history row. The active record (index 0) is
 * edited in place — matching legacy, which only edits the current history and
 * blocks deleting it; older rows (index > 0) can only be deleted.
 */
function RowActionCell( { isActive, onEdit, onDelete }: RowActionCellProps ): JSX.Element {
	return (
		<td className="px-4 align-middle text-right">
			{ isActive ? (
				<Button variant="ghost" size="icon" className="size-8" onClick={ onEdit } aria-label={ __( 'Edit', 'erp' ) }>
					<Pencil size={ 14 } aria-hidden="true" />
				</Button>
			) : (
				<Button variant="ghost" size="icon" className="size-8 text-destructive" onClick={ onDelete } aria-label={ __( 'Delete', 'erp' ) }>
					<Trash2 size={ 14 } aria-hidden="true" />
				</Button>
			) }
		</td>
	);
}

export function EmployeeJobTab( { userId }: { readonly userId: number } ): JSX.Element {
	const { data, loading, error, createHistory, updateHistory, deleteHistory, refetch } = useEmployeeJobHistories( userId );
	const canManage = useCan( 'erp_manage_jobinfo' );
	const { terminateEmployee } = useDispatch( employeesStoreName ) as unknown as JobDispatch;

	const [ action, setAction ]   = useState< JobAction | null >( null );
	const [ busy, setBusy ]       = useState( false );
	const [ formError, setError ] = useState< string | null >( null );
	const [ pendingDelete, setPendingDelete ] = useState< number | null >( null );
	// Set while editing the active row in place (PUT); null in create mode (POST).
	const [ editState, setEditState ] = useState< { editId: number; initial: Partial< FormState > } | null >( null );

	async function handleDelete(): Promise< void > {
		if ( pendingDelete === null ) {
			return;
		}
		setBusy( true );
		try {
			await deleteHistory( pendingDelete );
			toast.success( __( 'History entry deleted.', 'erp' ) );
			setPendingDelete( null );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not delete the entry.', 'erp' ) );
		} finally {
			setBusy( false );
		}
	}

	function closeDialog(): void {
		setAction( null );
		setEditState( null );
	}

	function openAction( next: JobAction ): void {
		setError( null );
		setEditState( null );
		setAction( next );
	}

	function openEdit( next: JobAction, editId: number, initial: Partial< FormState > ): void {
		setError( null );
		setEditState( { editId, initial } );
		setAction( next );
	}

	async function handleSubmit( payload: Record< string, unknown > ): Promise< void > {
		setBusy( true );
		setError( null );
		try {
			if ( editState ) {
				// Edit the active history row in place (PUT). The dialog never emits a
				// `terminate` payload in edit mode, so this is always a plain update.
				await updateHistory( editState.editId, payload );
				toast.success( __( 'History updated.', 'erp' ) );
			} else if ( payload.terminate === true ) {
				// Status → Terminated routes to the terminate endpoint (legacy parity),
				// not a plain status-history row.
				await terminateEmployee( userId, {
					terminate_date:      String( payload.terminate_date ?? '' ),
					termination_type:    String( payload.termination_type ?? '' ),
					termination_reason:  String( payload.termination_reason ?? '' ),
					eligible_for_rehire: String( payload.eligible_for_rehire ?? '' ),
				} );
				toast.success( __( 'Employee terminated.', 'erp' ) );
				refetch();
			} else {
				await createHistory( payload );
				toast.success( __( 'History updated.', 'erp' ) );
			}
			closeDialog();
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? __( 'Could not save the history.', 'erp' ) );
		} finally {
			setBusy( false );
		}
	}

	const actionButton = ( target: JobAction, label: string ): ReactNode =>
		canManage ? (
			<Button variant="outline" size="sm" className="h-9 gap-1.5 px-4" onClick={ () => openAction( target ) }>
				<Pencil size={ 14 } aria-hidden="true" />
				{ label }
			</Button>
		) : null;

	if ( error ) {
		return <p className="rounded-lg border border-border bg-card p-6 text-sm text-destructive">{ error }</p>;
	}

	if ( loading || ! data ) {
		return (
			<div className="flex items-center justify-center gap-2 rounded-lg border border-border bg-card p-10 text-sm text-muted-foreground">
				<Spinner className="size-4" />
				{ __( 'Loading job history…', 'erp' ) }
			</div>
		);
	}

	return (
		<div className="space-y-6">
			<HistoryCard
				title={ __( 'Employment Status', 'erp' ) }
				columns={ [ __( 'Date', 'erp' ), __( 'Status', 'erp' ), __( 'Comment', 'erp' ) ] }
				empty={ __( 'No status changes recorded.', 'erp' ) }
				rowCount={ data.status.length }
				canDelete={ canManage }
				headerAction={ actionButton( 'status', __( 'Update Status', 'erp' ) ) }
			>
				{ data.status.map( ( row, index ) => (
					<tr key={ row.id } className="h-18 border-b border-border bg-card last:border-b-0 hover:bg-muted/40">
						<td className="px-4 align-middle text-sm text-foreground">{ formatDate( row.date ) }</td>
						<td className="px-2 align-middle text-sm text-foreground">{ cell( row.status ) }</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">{ cell( row.comment ) }</td>
						{ canManage ? (
							<RowActionCell
								isActive={ index === 0 }
								onEdit={ () => openEdit( 'status', row.id, statusInitial( row ) ) }
								onDelete={ () => setPendingDelete( row.id ) }
							/>
						) : null }
					</tr>
				) ) }
			</HistoryCard>

			<HistoryCard
				title={ __( 'Employment Type', 'erp' ) }
				columns={ [ __( 'Date', 'erp' ), __( 'Type', 'erp' ), __( 'Comment', 'erp' ) ] }
				empty={ __( 'No employment-type changes recorded.', 'erp' ) }
				rowCount={ data.employment.length }
				canDelete={ canManage }
				headerAction={ actionButton( 'type', __( 'Update Type', 'erp' ) ) }
			>
				{ data.employment.map( ( row, index ) => (
					<tr key={ row.id } className="h-18 border-b border-border bg-card last:border-b-0 hover:bg-muted/40">
						<td className="px-2 align-middle text-sm text-foreground">{ formatDate( row.date ) }</td>
						<td className="px-2 align-middle text-sm text-foreground">{ cell( row.type ) }</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">{ cell( row.comment ) }</td>
						{ canManage ? (
							<RowActionCell
								isActive={ index === 0 }
								onEdit={ () => openEdit( 'type', row.id, typeInitial( row ) ) }
								onDelete={ () => setPendingDelete( row.id ) }
							/>
						) : null }
					</tr>
				) ) }
			</HistoryCard>

			<HistoryCard
				title={ __( 'Compensation', 'erp' ) }
				columns={ [ __( 'Date', 'erp' ), __( 'Pay Rate', 'erp' ), __( 'Pay Type', 'erp' ), __( 'Reason', 'erp' ), __( 'Comment', 'erp' ) ] }
				empty={ __( 'No compensation changes recorded.', 'erp' ) }
				rowCount={ data.compensation.length }
				canDelete={ canManage }
				headerAction={ actionButton( 'compensation', __( 'Update Compensation', 'erp' ) ) }
			>
				{ data.compensation.map( ( row, index ) => (
					<tr key={ row.id } className="h-18 border-b border-border bg-card last:border-b-0 hover:bg-muted/40">
						<td className="px-2 align-middle text-sm text-foreground">{ formatDate( row.date ) }</td>
						<td className="px-2 align-middle text-sm text-foreground"><PayRateReveal value={ row.pay_rate } /></td>
						<td className="px-2 align-middle text-sm text-foreground">{ cell( row.pay_type ) }</td>
						<td className="px-2 align-middle text-sm text-foreground">{ cell( row.reason ) }</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">{ cell( row.comment ) }</td>
						{ canManage ? (
							<RowActionCell
								isActive={ index === 0 }
								onEdit={ () => openEdit( 'compensation', row.id, compensationInitial( row ) ) }
								onDelete={ () => setPendingDelete( row.id ) }
							/>
						) : null }
					</tr>
				) ) }
			</HistoryCard>

			<HistoryCard
				title={ __( 'Job Information', 'erp' ) }
				columns={ [ __( 'Date', 'erp' ), __( 'Department', 'erp' ), __( 'Designation', 'erp' ), __( 'Location', 'erp' ), __( 'Reporting To', 'erp' ) ] }
				empty={ __( 'No job-information changes recorded.', 'erp' ) }
				rowCount={ data.job.length }
				canDelete={ canManage }
				headerAction={ actionButton( 'job', __( 'Update Job Information', 'erp' ) ) }
			>
				{ data.job.map( ( row, index ) => (
					<tr key={ row.id } className="h-18 border-b border-border bg-card last:border-b-0 hover:bg-muted/40">
						<td className="px-2 align-middle text-sm text-foreground">{ formatDate( row.date ) }</td>
						<td className="px-2 align-middle text-sm text-foreground">{ cell( row.department ) }</td>
						<td className="px-2 align-middle text-sm text-foreground">{ cell( row.designation ) }</td>
						<td className="px-2 align-middle text-sm text-foreground">{ cell( row.location ) }</td>
						<td className="px-2 align-middle text-sm text-foreground">{ cell( row.reporting_to ) }</td>
						{ canManage ? (
							<RowActionCell
								isActive={ index === 0 }
								onEdit={ () => openEdit( 'job', row.id, jobInitial( row ) ) }
								onDelete={ () => setPendingDelete( row.id ) }
							/>
						) : null }
					</tr>
				) ) }
			</HistoryCard>

			<JobUpdateDialog
				action={ action }
				busy={ busy }
				error={ formError }
				editId={ editState?.editId }
				initial={ editState?.initial }
				onClose={ closeDialog }
				onSubmit={ ( payload ) => void handleSubmit( payload ) }
			/>

			<OrgDeleteDialog
				open={ pendingDelete !== null }
				title={ __( 'Delete history entry?', 'erp' ) }
				description={ __( 'This will be permanently deleted.', 'erp' ) }
				busy={ busy }
				onCancel={ () => setPendingDelete( null ) }
				onConfirm={ () => void handleDelete() }
			/>
		</div>
	);
}
