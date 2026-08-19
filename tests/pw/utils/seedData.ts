/**
 * The seed dataset.
 *
 * The site is a demo of a real business, not a bag of `test_x7f2` strings: one
 * coherent company with plausible people, departments, customers and products,
 * so screenshots in a bug report look like a customer's site.
 *
 * Cleanup still works, because everything the suite creates lives on the
 * company's own e-mail domain (`SEED_DOMAIN`) — that is the handle, not an ugly
 * visible prefix. Data a spec creates on the fly keeps the short `pwerp` prefix
 * from `helpers.ts` so it is obviously transient.
 */

export const SEED_DOMAIN = 'northwind-analytics.test';
// NOTE: in .env this value MUST be quoted — dotenv treats an unquoted `#` as
// the start of an inline comment, which silently truncated it to `Erp@Test`.
export const SEED_PASSWORD = 'Erp@Test#2026';

export const company = {
    name: 'Northwind Analytics Ltd.',
    address1: '18 Kemal Ataturk Avenue',
    address2: 'Banani, Level 7',
    city: 'Dhaka',
    state: 'Dhaka',
    postalCode: '1213',
    country: 'BD',
    phone: '+880 2 9876543',
    email: `hello@${SEED_DOMAIN}`,
    website: `https://www.${SEED_DOMAIN}`,
    currency: 'USD',
    financialYearStart: '01-01',
};

export const departments = [
    { title: 'Engineering', description: 'Product engineering and platform delivery.' },
    { title: 'Product & Design', description: 'Product management, UX research and design systems.' },
    { title: 'Sales', description: 'New business, account management and partnerships.' },
    { title: 'Marketing', description: 'Demand generation, content and brand.' },
    { title: 'Customer Success', description: 'Onboarding, support and renewals.' },
    { title: 'Finance & Accounts', description: 'Accounting, payroll, procurement and compliance.' },
    { title: 'People Operations', description: 'Recruitment, employee experience and HR operations.' },
    { title: 'Operations', description: 'IT, facilities, vendor and asset management.' },
];

export const designations = [
    'Chief Executive Officer',
    'Chief Technology Officer',
    'Engineering Manager',
    'Senior Software Engineer',
    'Software Engineer',
    'QA Engineer',
    'Product Manager',
    'Product Designer',
    'Sales Director',
    'Account Executive',
    'Marketing Manager',
    'Content Strategist',
    'Customer Success Manager',
    'Support Specialist',
    'Finance Manager',
    'Accountant',
    'HR Manager',
    'Recruitment Specialist',
    'IT Administrator',
    'Office Manager',
];

const employee = (
    firstName: string,
    lastName: string,
    department: string,
    designation: string,
    hiringDate: string,
    payRate: string,
    extra: Partial<{ type: string; gender: string; payType: string; mobile: string; hiringSource: string }> = {}
) => ({
    firstName,
    lastName,
    email: `${firstName}.${lastName}`.toLowerCase().replace(/[^a-z.]/g, '') + `@${SEED_DOMAIN}`,
    department,
    designation,
    hiringDate,
    payRate,
    type: extra.type ?? 'permanent',
    status: 'active',
    payType: extra.payType ?? 'monthly',
    gender: extra.gender ?? 'male',
    mobile: extra.mobile ?? '+8801711000000',
    hiringSource: extra.hiringSource ?? 'direct',
});

