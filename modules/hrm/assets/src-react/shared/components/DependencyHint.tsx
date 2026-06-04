/**
 * Reusable "you need to set something up first" hint.
 *
 * Whenever a form requires a prerequisite that may not exist yet (a department
 * before an employee, a leave type before a policy, a policy before an
 * entitlement…), render this instead of a dead-end empty dropdown. It shows a
 * short message plus the fix steps in dependency order — each a link that
 * (optionally) closes the host dialog and routes to the setup screen.
 */

import { Alert, AlertDescription } from '@wedevs/plugin-ui';
import { ArrowRight } from 'lucide-react';
import type { JSX } from 'react';
import { useNavigate } from 'react-router-dom';

export interface DependencyStep {
	readonly label: string;
	readonly path:  string;
}

interface DependencyHintProps {
	readonly message: string;
	readonly steps:   readonly DependencyStep[];
	/** Called before navigating — e.g. to close the host dialog. */
	readonly onBeforeNavigate?: () => void;
}

export function DependencyHint( { message, steps, onBeforeNavigate }: DependencyHintProps ): JSX.Element {
	const navigate = useNavigate();

	function go( path: string ): void {
		onBeforeNavigate?.();
		navigate( path );
	}

	return (
		<Alert variant="destructive">
			<AlertDescription>
				<p className="m-0">{ message }</p>
				<div className="mt-2 flex flex-col gap-1 text-sm">
					{ steps.map( ( step ) => (
						<button
							key={ step.path + step.label }
							type="button"
							className="inline-flex items-center gap-1 font-medium text-primary underline-offset-2 hover:underline"
							onClick={ () => go( step.path ) }
						>
							{ step.label }
							<ArrowRight size={ 13 } aria-hidden="true" />
						</button>
					) ) }
				</div>
			</AlertDescription>
		</Alert>
	);
}
