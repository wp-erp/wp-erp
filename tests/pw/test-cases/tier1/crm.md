# Tier 1 — crm

Happy path — the screen loads, every field and label renders as captured, and the primary create/read/update/delete flow succeeds.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**52 cases** (49 derived from the harness, 3 hand-written business flows).

## Business flows

#### CRM-F1-001 — Contact lifecycle with life stage and owner

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @flow
- **Steps:**
  1. Create a contact via `tmpl-erp-crm-new-contact` with all 26 fields.
  2. Confirm it appears in the list with the captured columns (Contact name, Email Address, Phone, Life stage, Owner, Created At).
  3. Change its life stage and owner.
  4. Delete it and confirm it leaves the list.
- **Expected:** Each change is reflected in the list row and the contact detail view.
- **Oracle:** REST `erp/v1/crm/contacts/{id}` + `wp_erp_peoples`.

#### CRM-F1-002 — Contact group subscribe and unsubscribe

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @flow
- **Steps:**
  1. Create a contact group.
  2. Subscribe a contact to it.
  3. Unsubscribe the contact.
- **Expected:** Group membership count changes by exactly one in each direction.
- **Oracle:** REST `erp/v1/crm/contacts/groups/{id}/subscribes`.

#### CRM-F1-003 — A deal moves through its pipeline stages and can be won or lost

- **Surface:** `admin.php?page=erp-crm&section=deals`
- **Tags:** @tier1 @crm @pro @flow
- **Steps:**
  1. Create a deal on the default pipeline.
  2. Move it through each stage.
  3. Mark it Won.
  4. Create a second deal and mark it Lost, choosing a lost reason.
- **Expected:** Stage history records each move; won/lost states render in their own filters; a lost deal stores its reason.
- **Oracle:** `wp_erp_crm_deals_stage_history` + `wp_erp_crm_deals_lost_reasons`.

## Screen & field coverage

#### CRM-T1-001 — CRM loads and renders its controls

- **Surface:** `admin.php?page=erp-crm&section=dashboard`
- **Tags:** @tier1 @crm @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-crm&section=dashboard`.
  3. Confirm the screen body renders.
- **Expected:** The screen returns 200, renders its heading "CRM", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CRM-T1-002 — CRM loads and renders its controls

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-crm&section=contact`.
  3. Confirm the action buttons render: "Import", "Export".
- **Expected:** The screen returns 200, renders its heading "CRM", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CRM-T1-003 — CRM loads and renders its controls

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier1 @crm @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-crm&section=task`.
  3. Confirm the screen body renders.
- **Expected:** The screen returns 200, renders its heading "CRM", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CRM-T1-004 — CRM loads and renders its controls

- **Surface:** `admin.php?page=erp-crm&section=deals`
- **Tags:** @tier1 @crm @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-crm&section=deals`.
  3. Confirm the screen body renders.
- **Expected:** The screen returns 200, renders its heading "CRM", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CRM-T1-005 — CRM loads and renders its controls

- **Surface:** `admin.php?page=erp-crm&section=integration`
- **Tags:** @tier1 @crm @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-crm&section=integration`.
  3. Confirm the action buttons render: "Configure".
- **Expected:** The screen returns 200, renders its heading "CRM", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CRM-T1-006 — CRM loads and renders its controls

- **Surface:** `admin.php?page=erp-crm&section=reports`
- **Tags:** @tier1 @crm @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-crm&section=reports`.
  3. Confirm the action buttons render: "View Report".
- **Expected:** The screen returns 200, renders its heading "CRM", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CRM-T1-007 — CRM loads and renders its controls

- **Surface:** `admin.php?page=erp-crm&section=contact&sub-section=companies`
- **Tags:** @tier1 @crm @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-crm&section=contact&sub-section=companies`.
  3. Confirm the action buttons render: "Import", "Export".
- **Expected:** The screen returns 200, renders its heading "CRM", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CRM-T1-008 — CRM loads and renders its controls

- **Surface:** `admin.php?page=erp-crm&section=contact&sub-section=contact_group`
- **Tags:** @tier1 @crm @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-crm&section=contact&sub-section=contact_group`.
  3. Confirm the screen body renders.
- **Expected:** The screen returns 200, renders its heading "CRM", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CRM-T1-009 — CRM loads and renders its controls

