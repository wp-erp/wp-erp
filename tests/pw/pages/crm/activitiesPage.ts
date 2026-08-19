import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * CRM contact activity feed — the composer and timeline on a contact's detail
 * page (`section=contact&action=view&id=N`).
 *
 * Activities are rows in `erp_crm_customer_activities`, typed by the composer
 * tab that created them (`new_note`, `email`, `log_activity`, `schedule`,
 * `tasks`). The note body is a **Trix** editor — a contenteditable custom
 * element, not a textarea and not an iframe — so it is filled by typing into it,
 * never by `fill()`.
 */
export const activitySelectors = {
    feed: '#erp-customer-feeds',
    form: '#erp-crm-activity-feed-form',
    editor: '#note-text-editor',
    // An <input type="submit">, NOT a <button>: `:has-text()` matches text
    // content, never an input's value, so a button selector silently finds
    // nothing here.
    saveNote: 'input[type="submit"][value="Save Note"]',
    composerNoteTab: 'a:has-text("New Note")',

    // Scoped to the feed: the admin nav also has a "Tasks" link, and an
    // unscoped match picks the hidden one.
    composerTabLink: (label: string) => `#erp-customer-feeds a:has-text("${label}")`,
    tasksPane: '#tasks',
    tasksEditor: '#tasks-text-editor',
    tasksDate: '#tasks input.hasDatepicker',
    tasksTime: '#tasks input.ui-timepicker-input',
    tasksAssignee: '#tasks select[name="selected_contact"]',
    createTask: '#tasks input[value="Create Task"]',
    timeline: '.erp-crm-activity-list, #erp-customer-feeds',
} as const;

/** Composer tabs the feed offers, captured live. */
export const composerTabs = ['New Note', 'Email', 'Log Activity', 'Schedule', 'Tasks'] as const;

/** Filters over the timeline, captured live. */
export const feedFilters = ['All Activities', 'Email', 'Task', 'Schedule', 'Note'] as const;

export class ActivitiesPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async gotoContact(id: number): Promise<void> {
        await this.gotoAdmin('erp-crm', { section: 'contact', action: 'view', id });
        await this.page.locator(activitySelectors.feed).waitFor({ state: 'visible', timeout: 15_000 });
        await this.page.waitForTimeout(1200);
    }

    get editor(): Locator {
        return this.page.locator(activitySelectors.editor);
    }

    get saveNoteButton(): Locator {
        return this.page.locator(activitySelectors.saveNote).first();
    }

    /** Everything the feed area currently shows. */
    async feedText(): Promise<string> {
        return ((await this.page.locator(activitySelectors.feed).textContent()) ?? '').replace(/\s+/g, ' ').trim();
    }

    async rendersComposerTabs(): Promise<string[]> {
        return this.rendersAll([...composerTabs]);
    }

    async rendersFeedFilters(): Promise<string[]> {
        return this.rendersAll([...feedFilters]);
    }

    async isEmpty(): Promise<boolean> {
        return (await this.feedText()).includes('No Activity found for this Contact');
    }

    /**
     * Writes a note and saves it.
     *
     * Trix ignores a programmatic value set — the editor keeps its own document
     * model — so the text is typed with real key events after focusing it.
     */
    async addNote(text: string): Promise<void> {
        await this.page.locator(activitySelectors.composerNoteTab).first().click();
        await this.page.waitForTimeout(600);
        await this.editor.click();
        await this.page.keyboard.type(text, { delay: 5 });
        await this.page.waitForTimeout(400);

        this.captureDialogs();
        await this.saveNoteButton.click();
        await this.page.waitForTimeout(3000);
    }

    /** Opens a composer tab by its label. */
    async openComposerTab(label: string): Promise<void> {
        await this.page.locator(activitySelectors.composerTabLink(label)).first().click();
        await this.page.waitForTimeout(1500);
    }

    /**
     * Fills the Tasks composer completely — due date, time, assignee and body.
     * Returns nothing; the caller asserts on the submit button's state.
     */
    async fillTask(text: string, dueDate = '2026-09-30', dueTime = '10:00 am'): Promise<void> {
        await this.openComposerTab('Tasks');

        await this.page.locator(activitySelectors.tasksDate).first().fill(dueDate);
        await this.page.keyboard.press('Escape');
        await this.page.locator(activitySelectors.tasksTime).first().fill(dueTime);
        await this.page.keyboard.press('Escape');
        await this.page.locator(activitySelectors.tasksAssignee).first().selectOption({ index: 0 }).catch(() => undefined);

        await this.page.locator(activitySelectors.tasksEditor).click();
        await this.page.keyboard.type(text, { delay: 6 });
        await this.page.waitForTimeout(1200);
    }

    async canCreateTask(): Promise<boolean> {
        return this.page.locator(activitySelectors.createTask).isEnabled().catch(() => false);
    }

    async hasActivity(text: string): Promise<boolean> {
        return (await this.feedText()).includes(text);
    }
}
