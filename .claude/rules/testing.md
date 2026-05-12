# Testing rules — WP ERP

The project uses **Codeception**. Config: [codeception.yml](../../codeception.yml). Suites configured: `acceptance`, `functional`, `unit` ([acceptance.suite.yml](../../tests/acceptance.suite.yml) etc.).

## Suite map

| Suite | Tests | Location |
|---|---|---|
| `acceptance` | Browser flows via WebDriver + Selenium | `tests/acceptance/{HR,CRM,Accounting,Scenario}/*Cest.php` |
| `functional` | Headless WP-Browser (no JS) | `tests/functional/` |
| `unit` | Pure PHPUnit-style logic tests | `tests/unit/` (dir currently empty — add Cests here) |

Generated actor classes: [tests/_support/](../../tests/_support/) — `AcceptanceTester`, `FunctionalTester`, `UnitTester`.

## Setup

1. Copy [.env.example](../../.env.example) → `.env` and fill in your local DB + WP URL.
2. Export the test DB to `tests/_data/dump.sql`.
3. For acceptance: install Selenium standalone + ChromeDriver, launch Selenium before running.

Details: [tests/README.md](../../tests/README.md).

## Running

```bash
vendor/bin/codecept run                              # all suites
vendor/bin/codecept run acceptance
vendor/bin/codecept run acceptance HR
vendor/bin/codecept run acceptance HR/EmployeeCreateCest.php
vendor/bin/codecept run acceptance HR/EmployeeCreateCest.php:createValidEmployee
```

## Writing a Cest

Codeception's BDD-ish style:

```php
<?php
class EmployeeCreateCest {

    public function _before( AcceptanceTester $I ) {
        $I->amOnPage( '/wp-admin' );
        $I->loginAsAdmin();
    }

    public function createValidEmployee( AcceptanceTester $I ) {
        $I->amOnPage( '/wp-admin/admin.php?page=erp-hr-employee' );
        $I->click( 'Add New' );
        $I->fillField( 'first_name', 'Jane' );
        $I->fillField( 'last_name',  'Doe' );
        $I->fillField( 'email',      'jane@example.com' );
        $I->click( 'Create Employee' );
        $I->seeInDatabase( 'wp_erp_hr_employees', [ 'email' => 'jane@example.com' ] );
    }
}
```

Mirror the existing organisation: `tests/acceptance/HR/*Cest.php`, `tests/acceptance/CRM/*Cest.php`, `tests/acceptance/Accounting/*Cest.php`. Reusable steps go in `tests/_support/Step/Acceptance/`. Page Objects in `tests/_support/Page/Acceptance/`.

## What to test

- New REST endpoint → acceptance Cest hitting the endpoint with `$I->sendGET/POST/...`.
- New `erp_*()` helper → unit Cest exercising boundaries (empty input, invalid types, happy path).
- New capability check → functional Cest with a user lacking the cap, expecting 403.
- New MCP ability → unit Cest that calls the registered `execute_callback` directly (no MCP transport needed).

## Don't

- Don't commit `.env` or `tests/_output/*`.
- Don't run acceptance in CI here — the only CI workflow is phpcs ([.github/workflows/phpcs.yml](../../.github/workflows/phpcs.yml)). Acceptance is local-only today.
- Don't write tests that depend on a specific WP version's UI strings — use selectors / IDs.
