import { defineConfig, devices } from '@playwright/test';
import { parseBoolean } from './utils/helpers';
import 'dotenv/config';

const { CI, HEADLESS, BASE_URL, SLOWMO, NO_SETUP, ERP_PRO } = process.env;
const ci = parseBoolean(CI);
const pro = parseBoolean(ERP_PRO ?? 'true');

export default defineConfig({
    testDir: 'tests/e2e',

    /* Tier grep is applied per-run by the npm scripts; @pro drops out when the
       suite runs against a free-only site, and @license-limit / @needs-external
       never run inside the normal suite — they have their own projects/runs. */
    grepInvert: pro ? [/@license-limit/] : [/@pro/, /@license-limit/],

    outputDir: 'test-results/',
    globalTimeout: 120 * 60 * 1000,
    timeout: ci ? 180 * 1000 : 90 * 1000,

    expect: {
        timeout: 15 * 1000,
    },

    fullyParallel: false,
    forbidOnly: ci,
    retries: ci ? 2 : 0,
    workers: ci ? 4 : 4,
    preserveOutput: 'always',
    reportSlowTests: { max: 5, threshold: 30_000 },

    reporter: ci
        ? [
              ['blob', { outputDir: 'blob-report' }],
              ['list', { printSteps: true }],
              ['json', { outputFile: 'playwright-report/e2e/results.json' }],
          ]
        : [
              ['html', { open: 'never', outputFolder: 'playwright-report/e2e/html-report' }],
              ['list', { printSteps: true }],
          ],

    use: {
        ...devices['Desktop Chrome'],
        baseURL: BASE_URL ?? 'http://localhost:8888',
        headless: parseBoolean(HEADLESS ?? 'true'),
        acceptDownloads: true,
        ignoreHTTPSErrors: true,
        bypassCSP: true,
        actionTimeout: ci ? 30 * 1000 : 15 * 1000,
        navigationTimeout: ci ? 120 * 1000 : 45 * 1000,
        trace: 'on-first-retry',
        screenshot: { mode: 'only-on-failure', fullPage: true },
        video: 'on-first-retry',
        launchOptions: { slowMo: Number(SLOWMO ?? 0) * 1000 },
        viewport: { width: 1440, height: 900 },
    },

    projects: [
        {
            name: 'site_setup',
            testMatch: ['_site.setup.ts'],
        },
        {
            name: 'auth_setup',
            testMatch: ['_auth.setup.ts'],
            dependencies: parseBoolean(NO_SETUP) ? [] : ['site_setup'],
        },
        {
            name: 'env_setup',
            testMatch: ['_env.setup.ts'],
            dependencies: parseBoolean(NO_SETUP) ? [] : ['auth_setup'],
        },
        {
            name: 'e2e_tests',
            testMatch: /.*\.spec\.ts/,
            /* The accounting money specs share one Cash ledger and one voucher
               sequence — global state no customer or vendor scoping can isolate.
               Run beside each other they broke each other's preconditions, so
               they get their own single-worker project below. */
            testIgnore: /accounting\/(transactions|payments|bills)\.spec\.ts/,
            dependencies: parseBoolean(NO_SETUP) ? [] : ['env_setup'],
        },
        {
            /* Invoice, settlement and bill flows. Serial by necessity: they all
               post to the shared Cash ledger, and the "account is empty"
               preconditions are only meaningful when nothing else is spending or
               funding it at the same time. */
            name: 'accounting_money',
            testMatch: /accounting\/(transactions|payments|bills)\.spec\.ts/,
            fullyParallel: false,
            workers: 1,
            /* AFTER `e2e_tests`, not beside it. Projects run concurrently by
               default, so a single-worker project still competed with four e2e
               workers for one Docker site — these cases passed 21/21 alone and
               flaked whenever the rest of the suite ran alongside them. The
               dependency makes the ordering explicit and the money assertions
               deterministic; it costs wall-clock, which is the right trade for a
               ledger oracle. */
            dependencies: ['e2e_tests'],
        },
        {
            /* Leaves 100 users behind and must not run in parallel with anything.
               Invoked explicitly: npm run test:license-limit */
            name: 'license_limit',
            testMatch: ['license/userLimit.spec.ts'],
            fullyParallel: false,
            workers: 1,
            retries: 0,
        },
    ],
});
