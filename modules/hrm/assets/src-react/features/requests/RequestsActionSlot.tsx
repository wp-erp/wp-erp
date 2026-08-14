/**
 * Portals a request page's primary action (e.g. "New Request") into the tab-row
 * action slot, so it renders inline with the tabs instead of on its own row.
 * Renders nothing when the page is not mounted inside the Requests tabs.
 */

import { useContext } from 'react';
import { createPortal } from 'react-dom';
import type { JSX, ReactNode } from 'react';

import { RequestsActionSlotContext } from './requests-tab-context';

export function RequestsActionSlot( { children }: { readonly children: ReactNode } ): JSX.Element | null {
	const el = useContext( RequestsActionSlotContext );
	return el ? createPortal( children, el ) : null;
}
