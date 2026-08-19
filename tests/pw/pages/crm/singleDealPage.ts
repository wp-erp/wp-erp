import { Page, Locator } from '@playwright/test';
import { DealsPage, dealSelectors } from '@pages/crm/dealsPage';
import { adminPath } from '@utils/helpers';

/**
 * The single-deal page — `sub-section=all-deals&action=view-deal&id=N`.
 *
 * The largest surface in the Deals module: an editable header (title, value,
 * owner, pipeline, expected close date), a clickable stage bar, Won / Lost /
 * Trash controls, five sidebar boxes, a four-tab composer, competitors, and a
 * timeline with its own tabs.
 *
 * Extends `DealsPage` for `callAjax()` and the board navigation the setup of
 * every case here needs.
 *
 * Two structural notes worth keeping:
 *
 * 1. **The ids are Vue `_uid` counters** (`activity-form-22`, `erp-deal-note-24`,
 *    `competitor-form-16`). They were stable across reloads when measured, but
 *    they are a render-order artifact, not a contract — everything below anchors
 *    on classes instead.
 * 2. **The stage bar's `tooltip-title` is NOT the stage name here.** It is a
 *    composed history string ("Lead In 0 days ($500.00)"), unlike the Add New
 *    Deal modal where it is the bare title. Stages are addressed by index.
 */
export const singleDealSelectors = {
    root: '#erp-deal-single',
    title: '#erp-deal-single h1.editable-content',
    value: '#erp-deal-single .deal-value .editable-content',
    contact: '#erp-deal-single .deal-contact',
    company: '#erp-deal-single .deal-company',
    owner: '#erp-deal-single .deal-owner',

    statusButtons: '#erp-deal-single .deal-status-buttons button',
    badgeWon: '#erp-deal-single .erp-badge-success',
    badgeLost: '#erp-deal-single .erp-badge-danger',

    stageBar: '#erp-deal-single .step-progressbar li',
    activeStage: '#erp-deal-single .step-progressbar li.active',

    sidebarBoxes: '#erp-deal-single h3.hndle, #erp-deal-single h3.postbox-title-outside',

    feedTabs: '.erp-feed-editors-tabs button',
    noteEditor: '.erp-feed-editor-note trix-editor',
    noteSave: '.erp-feed-editor-note-footer button[type="submit"]',
    noteSticky: '.erp-feed-editor-note-footer input[type="checkbox"]',

    competitorBox: '.deal-competitors',
    competitorAdd: '.deal-competitors .add-new-btn',
    competitorModal: '.erp-deal-competitor-modal',
    competitorRows: '.deal-competitors tbody tr',

    timeline: '.erp-deals-timeline',
    timelineTabs: '.erp-deals-timeline .timeline-tabs button',
    timelineItems: '.erp-deals-timeline .single-item',
} as const;

/** Sidebar boxes the page renders, captured live. */
export const dealBoxes = ['Company', 'Contact', 'Participants', 'Agents', 'Overview', 'Open Activities', 'Competitors'] as const;

/** Composer tabs, captured live. */
export const composerTabs = ['Add activity', 'Take notes', 'Send Mail', 'Upload Files'] as const;

/**
 * Timeline tabs, captured live.
 *
 * **Title Case, not the upper case the screen shows.** The tabs are uppercased
 * by CSS `text-transform`, so a dump taken with `innerText` reports "NOTES"
 * while `textContent` — and therefore every Playwright text API — sees "Notes".
 */
export const timelineTabs = ['All', 'Activities', 'Notes', 'Emails', 'Attachments', 'Changelog'] as const;

export interface Competitor {
    name: string;
    website?: string;
    strengths?: string;
    weaknesses?: string;
}

export class SingleDealPage extends DealsPage {
    constructor(page: Page) {
        super(page);
    }

    // ---- setup ------------------------------------------------------------

