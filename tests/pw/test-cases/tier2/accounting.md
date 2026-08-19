# Tier 2 — accounting

Edge cases — boundaries, optional-field omission, empty states, unusual but legal input.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**56 cases** (52 derived from the harness, 4 hand-written business flows).

## Business flows

#### ACCOUNTING-F2-001 — An unbalanced journal is refused

- **Surface:** `admin.php?page=erp-accounting#/transactions/journals/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Create a journal whose debits and credits differ by 0.01 and save.
- **Expected:** The save is refused with a balance message; no journal or ledger entry is written.
- **Oracle:** UI message + journal row count unchanged.

#### ACCOUNTING-F2-002 — A payment larger than the invoice total is handled

- **Surface:** `admin.php?page=erp-accounting#/payments/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Receive a payment greater than the invoice due amount.
- **Expected:** It is either refused or recorded as an explicit overpayment/credit — never a negative due amount shown as if it were normal.
- **Oracle:** Invoice due amount + customer balance sign.

#### ACCOUNTING-F2-003 — Tax rate applies to invoice line items correctly

- **Surface:** `admin.php?page=erp-accounting#/settings/taxes/tax-rates`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Create a tax rate of a known percentage.
  2. Create an invoice applying it to a line of known value.
- **Expected:** Line tax equals value × rate, and the invoice total equals subtotal + tax, to the currency's decimal places.
- **Oracle:** Arithmetic on the rendered invoice vs the stored line rows.

#### ACCOUNTING-F2-004 — Changing the currency does not re-denominate existing records

- **Surface:** `admin.php?page=erp-settings#/erp-ac/currency_option`
- **Tags:** @tier2 @accounting @edge @destructive
- **Preconditions:** A DB snapshot has been taken — this changes global state.
- **Steps:**
  1. Note an existing invoice total.
  2. Change the accounting currency.
  3. Re-open that invoice.
- **Expected:** The stored amount is unchanged; only the displayed symbol changes. No silent conversion.
- **Oracle:** Stored amount before/after + rendered symbol.

## Screen & field coverage

#### ACCOUNTING-T2-001 — `query_time` accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/dashboard`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/dashboard`.
  2. Save with `query_time` set to the first real option.
  3. Save with `query_time` set to the last option.
  4. Save with `query_time` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-002 — `admin.php?page=erp-accounting#/dashboard` saves with only its required fields

- **Surface:** `admin.php?page=erp-accounting#/dashboard`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/dashboard`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### ACCOUNTING-T2-003 — `qty` ("Please search Oops! No elements found. List is empty.") accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/invoices/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/invoices/new`.
  2. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to 0.
  3. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a negative value.
  4. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a decimal where an integer is expected.
  5. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-004 — `qty` ("Please search Oops! No elements found. List is empty.") accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/invoices/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/invoices/new`.
  2. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to 0.
  3. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a negative value.
  4. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a decimal where an integer is expected.
  5. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-005 — `qty` ("Please search Oops! No elements found. List is empty.") accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/invoices/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/invoices/new`.
  2. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to 0.
  3. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a negative value.
  4. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a decimal where an integer is expected.
  5. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-006 — `attachments[]` ("Select files") accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/invoices/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/invoices/new`.
  2. Save with `attachments[]` ("Select files") set to a single character.
  3. Save with `attachments[]` ("Select files") set to a 255-character value.
  4. Save with `attachments[]` ("Select files") set to a value with leading and trailing whitespace.
  5. Save with `attachments[]` ("Select files") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-007 — `href` accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/invoices/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/invoices/new`.
  2. Save with `href` set to a scheme-less host (`example.test`).
  3. Save with `href` set to an `https://` URL with a query string and a fragment.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-008 — `admin.php?page=erp-accounting#/invoices/new` saves with only its required fields

- **Surface:** `admin.php?page=erp-accounting#/invoices/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/invoices/new`.
  2. Fill only: `href`.
  3. Submit.
