/**
 * Seeds the site with the demo company in `seedData.ts`.
 *
 * Two goals, in order:
 *   1. Give the specs real data to assert against — lists that paginate, filters
 *      that narrow, reports that have something to report.
 *   2. Make the site presentable. A bug-report screenshot of "Northwind
 *      Analytics" reads as a customer site; one full of `test_x7f2` does not.
 *
 * Every route and payload shape below was probed against the running site
 * before it was written here — see `harness/routes.json`.
 *
 * Idempotent: each seeder lists what already exists and creates only the gap,
 * so running setup twice does not double the data.
 */
import { ApiUtils } from '@utils/apiUtils';
import { endPoints } from '@utils/apiEndPoints';
import { query, execute, prefix } from '@utils/dbUtils';
import * as data from '@utils/seedData';
import type { RowDataPacket } from 'mysql2/promise';

type Named = { id?: number | string; title?: string; name?: string; email?: string };

const byTitle = (rows: Named[]) => new Map(rows.map((r) => [String(r.title ?? r.name ?? '').toLowerCase(), r.id]));
const byEmail = (rows: Named[]) => new Map(rows.map((r) => [String(r.email ?? '').toLowerCase(), r.id]));

/** Reads a list endpoint, tolerating the shapes ERP returns (array or {data}). */
async function list(api: ApiUtils, route: string): Promise<Named[]> {
    try {
        const body = await api.getJson<unknown>(route, undefined, { per_page: 100 });
        if (Array.isArray(body)) return body as Named[];
        if (body && typeof body === 'object' && Array.isArray((body as { data?: unknown }).data)) return (body as { data: Named[] }).data;
        return [];
    } catch {
        return [];
    }
}

// ---------------------------------------------------------------------------
// WordPress + WooCommerce
// ---------------------------------------------------------------------------

/**
 * Puts WooCommerce into a real, open-for-business state.
 *
 * The one that matters most: `woocommerce_coming_soon = 'no'` is WooCommerce's
 * **Site Visibility = Live**. A fresh wp-env install ships `'yes'`, which hides
 * the whole storefront behind a coming-soon page — every front-end spec would
 * then be testing that placeholder.
 */
export async function setupWooCommerce(api: ApiUtils): Promise<void> {
    const { wooStore, company } = data;

    await api.setOptions({
        // Site Visibility → Live
        woocommerce_coming_soon: 'no',
        woocommerce_store_pages_only: 'no',
        woocommerce_private_link: 'no',

        // Store identity
        woocommerce_store_address: wooStore.address,
        woocommerce_store_address_2: wooStore.address2,
        woocommerce_store_city: wooStore.city,
        woocommerce_store_postcode: wooStore.postcode,
        woocommerce_default_country: wooStore.country,
        woocommerce_currency: wooStore.currency,
        woocommerce_weight_unit: wooStore.weightUnit,
        woocommerce_dimension_unit: wooStore.dimensionUnit,
        woocommerce_allowed_countries: wooStore.sellingLocation,

        // Skip the onboarding wizard so wp-admin never redirects mid-test
        woocommerce_onboarding_profile: { completed: true, skipped: true, business_choice: 'im_already_selling' },
        woocommerce_task_list_hidden: 'yes',
        woocommerce_task_list_welcome_modal_dismissed: 'yes',
        woocommerce_show_marketplace_suggestions: 'no',

        // Site identity, so the admin bar and e-mails read like a real company
        blogname: company.name,
        blogdescription: 'Analytics for teams that need answers today',

        // Decline the Appsero diagnostics opt-in. It renders a banner above every
        // ERP screen, which pushes real content down and lands in every
        // screenshot a bug report attaches.
        // Appsero keys these off the client SLUG, so the prefix is `wp-erp_`,
        // not `erp_`. Setting the `erp_`-prefixed pair leaves the banner up.
        'wp-erp_allow_tracking': 'no',
        'wp-erp_tracking_notice': 'hide',
        'wp-erp_tracking_skipped': 1,
        'wp-erp_tracker_optout': 1,
    });
}

