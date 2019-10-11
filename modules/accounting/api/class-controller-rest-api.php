<?php

namespace WeDevs\ERP\Accounting\API;

/**
 * class REST_API Handler
 */
class REST_API {

    public function __construct() {
        add_filter( 'erp_rest_api_controllers', array( $this, 'register_accounting_new_controllers' ) );
    }

    public function register_accounting_new_controllers( $controllers ) {
        $this->include_controllers();

        $controllers = array_merge(
            $controllers,
            [
				'\WeDevs\ERP\Accounting\API\Customers_Controller',
				'\WeDevs\ERP\Accounting\API\Vendors_Controller',
				'\WeDevs\ERP\Accounting\API\Employees_Controller',
				'\WeDevs\ERP\Accounting\API\Inventory_Products_Controller',
				'\WeDevs\ERP\Accounting\API\Inventory_Product_Cats_Controller',
				'\WeDevs\ERP\Accounting\API\Ledgers_Accounts_Controller',
				'\WeDevs\ERP\Accounting\API\Opening_Balances_Controller',
				'\WeDevs\ERP\Accounting\API\Closing_Balance_Controller',
				'\WeDevs\ERP\Accounting\API\Invoices_Controller',
				'\WeDevs\ERP\Accounting\API\Payments_Controller',
				'\WeDevs\ERP\Accounting\API\Bills_Controller',
				'\WeDevs\ERP\Accounting\API\Pay_Bills_Controller',
				'\WeDevs\ERP\Accounting\API\Purchases_Controller',
				'\WeDevs\ERP\Accounting\API\Pay_Purchases_Controller',
				'\WeDevs\ERP\Accounting\API\Transactions_Controller',
				'\WeDevs\ERP\Accounting\API\Tax_Rates_Controller',
				'\WeDevs\ERP\Accounting\API\Bank_Accounts_Controller',
				'\WeDevs\ERP\Accounting\API\Company_Controller',
				'\WeDevs\ERP\Accounting\API\Currencies_Controller',
				'\WeDevs\ERP\Accounting\API\Journals_Controller',
				'\WeDevs\ERP\Accounting\API\Expenses_Controller',
				'\WeDevs\ERP\Accounting\API\Tax_Agencies_Controller',
				'\WeDevs\ERP\Accounting\API\Tax_Cats_Controller',
				'\WeDevs\ERP\Accounting\API\Tax_Rate_Names_Controller',
				'\WeDevs\ERP\Accounting\API\People_Controller',
				'\WeDevs\ERP\Accounting\API\Reports_Controller',
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
