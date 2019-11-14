<template>
    <ul class="reports-overview">
        <li>
            <h3>{{ __('Trial Balance', 'erp') }}</h3>
            <p>{{ __('Trial balance is the bookkeeping or accounting report that lists the balances in each of general ledger accounts', 'erp') }}.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'TrialBalance' }">{{ __('View Report', 'erp') }}</router-link>
        </li>

        <li>
            <h3>{{ __('Ledger Report', 'erp') }}</h3>
            <p>{{ __('The ledger report contains the classified and detailed information of all the individual accounts including the debit and credit aspects.', 'erp') }}</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'LedgerSingle', params: { id: 7 } }">{{ __('View Report', 'erp') }}</router-link>
        </li>

        <li>
            <h3>{{ __('Income Statement', 'erp') }}</h3>
            <p>{{ __('A summary of a management\'s performance reflected as the profitability of an organization during the time interval', 'erp') }}.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'IncomeStatement' }">View Report</router-link>
        </li>

        <li>
            <h3>{{ __('Sales Tax', 'erp') }}</h3>
            <p>{{ __('It generates report based on the sales tax charged or paid for the current financial cycle/year', 'erp') }}.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'SalesTax' }">{{ __('View Report', 'erp') }}</router-link>
        </li>

        <li>
            <h3>{{ __('Balance Sheet', 'erp') }}</h3>
            <p>{{ __('This report gives you an immediate status of your accounts at a specified date. You can call it a "Snapshot" view of the current position (day) of the financial year', 'erp') }}.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'BalanceSheet' }">{{ __('View Report', 'erp') }}</router-link>
        </li>

        <component
            v-for="(component, index) in reportLists"
            :key="index"
            :is="component"
        />
    </ul>
</template>

<script>
export default {
    name: 'ReportsOverview',

    data() {
        return {
            reportLists: window.acct.hooks.applyFilters('acctExtensionReportsList', [])
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
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            margin: 10px;
            border-radius: 3px;

            h3 {
                border-bottom: 1px solid rgba(0,0,0, .08);
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
