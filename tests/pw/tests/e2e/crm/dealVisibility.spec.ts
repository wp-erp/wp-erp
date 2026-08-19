import { test, expect } from '@utils/test';
import { SingleDealPage } from '@pages/crm/singleDealPage';
import { ADMIN_STATE } from '@utils/authStates';
import { withRole } from '@utils/roles';
import { uniqueId } from '@utils/helpers';
import { cleanupDeals } from '@utils/cleanup';
import { query, prefix, closeDb } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

test.use({ storageState: ADMIN_STATE });

/**
 * Agent-vs-agent access control on Deals.
 *
 * Deals DOES have a permission model, and it is worth stating before the cases
 * because it is not obvious from the AJAX layer: `Deal::scopeReadable()`
 * (`Models/Deal.php:35`) restricts anyone who is neither administrator nor CRM
 * manager to deals they OWN or are a listed agent on. Every path that loads a
 * deal through `get_deal()` inherits it — the board, the single-deal read, the
 * delete, the note.
 *
 * `Deals::save_deal()` is the one path that does not: it goes straight to
 * `DealModel::firstOrNew(['id' => …])` with no ownership test, no capability
 * test, and only a nonce between it and any CRM agent.
 *
 * These cases assert the model where it holds — those are the positive controls
 * that make the one failing case meaningful — and pin the hole where it does
 * not. Two agents are required to ask the question at all, which is why
 * `crmAgent2` exists in `authStates.ts`.
 */