/** Creates the storefront catalogue. Uses the WooCommerce REST API. */
export async function seedWooProducts(api: ApiUtils): Promise<number> {
    const existing = await list(api, '/wc/v3/products');
    const have = new Set(existing.map((p) => String(p.name ?? '').toLowerCase()));
    let created = 0;

    for (const p of data.wooProducts) {
        if (have.has(p.name.toLowerCase())) continue;

        await api.post('/wc/v3/products', {
            name: p.name,
            type: 'simple',
            regular_price: p.price,
            sku: p.sku,
            description: p.description,
            short_description: p.description,
            status: 'publish',
            catalog_visibility: 'visible',
            manage_stock: false,
            categories: [{ name: p.category }],
        });
        created++;
    }

    return created;
}

// ---------------------------------------------------------------------------
// ERP — HR
// ---------------------------------------------------------------------------

export async function seedDepartments(api: ApiUtils): Promise<Map<string, number>> {
    const existing = byTitle(await list(api, endPoints.hrm.departments));
    const ids = new Map<string, number>();

    for (const d of data.departments) {
        const key = d.title.toLowerCase();
        if (existing.has(key)) {
            ids.set(d.title, Number(existing.get(key)));
            continue;
        }
        const row = await api.postJson<{ id: number }>(endPoints.hrm.departments, { title: d.title, description: d.description });
        ids.set(d.title, row.id);
    }

    return ids;
}

export async function seedDesignations(api: ApiUtils): Promise<Map<string, number>> {
    const existing = byTitle(await list(api, endPoints.hrm.designations));
    const ids = new Map<string, number>();

    for (const title of data.designations) {
        const key = title.toLowerCase();
        if (existing.has(key)) {
            ids.set(title, Number(existing.get(key)));
            continue;
        }
        const row = await api.postJson<{ id: number }>(endPoints.hrm.designations, { title, description: `${title} at ${data.company.name}.` });
        ids.set(title, row.id);
    }

    return ids;
}

export async function seedEmployees(api: ApiUtils, depts: Map<string, number>, desigs: Map<string, number>): Promise<number> {
    const existing = byEmail(await list(api, endPoints.hrm.employees));
    let created = 0;

    for (const e of data.employees) {
        if (existing.has(e.email.toLowerCase())) continue;

        // Flat payload: EmployeesController maps `email` -> user_email and the
        // work fields into `work[...]` itself (see prepare_item_for_database).
        await api.post(endPoints.hrm.employees, {
            first_name: e.firstName,
            last_name: e.lastName,
            email: e.email,
            department: depts.get(e.department) ?? 0,
            designation: desigs.get(e.designation) ?? 0,
            hiring_date: e.hiringDate,
            hiring_source: e.hiringSource,
            type: e.type,
            status: e.status,
            pay_rate: e.payRate,
            pay_type: e.payType,
            gender: e.gender,
            mobile: e.mobile,
        });
        created++;
    }

    return created;
}

/**
 * ERP ties leave policies and entitlements to a financial year, and a fresh
 * install has none — `wp_erp_hr_financial_years` is empty, so every policy
 * insert fails. Setup creates the current year first.
 */
export async function ensureFinancialYear(): Promise<number> {
    const year = new Date().getFullYear();
    const name = `${year}`;

    const rows = await query<RowDataPacket[]>(`SELECT id FROM ${prefix()}erp_hr_financial_years WHERE fy_name = ? LIMIT 1`, [name]);
    if (rows.length) return Number(rows[0]!.id);

    // start_date/end_date are int(11) UNIX timestamps in this table, not DATEs.
    const start = Math.floor(Date.UTC(year, 0, 1) / 1000);
    const end = Math.floor(Date.UTC(year, 11, 31) / 1000);
    const now = Math.floor(Date.now() / 1000);

    const result = await execute(
        `INSERT INTO ${prefix()}erp_hr_financial_years (fy_name, start_date, end_date, description, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?)`,
        [name, start, end, `Financial year ${year}`, 1, now]
    );

    return result.insertId;
}

