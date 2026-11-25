/**
 * REST calls for the MCP screen. Thin wrappers over `erp/v2/hrm/mcp/*`.
 */

import { request, requestWithHeaders, restPath } from '@/shared/utils/apiFetch';

import type { McpLogEntry, McpOverview, McpSettings, McpToken, McpUser } from './types';

const base = '/hrm/mcp';

export function fetchOverview(): Promise< McpOverview > {
	return request< McpOverview >( restPath( 'v2', `${ base }/overview` ) );
}

export function fetchSettings(): Promise< McpSettings > {
	return request< McpSettings >( restPath( 'v2', `${ base }/settings` ) );
}

export function saveSettings( changes: Record< string, unknown > ): Promise< McpSettings > {
	return request< McpSettings >( restPath( 'v2', `${ base }/settings` ), { method: 'POST', data: changes } );
}

export function searchUsers( search: string ): Promise< McpUser[] > {
	return request< McpUser[] >( restPath( 'v2', `${ base }/users`, { search } ) );
}

export function fetchTokens( all: boolean ): Promise< McpToken[] > {
	return request< McpToken[] >( restPath( 'v2', `${ base }/tokens`, all ? { all: 1 } : undefined ) );
}

export function createToken( label: string, scope: 'full' | 'read' ): Promise< McpToken > {
	return request< McpToken >( restPath( 'v2', `${ base }/tokens` ), { method: 'POST', data: { label, scope } } );
}

export function revokeToken( id: number ): Promise< McpToken > {
	return request< McpToken >( restPath( 'v2', `${ base }/tokens/${ id }` ), { method: 'DELETE' } );
}

export async function fetchLog( page: number ): Promise< { items: McpLogEntry[]; totalPages: number } > {
	const { body, headers } = await requestWithHeaders< McpLogEntry[] >(
		restPath( 'v2', `${ base }/log`, { page, per_page: 20 } )
	);

	return {
		items:      Array.isArray( body ) ? body : [],
		totalPages: Number( headers.get( 'X-WP-TotalPages' ) ?? 1 ) || 1,
	};
}