test.describe('CRM — Deal access control between agents @pro', () => {
    /** A deal owned by agent one. Agent two has no claim to it. */
    let agentDealId = 0;
    let agentDealTitle = '';

    test.beforeAll(async ({ browser }) => {
        await cleanupDeals('pwerp_vis');

        agentDealTitle = uniqueId('vis');
        agentDealId = await withRole(browser, 'crmAgent', async (p) => {
            const asAgent = new SingleDealPage(p);
            return asAgent.createDealFast(agentDealTitle, 2, 3300);
        });
    });

    test.afterAll(async () => {
        await cleanupDeals('pwerp_vis');
        await closeDb();
    });

    // ---- Tier 2 — the model where it holds --------------------------------

    test("an agent's deal is stamped with its creator and owner", { tag: ['@tier2', '@pro', '@crm-deals', '@authz'] }, async () => {
        const rows = await query<RowDataPacket[]>(
            `SELECT d.created_by, d.owner_id, u.user_login
               FROM ${prefix()}erp_crm_deals d
               JOIN ${prefix()}users u ON u.ID = d.created_by
              WHERE d.id = ?`,
            [agentDealId]
        );

        expect(rows, 'precondition: agent one really did create a deal').toHaveLength(1);
        expect(Number(rows[0]!.owner_id), 'and owns it as well as created it').toBe(Number(rows[0]!.created_by));
    });

    test('a CRM manager can read any deal', { tag: ['@tier2', '@pro', '@crm-deals', '@authz'] }, async ({ browser }) => {
        // The positive control for everything below: a manager is exempt from
        // `scopeReadable()`, so a refusal here would mean the fixture is broken
        // rather than that the product is permissive.
        const response = await withRole(browser, 'crmManager', async (p) => {
            const asManager = new SingleDealPage(p);
            await asManager.goto('all-deals');
            return asManager.callAjaxGet('erp_deals_get_single_deal_data', { deal_id: agentDealId });
        });

        const body = response.body as { success?: boolean };
        expect(body.success, 'a manager reads a deal they do not own').toBe(true);
    });

    test("an agent cannot read another agent's deal", { tag: ['@tier2', '@pro', '@crm-deals', '@authz'] }, async ({ browser }) => {
        const response = await withRole(browser, 'crmAgent2', async (p) => {
            const asOtherAgent = new SingleDealPage(p);
            await asOtherAgent.goto('all-deals');
            return asOtherAgent.callAjaxGet('erp_deals_get_single_deal_data', { deal_id: agentDealId });
        });

        const body = response.body as { success?: boolean; data?: { msg?: string } };

        expect(response.status, 'the request is answered, not fatalled').toBe(200);
        expect(body.success, 'the read is refused').toBe(false);
        expect(body.data?.msg ?? '', 'and the deal is reported as not existing at all').toMatch(/does not exist/i);
    });

    test("an agent cannot note on another agent's deal", { tag: ['@tier2', '@pro', '@crm-deals', '@authz'] }, async ({ browser }) => {
        const response = await withRole(browser, 'crmAgent2', async (p) => {
            const asOtherAgent = new SingleDealPage(p);
            await asOtherAgent.goto('all-deals');
            return asOtherAgent.callAjax('erp_deals_save_deal_note', {
                note: { deal_id: agentDealId, note: 'pwerp_vis note by a foreign agent' },
            });
        });

        const body = response.body as { success?: boolean };
        expect(body.success, 'the note is refused').toBe(false);

        const notes = await query<RowDataPacket[]>(
            `SELECT id FROM ${prefix()}erp_crm_deals_notes WHERE deal_id = ?`,
            [agentDealId]
        );
        expect(notes, 'and nothing is written').toHaveLength(0);
    });

    test("the board shows an agent only the deals they can read", { tag: ['@tier2', '@pro', '@crm-deals', '@authz'] }, async ({ browser }) => {
        const titles = await withRole(browser, 'crmAgent2', async (p) => {
            const asOtherAgent = new SingleDealPage(p);
            await asOtherAgent.goto('all-deals');
            return Object.values(await asOtherAgent.dealsByStage()).flat();
        });

        expect(titles.join(' | '), "another agent's deal is not on this agent's board").not.toContain(agentDealTitle);
    });

    test("an agent cannot trash another agent's deal", { tag: ['@tier2', '@pro', '@crm-deals', '@authz'] }, async ({ browser }) => {
        const title = uniqueId('vis');
        const victimId = await withRole(browser, 'crmAgent', async (p) => {
            const asAgent = new SingleDealPage(p);
            return asAgent.createDealFast(title, 2, 900);
        });

        const before = await query<RowDataPacket[]>(`SELECT deleted_at FROM ${prefix()}erp_crm_deals WHERE id = ?`, [victimId]);
        expect(before[0]!.deleted_at, 'precondition: the deal is not already trashed').toBeNull();

        const response = await withRole(browser, 'crmAgent2', async (p) => {
            const asOtherAgent = new SingleDealPage(p);
            await asOtherAgent.goto('all-deals');
            return asOtherAgent.callAjax('erp_deals_delete_deal', { deal: { id: victimId, action: 'trash' } });
        });

        const body = response.body as { success?: boolean };
        expect(body.success, 'the delete is refused').toBe(false);

        const after = await query<RowDataPacket[]>(`SELECT deleted_at FROM ${prefix()}erp_crm_deals WHERE id = ?`, [victimId]);
        expect(after[0]!.deleted_at, "and the other agent's deal is untouched").toBeNull();
    });

    // ---- Tier 3 — the hole -------------------------------------------------

    test.fail(
        "an agent cannot edit or take over another agent's deal",
        { tag: ['@tier3', '@pro', '@crm-deals', '@authz', '@known-defect'] },
        async ({ browser }) => {
            // KNOWN DEFECT — `Deals::save_deal()` never routes through
            // `scopeReadable()`, so the ownership model every other path enforces
            // is simply absent here. Worse than an edit: `Deal_Ajax::save_deal()`
            // sets `owner_id = get_current_user_id()` for any non-manager, so the
            // write TRANSFERS the deal to the caller.
            const title = uniqueId('vis');
            const victimId = await withRole(browser, 'crmAgent', async (p) => {
                const asAgent = new SingleDealPage(p);
                return asAgent.createDealFast(title, 2, 1234);
            });

            const before = await query<RowDataPacket[]>(
                `SELECT title, owner_id FROM ${prefix()}erp_crm_deals WHERE id = ?`,
                [victimId]
            );
            expect(before, 'precondition: the victim deal exists').toHaveLength(1);

            await withRole(browser, 'crmAgent2', async (p) => {
                const asOtherAgent = new SingleDealPage(p);
                await asOtherAgent.goto('all-deals');
                return asOtherAgent.callAjax('erp_deals_save_deal', {
                    deal: {
                        id: victimId,
                        title: `${title} HIJACKED`,
                        stage_id: 2,
                        contact_id: await asOtherAgent.firstContactId(),
                    },
                });
            });

            const after = await query<RowDataPacket[]>(
                `SELECT title, owner_id FROM ${prefix()}erp_crm_deals WHERE id = ?`,
                [victimId]
            );

            expect(String(after[0]!.title), "the other agent's title is unchanged").toBe(String(before[0]!.title));
            expect(Number(after[0]!.owner_id), 'and its owner is unchanged').toBe(Number(before[0]!.owner_id));
        }
    );

    test.fail(
        'taking over a deal does not unlock the paths that check ownership',
        { tag: ['@tier3', '@pro', '@crm-deals', '@authz', '@known-defect', '@escalation'] },
        async ({ browser }) => {
            // KNOWN DEFECT, and the reason the case above is not merely an edit
            // bug: because the unguarded write reassigns `owner_id`, everything
            // `scopeReadable()` was protecting opens up afterwards. The trash
            // below is REFUSED before the takeover and SUCCEEDS after it — same
            // caller, same deal, same endpoint.
            const title = uniqueId('vis');
            const victimId = await withRole(browser, 'crmAgent', async (p) => {
                const asAgent = new SingleDealPage(p);
                return asAgent.createDealFast(title, 2, 4321);
            });

            const outcome = await withRole(browser, 'crmAgent2', async (p) => {
                const asOtherAgent = new SingleDealPage(p);
                await asOtherAgent.goto('all-deals');

                const trashBefore = await asOtherAgent.callAjax('erp_deals_delete_deal', {
                    deal: { id: victimId, action: 'trash' },
                });

                await asOtherAgent.callAjax('erp_deals_save_deal', {
                    deal: {
                        id: victimId,
                        title: `${title} HIJACKED`,
                        stage_id: 2,
                        contact_id: await asOtherAgent.firstContactId(),
                    },
                });

                const trashAfter = await asOtherAgent.callAjax('erp_deals_delete_deal', {
                    deal: { id: victimId, action: 'trash' },
                });

                return {
                    before: (trashBefore.body as { success?: boolean }).success,
                    after: (trashAfter.body as { success?: boolean }).success,
                };
            });

            expect(outcome.before, 'precondition: the trash is refused before any takeover').toBe(false);

            const rows = await query<RowDataPacket[]>(`SELECT deleted_at FROM ${prefix()}erp_crm_deals WHERE id = ?`, [victimId]);

            expect(outcome.after, 'and it is still refused afterwards').toBe(false);
            expect(rows[0]!.deleted_at, "so the other agent's deal survives").toBeNull();
        }
    );
});
