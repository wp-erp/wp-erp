/**
 * Drop-in replacement for `@playwright/test`. Every spec imports from here so
 * shared fixtures can be added in one place.
 *
 *   import { test, expect } from '@utils/test';
 *
 * Fixtures:
 *   phpErrors — fails a test whose page rendered a PHP fatal/notice. ERP screens
 *               swallow warnings into the markup, so a silent fatal would
 *               otherwise pass as an empty list.
 */
import { test as base, expect } from '@playwright/test';

const FATAL = /Fatal error|Parse error|There has been a critical error|Warning:\s|Notice:\s|Deprecated:\s/;

type ErpFixtures = {
    failOnPhpError: void;
};

export const test = base.extend<ErpFixtures>({
    failOnPhpError: [
        async ({ page }, use, testInfo) => {
            const seen: string[] = [];

            page.on('console', (msg) => {
                if (msg.type() === 'error') seen.push(`console: ${msg.text()}`);
            });

            page.on('pageerror', (err) => seen.push(`pageerror: ${err.message}`));

            page.on('response', async (response) => {
                if (!response.url().includes(process.env.BASE_URL ?? 'localhost')) return;
                const type = response.headers()['content-type'] ?? '';
                if (!type.includes('text/html')) return;
                try {
                    const body = await response.text();
                    const match = body.match(FATAL);
                    if (match) seen.push(`php: ${match[0]} in ${response.url()}`);
                } catch {
                    // body already consumed or navigation raced — not a failure signal
                }
            });

            await use();

            const php = seen.filter((s) => s.startsWith('php:'));
            if (php.length) {
                await testInfo.attach('php-errors', { body: php.join('\n'), contentType: 'text/plain' });
                throw new Error(`PHP error rendered by the page under test:\n${php.join('\n')}`);
            }
        },
        { auto: true },
    ],
});

export { expect };
export { request, chromium, firefox, webkit, devices, selectors } from '@playwright/test';
export type { Page, Browser, BrowserContext, APIRequestContext, APIResponse, Locator, Response, TestInfo } from '@playwright/test';
