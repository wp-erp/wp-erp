import { Locator, Page } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * SMS notification (@pro) — ERP Settings → Integration → SMS.
 *
 * The module registers itself through `erp_integration_classes`, so it has no
 * menu of its own: it is a panel that opens INLINE underneath the integration
 * table, on the settings SPA route `#/erp-integration`. One gateway is active
 * at a time and each brings its own credential fields.
 */
export const smsSelectors = {
    integrationRow: 'tr',
    configure: 'a, button',
    gatewayPicker: '.multiselect',
    panelHeading: 'text=SMS Integration',
    save: 'button:has-text("Save")',
} as const;

/** Every gateway the module bundles, and the fields each one asks for. */
export const smsGateways = [
    { label: 'Twilio', slug: 'twilio', fields: ['Number From', 'Account SID', 'Auth Token'] },
    { label: 'Clickatell', slug: 'clickatell', fields: ['Username', 'Password', 'API ID'] },
    { label: 'SMSGlobal', slug: 'smsglobal', fields: ['Username', 'Password', 'From Number'] },
    { label: 'Nexmo', slug: 'nexmo', fields: ['API Key', 'API Secret', 'Sender ID'] },
    { label: 'Hoiio', slug: 'hoiio', fields: ['App ID', 'Access Token'] },
    { label: 'Intellisms', slug: 'intellisms', fields: ['Username', 'Password', 'Sender'] },
    { label: 'Infobip', slug: 'infobip', fields: ['Username', 'Password', 'Sender'] },
] as const;

export type SmsGateway = (typeof smsGateways)[number]['label'];

/** The option row every SMS setting is stored under. */
export const SMS_OPTION = 'erp_integration_settings_erp-sms';

/**
 * Field ids that hold a secret. Declared `'type' => 'text'` by the module for
 * every gateway, which is what the masking case asserts against.
 */
export const smsSecretFieldIds = [
    'erp-erp_sms_twilio_auth_token',
    'erp-erp_sms_clickatell_password',
    'erp-erp_sms_smsglobal_password',
    'erp-erp_sms_nexmo_apisecret',
    'erp-erp_sms_hoiio_access_token',
    'erp-erp_sms_intellisms_password',
    'erp-erp_sms_infobip_password',
] as const;

export class SmsSettingsPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    /**
     * Opens the integration list and expands the SMS panel.
     *
     * The settings screen is a Vue SPA on a hash route — a query-string URL for
     * the section bounces to General, which is what makes the hash form load
     * bearing rather than cosmetic. The wait is generous because the panel is
     * rendered only after the integration list has fetched.
     */
    async goto(): Promise<void> {
        await this.gotoUrl('/wp-admin/admin.php?page=erp-settings#/erp-integration');
        await this.page.waitForTimeout(6000);

        const row = this.page.locator(smsSelectors.integrationRow).filter({ hasText: /(^|\s)SMS(\s|$)/ }).first();
        await row.locator(smsSelectors.configure).filter({ hasText: /Configure/i }).first().click();
        await this.page.waitForTimeout(3000);
    }

    /** True once the SMS panel is on screen. */
    async isOpen(): Promise<boolean> {
        return (await this.bodyText()).includes('SMS Integration');
    }

    get gatewayPicker(): Locator {
        return this.page.locator(smsSelectors.gatewayPicker).first();
    }

    /** Every gateway the Active Gateway picker offers. */
    async gatewayOptions(): Promise<string[]> {
        await this.gatewayPicker.click();
        await this.page.waitForTimeout(600);

        const options = (await this.gatewayPicker.locator('.multiselect__option').allTextContents())
            .map((t) => t.trim())
            .filter((t) => t && !/Oops|List is empty/i.test(t));

        await this.page.keyboard.press('Escape');

        return options;
    }

    /** The gateway currently selected. */
    async activeGateway(): Promise<string> {
        return (await this.gatewayPicker.locator('.multiselect__single').innerText()).trim();
    }

    /** Switches the active gateway, which swaps the credential fields below it. */
    async chooseGateway(label: SmsGateway): Promise<boolean> {
        await this.gatewayPicker.click();
        await this.page.waitForTimeout(600);

        const option = this.gatewayPicker.locator('.multiselect__option', { hasText: label }).first();
        if (!(await option.isVisible().catch(() => false))) return false;

        await option.click();
        await this.page.waitForTimeout(1200);

        return true;
    }

    /** Ids of the credential inputs the panel is currently showing. */
    async visibleFieldIds(): Promise<string[]> {
        return this.page
            .locator('#wpbody-content input[id^="erp-erp_sms"]')
            .evaluateAll((els) => els.filter((e) => (e as HTMLElement).offsetParent !== null).map((e) => e.id));
    }

    /** The `type` attribute of a credential input, by its id. */
    async fieldType(id: string): Promise<string | null> {
        return this.page.locator(`#${id}`).getAttribute('type');
    }

    /** Fills a credential field by its id. */
    async fillField(id: string, value: string): Promise<void> {
        await this.page.locator(`#${id}`).fill(value);
        await this.page.waitForTimeout(200);
    }

    async save(): Promise<void> {
        await this.page.locator('#wpbody-content').getByRole('button', { name: /^Save$/ }).first().click();
        await this.page.waitForTimeout(2500);
    }
}
