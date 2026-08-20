import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * WP ERP Accounting — a **hash-router Vue SPA**, unlike every other ERP module.
 *
 * Screens are `admin.php?page=erp-accounting#/users/customers`, not
 * `section=`/`sub-section=` query parameters. Two consequences that shape
 * everything here:
 *
 * 1. **A hash-only change does not reload the page**, so `page.goto()` with a
 *    new hash is a no-op when the SPA is already open. `gotoRoute()` sets
 *    `location.hash` and fires `hashchange` instead, and only does a real
 *    navigation when arriving from another screen.
 * 2. The route list is taken from the app's OWN navigation anchors rather than
 *    reconstructed from `router/index.js` — the router file nests paths across
 *    124 declarations and hand-assembling them was guesswork.
 */
export const accountingSelectors = {
    app: '#wpbody-content',
    // h3 included deliberately: the SPA titles several screens with an h3 card
    // heading and leaves the h2 empty — the reports screen renders `<h2></h2>`
    // followed by one h3 per report.
    heading: '#wpbody-content h1, #wpbody-content h2, #wpbody-content h3',
    listTable: 'table',
    rows: 'table tbody tr',
    addNew: 'a:has-text("Add New"), button:has-text("Add New")',
    modal: '.modal, .erp-modal, [class*="modal"]',
    primaryButton: 'button.btn-primary, button[type="submit"], .wperp-btn.btn--primary',
    spinner: '.spinner, .loading, .erp-loader',
} as const;

/** Every accounting route, captured from the app's own navigation. */
export const accountingRoutes = {
    dashboard: '/dashboard',
    customers: '/users/customers',
    vendors: '/users/vendors',
    employees: '/users/employees',
    sales: '/transactions/sales',
    expenses: '/transactions/expenses',
    purchases: '/transactions/purchases',
    journals: '/transactions/journals',
    reimbursements: '/transactions/reimbursements',
    products: '/products/product-service',
    productCategories: '/products/product-categories',
    inventory: '/products/inventory',
    chartOfAccounts: '/settings/charts',
    bankAccounts: '/settings/banks',
    taxRates: '/settings/taxes/tax-rates',
    taxPayments: '/settings/taxes/tax-records',
    reports: '/reports',
    newInvoice: '/invoices/new',
    newEstimate: '/estimates/new',
    newPayment: '/payments/new',
    newBill: '/bills/new',
    newPayBill: '/pay-bills/new',
    newPurchaseOrder: '/purchase-orders/new',
    newPurchase: '/purchases/new',
    newPayPurchase: '/pay-purchases/new',
    newExpense: '/expenses/new',
    newCheck: '/checks/new',
    newJournal: '/transactions/journals/new',
    openingBalance: '/opening-balance',
} as const;

export type AccountingRoute = keyof typeof accountingRoutes;

/**
 * The heading each route renders, captured live. These are the anti-invention
 * rail: a spec asserts against this map, never against a remembered label.
 */
export const routeHeadings: Record<AccountingRoute, string> = {
    dashboard: 'Dashboard',
    customers: 'Customers',
    vendors: 'Vendors',
    employees: 'Employees',
    sales: 'Sales Transactions',
    expenses: 'Expenses Transactions',
    purchases: 'Purchases Transactions',
    journals: 'Journals',
    reimbursements: 'Reimbursements',
    products: 'Products',
    productCategories: 'Add new category',
    inventory: 'Inventory Products',
    chartOfAccounts: 'Chart of Accounts',
    bankAccounts: 'Accounts',
    taxRates: 'Tax Rates',
    taxPayments: 'Tax Payments',
    reports: 'Trial Balance',
    newInvoice: 'New Invoice',
    newEstimate: 'New Estimate',
    newPayment: 'Payment',
    newBill: 'New Bill',
    newPayBill: 'New Bill Payment',
    newPurchaseOrder: 'New Purchase Order',
    newPurchase: 'New Purchase',
    newPayPurchase: 'Payment',
    newExpense: 'New Expense',
    newCheck: 'New Check',
    newJournal: 'New Journal',
    openingBalance: 'Opening Balances',
};

/** The five account classes the chart of accounts is grouped by, captured live. */
export const accountClasses = ['Asset', 'Liability', 'Equity', 'Income', 'Expense'] as const;

/** Reports the reports screen offers, captured live. */
export const reportNames = ['Trial Balance', 'Ledger Report', 'Income Statement', 'Sales Tax', 'Balance Sheet'] as const;

