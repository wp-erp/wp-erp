import { test, expect, request } from '@utils/test';
import type { APIRequestContext, APIResponse, Page } from '@playwright/test';
import { data } from '@utils/testData';
import { dbUtils } from '@utils/dbUtils';
import { BASE_URL, exeCommandWpcli, restUrl, toPath } from '@utils/helpers';

/**
 * Regression specs for the accounting fixes merged for the 1.17.12 cycle.
 *
 *   wp-erp#1682 / erp-pro#1119  product CSV import failed for every row (VALUES %s)
 *   wp-erp#1680 / erp-pro#1107, #1109  Send Mail reported false success, ignored "Attach as PDF"
 *   wp-erp#1679 / erp-pro#1106  Start Tutorial pointer printed as escaped text
 *   wp-erp#1675 / erp-pro#1105  preset dates wiped in accounting forms, deleted vendor address fatal
 *   wp-erp#1674 / erp-pro#1104  accounting PDFs stored at a public, guessable path
 *   wp-erp#1673                 PHP 8 TypeErrors in erp_acct_generate_pdf()
 *   wp-erp#1672 / erp-pro#1097  particulars stored unsanitized and rendered with v-html
 *   wp-erp#1670 / erp-pro#1096  guessable readonly invoice hash, accounting employees routes gated by HR caps
 *   erp-pro#1122 / erp-pro#305  purchase/sales return kept unselected line items (splice in forEach)
 *   erp-pro#1102 / erp-pro#1101 payment gateway never compared the invoice link hash
 *
 * The erp-pdf-invoice add-on is not installed on the test site. Where a fix sits behind
 * `is_plugin_active( 'erp-pdf-invoice/wp-erp-pdf.php' )`, the PHP is driven through
 * `wp eval` with that plugin reported active and `WeDevs\ERP_PDF\PDF_Invoicer` replaced by a
 * recording stub, so the fixed ERP code runs for real while the renderer is faked.
 * Outgoing mail is captured with `pre_wp_mail`; nothing leaves the container.
 */

const P = process.env.DB_PREFIX ?? 'wp';
const RUN = `pw_acct_${Date.now()}`;
const LEDGER = { cash: 7, ownersContribution: 60, generalExpenses: 33 } as const;
const CRITICAL_ERROR = 'There has been a critical error on this website';

// ─────────────────────────────────────────────────────────────────────────────
// Helpers (local on purpose, the shared utils are not edited)
// ─────────────────────────────────────────────────────────────────────────────

/** Run PHP through wp-cli without fighting shell quoting. */
function php(code: string): string {
    const b64 = Buffer.from(code).toString('base64');
    return exeCommandWpcli(`eval 'eval(base64_decode("${b64}"));'`);
}

function phpJson<T>(code: string): T {
    const out = php(code);
    const line = out.split('\n').reverse().find(l => l.trim().startsWith('{') || l.trim().startsWith('['));
    expect(line, `wp eval printed JSON, got: ${out}`).toBeTruthy();
    return JSON.parse(line as string) as T;
}

/**
 * PHP prelude: report erp-pdf-invoice as active and swap its renderer for a stub that records
 * the builder calls and writes a tiny file on render(). Also captures wp_mail() instead of sending.
 */
const PDF_STUB = `
add_filter( 'option_active_plugins', function ( $p ) { $p = (array) $p; $p[] = 'erp-pdf-invoice/wp-erp-pdf.php'; return $p; } );
if ( ! defined( 'WPERP_PDF_VERSION' ) ) { define( 'WPERP_PDF_VERSION', 'pw-stub' ); }
$GLOBALS['pw_pdf'] = [ 'calls' => [], 'rendered' => [] ];
if ( ! class_exists( 'WeDevs\\\\ERP_PDF\\\\PDF_Invoicer' ) ) {
    $pw_stub = new class() {
        public function __construct( ...$a ) {}
        public function __call( $n, $a ) { $GLOBALS['pw_pdf']['calls'][] = [ $n, $a ]; }
        public function render( $f, $m = 'F' ) { $GLOBALS['pw_pdf']['rendered'][] = $f; if ( $f ) { wp_mkdir_p( dirname( $f ) ); file_put_contents( $f, '%PDF-1.4 pw' ); } }
    };
    class_alias( get_class( $pw_stub ), 'WeDevs\\\\ERP_PDF\\\\PDF_Invoicer' );
}
$GLOBALS['pw_mail'] = [];
add_filter( 'pre_wp_mail', function ( $r, $a ) { $GLOBALS['pw_mail'][] = $a; return true; }, 10, 2 );
`;

type RestClient = { ctx: APIRequestContext; call: (method: string, route: string, body?: unknown) => Promise<APIResponse>; dispose: () => Promise<void> };

/** REST client for a saved role session, carrying that user's own wp_rest nonce. */
async function restAs(storageState: string | undefined): Promise<RestClient> {
    const ctx = await request.newContext({ baseURL: BASE_URL, ignoreHTTPSErrors: true, ...(storageState ? { storageState } : {}) });
    let nonce = '';
    if (storageState) {
        const r = await ctx.get(toPath('wp-admin/admin-ajax.php?action=rest-nonce'));
        nonce = (await r.text()).trim();
        expect(nonce, 'rest-nonce for the role session').toMatch(/^[a-f0-9]{10}$/);
    }
    return {
        ctx,
        call: (method, route, body) =>
            ctx.fetch(restUrl(route), {
                method,
                headers: nonce ? { 'X-WP-Nonce': nonce } : {},
                ...(body !== undefined ? { data: body } : {}),
            }),
        dispose: () => ctx.dispose(),
    };
}

async function json<T = Record<string, unknown>>(res: APIResponse): Promise<T> {
    const text = await res.text();
    try {
        return JSON.parse(text) as T;
    } catch {
        throw new Error(`expected JSON (HTTP ${res.status()} ${res.url()}), got: ${text.slice(0, 500)}`);
    }
}

const ACCT = '/erp/v1/accounting/v1';

/** Everything this spec creates, removed in the top-level afterAll. */
const created = { people: [] as number[], products: [] as number[], productCats: [] as number[], vouchers: [] as number[], files: [] as string[] };

async function purgeVouchers(vouchers: number[]): Promise<void> {
    if (!vouchers.length) return;
    const ids = [...new Set(vouchers)];
    // Returns create their own voucher that points back at the source voucher.
    for (const t of [`${P}_erp_acct_purchase_return`, `${P}_erp_acct_sales_return`]) {
        try {
            const rows = await dbUtils.dbQuery<{ v: number }>(`SELECT voucher_no v FROM ${t} WHERE invoice_id IN (${ids.map(() => '?').join(',')})`, ids);
            rows.forEach(r => ids.push(Number(r.v)));
        } catch {
            /* pro table missing */
        }
    }
    const marks = ids.map(() => '?').join(',');
    const cols = await dbUtils.dbQuery<{ t: string; c: string }>(
        `SELECT TABLE_NAME t, COLUMN_NAME c FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE ? AND COLUMN_NAME IN ('trn_no', 'voucher_no')`,
        [`${P}\\_erp\\_acct\\_%`],
    );
    for (const { t, c } of cols) {
        await dbUtils.dbQuery(`DELETE FROM \`${t}\` WHERE \`${c}\` IN (${marks})`, ids);
    }
    await dbUtils.dbQuery(`DELETE FROM ${P}_erp_acct_voucher_no WHERE id IN (${marks})`, ids);
}

