import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * HR → People → Employees (`sub-section=employee`).
 *
 * Selectors verified against the live screen. Note the create modal's selects
 * carry bracketed ids (`work[type]`), so they are addressed by NAME — a CSS id
 * selector would need escaping and reads badly.
 *
 * Field list comes from `tmpl-erp-new-employee` (42 fields, 8 required) as
 * captured in harness/forms.json.
 */
export const employeeSelectors = {
    addNew: '#erp-employee-new',
    search: '#erp-employee-search-search-input',
    searchSubmit: '#search-submit',

    filterDesignation: '#filter_designation',
    filterDepartment: '#filter_department',
    filterType: '#filter_employment_type',
    // The filter selects live in a panel that is collapsed until this toggle is
    // clicked — using them without opening it times out on "element is not visible".
    filterToggle: 'a.wperp-btn:has-text("Filters")',
    applyFilter: '#filter',
    resetFilter: 'input[name="reset_filter"]',

    row: '#wpbody-content table tbody tr',
    rowName: '#wpbody-content table tbody td.column-name',
    rowDesignation: 'td.column-designation',
    rowDepartment: 'td.column-department',
    rowType: 'td.column-type',
    rowStatus: 'td.column-status',
    totalItems: '.displaying-num',
    statusViews: '.subsubsub a',
    noItems: 'td.colspanchange',

    // ---- create modal (tmpl-erp-new-employee) ----
    firstName: '#first_name',
    lastName: '#last_name',
    email: '#erp-hr-user-email',
    type: 'select[name="work[type]"]',
    status: 'select[name="work[status]"]',
    hiringDate: 'input[name="work[hiring_date]"]',
    department: 'select[name="work[department]"]',
    designation: 'select[name="work[designation]"]',
    payRate: 'input[name="work[pay_rate]"]',
    mobile: 'input[name="personal[mobile]"]',
} as const;

/** Values the Employee Type select accepts, read from the live options. */
export const employeeTypes = ['permanent', 'parttime', 'contract', 'temporary', 'trainee'] as const;

/** Values the Employee Status select accepts. */
export const employeeStatuses = ['active', 'inactive', 'terminated', 'deceased', 'resigned'] as const;

export interface EmployeeInput {
    firstName: string;
    lastName: string;
    email: string;
    type?: (typeof employeeTypes)[number];
    status?: (typeof employeeStatuses)[number];
    hiringDate?: string;
    departmentLabel?: string;
    designationLabel?: string;
    payRate?: string;
    mobile?: string;
}

