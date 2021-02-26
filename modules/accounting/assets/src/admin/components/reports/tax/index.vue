<template>
    <ul class="reports-overview">
        <li>
            <h3>{{ __('Sales Tax report (Transaction wise)', 'erp') }}</h3>
            <p>{{ __('Invoice Based Sales Tax Report ', 'erp') }}.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'SalesTaxReport' }">{{ __('View Report', 'erp')
                }}
            </router-link>
        </li>

        <li>
            <h3>{{ __('Sales Tax report (Customer wise)', 'erp') }}</h3>
            <p>{{ __('Sales Tax report (Customer wise)', 'erp') }}</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'SalesTaxReportCustomerBased' }">{{ __('View Report', 'erp') }}
            </router-link>
        </li>

        <li>
            <h3>{{ __('Sales Tax report (Category wise)', 'erp') }}</h3>
            <p>{{ __('Sales Tax report (Category wise)', 'erp') }}.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'SalesTaxReportCategoryBased' }">View Report
            </router-link>
        </li>

        <li>
            <h3>{{ __('Sales Tax report (Agency wise)', 'erp') }}</h3>
            <p>{{ __('Sales Tax report (Agency wise)', 'erp') }}.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'SalesTaxReportAgencyBased' }">View Report
            </router-link>
        </li>


        <li>
            <h3>{{ __('Purchase VAT report (Transaction wise)', 'erp') }}</h3>
            <p>{{ __('Purchase VAT report (Transaction wise)', 'erp') }}.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'purchaseTaxReport' }">View Report</router-link>
        </li>

        <li>
            <h3>{{ __('Purchase VAT report (Vendor wise) ', 'erp') }}</h3>
            <p>{{ __('Purchase VAT report (Vendor wise) ', 'erp') }}.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'purchaseTaxReportVendorWise' }"> View Report
            </router-link>
        </li>

        <li>
            <h3>{{ __('Purchase VAT report (Category wise)', 'erp') }}</h3>
            <p>{{ __('Purchase VAT report (Category wise)', 'erp') }}.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'purchaseTaxReportCategoryBased' }"> View Report
            </router-link>
        </li>

        <li>
            <h3>{{ __('Purchase VAT report (Agency wise)', 'erp') }}</h3>
            <p>{{ __('Purchase VAT report (Agency wise)', 'erp') }}.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'purchaseTaxReportAgencyBased' }"> View Report
            </router-link>
        </li>


    </ul>
</template>

<script>
    export default {
        name: 'ReportsOverview',

        data() {
            return {
                report: []
            };
        }
    };
</script>

<style lang="less">
    .reports-overview {
        margin: 0;
        padding: 10px;
        display: flex;
        flex-wrap: wrap;

        li {
            font-size: 20px;
            background: #fff;
            margin-bottom: 1px;
            padding: 15px;
            width: 48%;
            box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
            margin: 10px;
            border-radius: 3px;

            h3 {
                border-bottom: 1px solid rgba(0, 0, 0, .08);
                padding-bottom: 10px;
                font-weight: normal;
                color: #263238;
            }

            p {
                font-size: 15px;
                color: #525252;
            }
        }
    }
</style>