- **Surface:** `admin.php?page=erp-crm&section=contact&sub-section=activity`
- **Tags:** @tier1 @crm @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-crm&section=contact&sub-section=activity`.
  3. Confirm the screen body renders.
- **Expected:** The screen returns 200, renders its heading "CRM", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CRM-T1-010 — CRM loads and renders its controls

- **Surface:** `admin.php?page=erp-crm&section=reports&sub-section=growth`
- **Tags:** @tier1 @crm @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-crm&section=reports&sub-section=growth`.
  3. Confirm the action buttons render: "View Report".
- **Expected:** The screen returns 200, renders its heading "CRM", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CRM-T1-011 — CRM loads and renders its controls

- **Surface:** `admin.php?page=erp-crm&section=dashboard`
- **Tags:** @tier1 @crm @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-crm&section=dashboard`.
  3. Confirm the action buttons render: "More", "today", "month", "week", "day".
  4. Confirm the list columns render: SunMonTueWedThuFriSat, Sun, Mon, Tue, Wed, Thu, Fri, Sat, 26, 27.
- **Expected:** The screen returns 200, renders its heading "CRM", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CRM-T1-012 — CRM loads and renders its controls

- **Surface:** `admin.php?page=erp-crm&section=deals`
- **Tags:** @tier1 @crm @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-crm&section=deals`.
  3. Confirm the action buttons render: "More", "Pipeline", "This month", "Open Deals", "Absolute", "Percentage".
  4. Confirm the list columns render: Stage, Counts of deals reached the stage, Values of deals reached the stage, Average deal value (USD), Average time until the stage reached (days), Type, Total, Open, Done, Title.
- **Expected:** The screen returns 200, renders its heading "CRM", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CRM-T1-013 — CRM loads and renders its controls

- **Surface:** `admin.php?page=erp-crm&section=integration`
- **Tags:** @tier1 @crm @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-crm&section=integration`.
  3. Confirm the action buttons render: "More", "Configure".
- **Expected:** The screen returns 200, renders its heading "CRM", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CRM-T1-014 — CRM loads and renders its controls

- **Surface:** `admin.php?page=erp-crm&section=reports`
- **Tags:** @tier1 @crm @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-crm&section=reports`.
  3. Confirm the action buttons render: "More", "View Report".
- **Expected:** The screen returns 200, renders its heading "CRM", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CRM-T1-015 — CRM loads and renders its controls

- **Surface:** `http://localhost:8888/wp-admin/admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `http://localhost:8888/wp-admin/admin.php?page=erp-crm&section=contact`.
  3. Confirm the action buttons render: "More", "Import", "Export", "Add Filter", "Or Filter", "Cancel".
  4. Confirm the list columns render: Select All, Contact name, Email Address, Phone, Life stage, Owner, Created At.
- **Expected:** The screen returns 200, renders its heading "CRM", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CRM-T1-016 — Save modal form `tmpl-erp-crm-new-contact` with every field completed

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @crud
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Fill all 26 fields with valid data (required: `contact[main][first_name]` ("First Name *"), `contact[main][company]` ("Company Name *"), `contact[main][email]` ("Email *"), `contact[meta][life_stage]` ("Life Stage *"), `contact[meta][contact_owner]` ("Contact Owner *")).
  3. Submit with "Upload Photo".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### CRM-T1-017 — modal form `tmpl-erp-crm-new-contact` renders all 26 fields with their labels

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @labels
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 26 fields render:
      - `contact[main][first_name]` ("First Name *") — type `text`, **required**
      - `contact[main][last_name]` ("Last Name") — type `text`
      - `contact[main][company]` ("Company Name *") — type `text`, **required**
      - `contact[main][email]` ("Email *") — type `email`, **required**
      - `contact[main][phone]` ("Phone Number") — type `text`
      - `contact[meta][life_stage]` ("Life Stage *") — type `select`, **required**
      - `contact[meta][contact_owner]` ("Contact Owner *") — type `select`, **required**
      - `advanced_fields` ("Show Advanced Fields") — type `checkbox`
      - `contact[meta][date_of_birth]` ("Date of Birth") — type `text`
      - `contact[meta][contact_age]` ("Age (years)") — type `number`
      - `contact[main][mobile]` ("Mobile") — type `text`
      - `contact[main][website]` ("Website") — type `text`
      - `contact[main][fax]` ("Fax Number") — type `text`
      - `contact[main][street_1]` ("Address 1") — type `text`
      - `contact[main][street_2]` ("Address 2") — type `text`
      - `contact[main][city]` ("City") — type `text`
      - `contact[main][country]` ("Country") — type `select`
      - `contact[main][state]` ("Province / State") — type `select`
      - `contact[main][postal_code]` ("Post Code/Zip Code") — type `text`
      - `contact[meta][source]` ("Contact Source") — type `select`
      - `contact[main][other]` ("Others") — type `text`
      - `contact[main][notes]` ("Notes") — type `textarea`
      - `contact[social][facebook]` ("Facebook") — type `text`
      - `contact[social][twitter]` ("Twitter") — type `text`
      - `contact[social][googleplus]` ("Google Plus") — type `text`
      - `contact[social][linkedin]` ("Linkedin") — type `text`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### CRM-T1-018 — `contact[meta][life_stage]` ("Life Stage *") offers its full option set

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @options
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Read every option of `contact[meta][life_stage]` ("Life Stage *").
- **Expected:** The options are exactly: "--Select Stage--" (``), "Customer" (`customer`), "Lead" (`lead`), "Opportunity" (`opportunity`), "Subscriber" (`subscriber`).
- **Oracle:** UI — `<option>` label/value pairs.

