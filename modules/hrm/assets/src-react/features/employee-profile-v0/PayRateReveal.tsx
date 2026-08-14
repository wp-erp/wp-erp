/**
 * Pay-rate value with optional privacy blur.
 *
 * Honors the global `erp_hrm_hide_pay_rate` option (surfaced on the boot payload
 * as `settings.hidePayRate`). When enabled, the pay rate renders blurred with an
 * eye toggle to reveal it — mirroring the legacy `tab-job.php` compensation
 * blur/reveal behavior. When disabled, the value renders plainly.
 */

import { Button } from '@wedevs/plugin-ui';
import { Eye, EyeOff } from 'lucide-react';
import { useState } from 'react';
import type { JSX } from 'react';

import { useBoot } from '@/shared/hooks/useBoot';
import { __ } from '@/shared/i18n';

interface PayRateRevealProps {
	readonly value: string;
	/** Extra classes for the value span. */
	readonly className?: string;
}

export function PayRateReveal( { value, className }: PayRateRevealProps ): JSX.Element {
	const boot   = useBoot();
	const hide   = Boolean( boot.settings?.hidePayRate );
	const [ revealed, setRevealed ] = useState( false );

	const display = value.trim() === '' ? '—' : value;

	if ( ! hide ) {
		return <span className={ className }>{ display }</span>;
	}

	return (
		<span className="inline-flex items-center gap-1.5">
			<Button
				type="button"
				variant="ghost"
				size="icon-sm"
				className="text-muted-foreground hover:text-foreground"
				aria-label={ revealed ? __( 'Hide pay rate', 'erp' ) : __( 'Show pay rate', 'erp' ) }
				aria-pressed={ revealed }
				onClick={ () => setRevealed( ( prev ) => ! prev ) }
			>
				{ revealed ? <EyeOff size={ 14 } aria-hidden="true" /> : <Eye size={ 14 } aria-hidden="true" /> }
			</Button>
			<span className={ [ className ?? '', revealed ? '' : 'select-none blur-[6px]' ].join( ' ' ).trim() }>
				{ display }
			</span>
		</span>
	);
}
