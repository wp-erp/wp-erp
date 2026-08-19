import { test, expect } from '@utils/test';
import { ModulesPage, categoryFilters, statusFilters, bulkActionLabels } from '@pages/core/modulesPage';
import { ADMIN_STATE } from '@utils/authStates';
import { storedLicenseStatus } from '@utils/license';
import { closeDb } from '@utils/dbUtils';
import { withRole } from '@utils/roles';

test.use({ storageState: ADMIN_STATE });

test.describe('Modules & Extensions', () => {
    let modules: ModulesPage;

    test.beforeEach(async ({ page }) => {
        modules = new ModulesPage(page);
        await modules.goto();
    });

    test.afterAll(async () => {
        await closeDb();
    });

    test('the screen loads and lists the three core modules', { tag: ['@tier1', '@core-modules'] }, async () => {
        await modules.expectHeading(/Modules & Extensions/);

        expect(await modules.missingCoreModules(), 'every core module is listed').toEqual([]);
        expect(await modules.coreModuleCardCount(), 'a card per core module').toBeGreaterThanOrEqual(3);
        expect(await modules.hasNoPhpFatal(), 'no PHP fatal').toBe(true);
    });

    /**
     * Expected-to-fail: the Pro block is rendered but hidden, because
     * modules.php:817-823 binds its reveal to `$(window).on('load')` from inside
     * the DOM-ready callback. When load has already fired, the handler never
     * runs. This passes while the defect stands and FAILS once it is fixed —
     * the signal to drop the test.fail() and the revealProExtensions() workaround.
     */
    test('KNOWN DEFECT: the Pro extensions block never becomes visible', { tag: ['@tier1', '@core-modules', '@pro', '@known-defect'] }, async () => {
        test.fail();
        expect(await modules.proExtensionsVisible(), 'Pro extensions block is visible to the user').toBe(true);
    });

    test('the category and status filters render once the Pro block is shown', { tag: ['@tier1', '@core-modules', '@pro'] }, async () => {
        await modules.revealProExtensions();

        for (const label of Object.keys(categoryFilters) as Array<keyof typeof categoryFilters>) {
            await expect(modules.categoryFilter(label), `category filter "${label}"`).toBeVisible();
        }
        for (const label of Object.keys(statusFilters) as Array<keyof typeof statusFilters>) {
            await expect(modules.statusFilter(label), `status filter "${label}"`).toBeVisible();
        }

        await expect(modules.searchBox).toBeVisible();
    });

    test('the bulk-action bar appears only after a module is selected', { tag: ['@tier2', '@core-modules', '@pro'] }, async () => {
        await modules.revealProExtensions();
        await expect(modules.bulkBar, 'hidden until there is something to act on').toBeHidden();

        await modules.selectFirstModule();
        await expect(modules.bulkBar, 'selecting a module reveals the bulk bar').toBeVisible();

        for (const label of bulkActionLabels) {
            await expect(modules.bulkAction(label), `bulk action "${label}"`).toBeVisible();
        }

        await modules.deselectFirstModule();
    });

    test('every licensed extension appears in the modules markup', { tag: ['@tier1', '@core-modules', '@pro'] }, async () => {
        await modules.revealProExtensions();

        const granted = (await storedLicenseStatus())?.extensions ?? [];
        expect(granted.length, 'the licence grants extensions').toBeGreaterThan(0);

        expect(await modules.moduleRowCountInDom(), 'module rows are rendered').toBeGreaterThanOrEqual(granted.length);
    });

    test('searching narrows the list and clearing restores it', { tag: ['@tier2', '@core-modules', '@pro'] }, async () => {
        await modules.revealProExtensions();

        const before = await modules.visibleModuleCount();
        expect(before, 'the unfiltered list has modules').toBeGreaterThan(1);

        await modules.search('Payroll');
        const narrowed = await modules.moduleNames();

        expect(narrowed.length, 'search narrows the list').toBeLessThan(before);
        expect(narrowed.some((n) => /payroll/i.test(n)), 'the matching module is kept').toBe(true);

        await modules.search('');
        expect(await modules.visibleModuleCount(), 'clearing the search restores the list').toBe(before);
    });

    test('a search matching nothing yields an empty list, not an error', { tag: ['@tier3', '@core-modules', '@pro'] }, async () => {
        await modules.revealProExtensions();
        await modules.search('zzz-no-such-module-zzz');

        expect(await modules.visibleModuleCount(), 'nothing matches').toBe(0);
        expect(await modules.hasNoPhpFatal(), 'no PHP fatal on an empty result').toBe(true);
    });

    test('the modules screen is closed to roles without manage_options', { tag: ['@tier3', '@core-modules', '@authz'] }, async ({ browser }) => {
        const denied = await withRole(browser, 'employee', async (page) => {
            const asEmployee = new ModulesPage(page);
            await asEmployee.goto();
            return asEmployee.isAccessDenied();
        });

        expect(denied, 'an employee cannot reach the modules screen').toBe(true);
    });
});
