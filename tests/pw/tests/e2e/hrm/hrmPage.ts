import { expect, type Page } from '@utils/test';
import { toPath } from '@utils/helpers';
import { ApiUtils } from '@utils/apiUtils';
import { endPoints } from '@utils/apiEndPoints';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import type { IdMap, ResponseBody } from '@utils/interfaces';

/**
 * Feature-isolated page object for the WP ERP HRM module.
 *
 * Everything is grounded in the real plugin source:
 *  - Admin pages hang off `admin.php?page=erp-hr`, routed by `&section=` /
 *    `&sub-section=` (modules/hrm/includes/Admin/AdminMenu.php). The legacy short
 *    URLs `&section=employee` / `&section=department` / `&section=designation`
 *    still resolve and are the ones the FREE Codeception acceptance tests drive.
 *  - Form selectors are the real ids from the js-templates views:
 *      new-employee.php  -> #first_name, #last_name, #erp-hr-user-email,
 *                           work[type] (select2 container #select2-worktype-container),
 *                           work[hiring_date], action=erp-hr-employee-new
 *      new-dept.php      -> #dept-title, #dept-desc, #dept-lead, action=erp-hr-new-dept
 *      new-designation.php -> #desig-title, #desig-desc, action=erp-hr-new-desig
 *  - REST seeding uses the controllers under modules/hrm/includes/API/* which
 *    accept a FLAT payload (first_name/last_name/email/type/status/department/
 *    designation/hiring_date for employees; title/description/head for depts;
 *    title/description for designations; name/days for leave policies).
 */
export class HrmPage {
    readonly page: Page;

    constructor( page: Page ) {
        this.page = page;
    }

    // ── URLs (real admin routes) ─────────────────────────────────────────────
    readonly urls = {
        dashboard: toPath( 'wp-admin/admin.php?page=erp-hr&section=dashboard' ),
        employees: toPath( 'wp-admin/admin.php?page=erp-hr&section=people&sub-section=employee' ),
        employeesLegacy: toPath( 'wp-admin/admin.php?page=erp-hr&section=employee' ),
        employeeView: ( id: string | number ): string =>
            toPath( `wp-admin/admin.php?page=erp-hr&section=people&sub-section=employee&action=view&id=${id}` ),
        departments: toPath( 'wp-admin/admin.php?page=erp-hr&section=people&sub-section=department' ),
        departmentsLegacy: toPath( 'wp-admin/admin.php?page=erp-hr&section=department' ),
        designations: toPath( 'wp-admin/admin.php?page=erp-hr&section=people&sub-section=designation' ),
        designationsLegacy: toPath( 'wp-admin/admin.php?page=erp-hr&section=designation' ),
        announcements: toPath( 'wp-admin/admin.php?page=erp-hr&section=people&sub-section=announcement' ),
        leaveRequests: toPath( 'wp-admin/admin.php?page=erp-hr&section=leave&sub-section=leave-requests' ),
        leavePolicies: toPath( 'wp-admin/admin.php?page=erp-hr&section=leave&sub-section=policies' ),
        leaveEntitlements: toPath( 'wp-admin/admin.php?page=erp-hr&section=leave&sub-section=leave-entitlements' ),
        holidays: toPath( 'wp-admin/admin.php?page=erp-hr&section=leave&sub-section=holidays' ),
        leaveCalendar: toPath( 'wp-admin/admin.php?page=erp-hr&section=leave&sub-section=leave-calendar' ),
        report: toPath( 'wp-admin/admin.php?page=erp-hr&section=report' ),
        reportHeadcount: toPath( 'wp-admin/admin.php?page=erp-hr&section=report&type=headcount' ),
    } as const;

