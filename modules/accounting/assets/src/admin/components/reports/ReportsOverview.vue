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

            <router-link class="wperp-btn btn--primary" :to="{ name: 'IncomeStatement' }">{{ __('View Report', 'erp') }}</router-link>
        </li>

        <li>
            <h3>{{ __('Sales Tax', 'erp') }}</h3>
            <p>{{ __('It generates report based on the sales tax charged or paid for the current financial cycle/year', 'erp') }}.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'SalesTaxReportOverview' }">{{ __('View Report', 'erp') }}</router-link>
        </li>

        <li>
            <h3>{{ __('Balance Sheet', 'erp') }}</h3>
            <p>{{ __('This report gives you an immediate status of your accounts at a specified date. You can call it a "Snapshot" view of the current position (day) of the financial year', 'erp') }}.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'BalanceSheet' }">{{ __('View Report', 'erp') }}</router-link>
        </li>
        <li v-if="!proActivated" class="reports-popup">
            <h3 class="pro-popup-reports-main">
                {{ __('Purchase VAT', 'erp') }}
                <span class="pro-popup-nav">Pro</span>
            </h3>
            <p>{{ __('It generates report based on the VAT on purchases charged or paid for the current financial cycle/year', 'erp') }}.</p>

            <router-link
                class="wperp-btn btn--primary"
                :to="{ name: 'ReportsOverview' }">
                {{ __('View Report', 'erp') }}
            </router-link>
        </li>
        <li v-if="!proActivated" class="reports-popup">
            <h3 class="pro-popup-reports-main">
                {{ __( 'Purchase Return', 'erp' ) }}
                <span class="pro-popup-nav">Pro</span>
            </h3>
            <p>{{ __( 'It generates report based on the purchases that have been returned for the current financial cycle/year', 'erp' ) }}.</p>

            <router-link
                class="wperp-btn btn--primary"
                :to="{ name: 'ReportsOverview' }">
                {{ __( 'View Report', 'erp' ) }}
            </router-link>
        </li>
        <li v-if="!proActivated" class="reports-popup">
            <h3 class="pro-popup-reports-main">
                {{ __( 'Sales Return', 'erp' ) }}
                <span class="pro-popup-nav">Pro</span>
            </h3>
            <p>{{ __( 'It generates report based on the sales that have been returned for the current financial cycle/year', 'erp' ) }}.</p>

            <router-link
                class="wperp-btn btn--primary"
                :to="{ name: 'ReportsOverview' }">
                {{ __( 'View Report', 'erp' ) }}
            </router-link>
        </li>
        <li v-if="!proActivated" class="reports-popup">
            <h3 class="pro-popup-reports-main">
                {{ __('Product Sales', 'erp') }}
                <span class="pro-popup-nav">Pro</span>
            </h3>
            <p>{{ __('Product Sales history will be shown here with date between facility', 'erp') }}.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'InventorySalesReport' }">
                {{ __('View Report', 'erp') }}
            </router-link>
        </li>
        <li v-if="!proActivated" class="reports-popup">
            <h3 class="pro-popup-reports-main">
                {{ __('Product Purchase', 'erp') }}
                <span class="pro-popup-nav">Pro</span>
            </h3>
            <p>{{ __('Product Purchases history will be shown here with date between facility', 'erp') }}.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'InventoryPurchaseReport' }">{{ __('View Report',
                'erp') }}
            </router-link>
        </li>
        <li v-if="!proActivated" class="reports-popup">
            <h3 class="pro-popup-reports-main">
                {{ __('Inventory Report', 'erp') }}
                <span class="pro-popup-nav">Pro</span>
            </h3>
            <p>{{ __('Product purchase and sales history will be shown here with date between facility.', 'erp')
               }}</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'InventoryHistoryReport' }">
                {{ __('View Report', 'erp') }}
            </router-link>
        </li>
        <li v-if="!proActivated" class="reports-popup">
            <h3 class="pro-popup-reports-main">
                {{ __('Reimbursements', 'erp') }}
                <span class="pro-popup-nav">Pro</span>
            </h3>
            <p>{{ __('This is a report provides you the transactions of a particular people at a specified date',
                'erp') }}.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'PeopleTrnReport' }">{{ __('View Report',
                'erp') }}
            </router-link>
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

    data () {
        return {
            reportLists: window.acct.hooks.applyFilters('acctExtensionReportsList', []),
            proEnable: false,
            proActivated: false,
        }
    },

    watch:{
      '$store.state..common.erp_pro_activated' : function() {
          console.log(this.$store.state.erp_pro_activated + 'ok')
      }
    },

    mounted () {
        setTimeout(() => {
            this.proActivated = this.$store.state.erp_pro_activated
        }, 200)
    },
}
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
