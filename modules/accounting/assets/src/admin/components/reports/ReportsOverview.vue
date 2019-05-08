<template>
    <ul>
        <li>
            <h3>Trial Balance</h3>
            <p>Trial balance is the bookkeeping or accounting report that lists the balances in each of general ledger accounts.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'TrialBalance' }">View Report</router-link>
        </li>

        <li>
            <h3>Ledger Report</h3>
            <p>Ledger report</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'LedgerSingle', params: { id: 1 } }">View Report</router-link>
        </li>

        <li>
            <h3>Income Statement</h3>
            <p>A summary of a management's performance as reflecte the profitability of an organization during the time interval.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'IncomeStatement' }">View Report</router-link>
        </li>

        <li>
            <h3>Sales Tax</h3>
            <p>It generates report based on the sales tax charged or paid for the current financial cycle/year.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'SalesTax' }">View Report</router-link>
        </li>

        <li>
            <h3>Balance Sheet</h3>
            <p>This is a report gives you an immediate status of your accounts at a specified date. You can call it a "Snapshot" view of the current position (day) of the financial year.</p>

            <router-link class="wperp-btn btn--primary" :to="{ name: 'BalanceSheet' }">View Report</router-link>
        </li>
    </ul>
</template>

<script>
    export default {
        name: 'ReportsOverview',
    }
</script>

<style lang="less" scoped>
    ul {
        margin: 0;
        padding: 10px;
        display: flex;
        flex-wrap: wrap;
    }

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
</style>

