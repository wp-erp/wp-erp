# Tier 2 — core-tools

Edge cases — boundaries, optional-field omission, empty states, unusual but legal input.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**33 cases** (32 derived from the harness, 1 hand-written business flows).

## Business flows

#### CORE_TOOLS-F2-001 — The audit log records an ERP change

- **Surface:** `admin.php?page=erp-tools&tab=audit-log`
- **Tags:** @tier2 @core-tools @flow
- **Steps:**
  1. Note the audit-log row count.
  2. Make one auditable change (edit an employee).
  3. Reload the audit log.
- **Expected:** Exactly one new entry naming the change, the actor and the time.
- **Oracle:** Audit-log row count and content.

## Screen & field coverage

#### CORE_TOOLS-T2-001 — `menu[]` ("Dashboard") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `menu[]` ("Dashboard") set to checked.
  3. Save with `menu[]` ("Dashboard") set to unchecked.
  4. Save with `menu[]` ("Dashboard") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-002 — `menu[]` ("Posts") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `menu[]` ("Posts") set to checked.
  3. Save with `menu[]` ("Posts") set to unchecked.
  4. Save with `menu[]` ("Posts") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-003 — `menu[]` ("Media") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `menu[]` ("Media") set to checked.
  3. Save with `menu[]` ("Media") set to unchecked.
  4. Save with `menu[]` ("Media") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-004 — `menu[]` ("Pages") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `menu[]` ("Pages") set to checked.
  3. Save with `menu[]` ("Pages") set to unchecked.
  4. Save with `menu[]` ("Pages") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-005 — `menu[]` ("Comments") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `menu[]` ("Comments") set to checked.
  3. Save with `menu[]` ("Comments") set to unchecked.
  4. Save with `menu[]` ("Comments") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-006 — `menu[]` ("Email Log") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `menu[]` ("Email Log") set to checked.
  3. Save with `menu[]` ("Email Log") set to unchecked.
  4. Save with `menu[]` ("Email Log") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-007 — `menu[]` ("Training") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `menu[]` ("Training") set to checked.
  3. Save with `menu[]` ("Training") set to unchecked.
  4. Save with `menu[]` ("Training") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-008 — `menu[]` ("WooCommerce") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `menu[]` ("WooCommerce") set to checked.
  3. Save with `menu[]` ("WooCommerce") set to unchecked.
  4. Save with `menu[]` ("WooCommerce") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-009 — `menu[]` ("Products") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `menu[]` ("Products") set to checked.
  3. Save with `menu[]` ("Products") set to unchecked.
  4. Save with `menu[]` ("Products") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-010 — `menu[]` ("Payments") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `menu[]` ("Payments") set to checked.
  3. Save with `menu[]` ("Payments") set to unchecked.
  4. Save with `menu[]` ("Payments") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-011 — `menu[]` ("Analytics") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `menu[]` ("Analytics") set to checked.
  3. Save with `menu[]` ("Analytics") set to unchecked.
  4. Save with `menu[]` ("Analytics") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-012 — `menu[]` ("Marketing") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `menu[]` ("Marketing") set to checked.
  3. Save with `menu[]` ("Marketing") set to unchecked.
  4. Save with `menu[]` ("Marketing") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-013 — `menu[]` ("Appearance") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `menu[]` ("Appearance") set to checked.
  3. Save with `menu[]` ("Appearance") set to unchecked.
  4. Save with `menu[]` ("Appearance") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-014 — `menu[]` ("Plugins") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `menu[]` ("Plugins") set to checked.
  3. Save with `menu[]` ("Plugins") set to unchecked.
  4. Save with `menu[]` ("Plugins") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-015 — `menu[]` ("Users") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `menu[]` ("Users") set to checked.
  3. Save with `menu[]` ("Users") set to unchecked.
  4. Save with `menu[]` ("Users") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-016 — `menu[]` ("Tools") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `menu[]` ("Tools") set to checked.
  3. Save with `menu[]` ("Tools") set to unchecked.
  4. Save with `menu[]` ("Tools") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-017 — `menu[]` ("Settings") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `menu[]` ("Settings") set to checked.
  3. Save with `menu[]` ("Settings") set to unchecked.
  4. Save with `menu[]` ("Settings") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-018 — `admin_menu[]` ("WordPress Logo") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `admin_menu[]` ("WordPress Logo") set to checked.
  3. Save with `admin_menu[]` ("WordPress Logo") set to unchecked.
  4. Save with `admin_menu[]` ("WordPress Logo") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-019 — `admin_menu[]` ("Site Name") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `admin_menu[]` ("Site Name") set to checked.
  3. Save with `admin_menu[]` ("Site Name") set to unchecked.
  4. Save with `admin_menu[]` ("Site Name") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-020 — `admin_menu[]` ("Updates") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `admin_menu[]` ("Updates") set to checked.
  3. Save with `admin_menu[]` ("Updates") set to unchecked.
  4. Save with `admin_menu[]` ("Updates") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-021 — `admin_menu[]` ("Comments") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `admin_menu[]` ("Comments") set to checked.
  3. Save with `admin_menu[]` ("Comments") set to unchecked.
  4. Save with `admin_menu[]` ("Comments") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-022 — `admin_menu[]` ("New Posts") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `admin_menu[]` ("New Posts") set to checked.
  3. Save with `admin_menu[]` ("New Posts") set to unchecked.
  4. Save with `admin_menu[]` ("New Posts") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-023 — `admin_menu[]` ("New Transaction") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `admin_menu[]` ("New Transaction") set to checked.
  3. Save with `admin_menu[]` ("New Transaction") set to unchecked.
  4. Save with `admin_menu[]` ("New Transaction") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-024 — `admin_menu[]` ("WP ERP") accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `admin_menu[]` ("WP ERP") set to checked.
  3. Save with `admin_menu[]` ("WP ERP") set to unchecked.
  4. Save with `admin_menu[]` ("WP ERP") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-025 — `erp_admin_menu` accepts its edge values

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Save with `erp_admin_menu` set to a single character.
  3. Save with `erp_admin_menu` set to a 255-character value.
  4. Save with `erp_admin_menu` set to a value with leading and trailing whitespace.
  5. Save with `erp_admin_menu` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-026 — `admin.php?page=erp-tools` saves with only its required fields

