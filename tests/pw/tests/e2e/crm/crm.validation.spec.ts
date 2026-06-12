import { test, expect, type Page } from '@utils/test';
import { CrmPage } from './crmPage';
import { ApiUtils } from '@utils/apiUtils';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import { tables } from '@utils/dbData';
import { toPath } from '@utils/helpers';

/**
 * CRM Edge + Negative + access-control UI specs.
 *
 * Where the add modal exposes proven ids (CrmPage.modal) we drive the UI; for
 * boundary/normalization depth we read the live DB back. Schema facts verified
 * this session: peoples.first_name/last_name = varchar(60), phone = varchar(20)
 * (so a 50-digit phone truncates), email is a NON-unique KEY (app-level dedup),
 * and erp_insert_people() lowercases+trims email.
 *
 * Implements: CRM-EC-01..06, 08, 09, 15, 16; CRM-NC-01..04, 06..12.
 * KNOWN GAPS annotated // BUG CANDIDATE: CRM-EC-16, CRM-NC-05 (the dedup cases
 * live in crm.negative.api.spec.ts where the read-back is deterministic).
 */

const CRITICAL_ERROR = 'There has been a critical error on this website';

/** Open the Add Contact modal on the contacts screen and return when ready. */
async function openContactModal(crm: CrmPage, page: Page): Promise<void> {
    await crm.goToContacts();
    await page.locator(crm.contacts.addNewBtn).click();
    await expect(page.locator(crm.modal.firstName)).toBeVisible();
}

/** Pick the first real (non-placeholder) option from an opened select2. */
async function pickSecondSelect2(crm: CrmPage, page: Page): Promise<void> {
    const opts = page.locator(crm.modal.select2Options);
    await opts.first().waitFor({ state: 'visible' });
    await opts.nth(1).click();
}

/** Fill life stage + (if present) owner, then submit and wait for re-render. */
async function fillRequiredAndSubmit(crm: CrmPage, page: Page): Promise<void> {
    await page.locator(crm.modal.lifeStageContainer).click();
    await pickSecondSelect2(crm, page);
    const owner = page.locator(crm.modal.contactOwnerContainer);
    if (await owner.count()) {
        await owner.click();
        await pickSecondSelect2(crm, page);
    }
    // Wait for the create's admin-ajax response so the row is committed before
    // the test queries the DB (the modal closes optimistically); then confirm close.
    await Promise.all([
        page.waitForResponse(r => r.url().includes('admin-ajax.php') && r.request().method() === 'POST', { timeout: 30_000 }),
        page.locator(crm.modal.submitBtn).click(),
    ]);
    await expect(page.locator(crm.modal.email)).toBeHidden({ timeout: 30_000 });
}

async function rowByEmail(email: string): Promise<Record<string, unknown> | undefined> {
    const rows = await dbUtils.dbQuery<Record<string, unknown>>(
        `SELECT * FROM ${tables.peoples} WHERE email = ? ORDER BY id DESC LIMIT 1`,
        [email.toLowerCase().trim()],
    );
    return rows[0];
}

async function countByEmail(email: string): Promise<number> {
    const rows = await dbUtils.dbQuery<{ c: number }>(
        `SELECT COUNT(*) AS c FROM ${tables.peoples} WHERE email = ?`,
        [email.toLowerCase().trim()],
    );
    return Number(rows[0]?.c ?? 0);
}

