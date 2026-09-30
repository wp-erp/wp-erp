/**
 * A team pill in the org-chart top filter bar. Presentational toggle button.
 */

import type { JSX } from 'react';

import { SegmentedTab } from '@/shared/components/SegmentedTab';

/** A team pill in the top filter bar. */
export function DeptPill( { label, active, onClick }: { readonly label: string; readonly active: boolean; readonly onClick: () => void } ): JSX.Element {
	return (
		<SegmentedTab
			active={ active }
			onClick={ onClick }
			aria-pressed={ active }
			className="max-w-56 truncate"
		>
			{ label }
		</SegmentedTab>
	);
}
