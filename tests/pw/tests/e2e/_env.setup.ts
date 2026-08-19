import { test as setup, expect, request } from '@utils/test';
import { ApiUtils } from '@utils/apiUtils';
import { env } from '@utils/helpers';
import { closeDb, tableCount } from '@utils/dbUtils';
import { setupWooCommerce, seedWooProducts, seedDepartments, seedDesignations, seedEmployees, seedLeavePolicies, seedHolidays, seedCrmContacts, seedAccountingPeople, seedAccountingProducts, ensureFinancialYear } from '@utils/seed';
import { company, employees as seedEmployeeList } from '@utils/seedData';

/**
 * Brings the site to the state the specs expect: WooCommerce open for business,
 * and the demo company (Northwind Analytics) seeded across HR, CRM and
 * Accounting so lists, filters and reports have real data to work on.
 *
 * Every step is idempotent — running setup twice does not double the data.
 */
setup.describe.configure({ mode: 'serial' });

let api: ApiUtils;

setup.beforeAll(async () => {
    api = new ApiUtils(await request.newContext({ baseURL: env('BASE_URL') }));
});

setup.afterAll(async () => {
    await api?.dispose();
    await closeDb();
});

setup('ERP schema is installed', async () => {
    // A missing ERP table means the installer never ran; every downstream
    // failure would then be a misleading UI error rather than the real cause.
    expect(await tableCount('erp_hr_employees'), 'wp_erp_hr_employees exists').toBeGreaterThanOrEqual(0);
    expect(await tableCount('erp_peoples'), 'wp_erp_peoples exists').toBeGreaterThanOrEqual(0);
});

setup('test-helper mu-plugin is reachable', async () => {
    const state = await api.licenseState();
    expect(state, 'erp-pw/v1/license responds').toBeTruthy();
});

setup('WooCommerce is configured and the site is Live', async () => {
    await setupWooCommerce(api);

    const options = await api.getOptions(['woocommerce_coming_soon', 'woocommerce_store_pages_only', 'woocommerce_store_city', 'woocommerce_currency', 'blogname']);

    // 'no' is WooCommerce's Site Visibility = Live. A fresh wp-env install ships
    // 'yes', which hides the entire storefront behind a coming-soon page.
    expect(options.woocommerce_coming_soon, 'Site Visibility is Live').toBe('no');
    expect(options.woocommerce_store_pages_only, 'store pages are not restricted').toBe('no');
    expect(options.woocommerce_store_city, 'store address is set').toBe(company.city);
    expect(options.blogname, 'site is branded as the demo company').toBe(company.name);
});

setup('storefront renders the shop rather than a coming-soon page', async ({ page }) => {
    const response = await page.goto('/?post_type=product');
    expect(response?.status(), 'storefront responds').toBeLessThan(400);

    const body = (await page.locator('body').textContent()) ?? '';
    expect(body, 'no coming-soon placeholder').not.toMatch(/coming soon|pardon our dust/i);
});

setup('WooCommerce catalogue is seeded', async () => {
    await seedWooProducts(api);
    const products = await api.getJson<unknown[]>('/wc/v3/products', undefined, { per_page: 100 });
    expect(products.length, 'storefront has products').toBeGreaterThanOrEqual(8);
});

setup('HR is seeded with the demo company', async () => {
    const departments = await seedDepartments(api);
    const designations = await seedDesignations(api);

    expect(departments.size, 'departments seeded').toBeGreaterThanOrEqual(8);
    expect(designations.size, 'designations seeded').toBeGreaterThanOrEqual(20);

    await seedEmployees(api, departments, designations);

    const employees = await api.getJson<unknown[]>('/erp/v1/hrm/employees', undefined, { per_page: 100 });
    expect(employees.length, 'employees seeded').toBeGreaterThanOrEqual(seedEmployeeList.length);
});

setup('a financial year exists', async () => {
    const id = await ensureFinancialYear();
    expect(id, 'financial year id').toBeGreaterThan(0);
});

setup('holidays are seeded', async () => {
    await seedHolidays(api);
    expect(await tableCount('erp_hr_holiday'), 'holidays seeded').toBeGreaterThanOrEqual(6);
});

setup('leave policies are seeded', async () => {
    await seedLeavePolicies(api);

    // Asserted against the table, not the create response: POST
    // erp/v1/hrm/leaves/policies answers 201 with an empty record even when the
    // insert fails (create_policy never checks the WP_Error from
    // erp_hr_leave_insert_policy). Reported as a product defect — trusting its
    // status code here would make a zero-row seed look successful.
    expect(await tableCount('erp_hr_leave_policies'), 'leave policies seeded').toBeGreaterThanOrEqual(6);
});

setup('CRM contacts are seeded', async () => {
    await seedCrmContacts(api);

    // Counted in the DB, not over REST: GET erp/v1/crm/contacts dies inside
    // ContactsController::prepare_item_for_response() (a stray wp_send_json at
    // includes/API/ContactsController.php:378), so it never returns a collection.
    // Reported as a product defect; asserting through it here would be asserting
    // on a broken endpoint.
    const contacts = await tableCount('erp_peoples', "email LIKE '%@beacon-retail.test' OR email LIKE '%@harbourline.test' OR email LIKE '%@verdantfoods.test' OR email LIKE '%@kestrel-mfg.test' OR email LIKE '%@lumenhealth.test' OR email LIKE '%@sablefinch.test'");
    expect(contacts, 'CRM contacts seeded').toBeGreaterThanOrEqual(8);
});

setup('Accounting people and products are seeded', async () => {
    await seedAccountingPeople(api);
    await seedAccountingProducts(api);

    const customers = await api.getJson<unknown[]>('/erp/v1/accounting/v1/customers', undefined, { per_page: 100 });
    expect(customers.length, 'accounting customers seeded').toBeGreaterThanOrEqual(6);
});