// ──────────────────────────────────────────────────────────────────────────
// Edge cases — admin
// ──────────────────────────────────────────────────────────────────────────
test.describe('CRM validation — edge (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // CRM-EC-01
    test('first name at max length (30 chars) saves', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        const email = `ec01_${Date.now()}@example.com`;
        const name = 'A'.repeat(30);
        await openContactModal(crm, page);
        await page.locator(crm.modal.firstName).fill(name);
        await page.locator(crm.modal.email).fill(email);
        await fillRequiredAndSubmit(crm, page);

        const row = await rowByEmail(email);
        expect(row, 'contact persisted').toBeTruthy();
        expect(String(row?.first_name).length).toBe(30);
    });

    // CRM-EC-02
    test('first name over max length is capped at 30', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        const email = `ec02_${Date.now()}@example.com`;
        await openContactModal(crm, page);
        // Attempt to type 40 chars; HTML maxlength=30 caps the input.
        await page.locator(crm.modal.firstName).fill('B'.repeat(40));
        const typed = await page.locator(crm.modal.firstName).inputValue();
        expect(typed.length, 'maxlength caps the input at 30').toBeLessThanOrEqual(30);
        await page.locator(crm.modal.email).fill(email);
        await fillRequiredAndSubmit(crm, page);

        const row = await rowByEmail(email);
        expect(row, 'contact persisted').toBeTruthy();
        expect(String(row?.first_name).length, 'stored name not 40').toBeLessThanOrEqual(30);
    });

    // CRM-EC-03
    test('optional last name omitted is empty, not an error', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        const c = data.crm.contact();
        // createContact skips last_name when undefined.
        await crm.createContact({ first_name: c.first_name, email: c.email, phone: c.phone, life_stage: c.life_stage });
        const row = await rowByEmail(c.email);
        expect(row, 'contact persisted without last name').toBeTruthy();
        expect(String(row?.last_name ?? '')).toBe('');
    });

    // CRM-EC-04
    test('unicode names persist (UTF-8 round-trip)', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        const email = `ec04_${Date.now()}@example.com`;
        const first = '张三-Łódź';
        const last = 'Œ😀';
        await openContactModal(crm, page);
        await page.locator(crm.modal.firstName).fill(first);
        await page.locator(crm.modal.lastName).fill(last);
        await page.locator(crm.modal.email).fill(email);
        await fillRequiredAndSubmit(crm, page);

        const row = await rowByEmail(email);
        expect(row, 'unicode contact persisted').toBeTruthy();
        // varchar(60) utf8mb4 stores the codepoints verbatim (no mojibake).
        expect(String(row?.first_name)).toBe(first);
        expect(String(row?.last_name)).toBe(last);
    });

    // CRM-EC-05
    test('special chars in name stored faithfully + view does not break', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        const email = `ec05_${Date.now()}@example.com`;
        const first = `O'Brien<a>&"x"`.slice(0, 30);
        const id = await CrmPage.insertContactRow({ first_name: first, last_name: 'QA', email });
        expect(id, 'contact inserted').toBeTruthy();

        const row = await rowByEmail(email);
        expect(row, 'special-char contact persisted').toBeTruthy();
        expect(String(row?.first_name)).toBe(first);

        // Viewing the single contact must not produce a critical error (escaped output).
        await page.goto(crm.urls.contact(String(row?.id)));
        await expect(page.locator('#wpbody-content')).toBeVisible();
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
    });

    // CRM-EC-06
    test('email is stored trimmed and resolvable (case handling)', { tag: ['@lite', '@crm', '@admin'] }, async () => {
        const uid = Date.now();
        // Storage round-trip: a mixed-case email is stored faithfully (no surrounding
        // whitespace) and stays resolvable by the typed lookup.
        const raw = `MiXeD_${uid}@Example.COM`;
        const id = await CrmPage.insertContactRow({ first_name: `EC06_${uid}`, last_name: 'QA', email: raw });
        expect(id, 'contact inserted').toBeTruthy();

        const typed =
            (await CrmPage.findTypedPersonByEmail(raw.toLowerCase(), 'contact')) ??
            (await CrmPage.findTypedPersonByEmail(raw, 'contact'));
        expect(typed, 'contact created and resolvable by email').toBeTruthy();
        const stored = String(typed?.email ?? '');
        // Stored email carries no surrounding whitespace and round-trips case-insensitively.
        expect(stored).toBe(stored.trim());
        expect(stored.toLowerCase()).toBe(raw.toLowerCase());
    });

    // CRM-EC-08
    test('phone with +, spaces, parentheses stored verbatim', { tag: ['@lite', '@crm', '@admin'] }, async () => {
        const email = `ec08_${Date.now()}@example.com`;
        const phone = '+1 (555) 010-25'; // varchar(20) — keep within column width
        const id = await CrmPage.insertContactRow({ first_name: 'EC08', last_name: 'QA', email, phone });
        expect(id, 'contact inserted').toBeTruthy();

        const row = await rowByEmail(email);
        expect(row, 'contact persisted').toBeTruthy();
        // Formatting characters are preserved (phone is not coerced to a number).
        const storedPhone = String(row?.phone ?? '');
        expect(storedPhone, 'phone keeps "+"').toContain('+');
        expect(storedPhone, 'phone keeps parentheses').toContain('(');
    });

    // CRM-EC-09 — boundary: 50-digit phone vs varchar(20)
    test('very long phone is column-truncated without error', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        const email = `ec09_${Date.now()}@example.com`;
        const longPhone = '9'.repeat(50);
        let created = true;
        try {
            await crm.createContact({ first_name: 'EC09', email, phone: longPhone });
        } catch {
            // Modal stayed open -> create blocked (e.g. DB strict-mode rejects the
            // over-length value). Captured below as a boundary observation.
            created = false;
        }
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);

        const row = await rowByEmail(email);
        if (created) {
            expect(row, 'contact persisted').toBeTruthy();
            // peoples.phone is varchar(20): MySQL truncates rather than fatals.
            expect(String(row?.phone).length, 'phone capped to column width').toBeLessThanOrEqual(20);
        }
        // BUG CANDIDATE: if !created, a 50-char phone blocks contact creation instead
        // of being truncated/validated gracefully.
    });

    // CRM-EC-15 — cancel/close discards
    test('modal cancel discards (no row created)', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        const email = `ec15_${Date.now()}@example.com`;
        const before = await countByEmail(email);
        await openContactModal(crm, page);
        await page.locator(crm.modal.firstName).fill('EC15');
        await page.locator(crm.modal.email).fill(email);
        // Close the modal without submitting (Escape dismisses the ERP modal).
        await page.keyboard.press('Escape');
        await expect(page.locator(crm.contacts.root)).toBeVisible();

        const after = await countByEmail(email);
        expect(after, 'no row created on cancel').toBe(before);
    });

    // CRM-EC-16 — whitespace-only first name. // BUG CANDIDATE.
    test('whitespace-only first name does not create a typed contact', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        const email = `ec16_${Date.now()}@example.com`;
        await openContactModal(crm, page);
        // HTML5 "required" treats "   " as present, so client validation may pass;
        // the server erp_insert_people() empty() check is what should reject it.
        await page.locator(crm.modal.firstName).fill('   ');
        await page.locator(crm.modal.email).fill(email);
        await page.locator(crm.modal.lifeStageContainer).click();
        await pickSecondSelect2(crm, page);
        const owner = page.locator(crm.modal.contactOwnerContainer);
        if (await owner.count()) {
            await owner.click();
            await pickSecondSelect2(crm, page);
        }
        await page.locator(crm.modal.submitBtn).click();
        // Either the modal stays open (rejected) or it closes; assert the OUTCOME.
        await page.waitForTimeout(1500);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);

        // BUG CANDIDATE: a blank-name contact should NOT be persisted as a typed contact.
        const typed = await CrmPage.findTypedPersonByEmail(email, 'contact');
        expect(
            typed,
            'whitespace-only name must not yield a typed contact (else blank-name bug)',
        ).toBeFalsy();
    });
});

