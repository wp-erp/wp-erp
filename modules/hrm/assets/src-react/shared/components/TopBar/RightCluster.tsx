/**
 * Right-cluster aggregator.
 *
 * Default order: SearchTrigger → ThemeToggle → UserMenu.
 * The "View legacy version" link lives in the shell footer
 * (see `app/AppFooter.tsx`) instead of the top bar.
 * Pro injects items via `wp.hooks.applyFilters('erp_hr.topbar.right_items', items)`.
 */

import { applyFilters } from '@wordpress/hooks';
import { useMemo } from 'react';
import type { JSX, ReactNode } from 'react';

import { HOOKS } from '@/shared/filters';
import { useBoot } from '@/shared/hooks/useBoot';

import { SearchTrigger } from './SearchTrigger';
import { ThemeToggle } from './ThemeToggle';
import { UpgradeButton } from './UpgradeButton';
import { UserMenu } from './UserMenu';

export interface TopBarRightItem {
	readonly id:       string;
	readonly weight?:  number;
	readonly render:   () => ReactNode;
}

/** Search + theme toggle joined in one bordered group with a full-height divider. */
function SearchThemeSegment(): JSX.Element {
	return (
		<div className="inline-flex items-center overflow-hidden rounded-md border border-border bg-card">
			<SearchTrigger />
			<span aria-hidden="true" className="self-stretch w-px bg-border" />
			<ThemeToggle />
		</div>
	);
}

const DEFAULTS: readonly TopBarRightItem[] = [
	{ id: 'erp-hr/search-theme', weight: 10, render: () => <SearchThemeSegment /> },
	{ id: 'erp-hr/user-menu',    weight: 99, render: () => <UserMenu /> },
];

export function RightCluster(): JSX.Element {
	const isPro = useBoot().isPro;

	const items = useMemo( () => {
		// When Pro is absent, the free shell shows its own "Upgrade" upsell button
		// (Pro injects its own What's New / Support / Upgrade set when installed).
		const base: readonly TopBarRightItem[] = isPro
			? DEFAULTS
			: [
					{ id: 'erp-hr/upgrade', weight: 30, render: () => <UpgradeButton /> },
					...DEFAULTS,
				];

		const merged = applyFilters(
			HOOKS.TOPBAR_RIGHT_ITEMS,
			base
		) as readonly TopBarRightItem[];

		return [ ...merged ].sort(
			( a, b ) => ( a.weight ?? 50 ) - ( b.weight ?? 50 )
		);
	}, [ isPro ] );

	return (
		<div
			style={ {
				marginLeft: 'auto',
				display: 'flex',
				alignItems: 'center',
				gap: '12px',
			} }
		>
			{ items.map( ( item ) => (
				<span key={ item.id }>{ item.render() }</span>
			) ) }
		</div>
	);
}
