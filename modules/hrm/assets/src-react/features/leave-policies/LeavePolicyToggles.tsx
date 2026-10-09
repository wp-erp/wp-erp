/**
 * The two switch toggles of the leave-policy form: apply to new employees
 * automatically (always), and (create-only) apply for existing matching
 * employees now. Presentational — the dialog owns state.
 */

import { Label, Switch } from '@wedevs/plugin-ui';
import type { Dispatch, JSX, SetStateAction } from 'react';

import { __ } from '@/shared/i18n';

import type { LeavePolicy } from './types';
import type { FormState } from './leave-policy-form-helpers';

interface LeavePolicyTogglesProps {
	readonly form:    FormState;
	readonly setForm: Dispatch< SetStateAction< FormState > >;
	readonly editing: LeavePolicy | null;
}

export function LeavePolicyToggles( { form, setForm, editing }: LeavePolicyTogglesProps ): JSX.Element {
	return (
		<>
			<div className="flex items-center justify-between rounded-md border border-border bg-muted/20 px-4 py-3">
				<Label htmlFor="policy_apply_new" className="text-sm font-medium text-foreground">
					{ __( 'Apply to new employees automatically', 'erp' ) }
				</Label>
				<Switch
					id="policy_apply_new"
					checked={ form.apply_for_new_users }
					onCheckedChange={ ( checked ) => setForm( ( p ) => ( { ...p, apply_for_new_users: checked } ) ) }
				/>
			</div>

			{ ! editing ? (
				<div className="flex items-center justify-between rounded-md border border-border bg-muted/20 px-4 py-3">
					<Label htmlFor="policy_apply_existing" className="text-sm font-medium text-foreground">
						{ __( 'Apply for existing employees', 'erp' ) }
						<span className="mt-0.5 block text-xs font-normal text-muted-foreground">
							{ __( 'Entitle existing matching employees to this policy now.', 'erp' ) }
						</span>
					</Label>
					<Switch
						id="policy_apply_existing"
						checked={ form.apply_for_existing }
						onCheckedChange={ ( checked ) => setForm( ( p ) => ( { ...p, apply_for_existing: checked } ) ) }
					/>
				</div>
			) : null }
		</>
	);
}