    // ── Selectors grouped by area (real ids from the views) ──────────────────
    readonly admin = {
        // employee list + add-employee modal (views/employee.php + js-templates/new-employee.php)
        employee: {
            addNew: '#erp-employee-new',
            firstName: '#first_name',
            middleName: '#middle_name',
            lastName: '#last_name',
            email: '#erp-hr-user-email',
            employeeId: 'input[name="personal[employee_id]"]',
            typeSelect2: '#select2-worktype-container',
            type: 'select[name="work[type]"]',
            status: 'select[name="work[status]"]',
            hiringDate: 'input[name="work[hiring_date]"]',
            department: 'select[name="work[department]"]',
            designation: 'select[name="work[designation]"]',
            actionHidden: 'input[name="action"][value="erp-hr-employee-new"]',
            submit: 'button:has-text("Create Employee"), input[value="Create Employee"]',
            listRow: '.wp-list-table tbody tr',
        },
        // department modal (views/departments.php + js-templates/new-dept.php)
        department: {
            addNew: '#erp-new-dept',
            title: '#dept-title',
            desc: '#dept-desc',
            lead: '#dept-lead',
            parent: '#dept-parent',
            actionHidden: 'input[name="action"][value="erp-hr-new-dept"]',
            submit: 'button:has-text("Create Department"), input[value="Create Department"]',
            listRow: '.wp-list-table tbody tr',
        },
        // designation modal (views/designation.php + js-templates/new-designation.php)
        designation: {
            addNew: '#erp-new-designation',
            title: '#desig-title',
            desc: '#desig-desc',
            actionHidden: 'input[name="action"][value="erp-hr-new-desig"]',
            submit: 'button:has-text("Create Designation"), input[value="Create Designation"]',
            listRow: '.wp-list-table tbody tr',
        },
    } as const;

    // ── Navigation helpers ───────────────────────────────────────────────────
    async goToDashboard(): Promise<void> {
        await this.page.goto( this.urls.dashboard, { waitUntil: 'domcontentloaded' } );
    }

    async goToEmployees(): Promise<void> {
        await this.page.goto( this.urls.employees, { waitUntil: 'domcontentloaded' } );
    }

    async goToDepartments(): Promise<void> {
        await this.page.goto( this.urls.departments, { waitUntil: 'domcontentloaded' } );
    }

    async goToDesignations(): Promise<void> {
        await this.page.goto( this.urls.designations, { waitUntil: 'domcontentloaded' } );
    }

    async goToHolidays(): Promise<void> {
        await this.page.goto( this.urls.holidays, { waitUntil: 'domcontentloaded' } );
    }

    async goToHeadcountReport(): Promise<void> {
        await this.page.goto( this.urls.reportHeadcount, { waitUntil: 'domcontentloaded' } );
    }

    /** True if the rendered page shows WP's fatal "critical error" splash. */
    async hasCriticalError(): Promise<boolean> {
        const body = ( await this.page.locator( 'body' ).textContent() ) ?? '';
        return /There has been a critical error/i.test( body );
    }

    // ── High-level UI flows (real selectors / ajax action names) ─────────────

    /**
     * Create a department via the admin modal. Returns the title used so the
     * caller can assert it appears in the list.
     */
    async createDepartment( dept: { title: string; description?: string } ): Promise<string> {
        await this.goToDepartments();
        await this.page.click( this.admin.department.addNew );
        await this.page.fill( this.admin.department.title, dept.title );
        if ( dept.description ) {
            await this.page.fill( this.admin.department.desc, dept.description );
        }
        // Wait for the create's admin-ajax response so the row is committed before
        // a fresh list re-query (avoids a count=0 race on the reloaded list).
        await Promise.all( [
            this.page.waitForResponse(
                r => r.url().includes( 'admin-ajax.php' ) && r.request().method() === 'POST' && ( r.request().postData() ?? '' ).includes( 'erp-hr-new-dept' ),
                { timeout: 30_000 },
            ),
            this.page.locator( this.admin.department.submit ).first().click(),
        ] );
        return dept.title;
    }

    /** Create a designation via the admin modal. Returns the title used. */
    async createDesignation( desig: { title: string; description?: string } ): Promise<string> {
        await this.goToDesignations();
        await this.page.click( this.admin.designation.addNew );
        await this.page.fill( this.admin.designation.title, desig.title );
        if ( desig.description ) {
            await this.page.fill( this.admin.designation.desc, desig.description );
        }
        await Promise.all( [
            this.page.waitForResponse(
                r => r.url().includes( 'admin-ajax.php' ) && r.request().method() === 'POST' && ( r.request().postData() ?? '' ).includes( 'erp-hr-new-desig' ),
                { timeout: 30_000 },
            ),
            this.page.locator( this.admin.designation.submit ).first().click(),
        ] );
        return desig.title;
    }

    /**
     * Open the add-employee modal and fill the always-present required fields
     * (first/last name, email, hiring date). Type/status/department/designation
     * are select2-driven and seeded via REST for depth, so the UI flow here is a
     * resilient smoke of the modal rather than a brittle deep submit.
     */
    async openAddEmployeeModal(): Promise<void> {
        await this.goToEmployees();
        await this.page.click( this.admin.employee.addNew );
        await expect( this.page.locator( this.admin.employee.firstName ) ).toBeVisible();
    }

