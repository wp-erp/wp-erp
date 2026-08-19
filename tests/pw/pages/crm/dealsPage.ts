import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * CRM Deals — an erp-pro module (`modules/crm/deals`) with FOUR screens behind
 * one page slug, switched by `sub-section`:
 *
 *   dashboard  — the analytics overview (funnel, won/lost, charts)
 *   all-deals  — the pipeline board, where deals are created and dragged
 *   activities — the cross-deal activity list
 *   settings   — pipelines, stages, activity types and lost reasons, rendered
 *                inside ERP Settings rather than here
 *
 * Like payroll, the whole thing is Vue with **no ids and no name attributes** on
 * its form fields, so every control is anchored on its label text or on the one
 * structural id the markup does provide (`#new-deal-modal-body`,
 * `#erp-deals-pipeline-view`).
 *
 * It also has its own modal implementation — `.erp-deal-modal`, nothing to do
 * with the shared `#erp-modal` shell `BasePage` handles — and reports both
 * success and refusal through **sweetalert**, not through a notice.
 */
export const dealSelectors = {
    app: '#erp-deals',

    // board
    board: '#erp-deals-pipeline-view',
    stageHeaders: '#pipeline-stage-headers .pipeline-stage-column h4',
    stageColumns: '#pipeline-stage-deals .pipeline-stage-column',
    dealCards: '.deal-in-stage',
    addNew: 'a[href="#add-new-deal"]',
    statusFilter: 'button.filter-status',
    ownerFilter: 'button.filter-owner',
    pipeFilter: 'button.filter-pipe',
    openDropdownItem: '.deal-filter-dropdown.open .erp-dropdown-menu a',

    // new-deal modal
    modal: '.erp-deal-modal',
    modalBody: '#new-deal-modal-body',
    modalTitle: '.erp-deal-modal-title',
    modalSave: '.erp-deal-modal-footer button.button-primary',
    modalCancel: '.erp-deal-modal-footer button.button:not(.button-primary)',
    multiselectInput: '.multiselect__input',
    multiselectOption: '.multiselect__content .multiselect__option',
    stageBullets: '.step-progressbar li',
    fieldError: '.input-error',

    // sweetalert — the module's only success/error channel
    sweetAlert: '.sweet-alert',
    sweetAlertConfirm: '.sweet-alert button.confirm',

    // dashboard
    overview: '#deal-overview',
    funnelStage: '.stage-name',

    // activities
    activityList: '.erp-deal-activity-list',

    // settings (ERP Settings → CRM → Deals)
    settingsBox: '#erp-settings-box-erp-crm-erp_deals',
    settingsPipeline: '.settings-pipeline',
    settingsStageTitle: '.settings-pipeline .stage-title',
    addPipeline: 'button:has-text("Add new pipeline")',
} as const;

/** Sub-sections the module registers, captured from `Admin::load_new_menu()`. */
export const dealSubSections = ['dashboard', 'all-deals', 'activities'] as const;

/** Stage titles the plugin seeds into the default pipeline (`table-data.php`). */
export const seededStages = ['Lead In', 'Contact Made', 'Demo Scheduled', 'Proposal Made', 'Negotiations Started'] as const;

/** Columns of the cross-deal activity list, captured live. */
export const activityListColumns = ['Done', 'Type', 'Title', 'Deal', 'Contacts', 'Due date', 'Assigned To'] as const;

/** Statistic boxes on the dashboard, captured live. */
export const dashboardBoxes = ['New Deals', 'Won Deals', 'Lost Deals'] as const;

export interface NewDeal {
    contactSearch: string;
    title: string;
    value?: string;
    stage?: string;
    closeDate?: string;
}

