/**
 * Keeps the page still behind an open dialog.
 *
 * Base UI locks the page while a dialog is open (`overflow: hidden` on the
 * body), but that only stops the reader scrolling it: code can still move it.
 * plugin-ui's searchable select does exactly that: on open it calls
 * `scrollIntoView()` on the chosen option before the popup is positioned,
 * while the option still sits at the top of the document, so the page under
 * the dialog jumps to the top and stays there after the dialog closes.
 *
 * While the lock is on, any scroll of the page itself is put back where the
 * reader left it. Scroll events run before the next paint, so the jump is
 * never seen. Scrolling inside the dialog or a list is not the page and is
 * left alone. One listener covers every dialog in the app, and the Pro screens that
 * render inside it.
 */

import { useEffect } from 'react';

function pageLocked(): boolean {
	const body = document.body.style;
	return body.overflow === 'hidden' || body.overflowY === 'hidden';
}

export function useScrollLockGuard(): void {
	useEffect( () => {
		let x = window.scrollX;
		let y = window.scrollY;

		const onScroll = ( e: Event ): void => {
			if ( e.target !== document ) {
				return;
			}
			if ( ! pageLocked() ) {
				x = window.scrollX;
				y = window.scrollY;
				return;
			}
			if ( window.scrollX !== x || window.scrollY !== y ) {
				window.scrollTo( x, y );
			}
		};

		window.addEventListener( 'scroll', onScroll, { passive: true } );
		return () => window.removeEventListener( 'scroll', onScroll );
	}, [] );
}
