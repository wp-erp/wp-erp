/**
 * REST client for the onboarding wizard (`erp/v2/onboarding/*`).
 */

import { request, restPath } from '@/shared/utils/apiFetch';

import type { OnboardingStatus, OnboardingValues } from './types';

/** GET the whole wizard state (completion flag + hydrated values + option lists). */
export function fetchStatus(): Promise< OnboardingStatus > {
	return request< OnboardingStatus >( restPath( 'v2', '/onboarding' ) );
}

/** POST the keys one step edits; the response echoes the re-hydrated values. */
export function saveStep(
	data: Partial< OnboardingValues >
): Promise< { success: boolean; values: OnboardingValues } > {
	return request( restPath( 'v2', '/onboarding/save-step' ), {
		method: 'POST',
		data,
	} );
}

/** POST-complete: creates the chosen leave policies, returns the redirect target. */
export function completeOnboarding(): Promise< { redirect_url: string } > {
	return request( restPath( 'v2', '/onboarding/complete' ), {
		method: 'POST',
		data: {},
	} );
}

/** What `POST onboarding/sample-data` created. */
export interface SampleDataResult {
	readonly employees: number;
	readonly departments: number;
	readonly leave_types: number;
	readonly holidays: number;
	readonly requests: number;
}

/** POST: fill the site with sample data (refused if it is already there). */
export function loadSampleData(): Promise< SampleDataResult > {
	return request( restPath( 'v2', '/onboarding/sample-data' ), {
		method: 'POST',
		data: {},
	} );
}
