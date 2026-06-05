/**
 * Employee-profile tab extension point.
 *
 * Additive seam over the hardcoded profile tabs: free seeds a `documents`
 * preview tab and applies the `erp_hr.employee.profile.tabs` filter LAZILY at
 * render (in an effect, like the `extra_fields` bridge) so a pro bundle that
 * loads after the free app can still register before the tabs paint. The pro
 * Document Manager module replaces the `documents` tab's `render` with the real
 * file manager when active; when it is absent, the free preview/upsell shows.
 *
 * Purely additive — the built-in tabs (Personal/Job/Leave/Notes/Performance/
 * Permission) keep rendering exactly as before; these are EXTRA tabs appended
 * after them.
 */

import { useSelect } from '@wordpress/data';
import { applyFilters } from '@wordpress/hooks';
import { FileText } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { ComponentType, JSX, ReactNode, SVGProps } from 'react';

import { HOOKS } from '@/shared/filters';
import { __ } from '@/shared/i18n';
import { storeName as meStoreName } from '@/stores/me';
import type { Capability } from '@/types/global';

/** Icon shape compatible with the profile NavMenu (`NavItem.icon`). */
export type ProfileTabIcon = ComponentType<
	SVGProps< SVGSVGElement > & { size?: number; strokeWidth?: number }
>;

/** Context handed to every profile tab's `render`. */
export interface ProfileTabContext {
	readonly userId:  number;
	readonly canEdit: boolean;
}

/** A profile tab definition — built-in or pro-injected. */
export interface ProfileTab {
	readonly id:            string;
	readonly label:         string;
	readonly icon:          ProfileTabIcon;
	/** Lower sorts earlier. Built-ins occupy < 50; defaults to 100. */
	readonly order?:        number;
	/** Optional caps required to see the tab. */
	readonly capabilities?: readonly Capability[];
	readonly render:        ( ctx: ProfileTabContext ) => ReactNode;
}

/**
 * Free preview shown when the Document Manager pro module is not active. Built
 * from the same tokens as the rest of the profile — no new visual language.
 */
function DocumentsTabPreview(): JSX.Element {
	return (
		<section className="rounded-2xl bg-card p-10 text-center shadow-sm ring-1 ring-border/60">
			<div className="mx-auto flex size-14 items-center justify-center rounded-full bg-muted text-muted-foreground">
				<FileText size={ 26 } strokeWidth={ 1.75 } aria-hidden="true" />
			</div>
			<h2 className="mx-auto mt-4 max-w-md text-xl font-bold tracking-tight text-foreground">
				{ __( 'Employee Documents', 'erp' ) }
			</h2>
			<p className="mx-auto mt-2 max-w-md text-sm text-muted-foreground">
				{ __(
					'Organise contracts, ID files and certificates per employee in folders, share them, and sync with Dropbox. Available in WP ERP Pro — Document Manager.',
					'erp'
				) }
			</p>
			<div className="mt-5">
				<a
					href="https://wperp.com/downloads/document-manager/"
					target="_blank"
					rel="noreferrer"
					className="inline-flex h-9 items-center rounded-md border border-border bg-background px-4 text-sm font-medium text-foreground transition-colors hover:bg-muted"
				>
					{ __( 'Learn more', 'erp' ) }
				</a>
			</div>
		</section>
	);
}

/** The seeded documents tab — pro swaps its `render` via the filter. */
const PREVIEW_DOCUMENTS_TAB: ProfileTab = {
	id:     'documents',
	label:  __( 'Documents', 'erp' ),
	icon:   FileText,
	order:  50,
	render: () => <DocumentsTabPreview />,
};

interface MeStoreSelectors {
	hasCap: ( capability: Capability | readonly Capability[] ) => boolean;
}

/**
 * Resolve the extra (pro-injectable) profile tabs for an employee. Seeds the
 * documents preview, applies the filter lazily, gates by capabilities, sorts.
 */
export function useProfileExtraTabs( ctx: ProfileTabContext ): ProfileTab[] {
	const hasCap = useSelect(
		( select ) => ( select( meStoreName ) as unknown as MeStoreSelectors ).hasCap,
		[]
	);

	const [ tabs, setTabs ] = useState< ProfileTab[] >( [] );

	useEffect( () => {
		const resolved = applyFilters(
			HOOKS.EMPLOYEE_PROFILE_TABS,
			[ PREVIEW_DOCUMENTS_TAB ],
			ctx
		) as ProfileTab[];
		setTabs( Array.isArray( resolved ) ? resolved : [ PREVIEW_DOCUMENTS_TAB ] );
		// ctx is rebuilt each render; depend on its primitive fields only.
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ ctx.userId, ctx.canEdit ] );

	return tabs
		.filter( ( t ) => ! t.capabilities?.length || hasCap( t.capabilities ) )
		.slice()
		.sort( ( a, b ) => ( a.order ?? 100 ) - ( b.order ?? 100 ) );
}
