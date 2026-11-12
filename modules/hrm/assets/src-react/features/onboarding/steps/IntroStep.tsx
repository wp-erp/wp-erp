/**
 * Introduction: welcome framing screen. No data, no save. The FlyForms
 * landing entrance (`../Reveal.tsx`): the headline word by word, the lines
 * and the button after it, and a floating hero icon.
 */

import { Alert, AlertDescription, Button } from '@wedevs/plugin-ui';
import { Rocket } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { Reveal, RevealWords, afterWords } from '@/shared/motion/Reveal';
import type { StepProps } from '../types';

export function IntroStep( { completed, onNext }: StepProps ): JSX.Element {
	// Re-running setup is safe: every step opens on what is stored, departments,
	// designations and leave policies are only added when missing, and no leave
	// entitlements are created. Each step that is continued saves its fields
	// again, so say that rather than a vague "this may overwrite data".
	const alreadyCompleted = completed;

	const welcome = __( 'Welcome to WP ERP', 'erp' );

	return (
		<div className="mx-auto flex w-full max-w-160 flex-col items-center text-center">
			<Reveal as="span" arrival="panel" className="mb-7 inline-flex size-16 items-center justify-center rounded-2xl bg-primary/10">
				<Rocket className="erp-float size-8 text-primary" strokeWidth={ 1.75 } aria-hidden="true" />
			</Reveal>

			<RevealWords
				text={ welcome }
				delay={ 80 }
				className="m-0 mb-3 text-3xl font-normal leading-9 text-foreground"
			/>

			<Reveal as="p" delay={ afterWords( welcome, 80 ) } className="m-0 mb-10 max-w-md text-base leading-6 text-muted-foreground">
				{ __(
					'Let’s get your workspace ready. A few quick steps to set up your company, team, and schedule.',
					'erp'
				) }
			</Reveal>

			{ alreadyCompleted ? (
				<Alert className="erp-reveal erp-reveal-soft mb-8 text-start" style={ { animationDelay: `${ afterWords( welcome, 80 ) + 60 }ms` } }>
					<AlertDescription>
						<p className="m-0 mb-2 font-medium text-foreground">
							{ __( 'You have already completed setup.', 'erp' ) }
						</p>
						<p className="m-0 text-sm leading-5">
							{ __( 'Running it again is safe. Every step opens on your saved settings, departments, designations and leave policies are only added when they are missing, and no leave entitlements are created or changed. Each step you continue saves its settings again, so check them before you press Next.', 'erp' ) }
						</p>
					</AlertDescription>
				</Alert>
			) : null }

			<Reveal delay={ afterWords( welcome, 80 ) + 90 }>
				<Button type="button" size="lg" onClick={ () => onNext() }>
					{ alreadyCompleted ? __( 'Run Setup Again', 'erp' ) : __( 'Get Started', 'erp' ) }
				</Button>
			</Reveal>
		</div>
	);
}
