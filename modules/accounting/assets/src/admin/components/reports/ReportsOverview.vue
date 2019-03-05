<template>
    <ul>
        <li><router-link :to="{ name: 'TrialBalance' }">Trial Balance</router-link></li>
        <li><router-link :to="{ name: 'LedgerReport' }">Ledger Report</router-link></li>
        <li><router-link :to="{ name: 'IncomeStatement' }">Income Statement</router-link></li>
        <li><router-link :to="{ name: 'SalesTax' }">Sales Tax</router-link></li>
        <li><router-link :to="{ name: 'BalanceSheet' }">Balance Sheet</router-link></li>
    </ul>
</template>

<script>
    export default {
        name: 'ReportsOverview',
    }
</script>

<style lang="less" scoped>
    // temporary styles

    ul {
        margin: 0;
        padding: 10px;
        width: 200px;
    }

    li {
        font-size: 20px;
        background: #fff;
        margin-bottom: 1px;
        padding: 10px;
    }
</style>

