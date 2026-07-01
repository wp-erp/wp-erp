/**
 * `erp-hr/me` @wordpress/data store registration.
 *
 * Importing this module registers the store as a side effect. The shell's
 * `stores/index.ts` imports it once on boot.
 */

import { createReduxStore, register } from '@wordpress/data';

import * as actions   from './actions';
import reducer        from './reducer';
import * as resolvers from './resolvers';
import * as selectors from './selectors';
import { STORE_NAME } from './types';

export const storeName = STORE_NAME;

const config = {
	reducer,
	actions,
	selectors,
	resolvers,
} as const;

export const meStore = createReduxStore( storeName, config );

register( meStore );

export type { MeError, MePreferences, MeState, MeUser, RawMeResponse } from './types';
export { setMe, setCapabilities, setPreferences, invalidate, fetchMe, updatePreferences } from './actions';
export { getUser, getCapabilities, hasCap, getPreferences, getResolvedThemeMode, getNavLayout, isLoading, getError, isReady } from './selectors';
