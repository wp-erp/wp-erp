/**
 * Vertical sidebar shell (sidebar nav layout).
 *
 * Logo header (matches the top bar's logo cluster) + a collapse toggle + the
 * scrollable `<SidebarNav/>`. Sticky, full-height, white surface with a right
 * border — per Figma HRM-Redesign-2024 node 1511:29973.
 *
 * The collapse toggle shrinks the rail to an icon-only minimal state (`w-16`);
 * the choice persists in `localStorage` via `useSidebarCollapsed`.
 *
 * The RightCluster (search / theme / user / upgrade) does NOT live here — in
 * sidebar mode it stays in a slim top strip rendered by `AppShell`.
 */

import { PanelLeftClose, PanelLeftOpen } from 'lucide-react';
import type { JSX } from 'react';

import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { Logo } from '@/shared/components/TopBar/Logo';
import { useSidebarCollapsed } from '@/shared/hooks/useSidebarCollapsed';
import { __ } from '@/shared/i18n';

import { SidebarNav } from './SidebarNav';

export function Sidebar(): JSX.Element {
	const { collapsed, toggle } = useSidebarCollapsed();

	const label = collapsed ? __( 'Expand sidebar', 'erp' ) : __( 'Collapse sidebar', 'erp' );
	const ToggleIcon = collapsed ? PanelLeftOpen : PanelLeftClose;

	return (
		<ErrorBoundary
			fallback={
				<aside
					className={ `${ collapsed ? 'w-16' : 'w-60' } shrink-0 border-r border-border bg-card` }
				/>
			}
		>
			<aside
				className={ `erp-hr-sidebar sticky top-8 flex h-[calc(100vh-32px)] ${
					collapsed ? 'w-16' : 'w-60'
				} shrink-0 flex-col bg-card transition-[width] duration-200` }
				role="navigation"
				aria-label="HR"
			>
				<div
					className={ `flex h-16 shrink-0 items-center ${
						collapsed ? 'justify-center' : 'gap-2 px-5'
					}` }
				>
					{ ! collapsed && <Logo /> }
					<button
						type="button"
						onClick={ toggle }
						aria-label={ label }
						aria-expanded={ ! collapsed }
						title={ label }
						className={ `${
							collapsed ? '' : 'ml-auto'
						} rounded p-1.5 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground` }
					>
						<ToggleIcon size={ 18 } strokeWidth={ 1.75 } aria-hidden="true" />
					</button>
				</div>
				<div className="min-h-0 flex-1 overflow-y-auto">
					<SidebarNav collapsed={ collapsed } />
				</div>
			</aside>
		</ErrorBoundary>
	);
}
