import { expect, type Page } from '@utils/test';
import { toPath } from '@utils/helpers';
import { dbUtils } from '@utils/dbUtils';
import { tables } from '@utils/dbData';
import { data, TEST_PREFIX } from '@utils/testData';
import type { ApiUtils } from '@utils/apiUtils';
import type { IdMap } from '@utils/interfaces';

/**
 * Feature-isolated page object for the WP ERP CRM module.
 *
 * The free CRM has NO REST controller (see utils/apiEndPoints note), so fixtures
 * are seeded directly through dbUtils against the unified "people" tables:
 *   - wp_erp_peoples            (the contact/company row)
 *   - wp_erp_people_types       (lookup: contact|company|customer|vendor)
 *   - wp_erp_people_type_relations (typing + soft-delete via deleted_at)
 *   - wp_erp_peoplemeta         (life_stage, contact_owner, …)
 * This mirrors what erp_insert_people() persists (includes/functions-people.php).
 *
 * UI selectors are taken verbatim from the rendered views
 * (modules/crm/views/contact.php, company.php, contact-groups.php and the
 * js-template modules/crm/views/js-templates/new-customer.php) and cross-checked
 * with wp-erp/tests/acceptance/CRM/*Cest.php.
 */

export interface ContactInput {
    first_name: string;
    last_name?: string;
    email: string;
    phone?: string;
    life_stage?: string;
}

export interface CompanyInput {
    company: string;
    email: string;
    phone?: string;
    life_stage?: string;
}

