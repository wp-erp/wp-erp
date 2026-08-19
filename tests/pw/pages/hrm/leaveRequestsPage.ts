import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * HR → Leave → Requests.
 *
 * List at `sub-section=leave-requests`; the new-request form is a full page at
 * `&view=new`. A request can only be raised against an entitlement the employee
 * already holds, so specs assign one first (see LeaveEntitlementsPage).
 */
export const leaveRequestSelectors = {
    newRequest: '.add-new-h2',

    employee: '#erp-hr-leave-req-employee-id',
    // Appears only after an employee is chosen, and lists that employee's
    // ENTITLEMENTS (option value is the entitlement id, not the policy id).
    leavePolicy: '#erp-hr-leave-req-leave-policy',
    fromDate: '#erp-hr-leave-req-from-date',
    toDate: '#erp-hr-leave-req-to-date',
    reason: '#leave_reason',
    document: '#leave_document',
    submit: '#submit',

    row: '#wpbody-content table tbody tr',
    rowEmployee: '#wpbody-content table tbody tr td:first-of-type',
    empty: 'td.colspanchange',
    filterYear: '#filter_leave_year',
    filterPolicy: '#leave_policy',

    approve: 'a.erp-hr-leave-approve-btn',
    reject: 'a.erp-hr-leave-reject-btn',
} as const;

/** List columns, verified live. */
export const leaveRequestColumns = ['Employee Name', 'Policy', 'Request For', 'Requested On', 'Available', 'Status', 'Reason', 'Approved By'] as const;

/** Marker written into every request the suite raises. */
export const SUITE_REQUEST_REASON = 'Raised by the automated suite.';

export interface LeaveRequestInput {
    employeeLabel: string;
    from: string;
    to: string;
    /** Entitled policy to draw from; defaults to the first the employee holds. */
    policyLabel?: string;
    reason?: string;
}

