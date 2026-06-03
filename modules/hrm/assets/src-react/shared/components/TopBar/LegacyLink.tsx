/**
 * "View previous version" link. Wraps plugin-ui's Button via `render` slot.
 */

import { Button } from '@wedevs/plugin-ui';
import { History } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import { useBoot } from '@/shared/hooks/useBoot';

export function LegacyLink(): JSX.Element | null {
	const boot = useBoot();

	if ( ! boot.switchUrl ) {
		return null;
	}

	return (
		<Button
			variant="ghost"
			size="sm"
			render={ <a href={ boot.switchUrl } /> }
			className="gap-1.5 text-muted-foreground"
		>
			<History size={ 14 } strokeWidth={ 1.75 } aria-hidden="true" />
			{ __( 'View previous version', 'erp' ) }
		</Button>
	);
}