/** 24 employees — enough to make lists, filters, reports and charts look real. */
export const employees = [
    employee('Farhana', 'Rahman', 'People Operations', 'Chief Executive Officer', '2019-02-04', '9500', { gender: 'female', mobile: '+8801711000101' }),
    employee('Imran', 'Chowdhury', 'Engineering', 'Chief Technology Officer', '2019-03-18', '9000', { mobile: '+8801711000102' }),
    employee('Nusrat', 'Jahan', 'Engineering', 'Engineering Manager', '2020-01-06', '6200', { gender: 'female', mobile: '+8801711000103' }),
    employee('Tanvir', 'Hasan', 'Engineering', 'Senior Software Engineer', '2020-06-15', '5200', { mobile: '+8801711000104' }),
    employee('Sadia', 'Islam', 'Engineering', 'Senior Software Engineer', '2021-02-01', '5100', { gender: 'female', mobile: '+8801711000105' }),
    employee('Rakib', 'Hossain', 'Engineering', 'Software Engineer', '2021-09-13', '3800', { mobile: '+8801711000106' }),
    employee('Mehedi', 'Alam', 'Engineering', 'Software Engineer', '2022-04-04', '3600', { mobile: '+8801711000107' }),
    employee('Anika', 'Tabassum', 'Engineering', 'QA Engineer', '2022-08-22', '3400', { gender: 'female', mobile: '+8801711000108' }),
    employee('Shafiq', 'Ahmed', 'Product & Design', 'Product Manager', '2020-11-02', '5600', { mobile: '+8801711000109' }),
    employee('Maria', 'Khatun', 'Product & Design', 'Product Designer', '2021-05-17', '4200', { gender: 'female', mobile: '+8801711000110' }),
    employee('Arif', 'Mahmud', 'Sales', 'Sales Director', '2019-10-07', '7000', { mobile: '+8801711000111' }),
    employee('Rumana', 'Akter', 'Sales', 'Account Executive', '2021-03-08', '3900', { gender: 'female', mobile: '+8801711000112' }),
    employee('Sabbir', 'Karim', 'Sales', 'Account Executive', '2022-01-10', '3700', { mobile: '+8801711000113' }),
    employee('Tania', 'Sultana', 'Marketing', 'Marketing Manager', '2020-08-03', '5000', { gender: 'female', mobile: '+8801711000114' }),
    employee('Nabil', 'Reza', 'Marketing', 'Content Strategist', '2022-06-06', '3300', { mobile: '+8801711000115' }),
    employee('Sharmin', 'Nahar', 'Customer Success', 'Customer Success Manager', '2021-01-11', '4600', { gender: 'female', mobile: '+8801711000116' }),
    employee('Jubayer', 'Rahman', 'Customer Success', 'Support Specialist', '2022-09-19', '2900', { mobile: '+8801711000117' }),
    employee('Sumaiya', 'Haque', 'Customer Success', 'Support Specialist', '2023-02-13', '2800', { gender: 'female', mobile: '+8801711000118' }),
    employee('Kamrul', 'Islam', 'Finance & Accounts', 'Finance Manager', '2019-07-01', '6000', { mobile: '+8801711000119' }),
    employee('Nadia', 'Parvin', 'Finance & Accounts', 'Accountant', '2021-11-15', '3500', { gender: 'female', mobile: '+8801711000120' }),
    employee('Rezaul', 'Karim', 'People Operations', 'HR Manager', '2020-02-17', '5400', { mobile: '+8801711000121' }),
    employee('Ishrat', 'Binte Anwar', 'People Operations', 'Recruitment Specialist', '2022-03-07', '3200', { gender: 'female', mobile: '+8801711000122' }),
    employee('Masud', 'Rana', 'Operations', 'IT Administrator', '2020-05-11', '3900', { mobile: '+8801711000123' }),
    employee('Farzana', 'Yeasmin', 'Operations', 'Office Manager', '2021-08-09', '3100', { gender: 'female', mobile: '+8801711000124' }),
];

export const leavePolicies = [
    { name: 'Annual Leave', days: 20, description: 'Paid annual holiday entitlement.' },
    { name: 'Sick Leave', days: 14, description: 'Paid leave for illness, with a certificate beyond three days.' },
    { name: 'Casual Leave', days: 10, description: 'Short-notice personal leave.' },
    { name: 'Maternity Leave', days: 112, description: 'Statutory maternity entitlement.' },
    { name: 'Paternity Leave', days: 10, description: 'Leave for new fathers.' },
    { name: 'Unpaid Leave', days: 30, description: 'Approved leave without pay.' },
];

export const holidays = [
    { title: 'New Year’s Day', start: '01-01', end: '01-01' },
    { title: 'International Mother Language Day', start: '02-21', end: '02-21' },
    { title: 'Independence Day', start: '03-26', end: '03-26' },
    { title: 'May Day', start: '05-01', end: '05-01' },
    { title: 'Victory Day', start: '12-16', end: '12-16' },
    { title: 'Company Foundation Day', start: '02-04', end: '02-04' },
];

export const crmCompanies = [
    { name: 'Beacon Retail Group', email: `procurement@beacon-retail.test`, phone: '+1 415 555 0142', city: 'San Francisco', country: 'US' },
    { name: 'Harbourline Logistics', email: `accounts@harbourline.test`, phone: '+44 20 7946 0311', city: 'London', country: 'GB' },
    { name: 'Verdant Foods', email: `hello@verdantfoods.test`, phone: '+61 2 8123 4477', city: 'Sydney', country: 'AU' },
    { name: 'Kestrel Manufacturing', email: `orders@kestrel-mfg.test`, phone: '+49 30 5557 8890', city: 'Berlin', country: 'DE' },
    { name: 'Lumen Health Partners', email: `billing@lumenhealth.test`, phone: '+1 617 555 0198', city: 'Boston', country: 'US' },
    { name: 'Sable & Finch Consulting', email: `contact@sablefinch.test`, phone: '+1 212 555 0164', city: 'New York', country: 'US' },
];

