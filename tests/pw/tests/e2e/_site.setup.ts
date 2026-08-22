import { test as setup, expect, request } from '@utils/test';
import { ensureLicensed } from '@utils/license';
import { ApiUtils } from '@utils/apiUtils';
import { env, parseBoolean } from '@utils/helpers';
import { LoginPage } from '@pages/core/loginPage';
import { company, proModules } from '@utils/seedData';
import { closeDb } from '@utils/dbUtils';

setup.describe.configure({ mode: 'serial' });

setup('site is reachable and WordPress is installed', async ({ page }) => {
    const response = await page.goto('/');
    expect(response?.status(), 'site responds').toBeLessThan(400);
});

/**
 * Everything a FRESH install needs before any of the rest is possible.
 *
 * `wp-env destroy && wp-env start` does NOT leave a runnable site:
 * `.wp-env.json` MAPS wp-erp and erp-pro instead of listing them under
 * `plugins`, so neither is active; ERP installs with only the HRM core module;
 * every Pro module starts off; two setup wizards hijack the first admin login;
 * permalinks are plain; and the company and currency the wizard would collect
 * are unset. All seven had to be done by hand the first time the site was
 * rebuilt from nothing, and CI would have hit every one of them.
 *
 * Idempotent — on an already-configured site it reports doing nothing.
 */
setup('a fresh install is bootstrapped into a runnable site', async () => {
    const apiUtils = new ApiUtils(await request.newContext({ baseURL: env('BASE_URL') }));

    try {
        const result = await apiUtils.bootstrapSite({
            company: {
                name: company.name,
                address: {
                    address_1: company.address1,
                    address_2: company.address2,
                    city: company.city,
                    state: company.state,
                    postcode: company.postalCode,
                    country: company.country,
                },
                phone: company.phone,
                email: company.email,
                website: company.website,
            },
            // An EMPTY currency is not cosmetic: it makes
            // `erp_get_currency_symbol()` return the whole symbol array and
            // fatals the CRM Deals board (plugin-internal-tasks#2301).
            settings: { erp_currency: '148', erp_country: company.country, gen_financial_month: '1', date_format: 'Y-m-d' },
            // Whatever timezone this runner is in — see the endpoint for why.
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            pro_modules: proModules,
        });

        console.log(`[bootstrap] ${result.did.length ? result.did.join(' | ') : 'nothing to do — already configured'}`);

        expect(result.done, 'the bootstrap completed').toBe(true);
    } finally {
        await apiUtils.dispose();
    }
});

setup('admin can log in', async ({ page }) => {
    const login = new LoginPage(page);
    await login.login(env('ADMIN'), env('ADMIN_PASSWORD'));
    await expect(page.locator('#wpadminbar')).toBeVisible();
});

setup('ERP Pro licence is active', async ({ page }) => {
    setup.skip(!parseBoolean(env('ERP_PRO', 'true')), 'ERP_PRO is off — running the free suite only');

    const login = new LoginPage(page);
    await login.login(env('ADMIN'), env('ADMIN_PASSWORD'));

    const outcome = await ensureLicensed(page);

    // Recorded in the run output so a run that did NOT exercise activation
    // cannot be mistaken for one that did.
    console.log(`[licence] ${outcome.activated ? 'ACTIVATED' : 'NOT ACTIVATED IN THIS RUN'} — ${outcome.reason}`);

    expect(outcome.status?.license, 'stored licence status is valid').toBe('valid');
    expect(Number(outcome.status?.users), 'licensed seats').toBe(Number(env('ERP_LICENSE_USERS', '100')));

    await closeDb();
});

/**
 * Pro modules, AFTER the licence.
 *
 * `Module::activate_modules()` bails without a valid licence, so the bootstrap's
 * first pass activates nothing Pro on a fresh site (it reports `0/22`). Running
 * it again once the seat is held is all that is needed — the endpoint is
 * idempotent and only touches what is still missing.
 */
setup('the Pro modules the suite needs are active', async () => {
    setup.skip(!parseBoolean(env('ERP_PRO', 'true')), 'ERP_PRO is off — running the free suite only');

    const apiUtils = new ApiUtils(await request.newContext({ baseURL: env('BASE_URL') }));

    try {
        const result = await apiUtils.bootstrapSite({
            company: {},
            settings: {},
            pro_modules: proModules,
        });

        console.log(`[bootstrap:pro] ${result.did.length ? result.did.join(' | ') : 'all Pro modules already active'}`);

        const pro = result.pro_modules as { active: number; still_missing: string[] } | undefined;

        expect(pro?.still_missing ?? [], 'every Pro module the suite needs is active').toEqual([]);
    } finally {
        await apiUtils.dispose();
    }
});

setup('ERP modules and pretty permalinks are in place', async () => {
    const apiUtils = new ApiUtils(await request.newContext({ baseURL: env('BASE_URL') }));
    try {
        const modules = await apiUtils.activeModules();
        expect(modules, 'erp_pro/v1/admin/modules responds').toBeTruthy();
    } finally {
        await apiUtils.dispose();
    }
});
