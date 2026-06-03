/**
 * Page header — title + right cluster with toolbar items.
 *
 * Free defaults: "Add new employee" CTA. Pro injects via
 * `wp.hooks.applyFilters('erp_hr.employees.toolbar_items', items, ctx)`.
 */

import { Button } from '@wedevs/plugin-ui';
import { Plus } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { useEmployeeToolbarItems } from './useEmployeeToolbarItems';

const ICON_MAP: Record< string, typeof Plus > = {
	Plus,
};

export function EmployeesToolbar(): JSX.Element {
	const items = useEmployeeToolbarItems();

	return (
		<header className="mb-6 flex items-center justify-between gap-4">
			<h1 className="text-2xl font-bold leading-8 text-foreground">
				{ __( 'Employees', 'erp' ) }
			</h1>
			<div className="flex items-center gap-3">
				{ items.map( ( item ) => {
					const Icon = item.icon ? ICON_MAP[ item.icon ] : undefined;
					const variant: 'default' | 'secondary' | 'ghost' = (() => {
						switch ( item.variant ) {
							case 'primary':
								return 'default';
							case 'secondary':
								return 'secondary';
							case 'ghost':
								return 'ghost';
							default:
								return 'default';
						}
					})();
					return (
						<Button
							key={ item.id }
							variant={ variant }
							size="default"
							className="inline-flex h-10 items-center gap-2 rounded-md px-5 text-sm font-medium leading-5 shadow-sm"
							onClick={ () => item.onSelect() }
						>
							{ Icon ? <Icon size={ 16 } strokeWidth={ 2 } aria-hidden="true" /> : null }
							<span>{ item.label }</span>
						</Button>
					);
				} ) }
			</div>
		</header>
	);
}
