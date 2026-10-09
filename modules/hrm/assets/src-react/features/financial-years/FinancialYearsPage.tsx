/**
 * HR Financial Years — restores the legacy Settings → "Leave Years" editor
 * (Vue `HRLeaveYears.vue`) that had no React surface after the redesign.
 *
 * A full-table editor: add / edit / delete rows, then "Save Changes" replaces
 * the whole set (the server truncates + reinserts, mirroring the legacy save).
 * Entitlements are scoped to these years, so this is manager-gated (`erp_hr_manager`).
 */

import { Button, Input, toast } from '@wedevs/plugin-ui';
import { CalendarRange, Plus, Save, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import { TableSkeleton } from '@/shared/components/TableSkeleton';
import type { JSX } from 'react';

import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { DateField } from '@/shared/DateField';
import { EmptyState } from '@/shared/components/EmptyState';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { PlainLabel } from '@/shared/components/FieldLabels';
import { __ } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';

import type { FinancialYear } from './types';
import { useFinancialYears } from './useFinancialYears';
import { findYearsError } from './validate-years';

function emptyRow(): FinancialYear {
	return { id: null, fy_name: '', start_date: '', end_date: '', description: '' };
}

function FinancialYearsInner(): JSX.Element {
	const { rows, loading, error, save } = useFinancialYears();

	const [ draft, setDraft ] = useState< FinancialYear[] >( [] );
	const [ busy, setBusy ]   = useState( false );

	// Seed the editable draft from the server rows whenever they (re)load. With
	// no year saved yet the draft starts empty, so the empty state explains
	// what a financial year is before the first row is added.
	useEffect( () => {
		setDraft( rows.map( ( r ) => ( { ...r } ) ) );
	}, [ rows ] );

	function patch( index: number, key: keyof FinancialYear, value: string ): void {
		setDraft( ( prev ) => prev.map( ( r, i ) => ( i === index ? { ...r, [ key ]: value } : r ) ) );
	}

	function addRow(): void {
		setDraft( ( prev ) => [ ...prev, emptyRow() ] );
	}

	function removeRow( index: number ): void {
		setDraft( ( prev ) => ( prev.length > 1 ? prev.filter( ( _r, i ) => i !== index ) : [ emptyRow() ] ) );
	}

	async function handleSave(): Promise< void > {
		// Mirror the server validation client-side for instant feedback.
		const invalid = findYearsError( draft, rows );
		if ( invalid ) {
			toast.error( invalid );
			return;
		}

		setBusy( true );
		try {
			await save( draft );
			toast.success( __( 'Settings saved successfully !', 'erp' ) );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not save financial years.', 'erp' ) );
		} finally {
			setBusy( false );
		}
	}

	return (
		<section className="mx-auto w-full max-w-5xl">
			<header className="mb-6 flex flex-wrap items-center justify-between gap-4">
				<div>
					<h1 className="text-2xl font-bold leading-8 text-foreground">{ __( 'Financial Years', 'erp' ) }</h1>
					<p className="mt-1 text-sm text-muted-foreground">
						{ __( 'Define the financial years that leave entitlements are granted against.', 'erp' ) }
					</p>
				</div>
				<Button className="h-10 gap-1.5 px-4" disabled={ busy || loading || draft.length === 0 } onClick={ () => void handleSave() }>
					<Save size={ 16 } aria-hidden="true" />
					{ busy ? __( 'Saving…', 'erp' ) : __( 'Save Changes', 'erp' ) }
				</Button>
			</header>

			<div className="rounded-[10px] bg-card p-6 shadow-sm">
				{ error ? (
					<p className="text-sm text-destructive">{ error }</p>
				) : loading ? (
					<TableSkeleton rows={ 6 } />
				) : (
					<>
						{ draft.length === 0 ? (
							<EmptyState
								size="compact"
								icon={ CalendarRange }
								title={ __( 'No financial years yet.', 'erp' ) }
								description={ __( 'A financial year sets the dates leave is counted in, like January to December. Use Add New to add one, then Save Changes.', 'erp' ) }
							/>
						) : null }
						<div className="space-y-3">
							{ draft.map( ( row, index ) => (
								<div key={ index } className="grid grid-cols-1 items-end gap-3 sm:grid-cols-[1.1fr_1fr_1fr_1.3fr_auto]">
									<PlainLabel className="flex flex-col gap-2.5">
										{ index === 0 ? <span className="text-sm font-medium text-foreground">{ __( 'Name', 'erp' ) }</span> : null }
										<Input
											value={ row.fy_name }
											onChange={ ( e ) => patch( index, 'fy_name', e.target.value ) }
											placeholder={ __( 'e.g. 2025-2026', 'erp' ) }
											className="h-10 bg-background px-4 text-sm"
										/>
									</PlainLabel>
									<PlainLabel className="flex flex-col gap-2.5">
										{ index === 0 ? <span className="text-sm font-medium text-foreground">{ __( 'Start date', 'erp' ) }</span> : null }
										<DateField
											value={ row.start_date }
											onChange={ ( v ) => patch( index, 'start_date', v ) }
											max={ row.end_date || undefined }
											className="h-10 bg-background px-4 text-sm"
										/>
									</PlainLabel>
									<PlainLabel className="flex flex-col gap-2.5">
										{ index === 0 ? <span className="text-sm font-medium text-foreground">{ __( 'End date', 'erp' ) }</span> : null }
										<DateField
											value={ row.end_date }
											onChange={ ( v ) => patch( index, 'end_date', v ) }
											min={ row.start_date || undefined }
											className="h-10 bg-background px-4 text-sm"
										/>
									</PlainLabel>
									<PlainLabel className="flex flex-col gap-2.5">
										{ index === 0 ? <span className="text-sm font-medium text-foreground">{ __( 'Description', 'erp' ) }</span> : null }
										<Input
											value={ row.description }
											onChange={ ( e ) => patch( index, 'description', e.target.value ) }
											placeholder={ __( 'Optional', 'erp' ) }
											className="h-10 bg-background px-4 text-sm"
										/>
									</PlainLabel>
									<Button
										variant="ghost"
										size="icon"
										className="text-destructive hover:bg-destructive/10 hover:text-destructive"
										aria-label={ __( 'Remove row', 'erp' ) }
										disabled={ busy }
										onClick={ () => removeRow( index ) }
									>
										<Trash2 size={ 16 } aria-hidden="true" />
									</Button>
								</div>
							) ) }
						</div>

						<Button variant="outline" className="mt-4 h-10 gap-1.5" disabled={ busy } onClick={ addRow }>
							<Plus size={ 16 } aria-hidden="true" />
							{ __( 'Add New', 'erp' ) }
						</Button>
					</>
				) }
			</div>
		</section>
	);
}

export function FinancialYearsPage(): JSX.Element {
	return (
		<CapabilityGate caps={ [ 'erp_hr_manager' ] }>
			<ErrorBoundary>
				<FinancialYearsInner />
			</ErrorBoundary>
		</CapabilityGate>
	);
}
