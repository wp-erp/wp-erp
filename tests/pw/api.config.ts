import { defineConfig } from '@playwright/test';
import { parseBoolean } from './utils/helpers';
import 'dotenv/config';

const { CI, BASE_URL } = process.env;
const ci = parseBoolean(CI);

/** REST suite: erp/v1 (284 routes) + erp_pro/v1/admin (5). No browser involved. */
export default defineConfig({
    testDir: 'tests/api',
    outputDir: 'test-results/api',
    timeout: ci ? 90 * 1000 : 45 * 1000,
    expect: { timeout: 10 * 1000 },
    fullyParallel: true,
    forbidOnly: ci,
    retries: ci ? 1 : 0,
    workers: ci ? 4 : 4,

    reporter: ci
        ? [
              ['blob', { outputDir: 'blob-report/api' }],
              ['list', { printSteps: true }],
          ]
        : [
              ['html', { open: 'never', outputFolder: 'playwright-report/api/html-report' }],
              ['list'],
          ],

    use: {
        baseURL: BASE_URL ?? 'http://localhost:8888',
        ignoreHTTPSErrors: true,
        extraHTTPHeaders: { 'Content-Type': 'application/json' },
        trace: 'on-first-retry',
    },

    projects: [{ name: 'api_tests', testMatch: /.*\.spec\.ts/ }],
});
