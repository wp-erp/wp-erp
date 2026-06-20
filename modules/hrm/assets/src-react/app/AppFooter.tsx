/**
 * Shell footer — bottom-of-page meta strip.
 *
 * Houses the "View previous version" engine-switch link (relocated from the
 * top bar) plus a small copyright line.
 */

import type { JSX } from 'react';

import { LegacyLink } from '@/shared/components/TopBar/LegacyLink';
import { __ } from '@/shared/i18n';

export function AppFooter(): JSX.Element {
	const year = new Date().getFullYear();

	return (
		<footer
			role="contentinfo"
			className="border-t border-border bg-card"
		>
			<div className="mx-auto flex w-full max-w-full flex-wrap items-center justify-between gap-3 px-6 py-3 text-xs text-muted-foreground">
				<span>
					{ __( '© ', 'erp' ) }{ year }{ ' ' }
					{ __( 'WP-ERP HR · Built with care', 'erp' ) }
				</span>
				<LegacyLink />
			</div>
		</footer>
	);
}
