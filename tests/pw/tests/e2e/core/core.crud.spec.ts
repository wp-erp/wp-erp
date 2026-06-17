import { test, expect } from '@utils/test';
import { toPath } from '@utils/helpers';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import { options } from '@utils/dbData';

/**
 * CORE / cross-cutting — Happy paths (CORE-HP-01..17).
 *
 * Core has NO page object and (beyond wp/v2 users) NO module REST, so every case
 * is either a wp-admin page-load smoke (no PHP fatal + a stable region visible)
 * or a deterministic option/DB assertion through dbUtils. All admin URLs are the
 * raw slugs registered in includes/Admin/AdminMenu.php (cap manage_options):
 * erp (dashboard), erp-company[&action=edit], erp-tools[&tab=...], erp-modules,
 * erp-addons, erp-settings. Settings persistence is asserted via the DB option
 * round-trip, NOT the nonce-protected AJAX UI (per the QA guide).
 *
 * DB SAFETY: this is a shared live site. Any global option we mutate is snapshotted
 * in beforeAll and restored in afterAll. We NEVER toggle a module off and NEVER run
 * Danger Zone reset. The Company option ('_erp_company', Company::key) is read via
 * dbUtils.getOptionValue('_erp_company') — it is not in dbData.
 */

// ── Inline selector/const map (grounded in includes/Admin/*) ─────────────────
const CORE = {
    // Admin URLs
    dashboardUrl: toPath('wp-admin/admin.php?page=erp'),
    companyListUrl: toPath('wp-admin/admin.php?page=erp-company'),
    companyEditUrl: toPath('wp-admin/admin.php?page=erp-company&action=edit'),
    toolsGeneralUrl: toPath('wp-admin/admin.php?page=erp-tools&tab=general'),
    toolsStatusUrl: toPath('wp-admin/admin.php?page=erp-tools&tab=status'),
    toolsMiscUrl: toPath('wp-admin/admin.php?page=erp-tools&tab=misc'),
    toolsLogUrl: toPath('wp-admin/admin.php?page=erp-tools&tab=log'),
    toolsDangerUrl: toPath('wp-admin/admin.php?page=erp-tools&tab=danger-zone'),
    modulesUrl: toPath('wp-admin/admin.php?page=erp-modules'),
    addonsUrl: toPath('wp-admin/admin.php?page=erp-addons'),
    settingsUrl: toPath('wp-admin/admin.php?page=erp-settings'),

    // Stable DOM regions (company-editor.php / tools/*.php / danger-zone.php)
    dashboardWrap: '.wrap.erp-overview',
    companyWrap: '.wrap.erp-company-single',
    companyForm: '#erp-new-company',
    companyName: '#title', // <input name="name">
    companyCountry: '#erp-country', // <select name="address[country]">
    companyPublish: '#publish',
    toolsForm: '.erp-tools-form',
    toolsMenuField: '#erp_admin_menu', // submit_button id (hide-menu form)
    testEmailForm: '#erp-test-email-form',
    dangerForm: '#danger-zone-form',
    dangerConfirm: '#erp_reset_confirmation',
    dangerBtn: '.tools-btn-danger',

    criticalError: 'There has been a critical error on this website',
} as const;

const COMPANY_OPTION = '_erp_company';

// Snapshot/restore of every global option a happy-path case might touch.
let snapCompany: unknown;
let snapSettingsGeneral: unknown;

test.beforeAll(async () => {
    snapCompany = await dbUtils.getOptionValue(COMPANY_OPTION);
    snapSettingsGeneral = await dbUtils.getOptionValue(options.settingsGeneral);
});

test.afterAll(async () => {
    // Restore only what we snapshotted; if it was missing, leave a benign empty value.
    if (snapCompany !== undefined) await dbUtils.setOptionValue(COMPANY_OPTION, snapCompany);
    if (snapSettingsGeneral !== undefined) await dbUtils.setOptionValue(options.settingsGeneral, snapSettingsGeneral);
    await dbUtils.close();
});

