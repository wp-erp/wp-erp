import { Page, Locator, expect } from '@playwright/test';
import { adminPath } from '@utils/helpers';

/**
 * Shared behaviour for every ERP page object. Page objects own selectors and
 * actions; specs own assertions about business outcomes. Anything a spec would
 * otherwise repeat lives here.
 */
export class BasePage {
    /** Messages from JS alert()/confirm() dialogs raised by the screen. */
    private readonly dialogs: string[] = [];
    private dialogsWired = false;

    constructor(protected readonly page: Page) {}

    /**
     * Starts capturing dialog text.
     *
     * ERP reports form validation through `alert()`. Playwright auto-dismisses
     * dialogs, so without this the message is lost and a refused save merely
     * looks like "nothing happened". Call before an action expected to fail.
     */
    captureDialogs(): void {
        if (this.dialogsWired) return;
        this.dialogsWired = true;
        this.page.on('dialog', (dialog) => {
            this.dialogs.push(dialog.message());
            void dialog.dismiss().catch(() => undefined);
        });
    }

    /** Everything alerted since capture began. */
    dialogMessages(): string[] {
        return [...this.dialogs];
    }

    /** The most recent alert text, or ''. */
    lastDialog(): string {
        return this.dialogs[this.dialogs.length - 1] ?? '';
    }

    clearDialogs(): void {
        this.dialogs.length = 0;
    }

    // ---- navigation -------------------------------------------------------

    /** Opens an ERP admin screen and waits for it to settle. */
    async gotoAdmin(pageSlug: string, params: Record<string, string | number> = {}): Promise<void> {
        await this.page.goto(adminPath(pageSlug, params), { waitUntil: 'domcontentloaded' });
        await this.waitForErpReady();
    }

    async gotoUrl(url: string): Promise<void> {
        await this.page.goto(url, { waitUntil: 'domcontentloaded' });
    }

    /**
     * ERP admin screens are Vue apps mounted after DOMContentLoaded, plus a
     * handful of plain PHP tables. Waiting for the spinner to clear covers both:
     * on a PHP screen the spinner never exists, so this returns immediately.
     */
    async waitForErpReady(): Promise<void> {
        const spinner = this.page.locator('.erp-loader, .spinner.is-active, .erp-list-table-wrap .spinner');
        await spinner.first().waitFor({ state: 'hidden', timeout: 15_000 }).catch(() => undefined);
        await this.page.waitForLoadState('domcontentloaded');
    }

    // ---- notices ----------------------------------------------------------

    get successNotice(): Locator {
        return this.page.locator('.notice-success, .updated, .erp-message-success, .toast-success');
    }

    get errorNotice(): Locator {
        return this.page.locator('.notice-error, .error, .erp-message-error, .toast-error');
    }

    /** Text of the first visible notice of either kind, or ''. */
    async noticeText(): Promise<string> {
        const notice = this.page.locator('.notice, .updated, .error').first();
        return (await notice.isVisible().catch(() => false)) ? ((await notice.textContent()) ?? '').trim() : '';
    }

    // ---- form primitives --------------------------------------------------

    async fillByLabel(label: string, value: string): Promise<void> {
        await this.page.getByLabel(label, { exact: false }).fill(value);
    }

    async selectByLabel(label: string, value: string): Promise<void> {
        await this.page.getByLabel(label, { exact: false }).selectOption(value);
    }

    /** ERP uses select2 in several places; a native select() would silently miss. */
    async selectFromSelect2(containerSelector: string, optionText: string): Promise<void> {
        await this.page.locator(containerSelector).click();
        const results = this.page.locator('.select2-results__option', { hasText: optionText });
        await results.first().click();
    }

    /** ERP date fields are jQuery UI datepickers; Escape closes the overlay. */
    async fillDate(selector: string, isoDate: string): Promise<void> {
        const field = this.page.locator(selector);
        await field.click();
        await field.fill(isoDate);
        await this.page.keyboard.press('Escape');
    }

    // ---- ERP modal (includes/Admin/views/erp-modal.php) --------------------
    //
    // Every ERP admin screen shares one modal shell: #erp-modal wraps a
    // .erp-modal-form whose primary control is button[type=submit].button-primary.
    // The body is rendered from a wp.template at open time, so a field only
    // exists after the trigger is clicked.