/**
 * Leave policies are seeded directly into the schema, not over REST.
 *
 * `POST erp/v1/hrm/leaves/policies` cannot create one: the controller's
 * `prepare_item_for_database()` never resolves the `leave_id` that
 * `erp_hr_leave_insert_policy()` requires (wp-erp 1.17.8), so the insert always
 * fails — and `create_policy()` still answers **201 Created** with an empty
 * record because it never checks the returned WP_Error. Reported as a product
 * defect; seeding through it would produce a site with no leave policies while
 * looking successful.
 *
 * A policy is two rows: the leave TYPE (`wp_erp_hr_leaves`) and the policy that
 * scopes it (`wp_erp_hr_leave_policies`).
 */
export async function seedLeavePolicies(_api: ApiUtils): Promise<number> {
    const financialYear = await ensureFinancialYear();
    const now = Math.floor(Date.now() / 1000);

    const existing = new Set(
        (
            await query<RowDataPacket[]>(
                `SELECT l.name FROM ${prefix()}erp_hr_leave_policies p JOIN ${prefix()}erp_hr_leaves l ON l.id = p.leave_id`
            )
        ).map((r) => String(r.name ?? '').toLowerCase())
    );

    let created = 0;

    for (const p of data.leavePolicies) {
        if (existing.has(p.name.toLowerCase())) continue;

        const types = await query<RowDataPacket[]>(`SELECT id FROM ${prefix()}erp_hr_leaves WHERE name = ? LIMIT 1`, [p.name]);
        const leaveId = types.length
            ? Number(types[0]!.id)
            : (await execute(`INSERT INTO ${prefix()}erp_hr_leaves (name, description, created_at) VALUES (?, ?, ?)`, [p.name, p.description, now])).insertId;

        await execute(
            `INSERT INTO ${prefix()}erp_hr_leave_policies
                (leave_id, description, days, color, employee_type, department_id, location_id, designation_id, gender, marital, f_year, apply_for_new_users, created_at)
             VALUES (?, ?, ?, ?, '-1', -1, -1, -1, '-1', '-1', ?, 1, ?)`,
            [leaveId, p.description, p.days, '#4CAF50', financialYear, now]
        );

        created++;
    }

    return created;
}

export async function seedHolidays(api: ApiUtils): Promise<number> {
    const year = new Date().getFullYear();

    // `start`/`end` are TIMESTAMP columns with defaults, so a holiday can exist
    // with a zero date if it was ever posted with the wrong parameter names —
    // the controller reads `name`/`start_date`/`end_date`, not `title`/`start`/`end`.
    await execute(`DELETE FROM ${prefix()}erp_hr_holiday WHERE start = '0000-00-00 00:00:00'`);

    const existing = new Set((await query<RowDataPacket[]>(`SELECT title FROM ${prefix()}erp_hr_holiday`)).map((r) => String(r.title ?? '').toLowerCase()));
    let created = 0;

    for (const h of data.holidays) {
        if (existing.has(h.title.toLowerCase())) continue;

        // The controller reads `name`/`start_date`/`end_date` and maps them to
        // the title/start/end columns (HolidaysController prepare_item_for_database).
        const response = await api.post(endPoints.hrm.holidays, {
            name: h.title,
            start_date: `${year}-${h.start}`,
            end_date: `${year}-${h.end}`,
            description: `${h.title} — company holiday.`,
        });
        if (response.ok()) created++;
    }

    return created;
}

// ---------------------------------------------------------------------------
// ERP — CRM
// ---------------------------------------------------------------------------

