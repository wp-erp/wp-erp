import { defineConfig, devices } from '@playwright/test';
import 'dotenv/config';
import { expect } from '@playwright/test';
import { parseBoolean } from './utils/helpers';
import { customExpect } from './utils/pwMatchers';

const { CI, HEADLESS, BASE_URL, SLOWMO, NO_SETUP, ERP_PRO } = process.env;
const isCI = parseBoolean(CI);
const isPro = parseBoolean(ERP_PRO);
const dep = (deps: string[]): string[] => (parseBoolean(NO_SETUP) ? [] : deps);

expect.extend(customExpect);

/**
 * WP ERP — HR **New UI** (React engine) suite.
 *
 * A THIRD config beside playwright.config.ts (legacy Vue e2e) and api.config.ts,
 * because the HR admin engine is one site-wide option: the legacy specs need
 * `erp_hr_ui_engine=vue` and these need `react`, and two projects sharing one site
 * would flip it under each other mid-run. Separate configs mean separate runs — and
 * in CI, separate jobs — so neither can race the other. Measured why this matters:
 * with React pinned globally, 61 legacy specs failed on Vue-only selectors, and all
 * of them passed again on `vue`.
 *
 * The setup chain is reused wholesale from tests/e2e (site, auth, fixtures), with the
 * React engine pinned as the last step before the specs run.
 */
export default defineConfig({
    testDir: 'tests/e2e/newui',
    fullyParallel: false,
    forbidOnly: isCI,
    timeout: (isCI ? 60 : 45) * 1000,
    expect: { timeout: 10_000 },
    retries: isCI ? 2 : 1,
    workers: isCI ? 1 : 4,
    globalSetup: './global-setup',
    globalTeardown: './global-teardown',
    grep: [/@lite/, /@liteOnly/, /@pro/],
    grepInvert: isPro ? [/@liteOnly/, /@serial/] : [/@pro/, /@serial/],
    reporter: isCI
        ? [['list'], ['blob'], ['./utils/summaryReporter.ts']]
        : [['list'], ['html', { open: 'never' }], ['./utils/summaryReporter.ts']],
    use: {
        ...devices['Desktop Chrome'],
        baseURL: BASE_URL ?? 'http://localhost:9999',
        headless: parseBoolean(HEADLESS, true),
        launchOptions: { slowMo: Number(SLOWMO ?? 0) },
        ignoreHTTPSErrors: true,
        bypassCSP: true,
        actionTimeout: 15_000,
        navigationTimeout: (isCI ? 45 : 30) * 1000,
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
        video: 'on-first-retry',
    },
    projects: [
        { name: 'local_site_setup', testDir: 'tests/e2e', testMatch: ['**/_localSite.setup.ts'] },
        { name: 'site_setup', testDir: 'tests/e2e', testMatch: ['**/_site.setup.ts'], dependencies: dep(['local_site_setup']) },
        { name: 'auth_setup', testDir: 'tests/e2e', testMatch: ['**/_auth.setup.ts'], dependencies: dep(['site_setup']), retries: 1 },
        {
            name: 'e2e_setup',
            testDir: 'tests/e2e',
            testMatch: ['**/_env.setup.ts'],
            dependencies: dep(['auth_setup']),
            fullyParallel: true,
            retries: 1,
        },
        // Always runs — even under NO_SETUP. The legacy chain pins `vue`, so a newui run
        // against an already-seeded site MUST flip the engine back or every spec fails.
        { name: 'newui_engine', testMatch: ['**/_engine.setup.ts'], dependencies: dep(['e2e_setup']) },
        { name: 'newui_tests', testMatch: /.*\.spec\.ts/, dependencies: ['newui_engine'] },
    ],
});
