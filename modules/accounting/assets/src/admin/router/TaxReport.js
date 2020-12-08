
import TaxReport from 'admin/components/reports/tax/index.vue';
import SalesTaxReport from 'admin/components/reports/tax/sales/TransactionBased.vue';
import SalesTaxReportCustomerBased from 'admin/components/reports/tax/sales/CustomerBased.vue';
import SalesTaxReportCategoryBased from 'admin/components/reports/tax/sales/CategoryBased.vue';
import SalesTaxReportAgencyBased from 'admin/components/reports/tax/sales/AgencyBased.vue';

import purchaseTaxReport from 'admin/components/reports/tax/purchase/TransactionBased.vue';
import purchaseTaxReportVendorBased from 'admin/components/reports/tax/purchase/VendorBased.vue';
import purchaseTaxReportCategoryBased from 'admin/components/reports/tax/purchase/CategoryBased.vue';
import purchaseTaxReportAgencyBased from 'admin/components/reports/tax/purchase/AgencyBased.vue';

export default [
    {
        path: "/tax-report",
        name: "TaxReport",
        component: TaxReport,
        meta: {
            title: "Tax Report List"
        }
    },
    {
        path: "/tax-report/sales",
        name: "SalesTaxReport",
        component: SalesTaxReport,
        meta: {
            title: "Sales Tax Report"
        }
    },
    {
        path: "/tax-report/sales/customer-based",
        name: "SalesTaxReportCustomerBased",
        component: SalesTaxReportCustomerBased,
        meta: {
            title: "Sales Tax Report Customer Based"
        }
    },
    {
        path: "/tax-report/sales/category-Based",
        name: "SalesTaxReportCategoryBased",
        component: SalesTaxReportCategoryBased,
        meta: {
            title: "Sales Tax Report Category Based"
        }
    },
    {
        path: "/tax-report/sales/category-Based",
        name: "SalesTaxReportCategoryBased",
        component: SalesTaxReportCategoryBased,
        meta: {
            title: "Sales Tax Report Category Based"
        }
    },
    {
        path: "/tax-report/sales/agency-Based",
        name: "SalesTaxReportAgencyBased",
        component: SalesTaxReportAgencyBased,
        meta: {
            title: "Sales Tax Report Agency Based"
        }
    },
    {
        path: "/tax-report/purchase",
        name: "purchaseTaxReport",
        component: purchaseTaxReport,
        meta: {
            title: "Purchase Tax Report"
        }
    },
    {
        path: "/tax-report/purchase/vendor-based",
        name: "purchaseTaxReportVendorBased",
        component: purchaseTaxReportVendorBased,
        meta: {
            title: "Purchase Tax Report Vendor Based"
        }
    },
    {
        path: "/tax-report/purchase/category-based",
        name: "purchaseTaxReportCategoryBased",
        component: purchaseTaxReportCategoryBased,
        meta: {
            title: "Purchase Tax Report Category Based"
        }
    },
    {
        path: "/tax-report/purchase/agency-based",
        name: "purchaseTaxReportAgencyBased",
        component: purchaseTaxReportAgencyBased,
        meta: {
            title: "Purchase Tax Report Agency Based"
        }
    },

];
