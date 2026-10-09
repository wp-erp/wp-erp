/**
 * Top bar logo cluster — briefcase 24×24 + "HR" + ChevronDown.
 *
 * Figma node `502:23167` (68×24).
 */

import { Briefcase } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

export function Logo(): JSX.Element {
	return (
		<div
			className="flex shrink-0 items-center gap-2 text-foreground"
			aria-label={ __( 'WP-ERP HR', 'erp' ) }
		>
			<Briefcase size={ 18 } strokeWidth={ 1.75 } aria-hidden="true" />
			<span className="text-sm font-semibold leading-none">HR</span>
		</div>
	);
}
