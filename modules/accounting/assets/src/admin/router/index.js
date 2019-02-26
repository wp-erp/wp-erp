import Vue               from 'vue';
import Router            from 'vue-router';
import People            from 'admin/components/people/People.vue';
import Products          from 'admin/components/products/Products.vue';
import Employees         from 'admin/components/people/Employees.vue';
import EmployeeDetails   from 'admin/components/people/EmployeeDetails.vue';
import DashBoard         from 'admin/components/dashboard/DashBoard.vue';
import TrialBalance      from 'admin/components/reports/TrialBalance.vue';
import PeopleDetails     from 'admin/components/people/PeopleDetails.vue';
import InvoiceCreate     from 'admin/components/invoice/InvoiceCreate.vue';
import ChartOfAccounts   from 'admin/components/chart-accounts/ChartOfAccounts.vue';
import AddChartAccounts  from 'admin/components/chart-accounts/AddChartAccounts.vue';
import ReportsOverview   from 'admin/components/reports/ReportsOverview.vue';
import ProductCategory   from 'admin/components/product-category/ProductCategory.vue';
import RecPaymentCreate  from 'admin/components/rec-payment/RecPaymentCreate.vue';
import BillCreate        from 'admin/components/bill/BillCreate.vue';
import BillSingle        from 'admin/components/bill/BillSingle.vue';
import PayBillCreate     from 'admin/components/pay-bill/PayBillCreate.vue';
import PayBillSingle     from 'admin/components/pay-bill/PayBillSingle.vue';
import PurchaseCreate    from 'admin/components/purchase/PurchaseCreate.vue';
import PurchaseSingle    from 'admin/components/purchase/PurchaseSingle.vue';
import PayPurchaseCreate from 'admin/components/pay-purchase/PayPurchaseCreate.vue';
import PayPurchaseSingle from 'admin/components/pay-purchase/PayPurchaseSingle.vue';
import JournalList       from 'admin/components/journal/JournalList.vue';
import JournalCreate     from 'admin/components/journal/JournalCreate.vue';
import JournalSingle     from 'admin/components/journal/JournalSingle.vue';
import Transfer          from 'admin/components/transfers/Transfer.vue';
import ExpenseCreate     from 'admin/components/expense/ExpenseCreate.vue';
import SalesSingle       from 'admin/components/transactions/sales/SalesSingle.vue';
import Sales             from 'admin/components/transactions/sales/Sales.vue';
import Expenses          from 'admin/components/transactions/expenses/Expenses.vue';
import ExpenseSingle     from 'admin/components/expense/ExpenseSingle.vue';
import Purchases         from 'admin/components/transactions/purchases/Purchases.vue';
import NewTaxAgency      from 'admin/components/tax/NewTaxAgency.vue';
import NewTaxCategory    from 'admin/components/tax/NewTaxCategory.vue';
import NewTaxRate        from 'admin/components/tax/NewTaxRate.vue';
import NewTaxRateName    from 'admin/components/tax/NewTaxRateName.vue';
import RecordPayTax      from 'admin/components/tax/RecordPayTax.vue';
import TaxAgencies       from 'admin/components/tax/TaxAgencies.vue';
import TaxCategories     from 'admin/components/tax/TaxCategories.vue';
import TaxRateNames      from 'admin/components/tax/TaxRateNames.vue';
import TaxRates          from 'admin/components/tax/TaxRates.vue';
import TaxRecords        from 'admin/components/tax/TaxRecords.vue';
import BankAccounts      from 'admin/components/bank-accounts/BankAccounts.vue';
import CheckCreate       from 'admin/components/check/CheckCreate.vue';
import CheckSingle       from 'admin/components/check/CheckSingle.vue';
import SingleTaxRate     from 'admin/components/tax/SingleTaxRate.vue';

Vue.use(Router);

