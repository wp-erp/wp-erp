/**
 * Employee CSV import dialog.
 *
 * Pick/drop a CSV → parse client-side → MATCH each CSV column to an ERP field
 * (auto-detected, editable) → POST to the bulk-import endpoint → show a success
 * screen (with a per-row failure table when needed). On any success the employee
 * list + counts are invalidated so the table refreshes.
 *
 * The visual language follows the HRM 2024 redesign (upload dropzone,
 * column-matching step, celebratory success state).
 */

import {
	Dialog,
	DialogContent,
	DialogHeader,
	DialogTitle,
} from '@wedevs/plugin-ui';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { EmployeeImportMapping } from './EmployeeImportMapping';
import { EmployeeImportResult } from './EmployeeImportResult';
import { EmployeeImportUpload } from './EmployeeImportUpload';
import { useEmployeeImport } from './useEmployeeImport';

interface EmployeeImportDialogProps {
	readonly open:    boolean;
	readonly onClose: () => void;
}

export function EmployeeImportDialog( { open, onClose }: EmployeeImportDialogProps ): JSX.Element {
	const flow = useEmployeeImport();

	function close(): void {
		flow.reset();
		onClose();
	}

	return (
		<Dialog open={ open } onOpenChange={ ( next ) => ( next ? undefined : close() ) }>
			<DialogContent className="max-h-[90vh] gap-4 overflow-y-auto rounded-[10px] p-6 sm:max-w-lg">
				<DialogHeader className="space-y-1.5 text-left sm:text-left">
					<DialogTitle className="m-0 mb-4 text-left text-2xl font-bold leading-tight tracking-tight text-foreground">
						{ __( 'Import Employee', 'erp' ) }
					</DialogTitle>
					<p className="mx-auto max-w-md text-sm text-muted-foreground">
						{ __( 'Add employees to your WP ERP by uploading a CSV file.', 'erp' ) }
					</p>
				</DialogHeader>
				<div className="h-px w-full bg-border" />

				{ flow.result ? (
					<EmployeeImportResult result={ flow.result } onReset={ flow.reset } onClose={ close } />
				) : (
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
						onCancel={ close }
						onImport={ flow.runImport }
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
				) }
			</DialogContent>
		</Dialog>
	);
}
