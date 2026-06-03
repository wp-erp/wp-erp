/**
 * Reports sub-navigation — line/underline tabs, same treatment as the employee
 * profile page (`EmployeeSinglePage` ProfileTab). Each tab is a route link; the
 * active tab is derived from the current path so deep-linking a report works.
 */

import type { JSX } from 'react';
import { NavLink } from 'react-router-dom';

import { __ } from '@/shared/i18n';

interface ReportTab {
	readonly to:    string;
	readonly label: string;
}

const REPORT_TABS: readonly ReportTab[] = [
	{ to: '/reports/age-profile', label: __( 'Age Profile', 'erp' ) },
	{ to: '/reports/gender-profile', label: __( 'Gender Profile', 'erp' ) },
	{ to: '/reports/headcount', label: __( 'Head Count', 'erp' ) },
	{ to: '/reports/salary-history', label: __( 'Salary History', 'erp' ) },
	{ to: '/reports/years-of-service', label: __( 'Years of Service', 'erp' ) },
	{ to: '/reports/leaves', label: __( 'Leaves', 'erp' ) },
];

export function ReportsTabs(): JSX.Element {
	return (
		<nav
			aria-label={ __( 'Reports', 'erp' ) }
			className="flex w-full justify-start gap-1 overflow-x-auto border-b border-border pb-0.5 scrollbar-none"
		>
			{ REPORT_TABS.map( ( tab ) => (
				<NavLink
					key={ tab.to }
					to={ tab.to }
					className={ ( { isActive } ) =>
						[
							'group relative shrink-0 flex-none rounded-none px-3 pb-2.5 pt-1 text-sm font-medium transition-colors',
							isActive ? 'text-primary' : 'text-muted-foreground hover:text-foreground',
						].join( ' ' )
					}
				>
					{ ( { isActive } ) => (
						<>
							{ tab.label }
							<span
								aria-hidden="true"
								className={ [
									'pointer-events-none absolute inset-x-0 bottom-0 h-0.5 bg-primary transition-opacity',
									isActive ? 'opacity-100' : 'opacity-0',
								].join( ' ' ) }
							/>
						</>
					) }
				</NavLink>
			) ) }
		</nav>
	);
}
