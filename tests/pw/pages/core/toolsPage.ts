import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * WP ERP → Tools — admin.php?page=erp-tools.
 *
 * Tabs: General, Misc, Status, Audit Log, Danger Zone. The suite never executes
 * anything in Danger Zone — it only asserts the confirmation gate exists.
 */
export const toolsSelectors = {
    menuCheckbox: 'input[name="menu[]"]',
    saveChanges: 'input[type="submit"], button[type="submit"]',
    testEmailTo: '#to, input[name="to"]',
    testEmailFrom: '#from, input[name="from"]',
    testEmailBody: '#body, textarea[name="body"]',
    sendEmail: '#erp_send_test_email, input[name="erp_send_test_email"], button:has-text("Send Email")',
} as const;

/**
 * Real tab slugs, read from includes/Admin/views/tools.php:32-40. Note the Audit
 * Log tab is `log`, NOT `audit-log` — an unknown slug falls through to the
 * `default:` branch and renders an empty page rather than 404ing, so a wrong
 * slug looks like a broken tab.
 */
export const toolsTabs = ['general', 'misc', 'status', 'log', 'danger-zone'] as const;

/** Tab label as rendered in the nav, keyed by slug. */
export const toolsTabLabels: Record<string, string> = {
    general: 'General',
    misc: 'Misc.',
    status: 'Status',
    log: 'Audit Log',
    'danger-zone': 'Danger Zone',
};
export type ToolsTab = (typeof toolsTabs)[number];

export class ToolsPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(tab?: ToolsTab): Promise<void> {
        await this.gotoAdmin('erp-tools', tab ? { tab } : {});
    }

    get menuCheckboxes(): Locator {
        return this.page.locator(toolsSelectors.menuCheckbox);
    }

    get testEmailTo(): Locator {
        return this.page.locator(toolsSelectors.testEmailTo);
    }

    get testEmailFrom(): Locator {
        return this.page.locator(toolsSelectors.testEmailFrom);
    }

    get testEmailBody(): Locator {
        return this.page.locator(toolsSelectors.testEmailBody);
    }

    get sendEmailButton(): Locator {
        return this.page.locator(toolsSelectors.sendEmail).first();
    }

    /** How many menu toggles the General tab offers. */
    async menuToggleCount(): Promise<number> {
        return this.menuCheckboxes.count();
    }

    /** True when the tab rendered real content rather than the empty default branch. */
    async tabRenderedContent(): Promise<boolean> {
        const body = await this.bodyText();
        return body.length > 50 && /General|Misc|Status|Audit Log|Danger Zone/i.test(body);
    }

    /** True when the Status report names the platform it is reporting on. */
    async statusReportsPlatform(): Promise<boolean> {
        const body = await this.bodyText();
        return /WordPress/i.test(body) && /PHP/i.test(body);
    }

    /** True when the Danger Zone warns before offering the destructive action. */
    async dangerZoneWarns(): Promise<boolean> {
        return /danger|reset|delete|cannot be undone|permanently/i.test(await this.bodyText());
    }

    /** Controls standing between the user and the destructive action. */
    async dangerZoneControlCount(): Promise<number> {
        return this.page.locator('#wpbody-content input, #wpbody-content button').count();
    }
}