- **Surface:** `admin.php?page=erp-tools`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 25 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### CORE_TOOLS-T2-027 — `to` ("To *") accepts its edge values

- **Surface:** `admin.php?page=erp-tools&tab=misc`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools&tab=misc`.
  2. Save with `to` ("To *") set to an address with a plus tag (`a+b@example.test`).
  3. Save with `to` ("To *") set to an address at the 254-character RFC limit.
  4. Save with `to` ("To *") set to unicode in the local part.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-028 — `from` ("From") accepts its edge values

- **Surface:** `admin.php?page=erp-tools&tab=misc`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools&tab=misc`.
  2. Save with `from` ("From") set to a single character.
  3. Save with `from` ("From") set to a 255-character value.
  4. Save with `from` ("From") set to a value with leading and trailing whitespace.
  5. Save with `from` ("From") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-029 — `body` ("Message") accepts its edge values

- **Surface:** `admin.php?page=erp-tools&tab=misc`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools&tab=misc`.
  2. Save with `body` ("Message") set to a 5,000-character body.
  3. Save with `body` ("Message") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-030 — `erp_send_test_email` accepts its edge values

- **Surface:** `admin.php?page=erp-tools&tab=misc`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools&tab=misc`.
  2. Save with `erp_send_test_email` set to a single character.
  3. Save with `erp_send_test_email` set to a 255-character value.
  4. Save with `erp_send_test_email` set to a value with leading and trailing whitespace.
  5. Save with `erp_send_test_email` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_TOOLS-T2-031 — `admin.php?page=erp-tools&tab=misc` saves with only its required fields

- **Surface:** `admin.php?page=erp-tools&tab=misc`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools&tab=misc`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 4 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### CORE_TOOLS-T2-032 — `erp_reset_confirmation` accepts its edge values

- **Surface:** `admin.php?page=erp-tools&tab=danger-zone`
- **Tags:** @tier2 @core-tools @edge
- **Steps:**
  1. Open `admin.php?page=erp-tools&tab=danger-zone`.
  2. Save with `erp_reset_confirmation` set to a single character.
  3. Save with `erp_reset_confirmation` set to a 255-character value.
  4. Save with `erp_reset_confirmation` set to a value with leading and trailing whitespace.
  5. Save with `erp_reset_confirmation` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.
