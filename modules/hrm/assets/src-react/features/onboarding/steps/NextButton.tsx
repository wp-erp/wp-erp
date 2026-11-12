/**
 * The centered primary submit button shared by steps.
 *
 * Spacing in Tailwind v4: the button block sits 100px below the form
 * (`mt-25`), and the button itself is
 * `inline-flex items-center gap-2 px-8 py-3 font-semibold` at 15px →
 * `gap-2 px-8 py-3 text-[15px] font-semibold`. Color/radius come from the
 * design-system Button component.
 */

import { Button, Spinner } from '@wedevs/plugin-ui';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

interface NextButtonProps {
	readonly saving: boolean;
	readonly label?: string;
}

export function NextButton( { saving, label }: NextButtonProps ): JSX.Element {
	return (
		<div className="mt-25 text-center">
			{ /* `h-auto` so the `py-3` above actually applies: the Button size variants
			   set a fixed height (h-11) that would otherwise swallow it. */ }
			<Button
				type="submit"
				disabled={ saving }
				className="inline-flex h-auto items-center gap-2 px-8 py-3 text-[15px] font-semibold"
			>
				{ saving ? <Spinner className="size-4" /> : null }
				{ saving ? __( 'Saving…', 'erp' ) : label ?? __( 'Next', 'erp' ) }
			</Button>
		</div>
	);
}
