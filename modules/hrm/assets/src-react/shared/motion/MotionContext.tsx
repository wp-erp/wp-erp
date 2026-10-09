/**
 * Whether the page under the shell may play its entrance yet.
 *
 * Normally it may at once. While the welcome screen covers the app, the page
 * beneath it waits (its CSS entrances are paused by `.erp-motion-hold`), and
 * the JavaScript-driven motion, chart drawing and number counting, reads this
 * flag to wait with it. When the welcome closes, everything starts together.
 */

import { createContext, useContext } from 'react';

const MotionReady = createContext< boolean >( true );

export const MotionProvider = MotionReady.Provider;

/** True once the page's entrance may run. */
export function useMotionReady(): boolean {
	return useContext( MotionReady );
}

/** True when the reader asked the system for less motion. */
export function prefersReducedMotion(): boolean {
	return typeof window !== 'undefined'
		&& typeof window.matchMedia === 'function'
		&& window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
}
