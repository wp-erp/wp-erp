import type { Page } from '@utils/test';
import { expect } from '@utils/test';
import { toPath } from '@utils/helpers';
import { ApiUtils } from '@utils/apiUtils';
import { endPoints } from '@utils/apiEndPoints';
import { data } from '@utils/testData';
import type { IdMap } from '@utils/interfaces';

/**
 * Feature-isolated page object for the WP ERP Accounting module.
 *
 * The admin UI (`admin.php?page=erp-accounting`) is a Vue SPA mounted on a single
 * shell `<div id="erp-accounting">` with client-side hash routing (`#/...`).
 * Stable DOM ids live inside the JS bundle, so UI methods are kept smoke-level:
 * navigate to a hash route, wait for the app to mount, and assert headings /
 * key controls. Transaction depth (double-entry, reconciliation) is exercised
 * over REST in the API spec where it can be asserted to the cent.
 *
 * Routes verified against the QA guide and `wp-erp/tests/acceptance/Scenario/*Cest.php`.
 */
export class AccountingPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    // ── Selectors / URLs grouped by area ──────────────────────────────────────
    readonly admin = {
        // SPA shell (server only renders this empty container).
        appRoot: '#erp-accounting',

        // Top admin page + hash routes (see guide §2).
        dashboardUrl: toPath('wp-admin/admin.php?page=erp-accounting#/dashboard'),
        customersUrl: toPath('wp-admin/admin.php?page=erp-accounting#/users/customers'),
        vendorsUrl: toPath('wp-admin/admin.php?page=erp-accounting#/users/vendors'),
        salesUrl: toPath('wp-admin/admin.php?page=erp-accounting#/sales'),
        invoiceNewUrl: toPath('wp-admin/admin.php?page=erp-accounting#/invoices/new'),
        expensesUrl: toPath('wp-admin/admin.php?page=erp-accounting#/transactions/expenses'),
        journalsUrl: toPath('wp-admin/admin.php?page=erp-accounting#/transactions/journals'),
        chartsUrl: toPath('wp-admin/admin.php?page=erp-accounting#/charts'),
        banksUrl: toPath('wp-admin/admin.php?page=erp-accounting#/banks'),
        productsUrl: toPath('wp-admin/admin.php?page=erp-accounting#/products/product-service'),
        reportsUrl: toPath('wp-admin/admin.php?page=erp-accounting#/reports'),

        // Customer/Vendor add modal (ids confirmed from guide §5.1 + people modal).
        addCustomerBtnText: 'Add New Customer',
        addVendorBtnText: 'Add New Vendor',
        customerModal: '#wperp-add-customer-modal',
        firstName: '#first_name',
        lastName: '#last_name',
        email: '#email',
        phone: '#phone',
        company: '#company',

        // Product add modal (ids confirmed from acceptance Cest 03AddProductCest).
        addProductBtnText: 'Add New Product',
        productModal: '#wperp-product-modal',
        costPrice: '#cost-price',
        salePrice: '#sale-price',

        // Generic SPA controls.
        saveBtnText: 'Save',
    };

    // ── High-level UI helpers (smoke-level for the SPA) ───────────────────────

    /** Hard-navigate to a hash route and wait for the SPA shell to mount. */
    async goto(url: string): Promise<void> {
        await this.page.goto(url);
        // Wait for the Vue app shell to mount and render its first view.
        await expect(this.page.locator(this.admin.appRoot)).toBeVisible({ timeout: 30_000 });
    }

    async goToDashboard(): Promise<void> {
        await this.goto(this.admin.dashboardUrl);
    }

    async goToCustomers(): Promise<void> {
        await this.goto(this.admin.customersUrl);
    }

    async goToVendors(): Promise<void> {
        await this.goto(this.admin.vendorsUrl);
    }

    async goToSales(): Promise<void> {
        await this.goto(this.admin.salesUrl);
    }

    async goToProducts(): Promise<void> {
        await this.goto(this.admin.productsUrl);
    }

    async goToReports(): Promise<void> {
        await this.goto(this.admin.reportsUrl);
    }

    /** True when the page shows a WP/PHP fatal — used as a smoke oracle. */
    async hasCriticalError(): Promise<boolean> {
        const body = (await this.page.locator('body').innerText().catch(() => '')) ?? '';
        return /critical error|Fatal error|There has been a critical error/i.test(body);
    }

    /**
     * Open the "Add New Customer" modal and fill the basic fields. Kept resilient:
     * if the SPA renders a differently-id'd modal we still surface a clear failure.
     */
    async openAddCustomerModal(): Promise<void> {
        await this.page.getByRole('button', { name: this.admin.addCustomerBtnText }).first().click();
        await expect(this.page.locator(this.admin.customerModal)).toBeVisible({ timeout: 15_000 });
    }

    // ── Seeding ───────────────────────────────────────────────────────────────

    /**
     * Ensure the accounting module is usable, then create the core fixtures via
     * REST and return their IDs. Resilient by design: each create is wrapped so a
     * single failure still returns whatever was successfully seeded.
     *
     * Returns an IdMap with uppercase keys matching the .env placeholders:
     *   CUSTOMER_ID, VENDOR_ID, ACCT_PRODUCT_ID, INVOICE_ID.
     */
    static async seed(api: ApiUtils): Promise<IdMap> {
        const ids: IdMap = {};

        // Customer — first_name/last_name/email are the required fields.
        try {
            const [, customerId] = await api.create(endPoints.acctCustomers, data.accounting.customer());
            if (customerId) ids.CUSTOMER_ID = customerId;
        } catch {
            /* keep going — return partial IDs */
        }

        // Vendor — same required shape, type=vendor is set server-side.
        try {
            const [, vendorId] = await api.create(endPoints.acctVendors, data.accounting.vendor());
            if (vendorId) ids.VENDOR_ID = vendorId;
        } catch {
            /* ignore */
        }

        // Product — name + cost/sale price. The products controller nests the
        // created record under `id` (the real id is at id.id), so extract defensively.
        try {
            const [, body] = await api.post(endPoints.acctProducts, { data: data.accounting.product() });
            const productId = body?.id && typeof body.id === 'object' ? String(body.id.id ?? '') : String(body?.id ?? '');
            if (productId && productId !== 'undefined') ids.ACCT_PRODUCT_ID = productId;
        } catch {
            /* ignore */
        }

        // Invoice — needs a customer and at least one line item. We post a draft
        // (status 1) so the books are not touched by the fixture; depth tests in
        // the API spec create fully-posted invoices on purpose.
        if (ids.CUSTOMER_ID) {
            try {
                const amount = data.accounting.invoiceAmount();
                const payload = AccountingPage.invoicePayload(ids.CUSTOMER_ID, amount, {
                    productId: ids.ACCT_PRODUCT_ID,
                    status: 1,
                });
                const [, invoiceId] = await api.create(endPoints.acctInvoices, payload);
                if (invoiceId) ids.INVOICE_ID = invoiceId;
            } catch {
                /* ignore */
            }
        }

        return ids;
    }

    /**
     * Build a minimal valid invoice payload (single line item).
     * Shape verified against InvoicesController::create_invoice and
     * erp_acct_insert_invoice_details_and_tax (product_id/qty/unit_price/discount/tax).
     */
    static invoicePayload(
        customerId: string | number,
        unitPrice: number,
        opts: { productId?: string; qty?: number; tax?: number; discount?: number; status?: number; estimate?: number } = {},
    ): Record<string, unknown> {
        const qty = opts.qty ?? 1;
        return {
            customer_id: Number(customerId),
            date: '2025-01-15',
            due_date: '2025-02-15',
            billing_address: 'PW Billing Address',
            discount_type: 0,
            tax_rate_id: 0,
            estimate: opts.estimate ?? 0,
            status: opts.status ?? 2,
            attachments: '',
            line_items: [
                {
                    product_id: opts.productId ? Number(opts.productId) : 0,
                    qty,
                    unit_price: unitPrice,
                    discount: opts.discount ?? 0,
                    tax: opts.tax ?? 0,
                    tax_cat_id: 0,
                    item_total: qty * unitPrice,
                },
            ],
        };
    }
}
