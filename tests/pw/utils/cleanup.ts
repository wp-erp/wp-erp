/**
 * Removes records the suite itself created, so the demo site stays presentable
 * and repeated runs do not drift (lists paginating further each time, counts
 * creeping up).
 *
 * Deliberately narrow: it only ever matches the suite's own tokens — the
 * `pwerp` prefix from `helpers.uniqueId`, and the `Qa*`/`Diag*` person names the
 * HRM specs generate. Seeded demo data (Northwind Analytics) is never touched.
 */
import { execute, prefix, query } from '@utils/dbUtils';
import type { RowDataPacket } from 'mysql2/promise';

/**
 * The unambiguous marker for a suite-created person is the e-mail domain:
 * `helpers.uniqueEmail()` always mints `@example.test`, while every seeded demo
 * employee is `@northwind-analytics.test`. Matching on names was tried and was
 * wrong — `uniqueName('Hire')` yields "HireZvoxuve", not "QaHire…".
 */
const SUITE_EMAIL_DOMAIN = '%@example.test';

/**
 * Resolves employee display names to user ids.
 *
 * Leave cleanup is SCOPED to the employees a spec actually touched. The suite
 * runs four workers, so leave specs overlap in time; a marker-wide delete from
 * one file's `afterAll` was removing rows another file was mid-approve on —
 * which the product answers with an uncaught fatal (ERP-140), not an error.
 * Scoping by employee removes the race at its source.
 */
async function userIdsFor(employees: string[]): Promise<number[]> {
    if (!employees.length) return [];

    const placeholders = employees.map(() => '?').join(', ');
    const rows = await query<RowDataPacket[]>(`SELECT ID FROM ${prefix()}users WHERE display_name IN (${placeholders})`, employees);

    return rows.map((row) => Number(row.ID));
}

export async function cleanupDepartments(): Promise<number> {
    const result = await execute(`DELETE FROM ${prefix()}erp_hr_depts WHERE title LIKE 'pwerp%' OR title LIKE 'DiagDept%'`);
    return result.affectedRows ?? 0;
}

export async function cleanupDesignations(): Promise<number> {
    const result = await execute(`DELETE FROM ${prefix()}erp_hr_designations WHERE title LIKE 'pwerp%'`);
    return result.affectedRows ?? 0;
}

/** Employees the suite hired, plus their WordPress users. */
export async function cleanupEmployees(): Promise<number> {
    const rows = await query<RowDataPacket[]>(
        `SELECT DISTINCT u.ID FROM ${prefix()}users u
           JOIN ${prefix()}erp_hr_employees e ON e.user_id = u.ID
          WHERE u.user_email LIKE ?`,
        [SUITE_EMAIL_DOMAIN]
    );

    let removed = 0;

    for (const row of rows) {
        const id = Number(row.ID);
        await execute(`DELETE FROM ${prefix()}erp_hr_employees WHERE user_id = ?`, [id]);
        await execute(`DELETE FROM ${prefix()}usermeta WHERE user_id = ?`, [id]);
        await execute(`DELETE FROM ${prefix()}users WHERE ID = ?`, [id]);
        removed++;
    }

    // Users seeded straight onto the example.test domain by earlier probes.
    await execute(`DELETE FROM ${prefix()}erp_hr_employees WHERE user_id NOT IN (SELECT ID FROM ${prefix()}users)`);

    // An ERP person is TWO rows: the module row (here `erp_hr_employees`) and a
    // shared `erp_peoples` row with its type relation. Deleting only the module
    // row orphans the person — and `erp_peoples` is the SAME table CRM contacts,
    // customers and vendors live in, so every run quietly inflated the CRM data
    // set. 58 orphans had accumulated before this was noticed.
    await cleanupPeople(['employee']);

    return removed;
}

/**
 * Shared `erp_peoples` rows the suite created, and their type relations.
 *
 * Matched on the suite's own markers only — the `@example.test` e-mail domain
 * and the `pwerp` name prefix. Seeded demo people are `@northwind-analytics.test`
 * and are never touched.
 */
/** CRM contacts the suite created. */
export async function cleanupCrmContacts(): Promise<number> {
    return cleanupPeople(['contact']);
}

/** CRM companies the suite created. */
export async function cleanupCrmCompanies(): Promise<number> {
    return cleanupPeople(['company']);
}

/**
 * Shared `erp_peoples` rows the suite created, and their type relations.
 *
 * SCOPED BY PEOPLE TYPE, because several spec files touch this one table and the
 * suite runs four workers: an unscoped delete from the companies spec removed the
 * contacts spec's in-flight row and turned an unrelated file red. Passing no type
 * cleans every suite-created person and is for the full `cleanupAll()` only.
 *
 * Matched on the suite's own markers — the `@example.test` e-mail domain and the
 * `pwerp` name prefix. Seeded demo people are `@northwind-analytics.test` and are
 * never touched.
 */
