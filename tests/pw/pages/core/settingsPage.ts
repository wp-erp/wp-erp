import { Page } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * ERP Settings — the Vue SPA at `admin.php?page=erp-settings#/{module}`.
 *
 * Saving goes through one ajax action, `erp-settings-save`, whose permission
 * model is **per module** (`Settings/Ajax.php:53-95`): `general`, `erp-email`
 * and `erp-integration` require `manage_options`, while `erp-hr`, `erp-ac` and
 * `erp-crm` also accept that module's own manager role. Every one of the
 * eleven settings ajax handlers verifies a nonce AND a capability.
 */
export const settingsModules = ['general', 'erp-hr', 'erp-crm', 'erp-ac', 'erp-email', 'erp-integration'] as const;
export type SettingsModule = (typeof settingsModules)[number];

/** Modules a non-admin manager may save, and the role that may save each. */
export const managerScopedModules: Partial<Record<SettingsModule, string>> = {
    'erp-hr': 'erp_hr_manager',
    'erp-ac': 'erp_ac_manager',
    'erp-crm': 'erp_crm_manager',
};

/** Tab labels the settings screen paints, captured live. */
export const settingsTabs = ['General', 'HR', 'CRM', 'WooCommerce', 'Accounting', 'Emails', 'Integration'] as const;

export class SettingsPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(module: SettingsModule | '' = ''): Promise<void> {
        await this.gotoUrl(`/wp-admin/admin.php?page=erp-settings${module ? `#/${module}` : ''}`);
        await this.page.waitForTimeout(5000);
    }

    /** The settings nonce the SPA itself uses. */
    async settingsNonce(): Promise<string> {
        return this.page.evaluate(() => {
            const w = window as unknown as { erp_settings_var?: { nonce?: string } };
            return w.erp_settings_var?.nonce ?? '';
        });
    }

    /**
     * Attempts a settings save through the product's own ajax action.
     *
     * Sends a real nonce taken from the caller's own page, so a refusal can only
     * be about the CAPABILITY — which is the whole point of the authorization
     * cases. A missing nonce would refuse everyone and prove nothing.
     */
    async saveModule(module: SettingsModule, fields: Record<string, string> = {}): Promise<{ ok: boolean; body: string }> {
        const nonce = await this.settingsNonce();

        return this.page.evaluate(
            async ({ module, fields, nonce }) => {
                const body = new URLSearchParams({ action: 'erp-settings-save', _wpnonce: nonce, module, ...fields });
                const r = await fetch('/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString(),
                });
                const text = await r.text();

                let ok = false;
                try {
                    ok = Boolean(JSON.parse(text).success);
                } catch {
                    ok = false;
                }

                return { ok, body: text.slice(0, 220) };
            },
            { module, fields, nonce }
        );
    }
}
