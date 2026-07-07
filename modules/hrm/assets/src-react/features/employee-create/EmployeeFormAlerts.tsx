/**
 * Top-of-form alert stack for the employee create/edit form: submit error,
 * missing-org dependency hint, the create-mode email collision / WP-user
 * conversion notices, and the validation summary. Presentational — the form
 * owns the state; this just renders whatever is passed in.
 */

import {
	Alert,
	AlertDescription,
	AlertTitle,
	Button,
} from '@wedevs/plugin-ui';
import type { JSX } from 'react';

import { DependencyHint } from '@/shared/components/DependencyHint';
import { __ } from '@/shared/i18n';

import type { UserCheckResult } from './useUserCheck';

interface EmployeeFormAlertsProps {
	readonly submitError:     string | null;
	readonly missingOrgSteps: { label: string; path: string }[];
	readonly isEdit:          boolean;
	readonly userCheck:       UserCheckResult | null;
	readonly converting:      boolean;
	readonly onConvert:       () => void;
	readonly errors:          Record< string, string >;
}

export function EmployeeFormAlerts( {
	submitError,
	missingOrgSteps,
	isEdit,
	userCheck,
	converting,
	onConvert,
	errors,
}: EmployeeFormAlertsProps ): JSX.Element {
	return (
		<>
			{ submitError ? (
				<Alert variant="destructive" className="mb-6">
					<AlertTitle>
						{ __( 'Something went wrong', 'erp' ) }
					</AlertTitle>
					<AlertDescription>{ submitError }</AlertDescription>
				</Alert>
			) : null }

			{ missingOrgSteps.length > 0 ? (
				<div className="mb-6">
					<DependencyHint
						message={ __(
							'Set up your organisation before adding employees — Department and Designation are required.',
							'erp'
						) }
						steps={ missingOrgSteps }
					/>
				</div>
			) : null }

			{ ! isEdit && userCheck && userCheck.type === 'employee' ? (
				<Alert variant="destructive" className="mb-6">
					<AlertTitle>
						{ __( 'Email already in use', 'erp' ) }
					</AlertTitle>
					<AlertDescription>
						{ __(
							'An employee already exists with this email address.',
							'erp'
						) }
					</AlertDescription>
				</Alert>
			) : null }

			{ ! isEdit &&
			userCheck &&
			userCheck.type === 'wp_user' &&
			userCheck.user ? (
				<Alert className="mb-6">
					<AlertTitle>
						{ __(
							'This email belongs to an existing user',
							'erp'
						) }
					</AlertTitle>
					<AlertDescription>
						<div className="flex flex-col gap-3">
							<span>
								{ __(
									'A WordPress user with this email already exists. Convert them into an employee instead of creating a new account.',
									'erp'
								) }
							</span>
							<div>
								<Button
									type="button"
									variant="outline"
									className="h-9 px-4"
									disabled={ converting }
									onClick={ onConvert }
								>
									{ converting
										? __( 'Converting…', 'erp' )
										: __(
												'Convert to employee',
												'erp'
										  ) }
								</Button>
							</div>
						</div>
					</AlertDescription>
				</Alert>
			) : null }

			{ Object.keys( errors ).length > 0 ? (
				<Alert variant="destructive" className="mb-6">
					<AlertTitle>
						{ __( 'Please correct the following', 'erp' ) }
					</AlertTitle>
					<AlertDescription>
						<ul className="list-disc pl-5">
							{ Array.from(
								new Set( Object.values( errors ) )
							).map( ( msg ) => (
								<li key={ msg }>{ msg }</li>
							) ) }
						</ul>
					</AlertDescription>
				</Alert>
			) : null }
		</>
	);
}
