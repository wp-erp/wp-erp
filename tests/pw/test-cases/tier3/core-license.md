# Tier 3 — core-license

Negative cases — required-field enforcement, malformed input, injection, and per-role authorization.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**5 cases** (1 derived from the harness, 4 hand-written business flows).

## Business flows

#### CORE_LICENSE-F3-001 — Submitting the licence form with no e-mail is rejected

- **Surface:** `admin.php?page=erp-license`
- **Tags:** @tier3 @core-license @pro @validation
- **Steps:**
  1. Open the licence screen with no licence active.
  2. Clear `#email`, fill `#license_key`, submit.
- **Expected:** The error "Empty email address" renders and no activation request is sent.
- **Oracle:** UI error text (`Update.php:1351`) + `erp_pro_license_status` unchanged.

#### CORE_LICENSE-F3-002 — Submitting the licence form with no key is rejected

- **Surface:** `admin.php?page=erp-license`
- **Tags:** @tier3 @core-license @pro @validation
- **Steps:**
  1. Clear `#license_key`, fill `#email`, submit.
- **Expected:** The error "Empty license key" renders and no activation request is sent.
- **Oracle:** UI error text (`Update.php:1355`).

#### CORE_LICENSE-F3-003 — A key whose seats are exhausted reports the activation limit

- **Surface:** `admin.php?page=erp-license`
- **Tags:** @tier3 @core-license @pro @needs-external
- **Preconditions:** A key whose single seat is already held by another site URL.
- **Steps:**
  1. Activate that key on this site.
- **Expected:** The error reads "Your license key has reached its activation limit." and the site stays unlicensed.
- **Oracle:** UI error text — the `no_activations_left` branch at `Update.php:1309`.

#### CORE_LICENSE-F3-004 — A garbage licence key is reported, not silently accepted

- **Surface:** `admin.php?page=erp-license`
- **Tags:** @tier3 @core-license @pro
- **Steps:**
  1. Enter a well-formed but non-existent 32-character key and submit.
- **Expected:** An error from the licence server renders (e.g. "Invalid license. License doesn't exist.") and Pro stays disabled.
- **Oracle:** UI error text + `erp_pro_license_status` is not `valid`.

## Screen & field coverage

#### CORE_LICENSE-T3-001 — `admin.php?page=erp-license` is closed to roles that do not own it

- **Surface:** `admin.php?page=erp-license`
- **Tags:** @tier3 @core-license @authz
- **Steps:**
  1. For each of erp_hr_manager, erp_crm_manager, erp_crm_agent, erp_ac_manager, erp_recruiter, employee, a logged-out visitor, open `admin.php?page=erp-license` directly by URL.
  2. Record the outcome for each role.
- **Expected:** A role without the capability gets a WordPress permission error or a redirect — never the screen contents, and never a PHP fatal. A logged-out visitor is sent to wp-login.php.
- **Oracle:** HTTP status + rendered body per role; capability checked against the role definition.
