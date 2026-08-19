# Tier 1 — core-license

Happy path — the screen loads, every field and label renders as captured, and the primary create/read/update/delete flow succeeds.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**3 cases** (1 derived from the harness, 2 hand-written business flows).

## Business flows

#### CORE_LICENSE-F1-001 — Activating the licence through the admin form enables Pro

- **Surface:** `admin.php?page=erp-license`
- **Tags:** @tier1 @core-license @pro @flow
- **Preconditions:** The licence seat is free (no other site or CI run holds it) and `ERP_LICENSE_KEY`/`ERP_LICENSE_EMAIL` are set.
- **Steps:**
  1. Open `admin.php?page=erp-license`.
  2. Fill `#email` and `#license_key`, choose `scale_yearly` in `#subscription-type`.
  3. Submit `#submit` ("Save & Activate").
- **Expected:** The notice reads exactly "License activated successfully." and the screen now offers `button[name="deactivate_license"]`.
- **Oracle:** `erp_pro_license_status` stores `license: "valid"`, `users: 100`, `tier: "scale"` (read via `erp-pw/v1/license`).

#### CORE_LICENSE-F1-002 — Deactivating the licence releases the seat

- **Surface:** `admin.php?page=erp-license`
- **Tags:** @tier1 @core-license @pro @flow
- **Preconditions:** The site holds an active licence.
- **Steps:**
  1. Open `admin.php?page=erp-license`.
  2. Click "Deactivate License".
- **Expected:** The notice reads exactly "License deactivated successfully." and the activation form returns.
- **Oracle:** `erp_pro_license` and `erp_pro_license_status` are both deleted (see `erp-pro/includes/Admin/Update.php:1382-1400`).

## Screen & field coverage

#### CORE_LICENSE-T1-001 — admin.php?page=erp-license loads and renders its controls

- **Surface:** `admin.php?page=erp-license`
- **Tags:** @tier1 @core-license @smoke
- **Steps:**
  1. Sign in as an administrator.
  2. Open `admin.php?page=erp-license`.
  3. Confirm the action buttons render: "Sync", "Deactivate License".
- **Expected:** The screen returns 200, renders its heading, and shows the controls above.
- **Oracle:** UI — heading, buttons and list columns; plus no PHP fatal/notice in the response body or `debug.log`.
