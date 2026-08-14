/**
 * Create-only "Notification" section: opt in to a welcome email, and (when on)
 * opt in to including login details. The caller renders this only in create
 * mode, matching the legacy form. Presentational — the form owns the state.
 */

import { Checkbox } from '@wedevs/plugin-ui';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { FormSection } from './fields';

interface EmployeeNotificationSectionProps {
	readonly notify:        boolean;
	readonly setNotify:     ( value: boolean ) => void;
	readonly sendLogin:     boolean;
	readonly setSendLogin:  ( value: boolean ) => void;
}

export function EmployeeNotificationSection( {
	notify,
	setNotify,
	sendLogin,
	setSendLogin,
}: EmployeeNotificationSectionProps ): JSX.Element {
	return (
		<FormSection title={ __( 'Notification', 'erp' ) }>
			<label className="flex items-start gap-2.5 sm:col-span-2 lg:col-span-3">
				<Checkbox
					checked={ notify }
					onCheckedChange={ ( v ) =>
						setNotify( v === true )
					}
					className="mt-0.5"
				/>
				<span className="text-sm text-foreground">
					{ __(
						'Send the employee a welcome email.',
						'erp'
					) }
				</span>
			</label>
			{ notify ? (
				<label className="flex items-start gap-2.5 sm:col-span-2 lg:col-span-3">
					<Checkbox
						checked={ sendLogin }
						onCheckedChange={ ( v ) =>
							setSendLogin( v === true )
						}
						className="mt-0.5"
					/>
					<span className="text-sm text-foreground">
						{ __(
							'Include login details in the welcome email.',
							'erp'
						) }
					</span>
				</label>
			) : null }
		</FormSection>
	);
}
