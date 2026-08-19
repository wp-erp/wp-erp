/**
 * ERP Pro licence lifecycle for the setup/teardown projects.
 *
 * The key grants ONE seat (license_limit: 1). Setup activates it, teardown
 * releases it, and teardown is wired to run even when the suite fails — a
 * stranded seat blocks every other run and the developer's own site.
 */
import { Page } from '@playwright/test';
import { LicensePage, licenseMessages } from '@pages/core/licensePage';
import { getOption } from '@utils/dbUtils';
import { ErpLicenseStatus } from '@utils/interfaces';
import { env, hasEnv } from '@utils/helpers';

export type LicenseOutcome = { activated: boolean; reason: string; status: ErpLicenseStatus | null };

/** The stored server response, or null when the site holds no licence. */
export async function storedLicenseStatus(): Promise<ErpLicenseStatus | null> {
    const status = await getOption<ErpLicenseStatus>('erp_pro_license_status');
    if (!status || typeof status !== 'object' || !('license' in status)) return null;
    return status;
}

export async function isLicenseValid(): Promise<boolean> {
    const status = await storedLicenseStatus();
    return status?.license === 'valid' && status?.success === true;
}

/**
 * Brings the site to a licensed state.
 *
 * With ERP_LICENSE_KEY set, this drives the real admin form — the same path a
 * customer takes — so the activation itself is under test.
 *
 * Without a key it does NOT fabricate one. It verifies whatever licence the
 * site already holds and returns the reason activation was not exercised, so
 * the coverage ledger can record it instead of the run implying it passed.
 */
export async function ensureLicensed(page: Page): Promise<LicenseOutcome> {
    if (hasEnv('ERP_LICENSE_KEY') && hasEnv('ERP_LICENSE_EMAIL')) {
        const licensePage = new LicensePage(page);
        await licensePage.goto();

        if (await licensePage.isActivated()) {
            return { activated: true, reason: 'already activated on this site', status: await storedLicenseStatus() };
        }

        const notice = await licensePage.activate(env('ERP_LICENSE_EMAIL'), env('ERP_LICENSE_KEY'), env('ERP_LICENSE_SUBSCRIPTION_TYPE', 'scale_yearly'));

        if (!notice.includes(licenseMessages.activated)) {
            throw new Error(`Licence activation failed. Notice from the product: "${notice || '(none rendered)'}"`);
        }

        return { activated: true, reason: 'activated through the admin form', status: await storedLicenseStatus() };
    }

    const status = await storedLicenseStatus();

    if (status?.license === 'valid') {
        return {
            activated: false,
            reason: 'ERP_LICENSE_KEY not set — the site already holds a valid licence, so Pro specs run, but the activation flow itself was NOT exercised in this run',
            status,
        };
    }

    throw new Error('No ERP_LICENSE_KEY in the environment and the site holds no valid licence. Pro specs cannot run — set ERP_LICENSE_EMAIL/ERP_LICENSE_KEY.');
}

/** Releases the seat. Safe to call when nothing is activated. */
export async function releaseLicense(page: Page): Promise<string> {
    const licensePage = new LicensePage(page);
    await licensePage.goto();

    if (!(await licensePage.isActivated())) return 'nothing to deactivate';

    const notice = await licensePage.deactivate();
    return notice || '(no notice rendered)';
}
