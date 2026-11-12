/**
 * Full-screen wizard chrome: centered WP ERP logo, Back (top-left) and
 * Skip + Close (top-right). Logo block 50px below (`pb-12.5`), logo capped at
 * 150px wide, controls at `top-6` / `left-6` / `right-6`, on the HR tokens.
 */

import { ArrowLeft, ArrowRight, X } from 'lucide-react';
import type { JSX, ReactNode } from 'react';

import { PlainButton } from '@/shared/components/PlainButton';
import { __ } from '@/shared/i18n';

interface OnboardingLayoutProps {
	readonly children: ReactNode;
	readonly onBack?: ( () => void ) | null;
	readonly onSkip?: ( () => void ) | null;
	/** Leave the wizard entirely. Omit to hide the close button. */
	readonly onExit?: ( () => void ) | null;
}

const CONTROL = 'inline-flex items-center gap-2 border-0 bg-transparent p-0 text-base text-foreground transition-opacity hover:opacity-70';

export function OnboardingLayout( { children, onBack, onSkip, onExit }: OnboardingLayoutProps ): JSX.Element {
	const logo = window.__ERP_ONBOARDING__?.logoUrl ?? '';

	return (
		<div className="relative flex min-h-screen flex-col bg-background px-6">
			{ onBack ? (
				<PlainButton onClick={ onBack } aria-label={ __( 'Go back', 'erp' ) } className={ `absolute left-6 top-6 z-10 ${ CONTROL }` }>
					<ArrowLeft size={ 18 } strokeWidth={ 2 } aria-hidden="true" />
				</PlainButton>
			) : null }

			{ /* Skip and Close share the top-right corner, so they live in one row. */ }
			{ onSkip || onExit ? (
				<div className="absolute right-6 top-6 z-10 flex items-center gap-5">
					{ onSkip ? (
						<PlainButton onClick={ onSkip } className={ CONTROL }>
							{ __( 'Skip This Step', 'erp' ) }
							<ArrowRight size={ 18 } strokeWidth={ 2 } aria-hidden="true" />
						</PlainButton>
					) : null }

					{ onExit ? (
						<PlainButton
							onClick={ onExit }
							aria-label={ __( 'Exit setup', 'erp' ) }
							title={ __( 'Exit setup', 'erp' ) }
							className={ CONTROL }
						>
							<X size={ 20 } strokeWidth={ 2 } aria-hidden="true" />
						</PlainButton>
					) : null }
				</div>
			) : null }

			<div className="flex w-full flex-1 flex-col items-center justify-center py-16">
				<div className="pb-12.5 text-center">
					{ logo ? (
						<img src={ logo } alt="WP ERP" className="mx-auto h-9 w-auto max-w-[150px]" />
					) : (
						<span className="text-2xl font-bold text-foreground">WP ERP</span>
					) }
				</div>

				<div className="w-full">{ children }</div>
			</div>
		</div>
	);
}