#### CRM-T1-019 — `contact[meta][contact_owner]` ("Contact Owner *") offers its full option set

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @options
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Read every option of `contact[meta][contact_owner]` ("Contact Owner *").
- **Expected:** The options are exactly: "--Select--" (``), "admin (wordpress@example.com)" (`1`), "erp_crm_agent (erp_crm_agent@erp.test)" (`11`), "erp_crm_manager (crm.manager@example.test)" (`3`).
- **Oracle:** UI — `<option>` label/value pairs.

#### CRM-T1-020 — `contact[main][country]` ("Country") offers its full option set

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @options
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Read every option of `contact[main][country]` ("Country").
- **Expected:** The options are exactly: "- Select -" (`-1`), "Åland Islands" (`AX`), "Afghanistan" (`AF`), "Albania" (`AL`), "Algeria" (`DZ`), "Andorra" (`AD`), "Angola" (`AO`), "Anguilla" (`AI`), "Antarctica" (`AQ`), "Antigua and Barbuda" (`AG`), "Argentina" (`AR`), "Armenia" (`AM`), "Aruba" (`AW`), "Australia" (`AU`), "Austria" (`AT`), "Azerbaijan" (`AZ`), "Bahamas" (`BS`), "Bahrain" (`BH`), "Bangladesh" (`BD`), "Barbados" (`BB`), "Belarus" (`BY`), "Belgium" (`BE`), "Belize" (`BZ`), "Benin" (`BJ`), "Bermuda" (`BM`) … and 220 more (see harness).
- **Oracle:** UI — `<option>` label/value pairs.

#### CRM-T1-021 — `contact[meta][source]` ("Contact Source") offers its full option set

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @options
- **Steps:**
  1. Open modal form `tmpl-erp-crm-new-contact`.
  2. Read every option of `contact[meta][source]` ("Contact Source").
- **Expected:** The options are exactly: "Advertisement" (`advert`), "Chat" (`chat`), "Contact Form" (`contact_form`), "Employee Referral" (`employee_referral`), "External Referral" (`external_referral`), "Marketing campaign" (`marketing_campaign`), "Newsletter" (`newsletter`), "OnlineStore" (`online_store`), "Optin Forms" (`optin_form`), "Partner" (`partner`), "Phone Call" (`phone`), "Public Relations" (`public_relations`), "Sales Mail Alias" (`sales_mail_alias`), "Search Engine" (`search_engine`), "Seminar-Internal" (`seminar_internal`), "Seminar Partner" (`seminar_partner`), "Social Media" (`social_media`), "Trade Show" (`trade_show`), "Web Download" (`web_download`), "Web Research" (`web_research`).
- **Oracle:** UI — `<option>` label/value pairs.

