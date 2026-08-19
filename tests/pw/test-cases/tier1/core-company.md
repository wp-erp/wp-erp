# Tier 1 — core-company

Happy path — the screen loads, every field and label renders as captured, and the primary create/read/update/delete flow succeeds.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**9 cases** (9 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### CORE_COMPANY-T1-001 — Company Details loads and renders its controls

- **Surface:** `admin.php?page=erp-company`
- **Tags:** @tier1 @core-company @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-company`.
  3. Confirm the screen body renders.
- **Expected:** The screen returns 200, renders its heading "Company Details", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CORE_COMPANY-T1-002 — Company loads and renders its controls

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier1 @core-company @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-company&action=edit`.
  3. Confirm the action buttons render: "Update Company".
- **Expected:** The screen returns 200, renders its heading "Company", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CORE_COMPANY-T1-003 — Company Details loads and renders its controls

- **Surface:** `admin.php?page=erp-company`
- **Tags:** @tier1 @core-company @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-company`.
  3. Confirm the screen body renders.
- **Expected:** The screen returns 200, renders its heading "Company Details", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CORE_COMPANY-T1-004 — Company Details loads and renders its controls

- **Surface:** `http://localhost:8888/wp-admin/admin.php?page=erp-company`
- **Tags:** @tier1 @core-company @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `http://localhost:8888/wp-admin/admin.php?page=erp-company`.
  3. Confirm the screen body renders.
- **Expected:** The screen returns 200, renders its heading "Company Details", and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.

#### CORE_COMPANY-T1-005 — Save `admin.php?page=erp-company&action=edit` with every field completed

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier1 @core-company @crud
- **Steps:**
  1. Open `admin.php?page=erp-company&action=edit`.
  2. Fill all 13 fields with valid data (required: `address[country]` ("Country")).
  3. Submit with "Update Company".
- **Expected:** The record is created, a success notice renders, and the new row appears in the list.
- **Oracle:** UI success notice + list row, confirmed by the matching REST/DB row.

#### CORE_COMPANY-T1-006 — `admin.php?page=erp-company&action=edit` renders all 13 fields with their labels

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier1 @core-company @labels
- **Steps:**
  1. Open `admin.php?page=erp-company&action=edit`.
  2. For each field below, assert it is present and its label reads exactly as captured.
- **Expected:** All 13 fields render:
      - `name` ("Enter company name here") — type `text`
      - `address[address_1]` ("Address Line 1") — type `text`
      - `address[address_2]` ("Address Line 2") — type `text`
      - `address[city]` ("City") — type `text`
      - `address[country]` ("Country") — type `select`, **required**
      - `address[state]` ("Province / State") — type `select`
      - `address[zip]` ("Postal / Zip Code") — type `text`
      - `phone` ("Phone") — type `text`
      - `fax` ("Fax") — type `text`
      - `mobile` ("Mobile") — type `text`
      - `website` ("Website") — type `url`
      - `business_type` ("What sort of business do you do?") — type `select`
      - `save` — type `submit`
- **Oracle:** UI — field presence, associated `<label>` text, input type and required flag.

#### CORE_COMPANY-T1-007 — `address[country]` ("Country") offers its full option set

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier1 @core-company @options
- **Steps:**
  1. Open `admin.php?page=erp-company&action=edit`.
  2. Read every option of `address[country]` ("Country").
- **Expected:** The options are exactly: "- Select -" (`-1`), "Åland Islands" (`AX`), "Afghanistan" (`AF`), "Albania" (`AL`), "Algeria" (`DZ`), "Andorra" (`AD`), "Angola" (`AO`), "Anguilla" (`AI`), "Antarctica" (`AQ`), "Antigua and Barbuda" (`AG`), "Argentina" (`AR`), "Armenia" (`AM`), "Aruba" (`AW`), "Australia" (`AU`), "Austria" (`AT`), "Azerbaijan" (`AZ`), "Bahamas" (`BS`), "Bahrain" (`BH`), "Bangladesh" (`BD`), "Barbados" (`BB`), "Belarus" (`BY`), "Belgium" (`BE`), "Belize" (`BZ`), "Benin" (`BJ`), "Bermuda" (`BM`) … and 15 more (see harness).
- **Oracle:** UI — `<option>` label/value pairs.

#### CORE_COMPANY-T1-008 — `address[state]` ("Province / State") offers its full option set

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier1 @core-company @options
- **Steps:**
  1. Open `admin.php?page=erp-company&action=edit`.
  2. Read every option of `address[state]` ("Province / State").
- **Expected:** The options are exactly: "Bagerhat" (`BAG`), "Bandarban" (`BAN`), "Barguna" (`BAR`), "Barisal" (`BARI`), "Bhola" (`BHO`), "Bogra" (`BOG`), "Brahmanbaria" (`BRA`), "Chandpur" (`CHA`), "Chittagong" (`CHI`), "Chuadanga" (`CHU`), "Comilla" (`COM`), "Cox's Bazar" (`COX`), "Dhaka" (`DHA`), "Dinajpur" (`DIN`), "Faridpur" (`FAR`), "Feni" (`FEN`), "Gaibandha" (`GAI`), "Gazipur" (`GAZI`), "Gopalganj" (`GOP`), "Habiganj" (`HAB`), "Jamalpur" (`JAM`), "Jessore" (`JES`), "Jhalokati" (`JHA`), "Jhenaidah" (`JHE`), "Joypurhat" (`JOY`) … and 15 more (see harness).
- **Oracle:** UI — `<option>` label/value pairs.

#### CORE_COMPANY-T1-009 — `business_type` ("What sort of business do you do?") offers its full option set

- **Surface:** `admin.php?page=erp-company&action=edit`
- **Tags:** @tier1 @core-company @options
- **Steps:**
  1. Open `admin.php?page=erp-company&action=edit`.
  2. Read every option of `business_type` ("What sort of business do you do?").
- **Expected:** The options are exactly: "-- select --" (``), "Freelance" (`Freelance`), "Freelance (Developer)" (`FreelanceDev`), "Freelance (Design)" (`FreelanceDes`), "Small Business: Local Service (e.g. Hairdresser)" (`SmallBLocal`), "Small Business: Web Business" (`SmallBWeb`), "Small Business (Other)" (`SmallBOther`), "eCommerce (WooCommerce)" (`ecommerceWoo`), "eCommerce (Shopify)" (`ecommerceShopify`), "eCommerce (Other)" (`ecommerceOther`), "Other" (`Other`).
- **Oracle:** UI — `<option>` label/value pairs.
