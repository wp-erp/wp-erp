import { test as teardown } from '@utils/test';
import { releaseLicense } from '@utils/license';
import { LoginPage } from '@pages/core/loginPage';
import { env, hasEnv } from '@utils/helpers';

/**
 * Releases the single licence seat. Runs from its own config so CI can invoke it
 * with `if: always()` — a run that dies mid-suite must still hand the seat back.
 */
teardown('deactivate the ERP Pro licence', async ({ page }) => {
    teardown.skip(!hasEnv('ERP_LICENSE_KEY'), 'no ERP_LICENSE_KEY in the environment — this run did not take the seat');

    const login = new LoginPage(page);
    await login.login(env('ADMIN'), env('ADMIN_PASSWORD'));

    const notice = await releaseLicense(page);
    console.log(`[licence] release: ${notice}`);
});
