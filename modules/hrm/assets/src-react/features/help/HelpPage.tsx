/**
 * `/help` — HR help & resources landing page.
 *
 * Static, read-only. Surfaces the same kind of guidance the legacy "Help" menu
 * linked out to (documentation, support, community), grouped into cards that
 * follow the profile design system (`rounded-[10px] bg-card p-6 shadow-sm`).
 */

import { BookOpen, LifeBuoy, MessageCircleQuestion, Users } from 'lucide-react';
import type { ComponentType, JSX, SVGProps } from 'react';

import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { __ } from '@/shared/i18n';

type LucideIcon = ComponentType< SVGProps< SVGSVGElement > & { size?: number; strokeWidth?: number } >;

interface Resource {
	readonly icon:  LucideIcon;
	readonly title: string;
	readonly desc:  string;
	readonly href:  string;
	readonly cta:   string;
}

const RESOURCES: readonly Resource[] = [
	{
		icon:  BookOpen,
		title: __( 'Documentation', 'erp' ),
		desc:  __( 'Step-by-step guides for every HR module — employees, leave, holidays, reports and more.', 'erp' ),
		href:  'https://wperp.com/docs/',
		cta:   __( 'Browse docs', 'erp' ),
	},
	{
		icon:  LifeBuoy,
		title: __( 'Support', 'erp' ),
		desc:  __( 'Stuck on something? Open a ticket with the weDevs support team.', 'erp' ),
		href:  'https://wperp.com/support/',
		cta:   __( 'Get support', 'erp' ),
	},
	{
		icon:  Users,
		title: __( 'Community', 'erp' ),
		desc:  __( 'Ask questions and share ideas with other WP ERP users in the community forum.', 'erp' ),
		href:  'https://www.facebook.com/groups/wperp/',
		cta:   __( 'Join the community', 'erp' ),
	},
	{
		icon:  MessageCircleQuestion,
		title: __( 'Feature requests', 'erp' ),
		desc:  __( 'Have an idea to make WP ERP better? Let the team know what you’d like to see.', 'erp' ),
		href:  'https://wperp.com/contact/',
		cta:   __( 'Send feedback', 'erp' ),
	},
];

function ResourceCard( { icon: Icon, title, desc, href, cta }: Resource ): JSX.Element {
	return (
		<section className="flex flex-col rounded-[10px] bg-card p-6 shadow-sm">
			<span className="mb-4 inline-flex size-11 items-center justify-center rounded-lg bg-primary/10 text-primary">
				<Icon size={ 20 } strokeWidth={ 1.9 } aria-hidden="true" />
			</span>
			<h2 className="text-base font-semibold text-foreground">{ title }</h2>
			<p className="mt-1 flex-1 text-sm leading-relaxed text-muted-foreground">{ desc }</p>
			<a
				href={ href }
				target="_blank"
				rel="noopener noreferrer"
				className="mt-4 inline-flex h-9 w-fit items-center gap-1.5 rounded-md border border-border bg-card px-3 text-sm font-medium text-foreground transition-colors hover:bg-muted"
			>
				{ cta }
			</a>
		</section>
	);
}

export function HelpPage(): JSX.Element {
	return (
		<ErrorBoundary>
			<div className="mx-auto w-full max-w-7xl space-y-6">
				<header>
					<h1 className="text-2xl font-bold leading-tight tracking-tight text-foreground">
						{ __( 'Help & Resources', 'erp' ) }
					</h1>
					<p className="mt-1 text-sm text-muted-foreground">
						{ __( 'Guides, support and community links for WP ERP HR.', 'erp' ) }
					</p>
				</header>

				<div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
					{ RESOURCES.map( ( r ) => <ResourceCard key={ r.title } { ...r } /> ) }
				</div>
			</div>
		</ErrorBoundary>
	);
}