    /**
     * The modal's readiness is judged by its FORM, not by `#erp-modal .erp-modal`
     * — that wrapper computes to display:none even while the form is on screen
     * and interactive, so waiting on it hangs forever.
     */
    get modal(): Locator {
        return this.page.locator('.erp-modal-form');
    }

    get modalForm(): Locator {
        return this.page.locator('.erp-modal-form');
    }

    /**
     * The real submit is the labelled button INSIDE the form (e.g. "Create
     * Department"). The shell also ships an empty `.activate > button.button-primary`
     * placeholder that is never visible — matching it makes every submit time out.
     */
    get modalSubmit(): Locator {
        return this.modalForm.locator('button.button-primary:visible').first();
    }

    get modalClose(): Locator {
        return this.page.locator('a.close').first();
    }

    /** Clicks a trigger and waits for the modal body to render. */
    async openModal(trigger: Locator, firstField: string): Promise<void> {
        await trigger.click();
        await this.page.locator(firstField).waitFor({ state: 'visible' });
    }

    /** Submits the modal and returns the notice the screen rendered. */
    async submitModal(): Promise<string> {
        await this.modalSubmit.click();
        await this.page.waitForTimeout(1500);
        await this.waitForErpReady();
        return this.noticeText();
    }

    /** Submits without waiting for success — for validation cases that stay open. */
    async submitModalExpectingError(): Promise<void> {
        await this.modalSubmit.click();
        await this.page.waitForTimeout(1200);
    }

    async closeModal(): Promise<void> {
        if (await this.isModalOpen()) {
            await this.modalClose.click({ force: true }).catch(() => undefined);
            await this.page.waitForTimeout(600);
        }
    }

    /** True while the modal form is still on screen (a refused save keeps it open). */
    async isModalOpen(): Promise<boolean> {
        return this.modalSubmit.isVisible().catch(() => false);
    }

    // ---- page-level reads a spec should not do itself ----------------------

    /** Flattened text of the admin body. */
    async bodyText(): Promise<string> {
        return ((await this.page.locator('#wpbody-content').textContent()) ?? '').replace(/\s+/g, ' ').trim();
    }

    /** True when this screen refused the current role on capability grounds. */
    async isAccessDenied(): Promise<boolean> {
        const body = (await this.page.locator('body').textContent()) ?? '';
        return /do not have sufficient permissions|not allowed to access|Sorry, you are not allowed/i.test(body);
    }

    /**
     * True when an injected payload became executable markup on this screen.
     *
     * The safe outcomes are "stored and escaped" OR "sanitised away" — both are
     * fine. The failure is a real <script> element (or an inline handler)
     * carrying the payload into the DOM.
     */
    async rendersInjectedScript(payload: string): Promise<boolean> {
        return this.page.evaluate((needle) => {
            const scope = document.querySelector('#wpbody-content');
            if (!scope) return false;
            const scripts = Array.from(scope.querySelectorAll('script')).some((s) => (s.textContent || '').includes(needle));
            const handlers = Array.from(scope.querySelectorAll('[onerror],[onload],[onclick]')).some((e) =>
                ['onerror', 'onload', 'onclick'].some((a) => (e.getAttribute(a) || '').includes(needle))
            );
            return scripts || handlers;
        }, payload);
    }

    /** True when the rendered body contains every one of the given strings. */
    async rendersAll(needles: string[]): Promise<string[]> {
        const body = await this.bodyText();
        return needles.filter((n) => !body.includes(n));
    }

    // ---- assertions a page object can own ---------------------------------

    /** The whole page rendered without a PHP fatal. Cheap enough to call anywhere. */
    async hasNoPhpFatal(): Promise<boolean> {
        const body = (await this.page.locator('body').textContent()) ?? '';
        return !/Fatal error|Parse error|There has been a critical error/i.test(body);
    }

    async expectHeading(text: string | RegExp): Promise<void> {
        await expect(this.page.locator('h1, h2, h3.erp-page-title').filter({ hasText: text }).first()).toBeVisible();
    }
}
