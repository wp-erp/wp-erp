import { test, expect } from '@utils/test';
import { LicensePage, licenseMessages, subscriptionTypes } from '@pages/core/licensePage';
import { ADMIN_STATE } from '@utils/authStates';
import { env } from '@utils/helpers';
import { storedLicenseStatus } from '@utils/license';
import { closeDb } from '@utils/dbUtils';
import { withRole } from '@utils/roles';

test.use({ storageState: ADMIN_STATE });

test.describe('ERP Pro licence', () => {
    let licensePage: LicensePage;

    test.beforeEach(async ({ page }) => {
        licensePage = new LicensePage(page);
        await licensePage.goto();
    });

    test.afterAll(async () => {
        await closeDb();
    });

    test('licence screen renders the activated state', { tag: ['@tier1', '@core-license', '@pro'] }, async () => {
        await expect(licensePage.activatedSection).toBeVisible();
        await expect(licensePage.detailsHeading).toContainText('License Details');
        await expect(licensePage.deactivateButton).toBeVisible();
        await expect(licensePage.syncButton).toBeVisible();
        await expect(licensePage.upgradeLink).toBeVisible();

        expect(await licensePage.hasNoPhpFatal(), 'no PHP fatal on the licence screen').toBe(true);
    });

    test('licence detail cards report the purchased plan', { tag: ['@tier1', '@core-license', '@pro'] }, async () => {
        expect(await licensePage.detailCardCount(), 'three detail cards render').toBe(3);

        const summary = await licensePage.detailSummary();
        expect(summary).toContain('Subscription Status');
        expect(summary).toContain('Renewal Date');
        expect(summary).toContain('Number of user');
        expect(summary, 'the seat count on screen matches the licence').toContain(env('ERP_LICENSE_USERS', '100'));
    });

    test('the screen agrees with the stored licence status', { tag: ['@tier1', '@core-license', '@pro'] }, async () => {
        const status = await storedLicenseStatus();

        expect(status, 'a licence status is stored').not.toBeNull();
        expect(status?.license, 'licence is valid').toBe('valid');
        expect(status?.success, 'server reported success').toBe(true);
        expect(Number(status?.users), 'seats').toBe(Number(env('ERP_LICENSE_USERS', '100')));
        expect(status?.tier, 'tier').toBe('scale');
        expect(status?.subscription_status, 'subscription').toBe('active');
        expect(new Date(String(status?.expires)).getTime(), 'licence has not expired').toBeGreaterThan(Date.now());
    });

    test('every licensed extension is rendered on the licence screen', { tag: ['@tier1', '@core-license', '@pro'] }, async () => {
        await expect(licensePage.includedExtensionsHeading).toContainText('Included Extensions');

        const granted = (await storedLicenseStatus())?.extensions ?? [];
        const purchased = await licensePage.listedExtraExtensions();

        expect(granted.length, 'the licence grants extensions').toBeGreaterThan(0);
        expect(purchased.length, 'one row per licensed extension').toBe(granted.length);
    });

    test('no extension is advertised that the licence does not grant', { tag: ['@tier2', '@core-license', '@pro'] }, async () => {
        expect(await licensePage.includedButNotGranted(), 'every extension shown as Included is one the licence grants').toEqual([]);
    });

    test('licence survives a reload without re-validating', { tag: ['@tier2', '@core-license', '@pro'] }, async () => {
        const before = await storedLicenseStatus();

        await licensePage.reload();
        await expect(licensePage.deactivateButton).toBeVisible();

        expect((await storedLicenseStatus())?.checksum, 'stored licence is untouched by a page view').toBe(before?.checksum);
    });

    test('the subscription-type select offers every plan', { tag: ['@tier2', '@core-license', '@pro'] }, async () => {
        test.skip(await licensePage.isActivated(), 'site is licensed — the activation form with #subscription-type is not rendered');

        const values = await licensePage.subscriptionOptionValues();

        for (const expected of subscriptionTypes) {
            expect(values, `option ${expected} is offered`).toContain(expected);
        }
    });

    test('submitting the activation form with no e-mail is rejected', { tag: ['@tier3', '@core-license', '@pro'] }, async () => {
        test.skip(await licensePage.isActivated(), 'site is licensed — the activation form is not rendered');

        await licensePage.emailField.fill('');
        await licensePage.keyField.fill('0000000000000000000000000000dead');

        expect(await licensePage.submitRaw(), 'the product rejects an empty e-mail').toContain(licenseMessages.emptyEmail);
    });

    test('submitting the activation form with no key is rejected', { tag: ['@tier3', '@core-license', '@pro'] }, async () => {
        test.skip(await licensePage.isActivated(), 'site is licensed — the activation form is not rendered');

        await licensePage.emailField.fill(env('ERP_LICENSE_EMAIL', 'qa@example.test'));
        await licensePage.keyField.fill('');

        expect(await licensePage.submitRaw(), 'the product rejects an empty key').toContain(licenseMessages.emptyKey);
    });

    test('the licence screen is closed to non-admin roles', { tag: ['@tier3', '@core-license', '@pro', '@authz'] }, async ({ browser }) => {
        const result = await withRole(browser, 'hrManager', async (page) => {
            const asHrManager = new LicensePage(page);
            await asHrManager.goto();

            return {
                denied: await asHrManager.isAccessDenied(),
                leaksKey: await asHrManager.exposesLicenseKey(env('ERP_LICENSE_KEY', '__no_key__')),
            };
        });

        expect(result.denied, 'access is refused to a role without manage_options').toBe(true);
        expect(result.leaksKey, 'the licence key is not exposed').toBe(false);
    });
});
