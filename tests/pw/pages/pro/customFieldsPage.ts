import { Dialog, Locator, Page } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * Custom Field Builder — ERP Pro's per-people-type form designer.
 *
 * Lives at `admin.php?page=custom-field-builder`, outside the ERP SPA: it is a
 * plain admin page carrying a Vue 1 app, so there is no hash router and no
 * spinner to wait on. Fields are stored one WordPress option per people type,
 * `erp-{type}-fields`, and read back by every form that renders that type.
 */
export const customFieldSelectors = {
    tab: '.nav-tab',
    fieldParent: '#people-field-parent',
    addNew: '#add-new-field button',
    save: 'button:has-text("Save Changes")',
    field: '#people-field-parent > .single-field, #people-field-parent .single-field',
    label: 'input[placeholder="Label"]',
    placeholder: 'input[placeholder="placeholder"]',
    helpText: 'input[placeholder="helptext"]',
    done: 'button:has-text("Done")',
    deleteField: 'button.button-delete',
    optionText: 'input[placeholder="text"]',
    optionValue: 'input[placeholder="value"]',
    addOption: 'button:has-text("Add Option")',
} as const;

/** The people types the builder offers a tab for, captured live. */
export const peopleTypes = ['Employee', 'Contact', 'Company', 'Customer', 'Vendor'] as const;
export type PeopleType = (typeof peopleTypes)[number];

/** The sections an Employee field can be placed in, captured live. */
export const employeeSections = [
    'Top Area',
    'Basic Information',
    'Work Information',
    'Personal Information',
    'Bottom Area',
] as const;

/** The field types the builder offers, captured live — note `number` is the one lower-case entry. */
export const fieldTypes = [
    'Text',
    'Password',
    'Textarea',
    'Dropdown',
    'Radio',
    'Checkbox',
    'number',
    'Url',
    'Email',
    'Date',
] as const;

/** The option name a people type's fields are stored under. */
export function fieldsOptionFor(type: PeopleType): string {
    return `erp-${type.toLowerCase()}-fields`;
}

export class CustomFieldsPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    /** Opens the builder, optionally on one people type's tab. */
    async goto(type?: PeopleType): Promise<void> {
        const params: Record<string, string> = { page: 'custom-field-builder' };
        if (type) params.tab = type.toLowerCase();

        await this.page.goto(`/wp-admin/admin.php?${new URLSearchParams(params).toString()}`, {
            waitUntil: 'domcontentloaded',
        });
        await this.page.waitForTimeout(1200);
    }

    /** The people-type tabs the screen paints. */
    async tabs(): Promise<string[]> {
        return (await this.page.locator(customFieldSelectors.tab).allTextContents()).map((t) => t.trim()).filter(Boolean);
    }

    /** Labels of the fields currently listed for this people type. */
    async fieldLabels(): Promise<string[]> {
        const text = await this.page.locator(customFieldSelectors.fieldParent).innerText();

        return text
            .split('\n')
            .map((line) => line.trim())
            .filter((line) => line && line !== 'Add New Field');
    }

    get panel(): Locator {
        return this.page.locator(customFieldSelectors.fieldParent);
    }

    /**
     * Adds one field and closes its editor.
     *
     * The editor opens expanded on **Add New Field** and the two `select`s are
     * positional — section first, then type — because neither carries a name or
     * an id. `Done` collapses the editor; it does NOT persist. `save()` is what
     * writes the option, and forgetting it is the obvious way to write a test
     * that passes against nothing.
     */
    async addField(input: {
        label: string;
        section?: (typeof employeeSections)[number];
        type?: (typeof fieldTypes)[number];
        placeholder?: string;
        options?: { text: string; value: string }[];
    }): Promise<void> {
        await this.page.locator(customFieldSelectors.addNew).click();
        await this.page.waitForTimeout(600);

        const editor = this.panel.locator('.single-field').last();

        await editor.locator(customFieldSelectors.label).first().fill(input.label);
        await this.page.waitForTimeout(300);

        const selects = editor.locator('select');
        if (input.section) await selects.nth(0).selectOption({ label: input.section });
        if (input.type) await selects.nth(1).selectOption({ label: input.type });

        if (input.placeholder) await editor.locator(customFieldSelectors.placeholder).first().fill(input.placeholder);

        if (input.options) {
            for (const [index, option] of input.options.entries()) {
                if (index > 0) await editor.locator(customFieldSelectors.addOption).first().click();
                await editor.locator(customFieldSelectors.optionText).nth(index).fill(option.text);
                await editor.locator(customFieldSelectors.optionValue).nth(index).fill(option.value);
            }
        }

        await editor.locator(customFieldSelectors.done).first().click();
        await this.page.waitForTimeout(500);
    }

    /** The meta key the builder derived for the field currently being edited. */
    async metaKeyOf(index = 0): Promise<string> {
        return this.panel.locator('.single-field').nth(index).locator('input[type="text"]').nth(2).inputValue();
    }

    /**
     * Removes the nth field, accepting the confirmation it raises.
     *
     * Two traps here, and the first one nearly went out as a bug report:
     *
     * 1. `deleteModel()` opens a native `confirm()`. Playwright DISMISSES
     *    dialogs by default, so the click landed, no error was raised, and
     *    nothing was removed — which reads exactly like a dead button. The
     *    one-shot handler below is what makes the click mean anything.
     * 2. The trash button sits in an `.extended-button-holder` the stylesheet
     *    keeps at `display: none` until the row is HOVERED, so it has to be
     *    hovered before it can be clicked.
     *
     * Deleting also persists on its own — `deleteModel()` calls the module's
     * `sendToServer()` straight away — so unlike adding a field this needs no
     * `save()` afterwards.
     */
    async deleteField(index = 0): Promise<void> {
        const row = this.panel.locator('.single-field').nth(index);

        const accept = (dialog: Dialog) => void dialog.accept().catch(() => undefined);
        this.page.on('dialog', accept);

        try {
            await row.locator('.main-people-single').hover();
            await this.page.waitForTimeout(400);
            await row.locator('.main-people-single .button-delete').first().click();
            await this.page.waitForTimeout(2000);
        } finally {
            this.page.off('dialog', accept);
        }
    }

    /** Persists the current field list for this people type. */
    async save(): Promise<void> {
        await this.page.locator(customFieldSelectors.save).click();
        await this.page.waitForTimeout(2000);
    }
}
