/**
 * Permission tab for the single-employee profile.
 *
 * Renders one toggle per role the acting user may grant — HR Manager (free) plus
 * any active module's manager roles (CRM Manager/Agent, Accounting Manager,
 * Recruiter), exactly the set the legacy `erp_hr_permission_management` action
 * produced. Each toggle PUTs the full role map; the server applies HR inline and
 * fires `erp_hr_after_employee_permission_set` so modules apply their own roles.
 */

import { Spinner, Switch, toast } from '@wedevs/plugin-ui';
import { useState } from 'react';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';

import { useEmployeePermission } from './useEmployeePermission';

export function EmployeePermissionTab( { userId }: { readonly userId: number } ): JSX.Element {
	const { roles, loading, error, save } = useEmployeePermission( userId );
	const [ savingKey, setSavingKey ] = useState< string | null >( null );

	async function handleToggle( key: string, label: string, next: boolean ): Promise< void > {
		setSavingKey( key );
		try {
			await save( key, next );
			toast.success(
				next
					? // translators: %s is a role name, e.g. "HR Manager".
						sprintf( __( '%s access granted.', 'erp' ), label )
					: sprintf( __( '%s access revoked.', 'erp' ), label )
			);
		} catch ( raw ) {
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not update permission.', 'erp' ) );
		} finally {
			setSavingKey( null );
		}
	}

	if ( error ) {
		return <p className="rounded-lg border border-border bg-card p-6 text-sm text-destructive">{ error }</p>;
	}

	if ( loading ) {
		return (
			<div className="flex items-center justify-center gap-2 rounded-lg border border-border bg-card p-10 text-sm text-muted-foreground">
				<Spinner className="size-4" />
				{ __( 'Loading permissions…', 'erp' ) }
			</div>
		);
	}

	return (
		<section className="overflow-hidden rounded-[10px] bg-card shadow-sm">
			<header className="px-6 py-4">
				<h2 className="m-0 text-2xl font-bold leading-tight tracking-tight text-foreground">{ __( 'Permission Management', 'erp' ) }</h2>
				<p className="mt-0.5 text-xs text-muted-foreground">
					{ __( 'Additional access for this employee beyond their default role.', 'erp' ) }
				</p>
			</header>
			<div className="mx-6 h-px bg-border" />
			<div className="divide-y divide-border">
				{ roles.map( ( role ) => (
					<div key={ role.key } className="flex items-center justify-between gap-4 p-5">
						<div className="min-w-0">
							<div className="text-sm font-medium text-foreground">{ role.label }</div>
							<p className="mt-0.5 text-xs text-muted-foreground">{ role.description }</p>
						</div>
						<Switch
							checked={ role.enabled }
							onCheckedChange={ ( next: boolean ) => void handleToggle( role.key, role.label, next ) }
							disabled={ savingKey !== null }
							aria-label={ role.label }
						/>
					</div>
				) ) }
			</div>
		</section>
	);
}