export async function cleanupPeople(types: string[] = []): Promise<number> {
    const people = `${prefix()}erp_peoples`;
    const relations = `${prefix()}erp_people_type_relations`;
    const peopleTypes = `${prefix()}erp_people_types`;
    const mine = `(email LIKE '%@example.test' OR first_name LIKE 'pwerp%' OR last_name LIKE 'pwerp%' OR company LIKE 'Pwerp%')`;

    // Resolve the ids BEFORE deleting anything. The type scope is expressed
    // through the relation table, so deleting relations first would leave the
    // people delete matching nothing and orphan the row — which is exactly what
    // happened: six people survived with no type at all.
    const scope = types.length
        ? ` AND id IN (SELECT r.people_id FROM ${relations} r JOIN ${peopleTypes} t ON t.id = r.people_types_id WHERE t.name IN (${types.map(() => '?').join(', ')}))`
        : '';

    const targets = await query<RowDataPacket[]>(`SELECT id FROM ${people} WHERE ${mine}${scope}`, types);
    const ids = targets.map((row) => Number(row.id));

    // Rows the older, broken ordering already orphaned: suite-marked people with
    // no type relation left. Only ever suite data, so safe to take with us.
    const orphans = await query<RowDataPacket[]>(
        `SELECT id FROM ${people} WHERE ${mine} AND id NOT IN (SELECT people_id FROM ${relations})`
    );
    ids.push(...orphans.map((row) => Number(row.id)));

    if (!ids.length) return 0;

    const list = ids.map(() => '?').join(', ');
    await execute(`DELETE FROM ${relations} WHERE people_id IN (${list})`, ids);

    const result = await execute(`DELETE FROM ${people} WHERE id IN (${list})`, ids);

    return result.affectedRows ?? 0;
}

/** Holidays the suite created. Seeded demo holidays are named from seedData. */
export async function cleanupHolidays(): Promise<number> {
    const result = await execute(
        `DELETE FROM ${prefix()}erp_hr_holiday WHERE title LIKE 'Holiday%' OR title LIKE 'Range%' OR title LIKE 'Leap%' OR title LIKE 'Xss%' OR title LIKE 'Datecheck%'`
    );
    return result.affectedRows ?? 0;
}

/**
 * Leave policies the suite created, matched on the marker written into their
 * description. Seeded demo policies carry their own descriptions from seedData.
 */
export async function cleanupLeavePolicies(): Promise<number> {
    const result = await execute(`DELETE FROM ${prefix()}erp_hr_leave_policies WHERE description = ?`, ['Created by the automated suite.']);
    return result.affectedRows ?? 0;
}

/**
 * Entitlements the suite assigned — and the deduction rows its approvals wrote.
 *
 * `wp_erp_hr_leave_entitlements` is a LEDGER, not one row per entitlement:
 * granting writes `day_in = <policy days>` with our marker in `description`,
 * and approving a request writes a SECOND row with `day_out = <days approved>`
 * and `description = 'Approved'`. "Available" is the difference. Deleting only
 * the marked rows therefore leaves the deductions behind, and the next run sees
 * a balance of 17 where it expects 20.
 *
 * So: find every (user_id, leave_id) pair the suite granted, then clear the
 * whole ledger for those pairs.
 */
export async function cleanupEntitlements(employees: string[] = []): Promise<number> {
    const userIds = await userIdsFor(employees);

    if (employees.length && !userIds.length) return 0;

    const scope = userIds.length ? ` AND user_id IN (${userIds.map(() => '?').join(', ')})` : '';
    const pairs = await query<RowDataPacket[]>(
        `SELECT DISTINCT user_id, leave_id FROM ${prefix()}erp_hr_leave_entitlements WHERE description = ?${scope}`,
        ['Assigned by the automated suite.', ...userIds]
    );

    let removed = 0;

    for (const pair of pairs) {
        const result = await execute(`DELETE FROM ${prefix()}erp_hr_leave_entitlements WHERE user_id = ? AND leave_id = ?`, [
            Number(pair.user_id),
            Number(pair.leave_id),
        ]);
        removed += result.affectedRows ?? 0;
    }

    return removed;
}

