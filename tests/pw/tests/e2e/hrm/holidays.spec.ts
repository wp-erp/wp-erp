import { test, expect } from '@utils/test';
import { HolidaysPage, holidayColumns } from '@pages/hrm/holidaysPage';
import { ADMIN_STATE } from '@utils/authStates';
import { uniqueName, dateOffset } from '@utils/helpers';
import { withRole } from '@utils/roles';
import { holidays as seededHolidays } from '@utils/seedData';
import { cleanupHolidays } from '@utils/cleanup';
import { closeDb } from '@utils/dbUtils';

test.use({ storageState: ADMIN_STATE });

test.describe('HR — Leave → Holidays', () => {
    let page: HolidaysPage;

    test.beforeEach(async ({ page: p }) => {
        page = new HolidaysPage(p);
        await page.goto();
    });

    test.afterAll(async () => {
        await cleanupHolidays();
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('the holidays list renders the seeded holidays', { tag: ['@tier1', '@hrm-leave'] }, async () => {
        expect(await page.totalItems(), 'holidays are listed').toBeGreaterThanOrEqual(seededHolidays.length);

        for (const seeded of seededHolidays.slice(0, 3)) {
            expect(await page.findAcrossPages(seeded.title), `"${seeded.title}" is listed`).toBe(true);
        }

        expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);
    });

    test('the list renders every captured column', { tag: ['@tier1', '@hrm-leave'] }, async () => {
        const headers = await page.columnHeaders();

        for (const column of holidayColumns) {
            expect(headers, `column "${column}"`).toContain(column);
        }
    });

    test('the create modal renders its captured fields', { tag: ['@tier1', '@hrm-leave'] }, async () => {
        await page.openCreateModal();

        await expect(page.titleField, 'Holiday Name').toBeVisible();
        await expect(page.startDateField, 'Start Date').toBeVisible();
        await expect(page.rangeCheckbox, 'Range').toBeVisible();
        await expect(page.descriptionField, 'Description').toBeVisible();
        await expect(page.modalSubmit).toBeVisible();

        await page.closeModal();
    });

    test('a single-day holiday can be created', { tag: ['@tier1', '@hrm-leave', '@crud'] }, async () => {
        const title = uniqueName('Holiday');
        const before = await page.totalItems();

        await page.create({ title, startDate: dateOffset(40), description: 'Created by the automated suite.' });
        await page.goto();

        expect(await page.totalItems(), 'the total grew by exactly one').toBe(before + 1);
        expect(await page.findAcrossPages(title), 'the new holiday is listed').toBe(true);
    });

    // ---- Tier 2 ----------------------------------------------------------

    test('a multi-day holiday reports its duration', { tag: ['@tier2', '@hrm-leave', '@edge'] }, async () => {
        const title = uniqueName('Range');
        const start = dateOffset(60);
        const end = dateOffset(62);

        await page.create({ title, startDate: start, endDate: end, description: 'Three-day range.' });
        await page.goto();

        expect(await page.findAcrossPages(title), 'the range holiday is listed').toBe(true);

        // Start and end inclusive — three calendar days.
        expect(await page.durationOf(title), 'duration covers the whole range').toMatch(/3\s*day/i);
    });

    test('the start date entered is the start date stored', { tag: ['@tier2', '@hrm-leave', '@edge'] }, async () => {
        const title = uniqueName('Datecheck');
        const start = dateOffset(45);

        await page.create({ title, startDate: start, description: 'Date round-trip.' });
        await page.goto();

        expect(await page.findAcrossPages(title), 'the holiday is listed').toBe(true);
        expect(await page.startDateOf(title), 'the date round-trips unchanged').toContain(start);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('a holiday with no title is refused', { tag: ['@tier3', '@hrm-leave', '@validation'] }, async () => {
        const before = await page.totalItems();

        const alerted = await page.attemptCreate({ title: '', startDate: dateOffset(80) });

        expect(alerted === '' || /title|name|required|provide/i.test(alerted), `unexpected alert: "${alerted}"`).toBe(true);
        expect(await page.isModalOpen(), 'the modal stays open on a refused save').toBe(true);

        await page.closeModal();
        await page.goto();

        expect(await page.totalItems(), 'no holiday was created').toBe(before);
    });

    test('script in a holiday title never becomes executable markup', { tag: ['@tier3', '@hrm-leave', '@security'] }, async () => {
        const title = `${uniqueName('Xss')}<script>alert(1)</script>`;

        await page.create({ title, startDate: dateOffset(90) });
        await page.goto();

        expect(await page.hasNoPhpFatal(), 'no PHP fatal after the payload').toBe(true);
        expect(await page.rendersInjectedScript('alert(1)'), 'the payload is not executable in the list').toBe(false);
    });

    test('Holidays is closed to an employee', { tag: ['@tier3', '@hrm-leave', '@authz'] }, async ({ browser }) => {
        const denied = await withRole(browser, 'employee', async (p) => {
            const asEmployee = new HolidaysPage(p);
            await asEmployee.goto();
            return asEmployee.isAccessDenied();
        });

        expect(denied, 'an employee cannot reach Holidays').toBe(true);
    });
});
