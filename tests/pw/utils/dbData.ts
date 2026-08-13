/**
 * WP ERP DB table & option names used for direct seeding / cleanup.
 * Table base names are derived from the plugin's $wpdb->prefix . 'erp_' schema;
 * confirm against your install before relying on a given table for assertions.
 */
const PREFIX = process.env.DB_PREFIX ?? 'wp';
const t = (name: string) => `${PREFIX}_${name}`;

export const tables = {
    // core people store (CRM contacts/companies + accounting customers/vendors)
    peoples: t('erp_peoples'),
    peopleMeta: t('erp_peoplemeta'),
    peopleTypeRelations: t('erp_people_type_relations'),
    peopleTypes: t('erp_people_types'),

    // HRM
    hrEmployees: t('erp_hr_employees'),
    hrDepartments: t('erp_hr_depts'),
    hrDesignations: t('erp_hr_designations'),
    hrLeaves: t('erp_hr_leaves'),
    hrLeavePolicies: t('erp_hr_leave_policies'),
    hrLeaveRequests: t('erp_hr_leave_requests'),
    hrLeaveEntitlements: t('erp_hr_leave_entitlements'),
    hrLeaveHolidays: t('erp_hr_leave_holiday'),

    // Accounting
    acctInvoices: t('erp_acct_invoices'),
    acctBills: t('erp_acct_bills'),
    acctExpenses: t('erp_acct_expenses'),
    acctPurchases: t('erp_acct_purchase'),
    acctProducts: t('erp_acct_products'),
    acctLedger: t('erp_acct_ledger'),
    acctJournals: t('erp_acct_journals'),
    acctProductCategories: t('erp_acct_product_categories'),

    // Security-suite oracles
    hrEmployeePerformance: t('erp_hr_employee_performance'),
    crmSaveSearch: t('erp_crm_save_search'),

    // WP core
    users: t('users'),
    userMeta: t('usermeta'),
    options: t('options'),
    posts: t('posts'),
} as const;

/** WP ERP options (mostly php-serialized arrays). */
export const options = {
    modules: 'erp_modules',
    settingsGeneral: 'erp_settings_general',
    settingsHr: 'erp_settings_erp-hr',
    settingsCrm: 'erp_settings_erp-crm',
    acctOpeningBalance: 'erp_acct_new_opening_balance',
    proLicense: 'erp_pro_license_status',
} as const;
