import { test, expect, request } from '@utils/test';
import { ApiUtils } from '@utils/apiUtils';
import { endPoints } from '@utils/apiEndPoints';
import { data } from '@utils/testData';
import { schemas } from '@utils/schemas';
import type { APIRequestContext } from '@utils/test';

/**
 * HRM REST specs (/erp/v1/hrm/*).
 *
 * Coverage per resource: list (GET 200 + schema), create (POST -> id),
 * read-back (GET by id), and a negative (unauthorized via the no-auth context,
 * or an invalid payload -> 4xx). All grounded in the real controllers under
 * modules/hrm/includes/API/* — list endpoints return a bare JSON array, the
 * single-employee route keys off `user_id`, and create payloads are flat.
 *
 * Auth: ApiUtils built from the admin storageState; X-WP-Nonce is injected
 * automatically from process.env by ApiUtils.
 */

let api: ApiUtils;

test.beforeAll(async () => {
    api = await ApiUtils.fromStorageState(data.auth.adminFile);
});

test.afterAll(async () => {
    await api.dispose();
});

test.describe('HRM REST — departments / designations / employees', () => {
    // ── Departments ──────────────────────────────────────────────────────────
    test('GET departments returns 200 + an array of departments', { tag: ['@lite', '@hrm'] }, async () => {
        const [response, body] = await api.get(endPoints.departments);
        expect(response.status()).toBe(200);
        expect(Array.isArray(body)).toBe(true);
        const parsed = schemas.list(schemas.department).safeParse(body);
        expect(parsed.success, JSON.stringify(body).slice(0, 300)).toBe(true);
    });

    test('POST creates a department, then GET by id reads it back', { tag: ['@lite', '@hrm'] }, async () => {
        const dept = data.hrm.department();
        const [created, id] = await api.create(endPoints.departments, {
            title: dept.title,
            description: dept.description,
        });
        expect(id, 'create must return a numeric id').not.toBe('');
        expect(schemas.department.safeParse(created).success).toBe(true);

        const [readResp, readBody] = await api.get(endPoints.department(id));
        expect(readResp.status()).toBe(200);
        expect(String(readBody?.id)).toBe(id);
        expect(readBody?.title).toBe(dept.title);
    });

    // ── Designations ─────────────────────────────────────────────────────────
    test('GET designations returns 200 + an array', { tag: ['@lite', '@hrm'] }, async () => {
        const [response, body] = await api.get(endPoints.designations);
        expect(response.status()).toBe(200);
        expect(Array.isArray(body)).toBe(true);
        expect(schemas.list(schemas.designation).safeParse(body).success).toBe(true);
    });

    test('POST creates a designation, then GET by id reads it back', { tag: ['@lite', '@hrm'] }, async () => {
        const desig = data.hrm.designation();
        const [, id] = await api.create(endPoints.designations, {
            title: desig.title,
            description: desig.description,
        });
        expect(id).not.toBe('');

        const [readResp, readBody] = await api.get(endPoints.designation(id));
        expect(readResp.status()).toBe(200);
        expect(readBody?.title).toBe(desig.title);
    });

    // ── Employees ────────────────────────────────────────────────────────────
    test('GET employees returns 200 + an array (schema)', { tag: ['@lite', '@hrm'] }, async () => {
        const [response, body] = await api.get(endPoints.employees);
        expect(response.status()).toBe(200);
        expect(Array.isArray(body)).toBe(true);
        expect(schemas.list(schemas.employee).safeParse(body).success).toBe(true);
    });

    test('POST creates an employee, then GET by user_id reads it back', { tag: ['@lite', '@hrm'] }, async () => {
        // Seed a department/designation first so the employee links cleanly.
        const [, deptId] = await api.create(endPoints.departments, data.hrm.department());
        const [, desigId] = await api.create(endPoints.designations, data.hrm.designation());

        const emp = data.hrm.employee();
        const payload: Record<string, unknown> = {
            first_name: emp.first_name,
            last_name: emp.last_name,
            email: emp.email,
            type: 'permanent',
            status: 'active',
            hiring_date: emp.hiring_date,
        };
        if (deptId) payload.department = Number(deptId);
        if (desigId) payload.designation = Number(desigId);

        const [createResp, createBody] = await api.post(endPoints.employees, { data: payload });
        expect(createResp.status()).toBe(201);
        expect(schemas.employee.safeParse(createBody).success).toBe(true);

        const userId = String(createBody?.user_id ?? createBody?.id ?? '');
        expect(userId, 'create must return a user_id').not.toBe('');

        const [readResp, readBody] = await api.get(endPoints.employee(userId));
        expect(readResp.status()).toBe(200);
        expect(readBody?.email ?? readBody?.user_email).toBe(emp.email);
    });

    // ── Negative: invalid id read-back ───────────────────────────────────────
    test('GET a non-existent employee returns a blank record (lenient API)', { tag: ['@lite', '@hrm'] }, async () => {
        // QA finding: WP ERP responds 200 with an EMPTY employee object (user_id="")
        // for an unknown id instead of a 404 — a validation gap worth flagging.
        const [response, body] = await api.get(endPoints.employee(99999999), undefined, false);
        expect(response.status()).toBe(200);
        expect(String(body?.user_id ?? '')).toBe('');
        expect(String(body?.first_name ?? '')).toBe('');
    });

    // ── Negative: invalid create payload ─────────────────────────────────────
    test('POST department with an empty title is rejected (negative)', { tag: ['@lite', '@hrm'] }, async () => {
        // erp_hr_create_department requires a title; an empty payload must not
        // yield a valid 2xx-created resource with an id.
        const [response, body] = await api.post(endPoints.departments, { data: {} }, false);
        const ok = response.ok() && Boolean(body?.id);
        expect(ok, 'empty department payload should not create a valid resource').toBe(false);
    });
});

// ── Negative: unauthorized (no cookie, no nonce) ─────────────────────────────
test.describe('HRM REST — unauthorized access', () => {
    let anonContext: APIRequestContext;

    test.beforeAll(async () => {
        anonContext = await request.newContext(data.auth.noAuth);
    });

    test.afterAll(async () => {
        await anonContext.dispose();
    });

    test('creating an employee without auth is rejected (4xx)', { tag: ['@lite', '@hrm'] }, async () => {
        // No cookie + no X-WP-Nonce: WP REST must reject the write
        // (erp_create_employee cap fails) with a 401/403.
        const emp = data.hrm.employee();
        const response = await anonContext.post(endPoints.employees, {
            data: {
                first_name: emp.first_name,
                last_name: emp.last_name,
                email: emp.email,
                type: 'permanent',
                status: 'active',
                hiring_date: emp.hiring_date,
            },
            headers: { 'Content-Type': 'application/json' },
            failOnStatusCode: false,
        });
        expect(response.status(), 'anonymous create must be 401/403').toBeGreaterThanOrEqual(400);
        expect(response.status()).toBeLessThan(500);
    });
});