export default new Router({
    routes: [
        {
            path: '/',
            component: DashBoard,
            children: [
                {
                    path : '/dashboard',
                    name : 'DashBoard',
                    component: DashBoard,
                }
            ]
        },
        {
            path : '/inventory',
            name : 'Products',
            component: Products,
            alias: '/products'
        },
        {
            path: '/customers',
            component: { render (c) { return c('router-view') } },
            children: [
                {
                    path : '',
                    name : 'Customers',
                    component: People,
                    alias: '/users'
                },
                {
                    path : 'page/:page',
                    name : 'PaginateCustomers',
                    component: People,
                },
                {
                    path : 'view/:id',
                    name : 'CustomerDetails',
                    component: PeopleDetails,
                }
            ]
        },
        {
            path: '/vendors',
            component: { render (c) { return c('router-view') } },
            children: [
                {
                    path: '',
                    name: 'Vendors',
                    component: People,
                },
                {
                    path : 'view/:id',
                    name : 'VendorDetails',
                    component: PeopleDetails,
                },
                {
                    path: 'page/:page',
                    name: 'PaginateVendors',
                    component: People,
                },
            ]
        },
        {
            path: '/employees',
            component: { render (c) { return c('router-view') } },
            children: [
                {
                    path: '',
                    name: 'Employees',
                    component: Employees,
                },
                {
                    path: 'view/:id',
                    name: 'EmployeeDetails',
                    component: EmployeeDetails
                },
                {
                    path: 'page/:page',
                    name: 'PaginateEmployees',
                    component: Employees,
                }
            ]
        },
        {
            path: '/transactions',
            component: { render (c) { return c('router-view') } },
            children: [
                {
                    path: '',
                    name: 'Transactions',
                    component: Sales,
                },
                {
                    path: 'sales',
                    component: { render (c) { return c('router-view') } },
                    children: [
                        {
                            path: '',
                            name: 'Sales',
                            component: Sales,
                        },
                        {
                            path: ':id',
                            name: 'SalesSingle',
                            component: SalesSingle,
                        },
                    ]
                },
                {
                    path: 'expenses',
                    name: 'Expenses',
                    component: Expenses,
                },
                {
                    path: 'purchases',
                    name: 'purchases',
                    component: Purchases,
                },
            ]
        },
        {
            path: '/charts',
            component: { render (c) { return c('router-view') } },
            children: [
                {
                    path: '',
                    name: 'ChartOfAccounts',
                    component: ChartOfAccounts,
                },
                {
                    path: 'new',
                    name: 'AddChartAccounts',
                    component: AddChartAccounts,
                },
            ]
        },
        {
            path: '/banks',
            name: 'BankAccounts',
            component: BankAccounts
        },
        {
            path: '/invoices',
            component: { render (c) { return c('router-view') } },
            children: [
                {
                    path: 'new',
                    name: 'InvoiceCreate',
                    component: InvoiceCreate,
                },
                {
                    path: ':id/edit',
                    name: 'InvoiceEdit',
                    component: InvoiceCreate,
                },
            ]
        },
        {
            path: '/product_categories',
            name: 'ProductCategory',
            component: ProductCategory
        },
        {
            path: '/payments/new',
            name: 'RecPaymentCreate',
            component: RecPaymentCreate
        },
        {
            path: '/bills',
            component: { render (c) { return c('router-view') } },
            children: [
                {

                    path: 'new',
                    name: 'BillCreate',
                    component: BillCreate
                },
                {
                    path: ':id',
                    name: 'BillSingle',
                    component: BillSingle,
                },
                {
                    path: ':id/edit',
                    name: 'BillEdit',
                    component: BillCreate,
                },
            ]
        },
        {
            path: '/pay-bills',
            component: { render (c) { return c('router-view') } },
            children: [
                {

                    path: 'new',
                    name: 'PayBillCreate',
                    component: PayBillCreate
                },
                {
                    path: ':id',
                    name: 'PayBillSingle',
                    component: PayBillSingle,
                },
            ]
        },
        {
            path: '/purchases',
            component: { render (c) { return c('router-view') } },
            children: [
                {

                    path: 'new',
                    name: 'PurchaseCreate',
                    component: PurchaseCreate
                },
                {
                    path: ':id',
                    name: 'PurchaseSingle',
                    component: PurchaseSingle,
                },
                {
                    path: ':id/edit',
                    name: 'PurchaseEdit',
                    component: PurchaseCreate,
                },
            ]
        },
        {
            path: '/pay-purchases',
            component: { render (c) { return c('router-view') } },
            children: [
                {

                    path: 'new',
                    name: 'PayPurchaseCreate',
                    component: PayPurchaseCreate
                },
                {
                    path: ':id',
                    name: 'PayPurchaseSingle',
                    component: PayPurchaseSingle,
                },
            ]
        },
        {
            path: '/reports',
            component: { render (c) { return c('router-view') } },
            children: [
                {
                    path: '',
                    name: 'ReportsOverview',
                    component: ReportsOverview,
                },
                {
                    path: 'trial-balance',
                    name: 'TrialBalance',
                    component: TrialBalance,
                },
            ]
        },
        {
            path: '/journals',
            component: { render (c) { return c('router-view') } },
            children: [
                {
                    path: '',
                    name: 'Journals',
                    component: JournalList,
                },
                {

                    path: '/journals/new',
                    name: 'JournalCreate',
                    component: JournalCreate
                },
                {

                    path: '/journals/:id',
                    name: 'JournalSingle',
                    component: JournalSingle
                },
                {
                    path: 'page/:page',
                    name: 'PaginateJournals',
                    component: JournalList,
                },
            ]
        },
        {
            path: '/taxes',
            component: { render (c) { return c('router-view') } },
            children: [
                {
                    path: '',
                    name: 'TaxRates',
                    component: TaxRates,
                },
                {
                    path: 'new',
                    name: 'NewTaxRate',
                    component: NewTaxRate,
                },
                {
                    path: ':id',
                    name: 'SingleTaxRate',
                    component: SingleTaxRate,
                },
                {
                    path: ':id/edit',
                    name: 'EditSingleTaxRate',
                    component: SingleTaxRate,
                },
                {
                    path: 'page/:page',
                    name: 'PaginateTaxRates',
                    component: TaxRates,
                },
                {
                    path: 'rate-names',
                    name: 'TaxRateNames',
                    component: TaxRateNames,
                },
                {
                    path: 'rate-names/page/:page',
                    name: 'PaginateTaxRateNames',
                    component: TaxRateNames,
                },
                {
                    path: 'agencies',
                    name: 'TaxAgencies',
                    component: TaxAgencies,
                },
                {
                    path: 'agencies/page/:page',
                    name: 'PaginateTaxAgencies',
                    component: TaxAgencies,
                },
                {
                    path: 'categories',
                    name: 'TaxCategories',
                    component: TaxCategories,
                },
                {
                    path: 'categories/page/:page',
                    name: 'PaginateTaxCategories',
                    component: TaxCategories,
                },
                {
                    path: 'new-rate-name',
                    name: 'NewTaxRateName',
                    component: NewTaxRateName,
                },
                {
                    path: 'new-agency',
                    name: 'NewTaxAgency',
                    component: NewTaxAgency,
                },
                {
                    path: 'new-tax-cat',
                    name: 'NewTaxCategory',
                    component: NewTaxCategory,
                },
            ]
        },
        {
            path: '/tax-records',
            component: { render (c) { return c('router-view') } },
            children: [
                {
                    path: '',
                    name: 'TaxRecords',
                    component: TaxRecords,
                },
                {
                    path: 'page/:page',
                    name: 'PaginateTaxRecords',
                    component: TaxRecords,
                },
            ]
        },
        {
            path: '/transfer/new',
            name: 'Transfer',
            component: Transfer
        },
        {
            path: '/expenses',
            component: { render (c) { return c('router-view') } },
            children: [
                {

                    path: 'new',
                    name: 'ExpenseCreate',
                    component: ExpenseCreate
                },
                {
                    path: ':id',
                    name: 'ExpenseSingle',
                    component: ExpenseSingle,
                },
                {
                    path: ':id/edit',
                    name: 'ExpenseEdit',
                    component: ExpenseCreate,
                },
            ]
        },
        {
            path: '/checks',
            component: { render (c) { return c('router-view') } },
            children: [
                {

                    path: 'new',
                    name: 'CheckCreate',
                    component: CheckCreate
                },
                {
                    path: ':id',
                    name: 'CheckSingle',
                    component: CheckSingle,
                },
            ]
        },
        {
            path: '/pay-tax',
            name: 'RecordPayTax',
            component: RecordPayTax
        },
    ]
})
