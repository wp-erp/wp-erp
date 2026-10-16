/**
 * Inline "where do these come from?" affordance beside a dependency select's
 * label.
 *
 * Sibling of `QuickAddButton`: use that one when the prerequisite can be created
 * inline (a department, a leave type); use this one when it can only be created
 * on another screen — an in-app setup route or a legacy wp-admin page (work
 * locations still live on ERP → Company). Pair it with the `labelAction` slot on
 * `SmartSelectField`/`FieldShell`.
 *
 * Call sites use `<FieldSourceAction source="locations" />`: the registry below
 * owns the target and its capability gate, so every form links to the same place
 * and hides the link from users who cannot reach that screen.
 */

import { ArrowUpRight, ExternalLink } from 'lucide-react';
import type { JSX } from 'react';
import { Link } from 'react-router-dom';

import { useCan } from '@/shared/hooks/useCan';
import { __ } from '@/shared/i18n';
import type { Capability } from '@/types/global';

interface FieldSourceLinkProps {
	readonly label: string;
	/** In-app route, e.g. `/leave/policies`. */
	readonly to?: string | undefined;
	/** Legacy wp-admin page slug, e.g. `erp-company` — opens in a new tab. */
	readonly page?: string | undefined;
}

const CLASSES =
	'inline-flex shrink-0 items-center gap-1 text-xs font-medium text-primary underline-offset-2 hover:underline';

/**
 * Absolute URL of a legacy wp-admin page. The React shell always runs from
 * `wp-admin/admin.php`, so the current pathname is the right base — no boot
 * value needed.
 */
function legacyPageUrl( page: string ): string {
	return `${ window.location.pathname }?page=${ encodeURIComponent( page ) }`;
}

export function FieldSourceLink( { label, to, page }: FieldSourceLinkProps ): JSX.Element | null {
	if ( to ) {
		return (
			<Link to={ to } className={ CLASSES }>
				{ label }
				<ArrowUpRight size={ 13 } aria-hidden="true" />
			</Link>
		);
	}

	if ( page ) {
		return (
			<a
				href={ legacyPageUrl( page ) }
				target="_blank"
				rel="noreferrer"
				className={ CLASSES }
			>
				{ label }
				<ExternalLink size={ 12 } aria-hidden="true" />
			</a>
		);
	}

	return null;
}

interface FieldSource {
	readonly label: string;
	readonly cap:   Capability;
	readonly to?:   string;
	readonly page?: string;
}

/**
 * Where each lookup is maintained, plus the gate of that screen — the route
 * capabilities in `app/router.tsx`, or `manage_options` for the legacy Company
 * page that owns work locations.
 */
const FIELD_SOURCES: Record< string, FieldSource > = {
	locations:      { label: __( 'Add location', 'erp' ),      cap: 'manage_options',  page: 'erp-company' },
	departments:    { label: __( 'Add department', 'erp' ),    cap: 'erp_view_list',   to: '/departments' },
	designations:   { label: __( 'Add designation', 'erp' ),   cap: 'erp_view_list',   to: '/designations' },
	leaveTypes:     { label: __( 'Add leave type', 'erp' ),    cap: 'erp_leave_manage', to: '/leave/types' },
	leavePolicies:  { label: __( 'Add policy', 'erp' ),        cap: 'erp_leave_manage', to: '/leave/policies' },
	financialYears: { label: __( 'Manage years', 'erp' ),      cap: 'erp_hr_manager',  to: '/leave/financial-years' },
};

export type FieldSourceKey = keyof typeof FIELD_SOURCES;

interface FieldSourceActionProps {
	readonly source: FieldSourceKey;
}

/**
 * Capability-gated `FieldSourceLink` for a known lookup. Renders nothing when
 * the user cannot open the screen that owns the data.
 */
export function FieldSourceAction( { source }: FieldSourceActionProps ): JSX.Element | null {
	const entry  = FIELD_SOURCES[ source ];
	const canSee = useCan( entry ? entry.cap : 'erp_view_list' );

	if ( ! entry || ! canSee ) {
		return null;
	}

	return <FieldSourceLink label={ entry.label } to={ entry.to } page={ entry.page } />;
}