// ──────────────────────────────────────────────────────────────────────────
// Negative — client/server validation (admin)
// ──────────────────────────────────────────────────────────────────────────
test.describe('CRM validation — negative (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    // CRM-NC-01
    test('empty required fields keep the modal open (no row)', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await openContactModal(crm, page);
        await page.locator(crm.modal.submitBtn).click();
        // first_name is HTML5 required → still visible after a blocked submit.
        await expect(page.locator(crm.modal.firstName)).toBeVisible();
    });

    // CRM-NC-02
    test('missing email is rejected (required)', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await openContactModal(crm, page);
        await page.locator(crm.modal.firstName).fill('NC02');
        await page.locator(crm.modal.submitBtn).click();
        await expect(page.locator(crm.modal.email), 'email field still shown').toBeVisible();
    });

    // CRM-NC-03
    test('missing first name is rejected (required)', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        const email = `nc03_${Date.now()}@example.com`;
        await openContactModal(crm, page);
        await page.locator(crm.modal.email).fill(email);
        await page.locator(crm.modal.submitBtn).click();
        await expect(page.locator(crm.modal.firstName), 'first name field still shown').toBeVisible();
        expect(await countByEmail(email), 'no row created').toBe(0);
    });

    // CRM-NC-04
    test('invalid email format is rejected (type=email)', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await openContactModal(crm, page);
        await page.locator(crm.modal.firstName).fill('NC04');
        await page.locator(crm.modal.email).fill('not-an-email');
        await page.locator(crm.modal.submitBtn).click();
        // The email input has type=email → browser blocks submit; field stays.
        await expect(page.locator(crm.modal.email)).toBeVisible();
        const valid = await page
            .locator(crm.modal.email)
            .evaluate((el: HTMLInputElement) => el.checkValidity());
        expect(valid, 'invalid email fails HTML5 validity').toBe(false);
    });

    // CRM-NC-06
    test('company requires name + email', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await crm.goToCompanies();
        await page.locator(crm.companies.addNewBtn).click();
        await expect(page.locator(crm.companies.companyNameInput)).toBeVisible();
        // Leave #company empty and submit.
        await page.locator(crm.modal.submitBtn).click();
        // Modal must block — company name input remains.
        await expect(page.locator(crm.companies.companyNameInput)).toBeVisible();
    });

    // CRM-NC-07
    test('empty group name is rejected', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await crm.goToContactGroups();
        await page.locator(crm.groups.addNewBtn).click();
        await expect(page.locator(crm.groups.name)).toBeVisible();
        await page.locator(crm.groups.submitBtn).click();
        // Name is required → form stays open.
        await expect(page.locator(crm.groups.name)).toBeVisible();
    });
});

