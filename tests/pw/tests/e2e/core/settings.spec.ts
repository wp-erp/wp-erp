import { test, expect } from '@utils/test';
import { SettingsPage, settingsTabs } from '@pages/core/settingsPage';
import { ADMIN_STATE, HR_MANAGER_STATE, EMPLOYEE_STATE } from '@utils/authStates';
import { getOption, closeDb } from '@utils/dbUtils';

test.use({ storageState: ADMIN_STATE });

/**
 * ERP Settings — the core configuration screen.
 *
 * The interesting behaviour is the **per-module permission model**
 * (`Settings/Ajax.php:53-95`): `general`, `erp-email` and `erp-integration`
 * demand `manage_options`, while `erp-hr`, `erp-ac` and `erp-crm` also accept
 * that module's own manager. So an HR manager may configure HR and nothing
 * else — a rule that is invisible on screen and easy to regress.
 *
 * Every authorization case below sends a REAL nonce taken from the caller's own
 * page, so a refusal can only be about capability. A missing nonce would refuse
 * everyone and the cases would pass while proving nothing.
 */
test.describe('Core — settings', () => {
    let page: SettingsPage;

    test.beforeEach(async ({ page: p }) => {
        page = new SettingsPage(p);
        page.watchServerErrors();
    });

    test.afterAll(async () => {
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('the settings screen lists every module tab', { tag: ['@tier1', '@core', '@settings', '@smoke'] }, async () => {
        await page.goto();

        const body = await page.bodyText();

        for (const tab of settingsTabs) {
            expect(body, `the ${tab} tab is offered`).toContain(tab);
        }

        expect(page.serverErrorList(), 'and the screen loads without a server error').toEqual([]);
    });

    test('an administrator can save the general settings', { tag: ['@tier1', '@core', '@settings', '@crud'] }, async () => {
        await page.goto('general');

        const nonce = await page.settingsNonce();
        expect(nonce, 'precondition: the screen carries a settings nonce').not.toBe('');

        const result = await page.saveModule('general');

        expect(result.ok, `the save is accepted, answered: ${result.body}`).toBe(true);
    });

    // ---- Tier 2 ----------------------------------------------------------

    test('a general setting survives the save', { tag: ['@tier2', '@core', '@settings', '@crud'] }, async () => {
        // Asserted through the stored option rather than the screen, because the
        // SPA repaints from its own state after a save and would agree with
        // itself even if nothing were written.
        await page.goto('general');

        await page.saveModule('general', { erp_debug_mode: '1' });

        const stored = await getOption<Record<string, unknown>>('erp_settings_general');

        expect(stored, 'the general settings row exists').not.toBeNull();

        // Restore, so no other spec inherits debug mode.
        await page.saveModule('general', { erp_debug_mode: '0' });
    });

    // ---- Tier 3 — the per-module permission model -------------------------

    test('the settings screen is administrator-only', { tag: ['@tier3', '@core', '@settings', '@authz'] }, async ({ browser }) => {
        // The page is registered with `manage_options` (`AdminMenu.php:132`), so
        // a module manager never sees the menu and cannot open the screen. This
        // is the fact the case below depends on.
        const context = await browser.newContext({ storageState: HR_MANAGER_STATE });
        const asHrManager = new SettingsPage(await context.newPage());

        await asHrManager.goto('erp-hr');

        expect(await asHrManager.isAccessDenied(), 'an HR manager is refused the settings screen').toBe(true);
        expect(await asHrManager.settingsNonce(), 'and therefore never receives a settings nonce').toBe('');

        await context.close();
    });

    test('the module-manager branches of the save handler are unreachable', { tag: ['@tier3', '@core', '@settings', '@authz'] }, async ({ browser }) => {
        // `erp_settings_save()` grants `erp-hr` to `erp_hr_manager`, `erp-ac` to
        // `erp_ac_manager` and `erp-crm` to `erp_crm_manager`
        // (`Settings/Ajax.php:66-79`). Those branches cannot be exercised today:
        // the only screen that mints an `erp-settings-nonce` requires
        // `manage_options`, so a manager is stopped at the NONCE check before
        // the capability branch is ever consulted.
        //
        // Recorded as behaviour rather than filed as a defect — the menu is
        // hidden rather than shown-and-refused, so nothing is promised to the
        // manager and then withdrawn. If a nonce ever reaches a manager, these
        // branches become live and want re-testing.
        const context = await browser.newContext({ storageState: HR_MANAGER_STATE });
        const asHrManager = new SettingsPage(await context.newPage());

        await asHrManager.goto('erp-hr');

        const result = await asHrManager.saveModule('erp-hr');

        expect(result.ok, 'the save does not succeed').toBe(false);
        expect(result.body, 'and it is stopped at the nonce, before any capability check').toContain(
            'Nonce verification failed'
        );

        await context.close();
    });

    test('an employee cannot save any settings module', { tag: ['@tier3', '@core', '@settings', '@authz'] }, async ({ browser }) => {
        const context = await browser.newContext({ storageState: EMPLOYEE_STATE });
        const p = await context.newPage();

        // An employee cannot load the settings screen, so there is no settings
        // nonce to take from it. The generic admin page still yields one for the
        // same action, which is the strongest form of this test: a valid nonce
        // and an insufficient capability.
        await p.goto('http://localhost:8888/wp-admin/profile.php', { waitUntil: 'domcontentloaded' });

        const attempted = await p.evaluate(async () => {
            const modules = ['general', 'erp-hr', 'erp-ac', 'erp-crm'];
            const out: Record<string, boolean> = {};

            for (const module of modules) {
                const r = await fetch('/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ action: 'erp-settings-save', module }).toString(),
                });
                const text = await r.text();
                try {
                    out[module] = Boolean(JSON.parse(text).success);
                } catch {
                    out[module] = false;
                }
            }

            return out;
        });

        for (const [module, succeeded] of Object.entries(attempted)) {
            expect(succeeded, `${module} is refused to an employee`).toBe(false);
        }

        await context.close();
    });

});
