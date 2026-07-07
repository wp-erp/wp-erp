/**
 * "View legacy version" link — a plain native anchor (no SPA interception),
 * exactly like the legacy "View newer version" link rendered by the Vue/PHP
 * side. Clicking does a real top-level navigation to the server switch URL,
 * which saves the engine preference and redirects to the legacy admin.
 */

import { History } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import { useBoot } from '@/shared/hooks/useBoot';

export function LegacyLink(): JSX.Element | null {
	const boot = useBoot();

	if ( ! boot.switchUrl ) {
		return null;
	}

	return (
		<a
			href={ boot.switchUrl }
			className="inline-flex h-8 items-center gap-1.5 rounded-md px-2 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
		>
			<History size={ 14 } strokeWidth={ 1.75 } aria-hidden="true" />
			{ __( 'View legacy version', 'erp' ) }
		</a>
	);
}
