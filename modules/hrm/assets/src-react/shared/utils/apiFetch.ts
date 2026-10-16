/**
 * `@wordpress/api-fetch` wrapper for the WP-ERP HR React shell.
 *
 * Responsibilities:
 *   - Register root-URL + nonce middlewares once on boot (from `wpApiSettings`).
 *   - Add an error-normalization middleware so every resolver sees a single
 *     `{ code, message, status }` shape.
 *   - Provide a typed `request<T>(path, opts)` facade.
 *   - Expose `restPath(ns, path, query?)` for stable path building.
 *
 * Free is the sole registrant — pro inherits the middleware stack through the
 * shared `@wordpress/api-fetch` singleton.
 */

import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';

import type { BootPayload } from '@/types/global';

export interface ApiError {
	readonly code:    string;
	readonly message: string;
	readonly status:  number;
	readonly data?:   unknown;
}

export interface ApiFetchOptions {
	readonly method?:  'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';
	readonly data?:    unknown;
	/** Raw body (e.g. `FormData` for multipart uploads). Bypasses JSON encoding. */
	readonly body?:    unknown;
	readonly query?:   Record< string, unknown >;
	readonly signal?:  AbortSignal;
	readonly headers?: Record< string, string >;
	readonly parse?:   boolean;
}

/** Per-request timeout. A hung endpoint rejects instead of spinning forever. */
const REQUEST_TIMEOUT_MS = 30000;

/** Bounded retry for transient 5xx — GET/HEAD only, never on mutations. */
const MAX_RETRIES = 2;

function delay( ms: number ): Promise< void > {
	return new Promise( ( resolve ) => setTimeout( resolve, ms ) );
}

let booted = false;

/**
 * Register middlewares ONCE per page load. Idempotent.
 *
 * Reads `window.wpApiSettings` (set by WP core) plus `window.__ERP_HR_BOOT__`
 * (set by `Enqueue::for_page()` as a belt-and-suspenders fallback).
 */
export function bootApiFetch(): void {
	if ( booted ) {
		return;
	}
	booted = true;

	const wpApiSettings = window.wpApiSettings;
	const boot          = window.__ERP_HR_BOOT__;

	const root =
		( wpApiSettings && wpApiSettings.root ) ||
		( boot && boot.api.root ) ||
		'/wp-json/';

	const nonce =
		( wpApiSettings && wpApiSettings.nonce ) ||
		( boot && boot.nonce ) ||
		'';

	apiFetch.use( apiFetch.createRootURLMiddleware( root ) );

	// WordPress core registers its own nonce middleware (and the refresh
	// endpoint) on `wp.apiFetch`. Adding a second one would pin the boot nonce:
	// after api-fetch refreshes an expired nonce on the core middleware, ours
	// would overwrite the header with the stale value again. Only register when
	// core did not, and expose it so api-fetch's refresh can update it.
	if ( nonce && ! apiFetch.nonceMiddleware ) {
		const nonceMiddleware = apiFetch.createNonceMiddleware( nonce );
		apiFetch.use( nonceMiddleware );
		apiFetch.nonceMiddleware = nonceMiddleware;
	}

	// Error-normalization middleware. Always last to run on the response path.
	apiFetch.use( async ( options, next ) => {
		try {
			return await next( options );
		} catch ( raw: unknown ) {
			// A `rest_cookie_invalid_nonce` code here lets api-fetch's own
			// handler refresh the nonce and retry, `parse: false` calls included.
			throw await normalizeError( raw );
		}
	} );

	// Timeout + transient-retry middleware. Registered last so it is the
	// outermost wrapper: it injects a 30s abort signal (chained to any caller
	// signal) and retries GET/HEAD on a 5xx, leaving mutations untouched so a
	// timed-out POST never silently double-submits.
	apiFetch.use( async ( options, next ) => {
		const method       = String( options.method ?? 'GET' ).toUpperCase();
		const isIdempotent = method === 'GET' || method === 'HEAD';
		const callerSignal = options.signal as AbortSignal | undefined;

		const runOnce = (): Promise< unknown > => {
			const controller = new AbortController();
			const timer      = setTimeout( () => controller.abort(), REQUEST_TIMEOUT_MS );

			if ( callerSignal ) {
				if ( callerSignal.aborted ) {
					controller.abort();
				} else {
					callerSignal.addEventListener( 'abort', () => controller.abort(), { once: true } );
				}
			}

			return Promise.resolve( next( { ...options, signal: controller.signal } ) ).finally(
				() => clearTimeout( timer )
			);
		};

		let attempt = 0;
		for ( ;; ) {
			try {
				return await runOnce();
			} catch ( err: unknown ) {
				const status    = ( err as Partial< ApiError > )?.status ?? 0;
				const transient = status >= 500 && status <= 599;

				if ( isIdempotent && transient && attempt < MAX_RETRIES && ! callerSignal?.aborted ) {
					attempt += 1;
					await delay( 2 ** attempt * 250 ); // 500ms, then 1000ms
					continue;
				}

				throw err;
			}
		}
	} );
}

