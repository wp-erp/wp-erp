import { Page, Locator } from '@playwright/test';
import { BasePage } from '@pages/basePage';

/**
 * HR → Attendance (`section=attendance`), an erp-pro module.
 *
 * A hash-routed SPA — `#/`, `#/shifts`, `#/assign-shift-bulk`, `#/exim` — served
 * from one admin page, so navigation is a hash change and the list repaints
 * without a document load. Unlike payroll it has a real REST API
 * (`erp/v1/hrm/attendance/*`) with capability checks on every route, which the
 * specs use as an independent oracle.
 */
export const attendanceSelectors = {
    addShift: 'a:has-text("Add New Shift")',
    addAttendance: 'a:has-text("Add New")',

    modal: '.modal-body, .erp-modal-body, [class*="modal"]',
    shiftName: 'input.input[placeholder="Type here"]',
    startTime: '#atts-shift-start-time',
    endTime: '#atts-shift-end-time',
    save: 'button:has-text("Save")',
    closeModal: 'button:has-text("Close modal panel")',

    table: '#wpbody-content table',
    row: '#wpbody-content table tbody tr',
    listCount: '.displaying-num',
} as const;

/** Columns each SPA screen renders, captured live. */
export const shiftColumns = ['Shift Name', 'Start Time', 'End Time', 'Duration', 'Holidays', 'Actions'] as const;
export const attendanceColumns = ['Date', 'Attended', 'Absent', 'Presence', 'Actions'] as const;
export const assignShiftColumns = ['Employee Name', 'Designation', 'Department', 'Employment Type', 'Shift', 'Status'] as const;

export const attendanceScreens = {
    attendance: '',
    shifts: '#/shifts',
    assignShiftBulk: '#/assign-shift-bulk',
    exim: '#/exim',
} as const;

export type AttendanceScreen = keyof typeof attendanceScreens;

export interface ShiftInput {
    name: string;
    /** As the timepicker renders them, e.g. "09:00 am". */
    startTime: string;
    endTime: string;
}

export class AttendancePage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    async goto(screen: AttendanceScreen = 'attendance'): Promise<void> {
        await this.page.goto(`/wp-admin/admin.php?page=erp-hr&section=attendance${attendanceScreens[screen]}`);
        await this.page.waitForTimeout(2500);
    }

    async columnHeaders(): Promise<string[]> {
        return [
            ...new Set(
                (await this.page.locator(`${attendanceSelectors.table} th`).allTextContents())
                    .map((t) => t.replace(/\s+/g, ' ').trim())
                    .filter(Boolean)
            ),
        ];
    }

    async rowCount(): Promise<number> {
        return this.page.locator(attendanceSelectors.row).count();
    }

    /** Flattened text of every list row. */
    async rowTexts(): Promise<string[]> {
        return (await this.page.locator(attendanceSelectors.row).allTextContents()).map((t) => t.replace(/\s+/g, ' ').trim()).filter(Boolean);
    }

    async hasRowFor(text: string): Promise<boolean> {
        return (await this.rowTexts()).some((row) => row.includes(text));
    }

    row(text: string): Locator {
        return this.page.locator(attendanceSelectors.row).filter({ hasText: text }).first();
    }

    // ---- shifts -----------------------------------------------------------

    /**
     * Opens the shift modal.
     *
     * Navigating to `#/shifts` when already there is a no-op — the SPA does not
     * remount and a second `Add New Shift` click can find nothing to press, so
     * a full document load is forced. If a modal is already open (a refused
     * save leaves it up) it is reused rather than fought with.
     */
    async openShiftForm(): Promise<void> {
        if (await this.isShiftFormOpen()) return;

        await this.page.goto('/wp-admin/admin.php?page=erp-hr&section=attendance#/shifts', { waitUntil: 'domcontentloaded' });
        await this.page.reload({ waitUntil: 'domcontentloaded' });
        await this.page.waitForTimeout(2500);

        const add = this.page.locator(attendanceSelectors.addShift).first();
        await add.waitFor({ state: 'visible', timeout: 15_000 });
        await add.click();
        await this.page.locator(attendanceSelectors.shiftName).waitFor({ state: 'visible', timeout: 10_000 });
    }

    /**
     * Fills and saves the shift modal.
     *
     * The time fields are pickers that open an overlay on focus; it covers the
     * Save button, so it is dismissed with Escape before submitting.
     */
    async createShift(input: ShiftInput): Promise<string> {
        await this.openShiftForm();
        await this.page.locator(attendanceSelectors.shiftName).fill(input.name);
        await this.page.locator(attendanceSelectors.startTime).fill(input.startTime);
        await this.page.keyboard.press('Escape');
        await this.page.locator(attendanceSelectors.endTime).fill(input.endTime);
        await this.page.keyboard.press('Escape');

        await this.page.locator(attendanceSelectors.save).first().click();
        await this.page.waitForTimeout(2500);

        return this.noticeText();
    }

    /** Whatever error the SPA surfaced, from its toast or the modal itself. */
    async errorText(): Promise<string> {
        const candidates = await this.page.locator('.notice-error, .erp-toast, .toast, [class*="error"]').allTextContents();
        return candidates.map((t) => t.replace(/\s+/g, ' ').trim()).filter(Boolean).join(' | ');
    }

    /** True while the shift modal is still on screen. */
    async isShiftFormOpen(): Promise<boolean> {
        return this.page.locator(attendanceSelectors.shiftName).isVisible().catch(() => false);
    }

    async closeShiftForm(): Promise<void> {
        const close = this.page.locator(attendanceSelectors.closeModal).first();
        if (await close.isVisible().catch(() => false)) await close.click();
    }
}
