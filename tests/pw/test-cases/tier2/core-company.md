# Tier 2 — core-company

Edge cases — boundaries, optional-field omission, empty states, unusual but legal input.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**14 cases** (14 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### CORE_COMPANY-T2-001 — `name` ("Enter company name here") accepts its edge values

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier2 @core-company @edge
- **Steps:**
  1. Open `admin.php?page=erp-company&action=edit`.
  2. Save with `name` ("Enter company name here") set to a single character.
  3. Save with `name` ("Enter company name here") set to a 255-character value.
  4. Save with `name` ("Enter company name here") set to a value with leading and trailing whitespace.
  5. Save with `name` ("Enter company name here") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_COMPANY-T2-002 — `address[address_1]` ("Address Line 1") accepts its edge values

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier2 @core-company @edge
- **Steps:**
  1. Open `admin.php?page=erp-company&action=edit`.
  2. Save with `address[address_1]` ("Address Line 1") set to a single character.
  3. Save with `address[address_1]` ("Address Line 1") set to a 255-character value.
  4. Save with `address[address_1]` ("Address Line 1") set to a value with leading and trailing whitespace.
  5. Save with `address[address_1]` ("Address Line 1") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_COMPANY-T2-003 — `address[address_2]` ("Address Line 2") accepts its edge values

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier2 @core-company @edge
- **Steps:**
  1. Open `admin.php?page=erp-company&action=edit`.
  2. Save with `address[address_2]` ("Address Line 2") set to a single character.
  3. Save with `address[address_2]` ("Address Line 2") set to a 255-character value.
  4. Save with `address[address_2]` ("Address Line 2") set to a value with leading and trailing whitespace.
  5. Save with `address[address_2]` ("Address Line 2") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_COMPANY-T2-004 — `address[city]` ("City") accepts its edge values

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier2 @core-company @edge
- **Steps:**
  1. Open `admin.php?page=erp-company&action=edit`.
  2. Save with `address[city]` ("City") set to a single character.
  3. Save with `address[city]` ("City") set to a 255-character value.
  4. Save with `address[city]` ("City") set to a value with leading and trailing whitespace.
  5. Save with `address[city]` ("City") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_COMPANY-T2-005 — `address[country]` ("Country") accepts its edge values

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier2 @core-company @edge
- **Steps:**
  1. Open `admin.php?page=erp-company&action=edit`.
  2. Save with `address[country]` ("Country") set to the first real option.
  3. Save with `address[country]` ("Country") set to the last option.
  4. Save with `address[country]` ("Country") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_COMPANY-T2-006 — `address[state]` ("Province / State") accepts its edge values

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier2 @core-company @edge
- **Steps:**
  1. Open `admin.php?page=erp-company&action=edit`.
  2. Save with `address[state]` ("Province / State") set to the first real option.
  3. Save with `address[state]` ("Province / State") set to the last option.
  4. Save with `address[state]` ("Province / State") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_COMPANY-T2-007 — `address[zip]` ("Postal / Zip Code") accepts its edge values

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier2 @core-company @edge
- **Steps:**
  1. Open `admin.php?page=erp-company&action=edit`.
  2. Save with `address[zip]` ("Postal / Zip Code") set to a single character.
  3. Save with `address[zip]` ("Postal / Zip Code") set to a 255-character value.
  4. Save with `address[zip]` ("Postal / Zip Code") set to a value with leading and trailing whitespace.
  5. Save with `address[zip]` ("Postal / Zip Code") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_COMPANY-T2-008 — `phone` ("Phone") accepts its edge values

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier2 @core-company @edge
- **Steps:**
  1. Open `admin.php?page=erp-company&action=edit`.
  2. Save with `phone` ("Phone") set to a single character.
  3. Save with `phone` ("Phone") set to a 255-character value.
  4. Save with `phone` ("Phone") set to a value with leading and trailing whitespace.
  5. Save with `phone` ("Phone") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_COMPANY-T2-009 — `fax` ("Fax") accepts its edge values

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier2 @core-company @edge
- **Steps:**
  1. Open `admin.php?page=erp-company&action=edit`.
  2. Save with `fax` ("Fax") set to a single character.
  3. Save with `fax` ("Fax") set to a 255-character value.
  4. Save with `fax` ("Fax") set to a value with leading and trailing whitespace.
  5. Save with `fax` ("Fax") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_COMPANY-T2-010 — `mobile` ("Mobile") accepts its edge values

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier2 @core-company @edge
- **Steps:**
  1. Open `admin.php?page=erp-company&action=edit`.
  2. Save with `mobile` ("Mobile") set to a single character.
  3. Save with `mobile` ("Mobile") set to a 255-character value.
  4. Save with `mobile` ("Mobile") set to a value with leading and trailing whitespace.
  5. Save with `mobile` ("Mobile") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_COMPANY-T2-011 — `website` ("Website") accepts its edge values

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier2 @core-company @edge
- **Steps:**
  1. Open `admin.php?page=erp-company&action=edit`.
  2. Save with `website` ("Website") set to a scheme-less host (`example.test`).
  3. Save with `website` ("Website") set to an `https://` URL with a query string and a fragment.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_COMPANY-T2-012 — `business_type` ("What sort of business do you do?") accepts its edge values

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier2 @core-company @edge
- **Steps:**
  1. Open `admin.php?page=erp-company&action=edit`.
  2. Save with `business_type` ("What sort of business do you do?") set to the first real option.
  3. Save with `business_type` ("What sort of business do you do?") set to the last option.
  4. Save with `business_type` ("What sort of business do you do?") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_COMPANY-T2-013 — `save` accepts its edge values

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier2 @core-company @edge
- **Steps:**
  1. Open `admin.php?page=erp-company&action=edit`.
  2. Save with `save` set to a single character.
  3. Save with `save` set to a 255-character value.
  4. Save with `save` set to a value with leading and trailing whitespace.
  5. Save with `save` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CORE_COMPANY-T2-014 — `admin.php?page=erp-company&action=edit` saves with only its required fields

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier2 @core-company @edge
- **Steps:**
  1. Open `admin.php?page=erp-company&action=edit`.
  2. Fill only: `address[country]` ("Country").
  3. Submit.
- **Expected:** The record saves. The 12 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.
