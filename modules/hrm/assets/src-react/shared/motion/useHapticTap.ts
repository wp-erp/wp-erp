/**
 * A short haptic tick when a button is pressed by touch, on devices that
 * support web vibration (Android browsers; iOS Safari has no vibration API,
 * so there the visual press in the stylesheet is the feedback).
 *
 * Function-first: only enabled buttons, only touch (a mouse or trackpad has
 * its own click), one 10ms pulse, and nothing for readers who asked for less
 * motion. One listener on the document covers every button in the app.
 */

import { useEffect } from 'react';

import { prefersReducedMotion } from './MotionContext';

export function useHapticTap(): void {
	useEffect( () => {
		if ( typeof navigator === 'undefined' || typeof navigator.vibrate !== 'function' ) {
			return undefined;
		}

		const onPress = ( e: PointerEvent ): void => {
			if ( e.pointerType !== 'touch' || prefersReducedMotion() ) {
				return;
			}
			const target = e.target as Element | null;
			const button = target?.closest( '[data-slot="button"], button' ) as HTMLButtonElement | null;
			if ( button && ! button.disabled && button.getAttribute( 'aria-disabled' ) !== 'true' ) {
				navigator.vibrate( 10 );
			}
		};

		document.addEventListener( 'pointerdown', onPress, { passive: true } );
		return () => document.removeEventListener( 'pointerdown', onPress );
	}, [] );
}
