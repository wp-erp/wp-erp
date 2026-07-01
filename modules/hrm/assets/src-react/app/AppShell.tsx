/**
 * Layout chrome — `<TopBar/>` slot + `<Outlet/>` for the active route.
 *
 * Routes pass through their own ErrorBoundary; this one wraps the whole shell
 * so a TopBar crash doesn't kill the route content (and vice versa).
 */

import { Outlet, useLocation } from 'react-router-dom';
import type { JSX } from 'react';

import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { ProUpsellProvider } from '@/shared/components/pro/ProUpsell';
import { TopBar } from '@/shared/components/TopBar';
import { RightCluster } from '@/shared/components/TopBar/RightCluster';
import { Sidebar } from '@/shared/components/Sidebar';
import { useNavLayout } from '@/shared/hooks/useNavLayout';
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
	const { layout }   = useNavLayout();

	const mainClass = chromeless
		? 'flex-1'
		: 'erp-hr-panel flex-1 px-6 py-4 lg:px-12 lg:py-6';

	const content = (
		<main className={ mainClass }>
			<ErrorBoundary>
				<EmployeeActionsProvider>
					<Outlet />
				</EmployeeActionsProvider>
			</ErrorBoundary>
		</main>
	);

	// Sidebar layout: vertical nav on the left, a slim top strip keeps the
	// RightCluster (search / theme / user / upgrade) above the content.
	//
	// The right column is pinned to the viewport height and the CONTENT scrolls
	// inside its own wrapper (not the window). That keeps the gray panel's
	// rounded top-left corner — which rounds against the white sidebar + top
	// strip (Figma HRM-Redesign-2024 node 1511:29973) — fixed on scroll instead
	// of sliding up under the sticky header and going sharp.
	if ( layout === 'sidebar' ) {
		return (
			<ProUpsellProvider>
				<div className="erp-hr-shell flex h-[calc(100vh-32px)] bg-background text-foreground">
					<Sidebar />
					<div className="flex min-w-0 flex-1 flex-col overflow-hidden">
						<header
							role="banner"
							className="z-30 flex h-12 shrink-0 items-center bg-card px-4"
						>
							<div className="ml-auto">
								<ErrorBoundary>
									<RightCluster />
								</ErrorBoundary>
							</div>
						</header>
						<div
							className={
								chromeless
									? 'flex min-h-0 flex-1 flex-col overflow-y-auto'
									: 'erp-hr-panel flex min-h-0 flex-1 flex-col overflow-y-auto rounded-tl-2xl'
							}
						>
							{ content }
							<AppFooter />
						</div>
					</div>
				</div>
			</ProUpsellProvider>
		);
	}

	// Default: horizontal top-bar layout.
	return (
		<ProUpsellProvider>
			<div className="erp-hr-shell flex min-h-[calc(100vh-32px)] flex-col bg-background text-foreground">
				<TopBar />
				{ content }
				<AppFooter />
			</div>
		</ProUpsellProvider>
	);
}
