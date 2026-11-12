/**
 * Setup wizard entry (`index.php?page=erp-setup`).
 *
 * Mounts the onboarding app into `#erp-hr-app` on the bare setup page that
 * `Admin\SetupWizard` prints, with the same providers, stores and stylesheet
 * as the HR app so it uses the same design system.
 */

import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';

import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { OnboardingPage } from '@/features/onboarding';
import '@/styles/main.css';

import { Providers } from '../providers';
import { initApiFetch, registerStores } from '../boot';

function mount(): void {
	const root = document.getElementById( 'erp-hr-app' );
	if ( ! root ) {
		return;
	}

	initApiFetch();
	registerStores();

	createRoot( root ).render(
		<StrictMode>
			<ErrorBoundary>
				<Providers>
					<OnboardingPage />
				</Providers>
			</ErrorBoundary>
		</StrictMode>
	);
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', mount, { once: true } );
} else {
	mount();
}
