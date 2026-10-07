/**
 * Full-screen welcome, shown once to a user who has just switched from the
 * classic screens to the new design (the server sets the one-time flag in
 * UiEngineResolver::handle_switch and the boot payload hands it over).
 *
 * It uses FlyHR's intro motion: the headline arrives word by word, the lines
 * follow, the three panels rise in turn. The page underneath waits; when the
 * welcome fades out, the page plays its own entrance, so the two read as one
 * sequence rather than two animations on top of each other.
 */

import { Button } from '@wedevs/plugin-ui';
import { ArrowRight, History, LayoutDashboard, Zap } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';
import { Reveal, RevealWords, afterWords } from '@/shared/motion/Reveal';
import { prefersReducedMotion } from '@/shared/motion/MotionContext';
import { useBoot } from '@/shared/hooks/useBoot';

/** How long the welcome takes to fade away before the page starts. */
const EXIT_MS = 280;

interface WelcomeScreenProps {
	/** Called once the welcome has faded out. */
	readonly onDone: () => void;
}

export function WelcomeScreen( { onDone }: WelcomeScreenProps ): JSX.Element {
	const boot = useBoot();
	const [ leaving, setLeaving ] = useState( false );
	const button = useRef< HTMLButtonElement >( null );

	const title = __( 'Welcome to the new WP ERP', 'erp' );
	const name = boot.displayName ? boot.displayName.split( ' ' )[ 0 ] : '';
	const linesAt = afterWords( title, 150 );

	const features = [
		{
			icon: LayoutDashboard,
			title: __( 'Everything at a glance', 'erp' ),
			text: __( 'A dashboard that shows who is out, what is pending and what is coming up.', 'erp' ),
		},
		{
			icon: Zap,
			title: __( 'Quicker everyday work', 'erp' ),
			text: __( 'Lists, profiles and requests open in place, without full page reloads.', 'erp' ),
		},
		{
			icon: History,
			title: __( 'Classic screens stay', 'erp' ),
			text: __( 'Switch back from the footer at any time. Your data is the same in both.', 'erp' ),
		},
	];

	function close(): void {
		if ( leaving ) {
			return;
		}
		setLeaving( true );
		window.setTimeout( onDone, prefersReducedMotion() ? 0 : EXIT_MS );
	}

	useEffect( () => {
		// Focus lands on the way out, and the page behind does not scroll.
		const t = window.setTimeout( () => button.current?.focus(), linesAt );
		const previous = document.body.style.overflow;
		document.body.style.overflow = 'hidden';
		const onKey = ( e: KeyboardEvent ): void => {
			if ( e.key === 'Escape' ) {
				close();
			}
		};
		window.addEventListener( 'keydown', onKey );
		return () => {
			window.clearTimeout( t );
			document.body.style.overflow = previous;
			window.removeEventListener( 'keydown', onKey );
		};
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [] );

	return (
		<div
			role="dialog"
			aria-modal="true"
			aria-labelledby="erp-welcome-title"
			className={ `erp-welcome fixed inset-0 z-100000 flex items-center justify-center overflow-y-auto bg-background px-4 py-10 text-foreground ${ leaving ? 'erp-welcome-leave' : '' }` }
		>
			{ /* Three soft glows in the brand colour, drifting behind the content. */ }
			<span aria-hidden="true" className="erp-welcome-glow pointer-events-none absolute -left-32 -top-32 size-120 rounded-full bg-primary/20 blur-3xl" />
			<span aria-hidden="true" className="erp-welcome-glow erp-welcome-glow-late pointer-events-none absolute -bottom-40 -right-24 size-136 rounded-full bg-primary/15 blur-3xl" />
			<span aria-hidden="true" className="erp-welcome-glow erp-welcome-glow-third pointer-events-none absolute left-1/2 top-1/2 size-96 rounded-full bg-primary/10 blur-3xl" />

			<div className="relative mx-auto flex w-full max-w-3xl flex-col items-center text-center">
				{ boot.assets?.logoUrl ? (
					<img src={ boot.assets.logoUrl } alt="" className="mb-8 h-10 w-auto" />
				) : null }

				{ name ? (
					<Reveal delay={ 60 } className="mb-3 text-sm font-medium text-primary">
						{ /* translators: %s: the user's first name. */ }
						{ sprintf( __( 'Hi %s', 'erp' ), name ) }
					</Reveal>
				) : null }

				<RevealWords
					text={ title }
					delay={ 150 }
					className="m-0 text-3xl font-bold leading-tight tracking-tight text-foreground sm:text-4xl"
				/>
				<h2 id="erp-welcome-title" className="sr-only">{ title }</h2>

				<Reveal delay={ linesAt } as="p" className="m-0 mt-4 max-w-xl text-base text-muted-foreground">
					{ __( 'HR has a new look: faster screens, clearer lists and a dashboard built around your day.', 'erp' ) }
				</Reveal>

				<div className="mt-10 grid w-full gap-4 sm:grid-cols-3">
					{ features.map( ( f, i ) => (
						<Reveal
							key={ f.title }
							arrival="panel"
							delay={ linesAt + 120 + i * 90 }
							className="rounded-[10px] border border-border bg-card p-5 text-left shadow-sm"
						>
							<span className="mb-3 inline-flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
								<f.icon size={ 18 } aria-hidden="true" />
							</span>
							<p className="m-0 text-sm font-semibold text-foreground">{ f.title }</p>
							<p className="m-0 mt-1 text-sm text-muted-foreground">{ f.text }</p>
						</Reveal>
					) ) }
				</div>

				<Reveal delay={ linesAt + 120 + features.length * 90 + 60 } className="mt-10 flex flex-col items-center gap-3">
					<Button ref={ button } size="lg" className="gap-2 px-6" onClick={ close }>
						{ __( 'Take me in', 'erp' ) } <ArrowRight size={ 16 } aria-hidden="true" />
					</Button>
					<span className="text-xs text-muted-foreground">{ __( 'Press Esc to skip', 'erp' ) }</span>
				</Reveal>
			</div>
		</div>
	);
}
