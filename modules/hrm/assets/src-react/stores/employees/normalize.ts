/**
 * Defensive coercion of raw `/erp/v2/employees` response items.
 *
 * The v2 controller already casts most fields. This pass keeps the React
 * layer safe against any drift (e.g. a hook that returns a stringly-typed
 * field via `erp_hr_v2_employees_response_item`).
 */

import {
	toBool,
	toEnumOrNull,
	toInt,
	toIntOrNull,
	toObject,
	toStr,
	toStrOrNull,
} from '@/shared/utils/coerce';

import type {
	EmployeeListItem,
	EmployeeLookup,
	EmployeeReportingTo,
	EmployeeStatus,
	RawEmployeeListItem,
} from './types';

const STATUS_VALUES: readonly EmployeeStatus[] = [
	'active',
	'inactive',
	'terminated',
	'resigned',
	'deceased',
];

export function normalizeEmployee( raw: RawEmployeeListItem ): EmployeeListItem {
	const id = toInt( raw.id ?? raw.user_id, 0 );

	return {
		id,
		user_id:          toInt( raw.user_id ?? raw.id, 0 ),
		employee_id:      toStr( raw.employee_id ),
		full_name:        toStr( raw.full_name ),
		first_name:       toStr( raw.first_name ),
		last_name:        toStr( raw.last_name ),
		email:            toStr( raw.email ),
		avatar_url:       toStrOrNull( raw.avatar_url ),
		status:           toEnumOrNull( raw.status, STATUS_VALUES ),
		employee_type:    toStrOrNull( raw.employee_type ),
		hire_date:        toStrOrNull( raw.hire_date ),
		termination_date: toStrOrNull( raw.termination_date ),
		status_date:      toStrOrNull( raw.status_date ),
		is_active:        toBool( raw.is_active ),
		department:       normalizeLookup( raw.department ),
		designation:      normalizeLookup( raw.designation ),
		location:         normalizeLookup( raw.location ),
		reporting_to:     normalizeReportingTo( raw.reporting_to ),
		phone:            toStrOrNull( raw.phone ),
		pay_type:         toStrOrNull( raw.pay_type ),
		extra:            toObject< Record< string, unknown > >( raw.extra ),
	};
}

function normalizeLookup( raw: unknown ): EmployeeLookup | null {
	if ( ! raw || typeof raw !== 'object' ) {
		return null;
	}
	const obj = raw as { id?: unknown; name?: unknown };
	const id  = toIntOrNull( obj.id );
	if ( id === null ) {
		return null;
	}
	return { id, name: toStr( obj.name ) };
}

function normalizeReportingTo( raw: unknown ): EmployeeReportingTo | null {
	if ( ! raw || typeof raw !== 'object' ) {
		return null;
	}
	const obj = raw as { id?: unknown; full_name?: unknown };
	const id  = toIntOrNull( obj.id );
	if ( id === null ) {
		return null;
	}
	return { id, full_name: toStr( obj.full_name ) };
}
