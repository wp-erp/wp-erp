import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/** wp-login.php. Kept separate from BasePage because it is the only page reached unauthenticated. */
export class LoginPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    get username(): Locator {
        return this.page.locator('#user_login');
    }

    get password(): Locator {
        return this.page.locator('#user_pass');
    }

    get submit(): Locator {
        return this.page.locator('#wp-submit');
    }

    get error(): Locator {
        return this.page.locator('#login_error');
    }

    async goto(): Promise<void> {
        await this.page.goto('/wp-login.php', { waitUntil: 'domcontentloaded' });
    }

    async login(user: string, pass: string): Promise<void> {
        await this.goto();
        await this.username.fill(user);
        await this.password.fill(pass);

        // Wait for the navigation the submit CAUSES, not for the state of the
        // page we are leaving: waitForLoadState can resolve against wp-login.php
        // before the POST has even started, which made a valid login look failed
        // for whichever actor happened to lose the race.
        await Promise.all([
            this.page.waitForURL((url) => !url.pathname.includes('wp-login.php'), { timeout: 30_000 }).catch(() => undefined),
            this.submit.click(),
        ]);

        await this.page.waitForLoadState('domcontentloaded');
    }

    async logout(): Promise<void> {
        await this.page.goto('/wp-login.php?action=logout', { waitUntil: 'domcontentloaded' });
        await this.page.locator('a:has-text("log out")').click().catch(() => undefined);
    }
}
