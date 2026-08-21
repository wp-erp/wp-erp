import { Page } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * Advanced Leave (@pro) — five optional behaviours bolted onto core leave:
 * half-day requests, multilevel approval, segregation, carry/encash forwarding
 * and unpaid-leave tracking.
 *
 * Each is OFF by default and each gates itself in its own constructor on a
 * plain `get_option()`, so a feature that is off registers nothing at all — no
 * menu, no ajax handler, no form field. That is why the screens below cannot be
 * asserted without turning the toggle on first: the URL simply falls back to
 * Leave Requests.
 */
export const advancedLeaveToggles = [
    { label: 'Enable Half-Day Request', option: 'erp_pro_half_leave' },
    { label: 'Enable Multilevel Approval', option: 'erp_pro_multilevel_approval' },
    { label: 'Enable Segregation', option: 'erp_pro_seg_leave' },
    { label: 'Enable Carry / Encash', option: 'erp_pro_carry_encash_leave' },
] as const;

export type AdvancedLeaveToggle = (typeof advancedLeaveToggles)[number]['label'];

/** Ajax actions the module registers, with the capability each demands. */
export const advancedLeaveAjax = [
    { action: 'erp_pro_hr_leave_multilevel_approval', needs: 'erp_leave_manage' },
    { action: 'erp_pro_hr_unpaid_leave_calc', needs: 'erp_leave_manage' },
    { action: 'erp_pro_hr_unpaid_leave_calc_single', needs: 'erp_leave_manage' },
    { action: 'erp_pro_hr_check_halfday_availability', needs: 'erp_leave_manage' },
] as const;

export class AdvancedLeavePage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    /** HR → Leave settings, where all four toggles live. */
    async gotoLeaveSettings(): Promise<void> {
        await this.gotoUrl('/wp-admin/admin.php?page=erp-settings#/erp-hr/leave');
        await this.page.waitForTimeout(6000);
    }

    /** The HR → Leave screen, for reading its sub-navigation. */
    async gotoLeave(): Promise<void> {
        await this.gotoAdmin('erp-hr', { section: 'leave' });
        await this.page.waitForTimeout(1500);
    }

    /** Link labels the Leave sub-navigation offers. */
    async leaveSubNav(): Promise<string[]> {
        return (
            await this.page
                .locator('#wpbody-content a[href*="section=leave"]')
                .allTextContents()
        )
            .map((t) => t.trim())
            .filter(Boolean);
    }

    /**
     * Ticks or unticks one toggle and saves.
     *
     * The real `input[type=checkbox]` is visually hidden behind
     * `.form-check-sign`, so Playwright refuses to `check()` it — the clickable
     * thing is the wrapping `label.form-check-label`. Saving writes EVERY toggle
     * on the tab as `yes`/`no`, not just the one touched.
     */
    async setToggle(label: AdvancedLeaveToggle, on: boolean): Promise<void> {
        const row = this.page
            .locator('#wpbody-content')
            .locator('tr, .erp-settings-field-wrap, div')
            .filter({ hasText: label })
            .last();

        const box = row.locator('input[type="checkbox"]').first();

        if ((await box.isChecked()) !== on) {
            await row.locator('label.form-check-label').first().click();
            await this.page.waitForTimeout(400);
        }

        await this.page
            .locator('#wpbody-content')
            .getByRole('button', { name: /Save Changes|^Save$/ })
            .first()
            .click();
        await this.page.waitForTimeout(3500);
    }

    /** Whether a toggle currently reads as on. */
    async isToggleOn(label: AdvancedLeaveToggle): Promise<boolean> {
        const row = this.page
            .locator('#wpbody-content')
            .locator('tr, .erp-settings-field-wrap, div')
            .filter({ hasText: label })
            .last();

        return row.locator('input[type="checkbox"]').first().isChecked();
    }

    /** Opens an arbitrary admin URL and lets the screen settle. */
    async open(url: string): Promise<void> {
        await this.gotoUrl(url);
        await this.page.waitForTimeout(1500);
    }

    /** Posts an advanced-leave ajax action with no nonce. */
    async ajaxWithoutNonce(action: string): Promise<{ status: number; body: string }> {
        return this.page.evaluate(async (action) => {
            const r = await fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action }).toString(),
            });

            return { status: r.status, body: (await r.text()).slice(0, 300) };
        }, action);
    }
}
