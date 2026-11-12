/**
 * Segmented progress bar + step counter + error banner.
 *
 * Progress spacing in Tailwind v4: container is 640px wide and centered
 * (`w-full max-w-160 … mb-16 gap-6`), bar row `flex items-center gap-4`, bars
 * `flex gap-5 flex-1` each `h-0.5`, counter `text-base leading-6 font-medium`,
 * step label `text-sm leading-[14px]`. Colors come from the HR tokens.
 */

import { Alert, AlertDescription } from '@wedevs/plugin-ui';
import type { JSX } from 'react';

interface OnboardingProgressProps {
	readonly currentStep: number;
	readonly totalSteps: number;
	readonly stepName?: string;
	readonly error?: string | null;
}

export function OnboardingProgress( {
	currentStep,
	totalSteps,
	stepName,
	error,
}: OnboardingProgressProps ): JSX.Element {
	return (
		<div className="mx-auto mb-16 flex w-full max-w-160 flex-col gap-6">
			<div className="flex items-center gap-4">
				<div className="flex flex-1 gap-5">
					{ Array.from( { length: totalSteps } ).map( ( _, index ) => (
						<div
							key={ index }
							className={ [
								'h-0.5 flex-1 transition-colors duration-300',
								index < currentStep ? 'bg-primary' : 'bg-border',
							].join( ' ' ) }
						/>
					) ) }
				</div>
				<div className="shrink-0 whitespace-nowrap text-base font-medium leading-6 text-foreground">
					{ currentStep }/{ totalSteps }
				</div>
			</div>

			{ stepName ? (
				<div className="text-center text-sm font-normal leading-3.5 text-muted-foreground">
					{ stepName }
				</div>
			) : null }

			{ error ? (
				<Alert variant="destructive">
					<AlertDescription>{ error }</AlertDescription>
				</Alert>
			) : null }
		</div>
	);
}
