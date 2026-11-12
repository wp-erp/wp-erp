/**
 * Shared per-step chrome.
 *
 * Setup-content spacing in Tailwind v4: the column is 640px and centered
 * (`max-w-160 mx-auto`); heading
 * `text-30px font-normal leading-9 text-center m-0 mb-3` → `text-[30px] …`;
 * subtitle `text-base leading-6 m-0 mb-16` (the Workday step uses `mb-20`, hence
 * `subtitleSpacing`).
 */

import type { JSX, ReactNode } from 'react';

import { Reveal, RevealWords, afterWords } from '@/shared/motion/Reveal';

interface StepShellProps {
	readonly title: string;
	readonly subtitle: string;
	readonly children: ReactNode;
	/** Bottom margin under the subtitle: `mb-16` by default, Workday uses `mb-20`. */
	readonly subtitleSpacing?: string;
}

export function StepShell( {
	title,
	subtitle,
	children,
	subtitleSpacing = 'mb-16',
}: StepShellProps ): JSX.Element {
	return (
		<div className="mx-auto w-full max-w-160">
			{ /* FlyForms' arrival: the title word by word, the subtitle softly
			   after its last word, the step's fields settling in last. */ }
			<RevealWords
				text={ title }
				className="m-0 mb-3 text-center text-[30px] font-normal leading-9 text-foreground"
			/>
			<Reveal
				as="p"
				delay={ afterWords( title ) }
				className={ `m-0 ${ subtitleSpacing } text-center text-base leading-6 text-muted-foreground` }
			>
				{ subtitle }
			</Reveal>
			<Reveal arrival="panel" delay={ afterWords( title ) + 90 }>
				{ children }
			</Reveal>
		</div>
	);
}
