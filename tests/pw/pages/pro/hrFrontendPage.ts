import { Page } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * HR Frontend (@pro) — the employee self-service dashboard served on the SITE,
 * not in wp-admin, at `/{slug}/` via a rewrite rule.
 *
 * Access is layered and correct: not logged in → the login screen; logged in
 * but neither an ERP employee nor an HR manager/administrator → back to
 * wp-admin (`Rewrites.php:53-69`).
 *
 * ⚠️ The slug has TWO different defaults in the same module, which is ERP-159:
 * `get_erp_dashboard_slug()` falls back to `wp-erp` and registers the rewrite,
 * while `DashboardSettings` falls back to `wp-erp-dashboard` and is what the
 * settings screen displays. `LIVE_SLUG` below is the one that actually serves.
 */
export const HR_FRONTEND_SETTINGS_ROUTE = '/wp-json/erp/v1/hrm/hr-frontend/settings';

/** The slug the rewrite rule really registers on a site that has never saved the setting. */
export const LIVE_SLUG = 'wp-erp';

export interface HrFrontendSettings {
    hr_frontend_slug: string;
    hr_frontend_dashboard_title: string;
    hr_frontend_logo: string;
    hr_frontend_redirect: boolean;
}

export class HrFrontendPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    /** Opens the dashboard at a given slug and reports what came back. */
    async openDashboard(slug: string): Promise<{ status: number; url: string; title: string }> {
        const response = await this.page.goto(`http://localhost:8888/${slug}/`, { waitUntil: 'domcontentloaded' });
        await this.page.waitForTimeout(1200);

        return {
            status: response?.status() ?? 0,
            url: this.page.url(),
            title: await this.page.title(),
        };
    }

    /** True when the dashboard shell actually rendered. */
    async showsDashboard(): Promise<boolean> {
        const body = (await this.page.locator('body').innerText()).replace(/\s+/g, ' ');

        return /HR Dashboard|WP ERP/i.test(await this.page.title()) && !/Page not found/i.test(body);
    }

    /**
     * Reads the dashboard settings through REST, using the caller's own nonce.
     *
     * Navigates to wp-admin first because `wpApiSettings` is only printed there,
     * and a relative `fetch` needs a real origin.
     */
    async readSettings(): Promise<{ status: number; settings: HrFrontendSettings | null }> {
        await this.gotoUrl('/wp-admin/');
        await this.page.waitForTimeout(800);

        return this.page.evaluate(async (route) => {
            const w = window as unknown as { wpApiSettings?: { nonce?: string } };
            const r = await fetch(route, { headers: { 'X-WP-Nonce': w.wpApiSettings?.nonce ?? '' } });
            const text = await r.text();

            try {
                return { status: r.status, settings: JSON.parse(text) };
            } catch {
                return { status: r.status, settings: null };
            }
        }, HR_FRONTEND_SETTINGS_ROUTE);
    }
}