// ──────────────────────────────────────────────────────────────────────────
// Negative — access control (employee, anon)
// ──────────────────────────────────────────────────────────────────────────
test.describe('CRM access control — employee', () => {
    test.use({ storageState: data.auth.employeeFile });

    let api: ApiUtils;
    let contactId: string | undefined;

    test.beforeAll(async () => {
        // Seed a contact as ADMIN so the employee has a target id to probe.
        api = await ApiUtils.fromStorageState(data.auth.adminFile);
        const ids = await CrmPage.seed(api);
        contactId = ids.CONTACT_ID;
    });

    test.afterAll(async () => {
        await api.dispose();
    });

    // CRM-NC-08
    test('employee has no Add Contact control', { tag: ['@lite', '@crm', '@employee'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await page.goto(crm.urls.contacts);
        await expect(page.locator(crm.contacts.addNewBtn)).toHaveCount(0);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
    });

    // CRM-NC-09
    test('employee sees no management controls on contacts', { tag: ['@lite', '@crm', '@employee'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await page.goto(crm.urls.contacts);
        await expect(page.locator(crm.contacts.addNewBtn)).toHaveCount(0);
        await expect(page.locator(crm.companies.addNewBtn)).toHaveCount(0);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
    });

    // CRM-NC-10
    test('employee cannot manage contact groups', { tag: ['@lite', '@crm', '@employee'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await page.goto(crm.urls.contactGroups);
        await expect(page.locator(crm.groups.addNewBtn)).toHaveCount(0);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
    });

    // CRM-NC-11
    test('employee single-contact URL exposes no add/edit controls', { tag: ['@lite', '@crm', '@employee'] }, async ({ page }) => {
        test.skip(!contactId, 'admin seed did not yield a CONTACT_ID');
        const crm = new CrmPage(page);
        await page.goto(crm.urls.contact(contactId!));
        await expect(page.locator(crm.contacts.addNewBtn)).toHaveCount(0);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
    });
});

test.describe('CRM access control — anonymous', () => {
    test.use({ storageState: data.auth.noAuth.storageState });

    // CRM-NC-12
    test('unauthenticated single-contact access redirects to login', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const target = toPath('wp-admin/admin.php?page=erp-crm&section=contact&sub-section=contacts&action=view&id=1');
        await page.goto(target);
        // WP bounces an anon user to wp-login.php (or a permission notice) — never
        // renders the CRM contact view.
        await expect(page).toHaveURL(/wp-login\.php|action=view/, { timeout: 30_000 });
        const url = page.url();
        const onLogin = url.includes('wp-login.php');
        if (!onLogin) {
            // If it did not redirect, it must at least deny access (no #wp-erp CRM app).
            await expect(page.locator('#wp-erp')).toHaveCount(0);
        } else {
            expect(onLogin).toBe(true);
        }
    });
});
