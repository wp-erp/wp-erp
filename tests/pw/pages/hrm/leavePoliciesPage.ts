import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * HR → Leave → Policies (`section=leave&sub-section=policies`).
 *
 * Creating is a FULL PAGE at `&action=new`, not a modal. The form requires a
 * `leave-id` — the leave TYPE from `wp_erp_hr_leaves` — which is exactly the
 * field the REST controller never maps (see ERP-136 / erp-pro#951), so the UI
 * can create a policy where `POST erp/v1/hrm/leaves/policies` cannot.
 *
 * `#color` ("Calendar Color *") is required but hidden: wpColorPicker replaces
 * it with a button, so it is set through its value rather than typed into.
 */
export const policySelectors = {
    addNew: '#erp-leave-policy-new',
    viewLeaveTypes: 'a:has-text("View Leave Types")',

    year: '#f-year',
    leaveType: '#leave-id',
    employeeType: '#employee_type',
    description: '#description',
    days: '#days',
    applicableFrom: '#applicable-from',
    color: '#color',
    entitleNew: '#apply-for-new-users',
    applyExisting: '#apply-for-existing-users',
    department: '#department',
    designation: '#designation',
    location: '#location',
    gender: '#gender',
    marital: '#marital',
    submit: '#submit',

    row: '#wpbody-content table tbody tr',
    rowName: '#wpbody-content table tbody tr td:first-of-type',
    totalItems: '.displaying-num',
    filterYear: '#filter_year',
    filterEmployeeType: '#filter_employee_type',
} as const;

/** List columns, verified live. */
export const policyColumns = [
    'Policy Name',
    'Year',
    'Description',
    'Days',
    'Calendar Color',
    'Type',
    'Department',
    'Designation',
    'Location',
    'Gender',
    'Marital',
] as const;

/** Fields the form marks required. */
export const requiredPolicyLabels = ['Year', 'Leave Type', 'Employee Type', 'Days', 'Calendar Color'] as const;

export interface PolicyInput {
    leaveTypeLabel: string;
    days: string;
    employeeTypeValue?: string;
    description?: string;
    applicableFrom?: string;
    color?: string;
    departmentLabel?: string;
    genderValue?: string;
}

/** Marker written into every policy the suite creates, so cleanup is exact. */
export const SUITE_POLICY_DESCRIPTION = 'Created by the automated suite.';

export class LeavePoliciesPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(): Promise<void> {
        await this.gotoAdmin('erp-hr', { section: 'leave', 'sub-section': 'policies' });
    }

    async gotoCreate(): Promise<void> {
        await this.gotoAdmin('erp-hr', { section: 'leave', 'sub-section': 'policies', action: 'new' });
    }

    get addNewButton(): Locator {
        return this.page.locator(policySelectors.addNew);
    }

    get submitButton(): Locator {
        return this.page.locator(policySelectors.submit);
    }

    get daysField(): Locator {
        return this.page.locator(policySelectors.days);
    }

    get leaveTypeSelect(): Locator {
        return this.page.locator(policySelectors.leaveType);
    }

    get yearSelect(): Locator {
        return this.page.locator(policySelectors.year);
    }

    get employeeTypeSelect(): Locator {
        return this.page.locator(policySelectors.employeeType);
    }

    // ---- list ------------------------------------------------------------

    async columnHeaders(): Promise<string[]> {
        return (await this.page.locator('#wpbody-content table thead th').allTextContents())
            .map((t) => t.replace(/\s+/g, ' ').replace(/\s*Sort\s+(ascending|descending)\.?/gi, '').trim())
            .filter(Boolean);
    }

    async rowCount(): Promise<number> {
        return this.page.locator(policySelectors.row).count();
    }

    async totalItems(): Promise<number> {
        const label = this.page.locator(policySelectors.totalItems).first();
        if (!(await label.count())) return this.rowCount();
        return Number(((await label.textContent()) ?? '').replace(/[^0-9]/g, '')) || 0;
    }

    async names(): Promise<string[]> {
        return (await this.page.locator(policySelectors.rowName).allTextContents()).map((t) => t.replace(/\s+/g, ' ').trim()).filter(Boolean);
    }

    async hasPolicy(name: string): Promise<boolean> {
        return (await this.names()).some((n) => n.includes(name));
    }

    /** Reads the Days cell for a policy row. */
    async daysOf(name: string): Promise<string> {
        const row = this.page.locator(policySelectors.row).filter({ hasText: name }).first();
        return ((await row.locator('td').nth(3).textContent()) ?? '').replace(/\s+/g, ' ').trim();
    }

    /** Reads the Type (employee type) cell for a policy row. */
    async typeOf(name: string): Promise<string> {
        const row = this.page.locator(policySelectors.row).filter({ hasText: name }).first();
        return ((await row.locator('td').nth(5).textContent()) ?? '').replace(/\s+/g, ' ').trim();
    }

    // ---- create form ------------------------------------------------------

    /** Labels offered by the leave-type select, minus its placeholder. */
    async leaveTypeOptions(): Promise<string[]> {
        return (await this.leaveTypeSelect.locator('option').allTextContents())
            .map((t) => t.trim())
            .filter((t) => t && !/select leave type/i.test(t));
    }

    async employeeTypeOptions(): Promise<string[]> {
        return (await this.employeeTypeSelect.locator('option').allTextContents()).map((t) => t.trim()).filter(Boolean);
    }

    async fillPolicy(input: PolicyInput): Promise<void> {
        // Year has exactly one real option (the configured financial year).
        await this.yearSelect.selectOption({ index: 1 });
        await this.leaveTypeSelect.selectOption({ label: input.leaveTypeLabel });
        await this.employeeTypeSelect.selectOption(input.employeeTypeValue ?? '-1');
        await this.daysField.fill(input.days);

        if (input.description) await this.page.locator(policySelectors.description).fill(input.description);
        if (input.applicableFrom) await this.page.locator(policySelectors.applicableFrom).fill(input.applicableFrom);
        if (input.departmentLabel) await this.page.locator(policySelectors.department).selectOption({ label: input.departmentLabel });
        if (input.genderValue) await this.page.locator(policySelectors.gender).selectOption(input.genderValue);

        // wpColorPicker hides the real input, so set the value directly.
        await this.page.locator(policySelectors.color).evaluate((el, value) => {
            (el as HTMLInputElement).value = value;
        }, input.color ?? '#4CAF50');
    }

    async create(input: PolicyInput): Promise<string> {
        await this.gotoCreate();
        await this.fillPolicy({ description: SUITE_POLICY_DESCRIPTION, ...input });

        this.captureDialogs();
        await this.submitButton.click();
        await this.waitForErpReady();

        return this.noticeText();
    }

    /** Submits the create form as-is; returns any alert the product raised. */
    async submitRaw(): Promise<string> {
        this.captureDialogs();
        this.clearDialogs();

        await this.submitButton.click();
        await this.page.waitForTimeout(1200);

        return this.lastDialog();
    }

    /** True while still on the create form (a refused save does not navigate). */
    async isOnCreateForm(): Promise<boolean> {
        return this.daysField.isVisible().catch(() => false);
    }
}
