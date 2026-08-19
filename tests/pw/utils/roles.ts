import { Browser, Page } from '@playwright/test';
import { actors, ActorKey } from '@utils/authStates';

/**
 * Runs a block as another actor and always tears the context down.
 *
 * Specs should never build a BrowserContext by hand — that is plumbing, not a
 * test. They say "as an employee, open X" and assert on the result.
 */
export async function withRole<T>(browser: Browser, role: ActorKey, fn: (page: Page) => Promise<T>): Promise<T> {
    const context = await browser.newContext({ storageState: actors[role].state });
    const page = await context.newPage();

    try {
        return await fn(page);
    } finally {
        await page.close();
        await context.close();
    }
}

/** WordPress's capability-refusal copy, in the variants ERP screens produce. */
export const ACCESS_DENIED = /do not have sufficient permissions|not allowed to access|Sorry, you are not allowed/i;
