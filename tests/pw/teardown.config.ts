import { defineConfig, devices } from '@playwright/test';
import { parseBoolean } from './utils/helpers';
import 'dotenv/config';

const { BASE_URL, HEADLESS } = process.env;

/**
 * Licence release only. Separate config so CI can run it with `if: always()`
 * without dragging in the setup dependency chain — the seat must come back even
 * when the suite failed or the runner was cancelled.
 */
export default defineConfig({
    testDir: 'tests/e2e',
    testMatch: ['_license.teardown.ts'],
    outputDir: 'test-results/teardown',
    timeout: 120 * 1000,
    retries: 1,
    workers: 1,
    reporter: [['list']],
    use: {
        ...devices['Desktop Chrome'],
        baseURL: BASE_URL ?? 'http://localhost:8888',
        headless: parseBoolean(HEADLESS ?? 'true'),
        ignoreHTTPSErrors: true,
    },
    projects: [{ name: 'license_teardown' }],
});
