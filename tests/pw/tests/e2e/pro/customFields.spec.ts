import { test, expect } from '@utils/test';
import { CustomFieldsPage, peopleTypes, fieldsOptionFor } from '@pages/pro/customFieldsPage';
import { EmployeesPage } from '@pages/hrm/employeesPage';
import { ADMIN_STATE, EMPLOYEE_STATE } from '@utils/authStates';

test.use({ storageState: ADMIN_STATE });
import { deleteOption, getOption, closeDb } from '@utils/dbUtils';

/**
 * Custom Field Builder (@pro) — the module that lets an administrator add
 * fields to the employee, contact, company, customer and vendor forms.
 *
 * Its whole point is that a field defined here shows up somewhere else, so the
 * oracle is never the builder screen: it is the FORM the field was added to.
 * A test that only checks the builder lists what it was told would pass on a
 * module that writes nothing.
 *
 * Storage is one WordPress option per people type, `erp-{type}-fields`, which
 * is also what the cleanup removes — this file owns those options and no other
 * spec touches them.
 */
const LABEL = 'Emergency Contact';
const META_KEY = 'emergency_contact';

test.describe('Custom Field Builder @pro', () => {
    let page: CustomFieldsPage;

    /** Every people type this file may have written to. */
    const owned = ['Employee', 'Contact', 'Company', 'Customer', 'Vendor'] as const;

    async function clearFields(): Promise<void> {
        for (const type of owned) await deleteOption(fieldsOptionFor(type));
    }

    test.beforeAll(async () => {
        await clearFields();
    });

    test.beforeEach(async ({ page: p }) => {
        page = new CustomFieldsPage(p);
        await clearFields();
    });

    test.afterAll(async () => {
        await clearFields();
        await closeDb();
    });

    // ---- Tier 1 ----------------------------------------------------------

    test('the builder offers a tab for every people type', { tag: ['@tier1', '@pro', '@custom-fields', '@smoke'] }, async () => {
        await page.goto();

        expect(await page.tabs(), 'the five people types the module supports').toEqual([...peopleTypes]);
    });

    test('a field added to a people type is stored against it', { tag: ['@tier1', '@pro', '@custom-fields', '@crud'] }, async () => {
        await page.goto('Employee');
        await page.addField({ label: LABEL, section: 'Personal Information', type: 'Text', placeholder: 'Who to call' });
        await page.save();

        const stored = await getOption<Record<string, string>[]>(fieldsOptionFor('Employee'));

        expect(stored, 'the field list is written for the Employee type').toHaveLength(1);
        expect(stored![0]!.label, 'with the label given').toBe(LABEL);
        expect(stored![0]!.section, 'in the section chosen').toBe('personal');
        expect(stored![0]!.type, 'and the type chosen').toBe('text');
        expect(stored![0]!.placeholder, 'carrying its placeholder').toBe('Who to call');
    });

    test('a saved field appears on the form it was added to', { tag: ['@tier1', '@pro', '@custom-fields', '@flow'] }, async ({ page: p }) => {
        // The case that matters. The builder writing an option proves nothing on
        // its own — this asserts the employee form actually renders the field.
        await page.goto('Employee');
        await page.addField({ label: LABEL, section: 'Personal Information', type: 'Text' });
        await page.save();

        const employees = new EmployeesPage(p);
        await employees.goto();
        await employees.openCreateModal();
        await employees.showAdvancedFields();

        expect(await employees.modalText(), 'the employee form now carries the field').toContain(LABEL);
    });

    // ---- Tier 2 ----------------------------------------------------------

    test('the meta key is derived from the label', { tag: ['@tier2', '@pro', '@custom-fields'] }, async () => {
        await page.goto('Employee');
        await page.addField({ label: LABEL, section: 'Personal Information', type: 'Text' });
        await page.save();

        const stored = await getOption<Record<string, string>[]>(fieldsOptionFor('Employee'));

        expect(stored![0]!.name, 'lower-cased and underscored, so it is safe as a meta key').toBe(META_KEY);
    });

    test('a field belongs to one people type only', { tag: ['@tier2', '@pro', '@custom-fields'] }, async () => {
        // Five tabs share one screen, and an implementation that keyed the option
        // wrongly would leak every field into every form on the site.
        await page.goto('Employee');
        await page.addField({ label: LABEL, section: 'Personal Information', type: 'Text' });
        await page.save();

        expect(await getOption(fieldsOptionFor('Employee')), 'the Employee type has the field').not.toBeNull();
        expect(await getOption(fieldsOptionFor('Contact')), 'and no other type does').toBeNull();

        await page.goto('Contact');
        expect(await page.fieldLabels(), 'the Contact tab lists nothing').toEqual([]);
    });

    test('a saved field survives a reload of the builder', { tag: ['@tier2', '@pro', '@custom-fields'] }, async () => {
        await page.goto('Employee');
        await page.addField({ label: LABEL, section: 'Personal Information', type: 'Text' });
        await page.save();

        await page.goto('Employee');

        expect(await page.fieldLabels(), 'the field is listed again on a fresh load').toContain(LABEL);
    });

    test('a dropdown field keeps the options it was given', { tag: ['@tier2', '@pro', '@custom-fields'] }, async () => {
        await page.goto('Employee');
        await page.addField({
            label: 'Shirt Size',
            section: 'Personal Information',
            type: 'Dropdown',
            options: [
                { text: 'Small', value: 'S' },
                { text: 'Large', value: 'L' },
            ],
        });
        await page.save();

        const stored = await getOption<Record<string, unknown>[]>(fieldsOptionFor('Employee'));
        const options = stored![0]!.options as { text: string; value: string }[];

        expect(options.map((o) => o.text), 'both option labels round-trip').toEqual(['Small', 'Large']);
        expect(options.map((o) => o.value), 'and both values').toEqual(['S', 'L']);
    });

    test('a deleted field is taken off the form', { tag: ['@tier2', '@pro', '@custom-fields', '@crud'] }, async ({ page: p }) => {
        await page.goto('Employee');
        await page.addField({ label: LABEL, section: 'Personal Information', type: 'Text' });
        await page.save();

        await page.goto('Employee');
        await page.deleteField(0);

        // No `save()` here on purpose: deleting posts to the server by itself,
        // and asserting after an extra save would hide it if that ever stopped.
        expect(await page.fieldLabels(), 'the builder no longer lists it').not.toContain(LABEL);
        expect(
            JSON.stringify(await getOption(fieldsOptionFor('Employee')) ?? ''),
            'and it is gone from the stored definition'
        ).not.toContain(LABEL);

        const employees = new EmployeesPage(p);
        await employees.goto();
        await employees.openCreateModal();
        await employees.showAdvancedFields();

        expect(await employees.modalText(), 'and the employee form no longer offers it').not.toContain(LABEL);
    });

    // ---- Tier 3 ----------------------------------------------------------

    test('the builder is closed to an employee', { tag: ['@tier3', '@pro', '@custom-fields', '@authz'] }, async ({ browser }) => {
        // The page is registered with `manage_options`, so only administrators
        // should ever see it — a field builder is a schema editor for the whole
        // site's forms.
        const context = await browser.newContext({ storageState: EMPLOYEE_STATE });
        const p = await context.newPage();
        const asEmployee = new CustomFieldsPage(p);

        await asEmployee.goto();

        expect(await asEmployee.isAccessDenied(), 'an employee is refused the builder').toBe(true);

        await context.close();
    });

    test('the save endpoint refuses a request with no nonce', { tag: ['@tier3', '@pro', '@custom-fields', '@authz'] }, async ({ page: p }) => {
        await page.goto('Employee');

        const response = await p.evaluate(async () => {
            const body = new URLSearchParams({ action: 'erp_form_builder', people: 'employee', nonce: 'not-a-nonce' });
            const r = await fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString(),
            });
            return (await r.text()).slice(0, 60);
        });

        // The product's own wording, typo and all — asserted verbatim rather than
        // paraphrased, so this test fails if the message ever changes.
        expect(response, 'the handler refuses it outright').toContain('You are no allowed');
        expect(await getOption(fieldsOptionFor('Employee')), 'and writes nothing').toBeNull();
    });

    test('a field label carrying a script payload is never executed', { tag: ['@tier3', '@pro', '@custom-fields', '@xss'] }, async ({ page: p }) => {
        const payload = '<script>window.__cfbXss = true;</script>';

        await page.goto('Employee');
        await page.addField({ label: `Notes ${payload}`, section: 'Personal Information', type: 'Text' });
        await page.save();

        const employees = new EmployeesPage(p);
        await employees.goto();
        await employees.openCreateModal();
        await employees.showAdvancedFields();

        expect(await employees.rendersInjectedScript('__cfbXss'), 'the payload is rendered as text, not run').toBe(false);
    });
});