/**
 * Contacts already on the site, read from the DB rather than from
 * `GET erp/v1/crm/contacts`.
 *
 * That endpoint cannot be used here: `ContactsController::prepare_item_for_response()`
 * opens with a stray `wp_send_json( $item )` (wp-erp 1.17.8,
 * includes/API/ContactsController.php:378), which echoes one raw row and dies,
 * so the collection response is never built. Reported — not worked around in
 * product code, only here, so seeding stays idempotent.
 */
async function existingContactEmails(): Promise<Set<string>> {
    const rows = await query<RowDataPacket[]>(
        `SELECT p.email FROM ${prefix()}erp_peoples p
           JOIN ${prefix()}erp_people_type_relations r ON r.people_id = p.id
           JOIN ${prefix()}erp_people_types t ON t.id = r.people_types_id
          WHERE t.name = 'contact'`
    );
    return new Set(rows.map((r) => String(r.email ?? '').toLowerCase()));
}

export async function seedCrmContacts(api: ApiUtils): Promise<number> {
    const existing = await existingContactEmails();
    let created = 0;

    for (const c of data.crmContacts) {
        if (existing.has(c.email.toLowerCase())) continue;

        // `owner` is required by the controller; it is the CRM agent user id.
        await api.post(endPoints.crm.contacts, {
            first_name: c.firstName,
            last_name: c.lastName,
            email: c.email,
            phone: c.phone,
            company: c.company,
            type: 'contact',
            life_stage: c.lifeStage,
            owner: 1,
        });
        created++;
    }

    return created;
}

// ---------------------------------------------------------------------------
// ERP — Accounting
// ---------------------------------------------------------------------------

export async function seedAccountingPeople(api: ApiUtils): Promise<{ customers: number; vendors: number }> {
    const customers = byEmail(await list(api, endPoints.accounting.customers));
    const vendors = byEmail(await list(api, endPoints.accounting.vendors));
    let c = 0;
    let v = 0;

    for (const company of data.crmCompanies) {
        if (customers.has(company.email.toLowerCase())) continue;
        const [first, ...rest] = company.name.split(' ');
        await api.post(endPoints.accounting.customers, {
            first_name: first,
            last_name: rest.join(' ') || first,
            email: company.email,
            phone: company.phone,
            city: company.city,
            country: company.country,
            type: 'customer',
        });
        c++;
    }

    for (const vendor of data.accountingVendors) {
        if (vendors.has(vendor.email.toLowerCase())) continue;
        const [first, ...rest] = vendor.name.split(' ');
        await api.post(endPoints.accounting.vendors, {
            first_name: first,
            last_name: rest.join(' ') || first,
            email: vendor.email,
            phone: vendor.phone,
            type: 'vendor',
        });
        v++;
    }

    return { customers: c, vendors: v };
}

export async function seedAccountingProducts(api: ApiUtils): Promise<number> {
    const existing = byTitle(await list(api, endPoints.accounting.products));
    let created = 0;

    for (const p of data.accountingProducts) {
        if (existing.has(p.name.toLowerCase())) continue;
        const response = await api.post(endPoints.accounting.products, { name: p.name, product_type_id: 1, cost_price: p.price, sale_price: p.price });
        if (response.ok()) created++;
    }

    return created;
}

// ---------------------------------------------------------------------------
// Orchestration
// ---------------------------------------------------------------------------

export type SeedReport = Record<string, number | string>;

export async function seedEverything(api: ApiUtils): Promise<SeedReport> {
    const report: SeedReport = {};

    await setupWooCommerce(api);
    report.wooProducts = await seedWooProducts(api);

    const depts = await seedDepartments(api);
    const desigs = await seedDesignations(api);
    report.departments = depts.size;
    report.designations = desigs.size;
    report.employees = await seedEmployees(api, depts, desigs);
    report.leavePolicies = await seedLeavePolicies(api);
    report.holidays = await seedHolidays(api);
    report.crmContacts = await seedCrmContacts(api);

    const people = await seedAccountingPeople(api);
    report.acctCustomers = people.customers;
    report.acctVendors = people.vendors;
    report.acctProducts = await seedAccountingProducts(api);

    return report;
}