export class CrmPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    // ── URLs (admin.php?page=erp-crm routed by &section=) ─────────────────────
    readonly urls = {
        dashboard: toPath('wp-admin/admin.php?page=erp-crm&section=dashboard'),
        contacts: toPath('wp-admin/admin.php?page=erp-crm&section=contact&sub-section=contacts'),
        contact: (id: string | number) =>
            toPath(`wp-admin/admin.php?page=erp-crm&section=contact&sub-section=contacts&action=view&id=${id}`),
        companies: toPath('wp-admin/admin.php?page=erp-crm&section=contact&sub-section=companies'),
        company: (id: string | number) =>
            toPath(`wp-admin/admin.php?page=erp-crm&section=contact&sub-section=companies&action=view&id=${id}`),
        contactGroups: toPath('wp-admin/admin.php?page=erp-crm&section=contact&sub-section=contact-groups'),
        activities: toPath('wp-admin/admin.php?page=erp-crm&section=activities'),
        schedules: toPath('wp-admin/admin.php?page=erp-crm&section=task&sub-section=schedules'),
        reports: toPath('wp-admin/admin.php?page=erp-crm&section=reports'),
        // PRO — Deals pipeline. In WPERP >= 1.4.0 deals registers via erp_add_menu
        // into the CRM app, so it is routed as a &section, not a top-level page
        // (erp-pro/modules/crm/deals/includes/Admin.php::load_new_menu()).
        deals: toPath('wp-admin/admin.php?page=erp-crm&section=deals'),
        dealsBoard: toPath('wp-admin/admin.php?page=erp-crm&section=deals&sub-section=erp-deals-admin-page'),
        integrations: toPath('wp-admin/admin.php?page=erp-crm&section=integration'),
    } as const;

    // ── Selectors grouped by area (real ids from the views) ───────────────────
    readonly contacts = {
        root: '#wp-erp',
        heading: 'h2',
        addNewBtn: '#erp-customer-new',
        importUsersBtn: '#erp-contact-import-users',
        searchSegmentBtn: '#erp-contact-search-segmen',
        importExportGroup: '#crm-import-export',
        table: 'table.customers',
        rowCheckbox: '#erp-crm-customer-id-checkbox',
    } as const;

    readonly companies = {
        addNewBtn: '#erp-company-new',
        companyNameInput: '#company',
    } as const;

    // Shared add-contact / add-company modal (js-templates/new-customer.php).
    readonly modal = {
        firstName: '#first_name',
        lastName: '#last_name',
        email: '#erp-crm-new-contact-email',
        phone: 'input[name="contact[main][phone]"]',
        lifeStageContainer: '#select2-contactmetalife_stage-container',
        contactOwner: '#erp-crm-contact-owner-id',
        contactOwnerContainer: '#select2-erp-crm-contact-owner-id-container',
        advancedFieldsToggle: '#advanced_fields',
        // The modal save button renders with the text "Add New".
        submitBtn: 'button:has-text("Add New")',
        select2Options: '.select2-results__option',
    } as const;

    readonly groups = {
        root: '#wp-erp',
        addNewBtn: '#erp-new-contact-group',
        name: '#erp-crm-contact-group-name',
        description: '#erp-crm-contact-group-description',
        submitBtn: 'button:has-text("Add New")',
    } as const;

    // ── Navigation helpers ────────────────────────────────────────────────────
    async goToContacts(): Promise<void> {
        await this.page.goto(this.urls.contacts);
        await expect(this.page.locator(this.contacts.root)).toBeVisible();
    }

    async goToCompanies(): Promise<void> {
        await this.page.goto(this.urls.companies);
        await expect(this.page.locator(this.contacts.root)).toBeVisible();
    }

    async goToContactGroups(): Promise<void> {
        await this.page.goto(this.urls.contactGroups);
        await expect(this.page.locator(this.groups.root)).toBeVisible();
    }

    /** Pick the first non-placeholder option in an opened select2 dropdown. */
    /**
     * Set the required Life Stage + Contact Owner by writing the underlying
     * <select> values directly (both are pre-populated selects; select2/jQuery
     * listen to "change"). Avoids the flaky ajax-dropdown timing that otherwise
     * leaves a required field empty and hangs the modal open under parallel load.
     */
    /** Open a select2 by its container and click its first non-placeholder option. */
    private async pickFromSelect2(containerSelector: string, optionMatch?: RegExp): Promise<void> {
        await this.page.locator(containerSelector).waitFor({ state: 'visible', timeout: 15_000 });
        await this.page.locator(containerSelector).click();
        const options = this.page.locator(this.modal.select2Options);
        await options.first().waitFor({ state: 'visible', timeout: 15_000 });
        const target = optionMatch
            ? options.filter({ hasText: optionMatch }).first()
            : options.filter({ hasNotText: /^\s*[-–—]*\s*select/i }).first();
        await target.waitFor({ state: 'visible', timeout: 15_000 });
        await target.click();
    }

    private async selectLifeStageAndOwner(): Promise<void> {
        // Interact like a user: open each select2 and click a concrete option. This
        // lets select2 own the value, avoiding the re-init wipe that an early
        // programmatic value-set hits on quickly-filled modals.
        await this.pickFromSelect2(this.modal.lifeStageContainer);
        await this.pickFromSelect2(this.modal.contactOwnerContainer, /admin/i);
    }

    /**
     * Open the Add Contact modal, fill the required fields and submit.
     * Returns once the list re-renders (#wp-erp present again).
     */
    async createContact(contact: ContactInput): Promise<void> {
        await this.goToContacts();
        await this.page.locator(this.contacts.addNewBtn).click();

        await this.page.locator(this.modal.firstName).fill(contact.first_name);
        if (contact.last_name) await this.page.locator(this.modal.lastName).fill(contact.last_name);
        await this.page.locator(this.modal.email).fill(contact.email);
        if (contact.phone) await this.page.locator(this.modal.phone).fill(contact.phone);

        // Life Stage + Contact Owner (both required) via the underlying selects.
        await this.selectLifeStageAndOwner();

        // Submit and wait for the create's admin-ajax response so the row is
        // committed before callers query the DB (the modal closes optimistically,
        // before the insert lands); then confirm the modal actually closed.
        await Promise.all([
            this.page.waitForResponse(r => r.url().includes('admin-ajax.php') && r.request().method() === 'POST', { timeout: 30_000 }),
            this.page.locator(this.modal.submitBtn).click(),
        ]);
        await expect(this.page.locator(this.modal.email)).toBeHidden({ timeout: 30_000 });
    }

    /** Open the Add Company modal, fill required fields and submit. */
    async createCompany(company: CompanyInput): Promise<void> {
        await this.goToCompanies();
        await this.page.locator(this.companies.addNewBtn).click();

        await this.page.locator(this.companies.companyNameInput).fill(company.company);
        await this.page.locator(this.modal.email).fill(company.email);
        if (company.phone) await this.page.locator(this.modal.phone).fill(company.phone);

        await this.selectLifeStageAndOwner();

        // Submit and wait for the create's admin-ajax response so the row is
        // committed before callers query the DB (the modal closes optimistically,
        // before the insert lands); then confirm the modal actually closed.
        await Promise.all([
            this.page.waitForResponse(r => r.url().includes('admin-ajax.php') && r.request().method() === 'POST', { timeout: 30_000 }),
            this.page.locator(this.modal.submitBtn).click(),
        ]);
        await expect(this.page.locator(this.modal.email)).toBeHidden({ timeout: 30_000 });
    }

    /** Create a contact group from the contact-groups screen. */
    async createContactGroup(name: string, description = ''): Promise<void> {
        await this.goToContactGroups();
        await this.page.locator(this.groups.addNewBtn).click();
        await this.page.locator(this.groups.name).fill(name);
        if (description) await this.page.locator(this.groups.description).fill(description);
        await Promise.all([
            this.page.waitForResponse(r => r.url().includes('admin-ajax.php') && r.request().method() === 'POST', { timeout: 30_000 }),
            this.page.locator(this.groups.submitBtn).click(),
        ]);
        // Modal closes on save — its name field detaches.
        await expect(this.page.locator(this.groups.name)).toBeHidden({ timeout: 30_000 });
    }

    // ── DB seeding (no REST in free CRM) ──────────────────────────────────────

    /** Resolve the numeric people-type id for a type name (contact/company). */
    private static async peopleTypeId(typeName: string): Promise<number | undefined> {
        const rows = await dbUtils.dbQuery<{ id: number }>(
            `SELECT id FROM ${tables.peopleTypes} WHERE name = ? LIMIT 1`,
            [typeName],
        );
        return rows[0]?.id;
    }

    /**
     * Insert one person row, type it, and write its CRM meta. Returns the new
     * people id (or undefined if the type lookup failed).
     */
    private static async insertPerson(args: {
        type: string;
        first_name: string;
        last_name?: string;
        company?: string;
        email: string;
        phone?: string;
        life_stage?: string;
        contact_owner?: number;
    }): Promise<string | undefined> {
        const typeId = await CrmPage.peopleTypeId(args.type);
        if (typeId === undefined) return undefined;

        const owner = args.contact_owner ?? 1;
        const hash = `${TEST_PREFIX}${Date.now()}${Math.floor(Math.random() * 1e6)}`;

        const result = await dbUtils.dbQuery<{ insertId: number }>(
            `INSERT INTO ${tables.peoples}
                (user_id, first_name, last_name, company, email, phone, life_stage, contact_owner, hash, created_by, created)
             VALUES (0, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())`,
            [
                args.first_name,
                args.last_name ?? '',
                args.company ?? '',
                args.email,
                args.phone ?? '',
                args.life_stage ?? 'lead',
                owner,
                hash,
                owner,
            ],
        );

        // mysql2 surfaces insertId on the OkPacket; fall back to a lookup by email.
        let peopleId = (result as unknown as { insertId?: number }).insertId;
        if (!peopleId) {
            const found = await dbUtils.dbQuery<{ id: number }>(
                `SELECT id FROM ${tables.peoples} WHERE email = ? ORDER BY id DESC LIMIT 1`,
                [args.email],
            );
            peopleId = found[0]?.id;
        }
        if (!peopleId) return undefined;

        await dbUtils.dbQuery(
            `INSERT INTO ${tables.peopleTypeRelations} (people_id, people_types_id, deleted_at)
             VALUES (?, ?, NULL)`,
            [peopleId, typeId],
        );

        await dbUtils.dbQuery(
            `INSERT INTO ${tables.peopleMeta} (erp_people_id, meta_key, meta_value) VALUES (?, 'life_stage', ?)`,
            [peopleId, args.life_stage ?? 'lead'],
        );
        await dbUtils.dbQuery(
            `INSERT INTO ${tables.peopleMeta} (erp_people_id, meta_key, meta_value) VALUES (?, 'contact_owner', ?)`,
            [peopleId, String(owner)],
        );

        return String(peopleId);
    }

    /**
     * Insert a typed contact straight through the storage layer (same path the app
     * uses) and return its id. Storage-fidelity tests use this for deterministic
     * round-trips of edge values (charset, case, phone formatting, truncation),
     * independent of the modal's async select2 timing.
     */
    static async insertContactRow(args: { first_name: string; last_name?: string; email: string; phone?: string; life_stage?: string }): Promise<string | undefined> {
        return CrmPage.insertPerson({ type: 'contact', ...args });
    }

    /** Fetch a seeded person row by id (used by API/DB specs to read back). */
    static async getPerson(id: string | number): Promise<Record<string, unknown> | undefined> {
        const rows = await dbUtils.dbQuery<Record<string, unknown>>(
            `SELECT * FROM ${tables.peoples} WHERE id = ? LIMIT 1`,
            [id],
        );
        return rows[0];
    }

    /** Find a typed person by email (verifies the type relation exists). */
    static async findTypedPersonByEmail(
        email: string,
        typeName: string,
    ): Promise<Record<string, unknown> | undefined> {
        const rows = await dbUtils.dbQuery<Record<string, unknown>>(
            `SELECT p.* FROM ${tables.peoples} p
                JOIN ${tables.peopleTypeRelations} r ON r.people_id = p.id
                JOIN ${tables.peopleTypes} t ON t.id = r.people_types_id
             WHERE p.email = ? AND t.name = ? AND r.deleted_at IS NULL
             ORDER BY p.id DESC LIMIT 1`,
            [email, typeName],
        );
        return rows[0];
    }

    /**
     * Seed the CRM module to a usable state and create one contact + one company
     * via dbUtils (the free CRM exposes no REST). Resilient: any failure is
     * swallowed and whatever IDs were obtained are returned.
     *
     * @param _api accepted for signature parity with HRM/Accounting seeders; the
     *             CRM seeder uses dbUtils instead of REST, so it is unused.
     */
    static async seed(_api: ApiUtils): Promise<IdMap> {
        const ids: IdMap = {};

        try {
            const contact = { ...data.crm.contact(), last_name: 'Contact' };
            const contactId = await CrmPage.insertPerson({
                type: 'contact',
                first_name: contact.first_name,
                last_name: contact.last_name,
                email: contact.email,
                phone: contact.phone,
                life_stage: contact.life_stage,
            });
            if (contactId) ids.CONTACT_ID = contactId;
        } catch {
            /* best-effort seeding — keep whatever we got */
        }

        try {
            const co = data.crm.company();
            const companyId = await CrmPage.insertPerson({
                type: 'company',
                first_name: co.company,
                last_name: '(company)',
                company: co.company,
                email: co.email,
                life_stage: 'customer',
            });
            if (companyId) ids.CRM_COMPANY_ID = companyId;
        } catch {
            /* best-effort seeding — keep whatever we got */
        }

        return ids;
    }
}
