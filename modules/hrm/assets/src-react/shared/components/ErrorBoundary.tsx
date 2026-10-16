/**
 * Generic React error boundary.
 *
 * Used at the root mount + per-route inside AppShell. Renders a minimal
 * recovery card. Console-logs the error only when `WP_DEBUG` is on
 * (heuristic: `window.wp?.debug`).
 */

import { Component } from 'react';
import type { ErrorInfo, ReactNode } from 'react';

import { __ } from '@/shared/i18n';

interface ErrorBoundaryProps {
	readonly children: ReactNode;
	readonly fallback?: ReactNode;
}

interface ErrorBoundaryState {
	readonly error: Error | null;
}

export class ErrorBoundary extends Component< ErrorBoundaryProps, ErrorBoundaryState > {
	public override state: ErrorBoundaryState = { error: null };

	public static getDerivedStateFromError( error: Error ): ErrorBoundaryState {
		return { error };
	}

	public override componentDidCatch( error: Error, info: ErrorInfo ): void {
		if ( typeof window !== 'undefined' && ( window as { wp?: { debug?: boolean } } ).wp?.debug ) {
			// eslint-disable-next-line no-console
			console.error( '[erp-hr] React error', error, info );
		}
	}

	public override render(): ReactNode {
		if ( ! this.state.error ) {
			return this.props.children;
		}

		if ( this.props.fallback ) {
			return this.props.fallback;
		}

		return (
			<div
				role="alert"
				style={ {
					padding: '24px',
					background: 'var(--card)',
					border: '1px solid var(--border)',
					borderRadius: 'var(--radius)',
					color: 'var(--card-foreground)',
				} }
			>
				<h2 style={ { margin: '0 0 8px', fontSize: '18px' } }>
					{ __( 'Something went wrong.', 'erp' ) }
				</h2>
				<p style={ { margin: '0 0 16px', color: 'var(--muted-foreground)' } }>
					{ __(
						'The HR admin app could not render this view. Try reloading the page.',
						'erp'
					) }
				</p>
				<button
					type="button"
					onClick={ () => window.location.reload() }
					style={ {
						background: 'var(--primary)',
						color: 'var(--primary-foreground)',
						border: 'none',
						padding: '8px 16px',
						borderRadius: 'var(--radius)',
						cursor: 'pointer',
					} }
				>
					{ __( 'Reload', 'erp' ) }
				</button>
			</div>
		);
	}
}
