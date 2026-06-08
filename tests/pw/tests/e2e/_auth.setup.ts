import { test as setup } from '@utils/test';
import { login, getApiNonce, createEnvVar, ensureUser } from '@utils/helpers';
import { data } from '@utils/testData';

/**
 * Authenticates each role once and persists storageState files for specs to
 * reuse. Also creates the role users via REST and stashes the X-WP-Nonce + IDs
 * into .env (read by later projects through process.env). Runs serially.
 */
setup.describe('authentication & role users', () => {
    setup('admin login + capture nonce', { tag: ['@lite'] }, async ({ page }) => {
        await login(page, data.users.admin.username, data.users.admin.password, data.auth.adminFile);
        const nonce = await getApiNonce(page);
        if (nonce) createEnvVar('X_WP_NONCE', nonce);
    });

    setup('create role users via wp-cli', { tag: ['@lite'] }, async () => {
        const password = process.env.USER_PASSWORD ?? '01erp01';
        const roleUsers = [
            { username: data.users.hrManager.username, email: 'hr_manager1@example.com', role: 'erp_hr_manager', idKey: 'HR_MANAGER_ID' },
            { username: data.users.crmManager.username, email: 'crm_manager1@example.com', role: 'erp_crm_manager', idKey: 'CRM_MANAGER_ID' },
            { username: data.users.accManager.username, email: 'acc_manager1@example.com', role: 'erp_ac_manager', idKey: 'ACC_MANAGER_ID' },
            { username: data.users.employee.username, email: 'employee1@example.com', role: 'employee', idKey: 'EMPLOYEE_USER_ID' },
        ];
        for (const u of roleUsers) {
            const id = ensureUser(u.username, u.email, u.role, password);
            if (id) createEnvVar(u.idKey, id);
        }
    });

    setup('authenticate HR manager', { tag: ['@lite'] }, async ({ page }) => {
        await login(page, data.users.hrManager.username, data.users.hrManager.password, data.auth.hrManagerFile);
        // Capture the HR manager's own REST nonce (the admin nonce won't authenticate
        // this session) so manager-context REST specs are genuinely authorized.
        const nonce = await getApiNonce(page);
        if (nonce) createEnvVar('HR_MANAGER_NONCE', nonce);
    });
    setup('authenticate CRM manager', { tag: ['@lite'] }, async ({ page }) => {
        await login(page, data.users.crmManager.username, data.users.crmManager.password, data.auth.crmManagerFile);
        // Capture the CRM manager's own REST nonce from the CRM page (the manager
        // cannot reach the HR page, so read wpApiSettings from erp-crm).
        const nonce = await getApiNonce(page, 'wp-admin/admin.php?page=erp-crm');
        if (nonce) createEnvVar('CRM_MANAGER_NONCE', nonce);
    });
    setup('authenticate Account manager', { tag: ['@lite'] }, async ({ page }) => {
        await login(page, data.users.accManager.username, data.users.accManager.password, data.auth.accManagerFile);
        // Capture the Accounting manager's own REST nonce from the accounting page
        // (manager-context accounting/pro specs: inventory, returns, reports,
        // reimbursement charts). Read wpApiSettings from erp-accounting.
        const nonce = await getApiNonce(page, 'wp-admin/admin.php?page=erp-accounting');
        if (nonce) createEnvVar('ACC_MANAGER_NONCE', nonce);
    });
    setup('authenticate Employee', { tag: ['@lite'] }, async ({ page }) => {
        await login(page, data.users.employee.username, data.users.employee.password, data.auth.employeeFile);
    });
});
