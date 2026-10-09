/**
 * Shared context for the People → Requests tabbed aggregator.
 *
 * `RequestsTabContext` — true when a request-type page (Leave / Resignation /
 * Remote Work / …) is rendered inside the tabs, so the child hides its own
 * redundant title + header and routes its primary action into the tab row.
 *
 * `RequestsActionSlotContext` — the DOM node at the right of the tab row where
 * the active child portals its "New Request" button (kept inline with the tabs
 * instead of on a separate row below).
 *
 * `RequestsCountsContext` - asks the Requests page to refetch its tab badges
 * after a child changes a request (approve, reject, delete, create). Both the
 * provider and the default also refresh the top-bar pending badge, so a list
 * mounted outside the Requests page keeps that badge current too.
 */

import { createContext } from 'react';

import { refreshRequestsPendingCount } from '@/shared/hooks/useRequestsPendingCount';

export const RequestsTabContext = createContext( false );

export const RequestsActionSlotContext = createContext< HTMLElement | null >( null );

export const RequestsCountsContext = createContext< () => void >( () => {
	void refreshRequestsPendingCount();
} );
