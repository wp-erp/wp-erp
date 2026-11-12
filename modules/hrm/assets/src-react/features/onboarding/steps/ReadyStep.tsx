/**
 * Ready: completion screen. Finishing persists the flag and redirects to
 * People → All Employees.
 *
 * Layout in Tailwind v4: a bordered card `rounded-2xl p-24 text-center my-8
 * max-h-340px`, the celebrate-animated 64px icon block `mb-6`,
 * `h2 text-xl font-medium m-0 mb-1`, body `text-sm leading-5`, and the button
 * block at `mt-8` (closer than the steps' 100px). No artwork asset ships with
 * the plugin, so a 🎉 glyph stands in for the celebration image.
 */

import { createInterpolateElement } from '@wordpress/element';
import { Button, Spinner } from '@wedevs/plugin-ui';
import type { JSX } from 'react';

import { PlainButton } from '@/shared/components/PlainButton';
import { __ } from '@/shared/i18n';

import { Confetti } from '../Confetti';
import { Reveal, RevealWords, afterWords } from '@/shared/motion/Reveal';
import type { StepProps } from '../types';

export function ReadyStep( { saving, onComplete }: StepProps ): JSX.Element {
	const title = __( 'You’re All Set!', 'erp' );

	return (
		<div className="mx-auto w-full max-w-160">

			{ /*
			  * No `max-h`: the card is `p-24`, and clamping the height made the
			  * content overflow its own bottom padding as soon as a second
			  * paragraph landed: the card has to grow with what it holds.
			  */ }
			<div className="my-8 rounded-2xl border border-border bg-card p-24 text-center">
				{ /* The burst starts at the 🎉, so it is centred on this card. */ }
				<div className="relative mb-6 flex justify-center">
					<Confetti />
					<span className="erp-celebrate text-[64px] leading-none" role="img" aria-label={ __( 'Celebration', 'erp' ) }>
						🎉
					</span>
				</div>

				{ /* After the 🎉 pops (0.5s), the title arrives word by word. */ }
				<RevealWords as="h2" text={ title } delay={ 350 } className="m-0 mb-1 text-xl font-medium text-foreground" />

				<Reveal as="p" delay={ afterWords( title, 350 ) } className="m-0 text-sm font-normal leading-5 text-muted-foreground">
					{ __( 'Your WP ERP is configured and ready to go. Start managing your team now!', 'erp' ) }
				</Reveal>

				{ /* Setup creates leave *policies*, never entitlements: nobody is
				   given leave automatically. Say so here, or the first leave
				   request fails with "no entitlement" and the cause is invisible.
				   The link finishes setup first and lands there, rather than
				   navigating away and leaving the wizard unfinished. */ }
				<Reveal as="p" delay={ afterWords( title, 350 ) + 70 } className="m-0 mt-4 text-sm font-normal leading-5 text-muted-foreground">
					{ createInterpolateElement(
						__( 'One more step: assign leave policies to your employees under <link>Leave → Entitlements</link>. Until then they have no leave balance and cannot submit requests.', 'erp' ),
						{
							link: (
								<PlainButton
									disabled={ saving }
									onClick={ () => onComplete( '#/leave/entitlements' ) }
									className="cursor-pointer border-0 bg-transparent p-0 font-medium text-primary underline underline-offset-2 hover:no-underline disabled:cursor-not-allowed disabled:opacity-60"
								/>
							),
						}
					) }
				</Reveal>
			</div>

			<Reveal className="mt-8 text-center" delay={ afterWords( title, 350 ) + 140 }>
				<Button
					type="button"
					disabled={ saving }
					// Wrapped, not passed by reference: `onComplete` now takes an
					// optional route, and handing it straight to onClick would feed
					// it the click event as the redirect target.
					onClick={ () => onComplete() }
					className="inline-flex h-auto items-center gap-2 px-8 py-3 text-[15px] font-semibold"
				>
					{ saving ? <Spinner className="size-4" /> : null }
					{ saving ? __( 'Redirecting…', 'erp' ) : __( 'Go to Employees', 'erp' ) }
				</Button>
			</Reveal>
		</div>
	);
}
