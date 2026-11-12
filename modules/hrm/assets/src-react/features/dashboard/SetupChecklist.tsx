/**
 * Dashboard setup checklist: the required settings, which are done and which remain.
 *
 * A new company lands on a dashboard of empty cards, which shows what is
 * missing without saying what to do about it. This card sits above them and
 * turns that into a short list of next steps, each with a button to the
 * screen that does it. Steps are ticked from live data
 * (`GET erp/v2/onboarding/checklist`), so work done through the setup wizard,
 * an import or sample data already counts. Ported from FlyHR's dashboard.
 *
 * Site administrators only: the route needs `manage_options`, and the card is
 * not rendered for anyone else. It hides itself once every step is done, or
 * when the person dismisses it.
 */

import { Button } from '@wedevs/plugin-ui';
import { Check, ListChecks, X } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { JSX } from 'react';
import { useNavigate } from 'react-router-dom';

import { useCan } from '@/shared/hooks/useCan';
import { __, sprintf } from '@/shared/i18n';
import { request, restPath } from '@/shared/utils/apiFetch';

interface ChecklistStep {
	readonly key: string;
	readonly label: string;
	readonly description: string;
	readonly action: string;
	readonly route?: string;
	readonly url?: string;
	readonly done: boolean;
}

interface ChecklistState {
	readonly steps: readonly ChecklistStep[];
	readonly completed: number;
	readonly total: number;
	readonly done: boolean;
	readonly dismissed: boolean;
	/** The setup wizard, to run again from the card. */
	readonly wizardUrl?: string;
}

function ChecklistCard(): JSX.Element | null {
	const [ state, setState ] = useState< ChecklistState | null >( null );
	const navigate = useNavigate();

	useEffect( () => {
		let active = true;

		request< ChecklistState >( restPath( 'v2', '/onboarding/checklist' ) )
			.then( ( result ) => {
				if ( active ) {
					setState( result );
				}
			} )
			.catch( () => {
				// A checklist that cannot load is not worth an error message.
			} );

		return () => {
			active = false;
		};
	}, [] );

	function dismiss(): void {
		setState( null );

		request( restPath( 'v2', '/onboarding/checklist/dismiss' ), {
			method: 'POST',
		} ).catch( () => {
			// Already hidden; a failed write only means it may come back.
		} );
	}

	function open( step: ChecklistStep ): void {
		if ( step.url ) {
			window.location.href = step.url;
			return;
		}

		if ( step.route ) {
			navigate( step.route, { viewTransition: true } );
		}
	}

	if ( ! state || state.dismissed || state.done || state.total === 0 ) {
		return null;
	}

	const percent = Math.round( ( state.completed / state.total ) * 100 );

	return (
		<section
			aria-labelledby="erp-setup-checklist-title"
			className="erp-card-in mb-6 rounded-[10px] border border-border bg-card p-6 shadow-sm"
		>
			<div className="flex items-start justify-between gap-4">
				<div className="flex items-start gap-3">
					<span className="inline-flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
						<ListChecks
							size={ 16 }
							strokeWidth={ 1.9 }
							aria-hidden="true"
						/>
					</span>
					<div>
						<h2
							id="erp-setup-checklist-title"
							className="text-base font-semibold leading-6 text-foreground"
						>
							{ __( 'Finish setting up WP ERP', 'erp' ) }
						</h2>
						<p className="mt-0.5 text-sm text-muted-foreground">
							{ sprintf(
								/* translators: 1: completed step count, 2: total step count. */
								__( '%1$d of %2$d done', 'erp' ),
								state.completed,
								state.total
							) }
						</p>
					</div>
				</div>

				<div className="flex shrink-0 items-center gap-2">
					{ state.wizardUrl ? (
						<Button
							type="button"
							size="sm"
							variant="outline"
							onClick={ () => {
								window.location.href = state.wizardUrl ?? '';
							} }
						>
							{ __( 'Run setup wizard', 'erp' ) }
						</Button>
					) : null }
				<Button
					type="button"
					variant="ghost"
					size="icon"
					className="text-muted-foreground"
					onClick={ dismiss }
					aria-label={ __( 'Dismiss setup checklist', 'erp' ) }
					title={ __( 'Dismiss', 'erp' ) }
				>
					<X className="size-4" aria-hidden="true" />
				</Button>
				</div>
			</div>

			<div
				className="mt-4 h-1.5 w-full overflow-hidden rounded-full bg-muted"
				role="progressbar"
				aria-label={ __( 'Setup progress', 'erp' ) }
				aria-valuenow={ percent }
				aria-valuemin={ 0 }
				aria-valuemax={ 100 }
			>
				<div
					className="h-full rounded-full bg-primary transition-all duration-500"
					style={ { width: `${ percent }%` } }
				/>
			</div>

			<ul className="mt-4 flex flex-col gap-1">
				{ state.steps.map( ( step ) => (
					<li
						key={ step.key }
						className="flex flex-wrap items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-muted/50"
					>
						<span
							className={ `flex size-5 shrink-0 items-center justify-center rounded-full border ${
								step.done
									? 'border-primary bg-primary text-primary-foreground'
									: 'border-border bg-card'
							}` }
						>
							{ step.done ? (
								<Check
									className="size-3"
									strokeWidth={ 3 }
									aria-hidden="true"
								/>
							) : null }
							<span className="sr-only">
								{ step.done
									? __( 'Done', 'erp' )
									: __( 'To do', 'erp' ) }
							</span>
						</span>

						<span className="min-w-48 flex-1">
							<span
								className={ `block text-sm font-medium ${
									step.done
										? 'text-muted-foreground line-through'
										: 'text-foreground'
								}` }
							>
								{ step.label }
							</span>
							{ ! step.done ? (
								<span className="block text-xs text-muted-foreground">
									{ step.description }
								</span>
							) : null }
						</span>

						{ ! step.done ? (
							<Button
								type="button"
								size="sm"
								variant="outline"
								onClick={ () => open( step ) }
							>
								{ step.action }
							</Button>
						) : null }
					</li>
				) ) }
			</ul>
		</section>
	);
}

export function SetupChecklist(): JSX.Element | null {
	const canManageSettings = useCan( 'manage_options' );

	return canManageSettings ? <ChecklistCard /> : null;
}