#### CRM-T1-022 — Save modal form `tmpl-erp-crm-import-customer` with every field completed

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @crud
- **Steps:**
  1. Open modal form `tmpl-erp-crm-import-customer`.
  2. Fill all 4 fields with valid data (required: `csv_file` ("CSV File *")).
  3. Submit with "Download Sample CSV".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### CRM-T1-023 — modal form `tmpl-erp-crm-import-customer` renders all 4 fields with their labels

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @labels
- **Steps:**
  1. Open modal form `tmpl-erp-crm-import-customer`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 4 fields render:
      - `csv_file` ("CSV File *") — type `file`, **required**
      - `contact_owner` ("Contact Owner") — type `select`
      - `life_stage` ("Life Stage") — type `select`
      - `contact_group` ("Contact Group") — type `select`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### CRM-T1-024 — `contact_owner` ("Contact Owner") offers its full option set

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @options
- **Steps:**
  1. Open modal form `tmpl-erp-crm-import-customer`.
  2. Read every option of `contact_owner` ("Contact Owner").
- **Expected:** The options are exactly: "Admin <wordpress@example.com>" (`1`), "Erp_crm_agent <erp_crm_agent@erp.test>" (`11`), "Erp_crm_manager <crm.manager@example.test>" (`3`).
- **Oracle:** UI — `<option>` label/value pairs.

#### CRM-T1-025 — `life_stage` ("Life Stage") offers its full option set

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @options
- **Steps:**
  1. Open modal form `tmpl-erp-crm-import-customer`.
  2. Read every option of `life_stage` ("Life Stage").
- **Expected:** The options are exactly: "Customer" (`customer`), "Lead" (`lead`), "Opportunity" (`opportunity`), "Subscriber" (`subscriber`).
- **Oracle:** UI — `<option>` label/value pairs.

#### CRM-T1-026 — Save modal form `tmpl-erp-crm-export-customer` with every field completed

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @crud
- **Steps:**
  1. Open modal form `tmpl-erp-crm-export-customer`.
  2. Fill all 1 fields with valid data.
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### CRM-T1-027 — modal form `tmpl-erp-crm-export-customer` renders all 1 fields with their labels

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @labels
- **Steps:**
  1. Open modal form `tmpl-erp-crm-export-customer`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 1 fields render:
      - `selecctall` — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### CRM-T1-028 — Save modal form `tmpl-erp-crm-import-users` with every field completed

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @crud
- **Steps:**
  1. Open modal form `tmpl-erp-crm-import-users`.
  2. Fill all 4 fields with valid data.
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### CRM-T1-029 — modal form `tmpl-erp-crm-import-users` renders all 4 fields with their labels

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @labels
- **Steps:**
  1. Open modal form `tmpl-erp-crm-import-users`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 4 fields render:
      - `user_role` ("User Role") — type `select`
      - `contact_owner` ("Contact Owner") — type `select`
      - `life_stage` ("Life Stage") — type `select`
      - `contact_group` ("Contact Group") — type `select`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### CRM-T1-030 — `user_role` ("User Role") offers its full option set

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @options
- **Steps:**
  1. Open modal form `tmpl-erp-crm-import-users`.
  2. Read every option of `user_role` ("User Role").
- **Expected:** The options are exactly: "Administrator" (`administrator`), "Editor" (`editor`), "Author" (`author`), "Contributor" (`contributor`), "Subscriber" (`subscriber`), "HR Manager" (`erp_hr_manager`), "Employee" (`employee`), "CRM Manager" (`erp_crm_manager`), "CRM Agent" (`erp_crm_agent`), "Accounting Manager" (`erp_ac_manager`), "Customer" (`customer`), "Shop Manager" (`shop_manager`), "Recruiter" (`erp_recruiter`).
- **Oracle:** UI — `<option>` label/value pairs.

#### CRM-T1-031 — `contact_owner` ("Contact Owner") offers its full option set

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @options
- **Steps:**
  1. Open modal form `tmpl-erp-crm-import-users`.
  2. Read every option of `contact_owner` ("Contact Owner").
- **Expected:** The options are exactly: "Admin <wordpress@example.com>" (`1`), "Erp_crm_agent <erp_crm_agent@erp.test>" (`11`), "Erp_crm_manager <crm.manager@example.test>" (`3`).
- **Oracle:** UI — `<option>` label/value pairs.

#### CRM-T1-032 — `life_stage` ("Life Stage") offers its full option set

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @options
- **Steps:**
  1. Open modal form `tmpl-erp-crm-import-users`.
  2. Read every option of `life_stage` ("Life Stage").
- **Expected:** The options are exactly: "Customer" (`customer`), "Lead" (`lead`), "Opportunity" (`opportunity`), "Subscriber" (`subscriber`).
- **Oracle:** UI — `<option>` label/value pairs.

