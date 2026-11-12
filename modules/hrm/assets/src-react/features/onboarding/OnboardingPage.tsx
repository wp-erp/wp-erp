/**
 * WP ERP setup wizard (`index.php?page=erp-setup`).
 *
 * Holds the step index and a hydrated `values` blob. Each data step saves its
 * own keys to `erp/v2/onboarding/save-step` on "Next"; Add Team imports through
 * `erp/v2/employees/import`; reaching Ready calls `/complete`, which creates
 * the chosen leave policies. Running the wizard again is safe: it starts from
 * what is stored and only adds what is missing.
 */

import { Spinner, toast } from '@wedevs/plugin-ui';
import { useEffect, useMemo, useRef, useState } from 'react';
import type { ComponentType, JSX } from 'react';

import { __ } from '@/shared/i18n';
import type { ApiError } from '@/shared/utils/apiFetch';

import { completeOnboarding, fetchStatus, saveStep } from './api';
import { OnboardingLayout } from './OnboardingLayout';
import { OnboardingProgress } from './OnboardingProgress';
import { BasicStep } from './steps/BasicStep';
import { DepartmentsStep } from './steps/DepartmentsStep';
import { DesignationsStep } from './steps/DesignationsStep';
import { ImportStep } from './steps/ImportStep';
import { IntroStep } from './steps/IntroStep';
import { LeaveStep } from './steps/LeaveStep';
import { ReadyStep } from './steps/ReadyStep';
import { WorkdayStep } from './steps/WorkdayStep';
import type { OnboardingMeta, OnboardingValues, StepProps } from './types';

interface StepDef {
	readonly id: string;
	readonly name: string;
	readonly component: ComponentType< StepProps >;
	/** Whether "Next" saves the step (framing screens and Add Team do not). */
	readonly save: boolean;
}

const DEFAULT_VALUES: OnboardingValues = {
	companyName: '',
	companyStartDate: '',
	financialYearStarts: 'january',
	currency: '148',
	dateFormat: 'd-m-Y',
	businessType: '',
	shareEssentials: true,
	departments: [],
	designations: [],
	leaveYears: [],
	selectedLeaveTypes: [],
	workingDays: { mon: '8', tue: '8', wed: '8', thu: '8', fri: '8', sat: '0', sun: '0' },
	workingHours: { start: '09:00', end: '17:00' },
};

const DEFAULT_META: OnboardingMeta = { leaveTypes: [], currencies: [], dateFormats: [], businessTypes: [] };

function useSteps(): StepDef[] {
	return useMemo(
		() => [
			{ id: 'introduction', name: __( 'Introduction', 'erp' ), component: IntroStep, save: false },
			{ id: 'basic', name: __( 'Company Profile', 'erp' ), component: BasicStep, save: true },
			{ id: 'departments', name: __( 'Departments', 'erp' ), component: DepartmentsStep, save: true },
			{ id: 'designations', name: __( 'Designations', 'erp' ), component: DesignationsStep, save: true },
			{ id: 'leave', name: __( 'Leave Setup', 'erp' ), component: LeaveStep, save: true },
			{ id: 'workday', name: __( 'Work Days', 'erp' ), component: WorkdayStep, save: true },
			// Imports through the employees route, not a step save; skipping saves nothing.
			{ id: 'employees', name: __( 'Add Team', 'erp' ), component: ImportStep, save: false },
			{ id: 'ready', name: __( 'Ready', 'erp' ), component: ReadyStep, save: false },
		],
		[]
	);
}

