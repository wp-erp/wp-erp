/**
 * The four-way access level picker used in every cell of the access tables.
 */

import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@wedevs/plugin-ui';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import type { McpLevel } from './types';

export function levelLabel( level: McpLevel ): string {
	switch ( level ) {
		case 'read':
			return __( 'Read only', 'erp' );
		case 'write':
			return __( 'Read & write', 'erp' );
		case 'full':
			return __( 'Full (incl. delete)', 'erp' );
		default:
			return __( 'No access', 'erp' );
	}
}

const LEVELS: readonly McpLevel[] = [ 'none', 'read', 'write', 'full' ];

interface LevelSelectProps {
	readonly value:     McpLevel;
	readonly onChange:  ( level: McpLevel ) => void;
	/** What the level applies to, e.g. "Leave for HR Manager". */
	readonly label:     string;
	readonly disabled?: boolean;
}

export function LevelSelect( { value, onChange, label, disabled }: LevelSelectProps ): JSX.Element {
	const items = LEVELS.map( ( level ) => ( { value: level, label: levelLabel( level ) } ) );

	return (
		<Select
			items={ items }
			value={ value }
			onValueChange={ ( v ) => onChange( ( v ?? 'none' ) as McpLevel ) }
			disabled={ disabled ?? false }
		>
			<SelectTrigger
				aria-label={ label }
				className={ [
					'data-[size=default]:h-9 w-full min-w-40 bg-background',
					value === 'none' ? 'text-muted-foreground' : '',
				].join( ' ' ).trim() }
			>
				<SelectValue />
			</SelectTrigger>
			<SelectContent align="start" alignItemWithTrigger={ false }>
				{ items.map( ( item ) => (
					<SelectItem key={ item.value } value={ item.value }>
						{ item.label }
					</SelectItem>
				) ) }
			</SelectContent>
		</Select>
	);
}