    async fillEmployeeBasics( emp: {
        first_name: string;
        last_name: string;
        email: string;
        hiring_date?: string;
    } ): Promise<void> {
        await this.page.fill( this.admin.employee.firstName, emp.first_name );
        await this.page.fill( this.admin.employee.lastName, emp.last_name );
        await this.page.fill( this.admin.employee.email, emp.email );
        if ( emp.hiring_date ) {
            await this.page.fill( this.admin.employee.hiringDate, emp.hiring_date );
        }
    }

    // ── Seeding via REST (resilient) ─────────────────────────────────────────

    /**
     * Ensure the HRM module is usable, then create the baseline fixtures through
     * REST and return an IdMap whose keys match the .env placeholders
     * (DEPARTMENT_ID, DESIGNATION_ID, EMPLOYEE_ID, LEAVE_POLICY_ID, HOLIDAY_ID).
     *
     * Resilient by design: each step is wrapped so a single failure (e.g. a
     * disabled sub-feature) still returns whatever IDs were obtained.
     */
    static async seed( api: ApiUtils ): Promise<IdMap> {
        const ids: IdMap = {};

        const idOf = ( body: ResponseBody ): string => {
            const raw = body?.id ?? body?.user_id ?? '';
            return raw === '' ? '' : String( raw );
        };

        // Department
        try {
            const dept = data.hrm.department();
            const [ resp, body ] = await api.post( endPoints.departments, {
                data: { title: dept.title, description: dept.description },
            } );
            if ( resp.ok() ) {
                const id = idOf( body );
                if ( id ) ids.DEPARTMENT_ID = id;
            }
        } catch {
            /* keep going — return partial IdMap */
        }

        // Designation
        try {
            const desig = data.hrm.designation();
            const [ resp, body ] = await api.post( endPoints.designations, {
                data: { title: desig.title, description: desig.description },
            } );
            if ( resp.ok() ) {
                const id = idOf( body );
                if ( id ) ids.DESIGNATION_ID = id;
            }
        } catch {
            /* ignore */
        }

        // Employee (flat payload, links to seeded dept/designation when present)
        try {
            const emp = data.hrm.employee();
            const payload: Record<string, unknown> = {
                first_name: emp.first_name,
                last_name: emp.last_name,
                email: emp.email,
                type: 'permanent',
                status: 'active',
                hiring_date: emp.hiring_date,
            };
            if ( ids.DEPARTMENT_ID ) payload.department = Number( ids.DEPARTMENT_ID );
            if ( ids.DESIGNATION_ID ) payload.designation = Number( ids.DESIGNATION_ID );

            const [ resp, body ] = await api.post( endPoints.employees, { data: payload } );
            if ( resp.ok() ) {
                const id = idOf( body );
                if ( id ) {
                    ids.EMPLOYEE_ID = id;
                    // The payroll lifecycle needs an ACTIVE MONTHLY employee available
                    // on EVERY shard (each shard has its own DB). The REST payload has
                    // no pay-type field, so mark this seeded employee monthly directly.
                    // Nothing deletes this seeded employee, so it stays available to
                    // get_available_employees('monthly') regardless of shard split.
                    try {
                        await dbUtils.dbQuery(
                            `UPDATE wp_erp_hr_employees SET pay_type = 'monthly', status = 'active' WHERE user_id = ?`,
                            [ Number( id ) ],
                        );
                    } catch {
                        /* non-fatal: the payroll spec will surface a missing monthly employee */
                    }
                }
            }
        } catch {
            /* ignore */
        }

        // Leave policy (name + days). Optional — depends on a leave type existing.
        try {
            const policy = data.hrm.leavePolicy();
            const [ resp, body ] = await api.post(
                endPoints.leavePolicies,
                { data: { name: policy.name, days: policy.days } },
                false,
            );
            if ( resp.ok() ) {
                const id = idOf( body );
                if ( id ) ids.LEAVE_POLICY_ID = id;
            }
        } catch {
            /* ignore */
        }

        // Holiday (title + start/end). Optional.
        try {
            const holiday = data.hrm.holiday();
            const [ resp, body ] = await api.post(
                endPoints.holidays,
                { data: { title: holiday.title, start: holiday.start, end: holiday.end } },
                false,
            );
            if ( resp.ok() ) {
                const id = idOf( body );
                if ( id ) ids.HOLIDAY_ID = id;
            }
        } catch {
            /* ignore */
        }

        return ids;
    }
}
