/**
 * Endpoints the suite actually calls. The FULL live route table (289 routes,
 * captured from the running site) lives in `harness/routes.json` — coverage
 * checks read that; specs read this.
 */
export const endPoints = {
    // ---- WordPress core ----
    wp: {
        users: '/wp/v2/users',
        user: (id: number | string) => `/wp/v2/users/${id}`,
        settings: '/wp/v2/settings',
    },

    // ---- ERP core ----
    erp: {
        base: '/erp/v1',
        activePlugins: '/erp/v1/utility/get-active-plugins',
    },

    // ---- HRM ----
    hrm: {
        employees: '/erp/v1/hrm/employees',
        employee: (id: number | string) => `/erp/v1/hrm/employees/${id}`,
        departments: '/erp/v1/hrm/departments',
        department: (id: number | string) => `/erp/v1/hrm/departments/${id}`,
        designations: '/erp/v1/hrm/designations',
        designation: (id: number | string) => `/erp/v1/hrm/designations/${id}`,
        leavePolicies: '/erp/v1/hrm/leaves/policies',
        leaveRequests: '/erp/v1/hrm/leaves/requests',
        holidays: '/erp/v1/hrm/leaves/holidays',
        announcements: '/erp/v1/hrm/announcements',
        recruitmentJobs: '/erp/v1/hrm/recruitment/jobs',
        recruitmentCandidates: '/erp/v1/hrm/recruitment/candidates',
    },

    // ---- CRM ----
    crm: {
        contacts: '/erp/v1/crm/contacts',
        contact: (id: number | string) => `/erp/v1/crm/contacts/${id}`,
        contactGroups: '/erp/v1/crm/contacts/groups',
        activities: '/erp/v1/crm/activities',
        schedules: '/erp/v1/crm/schedules',
    },

    // ---- Accounting ----
    accounting: {
        base: '/erp/v1/accounting/v1',
        invoices: '/erp/v1/accounting/v1/invoices',
        bills: '/erp/v1/accounting/v1/bills',
        expenses: '/erp/v1/accounting/v1/expenses',
        customers: '/erp/v1/accounting/v1/customers',
        vendors: '/erp/v1/accounting/v1/vendors',
        products: '/erp/v1/accounting/v1/products',
        ledgers: '/erp/v1/accounting/v1/ledgers',
        taxRates: '/erp/v1/accounting/v1/taxes',
    },

    // ---- ERP Pro ----
    pro: {
        modules: '/erp_pro/v1/admin/modules',
        activateModule: '/erp_pro/v1/admin/modules/activate',
        deactivateModule: '/erp_pro/v1/admin/modules/deactivate',
        installedModules: '/erp_pro/v1/admin/modules/installed',
    },

    // ---- Test-only helpers (mu-plugins/erp-test-helpers.php) ----
    testHelper: {
        license: '/erp-pw/v1/license',
        userCount: '/erp-pw/v1/user-count',
        cron: '/erp-pw/v1/cron',
        notices: '/erp-pw/v1/notices',
        phpErrors: '/erp-pw/v1/php-errors',
        seedUser: '/erp-pw/v1/users',
        seedUsersBulk: '/erp-pw/v1/users/bulk',
        cleanupUsers: '/erp-pw/v1/users/cleanup',
        options: '/erp-pw/v1/options',
        flushCache: '/erp-pw/v1/flush-cache',
    },
} as const;
