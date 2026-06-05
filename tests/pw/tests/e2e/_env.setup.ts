import { test as setup } from '@utils/test';
import { ApiUtils } from '@utils/apiUtils';
import { createEnvVar } from '@utils/helpers';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import type { IdMap } from '@utils/interfaces';
import { HrmPage } from './hrm/hrmPage';
import { CrmPage } from './crm/crmPage';
import { AccountingPage } from './accounting/accountingPage';

/**
 * Seeds per-module fixtures (REST for HRM/Accounting, DB for CRM) and persists
 * the returned IDs into .env so specs can read them via process.env.
 * Each module's seed() is resilient and returns whatever IDs it obtained.
 */
setup.describe('seed module fixtures', () => {
    let api: ApiUtils;

    setup.beforeAll(async () => {
        api = await ApiUtils.fromStorageState(data.auth.adminFile);
    });

    setup.afterAll(async () => {
        await api.dispose();
        await dbUtils.close();
    });

    const persist = (ids: IdMap): void => {
        for (const [key, value] of Object.entries(ids)) {
            if (value) createEnvVar(key, value);
        }
    };

    setup('seed HRM fixtures', { tag: ['@lite'] }, async () => {
        persist(await HrmPage.seed(api));
    });

    setup('seed CRM fixtures', { tag: ['@lite'] }, async () => {
        persist(await CrmPage.seed(api));
    });

    setup('seed Accounting fixtures', { tag: ['@lite'] }, async () => {
        persist(await AccountingPage.seed(api));
    });
});
