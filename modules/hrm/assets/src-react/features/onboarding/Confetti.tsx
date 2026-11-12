/**
 * A confetti burst centred on its container, for the Ready step.
 *
 * Every piece starts at the centre (where the 🎉 sits), shoots out at its own
 * angle, arcs up and falls away, so the celebration is focused on the card
 * rather than raining from the top of the screen. Two staggered waves, pure
 * CSS (`erp-confetti-burst` in styles/main.css) driven by per-piece custom
 * properties; hidden under `prefers-reduced-motion`, and removed once the
 * last piece has landed.
 */

import { useLayoutEffect, useMemo, useRef, useState } from 'react';
import type { CSSProperties, JSX } from 'react';
import { createPortal } from 'react-dom';

const COLORS = [ '#5744f0', '#22c55e', '#f59e0b', '#ef4444', '#3b82f6', '#ec4899', '#14b8a6' ];

interface ConfettiProps {
	/** Pieces per wave. */
	readonly count?: number;
}

/** One piece's flight: direction and distance out, how high it rises, its spin. */
function piece( index: number, wave: number ): CSSProperties {
	const angle = ( Math.PI * 2 * index ) / 40 + Math.random() * 0.6;
	const distance = 140 + Math.random() * 220;
	const size = 6 + Math.round( Math.random() * 6 );

	return {
		width: `${ size }px`,
		height: `${ size * ( 0.4 + Math.random() * 0.8 ) }px`,
		background: COLORS[ index % COLORS.length ],
		borderRadius: index % 3 === 0 ? '9999px' : '2px',
		animationDelay: `${ wave * 0.45 + Math.random() * 0.15 }s`,
		animationDuration: `${ 1.6 + Math.random() * 0.9 }s`,
		[ '--erp-confetti-dx' as string ]: `${ Math.cos( angle ) * distance }px`,
		[ '--erp-confetti-dy' as string ]: `${ Math.sin( angle ) * distance * 0.6 - 120 }px`,
		[ '--erp-confetti-fall' as string ]: `${ 260 + Math.random() * 200 }px`,
		[ '--erp-confetti-spin' as string ]: `${ ( Math.random() > 0.5 ? 1 : -1 ) * ( 360 + Math.random() * 540 ) }deg`,
	};
}

/** Longest flight: second wave delay + jitter + slowest duration, in ms. */
const BURST_MS = ( 0.45 + 0.15 + 2.5 ) * 1000 + 100;

export function Confetti( { count = 40 }: ConfettiProps ): JSX.Element {
	const anchor = useRef< HTMLSpanElement >( null );
	const [ origin, setOrigin ] = useState< { x: number; y: number } | null >( null );
	const [ done, setDone ] = useState( false );

	// Stable across re-renders: the burst plays once, from the first render.
	const pieces = useMemo< CSSProperties[] >(
		() => [ 0, 1 ].flatMap( ( wave ) => Array.from( { length: count }, ( _, i ) => piece( i, wave ) ) ),
		[ count ]
	);

	// The pieces fly far past the card: fixed to the viewport (and portalled out
	// of any transformed ancestor) they never stretch the page into a scrollbar.
	useLayoutEffect( () => {
		const rect = anchor.current?.getBoundingClientRect();
		if ( rect ) {
			setOrigin( { x: rect.left, y: rect.top } );
		}
		const timer = window.setTimeout( () => setDone( true ), BURST_MS );

		return () => window.clearTimeout( timer );
	}, [] );

	return (
		<span ref={ anchor } className="pointer-events-none absolute left-1/2 top-1/2 size-0" aria-hidden="true">
			{ origin && ! done ? createPortal(
				// Inline styles: the portal sits outside the app root that scopes Tailwind.
				<div style={ { position: 'fixed', left: origin.x, top: origin.y, width: 0, height: 0, zIndex: 100000, pointerEvents: 'none' } } aria-hidden="true">
					{ pieces.map( ( style, i ) => (
						<span key={ i } className="erp-confetti-piece" style={ style } />
					) ) }
				</div>,
				document.body
			) : null }
		</span>
	);
}
