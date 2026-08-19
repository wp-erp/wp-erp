# Tier 2 — settings

Edge cases — boundaries, optional-field omission, empty states, unusual but legal input.

Every field, label and option named below was captured from the running site
(wp-erp 1.17.8 + erp-pro 1.7.0 at `localhost:8888`) — see `harness/report/`.

**155 cases** (155 derived from the harness, 0 hand-written business flows).

## Screen & field coverage

#### SETTINGS-T2-001 — `erp-gen_com_start` ("Company Start Date The date the company officially started.") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/general`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/general`.
  2. Save with `erp-gen_com_start` ("Company Start Date The date the company officially started.") set to a single character.
  3. Save with `erp-gen_com_start` ("Company Start Date The date the company officially started.") set to a 255-character value.
  4. Save with `erp-gen_com_start` ("Company Start Date The date the company officially started.") set to a value with leading and trailing whitespace.
  5. Save with `erp-gen_com_start` ("Company Start Date The date the company officially started.") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-002 — `admin.php?page=erp-settings#/general` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/general`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/general`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-003 — `erp-undefined` ("Administrator") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm`.
  2. Save with `erp-undefined` ("Administrator") set to checked.
  3. Save with `erp-undefined` ("Administrator") set to unchecked.
  4. Save with `erp-undefined` ("Administrator") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-004 — `erp-undefined` ("Editor") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm`.
  2. Save with `erp-undefined` ("Editor") set to checked.
  3. Save with `erp-undefined` ("Editor") set to unchecked.
  4. Save with `erp-undefined` ("Editor") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-005 — `erp-undefined` ("Author") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm`.
  2. Save with `erp-undefined` ("Author") set to checked.
  3. Save with `erp-undefined` ("Author") set to unchecked.
  4. Save with `erp-undefined` ("Author") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-006 — `erp-undefined` ("Contributor") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm`.
  2. Save with `erp-undefined` ("Contributor") set to checked.
  3. Save with `erp-undefined` ("Contributor") set to unchecked.
  4. Save with `erp-undefined` ("Contributor") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-007 — `erp-undefined` ("Subscriber") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm`.
  2. Save with `erp-undefined` ("Subscriber") set to checked.
  3. Save with `erp-undefined` ("Subscriber") set to unchecked.
  4. Save with `erp-undefined` ("Subscriber") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-008 — `erp-undefined` ("HR Manager") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm`.
  2. Save with `erp-undefined` ("HR Manager") set to checked.
  3. Save with `erp-undefined` ("HR Manager") set to unchecked.
  4. Save with `erp-undefined` ("HR Manager") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-009 — `erp-undefined` ("Employee") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm`.
  2. Save with `erp-undefined` ("Employee") set to checked.
  3. Save with `erp-undefined` ("Employee") set to unchecked.
  4. Save with `erp-undefined` ("Employee") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-010 — `erp-undefined` ("CRM Manager") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm`.
  2. Save with `erp-undefined` ("CRM Manager") set to checked.
  3. Save with `erp-undefined` ("CRM Manager") set to unchecked.
  4. Save with `erp-undefined` ("CRM Manager") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-011 — `erp-undefined` ("CRM Agent") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm`.
  2. Save with `erp-undefined` ("CRM Agent") set to checked.
  3. Save with `erp-undefined` ("CRM Agent") set to unchecked.
  4. Save with `erp-undefined` ("CRM Agent") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-012 — `erp-undefined` ("Accounting Manager") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm`.
  2. Save with `erp-undefined` ("Accounting Manager") set to checked.
  3. Save with `erp-undefined` ("Accounting Manager") set to unchecked.
  4. Save with `erp-undefined` ("Accounting Manager") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-013 — `erp-undefined` ("Customer") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm`.
  2. Save with `erp-undefined` ("Customer") set to checked.
  3. Save with `erp-undefined` ("Customer") set to unchecked.
  4. Save with `erp-undefined` ("Customer") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-014 — `erp-undefined` ("Shop manager") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm`.
  2. Save with `erp-undefined` ("Shop manager") set to checked.
  3. Save with `erp-undefined` ("Shop manager") set to unchecked.
  4. Save with `erp-undefined` ("Shop manager") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-015 — `erp-undefined` ("Recruiter") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm`.
  2. Save with `erp-undefined` ("Recruiter") set to checked.
  3. Save with `erp-undefined` ("Recruiter") set to unchecked.
  4. Save with `erp-undefined` ("Recruiter") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-016 — `admin.php?page=erp-settings#/erp-crm` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-crm`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 13 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-017 — `btn-rebuild` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce`.
  2. Save with `btn-rebuild` set to a single character.
  3. Save with `btn-rebuild` set to a 255-character value.
  4. Save with `btn-rebuild` set to a value with leading and trailing whitespace.
  5. Save with `btn-rebuild` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-018 — `admin.php?page=erp-settings#/erp-woocommerce` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-019 — `erp-undefined` ("Contact") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-ac`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac`.
  2. Save with `erp-undefined` ("Contact") set to checked.
  3. Save with `erp-undefined` ("Contact") set to unchecked.
  4. Save with `erp-undefined` ("Contact") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-020 — `erp-undefined` ("Company") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-ac`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac`.
  2. Save with `erp-undefined` ("Company") set to checked.
  3. Save with `erp-undefined` ("Company") set to unchecked.
  4. Save with `erp-undefined` ("Company") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-021 — `admin.php?page=erp-settings#/erp-ac` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-ac`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-022 — `erp-from_name` ("Sender Name The senders name appears on the outgoing emails") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email`.
  2. Save with `erp-from_name` ("Sender Name The senders name appears on the outgoing emails") set to a single character.
  3. Save with `erp-from_name` ("Sender Name The senders name appears on the outgoing emails") set to a 255-character value.
  4. Save with `erp-from_name` ("Sender Name The senders name appears on the outgoing emails") set to a value with leading and trailing whitespace.
  5. Save with `erp-from_name` ("Sender Name The senders name appears on the outgoing emails") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-023 — `erp-from_email` ("Sender Address The senders email appears on the outgoing emails") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email`.
  2. Save with `erp-from_email` ("Sender Address The senders email appears on the outgoing emails") set to a single character.
  3. Save with `erp-from_email` ("Sender Address The senders email appears on the outgoing emails") set to a 255-character value.
  4. Save with `erp-from_email` ("Sender Address The senders email appears on the outgoing emails") set to a value with leading and trailing whitespace.
  5. Save with `erp-from_email` ("Sender Address The senders email appears on the outgoing emails") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-024 — `erp-header_image` ("Header Image Upload a logo/banner and provide the URL here.") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email`.
  2. Save with `erp-header_image` ("Header Image Upload a logo/banner and provide the URL here.") set to a single character.
  3. Save with `erp-header_image` ("Header Image Upload a logo/banner and provide the URL here.") set to a 255-character value.
  4. Save with `erp-header_image` ("Header Image Upload a logo/banner and provide the URL here.") set to a value with leading and trailing whitespace.
  5. Save with `erp-header_image` ("Header Image Upload a logo/banner and provide the URL here.") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-025 — `erp-footer_text` ("Footer Text The text apears on each emails footer area.") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email`.
  2. Save with `erp-footer_text` ("Footer Text The text apears on each emails footer area.") set to a 5,000-character body.
  3. Save with `erp-footer_text` ("Footer Text The text apears on each emails footer area.") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-026 — `admin.php?page=erp-settings#/erp-email` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-email`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 4 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-027 — `erp-enable_extra_leave` ("Extra Unpaid Leave") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/leave`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/leave`.
  2. Save with `erp-enable_extra_leave` ("Extra Unpaid Leave") set to checked.
  3. Save with `erp-enable_extra_leave` ("Extra Unpaid Leave") set to unchecked.
  4. Save with `erp-enable_extra_leave` ("Extra Unpaid Leave") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-028 — `erp-enable_auto_leave_policy_assignment_on_type_change` ("Auto Assign Leave Policy") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/leave`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/leave`.
  2. Save with `erp-enable_auto_leave_policy_assignment_on_type_change` ("Auto Assign Leave Policy") set to checked.
  3. Save with `erp-enable_auto_leave_policy_assignment_on_type_change` ("Auto Assign Leave Policy") set to unchecked.
  4. Save with `erp-enable_auto_leave_policy_assignment_on_type_change` ("Auto Assign Leave Policy") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-029 — `erp-erp_pro_accrual_leave` ("Enable Accrual") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/leave`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/leave`.
  2. Save with `erp-erp_pro_accrual_leave` ("Enable Accrual") set to checked.
  3. Save with `erp-erp_pro_accrual_leave` ("Enable Accrual") set to unchecked.
  4. Save with `erp-erp_pro_accrual_leave` ("Enable Accrual") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-030 — `erp-erp_pro_carry_encash_leave` ("Enable Carry / Encash") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/leave`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/leave`.
  2. Save with `erp-erp_pro_carry_encash_leave` ("Enable Carry / Encash") set to checked.
  3. Save with `erp-erp_pro_carry_encash_leave` ("Enable Carry / Encash") set to unchecked.
  4. Save with `erp-erp_pro_carry_encash_leave` ("Enable Carry / Encash") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-031 — `erp-erp_pro_half_leave` ("Enable Half-Day Request") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/leave`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/leave`.
  2. Save with `erp-erp_pro_half_leave` ("Enable Half-Day Request") set to checked.
  3. Save with `erp-erp_pro_half_leave` ("Enable Half-Day Request") set to unchecked.
  4. Save with `erp-erp_pro_half_leave` ("Enable Half-Day Request") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-032 — `erp-erp_pro_multilevel_approval` ("Enable Multilevel Approval") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/leave`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/leave`.
  2. Save with `erp-erp_pro_multilevel_approval` ("Enable Multilevel Approval") set to checked.
  3. Save with `erp-erp_pro_multilevel_approval` ("Enable Multilevel Approval") set to unchecked.
  4. Save with `erp-erp_pro_multilevel_approval` ("Enable Multilevel Approval") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-033 — `erp-erp_pro_seg_leave` ("Enable Segregation") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/leave`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/leave`.
  2. Save with `erp-erp_pro_seg_leave` ("Enable Segregation") set to checked.
  3. Save with `erp-erp_pro_seg_leave` ("Enable Segregation") set to unchecked.
  4. Save with `erp-erp_pro_seg_leave` ("Enable Segregation") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-034 — `erp-erp_pro_sandwich_leave` ("Enable Sandwich Rule") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/leave`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/leave`.
  2. Save with `erp-erp_pro_sandwich_leave` ("Enable Sandwich Rule") set to checked.
  3. Save with `erp-erp_pro_sandwich_leave` ("Enable Sandwich Rule") set to unchecked.
  4. Save with `erp-erp_pro_sandwich_leave` ("Enable Sandwich Rule") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-035 — `admin.php?page=erp-settings#/erp-hr/leave` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-hr/leave`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/leave`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 8 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-036 — `dp1787046901139` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/financial`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/financial`.
  2. Save with `dp1787046901139` set to a single character.
  3. Save with `dp1787046901139` set to a 255-character value.
  4. Save with `dp1787046901139` set to a value with leading and trailing whitespace.
  5. Save with `dp1787046901139` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-037 — `dp1787046901140` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/financial`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/financial`.
  2. Save with `dp1787046901140` set to a single character.
  3. Save with `dp1787046901140` set to a 255-character value.
  4. Save with `dp1787046901140` set to a value with leading and trailing whitespace.
  5. Save with `dp1787046901140` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-038 — `admin.php?page=erp-settings#/erp-hr/financial` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-hr/financial`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/financial`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-039 — `erp-erp_hr_remote_work_enable` ("Enable Remote Work") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/remote_work`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/remote_work`.
  2. Save with `erp-erp_hr_remote_work_enable` ("Enable Remote Work") set to checked.
  3. Save with `erp-erp_hr_remote_work_enable` ("Enable Remote Work") set to unchecked.
  4. Save with `erp-erp_hr_remote_work_enable` ("Enable Remote Work") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-040 — `admin.php?page=erp-settings#/erp-hr/remote_work` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-hr/remote_work`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/remote_work`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-041 — `erp-erp_hrm_remove_wp_user` ("Remove WP User") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/miscellaneous`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/miscellaneous`.
  2. Save with `erp-erp_hrm_remove_wp_user` ("Remove WP User") set to checked.
  3. Save with `erp-erp_hrm_remove_wp_user` ("Remove WP User") set to unchecked.
  4. Save with `erp-erp_hrm_remove_wp_user` ("Remove WP User") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-042 — `erp-erp_hrm_hide_pay_rate` ("Hide Pay Rate") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/miscellaneous`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/miscellaneous`.
  2. Save with `erp-erp_hrm_hide_pay_rate` ("Hide Pay Rate") set to checked.
  3. Save with `erp-erp_hrm_hide_pay_rate` ("Hide Pay Rate") set to unchecked.
  4. Save with `erp-erp_hrm_hide_pay_rate` ("Hide Pay Rate") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-043 — `admin.php?page=erp-settings#/erp-hr/miscellaneous` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-hr/miscellaneous`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/miscellaneous`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-044 — `erp-hr_frontend_slug` ("HR Dashboard Slug") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/hr_frontend`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/hr_frontend`.
  2. Save with `erp-hr_frontend_slug` ("HR Dashboard Slug") set to a single character.
  3. Save with `erp-hr_frontend_slug` ("HR Dashboard Slug") set to a 255-character value.
  4. Save with `erp-hr_frontend_slug` ("HR Dashboard Slug") set to a value with leading and trailing whitespace.
  5. Save with `erp-hr_frontend_slug` ("HR Dashboard Slug") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-045 — `erp-hr_frontend_dashboard_title` ("HR Dashboard Title") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/hr_frontend`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/hr_frontend`.
  2. Save with `erp-hr_frontend_dashboard_title` ("HR Dashboard Title") set to a single character.
  3. Save with `erp-hr_frontend_dashboard_title` ("HR Dashboard Title") set to a 255-character value.
  4. Save with `erp-hr_frontend_dashboard_title` ("HR Dashboard Title") set to a value with leading and trailing whitespace.
  5. Save with `erp-hr_frontend_dashboard_title` ("HR Dashboard Title") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-046 — `erp-hr_frontend_redirect` ("Redirect to frontend") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/hr_frontend`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/hr_frontend`.
  2. Save with `erp-hr_frontend_redirect` ("Redirect to frontend") set to checked.
  3. Save with `erp-hr_frontend_redirect` ("Redirect to frontend") set to unchecked.
  4. Save with `erp-hr_frontend_redirect` ("Redirect to frontend") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-047 — `admin.php?page=erp-settings#/erp-hr/hr_frontend` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-hr/hr_frontend`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/hr_frontend`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 3 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-048 — `erp-recruitment_api_url` ("Global Api") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/recruitment`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/recruitment`.
  2. Save with `erp-recruitment_api_url` ("Global Api") set to a single character.
  3. Save with `erp-recruitment_api_url` ("Global Api") set to a 255-character value.
  4. Save with `erp-recruitment_api_url` ("Global Api") set to a value with leading and trailing whitespace.
  5. Save with `erp-recruitment_api_url` ("Global Api") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-049 — `admin.php?page=erp-settings#/erp-hr/recruitment` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-hr/recruitment`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/recruitment`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-050 — `erp_payroll_payment_method_settings` ("Select a method") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/payroll`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/payroll`.
  2. Save with `erp_payroll_payment_method_settings` ("Select a method") set to the first real option.
  3. Save with `erp_payroll_payment_method_settings` ("Select a method") set to the last option.
  4. Save with `erp_payroll_payment_method_settings` ("Select a method") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-051 — `erp_payroll_payment_bank_settings` ("Select a bank") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/payroll`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/payroll`.
  2. Save with `erp_payroll_payment_bank_settings` ("Select a bank") set to the first real option.
  3. Save with `erp_payroll_payment_bank_settings` ("Select a bank") set to the last option.
  4. Save with `erp_payroll_payment_bank_settings` ("Select a bank") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-052 — `admin.php?page=erp-settings#/erp-hr/payroll` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-hr/payroll`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/payroll`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-053 — `erp-grace_before_checkin` ("Grace Before Checkin") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/attendance`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/attendance`.
  2. Save with `erp-grace_before_checkin` ("Grace Before Checkin") set to a single character.
  3. Save with `erp-grace_before_checkin` ("Grace Before Checkin") set to a 255-character value.
  4. Save with `erp-grace_before_checkin` ("Grace Before Checkin") set to a value with leading and trailing whitespace.
  5. Save with `erp-grace_before_checkin` ("Grace Before Checkin") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-054 — `erp-grace_after_checkin` ("Grace After Checkin") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/attendance`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/attendance`.
  2. Save with `erp-grace_after_checkin` ("Grace After Checkin") set to a single character.
  3. Save with `erp-grace_after_checkin` ("Grace After Checkin") set to a 255-character value.
  4. Save with `erp-grace_after_checkin` ("Grace After Checkin") set to a value with leading and trailing whitespace.
  5. Save with `erp-grace_after_checkin` ("Grace After Checkin") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-055 — `erp-erp_att_diff_threshhold` ("Threshhold between checkout & checkin") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/attendance`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/attendance`.
  2. Save with `erp-erp_att_diff_threshhold` ("Threshhold between checkout & checkin") set to a single character.
  3. Save with `erp-erp_att_diff_threshhold` ("Threshhold between checkout & checkin") set to a 255-character value.
  4. Save with `erp-erp_att_diff_threshhold` ("Threshhold between checkout & checkin") set to a value with leading and trailing whitespace.
  5. Save with `erp-erp_att_diff_threshhold` ("Threshhold between checkout & checkin") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-056 — `erp-grace_before_checkout` ("Grace Before Checkout") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/attendance`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/attendance`.
  2. Save with `erp-grace_before_checkout` ("Grace Before Checkout") set to a single character.
  3. Save with `erp-grace_before_checkout` ("Grace Before Checkout") set to a 255-character value.
  4. Save with `erp-grace_before_checkout` ("Grace Before Checkout") set to a value with leading and trailing whitespace.
  5. Save with `erp-grace_before_checkout` ("Grace Before Checkout") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-057 — `erp-grace_after_checkout` ("Grace After Checkout") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/attendance`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/attendance`.
  2. Save with `erp-grace_after_checkout` ("Grace After Checkout") set to a single character.
  3. Save with `erp-grace_after_checkout` ("Grace After Checkout") set to a 255-character value.
  4. Save with `erp-grace_after_checkout` ("Grace After Checkout") set to a value with leading and trailing whitespace.
  5. Save with `erp-grace_after_checkout` ("Grace After Checkout") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-058 — `erp-enable_self_att` ("Self Attendance") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/attendance`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/attendance`.
  2. Save with `erp-enable_self_att` ("Self Attendance") set to checked.
  3. Save with `erp-enable_self_att` ("Self Attendance") set to unchecked.
  4. Save with `erp-enable_self_att` ("Self Attendance") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-059 — `erp-erp_at_enable_ip_restriction` ("IP Restriction") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/attendance`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/attendance`.
  2. Save with `erp-erp_at_enable_ip_restriction` ("IP Restriction") set to checked.
  3. Save with `erp-erp_at_enable_ip_restriction` ("IP Restriction") set to unchecked.
  4. Save with `erp-erp_at_enable_ip_restriction` ("IP Restriction") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-060 — `erp-erp_at_whitelisted_ips` ("Whitelisted IP\'s") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/attendance`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/attendance`.
  2. Save with `erp-erp_at_whitelisted_ips` ("Whitelisted IP\'s") set to a 5,000-character body.
  3. Save with `erp-erp_at_whitelisted_ips` ("Whitelisted IP\'s") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-061 — `erp-attendance_reminder` ("Attendance Reminder") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/attendance`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/attendance`.
  2. Save with `erp-attendance_reminder` ("Attendance Reminder") set to checked.
  3. Save with `erp-attendance_reminder` ("Attendance Reminder") set to unchecked.
  4. Save with `erp-attendance_reminder` ("Attendance Reminder") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-062 — `admin.php?page=erp-settings#/erp-hr/attendance` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-hr/attendance`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/attendance`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 9 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-063 — `erp-undefined` ("Administrator") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm/contacts`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/contacts`.
  2. Save with `erp-undefined` ("Administrator") set to checked.
  3. Save with `erp-undefined` ("Administrator") set to unchecked.
  4. Save with `erp-undefined` ("Administrator") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-064 — `erp-undefined` ("Editor") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm/contacts`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/contacts`.
  2. Save with `erp-undefined` ("Editor") set to checked.
  3. Save with `erp-undefined` ("Editor") set to unchecked.
  4. Save with `erp-undefined` ("Editor") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-065 — `erp-undefined` ("Author") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm/contacts`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/contacts`.
  2. Save with `erp-undefined` ("Author") set to checked.
  3. Save with `erp-undefined` ("Author") set to unchecked.
  4. Save with `erp-undefined` ("Author") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-066 — `erp-undefined` ("Contributor") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm/contacts`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/contacts`.
  2. Save with `erp-undefined` ("Contributor") set to checked.
  3. Save with `erp-undefined` ("Contributor") set to unchecked.
  4. Save with `erp-undefined` ("Contributor") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-067 — `erp-undefined` ("Subscriber") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm/contacts`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/contacts`.
  2. Save with `erp-undefined` ("Subscriber") set to checked.
  3. Save with `erp-undefined` ("Subscriber") set to unchecked.
  4. Save with `erp-undefined` ("Subscriber") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-068 — `erp-undefined` ("HR Manager") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm/contacts`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/contacts`.
  2. Save with `erp-undefined` ("HR Manager") set to checked.
  3. Save with `erp-undefined` ("HR Manager") set to unchecked.
  4. Save with `erp-undefined` ("HR Manager") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-069 — `erp-undefined` ("Employee") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm/contacts`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/contacts`.
  2. Save with `erp-undefined` ("Employee") set to checked.
  3. Save with `erp-undefined` ("Employee") set to unchecked.
  4. Save with `erp-undefined` ("Employee") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-070 — `erp-undefined` ("CRM Manager") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm/contacts`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/contacts`.
  2. Save with `erp-undefined` ("CRM Manager") set to checked.
  3. Save with `erp-undefined` ("CRM Manager") set to unchecked.
  4. Save with `erp-undefined` ("CRM Manager") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-071 — `erp-undefined` ("CRM Agent") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm/contacts`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/contacts`.
  2. Save with `erp-undefined` ("CRM Agent") set to checked.
  3. Save with `erp-undefined` ("CRM Agent") set to unchecked.
  4. Save with `erp-undefined` ("CRM Agent") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-072 — `erp-undefined` ("Accounting Manager") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm/contacts`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/contacts`.
  2. Save with `erp-undefined` ("Accounting Manager") set to checked.
  3. Save with `erp-undefined` ("Accounting Manager") set to unchecked.
  4. Save with `erp-undefined` ("Accounting Manager") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-073 — `erp-undefined` ("Customer") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm/contacts`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/contacts`.
  2. Save with `erp-undefined` ("Customer") set to checked.
  3. Save with `erp-undefined` ("Customer") set to unchecked.
  4. Save with `erp-undefined` ("Customer") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-074 — `erp-undefined` ("Shop manager") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm/contacts`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/contacts`.
  2. Save with `erp-undefined` ("Shop manager") set to checked.
  3. Save with `erp-undefined` ("Shop manager") set to unchecked.
  4. Save with `erp-undefined` ("Shop manager") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-075 — `erp-undefined` ("Recruiter") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm/contacts`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/contacts`.
  2. Save with `erp-undefined` ("Recruiter") set to checked.
  3. Save with `erp-undefined` ("Recruiter") set to unchecked.
  4. Save with `erp-undefined` ("Recruiter") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-076 — `admin.php?page=erp-settings#/erp-crm/contacts` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-crm/contacts`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/contacts`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 13 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-077 — `erp-is_enabled` ("Enable signup confirmation If you enable this option, your subscribers will first receive a confirmation email after they subscribe. Once they confirm their subscription (via this email), they will be marked as 'subscribed'.") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm/subscription`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/subscription`.
  2. Save with `erp-is_enabled` ("Enable signup confirmation If you enable this option, your subscribers will first receive a confirmation email after they subscribe. Once they confirm their subscription (via this email), they will be marked as 'subscribed'.") set to checked.
  3. Save with `erp-is_enabled` ("Enable signup confirmation If you enable this option, your subscribers will first receive a confirmation email after they subscribe. Once they confirm their subscription (via this email), they will be marked as 'subscribed'.") set to unchecked.
  4. Save with `erp-is_enabled` ("Enable signup confirmation If you enable this option, your subscribers will first receive a confirmation email after they subscribe. Once they confirm their subscription (via this email), they will be marked as 'subscribed'.") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-078 — `erp-email_subject` ("Email subject") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm/subscription`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/subscription`.
  2. Save with `erp-email_subject` ("Email subject") set to a single character.
  3. Save with `erp-email_subject` ("Email subject") set to a 255-character value.
  4. Save with `erp-email_subject` ("Email subject") set to a value with leading and trailing whitespace.
  5. Save with `erp-email_subject` ("Email subject") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-079 — `erp-email_content` ("Email content") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm/subscription`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/subscription`.
  2. Save with `erp-email_content` ("Email content") set to a 5,000-character body.
  3. Save with `erp-email_content` ("Email content") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-080 — `erp-confirm_page_title` ("Confirmation Page") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm/subscription`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/subscription`.
  2. Save with `erp-confirm_page_title` ("Confirmation Page") set to a single character.
  3. Save with `erp-confirm_page_title` ("Confirmation Page") set to a 255-character value.
  4. Save with `erp-confirm_page_title` ("Confirmation Page") set to a value with leading and trailing whitespace.
  5. Save with `erp-confirm_page_title` ("Confirmation Page") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-081 — `erp-confirm_page_content` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm/subscription`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/subscription`.
  2. Save with `erp-confirm_page_content` set to a 5,000-character body.
  3. Save with `erp-confirm_page_content` set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-082 — `erp-unsubs_page_title` ("Unsubscribe Page") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm/subscription`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/subscription`.
  2. Save with `erp-unsubs_page_title` ("Unsubscribe Page") set to a single character.
  3. Save with `erp-unsubs_page_title` ("Unsubscribe Page") set to a 255-character value.
  4. Save with `erp-unsubs_page_title` ("Unsubscribe Page") set to a value with leading and trailing whitespace.
  5. Save with `erp-unsubs_page_title` ("Unsubscribe Page") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-083 — `erp-unsubs_page_content` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm/subscription`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/subscription`.
  2. Save with `erp-unsubs_page_content` set to a 5,000-character body.
  3. Save with `erp-unsubs_page_content` set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-084 — `erp-edit_sub_page_title` ("Edit Subscription Page") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm/subscription`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/subscription`.
  2. Save with `erp-edit_sub_page_title` ("Edit Subscription Page") set to a single character.
  3. Save with `erp-edit_sub_page_title` ("Edit Subscription Page") set to a 255-character value.
  4. Save with `erp-edit_sub_page_title` ("Edit Subscription Page") set to a value with leading and trailing whitespace.
  5. Save with `erp-edit_sub_page_title` ("Edit Subscription Page") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-085 — `erp-edit_sub_page_content` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-crm/subscription`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/subscription`.
  2. Save with `erp-edit_sub_page_content` set to a 5,000-character body.
  3. Save with `erp-edit_sub_page_content` set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-086 — `admin.php?page=erp-settings#/erp-crm/subscription` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-crm/subscription`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-crm/subscription`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 9 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-087 — `btn-rebuild` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/wc_sync`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/wc_sync`.
  2. Save with `btn-rebuild` set to a single character.
  3. Save with `btn-rebuild` set to a 255-character value.
  4. Save with `btn-rebuild` set to a value with leading and trailing whitespace.
  5. Save with `btn-rebuild` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-088 — `admin.php?page=erp-settings#/erp-woocommerce/wc_sync` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/wc_sync`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/wc_sync`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-089 — `erp-show_wc_signup_option` ("Show signup on checkout") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/wc_subscription`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/wc_subscription`.
  2. Save with `erp-show_wc_signup_option` ("Show signup on checkout") set to checked.
  3. Save with `erp-show_wc_signup_option` ("Show signup on checkout") set to unchecked.
  4. Save with `erp-show_wc_signup_option` ("Show signup on checkout") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-090 — `erp-wc_signup_option_label` ("Signup option label") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/wc_subscription`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/wc_subscription`.
  2. Save with `erp-wc_signup_option_label` ("Signup option label") set to a single character.
  3. Save with `erp-wc_signup_option_label` ("Signup option label") set to a 255-character value.
  4. Save with `erp-wc_signup_option_label` ("Signup option label") set to a value with leading and trailing whitespace.
  5. Save with `erp-wc_signup_option_label` ("Signup option label") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-091 — `admin.php?page=erp-settings#/erp-woocommerce/wc_subscription` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/wc_subscription`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/wc_subscription`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-092 — `switchRounded` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/crm`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/crm`.
  2. Save with `switchRounded` set to checked.
  3. Save with `switchRounded` set to unchecked.
  4. Save with `switchRounded` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-093 — `admin.php?page=erp-settings#/erp-woocommerce/crm` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/crm`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/crm`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-094 — `switchRounded` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/accounting`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/accounting`.
  2. Save with `switchRounded` set to checked.
  3. Save with `switchRounded` set to unchecked.
  4. Save with `switchRounded` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-095 — `switchRounded` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/accounting`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/accounting`.
  2. Save with `switchRounded` set to checked.
  3. Save with `switchRounded` set to unchecked.
  4. Save with `switchRounded` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-096 — `switchRounded` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/accounting`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/accounting`.
  2. Save with `switchRounded` set to checked.
  3. Save with `switchRounded` set to unchecked.
  4. Save with `switchRounded` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-097 — `admin.php?page=erp-settings#/erp-woocommerce/accounting` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/accounting`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/accounting`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 3 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-098 — `btn-rebuild` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/orders`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/orders`.
  2. Save with `btn-rebuild` set to a single character.
  3. Save with `btn-rebuild` set to a 255-character value.
  4. Save with `btn-rebuild` set to a value with leading and trailing whitespace.
  5. Save with `btn-rebuild` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-099 — `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/orders` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/orders`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/orders`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-100 — `switchRounded` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/products`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/products`.
  2. Save with `switchRounded` set to checked.
  3. Save with `switchRounded` set to unchecked.
  4. Save with `switchRounded` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-101 — `btn-rebuild` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/products`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/products`.
  2. Save with `btn-rebuild` set to a single character.
  3. Save with `btn-rebuild` set to a 255-character value.
  4. Save with `btn-rebuild` set to a value with leading and trailing whitespace.
  5. Save with `btn-rebuild` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-102 — `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/products` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/products`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-woocommerce/wc_sync/products`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-103 — `erp-undefined` ("Contact") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-ac/customers`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/customers`.
  2. Save with `erp-undefined` ("Contact") set to checked.
  3. Save with `erp-undefined` ("Contact") set to unchecked.
  4. Save with `erp-undefined` ("Contact") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-104 — `erp-undefined` ("Company") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-ac/customers`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/customers`.
  2. Save with `erp-undefined` ("Company") set to checked.
  3. Save with `erp-undefined` ("Company") set to unchecked.
  4. Save with `erp-undefined` ("Company") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-105 — `admin.php?page=erp-settings#/erp-ac/customers` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-ac/customers`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/customers`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-106 — `erp-erp_ac_th_separator` ("Thousand Separator") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-ac/currency_option`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/currency_option`.
  2. Save with `erp-erp_ac_th_separator` ("Thousand Separator") set to a single character.
  3. Save with `erp-erp_ac_th_separator` ("Thousand Separator") set to a 255-character value.
  4. Save with `erp-erp_ac_th_separator` ("Thousand Separator") set to a value with leading and trailing whitespace.
  5. Save with `erp-erp_ac_th_separator` ("Thousand Separator") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-107 — `erp-erp_ac_de_separator` ("Decimal Separator") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-ac/currency_option`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/currency_option`.
  2. Save with `erp-erp_ac_de_separator` ("Decimal Separator") set to a single character.
  3. Save with `erp-erp_ac_de_separator` ("Decimal Separator") set to a 255-character value.
  4. Save with `erp-erp_ac_de_separator` ("Decimal Separator") set to a value with leading and trailing whitespace.
  5. Save with `erp-erp_ac_de_separator` ("Decimal Separator") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-108 — `admin.php?page=erp-settings#/erp-ac/currency_option` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-ac/currency_option`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/currency_option`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-109 — `dp1787046901141` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-ac/opening_balance`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/opening_balance`.
  2. Save with `dp1787046901141` set to a single character.
  3. Save with `dp1787046901141` set to a 255-character value.
  4. Save with `dp1787046901141` set to a value with leading and trailing whitespace.
  5. Save with `dp1787046901141` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-110 — `dp1787046901142` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-ac/opening_balance`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/opening_balance`.
  2. Save with `dp1787046901142` set to a single character.
  3. Save with `dp1787046901142` set to a 255-character value.
  4. Save with `dp1787046901142` set to a value with leading and trailing whitespace.
  5. Save with `dp1787046901142` set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-111 — `admin.php?page=erp-settings#/erp-ac/opening_balance` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-ac/opening_balance`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/opening_balance`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-112 — `erp-from_name` ("Sender Name The senders name appears on the outgoing emails") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email/general`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/general`.
  2. Save with `erp-from_name` ("Sender Name The senders name appears on the outgoing emails") set to a single character.
  3. Save with `erp-from_name` ("Sender Name The senders name appears on the outgoing emails") set to a 255-character value.
  4. Save with `erp-from_name` ("Sender Name The senders name appears on the outgoing emails") set to a value with leading and trailing whitespace.
  5. Save with `erp-from_name` ("Sender Name The senders name appears on the outgoing emails") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-113 — `erp-from_email` ("Sender Address The senders email appears on the outgoing emails") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email/general`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/general`.
  2. Save with `erp-from_email` ("Sender Address The senders email appears on the outgoing emails") set to a single character.
  3. Save with `erp-from_email` ("Sender Address The senders email appears on the outgoing emails") set to a 255-character value.
  4. Save with `erp-from_email` ("Sender Address The senders email appears on the outgoing emails") set to a value with leading and trailing whitespace.
  5. Save with `erp-from_email` ("Sender Address The senders email appears on the outgoing emails") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-114 — `erp-header_image` ("Header Image Upload a logo/banner and provide the URL here.") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email/general`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/general`.
  2. Save with `erp-header_image` ("Header Image Upload a logo/banner and provide the URL here.") set to a single character.
  3. Save with `erp-header_image` ("Header Image Upload a logo/banner and provide the URL here.") set to a 255-character value.
  4. Save with `erp-header_image` ("Header Image Upload a logo/banner and provide the URL here.") set to a value with leading and trailing whitespace.
  5. Save with `erp-header_image` ("Header Image Upload a logo/banner and provide the URL here.") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-115 — `erp-footer_text` ("Footer Text The text apears on each emails footer area.") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email/general`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/general`.
  2. Save with `erp-footer_text` ("Footer Text The text apears on each emails footer area.") set to a 5,000-character body.
  3. Save with `erp-footer_text` ("Footer Text The text apears on each emails footer area.") set to text containing newlines and emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-116 — `admin.php?page=erp-settings#/erp-email/general` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-email/general`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/general`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 4 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-117 — `switchRounded` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email/email_connect`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/email_connect`.
  2. Save with `switchRounded` set to checked.
  3. Save with `switchRounded` set to unchecked.
  4. Save with `switchRounded` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-118 — `erp_wpmail_test_email` ("Test Mail") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email/email_connect`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/email_connect`.
  2. Save with `erp_wpmail_test_email` ("Test Mail") set to an address with a plus tag (`a+b@example.test`).
  3. Save with `erp_wpmail_test_email` ("Test Mail") set to an address at the 254-character RFC limit.
  4. Save with `erp_wpmail_test_email` ("Test Mail") set to unicode in the local part.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-119 — `admin.php?page=erp-settings#/erp-email/email_connect` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-email/email_connect`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/email_connect`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-120 — `switchRounded` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email/notification`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/notification`.
  2. Save with `switchRounded` set to checked.
  3. Save with `switchRounded` set to unchecked.
  4. Save with `switchRounded` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-121 — `switchRounded` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email/notification`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/notification`.
  2. Save with `switchRounded` set to checked.
  3. Save with `switchRounded` set to unchecked.
  4. Save with `switchRounded` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-122 — `switchRounded` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email/notification`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/notification`.
  2. Save with `switchRounded` set to checked.
  3. Save with `switchRounded` set to unchecked.
  4. Save with `switchRounded` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-123 — `switchRounded` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email/notification`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/notification`.
  2. Save with `switchRounded` set to checked.
  3. Save with `switchRounded` set to unchecked.
  4. Save with `switchRounded` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-124 — `switchRounded` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email/notification`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/notification`.
  2. Save with `switchRounded` set to checked.
  3. Save with `switchRounded` set to unchecked.
  4. Save with `switchRounded` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-125 — `switchRounded` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email/notification`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/notification`.
  2. Save with `switchRounded` set to checked.
  3. Save with `switchRounded` set to unchecked.
  4. Save with `switchRounded` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-126 — `switchRounded` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email/notification`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/notification`.
  2. Save with `switchRounded` set to checked.
  3. Save with `switchRounded` set to unchecked.
  4. Save with `switchRounded` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-127 — `switchRounded` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email/notification`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/notification`.
  2. Save with `switchRounded` set to checked.
  3. Save with `switchRounded` set to unchecked.
  4. Save with `switchRounded` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-128 — `switchRounded` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email/notification`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/notification`.
  2. Save with `switchRounded` set to checked.
  3. Save with `switchRounded` set to unchecked.
  4. Save with `switchRounded` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-129 — `switchRounded` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email/notification`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/notification`.
  2. Save with `switchRounded` set to checked.
  3. Save with `switchRounded` set to unchecked.
  4. Save with `switchRounded` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-130 — `switchRounded` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email/notification`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/notification`.
  2. Save with `switchRounded` set to checked.
  3. Save with `switchRounded` set to unchecked.
  4. Save with `switchRounded` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-131 — `switchRounded` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email/notification`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/notification`.
  2. Save with `switchRounded` set to checked.
  3. Save with `switchRounded` set to unchecked.
  4. Save with `switchRounded` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-132 — `switchRounded` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email/notification`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/notification`.
  2. Save with `switchRounded` set to checked.
  3. Save with `switchRounded` set to unchecked.
  4. Save with `switchRounded` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-133 — `admin.php?page=erp-settings#/erp-email/notification` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-email/notification`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/notification`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 13 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-134 — `switchRounded` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email/hrm_digest_email`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/hrm_digest_email`.
  2. Save with `switchRounded` set to checked.
  3. Save with `switchRounded` set to unchecked.
  4. Save with `switchRounded` set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-135 — `admin.php?page=erp-settings#/erp-email/hrm_digest_email` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-email/hrm_digest_email`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/hrm_digest_email`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-136 — `href` accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-email/templates`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/templates`.
  2. Save with `href` set to a scheme-less host (`example.test`).
  3. Save with `href` set to an `https://` URL with a query string and a fragment.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-137 — `admin.php?page=erp-settings#/erp-email/templates` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-email/templates`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-email/templates`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 1 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-138 — `erp_payroll_payment_method_settings` ("Select a method") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/payroll/payment`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/payroll/payment`.
  2. Save with `erp_payroll_payment_method_settings` ("Select a method") set to the first real option.
  3. Save with `erp_payroll_payment_method_settings` ("Select a method") set to the last option.
  4. Save with `erp_payroll_payment_method_settings` ("Select a method") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-139 — `erp_payroll_payment_bank_settings` ("Select a bank") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-hr/payroll/payment`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/payroll/payment`.
  2. Save with `erp_payroll_payment_bank_settings` ("Select a bank") set to the first real option.
  3. Save with `erp_payroll_payment_bank_settings` ("Select a bank") set to the last option.
  4. Save with `erp_payroll_payment_bank_settings` ("Select a bank") set to leaving the placeholder option selected.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-140 — `admin.php?page=erp-settings#/erp-hr/payroll/payment` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-hr/payroll/payment`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-hr/payroll/payment`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 2 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-141 — `erp-erp_pg_paypal_enable_disable` ("Enable/Disable") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-ac/payment/paypal`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/payment/paypal`.
  2. Save with `erp-erp_pg_paypal_enable_disable` ("Enable/Disable") set to checked.
  3. Save with `erp-erp_pg_paypal_enable_disable` ("Enable/Disable") set to unchecked.
  4. Save with `erp-erp_pg_paypal_enable_disable` ("Enable/Disable") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-142 — `erp-erp_pg_paypal_title` ("Title This is the title which user see on payment options") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-ac/payment/paypal`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/payment/paypal`.
  2. Save with `erp-erp_pg_paypal_title` ("Title This is the title which user see on payment options") set to a single character.
  3. Save with `erp-erp_pg_paypal_title` ("Title This is the title which user see on payment options") set to a 255-character value.
  4. Save with `erp-erp_pg_paypal_title` ("Title This is the title which user see on payment options") set to a value with leading and trailing whitespace.
  5. Save with `erp-erp_pg_paypal_title` ("Title This is the title which user see on payment options") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-143 — `erp-erp_pg_paypal_description` ("Description This is the description which user see on payment options") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-ac/payment/paypal`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/payment/paypal`.
  2. Save with `erp-erp_pg_paypal_description` ("Description This is the description which user see on payment options") set to a single character.
  3. Save with `erp-erp_pg_paypal_description` ("Description This is the description which user see on payment options") set to a 255-character value.
  4. Save with `erp-erp_pg_paypal_description` ("Description This is the description which user see on payment options") set to a value with leading and trailing whitespace.
  5. Save with `erp-erp_pg_paypal_description` ("Description This is the description which user see on payment options") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-144 — `erp-erp_pg_paypal_receiver_email` ("Paypal Email Please enter your PayPal email address") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-ac/payment/paypal`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/payment/paypal`.
  2. Save with `erp-erp_pg_paypal_receiver_email` ("Paypal Email Please enter your PayPal email address") set to a single character.
  3. Save with `erp-erp_pg_paypal_receiver_email` ("Paypal Email Please enter your PayPal email address") set to a 255-character value.
  4. Save with `erp-erp_pg_paypal_receiver_email` ("Paypal Email Please enter your PayPal email address") set to a value with leading and trailing whitespace.
  5. Save with `erp-erp_pg_paypal_receiver_email` ("Paypal Email Please enter your PayPal email address") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-145 — `erp-erp_pg_paypal_sandbox` ("Paypal Sandbox") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-ac/payment/paypal`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/payment/paypal`.
  2. Save with `erp-erp_pg_paypal_sandbox` ("Paypal Sandbox") set to checked.
  3. Save with `erp-erp_pg_paypal_sandbox` ("Paypal Sandbox") set to unchecked.
  4. Save with `erp-erp_pg_paypal_sandbox` ("Paypal Sandbox") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-146 — `admin.php?page=erp-settings#/erp-ac/payment/paypal` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-ac/payment/paypal`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/payment/paypal`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 5 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.

