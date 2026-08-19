import { test, expect } from '@utils/test';
import { CompanyPage } from '@pages/core/companyPage';
import { ADMIN_STATE } from '@utils/authStates';
import { company } from '@utils/seedData';
import { withRole } from '@utils/roles';

test.use({ storageState: ADMIN_STATE });

test.describe('Company', () => {
    let companyPage: CompanyPage;

    test.beforeEach(async ({ page }) => {
        companyPage = new CompanyPage(page);
    });

    test('the company screen shows the seeded company', { tag: ['@tier1', '@core-company'] }, async () => {
        await companyPage.goto();

        await companyPage.expectHeading(/Company/);
        expect(await companyPage.missingFromSummary([company.city]), 'the company address is shown').toEqual([]);
        await expect(companyPage.editLink).toBeVisible();
        await expect(companyPage.createLocationButton).toBeVisible();
        expect(await companyPage.hasNoPhpFatal(), 'no PHP fatal').toBe(true);
    });

    test('the edit form renders every company field', { tag: ['@tier1', '@core-company'] }, async () => {
        await companyPage.gotoEdit();

        await expect(companyPage.nameField).toBeVisible();
        await expect(companyPage.address1Field).toBeVisible();
        await expect(companyPage.cityField).toBeVisible();
        await expect(companyPage.zipField).toBeVisible();
        await expect(companyPage.phoneField).toBeVisible();
        await expect(companyPage.websiteField).toBeVisible();
        await expect(companyPage.countrySelect).toBeVisible();
        await expect(companyPage.saveButton).toBeVisible();

        expect(await companyPage.countryOptionCount(), 'the country list is populated').toBeGreaterThan(100);
    });

    test('editing the company persists and is restored', { tag: ['@tier1', '@core-company', '@crud'] }, async () => {
        await companyPage.gotoEdit();
        const original = await companyPage.readForm();

        try {
            await companyPage.fillForm({ city: 'Chattogram', phone: '+880 31 555000' });
            await companyPage.saveAndReadNotice();

            await companyPage.gotoEdit();
            const saved = await companyPage.readForm();

            expect(saved.city, 'city persisted').toBe('Chattogram');
            expect(saved.phone, 'phone persisted').toBe('+880 31 555000');
        } finally {
            await companyPage.gotoEdit();
            await companyPage.fillForm(original);
            await companyPage.save();
        }

        await companyPage.gotoEdit();
        expect((await companyPage.readForm()).city, 'the original city is restored').toBe(original.city);
    });

    test('a blank company name is rejected', { tag: ['@tier3', '@core-company', '@validation'] }, async () => {
        await companyPage.gotoEdit();
        const original = await companyPage.readForm();

        try {
            await companyPage.fillForm({ name: '' });
            await companyPage.save();

            await companyPage.gotoEdit();
            expect((await companyPage.readForm()).name, 'an empty name is not saved over the real one').not.toBe('');
        } finally {
            await companyPage.gotoEdit();
            await companyPage.fillForm(original);
            await companyPage.save();
        }
    });

    test('Company is closed to roles without manage_options', { tag: ['@tier3', '@core-company', '@authz'] }, async ({ browser }) => {
        const denied = await withRole(browser, 'employee', async (page) => {
            const asEmployee = new CompanyPage(page);
            await asEmployee.goto();
            return asEmployee.isAccessDenied();
        });

        expect(denied, 'an employee cannot reach Company').toBe(true);
    });
});
