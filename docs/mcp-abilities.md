# WP ERP — MCP Abilities

This document explains how to use the **WordPress Abilities API** integration shipped with WP ERP. It exposes ERP operations (HRM, CRM, Accounting) as first-class **abilities** that AI assistants can discover and call via the [WordPress MCP Adapter](https://developer.wordpress.org/news/2026/02/from-abilities-to-ai-agents-introducing-the-wordpress-mcp-adapter/).

---

## Table of Contents

1. [Requirements](#requirements)
2. [How it works](#how-it-works)
3. [Setup — connecting an AI client via MCP](#setup--connecting-an-ai-client-via-mcp)
4. [Available abilities](#available-abilities)
   - [HRM](#hrm-abilities)
   - [CRM](#crm-abilities)
   - [Accounting](#accounting-abilities)
5. [Permission model](#permission-model)
6. [Extending — add your own abilities](#extending--add-your-own-abilities)
7. [Disabling the abilities](#disabling-the-abilities)

---

## Requirements

| Requirement | Version |
|---|---|
| WordPress | **6.9 or later** (Abilities API) |
| WP ERP | 1.17.3+ |
| WordPress MCP Adapter plugin | latest |
| PHP | 7.4+ |

> **WordPress < 6.9** — All `wp_register_ability*` calls are wrapped in `function_exists()` guards, so activating ERP on older WordPress versions is safe; the abilities are silently skipped.

---

## How it works

The Abilities API is WordPress 6.9's mechanism for plugins to declare discrete, typed, permission-protected operations. Each *ability* has:

- **Unique name** — namespaced slug, e.g. `wp-erp/hrm-list-employees`
- **`input_schema`** — JSON Schema describing accepted parameters
- **`output_schema`** — JSON Schema describing the returned data
- **`permission_callback`** — called before execution; maps directly to WP ERP's existing capabilities (e.g. `erp_list_employee`)
- **`execute_callback`** — the actual PHP code that runs

When the **WordPress MCP Adapter** plugin is active, these abilities are automatically exposed as MCP tools that AI clients (Claude Desktop, Cursor, VS Code AI, etc.) can call.

```
AI Client ──MCP──▶ WordPress MCP Adapter ──Abilities API──▶ WP ERP ability ──▶ ERP data
```

---

## Setup — connecting an AI client via MCP

### 1. Install the WordPress MCP Adapter

Install and activate the [WordPress MCP Adapter](https://wordpress.org/plugins/wp-mcp/) plugin. It is the bridge between the Abilities API and any MCP-compatible client.

### 2. Create an Application Password

1. Go to **Users → Profile** (or any user's profile).
2. Scroll to **Application Passwords**.
3. Enter a name (e.g. `Claude Desktop`) and click **Add New Application Password**.
4. Copy the generated password — it will not be shown again.

The user whose Application Password you use must have the WP ERP role that grants the required capability (e.g. **HRM Manager**, **CRM Manager**, **Accounting Manager**).

### 3. Configure your AI client

#### Claude Desktop

Add to `~/Library/Application Support/Claude/claude_desktop_config.json` (macOS) or the equivalent on your OS:

```json
{
  "mcpServers": {
    "wp-erp": {
      "command": "npx",
      "args": [
        "-y",
        "@modelcontextprotocol/server-wordpress",
        "--url",      "https://your-site.com",
        "--username", "your-wp-username",
        "--password", "xxxx xxxx xxxx xxxx xxxx xxxx"
      ]
    }
  }
}
```

Replace `https://your-site.com`, `your-wp-username`, and the Application Password accordingly.

#### Cursor / VS Code

Add a `.cursor/mcp.json` (or VS Code equivalent) at the project root:

```json
{
  "mcpServers": {
    "wp-erp": {
      "url": "https://your-site.com/wp-json/mcp/v1",
      "auth": {
        "type": "basic",
        "username": "your-wp-username",
        "password": "xxxx xxxx xxxx xxxx xxxx xxxx"
      }
    }
  }
}
```

### 4. Verify discovery

Ask your AI assistant: *"List all WP ERP tools available."* It should enumerate the abilities below. Alternatively, inspect the raw endpoint:

```
GET https://your-site.com/wp-json/mcp/v1/tools
Authorization: Basic <base64(username:app-password)>
```

---

## Available abilities

Abilities are grouped into three categories matching WP ERP's modules.

### HRM abilities

| Ability ID | Label | Required capability |
|---|---|---|
| `wp-erp/hrm-list-employees` | List Employees | `erp_list_employee` |
| `wp-erp/hrm-get-employee` | Get Employee | `erp_view_employee` |
| `wp-erp/hrm-create-employee` | Create Employee | `erp_create_employee` |
| `wp-erp/hrm-update-employee` | Update Employee | `erp_edit_employee` |
| `wp-erp/hrm-delete-employee` | Delete Employee | `erp_delete_employee` |
| `wp-erp/hrm-terminate-employee` | Terminate Employee | `erp_can_terminate` |
| `wp-erp/hrm-list-departments` | List Departments | `erp_view_list` |
| `wp-erp/hrm-manage-department` | Manage Department | `erp_manage_department` |
| `wp-erp/hrm-list-designations` | List Designations | `erp_view_list` |
| `wp-erp/hrm-manage-designation` | Manage Designation | `erp_manage_designation` |
| `wp-erp/hrm-create-leave-request` | Create Leave Request | `erp_leave_create_request` |
| `wp-erp/hrm-manage-leave` | Manage Leave Request | `erp_leave_manage` |
| `wp-erp/hrm-list-announcements` | List Announcements | `erp_view_announcement` |
| `wp-erp/hrm-create-announcement` | Create Announcement | `erp_crate_announcement` |

#### Example — list active employees in department 3

```json
{
  "ability": "wp-erp/hrm-list-employees",
  "input": {
    "number": 10,
    "offset": 0,
    "status": "active",
    "department": 3
  }
}
```

Response:

```json
{
  "employees": [ { "user_id": 42, "first_name": "Jane", "last_name": "Doe" } ],
  "total": 1
}
```

#### Example — create an employee

```json
{
  "ability": "wp-erp/hrm-create-employee",
  "input": {
    "first_name":  "Jane",
    "last_name":   "Doe",
    "email":       "jane.doe@example.com",
    "department":  3,
    "designation": 7,
    "hiring_date": "2026-06-01",
    "status":      "active"
  }
}
```

---

### CRM abilities

| Ability ID | Label | Required capability |
|---|---|---|
| `wp-erp/crm-list-contacts` | List CRM Contacts | `erp_crm_list_contact` |
| `wp-erp/crm-get-contact` | Get CRM Contact | `erp_crm_list_contact` |
| `wp-erp/crm-create-contact` | Create CRM Contact | `erp_crm_add_contact` |
| `wp-erp/crm-update-contact` | Update CRM Contact | `erp_crm_edit_contact` |
| `wp-erp/crm-delete-contact` | Delete CRM Contact | `erp_crm_delete_contact` |
| `wp-erp/crm-list-groups` | List Contact Groups | `erp_crm_manage_groups` |
| `wp-erp/crm-create-group` | Create Contact Group | `erp_crm_create_groups` |
| `wp-erp/crm-delete-group` | Delete Contact Group | `erp_crm_delete_groups` |
| `wp-erp/crm-list-activities` | List CRM Activities | `erp_crm_manage_activites` |
| `wp-erp/crm-list-schedules` | List CRM Schedules | `erp_crm_manage_schedules` |

#### Example — create a contact

```json
{
  "ability": "wp-erp/crm-create-contact",
  "input": {
    "first_name": "Alice",
    "last_name":  "Smith",
    "email":      "alice@example.com",
    "phone":      "+1-555-0100",
    "life_stage": "lead",
    "type":       "contact"
  }
}
```

#### Example — list activities for contact 55

```json
{
  "ability": "wp-erp/crm-list-activities",
  "input": {
    "contact_id": 55,
    "number": 20,
    "offset": 0
  }
}
```

---

### Accounting abilities

| Ability ID | Label | Required capability |
|---|---|---|
| `wp-erp/ac-list-customers` | List Customers | `erp_ac_view_customer` |
| `wp-erp/ac-get-customer` | Get Customer | `erp_ac_view_single_customer` |
| `wp-erp/ac-create-customer` | Create Customer | `erp_ac_create_customer` |
| `wp-erp/ac-update-customer` | Update Customer | `erp_ac_edit_customer` |
| `wp-erp/ac-delete-customer` | Delete Customer | `erp_ac_delete_customer` |
| `wp-erp/ac-list-vendors` | List Vendors | `erp_ac_view_vendor` |
| `wp-erp/ac-get-vendor` | Get Vendor | `erp_ac_view_single_vendor` |
| `wp-erp/ac-create-vendor` | Create Vendor | `erp_ac_create_vendor` |
| `wp-erp/ac-update-vendor` | Update Vendor | `erp_ac_edit_vendor` |
| `wp-erp/ac-delete-vendor` | Delete Vendor | `erp_ac_delete_vendor` |
| `wp-erp/ac-list-accounts` | List Ledger Accounts | `erp_ac_view_account_lists` |
| `wp-erp/ac-create-account` | Create Ledger Account | `erp_ac_create_account` |
| `wp-erp/ac-list-journals` | List Journal Entries | `erp_ac_view_journal` |
| `wp-erp/ac-list-bank-accounts` | List Bank Accounts | `erp_ac_view_bank_accounts` |
| `wp-erp/ac-view-sales-summary` | View Sales Summary | `erp_ac_view_sales_summary` |
| `wp-erp/ac-view-expenses-summary` | View Expenses Summary | `erp_ac_view_expenses_summary` |
| `wp-erp/ac-view-reports` | View Accounting Reports | `erp_ac_view_reports` |

#### Example — view sales summary for Q1 2026

```json
{
  "ability": "wp-erp/ac-view-sales-summary",
  "input": {
    "start_date": "2026-01-01",
    "end_date":   "2026-03-31"
  }
}
```

Response:

```json
{
  "count": "42",
  "total": "158340.00",
  "outstanding": "23100.00"
}
```

#### Example — list journal entries

```json
{
  "ability": "wp-erp/ac-list-journals",
  "input": {
    "number":     25,
    "offset":     0,
    "start_date": "2026-01-01",
    "end_date":   "2026-03-31"
  }
}
```

---

## Permission model

Every ability is protected by a `permission_callback` that calls `current_user_can()` with an existing WP ERP capability. The authenticated WordPress user (identified by the Application Password used in the MCP client) must have that capability.

| WP ERP role | Granted capabilities (subset) |
|---|---|
| HRM Manager | `erp_list_employee`, `erp_create_employee`, `erp_edit_employee`, `erp_delete_employee`, `erp_can_terminate`, `erp_manage_department`, `erp_manage_designation`, `erp_leave_manage`, `erp_crate_announcement` |
| HRM Officer | `erp_list_employee`, `erp_view_employee`, `erp_leave_create_request`, `erp_view_announcement` |
| CRM Manager | `erp_crm_list_contact`, `erp_crm_add_contact`, `erp_crm_edit_contact`, `erp_crm_delete_contact`, `erp_crm_manage_groups`, `erp_crm_manage_activites`, `erp_crm_manage_schedules` |
| Accounting Manager | `erp_ac_view_customer`, `erp_ac_create_customer`, `erp_ac_view_vendor`, `erp_ac_create_vendor`, `erp_ac_view_journal`, `erp_ac_view_reports`, `erp_ac_view_sales_summary`, `erp_ac_view_expenses_summary` |

Assigning roles is done at **ERP → HR → Employees → (employee) → Permissions** in the WordPress admin.

---

## Extending — add your own abilities

You can register additional WP ERP-aware abilities from a plugin or theme using the same hooks.

```php
// Register a category (once per plugin, on this hook)
add_action( 'wp_abilities_api_categories_init', function () {
    if ( ! function_exists( 'wp_register_ability_category' ) ) {
        return;
    }

    wp_register_ability_category( 'my-erp-addon', [
        'label'       => 'My ERP Add-on',
        'description' => 'Custom ERP abilities for my add-on.',
    ] );
} );

// Register abilities on this hook
add_action( 'wp_abilities_api_init', function () {
    if ( ! function_exists( 'wp_register_ability' ) ) {
        return;
    }

    wp_register_ability( 'my-erp-addon/send-report', [
        'label'       => 'Send Monthly Report',
        'description' => 'Generate and email the monthly HRM report.',
        'category'    => 'my-erp-addon',

        'input_schema' => [
            'type'       => 'object',
            'required'   => [ 'email', 'month' ],
            'properties' => [
                'email' => [ 'type' => 'string', 'format' => 'email' ],
                'month' => [ 'type' => 'string', 'format' => 'date',
                             'description' => 'First day of the month to report (YYYY-MM-01).' ],
            ],
        ],

        'output_schema' => [
            'type'       => 'object',
            'properties' => [
                'sent' => [ 'type' => 'boolean' ],
            ],
        ],

        'permission_callback' => function () {
            // Reuse an existing ERP capability or define your own
            return current_user_can( 'erp_ac_view_reports' );
        },

        'execute_callback' => function ( $input ) {
            // Your business logic here
            $sent = my_addon_send_report( $input['email'], $input['month'] );

            return [ 'sent' => (bool) $sent ];
        },
    ] );
} );
```

### Removing a built-in ability

Use the `wp_abilities_api_init` hook with a higher priority and call `wp_unregister_ability()`:

```php
add_action( 'wp_abilities_api_init', function () {
    if ( function_exists( 'wp_unregister_ability' ) ) {
        // Remove the delete-employee ability from MCP clients
        wp_unregister_ability( 'wp-erp/hrm-delete-employee' );
    }
}, 20 ); // priority 20 runs after ERP's registration at default priority 10
```

---

## Disabling the abilities

To prevent WP ERP from registering any MCP abilities, add the following to a must-use plugin or your theme's `functions.php`:

```php
remove_action( 'wp_abilities_api_categories_init', 'erp_hrm_register_ability_category' );
remove_action( 'wp_abilities_api_categories_init', 'erp_crm_register_ability_category' );
remove_action( 'wp_abilities_api_categories_init', 'erp_ac_register_ability_category' );

remove_action( 'wp_abilities_api_init', 'erp_hrm_register_abilities' );
remove_action( 'wp_abilities_api_init', 'erp_crm_register_abilities' );
remove_action( 'wp_abilities_api_init', 'erp_ac_register_abilities' );
```
