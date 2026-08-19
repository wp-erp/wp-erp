import { test, expect } from '@utils/test';
import { ToolsPage, toolsTabs } from '@pages/core/toolsPage';
import { ADMIN_STATE } from '@utils/authStates';
import { withRole } from '@utils/roles';

test.use({ storageState: ADMIN_STATE });

test.describe('ERP Tools', () => {
    let tools: ToolsPage;

    test.beforeEach(async ({ page }) => {
        tools = new ToolsPage(page);
    });

    test('every tools tab loads without a PHP error', { tag: ['@tier1', '@core-tools'] }, async () => {
        for (const tab of toolsTabs) {
            await tools.goto(tab);

            expect(await tools.hasNoPhpFatal(), `no PHP fatal on the "${tab}" tab`).toBe(true);
            expect(await tools.tabRenderedContent(), `the "${tab}" tab renders content`).toBe(true);
        }
    });

    test('the General tab lists the ERP menus it can toggle', { tag: ['@tier1', '@core-tools'] }, async () => {
        await tools.goto('general');

        expect(await tools.menuToggleCount(), 'menu toggles render').toBeGreaterThan(0);
    });

    test('the Misc tab renders the test-email form', { tag: ['@tier1', '@core-tools'] }, async () => {
        await tools.goto('misc');

        await expect(tools.testEmailTo).toBeVisible();
        await expect(tools.testEmailFrom).toBeVisible();
        await expect(tools.testEmailBody).toBeVisible();
        await expect(tools.sendEmailButton).toBeVisible();
    });

    test('the Status tab reports the environment', { tag: ['@tier1', '@core-tools'] }, async () => {
        await tools.goto('status');

        expect(await tools.statusReportsPlatform(), 'the status report names WordPress and PHP').toBe(true);
    });

    test('the Danger Zone requires an explicit confirmation before it will run', { tag: ['@tier3', '@core-tools', '@destructive'] }, async () => {
        // The reset itself is NEVER executed by the suite — it wipes all ERP data.
        await tools.goto('danger-zone');

        expect(await tools.dangerZoneWarns(), 'the Danger Zone warns before acting').toBe(true);
        expect(await tools.dangerZoneControlCount(), 'the destructive action sits behind a control').toBeGreaterThan(0);
    });

    test('Tools is closed to roles without manage_options', { tag: ['@tier3', '@core-tools', '@authz'] }, async ({ browser }) => {
        const denied = await withRole(browser, 'employee', async (page) => {
            const asEmployee = new ToolsPage(page);
            await asEmployee.goto();
            return asEmployee.isAccessDenied();
        });

        expect(denied, 'an employee cannot reach Tools').toBe(true);
    });
});