#### CRM-T1-033 — Save modal form `tmpl-erp-make-wp-user` with every field completed

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @crud
- **Steps:**
  1. Open modal form `tmpl-erp-make-wp-user`.
  2. Fill all 3 fields with valid data (required: `customeresc_attr_email` ("Enter your email")).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### CRM-T1-034 — modal form `tmpl-erp-make-wp-user` renders all 3 fields with their labels

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @labels
- **Steps:**
  1. Open modal form `tmpl-erp-make-wp-user`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 3 fields render:
      - `customeresc_attr_email` ("Enter your email") — type `email`, **required**, placeholder "Enter your email"
      - `customer_role` ("Role") — type `select`
      - `send_password_notification` ("Send password") — type `checkbox`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### CRM-T1-035 — `customer_role` ("Role") offers its full option set

- **Surface:** `admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @options
- **Steps:**
  1. Open modal form `tmpl-erp-make-wp-user`.
  2. Read every option of `customer_role` ("Role").
- **Expected:** The options are exactly: "Shop manager" (`shop_manager`), "Customer" (`customer`), "Accounting Manager" (`erp_ac_manager`), "Employee" (`employee`), "Subscriber" (`subscriber`), "Contributor" (`contributor`), "Author" (`author`), "Editor" (`editor`), "Administrator" (`administrator`).
- **Oracle:** UI — `<option>` label/value pairs.

#### CRM-T1-036 — Save modal form `tmpl-erp-crm-customer-schedules` with every field completed

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier1 @crm @crud
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Fill all 19 fields with valid data (required: `schedule_title`, `user_id`, `start_time` ("Start"), `end_date` ("End"), `end_time` ("End"), `schedule_type` ("Schedule Type"), `user_id`, `log_type`, `log_time`).
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### CRM-T1-037 — modal form `tmpl-erp-crm-customer-schedules` renders all 19 fields with their labels

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier1 @crm @labels
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 19 fields render:
      - `schedule_title` — type `text`, **required**, placeholder "Enter Schedule Title"
      - `user_id` — type `select`, **required**
      - `start_date` ("Start") — type `text`, placeholder "yy-mm-dd"
      - `start_time` ("Start") — type `text`, **required**, placeholder "12.00pm"
      - `end_date` ("End") — type `text`, **required**, placeholder "yy-mm-dd"
      - `end_time` ("End") — type `text`, **required**, placeholder "12.00pm"
      - `all_day` — type `checkbox`
      - `invite_contact[]` — type `select`
      - `schedule_type` ("Schedule Type") — type `select`, **required**
      - `allow_notification` — type `checkbox`
      - `notification_via` ("Notify Via") — type `select`
      - `notification_time_interval` ("Notify before") — type `text`, placeholder "10"
      - `notification_time` ("Notify before") — type `select`
      - `user_id` — type `select`, **required**
      - `log_type` — type `select`, **required**
      - `log_time` — type `text`, **required**, placeholder "12.00pm"
      - `log_date` — type `text`, placeholder "yy-mm-dd"
      - `email_subject` ("Subject") — type `text`, placeholder "Subject log..."
      - `invite_contact[]` — type `select`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### CRM-T1-038 — `invite_contact[]` offers its full option set

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier1 @crm @options
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Read every option of `invite_contact[]`.
- **Expected:** The options are exactly: "Me ( admin )" (`1`), "erp_crm_agent" (`11`), "erp_crm_manager" (`3`).
- **Oracle:** UI — `<option>` label/value pairs.

#### CRM-T1-039 — `schedule_type` ("Schedule Type") offers its full option set

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier1 @crm @options
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Read every option of `schedule_type` ("Schedule Type").
- **Expected:** The options are exactly: "--Select--" (``), "Meeting" (`meeting`), "Call" (`call`).
- **Oracle:** UI — `<option>` label/value pairs.

#### CRM-T1-040 — `notification_via` ("Notify Via") offers its full option set

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier1 @crm @options
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Read every option of `notification_via` ("Notify Via").
- **Expected:** The options are exactly: "--Select--" (``), "Email" (`email`), "SMS" (`sms`).
- **Oracle:** UI — `<option>` label/value pairs.

#### CRM-T1-041 — `notification_time` ("Notify before") offers its full option set

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier1 @crm @options
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Read every option of `notification_time` ("Notify before").
- **Expected:** The options are exactly: "-Select-" (``), "minute" (`minute`), "hour" (`hour`), "day" (`day`).
- **Oracle:** UI — `<option>` label/value pairs.

#### CRM-T1-042 — `log_type` offers its full option set

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier1 @crm @options
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Read every option of `log_type`.
- **Expected:** The options are exactly: "-- Select type --" (``), "Log a Call" (`call`), "Log a Meeting" (`meeting`), "Log an Email" (`email`), "Log an SMS" (`sms`).
- **Oracle:** UI — `<option>` label/value pairs.

#### CRM-T1-043 — `invite_contact[]` offers its full option set

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier1 @crm @options
- **Steps:**
  1. Open modal form `tmpl-erp-crm-customer-schedules`.
  2. Read every option of `invite_contact[]`.
- **Expected:** The options are exactly: "Me ( admin )" (`1`), "erp_crm_agent" (`11`), "erp_crm_manager" (`3`).
- **Oracle:** UI — `<option>` label/value pairs.

#### CRM-T1-044 — Save `admin.php?page=erp-crm&section=task` with every field completed

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier1 @crm @crud
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=task`.
  2. Fill all 5 fields with valid data.
  3. Submit the form.
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### CRM-T1-045 — `admin.php?page=erp-crm&section=task` renders all 5 fields with their labels

