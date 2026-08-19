import { test, expect } from '@utils/test';
import { DepartmentsPage } from '@pages/hrm/departmentsPage';
import { ADMIN_STATE } from '@utils/authStates';
import { uniqueId } from '@utils/helpers';
import { withRole } from '@utils/roles';
import { cleanupDepartments } from '@utils/cleanup';
import { closeDb } from '@utils/dbUtils';
import { departments } from '@utils/seedData';

test.use({ storageState: ADMIN_STATE });

test.describe('HR — Departments', () => {
    let page: DepartmentsPage;

    test.beforeEach(async ({ page: p }) => {
        page = new DepartmentsPage(p);
        await page.goto();
    });

    test.afterAll(async () => {
        // Leave the demo site as we found it.
        await cleanupDepartments();
        await closeDb();
    });

    test('the departments list renders the seeded departments', { tag: ['@tier1', '@hrm-people'] }, async () => {
        expect(await page.totalItems(), 'departments are listed').toBeGreaterThanOrEqual(departments.length);

        // Searched across pages: the list paginates at 20, so seeded entries drop
        // off page 1 once the suite has created a few of its own.
        for (const seeded of departments.slice(0, 3)) {
            expect(await page.findAcrossPages(seeded.title), `"${seeded.title}" is listed`).toBe(true);
        }

        expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);
    });

    test('the create modal opens with its captured fields', { tag: ['@tier1', '@hrm-people'] }, async () => {
        await page.openCreateModal();

        await expect(page.titleField, 'Department Title').toBeVisible();
        await expect(page.descriptionField, 'Description').toBeVisible();
        await expect(page.modalSubmit).toBeVisible();

        await page.closeModal();
    });

    test('a department can be created and appears in the list', { tag: ['@tier1', '@hrm-people', '@crud'] }, async () => {
        const title = uniqueId('dept_');

        const before = await page.totalItems();

        await page.create({ title, description: 'Created by the automated suite.' });
        await page.goto();

        expect(await page.totalItems(), 'the total grew by exactly one').toBe(before + 1);
        expect(await page.findAcrossPages(title), 'the new department is listed').toBe(true);
    });

    test('a department with no title is refused', { tag: ['@tier3', '@hrm-people', '@validation'] }, async () => {
        const before = await page.totalItems();

        await page.attemptCreate({ title: '' });

        expect(await page.isModalOpen(), 'the modal stays open on a refused save').toBe(true);
        await page.closeModal();
        await page.goto();

        expect(await page.totalItems(), 'no department was created').toBe(before);
    });

    test('script in a department title never becomes executable markup', { tag: ['@tier3', '@hrm-people', '@security'] }, async () => {
        // Safe outcomes are "escaped and shown as text" OR "sanitised away".
        // The failure being guarded is the payload reaching the DOM as a real
        // <script> element or an inline event handler.
        const title = `${uniqueId('dept_xss_')}<script>alert(1)</script>`;

        await page.create({ title });
        await page.goto();

        expect(await page.hasNoPhpFatal(), 'no PHP fatal after the payload').toBe(true);
        expect(await page.rendersInjectedScript('alert(1)'), 'the payload is not executable in the list').toBe(false);
    });

    test('Departments is closed to an employee', { tag: ['@tier3', '@hrm-people', '@authz'] }, async ({ browser }) => {
        const denied = await withRole(browser, 'employee', async (p) => {
            const asEmployee = new DepartmentsPage(p);
            await asEmployee.goto();
            return asEmployee.isAccessDenied();
        });

        expect(denied, 'an employee cannot reach Departments').toBe(true);
    });
});
