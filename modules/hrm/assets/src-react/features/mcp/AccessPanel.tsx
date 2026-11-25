/**
 * "Access" tab (administrators): who may use which HR feature through an AI
 * client, and how far. One table per audience — roles first, then the
 * individuals who need something different from their role.
 *
 * This never widens what a person can do in HR: a tool still runs with their
 * own WordPress capabilities. It only decides what is reachable over MCP.
 */

import { Button, Input, toast } from '@wedevs/plugin-ui';
import { Ban, Save, Trash2, UserPlus } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';

import { saveSettings, searchUsers } from './api';
import { LevelSelect } from './LevelSelect';
import type { McpAccessPayload, McpLevel, McpLevels, McpSettings, McpUser, McpUserRule } from './types';

interface AccessPanelProps {
	readonly settings: McpSettings;
	readonly onSaved:  ( settings: McpSettings ) => void;
}

export function AccessPanel( { settings, onSaved }: AccessPanelProps ): JSX.Element {
	const [ roles, setRoles ] = useState< Record< string, McpLevels > >( {} );
	const [ users, setUsers ] = useState< readonly McpUserRule[] >( [] );
	const [ dirty, setDirty ] = useState( false );
	const [ busy, setBusy ]   = useState( false );
	const [ query, setQuery ] = useState( '' );
	const [ found, setFound ] = useState< readonly McpUser[] >( [] );

	// Seed the editable copy from the server whenever it (re)loads.
	useEffect( () => {
		setRoles( Object.fromEntries( settings.roles.map( ( r ) => [ r.slug, { ...r.levels } ] ) ) );
		setUsers( settings.users.map( ( u ) => ( { ...u, levels: { ...u.levels } } ) ) );
		setDirty( false );
	}, [ settings ] );

	// Debounced user lookup for the "add a person" box.
	useEffect( () => {
		if ( query.trim().length < 2 ) {
			setFound( [] );
			return undefined;
		}
		const timer = window.setTimeout( () => {
			void searchUsers( query.trim() ).then( setFound ).catch( () => setFound( [] ) );
		}, 250 );
		return () => window.clearTimeout( timer );
	}, [ query ] );

	function setRoleLevel( role: string, group: string, level: McpLevel ): void {
		setRoles( ( prev ) => ( { ...prev, [ role ]: { ...prev[ role ], [ group ]: level } } ) );
		setDirty( true );
	}

	function patchUser( id: number, patch: Partial< McpUserRule > ): void {
		setUsers( ( prev ) => prev.map( ( u ) => ( u.user.id === id ? { ...u, ...patch } : u ) ) );
		setDirty( true );
	}

	function addUser( user: McpUser ): void {
		if ( ! users.some( ( u ) => u.user.id === user.id ) ) {
			// Starts with nothing: an individual rule replaces the role's, so
			// the administrator grants exactly what this person should have.
			const levels = Object.fromEntries( settings.groups.map( ( g ) => [ g.slug, 'none' as McpLevel ] ) );
			setUsers( ( prev ) => [ ...prev, { user, mode: 'custom', levels } ] );
			setDirty( true );
		}
		setQuery( '' );
		setFound( [] );
	}

	function removeUser( id: number ): void {
		setUsers( ( prev ) => prev.filter( ( u ) => u.user.id !== id ) );
		setDirty( true );
	}

	async function handleSave(): Promise< void > {
		const access: McpAccessPayload = {
			roles,
			users: Object.fromEntries( users.map( ( u ) => [ String( u.user.id ), { mode: u.mode, groups: u.levels } ] ) ),
		};
		setBusy( true );
		try {
			onSaved( await saveSettings( { access } ) );
			toast.success( __( 'Access rules saved.', 'erp' ) );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not save access rules.', 'erp' ) );
		} finally {
			setBusy( false );
		}
	}

	return (
		<div className="space-y-6">
			<section className="rounded-[10px] bg-card p-6 shadow-sm">
				<div className="flex flex-wrap items-start justify-between gap-4">
					<div className="max-w-2xl">
						<h2 className="text-base font-semibold text-foreground">{ __( 'Access by role', 'erp' ) }</h2>
						<p className="mt-1 text-sm text-muted-foreground">
							{ __( 'Choose which HR features each role may use through an AI assistant. This only narrows things: a person can never do more over MCP than they can in the HR screens.', 'erp' ) }
						</p>
					</div>
					<Button className="h-10 gap-1.5 px-4" disabled={ busy || ! dirty } onClick={ () => void handleSave() }>
						<Save size={ 16 } aria-hidden="true" />
						{ busy ? __( 'Saving…', 'erp' ) : __( 'Save access', 'erp' ) }
					</Button>
				</div>

				<div className="mt-5 overflow-x-auto">
					<table className="w-full text-left text-sm">
						<thead className="text-xs uppercase text-muted-foreground">
							<tr>
								<th className="py-2 pr-4 font-medium">{ __( 'Feature', 'erp' ) }</th>
								{ settings.roles.map( ( role ) => (
									<th key={ role.slug } className="py-2 pr-4 font-medium">{ role.label }</th>
								) ) }
							</tr>
						</thead>
						<tbody className="divide-y divide-border">
							{ settings.groups.map( ( group ) => (
								<tr key={ group.slug }>
									<td className="max-w-xs py-3 pr-4 align-top">
										<span className="font-medium text-foreground">{ group.label }</span>
										{ group.pro ? (
											<span className="ml-2 rounded bg-primary/10 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-primary">
												{ __( 'Pro', 'erp' ) }
											</span>
										) : null }
										<p className="mt-0.5 text-xs text-muted-foreground">{ group.description }</p>
									</td>
									{ settings.roles.map( ( role ) => (
										<td key={ role.slug } className="py-3 pr-4 align-top">
											<LevelSelect
												value={ roles[ role.slug ]?.[ group.slug ] ?? 'none' }
												onChange={ ( level ) => setRoleLevel( role.slug, group.slug, level ) }
												/* translators: 1: feature name, 2: role name. */
												label={ sprintf( __( '%1$s access for %2$s', 'erp' ), group.label, role.label ) }
											/>
										</td>
									) ) }
								</tr>
							) ) }
						</tbody>
					</table>
				</div>
			</section>

			<section className="rounded-[10px] bg-card p-6 shadow-sm">
				<h2 className="text-base font-semibold text-foreground">{ __( 'Individual access', 'erp' ) }</h2>
				<p className="mt-1 max-w-2xl text-sm text-muted-foreground">
					{ __( 'Give one person something different from their role. A rule here replaces their role\'s access completely, so it can grant more or less — or block them from connecting at all.', 'erp' ) }
				</p>

				<div className="relative mt-4 max-w-md">
					<Input
						value={ query }
						onChange={ ( e ) => setQuery( e.target.value ) }
						placeholder={ __( 'Search a person by name or email to add…', 'erp' ) }
						aria-label={ __( 'Search users', 'erp' ) }
						className="h-9 bg-background px-3 text-sm"
					/>
					{ found.length > 0 ? (
						<ul className="absolute z-10 mt-1 max-h-64 w-full overflow-y-auto rounded-md border border-border bg-popover py-1 shadow-md">
							{ found.map( ( user ) => (
								<li key={ user.id }>
									<Button
										variant="ghost"
										className="h-auto w-full justify-start gap-2 rounded-none px-3 py-2 text-left"
										onClick={ () => addUser( user ) }
									>
										<UserPlus size={ 14 } aria-hidden="true" />
										<span className="min-w-0">
											<span className="block truncate text-sm font-medium text-foreground">{ user.name }</span>
											<span className="block truncate text-xs text-muted-foreground">
												{ [ user.email, ...user.roles ].filter( Boolean ).join( ' · ' ) }
											</span>
										</span>
									</Button>
								</li>
							) ) }
						</ul>
					) : null }
				</div>

				{ users.length === 0 ? (
					<p className="mt-4 text-sm text-muted-foreground">
						{ __( 'No individual rules. Everyone gets what their role allows.', 'erp' ) }
					</p>
				) : (
					<div className="mt-5 space-y-4">
						{ users.map( ( rule ) => (
							<div key={ rule.user.id } className="rounded-md border border-border p-4">
								<div className="flex flex-wrap items-center justify-between gap-3">
									<div className="min-w-0">
										<p className="truncate text-sm font-semibold text-foreground">{ rule.user.name }</p>
										<p className="truncate text-xs text-muted-foreground">
											{ [ rule.user.email, ...rule.user.roles ].filter( Boolean ).join( ' · ' ) }
										</p>
									</div>
									<div className="flex items-center gap-2">
										<Button
											variant={ rule.mode === 'blocked' ? 'default' : 'outline' }
											className="h-8 gap-1.5"
											onClick={ () => patchUser( rule.user.id, { mode: rule.mode === 'blocked' ? 'custom' : 'blocked' } ) }
										>
											<Ban size={ 14 } aria-hidden="true" />
											{ rule.mode === 'blocked' ? __( 'Blocked — click to unblock', 'erp' ) : __( 'Block', 'erp' ) }
										</Button>
										<Button
											variant="ghost"
											size="icon"
											className="text-destructive hover:bg-destructive/10 hover:text-destructive"
											aria-label={ __( 'Remove individual rule', 'erp' ) }
											onClick={ () => removeUser( rule.user.id ) }
										>
											<Trash2 size={ 16 } aria-hidden="true" />
										</Button>
									</div>
								</div>
								{ rule.mode === 'blocked' ? (
									<p className="mt-3 text-sm text-muted-foreground">
										{ __( 'This person cannot connect an AI assistant, whatever their role allows.', 'erp' ) }
									</p>
								) : (
									<div className="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
										{ settings.groups.map( ( group ) => (
											<div key={ group.slug }>
												<p className="mb-1 text-xs font-medium text-muted-foreground">{ group.label }</p>
												<LevelSelect
													value={ rule.levels[ group.slug ] ?? 'none' }
													onChange={ ( level ) =>
														patchUser( rule.user.id, { levels: { ...rule.levels, [ group.slug ]: level } } )
													}
													/* translators: 1: feature name, 2: person's name. */
													label={ sprintf( __( '%1$s access for %2$s', 'erp' ), group.label, rule.user.name ) }
												/>
											</div>
										) ) }
									</div>
								) }
							</div>
						) ) }
					</div>
				) }
			</section>
		</div>
	);
}
