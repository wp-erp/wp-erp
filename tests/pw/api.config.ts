import { defineConfig, devices } from '@playwright/test';
import 'dotenv/config';
import { parseBoolean } from './utils/helpers';

const { CI, BASE_URL, NO_SETUP, ERP_PRO } = process.env;
const isCI = parseBoolean(CI);
const isPro = parseBoolean(ERP_PRO);
const dep = (deps: string[]): string[] => (parseBoolean(NO_SETUP) ? [] : deps);

/**
 * WP ERP REST suite. Reuses the e2e setup chain (it produces the admin
 * storageState + X-WP-Nonce that ApiUtils needs), then runs tests/api specs.
 */
export default defineConfig({
    testDir: 'tests/api',
    fullyParallel: true,
    forbidOnly: isCI,
    timeout: (isCI ? 30 : 20) * 1000,
    expect: { timeout: 10_000 },
    retries: isCI ? 2 : 1,
    workers: isCI ? 1 : 4,
    globalSetup: './global-setup',
    grep: [/@lite/, /@liteOnly/, /@pro/],
    grepInvert: isPro ? [/@liteOnly/, /@serial/] : [/@pro/, /@serial/],
    reporter: isCI ? [['list'], ['blob'], ['./utils/summaryReporter.ts']] : [['list'], ['html', { open: 'never' }]],
    use: {
        ...devices['Desktop Chrome'],
        baseURL: BASE_URL ?? 'http://localhost:9999',
        ignoreHTTPSErrors: true,
    },
    projects: [
        // local_site_setup MUST lead the chain: it sets pretty permalinks
        // (rewrite structure /%postname%/) + timezone. Without it a fresh wp-env
        // stays on PLAIN permalinks, so every SERVER_URL=/wp-json REST call 404s.
        // (The e2e config already runs this; the api config previously skipped it,
        // which only ever worked locally against an already-provisioned site.)
        { name: 'local_site_setup', testDir: 'tests/e2e', testMatch: ['**/_localSite.setup.ts'] },
        { name: 'site_setup', testDir: 'tests/e2e', testMatch: ['**/_site.setup.ts'], dependencies: dep(['local_site_setup']) },
        { name: 'auth_setup', testDir: 'tests/e2e', testMatch: ['**/_auth.setup.ts'], dependencies: dep(['site_setup']), retries: 1 },
        { name: 'api_tests', testMatch: /.*\.spec\.ts/, dependencies: dep(['auth_setup']) },
    ],
});
