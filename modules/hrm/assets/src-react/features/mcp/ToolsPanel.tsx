/**
 * "Tools" tab (administrators): every tool the server offers, grouped by
 * feature, each with a switch. A tool switched off here is off for everyone,
 * whatever the access rules say.
 */

import { Input, Switch, toast } from '@wedevs/plugin-ui';
import { useMemo, useState } from 'react';
import type { JSX } from 'react';

import { StatusPill } from '@/shared/components/StatusPill';
import type { StatusTone } from '@/shared/components/StatusPill';
import { __, sprintf } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';

import { saveSettings } from './api';
import type { McpMode, McpSettings } from './types';

const MODE_TONE: Record< McpMode, StatusTone > = { read: 'info', write: 'warning', delete: 'danger' };

function modeLabel( mode: McpMode ): string {
	if ( mode === 'read' ) {
		return __( 'Read', 'erp' );
	}
	return mode === 'write' ? __( 'Write', 'erp' ) : __( 'Delete', 'erp' );
}

interface ToolsPanelProps {
	readonly settings: McpSettings;
	readonly onSaved:  ( settings: McpSettings ) => void;
}

export function ToolsPanel( { settings, onSaved }: ToolsPanelProps ): JSX.Element {
	const [ search, setSearch ] = useState( '' );
	const [ busy, setBusy ]     = useState( false );

	const disabled = useMemo( () => new Set( settings.disabled_tools ), [ settings.disabled_tools ] );
	const needle   = search.trim().toLowerCase();

	async function setDisabled( names: readonly string[], off: boolean ): Promise< void > {
		const next = new Set( disabled );
		names.forEach( ( name ) => ( off ? next.add( name ) : next.delete( name ) ) );
		setBusy( true );
		try {
			onSaved( await saveSettings( { disabled_tools: [ ...next ] } ) );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not save the tool list.', 'erp' ) );
		} finally {
			setBusy( false );
		}
	}

	return (
		<div className="space-y-6">
			<div className="flex flex-wrap items-center justify-between gap-3">
				<p className="text-sm text-muted-foreground">
					{ sprintf(
						/* translators: 1: enabled tool count, 2: total tool count. */
						__( '%1$d of %2$d tools are on. Switching a tool off removes it for everyone.', 'erp' ),
						settings.tools.length - settings.tools.filter( ( t ) => disabled.has( t.name ) ).length,
						settings.tools.length
					) }
				</p>
				<Input
					value={ search }
					onChange={ ( e ) => setSearch( e.target.value ) }
					placeholder={ __( 'Search tools…', 'erp' ) }
					aria-label={ __( 'Search tools', 'erp' ) }
					className="h-9 w-64 bg-background px-3 text-sm"
				/>
			</div>

			{ settings.groups.map( ( group ) => {
				const tools = settings.tools.filter(
					( t ) =>
						t.group === group.slug &&
						( needle === '' || `${ t.title } ${ t.name } ${ t.description }`.toLowerCase().includes( needle ) )
				);
				if ( tools.length === 0 ) {
					return null;
				}
				const allOff = tools.every( ( t ) => disabled.has( t.name ) );

				return (
					<section key={ group.slug } className="rounded-[10px] bg-card p-6 shadow-sm">
						<div className="flex items-center justify-between gap-3">
							<h2 className="text-base font-semibold text-foreground">
								{ group.label }
								{ group.pro ? (
									<span className="ml-2 rounded bg-primary/10 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-primary">
										{ __( 'Pro', 'erp' ) }
									</span>
								) : null }
							</h2>
							<Switch
								checked={ ! allOff }
								disabled={ busy }
								/* translators: %s: feature name. */
								aria-label={ sprintf( __( 'All %s tools', 'erp' ), group.label ) }
								onCheckedChange={ ( on ) => void setDisabled( tools.map( ( t ) => t.name ), ! on ) }
							/>
						</div>
						<ul className="mt-3 divide-y divide-border">
							{ tools.map( ( tool ) => (
								<li key={ tool.name } className="flex items-start justify-between gap-4 py-3">
									<div className="min-w-0">
										<p className="flex flex-wrap items-center gap-2 text-sm font-medium text-foreground">
											{ tool.title }
											<StatusPill tone={ MODE_TONE[ tool.mode ] }>{ modeLabel( tool.mode ) }</StatusPill>
											<code className="text-xs font-normal text-muted-foreground">{ tool.name }</code>
										</p>
										<p className="mt-0.5 text-xs text-muted-foreground">{ tool.description }</p>
									</div>
									<Switch
										checked={ ! disabled.has( tool.name ) }
										disabled={ busy }
										aria-label={ tool.title }
										onCheckedChange={ ( on ) => void setDisabled( [ tool.name ], ! on ) }
									/>
								</li>
							) ) }
						</ul>
					</section>
				);
			} ) }
		</div>
	);
}
