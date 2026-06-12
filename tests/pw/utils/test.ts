import { test as base, expect, request, devices, chromium } from '@playwright/test';
import type { Page, Locator, BrowserContext, APIRequestContext } from '@playwright/test';

/**
 * Custom test entrypoint. Specs import `{ test, expect }` from '@utils/test'
 * instead of '@playwright/test' so every page gets the auto fixtures below.
 *
 * `_permalink404Recovery`: freshly-provisioned WP sites occasionally 404 a
 * wp-admin URL before rewrite rules settle. This best-effort fixture reloads a
 * page once when that happens, so setup/specs don't fail on a cold cache.
 */
type Fixtures = {
    _permalink404Recovery: void;
};

const reloadedPages = new WeakSet<Page>();

export const test = base.extend<Fixtures>({
    _permalink404Recovery: [
        async ({ context }, use) => {
            context.on('page', page => {
                page.on('response', async response => {
                    try {
                        if (
                            response.status() === 404 &&
                            response.request().resourceType() === 'document' &&
                            response.url().includes('/wp-admin') &&
                            !reloadedPages.has(page)
                        ) {
                            reloadedPages.add(page);
                            await page.waitForTimeout(500);
                            await page.reload();
                        }
                    } catch {
                        /* page may already be closed — ignore */
                    }
                });
            });
            await use();
        },
        { auto: true },
    ],
});

export { expect, request, devices, chromium };
export type { Page, Locator, BrowserContext, APIRequestContext };
