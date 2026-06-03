/**
 * Layout chrome — `<TopBar/>` slot + `<Outlet/>` for the active route.
 *
 * Routes pass through their own ErrorBoundary; this one wraps the whole shell
 * so a TopBar crash doesn't kill the route content (and vice versa).
 */

import { Outlet, useLocation } from 'react-router-dom';
import type { JSX } from 'react';

import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { TopBar } from '@/shared/components/TopBar';
import { EmployeeActionsProvider } from '@/features/employees/actions/EmployeeActionsContext';

import { AppFooter } from './AppFooter';

/**
 * Routes that own their own full-bleed background (no shell padding, no gray
 * canvas). Each route prefix listed here renders edge-to-edge under the top bar.
 */
const CHROMELESS_PREFIXES: readonly string[] = [];

// Employee create / edit / view share the standard gray-canvas chrome (the
// `<main>` inset below), so they are intentionally NOT listed here.
const CHROMELESS_PATTERNS: readonly RegExp[] = [];

function isChromeless( pathname: string ): boolean {
	return (
		CHROMELESS_PREFIXES.some( ( prefix ) => pathname === prefix || pathname.startsWith( prefix + '/' ) ) ||
		CHROMELESS_PATTERNS.some( ( re ) => re.test( pathname ) )
	);
}

export function AppShell(): JSX.Element {
	const { pathname } = useLocation();
	const chromeless   = isChromeless( pathname );

	return (
		<div className="erp-hr-shell flex min-h-[calc(100vh-32px)] flex-col bg-background text-foreground">
			<TopBar />
			<main className={ chromeless ? 'flex-1' : 'erp-hr-panel flex-1 px-6 py-6 lg:px-12 lg:py-12' }>
				<ErrorBoundary>
					<EmployeeActionsProvider>
						<Outlet />
					</EmployeeActionsProvider>
				</ErrorBoundary>
			</main>
			<AppFooter />
		</div>
	);
}
