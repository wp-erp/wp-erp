import { Page } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * CRM contact groups and their subscribers.
 *
 * Groups live at `page=erp-crm&section=contact&sub-section=contact-groups`, and
 * each group's subscriber register is the same screen with
 * `groupaction=view-subscriber&filter_contact_group={id}`.
 *
 * A group carries a `private` flag, and that flag is load bearing: the public
 * unsubscribe handler skips private groups entirely
 * (`Subscription.php:661` — `if ( empty( $group->private ) )`), so a contact
 * cannot remove themselves from one via an emailed link.
 */
export const contactGroupSelectors = {
    table: '#wpbody-content table',
    row: '#wpbody-content table tbody tr',
} as const;

/** The statuses a subscription row can hold. */
export const subscriptionStatuses = ['subscribe', 'unsubscribe'] as const;

export class ContactGroupsPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(): Promise<void> {
        await this.gotoAdmin('erp-crm', { section: 'contact', 'sub-section': 'contact-groups' });
        await this.page.waitForTimeout(1500);
    }

    /** The subscriber register for one group. */
    async gotoSubscribers(groupId: number): Promise<void> {
        await this.gotoAdmin('erp-crm', {
            section: 'contact',
            'sub-section': 'contact-groups',
            groupaction: 'view-subscriber',
            filter_contact_group: groupId,
        });
        await this.page.waitForTimeout(1500);
    }

    /** Flattened text of the list table, for name/status assertions. */
    async tableText(): Promise<string> {
        const table = this.page.locator(contactGroupSelectors.table).first();
        if (!(await table.count())) return '';

        return (await table.innerText()).replace(/\s+/g, ' ').trim();
    }

    /** Number of rows the list table shows. */
    async rowCount(): Promise<number> {
        return this.page.locator(contactGroupSelectors.row).count();
    }
}
