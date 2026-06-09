/**
 * Vertical sidebar shell (sidebar nav layout).
 *
 * Logo header (matches the top bar's logo cluster) + the scrollable
 * `<SidebarNav/>`. Sticky, full-height, white surface with a right border —
 * per Figma HRM-Redesign-2024 node 1511:29973.
 *
 * The RightCluster (search / theme / user / upgrade) does NOT live here — in
 * sidebar mode it stays in a slim top strip rendered by `AppShell`.
 */

import type { JSX } from 'react';

import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { Logo } from '@/shared/components/TopBar/Logo';

import { SidebarNav } from './SidebarNav';

export function Sidebar(): JSX.Element {
	return (
		<ErrorBoundary fallback={ <aside className="w-60 shrink-0 border-r border-border bg-card" /> }>
			<aside
				className="erp-hr-sidebar sticky top-8 flex h-[calc(100vh-32px)] w-60 shrink-0 flex-col bg-card"
				role="navigation"
				aria-label="HR"
			>
				<div className="flex h-16 shrink-0 items-center gap-2 px-5">
					<Logo />
				</div>
				<div className="min-h-0 flex-1 overflow-y-auto">
					<SidebarNav />
				</div>
			</aside>
		</ErrorBoundary>
	);
}