export class EmployeesPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(): Promise<void> {
        await this.gotoAdmin('erp-hr', { section: 'people', 'sub-section': 'employee' });
    }

    // ---- list ------------------------------------------------------------

    get addNewButton(): Locator {
        return this.page.locator(employeeSelectors.addNew);
    }

    get searchBox(): Locator {
        return this.page.locator(employeeSelectors.search);
    }

    get rows(): Locator {
        return this.page.locator(employeeSelectors.row);
    }

    async rowCount(): Promise<number> {
        return this.rows.count();
    }

    /** Total across all pages, from WP's own "N items" counter. */
    async totalItems(): Promise<number> {
        const label = this.page.locator(employeeSelectors.totalItems).first();
        if (!(await label.count())) return this.rowCount();
        return Number(((await label.textContent()) ?? '').replace(/[^0-9]/g, '')) || 0;
    }

    async names(): Promise<string[]> {
        return (await this.page.locator(employeeSelectors.rowName).allTextContents())
            .map((t) => t.replace(/Edit \| Delete.*$/i, '').replace(/Show more details/i, '').replace(/\s+/g, ' ').trim())
            .filter(Boolean);
    }

    async hasEmployee(name: string): Promise<boolean> {
        return (await this.names()).some((n) => n.includes(name));
    }

    /**
     * Finds an employee across pages. The list paginates at 20 and orders by
     * hire date, so a seeded early-hire or a brand-new record is often not on
     * page 1 — searching page 1 alone under-reports.
     */
    async findAcrossPages(name: string, maxPages = 5): Promise<boolean> {
        for (let pageNo = 1; pageNo <= maxPages; pageNo++) {
            await this.gotoAdmin('erp-hr', { section: 'people', 'sub-section': 'employee', paged: pageNo });
            if (await this.hasEmployee(name)) return true;
            if ((await this.rowCount()) === 0) break;
        }
        return false;
    }

    /** True when this role is offered the "Add New" control. */
    async canCreate(): Promise<boolean> {
        return this.addNewButton.isVisible().catch(() => false);
    }

    /** True when the list rendered rows for this role. */
    async canSeeList(): Promise<boolean> {
        return (await this.rowCount()) > 0;
    }

    /** The row for an employee, addressed by the visible name. */
    row(name: string): Locator {
        return this.rows.filter({ hasText: name }).first();
    }

    /** Reads one row's columns. */
    async rowDetails(name: string): Promise<{ designation: string; department: string; type: string; status: string }> {
        const row = this.row(name);
        const read = async (selector: string) => ((await row.locator(selector).textContent()) ?? '').replace(/\s+/g, ' ').trim();

        return {
            designation: await read(employeeSelectors.rowDesignation),
            department: await read(employeeSelectors.rowDepartment),
            type: await read(employeeSelectors.rowType),
            status: await read(employeeSelectors.rowStatus),
        };
    }

    /** The status views strip, e.g. "All (25)", "Active (25)", "Trash (0)". */
    async statusViews(): Promise<string[]> {
        return (await this.page.locator(employeeSelectors.statusViews).allTextContents()).map((t) => t.replace(/\s+/g, ' ').trim()).filter(Boolean);
    }

    /** Count shown beside a status view, e.g. statusCount('Active'). */
    async statusCount(label: string): Promise<number> {
        const view = (await this.statusViews()).find((v) => v.toLowerCase().startsWith(label.toLowerCase()));
        return view ? Number(view.replace(/[^0-9]/g, '')) || 0 : -1;
    }

    async isEmpty(): Promise<boolean> {
        return (await this.page.locator(employeeSelectors.noItems).count()) > 0;
    }

    // ---- search & filter --------------------------------------------------

    async search(term: string): Promise<void> {
        await this.searchBox.fill(term);
        await this.searchBox.press('Enter');
        await this.waitForErpReady();
    }

    /** Labels offered by a filter select, minus its placeholder. */
    async filterOptions(which: 'department' | 'designation' | 'type'): Promise<string[]> {
        await this.openFilterPanel();
        const map = {
            department: employeeSelectors.filterDepartment,
            designation: employeeSelectors.filterDesignation,
            type: employeeSelectors.filterType,
        };
        return (await this.page.locator(`${map[which]} option`).allTextContents()).map((t) => t.trim()).filter(Boolean);
    }

    /** Opens the collapsed filter panel if it is not already showing. */
    async openFilterPanel(): Promise<void> {
        const select = this.page.locator(employeeSelectors.filterDepartment);
        if (await select.isVisible().catch(() => false)) return;

        await this.page.locator(employeeSelectors.filterToggle).first().click();
        await select.waitFor({ state: 'visible' });
    }

    async filterBy(which: 'department' | 'designation' | 'type', label: string): Promise<void> {
        await this.openFilterPanel();
        const map = {
            department: employeeSelectors.filterDepartment,
            designation: employeeSelectors.filterDesignation,
            type: employeeSelectors.filterType,
        };
        await this.page.locator(map[which]).selectOption({ label });
        await this.page.locator(employeeSelectors.applyFilter).click();
        await this.waitForErpReady();
    }

    // ---- create modal -----------------------------------------------------

    async openCreateModal(): Promise<void> {
        await this.openModal(this.addNewButton, employeeSelectors.firstName);
    }

    /** Every field the create modal marks as required. */
    get requiredFields(): Locator[] {
        return [
            this.page.locator(employeeSelectors.firstName),
            this.page.locator(employeeSelectors.lastName),
            this.page.locator(employeeSelectors.email),
            this.page.locator(employeeSelectors.type),
            this.page.locator(employeeSelectors.status),
            this.page.locator(employeeSelectors.hiringDate),
            this.page.locator(employeeSelectors.department),
            this.page.locator(employeeSelectors.designation),
        ];
    }

    async fillEmployee(input: EmployeeInput): Promise<void> {
        await this.page.locator(employeeSelectors.firstName).fill(input.firstName);
        await this.page.locator(employeeSelectors.lastName).fill(input.lastName);
        await this.page.locator(employeeSelectors.email).fill(input.email);

        if (input.type) await this.page.locator(employeeSelectors.type).selectOption(input.type);
        if (input.status) await this.page.locator(employeeSelectors.status).selectOption(input.status);
        if (input.departmentLabel) await this.page.locator(employeeSelectors.department).selectOption({ label: input.departmentLabel });
        if (input.designationLabel) await this.page.locator(employeeSelectors.designation).selectOption({ label: input.designationLabel });
        if (input.hiringDate) await this.fillDate(employeeSelectors.hiringDate, input.hiringDate);
        if (input.payRate) await this.page.locator(employeeSelectors.payRate).fill(input.payRate);
        if (input.mobile) await this.page.locator(employeeSelectors.mobile).fill(input.mobile);
    }

    async create(input: EmployeeInput): Promise<string> {
        await this.openCreateModal();
        await this.fillEmployee(input);
        return this.submitModal();
    }

    /** Fills the modal but expects the save to be refused; leaves it open. */
    /**
     * Fills the modal and submits expecting refusal, returning the product's
     * own alert text — ERP reports these validations through `alert()`.
     */
    async attemptCreate(input: EmployeeInput): Promise<string> {
        this.captureDialogs();
        this.clearDialogs();

        await this.openCreateModal();
        await this.fillEmployee(input);
        await this.submitModalExpectingError();

        return this.lastDialog();
    }

    /** Option labels offered by a create-modal select. */
    async modalOptions(which: 'type' | 'status' | 'department' | 'designation'): Promise<string[]> {
        const map = {
            type: employeeSelectors.type,
            status: employeeSelectors.status,
            department: employeeSelectors.department,
            designation: employeeSelectors.designation,
        };
        return (await this.page.locator(`${map[which]} option`).allTextContents()).map((t) => t.trim()).filter(Boolean);
    }
}
