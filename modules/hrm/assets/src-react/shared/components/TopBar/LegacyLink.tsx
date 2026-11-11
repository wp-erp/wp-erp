/**
 * "View legacy version" link — a plain native anchor (no SPA interception),
 * exactly like the legacy "View newer version" link rendered by the Vue/PHP
 * side. Clicking does a real top-level navigation to the server switch URL,
 * which saves the engine preference and redirects to the legacy admin.
 *
 * The current route rides along as `erp_route` so the server can land on the
 * matching legacy screen instead of the dashboard — leaving Recruitment should
 * arrive at the job-openings list. Same shape as Dokan's panel switcher, which
 * sends the first hash segment as `legacy_key`.
 */

import { History } from 'lucide-react';
import { useLocation } from 'react-router-dom';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import { useBoot } from '@/shared/hooks/useBoot';

export function LegacyLink(): JSX.Element | null {
	const boot = useBoot();
	// `useLocation` rather than reading window.location.hash directly: the anchor
	// then re-renders on every SPA navigation, so the href always matches the
	// screen the user is looking at.
	const { pathname } = useLocation();

	if ( ! boot.switchUrl ) {
		return null;
	}

	// The switch URL is built raw (literal `&`) by the server precisely so it can
	// be extended here without entity-encoding surprises.
	const href = `${ boot.switchUrl }&erp_route=${ encodeURIComponent( pathname ) }`;

	return (
		<a
			href={ href }
			className="inline-flex h-8 items-center gap-1.5 rounded-md px-2 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
		>
			<History size={ 14 } strokeWidth={ 1.75 } aria-hidden="true" />
			{ __( 'View legacy version', 'erp' ) }
		</a>
	);
}