export class AccountingPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    // ---- navigation -------------------------------------------------------

    /**
     * Opens an accounting screen.
     *
     * Hash-only changes never trigger a navigation, so when the SPA is already
     * open this sets the hash and dispatches `hashchange` by hand. Arriving from
     * anywhere else does a real page load first.
     */
    async gotoRoute(route: AccountingRoute): Promise<void> {
        const hash = accountingRoutes[route];
        const base = '/wp-admin/admin.php?page=erp-accounting';

        if (!this.page.url().includes('page=erp-accounting')) {
            await this.page.goto(`${base}#${hash}`, { waitUntil: 'domcontentloaded' });
        } else {
            await this.page.evaluate((h) => {
                window.location.hash = h;
                window.dispatchEvent(new HashChangeEvent('hashchange'));
            }, hash);
        }

        await this.settle();
    }

    /** Waits for the SPA to finish painting the current route. */
    async settle(): Promise<void> {
        await this.page.waitForLoadState('domcontentloaded');
        await this.page
            .locator(accountingSelectors.spinner)
            .first()
            .waitFor({ state: 'hidden', timeout: 10_000 })
            .catch(() => undefined);
        await this.page.waitForTimeout(2500);
    }

    /** Headings currently rendered, trimmed. */
    async headings(): Promise<string[]> {
        return (await this.page.locator(accountingSelectors.heading).allTextContents())
            .map((t) => t.replace(/\s+/g, ' ').trim())
            .filter(Boolean);
    }

    /** True when the route painted the heading captured for it. */
    async showsHeadingFor(route: AccountingRoute): Promise<boolean> {
        const expected = routeHeadings[route];
        return (await this.headings()).some((h) => h.includes(expected));
    }

    /** How much text the screen rendered — a blank SPA route returns almost none. */
    async contentLength(): Promise<number> {
        return (await this.bodyText()).length;
    }

    // ---- list tables ------------------------------------------------------

    async rowCount(): Promise<number> {
        return this.page.locator(accountingSelectors.rows).count();
    }

    async tableText(): Promise<string> {
        return ((await this.page.locator(accountingSelectors.listTable).first().textContent()) ?? '')
            .replace(/\s+/g, ' ')
            .trim();
    }

    async hasRowContaining(text: string): Promise<boolean> {
        return (await this.bodyText()).includes(text);
    }

    // ---- chart of accounts ------------------------------------------------

    /** The account-class tabs/sections the chart of accounts groups by. */
    async chartClasses(): Promise<string[]> {
        const body = await this.bodyText();
        return [...accountClasses].filter((c) => body.includes(c));
    }

    // ---- transaction forms -------------------------------------------------
    //
    // The invoice/bill/expense forms carry no ids and no name attributes except
    // `qty` — everything is anchored on the sibling `<label>` text, the same way
    // payroll and the deals modal have to be. Pickers are **vue-multiselect**,
    // the identical component the Add New Deal modal uses.

    /**
     * The field block whose label matches, e.g. "Customer", "Deposit to".
     *
     * Anchored on the LABEL and stepped up one level, rather than on a wrapper
     * class. The wrappers are inconsistent — `.wperp-form-group` on some fields,
     * bare `.wperp-col-sm-4` on others — and a class-based selector silently
     * missed Payment Method and Deposit to, which are the two fields the payment
     * form refuses to save without.
     */
    private group(label: string): Locator {
        return this.page.locator('label', { hasText: label }).first().locator('xpath=..');
    }

    /** Fills a label-anchored text input (addresses, references). */
    async fillField(label: string, value: string): Promise<void> {
        await this.group(label).locator('input.wperp-form-field').first().fill(value);
        await this.page.keyboard.press('Escape');
    }

    /**
     * Sets a date field by driving its CALENDAR, which is the only path that
     * actually reaches the Vue model.
     *
     * `components/base/Datepicker.vue` binds the input with
     * `v-model="selectedDate" @input="onChangeDate"`, and `onChangeDate()` emits
     * to the parent **only when the field has been emptied**
     * (`if (this.selectedDate.length === 0)`). So typing a date updates what the
     * user sees and nothing else: the form still reports "Transaction Date is
     * required" on save. Only `pickerSelect()` — a day click — emits a real
     * value. That is ERP-149; every accounting form that takes a date is
     * affected, so this helper exists to work around it, not to hide it.
     */
    async pickDate(label: string, isoDate: string): Promise<void> {
        const [year, month, day] = isoDate.split('-').map(Number) as [number, number, number];
        const monthName = new Date(Date.UTC(year, month - 1, 1)).toLocaleString('en-US', { month: 'long', timeZone: 'UTC' });
        const wanted = `${monthName} ${year}`;

        const input = this.group(label).locator('input.wperp-form-field').first();
        await input.click();
        await this.page.waitForTimeout(900);

        const header = this.page.locator('.c-pane-container:visible .c-header').first();
        const arrows = this.page.locator('.c-pane-container:visible .c-arrow-layout');

        // Bounded walk — 24 months either way is far more than any test needs,
        // and a bound means a mis-read header cannot spin forever.
        for (let i = 0; i < 24; i++) {
            const current = ((await header.textContent()) ?? '').replace(/\s+/g, ' ').trim();
            if (current === wanted) break;

            const currentDate = new Date(`${current} 1`);
            const target = new Date(Date.UTC(year, month - 1, 1));
            await arrows.nth(currentDate.getTime() > target.getTime() ? 0 : 1).click();
            await this.page.waitForTimeout(450);
        }

        // Adjacent-month days share the day-cell class, so the grid is walked
        // from the first cell reading "1" — everything before it belongs to the
        // previous month.
        const cells = this.page.locator('.c-pane-container:visible .c-day-content');
        const texts = (await cells.allTextContents()).map((t) => t.trim());
        const monthStart = texts.indexOf('1');
        const index = texts.findIndex((t, i) => i >= monthStart && t === String(day));

        if (index < 0) throw new Error(`day ${day} not found in the ${wanted} calendar`);

        await cells.nth(index).click();
        await this.page.waitForTimeout(700);
    }

    /** What a date field currently displays. */
    async dateValue(label: string): Promise<string> {
        return this.group(label).locator('input.wperp-form-field').first().inputValue();
    }

    /**
     * Types into a date field without touching the calendar — the path a real
     * user takes and the one ERP-149 breaks. Used by the known-defect guard.
     */
    async typeDate(label: string, isoDate: string): Promise<void> {
        const input = this.group(label).locator('input.wperp-form-field').first();
        await input.click();
        await input.pressSequentially(isoDate, { delay: 25 });
        await this.page.keyboard.press('Escape');
        await this.page.waitForTimeout(400);
    }

    /**
     * Picks an option from a label-anchored vue-multiselect.
     *
     * The list is rendered only while the control is open, and the customer
     * picker filters as you type, so the search term is typed rather than set.
     */
    async pickFromMultiselect(label: string, search: string): Promise<boolean> {
        const box = this.group(label).locator('.multiselect').first();
        await box.click();
        await this.page.waitForTimeout(400);

        const input = box.locator('.multiselect__input');
        if (await input.count()) {
            await input.pressSequentially(search, { delay: 30 });
            await this.page.waitForTimeout(1500);
        }

        const option = box.locator('.multiselect__option', { hasText: search }).first();
        if (!(await option.isVisible().catch(() => false))) return false;

        await option.click();
        await this.page.waitForTimeout(500);

        return true;
    }

    /** The nth line-item row's product picker (rows are positional). */
    async pickLineProduct(index: number, search: string): Promise<boolean> {
        const box = this.page.locator('.multiselect').nth(index + 1);
        await box.click();
        await this.page.waitForTimeout(400);

        const option = box.locator('.multiselect__option', { hasText: search }).first();
        if (!(await option.isVisible().catch(() => false))) return false;

        await option.click();
        await this.page.waitForTimeout(600);

        return true;
    }

    /** Sets quantity on the nth line item. `qty` is the one named field here. */
    async setLineQty(index: number, qty: number): Promise<void> {
        await this.page.locator('input[name="qty"]').nth(index).fill(String(qty));
        await this.page.waitForTimeout(400);
    }

    /** Sets unit price on the nth line item — the number input after `qty`. */
    async setLinePrice(index: number, price: number): Promise<void> {
        const row = this.page.locator('input[name="qty"]').nth(index).locator('xpath=ancestor::tr[1]');
        await row.locator('input[type="number"]').nth(1).fill(String(price));
        await this.page.waitForTimeout(400);
    }

    /** The total the form computes, as a number. */
    async formTotal(): Promise<number> {
        const text = await this.bodyText();
        const match = text.match(/Total[^0-9$]*\$?\s*([0-9,]+\.?[0-9]*)/i);
        return match ? Number(match[1]!.replace(/,/g, '')) : NaN;
    }

    /**
     * Submits a transaction form.
     *
     * Matched on VISIBLE buttons with an exact "Save": the form also ships a
     * hidden `button.btn-fake` labelled "Save as Draft", and a loose
     * `:has-text("Save")` resolves to that one and then times out waiting for it
     * to become visible.
     */
    async save(): Promise<void> {
        await this.page.locator('button:visible').filter({ hasText: /^Save$/ }).first().click();
        await this.page.waitForTimeout(4500);
        await this.settle();
    }

    // ---- receive payment ---------------------------------------------------

    /**
     * The outstanding-invoice rows the payment screen lists once a customer is
     * chosen. Each row's amount input is PRE-FILLED with the full balance, so a
     * settlement in full needs no typing at all.
     */
    async outstandingRows(): Promise<string[]> {
        return (await this.page.locator('table tbody tr').allTextContents())
            .map((t) => t.replace(/\s+/g, ' ').trim())
            .filter((t) => t.startsWith('#'));
    }

    /** Overrides the amount being paid against the nth listed invoice. */
    async setPaymentAmount(index: number, amount: number): Promise<void> {
        await this.page.locator('table tbody tr input[type="number"]').nth(index).fill(String(amount));
        await this.page.waitForTimeout(600);
    }

    /** The screen's own computed total for the payment. */
    async paymentTotal(): Promise<string> {
        return this.page.locator('input[name="finalamount"]').first().inputValue();
    }

    /**
     * Receives a payment against a customer's outstanding invoices.
     *
     * FOUR fields are required, not the two the screen makes obvious:
     * `validateForm()` in `RecPaymentCreate.vue` also demands **Payment Method**
     * and **Deposit to**, and omitting either makes the form refuse silently —
     * no request, and the error panel sits above the fold.
     *
     * `amounts` overrides the invoice rows POSITIONALLY. Left out, every row
     * keeps the full balance the screen pre-fills — which settles all of the
     * customer's outstanding invoices, not just the newest. Pass an explicit
     * array (zeroes included) whenever the test means to pay only some of them.
     */
    async receivePayment(customer: string, paymentDate: string, amounts?: number[]): Promise<boolean> {
        await this.gotoRoute('newPayment');

        if (!(await this.pickFromMultiselect('Customer', customer))) return false;

        // WAIT for the outstanding-invoice rows rather than sleeping: choosing a
        // customer fires `GET /invoices/due/{id}` and the rows paint only when it
        // returns. A fixed delay raced it — the screen showed zero rows and the
        // payment silently covered nothing.
        await this.page
            .locator('table tbody tr input[type="number"]')
            .first()
            .waitFor({ state: 'visible', timeout: 15_000 })
            .catch(() => undefined);
        await this.page.waitForTimeout(600);

        await this.pickDate('Payment Date', paymentDate);

        for (const label of ['Payment Method', 'Deposit to']) {
            const box = this.group(label).locator('.multiselect').first();
            await box.click();
            await this.page.waitForTimeout(800);

            const option = box.locator('.multiselect__option').first();
            if (!(await option.isVisible().catch(() => false))) return false;

            await option.click();
            await this.page.waitForTimeout(600);
        }

        if (amounts) {
            for (const [index, value] of amounts.entries()) await this.setPaymentAmount(index, value);
        }

        await this.save();

        return true;
    }

    // ---- REST -------------------------------------------------------------

    /**
     * Calls an `erp/v1/accounting/v1/*` route with the page's own REST nonce.
     *
     * Accounting is REST-backed (161 routes), unlike Deals which is AJAX-only,
     * so this is the natural oracle for permission and validation cases.
     */
    async callRest(
        path: string,
        options: { method?: string; data?: unknown } = {}
    ): Promise<{ status: number; body: unknown }> {
        return this.page.evaluate(
            async ({ path, options }) => {
                const nonce =
                    (window as unknown as { wpApiSettings?: { nonce: string } }).wpApiSettings?.nonce ??
                    (window as unknown as { erp_acct_var?: { nonce: string } }).erp_acct_var?.nonce ??
                    '';

                const response = await fetch(`/wp-json/erp/v1/accounting/v1${path}`, {
                    method: options.method ?? 'GET',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                    body: options.data ? JSON.stringify(options.data) : undefined,
                });

                const text = await response.text();

                try {
                    return { status: response.status, body: JSON.parse(text) };
                } catch {
                    return { status: response.status, body: text.slice(0, 400) };
                }
            },
            { path, options }
        );
    }
}
