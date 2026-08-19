import { test, expect } from '@utils/test';
import { DesignationsPage } from '@pages/hrm/designationsPage';
import { ADMIN_STATE } from '@utils/authStates';
import { uniqueId } from '@utils/helpers';
import { withRole } from '@utils/roles';
import { cleanupDesignations } from '@utils/cleanup';
import { closeDb } from '@utils/dbUtils';
import { designations } from '@utils/seedData';

test.use({ storageState: ADMIN_STATE });

test.describe('HR — Designations', () => {
    let page: DesignationsPage;

    test.beforeEach(async ({ page: p }) => {
        page = new DesignationsPage(p);
        await page.goto();
    });

    test.afterAll(async () => {
        // Leave the demo site as we found it.
        await cleanupDesignations();
        await closeDb();
    });

    test('the designations list renders the seeded designations', { tag: ['@tier1', '@hrm-people'] }, async () => {
        expect(await page.rowCount(), 'designations are listed').toBeGreaterThan(0);

        for (const title of designations.slice(0, 3)) {
            expect(await page.hasDesignation(title), `"${title}" is listed`).toBe(true);
        }

        expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);
    });

    test('the create modal opens with its captured fields', { tag: ['@tier1', '@hrm-people'] }, async () => {
        await page.openCreateModal();

        await expect(page.titleField, 'Designation Title').toBeVisible();
        await expect(page.descriptionField, 'Description').toBeVisible();
        await expect(page.modalSubmit).toBeVisible();

        await page.closeModal();
    });

    test('a designation can be created and appears in the list', { tag: ['@tier1', '@hrm-people', '@crud'] }, async () => {
        const title = uniqueId('desig_');
        const before = await page.totalItems();

        await page.create({ title, description: 'Created by the automated suite.' });
        await page.goto();

        expect(await page.totalItems(), 'the total count grew by exactly one').toBe(before + 1);
        expect(await page.findAcrossPages(title), 'the new designation is listed').toBe(true);
    });

    test('a designation with no title is refused', { tag: ['@tier3', '@hrm-people', '@validation'] }, async () => {
        const before = await page.totalItems();

        await page.attemptCreate({ title: '' });

        expect(await page.isModalOpen(), 'the modal stays open on a refused save').toBe(true);
        await page.closeModal();
        await page.goto();

        expect(await page.totalItems(), 'no designation was created').toBe(before);
    });

    test('script in a designation title never becomes executable markup', { tag: ['@tier3', '@hrm-people', '@security'] }, async () => {
        const title = `${uniqueId('desig_xss_')}<script>alert(1)</script>`;

        await page.create({ title });
        await page.goto();

        expect(await page.hasNoPhpFatal(), 'no PHP fatal after the payload').toBe(true);
        expect(await page.rendersInjectedScript('alert(1)'), 'the payload is not executable in the list').toBe(false);
    });

    test('Designations is closed to an employee', { tag: ['@tier3', '@hrm-people', '@authz'] }, async ({ browser }) => {
        const denied = await withRole(browser, 'employee', async (p) => {
            const asEmployee = new DesignationsPage(p);
            await asEmployee.goto();
            return asEmployee.isAccessDenied();
        });

        expect(denied, 'an employee cannot reach Designations').toBe(true);
    });
});
