/**
 * Editable "General" sub-entity sections for the single-employee profile —
 * Work Experience, Education, Dependents. Renders one card per section with
 * add / edit / delete, all wired to the v2 routes (which mirror the legacy
 * General-tab AJAX handlers). Gated by `erp_edit_employee` upstream.
 */

import { Button, Spinner, toast } from '@wedevs/plugin-ui';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import type { JSX, ReactNode } from 'react';

import { __ } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';

import { OrgDeleteDialog } from '../../org/OrgDeleteDialog';
import { GeneralSectionDialog } from './GeneralSectionDialog';
import { useEmployeeGeneral } from './useEmployeeGeneral';
import type { GeneralSection } from './useEmployeeGeneral';

function cell( value: string | number | null | undefined ): string {
	const s = value === null || value === undefined ? '' : String( value );
	return s.trim() === '' ? '—' : s;
}

/** Parse the education `result` JSON into a display string (e.g. "3.8 / 4"). */
function resultLabel( result: string ): string {
	if ( ! result ) {
		return '—';
	}
	try {
		const parsed = JSON.parse( result ) as { gpa?: string; scale?: string };
		const gpa = ( parsed.gpa ?? '' ).trim();
		if ( ! gpa ) {
			return '—';
		}
		return parsed.scale ? `${ gpa } / ${ parsed.scale }` : gpa;
	} catch {
		return '—';
	}
}

interface SectionCardProps {
	readonly title:       string;
	readonly columns:     readonly string[];
	readonly empty:       string;
	readonly rowCount:    number;
	readonly onAdd:       () => void;
	readonly children:    ReactNode;
}

function SectionCard( { title, columns, empty, rowCount, onAdd, children }: SectionCardProps ): JSX.Element {
	return (
		<section className="overflow-hidden rounded-lg border border-border bg-card shadow-sm">
			<header className="flex items-center justify-between gap-4 px-6 py-4">
				<h2 className="m-0 text-2xl font-bold leading-tight tracking-tight text-foreground">{ title }</h2>
				<Button variant="outline" size="sm" className="h-9 gap-1.5 px-4" onClick={ onAdd }>
					<Plus size={ 14 } aria-hidden="true" />
					{ __( 'Add', 'erp' ) }
				</Button>
			</header>
			<div className="mx-6 h-px bg-border" />
			{ rowCount === 0 ? (
				<p className="p-6 text-sm text-muted-foreground">{ empty }</p>
			) : (
				<div className="overflow-x-auto">
					<table className="w-full text-left">
						<thead className="border-b border-border bg-muted/40">
							<tr className="h-10 text-xs font-medium uppercase tracking-normal text-muted-foreground">
								{ columns.map( ( col ) => (
									<th key={ col } scope="col" className="px-2">{ col }</th>
								) ) }
								<th scope="col" className="w-20 px-4">
									<span className="sr-only">{ __( 'Actions', 'erp' ) }</span>
								</th>
							</tr>
						</thead>
						<tbody>{ children }</tbody>
					</table>
				</div>
			) }
		</section>
	);
}

function RowActions( { onEdit, onDelete }: { readonly onEdit: () => void; readonly onDelete: () => void } ): JSX.Element {
	return (
		<td className="px-4 align-middle">
			<div className="flex items-center justify-end gap-1">
				<Button variant="ghost" size="icon" className="size-8" onClick={ onEdit } aria-label={ __( 'Edit', 'erp' ) }>
					<Pencil size={ 14 } aria-hidden="true" />
				</Button>
				<Button variant="ghost" size="icon" className="size-8 text-destructive" onClick={ onDelete } aria-label={ __( 'Delete', 'erp' ) }>
					<Trash2 size={ 14 } aria-hidden="true" />
				</Button>
			</div>
		</td>
	);
}

