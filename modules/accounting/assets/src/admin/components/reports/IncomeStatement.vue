<template>
    <div>
        <h4>Income Statement</h4>

        <form action="" method="" @submit.prevent="fetchItems" class="query-options">

            <div class="wperp-date-group">
                <datepicker v-model="start_date"></datepicker>
                <datepicker v-model="end_date"></datepicker>
                <button class="wperp-btn btn--primary add-line-trigger" type="submit">Filter</button>
            </div>

        </form>

         <list-table
            tableClass="wperp-table table-striped table-dark widefat income-statement"
            :columns="columns"
            :rows="rows"
            :showCb="false">
            <template slot="debit" slot-scope="data">
                {{ Math.sign(data.row.balance) === 1 ? getCurrencySign() + data.row.balance : '' }}
            </template>
            <template slot="credit" slot-scope="data">
                {{ Math.sign(data.row.balance) === -1 ? getCurrencySign() + Math.abs(data.row.balance) : '' }}
            </template>
            <template slot="tfoot">
                <tr class="t-foot">
                    <td>Total</td>
                    <td>{{ getCurrencySign() + totalDebit }}</td>
                    <td>{{ getCurrencySign() + Math.abs(totalCredit) }}</td>
                </tr>
            </template>
        </list-table>

    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import Datepicker  from 'admin/components/base/Datepicker.vue'
    import ListTable from 'admin/components/list-table/ListTable.vue'

    export default {
        name: 'IncomeStatement',

        components: {
            ListTable,
            Datepicker
        },

        data() {
            return {
                start_date    : null,
                end_date      : null,
                bulkActions: [
                    {
                        key: 'trash',
                        label: 'Move to Trash',
                        img: erp_acct_var.erp_assets + '/images/trash.png'
                    }
                ],
                columns: {
                    'name': { label: 'Account Name' },
                    'debit': { label: 'Debit Total' },
                    'credit': { label: 'Credit Total' }
                },
                rows: [],
                totalDebit: 0,
                totalCredit: 0
            }
        },

        created() {
            this.fetchItems();
        },

        methods: {
            fetchItems() {
                this.rows = [];
                this.$store.dispatch( 'spinner/setSpinner', true );
                HTTP.get( '/reports/income-statement',{
                    params: {
                        start_date: this.start_date,
                        end_date  : this.end_date
                    }
                }).then(response => {
                    this.rows = response.data.rows;
                    this.totalDebit = response.data.total_debit;
                    this.totalCredit = response.data.total_credit;
                    this.$store.dispatch( 'spinner/setSpinner', false );
                });
            }
        }
    }
</script>

<style>
    .wperp-date-group {
        padding-left: 50px !important;
    }
    .income-statement tbody tr td:last-child {
        text-align: initial !important;
    }
    .income-statement .t-foot td {
        color: #2196f3;
        font-weight: bold;
    }
</style>
