/**
 * Add Team step: import employees from a CSV file, between the work days and
 * the finish. It is the Employees page's import (`useEmployeeImport`, same
 * upload, column matching and result), shown inline: choose a file, match its
 * columns, import, then see how many were added and which rows were not.
 *
 * Optional: "Skip for Now" moves on without saving anything. Someone who cannot
 * create employees only gets the skip. The rows go to
 * `POST erp/v2/employees/import`, never to the onboarding routes, so the import
 * is held to the create-employee capability.
 */

import { Alert, AlertDescription, Button } from '@wedevs/plugin-ui';
import type { JSX } from 'react';

import { useCan } from '@/shared/hooks/useCan';
import { __ } from '@/shared/i18n';

import { EmployeeImportMapping } from '../../employees/EmployeeImportMapping';
import { EmployeeImportResult } from '../../employees/EmployeeImportResult';
import { EmployeeImportUpload } from '../../employees/EmployeeImportUpload';
import { useEmployeeImport } from '../../employees/useEmployeeImport';
import { SampleDataCard } from './SampleDataCard';
import { StepShell } from './StepShell';
import type { StepProps } from '../types';

export function ImportStep( { saving, onNext }: StepProps ): JSX.Element {
	const canCreate = useCan( 'erp_create_employee' );
	const flow      = useEmployeeImport();

	if ( ! canCreate ) {
		return (
			<StepShell
				title={ __( 'Add Your Team', 'erp' ) }
				subtitle={ __( 'Import your employee list from a CSV file.', 'erp' ) }
			>
				<Alert>
					<AlertDescription>
						{ __( 'Importing employees needs permission to add employees. You can skip this step; someone with that permission can import from the Employees page later.', 'erp' ) }
					</AlertDescription>
				</Alert>
				<div className="mt-25 text-center">
					<Button
						type="button"
						disabled={ saving }
						onClick={ () => onNext() }
						className="inline-flex h-auto items-center gap-2 px-8 py-3 text-[15px] font-semibold"
					>
						{ __( 'Skip for Now', 'erp' ) }
					</Button>
				</div>
			</StepShell>
		);
	}

	if ( flow.result ) {
		return (
			<StepShell
				title={ __( 'Employees Imported', 'erp' ) }
				subtitle={ __( 'Here is how your import went.', 'erp' ) }
			>
				<div className="text-left">
					<EmployeeImportResult
						result={ flow.result }
						onReset={ flow.reset }
						onClose={ () => onNext() }
						closeLabel={ __( 'Continue', 'erp' ) }
					/>
				</div>
			</StepShell>
		);
	}

	return (
		<StepShell
			title={ __( 'Add Your Team', 'erp' ) }
			subtitle={ __( 'Import your employee list from a CSV file. This is optional; you can add people later.', 'erp' ) }
		>
			<div className="flex flex-col gap-6 text-left">
				{ ! flow.fileName ? <SampleDataCard disabled={ saving || flow.submitting } onLoaded={ () => onNext() } /> : null }

				<EmployeeImportUpload
					fileRef={ flow.fileRef }
					fileName={ flow.fileName }
					dragging={ flow.dragging }
					setDragging={ flow.setDragging }
					onDrop={ flow.onDrop }
					onFile={ flow.onFile }
					parseError={ flow.parseError }
					submitting={ flow.submitting }
					canImport={ flow.canImport }
					onCancel={ () => onNext() }
					onImport={ flow.runImport }
					cancelLabel={ __( 'Skip for Now', 'erp' ) }
				>
					{ flow.hasMatrix && flow.headers.length > 0 ? (
						<EmployeeImportMapping
							headers={ flow.headers }
							mapping={ flow.mapping }
							onChange={ flow.setColumn }
							requiredMissing={ flow.requiredMissing }
						/>
					) : null }
				</EmployeeImportUpload>
			</div>
		</StepShell>
	);
}
