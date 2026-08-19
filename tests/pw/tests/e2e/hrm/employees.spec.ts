import { test, expect } from '@utils/test';
import { EmployeesPage, employeeTypes, employeeStatuses } from '@pages/hrm/employeesPage';
import { ADMIN_STATE } from '@utils/authStates';
import { uniqueName, uniqueEmail, dateOffset } from '@utils/helpers';
import { withRole } from '@utils/roles';
import { cleanupEmployees } from '@utils/cleanup';
import { closeDb } from '@utils/dbUtils';
import { employees as seededEmployees, departments, designations } from '@utils/seedData';

test.use({ storageState: ADMIN_STATE });

test.describe('HR — Employees', () => {
    let page: EmployeesPage;

    test.beforeEach(async ({ page: p }) => {
        page = new EmployeesPage(p);
        await page.goto();
    });

    test.afterAll(async () => {
        // Leave the demo site as we found it.
        await cleanupEmployees();
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('the employee list renders the seeded employees', { tag: ['@tier1', '@hrm-people'] }, async () => {
        expect(await page.totalItems(), 'employees are listed').toBeGreaterThanOrEqual(seededEmployees.length);

        // Searched rather than read off page 1: the list paginates at 20 and
        // orders by hire date, so the earliest hires sit on page 2.
        for (const seeded of seededEmployees.slice(0, 3)) {
            expect(await page.findAcrossPages(seeded.lastName), `${seeded.firstName} ${seeded.lastName} is listed`).toBe(true);
        }

        expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);
    });

    test('a row shows the designation, department, type and status', { tag: ['@tier1', '@hrm-people'] }, async () => {
        // Pick an employee that is on page 1 — the most recent hire.
        const seeded = [...seededEmployees].sort((a, b) => b.hiringDate.localeCompare(a.hiringDate))[0]!;
        const details = await page.rowDetails(`${seeded.firstName} ${seeded.lastName}`);

        expect(details.designation, 'designation column').toBe(seeded.designation);
        expect(details.department, 'department column').toBe(seeded.department);
        expect(details.status, 'status column').toMatch(/Active/i);
        expect(details.type, 'employment type column').toBeTruthy();
    });

    test('the status views report counts that agree with the list', { tag: ['@tier1', '@hrm-people'] }, async () => {
        const views = await page.statusViews();
        expect(views.length, 'status views render').toBeGreaterThan(0);

        for (const label of ['All', 'Active', 'Inactive', 'Terminated', 'Deceased', 'Resigned', 'Trash']) {
            expect(await page.statusCount(label), `"${label}" view is present with a count`).toBeGreaterThanOrEqual(0);
        }

        expect(await page.statusCount('All'), 'the All count matches the item total').toBe(await page.totalItems());
    });

    test('the create modal renders all eight required fields', { tag: ['@tier1', '@hrm-people'] }, async () => {
        await page.openCreateModal();

        for (const field of page.requiredFields) {
            await expect(field).toBeVisible();
        }

        await page.closeModal();
    });

    test('the create modal offers every employee type and status', { tag: ['@tier1', '@hrm-people', '@options'] }, async () => {
        await page.openCreateModal();

        const types = await page.modalOptions('type');
        const statuses = await page.modalOptions('status');

        expect(types.length, 'employee types are offered').toBeGreaterThanOrEqual(employeeTypes.length);
        expect(statuses.length, 'employee statuses are offered').toBeGreaterThanOrEqual(employeeStatuses.length);

        await page.closeModal();
    });

    test('an employee can be hired and appears in the list', { tag: ['@tier1', '@hrm-people', '@crud'] }, async () => {
        // Name tokens must be letters only — erp_is_valid_name() (functions.php:3756)
        // rejects digits and underscores with "Please provide a valid last name".
        const firstName = 'Qa';
        const lastName = uniqueName('Hire');
        const before = await page.totalItems();

        await page.create({
            firstName,
            lastName,
            email: uniqueEmail('hire_'),
            type: 'permanent',
            status: 'active',
            hiringDate: dateOffset(-30),
            departmentLabel: departments[0]!.title,
            designationLabel: designations[0]!,
        });

        await page.goto();

        expect(await page.totalItems(), 'the employee total grew by one').toBe(before + 1);
        expect(await page.findAcrossPages(lastName), 'the new employee is listed').toBe(true);

        const details = await page.rowDetails(lastName);
        expect(details.department, 'department was stored').toBe(departments[0]!.title);
        expect(details.designation, 'designation was stored').toBe(designations[0]!);
    });

    // ---- Tier 2 ----------------------------------------------------------

    test('searching by name narrows the list', { tag: ['@tier2', '@hrm-people', '@edge'] }, async () => {
        const seeded = seededEmployees[0]!;
        const before = await page.totalItems();

        await page.search(seeded.lastName);

        expect(await page.hasEmployee(seeded.lastName), 'the searched employee is kept').toBe(true);
        expect(await page.totalItems(), 'the list narrows').toBeLessThan(before);
    });

    test('a search matching nothing shows the empty state, not an error', { tag: ['@tier2', '@hrm-people', '@empty-state'] }, async () => {
        await page.search('zzz-no-such-employee-zzz');

        expect(await page.rowCount() === 0 || (await page.isEmpty()), 'nothing matches').toBe(true);
        expect(await page.hasNoPhpFatal(), 'no PHP fatal on an empty result').toBe(true);
    });

    test('filtering by department narrows the list to that department', { tag: ['@tier2', '@hrm-people', '@edge'] }, async () => {
        const target = departments[0]!.title;

        await page.filterBy('department', target);

        const names = await page.names();
        expect(names.length, 'the filter returns rows').toBeGreaterThan(0);

        for (const name of names) {
            expect((await page.rowDetails(name)).department, `${name} belongs to ${target}`).toBe(target);
        }
    });

    test('the department and designation filters offer the seeded values', { tag: ['@tier2', '@hrm-people', '@options'] }, async () => {
        const departmentOptions = await page.filterOptions('department');
        const designationOptions = await page.filterOptions('designation');

        expect(departmentOptions, 'a seeded department is offered').toContain(departments[0]!.title);
        expect(designationOptions, 'a seeded designation is offered').toContain(designations[0]!);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('hiring without the required fields is refused', { tag: ['@tier3', '@hrm-people', '@validation'] }, async () => {
        const before = await page.totalItems();

        const alerted = await page.attemptCreate({ firstName: '', lastName: '', email: '' });

        // Empty required fields are caught by the browser's own constraint
        // validation, which blocks submit silently — so an alert is possible but
        // not guaranteed. What must hold either way: the modal stays open and
        // nothing is written.
        expect(alerted === '' || /first name|last name|required|provide/i.test(alerted), `unexpected alert: "${alerted}"`).toBe(true);
        expect(await page.isModalOpen(), 'the modal stays open on a refused save').toBe(true);

        await page.closeModal();
        await page.goto();

        expect(await page.totalItems(), 'no employee was created').toBe(before);
    });

    test('a malformed e-mail is refused', { tag: ['@tier3', '@hrm-people', '@validation'] }, async () => {
        const before = await page.totalItems();

        const alerted = await page.attemptCreate({
            firstName: 'Qa',
            lastName: uniqueName('Bademail'),
            email: 'not-an-email',
            type: 'permanent',
            status: 'active',
            hiringDate: dateOffset(-1),
            departmentLabel: departments[0]!.title,
            designationLabel: designations[0]!,
        });

        // `#erp-hr-user-email` is type=email, so the browser blocks submit before
        // any ERP validation runs — an alert is possible but not guaranteed.
        expect(alerted === '' || /email|valid/i.test(alerted), `unexpected alert: "${alerted}"`).toBe(true);
        expect(await page.isModalOpen(), 'the modal stays open on a refused save').toBe(true);

        await page.closeModal();
        await page.goto();

        expect(await page.totalItems(), 'no employee was created from a malformed address').toBe(before);
    });

    test('a script payload in the employee name is sanitised, never executed', { tag: ['@tier3', '@hrm-people', '@security'] }, async () => {
        // Verified behaviour: ERP strips the tag before storage — the record is
        // created with the payload removed (stored last name becomes "Xss"),
        // which is a safe outcome. What must never happen is the payload
        // reaching the list as executable markup.
        const lastName = 'Xss<script>alert(1)</script>';
        const before = await page.totalItems();

        await page.create({
            firstName: 'Qa',
            lastName,
            email: uniqueEmail('xss'),
            type: 'permanent',
            status: 'active',
            hiringDate: dateOffset(-2),
            departmentLabel: departments[0]!.title,
            designationLabel: designations[0]!,
        });

        await page.goto();

        expect(await page.totalItems(), 'the record was created with the payload stripped').toBe(before + 1);
        expect(await page.rendersInjectedScript('alert(1)'), 'nothing executable reached the list').toBe(false);
        expect(await page.bodyText(), 'no raw script tag is rendered').not.toContain('<script>alert(1)</script>');
        expect(await page.hasNoPhpFatal(), 'no PHP fatal').toBe(true);
    });

    test('the People screen is closed to an employee role', { tag: ['@tier3', '@hrm-people', '@authz'] }, async ({ browser }) => {
        // The `employee` role holds `erp_list_employee`, but the People screen
        // still refuses it — verified against a freshly authenticated session
        // (a stale storageState silently redirects to wp-login and would make
        // this look like an empty list rather than a refusal).
        const denied = await withRole(browser, 'employee', async (p) => {
            const asEmployee = new EmployeesPage(p);
            await asEmployee.goto();
            return asEmployee.isAccessDenied();
        });

        expect(denied, 'an employee cannot reach the People screen').toBe(true);
    });
});
