/**
 * "Settings" tab (administrators): the master switch, browser origins and how
 * long the activity log is kept.
 */

import { Button, Input, Label, Switch, Textarea, toast } from '@wedevs/plugin-ui';
import { Save } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';

import { saveSettings } from './api';
import type { McpSettings } from './types';

interface SettingsPanelProps {
	readonly settings: McpSettings;
	readonly onSaved:  ( settings: McpSettings ) => void;
}

export function SettingsPanel( { settings, onSaved }: SettingsPanelProps ): JSX.Element {
	const [ origins, setOrigins ] = useState( '' );
	const [ days, setDays ]       = useState( '30' );
	const [ busy, setBusy ]       = useState( false );

	useEffect( () => {
		setOrigins( settings.allowed_origins.join( '\n' ) );
		setDays( String( settings.log_retention_days ) );
	}, [ settings ] );

	async function save( changes: Record< string, unknown >, message: string ): Promise< void > {
		setBusy( true );
		try {
			onSaved( await saveSettings( changes ) );
			toast.success( message );
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not save settings.', 'erp' ) );
		} finally {
			setBusy( false );
		}
	}

	return (
		<div className="space-y-6">
			<section className="flex items-start justify-between gap-6 rounded-[10px] bg-card p-6 shadow-sm">
				<div className="max-w-2xl">
					<Label htmlFor="mcp_enabled" className="text-base font-semibold text-foreground">
						{ __( 'MCP server', 'erp' ) }
					</Label>
					<p className="mt-1 text-sm text-muted-foreground">
						{ __( 'When on, people you have given access can connect AI assistants such as Claude or Cursor to HR. Each assistant acts as the person whose token it uses. When off, the endpoint does not exist and every token stops working.', 'erp' ) }
					</p>
				</div>
				<Switch
					id="mcp_enabled"
					checked={ settings.enabled }
					disabled={ busy }
					onCheckedChange={ ( on ) =>
						void save(
							{ enabled: on },
							on ? __( 'MCP server turned on.', 'erp' ) : __( 'MCP server turned off.', 'erp' )
						)
					}
				/>
			</section>

			<section className="rounded-[10px] bg-card p-6 shadow-sm">
				<div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
					<div>
						<Label htmlFor="mcp_origins" className="text-sm font-medium text-foreground">
							{ __( 'Allowed browser origins', 'erp' ) }
						</Label>
						<p className="mb-2 mt-1 text-xs text-muted-foreground">
							{ __( 'Only needed for AI apps that run inside a web browser. One origin per line, e.g. https://example.com. Desktop and command-line apps are not affected.', 'erp' ) }
						</p>
						<Textarea
							id="mcp_origins"
							rows={ 4 }
							value={ origins }
							onChange={ ( e ) => setOrigins( e.target.value ) }
							className="bg-background text-sm"
						/>
					</div>
					<div>
						<Label htmlFor="mcp_days" className="text-sm font-medium text-foreground">
							{ __( 'Keep activity for (days)', 'erp' ) }
						</Label>
						<p className="mb-2 mt-1 text-xs text-muted-foreground">
							{ __( 'Older entries in the activity log are deleted nightly. Use 0 to keep everything.', 'erp' ) }
						</p>
						<Input
							id="mcp_days"
							type="number"
							min={ 0 }
							value={ days }
							onChange={ ( e ) => setDays( e.target.value ) }
							className="h-10 w-32 bg-background px-3 text-sm"
						/>
					</div>
				</div>
				<Button
					className="mt-5 h-10 gap-1.5 px-4"
					disabled={ busy }
					onClick={ () =>
						void save(
							{
								allowed_origins:    origins.split( '\n' ).map( ( o ) => o.trim() ).filter( Boolean ),
								log_retention_days: Math.max( 0, parseInt( days, 10 ) || 0 ),
							},
							__( 'Settings saved.', 'erp' )
						)
					}
				>
					<Save size={ 16 } aria-hidden="true" />
					{ __( 'Save Changes', 'erp' ) }
				</Button>
			</section>
		</div>
	);
}
