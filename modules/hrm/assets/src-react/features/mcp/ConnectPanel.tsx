/**
 * "Connect" tab: everything one person needs to plug an AI client into HR —
 * the endpoint, their own access tokens, what they have been granted, and
 * copy-paste setup for the common clients. Shown to everyone.
 */

import { Alert, AlertDescription, Button, Input, Select, SelectContent, SelectItem, SelectTrigger, SelectValue, toast } from '@wedevs/plugin-ui';
import { Check, Copy, KeyRound, Plus, Trash2 } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';
import type { JSX } from 'react';

import { EmptyState } from '@/shared/components/EmptyState';
import { StatusPill } from '@/shared/components/StatusPill';
import { TableSkeleton } from '@/shared/components/TableSkeleton';
import { __, dateI18n, sprintf } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';

import { createToken, fetchTokens, revokeToken } from './api';
import { levelLabel } from './LevelSelect';
import type { McpOverview, McpToken } from './types';

function formatDate( iso: string | null ): string {
	return iso ? dateI18n( 'M j, Y g:i a', iso ) : __( 'Never', 'erp' );
}

function CopyField( { value, label }: { readonly value: string; readonly label: string } ): JSX.Element {
	const [ copied, setCopied ] = useState( false );

	function copy(): void {
		void navigator.clipboard?.writeText( value ).then( () => {
			setCopied( true );
			window.setTimeout( () => setCopied( false ), 1500 );
		} );
	}

	return (
		<div className="flex items-stretch gap-2">
			<code className="min-w-0 flex-1 overflow-x-auto whitespace-pre rounded-md border border-border bg-muted/50 px-3 py-2 text-xs leading-5 text-foreground">
				{ value }
			</code>
			<Button variant="outline" className="h-auto shrink-0 gap-1.5" onClick={ copy } aria-label={ label }>
				{ copied ? <Check size={ 14 } aria-hidden="true" /> : <Copy size={ 14 } aria-hidden="true" /> }
				{ copied ? __( 'Copied', 'erp' ) : __( 'Copy', 'erp' ) }
			</Button>
		</div>
	);
}

interface ConnectPanelProps {
	readonly overview: McpOverview;
}

