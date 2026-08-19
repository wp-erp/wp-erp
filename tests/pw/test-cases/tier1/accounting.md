# Tier 1 — accounting

Happy path — the screen loads, every field and label renders as captured, and the primary create/read/update/delete flow succeeds.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**61 cases** (56 derived from the harness, 5 hand-written business flows).

## Business flows

#### ACCOUNTING-F1-001 — Invoice → payment settles the customer balance

- **Surface:** `admin.php?page=erp-accounting#/invoices/new`
- **Tags:** @tier1 @accounting @flow
- **Preconditions:** A customer and at least one product exist; the chart of accounts is initialised.
- **Steps:**
  1. Create an invoice with two line items and save it.
  2. Receive a payment for the full invoice total.
  3. Open the customer.
- **Expected:** The invoice shows as paid and the customer's outstanding balance returns to zero.
- **Oracle:** Invoice status + customer balance + the ledger entries the payment created.

#### ACCOUNTING-F1-002 — A partial payment leaves the correct remainder

- **Surface:** `admin.php?page=erp-accounting#/payments/new`
- **Tags:** @tier1 @accounting @flow
- **Steps:**
  1. Create an invoice for a known total T.
  2. Receive a payment of T/2.
- **Expected:** The invoice shows partially paid with exactly T/2 outstanding — no rounding drift.
- **Oracle:** Invoice due amount vs T/2 to the currency's decimal places.

#### ACCOUNTING-F1-003 — Bill → pay bill settles the vendor balance

- **Surface:** `admin.php?page=erp-accounting#/bills/new`
- **Tags:** @tier1 @accounting @flow
- **Steps:**
  1. Create a bill against a vendor.
  2. Pay it in full via Pay Bill.
- **Expected:** The bill shows paid and the vendor balance returns to zero.
- **Oracle:** Bill status + vendor balance + ledger entries.

#### ACCOUNTING-F1-004 — A manual journal must balance before it posts

- **Surface:** `admin.php?page=erp-accounting#/transactions/journals/new`
- **Tags:** @tier1 @accounting @flow
- **Steps:**
  1. Create a journal with equal debit and credit totals and save.
- **Expected:** The journal posts and appears in the journals list.
- **Oracle:** Journal row + the resulting ledger entries summing to zero.

#### ACCOUNTING-F1-005 — Opening balance seeds the trial balance

- **Surface:** `admin.php?page=erp-accounting#/opening-balance`
- **Tags:** @tier1 @accounting @flow
- **Steps:**
  1. Enter opening balances across the chart of accounts (118 fields captured on this screen) so debits equal credits.
  2. Save.
  3. Open Reports → Trial Balance.
- **Expected:** The trial balance reflects the entered opening figures and its debit and credit totals are equal.
- **Oracle:** Trial-balance totals vs the entered opening balances.

## Screen & field coverage

