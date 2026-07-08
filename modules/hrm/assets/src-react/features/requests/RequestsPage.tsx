/**
 * People → Requests — a tabbed aggregator over every employee request type.
 *
 * Restores the legacy unified Requests screen (Leave · Asset · Reimbursement · …)
 * which the redesign had split per-module. Free seeds the **Leave** tab; pro
 * modules append their own type via `addFilter( 'erp_hr.request_tabs', … )`
 * (the same `wp.hooks` extension pattern as `erp_hr.routes` / dashboard widgets).
 *
 * Each tab is `{ id, label, element }`; the active tab's component renders below
 * the tab bar. Tabs are resolved at render so late-loading pro bundles are picked
 * up (mirrors how the router applies `erp_hr.routes`).
 */

import { applyFilters } from '@wordpress/hooks';
import { CalendarDays, LogOut, Laptop } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { ComponentType, JSX } from 'react';

import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { HOOKS } from '@/shared/filters';
import { __ } from '@/shared/i18n';
import { request, restPath } from '@/shared/utils/apiFetch';

import { LeaveRequestsPage } from '../leave-requests';
import { RequestsActionSlotContext, RequestsTabContext } from './requests-tab-context';
import { ResignationRequests } from './ResignationRequests';
import { RemoteWorkRequests } from './RemoteWorkRequests';

export interface RequestTab {
	readonly id:      string;
	readonly label:   string;
	/** Rendered inside the Requests tabs; `inTabs` lets a tab hide its own title. */
	readonly element: ComponentType< { readonly inTabs?: boolean } >;
	readonly icon?:   LucideIcon;
}

function RequestsInner(): JSX.Element {
	const baseTabs: RequestTab[] = [
		{ id: 'leave', label: __( 'Leave', 'erp' ), element: LeaveRequestsPage, icon: CalendarDays },
		{ id: 'resignation', label: __( 'Resignation', 'erp' ), element: ResignationRequests, icon: LogOut },
		{ id: 'remote_work', label: __( 'Remote Work', 'erp' ), element: RemoteWorkRequests, icon: Laptop },
	];
	const tabs = applyFilters( HOOKS.REQUEST_TABS, baseTabs ) as RequestTab[];

	const [ active, setActive ] = useState( tabs[ 0 ]?.id ?? 'leave' );
	const current = tabs.find( ( t ) => t.id === active ) ?? tabs[ 0 ];
	const ActiveEl = current?.element ?? LeaveRequestsPage;
	const [ actionSlotEl, setActionSlotEl ] = useState< HTMLDivElement | null >( null );

	// Per-type totals for the tab badges (Leave / Asset / Reimbursement / …),
	// keyed by tab id. Restores the legacy unified-Requests counts.
	const [ counts, setCounts ] = useState< Record< string, number > >( {} );
	useEffect( () => {
		const ctrl = new AbortController();
		request< { totals?: Record< string, number > } >(
			restPath( 'v2', '/requests/counts' ),
			{ signal: ctrl.signal }
		)
			.then( ( res ) => setCounts( res.totals ?? {} ) )
			.catch( () => undefined );
		return () => ctrl.abort();
	}, [] );

	return (
		<section className="mx-auto w-full max-w-full">
			<header className="mb-5">
				<h1 className="text-2xl font-bold leading-8 text-foreground">{ __( 'Requests', 'erp' ) }</h1>
			</header>

			<div className="mb-5 flex flex-wrap items-center justify-between gap-3">
			{ tabs.length > 1 ? (
				<nav role="tablist" aria-label={ __( 'Request types', 'erp' ) } className="inline-flex w-fit max-w-full items-center gap-1 overflow-x-auto rounded-lg border border-border bg-muted/60 p-1 scrollbar-none">
					{ tabs.map( ( tab ) => {
						const selected = tab.id === current?.id;
						const Icon = tab.icon;
						return (
							<button
								key={ tab.id }
								role="tab"
								type="button"
								aria-selected={ selected }
								onClick={ () => setActive( tab.id ) }
								className={ [
									'inline-flex shrink-0 flex-none items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium ring-1 ring-transparent transition-all',
									selected ? 'bg-card text-primary shadow-sm ring-primary/40' : 'text-muted-foreground hover:text-foreground',
								].join( ' ' ) }
							>
								{ Icon ? <Icon size={ 16 } aria-hidden="true" /> : null }
								{ tab.label }
								<span className={ [ '', selected ? 'text-primary/70' : 'text-muted-foreground/70' ].join( ' ' ) }>
									({ counts[ tab.id ] ?? 0 })
								</span>
							</button>
						);
					} ) }
				</nav>
			) : <span /> }
				<div ref={ setActionSlotEl } className="flex items-center gap-2 empty:hidden" />
			</div>

			<ErrorBoundary>
				<RequestsTabContext.Provider value={ true }>
					<RequestsActionSlotContext.Provider value={ actionSlotEl }>
						<ActiveEl inTabs />
					</RequestsActionSlotContext.Provider>
				</RequestsTabContext.Provider>
			</ErrorBoundary>
		</section>
	);
}

export function RequestsPage(): JSX.Element {
	return (
		<CapabilityGate caps={ [ 'erp_leave_manage' ] }>
			<ErrorBoundary>
				<RequestsInner />
			</ErrorBoundary>
		</CapabilityGate>
	);
}
