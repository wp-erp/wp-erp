/**
 * Success screen for the employee CSV import dialog: a celebratory summary plus
 * a per-row failure table when some rows could not be imported. Presentational —
 * the dialog owns the import result and the reset/close actions.
 */

import { Button, DialogFooter } from '@wedevs/plugin-ui';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';

import type { ImportResult } from './useEmployeeImportExport';

interface EmployeeImportResultProps {
	readonly result:  ImportResult;
	readonly onReset: () => void;
	readonly onClose: () => void;
}

export function EmployeeImportResult( { result, onReset, onClose }: EmployeeImportResultProps ): JSX.Element {
	return (
		<div className="space-y-4">
			<div className="flex flex-col items-center justify-center gap-3 rounded-xl border border-border bg-card px-6 py-10 text-center">
				<span className="text-4xl" aria-hidden="true">🎉</span>
				<div className="space-y-1">
					<h3 className="m-0 text-lg font-semibold text-foreground">
						{ result.failed.length > 0
							? __( 'Import finished', 'erp' )
							: __( 'Successfully Imported', 'erp' ) }
					</h3>
					<p className="text-sm text-muted-foreground">
						{ sprintf(
							/* translators: 1: created count, 2: total count */
							__( '%1$d of %2$d employees have been imported', 'erp' ),
							result.created,
							result.total
						) }
					</p>
				</div>
			</div>

			{ result.failed.length > 0 ? (
				<div className="max-h-56 overflow-auto rounded-md border border-border">
					<table className="w-full min-w-[22rem] text-left text-sm">
						<thead className="bg-muted/50 text-xs text-muted-foreground">
							<tr>
								<th className="px-3 py-2">{ __( 'Row', 'erp' ) }</th>
								<th className="px-3 py-2">{ __( 'Email', 'erp' ) }</th>
								<th className="px-3 py-2">{ __( 'Error', 'erp' ) }</th>
							</tr>
						</thead>
						<tbody>
							{ result.failed.map( ( f ) => (
								<tr key={ `${ f.row }-${ f.email }` } className="border-t border-border">
									<td className="px-3 py-2 tabular-nums">{ f.row }</td>
									<td className="px-3 py-2">{ f.email }</td>
									<td className="px-3 py-2 text-destructive">{ f.message }</td>
								</tr>
							) ) }
						</tbody>
					</table>
				</div>
			) : null }

			<DialogFooter className="gap-5 sm:gap-5">
				<Button type="button" variant="outline" className="h-10 px-6" onClick={ onReset }>
					{ __( 'Import another file', 'erp' ) }
				</Button>
				<Button type="button" className="h-10 px-6" onClick={ onClose }>
					{ __( 'Done', 'erp' ) }
				</Button>
			</DialogFooter>
		</div>
	);
}