#### ACCOUNTING-T1-001 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/dashboard`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/dashboard`.
  3. Confirm the action buttons render: "More".
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-002 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/users/customers`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/users/customers`.
  3. Confirm the action buttons render: "More", "Import", "Export", "Search".
  4. Confirm the list columns render: Name, Company, Email, Phone, Actions.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-003 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/users/vendors`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/users/vendors`.
  3. Confirm the action buttons render: "More", "Import", "Export", "Search".
  4. Confirm the list columns render: Name, Company, Email, Phone, Actions.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-004 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/users/employees`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/users/employees`.
  3. Confirm the action buttons render: "More", "Show more details".
  4. Confirm the list columns render: Name, Designation, Department, Email, Phone.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-005 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/transactions/sales`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/transactions/sales`.
  3. Confirm the action buttons render: "More", "Submit".
  4. Confirm the list columns render: Voucher No., Type, Ref, Customer, Trn Date, Due Date, Balance, Total, Status.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-006 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/transactions/expenses`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/transactions/expenses`.
  3. Confirm the action buttons render: "More", "Submit".
  4. Confirm the list columns render: Voucher No., Type, Ref, People, Trn Date, Due Date, Due, Total, Status.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-007 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/transactions/purchases`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/transactions/purchases`.
  3. Confirm the action buttons render: "More", "Submit".
  4. Confirm the list columns render: Voucher No., Type, Ref, Customer, Trn Date, Due Date, Balance, Total, Status.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-008 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/transactions/journals`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/transactions/journals`.
  3. Confirm the action buttons render: "More".
  4. Confirm the list columns render: Voucher No., Date, Particulars, Amount.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-009 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/transactions/reimbursements`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/transactions/reimbursements`.
  3. Confirm the action buttons render: "More".
  4. Confirm the list columns render: Voucher No, People Name, Amount, Transaction Date, Voucher Type.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-010 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/products/product-service`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/products/product-service`.
  3. Confirm the action buttons render: "More", "Import", "Export", "Search".
  4. Confirm the list columns render: Product Name, Sale Price, Cost Price, Product Category, Tax Category, Product Type, Vendor, Actions.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-011 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/products/product-categories`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/products/product-categories`.
  3. Confirm the action buttons render: "More", "Save".
  4. Confirm the list columns render: Category Name, Actions.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-012 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/products/inventory`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/products/inventory`.
  3. Confirm the action buttons render: "More".
  4. Confirm the list columns render: Product Name, Sale Price, Cost Price, Stock, Product Category, Tax Category, Vendor.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-013 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/settings/charts`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/settings/charts`.
  3. Confirm the action buttons render: "More", "Show more details".
  4. Confirm the list columns render: Code, Name, Balance, Count, Actions.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-014 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/settings/banks`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/settings/banks`.
  3. Confirm the action buttons render: "More".
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-015 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/settings/taxes/tax-rates`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/settings/taxes/tax-rates`.
  3. Confirm the action buttons render: "More".
  4. Confirm the list columns render: Tax Zone Name, Actions.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-016 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/settings/taxes/tax-records`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/settings/taxes/tax-records`.
  3. Confirm the action buttons render: "More".
  4. Confirm the list columns render: Voucher No, Agency, Date, Amount, Actions.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-017 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/reports`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/reports`.
  3. Confirm the action buttons render: "More".
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-018 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/opening-balance`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/opening-balance`.
  3. Confirm the action buttons render: "More", "Add Agency", "Save".
  4. Confirm the list columns render: Agency, Debit, Credit, Account.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-019 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/invoices/new`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/invoices/new`.
  3. Confirm the action buttons render: "More", "Add Line", "Bold", "Italic", "Strikethrough", "Link".
  4. Confirm the list columns render: Product/Service, Qty, Unit Price, Amount, Tax.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-020 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/estimates/new`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/estimates/new`.
  3. Confirm the action buttons render: "More", "Add Line", "Bold", "Italic", "Strikethrough", "Link".
  4. Confirm the list columns render: Product/Service, Qty, Unit Price, Amount, Tax.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-021 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/payments/new`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/payments/new`.
  3. Confirm the action buttons render: "More", "Save", "Save and New", "Save as Draft".
  4. Confirm the list columns render: Voucher No, Due Date, Total, Balance, Amount.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-022 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/bills/new`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/bills/new`.
  3. Confirm the action buttons render: "More", "Add Line", "Save", "Save and New", "Save as Draft".
  4. Confirm the list columns render: SL No., Account, Description, Amount, Total.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-023 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/pay-bills/new`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/pay-bills/new`.
  3. Confirm the action buttons render: "More", "Save", "Save and New", "Save as Draft".
  4. Confirm the list columns render: Bill No, Due Date, Total, Due, Amount.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-024 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/purchase-orders/new`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/purchase-orders/new`.
  3. Confirm the action buttons render: "More", "Add Line", "Save", "Save and New", "Save as Draft".
  4. Confirm the list columns render: Product/Service, Qty, Unit Price, Amount, VAT.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-025 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/purchases/new`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/purchases/new`.
  3. Confirm the action buttons render: "More", "Add Line", "Save", "Save and New", "Save as Draft".
  4. Confirm the list columns render: Product/Service, Qty, Unit Price, Amount, VAT.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-026 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/pay-purchases/new`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/pay-purchases/new`.
  3. Confirm the action buttons render: "More", "Save", "Save and New", "Save as Draft".
  4. Confirm the list columns render: Voucher No, Due Date, Total, Balance, Amount.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-027 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/expenses/new`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/expenses/new`.
  3. Confirm the action buttons render: "More", "Add Line", "Save", "Save and New", "Save as Draft".
  4. Confirm the list columns render: SL No., Account, Description, Amount, Total.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-028 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/checks/new`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/checks/new`.
  3. Confirm the action buttons render: "More", "Add Line", "Save", "Save and New", "Save as Draft".
  4. Confirm the list columns render: SL No., Account, Description, Amount, Total.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-029 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/transactions/journals/new`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/transactions/journals/new`.
  3. Confirm the action buttons render: "More", "Add Line", "Save".
  4. Confirm the list columns render: SL No., Account, Particulars, Debit, Credit.
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-030 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/transactions/reimbursements/new`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/transactions/reimbursements/new`.
  3. Confirm the action buttons render: "More", "Save", "Save and New".
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-031 — Accounting loads and renders its controls

