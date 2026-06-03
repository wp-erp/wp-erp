/**
 * Page entry — the single webpack entry for the first deliverable.
 *
 * Mounts the React shell into `#erp-hr-app`. Idempotent across HMR.
 */

import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { RouterProvider } from 'react-router-dom';
import { doAction } from '@wordpress/hooks';

import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { ACTIONS } from '@/shared/filters';
import '@/styles/main.css';

import { Providers } from '../providers';
import {
	applyLegacyUrlBridge,
	getBootPayload,
	initApiFetch,
	registerStores,
} from '../boot';
import { hashRouter } from '../router';

const MOUNT_ID = 'erp-hr-app';

function mount(): void {
	const root = document.getElementById( MOUNT_ID );
	if ( ! root ) {
		// PHP enqueue helper did not inject the mount node. Surface a console
		// warning in dev; production stays silent because the legacy engine
		// is still rendering the page (resolver fell through).
		if ( typeof window !== 'undefined' && ( window as { wp?: { debug?: boolean } } ).wp?.debug ) {
			// eslint-disable-next-line no-console
			console.warn( `[erp-hr] mount node #${ MOUNT_ID } missing — React shell did not mount.` );
		}
		return;
	}

	const boot = getBootPayload();

	initApiFetch();
	registerStores();
	applyLegacyUrlBridge();

	createRoot( root ).render(
		<StrictMode>
			<ErrorBoundary>
				<Providers>
					<RouterProvider router={ hashRouter } />
				</Providers>
			</ErrorBoundary>
		</StrictMode>
	);

	doAction( ACTIONS.SHELL_READY, boot );
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', mount, { once: true } );
} else {
	mount();
}
