/**
 * Direction hook.
 *
 * Pulled from the boot payload (`is_rtl()` on the PHP side). Static for the
 * lifetime of the page — WP determines this server-side; no JS sniffing.
 */

import type { Direction } from '@/types/global';

import { useBoot } from './useBoot';

export function useDir(): Direction {
	return useBoot().isRTL ? 'rtl' : 'ltr';
}