async function seedPerson(admin: RestClient, type: 'customer' | 'vendor'): Promise<{ id: number; name: string }> {
    const first = `pw_${type}`;
    const last = `${Date.now()}${Math.floor(Math.random() * 1000)}`;
    const res = await admin.call('POST', `${ACCT}/${type}s`, { first_name: first, last_name: last, email: `${first}_${last}@example.com`, type });
    expect(res.status(), `seed ${type}: ${await res.text()}`).toBeLessThan(300);
    const body = await json<{ id: number }>(res);
    created.people.push(Number(body.id));
    return { id: Number(body.id), name: `${first} ${last}` };
}

async function seedProduct(admin: RestClient, name: string, vendorId: number): Promise<number> {
    const res = await admin.call('POST', `${ACCT}/products`, { name, product_type_id: 1, category_id: 0, vendor: vendorId, cost_price: 10, sale_price: 20, tax_cat_id: 0 });
    expect(res.status(), `seed product: ${await res.text()}`).toBeLessThan(300);
    const body = await json<{ id: number | { id: number } }>(res);
    const id = typeof body.id === 'object' ? Number(body.id.id) : Number(body.id);
    created.products.push(id);
    return id;
}

let payee: { id: number; name: string } | undefined;

async function seedExpense(admin: RestClient, overrides: Record<string, unknown> = {}): Promise<{ voucher: number; body: Record<string, unknown> }> {
    payee ??= await seedPerson(admin, 'vendor');
    const res = await admin.call('POST', `${ACCT}/expenses`, {
        people_id: payee.id,
        people_name: payee.name,
        trn_date: '2026-03-06',
        amount: 30,
        deposit_to: LEDGER.cash,
        trn_by: 1,
        particulars: `${RUN} expense`,
        status: 4,
        type: 'expense',
        bill_details: [{ ledger_id: LEDGER.generalExpenses, particulars: `${RUN} line`, amount: 30 }],
        ...overrides,
    });
    expect(res.status(), `seed expense: ${await res.text()}`).toBeLessThan(300);
    const body = await json<Record<string, unknown>>(res);
    const voucher = Number(body.voucher_no ?? body.id);
    created.vouchers.push(voucher);
    return { voucher, body };
}

let admin: RestClient;

test.beforeAll(async () => {
    admin = await restAs(data.auth.adminFile);
});

test.afterAll(async () => {
    await purgeVouchers(created.vouchers);
    if (created.products.length) {
        await dbUtils.dbQuery(`DELETE FROM ${P}_erp_acct_products WHERE id IN (${created.products.map(() => '?').join(',')})`, created.products);
    }
    if (created.productCats.length) {
        await dbUtils.dbQuery(`DELETE FROM ${P}_erp_acct_product_categories WHERE id IN (${created.productCats.map(() => '?').join(',')})`, created.productCats);
    }
    if (created.people.length) {
        const m = created.people.map(() => '?').join(',');
        await dbUtils.dbQuery(`DELETE FROM ${P}_erp_people_type_relations WHERE people_id IN (${m})`, created.people);
        await dbUtils.dbQuery(`DELETE FROM ${P}_erp_peoplemeta WHERE erp_people_id IN (${m})`, created.people);
        await dbUtils.dbQuery(`DELETE FROM ${P}_erp_peoples WHERE id IN (${m})`, created.people);
    }
    // Stub PDFs this spec wrote, and the per-voucher folders erp_acct_get_pdf_filename() created for our vouchers.
    const touched = [...new Set(created.vouchers.flatMap(v => [v, v + 1]))].join(',');
    php(`
        foreach ( glob( WP_CONTENT_DIR . '/uploads/erp-pdfs/*/voucher_*.pdf' ) as $f ) { if ( 0 === strpos( (string) file_get_contents( $f ), '%PDF-1.4 pw' ) ) { unlink( $f ); } }
        foreach ( array_filter( [ ${touched || '0'} ] ) as $v ) { @rmdir( dirname( erp_acct_get_pdf_filename( $v ) ) ); }
    `);
    await admin?.dispose();
});

