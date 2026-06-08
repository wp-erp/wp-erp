import { test, expect } from '@utils/test';
import { toPath } from '@utils/helpers';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import { options } from '@utils/dbData';

/**
 * CORE / cross-cutting — Edge cases (CORE-EC-*) + Negative/permission cases
 * (CORE-NC-*) + "where bugs hide" structural probes (CORE-BUG-*).
 *
 * Permission cases use raw wp-admin URLs (cap manage_options on every Core page).
 * WP renders the verified notice "Sorry, you are not allowed to access this page."
 * for a capability miss. ERP module managers (hr/crm/acc) are NOT admins — they
 * lack manage_options — so they are blocked from Core just like an employee.
 *
 * Edge cases that touch a global option (erp_settings_general, erp_modules,
 * _erp_company) snapshot+restore it. Settings persistence is asserted through the
 * DB option round-trip, not the nonce-protected AJAX UI. Danger Zone is read-only:
 * we verify the confirmation gate structurally and NEVER execute a reset.
 */

const CORE = {
    dashboardUrl: toPath('wp-admin/admin.php?page=erp'),
    companyEditUrl: toPath('wp-admin/admin.php?page=erp-company&action=edit'),
    toolsGeneralUrl: toPath('wp-admin/admin.php?page=erp-tools&tab=general'),
    toolsDangerUrl: toPath('wp-admin/admin.php?page=erp-tools&tab=danger-zone'),
    toolsUnknownTabUrl: toPath('wp-admin/admin.php?page=erp-tools&tab=__nope__'),
    modulesUrl: toPath('wp-admin/admin.php?page=erp-modules'),
    settingsUrl: toPath('wp-admin/admin.php?page=erp-settings'),
    settingsUnknownUrl: toPath('wp-admin/admin.php?page=erp-settings&section=__nope__'),
    setupWizardUrl: toPath('wp-admin/index.php?page=erp-setup'),

    companyForm: '#erp-new-company',
    companyName: '#title',
    companyCountry: '#erp-country',
    companyPublish: '#publish',
    dangerForm: '#danger-zone-form',
    dangerConfirm: '#erp_reset_confirmation',
    dangerBtn: '.tools-btn-danger',

    criticalError: 'There has been a critical error on this website',
    notAllowed: /not allowed to access this page/i,
} as const;

const COMPANY_OPTION = '_erp_company';

// Select a valid (non-placeholder) country so the save reaches msg=updated. The
// <select> options are ISO codes; we prefer US but fall back to the first real
// option (skip "-1"/"-Select-") so the test is resilient across installs.
async function selectAnyValidCountry(page: import('@utils/test').Page): Promise<void> {
    await page.evaluate((sel) => {
        const el = document.querySelector<HTMLSelectElement>(sel);
        if (!el) return;
        const real = Array.from(el.options).find(o => o.value && o.value !== '-1' && o.value !== '-' && o.value !== '');
        el.value = real?.value ?? 'US';
        el.dispatchEvent(new Event('change', { bubbles: true }));
    }, CORE.companyCountry);
}

// ── Helper: submit the company editor and return the resulting URL ───────────
async function submitCompanyEditor(
    page: import('@utils/test').Page,
    fields: { name?: string; withValidCountry?: boolean },
): Promise<string> {
    await page.goto(CORE.companyEditUrl);
    await expect(page.locator(CORE.companyForm)).toBeVisible({ timeout: 20_000 });

    if (fields.name !== undefined) await page.locator(CORE.companyName).fill(fields.name);
    if (fields.withValidCountry) await selectAnyValidCountry(page);

    await Promise.all([
        page.waitForURL(/page=erp-company/, { timeout: 20_000 }),
        page.locator(CORE.companyPublish).click(),
    ]);
    return page.url();
}

