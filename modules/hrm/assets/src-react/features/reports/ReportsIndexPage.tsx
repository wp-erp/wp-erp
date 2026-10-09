/**
 * `/reports` landing page (legacy HR Frontend `Reports.jsx`): one card per
 * report this user can open (title, description, View report).
 *
 * The cards come from the Reports nav entry through the shared nav runtime, so
 * they follow the same capability and pro-module gates as the menu: a report
 * whose pro module is off does not appear, and without Pro a pro report shows
 * as a "Pro" card that opens the upgrade dialog.
 */

import { BarChart3 } from 'lucide-react';
import type { JSX } from 'react';
import { Link } from 'react-router-dom';

import { EmptyState } from '@/shared/components/EmptyState';
import { useNavMenu } from '@/shared/components/nav/nav-runtime';
import { ProBadge } from '@/shared/components/pro/ProUpsell';
import { __ } from '@/shared/i18n';

const CARD = 'flex h-full flex-col gap-2 rounded-[10px] bg-card p-5 text-left shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md';

export function ReportsIndexPage(): JSX.Element {
	const { entries, childrenOf, openUpsell } = useNavMenu();
	const reports = entries.find( ( entry ) => entry.item.id === 'reports' );
	const cards   = reports ? childrenOf( reports.item ) : [];

	return (
		<section className="mx-auto w-full max-w-full">
			<header className="mb-6">
				<h1 className="text-2xl font-bold leading-8 text-foreground">{ __( 'Reports', 'erp' ) }</h1>
			</header>

			{ cards.length === 0 ? (
				<div className="rounded-[10px] bg-card shadow-sm">
					<EmptyState icon={ BarChart3 } title={ __( 'No reports available.', 'erp' ) } />
				</div>
			) : (
				<ul className="m-0 grid list-none grid-cols-1 gap-4 p-0 sm:grid-cols-2 lg:grid-cols-3">
					{ cards.map( ( { sub, proLocked } ) => (
						<li key={ sub.id }>
							{ proLocked ? (
								<button type="button" className={ `${ CARD } w-full` } onClick={ () => openUpsell( sub.label ) }>
									<span className="flex items-center gap-2 text-base font-semibold text-foreground">
										{ sub.label }
										<ProBadge />
									</span>
									{ sub.description ? <span className="text-sm text-muted-foreground">{ sub.description }</span> : null }
									<span className="mt-auto pt-2 text-sm font-medium text-primary">{ __( 'Upgrade to view', 'erp' ) }</span>
								</button>
							) : (
								<Link to={ sub.to } className={ `${ CARD } no-underline` }>
									<span className="text-base font-semibold text-foreground">{ sub.label }</span>
									{ sub.description ? <span className="text-sm text-muted-foreground">{ sub.description }</span> : null }
									<span className="mt-auto pt-2 text-sm font-medium text-primary">{ __( 'View report', 'erp' ) }</span>
								</Link>
							) }
						</li>
					) ) }
				</ul>
			) }
		</section>
	);
}
