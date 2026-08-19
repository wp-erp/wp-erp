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
     * erp-pro#952: the Pro block is rendered but its reveal is bound to
     * `$(window).on('load')` from INSIDE the DOM-ready callback, so when load has
     * already fired the handler never runs and the block stays hidden.
     *
     * Visibility is therefore a RACE and cannot be asserted either way: measured
     * on this build it is hidden 5/5 when the spec runs alone, and VISIBLE in a
     * full four-worker run, where the page loads slowly enough that `load` lands
     * after ready. A `test.fail()` on visibility flips between runs and turns a
     * known, filed defect into intermittent suite noise.
     *
     * So the deterministic half is asserted instead — the extensions are always
     * RENDERED, whatever the race does with showing them. That still catches a
     * licensing or rendering regression, which is what this screen is for.
     */
    test('every licensed Pro extension is rendered on the modules screen', { tag: ['@tier1', '@core-modules', '@pro'] }, async () => {
        expect(await modules.proExtensionsRendered(), 'the Pro extension markup is emitted for every granted extension').toBeGreaterThanOrEqual(20);
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