export const crmContacts = [
    { firstName: 'Elena', lastName: 'Marsh', email: 'elena.marsh@beacon-retail.test', phone: '+1 415 555 0143', lifeStage: 'customer', company: 'Beacon Retail Group' },
    { firstName: 'Daniel', lastName: 'Okoro', email: 'daniel.okoro@harbourline.test', phone: '+44 20 7946 0312', lifeStage: 'opportunity', company: 'Harbourline Logistics' },
    { firstName: 'Priya', lastName: 'Nair', email: 'priya.nair@verdantfoods.test', phone: '+61 2 8123 4478', lifeStage: 'lead', company: 'Verdant Foods' },
    { firstName: 'Jonas', lastName: 'Weber', email: 'jonas.weber@kestrel-mfg.test', phone: '+49 30 5557 8891', lifeStage: 'customer', company: 'Kestrel Manufacturing' },
    { firstName: 'Amara', lastName: 'Diallo', email: 'amara.diallo@lumenhealth.test', phone: '+1 617 555 0199', lifeStage: 'subscriber', company: 'Lumen Health Partners' },
    { firstName: 'Thomas', lastName: 'Reid', email: 'thomas.reid@sablefinch.test', phone: '+1 212 555 0165', lifeStage: 'opportunity', company: 'Sable & Finch Consulting' },
    { firstName: 'Yuki', lastName: 'Tanaka', email: 'yuki.tanaka@beacon-retail.test', phone: '+1 415 555 0144', lifeStage: 'lead', company: 'Beacon Retail Group' },
    { firstName: 'Grace', lastName: 'Mbeki', email: 'grace.mbeki@harbourline.test', phone: '+44 20 7946 0313', lifeStage: 'customer', company: 'Harbourline Logistics' },
];

/** Accounting customers/vendors reuse the CRM company names so the two modules agree. */
export const accountingVendors = [
    { name: 'Meridian Office Supplies', email: 'ar@meridian-office.test', phone: '+880 2 9887711' },
    { name: 'Bluewave Cloud Services', email: 'billing@bluewave-cloud.test', phone: '+1 206 555 0117' },
    { name: 'Skyline Facilities Ltd.', email: 'accounts@skyline-facilities.test', phone: '+880 2 9887712' },
    { name: 'Pinewood Print & Signage', email: 'hello@pinewoodprint.test', phone: '+880 2 9887713' },
];

export const accountingProducts = [
    { name: 'Analytics Platform — Starter (annual)', price: '1200.00', category: 'Subscriptions' },
    { name: 'Analytics Platform — Growth (annual)', price: '4800.00', category: 'Subscriptions' },
    { name: 'Analytics Platform — Scale (annual)', price: '12000.00', category: 'Subscriptions' },
    { name: 'Implementation & Onboarding', price: '2500.00', category: 'Services' },
    { name: 'Data Migration (per source)', price: '750.00', category: 'Services' },
    { name: 'Custom Dashboard Build', price: '1800.00', category: 'Services' },
    { name: 'Priority Support Retainer (monthly)', price: '400.00', category: 'Support' },
    { name: 'Training Workshop (per day)', price: '900.00', category: 'Services' },
];

export const taxRates = [
    { name: 'VAT (Standard)', rate: '15' },
    { name: 'VAT (Reduced)', rate: '7.5' },
    { name: 'Zero Rated', rate: '0' },
];

/** WooCommerce storefront — a real-looking shop so the site is presentable. */
export const wooStore = {
    address: company.address1,
    address2: company.address2,
    city: company.city,
    postcode: company.postalCode,
    country: 'BD:BD-13',
    currency: 'USD',
    sellingLocation: 'all',
    weightUnit: 'kg',
    dimensionUnit: 'cm',
};

export const wooProducts = [
    { name: 'Northwind Analytics — Starter Plan', price: '1200', sku: 'NA-STARTER', category: 'Subscriptions', description: 'Dashboards, 5 data sources and daily refresh for small teams.' },
    { name: 'Northwind Analytics — Growth Plan', price: '4800', sku: 'NA-GROWTH', category: 'Subscriptions', description: 'Unlimited dashboards, 25 data sources, hourly refresh and role-based access.' },
    { name: 'Northwind Analytics — Scale Plan', price: '12000', sku: 'NA-SCALE', category: 'Subscriptions', description: 'Unlimited everything, SSO, audit logging and a named success manager.' },
    { name: 'Implementation & Onboarding', price: '2500', sku: 'NA-ONBOARD', category: 'Professional Services', description: 'A guided four-week rollout run by our solutions team.' },
    { name: 'Data Migration (per source)', price: '750', sku: 'NA-MIGRATE', category: 'Professional Services', description: 'We move one legacy source into Northwind, validated row by row.' },
    { name: 'Custom Dashboard Build', price: '1800', sku: 'NA-DASH', category: 'Professional Services', description: 'A bespoke dashboard designed with your analysts.' },
    { name: 'Priority Support Retainer', price: '400', sku: 'NA-SUPPORT', category: 'Support', description: 'One-hour response target, business hours, rolling monthly.' },
    { name: 'Training Workshop (per day)', price: '900', sku: 'NA-TRAIN', category: 'Training', description: 'Hands-on training for up to twelve people, on site or remote.' },
];