- **Expected:** The record saves. The 4 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### ACCOUNTING-T2-009 — `qty` ("Please search Oops! No elements found. List is empty.") accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/estimates/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/estimates/new`.
  2. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to 0.
  3. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a negative value.
  4. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a decimal where an integer is expected.
  5. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-010 — `qty` ("Please search Oops! No elements found. List is empty.") accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/estimates/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/estimates/new`.
  2. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to 0.
  3. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a negative value.
  4. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a decimal where an integer is expected.
  5. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-011 — `qty` ("Please search Oops! No elements found. List is empty.") accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/estimates/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/estimates/new`.
  2. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to 0.
  3. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a negative value.
  4. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a decimal where an integer is expected.
  5. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-012 — `attachments[]` ("Select files") accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/estimates/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/estimates/new`.
  2. Save with `attachments[]` ("Select files") set to a single character.
  3. Save with `attachments[]` ("Select files") set to a 255-character value.
  4. Save with `attachments[]` ("Select files") set to a value with leading and trailing whitespace.
  5. Save with `attachments[]` ("Select files") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-013 — `href` accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/estimates/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/estimates/new`.
  2. Save with `href` set to a scheme-less host (`example.test`).
  3. Save with `href` set to an `https://` URL with a query string and a fragment.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-014 — `admin.php?page=erp-accounting#/estimates/new` saves with only its required fields

- **Surface:** `admin.php?page=erp-accounting#/estimates/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/estimates/new`.
  2. Fill only: `href`.
  3. Submit.
- **Expected:** The record saves. The 4 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### ACCOUNTING-T2-015 — `finalamount` accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/payments/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/payments/new`.
  2. Save with `finalamount` set to a single character.
  3. Save with `finalamount` set to a 255-character value.
  4. Save with `finalamount` set to a value with leading and trailing whitespace.
  5. Save with `finalamount` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-016 — `attachments[]` ("Select files") accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/payments/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/payments/new`.
  2. Save with `attachments[]` ("Select files") set to a single character.
  3. Save with `attachments[]` ("Select files") set to a 255-character value.
  4. Save with `attachments[]` ("Select files") set to a value with leading and trailing whitespace.
  5. Save with `attachments[]` ("Select files") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-017 — `admin.php?page=erp-accounting#/payments/new` saves with only its required fields

- **Surface:** `admin.php?page=erp-accounting#/payments/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/payments/new`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### ACCOUNTING-T2-018 — `amount` accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/bills/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/bills/new`.
  2. Save with `amount` set to a single character.
  3. Save with `amount` set to a 255-character value.
  4. Save with `amount` set to a value with leading and trailing whitespace.
  5. Save with `amount` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-019 — `amount` accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/bills/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/bills/new`.
  2. Save with `amount` set to a single character.
  3. Save with `amount` set to a 255-character value.
  4. Save with `amount` set to a value with leading and trailing whitespace.
  5. Save with `amount` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-020 — `amount` accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/bills/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/bills/new`.
  2. Save with `amount` set to a single character.
  3. Save with `amount` set to a 255-character value.
  4. Save with `amount` set to a value with leading and trailing whitespace.
  5. Save with `amount` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-021 — `finalamount` accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/bills/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/bills/new`.
  2. Save with `finalamount` set to a single character.
  3. Save with `finalamount` set to a 255-character value.
  4. Save with `finalamount` set to a value with leading and trailing whitespace.
  5. Save with `finalamount` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-022 — `attachments[]` ("Select files") accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/bills/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/bills/new`.
  2. Save with `attachments[]` ("Select files") set to a single character.
  3. Save with `attachments[]` ("Select files") set to a 255-character value.
  4. Save with `attachments[]` ("Select files") set to a value with leading and trailing whitespace.
  5. Save with `attachments[]` ("Select files") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-023 — `admin.php?page=erp-accounting#/bills/new` saves with only its required fields

