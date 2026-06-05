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
    reporter: isCI ? [['list'], ['blob']] : [['list'], ['html', { open: 'never' }]],
    use: {
        ...devices['Desktop Chrome'],
        baseURL: BASE_URL ?? 'http://localhost:9999',
        ignoreHTTPSErrors: true,
    },
    projects: [
        { name: 'site_setup', testDir: 'tests/e2e', testMatch: ['**/_site.setup.ts'] },
        { name: 'auth_setup', testDir: 'tests/e2e', testMatch: ['**/_auth.setup.ts'], dependencies: dep(['site_setup']), retries: 1 },
        { name: 'api_tests', testMatch: /.*\.spec\.ts/, dependencies: dep(['auth_setup']) },
    ],
});