/**
 * Leave requests the suite raised, matched on the marker in `reason`.
 *
 * A request is THREE rows, not one: the header in `erp_hr_leave_requests`, one
 * `erp_hr_leave_request_details` row per leave day, and an
 * `erp_hr_leave_approval_status` row. Deleting only the header leaves the detail
 * rows behind — and the product's overlap guard
 * (`erp_hrm_is_leave_recored_exist_between_date`) reads the DETAILS table, so
 * those orphans silently refuse every later request over the same dates with
 * "Existing Leave Record found within selected range!". That looks like a
 * product bug (list empty, header table empty, submit stays disabled) and is
 * not one. Children go first, then the header, then any orphan left by an
 * earlier run of the older cleanup.
 */
export async function cleanupLeaveRequests(employees: string[] = []): Promise<number> {
    const requests = `${prefix()}erp_hr_leave_requests`;
    const details = `${prefix()}erp_hr_leave_request_details`;
    const approvals = `${prefix()}erp_hr_leave_approval_status`;
    const marker = 'Raised by the automated suite.';

    const userIds = await userIdsFor(employees);

    if (employees.length && !userIds.length) return 0;

    const scope = userIds.length ? ` AND user_id IN (${userIds.map(() => '?').join(', ')})` : '';
    const mine = `SELECT id FROM ${requests} WHERE reason = ?${scope}`;
    const args = [marker, ...userIds];

    await execute(`DELETE FROM ${details} WHERE leave_request_id IN (${mine})`, args);
    await execute(`DELETE FROM ${approvals} WHERE leave_request_id IN (${mine})`, args);

    const result = await execute(`DELETE FROM ${requests} WHERE reason = ?${scope}`, args);

    // Orphans left by an older run of this cleanup. Only swept on an unscoped
    // call: it touches rows the caller never created, so running it while
    // another worker is mid-delete could take that worker's rows with it.
    if (!employees.length) {
        await execute(`DELETE FROM ${details} WHERE leave_request_id NOT IN (SELECT id FROM ${requests})`);
        await execute(`DELETE FROM ${approvals} WHERE leave_request_id NOT IN (SELECT id FROM ${requests})`);
    }

    return result.affectedRows ?? 0;
}

/**
 * Pay calendars the suite created. The table keeps its own employee-assignment
 * rows in `erp_hr_payroll_pay_calendar_employee`, which are removed with it.
 */
export async function cleanupPayCalendars(): Promise<number> {
    const calendars = `${prefix()}erp_hr_payroll_pay_calendar`;
    const assignments = `${prefix()}erp_hr_payroll_pay_calendar_employee`;

    await execute(`DELETE FROM ${assignments} WHERE pay_calendar_id IN (SELECT id FROM ${calendars} WHERE pay_calendar_name LIKE 'pwerp%')`);

    const result = await execute(`DELETE FROM ${calendars} WHERE pay_calendar_name LIKE 'pwerp%'`);

    return result.affectedRows ?? 0;
}

/** Attendance shifts the suite created, plus their user assignments. */
export async function cleanupShifts(): Promise<number> {
    const shifts = `${prefix()}erp_attendance_shifts`;
    const assignments = `${prefix()}erp_attendance_shift_user`;

    await execute(`DELETE FROM ${assignments} WHERE shift_id IN (SELECT id FROM ${shifts} WHERE name LIKE 'pwerp%')`);

    const result = await execute(`DELETE FROM ${shifts} WHERE name LIKE 'pwerp%'`);

    return result.affectedRows ?? 0;
}

/** Assets and asset categories the suite created. */
export async function cleanupAssets(): Promise<number> {
    const assets = `${prefix()}erp_hr_assets`;
    const categories = `${prefix()}erp_hr_assets_category`;

    await execute(`DELETE FROM ${assets} WHERE item_group LIKE 'pwerp%'`);
    await execute(`DELETE FROM ${assets} WHERE category_id IN (SELECT id FROM ${categories} WHERE cat_name LIKE 'pwerp%')`);

    const result = await execute(`DELETE FROM ${categories} WHERE cat_name LIKE 'pwerp%'`);

    return result.affectedRows ?? 0;
}

/**
 * Job openings the suite created. They are WordPress posts of type
 * `erp_hr_recruitment`, so postmeta goes with them.
 */
export async function cleanupJobOpenings(): Promise<number> {
    const posts = `${prefix()}posts`;
    const meta = `${prefix()}postmeta`;

    await execute(`DELETE FROM ${meta} WHERE post_id IN (SELECT ID FROM ${posts} WHERE post_type = 'erp_hr_recruitment' AND post_title LIKE 'pwerp%')`);

    const result = await execute(`DELETE FROM ${posts} WHERE post_type = 'erp_hr_recruitment' AND post_title LIKE 'pwerp%'`);

    return result.affectedRows ?? 0;
}

/**
 * Documents the suite uploaded, plus the WordPress attachments behind them.
 * The file on disk is left to WordPress; only rows the suite created go.
 */
