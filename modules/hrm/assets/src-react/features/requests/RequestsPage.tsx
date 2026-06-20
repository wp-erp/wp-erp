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
import { CalendarDays, Inbox, LogOut, Laptop } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { ComponentType, JSX } from 'react';

import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { HOOKS } from '@/shared/filters';
import { __ } from '@/shared/i18n';
import { request, restPath } from '@/shared/utils/apiFetch';

import { LeaveRequestsPage } from '../leave-requests';

export interface RequestTab {
	readonly id:      string;
	readonly label:   string;
	readonly element: ComponentType;
	readonly icon?:   LucideIcon;
}

/**
 * Empty-state shown for request types whose owning module isn't active yet
 * (Resignation, Remote Work). Mirrors the legacy "No requests found." placeholder
 * — the type stays visible so the UI matches the design, and a pro module can
 * replace the tab (same `id`) with a real list via `erp_hr.request_tabs`.
 */
function RequestPlaceholder( { label }: { readonly label: string } ): JSX.Element {
	return (
		<div className="rounded-lg border border-border bg-card p-12 text-center shadow-sm">
			<Inbox size={ 32 } aria-hidden="true" className="mx-auto text-muted-foreground/60" />
			<p className="mt-3 text-sm font-medium text-foreground">
				{ /* translators: %s: request type label. */ }
				{ __( 'No requests found.', 'erp' ) }
			</p>
			<p className="mt-1 text-xs text-muted-foreground">
				{ label }
			</p>
		</div>
	);
}

function ResignationPlaceholder(): JSX.Element {
	return <RequestPlaceholder label={ __( 'Resignation requests appear here once the feature is enabled.', 'erp' ) } />;
}
function RemoteWorkPlaceholder(): JSX.Element {
	return <RequestPlaceholder label={ __( 'Remote work requests appear here once the feature is enabled.', 'erp' ) } />;
}

function RequestsInner(): JSX.Element {
	const baseTabs: RequestTab[] = [
		{ id: 'leave', label: __( 'Leave', 'erp' ), element: LeaveRequestsPage, icon: CalendarDays },
		{ id: 'resignation', label: __( 'Resignation', 'erp' ), element: ResignationPlaceholder, icon: LogOut },
		{ id: 'remote_work', label: __( 'Remote Work', 'erp' ), element: RemoteWorkPlaceholder, icon: Laptop },
	];
	const tabs = applyFilters( HOOKS.REQUEST_TABS, baseTabs ) as RequestTab[];

	const [ active, setActive ] = useState( tabs[ 0 ]?.id ?? 'leave' );
	const current = tabs.find( ( t ) => t.id === active ) ?? tabs[ 0 ];
	const ActiveEl = current?.element ?? LeaveRequestsPage;

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

			{ tabs.length > 1 ? (
				<nav role="tablist" aria-label={ __( 'Request types', 'erp' ) } className="mb-5 inline-flex w-fit max-w-full items-center gap-1 overflow-x-auto rounded-lg border border-border bg-muted/60 p-1 scrollbar-none">
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
								<span className={ [ 'tabular-nums', selected ? 'text-primary/70' : 'text-muted-foreground/70' ].join( ' ' ) }>
									({ counts[ tab.id ] ?? 0 })
								</span>
							</button>
						);
					} ) }
				</nav>
			) : null }

			<ErrorBoundary>
				<ActiveEl />
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