#### SETTINGS-T2-147 — `erp-erp_pg_stripe_enable_disable` ("Enable/Disable") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-ac/payment/stripe`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/payment/stripe`.
  2. Save with `erp-erp_pg_stripe_enable_disable` ("Enable/Disable") set to checked.
  3. Save with `erp-erp_pg_stripe_enable_disable` ("Enable/Disable") set to unchecked.
  4. Save with `erp-erp_pg_stripe_enable_disable` ("Enable/Disable") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-148 — `erp-erp_pg_stripe_title` ("Title This is the title which user see on payment options") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-ac/payment/stripe`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/payment/stripe`.
  2. Save with `erp-erp_pg_stripe_title` ("Title This is the title which user see on payment options") set to a single character.
  3. Save with `erp-erp_pg_stripe_title` ("Title This is the title which user see on payment options") set to a 255-character value.
  4. Save with `erp-erp_pg_stripe_title` ("Title This is the title which user see on payment options") set to a value with leading and trailing whitespace.
  5. Save with `erp-erp_pg_stripe_title` ("Title This is the title which user see on payment options") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-149 — `erp-erp_pg_stripe_description` ("Description This is the description which user see on payment options") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-ac/payment/stripe`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/payment/stripe`.
  2. Save with `erp-erp_pg_stripe_description` ("Description This is the description which user see on payment options") set to a single character.
  3. Save with `erp-erp_pg_stripe_description` ("Description This is the description which user see on payment options") set to a 255-character value.
  4. Save with `erp-erp_pg_stripe_description` ("Description This is the description which user see on payment options") set to a value with leading and trailing whitespace.
  5. Save with `erp-erp_pg_stripe_description` ("Description This is the description which user see on payment options") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-150 — `erp-erp_pg_stripe_live_secret_key` ("Live Secret Key Enter your Stripe Live Secret Key") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-ac/payment/stripe`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/payment/stripe`.
  2. Save with `erp-erp_pg_stripe_live_secret_key` ("Live Secret Key Enter your Stripe Live Secret Key") set to a single character.
  3. Save with `erp-erp_pg_stripe_live_secret_key` ("Live Secret Key Enter your Stripe Live Secret Key") set to a 255-character value.
  4. Save with `erp-erp_pg_stripe_live_secret_key` ("Live Secret Key Enter your Stripe Live Secret Key") set to a value with leading and trailing whitespace.
  5. Save with `erp-erp_pg_stripe_live_secret_key` ("Live Secret Key Enter your Stripe Live Secret Key") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-151 — `erp-erp_pg_stripe_live_publishable_key` ("Live Publishable Key Enter your Stripe Live Publishable Key") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-ac/payment/stripe`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/payment/stripe`.
  2. Save with `erp-erp_pg_stripe_live_publishable_key` ("Live Publishable Key Enter your Stripe Live Publishable Key") set to a single character.
  3. Save with `erp-erp_pg_stripe_live_publishable_key` ("Live Publishable Key Enter your Stripe Live Publishable Key") set to a 255-character value.
  4. Save with `erp-erp_pg_stripe_live_publishable_key` ("Live Publishable Key Enter your Stripe Live Publishable Key") set to a value with leading and trailing whitespace.
  5. Save with `erp-erp_pg_stripe_live_publishable_key` ("Live Publishable Key Enter your Stripe Live Publishable Key") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-152 — `erp-erp_pg_stripe_enable_testmode` ("Test Mode") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-ac/payment/stripe`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/payment/stripe`.
  2. Save with `erp-erp_pg_stripe_enable_testmode` ("Test Mode") set to checked.
  3. Save with `erp-erp_pg_stripe_enable_testmode` ("Test Mode") set to unchecked.
  4. Save with `erp-erp_pg_stripe_enable_testmode` ("Test Mode") set to toggled twice and saved.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-153 — `erp-erp_pg_stripe_test_secret_key` ("Test Secret Key Enter your Stripe Test Secret Key") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-ac/payment/stripe`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/payment/stripe`.
  2. Save with `erp-erp_pg_stripe_test_secret_key` ("Test Secret Key Enter your Stripe Test Secret Key") set to a single character.
  3. Save with `erp-erp_pg_stripe_test_secret_key` ("Test Secret Key Enter your Stripe Test Secret Key") set to a 255-character value.
  4. Save with `erp-erp_pg_stripe_test_secret_key` ("Test Secret Key Enter your Stripe Test Secret Key") set to a value with leading and trailing whitespace.
  5. Save with `erp-erp_pg_stripe_test_secret_key` ("Test Secret Key Enter your Stripe Test Secret Key") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-154 — `erp-erp_pg_stripe_test_publishable_key` ("Test Publishable Key Enter your Stripe Test Publishable Key") accepts its edge values

