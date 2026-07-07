/**
 * Pure helpers + constants for the central "New Leave Request" dialog: the raw
 * financial-year shape, the date-validation debounce, and the option/placeholder
 * derivations. No component state.
 */

import { __, sprintf } from '@/shared/i18n';

import type { Option } from '../employee-create/options';
import type { AssignablePolicy } from '../employee-create/leave/useEmployeeLeave';

export interface RawFinancialYear {
	readonly id:      number;
	readonly fy_name: string;
}

export const DATE_VALIDATE_DEBOUNCE_MS = 350;

export function toYearOptions( years: readonly RawFinancialYear[] ): Option[] {
	return years.map( ( fy ) => ( { value: String( fy.id ), label: fy.fy_name } ) );
}

export function toPolicyOptions( policies: readonly AssignablePolicy[] ): Option[] {
	return policies.map( ( p ) => ( {
		value: String( p.id ),
		// translators: %1$s policy name, %2$s available day count.
		label: sprintf( __( '%1$s (%2$s available)', 'erp' ), p.name, String( p.available ) ),
	} ) );
}

export function policyPlaceholder( employeeId: string, year: string, policiesLoading: boolean ): string {
	return ! employeeId
		? __( 'Select an employee first', 'erp' )
		: ! year
		? __( 'Select a financial year first', 'erp' )
		: policiesLoading
		? __( 'Loading…', 'erp' )
		: __( '- Select -', 'erp' );
}
