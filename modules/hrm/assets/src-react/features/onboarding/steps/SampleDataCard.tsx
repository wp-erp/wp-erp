/**
 * "Just looking around?": a small offer on the Add Your Team step to fill the
 * site with sample data, so every screen has something to show at once.
 *
 * A confirm explains what will be added before anything is written, the
 * dialog holds a loader while the seeders run (a few seconds), and on success
 * the wizard moves on. The import above it is untouched.
 */

import {
	AlertDialog,
	AlertDialogCancel,
	AlertDialogContent,
	AlertDialogDescription,
	AlertDialogFooter,
	AlertDialogHeader,
	AlertDialogTitle,
	Button,
	Spinner,
	toast,
} from '@wedevs/plugin-ui';
import { Sparkles, TriangleAlert } from 'lucide-react';
import { useState } from 'react';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';

import { loadSampleData } from '../api';

interface SampleDataCardProps {
	/** Disables the offer while the step itself is busy. */
	readonly disabled: boolean;
	/** Called once the sample is in, to move the wizard on. */
	readonly onLoaded: () => void;
}

export function SampleDataCard( { disabled, onLoaded }: SampleDataCardProps ): JSX.Element {
	const [ open, setOpen ] = useState( false );
	const [ busy, setBusy ] = useState( false );

	function run(): void {
		setBusy( true );
		loadSampleData()
			.then( ( r ) => {
				toast.success(
					sprintf(
						/* translators: 1: employee count, 2: leave request count. */
						__( 'Sample data loaded: %1$d employees and %2$d leave requests.', 'erp' ),
						r.employees,
						r.requests
					)
				);
				setOpen( false );
				onLoaded();
			} )
			.catch( ( e: ApiError ) => {
				if ( 'erp_sample_data_exists' === e?.code ) {
					toast.info( __( 'Sample data is already on this site.', 'erp' ) );
					setOpen( false );
					onLoaded();
					return;
				}
				toast.error( e?.message || __( 'Sample data could not be loaded. Please try again.', 'erp' ) );
			} )
			.finally( () => setBusy( false ) );
	}

	return (
		<>
			<div className="flex flex-wrap items-center gap-4 rounded-[10px] border border-dashed border-border bg-muted/30 px-5 py-4">
				<span className="inline-flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
					<Sparkles size={ 18 } aria-hidden="true" />
				</span>
				<div className="min-w-0 flex-1">
					<p className="m-0 text-sm font-semibold text-foreground">{ __( 'Just looking around?', 'erp' ) }</p>
					<p className="m-0 mt-0.5 text-sm text-muted-foreground">
						{ __( 'Load sample data to see every screen filled in: employees, departments, leave types and requests.', 'erp' ) }
					</p>
				</div>
				<Button type="button" variant="outline" size="sm" disabled={ disabled } onClick={ () => setOpen( true ) }>
					{ __( 'Load sample data', 'erp' ) }
				</Button>
			</div>

			<AlertDialog open={ open } onOpenChange={ ( next ) => ( next || busy ? undefined : setOpen( false ) ) }>
				<AlertDialogContent>
					<AlertDialogHeader>
						<AlertDialogTitle>{ __( 'Load sample data?', 'erp' ) }</AlertDialogTitle>
						<AlertDialogDescription>
							{ __( 'This adds 20 sample employees with departments, designations, a leave year, leave types and policies, their leave balances, holidays and a few pending leave requests.', 'erp' ) }
						</AlertDialogDescription>
					</AlertDialogHeader>

					<div className="flex gap-2.5 rounded-md bg-warning/10 p-3 text-sm text-foreground">
						<TriangleAlert size={ 16 } className="mt-0.5 shrink-0 text-warning" aria-hidden="true" />
						<span>
							{ __( 'The sample people are real WordPress accounts (on @wperp-sample.test addresses, so no email reaches anyone) and stay until you delete them. Best on a test or demo site.', 'erp' ) }
						</span>
					</div>

					{ busy ? (
						<div role="status" aria-live="polite" className="flex items-center gap-2 text-sm text-muted-foreground">
							<Spinner className="size-4" />
							{ __( 'Adding sample data, this takes a few seconds…', 'erp' ) }
						</div>
					) : null }

					<AlertDialogFooter>
						<AlertDialogCancel disabled={ busy } onClick={ () => setOpen( false ) }>
							{ __( 'Cancel', 'erp' ) }
						</AlertDialogCancel>
						<Button type="button" disabled={ busy } onClick={ run } className="gap-2">
							{ busy ? <Spinner className="size-4" /> : null }
							{ busy ? __( 'Loading…', 'erp' ) : __( 'Load sample data', 'erp' ) }
						</Button>
					</AlertDialogFooter>
				</AlertDialogContent>
			</AlertDialog>
		</>
	);
}
