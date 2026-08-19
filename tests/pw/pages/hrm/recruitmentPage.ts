import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * HR → Recruitment (`section=recruitment`), an erp-pro module.
 *
 * Job openings are a WordPress custom post type (`erp_hr_recruitment`), not an
 * ERP table, so the DB oracle is `wp_posts`. Creating one is the first step of a
 * wizard: the "Next" button is disabled and only enabled by a **keyup** handler
 * on the title (`recruitment_entry.js:687`) — `fill()` sets the value without
 * firing keyup, so the button stays disabled and the form can never be sent.
 *
 * Four screens are AI features backed by a Gemini API key. The suite LOADS them
 * and asserts they render; it never triggers a generation, which would be an
 * outbound call to a paid third-party service. Only wordpress.org is blocked by
 * the test mu-plugin, so such a call would really leave the machine.
 */
export const recruitmentSelectors = {
    openingTitle: '#opening_title',
    openingDescriptionFrame: '#opening_description_ifr',
    createOpening: '#create_opening',

    aiApiKey: '#erp_rec_gemini_api_key',
    aiModelSelector: '#ai-model-selector',

    table: '#wpbody-content table',
    row: '#wpbody-content table tbody tr',
} as const;

export const recruitmentScreens = {
    jobOpening: 'job-opening',
    addOpening: 'add-opening',
    candidates: 'jobseeker_list',
    addCandidate: 'add_candidate',
    stages: 'stages',
    calendar: 'todo-calendar',
    reports: 'reports',
    aiSettings: 'erp-rec-ai-settings',
} as const;

export type RecruitmentScreen = keyof typeof recruitmentScreens;

/** Columns each list renders, captured live. */
export const jobOpeningColumns = ['Job Title', 'Applicants', 'Status', 'Created On', 'Expire Date', 'Publish Date', 'Action'] as const;
export const candidateColumns = ['Name', 'Stage', 'Rating', 'Date', 'Action'] as const;
export const stageColumns = ['Stage Name', 'Jobs Using', 'Candidates', 'Actions'] as const;

/** Stages the module installs by default — read from the screen, not guessed. */
export const defaultStages = ['Screening', 'Phone Interview', 'Face to Face Interview', 'Make an Offer'] as const;

export class RecruitmentPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(screen: RecruitmentScreen = 'jobOpening'): Promise<void> {
        await this.gotoAdmin('erp-hr', { section: 'recruitment', 'sub-section': recruitmentScreens[screen] });
    }

    async columnHeaders(): Promise<string[]> {
        return [
            ...new Set(
                (await this.page.locator(`${recruitmentSelectors.table} th`).allTextContents())
                    .map((t) => t.replace(/\s+/g, ' ').replace(/\s*Sort\s+(ascending|descending)\.?/gi, '').trim())
                    .filter(Boolean)
            ),
        ];
    }

    async rowTexts(): Promise<string[]> {
        return (await this.page.locator(recruitmentSelectors.row).allTextContents()).map((t) => t.replace(/\s+/g, ' ').trim()).filter(Boolean);
    }

    async hasRowFor(text: string): Promise<boolean> {
        return (await this.rowTexts()).some((row) => row.includes(text));
    }

    get nextButton(): Locator {
        return this.page.locator(recruitmentSelectors.createOpening);
    }

    async isNextEnabled(): Promise<boolean> {
        return this.nextButton.isEnabled().catch(() => false);
    }

    /**
     * Types a job title.
     *
     * `pressSequentially`, never `fill` — the wizard's Next button is enabled by
     * a jQuery `keyup` handler which `fill()` does not fire.
     */
    async typeOpeningTitle(title: string): Promise<void> {
        await this.goto('addOpening');
        await this.page.locator(recruitmentSelectors.openingTitle).pressSequentially(title, { delay: 15 });
    }

    /** Creates a job opening and lands on the wizard's next step. */
    async createOpening(title: string, description = 'Written by the automated suite.'): Promise<void> {
        await this.typeOpeningTitle(title);

        const body = this.page.frameLocator(recruitmentSelectors.openingDescriptionFrame).locator('body');
        await body.click();
        await body.fill(description);

        await this.nextButton.scrollIntoViewIfNeeded();
        await this.nextButton.click();
        await this.page.waitForLoadState('domcontentloaded');
        await this.page.waitForTimeout(1500);
    }

    /** True when the wizard has advanced to the named step. */
    async isOnWizardStep(step: string): Promise<boolean> {
        return this.page.url().includes(`step=${step}`);
    }

    /** True when the AI settings screen offers its key field. */
    async hasAiKeyField(): Promise<boolean> {
        return this.page.locator(recruitmentSelectors.aiApiKey).isVisible().catch(() => false);
    }
}