export function EmployeeGeneralSections( { userId }: { readonly userId: number } ): JSX.Element {
	const { experiences, educations, dependents, loading, error, save, remove } = useEmployeeGeneral( userId );

	const [ dialog, setDialog ]   = useState< { section: GeneralSection; initial: Record< string, unknown > | null } | null >( null );
	const [ pending, setPending ] = useState< { section: GeneralSection; id: number } | null >( null );
	const [ busy, setBusy ]       = useState( false );
	const [ formError, setError ] = useState< string | null >( null );

	function openAdd( section: GeneralSection ): void {
		setError( null );
		setDialog( { section, initial: null } );
	}

	function openEdit( section: GeneralSection, row: Record< string, unknown > ): void {
		setError( null );
		setDialog( { section, initial: row } );
	}

	async function handleSubmit( data: Record< string, unknown > ): Promise< void > {
		if ( ! dialog ) {
			return;
		}
		setBusy( true );
		setError( null );
		try {
			await save( dialog.section, data );
			toast.success( __( 'Saved.', 'erp' ) );
			setDialog( null );
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? __( 'Could not save the record.', 'erp' ) );
		} finally {
			setBusy( false );
		}
	}

	async function handleDelete(): Promise< void > {
		if ( ! pending ) {
			return;
		}
		setBusy( true );
		try {
			await remove( pending.section, pending.id );
			toast.success( __( 'Deleted.', 'erp' ) );
			setPending( null );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not delete the record.', 'erp' ) );
		} finally {
			setBusy( false );
		}
	}

	if ( loading ) {
		return (
			<div className="flex items-center justify-center gap-2 rounded-[10px] bg-card p-10 text-sm text-muted-foreground shadow-sm">
				<Spinner className="size-4" />
				{ __( 'Loading profile details…', 'erp' ) }
			</div>
		);
	}

	if ( error ) {
		return <p className="rounded-[10px] bg-card p-6 text-sm text-destructive shadow-sm">{ error }</p>;
	}

	return (
		<>
			<SectionCard
				title={ __( 'Work Experience', 'erp' ) }
				columns={ [ __( 'Company', 'erp' ), __( 'Job Title', 'erp' ), __( 'From', 'erp' ), __( 'To', 'erp' ) ] }
				empty={ __( 'No work experience added.', 'erp' ) }
				rowCount={ experiences.length }
				onAdd={ () => openAdd( 'experiences' ) }
			>
				{ experiences.map( ( row ) => (
					<tr key={ row.id } className="h-18 border-b border-border last:border-b-0">
						<td className="px-2 align-middle text-sm text-foreground">{ cell( row.company_name ) }</td>
						<td className="px-2 align-middle text-sm text-foreground">{ cell( row.job_title ) }</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">{ cell( row.from ) }</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">{ cell( row.to ) }</td>
						<RowActions onEdit={ () => openEdit( 'experiences', row as unknown as Record< string, unknown > ) } onDelete={ () => setPending( { section: 'experiences', id: row.id } ) } />
					</tr>
				) ) }
			</SectionCard>

			<SectionCard
				title={ __( 'Education', 'erp' ) }
				columns={ [ __( 'School', 'erp' ), __( 'Degree', 'erp' ), __( 'Field', 'erp' ), __( 'Result', 'erp' ), __( 'Year', 'erp' ) ] }
				empty={ __( 'No education added.', 'erp' ) }
				rowCount={ educations.length }
				onAdd={ () => openAdd( 'educations' ) }
			>
				{ educations.map( ( row ) => (
					<tr key={ row.id } className="h-18 border-b border-border last:border-b-0">
						<td className="px-2 align-middle text-sm text-foreground">{ cell( row.school ) }</td>
						<td className="px-2 align-middle text-sm text-foreground">{ cell( row.degree ) }</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">{ cell( row.field ) }</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">{ resultLabel( row.result ) }</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">{ cell( row.finished ) }</td>
						<RowActions onEdit={ () => openEdit( 'educations', row as unknown as Record< string, unknown > ) } onDelete={ () => setPending( { section: 'educations', id: row.id } ) } />
					</tr>
				) ) }
			</SectionCard>

			<SectionCard
				title={ __( 'Dependents', 'erp' ) }
				columns={ [ __( 'Name', 'erp' ), __( 'Relation', 'erp' ), __( 'Date of Birth', 'erp' ) ] }
				empty={ __( 'No dependents added.', 'erp' ) }
				rowCount={ dependents.length }
				onAdd={ () => openAdd( 'dependents' ) }
			>
				{ dependents.map( ( row ) => (
					<tr key={ row.id } className="h-18 border-b border-border last:border-b-0">
						<td className="px-2 align-middle text-sm text-foreground">{ cell( row.name ) }</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">{ cell( row.relation ) }</td>
						<td className="px-2 align-middle text-sm text-muted-foreground">{ cell( row.dob ) }</td>
						<RowActions onEdit={ () => openEdit( 'dependents', row as unknown as Record< string, unknown > ) } onDelete={ () => setPending( { section: 'dependents', id: row.id } ) } />
					</tr>
				) ) }
			</SectionCard>

			<GeneralSectionDialog
				section={ dialog?.section ?? null }
				initial={ dialog?.initial ?? null }
				busy={ busy }
				error={ formError }
				onClose={ () => setDialog( null ) }
				onSubmit={ ( data ) => void handleSubmit( data ) }
			/>

			<OrgDeleteDialog
				open={ pending !== null }
				title={ __( 'Delete record?', 'erp' ) }
				description={ __( 'This will be permanently deleted.', 'erp' ) }
				busy={ busy }
				onCancel={ () => setPending( null ) }
				onConfirm={ () => void handleDelete() }
			/>
		</>
	);
}