- **Surface:** `admin.php?page=erp-accounting#/settings/pay-tax`
- **Tags:** @tier1 @accounting @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-accounting#/settings/pay-tax`.
  3. Confirm the action buttons render: "More", "Save".
- **Expected:** The screen returns 200, renders its heading "Accounting", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### ACCOUNTING-T1-032 — Save `admin.php?page=erp-accounting#/dashboard` with every field completed

- **Surface:** `admin.php?page=erp-accounting#/dashboard`
- **Tags:** @tier1 @accounting @crud
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/dashboard`.
  2. Fill all 1 fields with valid data.
  3. Submit with "More".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### ACCOUNTING-T1-033 — `admin.php?page=erp-accounting#/dashboard` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-accounting#/dashboard`
- **Tags:** @tier1 @accounting @labels
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/dashboard`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `query_time` — type `select`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### ACCOUNTING-T1-034 — `query_time` offers its full option set

- **Surface:** `admin.php?page=erp-accounting#/dashboard`
- **Tags:** @tier1 @accounting @options
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/dashboard`.
  2. Read every option of `query_time`.
- **Expected:** The options are exactly: "This Month" (`this_month`), "Last Month" (`last_month`), "This Quarter" (`this_quarter`), "Last Quarter" (`last_quarter`), "This Year" (`this_year`), "Last Year" (`last_year`).
- **Oracle:** UI — `<option>` label/value pairs.

#### ACCOUNTING-T1-035 — Save `admin.php?page=erp-accounting#/invoices/new` with every field completed

- **Surface:** `admin.php?page=erp-accounting#/invoices/new`
- **Tags:** @tier1 @accounting @crud
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/invoices/new`.
  2. Fill all 5 fields with valid data (required: `href`).
  3. Submit with "Add Line".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### ACCOUNTING-T1-036 — `admin.php?page=erp-accounting#/invoices/new` renders all 5 fields with their labels

- **Surface:** `admin.php?page=erp-accounting#/invoices/new`
- **Tags:** @tier1 @accounting @labels
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/invoices/new`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 5 fields render:
      - `qty` ("Please search Oops! No elements found. List is empty.") — type `number`
      - `qty` ("Please search Oops! No elements found. List is empty.") — type `number`
      - `qty` ("Please search Oops! No elements found. List is empty.") — type `number`
      - `attachments[]` ("Select files") — type `file`
      - `href` — type `url`, **required**, placeholder "Enter a URL…"
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### ACCOUNTING-T1-037 — Save `admin.php?page=erp-accounting#/estimates/new` with every field completed

