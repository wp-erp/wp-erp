/**
 * Create-flow helpers — "does this email already belong to a WP user /
 * employee?" and "convert that WP user into an employee". Mirror the legacy
 * `check_user` + `employee_create_from_wp_user` AJAX handlers via their v2
 * routes (`EmployeeUserControllerV2`).
 */

import { request, restPath } from '@/shared/utils/apiFetch';

export type UserCheckType = 'none' | 'wp_user' | 'employee';

export interface UserCheckResult {
	readonly available: boolean;
	readonly type:      UserCheckType;
	readonly user:      { readonly id: number; readonly display_name: string; readonly email: string } | null;
}

/** Look up an email. `type`: none → free; wp_user → convertible; employee → taken. */
export async function checkUser( email: string ): Promise< UserCheckResult > {
	const path = restPath( 'v2', '/employees/check-user', { email } );
	return request< UserCheckResult >( path );
}

/** Convert an existing WP user into an employee; resolves with the new user id. */
export async function convertUser( userId: number ): Promise< number > {
	const path = restPath( 'v2', '/employees/from-wp-user' );
	const body = await request< { converted: boolean; user_id: number } >( path, {
		method: 'POST',
		data: { user_id: userId },
	} );
	return body.user_id;
}
