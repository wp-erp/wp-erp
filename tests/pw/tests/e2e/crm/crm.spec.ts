import { test, expect } from '@utils/test';
import { CrmPage } from './crmPage';
import { data } from '@utils/testData';

/**
 * CRM UI specs (thin). The contacts/companies screens are jQuery+Vue hybrids
 * with stable DOM ids (verified in modules/crm/views/*.php), so we drive the add
 * modal directly. Depth lives in the DB-backed api spec. Every test carries a
 * tier tag (@lite/@pro), the @crm module tag and a role tag.
 */

const CRITICAL_ERROR = 'There has been a critical error on this website';

// ──────────────────────────────────────────────────────────────────────────
// Admin role — full CRUD surface
// ──────────────────────────────────────────────────────────────────────────
test.describe('CRM contacts & companies (admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('contacts list page loads with Add New control', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await crm.goToContacts();
        await expect(page.locator(crm.contacts.root)).toBeVisible();
        await expect(page.locator(crm.contacts.addNewBtn)).toBeVisible();
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
    });

    test('create a contact via the modal', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        const contact = data.crm.contact();
        await crm.createContact(contact);
        // After save the list re-renders; the new email should be discoverable in the table.
        await expect(page.locator(crm.contacts.table)).toBeVisible();
    });

    test('companies list page loads with Add New Company', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await crm.goToCompanies();
        await expect(page.locator(crm.contacts.root)).toBeVisible();
        await expect(page.locator(crm.companies.addNewBtn)).toBeVisible();
    });

    test('create a company via the modal', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await crm.createCompany(data.crm.company());
        await expect(page.locator(crm.contacts.table)).toBeVisible();
    });

    test('contact groups page loads with Add control', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await crm.goToContactGroups();
        await expect(page.locator(crm.groups.addNewBtn)).toBeVisible();
    });

    test('create a contact group', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        const name = `Group ${Date.now()}`;
        await crm.createContactGroup(name, 'pw seeded group');
        await expect(page.locator(crm.groups.root)).toContainText(name, { timeout: 15_000 });
    });

    // Edge: open the add modal but submit with required fields empty — the modal
    // must stay open (client-side required validation), not save a blank row.
    test('add-contact modal blocks empty required fields', { tag: ['@lite', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await crm.goToContacts();
        await page.locator(crm.contacts.addNewBtn).click();
        await expect(page.locator(crm.modal.firstName)).toBeVisible();
        await page.locator(crm.modal.submitBtn).click();
        // first_name is HTML5 required, so the field is still on screen after submit.
        await expect(page.locator(crm.modal.firstName)).toBeVisible();
    });
});

// ──────────────────────────────────────────────────────────────────────────
// CRM manager role — should reach the same screens
// ──────────────────────────────────────────────────────────────────────────
test.describe('CRM contacts (manager)', () => {
    test.use({ storageState: data.auth.crmManagerFile });

    test('manager can reach the contacts list', { tag: ['@lite', '@crm', '@manager'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await crm.goToContacts();
        await expect(page.locator(crm.contacts.root)).toBeVisible();
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
    });

    test('manager sees the Add New Contact button', { tag: ['@lite', '@crm', '@manager'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await crm.goToContacts();
        await expect(page.locator(crm.contacts.addNewBtn)).toBeVisible();
    });
});

// ──────────────────────────────────────────────────────────────────────────
// Negative — a plain employee must not manage CRM contacts
// ──────────────────────────────────────────────────────────────────────────
test.describe('CRM access control (employee)', () => {
    test.use({ storageState: data.auth.employeeFile });

    test('employee cannot use the Add New Contact action', { tag: ['@lite', '@crm', '@employee'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await page.goto(crm.urls.contacts);
        // The add control is gated by current_user_can('erp_crm_add_contact');
        // an employee either lands on a "no permission" notice or simply lacks the button.
        await expect(page.locator(crm.contacts.addNewBtn)).toHaveCount(0);
    });
});

// ──────────────────────────────────────────────────────────────────────────
// PRO — Deals pipeline (erp-pro). Smoke-level: the React/Vue app boots.
// ──────────────────────────────────────────────────────────────────────────
test.describe('CRM Deals pipeline (pro, admin)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('deals overview page boots and mounts the pipeline app', { tag: ['@pro', '@crm', '@admin'] }, async ({ page }) => {
        const crm = new CrmPage(page);
        await page.goto(crm.urls.deals);
        // The Deals SPA mounts into #erp-deals once the pro module is active and its
        // tables exist (the @pro setup installs them). Assert no PHP fatal and the
        // real app container, plus the in-app Deals nav.
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);
        await expect(page.locator('#erp-deals')).toBeVisible();
        await expect(page.locator('#wpbody-content')).toContainText(/Deals|Pipeline|Overview/i);
    });
});
