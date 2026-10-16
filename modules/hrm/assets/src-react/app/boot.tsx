/**
 * Boot helpers — pure functions called once by the page entry.
 *
 * `registerStores` triggers side-effect store registrations.
 * `applyLegacyUrlBridge` translates a legacy `?section=people&...` URL into a
 *  React Router hash route once on first mount.
 */

import { bootApiFetch, readBootPayload } from '@/shared/utils/apiFetch';
import type { BootPayload } from '@/types/global';

import '@/stores';

export function getBootPayload(): BootPayload {
	return readBootPayload();
}

export function initApiFetch(): void {
	bootApiFetch();
}

/**
 * Translate `window.__ERP_HR_LEGACY_URL__` (set by the PHP enqueue helper when
 * the user landed via a legacy bookmark) into the matching React Router hash.
 *
 * Only the `people` section maps to a real route in this deliverable. Other
 * sections render as the default route (`/employees`).
 */
export function applyLegacyUrlBridge(): void {
	const legacy = window.__ERP_HR_LEGACY_URL__;
	if ( ! legacy ) {
		return;
	}

	if ( legacy.section === 'people' && legacy.action === 'view' && legacy.id ) {
		window.location.hash = `#/employees/${ legacy.id }`;
		return;
	}

	if ( legacy.section === 'people' ) {
		window.location.hash = '#/employees';
		return;
	}
}

/**
 * No-op importer — exported for symmetry. The actual registration happens
 * at module-import time via `import '@/stores';` above.
 */
export function registerStores(): void {
	// side-effect handled by static import above
}
