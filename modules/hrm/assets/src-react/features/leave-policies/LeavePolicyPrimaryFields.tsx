/**
 * Primary fields of the leave-policy form: Leave Type + Financial Year (required
 * on create, with an inline "+ Add new" leave type), Days + Color, and the
 * edit-mode note that days are locked. Presentational — the dialog owns state.
 */

import { Label } from '@wedevs/plugin-ui';
import type { Dispatch, JSX, SetStateAction } from 'react';

import { QuickAddButton } from '@/shared/components/QuickAddButton';
import { __ } from '@/shared/i18n';

import { SmartSelectField, TextField } from '../employee-create/fields';
import type { Option } from '../employee-create/options';
import type { LeavePolicy } from './types';
import type { FormState, PolicyErrors } from './leave-policy-form-helpers';

interface LeavePolicyPrimaryFieldsProps {
	readonly form:          FormState;
	readonly errors:        PolicyErrors;
	readonly setForm:       Dispatch< SetStateAction< FormState > >;
	readonly setErrors:     Dispatch< SetStateAction< PolicyErrors > >;
	readonly editing:       LeavePolicy | null;
	readonly leaveTypeOpts: Option[];
	readonly fYearOpts:     Option[];
	readonly busy:          boolean;
	readonly onAddType:     () => void;
}

export function LeavePolicyPrimaryFields( {
	form,
	errors,
	setForm,
	setErrors,
	editing,
	leaveTypeOpts,
	fYearOpts,
	busy,
	onAddType,
}: LeavePolicyPrimaryFieldsProps ): JSX.Element {
	return (
		<>
			<div className="grid grid-cols-2 gap-4">
				<SmartSelectField
					id="policy_leave_id"
					label={ __( 'Leave Type', 'erp' ) }
					required
					options={ leaveTypeOpts }
					value={ form.leave_id }
					onChange={ ( v ) => {
						setForm( ( p ) => ( { ...p, leave_id: v } ) );
						setErrors( ( p ) => ( { ...p, leave_id: undefined } ) );
					} }
					error={ errors.leave_id }
					placeholder={ __( '- Select -', 'erp' ) }
					searchPlaceholder={ __( 'Search leave types…', 'erp' ) }
					emptyMessage={ __( 'No leave types found.', 'erp' ) }
					labelAction={
						! editing ? (
							<QuickAddButton
								label={ __( 'Add new', 'erp' ) }
								onClick={ onAddType }
								disabled={ busy }
							/>
						) : undefined
					}
				/>
				<SmartSelectField
					id="policy_f_year"
					label={ __( 'Financial Year', 'erp' ) }
					required
					options={ fYearOpts }
					value={ form.f_year }
					onChange={ ( v ) => {
						setForm( ( p ) => ( { ...p, f_year: v } ) );
						setErrors( ( p ) => ( { ...p, f_year: undefined } ) );
					} }
					error={ errors.f_year }
					placeholder={ __( '- Select -', 'erp' ) }
					searchPlaceholder={ __( 'Search…', 'erp' ) }
					emptyMessage={ __( 'No financial years found.', 'erp' ) }
				/>
			</div>

			<div className="grid grid-cols-2 gap-4">
				<TextField
					id="policy_days"
					label={ __( 'Days', 'erp' ) }
					type="number"
					required={ ! editing }
					value={ form.days }
					onChange={ ( v ) => {
						setForm( ( p ) => ( { ...p, days: v } ) );
						setErrors( ( p ) => ( { ...p, days: undefined } ) );
					} }
					error={ errors.days }
					className={ editing ? 'opacity-60' : undefined }
				/>
				<div className="flex min-w-0 flex-col gap-2.5">
					<Label htmlFor="policy_color" className="text-sm font-medium text-foreground">
						{ __( 'Color', 'erp' ) }
						<span className="ml-0.5 text-destructive">*</span>
					</Label>
					<div className="flex items-center gap-3">
						<input
							id="policy_color"
							type="color"
							value={ form.color }
							onChange={ ( e ) => setForm( ( p ) => ( { ...p, color: e.target.value } ) ) }
							className="h-10 w-16 cursor-pointer rounded-md border border-border bg-background p-1"
						/>
						<span className="text-sm text-muted-foreground">{ form.color }</span>
					</div>
				</div>
			</div>

			{ editing ? (
				<p className="-mt-2 text-xs text-muted-foreground">
					{ __( 'The number of days cannot be changed after a policy is created.', 'erp' ) }
				</p>
			) : null }
		</>
	);
}
