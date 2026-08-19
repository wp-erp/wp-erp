import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * HR → Payroll (`section=payroll`), an erp-pro module.
 *
 * Unlike the rest of HRM this is a Vue app served from a PHP view, and its
 * fields carry NO id or name — they are bound with `v-model`
 * (`payroll/includes/Admin/views/pay-calendar-creation-form.php`). Every field
 * here is therefore anchored on its visible label, which is the only stable
 * handle the markup offers.
 *
 * There is no REST API: the module is 67 `wp_ajax_erp_payroll_*` actions
 * (`payroll/includes/AjaxHandler.php`), which is also where its authorization
 * defects live — see the `@authz` cases in the spec.
 */
export const payrollSelectors = {
    calendarWrapper: '#pay-calendar-add-edit-wrapper',
    addNewCalendar: 'a.button-primary:has-text("Add New Pay Calendar")',
    createCalendar: 'button:has-text("Create Pay Calendar")',
    calendarEmpty: '.erp-payroll-pay-calendar .error',
    // The list renders one .postbox per calendar, titled by h2.hndle
    // (`views/pay-calendar.php`); rows carry no table markup at all.
    calendarCard: '#pay-calendar-wrapper .postbox',
    calendarCardTitle: '#pay-calendar-wrapper .postbox h2.hndle',
    calendarCardBody: '#pay-calendar-wrapper .postbox .pay-cal-list',
    startPayrun: '.action-col .alignright',
    addEmployee: 'button:has-text("Add Employee")',
    employeeModal: '#myModal',
    modalDepartment: 'input[name="department[]"]',
    modalAddToList: 'button:has-text("Add employee to list")',
    // sweetalert v1 — the confirm step before the calendar is written.
    confirmButton: '.sweet-alert button.confirm',

    payrunTable: 'table.payruns',
    bulkEditTable: 'table.table-rec-reports',
    listHeaders: 'table th',
} as const;

/** Payroll sub-screens, as the HR nav links them. */
export const payrollScreens = {
    dashboard: 'dashboard',
    calendar: 'calendar',
    payrun: 'payrun',
    bulkPayItemEdit: 'bulk-pay-item-edit',
    reports: 'reports',
} as const;

export type PayrollScreen = keyof typeof payrollScreens;

/** Columns each list renders, captured live. */
export const payrunColumns = ['Pay Period', 'Pay Run', 'Payment Date', 'Employees', 'Net Pay + Tax', 'Status', 'Action'] as const;
export const bulkPayItemColumns = ['SL', 'Employee', 'Department', 'Designation', 'Total Payment'] as const;

/**
 * Calendar types the product offers.
 *
 * `erp_payroll_get_pay_calendar_types_dropdown_raw()` takes the six employee
 * pay types and explicitly `unset()`s `daily` and `contract`
 * (`functions-payroll.php:1200`) — a pay calendar cannot be either.
 */
export const payCalendarTypes = ['Hourly', 'Weekly', 'Biweekly', 'Monthly'] as const;

export interface PayCalendarInput {
    name: string;
    /** Defaults to Monthly. */
    type?: (typeof payCalendarTypes)[number];
    /** Index of the department whose staff to attach; defaults to the first. */
    department?: number;
}