export class LeaveRequestsPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(): Promise<void> {
        await this.gotoAdmin('erp-hr', { section: 'leave', 'sub-section': 'leave-requests' });
    }

    async gotoNew(): Promise<void> {
        await this.gotoAdmin('erp-hr', { section: 'leave', 'sub-section': 'leave-requests', view: 'new' });
    }

    get newRequestButton(): Locator {
        return this.page.locator(leaveRequestSelectors.newRequest).first();
    }

    get employeeSelect(): Locator {
        return this.page.locator(leaveRequestSelectors.employee);
    }

    get fromField(): Locator {
        return this.page.locator(leaveRequestSelectors.fromDate);
    }

    get toField(): Locator {
        return this.page.locator(leaveRequestSelectors.toDate);
    }

    get reasonField(): Locator {
        return this.page.locator(leaveRequestSelectors.reason);
    }

    get policySelect(): Locator {
        return this.page.locator(leaveRequestSelectors.leavePolicy);
    }

    get submitButton(): Locator {
        return this.page.locator(leaveRequestSelectors.submit);
    }

    // ---- list ------------------------------------------------------------

    async rowCount(): Promise<number> {
        return this.page.locator(leaveRequestSelectors.row).count();
    }

    async employeeNames(): Promise<string[]> {
        return (await this.page.locator(leaveRequestSelectors.rowEmployee).allTextContents()).map((t) => t.replace(/\s+/g, ' ').trim()).filter(Boolean);
    }

    async hasRequestFor(employee: string): Promise<boolean> {
        return (await this.employeeNames()).some((n) => n.includes(employee));
    }

    row(employee: string): Locator {
        return this.page.locator(leaveRequestSelectors.row).filter({ hasText: employee }).first();
    }

    /** Flattened text of an employee's request row — status, days, dates. */
    async rowText(employee: string): Promise<string> {
        return ((await this.row(employee).textContent()) ?? '').replace(/\s+/g, ' ').trim();
    }

    async isEmpty(): Promise<boolean> {
        return (await this.page.locator(leaveRequestSelectors.empty).count()) > 0;
    }

    /** Employees offered by the new-request form. */
    async employeeOptions(): Promise<string[]> {
        return (await this.employeeSelect.locator('option').allTextContents())
            .map((t) => t.trim())
            .filter((t) => t && !/select employee/i.test(t));
    }

    /**
     * Days the row reports for a request.
     *
     * The cell holds the date range and the count in adjacent elements with no
     * separator, so its text reads "Sep 07, 2026 - Sep 09, 20263 days" — a naive
     * /(\d+)\s*day/ match returns 20263. The dates are stripped first.
     */
    async requestedDays(employee: string): Promise<number> {
        const raw = ((await this.row(employee).locator('td.column-request').textContent()) ?? '').replace(/\s+/g, ' ');
        const withoutDates = raw.replace(/[A-Za-z]{3}\s+\d{1,2},\s*\d{4}/g, ' ');
        const match = withoutDates.match(/(\d+)\s*day/i);
        return match ? Number(match[1]) : 0;
    }

    /** True when the list currently renders a table (it does not when empty). */
    async hasTable(): Promise<boolean> {
        return (await this.page.locator('#wpbody-content table thead th').count()) > 0;
    }

    /** The Available cell for a request row, e.g. "20 days" -> 20. */
    async availableDays(employee: string): Promise<number> {
        const cell = ((await this.row(employee).locator('td.column-available').textContent()) ?? '').replace(/\s+/g, ' ');
        const match = cell.match(/(\d+)/);
        return match ? Number(match[1]) : 0;
    }

    async statusOf(employee: string): Promise<string> {
        return ((await this.row(employee).locator('td.column-status').textContent()) ?? '').replace(/\s+/g, ' ').trim();
    }

    // ---- approve / reject -------------------------------------------------

    approveLink(employee: string): Locator {
        return this.row(employee).locator(leaveRequestSelectors.approve);
    }

    rejectLink(employee: string): Locator {
        return this.row(employee).locator(leaveRequestSelectors.reject);
    }

    /**
     * Clicks a WP row-action link. They sit in a `.row-actions` block that is
     * only laid out on hover, so a plain click reports "element is outside of
     * the viewport" — scroll the row in and hover it first.
     */
    private async clickRowAction(employee: string, link: Locator): Promise<void> {
        const row = this.row(employee);
        await row.scrollIntoViewIfNeeded();
        await row.hover();
        await link.scrollIntoViewIfNeeded();
        await link.click({ force: true });
    }

    /** Approves an employee's pending request, confirming the modal. */
    async approve(employee: string): Promise<string> {
        this.captureDialogs();
        await this.clickRowAction(employee, this.approveLink(employee));
        await this.page.waitForTimeout(1200);

        // Approve/Reject open the shared ERP modal for a confirmation comment.
        if (await this.isModalOpen()) return this.submitModal();

        await this.waitForErpReady();
        return this.noticeText();
    }

    async reject(employee: string): Promise<string> {
        this.captureDialogs();
        await this.clickRowAction(employee, this.rejectLink(employee));
        await this.page.waitForTimeout(1200);

        if (await this.isModalOpen()) return this.submitModal();

        await this.waitForErpReady();
        return this.noticeText();
    }

    // ---- create -----------------------------------------------------------

    /**
     * Fills the form in the order the screen requires: choosing the employee
     * loads their entitled policies over AJAX, choosing a policy computes the
     * available balance, and only then does the submit button become enabled.
     */
    async fillRequest(input: LeaveRequestInput): Promise<void> {
        await this.employeeSelect.selectOption({ label: input.employeeLabel });

        await this.page.waitForFunction(
            () => {
                const select = document.querySelector('#erp-hr-leave-req-leave-policy') as HTMLSelectElement | null;
                return !!select && select.options.length > 1;
            },
            undefined,
            { timeout: 15_000 }
        );

        if (input.policyLabel) {
            await this.policySelect.selectOption({ label: input.policyLabel });
        } else {
            await this.policySelect.selectOption({ index: 1 });
        }

        // Choosing a policy kicks off an availability lookup. Filling the dates
        // while it is in flight loses them, and the submit button then never
        // enables — wait for the "N days are available" hint to land first.
        await this.page
            .waitForFunction(() => /days? (is|are) available/i.test(document.querySelector('#wpbody-content')?.textContent ?? ''), undefined, { timeout: 15_000 })
            .catch(() => undefined);

        await this.fillDate(leaveRequestSelectors.fromDate, input.from);
        await this.fillDate(leaveRequestSelectors.toDate, input.to);
        await this.reasonField.fill(input.reason ?? SUITE_REQUEST_REASON);

        // Submit stays disabled until the form is satisfied.
        await this.page.waitForFunction(
            () => {
                const button = document.querySelector('#submit') as HTMLInputElement | null;
                return !!button && !button.disabled;
            },
            undefined,
            { timeout: 15_000 }
        );
    }

    /** Policies (entitlements) the chosen employee can draw from. */
    async policyOptions(): Promise<string[]> {
        return (await this.policySelect.locator('option').allTextContents())
            .map((t) => t.trim())
            .filter((t) => t && !/^-\s*Select\s*-$/i.test(t));
    }

    /** The "N days are available" hint the form shows for the chosen policy. */
    async availabilityHint(): Promise<string> {
        const body = await this.bodyText();
        const match = body.match(/(\d+)\s*days? (?:is|are) available/i);
        return match ? match[0] : '';
    }

    /** Settle time for the AJAX that follows an employee choice. */
    async page_waitBriefly(): Promise<void> {
        await this.page.waitForTimeout(2500);
    }

    async columnHeaders(): Promise<string[]> {
        return (await this.page.locator('#wpbody-content table thead th').allTextContents())
            .map((t) => t.replace(/\s+/g, ' ').replace(/\s*Sort\s+(ascending|descending)\.?/gi, '').trim())
            .filter(Boolean);
    }

    /** True when the submit button is enabled. */
    async canSubmit(): Promise<boolean> {
        return this.submitButton.isEnabled().catch(() => false);
    }

    async create(input: LeaveRequestInput): Promise<string> {
        await this.gotoNew();
        await this.fillRequest(input);

        this.captureDialogs();
        await this.submitButton.click();
        await this.waitForErpReady();

        return this.noticeText();
    }

    /** Submits the form as-is and returns any alert the product raised. */
    async submitRaw(): Promise<string> {
        this.captureDialogs();
        this.clearDialogs();

        await this.submitButton.click();
        await this.page.waitForTimeout(1500);

        return this.lastDialog();
    }

    /** True while still on the new-request form. */
    async isOnRequestForm(): Promise<boolean> {
        return this.reasonField.isVisible().catch(() => false);
    }
}