export async function cleanupDocuments(): Promise<number> {
    const rel = `${prefix()}erp_employee_dir_file_relationship`;
    const share = `${prefix()}erp_dir_file_share`;
    const posts = `${prefix()}posts`;

    await execute(`DELETE FROM ${share} WHERE dir_file_id IN (SELECT id FROM ${rel} WHERE dir_name LIKE 'pwerp%')`);
    await execute(`DELETE FROM ${posts} WHERE post_type = 'attachment' AND post_title LIKE 'pwerp%'`);

    const result = await execute(`DELETE FROM ${rel} WHERE dir_name LIKE 'pwerp%'`);

    return result.affectedRows ?? 0;
}

/**
 * Trainings the suite created — a WordPress post type, so meta goes too.
 *
 * Also sweeps `auto-draft` rows of this type: WordPress writes one EVERY time
 * `post-new.php` is opened, so each run that visits the editor leaves a stray
 * "Auto Draft" behind. They are empty placeholders, never user data, but they
 * accumulate run after run if nothing clears them.
 */
export async function cleanupTrainings(): Promise<number> {
    const posts = `${prefix()}posts`;
    const meta = `${prefix()}postmeta`;
    const mine = `post_type = 'erp_hr_training' AND (post_title LIKE 'pwerp%' OR post_status = 'auto-draft')`;

    await execute(`DELETE FROM ${meta} WHERE post_id IN (SELECT ID FROM ${posts} WHERE ${mine})`);

    const result = await execute(`DELETE FROM ${posts} WHERE ${mine}`);

    return result.affectedRows ?? 0;
}

/**
 * CRM activities the suite logged.
 *
 * SCOPED BY MARKER, because more than one spec writes to this table and the
 * suite runs four workers: an unscoped `%pwerp%` delete from the tasks spec
 * removed the activities spec's in-flight note and made that test flaky (it
 * failed roughly one run in three). Pass the marker your spec writes.
 */
export async function cleanupCrmActivities(marker = 'pwerp'): Promise<number> {
    const result = await execute(`DELETE FROM ${prefix()}erp_crm_customer_activities WHERE message LIKE ?`, [`%${marker}%`]);

    return result.affectedRows ?? 0;
}

/**
 * Deals created by the suite, plus every child row that hangs off them.
 *
 * SCOPED BY TITLE MARKER for the same reason leave requests, `erp_peoples` and
 * CRM activities are: four workers run at once and the deals tables are shared.
 * The children are removed by deal id rather than by their own marker — a note
 * or a stage-history row carries no title of its own, so an id scope is the only
 * one that exists.
 *
 * Stage history matters more than it looks: the product writes a row per stage
 * on every save, so leaving it behind skews the funnel analytics the dashboard
 * reads on the next run.
 */
export async function cleanupDeals(marker = 'pwerp'): Promise<number> {
    const deals = `${prefix()}erp_crm_deals`;
    const rows = await query<RowDataPacket[]>(`SELECT id FROM ${deals} WHERE title LIKE ?`, [`%${marker}%`]);
    const ids = rows.map((row) => Number(row.id));

    if (ids.length) {
        const placeholders = ids.map(() => '?').join(', ');
        const children = [
            'erp_crm_deals_stage_history',
            'erp_crm_deals_notes',
            'erp_crm_deals_participants',
            'erp_crm_deals_activities',
            'erp_crm_deals_agents',
            'erp_crm_deals_attachments',
            'erp_crm_deals_emails',
            'erp_crm_deals_competitors',
        ];

        for (const table of children) {
            await execute(`DELETE FROM ${prefix()}${table} WHERE deal_id IN (${placeholders})`, ids);
        }
    }

    const result = await execute(`DELETE FROM ${deals} WHERE title LIKE ?`, [`%${marker}%`]);

    return result.affectedRows ?? 0;
}

export async function cleanupAll(): Promise<Record<string, number>> {
    return {
        departments: await cleanupDepartments(),
        designations: await cleanupDesignations(),
        employees: await cleanupEmployees(),
        people: await cleanupPeople(),
        crmActivities: await cleanupCrmActivities(),
        deals: await cleanupDeals(),
        holidays: await cleanupHolidays(),
        leavePolicies: await cleanupLeavePolicies(),
        entitlements: await cleanupEntitlements(),
        leaveRequests: await cleanupLeaveRequests(),
        payCalendars: await cleanupPayCalendars(),
        shifts: await cleanupShifts(),
        assets: await cleanupAssets(),
        jobOpenings: await cleanupJobOpenings(),
        documents: await cleanupDocuments(),
        trainings: await cleanupTrainings(),
    };
}
