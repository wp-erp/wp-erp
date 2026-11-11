/**
 * Resolvers for the `erp-hr/me` store.
 *
 * @wordpress/data fires a resolver the first time a matching selector is
 * accessed. The same generator pattern as actions — yielded promises resolve
 * through built-in controls.
 *
 * Resolver naming convention: same name as the selector it primes.
 */

import * as actions from './actions';

/**
 * Resolver for `getUser` selector.
 *
 * Skips the network if the boot payload (`__ERP_HR_BOOT__`) preloaded
 * everything we need — calls `fetchMe` only on cold start or after an
 * `invalidate()` dispatch.
 */
export function* getUser(): Generator< unknown, void, unknown > {
	yield* actions.fetchMe();
}

/**
 * Resolver for `getCapabilities` — same network path as `getUser`. Triggers
 * the resolver only once because `@wordpress/data` de-duplicates identical
 * resolver invocations within a render cycle.
 */
export function* getCapabilities(): Generator< unknown, void, unknown > {
	yield* actions.fetchMe();
}
