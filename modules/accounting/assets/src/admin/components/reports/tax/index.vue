<template>
    <ul class="reports-overview">
        <li>
            <h3>{{ __('Sales Tax Report', 'erp') }}</h3>
            <p>{{ __('Invoice Based Sales Tax Report ', 'erp') }}.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'SalesTaxReport' }">{{ __('View Report', 'erp')
                }}
            </router-link>
        </li>

        <li>

            <p>{{ __('Customer Based Sales Tax Report', 'erp') }}</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'SalesTaxReportCustomerBased' }">{{ __('View Report', 'erp') }}
            </router-link>
        </li>

        <li>
            <h3>{{ __('Sales Tax Report Category Based', 'erp') }}</h3>
            <p>{{ __('Category Based Sales Tax Report', 'erp') }}.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'SalesTaxReportCategoryBased' }">View Report
            </router-link>
        </li>

        <li>
            <h3>{{ __('Sales Tax Report Agency Based', 'erp') }}</h3>
            <p>{{ __('Agency Based Sales Tax Report', 'erp') }}.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'SalesTaxReportAgencyBased' }">View Report
            </router-link>
        </li>


        <li>
            <h3>{{ __('Purchase Tax Report', 'erp') }}</h3>
            <p>{{ __('Purchase Tax Report Transaction Based', 'erp') }}.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'purchaseTaxReport' }">View Report</router-link>
        </li>

        <li>
            <h3>{{ __('Purchase Tax Report Vendor Based', 'erp') }}</h3>
            <p>{{ __('Purchase Tax Report Vendor Based', 'erp') }}.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'purchaseTaxReportVendorWise' }"> View Report
            </router-link>
        </li>

        <li>
            <h3>{{ __('Purchase Tax Report Category Based', 'erp') }}</h3>
            <p>{{ __('Purchase Tax Report Category Based', 'erp') }}.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'purchaseTaxReportCategoryBased' }"> View Report
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
