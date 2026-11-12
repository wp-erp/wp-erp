/**
 * Onboarding wizard types.
 */

export type Weekday = 'mon' | 'tue' | 'wed' | 'thu' | 'fri' | 'sat' | 'sun';

export interface WorkingHours {
	readonly start: string;
	readonly end: string;
}

export interface LeaveYear {
	/** Saved leave year id; absent for a new row. */
	readonly id?: number;
	readonly fy_name: string;
	readonly start_date: string;
	readonly end_date: string;
}

export interface LeaveTypeOption {
	readonly id: string;
	readonly name: string;
}

export interface OnboardingValues {
	readonly companyName: string;
	readonly companyStartDate: string;
	/** Lower-case month name, e.g. `january`. */
	readonly financialYearStarts: string;
	/** WP ERP currency id (`erp_settings_general.erp_currency`), e.g. `148` for USD. */
	readonly currency: string;
	/** PHP date format, one of `meta.dateFormats`. */
	readonly dateFormat: string;
	/** WP ERP business type key, or '' for none. */
	readonly businessType: string;
	/** Appsero "Share Essentials" consent. */
	readonly shareEssentials: boolean;
	readonly departments: readonly string[];
	readonly designations: readonly string[];
	readonly leaveYears: readonly LeaveYear[];
	readonly selectedLeaveTypes: readonly string[];
	readonly workingDays: Readonly< Record< Weekday, string > >;
	readonly workingHours: WorkingHours;
}

/** A `{value, label}` choice the server offers. */
export interface ChoiceOption {
	readonly value: string;
	readonly label: string;
}

export interface OnboardingMeta {
	readonly leaveTypes: readonly LeaveTypeOption[];
	readonly currencies: readonly ChoiceOption[];
	readonly dateFormats: readonly ChoiceOption[];
	readonly businessTypes: readonly ChoiceOption[];
}

export interface OnboardingStatus {
	readonly completed: boolean;
	readonly values: OnboardingValues;
	readonly meta?: OnboardingMeta;
}

/** Props every step component receives from the orchestrator. */
export interface StepProps {
	readonly values: OnboardingValues;
	readonly meta: OnboardingMeta;
	readonly saving: boolean;
	/** Whether setup was finished before (the Introduction says so). */
	readonly completed: boolean;
	/** Advance to the next step, optionally persisting a slice of the form. */
	readonly onNext: ( patch?: Partial< OnboardingValues > ) => void;
	/**
	 * Finish setup. An optional hash route overrides where the admin lands
	 * afterwards, so a link inside the final step can open the screen it names.
	 */
	readonly onComplete: ( target?: string ) => void;
}

/** Server data the setup page prints before the bundle. */
export interface OnboardingBoot {
	readonly logoUrl: string;
	readonly exitUrl: string;
}

declare global {
	interface Window {
		__ERP_ONBOARDING__?: OnboardingBoot;
	}
}
