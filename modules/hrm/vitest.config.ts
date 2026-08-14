/**
 * Vitest config for the HR React source.
 *
 * Only job is to teach the runner the `@/` alias that `tsconfig.json` already
 * declares — without it a spec cannot import anything from `assets/src-react`.
 */

import { resolve } from 'node:path';
import { defineConfig } from 'vitest/config';

export default defineConfig( {
	resolve: {
		alias: {
			'@': resolve( __dirname, './assets/src-react' ),
		},
	},
	test: {
		environment: 'node',
		include: [ 'assets/src-react/**/*.{test,spec}.{ts,tsx}' ],
	},
} );