    /**
     * Creates a deal straight through `erp_deals_save_deal` and returns its id.
     *
     * Faster than the modal, and the modal is already covered by `deals.spec.ts`
     * — here a deal is a precondition, not the thing under test.
     *
     * `owner_id` is REQUIRED even though the field looks optional: the AJAX layer
     * defaults the owner to the current user only for non-managers, so an admin
     * omitting it gets a failed insert reported as the generic
     * "Could not save the deal. Please try again."
     */
    async createDealFast(title: string, stageId = 1, value = 500): Promise<number> {
        await this.goto('all-deals');

        const userId = await this.page.evaluate(
            () => (window as unknown as { erpDealsGlobal: { currentUserId: number } }).erpDealsGlobal.currentUserId
        );

        const contactId = await this.firstContactId();

        const response = await this.callAjax('erp_deals_save_deal', {
            deal: { title, contact_id: contactId, stage_id: stageId, owner_id: userId, value },
        });

        const body = response.body as { success?: boolean; data?: { deal?: { id?: number | string } }; };
        const id = Number(body?.data?.deal?.id ?? 0);

        if (!id) throw new Error(`deal "${title}" was not created: ${JSON.stringify(response.body).slice(0, 200)}`);

        return id;
    }

    /** The id of any CRM contact, so a deal has a counterparty to attach to. */
    async firstContactId(): Promise<number> {
        const response = await this.callAjax('erp_deals_search_people', {});
        void response;

        return this.page.evaluate(async () => {
            const g = (window as unknown as { erpDealsGlobal: { ajaxurl: string; nonce: string } }).erpDealsGlobal;
            const url = `${g.ajaxurl}?action=erp_deals_search_people&_wpnonce=${g.nonce}&contact=true&s=a`;
            const json = await fetch(url, { credentials: 'same-origin' }).then((r) => r.json());
            return Number(json?.data?.contacts?.[0]?.id ?? 0);
        });
    }

    async gotoDeal(id: number): Promise<void> {
        await this.gotoUrl(adminPath('erp-crm', { section: 'deals', 'sub-section': 'all-deals', action: 'view-deal', id }));
        await this.page.locator(singleDealSelectors.root).waitFor({ state: 'visible', timeout: 20_000 }).catch(() => undefined);
        await this.page.waitForTimeout(2000);
    }

    // ---- header -----------------------------------------------------------

    async dealTitle(): Promise<string> {
        return ((await this.page.locator(singleDealSelectors.title).textContent()) ?? '').trim();
    }

    async dealValue(): Promise<string> {
        return ((await this.page.locator(singleDealSelectors.value).textContent()) ?? '').trim();
    }

    /** Labels on the Won / Lost / Trash / Reopen / Restore / Delete row. */
    async statusButtonLabels(): Promise<string[]> {
        return (await this.page.locator(singleDealSelectors.statusButtons).allTextContents()).map((t) => t.trim()).filter(Boolean);
    }

    async clickStatusButton(label: string): Promise<void> {
        await this.page.locator(singleDealSelectors.statusButtons).filter({ hasText: label }).first().click();
        await this.page.waitForTimeout(2500);
        await this.dismissAlert();
        await this.page.waitForTimeout(1500);
    }

    async hasWonBadge(): Promise<boolean> {
        return this.page.locator(singleDealSelectors.badgeWon).isVisible().catch(() => false);
    }

    async hasLostBadge(): Promise<boolean> {
        return this.page.locator(singleDealSelectors.badgeLost).isVisible().catch(() => false);
    }

    // ---- stage bar --------------------------------------------------------

    /**
     * Moves the deal by clicking a stage bullet.
     *
     * Addressed by INDEX, not by name — on this page the bullet's `tooltip-title`
     * is a composed history string, not the stage title.
     */
    async moveToStageIndex(index: number): Promise<void> {
        await this.page.locator(singleDealSelectors.stageBar).nth(index).click();
        await this.page.waitForTimeout(2500);
        await this.dismissAlert();
        await this.page.waitForTimeout(1500);
    }

    /** Zero-based index of the stage the bar marks active, or -1. */
    async activeStageIndex(): Promise<number> {
        return this.page.locator(singleDealSelectors.stageBar).evaluateAll((nodes) =>
            nodes.findIndex((n) => n.classList.contains('active'))
        );
    }

