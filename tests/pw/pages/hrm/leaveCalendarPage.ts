import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * HR → Leave → Calendar (`section=leave&sub-section=leave-calendar`).
 *
 * A fullCalendar v3 widget mounted on `#erp-hr-calendar`. The events are NOT
 * fetched — `views/leave/calendar.php` inlines them into the page as a JSON
 * literal, built from `erp_hr_get_leave_requests([ 'status' => 1, 'year' =>
 * <current year> ])`. Two consequences the specs rely on:
 *
 *  - only APPROVED requests are ever drawn; pending and rejected ones are not,
 *  - only the CURRENT calendar year is in the page at all, whatever month the
 *    widget is showing.
 *
 * The department/designation filter posts to itself; `FormHandler.php:145`
 * intercepts the POST and redirects to the same screen with `department` /
 * `designation` in the QUERY STRING, which is what the view actually reads.
 */
export const leaveCalendarSelectors = {
    calendar: '#erp-hr-calendar',
    view: '.fc-view-container',
    title: '.fc-center h2',
    event: '.fc-event',
    eventTitle: '.fc-event .fc-title',

    department: 'select[name="department"]',
    designation: 'select[name="designation"]',
    filter: 'input[name="erp_leave_calendar_filter"]',

    prev: '.fc-prev-button',
    next: '.fc-next-button',
    today: '.fc-today-button',
    monthView: '.fc-month-button',
    weekView: '.fc-agendaWeek-button',
    dayView: '.fc-agendaDay-button',
} as const;

export class LeaveCalendarPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(params: Record<string, string | number> = {}): Promise<void> {
        await this.gotoAdmin('erp-hr', { section: 'leave', 'sub-section': 'leave-calendar', ...params });
    }

    get calendar(): Locator {
        return this.page.locator(leaveCalendarSelectors.calendar);
    }

    get departmentSelect(): Locator {
        return this.page.locator(leaveCalendarSelectors.department);
    }

    get designationSelect(): Locator {
        return this.page.locator(leaveCalendarSelectors.designation);
    }

    /** True once fullCalendar has replaced the empty div with a rendered grid. */
    async hasRendered(): Promise<boolean> {
        return this.page
            .locator(leaveCalendarSelectors.view)
            .waitFor({ state: 'visible', timeout: 15_000 })
            .then(() => true)
            .catch(() => false);
    }

    /** The month the widget is currently showing, e.g. "September 2026". */
    async currentMonth(): Promise<string> {
        return ((await this.page.locator(leaveCalendarSelectors.title).textContent()) ?? '').trim();
    }

    /** View-switch and navigation controls, by their fullCalendar classes. */
    async hasControls(): Promise<boolean> {
        const controls = [leaveCalendarSelectors.prev, leaveCalendarSelectors.next, leaveCalendarSelectors.today, leaveCalendarSelectors.monthView, leaveCalendarSelectors.weekView, leaveCalendarSelectors.dayView];
        const visible = await Promise.all(controls.map((selector) => this.page.locator(selector).isVisible().catch(() => false)));
        return visible.every(Boolean);
    }

    /**
     * Pages forward until the widget shows the month containing `isoDate`.
     *
     * fullCalendar only renders the events of the month on screen, so an event
     * a fortnight out is often invisible on load — asserting without this reads
     * as "the request is missing" when it is merely off-screen.
     */
    async showMonthOf(isoDate: string): Promise<void> {
        const date = new Date(`${isoDate}T00:00:00Z`);
        const target = date.toLocaleString('en-US', { month: 'long', year: 'numeric', timeZone: 'UTC' });

        for (let step = 0; step < 18; step += 1) {
            if ((await this.currentMonth()) === target) return;
            await this.page.locator(leaveCalendarSelectors.next).click();
            await this.page.waitForTimeout(200);
        }

        throw new Error(`the calendar never reached ${target}`);
    }

    /** Event labels drawn in the month on screen. */
    async eventTitles(): Promise<string[]> {
        return (await this.page.locator(leaveCalendarSelectors.eventTitle).allTextContents()).map((t) => t.replace(/\s+/g, ' ').trim()).filter(Boolean);
    }

    async hasEventFor(employee: string): Promise<boolean> {
        return (await this.eventTitles()).some((title) => title.includes(employee));
    }

    /**
     * The id behind a department name in the filter.
     *
     * Needed because the filter FORM always emits `designation=-1`, and the
     * view runs that through `absint()` — which is 1, not 0. Requesting the
     * screen with `department` alone is the only way to exercise the
     * department-only branch of `erp_hr_get_leave_requests()`.
     */
    async departmentId(name: string): Promise<string> {
        // Exact match: the demo data ships "Engineering Department" as well as
        // the seeded "Engineering", and a substring match picks the wrong one.
        const option = this.departmentSelect.locator('option').filter({ hasText: new RegExp(`^\\s*${name}\\s*$`) });
        return (await option.first().getAttribute('value')) ?? '';
    }

    /** Department names the filter offers. */
    async departmentOptions(): Promise<string[]> {
        return (await this.departmentSelect.locator('option').allTextContents()).map((t) => t.trim()).filter(Boolean);
    }

    /**
     * Applies the department filter through the form, as a user would.
     * Returns the URL the product lands on, so a spec can assert the POST→GET
     * round trip rather than trusting the redirect blindly.
     */
    async filterByDepartment(department: string): Promise<string> {
        await this.departmentSelect.selectOption({ label: department });
        await this.page.locator(leaveCalendarSelectors.filter).click();
        await this.page.waitForLoadState('domcontentloaded');
        await this.hasRendered();
        return this.page.url();
    }
}
