/**
 * Minimal 403 view rendered by CapabilityGate when the current user lacks
 * required capabilities. The PHP layer already gates the admin page itself;
 * this is a defensive client-side fallback.
 */

import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

export function Forbidden(): JSX.Element {
	return (
		<div
			role="alert"
			style={ {
				margin: '64px auto',
				maxWidth: '480px',
				padding: '32px',
				background: 'var(--card)',
				border: '1px solid var(--border)',
				borderRadius: 'var(--radius)',
				textAlign: 'center',
			} }
		>
			<h2 style={ { margin: '0 0 8px', fontSize: '20px' } }>
				{ __( 'No access', 'erp' ) }
			</h2>
			<p style={ { margin: 0, color: 'var(--muted-foreground)' } }>
				{ __(
					'Your account does not have permission to view this page.',
					'erp'
				) }
			</p>
		</div>
	);
}
