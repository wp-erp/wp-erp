<template>
    <div class="wperp-container">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">{{ __('Expenses Transactions', 'erp') }}</h2>
                    <combo-box :options="pages" :hasUrl="true" :placeholder="__('New Transaction', 'erp')" />
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <expenses-stats />

        <transactions-filter />

        <expenses-list />

        <!-- End .wperp-crm-table -->
    </div>
</template>

<script>
import 'assets/js/plugins/chart.min';
import 'assets/js/status_chart';

import ComboBox from 'admin/components/select/ComboBox.vue';
import ExpensesStats from 'admin/components/transactions/expenses/ExpensesStats.vue';
import ExpensesList from 'admin/components/transactions/expenses/ExpensesList.vue';
import TransactionsFilter from 'admin/components/transactions/TransactionsFilter.vue';

export default {
    name: 'Expenses',

    components: {
        ComboBox,
        ExpensesStats,
        ExpensesList,
        TransactionsFilter
    },

    data() {
        return {
            pages: [
                { namedRoute: 'ExpenseCreate', name: 'Create Expense' },
                { namedRoute: 'CheckCreate', name: 'Create Check' },
                { namedRoute: 'BillCreate', name: 'Create Bill' },
                { namedRoute: 'PayBillCreate', name: 'Pay Bill' }
            ]
        };
    }
};
</script>
