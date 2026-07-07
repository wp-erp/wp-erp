import { faker } from '@faker-js/faker';
import { resolve } from 'node:path';
import 'dotenv/config';

const authDir = resolve(process.cwd(), 'playwright/.auth');
const uid = (n = 5) => faker.string.alphanumeric(n).toLowerCase();

/**
 * Central test data: storageState paths, role credentials (from .env), faker
 * factory functions (call them for unique values), and stable predefined names
 * that the setup chain seeds so specs can find them deterministically.
 */
export const data = {
    auth: {
        adminFile: `${authDir}/adminStorageState.json`,
        hrManagerFile: `${authDir}/hrManagerStorageState.json`,
        crmManagerFile: `${authDir}/crmManagerStorageState.json`,
        accManagerFile: `${authDir}/accManagerStorageState.json`,
        employeeFile: `${authDir}/employeeStorageState.json`,
        noAuth: { storageState: { cookies: [], origins: [] } },
    },

    users: {
        admin: { username: process.env.ADMIN ?? 'admin', password: process.env.ADMIN_PASSWORD ?? 'password' },
        hrManager: { username: process.env.HR_MANAGER ?? 'hr_manager1', password: process.env.USER_PASSWORD ?? '01erp01' },
        crmManager: { username: process.env.CRM_MANAGER ?? 'crm_manager1', password: process.env.USER_PASSWORD ?? '01erp01' },
        accManager: { username: process.env.ACC_MANAGER ?? 'acc_manager1', password: process.env.USER_PASSWORD ?? '01erp01' },
        employee: { username: process.env.EMPLOYEE ?? 'employee1', password: process.env.USER_PASSWORD ?? '01erp01' },
    },

    // ── HRM factories ────────────────────────────────────────────────────────
    hrm: {
        employee: () => {
            const firstName = faker.person.firstName();
            const lastName = faker.person.lastName();
            return {
                first_name: firstName,
                last_name: lastName,
                email: `emp_${uid()}@example.com`,
                designation: '',
                department: '',
                hiring_date: '2024-01-01',
            };
        },
        department: () => ({ title: `Dept_${uid()}`, description: faker.lorem.sentence() }),
        designation: () => ({ title: `Desig_${uid()}`, description: faker.lorem.sentence() }),
        leavePolicy: () => ({ name: `Policy_${uid()}`, days: faker.number.int({ min: 5, max: 20 }) }),
        holiday: () => ({ title: `Holiday_${uid()}`, start: '2025-12-25', end: '2025-12-25' }),
    },

    // ── CRM factories ─────────────────────────────────────────────────────────
    crm: {
        contact: () => ({
            first_name: faker.person.firstName(),
            last_name: faker.person.lastName(),
            email: `contact_${uid()}@example.com`,
            phone: faker.phone.number(),
            life_stage: 'lead',
        }),
        company: () => ({ company: `${faker.company.name()} ${uid()}`, email: `co_${uid()}@example.com` }),
    },

    // ── Accounting factories ──────────────────────────────────────────────────
    accounting: {
        customer: () => ({ first_name: faker.person.firstName(), last_name: faker.person.lastName(), email: `cust_${uid()}@example.com`, type: 'customer' }),
        vendor: () => ({ first_name: faker.person.firstName(), last_name: faker.person.lastName(), email: `vend_${uid()}@example.com`, type: 'vendor' }),
        product: () => ({ name: `Product_${uid()}`, cost_price: faker.commerce.price({ min: 10, max: 100 }), sale_price: faker.commerce.price({ min: 100, max: 200 }) }),
        invoiceAmount: () => Number(faker.finance.amount({ min: 50, max: 500, dec: 2 })),
    },

    // Stable names seeded by _env.setup (specs reference these).
    predefined: {
        department: 'PW Engineering',
        designation: 'PW Engineer',
        leavePolicy: 'PW Annual Leave',
        crmContact: { first_name: 'PW', last_name: 'Contact', email: 'pw.contact@example.com' },
        customer: { first_name: 'PW', last_name: 'Customer', email: 'pw.customer@example.com' },
        product: 'PW Product',
    },
};

/** Prefix used to bulk-delete only test-created rows in afterAll cleanups. */
export const TEST_PREFIX = 'pw_';
