/**
 * The card every HR toast renders through.
 *
 * Sonner's built-in toast is a plain title/description strip. This replaces it
 * with the shape WP Project Manager settled on: a bordered card whose whole
 * body drains left-to-right as the timer runs, so the remaining time is
 * readable at a glance instead of hidden in a hairline bar.
 *
 * The left slot is the interesting part. When a toast concerns a person —
 * an employee's photo was replaced, their leave was approved — it shows that
 * employee's avatar with the status icon tucked into its corner, so the
 * message is attached to a face rather than a generic tick. Falls back to a
 * caller-supplied icon, then to the type icon.
 *
 * Nothing here is HR-specific: it takes a `user`, not an employee.
 */

import { Avatar, AvatarFallback, AvatarImage } from '@wedevs/plugin-ui';
import { AlertTriangle, CircleCheck, CircleX, Info, Loader2, X } from 'lucide-react';
import type { JSX, ReactNode } from 'react';

import { __ } from '@/shared/i18n';

export type ToastType = 'success' | 'error' | 'warning' | 'info' | 'message' | 'loading';

export interface ToastUser {
	readonly name:    string;
	readonly avatar?: string | undefined;
}

export interface ToastAction {
	readonly label:   string;
	readonly onClick: () => void;
}

export interface ToastCardProps {
	readonly type?:        ToastType;
	readonly title?:       ReactNode;
	readonly description?: ReactNode;
	readonly icon?:        ReactNode;
	readonly user?:        ToastUser | undefined;
	readonly action?:      ToastAction | undefined;
	readonly cancel?:      ToastAction | undefined;
	readonly duration?:    number;
	/** 0–100 for a determinate bar (uploads); null for none. */
	readonly progress?:    number | null;
	readonly closeButton?: boolean;
	readonly onDismiss?:   () => void;
}

/**
 * Icon + accent per toast type. The colours are literals rather than theme
 * tokens on purpose: a toast has to read the same against whatever is behind
 * it, and success/danger keep their conventional meaning in both schemes.
 */
const TYPES: Record< ToastType, { Icon: typeof Info; color: string } > = {
	success: { Icon: CircleCheck,   color: '#16a34a' },
	error:   { Icon: CircleX,       color: '#dc2626' },
	warning: { Icon: AlertTriangle, color: '#d97706' },
	info:    { Icon: Info,          color: '#2563eb' },
	message: { Icon: Info,          color: '#6b7280' },
	loading: { Icon: Loader2,       color: '#6b7280' },
};

/** First letters of a name, for the avatar fallback. */
function initials( name: string ): string {
	return name
		.split( /\s+/ )
		.filter( Boolean )
		.slice( 0, 2 )
		.map( ( part ) => part.charAt( 0 ).toUpperCase() )
		.join( '' );
}

export function ToastCard( {
	type = 'info',
	title,
	description,
	icon,
	user,
	action,
	cancel,
	duration = 4000,
	progress = null,
	closeButton = true,
	onDismiss,
}: ToastCardProps ): JSX.Element {
	const conf         = TYPES[ type ] ?? TYPES.info;
	const Icon         = conf.Icon;
	const isLoading    = type === 'loading';
	const hasProgress  = progress !== null && Number.isFinite( progress );
	// The draining fill is the timer made visible, so it only makes sense when
	// there IS a timer and nothing more precise to show.
	const showFill     = ! isLoading && Number.isFinite( duration ) && ! hasProgress;

	const run = ( fn: () => void ) => () => {
		fn();
		onDismiss?.();
	};

	return (
		<div className="relative w-[356px] max-w-[calc(100vw-2rem)] overflow-hidden rounded-lg border border-border bg-card shadow-lg">
			{ showFill ? (
				<div
					className="absolute inset-0 origin-left"
					style={ {
						background: conf.color,
						opacity:    0.16,
						animation:  `erp-toast-progress ${ duration }ms linear forwards`,
					} }
					aria-hidden="true"
				/>
			) : null }

			<div className="relative z-10 flex items-start gap-2.5 px-4 py-3">
				<span className="relative mt-px shrink-0">
					{ user ? (
						<>
							<Avatar className="size-8">
								{ user.avatar ? <AvatarImage src={ user.avatar } alt={ user.name } /> : null }
								<AvatarFallback className="text-xs">{ initials( user.name ) }</AvatarFallback>
							</Avatar>
							{ /* Status rides the avatar's corner — whose photo it is stays
							     the primary signal, what happened is the qualifier. */ }
							<span className="absolute -bottom-0.5 -right-0.5 flex items-center justify-center rounded-full bg-card p-px">
								<Icon className="size-3" style={ { color: conf.color } } aria-hidden="true" />
							</span>
						</>
					) : icon !== undefined ? (
						icon
					) : (
						<Icon
							className={ `size-5 ${ isLoading ? 'animate-spin' : '' }` }
							style={ { color: conf.color } }
							aria-hidden="true"
						/>
					) }
				</span>

				<div className="min-w-0 flex-1">
					{ title != null ? (
						<div className="break-words text-sm font-medium leading-snug text-foreground">{ title }</div>
					) : null }
					{ description != null ? (
						<div className="mt-0.5 break-words text-[13px] leading-snug text-muted-foreground">
							{ description }
						</div>
					) : null }

					{ action || cancel ? (
						<div className="mt-2 flex items-center gap-2">
							{ action ? (
								<button
									type="button"
									onClick={ run( action.onClick ) }
									className="h-7 rounded-md bg-primary px-2.5 text-[13px] font-medium text-primary-foreground transition-colors hover:bg-primary/90"
								>
									{ action.label }
								</button>
							) : null }
							{ cancel ? (
								<button
									type="button"
									onClick={ run( cancel.onClick ) }
									className="h-7 rounded-md px-2.5 text-[13px] font-medium text-muted-foreground transition-colors hover:bg-muted"
								>
									{ cancel.label }
								</button>
							) : null }
						</div>
					) : null }
				</div>

				{ closeButton && onDismiss ? (
					<button
						type="button"
						onClick={ onDismiss }
						aria-label={ __( 'Close', 'erp' ) }
						className="-mr-1 -mt-0.5 shrink-0 rounded p-1 text-muted-foreground/70 transition-colors hover:bg-muted hover:text-foreground"
					>
						<X className="size-3.5" aria-hidden="true" />
					</button>
				) : null }
			</div>

			{ hasProgress ? (
				<div className="relative z-10 h-1 w-full bg-muted">
					<div
						className="h-full transition-[width] duration-200 ease-out"
						style={ {
							width:      `${ Math.max( 0, Math.min( 100, progress ) ) }%`,
							background: conf.color,
						} }
					/>
				</div>
			) : null }
		</div>
	);
}
