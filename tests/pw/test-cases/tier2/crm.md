# Tier 2 — crm

Edge cases — boundaries, optional-field omission, empty states, unusual but legal input.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**91 cases** (89 derived from the harness, 2 hand-written business flows).

## Business flows

#### CRM-F2-001 — Contact import creates no duplicates on a second run

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Import contacts from a CSV (`tmpl-erp-crm-import-customer`).
  2. Import the identical file again.
- **Expected:** The second import creates no duplicate contacts and says how many rows it skipped.
- **Oracle:** Contact count after each import.

#### CRM-F2-002 — Converting a contact to a WordPress user

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Use `tmpl-erp-make-wp-user` on a contact whose e-mail has no WP user.
  2. Repeat on a contact whose e-mail already has one.
- **Expected:** The first creates a user in the chosen role; the second reports the existing user instead of creating a duplicate.
- **Oracle:** `wp_users` rows for both addresses.

## Screen & field coverage

#### CRM-T2-001 — `contact[main][first_name]` ("First Name *") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[main][first_name]` ("First Name *") set to a single character.
  3. Save with `contact[main][first_name]` ("First Name *") set to a 255-character value.
  4. Save with `contact[main][first_name]` ("First Name *") set to a value with leading and trailing whitespace.
  5. Save with `contact[main][first_name]` ("First Name *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-002 — `contact[main][last_name]` ("Last Name") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[main][last_name]` ("Last Name") set to a single character.
  3. Save with `contact[main][last_name]` ("Last Name") set to a 255-character value.
  4. Save with `contact[main][last_name]` ("Last Name") set to a value with leading and trailing whitespace.
  5. Save with `contact[main][last_name]` ("Last Name") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-003 — `contact[main][company]` ("Company Name *") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[main][company]` ("Company Name *") set to a single character.
  3. Save with `contact[main][company]` ("Company Name *") set to a 255-character value.
  4. Save with `contact[main][company]` ("Company Name *") set to a value with leading and trailing whitespace.
  5. Save with `contact[main][company]` ("Company Name *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-004 — `contact[main][email]` ("Email *") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[main][email]` ("Email *") set to an address with a plus tag (`a+b@example.test`).
  3. Save with `contact[main][email]` ("Email *") set to an address at the 254-character RFC limit.
  4. Save with `contact[main][email]` ("Email *") set to unicode in the local part.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-005 — `contact[main][phone]` ("Phone Number") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[main][phone]` ("Phone Number") set to a single character.
  3. Save with `contact[main][phone]` ("Phone Number") set to a 255-character value.
  4. Save with `contact[main][phone]` ("Phone Number") set to a value with leading and trailing whitespace.
  5. Save with `contact[main][phone]` ("Phone Number") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-006 — `contact[meta][life_stage]` ("Life Stage *") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[meta][life_stage]` ("Life Stage *") set to the first real option.
  3. Save with `contact[meta][life_stage]` ("Life Stage *") set to the last option.
  4. Save with `contact[meta][life_stage]` ("Life Stage *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-007 — `contact[meta][contact_owner]` ("Contact Owner *") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[meta][contact_owner]` ("Contact Owner *") set to the first real option.
  3. Save with `contact[meta][contact_owner]` ("Contact Owner *") set to the last option.
  4. Save with `contact[meta][contact_owner]` ("Contact Owner *") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-008 — `advanced_fields` ("Show Advanced Fields") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `advanced_fields` ("Show Advanced Fields") set to checked.
  3. Save with `advanced_fields` ("Show Advanced Fields") set to unchecked.
  4. Save with `advanced_fields` ("Show Advanced Fields") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-009 — `contact[meta][date_of_birth]` ("Date of Birth") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[meta][date_of_birth]` ("Date of Birth") set to today.
  3. Save with `contact[meta][date_of_birth]` ("Date of Birth") set to a leap day (29 Feb).
  4. Save with `contact[meta][date_of_birth]` ("Date of Birth") set to a date before the company financial-year start.
  5. Save with `contact[meta][date_of_birth]` ("Date of Birth") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-010 — `contact[meta][contact_age]` ("Age (years)") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[meta][contact_age]` ("Age (years)") set to 0.
  3. Save with `contact[meta][contact_age]` ("Age (years)") set to a negative value.
  4. Save with `contact[meta][contact_age]` ("Age (years)") set to a decimal where an integer is expected.
  5. Save with `contact[meta][contact_age]` ("Age (years)") set to a value far above any sane maximum (`999999999`).
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-011 — `contact[main][mobile]` ("Mobile") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[main][mobile]` ("Mobile") set to a single character.
  3. Save with `contact[main][mobile]` ("Mobile") set to a 255-character value.
  4. Save with `contact[main][mobile]` ("Mobile") set to a value with leading and trailing whitespace.
  5. Save with `contact[main][mobile]` ("Mobile") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-012 — `contact[main][website]` ("Website") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[main][website]` ("Website") set to a single character.
  3. Save with `contact[main][website]` ("Website") set to a 255-character value.
  4. Save with `contact[main][website]` ("Website") set to a value with leading and trailing whitespace.
  5. Save with `contact[main][website]` ("Website") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-013 — `contact[main][fax]` ("Fax Number") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[main][fax]` ("Fax Number") set to a single character.
  3. Save with `contact[main][fax]` ("Fax Number") set to a 255-character value.
  4. Save with `contact[main][fax]` ("Fax Number") set to a value with leading and trailing whitespace.
  5. Save with `contact[main][fax]` ("Fax Number") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-014 — `contact[main][street_1]` ("Address 1") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[main][street_1]` ("Address 1") set to a single character.
  3. Save with `contact[main][street_1]` ("Address 1") set to a 255-character value.
  4. Save with `contact[main][street_1]` ("Address 1") set to a value with leading and trailing whitespace.
  5. Save with `contact[main][street_1]` ("Address 1") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-015 — `contact[main][street_2]` ("Address 2") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[main][street_2]` ("Address 2") set to a single character.
  3. Save with `contact[main][street_2]` ("Address 2") set to a 255-character value.
  4. Save with `contact[main][street_2]` ("Address 2") set to a value with leading and trailing whitespace.
  5. Save with `contact[main][street_2]` ("Address 2") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-016 — `contact[main][city]` ("City") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[main][city]` ("City") set to a single character.
  3. Save with `contact[main][city]` ("City") set to a 255-character value.
  4. Save with `contact[main][city]` ("City") set to a value with leading and trailing whitespace.
  5. Save with `contact[main][city]` ("City") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-017 — `contact[main][country]` ("Country") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[main][country]` ("Country") set to the first real option.
  3. Save with `contact[main][country]` ("Country") set to the last option.
  4. Save with `contact[main][country]` ("Country") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-018 — `contact[main][state]` ("Province / State") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[main][state]` ("Province / State") set to the first real option.
  3. Save with `contact[main][state]` ("Province / State") set to the last option.
  4. Save with `contact[main][state]` ("Province / State") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-019 — `contact[main][postal_code]` ("Post Code/Zip Code") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[main][postal_code]` ("Post Code/Zip Code") set to a single character.
  3. Save with `contact[main][postal_code]` ("Post Code/Zip Code") set to a 255-character value.
  4. Save with `contact[main][postal_code]` ("Post Code/Zip Code") set to a value with leading and trailing whitespace.
  5. Save with `contact[main][postal_code]` ("Post Code/Zip Code") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-020 — `contact[meta][source]` ("Contact Source") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[meta][source]` ("Contact Source") set to the first real option.
  3. Save with `contact[meta][source]` ("Contact Source") set to the last option.
  4. Save with `contact[meta][source]` ("Contact Source") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-021 — `contact[main][other]` ("Others") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[main][other]` ("Others") set to a single character.
  3. Save with `contact[main][other]` ("Others") set to a 255-character value.
  4. Save with `contact[main][other]` ("Others") set to a value with leading and trailing whitespace.
  5. Save with `contact[main][other]` ("Others") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-022 — `contact[main][notes]` ("Notes") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[main][notes]` ("Notes") set to a 5,000-character body.
  3. Save with `contact[main][notes]` ("Notes") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-023 — `contact[social][facebook]` ("Facebook") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[social][facebook]` ("Facebook") set to a single character.
  3. Save with `contact[social][facebook]` ("Facebook") set to a 255-character value.
  4. Save with `contact[social][facebook]` ("Facebook") set to a value with leading and trailing whitespace.
  5. Save with `contact[social][facebook]` ("Facebook") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-024 — `contact[social][twitter]` ("Twitter") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[social][twitter]` ("Twitter") set to a single character.
  3. Save with `contact[social][twitter]` ("Twitter") set to a 255-character value.
  4. Save with `contact[social][twitter]` ("Twitter") set to a value with leading and trailing whitespace.
  5. Save with `contact[social][twitter]` ("Twitter") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-025 — `contact[social][googleplus]` ("Google Plus") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[social][googleplus]` ("Google Plus") set to a single character.
  3. Save with `contact[social][googleplus]` ("Google Plus") set to a 255-character value.
  4. Save with `contact[social][googleplus]` ("Google Plus") set to a value with leading and trailing whitespace.
  5. Save with `contact[social][googleplus]` ("Google Plus") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-026 — `contact[social][linkedin]` ("Linkedin") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Save with `contact[social][linkedin]` ("Linkedin") set to a single character.
  3. Save with `contact[social][linkedin]` ("Linkedin") set to a 255-character value.
  4. Save with `contact[social][linkedin]` ("Linkedin") set to a value with leading and trailing whitespace.
  5. Save with `contact[social][linkedin]` ("Linkedin") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-027 — modal form `tmpl-erp-crm-new-contact` saves with only its required fields

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Fill only: `contact[main][first_name]` ("First Name *"), `contact[main][company]` ("Company Name *"), `contact[main][email]` ("Email *"), `contact[meta][life_stage]` ("Life Stage *"), `contact[meta][contact_owner]` ("Contact Owner *").
  3. Submit.
- **Expected:** The record saves. The 21 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### CRM-T2-028 — `csv_file` ("CSV File *") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-import-customer`.
  2. Save with `csv_file` ("CSV File *") set to a single character.
  3. Save with `csv_file` ("CSV File *") set to a 255-character value.
  4. Save with `csv_file` ("CSV File *") set to a value with leading and trailing whitespace.
  5. Save with `csv_file` ("CSV File *") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-029 — `contact_owner` ("Contact Owner") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-import-customer`.
  2. Save with `contact_owner` ("Contact Owner") set to the first real option.
  3. Save with `contact_owner` ("Contact Owner") set to the last option.
  4. Save with `contact_owner` ("Contact Owner") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-030 — `life_stage` ("Life Stage") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-import-customer`.
  2. Save with `life_stage` ("Life Stage") set to the first real option.
  3. Save with `life_stage` ("Life Stage") set to the last option.
  4. Save with `life_stage` ("Life Stage") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-031 — `contact_group` ("Contact Group") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-import-customer`.
  2. Save with `contact_group` ("Contact Group") set to the first real option.
  3. Save with `contact_group` ("Contact Group") set to the last option.
  4. Save with `contact_group` ("Contact Group") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-032 — modal form `tmpl-erp-crm-import-customer` saves with only its required fields

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-import-customer`.
  2. Fill only: `csv_file` ("CSV File *").
  3. Submit.
- **Expected:** The record saves. The 3 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### CRM-T2-033 — `selecctall` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-export-customer`.
  2. Save with `selecctall` set to checked.
  3. Save with `selecctall` set to unchecked.
  4. Save with `selecctall` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-034 — modal form `tmpl-erp-crm-export-customer` saves with only its required fields

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-export-customer`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### CRM-T2-035 — `user_role` ("User Role") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-import-users`.
  2. Save with `user_role` ("User Role") set to the first real option.
  3. Save with `user_role` ("User Role") set to the last option.
  4. Save with `user_role` ("User Role") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-036 — `contact_owner` ("Contact Owner") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-import-users`.
  2. Save with `contact_owner` ("Contact Owner") set to the first real option.
  3. Save with `contact_owner` ("Contact Owner") set to the last option.
  4. Save with `contact_owner` ("Contact Owner") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-037 — `life_stage` ("Life Stage") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-import-users`.
  2. Save with `life_stage` ("Life Stage") set to the first real option.
  3. Save with `life_stage` ("Life Stage") set to the last option.
  4. Save with `life_stage` ("Life Stage") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-038 — `contact_group` ("Contact Group") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-import-users`.
  2. Save with `contact_group` ("Contact Group") set to the first real option.
  3. Save with `contact_group` ("Contact Group") set to the last option.
  4. Save with `contact_group` ("Contact Group") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-039 — modal form `tmpl-erp-crm-import-users` saves with only its required fields

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-import-users`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 4 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### CRM-T2-040 — `customeresc_attr_email` ("Enter your email") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-make-wp-user`.
  2. Save with `customeresc_attr_email` ("Enter your email") set to an address with a plus tag (`a+b@example.test`).
  3. Save with `customeresc_attr_email` ("Enter your email") set to an address at the 254-character RFC limit.
  4. Save with `customeresc_attr_email` ("Enter your email") set to unicode in the local part.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-041 — `customer_role` ("Role") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-make-wp-user`.
  2. Save with `customer_role` ("Role") set to the first real option.
  3. Save with `customer_role` ("Role") set to the last option.
  4. Save with `customer_role` ("Role") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-042 — `send_password_notification` ("Send password") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-make-wp-user`.
  2. Save with `send_password_notification` ("Send password") set to checked.
  3. Save with `send_password_notification` ("Send password") set to unchecked.
  4. Save with `send_password_notification` ("Send password") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-043 — modal form `tmpl-erp-make-wp-user` saves with only its required fields

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-make-wp-user`.
  2. Fill only: `customeresc_attr_email` ("Enter your email").
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### CRM-T2-044 — `schedule_title` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Save with `schedule_title` set to a single character.
  3. Save with `schedule_title` set to a 255-character value.
  4. Save with `schedule_title` set to a value with leading and trailing whitespace.
  5. Save with `schedule_title` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-045 — `user_id` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Save with `user_id` set to the first real option.
  3. Save with `user_id` set to the last option.
  4. Save with `user_id` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-046 — `start_date` ("Start") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Save with `start_date` ("Start") set to today.
  3. Save with `start_date` ("Start") set to a leap day (29 Feb).
  4. Save with `start_date` ("Start") set to a date before the company financial-year start.
  5. Save with `start_date` ("Start") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-047 — `start_time` ("Start") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Save with `start_time` ("Start") set to a single character.
  3. Save with `start_time` ("Start") set to a 255-character value.
  4. Save with `start_time` ("Start") set to a value with leading and trailing whitespace.
  5. Save with `start_time` ("Start") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-048 — `end_date` ("End") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Save with `end_date` ("End") set to today.
  3. Save with `end_date` ("End") set to a leap day (29 Feb).
  4. Save with `end_date` ("End") set to a date before the company financial-year start.
  5. Save with `end_date` ("End") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-049 — `end_time` ("End") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Save with `end_time` ("End") set to a single character.
  3. Save with `end_time` ("End") set to a 255-character value.
  4. Save with `end_time` ("End") set to a value with leading and trailing whitespace.
  5. Save with `end_time` ("End") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-050 — `all_day` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Save with `all_day` set to checked.
  3. Save with `all_day` set to unchecked.
  4. Save with `all_day` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-051 — `invite_contact[]` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Save with `invite_contact[]` set to the first real option.
  3. Save with `invite_contact[]` set to the last option.
  4. Save with `invite_contact[]` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-052 — `schedule_type` ("Schedule Type") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Save with `schedule_type` ("Schedule Type") set to the first real option.
  3. Save with `schedule_type` ("Schedule Type") set to the last option.
  4. Save with `schedule_type` ("Schedule Type") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-053 — `allow_notification` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Save with `allow_notification` set to checked.
  3. Save with `allow_notification` set to unchecked.
  4. Save with `allow_notification` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-054 — `notification_via` ("Notify Via") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Save with `notification_via` ("Notify Via") set to the first real option.
  3. Save with `notification_via` ("Notify Via") set to the last option.
  4. Save with `notification_via` ("Notify Via") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-055 — `notification_time_interval` ("Notify before") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Save with `notification_time_interval` ("Notify before") set to a single character.
  3. Save with `notification_time_interval` ("Notify before") set to a 255-character value.
  4. Save with `notification_time_interval` ("Notify before") set to a value with leading and trailing whitespace.
  5. Save with `notification_time_interval` ("Notify before") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-056 — `notification_time` ("Notify before") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Save with `notification_time` ("Notify before") set to the first real option.
  3. Save with `notification_time` ("Notify before") set to the last option.
  4. Save with `notification_time` ("Notify before") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-057 — `user_id` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Save with `user_id` set to the first real option.
  3. Save with `user_id` set to the last option.
  4. Save with `user_id` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-058 — `log_type` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Save with `log_type` set to the first real option.
  3. Save with `log_type` set to the last option.
  4. Save with `log_type` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-059 — `log_time` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Save with `log_time` set to a single character.
  3. Save with `log_time` set to a 255-character value.
  4. Save with `log_time` set to a value with leading and trailing whitespace.
  5. Save with `log_time` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-060 — `log_date` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Save with `log_date` set to today.
  3. Save with `log_date` set to a leap day (29 Feb).
  4. Save with `log_date` set to a date before the company financial-year start.
  5. Save with `log_date` set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-061 — `email_subject` ("Subject") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Save with `email_subject` ("Subject") set to a single character.
  3. Save with `email_subject` ("Subject") set to a 255-character value.
  4. Save with `email_subject` ("Subject") set to a value with leading and trailing whitespace.
  5. Save with `email_subject` ("Subject") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-062 — `invite_contact[]` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Save with `invite_contact[]` set to the first real option.
  3. Save with `invite_contact[]` set to the last option.
  4. Save with `invite_contact[]` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-063 — modal form `tmpl-erp-crm-customer-schedules` saves with only its required fields

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Fill only: `schedule_title`, `user_id`, `start_time` ("Start"), `end_date` ("End"), `end_time` ("End"), `schedule_type` ("Schedule Type"), `user_id`, `log_type`, `log_time`.
  3. Submit.
- **Expected:** The record saves. The 10 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### CRM-T2-064 — `filter_status` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=task`.
  2. Save with `filter_status` set to the first real option.
  3. Save with `filter_status` set to the last option.
  4. Save with `filter_status` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-065 — `filter_contact` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=task`.
  2. Save with `filter_contact` set to the first real option.
  3. Save with `filter_contact` set to the last option.
  4. Save with `filter_contact` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-066 — `filter_user` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=task`.
  2. Save with `filter_user` set to the first real option.
  3. Save with `filter_user` set to the last option.
  4. Save with `filter_user` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-067 — `filter_date` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=task`.
  2. Save with `filter_date` set to today.
  3. Save with `filter_date` set to a leap day (29 Feb).
  4. Save with `filter_date` set to a date before the company financial-year start.
  5. Save with `filter_date` set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-068 — `search_task` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=task`.
  2. Save with `search_task` set to a single character.
  3. Save with `search_task` set to a 255-character value.
  4. Save with `search_task` set to a value with leading and trailing whitespace.
  5. Save with `search_task` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-069 — `admin.php?page=erp-crm&section=task` saves with only its required fields

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=task`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 5 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### CRM-T2-070 — `qlStartDate` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=deals`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=deals`.
  2. Save with `qlStartDate` set to today.
  3. Save with `qlStartDate` set to a leap day (29 Feb).
  4. Save with `qlStartDate` set to a date before the company financial-year start.
  5. Save with `qlStartDate` set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-071 — `qlEndDate` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=deals`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=deals`.
  2. Save with `qlEndDate` set to today.
  3. Save with `qlEndDate` set to a leap day (29 Feb).
  4. Save with `qlEndDate` set to a date before the company financial-year start.
  5. Save with `qlEndDate` set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-072 — `lgStartDate` ("From") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=deals`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=deals`.
  2. Save with `lgStartDate` ("From") set to today.
  3. Save with `lgStartDate` ("From") set to a leap day (29 Feb).
  4. Save with `lgStartDate` ("From") set to a date before the company financial-year start.
  5. Save with `lgStartDate` ("From") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-073 — `lgEndDate` ("To") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=deals`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=deals`.
  2. Save with `lgEndDate` ("To") set to today.
  3. Save with `lgEndDate` ("To") set to a leap day (29 Feb).
  4. Save with `lgEndDate` ("To") set to a date before the company financial-year start.
  5. Save with `lgEndDate` ("To") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-074 — `lrStartDate` ("From") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=deals`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=deals`.
  2. Save with `lrStartDate` ("From") set to today.
  3. Save with `lrStartDate` ("From") set to a leap day (29 Feb).
  4. Save with `lrStartDate` ("From") set to a date before the company financial-year start.
  5. Save with `lrStartDate` ("From") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-075 — `lrEndDate` ("To") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=deals`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=deals`.
  2. Save with `lrEndDate` ("To") set to today.
  3. Save with `lrEndDate` ("To") set to a leap day (29 Feb).
  4. Save with `lrEndDate` ("To") set to a date before the company financial-year start.
  5. Save with `lrEndDate` ("To") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-076 — `dealLostReasonStartDate` ("From") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=deals`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=deals`.
  2. Save with `dealLostReasonStartDate` ("From") set to today.
  3. Save with `dealLostReasonStartDate` ("From") set to a leap day (29 Feb).
  4. Save with `dealLostReasonStartDate` ("From") set to a date before the company financial-year start.
  5. Save with `dealLostReasonStartDate` ("From") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-077 — `dealLostReasonEndDate` ("To") accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=deals`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=deals`.
  2. Save with `dealLostReasonEndDate` ("To") set to today.
  3. Save with `dealLostReasonEndDate` ("To") set to a leap day (29 Feb).
  4. Save with `dealLostReasonEndDate` ("To") set to a date before the company financial-year start.
  5. Save with `dealLostReasonEndDate` ("To") set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-078 — `topSalesPersonStartDate` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=deals`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=deals`.
  2. Save with `topSalesPersonStartDate` set to today.
  3. Save with `topSalesPersonStartDate` set to a leap day (29 Feb).
  4. Save with `topSalesPersonStartDate` set to a date before the company financial-year start.
  5. Save with `topSalesPersonStartDate` set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-079 — `topSalesPersonEndDate` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=deals`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=deals`.
  2. Save with `topSalesPersonEndDate` set to today.
  3. Save with `topSalesPersonEndDate` set to a leap day (29 Feb).
  4. Save with `topSalesPersonEndDate` set to a date before the company financial-year start.
  5. Save with `topSalesPersonEndDate` set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-080 — `wld-start-date` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=deals`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=deals`.
  2. Save with `wld-start-date` set to today.
  3. Save with `wld-start-date` set to a leap day (29 Feb).
  4. Save with `wld-start-date` set to a date before the company financial-year start.
  5. Save with `wld-start-date` set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-081 — `wld-end-date` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=deals`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=deals`.
  2. Save with `wld-end-date` set to today.
  3. Save with `wld-end-date` set to a leap day (29 Feb).
  4. Save with `wld-end-date` set to a date before the company financial-year start.
  5. Save with `wld-end-date` set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-082 — `fwm-start-date` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=deals`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=deals`.
  2. Save with `fwm-start-date` set to today.
  3. Save with `fwm-start-date` set to a leap day (29 Feb).
  4. Save with `fwm-start-date` set to a date before the company financial-year start.
  5. Save with `fwm-start-date` set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-083 — `fwm-end-date` accepts its edge values

- **Surface:** `admin.php?page=erp-crm&section=deals`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=deals`.
  2. Save with `fwm-end-date` set to today.
  3. Save with `fwm-end-date` set to a leap day (29 Feb).
  4. Save with `fwm-end-date` set to a date before the company financial-year start.
  5. Save with `fwm-end-date` set to a far-future date.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-084 — `admin.php?page=erp-crm&section=deals` saves with only its required fields

- **Surface:** `admin.php?page=erp-crm&section=deals`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=deals`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 14 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### CRM-T2-085 — `filter_assign_contact` accepts its edge values

- **Surface:** `http://localhost:8888/wp-admin/admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `http://localhost:8888/wp-admin/admin.php?page=erp-crm&section=contact`.
  2. Save with `filter_assign_contact` set to the first real option.
  3. Save with `filter_assign_contact` set to the last option.
  4. Save with `filter_assign_contact` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-086 — `filter_save_filter` accepts its edge values

- **Surface:** `http://localhost:8888/wp-admin/admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `http://localhost:8888/wp-admin/admin.php?page=erp-crm&section=contact`.
  2. Save with `filter_save_filter` set to the first real option.
  3. Save with `filter_save_filter` set to the last option.
  4. Save with `filter_save_filter` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-087 — `filter_contact_company` accepts its edge values

- **Surface:** `http://localhost:8888/wp-admin/admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `http://localhost:8888/wp-admin/admin.php?page=erp-crm&section=contact`.
  2. Save with `filter_contact_company` set to the first real option.
  3. Save with `filter_contact_company` set to the last option.
  4. Save with `filter_contact_company` set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-088 — `filter` accepts its edge values

- **Surface:** `http://localhost:8888/wp-admin/admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `http://localhost:8888/wp-admin/admin.php?page=erp-crm&section=contact`.
  2. Save with `filter` set to a single character.
  3. Save with `filter` set to a 255-character value.
  4. Save with `filter` set to a value with leading and trailing whitespace.
  5. Save with `filter` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### CRM-T2-089 — `http://localhost:8888/wp-admin/admin.php?page=erp-crm&section=contact` saves with only its required fields

- **Surface:** `http://localhost:8888/wp-admin/admin.php?page=erp-crm&section=contact`
- **Tags:** @tier2 @crm @edge
- **Steps:**
  1. Open `http://localhost:8888/wp-admin/admin.php?page=erp-crm&section=contact`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 4 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.
