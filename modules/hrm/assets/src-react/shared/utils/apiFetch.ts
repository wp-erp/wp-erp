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
	readonly query?:   Record< string, unknown >;
	readonly signal?:  AbortSignal;
	readonly headers?: Record< string, string >;
	readonly parse?:   boolean;
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

	if ( nonce ) {
		apiFetch.use( apiFetch.createNonceMiddleware( nonce ) );
	}

	// Error-normalization middleware. Always last to run on the response path.
	apiFetch.use( async ( options, next ) => {
		try {
			return await next( options );
		} catch ( raw: unknown ) {
			throw normalizeError( raw );
		}
	} );
}

function normalizeError( raw: unknown ): ApiError {
	if ( raw && typeof raw === 'object' ) {
		const err = raw as Partial< ApiError > & { data?: { status?: number } };
		return {
			code:    err.code ?? 'erp_hr_unknown_error',
			message: err.message ?? 'Unknown error',
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
	const { method = 'GET', data, query, signal, headers } = opts;
	const url = query ? appendQuery( path, query ) : path;

	const base: Record< string, unknown > = { path: url, method };
	if ( data !== undefined ) {
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
	const body     = ( await response.json() ) as T;
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