export class DealsPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    // ---- navigation -------------------------------------------------------

    async goto(subSection: (typeof dealSubSections)[number] = 'all-deals'): Promise<void> {
        await this.gotoAdmin('erp-crm', { section: 'deals', 'sub-section': subSection });
        await this.waitForApp();
    }

    /**
     * The Vue app mounts after `DOMContentLoaded` and paints `v-cloak` away only
     * once its first AJAX round-trip lands. Waiting on the app root alone is not
     * enough — it exists in the PHP view — so this waits for real content.
     */
    async waitForApp(): Promise<void> {
        // A refused role never gets the app root at all, and an authorization
        // test has to reach `isAccessDenied()` to say so — so a refusal returns
        // immediately instead of burning two 20s waits on markup that will never
        // arrive.
        if (await this.isAccessDenied()) return;

        await this.page.locator(dealSelectors.app).waitFor({ state: 'attached', timeout: 20_000 }).catch(() => undefined);
        await this.page
            .locator(`${dealSelectors.board}, ${dealSelectors.overview}, ${dealSelectors.activityList}`)
            .first()
            .waitFor({ state: 'visible', timeout: 20_000 })
            .catch(() => undefined);
        await this.page.waitForTimeout(1500);
    }

    async gotoSettings(): Promise<void> {
        await this.gotoUrl(`/wp-admin/admin.php?page=erp-settings#/erp-crm/erp_deals`);
        await this.page.locator(dealSelectors.settingsPipeline).first().waitFor({ state: 'visible', timeout: 20_000 });
        await this.page.waitForTimeout(800);
    }

    // ---- board ------------------------------------------------------------

    /** Stage column titles, in the order the board paints them. */
    async boardStageOrder(): Promise<string[]> {
        return (await this.page.locator(dealSelectors.stageHeaders).allTextContents()).map((t) => t.trim()).filter(Boolean);
    }

    /**
     * Funnel stage labels on the dashboard, in painted order.
     *
     * The funnel table's own column header reuses `.stage-name`, so the first
     * cell is the literal word "Stage" rather than a pipeline stage. Dropping it
     * here keeps that quirk out of every spec that asks for the order.
     */
    async funnelStageOrder(): Promise<string[]> {
        const cells = (await this.page.locator(dealSelectors.funnelStage).allTextContents()).map((t) => t.trim()).filter(Boolean);

        return cells.filter((cell) => cell !== 'Stage');
    }

    /** Deal titles currently on the board, per stage column. */
    async dealsByStage(): Promise<Record<string, string[]>> {
        const order = await this.boardStageOrder();
        const columns = this.page.locator(dealSelectors.stageColumns);
        const out: Record<string, string[]> = {};

        for (let i = 0; i < order.length; i++) {
            const titles = await columns.nth(i).locator(`${dealSelectors.dealCards} h5`).allTextContents();
            out[order[i]!] = titles.map((t) => t.trim()).filter(Boolean);
        }

        return out;
    }

    async hasDealOnBoard(title: string): Promise<boolean> {
        const byStage = await this.dealsByStage();
        return Object.values(byStage).some((titles) => titles.some((t) => t.includes(title)));
    }

    /** Total deal cards painted, across every stage. */
    async dealCardCount(): Promise<number> {
        return this.page.locator(dealSelectors.dealCards).count();
    }

    /** The board's own per-stage counters, e.g. "$0.00 - 0 deal". */
    async stageCounterText(): Promise<string> {
        return ((await this.page.locator('#pipeline-stage-headers').textContent()) ?? '').replace(/\s+/g, ' ').trim();
    }

    async filterLabels(): Promise<string[]> {
        const labels: string[] = [];

        for (const selector of [dealSelectors.statusFilter, dealSelectors.ownerFilter]) {
            const control = this.page.locator(selector);
            if (await control.isVisible().catch(() => false)) {
                labels.push(((await control.textContent()) ?? '').trim());
            }
        }

        return labels;
    }

    /** True when the pipeline switcher is offered — it only renders for 2+ pipelines. */
    async hasPipelineFilter(): Promise<boolean> {
        return this.page.locator(dealSelectors.pipeFilter).isVisible().catch(() => false);
    }

    /** Opens the status dropdown and returns the options it offers. */
    async statusFilterOptions(): Promise<string[]> {
        await this.page.locator(dealSelectors.statusFilter).click();
        await this.page.waitForTimeout(400);
        const options = (await this.page.locator(dealSelectors.openDropdownItem).allTextContents()).map((t) => t.trim());
        await this.page.keyboard.press('Escape');
        return options.filter(Boolean);
    }

    // ---- new-deal modal ---------------------------------------------------

    /**
     * NOT `BasePage.modal` — deals ships its own modal implementation
     * (`.erp-deal-modal`) rather than the shared `#erp-modal` shell, so the
     * inherited helpers do not apply here.
     */
    get dealModal(): Locator {
        return this.page.locator(dealSelectors.modal);
    }

    async openNewDealModal(): Promise<void> {
        await this.page.locator(dealSelectors.addNew).first().click();
        await this.page.locator(dealSelectors.modalBody).waitFor({ state: 'visible', timeout: 10_000 });
        await this.page.waitForTimeout(600);
    }

    /**
     * The modal's fields carry no id, name or placeholder — only a sibling
     * `<label>`. This finds the `.margin-bottom-15` block whose label matches and
     * returns the input inside it.
     */
    private fieldByLabel(label: string, type = 'text'): Locator {
        return this.page.locator(`${dealSelectors.modalBody} .margin-bottom-15`).filter({ has: this.page.locator('label', { hasText: label }) }).locator(`input[type="${type}"]`).first();
    }

    private multiselectByLabel(label: string): Locator {
        return this.page.locator(`${dealSelectors.modalBody} > div`).filter({ has: this.page.locator('label', { hasText: label }) }).locator('.multiselect').first();
    }

    /**
     * Picks a contact through the multiselect.
     *
     * The search fires only at **3 or more characters** and is debounced through
     * a real AJAX round-trip (`erp_deals_search_people`), so the options list is
     * awaited rather than assumed.
     */
    async selectContact(search: string): Promise<boolean> {
        const box = this.multiselectByLabel('Contact');
        await box.click();
        await box.locator(dealSelectors.multiselectInput).pressSequentially(search, { delay: 40 });
        await this.page.waitForTimeout(2500);

        const option = box.locator(dealSelectors.multiselectOption).filter({ hasText: search }).first();

        if (!(await option.isVisible().catch(() => false))) return false;

        await option.click();
        await this.page.waitForTimeout(400);

        return true;
    }

    /** The stage bullets carry their title in `tooltip-title`, not as text. */
    async pickStage(title: string): Promise<void> {
        await this.page.locator(`${dealSelectors.modalBody} ${dealSelectors.stageBullets}[tooltip-title="${title}"]`).first().click();
        await this.page.waitForTimeout(200);
    }

    /** Stage titles offered by the modal, in painted order. */
    async modalStageOrder(): Promise<string[]> {
        return this.page.locator(`${dealSelectors.modalBody} ${dealSelectors.stageBullets}`).evaluateAll((nodes) =>
            nodes.map((n) => n.getAttribute('tooltip-title') ?? '')
        );
    }

    /** The stage the modal pre-selects for a brand-new deal. */
    async defaultStage(): Promise<string> {
        return (
            (await this.page
                .locator(`${dealSelectors.modalBody} ${dealSelectors.stageBullets}.active`)
                .first()
                .getAttribute('tooltip-title')
                .catch(() => null)) ?? ''
        );
    }

    async fillDealTitle(title: string): Promise<void> {
        await this.fieldByLabel('Deal Title').fill(title);
    }

    /** Whatever is currently in the Deal Title box. */
    async dealTitleValue(): Promise<string> {
        return this.fieldByLabel('Deal Title').inputValue();
    }

    async fillDealValue(value: string): Promise<void> {
        await this.fieldByLabel('Deal Value', 'number').fill(value);
    }

    async save(): Promise<void> {
        await this.page.locator(dealSelectors.modalSave).click();
        await this.page.waitForTimeout(2500);
    }

    /** Fills and submits the whole modal. Returns the sweetalert text. */
    async createDeal(deal: NewDeal): Promise<string> {
        await this.openNewDealModal();

        const found = await this.selectContact(deal.contactSearch);
        if (!found) throw new Error(`contact search "${deal.contactSearch}" returned no option — the deal cannot be built`);

        await this.fillDealTitle(deal.title);
        if (deal.value) await this.fillDealValue(deal.value);
        if (deal.stage) await this.pickStage(deal.stage);
        if (deal.closeDate) await this.fieldByLabel('Expected Close Date').fill(deal.closeDate);

        await this.save();

        return this.alertText();
    }

    /** True while the new-deal modal is still on screen. */
    async isModalStillOpen(): Promise<boolean> {
        return this.page.locator(dealSelectors.modalBody).isVisible().catch(() => false);
    }

    /** How many modal fields the client marked invalid. */
    async invalidFieldCount(): Promise<number> {
        return this.page.locator(`${dealSelectors.modalBody} ${dealSelectors.fieldError}`).count();
    }

    // ---- sweetalert -------------------------------------------------------

    /** Text of the sweetalert currently on screen, or ''. */
    async alertText(): Promise<string> {
        const alert = this.page.locator(dealSelectors.sweetAlert).first();

        if (!(await alert.isVisible().catch(() => false))) return '';

        return ((await alert.textContent()) ?? '').replace(/\s+/g, ' ').trim();
    }

    async dismissAlert(): Promise<void> {
        const confirm = this.page.locator(dealSelectors.sweetAlertConfirm).first();

        if (await confirm.isVisible().catch(() => false)) {
            await confirm.click();
            await this.page.waitForTimeout(800);
        }
    }

    // ---- settings ---------------------------------------------------------

    /** Stage titles as ERP Settings lists them, in painted order. */
    async settingsStageOrder(): Promise<string[]> {
        return (await this.page.locator(dealSelectors.settingsStageTitle).allTextContents()).map((t) => t.trim()).filter(Boolean);
    }

    // ---- AJAX probes ------------------------------------------------------

    /**
     * Calls one of the module's 41 `wp_ajax_erp_deals_*` actions from inside the
     * page, using the nonce the page itself was given.
     *
     * The whole module is AJAX-only — it registers no REST routes — so this is
     * the only way to test a handler's own permission check rather than the
     * menu's. Returns the parsed body plus the HTTP status.
     */
    async callAjax(action: string, data: Record<string, unknown> = {}): Promise<{ status: number; body: unknown }> {
        return this.page.evaluate(
            async ({ action, data }) => {
                const globals = (window as unknown as { erpDealsGlobal?: { ajaxurl: string; nonce: string } }).erpDealsGlobal;
                if (!globals) return { status: 0, body: 'erpDealsGlobal absent — the screen never loaded its script' };

                const form = new URLSearchParams();
                form.set('action', action);
                form.set('_wpnonce', globals.nonce);

                const flatten = (prefix: string, value: unknown): void => {
                    if (value !== null && typeof value === 'object') {
                        for (const [k, v] of Object.entries(value as Record<string, unknown>)) flatten(`${prefix}[${k}]`, v);
                    } else {
                        form.set(prefix, String(value));
                    }
                };

                for (const [key, value] of Object.entries(data)) flatten(key, value);

                const response = await fetch(globals.ajaxurl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: form.toString(),
                });

                const text = await response.text();

                try {
                    return { status: response.status, body: JSON.parse(text) };
                } catch {
                    return { status: response.status, body: text };
                }
            },
            { action, data }
        );
    }

    /**
     * The GET half of the same thing.
     *
     * Several handlers read `$_GET` rather than `$_POST` — `get_single_deal_data`,
     * `get_deals_by_pipeline`, `get_overview_data`, `search_people` — so posting
     * to them silently sends no arguments at all and the handler answers its
     * "invalid" branch, which reads exactly like a permission refusal. Use this
     * one for those.
     */
    async callAjaxGet(action: string, data: Record<string, unknown> = {}): Promise<{ status: number; body: unknown }> {
        return this.page.evaluate(
            async ({ action, data }) => {
                const globals = (window as unknown as { erpDealsGlobal?: { ajaxurl: string; nonce: string } }).erpDealsGlobal;
                if (!globals) return { status: 0, body: 'erpDealsGlobal absent — the screen never loaded its script' };

                const params = new URLSearchParams({ action, _wpnonce: globals.nonce });
                for (const [key, value] of Object.entries(data)) params.set(key, String(value));

                const response = await fetch(`${globals.ajaxurl}?${params.toString()}`, { credentials: 'same-origin' });
                const text = await response.text();

                try {
                    return { status: response.status, body: JSON.parse(text) };
                } catch {
                    return { status: response.status, body: text };
                }
            },
            { action, data }
        );
    }

    /** The nonce and role flags the module hands the current user. */
    async localizedGlobals(): Promise<Record<string, unknown> | null> {
        return this.page.evaluate(() => {
            const g = (window as unknown as { erpDealsGlobal?: Record<string, unknown> }).erpDealsGlobal;
            if (!g) return null;
            return {
                nonce: g.nonce,
                isUserAnAdmin: g.isUserAnAdmin,
                isUserAManager: g.isUserAManager,
                isUserAnAgent: g.isUserAnAgent,
                pipes: g.pipes,
            };
        });
    }
}
