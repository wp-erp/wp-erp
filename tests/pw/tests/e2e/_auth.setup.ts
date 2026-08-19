import { test as setup, expect, request } from '@utils/test';
import { actors, ActorKey } from '@utils/authStates';
import { ApiUtils } from '@utils/apiUtils';
import { LoginPage } from '@pages/core/loginPage';
import { env } from '@utils/helpers';

/**
 * Creates one user per ERP role (idempotent) and saves a storageState for each,
 * so authorization specs cost one context switch instead of a login.
 */
setup.describe.configure({ mode: 'serial' });

setup('seed role users', async () => {
    const apiUtils = new ApiUtils(await request.newContext({ baseURL: env('BASE_URL') }));

    try {
        for (const key of Object.keys(actors) as ActorKey[]) {
            const actor = actors[key];
            if (key === 'admin') continue;

            const login = env(actor.envUser);
            const password = env(actor.envPass);

            const seeded = await apiUtils.seedUser({ login, email: `${login}@erp.test`, password, role: actor.role });
            expect(seeded.roles, `${login} holds ${actor.role}`).toContain(actor.role);
        }
    } finally {
        await apiUtils.dispose();
    }
});

for (const key of Object.keys(actors) as ActorKey[]) {
    setup(`authenticate ${key}`, async ({ page }) => {
        const actor = actors[key];
        const loginPage = new LoginPage(page);

        await loginPage.login(env(actor.envUser), env(actor.envPass));

        // The admin bar is not a reliable authentication oracle here: some ERP
        // roles are redirected to the front end, and the HR Frontend module
        // moves others off wp-admin entirely. The session cookie is the fact.
        await expect(loginPage.error, `${key} login was accepted`).toBeHidden();

        const cookies = await page.context().cookies();
        expect(
            cookies.some((c) => c.name.startsWith('wordpress_logged_in_')),
            `${key} holds a WordPress session cookie (landed on ${page.url()})`
        ).toBeTruthy();

        await page.context().storageState({ path: actor.state });
    });
}
