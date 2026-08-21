import { Page } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * Reimbursement (@pro) — employees claim money back, and someone pays it.
 *
 * The module registers its REST routes under the ACCOUNTING namespace
 * (`erp/v1/accounting/v1/employee-requests`) even though it ships inside the HR
 * module tree, so the screen lives at `#/transactions/reimbursements` in the
 * accounting SPA while the code lives under `modules/hrm/reimbursement`.
 */
export const REIMBURSEMENT_REST = '/wp-json/erp/v1/accounting/v1/employee-requests';

export interface ReimbursementInput {
    reference: string;
    amount: number;
    trnDate: string;
    particulars?: string;
    ledgerId?: number;
}

export class ReimbursementPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    /** The reimbursement list inside the accounting SPA. */
    async goto(): Promise<void> {
        await this.page.goto('/wp-admin/admin.php?page=erp-accounting#/transactions/reimbursements', {
            waitUntil: 'domcontentloaded',
        });
        await this.page.waitForTimeout(2000);
    }

    /**
     * The REST nonce WordPress hands EVERY logged-in user.
     *
     * Deliberately taken from `wpApiSettings` on `profile.php` rather than from
     * an ERP screen. `wpApiSettings.nonce` is the generic `wp_rest` nonce, which
     * WordPress prints for anyone who can load the admin at all — including a
     * plain employee who has no access to the accounting screens. Reading it
     * from a screen the role is allowed to see is what makes the authorization
     * cases below a fair test rather than a contrived one: nothing here is
     * borrowed from a session more privileged than the caller's own.
     */
    async restNonce(): Promise<string> {
        await this.page.goto('/wp-admin/profile.php', { waitUntil: 'domcontentloaded' });
        await this.page.waitForTimeout(800);

        return this.page.evaluate(() => {
            const w = window as unknown as { wpApiSettings?: { nonce?: string } };
            return w.wpApiSettings?.nonce ?? '';
        });
    }

    /** Raises a reimbursement claim through the REST route the screen uses. */
    async createRequest(input: ReimbursementInput): Promise<{ status: number; body: string }> {
        const nonce = await this.restNonce();

        return this.page.evaluate(
            async ({ nonce, rest, input }) => {
                const r = await fetch(rest, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                    body: JSON.stringify({
                        trn_date: input.trnDate,
                        reference: input.reference,
                        amount_total: input.amount,
                        particulars: input.particulars ?? 'raised by test',
                        attachments: [],
                        line_items: [
                            { ledger_id: input.ledgerId ?? 36, particulars: 'travel', amount: input.amount },
                        ],
                    }),
                });

                return { status: r.status, body: (await r.text()).slice(0, 400) };
            },
            { nonce, rest: REIMBURSEMENT_REST, input }
        );
    }

    /** Reads the whole reimbursement list through REST. */
    async listRequests(): Promise<{ status: number; references: string[] }> {
        const nonce = await this.restNonce();

        return this.page.evaluate(
            async ({ nonce, rest }) => {
                const r = await fetch(rest, { headers: { 'X-WP-Nonce': nonce } });
                const text = await r.text();

                let references: string[] = [];
                try {
                    const parsed = JSON.parse(text) as { reference?: string }[];
                    if (Array.isArray(parsed)) references = parsed.map((row) => row.reference ?? '');
                } catch {
                    references = [];
                }

                return { status: r.status, references };
            },
            { nonce, rest: REIMBURSEMENT_REST }
        );
    }

    /** Rewrites an existing claim, whoever raised it. */
    async updateRequest(id: number, input: ReimbursementInput): Promise<{ status: number; body: string }> {
        const nonce = await this.restNonce();

        return this.page.evaluate(
            async ({ nonce, rest, id, input }) => {
                const r = await fetch(`${rest}/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                    body: JSON.stringify({
                        id,
                        trn_date: input.trnDate,
                        reference: input.reference,
                        amount_total: input.amount,
                        particulars: input.particulars ?? 'changed by test',
                        attachments: [],
                        line_items: [
                            { ledger_id: input.ledgerId ?? 36, particulars: 'travel', amount: input.amount },
                        ],
                    }),
                });

                return { status: r.status, body: (await r.text()).slice(0, 400) };
            },
            { nonce, rest: REIMBURSEMENT_REST, id, input }
        );
    }
}