// ─────────────────────────────────────────────────────────────────────────────
// wp-erp#1682 / erp-pro#1119 — product CSV import
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Product CSV import inserts every row (wp-erp#1682 / erp-pro#1119)', () => {
    let catId = 0;
    let vendorId = 0;
    const names = [`pw_csv_${Date.now()}_a`, `pw_csv_${Date.now()}_O'Brien`];
    // Kept under the importer's 60-character name limit.
    const injection = `pw_csv_${Math.random().toString(36).slice(2, 6)}'),('pw_pwn',1,1,1,1,1,1,1,'2020-01-01')-- `;

    test.beforeAll(async () => {
        vendorId = (await seedPerson(admin, 'vendor')).id;
        const res = await admin.call('POST', `${ACCT}/product-cats`, { name: `pw_cat_${Date.now()}` });
        expect(res.status()).toBeLessThan(300);
        const body = await json<{ id: number }>(res);
        catId = Number(body.id);
        created.productCats.push(catId);
    });

    test.afterAll(async () => {
        const rows = await dbUtils.dbQuery<{ id: number }>(`SELECT id FROM ${P}_erp_acct_products WHERE name LIKE 'pw\\_csv\\_%' OR name LIKE 'pw\\_pwn%'`);
        created.products.push(...rows.map(r => Number(r.id)));
    });

    /** Mirror ImportModal.vue: validate the CSV (multipart), then post its `data` back as `items`. */
    async function importCsv(rows: string[][], updateExisting = false): Promise<{ validate: Record<string, unknown>; importRes: APIResponse }> {
        const header = ['name', 'product_type_id', 'category_id', 'cost_price', 'sale_price', 'vendor', 'tax_cat_id'];
        const esc = (v: string) => (/[",\n]/.test(v) ? `"${v.replace(/"/g, '""')}"` : v);
        const csv = [header, ...rows].map(r => r.map(esc).join(',')).join('\n') + '\n';
        const multipart: Record<string, string | { name: string; mimeType: string; buffer: Buffer }> = {
            csv_file: { name: 'pw_products.csv', mimeType: 'text/csv', buffer: Buffer.from(csv) },
            type: 'product',
            category_id: String(catId),
            product_type_id: '2',
            tax_cat_id: '',
            vendor: String(vendorId),
            update_existing: updateExisting ? '1' : '0',
        };
        header.forEach((f, i) => (multipart[`fields[${f}]`] = String(i)));

        const vRes = await admin.ctx.fetch(restUrl(`${ACCT}/products/csv/validate`), {
            method: 'POST',
            headers: { 'X-WP-Nonce': await nonceOf(admin) },
            multipart,
        });
        expect(vRes.status(), `validate: ${await vRes.text()}`).toBe(200);
        const validate = await json<Record<string, unknown>>(vRes);
        const importRes = await admin.call('POST', `${ACCT}/products/csv/import`, { items: validate.data, total: validate.total, update: validate.update });
        return { validate, importRes };
    }

    test('a 2-row CSV (one name with a quote) inserts both products', { tag: ['@lite', '@accounting', '@admin'] }, async () => {
        const { validate, importRes } = await importCsv(names.map(n => [n, '2', String(catId), '5', '10', String(vendorId), '']));
        // The validator hands rows back as arrays keyed by column, not as a pre-built SQL string.
        expect(Array.isArray(validate.data), `validate.data is a row list, got ${JSON.stringify(validate.data)}`).toBe(true);
        expect(importRes.status(), await importRes.text()).toBe(201);

        const rows = await dbUtils.dbQuery<{ name: string; category_id: number; cost_price: string; sale_price: string; vendor: number; created_by: string }>(
            `SELECT name, category_id, cost_price, sale_price, vendor, created_by FROM ${P}_erp_acct_products WHERE name IN (?, ?) ORDER BY id`,
            names,
        );
        expect(rows.map(r => r.name), 'both rows inserted, O\'Brien intact').toEqual(names);
        for (const r of rows) {
            expect(Number(r.category_id)).toBe(catId);
            expect(Number(r.vendor)).toBe(vendorId);
            expect(Number(r.cost_price)).toBe(5);
            expect(Number(r.sale_price)).toBe(10);
            expect(r.created_by, 'created_by set server-side to the importing user').toBe('1');
        }
    });

    test('"Update Existing Products" updates the matching row and inserts the new one', { tag: ['@lite', '@accounting', '@admin'] }, async () => {
        const existing = `pw_csv_${Date.now()}_existing`;
        const fresh = `pw_csv_${Date.now()}_fresh`;
        await seedProduct(admin, existing, vendorId);
        const { importRes } = await importCsv(
            [
                [existing, '2', String(catId), '6', '99', String(vendorId), ''],
                [fresh, '2', String(catId), '7', '70', String(vendorId), ''],
            ],
            true,
        );
        expect(importRes.status(), await importRes.text()).toBe(201);
        const rows = await dbUtils.dbQuery<{ name: string; cost_price: string; sale_price: string }>(
            `SELECT name, cost_price, sale_price FROM ${P}_erp_acct_products WHERE name IN (?, ?) ORDER BY id`,
            [existing, fresh],
        );
        expect(rows.map(r => [r.name, Number(r.cost_price), Number(r.sale_price)])).toEqual([
            [existing, 6, 99],
            [fresh, 7, 70],
        ]);
    });

    test('a SQL-shaped product name is stored literally, no extra row', { tag: ['@lite', '@accounting', '@admin'] }, async () => {
        const { importRes } = await importCsv([[injection, '2', String(catId), '1', '1', String(vendorId), '']]);
        expect(importRes.status(), await importRes.text()).toBe(201);
        const rows = await dbUtils.dbQuery<{ name: string }>(`SELECT name FROM ${P}_erp_acct_products WHERE name = ? OR name = 'pw_pwn'`, [injection]);
        expect(rows.map(r => r.name)).toEqual([injection]);
    });
});

const nonces = new WeakMap<RestClient, string>();
async function nonceOf(client: RestClient): Promise<string> {
    const cached = nonces.get(client);
    if (cached) return cached;
    const r = await client.ctx.get(toPath('wp-admin/admin-ajax.php?action=rest-nonce'));
    const n = (await r.text()).trim();
    nonces.set(client, n);
    return n;
}

// ─────────────────────────────────────────────────────────────────────────────
// wp-erp#1680 / erp-pro#1107, #1109 — Send Mail
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Send Mail reports failures and honours Attach as PDF (wp-erp#1680 / erp-pro#1107 #1109)', () => {
    let voucher = 0;

    test.beforeAll(async () => {
        voucher = (await seedExpense(admin)).voucher;
    });

    type MailResult = { status: number; data: { code?: string; status?: number; message?: string }; echo: string; mail: { to: string; msg: string; att: string[] }[] };

    function sendPdf(params: Record<string, unknown>): MailResult {
        const p = JSON.stringify({ trn_data: { voucher_no: voucher }, type: 'expense', subject: `${RUN} subject`, ...params });
        return phpJson<MailResult>(`${PDF_STUB}
            wp_set_current_user( 1 );
            $req = new WP_REST_Request( 'POST', '${ACCT}/transactions/send-pdf/${voucher}' );
            foreach ( json_decode( base64_decode( '${Buffer.from(p).toString('base64')}' ), true ) as $k => $v ) { $req->set_param( $k, $v ); }
            ob_start();
            $res  = rest_do_request( $req );
            $echo = ob_get_clean();
            echo wp_json_encode( [
                'status' => $res->get_status(),
                'data'   => $res->get_data(),
                'echo'   => $echo,
                'mail'   => array_map( function ( $m ) { return [ 'to' => $m['to'], 'msg' => wp_strip_all_tags( $m['message'] ), 'att' => array_values( array_filter( (array) $m['attachments'] ) ) ]; }, $GLOBALS['pw_mail'] ),
            ] );
        `);
    }

    test('blank message falls back to the default body and no PDF is attached when unticked', { tag: ['@lite', '@accounting', '@admin'] }, () => {
        const r = sendPdf({ receiver: ['pw_mail@example.com'], message: '', attachment: false });
        expect(r.status, JSON.stringify(r.data)).toBe(200);
        expect(r.echo, 'nothing echoed into the REST response').toBe('');
        expect(r.mail).toHaveLength(1);
        expect(r.mail[0]?.msg).toContain('Thank you for the transaction');
        expect(r.mail[0]?.att, 'Attach as PDF unticked -> no attachment').toEqual([]);
    });

    test('"false" string and a missing attachment flag both send without a PDF', { tag: ['@lite', '@accounting', '@admin'] }, () => {
        for (const params of [{ attachment: 'false' }, {}]) {
            const r = sendPdf({ receiver: ['pw_mail@example.com'], message: 'pw body', ...params });
            expect(r.status, JSON.stringify(params)).toBe(200);
            expect(r.mail[0]?.att, JSON.stringify(params)).toEqual([]);
        }
    });

    test('ticked (true / legacy "on") attaches voucher_<id>.pdf', { tag: ['@lite', '@accounting', '@admin'] }, () => {
        for (const attachment of [true, 'on']) {
            const r = sendPdf({ receiver: ['pw_mail@example.com'], message: '', attachment });
            expect(r.status, JSON.stringify(r.data)).toBe(200);
            expect(r.mail).toHaveLength(1);
            expect(r.mail[0]?.msg).toContain('Thank you for the transaction');
            expect(r.mail[0]?.att).toHaveLength(1);
            expect(r.mail[0]?.att[0]).toMatch(new RegExp(`/erp-pdfs/[0-9a-f]{32}/voucher_${voucher}\\.pdf$`));
        }
    });

    test('no recipient is reported as a 500 error, not a 200 success', { tag: ['@lite', '@accounting', '@admin'] }, () => {
        const r = sendPdf({ receiver: [], message: 'pw body', attachment: false });
        expect(r.status).toBe(500);
        expect(r.data.code).toBe('rest_mail_not_sent');
        expect(r.mail).toHaveLength(0);
    });

    test('wp_mail() failing is reported as a 500 error', { tag: ['@lite', '@accounting', '@admin'] }, () => {
        const out = phpJson<{ status: number; code: string }>(`${PDF_STUB}
            add_filter( 'pre_wp_mail', function () { return false; }, 20 );
            wp_set_current_user( 1 );
            $req = new WP_REST_Request( 'POST', '${ACCT}/transactions/send-pdf/${voucher}' );
            $req->set_param( 'trn_data', [ 'voucher_no' => ${voucher} ] );
            $req->set_param( 'type', 'expense' );
            $req->set_param( 'receiver', [ 'pw_mail@example.com' ] );
            $req->set_param( 'message', 'pw body' );
            $req->set_param( 'attachment', false );
            $res = rest_do_request( $req );
            echo wp_json_encode( [ 'status' => $res->get_status(), 'code' => $res->get_data()['code'] ?? '' ] );
        `);
        expect(out).toEqual({ status: 500, code: 'rest_mail_not_sent' });
    });

    test('Email::style_inline() on empty content prints nothing', { tag: ['@lite', '@accounting', '@admin'] }, () => {
        const out = phpJson<{ echo: string; ret: string }>(`
            $email = new \\WeDevs\\ERP\\Accounting\\Classes\\SendEmail();
            ob_start();
            $ret  = $email->style_inline( '' );
            $echo = ob_get_clean();
            echo wp_json_encode( [ 'echo' => $echo, 'ret' => (string) $ret ] );
        `);
        expect(out.echo, 'no Emogrifier message echoed').toBe('');
        expect(out.ret).toBe('');
    });

    test('Send Mail modal shows the error instead of "Mail Sent!" when nothing was sent', { tag: ['@lite', '@accounting', '@admin'] }, async ({ browser }) => {
        // erp-pdf-invoice is not installed here, so erp_acct_send_email_with_pdf_attached() returns
        // early and nothing is sent. Before the fix the endpoint answered HTTP 200 {status:304} and
        // the modal said "Mail Sent!".
        const ctx = await browser.newContext({ storageState: data.auth.adminFile });
        const page = await ctx.newPage();
        await page.goto(toPath(`wp-admin/admin.php?page=erp-accounting#/expenses/${voucher}`));
        await page.getByText('More Action').click();
        await page.getByRole('link', { name: 'Send Mail' }).click();
        const modal = page.locator('.wperp-modal').filter({ hasText: 'Send Mail' });
        await expect(modal).toBeVisible();
        await modal.locator('.vue-input-tag-wrapper input').fill('pw_modal@example.com');
        await modal.locator('.vue-input-tag-wrapper input').press('Enter');
        const resp = page.waitForResponse(r => r.url().includes('send-pdf'));
        await modal.getByRole('button', { name: 'Send' }).click();
        expect((await resp).status()).toBe(500);
        await expect(page.locator('.swal2-popup')).toContainText('There was an error sending mail!');
        await expect(page.locator('.swal2-popup')).not.toContainText('Mail Sent!');
        await ctx.close();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// wp-erp#1679 / erp-pro#1106 — Start Tutorial pointer
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Accounting Start Tutorial opens the pointer (wp-erp#1679 / erp-pro#1106)', () => {
    test.use({ storageState: data.auth.adminFile });

    test('Start Tutorial runs the pointer script instead of printing it', { tag: ['@lite', '@accounting', '@admin'] }, async ({ page }) => {
        await page.goto(toPath('wp-admin/admin.php?page=erp-accounting#/'));
        const start = page.locator('#btn-tutorial-start');
        await expect(start).toBeVisible();
        await start.click();
        await page.waitForURL(/tutorial=/);

        await expect(page.locator('.wp-pointer').filter({ hasText: 'Accounting Tutorial' })).toBeVisible({ timeout: 10_000 });
        // The queued JS is a real <script>, not escaped text in the footer.
        await expect(page.locator('body')).not.toContainText('erp_pointers');
        const html = await page.content();
        expect(html.match(/var erp_pointers/g)?.length, 'pointer script queued once').toBe(1);
        expect(html).not.toContain('&lt;script');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// wp-erp#1675 / erp-pro#1105 — preset dates + deleted vendor address
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Accounting forms keep preset dates; deleted vendor address (wp-erp#1675 / erp-pro#1105)', () => {
    test.use({ storageState: data.auth.adminFile });

    const dateInput = (page: Page, label: string) =>
        page.locator('.wperp-form-group').filter({ has: page.locator('label', { hasText: label }) }).locator('input.wperp-form-field');

    for (const [route, labels] of [
        ['expenses/new', ['Expense Date']],
        ['invoices/new', ['Transaction Date', 'Due Date']],
        ['bills/new', ['Bill Date', 'Due Date']],
        ['purchases/new', ['Transaction Date', 'Due Date']],
    ] as const) {
        test(`new form ${route} opens with today's date`, { tag: ['@lite', '@accounting', '@admin'] }, async ({ page }) => {
            const errors: string[] = [];
            page.on('pageerror', e => errors.push(e.message));
            await page.goto(toPath(`wp-admin/admin.php?page=erp-accounting#/${route}`));
            const today = await page.evaluate(() => (window as unknown as { erp_acct_var: { current_date: string } }).erp_acct_var.current_date);
            expect(today).toMatch(/^\d{4}-\d{2}-\d{2}$/);
            for (const label of labels) {
                await expect(dateInput(page, label), label).toHaveValue(today);
            }
            expect(errors, 'no Datepicker TypeError').toEqual([]);
        });
    }

    test('editing an expense loads its stored date', { tag: ['@lite', '@accounting', '@admin'] }, async ({ page }) => {
        const errors: string[] = [];
        page.on('pageerror', e => errors.push(e.message));
        const { voucher } = await seedExpense(admin, { trn_date: '2026-02-14' });
        await page.goto(toPath(`wp-admin/admin.php?page=erp-accounting#/expenses/${voucher}/edit`));
        await expect(dateInput(page, 'Expense Date')).toHaveValue('2026-02-14');
        expect(errors, 'no TypeError reading .length').toEqual([]);
    });

    test('formatting a missing address returns an empty string', { tag: ['@lite', '@accounting', '@admin'] }, () => {
        const out = phpJson<{ ok: boolean; value?: string; err?: string }>(`
            try { $v = erp_acct_format_people_address( erp_acct_get_people_address( 999999999 ) ); echo wp_json_encode( [ 'ok' => true, 'value' => $v ] ); }
            catch ( \\Throwable $e ) { echo wp_json_encode( [ 'ok' => false, 'err' => get_class( $e ) . ': ' . $e->getMessage() ] ); }
        `);
        expect(out).toEqual({ ok: true, value: '' });
    });

    test('a purchase whose vendor was deleted still loads (GET purchases/{id} 200)', { tag: ['@lite', '@accounting', '@admin'] }, async () => {
        const vendor = await seedPerson(admin, 'vendor');
        const productId = await seedProduct(admin, `pw_prod_${Date.now()}_v`, vendor.id);
        const res = await admin.call('POST', `${ACCT}/purchases`, {
            vendor_id: vendor.id, vendor_name: vendor.name, trn_date: '2026-03-01', due_date: '2026-03-31', type: 'purchase', status: 2, amount: 10, due: 10, trn_by: 1,
            particulars: `${RUN} purchase`, line_items: [{ product_id: productId, qty: 1, price: 10, unit_price: 10, tax: 0, tax_amount: 0, discount: 0, tax_cat_id: 0, item_total: 10 }],
        });
        expect(res.status(), await res.text()).toBeLessThan(300);
        const voucher = Number((await json<{ voucher_no: number }>(res)).voucher_no);
        created.vouchers.push(voucher);

        await dbUtils.dbQuery(`DELETE FROM ${P}_erp_peoples WHERE id = ?`, [vendor.id]);
        const get = await admin.call('GET', `${ACCT}/purchases/${voucher}`);
        expect(get.status(), (await get.text()).slice(0, 400)).toBe(200);
        expect(Number((await json<{ voucher_no: number }>(get)).voucher_no)).toBe(voucher);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// wp-erp#1674 / erp-pro#1104 — PDFs behind an authenticated endpoint
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Accounting PDFs are served through an authenticated endpoint (wp-erp#1674 / erp-pro#1104)', () => {
    let voucher = 0;
    const downloadUrl = (v: number) => toPath(`wp-admin/admin-post.php?action=erp_acct_download_pdf&voucher_no=${v}`);

    test.beforeAll(async () => {
        voucher = (await seedExpense(admin)).voucher;
    });

    test('PDF files live under an unguessable per-voucher folder that the web server refuses', { tag: ['@lite', '@accounting', '@admin'] }, async () => {
        const out = phpJson<{ file: string; other: string; url: string; htaccess: boolean; index: boolean }>(`
            $file = erp_acct_get_pdf_filename( ${voucher} );
            file_put_contents( $file, '%PDF-1.4 pw' );
            $dir = WP_CONTENT_DIR . '/uploads/erp-pdfs/';
            echo wp_json_encode( [ 'file' => $file, 'other' => erp_acct_get_pdf_filename( ${voucher} + 1 ), 'url' => erp_acct_pdf_abs_path_to_url( ${voucher} ), 'htaccess' => file_exists( $dir . '.htaccess' ), 'index' => file_exists( $dir . 'index.php' ) ] );
        `);
        const m = out.file.match(new RegExp(`/wp-content/uploads/(erp-pdfs/([0-9a-f]{32})/voucher_${voucher}\\.pdf)$`));
        expect(m, out.file).toBeTruthy();
        expect(out.other, 'folder differs per voucher').not.toContain(m?.[2] as string);
        expect(out.htaccess && out.index, 'erp-pdfs/ is protected with .htaccess + index.php').toBe(true);
        expect(out.url).toBe(downloadUrl(voucher));

        const anon = await request.newContext({ baseURL: BASE_URL });
        for (const path of [m?.[1] as string, `erp-pdfs/voucher_${voucher}.pdf`, 'erp-pdfs/']) {
            const res = await anon.get(toPath(`wp-content/uploads/${path}`));
            expect(res.status(), `public GET ${path}`).not.toBe(200);
            expect(res.headers()['content-type'] ?? '').not.toContain('application/pdf');
        }
        await anon.dispose();
    });

    test('legacy public voucher_*.pdf files are deleted once on admin_init', { tag: ['@lite', '@accounting', '@admin'] }, () => {
        const out = phpJson<{ before: boolean; after: boolean; flag: string }>(`
            $prev = get_option( 'erp_acct_pdf_dir_secured' );
            $legacy = WP_CONTENT_DIR . '/uploads/erp-pdfs/voucher_987654321.pdf';
            wp_mkdir_p( dirname( $legacy ) );
            file_put_contents( $legacy, '%PDF-1.4 pw' );
            $before = file_exists( $legacy );
            delete_option( 'erp_acct_pdf_dir_secured' );
            erp_acct_secure_pdf_dir();
            $after = file_exists( $legacy );
            @unlink( $legacy );
            $flag = (string) get_option( 'erp_acct_pdf_dir_secured' );
            if ( false !== $prev ) { update_option( 'erp_acct_pdf_dir_secured', $prev, false ); }
            echo wp_json_encode( [ 'before' => $before, 'after' => $after, 'flag' => $flag ] );
        `);
        expect(out).toEqual({ before: true, after: false, flag: '1' });
    });

    test('Export as PDF links to the endpoint, not the uploads folder', { tag: ['@lite', '@accounting', '@admin'] }, async ({ browser }) => {
        const ctx = await browser.newContext({ storageState: data.auth.adminFile });
        const page = await ctx.newPage();
        const rest = page.waitForResponse(r => r.url().includes(`expenses/${voucher}`) && r.request().method() === 'GET');
        await page.goto(toPath(`wp-admin/admin.php?page=erp-accounting#/expenses/${voucher}`));
        const body = await (await rest).json();
        expect(body.pdf_link).toBe(downloadUrl(voucher));
        await page.getByText('More Action').click();
        await expect(page.getByRole('link', { name: 'Export as PDF' })).toHaveAttribute('href', downloadUrl(voucher));
        await ctx.close();
    });

    test('logged out and employee are refused; accounting manager passes the capability check', { tag: ['@lite', '@accounting', '@employee', '@accManager'] }, async () => {
        const anon = await request.newContext({ baseURL: BASE_URL });
        const anonRes = await anon.get(downloadUrl(voucher), { maxRedirects: 0 });
        expect(anonRes.status(), 'no nopriv handler').toBe(400);
        await anon.dispose();

        const emp = await request.newContext({ baseURL: BASE_URL, storageState: data.auth.employeeFile });
        const empRes = await emp.get(downloadUrl(voucher));
        expect(empRes.status(), 'employee lacks erp_ac_view_expense').toBe(403);
        expect(await empRes.text()).toContain('You do not have permission to view this transaction.');
        await emp.dispose();

        const mgr = await request.newContext({ baseURL: BASE_URL, storageState: data.auth.accManagerFile });
        const mgrRes = await mgr.get(downloadUrl(voucher));
        // The add-on is not installed here, so the handler stops right after the capability check.
        expect(mgrRes.status()).not.toBe(403);
        expect(mgrRes.status()).not.toBe(400);
        expect(await mgrRes.text()).toContain('ERP PDF extension is not installed');
        const missing = await mgr.get(downloadUrl(987654321));
        expect(missing.status(), 'unknown voucher').toBe(404);
        await mgr.dispose();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// wp-erp#1673 — PHP 8 TypeErrors in erp_acct_generate_pdf()
// ─────────────────────────────────────────────────────────────────────────────
test.describe('erp_acct_generate_pdf() survives PHP 8 edge cases (wp-erp#1673)', () => {
    let expenseVoucher = 0;

    test.beforeAll(async () => {
        expenseVoucher = (await seedExpense(admin)).voucher;
    });

    type GenResult = { ok: boolean; err?: string; ret?: string | null; rendered: string[]; to?: unknown; warnings: string[]; hasDueDate?: boolean };

    function generate(transactionPhp: string): GenResult {
        return phpJson<GenResult>(`${PDF_STUB}
            $pw_warn = [];
            set_error_handler( function ( $no, $str ) use ( &$pw_warn ) { $pw_warn[] = $str; return true; } );
            $file = sys_get_temp_dir() . '/pw_gen_' . wp_generate_password( 8, false ) . '.pdf';
            $out  = [ 'ok' => true, 'warnings' => [] ];
            try {
                $trn = ${transactionPhp};
                $out['hasDueDate'] = is_array( $trn ) ? array_key_exists( 'due_date', $trn ) : property_exists( $trn, 'due_date' );
                $out['ret'] = erp_acct_generate_pdf( [ 'receiver' => [ 'pw@example.com' ] ], $trn, $file, 'F' );
            } catch ( \\Throwable $e ) {
                $out['ok']  = false;
                $out['err'] = get_class( $e ) . ': ' . $e->getMessage();
            }
            restore_error_handler();
            @unlink( $file );
            foreach ( $GLOBALS['pw_pdf']['calls'] as $c ) { if ( 'set_to_address' === $c[0] ) { $out['to'] = $c[1][0]; } }
            $out['rendered'] = $GLOBALS['pw_pdf']['rendered'];
            $out['warnings'] = $pw_warn;
            echo wp_json_encode( $out );
        `);
    }

    test('an orphaned voucher (no transaction row) is skipped without a TypeError', { tag: ['@lite', '@accounting', '@admin'] }, () => {
        const r = generate('erp_acct_get_transaction( 987654321 )');
        expect(r.ok, r.err).toBe(true);
        expect(r.rendered, 'nothing rendered for an orphan').toEqual([]);
        expect(r.ret ?? null).toBeNull();
    });

    test('a transaction whose person no longer exists still renders, to-address is an array', { tag: ['@lite', '@accounting', '@admin'] }, () => {
        const r = generate(`(object) array_merge( erp_acct_get_transaction( ${expenseVoucher} ), [ 'people_id' => 987654321 ] )`);
        expect(r.ok, r.err).toBe(true);
        expect(r.rendered).toHaveLength(1);
        expect(Array.isArray(r.to), `set_to_address got ${JSON.stringify(r.to)}`).toBe(true);
    });

    test('an expense (no due_date) renders without "Undefined property" warnings', { tag: ['@lite', '@accounting', '@admin'] }, () => {
        const r = generate(`(object) erp_acct_get_transaction( ${expenseVoucher} )`);
        expect(r.ok, r.err).toBe(true);
        expect(r.hasDueDate, 'precondition: an expense has no due_date').toBe(false);
        expect(r.rendered).toHaveLength(1);
        expect(r.warnings.filter(w => /due_date/.test(w)), r.warnings.join('\n')).toEqual([]);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// wp-erp#1672 / erp-pro#1097 — particulars sanitized and rendered as text
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Accounting particulars are sanitized and never rendered as HTML (wp-erp#1672 / erp-pro#1097)', () => {
    const PAYLOAD = '<img src=x onerror="window.__pwXss=1">';

    test('accounting manager: markup in expense and journal particulars is stripped on save', { tag: ['@lite', '@accounting', '@accManager'] }, async () => {
        const mgr = await restAs(data.auth.accManagerFile);
        try {
            const exp = await mgr.call('POST', `${ACCT}/expenses`, {
                trn_date: '2026-03-06', amount: 30, deposit_to: LEDGER.cash, trn_by: 1, status: 4, type: 'expense',
                particulars: `pw_line1\n${PAYLOAD}`,
                bill_details: [{ ledger_id: LEDGER.generalExpenses, particulars: `pw_item ${PAYLOAD}`, amount: 30 }],
            });
            expect(exp.status(), await exp.text()).toBeLessThan(300);
            const expVoucher = Number((await json<{ voucher_no: number }>(exp)).voucher_no);
            created.vouchers.push(expVoucher);

            const head = await dbUtils.dbQuery<{ particulars: string }>(`SELECT particulars FROM ${P}_erp_acct_expenses WHERE voucher_no = ?`, [expVoucher]);
            expect(head[0]?.particulars).toContain('pw_line1');
            expect(head[0]?.particulars).not.toContain('<');
            const lines = await dbUtils.dbQuery<{ particulars: string }>(`SELECT particulars FROM ${P}_erp_acct_expense_details WHERE trn_no = ?`, [expVoucher]);
            expect(lines.length).toBeGreaterThan(0);
            for (const l of lines) expect(l.particulars ?? '').not.toContain('<');

            const jr = await mgr.call('POST', `${ACCT}/journals`, {
                trn_date: '2026-03-02', ref: 'PW-J',
                particulars: `pw_journal\n${PAYLOAD}`,
                line_items: [
                    { ledger_id: LEDGER.cash, particulars: `cash ${PAYLOAD}`, debit: 5, credit: 0 },
                    { ledger_id: LEDGER.ownersContribution, particulars: 'owner', debit: 0, credit: 5 },
                ],
            });
            expect(jr.status(), await jr.text()).toBeLessThan(300);
            const jVoucher = Number((await json<{ voucher_no: number }>(jr)).voucher_no);
            created.vouchers.push(jVoucher);
            const jHead = await dbUtils.dbQuery<{ particulars: string }>(`SELECT particulars FROM ${P}_erp_acct_journals WHERE voucher_no = ?`, [jVoucher]);
            expect(jHead[0]?.particulars).toContain('pw_journal');
            expect(jHead[0]?.particulars).not.toContain('<');
            const jLines = await dbUtils.dbQuery<{ particulars: string }>(`SELECT particulars FROM ${P}_erp_acct_journal_details WHERE trn_no = ?`, [jVoucher]);
            for (const l of jLines) expect(l.particulars ?? '').not.toContain('<');
        } finally {
            await mgr.dispose();
        }
    });

    test('a stored row that already contains markup is shown as text on the expense page', { tag: ['@lite', '@accounting', '@admin'] }, async ({ browser }) => {
        const { voucher } = await seedExpense(admin);
        // Simulate a legacy row saved before the input fix.
        await dbUtils.dbQuery(`UPDATE ${P}_erp_acct_expenses SET particulars = ? WHERE voucher_no = ?`, [`pw_legacy\n<b class="pw-bold">bold</b> ${PAYLOAD}`, voucher]);

        const ctx = await browser.newContext({ storageState: data.auth.adminFile });
        const page = await ctx.newPage();
        await page.goto(toPath(`wp-admin/admin.php?page=erp-accounting#/expenses/${voucher}`));
        await expect(page.getByText('pw_legacy')).toBeVisible();
        await expect(page.getByText(/^bold\s*$/)).toBeVisible();
        await expect(page.locator('b.pw-bold')).toHaveCount(0);
        await expect(page.locator('img[src="x"]')).toHaveCount(0);
        await page.waitForTimeout(500);
        expect(await page.evaluate(() => (window as unknown as { __pwXss?: number }).__pwXss)).toBeUndefined();
        await ctx.close();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// wp-erp#1670 / erp-pro#1096 — readonly invoice hash + accounting employees routes
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Invoice link hash and accounting employees access (wp-erp#1670 / erp-pro#1096)', () => {
    let invoiceVoucher = 0;
    let readonlyUrl = '';

    test.beforeAll(async () => {
        const customer = await seedPerson(admin, 'customer');
        const res = await admin.call('POST', `${ACCT}/invoices`, {
            customer_id: customer.id, date: '2026-03-01', due_date: '2026-03-31', billing_address: 'pw', discount_type: 0, tax_rate_id: 0, estimate: 0, status: 2, attachments: '',
            line_items: [{ product_id: 0, qty: 1, unit_price: 25, discount: 0, tax: 0, tax_cat_id: 0, item_total: 25 }],
        });
        expect(res.status(), await res.text()).toBeLessThan(300);
        const body = await json<{ id: number; voucher_no?: number }>(res);
        const rows = await dbUtils.dbQuery<{ v: number }>(`SELECT voucher_no v FROM ${P}_erp_acct_invoices WHERE id = ?`, [body.id]);
        invoiceVoucher = Number(rows[0]?.v);
        created.vouchers.push(invoiceVoucher);
        const get = await admin.call('GET', `${ACCT}/invoices/${invoiceVoucher}`);
        readonlyUrl = String((await json<{ readonly_url: string }>(get)).readonly_url);
    });

    test('readonly invoice opens with the issued link but not with a computable sha256 hash', { tag: ['@lite', '@accounting', '@guest'] }, async ({ browser }) => {
        const ctx = await browser.newContext({ storageState: { cookies: [], origins: [] } });
        const page = await ctx.newPage();
        const auth = new URL(readonlyUrl).searchParams.get('auth') as string;
        const sha = await import('node:crypto').then(c => c.createHash('sha256').update(`${invoiceVoucher}invoice`).digest('hex'));
        expect(auth, 'the issued hash is not the plain sha256(id + type)').not.toBe(sha);

        await page.goto(readonlyUrl);
        await expect(page.locator('link[href*="invoice-front.css"]')).toHaveCount(1);
        await expect(page.locator('body')).not.toContainText(CRITICAL_ERROR);

        for (const bad of [sha, 'deadbeef']) {
            await page.goto(toPath(`?query=readonly_invoice&trans_id=${invoiceVoucher}&auth=${bad}`));
            await expect(page.locator('link[href*="invoice-front.css"]'), `auth=${bad}`).toHaveCount(0);
        }
        await ctx.close();
    });

    test('employee is refused the accounting employees routes; accounting manager is allowed', { tag: ['@lite', '@accounting', '@employee', '@accManager'] }, async () => {
        const emp = await restAs(data.auth.employeeFile);
        const mgr = await restAs(data.auth.accManagerFile);
        try {
            const employeeId = process.env.EMPLOYEE_USER_ID ?? '5';
            for (const route of [`${ACCT}/employees`, `${ACCT}/employees/${employeeId}`, `${ACCT}/employees/${employeeId}/transactions`]) {
                const e = await emp.call('GET', route);
                expect(e.status(), `employee GET ${route}`).toBe(403);
            }
            const list = await mgr.call('GET', `${ACCT}/employees`);
            expect(list.status(), `accounting manager GET employees: ${(await list.text()).slice(0, 200)}`).toBe(200);
            const one = await mgr.call('GET', `${ACCT}/employees/${process.env.EMPLOYEE_USER_ID ?? '5'}`);
            expect(one.status()).not.toBe(403);
        } finally {
            await emp.dispose();
            await mgr.dispose();
        }
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// erp-pro#1102 / erp-pro#1101 — payment gateway compares the link hash
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Payment gateway verifies the invoice link hash (erp-pro#1102 / erp-pro#1101)', () => {
    let invoiceVoucher = 0;

    test.beforeAll(async () => {
        const customer = await seedPerson(admin, 'customer');
        const res = await admin.call('POST', `${ACCT}/invoices`, {
            customer_id: customer.id, date: '2026-03-01', due_date: '2026-03-31', billing_address: 'pw', discount_type: 0, tax_rate_id: 0, estimate: 0, status: 2, attachments: '',
            line_items: [{ product_id: 0, qty: 1, unit_price: 15, discount: 0, tax: 0, tax_cat_id: 0, item_total: 15 }],
        });
        expect(res.status(), await res.text()).toBeLessThan(300);
        const body = await json<{ id: number }>(res);
        const rows = await dbUtils.dbQuery<{ v: number }>(`SELECT voucher_no v FROM ${P}_erp_acct_invoices WHERE id = ?`, [body.id]);
        invoiceVoucher = Number(rows[0]?.v);
        created.vouchers.push(invoiceVoucher);
    });

    test('only the issued hash reaches erp_payment_gateway_{method}', { tag: ['@pro', '@accounting', '@guest'] }, () => {
        const out = phpJson<Record<string, { verified: boolean; fired: string }>>(`
            if ( ! class_exists( '\\\\WeDevs\\\\PaymentGateway\\\\PaymentGatewayHandler' ) ) { echo wp_json_encode( [ 'missing' => [ 'verified' => false, 'fired' => 'class missing' ] ] ); return; }
            wp_set_current_user( 0 );
            $id      = ${invoiceVoucher};
            $handler = new \\WeDevs\\PaymentGateway\\PaymentGatewayHandler();
            $fired   = null;
            add_action( 'erp_payment_gateway_pw_probe', function ( $trn ) use ( &$fired ) { $fired = is_array( $trn ) ? 'transaction' : 'false'; } );
            $cases = [
                'issued'   => erp_acct_get_invoice_link_hash( $id, 'invoice' ),
                'sha256'   => hash( 'sha256', $id . 'invoice' ),
                'deadbeef' => 'deadbeef',
            ];
            $out = [];
            foreach ( $cases as $k => $auth ) {
                $_REQUEST = $_POST = [ 'query' => 'readonly_invoice', 'trans_id' => $id, 'auth' => $auth, 'erp_payment_submit' => 1, 'erp_pg_payment_method' => 'pw_probe' ];
                $fired = null;
                $verified = (bool) $handler->_verify_page();
                $handler->payment_gateway_submission();
                $out[ $k ] = [ 'verified' => $verified, 'fired' => (string) $fired ];
            }
            echo wp_json_encode( $out );
        `);
        expect(out.missing, 'payment_gateway module loaded').toBeUndefined();
        expect(out.issued).toEqual({ verified: true, fired: 'transaction' });
        expect(out.sha256).toEqual({ verified: false, fired: 'false' });
        expect(out.deadbeef).toEqual({ verified: false, fired: 'false' });
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// erp-pro#1122 / erp-pro#305 — returns keep only the selected products
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Purchase and sales returns save only the selected products (erp-pro#1122 / erp-pro#305)', () => {
    test.use({ storageState: data.auth.adminFile });

    let vendor = { id: 0, name: '' };
    let customer = { id: 0, name: '' };
    const productIds: number[] = [];

    test.beforeAll(async () => {
        vendor = await seedPerson(admin, 'vendor');
        customer = await seedPerson(admin, 'customer');
        for (const s of ['a', 'b', 'c']) productIds.push(await seedProduct(admin, `pw_ret_${Date.now()}_${s}`, vendor.id));
    });

    /** Pick a day in the return form's calendar (the field only accepts picked dates). */
    async function pickReturnDate(page: Page) {
        const input = page.locator('table.invoice-info input.wperp-form-field').first();
        await input.click();
        const day = page.locator('.c-day-content, .vc-day-content').filter({ hasText: /^\s*15\s*$/ }).first();
        await day.click();
        await expect(input).not.toHaveValue('');
    }

    async function returnLastOnly(page: Page, route: string, createPath: string): Promise<{ sent: unknown[] }> {
        await page.goto(toPath(`wp-admin/admin.php?page=erp-accounting#/${route}`));
        const rows = page.locator('table.invoice-table tbody tr');
        await expect(rows).toHaveCount(3);
        await page.locator('#select-item-2').check();
        await pickReturnDate(page);

        const req = page.waitForRequest(r => r.url().includes(createPath) && r.method() === 'POST');
        await page.getByRole('button', { name: 'Save' }).click();
        await page.locator('.swal2-confirm').click();
        const sent = (await req).postDataJSON() as { line_items: unknown[] };
        return { sent: sent.line_items };
    }

    test('purchase with 3 products, return only the third -> return holds exactly 1 item', { tag: ['@pro', '@accounting', '@admin'] }, async ({ page }) => {
        const items = productIds.map((id, i) => ({ product_id: id, qty: 1, price: 10 * (i + 1), unit_price: 10 * (i + 1), tax: 0, tax_amount: 0, discount: 0, tax_cat_id: 0, item_total: 10 * (i + 1) }));
        const res = await admin.call('POST', `${ACCT}/purchases`, {
            vendor_id: vendor.id, vendor_name: vendor.name, trn_date: '2026-03-01', due_date: '2026-03-31', type: 'purchase', status: 2, amount: 60, due: 60, trn_by: 1,
            particulars: `${RUN} purchase return`, line_items: items,
        });
        expect(res.status(), await res.text()).toBeLessThan(300);
        const voucher = Number((await json<{ voucher_no: number }>(res)).voucher_no);
        created.vouchers.push(voucher);

        const { sent } = await returnLastOnly(page, `transactions/purchases/return/${voucher}/create`, 'purchase-return/create');
        expect(sent, 'only the selected line is posted').toHaveLength(1);
        expect(Number((sent[0] as { product_id: number }).product_id)).toBe(productIds[2]);

        await expect.poll(async () => (await dbUtils.dbQuery(`SELECT id FROM ${P}_erp_acct_purchase_return WHERE invoice_id = ?`, [voucher])).length).toBe(1);
        const details = await dbUtils.dbQuery<{ product_id: number }>(
            `SELECT d.product_id FROM ${P}_erp_acct_purchase_return_details d JOIN ${P}_erp_acct_purchase_return r ON r.voucher_no = d.trn_no WHERE r.invoice_id = ?`,
            [voucher],
        );
        expect(details.map(d => Number(d.product_id))).toEqual([productIds[2]]);
    });

    test('invoice with 3 products, return only the third -> return holds exactly 1 item', { tag: ['@pro', '@accounting', '@admin'] }, async ({ page }) => {
        const res = await admin.call('POST', `${ACCT}/invoices`, {
            customer_id: customer.id, date: '2026-03-01', due_date: '2026-03-31', billing_address: 'pw', discount_type: 0, tax_rate_id: 0, estimate: 0, status: 2, attachments: '',
            line_items: productIds.map((id, i) => ({ product_id: id, qty: 1, unit_price: 20 * (i + 1), discount: 0, tax: 0, tax_cat_id: 0, item_total: 20 * (i + 1) })),
        });
        expect(res.status(), await res.text()).toBeLessThan(300);
        const id = (await json<{ id: number }>(res)).id;
        const rows = await dbUtils.dbQuery<{ v: number }>(`SELECT voucher_no v FROM ${P}_erp_acct_invoices WHERE id = ?`, [id]);
        const voucher = Number(rows[0]?.v);
        created.vouchers.push(voucher);

        const { sent } = await returnLastOnly(page, `transactions/sales/return/${voucher}/create`, 'sales-return/create');
        expect(sent, 'only the selected line is posted').toHaveLength(1);
        expect(Number((sent[0] as { product_id: number }).product_id)).toBe(productIds[2]);

        await expect.poll(async () => (await dbUtils.dbQuery(`SELECT id FROM ${P}_erp_acct_sales_return WHERE invoice_id = ?`, [voucher])).length).toBe(1);
        const details = await dbUtils.dbQuery<{ product_id: number }>(
            `SELECT d.product_id FROM ${P}_erp_acct_sales_return_details d JOIN ${P}_erp_acct_sales_return r ON r.voucher_no = d.trn_no WHERE r.invoice_id = ?`,
            [voucher],
        );
        expect(details.map(d => Number(d.product_id))).toEqual([productIds[2]]);
    });
});
