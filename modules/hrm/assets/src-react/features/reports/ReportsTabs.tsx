/**
 * Reports sub-navigation — segmented pill control, matching the employee
 * profile tabs (`EmployeeSinglePage`): a muted track with a white active chip
 * (icon + label). Each tab is a route link; the active tab derives from the
 * current path so deep-linking a report works.
 */

import { Award, BarChart3, CalendarClock, CalendarDays, DollarSign, Users } from 'lucide-react';
import type { ComponentType, JSX, SVGProps } from 'react';
import { NavLink } from 'react-router-dom';

import { __ } from '@/shared/i18n';

type LucideIcon = ComponentType< SVGProps< SVGSVGElement > & { size?: number; strokeWidth?: number } >;

interface ReportTab {
	readonly to:    string;
	readonly label: string;
	readonly icon:  LucideIcon;
}

const REPORT_TABS: readonly ReportTab[] = [
	{ to: '/reports/age-profile', label: __( 'Age Profile', 'erp' ), icon: CalendarDays },
	{ to: '/reports/gender-profile', label: __( 'Gender Profile', 'erp' ), icon: Users },
	{ to: '/reports/headcount', label: __( 'Head Count', 'erp' ), icon: BarChart3 },
	{ to: '/reports/salary-history', label: __( 'Salary History', 'erp' ), icon: DollarSign },
	{ to: '/reports/years-of-service', label: __( 'Years of Service', 'erp' ), icon: Award },
	{ to: '/reports/leaves', label: __( 'Leaves', 'erp' ), icon: CalendarClock },
];

export function ReportsTabs(): JSX.Element {
	return (
		<nav
			aria-label={ __( 'Reports', 'erp' ) }
			className="inline-flex w-fit max-w-full items-center gap-1 overflow-x-auto rounded-lg border border-border bg-muted/60 p-1 scrollbar-none"
		>
			{ REPORT_TABS.map( ( tab ) => {
				const Icon = tab.icon;
				return (
					<NavLink
						key={ tab.to }
						to={ tab.to }
						viewTransition
						className={ ( { isActive } ) =>
							[
								'inline-flex shrink-0 flex-none items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium ring-1 ring-transparent transition-all',
								isActive
									? 'bg-card text-primary shadow-sm ring-primary/40'
									: 'text-muted-foreground hover:text-foreground',
							].join( ' ' )
						}
					>
						<Icon size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
						{ tab.label }
					</NavLink>
				);
			} ) }
		</nav>
	);
}
