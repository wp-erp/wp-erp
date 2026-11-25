/**
 * HR → AI Connection. Lets people connect an AI assistant (an MCP client) to
 * HR, and lets administrators decide who may and how far.
 *
 * Everyone gets the Connect and Activity tabs, scoped to themselves.
 * Administrators also get Access (who can use which feature), Tools (global
 * per-tool switches) and Settings (the master switch).
 */

import { useCallback, useEffect, useState } from 'react';
import type { JSX } from 'react';

import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { SegmentedTab } from '@/shared/components/SegmentedTab';
import { TableSkeleton } from '@/shared/components/TableSkeleton';
import { __ } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';

import { AccessPanel } from './AccessPanel';
import { ActivityPanel } from './ActivityPanel';
import { fetchOverview, fetchSettings } from './api';
import { ConnectPanel } from './ConnectPanel';
import { SettingsPanel } from './SettingsPanel';
import { ToolsPanel } from './ToolsPanel';
import type { McpOverview, McpSettings } from './types';

type TabId = 'connect' | 'access' | 'tools' | 'activity' | 'settings';

function McpInner(): JSX.Element {
	const [ overview, setOverview ] = useState< McpOverview | null >( null );
	const [ settings, setSettings ] = useState< McpSettings | null >( null );
	const [ error, setError ]       = useState< string | null >( null );
	const [ tab, setTab ]           = useState< TabId >( 'connect' );

	const load = useCallback( async (): Promise< void > => {
		try {
			const next = await fetchOverview();
			setOverview( next );
			if ( next.is_admin ) {
				setSettings( await fetchSettings() );
			}
		} catch ( raw ) {
			setError( ( raw as ApiError )?.message ?? __( 'Could not load the AI connection settings.', 'erp' ) );
		}
	}, [] );

	useEffect( () => {
		void load();
	}, [ load ] );

	// Saving settings changes what the current user can reach too.
	function handleSaved( next: McpSettings ): void {
		setSettings( next );
		void fetchOverview().then( setOverview ).catch( () => undefined );
	}

	const tabs: { id: TabId; label: string }[] = [ { id: 'connect', label: __( 'Connect', 'erp' ) } ];
	if ( overview?.is_admin ) {
		tabs.push( { id: 'access', label: __( 'Access', 'erp' ) }, { id: 'tools', label: __( 'Tools', 'erp' ) } );
	}
	tabs.push( { id: 'activity', label: __( 'Activity', 'erp' ) } );
	if ( overview?.is_admin ) {
		tabs.push( { id: 'settings', label: __( 'Settings', 'erp' ) } );
	}

	return (
		<section className="mx-auto w-full max-w-6xl">
			<header className="mb-6">
				<h1 className="text-2xl font-bold leading-8 text-foreground">{ __( 'AI Connection', 'erp' ) }</h1>
				<p className="mt-1 text-sm text-muted-foreground">
					{ __( 'Connect an AI assistant to HR using the Model Context Protocol (MCP). The assistant works as you, with your permissions.', 'erp' ) }
				</p>
			</header>

			{ error ? (
				<p className="text-sm text-destructive">{ error }</p>
			) : ! overview ? (
				<div className="rounded-[10px] bg-card p-6 shadow-sm">
					<TableSkeleton rows={ 6 } />
				</div>
			) : (
				<>
					<div
						role="tablist"
						className="mb-6 inline-flex w-fit items-center gap-1 rounded-lg border border-border bg-muted/60 p-1"
					>
						{ tabs.map( ( t ) => (
							<SegmentedTab key={ t.id } active={ tab === t.id } onClick={ () => setTab( t.id ) }>
								{ t.label }
							</SegmentedTab>
						) ) }
					</div>

					{ tab === 'connect' ? <ConnectPanel overview={ overview } /> : null }
					{ tab === 'activity' ? <ActivityPanel /> : null }
					{ settings && tab === 'access' ? <AccessPanel settings={ settings } onSaved={ handleSaved } /> : null }
					{ settings && tab === 'tools' ? <ToolsPanel settings={ settings } onSaved={ handleSaved } /> : null }
					{ settings && tab === 'settings' ? <SettingsPanel settings={ settings } onSaved={ handleSaved } /> : null }
				</>
			) }
		</section>
	);
}

export function McpPage(): JSX.Element {
	return (
		<ErrorBoundary>
			<McpInner />
		</ErrorBoundary>
	);
}