    // ---- boxes and tabs ---------------------------------------------------

    async boxTitles(): Promise<string[]> {
        return (await this.page.locator(singleDealSelectors.sidebarBoxes).allTextContents()).map((t) => t.trim()).filter(Boolean);
    }

    async composerTabLabels(): Promise<string[]> {
        return (await this.page.locator(singleDealSelectors.feedTabs).allTextContents()).map((t) => t.trim()).filter(Boolean);
    }

    async timelineTabLabels(): Promise<string[]> {
        return (await this.page.locator(singleDealSelectors.timelineTabs).allTextContents())
            .map((t) => t.replace(/\(\d+\)/, '').trim())
            .filter(Boolean);
    }

    async openTimelineTab(label: string): Promise<void> {
        await this.page.locator(singleDealSelectors.timelineTabs).filter({ hasText: label }).first().click();
        await this.page.waitForTimeout(1200);
    }

    async timelineText(): Promise<string> {
        return ((await this.page.locator(singleDealSelectors.timeline).textContent()) ?? '').replace(/\s+/g, ' ').trim();
    }

    // ---- notes ------------------------------------------------------------

    get noteEditor(): Locator {
        return this.page.locator(singleDealSelectors.noteEditor);
    }

    /**
     * Writes a note through the Take notes composer.
     *
     * **Trix again** — the same contenteditable custom element the CRM contact
     * feed uses. `fill()` does nothing; the text is typed with real key events.
     */
    async addNote(text: string): Promise<void> {
        await this.page.locator(singleDealSelectors.feedTabs).filter({ hasText: 'Take notes' }).first().click();
        await this.page.waitForTimeout(900);

        await this.noteEditor.click();
        await this.page.keyboard.type(text, { delay: 6 });
        await this.page.waitForTimeout(600);

        await this.page.locator(singleDealSelectors.noteSave).first().click();
        await this.page.waitForTimeout(2500);
        await this.dismissAlert();
    }

    // ---- competitors ------------------------------------------------------

    /** Fills and submits the Add New Competitor modal. */
    async addCompetitor(competitor: Competitor): Promise<void> {
        await this.page.locator(singleDealSelectors.competitorAdd).first().click();
        await this.page.locator(`${singleDealSelectors.competitorModal} input[type="text"]`).first().waitFor({ state: 'visible' });
        await this.page.waitForTimeout(500);

        const modal = this.page.locator(singleDealSelectors.competitorModal);
        const field = (label: string) => modal.locator('label').filter({ hasText: label }).locator('input').first();

        await field('Name').fill(competitor.name);
        if (competitor.website) await field('Website').fill(competitor.website);
        if (competitor.strengths) await field('Strengths').fill(competitor.strengths);
        if (competitor.weaknesses) await field('Weaknesses').fill(competitor.weaknesses);

        await modal.locator('button[type="submit"]').first().click();
        await this.page.waitForTimeout(2500);
        await this.dismissAlert();
    }

    async competitorNames(): Promise<string[]> {
        return (await this.page.locator(`${singleDealSelectors.competitorRows} .column-competitor-name`).allTextContents())
            .map((t) => t.trim())
            .filter(Boolean);
    }

    async competitorBoxText(): Promise<string> {
        return ((await this.page.locator(singleDealSelectors.competitorBox).textContent()) ?? '').replace(/\s+/g, ' ').trim();
    }

    // ---- whole-page reads -------------------------------------------------

    /** True when the page rendered the deal at all (a refused role gets nothing). */
    async isDealVisible(): Promise<boolean> {
        return this.page.locator(singleDealSelectors.root).isVisible().catch(() => false);
    }

    /** The board, for checking a trashed deal has left it. */
    async boardShowsDeal(title: string): Promise<boolean> {
        await this.goto('all-deals');
        return this.hasDealOnBoard(title);
    }

    /** The module's own modal shell, reused here — see `dealSelectors`. */
    get moduleModal(): Locator {
        return this.page.locator(dealSelectors.modal).first();
    }
}
