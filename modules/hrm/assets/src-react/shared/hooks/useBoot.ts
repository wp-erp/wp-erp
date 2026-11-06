/**
 * Hook returning the typed `__ERP_HR_BOOT__` payload.
 *
 * Throws in dev when missing — that means the PHP enqueue helper did not run
 * (e.g., wrong screen, bundle missing). In production the same condition is
 * caught by the root ErrorBoundary.
 */

import { readBootPayload } from '@/shared/utils/apiFetch';
import type { BootPayload } from '@/types/global';

export function useBoot(): BootPayload {
	return readBootPayload();
}