- **Surface:** `admin.php?page=erp-crm&section=task`
- **Tags:** @tier1 @crm @labels
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=task`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 5 fields render:
      - `filter_status` — type `select`
      - `filter_contact` — type `select`
      - `filter_user` — type `select`
      - `filter_date` — type `text`, placeholder "Select Date Range"
      - `search_task` — type `search`, placeholder "Search Task"
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### CRM-T1-046 — Save `admin.php?page=erp-crm&section=deals` with every field completed

- **Surface:** `admin.php?page=erp-crm&section=deals`
- **Tags:** @tier1 @crm @crud
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=deals`.
  2. Fill all 14 fields with valid data.
  3. Submit with "More".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### CRM-T1-047 — `admin.php?page=erp-crm&section=deals` renders all 14 fields with their labels

- **Surface:** `admin.php?page=erp-crm&section=deals`
- **Tags:** @tier1 @crm @labels
- **Steps:**
  1. Open `admin.php?page=erp-crm&section=deals`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 14 fields render:
      - `qlStartDate` — type `text`, placeholder "From Date"
      - `qlEndDate` — type `text`, placeholder "To Date"
      - `lgStartDate` ("From") — type `text`, placeholder "Start Date"
      - `lgEndDate` ("To") — type `text`, placeholder "End Date"
      - `lrStartDate` ("From") — type `text`, placeholder "Start Date"
      - `lrEndDate` ("To") — type `text`, placeholder "End Date"
      - `dealLostReasonStartDate` ("From") — type `text`, placeholder "Start Date"
      - `dealLostReasonEndDate` ("To") — type `text`, placeholder "End Date"
      - `topSalesPersonStartDate` — type `text`, placeholder "Start Date"
      - `topSalesPersonEndDate` — type `text`, placeholder "End Date"
      - `wld-start-date` — type `text`, placeholder "From Date"
      - `wld-end-date` — type `text`, placeholder "To Date"
      - `fwm-start-date` — type `text`, placeholder "From Date"
      - `fwm-end-date` — type `text`, placeholder "To Date"
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### CRM-T1-048 — Save `http://localhost:8888/wp-admin/admin.php?page=erp-crm&section=contact` with every field completed

- **Surface:** `http://localhost:8888/wp-admin/admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @crud
- **Steps:**
  1. Open `http://localhost:8888/wp-admin/admin.php?page=erp-crm&section=contact`.
  2. Fill all 4 fields with valid data.
  3. Submit with "Add Filter".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### CRM-T1-049 — `http://localhost:8888/wp-admin/admin.php?page=erp-crm&section=contact` renders all 4 fields with their labels

- **Surface:** `http://localhost:8888/wp-admin/admin.php?page=erp-crm&section=contact`
- **Tags:** @tier1 @crm @labels
- **Steps:**
  1. Open `http://localhost:8888/wp-admin/admin.php?page=erp-crm&section=contact`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 4 fields render:
      - `filter_assign_contact` — type `select`
      - `filter_save_filter` — type `select`
      - `filter_contact_company` — type `select`
      - `filter` — type `submit`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.
