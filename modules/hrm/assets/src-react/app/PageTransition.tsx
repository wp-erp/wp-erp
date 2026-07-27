/**
 * Fades each page in and cascades its top-level sections.
 *
 * Ported from WP Project Manager's `PageTransition`. The mechanism is a keyed
 * remount: React throws away the old subtree when the key changes, so the CSS
 * animation on the new one replays. No animation library, no exit animation —
 * the outgoing page simply goes.
 *
 * The key is a *normalised* pathname, not the raw one. Routes that open a
 * panel or dialog over the page they belong to (an employee's tab, a request
 * detail) must not re-key: remounting there would tear down the page behind
 * the overlay, and any URL-sync a dialog does would fight the remount. Only a
 * genuine page change animates.
 */

import { Outlet, useLocation } from 'react-router-dom';
import type { JSX } from 'react';

/**
 * Collapse URLs that render over their parent page down to that parent, so
 * opening one doesn't count as navigation.
 *
 * `?tab=` lives in the query string and never reaches here — this is only
 * about path segments that are really overlays.
 */
function pageKey( pathname: string ): string {
	return pathname
		// `/employees/42/edit` edits in place above the profile.
		.replace( /\/edit\/?$/, '' )
		// Profile preview routes are the same page in another skin.
		.replace( /\/profile(-v\d)?\/?$/, '' );
}

export function PageTransition(): JSX.Element {
	const { pathname } = useLocation();

	return (
		<div key={ pageKey( pathname ) } className="erp-page-transition">
			<Outlet />
		</div>
	);
}
