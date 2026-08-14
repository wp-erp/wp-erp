/**
 * Error state — plugin-ui Alert + retry button (re-fires the resolver).
 */

import { Alert, AlertDescription, AlertTitle, Button } from '@wedevs/plugin-ui';
import { useDispatch } from '@wordpress/data';
import { doAction } from '@wordpress/hooks';
import { RefreshCw } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import type { EmployeeListError } from '@/stores/employees';

import { EMPLOYEES_ACTIONS } from './constants';

interface EmployeesStoreDispatch {
	invalidate: () => void;
}

interface EmployeesErrorProps {
	readonly error: EmployeeListError;
}

export function EmployeesError( { error }: EmployeesErrorProps ): JSX.Element {
	const { invalidate } = useDispatch( employeesStoreName ) as unknown as EmployeesStoreDispatch;

	return (
		<Alert variant="destructive" className="my-6">
			<AlertTitle>{ __( 'Could not load employees', 'erp' ) }</AlertTitle>
			<AlertDescription>{ error.message }</AlertDescription>
			<div className="mt-3">
				<Button
					variant="outline"
					size="sm"
					className="gap-1.5"
					onClick={ () => {
						invalidate();
						doAction( EMPLOYEES_ACTIONS.REFRESH_REQUESTED );
					} }
				>
					<RefreshCw size={ 14 } aria-hidden="true" />
					{ __( 'Retry', 'erp' ) }
				</Button>
			</div>
		</Alert>
	);
}
