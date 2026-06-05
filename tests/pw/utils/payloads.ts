import 'dotenv/config';

/**
 * REST payloads shared across setup. Entity-specific bodies live inline in each
 * module's page object (feature-isolated). This holds the role-user payloads the
 * auth setup creates via wp/v2/users. WP ERP roles are registered by the plugin.
 */
const userPassword = process.env.USER_PASSWORD ?? '01erp01';

export const payloads = {
    createHrManager: {
        username: process.env.HR_MANAGER ?? 'hr_manager1',
        email: 'hr_manager1@example.com',
        password: userPassword,
        roles: ['erp_hr_manager'],
        first_name: 'HR',
        last_name: 'Manager',
    },
    createCrmManager: {
        username: process.env.CRM_MANAGER ?? 'crm_manager1',
        email: 'crm_manager1@example.com',
        password: userPassword,
        roles: ['erp_crm_manager'],
        first_name: 'CRM',
        last_name: 'Manager',
    },
    createAccManager: {
        username: process.env.ACC_MANAGER ?? 'acc_manager1',
        email: 'acc_manager1@example.com',
        password: userPassword,
        roles: ['erp_ac_manager'],
        first_name: 'Account',
        last_name: 'Manager',
    },
    createEmployee: {
        username: process.env.EMPLOYEE ?? 'employee1',
        email: 'employee1@example.com',
        password: userPassword,
        roles: ['employee'],
        first_name: 'Test',
        last_name: 'Employee',
    },
};
