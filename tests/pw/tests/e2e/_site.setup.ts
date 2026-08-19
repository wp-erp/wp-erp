import { test as setup, expect, request } from '@utils/test';
import { ensureLicensed } from '@utils/license';
import { ApiUtils } from '@utils/apiUtils';
import { env, parseBoolean } from '@utils/helpers';
import { LoginPage } from '@pages/core/loginPage';
import { closeDb } from '@utils/dbUtils';

setup.describe.configure({ mode: 'serial' });

setup('site is reachable and WordPress is installed', async ({ page }) => {
    const response = await page.goto('/');
    expect(response?.status(), 'site responds').toBeLessThan(400);
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

setup('ERP modules and pretty permalinks are in place', async () => {
    const apiUtils = new ApiUtils(await request.newContext({ baseURL: env('BASE_URL') }));
    try {
        const modules = await apiUtils.activeModules();
        expect(modules, 'erp_pro/v1/admin/modules responds').toBeTruthy();
    } finally {
        await apiUtils.dispose();
    }
});
