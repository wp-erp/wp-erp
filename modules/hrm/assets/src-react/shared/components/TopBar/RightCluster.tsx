/**
 * Right-cluster aggregator.
 *
 * Default order: SearchTrigger → ThemeToggle → UserMenu.
 * The "View previous version" link lives in the shell footer
 * (see `app/AppFooter.tsx`) instead of the top bar.
 * Pro injects items via `wp.hooks.applyFilters('erp_hr.topbar.right_items', items)`.
 */

import { applyFilters } from '@wordpress/hooks';
import { useMemo } from 'react';
import type { JSX, ReactNode } from 'react';

import { HOOKS } from '@/shared/filters';

import { SearchTrigger } from './SearchTrigger';
import { ThemeToggle } from './ThemeToggle';
import { UserMenu } from './UserMenu';

export interface TopBarRightItem {
	readonly id:       string;
	readonly weight?:  number;
	readonly render:   () => ReactNode;
}

const DEFAULTS: readonly TopBarRightItem[] = [
	{ id: 'erp-hr/search-trigger', weight: 10, render: () => <SearchTrigger /> },
	{ id: 'erp-hr/theme-toggle',   weight: 20, render: () => <ThemeToggle /> },
	{ id: 'erp-hr/user-menu',      weight: 99, render: () => <UserMenu /> },
];

export function RightCluster(): JSX.Element {
	const items = useMemo( () => {
		const merged = applyFilters(
			HOOKS.TOPBAR_RIGHT_ITEMS,
			DEFAULTS as readonly TopBarRightItem[]
		) as readonly TopBarRightItem[];

		return [ ...merged ].sort(
			( a, b ) => ( a.weight ?? 50 ) - ( b.weight ?? 50 )
		);
	}, [] );

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
