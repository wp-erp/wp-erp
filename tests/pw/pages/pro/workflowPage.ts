import { Page } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * Workflow (@pro) — event → condition → action automation.
 *
 * A plain WordPress admin screen (no SPA), gated on the custom capability
 * `erp_workflow_menu_permission`, driven entirely by `admin-ajax.php`. There is
 * no REST controller in this build: the `Api/` directory and
 * `WorkflowControllerV2` that erp-pro 1.6.0 shipped are both absent from 1.7.0.
 */
export const workflowSelectors = {
    listTable: '#wpbody-content table.wp-list-table',
    addNew: 'a[href*="page=erp-workflow-new"]',
} as const;

/** Every ajax action the module registers, and whether it verifies a nonce. */
export const workflowAjaxActions = [
    { action: 'erp_wf_fetch_workflow', guarded: false },
    { action: 'erp_wf_new_workflow', guarded: true },
    { action: 'erp_wf_edit_workflow', guarded: true },
    { action: 'erp_wf_get_employees', guarded: true },
    { action: 'erp_wf_get_contacts', guarded: true },
    { action: 'erp_wf_get_crm_users', guarded: true },
] as const;

export class WorkflowPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(): Promise<void> {
        await this.gotoAdmin('erp-workflow');
        await this.page.waitForTimeout(1500);
    }

    async gotoNew(): Promise<void> {
        await this.gotoAdmin('erp-workflow-new');
        await this.page.waitForTimeout(1500);
    }

    /** Names listed on the workflow table. */
    async workflowNames(): Promise<string[]> {
        const rows = this.page.locator(`${workflowSelectors.listTable} tbody tr`);
        if (!(await rows.count())) return [];

        return (await rows.locator('td, th').first().allTextContents()).map((t) => t.trim()).filter(Boolean);
    }

    /**
     * Posts one `admin-ajax.php` action with no nonce at all.
     *
     * Sent from inside the page so it carries the caller's own cookies and
     * nothing else — the point is what an ordinary logged-in session can reach
     * without ever visiting the workflow screen.
     */
    async ajaxWithoutNonce(action: string, params: Record<string, string> = {}): Promise<{ status: number; body: string }> {
        return this.page.evaluate(
            async ({ action, params }) => {
                const body = new URLSearchParams({ action, ...params });
                const r = await fetch('/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString(),
                });

                return { status: r.status, body: (await r.text()).slice(0, 1500) };
            },
            { action, params }
        );
    }
}
