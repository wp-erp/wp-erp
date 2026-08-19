# Tier 3 — accounting

Negative cases — required-field enforcement, malformed input, injection, and per-role authorization.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**38 cases** (38 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### ACCOUNTING-T3-001 — `href` is enforced as required

- **Surface:** `admin.php?page=erp-accounting#/invoices/new`
- **Tags:** @tier3 @accounting @validation
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/invoices/new`.
  2. Fill every field EXCEPT `href`.
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### ACCOUNTING-T3-002 — `admin.php?page=erp-accounting#/invoices/new` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-accounting#/invoices/new`
- **Tags:** @tier3 @accounting @security
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/invoices/new`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### ACCOUNTING-T3-003 — `qty` ("Please search Oops! No elements found. List is empty.") rejects a malformed value

- **Surface:** `admin.php?page=erp-accounting#/invoices/new`
- **Tags:** @tier3 @accounting @validation
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/invoices/new`.
  2. Set `qty` ("Please search Oops! No elements found. List is empty.") to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### ACCOUNTING-T3-004 — `qty` ("Please search Oops! No elements found. List is empty.") rejects a malformed value

- **Surface:** `admin.php?page=erp-accounting#/invoices/new`
- **Tags:** @tier3 @accounting @validation
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/invoices/new`.
  2. Set `qty` ("Please search Oops! No elements found. List is empty.") to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### ACCOUNTING-T3-005 — `qty` ("Please search Oops! No elements found. List is empty.") rejects a malformed value

- **Surface:** `admin.php?page=erp-accounting#/invoices/new`
- **Tags:** @tier3 @accounting @validation
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/invoices/new`.
  2. Set `qty` ("Please search Oops! No elements found. List is empty.") to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### ACCOUNTING-T3-006 — `href` rejects a malformed value

- **Surface:** `admin.php?page=erp-accounting#/invoices/new`
- **Tags:** @tier3 @accounting @validation
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/invoices/new`.
  2. Set `href` to a value of the wrong shape (e.g. `not-an-url`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### ACCOUNTING-T3-007 — `href` is enforced as required

- **Surface:** `admin.php?page=erp-accounting#/estimates/new`
- **Tags:** @tier3 @accounting @validation
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/estimates/new`.
  2. Fill every field EXCEPT `href`.
  3. Submit.
- **Expected:** Submission is blocked with a message identifying that field. No record is created.
- **Oracle:** UI validation message + REST/DB row count unchanged.

#### ACCOUNTING-T3-008 — `admin.php?page=erp-accounting#/estimates/new` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-accounting#/estimates/new`
- **Tags:** @tier3 @accounting @security
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/estimates/new`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### ACCOUNTING-T3-009 — `qty` ("Please search Oops! No elements found. List is empty.") rejects a malformed value

- **Surface:** `admin.php?page=erp-accounting#/estimates/new`
- **Tags:** @tier3 @accounting @validation
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/estimates/new`.
  2. Set `qty` ("Please search Oops! No elements found. List is empty.") to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### ACCOUNTING-T3-010 — `qty` ("Please search Oops! No elements found. List is empty.") rejects a malformed value

- **Surface:** `admin.php?page=erp-accounting#/estimates/new`
- **Tags:** @tier3 @accounting @validation
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/estimates/new`.
  2. Set `qty` ("Please search Oops! No elements found. List is empty.") to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### ACCOUNTING-T3-011 — `qty` ("Please search Oops! No elements found. List is empty.") rejects a malformed value

- **Surface:** `admin.php?page=erp-accounting#/estimates/new`
- **Tags:** @tier3 @accounting @validation
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/estimates/new`.
  2. Set `qty` ("Please search Oops! No elements found. List is empty.") to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### ACCOUNTING-T3-012 — `href` rejects a malformed value

- **Surface:** `admin.php?page=erp-accounting#/estimates/new`
- **Tags:** @tier3 @accounting @validation
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/estimates/new`.
  2. Set `href` to a value of the wrong shape (e.g. `not-an-url`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### ACCOUNTING-T3-013 — `admin.php?page=erp-accounting#/payments/new` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-accounting#/payments/new`
- **Tags:** @tier3 @accounting @security
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/payments/new`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### ACCOUNTING-T3-014 — `admin.php?page=erp-accounting#/bills/new` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-accounting#/bills/new`
- **Tags:** @tier3 @accounting @security
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/bills/new`.
  2. Set each of 4 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### ACCOUNTING-T3-015 — `qty` ("Please search Oops! No elements found. List is empty.") rejects a malformed value

- **Surface:** `admin.php?page=erp-accounting#/purchase-orders/new`
- **Tags:** @tier3 @accounting @validation
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/purchase-orders/new`.
  2. Set `qty` ("Please search Oops! No elements found. List is empty.") to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### ACCOUNTING-T3-016 — `qty` ("Please search Oops! No elements found. List is empty.") rejects a malformed value

- **Surface:** `admin.php?page=erp-accounting#/purchase-orders/new`
- **Tags:** @tier3 @accounting @validation
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/purchase-orders/new`.
  2. Set `qty` ("Please search Oops! No elements found. List is empty.") to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### ACCOUNTING-T3-017 — `qty` ("Please search Oops! No elements found. List is empty.") rejects a malformed value

- **Surface:** `admin.php?page=erp-accounting#/purchase-orders/new`
- **Tags:** @tier3 @accounting @validation
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/purchase-orders/new`.
  2. Set `qty` ("Please search Oops! No elements found. List is empty.") to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### ACCOUNTING-T3-018 — `qty` ("Please search Oops! No elements found. List is empty.") rejects a malformed value

- **Surface:** `admin.php?page=erp-accounting#/purchases/new`
- **Tags:** @tier3 @accounting @validation
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/purchases/new`.
  2. Set `qty` ("Please search Oops! No elements found. List is empty.") to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### ACCOUNTING-T3-019 — `qty` ("Please search Oops! No elements found. List is empty.") rejects a malformed value

- **Surface:** `admin.php?page=erp-accounting#/purchases/new`
- **Tags:** @tier3 @accounting @validation
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/purchases/new`.
  2. Set `qty` ("Please search Oops! No elements found. List is empty.") to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### ACCOUNTING-T3-020 — `qty` ("Please search Oops! No elements found. List is empty.") rejects a malformed value

- **Surface:** `admin.php?page=erp-accounting#/purchases/new`
- **Tags:** @tier3 @accounting @validation
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/purchases/new`.
  2. Set `qty` ("Please search Oops! No elements found. List is empty.") to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### ACCOUNTING-T3-021 — `admin.php?page=erp-accounting#/pay-purchases/new` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-accounting#/pay-purchases/new`
- **Tags:** @tier3 @accounting @security
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/pay-purchases/new`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### ACCOUNTING-T3-022 — `admin.php?page=erp-accounting#/expenses/new` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-accounting#/expenses/new`
- **Tags:** @tier3 @accounting @security
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/expenses/new`.
  2. Set each of 4 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### ACCOUNTING-T3-023 — `admin.php?page=erp-accounting#/checks/new` neutralises script and HTML in its text fields

- **Surface:** `admin.php?page=erp-accounting#/checks/new`
- **Tags:** @tier3 @accounting @security
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/checks/new`.
  2. Set each of 1 text fields to `<script>alert(1)</script>` and to `"><img src=x onerror=alert(1)>`.
  3. Save, then open the list and the detail view.
- **Expected:** The payload is escaped on output — it renders as literal text, never executes. No dialog appears and no console error is raised.
- **Oracle:** UI — rendered text is escaped; a page dialog would fail the test.

#### ACCOUNTING-T3-024 — `amount` rejects a malformed value

- **Surface:** `admin.php?page=erp-accounting#/checks/new`
- **Tags:** @tier3 @accounting @validation
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/checks/new`.
  2. Set `amount` to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### ACCOUNTING-T3-025 — `amount` rejects a malformed value

- **Surface:** `admin.php?page=erp-accounting#/checks/new`
- **Tags:** @tier3 @accounting @validation
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/checks/new`.
  2. Set `amount` to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### ACCOUNTING-T3-026 — `amount` rejects a malformed value

- **Surface:** `admin.php?page=erp-accounting#/checks/new`
- **Tags:** @tier3 @accounting @validation
- **Steps:**
  1. Open `admin.php?page=erp-accounting#/checks/new`.
  2. Set `amount` to a value of the wrong shape (e.g. `not-an-number`).
  3. Submit.
- **Expected:** The value is rejected with a specific message. The response is a 4xx, never a 500, and no partial record is written.
- **Oracle:** UI message + REST status code + DB row count.

#### ACCOUNTING-T3-027 — `admin.php?page=erp-accounting#/dashboard` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-accounting#/dashboard`
- **Tags:** @tier3 @accounting @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-accounting#/dashboard` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### ACCOUNTING-T3-028 — `admin.php?page=erp-accounting#/users/customers` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-accounting#/users/customers`
- **Tags:** @tier3 @accounting @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-accounting#/users/customers` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### ACCOUNTING-T3-029 — `admin.php?page=erp-accounting#/users/vendors` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-accounting#/users/vendors`
- **Tags:** @tier3 @accounting @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-accounting#/users/vendors` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### ACCOUNTING-T3-030 — `admin.php?page=erp-accounting#/users/employees` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-accounting#/users/employees`
- **Tags:** @tier3 @accounting @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-accounting#/users/employees` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### ACCOUNTING-T3-031 — `admin.php?page=erp-accounting#/transactions/sales` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-accounting#/transactions/sales`
- **Tags:** @tier3 @accounting @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-accounting#/transactions/sales` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### ACCOUNTING-T3-032 — `admin.php?page=erp-accounting#/transactions/expenses` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-accounting#/transactions/expenses`
- **Tags:** @tier3 @accounting @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-accounting#/transactions/expenses` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### ACCOUNTING-T3-033 — `admin.php?page=erp-accounting#/transactions/purchases` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-accounting#/transactions/purchases`
- **Tags:** @tier3 @accounting @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-accounting#/transactions/purchases` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### ACCOUNTING-T3-034 — `admin.php?page=erp-accounting#/transactions/journals` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-accounting#/transactions/journals`
- **Tags:** @tier3 @accounting @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-accounting#/transactions/journals` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### ACCOUNTING-T3-035 — `admin.php?page=erp-accounting#/transactions/reimbursements` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-accounting#/transactions/reimbursements`
- **Tags:** @tier3 @accounting @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-accounting#/transactions/reimbursements` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### ACCOUNTING-T3-036 — `admin.php?page=erp-accounting#/products/product-service` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-accounting#/products/product-service`
- **Tags:** @tier3 @accounting @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-accounting#/products/product-service` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### ACCOUNTING-T3-037 — `admin.php?page=erp-accounting#/products/product-categories` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-accounting#/products/product-categories`
- **Tags:** @tier3 @accounting @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-accounting#/products/product-categories` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.

#### ACCOUNTING-T3-038 — `admin.php?page=erp-accounting#/products/inventory` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-accounting#/products/inventory`
- **Tags:** @tier3 @accounting @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-accounting#/products/inventory` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.
