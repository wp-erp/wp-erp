/**
 * Top bar composition root.
 *
 * Sticky 64 px white bar with bottom border. Layout per
 * openspec/changes/redesign-hr-free/figma-reference.md §Layer 2:
 *
 *   [Logo + HR + ChevronDown]  [NavLinks (canonical 11 items)]   [RightCluster]
 *
 * The NavLinks consume `TOPBAR_NAV_ITEMS` directly — they are not derived
 * from the React Router route table because some items (Payroll, Attendance,
 * etc.) belong to Pro modules and have no route in the Free shell.
 */

import type { JSX } from 'react';

import { ErrorBoundary } from '@/shared/components/ErrorBoundary';

import { Logo } from './Logo';
import { NavLinks } from './NavLinks';
import { RightCluster } from './RightCluster';

export function TopBar(): JSX.Element {
	return (
		<ErrorBoundary fallback={ <header className="erp-hr-topbar" /> }>
			<header className="erp-hr-topbar" role="banner">
				<div className="flex min-w-0 flex-1 items-stretch">
					<Logo />
					<div className="erp-hr-topbar-nav-scroll flex min-w-0 flex-1 items-stretch overflow-x-auto">
						<NavLinks />
					</div>
				</div>
				<div className="shrink-0">
					<RightCluster />
				</div>
			</header>
		</ErrorBoundary>
	);
}

export type { TopBarRightItem } from './RightCluster';
