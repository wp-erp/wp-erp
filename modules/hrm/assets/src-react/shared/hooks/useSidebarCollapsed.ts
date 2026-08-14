/**
 * Sidebar collapse hook + setter.
 *
 * Tracks whether the vertical sidebar is in its minimal, icon-only state. This
 * is a pure view preference (not a server-side `me` preference like the
 * topbar/sidebar layout choice), so it persists locally in `localStorage` and
 * never round-trips to REST. Reads are guarded so a locked-down storage
 * (private mode, disabled cookies) degrades to "expanded" instead of throwing.
 */

import { useCallback, useState } from 'react';

const STORAGE_KEY = 'erp-hr-sidebar-collapsed';

function readCollapsed(): boolean {
	try {
		return window.localStorage.getItem( STORAGE_KEY ) === '1';
	} catch {
		return false;
	}
}

export interface UseSidebarCollapsedResult {
	readonly collapsed: boolean;
	readonly toggle:    () => void;
}

export function useSidebarCollapsed(): UseSidebarCollapsedResult {
	const [ collapsed, setCollapsed ] = useState< boolean >( readCollapsed );

	const toggle = useCallback( () => {
		setCollapsed( ( prev ) => {
			const next = ! prev;
			try {
				window.localStorage.setItem( STORAGE_KEY, next ? '1' : '0' );
			} catch {
				/* storage unavailable — keep the in-memory state only */
			}
			return next;
		} );
	}, [] );

	return { collapsed, toggle };
}