export function ConnectPanel( { overview }: ConnectPanelProps ): JSX.Element {
	const [ tokens, setTokens ]   = useState< readonly McpToken[] >( [] );
	const [ loading, setLoading ] = useState( true );
	const [ showAll, setShowAll ] = useState( false );
	const [ label, setLabel ]     = useState( '' );
	const [ scope, setScope ]     = useState< 'full' | 'read' >( 'full' );
	const [ busy, setBusy ]       = useState( false );
	const [ fresh, setFresh ]     = useState< McpToken | null >( null );

	const reload = useCallback( async (): Promise< void > => {
		setLoading( true );
		try {
			setTokens( await fetchTokens( showAll ) );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not load access tokens.', 'erp' ) );
		} finally {
			setLoading( false );
		}
	}, [ showAll ] );

	useEffect( () => {
		void reload();
	}, [ reload ] );

	async function handleCreate(): Promise< void > {
		if ( label.trim() === '' ) {
			toast.error( __( 'Give the token a name, like the app you will use it in.', 'erp' ) );
			return;
		}
		setBusy( true );
		try {
			setFresh( await createToken( label.trim(), scope ) );
			setLabel( '' );
			await reload();
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not create the token.', 'erp' ) );
		} finally {
			setBusy( false );
		}
	}

	async function handleRevoke( token: McpToken ): Promise< void > {
		try {
			await revokeToken( token.id );
			if ( fresh?.id === token.id ) {
				setFresh( null );
			}
			toast.success( __( 'Token revoked. Any app using it has lost access.', 'erp' ) );
			await reload();
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not revoke the token.', 'erp' ) );
		}
	}

	if ( ! overview.enabled ) {
		return (
			<EmptyState
				icon={ KeyRound }
				title={ __( 'AI connections are switched off.', 'erp' ) }
				description={
					overview.is_admin
						? __( 'Turn the MCP server on in the Settings tab to let people connect AI assistants to HR.', 'erp' )
						: __( 'Your administrator has not turned on AI connections for this site.', 'erp' )
				}
			/>
		);
	}

	if ( ! overview.can_connect ) {
		return (
			<EmptyState
				icon={ KeyRound }
				title={ __( 'You have not been given access yet.', 'erp' ) }
				description={ __( 'Ask your administrator to allow your role, or you individually, to use HR through an AI assistant.', 'erp' ) }
			/>
		);
	}

	const scopeItems = [
		{ value: 'full', label: __( 'Everything I can access', 'erp' ) },
		{ value: 'read', label: __( 'Read only', 'erp' ) },
	];
	const secret  = fresh?.token ?? 'YOUR_TOKEN';
	const granted = overview.groups.filter( ( g ) => ( overview.levels[ g.slug ] ?? 'none' ) !== 'none' );
	const claudeCode = `claude mcp add --transport http wp-erp-hr ${ overview.endpoint_url } --header "Authorization: Bearer ${ secret }"`;
	const jsonConfig = JSON.stringify(
		{
			mcpServers: {
				'wp-erp-hr': {
					type:    'http',
					url:     overview.endpoint_url,
					headers: { Authorization: `Bearer ${ secret }` },
				},
			},
		},
		null,
		2
	);

	return (
		<div className="space-y-6">
			<section className="rounded-[10px] bg-card p-6 shadow-sm">
				<h2 className="text-base font-semibold text-foreground">{ __( 'What you can use', 'erp' ) }</h2>
				<p className="mt-1 text-sm text-muted-foreground">
					{ sprintf(
						/* translators: %d: number of tools. */
						__( 'An AI assistant connected with your token acts as you. It can use %d tools, limited to what you can already do in HR and to the features below.', 'erp' ),
						overview.tools.length
					) }
				</p>
				<ul className="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
					{ granted.map( ( group ) => (
						<li key={ group.slug } className="flex items-center justify-between gap-3 rounded-md border border-border px-3 py-2">
							<span className="truncate text-sm font-medium text-foreground">{ group.label }</span>
							<StatusPill tone={ overview.levels[ group.slug ] === 'read' ? 'info' : 'success' }>
								{ levelLabel( overview.levels[ group.slug ] ?? 'none' ) }
							</StatusPill>
						</li>
					) ) }
				</ul>
			</section>

			<section className="rounded-[10px] bg-card p-6 shadow-sm">
				<div className="flex flex-wrap items-center justify-between gap-3">
					<div>
						<h2 className="text-base font-semibold text-foreground">{ __( 'Access tokens', 'erp' ) }</h2>
						<p className="mt-1 text-sm text-muted-foreground">
							{ __( 'A token is the password an AI app uses to act as you. Create one per app so you can revoke them separately.', 'erp' ) }
						</p>
					</div>
					{ overview.is_admin ? (
						<Button variant="outline" className="h-9" onClick={ () => setShowAll( ( v ) => ! v ) }>
							{ showAll ? __( 'Show only mine', 'erp' ) : __( 'Show everyone\'s', 'erp' ) }
						</Button>
					) : null }
				</div>

				<div className="mt-4 grid grid-cols-1 items-end gap-3 sm:grid-cols-[1fr_14rem_auto]">
					<Input
						value={ label }
						onChange={ ( e ) => setLabel( e.target.value ) }
						placeholder={ __( 'Token name, e.g. Claude Desktop', 'erp' ) }
						aria-label={ __( 'Token name', 'erp' ) }
						className="h-9 bg-background px-3 text-sm"
					/>
					<Select
						items={ scopeItems }
						value={ scope }
						onValueChange={ ( v ) => setScope( v === 'read' ? 'read' : 'full' ) }
					>
						<SelectTrigger aria-label={ __( 'Token scope', 'erp' ) } className="data-[size=default]:h-9 w-full bg-background">
							<SelectValue />
						</SelectTrigger>
						<SelectContent align="start" alignItemWithTrigger={ false }>
							{ scopeItems.map( ( item ) => (
								<SelectItem key={ item.value } value={ item.value }>
									{ item.label }
								</SelectItem>
							) ) }
						</SelectContent>
					</Select>
					<Button className="h-9 gap-1.5" disabled={ busy } onClick={ () => void handleCreate() }>
						<Plus size={ 16 } aria-hidden="true" />
						{ busy ? __( 'Creating…', 'erp' ) : __( 'Create token', 'erp' ) }
					</Button>
				</div>
				<p className="mt-2 text-xs text-muted-foreground">
					{ __( 'A read-only token can never change anything, whatever your own access is.', 'erp' ) }
				</p>

				{ fresh?.token ? (
					<Alert className="mt-4">
						<AlertDescription>
							<p className="mb-2 text-sm font-medium text-foreground">
								{ __( 'Copy this token now. It is shown once and cannot be recovered.', 'erp' ) }
							</p>
							<CopyField value={ fresh.token } label={ __( 'Copy token', 'erp' ) } />
						</AlertDescription>
					</Alert>
				) : null }

				<div className="mt-5">
					{ loading ? (
						<TableSkeleton rows={ 3 } />
					) : tokens.length === 0 ? (
						<p className="text-sm text-muted-foreground">{ __( 'No tokens yet.', 'erp' ) }</p>
					) : (
						<div className="overflow-x-auto">
							<table className="w-full text-left text-sm">
								<thead className="text-xs uppercase text-muted-foreground">
									<tr>
										<th className="py-2 pr-4 font-medium">{ __( 'Name', 'erp' ) }</th>
										{ showAll ? <th className="py-2 pr-4 font-medium">{ __( 'User', 'erp' ) }</th> : null }
										<th className="py-2 pr-4 font-medium">{ __( 'Scope', 'erp' ) }</th>
										<th className="py-2 pr-4 font-medium">{ __( 'Created', 'erp' ) }</th>
										<th className="py-2 pr-4 font-medium">{ __( 'Last used', 'erp' ) }</th>
										<th className="py-2 font-medium" />
									</tr>
								</thead>
								<tbody className="divide-y divide-border">
									{ tokens.map( ( token ) => (
										<tr key={ token.id } className={ token.revoked_at ? 'opacity-60' : '' }>
											<td className="py-2.5 pr-4 font-medium text-foreground">{ token.label }</td>
											{ showAll ? <td className="py-2.5 pr-4 text-muted-foreground">{ token.user.name }</td> : null }
											<td className="py-2.5 pr-4">
												<StatusPill tone={ token.scope === 'read' ? 'info' : 'neutral' }>
													{ token.scope === 'read' ? __( 'Read only', 'erp' ) : __( 'Full', 'erp' ) }
												</StatusPill>
											</td>
											<td className="py-2.5 pr-4 text-muted-foreground">{ formatDate( token.created_at ) }</td>
											<td className="py-2.5 pr-4 text-muted-foreground">{ formatDate( token.last_used_at ) }</td>
											<td className="py-2.5 text-right">
												{ token.revoked_at ? (
													<StatusPill tone="danger">{ __( 'Revoked', 'erp' ) }</StatusPill>
												) : (
													<Button
														variant="ghost"
														className="h-8 gap-1.5 text-destructive hover:bg-destructive/10 hover:text-destructive"
														onClick={ () => void handleRevoke( token ) }
													>
														<Trash2 size={ 14 } aria-hidden="true" />
														{ __( 'Revoke', 'erp' ) }
													</Button>
												) }
											</td>
										</tr>
									) ) }
								</tbody>
							</table>
						</div>
					) }
				</div>
			</section>

			<section className="rounded-[10px] bg-card p-6 shadow-sm">
				<h2 className="text-base font-semibold text-foreground">{ __( 'Connect your AI app', 'erp' ) }</h2>
				<p className="mt-1 text-sm text-muted-foreground">
					{ fresh?.token
						? __( 'Your new token is already filled in below.', 'erp' )
						: __( 'Create a token above, then replace YOUR_TOKEN below with it.', 'erp' ) }
				</p>
				<div className="mt-4 space-y-4">
					<div>
						<p className="mb-1.5 text-sm font-medium text-foreground">{ __( 'Server URL', 'erp' ) }</p>
						<CopyField value={ overview.endpoint_url } label={ __( 'Copy server URL', 'erp' ) } />
					</div>
					<div>
						<p className="mb-1.5 text-sm font-medium text-foreground">
							{ __( 'Claude on the web, desktop and mobile', 'erp' ) }
						</p>
						<p className="text-sm text-muted-foreground">
							{ __(
								'No token needed. In Claude, open Settings → Connectors → Add custom connector and paste the server URL. Claude sends you here to sign in and approve, and appears in the list above once connected. This site must be reachable from the internet over HTTPS.',
								'erp'
							) }
						</p>
					</div>
					<div>
						<p className="mb-1.5 text-sm font-medium text-foreground">{ __( 'Claude Code', 'erp' ) }</p>
						<CopyField value={ claudeCode } label={ __( 'Copy Claude Code command', 'erp' ) } />
					</div>
					<div>
						<p className="mb-1.5 text-sm font-medium text-foreground">
							{ __( 'Cursor, VS Code and other apps that take a JSON config', 'erp' ) }
						</p>
						<CopyField value={ jsonConfig } label={ __( 'Copy JSON config', 'erp' ) } />
					</div>
				</div>
			</section>
		</div>
	);
}