- **Surface:** `admin.php?page=erp-accounting#/estimates/new`
- **Tags:** @tier1 @accounting @crud
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/estimates/new`.
  2. Fill all 5 fields with valid data (required: `href`).
  3. Submit with "Add Line".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### ACCOUNTING-T1-038 — `admin.php?page=erp-accounting#/estimates/new` renders all 5 fields with their labels

- **Surface:** `admin.php?page=erp-accounting#/estimates/new`
- **Tags:** @tier1 @accounting @labels
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/estimates/new`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 5 fields render:
      - `qty` ("Please search Oops! No elements found. List is empty.") — type `number`
      - `qty` ("Please search Oops! No elements found. List is empty.") — type `number`
      - `qty` ("Please search Oops! No elements found. List is empty.") — type `number`
      - `attachments[]` ("Select files") — type `file`
      - `href` — type `url`, **required**, placeholder "Enter a URL…"
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### ACCOUNTING-T1-039 — Save `admin.php?page=erp-accounting#/payments/new` with every field completed

- **Surface:** `admin.php?page=erp-accounting#/payments/new`
- **Tags:** @tier1 @accounting @crud
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/payments/new`.
  2. Fill all 2 fields with valid data.
  3. Submit with "Save".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### ACCOUNTING-T1-040 — `admin.php?page=erp-accounting#/payments/new` renders all 2 fields with their labels

- **Surface:** `admin.php?page=erp-accounting#/payments/new`
- **Tags:** @tier1 @accounting @labels
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/payments/new`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 2 fields render:
      - `finalamount` — type `text`
      - `attachments[]` ("Select files") — type `file`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### ACCOUNTING-T1-041 — Save `admin.php?page=erp-accounting#/bills/new` with every field completed

- **Surface:** `admin.php?page=erp-accounting#/bills/new`
- **Tags:** @tier1 @accounting @crud
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/bills/new`.
  2. Fill all 5 fields with valid data.
  3. Submit with "Add Line".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### ACCOUNTING-T1-042 — `admin.php?page=erp-accounting#/bills/new` renders all 5 fields with their labels

- **Surface:** `admin.php?page=erp-accounting#/bills/new`
- **Tags:** @tier1 @accounting @labels
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/bills/new`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 5 fields render:
      - `amount` — type `text`
      - `amount` — type `text`
      - `amount` — type `text`
      - `finalamount` — type `text`
      - `attachments[]` ("Select files") — type `file`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### ACCOUNTING-T1-043 — Save `admin.php?page=erp-accounting#/pay-bills/new` with every field completed

- **Surface:** `admin.php?page=erp-accounting#/pay-bills/new`
- **Tags:** @tier1 @accounting @crud
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/pay-bills/new`.
  2. Fill all 1 fields with valid data.
  3. Submit with "Save".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### ACCOUNTING-T1-044 — `admin.php?page=erp-accounting#/pay-bills/new` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-accounting#/pay-bills/new`
- **Tags:** @tier1 @accounting @labels
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/pay-bills/new`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `attachments[]` ("Select files") — type `file`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### ACCOUNTING-T1-045 — Save `admin.php?page=erp-accounting#/purchase-orders/new` with every field completed

- **Surface:** `admin.php?page=erp-accounting#/purchase-orders/new`
- **Tags:** @tier1 @accounting @crud
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/purchase-orders/new`.
  2. Fill all 4 fields with valid data.
  3. Submit with "Add Line".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### ACCOUNTING-T1-046 — `admin.php?page=erp-accounting#/purchase-orders/new` renders all 4 fields with their labels

- **Surface:** `admin.php?page=erp-accounting#/purchase-orders/new`
- **Tags:** @tier1 @accounting @labels
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/purchase-orders/new`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 4 fields render:
      - `qty` ("Please search Oops! No elements found. List is empty.") — type `number`
      - `qty` ("Please search Oops! No elements found. List is empty.") — type `number`
      - `qty` ("Please search Oops! No elements found. List is empty.") — type `number`
      - `attachments[]` ("Select files") — type `file`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### ACCOUNTING-T1-047 — Save `admin.php?page=erp-accounting#/purchases/new` with every field completed

