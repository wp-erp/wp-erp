/**
 * Hero strip — gradient title block + 4 KPI cards (bento layout).
 *
 * Stats sourced from the shared `/erp/v2/employees/counts` endpoint so the
 * numbers match the StatusFilter on the legacy view.
 */

import { useDispatch, useSelect } from '@wordpress/data';
import { Briefcase, Plus, UserMinus, UserRound, Users } from 'lucide-react';
import type { ComponentType, JSX, SVGProps } from 'react';

import { useCan } from '@/shared/hooks/useCan';
import { __ } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import type {
	EmployeeListQuery,
	EmployeesState,
} from '@/stores/employees';

import { usePeopleReviewStats } from './usePeopleReviewStats';

type LucideIcon = ComponentType< SVGProps< SVGSVGElement > & { size?: number; strokeWidth?: number } >;

interface EmployeesStoreSelectors {
	getFilters: () => EmployeeListQuery;
}

interface EmployeesStoreDispatch {
	setFilters:    ( filters: EmployeeListQuery ) => void;
	setPagination: ( pagination: EmployeesState[ 'pagination' ] ) => void;
}

interface KpiCard {
	readonly id:        string;
	readonly label:     string;
	readonly value:     number | null;
	readonly icon:      LucideIcon;
	readonly accent:    string;
	readonly statusKey: 'all' | 'active' | 'inactive' | 'terminated';
}

export function PeopleReviewHero(): JSX.Element {
	const stats     = usePeopleReviewStats();
	const canCreate = useCan( 'erp_create_employee' );
	const filters   = useSelect(
		( select ) => ( select( employeesStoreName ) as unknown as EmployeesStoreSelectors ).getFilters(),
		[]
	);
	const { setFilters, setPagination } = useDispatch(
		employeesStoreName
	) as unknown as EmployeesStoreDispatch;

	const cards: readonly KpiCard[] = [
		{
			id:        'total',
			label:     __( 'Total people', 'erp' ),
			value:     stats.total,
			icon:      Users,
			accent:    'from-indigo-500/15 to-indigo-500/0 text-indigo-600',
			statusKey: 'all',
		},
		{
			id:        'active',
			label:     __( 'Active', 'erp' ),
			value:     stats.active,
			icon:      UserRound,
			accent:    'from-emerald-500/15 to-emerald-500/0 text-emerald-600',
			statusKey: 'active',
		},
		{
			id:        'inactive',
			label:     __( 'Inactive', 'erp' ),
			value:     stats.inactive,
			icon:      Briefcase,
			accent:    'from-amber-500/15 to-amber-500/0 text-amber-600',
			statusKey: 'inactive',
		},
		{
			id:        'terminated',
			label:     __( 'Terminated', 'erp' ),
			value:     stats.terminated,
			icon:      UserMinus,
			accent:    'from-rose-500/15 to-rose-500/0 text-rose-600',
			statusKey: 'terminated',
		},
	];

	const activeStatus = filters.status ?? 'all';

	return (
		<header className="relative mb-6 overflow-hidden rounded-3xl border border-white/40 bg-white/45 p-6 shadow-[0_8px_32px_-12px_rgba(15,23,42,0.22)] ring-1 ring-white/60 backdrop-blur-2xl backdrop-saturate-150">
			<span
				aria-hidden="true"
				className="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/80 to-transparent"
			/>
			<div className="relative flex flex-wrap items-start justify-between gap-4">
				<div className="min-w-0">
					<p className="text-xs font-medium uppercase tracking-wider text-primary/80">
						{ __( 'People Review', 'erp' ) }
					</p>
					<h1 className="mt-1 bg-gradient-to-br from-slate-900 via-indigo-700 to-slate-900 bg-clip-text text-3xl font-bold leading-9 tracking-tight text-transparent">
						{ __( 'Your workforce at a glance', 'erp' ) }
					</h1>
					<p className="mt-2 max-w-xl text-sm text-muted-foreground">
						{ __(
							'A modern, comparable view of the People page — same data, refreshed layout for design review.',
							'erp'
						) }
					</p>
				</div>
				{ canCreate ? (
					<button
						type="button"
						onClick={ () => { window.location.hash = '#/employees/new'; } }
						className="group/btn relative inline-flex h-10 shrink-0 items-center gap-2 overflow-hidden rounded-full border border-white/30 bg-primary/90 px-5 text-sm font-medium text-primary-foreground shadow-lg ring-1 ring-white/40 backdrop-blur transition-transform hover:-translate-y-0.5"
					>
						<span
							aria-hidden="true"
							className="pointer-events-none absolute inset-x-0 top-0 h-1/2 bg-gradient-to-b from-white/30 to-transparent"
						/>
						<Plus size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
						<span className="relative">{ __( 'Add person', 'erp' ) }</span>
					</button>
				) : null }
			</div>

			<div className="relative mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
				{ cards.map( ( card ) => {
					const Icon = card.icon;
					const isActive = activeStatus === card.statusKey;
					const iconColor = card.accent.split( ' ' ).pop() ?? '';
					return (
						<button
							key={ card.id }
							type="button"
							onClick={ () => {
								setFilters( { ...filters, status: card.statusKey } );
								setPagination( { page: 1, perPage: 20 } );
							} }
							aria-pressed={ isActive }
							className={ [
								'group relative flex flex-col items-start gap-3 overflow-hidden rounded-2xl border bg-white/55 p-4 text-left shadow-[0_4px_18px_-8px_rgba(15,23,42,0.18)] ring-1 backdrop-blur-xl backdrop-saturate-150 transition-all',
								isActive
									? 'border-primary/60 ring-primary/30'
									: 'border-white/50 ring-white/40 hover:-translate-y-0.5 hover:border-white/70',
							].join( ' ' ) }
						>
							<span
								aria-hidden="true"
								className={ `pointer-events-none absolute inset-x-0 top-0 h-20 bg-gradient-to-b ${ card.accent } opacity-80` }
							/>
							<span
								aria-hidden="true"
								className="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/90 to-transparent"
							/>
							<span
								className={ `relative inline-flex size-9 items-center justify-center rounded-xl border border-white/50 bg-white/70 shadow-sm ring-1 ring-white/40 backdrop-blur ${ iconColor }` }
							>
								<Icon size={ 18 } strokeWidth={ 1.75 } aria-hidden="true" />
							</span>
							<div className="relative">
								<p className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
									{ card.label }
								</p>
								<p className="mt-1 text-2xl font-bold leading-7 text-foreground tabular-nums">
									{ card.value === null ? '—' : card.value.toLocaleString() }
								</p>
							</div>
						</button>
					);
				} ) }
			</div>
		</header>
	);
}
