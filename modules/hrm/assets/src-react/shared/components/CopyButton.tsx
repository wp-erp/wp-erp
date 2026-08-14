/**
 * Tiny "copy to clipboard" icon button. Copies the given text, flips to a check
 * for ~1.5s, and toasts. Used inline next to copyable values (employee ID,
 * email). Renders a <span> button so it is safe inside links/cells.
 */

import { Button, toast } from '@wedevs/plugin-ui';
import { Check, Copy } from 'lucide-react';
import { useState } from 'react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

interface CopyButtonProps {
	readonly value: string;
	/** Accessible label, e.g. "Copy employee ID". */
	readonly label: string;
	readonly size?: number;
}

export function CopyButton( { value, label, size = 13 }: CopyButtonProps ): JSX.Element {
	const [ copied, setCopied ] = useState( false );

	function handleCopy( e: React.MouseEvent ): void {
		e.preventDefault();
		e.stopPropagation();
		const done = (): void => {
			setCopied( true );
			toast.success( __( 'Copied to clipboard.', 'erp' ) );
			window.setTimeout( () => setCopied( false ), 1500 );
		};
		if ( navigator.clipboard?.writeText ) {
			void navigator.clipboard.writeText( value ).then( done ).catch( () => toast.error( __( 'Could not copy.', 'erp' ) ) );
		}
	}

	return (
		<Button
			type="button"
			variant="ghost"
			size="icon"
			onClick={ handleCopy }
			aria-label={ label }
			title={ label }
			className="size-6 shrink-0 text-muted-foreground opacity-0 transition-all focus:opacity-100 group-hover/cell:opacity-100"
		>
			{ copied ? <Check size={ size } className="text-success" aria-hidden="true" /> : <Copy size={ size } aria-hidden="true" /> }
		</Button>
	);
}
