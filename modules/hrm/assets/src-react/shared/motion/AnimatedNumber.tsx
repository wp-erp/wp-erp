/**
 * A number that counts up to its value.
 *
 * Timed with the card it sits in, so a dashboard reads as one motion: the card
 * rises (`.erp-card-in`, staggered by position), and its number starts
 * counting the moment that card has landed. No index plumbing: the number
 * listens for its own card's `animationend`. A card with no entrance running
 * (reduced motion, or a re-render long after the page painted) counts at once.
 *
 * When the value changes later (Refresh, a filter), it counts from the number
 * on screen to the new one and its card gives one soft ring
 * (`.erp-card-updated`), so a change is noticed without hunting for it.
 */

import { useEffect, useRef, useState } from 'react';
import type { JSX } from 'react';

import { prefersReducedMotion, useMotionReady } from './MotionContext';

/** Count length, and the decelerating curve the cards themselves use. */
const DURATION_MS = 900;
const easeOutCubic = ( t: number ): number => 1 - Math.pow( 1 - t, 3 );

interface AnimatedNumberProps {
	readonly value: number;
	readonly className?: string;
	/** How to print the number; defaults to a rounded integer. */
	readonly format?: ( n: number ) => string;
}

export function AnimatedNumber( { value, className, format }: AnimatedNumberProps ): JSX.Element {
	const ready = useMotionReady();
	const ref = useRef< HTMLSpanElement >( null );
	const shown = useRef( 0 );
	// The first count is the page arriving; later ones are real changes.
	const counted = useRef( false );
	const [ display, setDisplay ] = useState( 0 );

	useEffect( () => {
		const target = Number.isFinite( value ) ? value : 0;

		if ( prefersReducedMotion() ) {
			shown.current = target;
			setDisplay( target );
			return undefined;
		}
		if ( ! ready ) {
			return undefined;
		}

		let frame = 0;
		let cancelled = false;
		const from = shown.current;

		const run = (): void => {
			if ( cancelled ) {
				return;
			}
			if ( counted.current && from !== target ) {
				const box = ref.current?.closest( '.erp-card-in' ) as HTMLElement | null;
				if ( box ) {
					box.classList.remove( 'erp-card-updated' );
					void box.offsetWidth; // restart the ring if it is already running
					box.classList.add( 'erp-card-updated' );
					window.setTimeout( () => box.classList.remove( 'erp-card-updated' ), 950 );
				}
			}
			counted.current = true;
			const start = performance.now();
			const tick = ( now: number ): void => {
				// A frame's timestamp can sit just before `start`; never count backwards.
				const t = Math.min( 1, Math.max( 0, ( now - start ) / DURATION_MS ) );
				const next = from + ( target - from ) * easeOutCubic( t );
				shown.current = next;
				setDisplay( next );
				if ( t < 1 && ! cancelled ) {
					frame = requestAnimationFrame( tick );
				}
			};
			frame = requestAnimationFrame( tick );
		};

		// Wait for this card's own entrance, if one is still running.
		const card = ref.current?.closest( '.erp-card-in' ) as HTMLElement | null;
		const entering = card?.getAnimations?.().some( ( a ) => a.playState !== 'finished' ) ?? false;

		if ( card && entering ) {
			const onEnd = ( e: AnimationEvent ): void => {
				if ( e.target === card ) {
					card.removeEventListener( 'animationend', onEnd );
					run();
				}
			};
			card.addEventListener( 'animationend', onEnd );
			return () => {
				cancelled = true;
				card.removeEventListener( 'animationend', onEnd );
				cancelAnimationFrame( frame );
			};
		}

		run();
		return () => {
			cancelled = true;
			cancelAnimationFrame( frame );
		};
	}, [ value, ready ] );

	const text = format ? format( display ) : String( Math.round( display ) );

	return (
		<span ref={ ref } className={ `tabular-nums ${ className ?? '' }` }>
			{ text }
		</span>
	);
}