- **Surface:** `admin.php?page=erp-accounting#/bills/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/bills/new`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 5 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### ACCOUNTING-T2-024 — `attachments[]` ("Select files") accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/pay-bills/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/pay-bills/new`.
  2. Save with `attachments[]` ("Select files") set to a single character.
  3. Save with `attachments[]` ("Select files") set to a 255-character value.
  4. Save with `attachments[]` ("Select files") set to a value with leading and trailing whitespace.
  5. Save with `attachments[]` ("Select files") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-025 — `admin.php?page=erp-accounting#/pay-bills/new` saves with only its required fields

- **Surface:** `admin.php?page=erp-accounting#/pay-bills/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/pay-bills/new`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### ACCOUNTING-T2-026 — `qty` ("Please search Oops! No elements found. List is empty.") accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/purchase-orders/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/purchase-orders/new`.
  2. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to 0.
  3. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a negative value.
  4. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a decimal where an integer is expected.
  5. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-027 — `qty` ("Please search Oops! No elements found. List is empty.") accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/purchase-orders/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/purchase-orders/new`.
  2. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to 0.
  3. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a negative value.
  4. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a decimal where an integer is expected.
  5. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-028 — `qty` ("Please search Oops! No elements found. List is empty.") accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/purchase-orders/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/purchase-orders/new`.
  2. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to 0.
  3. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a negative value.
  4. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a decimal where an integer is expected.
  5. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-029 — `attachments[]` ("Select files") accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/purchase-orders/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/purchase-orders/new`.
  2. Save with `attachments[]` ("Select files") set to a single character.
  3. Save with `attachments[]` ("Select files") set to a 255-character value.
  4. Save with `attachments[]` ("Select files") set to a value with leading and trailing whitespace.
  5. Save with `attachments[]` ("Select files") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-030 — `admin.php?page=erp-accounting#/purchase-orders/new` saves with only its required fields

- **Surface:** `admin.php?page=erp-accounting#/purchase-orders/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/purchase-orders/new`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 4 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### ACCOUNTING-T2-031 — `qty` ("Please search Oops! No elements found. List is empty.") accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/purchases/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/purchases/new`.
  2. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to 0.
  3. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a negative value.
  4. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a decimal where an integer is expected.
  5. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-032 — `qty` ("Please search Oops! No elements found. List is empty.") accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/purchases/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/purchases/new`.
  2. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to 0.
  3. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a negative value.
  4. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a decimal where an integer is expected.
  5. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-033 — `qty` ("Please search Oops! No elements found. List is empty.") accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/purchases/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/purchases/new`.
  2. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to 0.
  3. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a negative value.
  4. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a decimal where an integer is expected.
  5. Save with `qty` ("Please search Oops! No elements found. List is empty.") set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-034 — `attachments[]` ("Select files") accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/purchases/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/purchases/new`.
  2. Save with `attachments[]` ("Select files") set to a single character.
  3. Save with `attachments[]` ("Select files") set to a 255-character value.
  4. Save with `attachments[]` ("Select files") set to a value with leading and trailing whitespace.
  5. Save with `attachments[]` ("Select files") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-035 — `admin.php?page=erp-accounting#/purchases/new` saves with only its required fields

- **Surface:** `admin.php?page=erp-accounting#/purchases/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/purchases/new`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 4 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### ACCOUNTING-T2-036 — `finalamount` accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/pay-purchases/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/pay-purchases/new`.
  2. Save with `finalamount` set to a single character.
  3. Save with `finalamount` set to a 255-character value.
  4. Save with `finalamount` set to a value with leading and trailing whitespace.
  5. Save with `finalamount` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-037 — `attachments[]` ("Select files") accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/pay-purchases/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/pay-purchases/new`.
  2. Save with `attachments[]` ("Select files") set to a single character.
  3. Save with `attachments[]` ("Select files") set to a 255-character value.
  4. Save with `attachments[]` ("Select files") set to a value with leading and trailing whitespace.
  5. Save with `attachments[]` ("Select files") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-038 — `admin.php?page=erp-accounting#/pay-purchases/new` saves with only its required fields

