/**
 * Birthday treat for the employee profile: when today (the site's date) is the
 * employee's birthday, a cake pill shows beside the name and a confetti burst
 * shoots out of it, the way Google marks a birthday (same burst as FlyHR's
 * onboarding).
 *
 * The burst plays once per profile per browser session (clicking the pill
 * replays it) and is skipped for visitors who ask for reduced motion. Only
 * active employees are celebrated, and only when the date of birth reaches the
 * viewer at all (it is not sent to people who may not see it).
 */

import { Cake } from 'lucide-react';
import { useCallback, useEffect, useLayoutEffect, useMemo, useRef, useState } from 'react';
import type { CSSProperties, JSX } from 'react';
import { createPortal } from 'react-dom';

import { __, sprintf } from '@/shared/i18n';
import { siteToday } from '@/shared/utils/date';

const COLORS = [ '#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6' ];

/**
 * Whether a `YYYY-MM-DD` date of birth falls on the given day. A 29 February
 * birthday is celebrated on 28 February in non-leap years.
 */
export function isBirthday( dateOfBirth: string, today: Date ): boolean {
	const match = /^(\d{4})-(\d{2})-(\d{2})/.exec( dateOfBirth );
	if ( ! match ) {
		return false;
	}
	const month = Number( match[ 2 ] );
	const day   = Number( match[ 3 ] );
	const todayMonth = today.getMonth() + 1;
	const todayDay   = today.getDate();

	if ( month === todayMonth && day === todayDay ) {
		return true;
	}

	const year     = today.getFullYear();
	const leapYear = ( year % 4 === 0 && year % 100 !== 0 ) || year % 400 === 0;
	return month === 2 && day === 29 && ! leapYear && todayMonth === 2 && todayDay === 28;
}

function prefersReducedMotion(): boolean {
	return typeof window.matchMedia === 'function' && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
}

function sessionFlag( key: string, set = false ): boolean {
	try {
		if ( set ) {
			window.sessionStorage.setItem( key, '1' );
			return true;
		}
		return window.sessionStorage.getItem( key ) === '1';
	} catch {
		return false;
	}
}

/** One piece's flight: direction and distance out, how high it rises, its spin. */
function piece( index: number, wave: number ): CSSProperties {
	const angle    = ( Math.PI * 2 * index ) / 40 + Math.random() * 0.6;
	const distance = 140 + Math.random() * 220;
	const size     = 6 + Math.round( Math.random() * 6 );

	return {
		width:             `${ size }px`,
		height:            `${ size * ( 0.4 + Math.random() * 0.8 ) }px`,
		background:        COLORS[ index % COLORS.length ],
		borderRadius:      index % 3 === 0 ? '9999px' : '2px',
		animationDelay:    `${ wave * 0.45 + Math.random() * 0.15 }s`,
		animationDuration: `${ 1.6 + Math.random() * 0.9 }s`,
		[ '--erp-confetti-dx' as string ]:   `${ Math.cos( angle ) * distance }px`,
		[ '--erp-confetti-dy' as string ]:   `${ Math.sin( angle ) * distance * 0.6 - 120 }px`,
		[ '--erp-confetti-fall' as string ]: `${ 260 + Math.random() * 200 }px`,
		[ '--erp-confetti-spin' as string ]: `${ ( Math.random() > 0.5 ? 1 : -1 ) * ( 360 + Math.random() * 540 ) }deg`,
	};
}

/** Longest flight: second wave delay + jitter + slowest duration, in ms. */
const BURST_MS = ( 0.45 + 0.15 + 2.5 ) * 1000 + 100;

/**
 * Two staggered waves bursting out of the anchor's centre (pure CSS,
 * `erp-confetti-burst` in main.css). Fixed to the viewport and portalled to
 * the body so the pieces never stretch the page into a scrollbar; removed once
 * the last piece has landed.
 */
function ConfettiBurst( { anchor, onDone }: { readonly anchor: HTMLElement; readonly onDone: () => void } ): JSX.Element | null {
	const [ origin, setOrigin ] = useState< { x: number; y: number } | null >( null );

	const pieces = useMemo< CSSProperties[] >(
		() => [ 0, 1 ].flatMap( ( wave ) => Array.from( { length: 40 }, ( _, i ) => piece( i, wave ) ) ),
		[]
	);

	useLayoutEffect( () => {
		const rect = anchor.getBoundingClientRect();
		setOrigin( { x: rect.left + rect.width / 2, y: rect.top + rect.height / 2 } );
		const timer = window.setTimeout( onDone, BURST_MS );
		return () => window.clearTimeout( timer );
	}, [ anchor, onDone ] );

	if ( ! origin ) {
		return null;
	}

	return createPortal(
		// Inline styles: the portal sits outside the app root that scopes Tailwind.
		<div style={ { position: 'fixed', left: origin.x, top: origin.y, width: 0, height: 0, zIndex: 100000, pointerEvents: 'none' } } aria-hidden="true">
			{ pieces.map( ( style, i ) => <span key={ i } className="erp-confetti-piece" style={ style } /> ) }
		</div>,
		document.body
	);
}

interface BirthdayBadgeProps {
	readonly userId:      number;
	readonly firstName:   string;
	readonly dateOfBirth: string;
	readonly status:      string;
}

/**
 * Cake pill plus the confetti burst. Renders nothing unless today is the
 * active employee's birthday.
 */
export function BirthdayBadge( { userId, firstName, dateOfBirth, status }: BirthdayBadgeProps ): JSX.Element | null {
	const celebrate = status === 'active' && isBirthday( dateOfBirth, siteToday() );
	const pillRef = useRef< HTMLButtonElement >( null );
	// Bumped per burst so a replay remounts the burst with fresh pieces.
	const [ burst, setBurst ] = useState( 0 );

	useEffect( () => {
		if ( ! celebrate || prefersReducedMotion() ) {
			return undefined;
		}
		const key = `erp-hr-birthday-${ userId }`;
		if ( sessionFlag( key ) ) {
			return undefined;
		}
		// Wait out the page-in transition (380ms) so the burst starts from
		// where the pill settles, not from where it slides in.
		const timer = window.setTimeout( () => {
			sessionFlag( key, true );
			setBurst( ( n ) => n + 1 );
		}, 500 );
		return () => window.clearTimeout( timer );
	}, [ celebrate, userId ] );

	const stop = useCallback( () => setBurst( 0 ), [] );

	if ( ! celebrate ) {
		return null;
	}

	const label = firstName
		/* translators: %s: employee first name */
		? sprintf( __( 'Happy birthday, %s!', 'erp' ), firstName )
		: __( 'Happy birthday!', 'erp' );

	return (
		<>
			<button
				ref={ pillRef }
				type="button"
				onClick={ () => {
					if ( ! prefersReducedMotion() ) {
						setBurst( ( n ) => n + 1 );
					}
				} }
				className="inline-flex cursor-pointer items-center gap-1.5 rounded-full border-0 bg-pink-50 px-3 py-1 text-xs font-semibold text-pink-700 hover:bg-pink-100 dark:bg-pink-500/15 dark:text-pink-300"
				title={ __( 'Celebrate again', 'erp' ) }
			>
				<Cake size={ 14 } strokeWidth={ 2 } aria-hidden="true" />
				{ label }
			</button>
			{ burst > 0 && pillRef.current ? <ConfettiBurst key={ burst } anchor={ pillRef.current } onDone={ stop } /> : null }
		</>
	);
}