export function OnboardingPage(): JSX.Element {
	const steps = useSteps();

	const [ current, setCurrent ] = useState( 0 );
	const [ values, setValues ] = useState< OnboardingValues >( DEFAULT_VALUES );
	const [ meta, setMeta ] = useState< OnboardingMeta >( DEFAULT_META );
	const [ completed, setCompleted ] = useState( false );
	const [ loading, setLoading ] = useState( true );
	const [ saving, setSaving ] = useState( false );
	const [ error, setError ] = useState< string | null >( null );

	useEffect( () => {
		let active = true;

		fetchStatus()
			.then( ( status ) => {
				if ( active ) {
					setValues( { ...DEFAULT_VALUES, ...status.values } );
					setMeta( { ...DEFAULT_META, ...( status.meta ?? {} ) } );
					setCompleted( status.completed );
				}
			} )
			.catch( ( raw ) => {
				if ( active ) {
					setError( ( raw as ApiError )?.message ?? __( 'Could not load your settings. Reload the page to try again.', 'erp' ) );
				}
			} )
			.finally( () => {
				if ( active ) {
					setLoading( false );
				}
			} );

		return () => {
			active = false;
		};
	}, [] );

	const step = steps[ current ]!;

	useEffect( () => {
		document.title = `${ step.name } ‹ ${ __( 'WP ERP Setup', 'erp' ) }`;
	}, [ step.name ] );

	// Finish setup the moment "You're All Set!" shows, so closing the tab on
	// that screen still leaves the leave policies created. The server skips
	// policies that already exist, so the button's own call is harmless.
	const finished = useRef( false );
	useEffect( () => {
		if ( 'ready' !== step.id || finished.current ) {
			return;
		}
		finished.current = true;
		completeOnboarding().catch( ( raw ) => {
			finished.current = false;
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not finish setup. Please try again.', 'erp' ) );
		} );
	}, [ step.id ] );

	const isFirst = current === 0;
	const isLast = current === steps.length - 1;

	function advance(): void {
		setError( null );
		setCurrent( ( prev ) => Math.min( prev + 1, steps.length - 1 ) );
		window.scrollTo( { top: 0 } );
	}

	async function handleNext( patch?: Partial< OnboardingValues > ): Promise< void > {
		setError( null );

		if ( step.save && patch ) {
			setSaving( true );
			try {
				const saved = await saveStep( patch );
				setValues( { ...DEFAULT_VALUES, ...saved.values } );
			} catch ( raw ) {
				setError( ( raw as ApiError )?.message ?? __( 'Could not save. Please try again.', 'erp' ) );
				return;
			} finally {
				setSaving( false );
			}
		} else if ( patch ) {
			setValues( ( prev ) => ( { ...prev, ...patch } ) );
		}

		advance();
	}

	async function handleComplete( target?: string ): Promise< void > {
		setSaving( true );
		setError( null );
		try {
			const { redirect_url: redirectUrl } = await completeOnboarding();
			// `redirect_url` carries the default hash route; a link in the final
			// step swaps it to land on the screen it names.
			window.location.href = target ? redirectUrl.replace( /#.*$/, '' ) + target : redirectUrl;
		} catch ( raw ) {
			setSaving( false );
			toast.error( ( raw as ApiError )?.message ?? __( 'Could not finish setup. Please try again.', 'erp' ) );
		}
	}

	if ( loading ) {
		return (
			<div className="flex min-h-screen items-center justify-center bg-background">
				<Spinner className="size-8" />
			</div>
		);
	}

	const StepComponent = step.component;
	const exitUrl = window.__ERP_ONBOARDING__?.exitUrl ?? '';

	return (
		<OnboardingLayout
			onBack={ ! isFirst && ! isLast ? () => setCurrent( ( prev ) => Math.max( prev - 1, 0 ) ) : null }
			onSkip={ step.save ? advance : null }
			// Nothing to persist on exit: each data step already saved on Next,
			// and Settings → Start Onboarding reopens the wizard at its first step.
			onExit={ exitUrl && ! isLast ? () => ( window.location.href = exitUrl ) : null }
		>
			<OnboardingProgress
				currentStep={ current + 1 }
				totalSteps={ steps.length }
				stepName={ step.name }
				error={ error }
			/>

			<div className={ saving ? 'pointer-events-none opacity-60' : '' }>
				<StepComponent
					key={ step.id }
					values={ values }
					meta={ meta }
					saving={ saving }
					completed={ completed }
					onNext={ ( patch ) => void handleNext( patch ) }
					onComplete={ ( target?: string ) => void handleComplete( target ) }
				/>
			</div>
		</OnboardingLayout>
	);
}
