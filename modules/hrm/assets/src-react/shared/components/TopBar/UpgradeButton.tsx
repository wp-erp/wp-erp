/**
 * "Upgrade" upsell button — shown in the right cluster only when the ERP Pro
 * plugin is ABSENT (`!boot.isPro`). Opens the same Pro upsell dialog the nav
 * "Pro" badges use, so the prompt is present in both the top-bar and the
 * sidebar-layout top strip (RightCluster renders in both).
 *
 * When Pro IS installed it injects its own What's New / Support / Upgrade items
 * via `erp_hr.topbar.right_items`, so this free button stands down.
 */

import { Crown } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import { useProUpsell } from '@/shared/components/pro/ProUpsell';

export function UpgradeButton(): JSX.Element {
	const { openUpsell } = useProUpsell();

	return (
		<button
			type="button"
			onClick={ () => openUpsell( 'Pro' ) }
			className="inline-flex h-9 items-center gap-1.5 rounded-md bg-[#f7941d] px-4 text-sm font-semibold text-[#1a1a1a] transition-colors hover:bg-[#e8870f]"
		>
			{ __( 'Upgrade', 'erp' ) }
			<Crown size={ 15 } strokeWidth={ 2 } aria-hidden="true" />
		</button>
	);
}