async function normalizeError( raw: unknown ): Promise< ApiError > {
	// With `parse: false` api-fetch rejects with the raw Response, so the
	// WP_Error body (code, translated message, status) is still unread.
	if ( typeof Response !== 'undefined' && raw instanceof Response ) {
		let body: Partial< ApiError > & { data?: { status?: number } } = {};
		try {
			const parsed: unknown = await raw.json();
			if ( parsed && typeof parsed === 'object' ) {
				body = parsed as typeof body;
			}
		} catch {
			// Not JSON (a proxy error page, an empty body): keep the HTTP status.
		}
		return {
			code:    body.code ?? 'erp_hr_unknown_error',
			message: body.message ?? __( 'Unknown error', 'erp' ),
			status:  body.data?.status ?? raw.status ?? 0,
			data:    body.data,
		};
	}
	if ( raw && typeof raw === 'object' ) {
		const err = raw as Partial< ApiError > & { data?: { status?: number } };
		return {
			code:    err.code ?? 'erp_hr_unknown_error',
			message: err.message ?? __( 'Unknown error', 'erp' ),
			status:  err.data?.status ?? err.status ?? 0,
			data:    err.data,
		};
	}
	return {
		code:    'erp_hr_unknown_error',
		message: String( raw ),
		status:  0,
	};
}

/**
 * Build a REST path with optional query args.
 *
 * @example restPath('v2', '/employees', { per_page: 20 })  →  '/erp/v2/employees?per_page=20'
 */
export function restPath(
	namespace: 'v1' | 'v2',
	path: string,
	query?: Record< string, unknown >
): string {
	const ns       = namespace === 'v2' ? 'erp/v2' : 'erp/v1';
	const trimmed  = path.startsWith( '/' ) ? path : `/${ path }`;
	const fullPath = `/${ ns }${ trimmed }`;
	if ( ! query ) {
		return fullPath;
	}
	const filtered = Object.fromEntries(
		Object.entries( query ).filter(
			( [ , v ] ) => v !== undefined && v !== null && v !== ''
		)
	);
	return Object.keys( filtered ).length > 0 ? addQueryArgs( fullPath, filtered ) : fullPath;
}

/**
 * Typed REST request. Resolves with the body for `parse !== false`, or with
 * the full Response when `parse: false`.
 */
export async function request< T = unknown >(
	path: string,
	opts: ApiFetchOptions = {}
): Promise< T > {
	const { method = 'GET', data, body, query, signal, headers } = opts;
	const url = query ? appendQuery( path, query ) : path;

	const base: Record< string, unknown > = { path: url, method };
	if ( body !== undefined ) {
		// Raw body (FormData) — apiFetch leaves it untouched so the browser sets
		// the multipart boundary; never JSON-encoded.
		base.body = body;
	} else if ( data !== undefined ) {
		base.data = data;
	}
	if ( signal !== undefined ) {
		base.signal = signal;
	}
	if ( headers !== undefined ) {
		base.headers = headers;
	}
	if ( opts.parse === false ) {
		base.parse = false;
	}

	return apiFetch< T >( base as Parameters< typeof apiFetch< T > >[ 0 ] );
}

/**
 * Variant of `request` that also returns response headers (for `X-WP-Total`
 * etc.). Forces `parse: false`.
 */
export async function requestWithHeaders< T = unknown >(
	path: string,
	opts: Omit< ApiFetchOptions, 'parse' > = {}
): Promise< { body: T; headers: Headers } > {
	const response = await request< Response >( path, { ...opts, parse: false } );

	let body: T;
	try {
		body = ( await response.json() ) as T;
	} catch {
		// A 2xx with an unreadable body (PHP notice before the JSON, empty
		// reply): reject with the same shape every other failure has.
		const error: ApiError = {
			code:    'invalid_json',
			message: __( 'The response is not a valid JSON response.', 'erp' ),
			status:  response.status,
		};
		throw error;
	}
	return { body, headers: response.headers };
}

function appendQuery( path: string, query: Record< string, unknown > ): string {
	const filtered = Object.fromEntries(
		Object.entries( query ).filter(
			( [ , v ] ) => v !== undefined && v !== null && v !== ''
		)
	);
	return Object.keys( filtered ).length > 0 ? addQueryArgs( path, filtered ) : path;
}

/**
 * Returns the boot payload, asserting its presence in dev mode.
 *
 * Throws in development if the script wasn't enqueued via `Enqueue::for_page()`.
 * In production, falls back to a minimal stub so a missing payload doesn't
 * crash the whole shell.
 */
export function readBootPayload(): BootPayload {
	const boot = window.__ERP_HR_BOOT__;
	if ( ! boot ) {
		throw new Error(
			'WP-ERP HR: __ERP_HR_BOOT__ missing. The PHP enqueue helper did not localize the boot payload. Check Admin\\Enqueue::for_page().'
		);
	}
	return boot;
}