- **Surface:** `admin.php?page=erp-accounting#/purchases/new`
- **Tags:** @tier1 @accounting @crud
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/purchases/new`.
  2. Fill all 4 fields with valid data.
  3. Submit with "Add Line".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### ACCOUNTING-T1-048 — `admin.php?page=erp-accounting#/purchases/new` renders all 4 fields with their labels

- **Surface:** `admin.php?page=erp-accounting#/purchases/new`
- **Tags:** @tier1 @accounting @labels
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/purchases/new`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 4 fields render:
      - `qty` ("Please search Oops! No elements found. List is empty.") — type `number`
      - `qty` ("Please search Oops! No elements found. List is empty.") — type `number`
      - `qty` ("Please search Oops! No elements found. List is empty.") — type `number`
      - `attachments[]` ("Select files") — type `file`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### ACCOUNTING-T1-049 — Save `admin.php?page=erp-accounting#/pay-purchases/new` with every field completed

- **Surface:** `admin.php?page=erp-accounting#/pay-purchases/new`
- **Tags:** @tier1 @accounting @crud
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/pay-purchases/new`.
  2. Fill all 2 fields with valid data.
  3. Submit with "Save".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### ACCOUNTING-T1-050 — `admin.php?page=erp-accounting#/pay-purchases/new` renders all 2 fields with their labels

- **Surface:** `admin.php?page=erp-accounting#/pay-purchases/new`
- **Tags:** @tier1 @accounting @labels
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/pay-purchases/new`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 2 fields render:
      - `finalamount` — type `text`
      - `attachments[]` ("Select files") — type `file`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### ACCOUNTING-T1-051 — Save `admin.php?page=erp-accounting#/expenses/new` with every field completed

- **Surface:** `admin.php?page=erp-accounting#/expenses/new`
- **Tags:** @tier1 @accounting @crud
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/expenses/new`.
  2. Fill all 5 fields with valid data.
  3. Submit with "Add Line".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### ACCOUNTING-T1-052 — `admin.php?page=erp-accounting#/expenses/new` renders all 5 fields with their labels

- **Surface:** `admin.php?page=erp-accounting#/expenses/new`
- **Tags:** @tier1 @accounting @labels
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/expenses/new`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 5 fields render:
      - `amount` — type `text`
      - `amount` — type `text`
      - `amount` — type `text`
      - `finalamount` — type `text`
      - `attachments[]` ("Select files") — type `file`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### ACCOUNTING-T1-053 — Save `admin.php?page=erp-accounting#/checks/new` with every field completed

- **Surface:** `admin.php?page=erp-accounting#/checks/new`
- **Tags:** @tier1 @accounting @crud
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/checks/new`.
  2. Fill all 5 fields with valid data.
  3. Submit with "Add Line".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### ACCOUNTING-T1-054 — `admin.php?page=erp-accounting#/checks/new` renders all 5 fields with their labels

- **Surface:** `admin.php?page=erp-accounting#/checks/new`
- **Tags:** @tier1 @accounting @labels
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/checks/new`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 5 fields render:
      - `amount` — type `number`
      - `amount` — type `number`
      - `amount` — type `number`
      - `finalamount` — type `text`
      - `attachments[]` ("Select files") — type `file`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### ACCOUNTING-T1-055 — Save `admin.php?page=erp-accounting#/transactions/journals/new` with every field completed

- **Surface:** `admin.php?page=erp-accounting#/transactions/journals/new`
- **Tags:** @tier1 @accounting @crud
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/transactions/journals/new`.
  2. Fill all 1 fields with valid data.
  3. Submit with "Add Line".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### ACCOUNTING-T1-056 — `admin.php?page=erp-accounting#/transactions/journals/new` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-accounting#/transactions/journals/new`
- **Tags:** @tier1 @accounting @labels
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/transactions/journals/new`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `attachments[]` ("Select files") — type `file`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.
