/**
 * Data hook for the employee "General" sub-entities — Work Experience,
 * Education, Dependents — under `erp/v2/employees/{id}/{section}`.
 *
 * Mirrors the legacy General-tab AJAX handlers (the admin reference). Each
 * section is an independent list with create-or-update (POST) + delete (DELETE).
 * Same direct-fetch pattern as the Notes/Performance tabs.
 */

import { useCallback, useEffect, useState } from 'react';

import { __ } from '@/shared/i18n';
import { request, restPath } from '@/shared/utils/apiFetch';
import type { ApiError } from '@/shared/utils/apiFetch';

export type GeneralSection = 'experiences' | 'educations' | 'dependents';

export interface ExperienceRow {
	readonly id:           number;
	readonly company_name: string;
	readonly job_title:    string;
	readonly from:         string;
	readonly to:           string;
	readonly description:  string;
}

export interface EducationRow {
	readonly id:              number;
	readonly school:          string;
	readonly degree:          string;
	readonly field:           string;
	readonly result:          string;
	readonly result_type:     string;
	readonly finished:        number | string;
	readonly notes:           string;
	readonly interest:        string;
	readonly expiration_date: string;
}

export interface DependentRow {
	readonly id:       number;
	readonly name:     string;
	readonly relation: string;
	readonly dob:      string;
}

export interface UseEmployeeGeneral {
	readonly experiences: readonly ExperienceRow[];
	readonly educations:  readonly EducationRow[];
	readonly dependents:  readonly DependentRow[];
	readonly loading:     boolean;
	readonly error:       string | null;
	readonly save:        ( section: GeneralSection, data: Record< string, unknown > ) => Promise< void >;
	readonly remove:      ( section: GeneralSection, id: number ) => Promise< void >;
}

export function useEmployeeGeneral( userId: number ): UseEmployeeGeneral {
	const [ experiences, setExperiences ] = useState< readonly ExperienceRow[] >( [] );
	const [ educations, setEducations ]   = useState< readonly EducationRow[] >( [] );
	const [ dependents, setDependents ]   = useState< readonly DependentRow[] >( [] );
	const [ loading, setLoading ]         = useState( true );
	const [ error, setError ]             = useState< string | null >( null );
	const [ nonce, setNonce ]             = useState( 0 );

	useEffect( () => {
		let cancelled = false;
		setLoading( true );
		setError( null );

		const path = ( section: GeneralSection ): string => restPath( 'v2', `/employees/${ userId }/${ section }` );

		Promise.all( [
			request< ExperienceRow[] >( path( 'experiences' ) ),
			request< EducationRow[] >( path( 'educations' ) ),
			request< DependentRow[] >( path( 'dependents' ) ),
		] )
			.then( ( [ exp, edu, dep ] ) => {
				if ( cancelled ) {
					return;
				}
				setExperiences( exp ?? [] );
				setEducations( edu ?? [] );
				setDependents( dep ?? [] );
			} )
			.catch( ( raw ) => {
				if ( ! cancelled ) {
					setError( ( raw as ApiError )?.message ?? __( 'Could not load profile details.', 'erp' ) );
				}
			} )
			.finally( () => {
				if ( ! cancelled ) {
					setLoading( false );
				}
			} );

		return () => {
			cancelled = true;
		};
	}, [ userId, nonce ] );

	const save = useCallback(
		async ( section: GeneralSection, data: Record< string, unknown > ): Promise< void > => {
			await request( restPath( 'v2', `/employees/${ userId }/${ section }` ), { method: 'POST', data } );
			setNonce( ( n ) => n + 1 );
		},
		[ userId ]
	);

	const remove = useCallback(
		async ( section: GeneralSection, id: number ): Promise< void > => {
			await request( restPath( 'v2', `/employees/${ userId }/${ section }/${ id }` ), { method: 'DELETE' } );
			setNonce( ( n ) => n + 1 );
		},
		[ userId ]
	);

	return { experiences, educations, dependents, loading, error, save, remove };
}
