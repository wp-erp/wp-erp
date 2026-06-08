import { restUrl } from './helpers';

/**
 * WP ERP REST endpoints (verified from the plugin source).
 * HRM lives under /erp/v1/hrm/*, Accounting under /erp/v1/accounting/v1/*.
 * CRM has no REST controller in the free plugin — CRM seeding uses admin-ajax/DB
 * (handled inside crmPage.ts), so it is intentionally not listed here.
 */
const erp = (route: string) => restUrl(`/erp/v1/${route}`);
const wp = (route: string) => restUrl(`/wp/v2/${route}`);

export const endPoints = {
    // WordPress core
    users: wp('users'),
    currentUser: wp('users/me'),
    user: (id: string | number) => wp(`users/${id}`),

    // HRM
    employees: erp('hrm/employees'),
    employee: (id: string | number) => erp(`hrm/employees/${id}`),
    departments: erp('hrm/departments'),
    department: (id: string | number) => erp(`hrm/departments/${id}`),
    designations: erp('hrm/designations'),
    designation: (id: string | number) => erp(`hrm/designations/${id}`),
    announcements: erp('hrm/announcements'),
    leavePolicies: erp('hrm/leaves/policies'),
    leaveRequests: erp('hrm/leaves/requests'),
    leaveEntitlements: erp('hrm/leaves/entitlements'),
    holidays: erp('hrm/leaves/holidays'),
    hrReports: erp('hrm/reports'),

    // Accounting (rest_base already embeds accounting/v1)
    acctCustomers: erp('accounting/v1/customers'),
    acctVendors: erp('accounting/v1/vendors'),
    acctPeople: erp('accounting/v1/people'),
    acctProducts: erp('accounting/v1/products'),
    acctProductCats: erp('accounting/v1/product-cats'),
    acctInvoices: erp('accounting/v1/invoices'),
    acctExpenses: erp('accounting/v1/expenses'),
    acctBills: erp('accounting/v1/bills'),
    acctPurchases: erp('accounting/v1/purchases'),
    acctPayments: erp('accounting/v1/payments'),
    acctAccounts: erp('accounting/v1/accounts'),
    acctLedgers: erp('accounting/v1/ledgers'),
    acctJournals: erp('accounting/v1/journals'),
    acctTaxes: erp('accounting/v1/taxes'),
    acctTransactions: erp('accounting/v1/transactions'),
    acctReports: erp('accounting/v1/reports'),
    acctCompany: erp('accounting/v1/company'),
} as const;
