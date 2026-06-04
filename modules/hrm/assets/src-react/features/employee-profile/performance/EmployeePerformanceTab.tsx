/**
 * Performance tab for the single-employee profile.
 *
 * Three legacy sections — Reviews, Comments, Goals — each a table with an "Add"
 * action (gated `erp_create_review`) and per-row delete (gated
 * `erp_delete_review`). Reads + writes the v2 `/performance` endpoint, which
 * delegates to the unchanged v1 model.
 */

import { Button, Spinner, toast } from '@wedevs/plugin-ui';
import { Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import type { JSX, ReactNode } from 'react';

import { OrgDeleteDialog } from '@/features/org/OrgDeleteDialog';
import { useCan } from '@/shared/hooks/useCan';
import { __ } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';

import { PerformanceFormDialog } from './PerformanceFormDialog';
import type { PerformanceType } from './PerformanceFormDialog';
import { useEmployeePerformance } from './useEmployeePerformance';

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

interface SectionProps {
	readonly title:        string;
	readonly columns:      readonly string[];
	readonly empty:        string;
	readonly rowCount:     number;
	readonly hasActions:   boolean;
	readonly headerAction?: ReactNode;
	readonly children:     ReactNode;
}

function Section( { title, columns, empty, rowCount, hasActions, headerAction, children }: SectionProps ): JSX.Element {
	return (
		<section className="overflow-hidden rounded-[10px] bg-card shadow-sm">
			<header className="flex items-center justify-between gap-4 border-b border-border px-6 py-4">
				<h2 className="m-0 text-2xl font-bold leading-tight tracking-tight text-foreground">{ title }</h2>
				{ headerAction }
			</header>
			{ rowCount === 0 ? (
				<p className="p-6 text-sm text-muted-foreground">{ empty }</p>
			) : (
				<div className="overflow-x-auto">
					<table className="w-full text-left">
						<thead className="border-b border-border bg-muted/40">
							<tr className="h-10 text-xs font-medium uppercase tracking-normal text-muted-foreground">
								{ columns.map( ( col ) => (
									<th key={ col } scope="col" className="px-6">{ col }</th>
								) ) }
								{ hasActions ? <th scope="col" className="w-12 px-6" /> : null }
							</tr>
						</thead>
						<tbody>{ children }</tbody>
					</table>
				</div>
			) }
		</section>
	);
}

export function EmployeePerformanceTab( { userId }: { readonly userId: number } ): JSX.Element {
	const { data, loading, error, createPerformance, deletePerformance } = useEmployeePerformance( userId );
	const canCreate = useCan( 'erp_create_review' );
	const canDelete = useCan( 'erp_delete_review' );

	const [ formType, setFormType ] = useState< PerformanceType | null >( null );
	const [ busy, setBusy ]         = useState( false );
	const [ formError, setError ]   = useState< string | null >( null );
	const [ deleting, setDeleting ] = useState< number | null >( null );

	async function handleSubmit( payload: Record< string, unknown > ): Promise< void > {
		setBusy( true );
		setError( null );
		try {
			await createPerformance( payload );
			toast.success( __( 'Performance record added.', 'erp' ) );
			setFormType( null );
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? __( 'Could not save the record.', 'erp' ) );
		} finally {
			setBusy( false );
		}
	}

	async function handleDelete(): Promise< void > {
		if ( deleting === null ) {
			return;
		}
		setBusy( true );
		try {
			await deletePerformance( deleting );
			toast.success( __( 'Performance record deleted.', 'erp' ) );
			setDeleting( null );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not delete the record.', 'erp' ) );
			setDeleting( null );
		} finally {
			setBusy( false );
		}
	}

	const addButton = ( type: PerformanceType, label: string ): ReactNode =>
		canCreate ? (
			<Button variant="outline" size="sm" className="h-9 gap-1.5 px-4" onClick={ () => { setError( null ); setFormType( type ); } }>
				<Plus size={ 14 } aria-hidden="true" />
				{ label }
			</Button>
		) : null;

	const deleteCell = ( id: number ): ReactNode =>
		canDelete ? (
			<td className="px-6 align-middle text-right">
				<Button
					variant="ghost"
					size="icon-sm"
					className="text-destructive hover:text-destructive focus:text-destructive"
					aria-label={ __( 'Delete', 'erp' ) }
					onClick={ () => setDeleting( id ) }
				>
					<Trash2 size={ 14 } aria-hidden="true" />
				</Button>
			</td>
		) : null;

	if ( error ) {
		return <p className="rounded-lg border border-border bg-card p-6 text-sm text-destructive">{ error }</p>;
	}

	if ( loading || ! data ) {
		return (
			<div className="flex items-center justify-center gap-2 rounded-lg border border-border bg-card p-10 text-sm text-muted-foreground">
				<Spinner className="size-4" />
				{ __( 'Loading performance…', 'erp' ) }
			</div>
		);
	}

	return (
		<div className="space-y-6">
			<Section
				title={ __( 'Performance Reviews', 'erp' ) }
				columns={ [
					__( 'Date', 'erp' ),
					__( 'Reporting To', 'erp' ),
					__( 'Job Knowledge', 'erp' ),
					__( 'Work Quality', 'erp' ),
					__( 'Attendance', 'erp' ),
					__( 'Communication', 'erp' ),
					__( 'Dependability', 'erp' ),
				] }
				empty={ __( 'No performance reviews found.', 'erp' ) }
				rowCount={ data.reviews.length }
				hasActions={ canDelete }
				headerAction={ addButton( 'reviews', __( 'Add Review', 'erp' ) ) }
			>
				{ data.reviews.map( ( row ) => (
					<tr key={ row.id } className="h-12 border-b border-border last:border-b-0">
						<td className="px-6 align-middle text-sm text-foreground">{ formatDate( row.date ) }</td>
						<td className="px-6 align-middle text-sm text-foreground">{ cell( row.reporting_to ) }</td>
						<td className="px-6 align-middle text-sm text-foreground">{ cell( row.job_knowledge ) }</td>
						<td className="px-6 align-middle text-sm text-foreground">{ cell( row.work_quality ) }</td>
						<td className="px-6 align-middle text-sm text-foreground">{ cell( row.attendance ) }</td>
						<td className="px-6 align-middle text-sm text-foreground">{ cell( row.communication ) }</td>
						<td className="px-6 align-middle text-sm text-foreground">{ cell( row.dependability ) }</td>
						{ deleteCell( row.id ) }
					</tr>
				) ) }
			</Section>

			<Section
				title={ __( 'Performance Comments', 'erp' ) }
				columns={ [ __( 'Date', 'erp' ), __( 'Reviewer', 'erp' ), __( 'Comment', 'erp' ) ] }
				empty={ __( 'No performance comments found.', 'erp' ) }
				rowCount={ data.comments.length }
				hasActions={ canDelete }
				headerAction={ addButton( 'comments', __( 'Add Comment', 'erp' ) ) }
			>
				{ data.comments.map( ( row ) => (
					<tr key={ row.id } className="h-12 border-b border-border last:border-b-0">
						<td className="px-6 align-middle text-sm text-foreground">{ formatDate( row.date ) }</td>
						<td className="px-6 align-middle text-sm text-foreground">{ cell( row.reviewer ) }</td>
						<td className="px-6 align-middle text-sm text-muted-foreground">{ cell( row.comment ) }</td>
						{ deleteCell( row.id ) }
					</tr>
				) ) }
			</Section>

			<Section
				title={ __( 'Performance Goals', 'erp' ) }
				columns={ [
					__( 'Set Date', 'erp' ),
					__( 'Completion Date', 'erp' ),
					__( 'Goal', 'erp' ),
					__( 'Employee Assessment', 'erp' ),
					__( 'Supervisor', 'erp' ),
					__( 'Supervisor Assessment', 'erp' ),
				] }
				empty={ __( 'No performance goals found.', 'erp' ) }
				rowCount={ data.goals.length }
				hasActions={ canDelete }
				headerAction={ addButton( 'goals', __( 'Add Goal', 'erp' ) ) }
			>
				{ data.goals.map( ( row ) => (
					<tr key={ row.id } className="h-12 border-b border-border last:border-b-0">
						<td className="px-6 align-middle text-sm text-foreground">{ formatDate( row.set_date ) }</td>
						<td className="px-6 align-middle text-sm text-foreground">{ formatDate( row.completion_date ) }</td>
						<td className="px-6 align-middle text-sm text-muted-foreground">{ cell( row.goal_description ) }</td>
						<td className="px-6 align-middle text-sm text-muted-foreground">{ cell( row.employee_assessment ) }</td>
						<td className="px-6 align-middle text-sm text-foreground">{ cell( row.supervisor ) }</td>
						<td className="px-6 align-middle text-sm text-muted-foreground">{ cell( row.supervisor_assessment ) }</td>
						{ deleteCell( row.id ) }
					</tr>
				) ) }
			</Section>

			<PerformanceFormDialog
				type={ formType }
				busy={ busy }
				error={ formError }
				onClose={ () => setFormType( null ) }
				onSubmit={ ( payload ) => void handleSubmit( payload ) }
			/>

			<OrgDeleteDialog
				open={ deleting !== null }
				title={ __( 'Delete performance record?', 'erp' ) }
				description={ __( 'This record will be permanently deleted.', 'erp' ) }
				busy={ busy }
				onConfirm={ () => void handleDelete() }
				onCancel={ () => setDeleting( null ) }
			/>
		</div>
	);
}
