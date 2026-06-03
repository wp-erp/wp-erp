/**
 * Page header — title + meta line + sub-tabs + primary actions.
 *
 * Uses design-system tokens only (bg-card, border-border, text-foreground,
 * primary, muted, etc.). No raw hex, no gradient blobs.
 */

import { Button } from '@wedevs/plugin-ui';
import { useSelect } from '@wordpress/data';
import { Download, Plus, Settings2 } from 'lucide-react';
import type { JSX } from 'react';

import { useCan } from '@/shared/hooks/useCan';
import { __, sprintf } from '@/shared/i18n';
import { storeName as employeesStoreName, toCountsQuery } from '@/stores/employees';
import type {
	EmployeeCountsQuery,
	EmployeeListQuery,
	EmployeeStatusCounts,
} from '@/stores/employees';

interface EmployeesStoreSelectors {
	getFilters: () => EmployeeListQuery;
	getCounts:  ( query: EmployeeCountsQuery ) => EmployeeStatusCounts | null;
}

const SUB_TABS: ReadonlyArray< { readonly id: string; readonly label: string; readonly active: boolean } > = [
	{ id: 'people',       label: __( 'People',       'erp' ), active: true  },
	{ id: 'departments',  label: __( 'Departments',  'erp' ), active: false },
	{ id: 'designations', label: __( 'Designations', 'erp' ), active: false },
	{ id: 'org-chart',    label: __( 'Org chart',    'erp' ), active: false },
];

export function PeopleProHeader(): JSX.Element {
	const counts = useSelect( ( select ) => {
		const store   = select( employeesStoreName ) as unknown as EmployeesStoreSelectors;
		const filters = store.getFilters();
		return store.getCounts( toCountsQuery( filters ) );
	}, [] );
	const canCreate = useCan( 'erp_create_employee' );

	return (
		<header className="border-b border-border bg-card">
			<div className="mx-auto w-full max-w-7xl px-6 pt-6">
				<div className="flex flex-wrap items-end justify-between gap-4">
					<div className="min-w-0">
						<h1 className="text-2xl font-semibold leading-8 tracking-tight text-foreground">
							{ __( 'People', 'erp' ) }
						</h1>
						<p className="mt-1 text-sm text-muted-foreground">
							{ counts
								? sprintf(
									/* translators: %1$d: total people, %2$d: active count */
									__( '%1$d members · %2$d active', 'erp' ),
									counts.all,
									counts.by_status.active ?? 0
								)
								: __( 'Workforce directory', 'erp' ) }
						</p>
					</div>
					<div className="flex flex-wrap items-center gap-2">
						<Button
							variant="ghost"
							size="sm"
							className="h-9 gap-1.5 text-muted-foreground hover:text-foreground"
						>
							<Settings2 size={ 14 } strokeWidth={ 1.75 } aria-hidden="true" />
							{ __( 'Customize view', 'erp' ) }
						</Button>
						<Button
							variant="outline"
							size="sm"
							className="h-9 gap-1.5"
						>
							<Download size={ 14 } strokeWidth={ 1.75 } aria-hidden="true" />
							{ __( 'Export', 'erp' ) }
						</Button>
						{ canCreate ? (
							<Button
								variant="default"
								size="sm"
								className="h-9 gap-1.5"
								onClick={ () => { window.location.hash = '#/employees/new'; } }
							>
								<Plus size={ 14 } strokeWidth={ 2 } aria-hidden="true" />
								{ __( 'Invite member', 'erp' ) }
							</Button>
						) : null }
					</div>
				</div>

				<nav
					role="tablist"
					aria-label={ __( 'People sections', 'erp' ) }
					className="-mb-px mt-6 flex items-center gap-6 overflow-x-auto"
				>
					{ SUB_TABS.map( ( tab ) => (
						<button
							key={ tab.id }
							type="button"
							role="tab"
							aria-selected={ tab.active }
							aria-disabled={ ! tab.active }
							className={ [
								'relative inline-flex h-10 items-center whitespace-nowrap border-b-2 px-1 text-sm font-medium transition-colors',
								tab.active
									? 'border-primary text-foreground'
									: 'border-transparent text-muted-foreground hover:text-foreground',
							].join( ' ' ) }
						>
							{ tab.label }
						</button>
					) ) }
				</nav>
			</div>
		</header>
	);
}
