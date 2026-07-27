/**
 * Route every toast in the HR admin through `ToastCard`.
 *
 * There are ~240 `toast.success()` / `toast.error()` calls across free and the
 * pro modules. Rather than rewrite them, this patches the shared sonner
 * singleton in place: each type-method is replaced with one that renders our
 * card via `toast.custom`. Every existing call site — and every future one —
 * gets the design for free, and the options it already passes (description,
 * action, cancel, icon, id, duration, onAutoClose…) go straight through.
 *
 * Pro modules resolve `@wedevs/plugin-ui` at runtime from the free app's
 * `window.ERPPluginUI`, so they share this exact `toast` object. Patching once
 * here covers all nine of them; they need no code and no import.
 *
 * Mirrors WP Project Manager's `lib/toast-custom.js`, which is where this
 * design comes from.
 */

import { toast } from '@wedevs/plugin-ui';
import { createElement } from 'react';

import { __ } from '@/shared/i18n';
import { ToastCard } from './ToastCard';
import type { ToastAction, ToastType, ToastUser } from './ToastCard';

/**
 * Options a caller may add on top of sonner's own. Anything not listed is
 * forwarded to sonner untouched.
 */
export interface ErpToastOptions {
	readonly description?: string;
	/** Show this person's photo instead of the type icon. */
	readonly user?:        ToastUser;
	readonly icon?:        unknown;
	readonly action?:      ToastAction;
	readonly cancel?:      ToastAction;
	readonly duration?:    number;
	readonly closeButton?: boolean;
	readonly id?:          string | number;
}

/**
 * A supporting line per type, so a toast is never a bare five-word title.
 * A caller's own `description` always wins. Called lazily — at module-eval
 * time the locale data may not have landed yet, which would freeze the
 * English string into a translated site.
 */
const FALLBACK_DESCRIPTION: Record< string, () => string > = {
	success: () => __( 'Your changes were saved.', 'erp' ),
	error:   () => __( 'Something went wrong. Please try again.', 'erp' ),
	warning: () => __( 'Please review the highlighted issue.', 'erp' ),
	info:    () => __( 'Here is something you should know.', 'erp' ),
	loading: () => __( 'Please wait a moment…', 'erp' ),
};

/**
 * How long each type stays. Errors outlast successes because the reader has
 * to act on them; a loading toast waits to be dismissed by its own code.
 */
const DURATION: Record< string, number > = {
	success: 3000,
	error:   5000,
	warning: 4000,
	info:    3000,
	message: 4000,
	loading: Number.POSITIVE_INFINITY,
};

type ToastFn = ( message: string, data?: ErpToastOptions ) => string | number;

/** Loosened view of the sonner singleton — its methods are replaced in place. */
interface PatchableToast {
	custom: ( render: ( id: string | number ) => unknown, options?: Record< string, unknown > ) => string | number;
	dismiss: ( id?: string | number ) => void;
	__erpPatched?: boolean;
	[ key: string ]: unknown;
}

function cardFor( type: ToastType ): ToastFn {
	return ( message, data = {} ) => {
		const patchable = toast as unknown as PatchableToast;
		const duration = data.duration ?? DURATION[ type ] ?? 4000;
		const description = data.description ?? FALLBACK_DESCRIPTION[ type ]?.();

		return patchable.custom(
			( id ) =>
				createElement( ToastCard, {
					type,
					title:       message,
					description,
					icon:        data.icon as never,
					user:        data.user,
					action:      data.action,
					cancel:      data.cancel,
					duration,
					closeButton: data.closeButton !== false,
					onDismiss:   () => patchable.dismiss( id ),
				} ),
			{ ...data, duration }
		);
	};
}

const PATCHED_TYPES: readonly ToastType[] = [ 'success', 'error', 'warning', 'info', 'message', 'loading' ];

/**
 * Idempotent — the guard matters because both the free entry and any pro
 * module could import this, and double-patching would wrap the wrapper.
 */
export function installErpToasts(): void {
	const patchable = toast as unknown as PatchableToast;
	if ( patchable.__erpPatched ) {
		return;
	}

	PATCHED_TYPES.forEach( ( type ) => {
		if ( typeof patchable[ type ] === 'function' ) {
			patchable[ type ] = cardFor( type );
		}
	} );

	patchable.__erpPatched = true;
}

installErpToasts();

/**
 * The same `toast` object, typed to admit our extra options.
 *
 * Sonner's own `ExternalToast` has no `user`, so a call site passing one fails
 * to compile even though the patched runtime handles it. Importing `toast`
 * from `@wedevs/plugin-ui` stays correct for the ~240 calls that pass nothing
 * extra; reach for this only when adding a photo, action or cancel.
 */
export const erpToast = toast as unknown as Record< ToastType, ToastFn >;
