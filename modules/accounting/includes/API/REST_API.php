<?php

namespace WeDevs\ERP\Accounting\API;

/**
 * class REST_API Handler
 */
class REST_API {
    public function __construct() {
        add_filter( 'erp_rest_api_controllers', [ $this, 'register_accounting_new_controllers' ] );
    }

    public function register_accounting_new_controllers( $controllers ) {
        $this->include_controllers();

        $controllers = array_merge(
            $controllers,
            [
                '\WeDevs\ERP\Accounting\API\CustomersController',
                '\WeDevs\ERP\Accounting\API\VendorsController',
                '\WeDevs\ERP\Accounting\API\EmployeesController',
                '\WeDevs\ERP\Accounting\API\InventoryProductsController',
                '\WeDevs\ERP\Accounting\API\InventoryProductCatsController',
                '\WeDevs\ERP\Accounting\API\LedgersAccountsController',
                '\WeDevs\ERP\Accounting\API\OpeningBalancesController',
                '\WeDevs\ERP\Accounting\API\ClosingBalanceController',
                '\WeDevs\ERP\Accounting\API\InvoicesController',
                '\WeDevs\ERP\Accounting\API\PaymentsController',
                '\WeDevs\ERP\Accounting\API\BillsController',
                '\WeDevs\ERP\Accounting\API\PayBillsController',
                '\WeDevs\ERP\Accounting\API\PurchasesController',
                '\WeDevs\ERP\Accounting\API\PayPurchasesController',
                '\WeDevs\ERP\Accounting\API\TransactionsController',
                '\WeDevs\ERP\Accounting\API\TaxRatesController',
                '\WeDevs\ERP\Accounting\API\BankAccountsController',
                '\WeDevs\ERP\Accounting\API\CompanyController',
                '\WeDevs\ERP\Accounting\API\CurrenciesController',
                '\WeDevs\ERP\Accounting\API\JournalsController',
                '\WeDevs\ERP\Accounting\API\ExpensesController',
                '\WeDevs\ERP\Accounting\API\TaxAgenciesController',
                '\WeDevs\ERP\Accounting\API\TaxCatsController',
                '\WeDevs\ERP\Accounting\API\TaxRateNamesController',
                '\WeDevs\ERP\Accounting\API\PeopleController',
                '\WeDevs\ERP\Accounting\API\ReportsController',
            ]
        );

        return $controllers;
    }

    public function include_controllers() {
        foreach ( glob( ERP_ACCOUNTING_API . '/*.php' ) as $filename ) {
            include_once $filename;
        }
    }
}
