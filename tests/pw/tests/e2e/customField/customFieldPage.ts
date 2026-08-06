import { expect, type Page } from '@utils/test';
import { toPath } from '@utils/helpers';

/**
 * Feature-isolated page object for the WP ERP Pro "Custom Field Builder" admin
 * screen (erp-pro module: custom_field_builder).
 *
 * Route (grounded in Module.php L138,L218-227): the page is registered via
 * add_submenu_page('erp', ..., 'custom-field-builder', ...) on the
 * 'erp_submenu_page' action (AdminMenu fires it for WPERP >= 1.4.0). Admin.php's
 * own admin_menu hook is COMMENTED OUT — the menu only exists through that
 * submenu. So the URL is admin.php?page=custom-field-builder (NOT
 * 'erp-custom-field-builder'); the people-type tab is selected with &tab=.
 *
 * The screen is a jQuery + Vue hybrid (not an SPA route): #people-field-parent is
 * the Vue mount, populated from the localized wpErpForm.collection. Tabs are
 * gated by the active modules (Employee/Contact/Company/Customer/Vendor).
 *
 * Selectors are taken verbatim from the rendered view (views/view.php).
 */
export class CustomFieldPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    // ── URLs (admin.php?page=custom-field-builder, people tab via &tab=) ───────
    readonly urls = {
        // Default landing (no tab -> the first active people type).
        base: toPath('wp-admin/admin.php?page=custom-field-builder'),
        tab: (people: string) => toPath(`wp-admin/admin.php?page=custom-field-builder&tab=${people}`),
        employee: toPath('wp-admin/admin.php?page=custom-field-builder&tab=employee'),
        contact: toPath('wp-admin/admin.php?page=custom-field-builder&tab=contact'),
        company: toPath('wp-admin/admin.php?page=custom-field-builder&tab=company'),
        customer: toPath('wp-admin/admin.php?page=custom-field-builder&tab=customer'),
        vendor: toPath('wp-admin/admin.php?page=custom-field-builder&tab=vendor'),
    } as const;

    // ── Selectors (verbatim from views/view.php) ──────────────────────────────
    readonly sel = {
        wrap: 'div.wrap',
        heading: 'div.wrap > h2:not(.nav-tab-wrapper)', // title "Custom Field Builder" (force-pro renders a 2nd div.wrap>h2: the nav-tab-wrapper)
        tabBar: 'h2.nav-tab-wrapper',
        tab: 'a.nav-tab',
        activeTab: 'a.nav-tab-active',
        poststuff: '#poststuff', // WP metabox layout root (real mount)
        fieldParent: '#people-field-parent', // Vue field collection container
        addNewField: '#add-new-field button.button-primary', // "Add New Field"
        saveFields: '#save-fields.button-primary', // "Save Changes"
        savePostbox: 'div.save-fields-postbox',
        successNotice: '#notice-save-success', // hidden until a save
        singleFieldTemplate: 'script#single-field-template',
        singleField: '#people-field-parent .single-field',
    } as const;

    static readonly CRITICAL_ERROR = 'There has been a critical error on this website';

    /** Navigate to a people-type tab and confirm the page mounted without a fatal. */
    async goToTab(people: string): Promise<void> {
        await this.page.goto(this.urls.tab(people));
        await expect(this.page.locator('body')).not.toContainText(CustomFieldPage.CRITICAL_ERROR);
        await expect(this.page.locator(this.sel.wrap).first()).toBeVisible();
    }

    /** Navigate to the default (no-tab) landing and confirm it mounted. */
    async goToBase(): Promise<void> {
        await this.page.goto(this.urls.base);
        await expect(this.page.locator('body')).not.toContainText(CustomFieldPage.CRITICAL_ERROR);
        await expect(this.page.locator(this.sel.wrap).first()).toBeVisible();
    }

    /** Wait for the Vue field collection container to attach (jQuery+Vue boot). */
    async waitForVueMount(): Promise<void> {
        await expect(this.page.locator(this.sel.fieldParent)).toBeVisible({ timeout: 15_000 });
    }

    /** The text the page heading must contain. */
    headingText(): RegExp {
        return /Custom Field Builder/i;
    }
}