- **Surface:** `admin.php?page=erp-settings#/erp-ac/payment/stripe`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/payment/stripe`.
  2. Save with `erp-erp_pg_stripe_test_publishable_key` ("Test Publishable Key Enter your Stripe Test Publishable Key") set to a single character.
  3. Save with `erp-erp_pg_stripe_test_publishable_key` ("Test Publishable Key Enter your Stripe Test Publishable Key") set to a 255-character value.
  4. Save with `erp-erp_pg_stripe_test_publishable_key` ("Test Publishable Key Enter your Stripe Test Publishable Key") set to a value with leading and trailing whitespace.
  5. Save with `erp-erp_pg_stripe_test_publishable_key` ("Test Publishable Key Enter your Stripe Test Publishable Key") set to a value containing `&`, `<`, `"` and an emoji.
- **Expected:** Each value is either stored and echoed back unchanged, or rejected with a specific, field-level message — never silently dropped and never a 500.
- **Oracle:** UI echo after reload + the stored value in REST/DB.

#### SETTINGS-T2-155 — `admin.php?page=erp-settings#/erp-ac/payment/stripe` saves with only its required fields

- **Surface:** `admin.php?page=erp-settings#/erp-ac/payment/stripe`
- **Tags:** @tier2 @settings @edge
- **Steps:**
  1. Open `admin.php?page=erp-settings#/erp-ac/payment/stripe`.
  2. Leave every optional field blank.
  3. Submit.
- **Expected:** The record saves. The 8 optional fields store as empty/default and the detail view renders without an error.
- **Oracle:** REST/DB row + detail-view render.
