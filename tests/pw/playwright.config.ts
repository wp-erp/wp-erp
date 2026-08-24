import { defineConfig, devices } from '@playwright/test';
import { parseBoolean } from './utils/helpers';
import 'dotenv/config';

const { CI, HEADLESS, BASE_URL, SLOWMO, NO_SETUP, ERP_PRO } = process.env;
const ci = parseBoolean(CI);
const pro = parseBoolean(ERP_PRO ?? 'true');

/* The money specs, named once. `e2e_tests` excludes this list and the two
   serial money projects below divide it, so a new money spec needs exactly one
   edit here and cannot fall down the gap between the two jobs. */
const MONEY_SPECS = ['transactions', 'payments', 'bills', 'reports', 'expenses', 'purchases', 'journals'];

/* `payments.spec.ts` on its own is roughly half the money runtime — 14.5 minutes
   of 30 in CI run 32696378665 — so it takes one job and the other six take
   another, which lands them within about 90 seconds of each other.

   Playwright's `--shard` cannot do this split. Shards are balanced by test
   COUNT, not by duration, and the alphabetical order here would have put
   payments together with bills, expenses and journals in shard 1 (24 minutes)
   and left shard 2 finishing in 6. Two projects is the only way to divide these
   files by how long they actually take. */
const PAYMENT_SPECS = ['payments'];
const LEDGER_SPECS = MONEY_SPECS.filter((spec) => !PAYMENT_SPECS.includes(spec));

const specGroup = (names: string[]) => new RegExp(`accounting/(${names.join('|')})\\.spec\\.ts`);

/* Both money projects run under identical rules; only their file list differs. */
const moneyProject = {
    /* These drive multi-step money forms end to end — raise an expense, reopen
       its edit screen, re-read the ledger — and land around 50-60s each even on
       an idle machine. Against the 90s default that is under 2x headroom, so any
       contention on a shared laptop tips a passing test into a timeout: the
       expense cases passed 7/7 alone and timed out inside a full run. CI already
       allows 180s; matching it here removes the flake WITHOUT touching a single
       assertion. */
    timeout: 180 * 1000,
    fullyParallel: false,
    /* NOTE: `workers` is NOT a per-project option in Playwright — only
       `fullyParallel` is, and that serialises tests WITHIN a file, not files
       against each other. A `workers: 1` here was silently ignored. The npm
       scripts pass `--workers=1`, which is what actually enforces it. Without it
       these files run concurrently and fight over the shared Cash ledger. */
    /* AFTER `e2e_tests`, not beside it. Projects run concurrently by default, so
       a single-worker project still competed with four e2e workers for one
       Docker site — these cases passed 21/21 alone and flaked whenever the rest
       of the suite ran alongside them. CI passes `--no-deps` because each job
       stands up its own site and seeds it first. */
    dependencies: ['e2e_tests'],
};

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
    /* Two locally, not four. One wp-env container serves every worker, and at
       ~340 tests four of them saturated it: plain `page.goto` calls started
       failing with `net::ERR_ABORTED` and a different handful of tests went red
       on each run — never the same ones, never a logic fault. Two is
       deterministic here. CI keeps four because its runner has not been
       measured yet; revisit when the workflow first runs. */
    workers: ci ? 4 : 2,
    preserveOutput: 'always',
    reportSlowTests: { max: 5, threshold: 30_000 },

    /* JSON is emitted in BOTH modes, and its path comes from `PW_JSON_OUT` so
       each suite writes its own file: `npm test` runs e2e and money as separate
       invocations, and a fixed path would have the second silently overwrite the
       first. The quality report reads these four files. */
    reporter: ci
        ? [
              ['blob', { outputDir: 'blob-report' }],
              ['list', { printSteps: true }],
              ['json', { outputFile: process.env.PW_JSON_OUT ?? 'playwright-report/e2e/results.json' }],
          ]
        : [
              ['html', { open: 'never', outputFolder: 'playwright-report/e2e/html-report' }],
              ['list', { printSteps: true }],
              ['json', { outputFile: process.env.PW_JSON_OUT ?? 'playwright-report/e2e/results.json' }],
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
            testIgnore: new RegExp(`${specGroup(MONEY_SPECS).source}|license/userLimit\\.spec\\.ts`),
            dependencies: parseBoolean(NO_SETUP) ? [] : ['env_setup'],
        },
        {
            /* Bills, expenses, journals, purchases, reports and transactions.
               Serial by necessity: they all post to the shared Cash ledger, and
               the "account is empty" preconditions are only meaningful when
               nothing else is spending or funding it at the same time.

                   npm run test:money:ledgers

               This project takes whatever is in MONEY_SPECS and not in
               PAYMENT_SPECS, so a newly added money spec joins it automatically
               rather than being silently dropped by both jobs. */
            name: 'accounting_money',
            testMatch: specGroup(LEDGER_SPECS),
            ...moneyProject,
        },
        {
            /* Invoice, bill and purchase settlement. Split out of the project
               above only because of its runtime — same rules, same ledger, same
               single worker; see PAYMENT_SPECS for the measurement.

                   npm run test:money:payments */
            name: 'accounting_payments',
            testMatch: specGroup(PAYMENT_SPECS),
            ...moneyProject,
        },
        {
            /* OPT-IN ONLY, and destructive: it seeds enough users to stand the
               site ON its licensed seat limit so the guards can be exercised at
               all, then removes them in teardown. While it runs the site is AT
               its limit and no other spec can create an employee, so nothing may
               run beside it — `e2e_tests` excludes this file by name.

                   npm run test:license-limit

               NOTE: `workers` is not a per-project option in Playwright (see the
               same trap documented on `accounting_money` above); it is kept here
               only as intent. The npm script passes `--workers=1`, which is what
               actually enforces it. */
            name: 'license_limit',
            testMatch: /license\/userLimit\.spec\.ts/,
            /* The top-level `grepInvert` drops `@license-limit` from EVERY run,
               which is what keeps these cases out of the normal suite — but it
               also silently emptied this project, which listed zero tests while
               looking perfectly configured. Clearing it here re-enables them for
               the one project that is meant to run them.

               CONSEQUENCE, and the reason `npm test` names its projects: a bare
               `npx playwright test` runs EVERY project, and this one is no longer
               held back by the grep. `npm test` therefore passes
               `--project=e2e_tests --project=accounting_money` explicitly, so the
               destructive spec runs only when asked for by name. */
            grepInvert: [],
            fullyParallel: false,
            workers: 1,
            retries: 0,
            dependencies: parseBoolean(NO_SETUP) ? [] : ['env_setup'],
        },
    ],
});
