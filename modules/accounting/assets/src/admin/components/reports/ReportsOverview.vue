<template>
    <ul>
        <li><router-link :to="{ name: 'TrialBalance' }">Trial Balance</router-link></li>
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
