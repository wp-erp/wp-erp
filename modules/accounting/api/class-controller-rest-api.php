<?php
namespace WeDevs\ERP\Accounting\API;

/**
 * REST_API Handler
 */
class REST_API {

    public function __construct() {
        add_filter( 'erp_rest_api_controllers', array( $this,  'register_accounting_new_controllers' ) );
    }

    public function register_accounting_new_controllers( $controllers ) {
        $this->include_controllers();

        $controllers = array_merge( $controllers, [
            '\WeDevs\ERP\Accounting\API\Customers_Controller',
            '\WeDevs\ERP\Accounting\API\Vendors_Controller',
            '\WeDevs\ERP\Accounting\API\Employees_Controller',
            '\WeDevs\ERP\Accounting\API\Inventory_Products_Controller',
            '\WeDevs\ERP\Accounting\API\Inventory_Product_Cats_Controller',
            '\WeDevs\ERP\Accounting\API\Ledgers_Accounts_Controller',
            '\WeDevs\ERP\Accounting\API\Invoices_Controller',
            '\WeDevs\ERP\Accounting\API\Payments_Controller',
            '\WeDevs\ERP\Accounting\API\Bills_Controller',
            '\WeDevs\ERP\Accounting\API\Pay_Bills_Controller',
            '\WeDevs\ERP\Accounting\API\Purchases_Controller',
            '\WeDevs\ERP\Accounting\API\Pay_Purchases_Controller',
            '\WeDevs\ERP\Accounting\API\Taxes_Controller',
            '\WeDevs\ERP\Accounting\API\Bank_Accounts_Controller',
        ] );

        return $controllers;
    }

    public function include_controllers() {
        require_once ERP_ACCOUNTING_API . '/class-rest-api-customers.php';
        require_once ERP_ACCOUNTING_API . '/class-rest-api-vendors.php';
        require_once ERP_ACCOUNTING_API . '/class-rest-api-employees.php';
        require_once ERP_ACCOUNTING_API . '/class-rest-api-products.php';
        require_once ERP_ACCOUNTING_API . '/class-rest-api-product-cats.php';
        require_once ERP_ACCOUNTING_API . '/class-rest-api-ledgers-accounts.php';
        require_once ERP_ACCOUNTING_API . '/class-rest-api-invoices.php';
        require_once ERP_ACCOUNTING_API . '/class-rest-api-payments.php';
        require_once ERP_ACCOUNTING_API . '/class-rest-api-bills.php';
        require_once ERP_ACCOUNTING_API . '/class-rest-api-pay_bills.php';
        require_once ERP_ACCOUNTING_API . '/class-rest-api-purchases.php';
        require_once ERP_ACCOUNTING_API . '/class-rest-api-pay_purchases.php';
        require_once ERP_ACCOUNTING_API . '/class-rest-api-taxes.php';
        require_once ERP_ACCOUNTING_API . '/class-rest-api-transfer-bank.php';
    }
}