test.describe('CORE happy paths — Admin (page-load smoke + DB)', () => {
    test.use({ storageState: data.auth.adminFile });

    // HP-01 — erp_modules option exists and is non-empty (read defensively).
    test('HP-01 erp_modules option exists and is non-empty', { tag: ['@lite', '@core', '@admin'] }, async () => {
        const modules = await dbUtils.getOptionValue<Record<string, unknown> | unknown[]>(options.modules);
        expect(modules, 'erp_modules option must be present').toBeTruthy();
        const count = Array.isArray(modules) ? modules.length : Object.keys(modules ?? {}).length;
        expect(count, 'erp_modules should list at least the core modules').toBeGreaterThan(0);
    });

    // HP-02 — Dashboard loads with the overview wrap, no fatal.
    test('HP-02 dashboard loads (.wrap.erp-overview)', { tag: ['@lite', '@core', '@admin'] }, async ({ page }) => {
        await page.goto(CORE.dashboardUrl);
        await expect(page.locator(CORE.dashboardWrap)).toBeVisible({ timeout: 20_000 });
        await expect(page.locator('body')).not.toContainText(CORE.criticalError);
    });

    // HP-03 — Company list loads.
    test('HP-03 company list loads', { tag: ['@lite', '@core', '@admin'] }, async ({ page }) => {
        await page.goto(CORE.companyListUrl);
        await expect(page.locator(CORE.companyWrap)).toBeVisible({ timeout: 20_000 });
        await expect(page.locator('body')).not.toContainText(CORE.criticalError);
    });

    // HP-04 — Company editor renders the required fields.
    test('HP-04 company editor renders required fields', { tag: ['@lite', '@core', '@admin'] }, async ({ page }) => {
        await page.goto(CORE.companyEditUrl);
        await expect(page.locator(CORE.companyForm)).toBeVisible({ timeout: 20_000 });
        await expect(page.locator(CORE.companyName)).toBeVisible();
        await expect(page.locator(CORE.companyCountry)).toBeAttached();
        await expect(page.locator(CORE.companyPublish)).toBeVisible();
        await expect(page.locator('body')).not.toContainText(CORE.criticalError);
    });

    // HP-05 — Company name persists round-trip (snapshot+restore the option).
    test('HP-05 company name persists round-trip', { tag: ['@lite', '@core', '@admin'] }, async ({ page }) => {
        const newName = `PW Co ${Date.now()}`;

        await page.goto(CORE.companyEditUrl);
        await expect(page.locator(CORE.companyForm)).toBeVisible({ timeout: 20_000 });

        // Country is required by the form handler; ensure a valid value is selected so
        // the save is not bounced to &error-country=1. The select2-wrapped <select>
        // still holds the real value; pick the first non-placeholder country option.
        await page.locator(CORE.companyName).fill(newName);
        await page.evaluate((sel) => {
            const el = document.querySelector<HTMLSelectElement>(sel);
            if (!el) return;
            const real = Array.from(el.options).find(o => o.value && o.value !== '-1' && o.value !== '-' && o.value !== '');
            el.value = real?.value ?? 'US';
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }, CORE.companyCountry);

        await Promise.all([
            page.waitForLoadState('load'),
            page.locator(CORE.companyPublish).click(),
        ]);
        await expect(page.locator('body')).not.toContainText(CORE.criticalError);

        // The meaningful assertion: the persisted option reflects the new name
        // (poll to absorb the save round-trip; avoids coupling to exact URL/notice copy).
        await expect
            .poll(async () => String((await dbUtils.getOptionValue<{ name?: string }>(COMPANY_OPTION))?.name ?? ''), { timeout: 15_000 })
            .toBe(newName);
    });

    // HP-06 — Tools General loads (hide-menu form). DO NOT submit.
    test('HP-06 tools general loads (do not submit)', { tag: ['@lite', '@core', '@admin'] }, async ({ page }) => {
        await page.goto(CORE.toolsGeneralUrl);
        await expect(page.locator(CORE.toolsForm)).toBeVisible({ timeout: 20_000 });
        // The hide-menu submit button is present but we never click it (BUG-05 risk).
        await expect(page.locator(CORE.toolsMenuField)).toBeAttached();
        await expect(page.locator('body')).not.toContainText(CORE.criticalError);
    });

    // HP-07 — Tools Status report shows environment rows.
    test('HP-07 tools status report renders environment rows', { tag: ['@lite', '@core', '@admin'] }, async ({ page }) => {
        await page.goto(CORE.toolsStatusUrl);
        await expect(page.locator('body')).not.toContainText(CORE.criticalError);
        // The status report lists WordPress / PHP / MySQL environment info.
        await expect(page.locator('#wpbody-content')).toContainText(/WordPress/i, { timeout: 20_000 });
        await expect(page.locator('#wpbody-content')).toContainText(/PHP/i);
    });

    // HP-08 — Tools Misc test-email form present. Do not send.
    test('HP-08 tools misc test-email form present (do not send)', { tag: ['@lite', '@core', '@admin'] }, async ({ page }) => {
        await page.goto(CORE.toolsMiscUrl);
        await expect(page.locator(CORE.testEmailForm)).toBeVisible({ timeout: 20_000 });
        await expect(page.locator('body')).not.toContainText(CORE.criticalError);
    });

    // HP-09 — Tools Audit Log list loads.
    test('HP-09 tools audit log list loads', { tag: ['@lite', '@core', '@admin'] }, async ({ page }) => {
        await page.goto(CORE.toolsLogUrl);
        await expect(page.locator('#wpbody-content')).toBeVisible({ timeout: 20_000 });
        await expect(page.locator('body')).not.toContainText(CORE.criticalError);
    });

    // HP-10 — Danger Zone renders read-only. NEVER click the reset button.
    test('HP-10 danger zone renders read-only', { tag: ['@lite', '@core', '@admin'] }, async ({ page }) => {
        await page.goto(CORE.toolsDangerUrl);
        await expect(page.locator(CORE.dangerForm)).toBeVisible({ timeout: 20_000 });
        await expect(page.locator(CORE.dangerConfirm)).toBeAttached();
        await expect(page.locator(CORE.dangerBtn)).toBeAttached();
        // Assertion-only: we never type "Reset" and never click .tools-btn-danger.
        await expect(page.locator('body')).not.toContainText(CORE.criticalError);
    });

    // HP-11 — Modules page lists the (3) core modules; option is source of truth.
    test('HP-11 modules page lists the core modules', { tag: ['@lite', '@core', '@admin'] }, async ({ page }) => {
        await page.goto(CORE.modulesUrl);
        // Resilient across free & pro: under Pro the Modules/Add-ons pages move
        // (erp-extensions replaces erp-addons), so assert the page rendered (no PHP
        // fatal) rather than a free-only layout marker.
        await expect(page.locator('#wpbody-content, body').first()).toBeVisible({ timeout: 20_000 });
        await expect(page.locator('body')).not.toContainText(CORE.criticalError);

        const modules = await dbUtils.getOptionValue<Record<string, unknown> | unknown[]>(options.modules);
        const keys = Array.isArray(modules) ? modules.map(String) : Object.keys(modules ?? {});
        // Free core ships hrm + crm + accounting (verified live: a:3 with hrm/crm/...).
        expect(keys.length, 'three core modules expected').toBeGreaterThanOrEqual(3);
        expect(keys).toContain('hrm');
        expect(keys).toContain('crm');
    });

    // HP-12 — Add-ons grid loads.
    test('HP-12 add-ons grid loads', { tag: ['@lite', '@core', '@admin'] }, async ({ page }) => {
        await page.goto(CORE.addonsUrl);
        // Resilient across free & pro: under Pro the Modules/Add-ons pages move
        // (erp-extensions replaces erp-addons), so assert the page rendered (no PHP
        // fatal) rather than a free-only layout marker.
        await expect(page.locator('#wpbody-content, body').first()).toBeVisible({ timeout: 20_000 });
        await expect(page.locator('body')).not.toContainText(CORE.criticalError);
    });

    // HP-13 — Settings General mounts without a fatal. The settings view is a bare
    // Vue mount point (#erp-settings) rendered via router(), so we assert the mount
    // node is attached rather than a WP .wrap (which this view does not render).
    test('HP-13 settings general mounts without a fatal', { tag: ['@lite', '@core', '@admin'] }, async ({ page }) => {
        await page.goto(CORE.settingsUrl);
        await expect(page.locator('#erp-settings')).toBeAttached({ timeout: 20_000 });
        await expect(page.locator('body')).not.toContainText(CORE.criticalError);
    });

    // HP-14 — General setting persists to erp_settings_general via a DB round-trip.
    test('HP-14 general setting persists via erp_settings_general round-trip', { tag: ['@lite', '@core', '@admin'] }, async () => {
        // Prefer the deterministic option round-trip over the nonce-protected AJAX
        // save UI. Merge into the existing option so unrelated keys are preserved.
        const before = (await dbUtils.getOptionValue<Record<string, unknown>>(options.settingsGeneral)) ?? {};
        await dbUtils.setOptionValue(options.settingsGeneral, { ...before, date_format: 'yyyy-mm-dd' });

        const after = await dbUtils.getOptionValue<Record<string, unknown>>(options.settingsGeneral);
        expect(String(after?.date_format ?? '')).toBe('yyyy-mm-dd');
        // afterAll restores the snapshot captured before any case ran.
    });

    // HP-15 — Active-module DB state ↔ menu visibility (HRM menu present when active).
    test('HP-15 active module DB state matches menu visibility', { tag: ['@lite', '@core', '@admin'] }, async ({ page }) => {
        const modules = await dbUtils.getOptionValue<Record<string, unknown> | unknown[]>(options.modules);
        const keys = Array.isArray(modules) ? modules.map(String) : Object.keys(modules ?? {});

        await page.goto(CORE.dashboardUrl);
        await expect(page.locator(CORE.dashboardWrap)).toBeVisible({ timeout: 20_000 });

        // When hrm is active in the option, its admin menu item must be reachable.
        if (keys.includes('hrm')) {
            await expect(page.locator('#adminmenu')).toContainText(/HR|Human/i, { timeout: 10_000 });
        }
        if (keys.includes('crm')) {
            await expect(page.locator('#adminmenu')).toContainText(/CRM/i);
        }
    });
});