export class PayrollPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(screen: PayrollScreen = 'dashboard'): Promise<void> {
        await this.gotoAdmin('erp-hr', { section: 'payroll', 'sub-section': payrollScreens[screen] });
    }

    async gotoNewCalendar(): Promise<void> {
        await this.gotoAdmin('erp-hr', { section: 'payroll', 'sub-section': 'calendar', subpage: 'add-cal-form' });
        await this.calendarForm.waitFor({ state: 'visible', timeout: 15_000 });
    }

    get calendarForm(): Locator {
        return this.page.locator(payrollSelectors.calendarWrapper);
    }

    /** A labelled control in the Vue form, which has no ids to target. */
    private field(label: string): Locator {
        return this.calendarForm.locator('.row', { has: this.page.locator(`label:text-is("${label}")`) });
    }

    get calendarNameField(): Locator {
        return this.field('Calendar Name').locator('input');
    }

    get calendarTypeSelect(): Locator {
        return this.field('Calendar Type').locator('select');
    }

    async calendarTypeOptions(): Promise<string[]> {
        return (await this.calendarTypeSelect.locator('option').allTextContents()).map((t) => t.trim()).filter((t) => t && !/^select type$/i.test(t));
    }

    /**
     * Creates a pay calendar.
     *
     * Choosing a type swaps the next control: monthly shows "Pay Day Mode",
     * everything else shows "Normal Pay Day" (`v-if="showForMonthly"`). Both are
     * filled by index so the same call covers either branch.
     */
    async createCalendar(input: PayCalendarInput): Promise<void> {
        await this.gotoNewCalendar();
        await this.calendarNameField.fill(input.name);
        await this.calendarTypeSelect.selectOption({ label: input.type ?? 'Monthly' });
        await this.page.waitForTimeout(500);

        const payDay = this.calendarForm.locator('.row', { has: this.page.locator('label:text-is("Pay Day Mode"), label:text-is("Normal Pay Day")') }).locator('select');

        if (await payDay.count()) {
            await payDay.first().selectOption({ index: 1 });
            await this.page.waitForTimeout(300);
        }

        // A calendar with no employees is refused outright
        // (`app-pay-calendar-add-edit.js:307`), so at least one has to be picked.
        // Ticking a department in the modal pulls in its whole staff, which is
        // steadier than driving the select2 employee picker.
        await this.addEmployeesByDepartment(input.department ?? 0);

        await this.page.locator(payrollSelectors.createCalendar).click();
        await this.page.waitForTimeout(1200);

        // Creation goes through a sweetalert confirm; without the click nothing
        // is ever sent and the screen simply sits there.
        await this.confirm();
    }

    /**
     * Ticks a department in the employee modal and adds its staff.
     *
     * Ticking the box starts an employee lookup whose result lands only in Vue
     * state — no chip, no selected option, nothing in the DOM to wait on, so the
     * settle is a timeout. The count is therefore ASSERTED afterwards: adding
     * nobody is silent, and the calendar would later be refused with "You did
     * not select any employee" long after the real cause scrolled past.
     */
    async addEmployeesByDepartment(index = 0): Promise<number> {
        // Opening the modal fires the available-employee lookup; ticking a
        // department before it lands adds nobody. Wait on the response itself
        // rather than on a timeout.
        const lookup = this.page
            .waitForResponse((r) => (r.request().postData() ?? '').includes('erp_payroll_get_available_employees'), { timeout: 15_000 })
            .catch(() => undefined);

        await this.page.locator(payrollSelectors.addEmployee).first().click();
        await lookup;

        const modal = this.page.locator(payrollSelectors.employeeModal);
        await modal.waitFor({ state: 'visible', timeout: 10_000 });
        await modal.locator(payrollSelectors.modalDepartment).nth(index).check();
        await this.page.waitForTimeout(2500);
        await modal.locator(payrollSelectors.modalAddToList).click();
        await modal.waitFor({ state: 'hidden', timeout: 10_000 });

        // The "Employee(N)" label is repainted a beat AFTER the modal closes, so
        // reading it straight away always reports 0.
        await this.page
            .waitForFunction(() => !/Employee\(0\)/.test(document.body.innerText), undefined, { timeout: 15_000 })
            .catch(() => undefined);

        const count = await this.selectedEmployeeCount();

        if (count === 0) {
            throw new Error(`no employees were attached from department #${index} — the picker settled empty`);
        }

        return count;
    }

    /** Confirms a sweetalert prompt, if one is open. */
    async confirm(): Promise<void> {
        const confirm = this.page.locator(payrollSelectors.confirmButton).first();

        if (await confirm.isVisible().catch(() => false)) {
            await confirm.click();
            await this.page.waitForTimeout(2500);
        }
    }

    /** Employees currently attached to the calendar being built. */
    async selectedEmployeeCount(): Promise<number> {
        const label = (await this.calendarForm.locator('label', { hasText: /Employee\(\d+\)/ }).first().textContent()) ?? '';
        return Number(label.match(/Employee\((\d+)\)/)?.[1] ?? 0);
    }

    /**
     * Calendar names the list screen shows.
     *
     * The list is painted by Vue after an AJAX fetch, so a freshly created
     * calendar is not in the first paint — wait for the cards (or the explicit
     * empty state) before reading.
     */
    async calendarNames(): Promise<string[]> {
        await this.goto('calendar');
        await this.page
            .waitForFunction(
                () => {
                    const wrap = document.querySelector('#pay-calendar-wrapper');
                    if (!wrap) return false;
                    return wrap.querySelectorAll('.postbox').length > 0 || /No Pay Calendar Found/.test(wrap.textContent ?? '');
                },
                undefined,
                { timeout: 15_000 }
            )
            .catch(() => undefined);

        return (await this.page.locator(payrollSelectors.calendarCardTitle).allTextContents()).map((t) => t.replace(/\s+/g, ' ').trim()).filter(Boolean);
    }

    /** The "Calendar Type / Total Employees" detail a card reports. */
    async calendarCardText(name: string): Promise<string> {
        const card = this.page.locator(payrollSelectors.calendarCard).filter({ hasText: name }).first();
        return ((await card.textContent()) ?? '').replace(/\s+/g, ' ').trim();
    }

    async hasCalendar(name: string): Promise<boolean> {
        return (await this.calendarNames()).some((n) => n.includes(name));
    }

    async isCalendarListEmpty(): Promise<boolean> {
        return (await this.page.locator(payrollSelectors.calendarEmpty).textContent().catch(() => ''))?.includes('No Pay Calendar Found') ?? false;
    }

    async columnHeaders(table: string): Promise<string[]> {
        return (await this.page.locator(`${table} th`).allTextContents())
            .map((t) => t.replace(/\s+/g, ' ').replace(/\s*Sort\s+(ascending|descending)\.?/gi, '').trim())
            .filter(Boolean);
    }

    /**
     * Posts a payroll AJAX action with the given actor's cookies.
     *
     * The `@authz` cases need the raw endpoint, not the screen: the defect is
     * that the handlers answer anyone, so going through the UI would hide it
     * behind a menu the role cannot see.
     */
    async postAjax(action: string, form: Record<string, string> = {}): Promise<{ status: number; body: string }> {
        const response = await this.page.request.post('/wp-admin/admin-ajax.php', { form: { action, ...form } });

        return { status: response.status(), body: await response.text() };
    }
}
