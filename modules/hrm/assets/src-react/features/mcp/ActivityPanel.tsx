/**
 * "Activity" tab: the audit log of tool calls. Administrators see everyone's;
 * anyone else sees their own.
 */

import { Button } from '@wedevs/plugin-ui';
import { History } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { JSX } from 'react';

import { EmptyState } from '@/shared/components/EmptyState';
import { StatusPill } from '@/shared/components/StatusPill';
import type { StatusTone } from '@/shared/components/StatusPill';
import { TableSkeleton } from '@/shared/components/TableSkeleton';
import { __, dateI18n, sprintf } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';

import { fetchLog } from './api';
import type { McpLogEntry } from './types';

const STATUS_TONE: Record< McpLogEntry[ 'status' ], StatusTone > = {
	success: 'success',
	error:   'warning',
	refused: 'danger',
};

function statusLabel( status: McpLogEntry[ 'status' ] ): string {
	if ( status === 'success' ) {
		return __( 'Success', 'erp' );
	}
	return status === 'error' ? __( 'Failed', 'erp' ) : __( 'Refused', 'erp' );
}

export function ActivityPanel(): JSX.Element {
	const [ page, setPage ]             = useState( 1 );
	const [ items, setItems ]           = useState< readonly McpLogEntry[] >( [] );
	const [ totalPages, setTotalPages ] = useState( 1 );
	const [ loading, setLoading ]       = useState( true );
	const [ error, setError ]           = useState< string | null >( null );

	useEffect( () => {
		let cancelled = false;
		setLoading( true );
		fetchLog( page )
			.then( ( res ) => {
				if ( ! cancelled ) {
					setItems( res.items );
					setTotalPages( res.totalPages );
					setError( null );
				}
			} )
			.catch( ( raw ) => {
				if ( ! cancelled ) {
					setError( ( raw as ApiError )?.message ?? __( 'Could not load activity.', 'erp' ) );
				}
			} )
			.finally( () => {
				if ( ! cancelled ) {
					setLoading( false );
				}
			} );
		return () => {
			cancelled = true;
		};
	}, [ page ] );

	if ( error ) {
		return <p className="text-sm text-destructive">{ error }</p>;
	}

	if ( ! loading && items.length === 0 ) {
		return (
			<EmptyState
				icon={ History }
				title={ __( 'No activity yet.', 'erp' ) }
				description={ __( 'Every tool an AI assistant runs, and every one it is refused, is listed here.', 'erp' ) }
			/>
		);
	}

	return (
		<section className="rounded-[10px] bg-card p-6 shadow-sm">
			{ loading ? (
				<TableSkeleton rows={ 8 } />
			) : (
				<div className="overflow-x-auto">
					<table className="w-full text-left text-sm">
						<thead className="text-xs uppercase text-muted-foreground">
							<tr>
								<th className="py-2 pr-4 font-medium">{ __( 'When', 'erp' ) }</th>
								<th className="py-2 pr-4 font-medium">{ __( 'User', 'erp' ) }</th>
								<th className="py-2 pr-4 font-medium">{ __( 'Tool', 'erp' ) }</th>
								<th className="py-2 pr-4 font-medium">{ __( 'Result', 'erp' ) }</th>
								<th className="py-2 font-medium">{ __( 'Details', 'erp' ) }</th>
							</tr>
						</thead>
						<tbody className="divide-y divide-border">
							{ items.map( ( entry ) => (
								<tr key={ entry.id }>
									<td className="whitespace-nowrap py-2.5 pr-4 align-top text-muted-foreground">
										{ entry.created_at ? dateI18n( 'M j, Y g:i a', entry.created_at ) : '—' }
									</td>
									<td className="py-2.5 pr-4 align-top text-foreground">{ entry.user.name }</td>
									<td className="py-2.5 pr-4 align-top">
										<span className="font-medium text-foreground">{ entry.tool_title }</span>
										<code className="block text-xs text-muted-foreground">{ entry.tool }</code>
									</td>
									<td className="py-2.5 pr-4 align-top">
										<StatusPill tone={ STATUS_TONE[ entry.status ] ?? 'neutral' }>{ statusLabel( entry.status ) }</StatusPill>
									</td>
									<td className="max-w-md py-2.5 align-top text-xs text-muted-foreground">
										{ entry.message ? <p className="mb-1 text-foreground">{ entry.message }</p> : null }
										{ entry.arguments && Object.keys( entry.arguments ).length > 0 ? (
											<code className="break-all">{ JSON.stringify( entry.arguments ) }</code>
										) : null }
									</td>
								</tr>
							) ) }
						</tbody>
					</table>
				</div>
			) }

			{ totalPages > 1 ? (
				<div className="mt-4 flex items-center justify-end gap-3">
					<Button variant="outline" className="h-8" disabled={ page <= 1 || loading } onClick={ () => setPage( ( p ) => p - 1 ) }>
						{ __( 'Previous', 'erp' ) }
					</Button>
					<span className="text-xs text-muted-foreground">
						{ sprintf(
							/* translators: 1: current page, 2: total pages. */
							__( 'Page %1$d of %2$d', 'erp' ),
							page,
							totalPages
						) }
					</span>
					<Button variant="outline" className="h-8" disabled={ page >= totalPages || loading } onClick={ () => setPage( ( p ) => p + 1 ) }>
						{ __( 'Next', 'erp' ) }
					</Button>
				</div>
			) : null }
		</section>
	);
}
