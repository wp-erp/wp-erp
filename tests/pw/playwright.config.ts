import { defineConfig, devices } from '@playwright/test';
import 'dotenv/config';
import { parseBoolean } from './utils/helpers';
import { customExpect } from './utils/pwMatchers';
import { expect } from '@playwright/test';

const { CI, HEADLESS, BASE_URL, SLOWMO, NO_SETUP, ERP_PRO } = process.env;
const isCI = parseBoolean(CI);
const isPro = parseBoolean(ERP_PRO);

// NO_SETUP=true runs specs against an already-seeded site (skips the setup chain).
const dep = (deps: string[]): string[] => (parseBoolean(NO_SETUP) ? [] : deps);

expect.extend(customExpect);

/**
 * WP ERP E2E suite (Dokan-style). Lite/Pro is selected by tags + ERP_PRO:
 *   grep keeps @lite/@liteOnly/@pro; grepInvert drops the ones that don't apply.
 * Setup is a linear project chain gated by NO_SETUP.
 */
export default defineConfig({
    testDir: 'tests/e2e',
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
    // specDurationReporter runs in CI too: each shard writes playwright/spec-durations.json,
    // which the workflow uploads so the committed utils/shard-durations.json baseline can be
    // refreshed from real CI timings rather than one developer's laptop.
    reporter: isCI
        ? [['list'], ['blob'], ['./utils/summaryReporter.ts'], ['./utils/specDurationReporter.ts']]
        : [['list'], ['html', { open: 'never' }], ['./utils/summaryReporter.ts'], ['./utils/specDurationReporter.ts']],
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
        { name: 'local_site_setup', testMatch: ['**/_localSite.setup.ts'] },
        { name: 'site_setup', testMatch: ['**/_site.setup.ts'], dependencies: dep(['local_site_setup']) },
        { name: 'auth_setup', testMatch: ['**/_auth.setup.ts'], dependencies: dep(['site_setup']), retries: 1 },
        { name: 'e2e_setup', testMatch: ['**/_env.setup.ts'], dependencies: dep(['auth_setup']), fullyParallel: true, retries: 1 },
        // Always runs, even under NO_SETUP: the newui suite leaves the site on the
        // React engine, and these specs drive the legacy Vue screens.
        { name: 'engine_legacy', testMatch: ['**/_engine.setup.ts'], testIgnore: /newui\//, dependencies: dep(['e2e_setup']) },
        // The legacy-engine suite. tests/e2e/newui is excluded: those specs need the
        // opposite HR engine and run from newui.config.ts (see the note there).
        { name: 'e2e_tests', testMatch: /.*\.spec\.ts/, testIgnore: /newui\//, dependencies: ['engine_legacy'] },
    ],
});