- **Surface:** `admin.php?page=erp-accounting#/pay-purchases/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/pay-purchases/new`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### ACCOUNTING-T2-039 — `amount` accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/expenses/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/expenses/new`.
  2. Save with `amount` set to a single character.
  3. Save with `amount` set to a 255-character value.
  4. Save with `amount` set to a value with leading and trailing whitespace.
  5. Save with `amount` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-040 — `amount` accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/expenses/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/expenses/new`.
  2. Save with `amount` set to a single character.
  3. Save with `amount` set to a 255-character value.
  4. Save with `amount` set to a value with leading and trailing whitespace.
  5. Save with `amount` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-041 — `amount` accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/expenses/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/expenses/new`.
  2. Save with `amount` set to a single character.
  3. Save with `amount` set to a 255-character value.
  4. Save with `amount` set to a value with leading and trailing whitespace.
  5. Save with `amount` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-042 — `finalamount` accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/expenses/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/expenses/new`.
  2. Save with `finalamount` set to a single character.
  3. Save with `finalamount` set to a 255-character value.
  4. Save with `finalamount` set to a value with leading and trailing whitespace.
  5. Save with `finalamount` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-043 — `attachments[]` ("Select files") accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/expenses/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/expenses/new`.
  2. Save with `attachments[]` ("Select files") set to a single character.
  3. Save with `attachments[]` ("Select files") set to a 255-character value.
  4. Save with `attachments[]` ("Select files") set to a value with leading and trailing whitespace.
  5. Save with `attachments[]` ("Select files") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-044 — `admin.php?page=erp-accounting#/expenses/new` saves with only its required fields

- **Surface:** `admin.php?page=erp-accounting#/expenses/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/expenses/new`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 5 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### ACCOUNTING-T2-045 — `amount` accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/checks/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/checks/new`.
  2. Save with `amount` set to 0.
  3. Save with `amount` set to a negative value.
  4. Save with `amount` set to a decimal where an integer is expected.
  5. Save with `amount` set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-046 — `amount` accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/checks/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/checks/new`.
  2. Save with `amount` set to 0.
  3. Save with `amount` set to a negative value.
  4. Save with `amount` set to a decimal where an integer is expected.
  5. Save with `amount` set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-047 — `amount` accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/checks/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/checks/new`.
  2. Save with `amount` set to 0.
  3. Save with `amount` set to a negative value.
  4. Save with `amount` set to a decimal where an integer is expected.
  5. Save with `amount` set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-048 — `finalamount` accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/checks/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/checks/new`.
  2. Save with `finalamount` set to a single character.
  3. Save with `finalamount` set to a 255-character value.
  4. Save with `finalamount` set to a value with leading and trailing whitespace.
  5. Save with `finalamount` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-049 — `attachments[]` ("Select files") accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/checks/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/checks/new`.
  2. Save with `attachments[]` ("Select files") set to a single character.
  3. Save with `attachments[]` ("Select files") set to a 255-character value.
  4. Save with `attachments[]` ("Select files") set to a value with leading and trailing whitespace.
  5. Save with `attachments[]` ("Select files") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-050 — `admin.php?page=erp-accounting#/checks/new` saves with only its required fields

- **Surface:** `admin.php?page=erp-accounting#/checks/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/checks/new`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 5 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### ACCOUNTING-T2-051 — `attachments[]` ("Select files") accepts its edge values

- **Surface:** `admin.php?page=erp-accounting#/transactions/journals/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/transactions/journals/new`.
  2. Save with `attachments[]` ("Select files") set to a single character.
  3. Save with `attachments[]` ("Select files") set to a 255-character value.
  4. Save with `attachments[]` ("Select files") set to a value with leading and trailing whitespace.
  5. Save with `attachments[]` ("Select files") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### ACCOUNTING-T2-052 — `admin.php?page=erp-accounting#/transactions/journals/new` saves with only its required fields

- **Surface:** `admin.php?page=erp-accounting#/transactions/journals/new`
- **Tags:** @tier2 @accounting @edge
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/transactions/journals/new`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.