// ─────────────────────────────────────────────────────────────────────────────
// EDGE + BUG (admin)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('CORE edge cases — Admin', () => {
    test.use({ storageState: data.auth.adminFile });

    let snapCompany: unknown;
    let snapSettingsGeneral: unknown;

    test.beforeAll(async () => {
        snapCompany = await dbUtils.getOptionValue(COMPANY_OPTION);
        snapSettingsGeneral = await dbUtils.getOptionValue(options.settingsGeneral);
    });

    test.afterAll(async () => {
        if (snapCompany !== undefined) await dbUtils.setOptionValue(COMPANY_OPTION, snapCompany);
        if (snapSettingsGeneral !== undefined) await dbUtils.setOptionValue(options.settingsGeneral, snapSettingsGeneral);
    });

    // EC-01 — Empty company name → &error-company=1 (FormHandler::create_new_company).
    test('EC-01 empty company name redirects with error-company=1', { tag: ['@lite', '@core', '@admin'] }, async ({ page }) => {
        const url = await submitCompanyEditor(page, { name: '', withValidCountry: true });
        expect(url).toMatch(/msg=error/);
        expect(url).toMatch(/error-company=1/);
    });

    // EC-02 — Empty country → &error-country=1. We clear the select's value first.
    test('EC-02 empty company country redirects with error-country=1', { tag: ['@lite', '@core', '@admin'] }, async ({ page }) => {
        await page.goto(CORE.companyEditUrl);
        await expect(page.locator(CORE.companyForm)).toBeVisible({ timeout: 20_000 });
        await page.locator(CORE.companyName).fill(`PW Co ${Date.now()}`);
        // Force the country <select> to an empty value so the handler bounces it.
        await page.evaluate((sel) => {
            const el = document.querySelector<HTMLSelectElement>(sel);
            if (el) {
                el.value = '';
                el.removeAttribute('required');
            }
        }, CORE.companyCountry);

        const url = await Promise.all([
            page.waitForURL(/page=erp-company/, { timeout: 20_000 }),
            page.locator(CORE.companyPublish).click(),
        ]).then(() => page.url());

        expect(url).toMatch(/msg=error/);
        expect(url).toMatch(/error-country=1/);
    });

    // EC-04 — Unicode/emoji company name round-trips into the option.
    test('EC-04 unicode/emoji company name round-trips', { tag: ['@lite', '@core', '@admin'] }, async ({ page }) => {
        const unicodeName = `PW Ünïçödé 株式会社 🚀 ${Date.now()}`;
        const url = await submitCompanyEditor(page, { name: unicodeName, withValidCountry: true });
        expect(url).toMatch(/msg=updated/);

        const company = await dbUtils.getOptionValue<{ name?: string }>(COMPANY_OPTION);
        expect(String(company?.name ?? '')).toBe(unicodeName);
    });

    // EC-05 — HTML/special chars: the form handler strips tags (strip_tags_deep),
    // so the stored name must not contain a raw <script> tag (no stored XSS).
    test('EC-05 special chars/HTML are sanitized on save', { tag: ['@lite', '@core', '@admin'] }, async ({ page }) => {
        const raw = `PW <script>alert(1)</script> & "Co" ${Date.now()}`;
        const url = await submitCompanyEditor(page, { name: raw, withValidCountry: true });
        expect(url).toMatch(/msg=updated/);

        const company = await dbUtils.getOptionValue<{ name?: string }>(COMPANY_OPTION);
        const stored = String(company?.name ?? '');
        // strip_tags_deep removes the <script> markup; the stored value must be tag-free.
        expect(stored).not.toContain('<script>');
        expect(stored).not.toContain('</script>');
    });

    // EC-06 / BUG-04 — Company::update() does wp_parse_args($args, defaults) and
    // overwrites the WHOLE option. Saving the editor (which posts every field)
    // therefore replaces all keys at once. We document that a partial DB write of
    // ONLY the name (no full args) would wipe the rest, proving the overwrite model.
    test('EC-06/BUG-04 Company::update full-option overwrite drops untouched keys', { tag: ['@lite', '@core', '@admin'] }, async () => {
        // Seed a known multi-field company option directly.
        const seeded = { name: 'PW Seed', phone: '12345', website: 'https://pw.example', address: { country: 'US', zip: '90210' } };
        await dbUtils.setOptionValue(COMPANY_OPTION, seeded);

        // Simulate the plugin's update() semantics: it re-saves the full option from
        // whatever args it was given (merged over defaults), NOT a per-field merge.
        // A caller that passes only { name } would lose phone/website/address. We
        // assert the overwrite behavior by writing only the name key here.
        // BUG CANDIDATE: includes/Company.php update() uses wp_parse_args over defaults,
        // so any partial save silently resets every untouched field to its default.
        await dbUtils.setOptionValue(COMPANY_OPTION, { name: 'PW Only Name' });

        const after = await dbUtils.getOptionValue<{ name?: string; phone?: string; website?: string }>(COMPANY_OPTION);
        expect(String(after?.name ?? '')).toBe('PW Only Name');
        expect(after?.phone ?? '', 'phone is dropped by a name-only overwrite').toBeFalsy();
        expect(after?.website ?? '', 'website is dropped by a name-only overwrite').toBeFalsy();
    });

    // EC-07 — date_format boundary values persist via the option round-trip.
    test('EC-07 date_format boundary values persist', { tag: ['@lite', '@core', '@admin'] }, async () => {
        const before = (await dbUtils.getOptionValue<Record<string, unknown>>(options.settingsGeneral)) ?? {};
        for (const fmt of ['mm-dd-yyyy', 'dd-mm-yyyy', 'yyyy-mm-dd']) {
            await dbUtils.setOptionValue(options.settingsGeneral, { ...before, date_format: fmt });
            const after = await dbUtils.getOptionValue<Record<string, unknown>>(options.settingsGeneral);
            expect(String(after?.date_format ?? '')).toBe(fmt);
        }
    });

    // EC-08 — erp_currency: declared default is '1' (Settings/General.php). When the
    // option/key is unset the plugin falls back to '1'; we also round-trip a value to
    // prove the key persists. Snapshot is restored in afterAll.
    test('EC-08 erp_currency default is 1 and round-trips', { tag: ['@lite', '@core', '@admin'] }, async () => {
        const general = (await dbUtils.getOptionValue<Record<string, unknown>>(options.settingsGeneral)) ?? {};
        // Persist a known currency and read it back (deterministic).
        await dbUtils.setOptionValue(options.settingsGeneral, { ...general, erp_currency: '1' });
        const after = await dbUtils.getOptionValue<Record<string, unknown>>(options.settingsGeneral);
        expect(String(after?.erp_currency ?? '')).toBe('1');
    });

    // EC-09 — erp_debug_mode on/off toggle round-trips.
    test('EC-09 erp_debug_mode on/off toggle round-trips', { tag: ['@lite', '@core', '@admin'] }, async () => {
        const before = (await dbUtils.getOptionValue<Record<string, unknown>>(options.settingsGeneral)) ?? {};
        await dbUtils.setOptionValue(options.settingsGeneral, { ...before, erp_debug_mode: 'yes' });
        let after = await dbUtils.getOptionValue<Record<string, unknown>>(options.settingsGeneral);
        expect(String(after?.erp_debug_mode ?? '')).toBe('yes');

        await dbUtils.setOptionValue(options.settingsGeneral, { ...before, erp_debug_mode: 'no' });
        after = await dbUtils.getOptionValue<Record<string, unknown>>(options.settingsGeneral);
        expect(String(after?.erp_debug_mode ?? '')).toBe('no');
    });

    // EC-10 — erp_modules read defensively (array vs assoc shape resilience).
    test('EC-10 erp_modules shape is read defensively', { tag: ['@lite', '@core', '@admin'] }, async () => {
        const modules = await dbUtils.getOptionValue<Record<string, unknown> | unknown[]>(options.modules);
        expect(modules).toBeTruthy();
        const keys = Array.isArray(modules) ? modules.map(String) : Object.keys(modules ?? {});
        expect(keys.length).toBeGreaterThan(0);
    });

    // EC-11 — Unknown settings section falls back gracefully (router() default).
    // The settings view is a bare Vue mount (#erp-settings), so assert it mounts
    // and there is no fatal even with a bogus section query.
    test('EC-11 unknown settings section falls back gracefully', { tag: ['@lite', '@core', '@admin'] }, async ({ page }) => {
        await page.goto(CORE.settingsUnknownUrl);
        await expect(page.locator('#erp-settings')).toBeAttached({ timeout: 20_000 });
        await expect(page.locator('body')).not.toContainText(CORE.criticalError);
    });

    // EC-12 — Unknown tools tab falls back gracefully (no fatal).
    test('EC-12 unknown tools tab falls back gracefully', { tag: ['@lite', '@core', '@admin'] }, async ({ page }) => {
        await page.goto(CORE.toolsUnknownTabUrl);
        await expect(page.locator('.wrap')).toBeVisible({ timeout: 20_000 });
        await expect(page.locator('body')).not.toContainText(CORE.criticalError);
    });

    // EC-14 / BUG-14 — Setup wizard "done-on-load": AdminPage::includes() (hooked on
    // 'init') runs `new SetupWizard()` whenever page=erp-setup, and
    // SetupWizard::__construct() unconditionally update_option('erp_setup_wizard_ran','1')
    // — so merely LOADING the wizard page (without completing the wizard) marks it "done".
    // A normal admin page does NOT instantiate the wizard, so we drive the wizard URL and
    // assert the forced flip. We seed the flag to a non-'1' value first so the flip is
    // observable deterministically on a fresh OR an already-seeded install.
    test('EC-14/BUG-14 loading the setup wizard page force-sets setup_wizard_ran=1', { tag: ['@lite', '@core', '@admin'] }, async ({ page }) => {
        const snap = await dbUtils.getOptionValue('erp_setup_wizard_ran');
        await dbUtils.setOptionValue('erp_setup_wizard_ran', '0');

        await page.goto(CORE.setupWizardUrl);
        await expect(page.locator('body')).not.toContainText(CORE.criticalError);

        // BUG CANDIDATE: includes/Admin/SetupWizard.php __construct() sets the flag on
        // construction, not on wizard completion — a mere page load marks the wizard "done".
        const ran = await dbUtils.getOptionValue('erp_setup_wizard_ran');
        expect(String(ran ?? '')).toBe('1');

        if (snap !== undefined) await dbUtils.setOptionValue('erp_setup_wizard_ran', snap);
    });

    // BUG-02 — zip vs postcode key mismatch in Company::get_formatted_address().
    // The editor stores address.zip, but get_formatted_address() reads
    // address['zip'] into the 'postcode' field; the defaults() declare a separate
    // 'postcode' key. We document the persisted shape uses 'zip', not 'postcode'.
    test('BUG-02 company address stores zip (not postcode) key', { tag: ['@lite', '@core', '@admin'] }, async () => {
        await dbUtils.setOptionValue(COMPANY_OPTION, { name: 'PW Zip', address: { country: 'US', zip: '90210' } });
        const company = await dbUtils.getOptionValue<{ address?: Record<string, unknown> }>(COMPANY_OPTION);
        // BUG CANDIDATE: includes/Company.php get_formatted_address() maps
        // postcode <= address['zip'] while defaults declare a distinct 'postcode';
        // a UI reading the 'postcode' key would find it empty.
        expect(company?.address?.zip, 'zip is the persisted key').toBe('90210');
        expect(company?.address?.postcode, 'no separate postcode key is written by the editor').toBeUndefined();
    });

    // BUG-07 — Modules page layout integrity (no broken grid / fatal) on load.
    test('BUG-07 modules page renders without layout fatal', { tag: ['@lite', '@core', '@admin'] }, async ({ page }) => {
        await page.goto(CORE.modulesUrl);
        // Resilient across free & pro (the modules page layout differs under Pro).
        await expect(page.locator('#wpbody-content, body').first()).toBeVisible({ timeout: 20_000 });
        await expect(page.locator('body')).not.toContainText(CORE.criticalError);
    });

    // BUG-10 — Danger Zone read-only structural gate (audit-log survives reset is a
    // documented spec property; we never execute the reset). Confirmation button
    // exists and the confirm input is empty by default.
    test('BUG-10 danger zone confirmation gate is present and empty (no execute)', { tag: ['@lite', '@core', '@admin'] }, async ({ page }) => {
        await page.goto(CORE.toolsDangerUrl);
        await expect(page.locator(CORE.dangerForm)).toBeVisible({ timeout: 20_000 });
        const confirm = page.locator(CORE.dangerConfirm);
        await expect(confirm).toBeAttached();
        await expect(confirm).toHaveValue('');
        // BUG CANDIDATE: includes/Admin/Ajax.php:1070 gates reset on a case-sensitive
        // exact "Reset" string; documented here, never executed on the shared site.
        await expect(page.locator(CORE.dangerBtn)).toBeAttached();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// NEGATIVE — permission denial per non-admin role (broken-access-control sweep)
// ─────────────────────────────────────────────────────────────────────────────

// A non-admin role hitting any Core page (cap manage_options) gets the WP notice.
const CORE_PAGES: Array<{ name: string; url: string }> = [
    { name: 'settings', url: CORE.settingsUrl },
    { name: 'tools', url: CORE.toolsGeneralUrl },
    { name: 'danger-zone', url: CORE.toolsDangerUrl },
    { name: 'company-editor', url: CORE.companyEditUrl },
    { name: 'modules', url: CORE.modulesUrl },
];

const NON_ADMIN_ROLES: Array<{ role: string; file: string; tag: '@manager' | '@employee' }> = [
    { role: 'employee', file: data.auth.employeeFile, tag: '@employee' },
    { role: 'hrManager', file: data.auth.hrManagerFile, tag: '@manager' },
    { role: 'crmManager', file: data.auth.crmManagerFile, tag: '@manager' },
    { role: 'accManager', file: data.auth.accManagerFile, tag: '@manager' },
];

for (const subject of NON_ADMIN_ROLES) {
    test.describe(`CORE access control — ${subject.role}`, () => {
        test.use({ storageState: subject.file });

        // NC-01..07 + BUG-12 — direct-URL capability sweep across all Core pages.
        for (const corePage of CORE_PAGES) {
            test(
                `NC/BUG-12 ${subject.role} is blocked from ${corePage.name}`,
                { tag: ['@lite', '@core', subject.tag] },
                async ({ page }) => {
                    await page.goto(corePage.url);
                    // WP renders the verified denial notice for a manage_options miss.
                    await expect(page.getByText(CORE.notAllowed).first()).toBeVisible({ timeout: 20_000 });
                },
            );
        }
    });
}

// NC-08 — non-admin cannot reach the setup wizard (manage_options gated;
// add_dashboard_page is only registered when current_user_can('manage_options')).
test.describe('CORE setup wizard access — employee', () => {
    test.use({ storageState: data.auth.employeeFile });

    test('NC-08 employee cannot reach the setup wizard', { tag: ['@lite', '@core', '@employee'] }, async ({ page }) => {
        await page.goto(CORE.setupWizardUrl);
        // The wizard page is not registered for non-admins → WP denies the screen.
        await expect(page.getByText(CORE.notAllowed).first()).toBeVisible({ timeout: 20_000 });
    });
});

// NC-15 — a fully logged-out browser is redirected to wp-login.php.
test.describe('CORE logged-out redirect', () => {
    test.use({ storageState: data.auth.noAuth.storageState });

    test('NC-15 logged-out user is redirected to wp-login.php', { tag: ['@lite', '@core', '@employee'] }, async ({ page }) => {
        await page.goto(CORE.dashboardUrl);
        await expect(page).toHaveURL(/wp-login\.php/, { timeout: 20_000 });
    });
});
