/**
 * Text and block entrances, the same motion FlyHR's setup wizard uses (from
 * the FlyForms landing page): a headline arrives word by word, each word
 * blurring in and rising out of its own clipped box, 55ms apart; the lines
 * under it follow softly; a panel settles last.
 *
 * These run on mount with FlyHR's distances, blur, duration and easing
 * (`erp-reveal` in styles/main.css). Reduced motion shows the text at rest.
 */

import { Fragment } from 'react';
import type { CSSProperties, ElementType, JSX, ReactNode } from 'react';

/** How an element arrives: FlyForms' `soft` (lines of text) or `panel` (a card). */
export type Arrival = 'soft' | 'panel';

/** The stagger between words, as FlyForms uses. */
export const WORD_STAGGER_MS = 55;

interface RevealProps {
	readonly children: ReactNode;
	readonly arrival?: Arrival;
	/** Delay before this element starts, in milliseconds. */
	readonly delay?: number;
	readonly as?: ElementType;
	readonly className?: string;
}

/** One element that blurs in and rises into place after `delay`. */
export function Reveal( { children, arrival = 'soft', delay = 0, as: Tag = 'div', className = '' }: RevealProps ): JSX.Element {
	return (
		<Tag
			className={ `erp-reveal erp-reveal-${ arrival } ${ className }` }
			style={ { animationDelay: `${ delay }ms` } as CSSProperties }
		>
			{ children }
		</Tag>
	);
}

interface RevealWordsProps {
	readonly text: string;
	readonly as?: ElementType;
	readonly className?: string;
	/** Delay before the first word, in milliseconds. */
	readonly delay?: number;
}

/**
 * A headline that arrives word by word. The spaces sit outside the clipped
 * boxes, so the sentence still copies and reads as one.
 */
export function RevealWords( { text, as: Tag = 'h1', className = '', delay = 0 }: RevealWordsProps ): JSX.Element {
	const words = text.split( ' ' );

	return (
		<Tag className={ className }>
			{ words.map( ( word, index ) => (
				<Fragment key={ `${ index }-${ word }` }>
					<span className="inline-block overflow-hidden pb-1 align-bottom">
						<Reveal as="span" delay={ delay + index * WORD_STAGGER_MS } className="inline-block">
							{ word }
						</Reveal>
					</span>
					{ index < words.length - 1 ? ' ' : null }
				</Fragment>
			) ) }
		</Tag>
	);
}

/** When the lines under a headline of `text` should start: just after its last word. */
export function afterWords( text: string, delay = 0 ): number {
	return delay + Math.max( 330, text.split( ' ' ).length * WORD_STAGGER_MS + 110 );
}
