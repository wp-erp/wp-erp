import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * HR → Leave → Leave Entitlements.
 *
 * The list lives at `sub-section=leave-entitlements`; assignment is a separate
 * full page at `&tab=assignment`.
 *
 * Two mechanics that are easy to get wrong and are handled here:
 *  - `#assignment_to` UNCHECKED means "assign to one employee" (the
 *    `#single_employee` select is shown). CHECKED switches to bulk mode and
 *    HIDES that select.
 *  - `#single_employee` is populated by AJAX only AFTER a leave policy is
 *    chosen — selecting an employee first finds an empty list.
 */
export const entitlementSelectors = {
    addNew: '#erp-new-leave-request',

    year: '#f_year',
    employeeType: '#employee_type',
    department: '#department_id',
    designation: '#designation_id',
    location: '#location_id',
    gender: '#gender',
    marital: '#marital',
    leavePolicy: '#leave_policy',
    bulkToggle: '#assignment_to',
    singleEmployee: '#single_employee',
    comment: '#comment',
    submit: '#submit',

    row: '#wpbody-content table tbody tr',
    rowEmployee: '#wpbody-content table tbody tr td:first-of-type',
    empty: 'td.colspanchange',
    filterPolicy: '#leave_policy',
} as const;

/** List columns, verified live. */
export const entitlementColumns = ['Employee Name', 'Leave Policy', 'Validity', 'Available', 'Spent'] as const;

/** Marker written into every entitlement the suite assigns. */
export const SUITE_ENTITLEMENT_COMMENT = 'Assigned by the automated suite.';

export interface EntitlementInput {
    policyLabel: string;
    employeeLabel: string;
    comment?: string;
}

export class LeaveEntitlementsPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(): Promise<void> {
        await this.gotoAdmin('erp-hr', { section: 'leave', 'sub-section': 'leave-entitlements' });
    }

    async gotoAssign(): Promise<void> {
        await this.gotoAdmin('erp-hr', { section: 'leave', 'sub-section': 'leave-entitlements', tab: 'assignment' });

        // The policy list arrives after load, so reading it immediately finds
        // only the placeholder.
        await this.page
            .waitForFunction(
                () => {
                    const select = document.querySelector('#leave_policy') as HTMLSelectElement | null;
                    return !!select && select.options.length > 1;
                },
                undefined,
                { timeout: 15_000 }
            )
            .catch(() => undefined);
    }

    get addNewButton(): Locator {
        return this.page.locator(entitlementSelectors.addNew);
    }

    get policySelect(): Locator {
        return this.page.locator(entitlementSelectors.leavePolicy);
    }

    get employeeSelect(): Locator {
        return this.page.locator(entitlementSelectors.singleEmployee);
    }

    get bulkToggle(): Locator {
        return this.page.locator(entitlementSelectors.bulkToggle);
    }

    get submitButton(): Locator {
        return this.page.locator(entitlementSelectors.submit);
    }

    // ---- list ------------------------------------------------------------

    async columnHeaders(): Promise<string[]> {
        return (await this.page.locator('#wpbody-content table thead th').allTextContents())
            .map((t) => t.replace(/\s+/g, ' ').replace(/\s*Sort\s+(ascending|descending)\.?/gi, '').trim())
            .filter(Boolean);
    }

    async rowCount(): Promise<number> {
        return this.page.locator(entitlementSelectors.row).count();
    }

    async employeeNames(): Promise<string[]> {
        return (await this.page.locator(entitlementSelectors.rowEmployee).allTextContents()).map((t) => t.replace(/\s+/g, ' ').trim()).filter(Boolean);
    }

    async hasEntitlementFor(employee: string): Promise<boolean> {
        return (await this.employeeNames()).some((n) => n.includes(employee));
    }

    /** The row for an employee + policy pair. */
    row(employee: string, policy: string): Locator {
        return this.page.locator(entitlementSelectors.row).filter({ hasText: employee }).filter({ hasText: policy }).first();
    }

    /** Available and Spent for an employee's policy, as numbers. */
    async balanceOf(employee: string, policy: string): Promise<{ available: number; spent: number }> {
        const row = this.row(employee, policy);
        const cell = async (n: number) => Number(((await row.locator('td').nth(n).textContent()) ?? '').replace(/[^0-9.-]/g, '')) || 0;

        return { available: await cell(3), spent: await cell(4) };
    }

    async isEmpty(): Promise<boolean> {
        return (await this.page.locator(entitlementSelectors.empty).count()) > 0;
    }

    // ---- assignment form --------------------------------------------------

    /** Employee options, which only populate once a policy is chosen. */
    async employeeOptions(): Promise<string[]> {
        return (await this.employeeSelect.locator('option').allTextContents())
            .map((t) => t.trim())
            .filter((t) => t && !/^-\s*Select\s*-$/i.test(t));
    }

    async policyOptions(): Promise<string[]> {
        return (await this.policySelect.locator('option').allTextContents())
            .map((t) => t.trim())
            .filter((t) => t && !/^-\s*Select\s*-$/i.test(t));
    }

    /**
     * Chooses the policy and waits for the employee list to arrive over AJAX.
     * Selecting an employee before this finds only the placeholder.
     */
    async choosePolicy(label: string): Promise<void> {
        await this.policySelect.selectOption({ label });
        await this.page.waitForFunction(
            () => {
                const select = document.querySelector('#single_employee') as HTMLSelectElement | null;
                return !!select && select.options.length > 1;
            },
            undefined,
            { timeout: 15_000 }
        );
    }

    async assignToEmployee(input: EntitlementInput): Promise<string> {
        await this.gotoAssign();

        // Unchecked = single-employee mode.
        if (await this.bulkToggle.isChecked()) await this.bulkToggle.uncheck();

        await this.choosePolicy(input.policyLabel);
        await this.employeeSelect.selectOption({ label: input.employeeLabel });
        await this.page.locator(entitlementSelectors.comment).fill(input.comment ?? SUITE_ENTITLEMENT_COMMENT);

        this.captureDialogs();
        await this.submitButton.click();
        await this.waitForErpReady();

        return this.noticeText();
    }

    /** True while still on the assignment form. */
    async isOnAssignmentForm(): Promise<boolean> {
        return this.policySelect.isVisible().catch(() => false);
    }
}
