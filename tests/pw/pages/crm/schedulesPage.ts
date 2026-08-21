import { Page } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * CRM schedules — the calendar under **Tasks → Schedules**.
 *
 * A schedule is not its own record: it is an activity of type `log_activity`,
 * separated from a past "log" only by whether `start_date` is in the future
 * (`functions-customer.php:756-758`). Ownership is `created_by`, which is what
 * the **My Schedules** / **All Schedules** scopes switch on
 * (`:2623`, `:2656`).
 *
 * ⚠️ A calendar event is titled from its **`log_type` and time** — "8:30 am
 * Meeting" — never from the activity's `message`. Asserting on the message
 * looks for text the calendar never renders, and reads as "the schedule did not
 * paint" when it painted perfectly well.
 */
export const scheduleSelectors = {
    calendar: '#erp-crm-schedule-calendar, .fc',
    event: '.fc-event, .fc-event-title, .fc-daygrid-event',
    scopeTabs: '#wpbody-content a',
} as const;

/** The calendar views the screen offers, captured live. */
export const calendarViews = ['today', 'month', 'week', 'day'] as const;

/** The two ownership scopes the screen offers, captured live. */
export const scheduleScopes = ['My Schedules', 'All Schedules'] as const;

export class SchedulesPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(): Promise<void> {
        await this.gotoAdmin('erp-crm', { section: 'task', 'sub-section': 'schedules' });
        await this.page.waitForTimeout(3000);
    }

    /** True once the calendar itself has rendered. */
    async hasCalendar(): Promise<boolean> {
        return (await this.page.locator(scheduleSelectors.calendar).count()) > 0;
    }

    /** Titles of the events currently painted on the calendar. */
    async eventTitles(): Promise<string[]> {
        const events = this.page.locator(scheduleSelectors.event);
        if (!(await events.count())) return [];

        return (await events.allTextContents()).map((t) => t.replace(/\s+/g, ' ').trim()).filter(Boolean);
    }
}
