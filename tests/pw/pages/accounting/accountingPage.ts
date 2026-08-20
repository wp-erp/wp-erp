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
    trialBalance: '/reports/trial-balance',
    incomeStatement: '/reports/income-statement',
    balanceSheet: '/reports/balance-sheet',
    ledgerReport: '/reports/ledgers',
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
    trialBalance: 'Trial Balance',
    incomeStatement: 'Income Statement',
    balanceSheet: 'Balance Sheet',
    ledgerReport: 'Ledger Report',
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
        const current = this.page.url();

        // Three cases, and the third one is the trap. Arriving from elsewhere
        // needs a real navigation. Moving BETWEEN routes inside the SPA only
        // needs the hash and a `hashchange`. But navigating to the route the app
        // is ALREADY on does neither: the hash does not change, Vue keeps the
        // same component instance, and the screen keeps whatever the previous
        // test left in it — a half-filled form whose account lists are never
        // refetched. That surfaced as `receivePayment()` returning false on the
        // last test of a file and reading as a broken picker.
        const alreadyOnRoute = current.includes('page=erp-accounting') && current.endsWith(`#${hash}`);

        if (!current.includes('page=erp-accounting') || alreadyOnRoute) {
            await this.page.goto(`${base}#${hash}`, { waitUntil: 'domcontentloaded' });
            if (alreadyOnRoute) await this.page.reload({ waitUntil: 'domcontentloaded' });
        } else {
            await this.page.evaluate((h) => {
                window.location.hash = h;
                window.dispatchEvent(new HashChangeEvent('hashchange'));
            }, hash);
        }

        await this.settle();
    }

    /**
     * Opens the edit screen for an existing payment.
     *
     * Not in `accountingRoutes` because the path carries the payment's voucher
     * number: `#/payments/{voucherNo}/edit`. The SPA builds the component on
     * `created()` only, so a hash change alone leaves whatever the previous
     * route rendered still on screen — this always does a real load followed by
     * a reload, which is the only sequence that re-runs `prepareDataLoad()`.
     */
    async gotoPaymentEdit(voucherNo: number): Promise<void> {
        const url = `/wp-admin/admin.php?page=erp-accounting#/payments/${voucherNo}/edit`;

        await this.page.goto(url, { waitUntil: 'domcontentloaded' });
        await this.page.reload({ waitUntil: 'domcontentloaded' });

        await this.settle();
    }

    /**
     * Waits for the SPA to finish painting the current route.
     *
     * Waits for the route's own CONTENT — a heading with text — not just a
     * spinner and a delay. A full reload (which `gotoRoute()` now does when
     * re-entering the route the app is already on) takes longer than a hash
     * change, and a fixed pause let form interactions start against a
     * half-rendered screen: the save then failed validation silently and the
     * assertion read as "the record was never created".
     */
    async settle(): Promise<void> {
        await this.page.waitForLoadState('domcontentloaded');

        await this.page
            .locator(accountingSelectors.spinner)
            .first()
            .waitFor({ state: 'hidden', timeout: 10_000 })
            .catch(() => undefined);

        // A heading with actual text means the Vue route has rendered.
        await this.page
            .locator('#wpbody-content h1, #wpbody-content h2, #wpbody-content h3')
            .filter({ hasText: /\S/ })
            .first()
            .waitFor({ state: 'visible', timeout: 20_000 })
            .catch(() => undefined);

        await this.page.waitForTimeout(1500);
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
    /**
     * Opens a label-anchored multiselect and picks its first option, WAITING for
     * the options to render rather than sleeping.
     *
     * The account and payment-method lists are populated from AJAX, so a fixed
     * delay is a race: it held under a single worker and failed under four,
     * where `payBill()` silently returned false because the list was still
     * empty and the case read as a broken form.
     */
    private async pickFirstOption(label: string): Promise<boolean> {
        // RETRIED, because a single click is not reliable here under load. The
        // account and payment-method lists come from AJAX and the panel reflows
        // as they arrive, so the click that opens the dropdown can land on a
        // moved element and silently do nothing — which showed up only under
        // four workers, as `payBill()`/`receivePayment()` returning false and the
        // case reading as a broken form.
        const box = this.group(label).locator('.multiselect').first();
        const option = box.locator('.multiselect__option').first();

        for (let attempt = 0; attempt < 3; attempt++) {
            await box.click().catch(() => undefined);

            try {
                await option.waitFor({ state: 'visible', timeout: 6_000 });
            } catch {
                await this.page.waitForTimeout(800);
                continue;
            }

            await option.click();
            await this.page.waitForTimeout(500);

            return true;
        }

        return false;
    }

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

    /**
     * What a labelled picker currently shows.
     *
     * vue-multiselect renders the chosen value and the placeholder through the
     * same box, so this returns whichever is painted — "Harbourline Logistics"
     * on a populated form, "Please search" on an empty one. That distinction is
     * the whole assertion when the question is whether a screen loaded its own
     * record.
     */
    async multiselectValue(label: string): Promise<string> {
        const box = this.group(label).locator('.multiselect').first();

        return (await box.innerText()).replace(/\s+/g, ' ').trim();
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

    // ---- bills ---------------------------------------------------------------
    //
    // A bill's line items are LEDGER ACCOUNTS, not products — the vendor side
    // charges expense accounts directly — and the amount is
    // `input[name="amount"]`, a TEXT field, unlike the invoice's numeric `qty`.

    /** Picks the expense account on the nth bill line. */
    async pickLineAccount(index: number, account: string): Promise<boolean> {
        const box = this.page.locator('.multiselect').nth(index + 1);
        await box.click();
        await this.page.waitForTimeout(900);

        const option = box.locator('.multiselect__option', { hasText: account }).first();
        if (!(await option.isVisible().catch(() => false))) return false;

        await option.click();
        await this.page.waitForTimeout(600);

        return true;
    }

    /**
     * Sets the amount charged on the nth bill line.
     *
     * TYPED, not filled. The grand total is recomputed by
     * `@keyup="updateFinalAmount"` (`BillCreate.vue:73`), and `fill()` dispatches
     * `input` but never `keyup` — so the line's own value updates while
     * `finalTotalAmount` stays 0 and the form refuses with "Total amount can't be
     * zero" next to a line that visibly shows the amount.
     */
    async setLineAmount(index: number, amount: number): Promise<void> {
        const field = this.page.locator('table tbody tr input[name="amount"]').nth(index);

        await field.click();
        await field.pressSequentially(String(amount), { delay: 40 });
        await this.page.keyboard.press('Tab');
        await this.page.waitForTimeout(700);
    }

    /** Bill lines the BROWSER has marked invalid (an account with no amount). */
    async invalidAmountCount(): Promise<number> {
        return this.page.locator('table tbody tr input[name="amount"]:invalid').count();
    }

    /** The browser's own message for the first invalid amount field. */
    async amountValidationMessage(): Promise<string> {
        return this.page
            .locator('table tbody tr input[name="amount"]')
            .first()
            .evaluate((element) => (element as HTMLInputElement).validationMessage);
    }

    /** The grand total the bill form has computed, as shown in its footer. */
    async billTotal(): Promise<string> {
        return this.page.locator('input[name="finalamount"]').first().inputValue();
    }

    /**
     * Raises a bill against a vendor for a single expense line.
     *
     * Required fields mirror the invoice: Pay To, Bill Date, Due Date, plus a
     * line with an account and a non-zero amount.
     */
    async createBill(vendor: string, account: string, amount: number, billDate: string, dueDate: string): Promise<boolean> {
        await this.gotoRoute('newBill');

        if (!(await this.pickFromMultiselect('Pay To', vendor))) return false;

        await this.pickDate('Bill Date', billDate);
        await this.pickDate('Due Date', dueDate);

        if (!(await this.pickLineAccount(0, account))) return false;

        await this.setLineAmount(0, amount);
        await this.save();

        return true;
    }

    /**
     * Pays a vendor's outstanding bills.
     *
     * Mirrors `receivePayment()` but the fields are named differently — the
     * vendor picker is **Pay To** (the same label the bill form uses for it) and
     * the funding account is **Transaction From**, not "Deposit to".
     *
     * The outstanding-bill rows are pre-filled with each bill's full balance,
     * exactly like the customer side.
     */
    async payBill(vendor: string, paymentDate: string): Promise<boolean> {
        await this.gotoRoute('newPayBill');

        if (!(await this.pickFromMultiselect('Pay To', vendor))) return false;

        await this.page
            .locator('table tbody tr')
            .first()
            .waitFor({ state: 'visible', timeout: 15_000 })
            .catch(() => undefined);
        await this.page.waitForTimeout(1200);

        await this.pickDate('Payment Date', paymentDate);

        for (const label of ['Payment Method', 'Transaction From']) {
            if (!(await this.pickFirstOption(label))) return false;
        }

        await this.save();

        return true;
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
            if (!(await this.pickFirstOption(label))) return false;
        }

        if (amounts) {
            for (const [index, value] of amounts.entries()) await this.setPaymentAmount(index, value);
        }

        await this.save();

        return true;
    }

    /**
     * Sends `PUT /accounting/v1/payments/{voucherNo}` — the update the edit
     * screen is supposed to send and never does.
     *
     * The request goes through the page rather than a Playwright API context so
     * it carries the SPA's own REST nonce (`erp_acct_var.rest.nonce`) and the
     * admin's cookies: the same credentials the screen would have used. Only the
     * fields the payment form sends are included, so the payload is the one the
     * server actually has to handle.
     */
    async updatePaymentViaRest(
        voucherNo: number,
        payload: {
            customerId: number;
            trnDate: string;
            depositTo: number;
            lineItems: { invoiceNo: number; lineTotal: number }[];
            particulars?: string;
        }
    ): Promise<{ status: number; body: string }> {
        return this.page.evaluate(
            async ({ voucherNo, payload }) => {
                const v = (window as unknown as { erp_acct_var: { rest: { root: string; version: string; nonce: string } } })
                    .erp_acct_var;

                const response = await fetch(`${v.rest.root}${v.rest.version}/accounting/v1/payments/${voucherNo}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': v.rest.nonce },
                    body: JSON.stringify({
                        customer_id: payload.customerId,
                        trn_date: payload.trnDate,
                        type: 'payment',
                        status: 4,
                        particulars: payload.particulars ?? 'edited',
                        deposit_to: payload.depositTo,
                        trn_by: 1,
                        check_no: 0,
                        bank_trn_charge: 0,
                        line_items: payload.lineItems.map((item) => ({
                            id: 0,
                            invoice_no: item.invoiceNo,
                            due_date: payload.trnDate,
                            amount: item.lineTotal,
                            due: item.lineTotal,
                            line_total: item.lineTotal,
                        })),
                    }),
                });

                return { status: response.status, body: (await response.text()).slice(0, 500) };
            },
            { voucherNo, payload }
        );
    }

    // ---- reports -------------------------------------------------------------
    //
    // Every figure is rendered as "<label> Dr./Cr. $1,800.00". The reports carry
    // no ids, so amounts are read out of the tables' text — which is also what a
    // human reads, so an assertion that passes here is an assertion about what
    // the accountant actually sees.

    /** Flattened text of every table on the current report. */
    async reportText(): Promise<string> {
        const tables = await this.page.locator('table').allTextContents();
        return tables.join(' | ').replace(/\s+/g, ' ').trim();
    }

    /**
     * The amount printed against a label, as a number.
     *
     * `Dr.`/`Cr.` is deliberately NOT folded into the sign: the reports use it as
     * a presentational marker and each caller knows which side it expects. The
     * sign is returned as printed, so a caller comparing two figures compares
     * like with like.
     */
    async reportFigure(label: string): Promise<number> {
        const text = await this.reportText();
        const pattern = new RegExp(`${label.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\s*(?:Dr\\.|Cr\\.)?\\s*\\$([0-9,]+\\.?[0-9]*)`, 'i');
        const match = text.match(pattern);

        if (!match) throw new Error(`no amount printed for "${label}" in: ${text.slice(0, 300)}`);

        return Number(match[1]!.replace(/,/g, ''));
    }

    /** True when the report prints the label at all. */
    async reportHas(label: string): Promise<boolean> {
        return (await this.reportText()).includes(label);
    }

    /**
     * The trial balance's own totals row — the pair that must agree.
     *
     * Rendered as "Total $2,200.00 $2,200.00", so both are taken from one match
     * rather than two lookups that could land on different rows.
     */
    async trialBalanceTotals(): Promise<{ debit: number; credit: number }> {
        const text = await this.reportText();
        const match = text.match(/Total\s*\$([0-9,]+\.?[0-9]*)\s*\$([0-9,]+\.?[0-9]*)/);

        if (!match) throw new Error(`no trial balance totals row in: ${text.slice(0, 300)}`);

        return { debit: Number(match[1]!.replace(/,/g, '')), credit: Number(match[2]!.replace(/,/g, '')) };
    }

    /** The balance sheet's closing equation, as the screen states it. */
    async balanceSheetEquation(): Promise<{ assets: number; liabilitiesPlusEquity: number }> {
        const text = await this.reportText();
        const match = text.match(
            /Assets\s*=\s*(?:Dr\.|Cr\.)?\s*\$([0-9,]+\.?[0-9]*)\s*(?:\|)?\s*Liability \+ Equity\s*=\s*(?:Dr\.|Cr\.)?\s*\$([0-9,]+\.?[0-9]*)/
        );

        if (!match) throw new Error(`no balance sheet equation in: ${text.slice(0, 400)}`);

        return {
            assets: Number(match[1]!.replace(/,/g, '')),
            liabilitiesPlusEquity: Number(match[2]!.replace(/,/g, '')),
        };
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
